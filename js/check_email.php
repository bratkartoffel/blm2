<?php
	/**
		* Wird per AJAX aufgerufen, und überprüft, ob die EMail-Adresse gültig und noch frei ist.
		* 
		* @version 1.0.0
		* @author Simon Frankenberger <simonfrankenberger@web.de>
		* @package blm2.includes
	*/
	include("../include/config.inc.php");				// bindet die Konfigurationsdatei ein
	include("../include/functions.inc.php");			// bindet die Funktionen ein
	
	$email=trim($_GET['uemail']);
	
	if(!CheckEMail($email)) {
		die('0');
	}
	
	ConnectDB();
	
	$sql_abfrage="SELECT
									'1'
								FROM
									mitglieder
								WHERE
									EMail LIKE '" . mysql_real_escape_string($email) . "';";
	$sql_ergebnis=mysql_query($sql_abfrage);
	
	if(mysql_num_rows($sql_ergebnis) == 0) {
		die('1');
	}
	else {
		die('2');
	}
?>