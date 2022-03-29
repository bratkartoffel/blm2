<?php
	/**
		* Da dieser Cronjob regelmäßig ausgeführt wird (Standard: 30 Minuten), werden hier das Einkommen, die Zinsen und die Platzierung des Spiels auf den Voteseiten abgehandelt.
		* 
		* @version 1.0.0
		* @author Simon Frankenberger <simonfrankenberger@web.de>
		* @package blm2.cronjobs
	*/
	
	require("../include/config.inc.php");
	require("../include/functions.inc.php");
	
	ConnectDB();
	error_reporting(0);
	
	/**
		* Hilfsfunktion: Überprüft, ob der Benutzer sein Einkommen und Zinsen bekommt und verbucht diese auch direkt
		* 
		* @author Simon Frankenberger <simonfrankenberger@web.de>
		* @version 1.0.0
		* 
		* @param double $ZinsenKredit
		* @param double $ZinsenAnlage
		* 
		* @return void
	**/
	function EinkommenBankCheck($ZinsenKredit, $ZinsenAnlage) {
		$sql_abfrage="SELECT
										m.ID,
										m.Bank,
										s.AusgabenZinsen,
										s.EinnahmenZinsen,
										g.Gebaeude3,
										g.Gebaeude4,
										m.Geld,
										s.EinnahmenGebaeude,
										m.Punkte
									FROM
										(
											(
												mitglieder m NATURAL JOIN gebaeude g
											) NATURAL JOIN forschung f
										) NATURAL JOIN statistik s
									ORDER BY
										ID ASC;";
		$sql_ergebnis=mysql_query($sql_abfrage);
		
		while($user=mysql_fetch_object($sql_ergebnis)) {
			/*
				Zuerst werden die Zinsen abgearbeitet
			*/
			
			if($user->Bank < 0) {		// Kontostand ist < 0 also muss er Kreditzinsen zahlen
				$Zinsen=$user->Bank*($ZinsenKredit-1);
				$user->AusgabenZinsen+=($Zinsen*-1);
				$user->Bank+=$Zinsen;
			}
			else {		// Er hat ein positives Guthaben auf dem Konto, also bekommt er Anlagezinsen
				$Zinsen=$user->Bank*($ZinsenAnlage-1);
				if($user->Punkte < 100000) {
					if($user->Bank+$Zinsen < 100000) {
						$user->EinnahmenZinsen+=$Zinsen;
						$user->Bank+=$Zinsen;
					}
					else {
						$user->EinnahmenZinsen+=(100000-$user->Bank);
						$user->Bank=99999.99;
					}
				}
				else {
					if($user->Bank+$Zinsen < $user->Punkte) {
						$user->EinnahmenZinsen+=$Zinsen;
						$user->Bank+=$Zinsen;
					}
					else {
						$user->EinnahmenZinsen+=($user->Punkte-$user->Bank);
						$user->Bank=$user->Punkte;
					}
				}
			}
			
			$einkommen=(EINKOMMEN_BASIS+($user->Gebaeude3*EINKOMMEN_BIOLADEN_BONUS)+($user->Gebaeude4*EINKOMMEN_DOENERSTAND_BONUS));		// Dann das Einkommen ausrechnen
			
			$user->Geld+=$einkommen;
			$user->EinnahmenGebaeude+=$einkommen;
			
			$sql_abfrage="UPDATE
											mitglieder m NATURAL JOIN statistik s
										SET
											m.Bank=" . $user->Bank . ",
											s.AusgabenZinsen=" . $user->AusgabenZinsen . ",
											s.EinnahmenZinsen=" . $user->EinnahmenZinsen . ",
											m.Geld=" . $user->Geld . ",
											s.EinnahmenGebaeude=" . $user->EinnahmenGebaeude . "
										WHERE
											m.ID=" . $user->ID . ";";
			mysql_query($sql_abfrage);
		}
	}
	
	/**
		* Hilfsfunktion: Holt sich die Platzierung des Spiels auf den Voteseiten und speichert diese zwischen.
		* 
		* @author Simon Frankenberger <simonfrankenberger@web.de>
		* @version 1.0.0
		* 
		* @return array
	**/
	function getPlatzierungLive() {
		// Ruft die Platzierung von Galaxy-News.de ab
		$datei=fopen("http://www.galaxy-news.de/games/2839_bioladenmanager_2.html", "r");		// Öffnet ein Handle um die Platzierung live von deren Seite zu holen.
		
		while(!feof($datei)) {
			$daten.=fgets($datei, 1024);	// Holt sich Stück für Stück die Daten der Seite.
		}
		
		$daten=stripslashes($daten);
		
		$daten=explode('<!-- Charts -->', $daten);		// Zerteilt die Daten erst mal anhand der "Charts"-Kommentare, welche die Tabelle umgrenzen
		$daten=explode('<span style="font-size:13px; font-weight:bold;">', $daten[1]);	// Teilt nun nach den Ranglistenpositionen auf (Sind per CSS formatiert)
		ereg("[0-9]*", $daten[3], $rang);	// Holt sich jetzt per RegEx die Platzierung
		
		$platz["galaxy-news.de"]=$rang[0];	// Hier haben wir die Platzierung
		
		
		// Dann kommt die Platzierung von GamesDynamite.de
		
		$datei=fopen("http://bgs.gdynamite.de/games_1600.html", "r");		// Öffnet ein Handle um die Platzierung live von deren Seite zu holen.
		
		while(!feof($datei)) {
			$daten.=fgets($datei, 1024);	// Holt sich Stück für Stück die Daten der Seite.
		}
		
		$daten=stripslashes($daten);
		
		ereg("Rang\ [0-9]*", $daten, $rang);
		$rang=str_replace("Rang ", "", $rang[0]);
		
		$platz["gamesdynamite.de"]=$rang;
		
		
		// Dann kommt die Platzierung bei Browserwelten.de
		
		$datei=fopen("http://www.browserwelten.net/index.php?ac=charts", "r");		// Öffnet ein Handle um die Platzierung live von deren Seite zu holen.
		
		while(!feof($datei)) {
			$daten.=fgets($datei, 1024);	// Holt sich Stück für Stück die Daten der Seite.
		}
		
		$daten=stripslashes($daten);
		
		$daten=explode('<td class="tdh3">&nbsp;<img src="img/url.gif" align="absmiddle"> <a href="index.php?ac=gameview&gameid=2160" target="_top">Bioladenmanager 2</a></td>', $daten);
		
		$daten=$daten[0];
		
		$daten=explode('<td class="tdh3" align="center">', $daten);
		
		$daten=$daten[count($daten)-1];
		ereg("[0-9]*", $daten, $rang);
		
		$platz["browserwelten.de"]=$rang[0];
		
		return $platz;
	}
	
	EinkommenBankCheck($ZinsenKredit, $ZinsenAnlage);
	
	$platz=getPlatzierungLive();
	
	
	
	$datei=fopen("../votes/galaxy-news.txt", "w");
	fputs($datei, $platz["galaxy-news.de"]);
	fclose($datei);
	
	$datei=fopen("../votes/gamesdynamite.txt", "w");
	fputs($datei, $platz["gamesdynamite.de"]);
	fclose($datei);
	
	$datei=fopen("../votes/browserwelten.txt", "w");
	fputs($datei, $platz["browserwelten.de"]);
	fclose($datei);
?>