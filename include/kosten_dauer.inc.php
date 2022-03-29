<?php
	/**
		* Berechnet die Kosten, Dauer und Punktzahl eines Auftrags nach dem Schema des Zinses-Zins: Kapital_X=Kapital_0*(Zinssatz^X)
		* 
		* @version 1.0.0
		* @author Simon Frankenberger <simonfrankenberger@web.de>
		* @package blm2.includes
	*/
	
	$Plantage->Kosten = 
		$Plantage->BasisKosten
		*
		pow($Plantage->KostenFaktor, $ich->Gebaeude1+1);
	
	$Plantage->Dauer = 
		$Plantage->BasisDauer
		*
		(pow($Plantage->DauerFaktor, $ich->Gebaeude1+1)*(1-(BONUS_FAKTOR_BAUHOF*$ich->Gebaeude5)));
	
	$Plantage->Punkte = 
		$Plantage->BasisPunkte
		*
		(pow($Plantage->PunkteFaktor, $ich->Gebaeude1+1));
	
	
	
	$Forschungszentrum->Kosten = 
		$Forschungszentrum->BasisKosten
		*
		pow($Forschungszentrum->KostenFaktor, $ich->Gebaeude2+1);
	
	$Forschungszentrum->Dauer = 
		$Forschungszentrum->BasisDauer
		*
		(pow($Forschungszentrum->DauerFaktor, $ich->Gebaeude2+1)*(1-(BONUS_FAKTOR_BAUHOF*$ich->Gebaeude5)));
	
	$Forschungszentrum->Punkte = 
		$Forschungszentrum->BasisPunkte
		*
		(pow($Forschungszentrum->PunkteFaktor, $ich->Gebaeude2+1));
	
	
	
	$Bioladen->Kosten = 
		$Bioladen->BasisKosten
		*
		pow($Bioladen->KostenFaktor, $ich->Gebaeude3+1);
	
	$Bioladen->Dauer = 
		$Bioladen->BasisDauer
		*
		(pow($Bioladen->DauerFaktor, $ich->Gebaeude3+1)*(1-(BONUS_FAKTOR_BAUHOF*$ich->Gebaeude5)));
	
	$Bioladen->Punkte = 
		$Bioladen->BasisPunkte
		*
		(pow($Bioladen->PunkteFaktor, $ich->Gebaeude3+1));
	
	
	
	$Doenerstand->Kosten = 
		$Doenerstand->BasisKosten
		*
		pow($Doenerstand->KostenFaktor, $ich->Gebaeude4+1);
	
	$Doenerstand->Dauer = 
		$Doenerstand->BasisDauer
		*
		(pow($Doenerstand->DauerFaktor, $ich->Gebaeude4+1)*(1-(BONUS_FAKTOR_BAUHOF*$ich->Gebaeude5)));
	
	$Doenerstand->Punkte = 
		$Doenerstand->BasisPunkte
		*
		(pow($Doenerstand->PunkteFaktor, $ich->Gebaeude4+1));
	
	
	
	$Bauhof->Kosten = 
		$Bauhof->BasisKosten
		*
		pow($Bauhof->KostenFaktor, $ich->Gebaeude5+1);
	
	$Bauhof->Dauer = 
		$Bauhof->BasisDauer
		*
		(pow($Bauhof->DauerFaktor, $ich->Gebaeude5+1));
	
	$Bauhof->Punkte = 
		$Bauhof->BasisPunkte
		*
		(pow($Bauhof->PunkteFaktor, $ich->Gebaeude5+1));
	
	
	
	$Schule->Kosten = 
		$Schule->BasisKosten
		*
		pow($Schule->KostenFaktor, $ich->Gebaeude6+1);
	
	$Schule->Dauer = 
		$Schule->BasisDauer
		*
		(pow($Schule->DauerFaktor, $ich->Gebaeude6+1)*(1-(BONUS_FAKTOR_BAUHOF*$ich->Gebaeude5)));
	
	$Schule->Punkte = 
		$Schule->BasisPunkte
		*
		(pow($Schule->PunkteFaktor, $ich->Gebaeude6+1));
	
	
	
	$Zaun->Kosten = 
		$Zaun->BasisKosten
		*
		pow($Zaun->KostenFaktor, $ich->Gebaeude7+1);
	
	$Zaun->Dauer = 
		$Zaun->BasisDauer
		*
		(pow($Zaun->DauerFaktor, $ich->Gebaeude7+1)*(1-(BONUS_FAKTOR_BAUHOF*$ich->Gebaeude5)));
	
	$Zaun->Punkte = 
		$Zaun->BasisPunkte
		*
		(pow($Zaun->PunkteFaktor, $ich->Gebaeude7+1));
	
	
	
	$Pizzeria->Kosten = 
		$Pizzeria->BasisKosten
		*
		pow($Pizzeria->KostenFaktor, $ich->Gebaeude8+1);
	
	$Pizzeria->Dauer = 
		$Pizzeria->BasisDauer
		*
		(pow($Pizzeria->DauerFaktor, $ich->Gebaeude8+1)*(1-(BONUS_FAKTOR_BAUHOF*$ich->Gebaeude5)));
	
	$Pizzeria->Punkte = 
		$Pizzeria->BasisPunkte
		*
		(pow($Pizzeria->PunkteFaktor, $ich->Gebaeude8+1));
	
	
	
	for($i=1; $i<=ANZAHL_WAREN; $i++) {
		$temp="Forschung" . $i;
		
		$$temp->Kosten = 
			(
				100
				*
				$i
			)
			+
			(
				$Forschung->BasisKosten
				*
				pow($Forschung->KostenFaktor, $ich->$temp+1)
			);
		
		$$temp->Dauer = 
			$Forschung->BasisDauer
			*
			(pow($Forschung->DauerFaktor, $ich->$temp+1)*(1-(BONUS_FAKTOR_FORSCHUNGSZENTRUM*$ich->Gebaeude2)));
		
		$$temp->Punkte = 
			$Forschung->BasisPunkte
			*
			(pow($Forschung->PunkteFaktor, $ich->$temp+1));
		
		if($$temp->Dauer<FORSCHUNG_MIN_DAUER) {
			$$temp->Dauer=FORSCHUNG_MIN_DAUER;
		}
	}
?>