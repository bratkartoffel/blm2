<?php
	/**
		* Berechnet die Verkaufspreise der Waren anhand der Forschung und der Gebäudestufen
		* 
		* @version 1.0.0
		* @author Simon Frankenberger <simonfrankenberger@web.de>
		* @package blm2.includes
	*/
	
	for($i=1;$i<=ANZAHL_WAREN;$i++) {
		$temp="Forschung" . $i;
		$Preis[$i]=(WAREN_PREIS_GRUND+$ich->$temp*WAREN_PREIS_FORSCHUNG+$ich->Gebaeude3*WAREN_PREIS_BIOLADEN+$ich->Gebaeude6*WAREN_PREIS_VERKAEUFERSCHULE+$i*WAREN_PREIS_WARE)*$KursWare[$i];
	}
?>