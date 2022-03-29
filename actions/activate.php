<?php
	/**
		* Führt die Aktionen des Benutzers zum Aktivieren seines Accounts aus
		* 
		* @version 1.0.0
		* @author Simon Frankenberger <simonfrankenberger@web.de>
		* @package blm2.actions
	*/
	
	include("../include/config.inc.php");
	include("../include/functions.inc.php");
	
	$user=trim($_GET['user']);
	$code=trim($_GET['code']);
	
	if(strlen($user) < 2 || strlen($code) <> 40) {
		die("");
	}
	
	ConnectDB();		// Verbindung mit der Datenbank aufbauen
	
	$sql_abfrage="UPDATE
									mitglieder
								SET
									EMailAct = NULL
								WHERE
									Name LIKE '" . mysql_real_escape_string($user) . "'
								AND
									EMailAct = '" . mysql_real_escape_string($code) . "'
								;";
	$sql_ergebnis=mysql_query($sql_abfrage);
	
	if(mysql_affected_rows() == 0) {
		DisconnectDB();
		die("Ungültiger Aktivierungscode, oder Account bereits aktiviert!");
	}
	else {
		DisconnectDB();
		die('<h2>Account erfolgreich aktiviert. Sie können sich nun einloggen. Viel Spaß beim BLM2!<br /><br /><a href="../?p=anmelden" style="color: blue;">Zur Anmeldung</a></h2>');
	}
?>