<?php
	/**
		* Das "Hirn" des Programms, hier stehen die Hilfetexte.
		* 
		* @version 1.0.1
		* @author Simon Frankenberger <simonfrankenberger@web.de>
		* @package blm2.includes
	*/
	
	/*
		Ich denke, weitere Kommentare sind in dieser Datei überflüssig ;)
	*/
	$HilfeText[101][]="Registrieren";
	$HilfeText[101][]="Hier können Sie einen neuen Spieler registrieren. Zugang zum Spiel haben nur registrierte Spieler.
Füllen Sie hierzu alle Felder aus und klicken Sie dann auf \"Abschicken\" um die Registrierung abzuschließen.

Wenn die Registrierung erfolgreich ist, dann werden Sie sofort auf die Anmeldeseite weitergeleitet, wo Sie sich im Spiel anmelden können.";
	
	$HilfeText[102][]="Anmelden";
	$HilfeText[102][]="Damit Sie das Spiel spielen können, müssen Sie am Server angemeldet sein. Dazu geben Sie bitte Ihre Daten, welche Sie für die Registrierung verwendet haben, ein und schicken das Formular ab.

	Wenn die Daten überprüft wurden und richtig sind, befinden Sie sich im Spiel und die Navigationsleiste links bietet nun andere Optionen.
	";
	
	$HilfeText[103][]="Startseite";
	$HilfeText[103][]="Hier sehen Sie eine kurze Beschreibung des Spiels und eine kurze Liste mit den wichtigsten News oder Änderungen des Spiels.

Wenn die Runde beendet ist, sehen Sie dort ausserdem noch die letzten Rundengewinner.
Wenn die Runde läuft, dann sehen Sie einige Informationen über die Runde, zum Beispiel wann diese gestartet ist und wann diese endet.";

	$HilfeText[104][]="Gebäude";
	$HilfeText[104][]="Über diesen Menüpunkt können Sie neue Gebäude bauen oder bereits vorhandene ausbauen. Von Anfang an sind jedoch nicht alle Gebäude freigeschaltet, da man manche erst bei fortschreitenden Spielverlauf bekommt.
	
Bei jedem Gebäude steht ein kurzer Informationstext, der den Zweck des Gebäudes erklärt. Ausserdem stehen auch immer die Ausbauzeit und Ausbaukosten dabei. Diese werden von Stufe zu Stufe erhöht, meist um einen Faktor von ~1,3 je Stufe (genaueres können Sie dem Changelog oder dem Quelltext direkt entnehmen).

Hier ist noch eine Liste mit den verfügbaren Gebäuden und deren Vorraussetungen:

[b]Beim Spielstart:[/b]
- Plantage (für den Anbau von neuen Obst- und Gemüsesorten)
- Forschungszentrum (für die Erforschung / Verbesserung von Obst und Gemüse)
- Bioladen (erhöht den Verkaufspreis der Waren)

[b]Bioladen Stufe >= 5:[/b]
- Dönerstand (erhöhtes Grundeinkommen)

[b]Plantage Stufe >= 5:[/b]
- Verkäuferschule (erhöhter Verkaufspreis der Waren)

[b]Plantage Stufe >= 8 und Forschungszentrum Stufe >= 9:[/b]
- Bauhof (gesenkte Bauzeit für Gebäude)

[b]Plantage Stufe >= 10 und Ausgaben für Mafia >= 10.000 " . $CurrencyC . ":[/b]
- Zaun (gesenkte Erfolgschance für gegnerischen Mafiaangriff)

[b]Plantage Stufe >= 12 und Ausgaben für Mafia >= 25.000 " . $CurrencyC . ":[/b]
- Pizzeria (Erhöht die Erfolgschancen der Mafia)
";
	
	$HilfeText[105][]="Plantage";
	$HilfeText[105][]="Die Plantage ist das wichtigste Gebäude im Spiel, denn hier bauen Sie Ihr erforschtes Gemüse an.

Je weiter die Plantage ausgebaut ist, desto mehr andere Gebäude werden bekannt. Ausserdem muss die Plantage eine bestimmte Stufe besitzen, um ein Gemüse forschen oder überhaupt anbauen zu können.

[b]Dabei gilt:[/b]
Kartoffeln: Stufe 1
Karotten: Stufe 3
Tomaten: Stufe 4
Salat: Stufe 6
Äpfel: Stufe 7
usw.

Sobald die Plantage durch einen Angriff unter die für ein Gemüse erforderliche Stufe fällt, ist der Anbau des Gemüses auch nicht mehr möglich bis die Anforderungen wieder erreicht sind, egal welche Stufe des Gemüses erforscht ist.

Die Produktionsmenge kann frei bestimmt werden, jedoch beträgt die maximale Produktionsdauer am Stück 12 Stunden.
";
	
	$HilfeText[106][]="Forschungszentrum";
	$HilfeText[106][]="In Ihrem Forschungszentrum können Sie neue Gemüsesorten erfoschen oder bestehende verbessern.

Die Stufe des Forschungszentrum ist ein Kriterium dafür, welche Gemüsesorten Sie forschen können. Dabei gilt das selbe wie für den Anbau der Gemüssorten auf der Plantage:

Kartoffeln: Stufe 1
Karotten: Stufe 3
Tomaten: Stufe 4
Salat: Stufe 6
Äpfel: Stufe 7
usw.

Ausserdem werden die Forschungen für jede Stufe, welche das Gebäude erreicht, schneller abgeschlossen.

Stufe 1 einer Forschung ermöglicht den Anbau des Gemüses, jede weitere Stufe erhöht die Anbaumenge um " . PRODUKTIONS_FORSCHUNGS_FAKTOR_MENGE . " kg, erhöht aber auch die Kosten für den Anbau um " . number_format(PRODUKTIONS_FORSCHUNGS_FAKTOR_KOSTEN,2,",",".") . " " . $CurrencyC . ".
";
	
	$HilfeText[107][]="Bioladen";
	$HilfeText[107][]="In diesem Gebäude können Sie Ihre produzierten Pflanzen an virtuelle Kunden verkaufen.

Es gibt keine Beschränkung, wie viel Gemüse Sie am Tag verkaufen können. Der Verkaufspreis wird aus einem Grundpreis, dem Marktkurs, der Stufe der Forschung des entsprechenden Gemüses, der Stufe des Bioladens und der Verkäuferschule berechnet. Dabei erhöht jede Stufe des Bioladens den Verkaufspreis um " . number_format(WAREN_PREIS_BIOLADEN,2,",",".") . " " . $CurrencyC . ".

Der Verkaufspreis kann nicht selbst direkt eingegeben oder verändert werden.";
	
	$HilfeText[108][]="Büro";
	$HilfeText[108][]="Das Büro ist eine Art Schaltzentrale des Spiels, wo alle wichtigen Informationen Ihres Accounts zusammenlaufen.
	
Hier sehen Sie zum Beispiel die aktuellen Marktkurse (bewegen sich zwischen 75% und 100%, werden stündlich neu berechnet), eine Übersicht über Ihre Eingaben und Ausgaben, sowie eine Aufschlüsselung über die Punkterechnung.";
	
	$HilfeText[109][]="Bank";
	$HilfeText[109][]="Diese verwaltet Ihr Vermögen, gibt Zinsen auf Anlagen und vergibt Kredite.

Sie haben von Anfang an ein Bankkonto mit " . number_format($Start["geld"], 2, ",", ".") . " " . $CurrencyC . " Startguthaben.
Die maximale Summe, welche Sie einzahlen können liegt bei 99.999,99 " . $CurrencyC . " (Bitte beachten: Bei diesem Betrag bekommen Sie auch keine Zinsen mehr!), die maximale Kreditsumme beträgt 25.000 " . $CurrencyC . ".

Die Zinsen werden alle " . (ZINSEN_DAUER/60) . " Minuten abgerechnet.

Das Geld auf der Bank kann nicht (im Gegensatz zum Bargeld) von anderen Spielern geklaut werden.

[color=red]Wichtig: Falls Ihr Kontostand unter " . number_format(DISPO_LIMIT, 0, ",", ".") . " " . $CurrencyC . " fällt, wird Ihr Account automatisch resettet![/color]";
	
	$HilfeText[1010][]="Verträge";
	$HilfeText[1010][]="Hier können Sie Waren direkt an einen anderen Mitspieler schicken. Die Preise können frei gewählt werden.

Auf dieser Seite haben Sie auch eine Übersicht darüber, welche Verträge noch ausstehen.
Die Vertäge sind einmalig und werden nicht über Zeiträume abgerechnet.

Anders als beim Marktplatz gibt es hier keine extra Gebühren, das heißt, der Verkaufspreis geht zu 100% beim Verkäufer ein.

Die Waren werden direkt beim Versandt des Vertrags reserviert und aus dem Lager entfernt.
Verträge können wieder zurückgezogen werden, solange der Gegenüber diesen noch nicht angenommen hat.";
	
	$HilfeText[1011][]="Marktplatz";
	$HilfeText[1011][]="Hier können alle Spieler Ihre Waren zu frei wählbaren Preisen zum Verkauf anbieten oder andere Angebote kaufen.

Es können keine Teilmengen gekauft werden, es muss also das gesammte Angebot gekauft werden. Deshalb ist zu empfehlen, nicht 1x 1000kg zu verkaufen, sondern lieber 4x 250kg einzustellen.

Der Markt verlangt " . (100-MARKT_PROVISION_FAKTOR*100) . "% des Gesamtpreises als Provision. Diese wird beim Kauf direkt vom Erlös abgezogen.

[b]Wichtig![/b]
Man kann Angebote wieder vom Markt zurückziehen, jedoch gehen dabei " . (100-MARKT_ZURUECKZIEH_FAKTOR*100) . "% der Ware verloren!.";
	
	$HilfeText[1012][]="Mafia";
	$HilfeText[1012][]="Die Mafia ermöglicht Angriffe auf andere Spieler, um diese zu bestehlen oder deren Plantagen anzugreifen.

Bei der Mafia gibt es 4 verschiedene Arten von Attacken:
- Spionage
- Diebstahl
- Angriff
- Bomben

Die Erfolgschancen hängen von den gewünschten Kosten für die Aktion ab, und können per DropDown-Menü ausgewählt werden.

Bei der Spionage wird der Lagerstand und das Bargeld des Angegriffenen ausspioniert und per IGM an den Angreifer geschickt. Da dies ein relativ blilliger Vorgang ist, welcher maximal 50 % Erfolgsaussicht hat, ist dies ein perfektes Mittel um weitere Angriffe auf den Gegner zu planen.

Beim Diebstahl wird versucht, das Lager des Gegners leer zu räumen. Gelingt dieser Vorgang, so werden die Waren des Gegners dem Angreifer gutgeschrieben.

Der Angriff zielt auf das Barvermögen des Gegners und stielt diesem per Zufall zwischen " . MAFIA_DIEBSTAHL_MIN_RATE . "% und " . MAFIA_DIEBSTAHL_MAX_RATE . "% seines Barvermögens und schreibt es dem Angreifer gut.

Das Bomben ist die teuerste, aber auch die fieseste Waffe gegen Ihre Konkurenten. Dadurch ist bei dieser Art von Angriff die Obergrenze für den Erfolg bei 40%. Gelingt der Angriff, wird die Stufe der Plantage des Gegners um ein verringert. Befindet sich diese schon auf dem niedrigsten Level, so passiert nichts.

Bei allen Angriffsarten, egal ob erfolgreich oder nicht, wir dem angegriffenen eine IGM zugestellt, in der der Auftragsgeber steht.

[b]Wichtig:[/b]
Man kann nur Spieler angreifen, die
a) Mindestens 7.000 Punkte haben
b) Mindestens so viele Punkte haben, wie Ihre Punktzahl durch " . MAFIA_FAKTOR_MIN_PUNKTE . "
c) Maximal so viele Punkte haben, wie Ihre Punktzahl mal " . MAFIA_FAKTOR_MAX_PUNKTE . "

[b]Hinweis:[/b]
Im Krieg zählen die oben genannten Angriffsbeschränkungen nicht!";
	
	$HilfeText[1013][]="Nachrichten (IGM's)";
	$HilfeText[1013][]="Hier können Sie empfangene Nachrichten lesen oder löschen, und neue Nachrichten verfassen.

In diesem Modul findet die komplette Kommunikation zwischen den Spielern und dem System statt.
[b]Bei den Nachrichten können auch BB-Code und Smileys verwendet werden.[/b]";
	
	$HilfeText[1014][]="Notizblock";
	$HilfeText[1014][]="Hier können Sie eigene Notizen festhalten und bearbeiten.

Die maximale Länge des Notizblock sind 2048 Zeichen.";
	
	$HilfeText[1015][]="Einstellungen";
	$HilfeText[1015][]="Hier können Sie die Einstellungen zu Ihrem Konto ändern.

Sie können hier:
- das Passwort ändern
- das Konto resetten
- das Konto löschen
- Ihre Profilbeschreibung ändern
- Ein Profilbild hochladen / löschen
- Das Sitting ein-/ ausschalten und die Rechte festlegen

Um ein bereits hochgeladenes Bild zu löschen, klicken Sie einfach auf den Button \"Absenden\" ohne eine Datei auszuwählen.

Die maximale Größe des Profilbildes beträgt " . (BILD_GROESE_MAXIMAL/1024) . " KB.

[b]Was ist ein Sitter?[/b]

Ein Sitter hat einen seperaten Zugang zu Ihrem Konto und darf, je nach Ihren Einstellungen, bestimmte Sachen regeln. Der Sitter loggt sich mit Ihrem Namen und seinem speziellen Sitterkennwort ein.

[b]Was darf ein Sitter auf keinen Fall?[/b]
- Einstellungen des Accounts ändern
- Laufende Aufträge abbrechen
- Admin-/ Betatesterfunktionen nutzen

[b]Wozu einen Sitter?[/b]
Multiaccounts sind laut den Regeln verboten. Dazu zählen auch wiederholte Anmeldungen bei fremden Accounts. Deshalb habe ich die Sitteraccounts eingeührt. So kann man die Kontrolle über den Account völlig legal und vorallem ohne Risiko und ohne das Hauptpasswort mitteilen zu müssen, jemand anderen einen bestimmten Teil des Accounts managen lassen. Dies kann zum Beispiel während des Urlaubs sehr von Vorteil sein.

[b]Wichtig![/b]
Sobald Sie das Konto resettet oder gelöscht haben, kann man es nicht mehr wiederherstellen!";
	
	$HilfeText[1016][]="Chefbox";
	$HilfeText[1016][]="Die wichtigsten Informationen zu den aktuellen Vorgängen und Aufträgen auf einen Blick.

Diese Box wird in einem neuen Fenster (\"Popup\") geöffnet und zeigt auf einen Blick alle aktive Aufträge mit der verbleibenden Dauer, die Anzahl der zur Zeit angemeldeten Spieler, ob neue Nachrichten, Verträge, Marktplatzangebote vorliegen und wann die das nächste Grundeinkommen oder die Zinsen verbucht werden.";
	
	$HilfeText[1017][]="Rangliste";
	$HilfeText[1017][]="Zeigt die besten Spieler nach Punkten sortiert an. Als zweite Sortierung gibt es noch eine Liste der \"Bioladenfreaks\", welches die Spieler nach der bisherigen Spielzeit sortiert ausgibt.

[b]Wichtig:[/b]
Die Aktualisierung der Loginzeit findet nur statt, wenn der Benutzer sich über den Menüpunkt \"Logout\" ordnungsgemäß abmeldet! Wird der Browser einfach geschlossen, oder die Seite anderweitig verlassen, ohne sich abzumelden, wird die Zeit nicht aktualisiert!";
	
	$HilfeText[1018][]="Serverstatistik";
	$HilfeText[1018][]="Zeigt verschiedene spielerübergreifende Statistikenan, zum Beispiel:

- Anzahl der bisher erteilten Aufträge,
- Gesamteinnahmen,
- Gesamtausgaben,
- Gewinn / Auftrag,
- Anzahl aller Forschungslevel,
- Anzahl aller bisher verschickten IGMs

Und noch viele mehr.";
	
	$HilfeText[1019][]="Regeln";
	$HilfeText[1019][]="Wie bei jedem Spiel gibt es auch bei diesem hier Regeln.

Hier wird der Umgang mit anderen Spielern und eventuelle Problemfaktoren geregelt. Alle Spieler müssen sich an diese Regeln halten.

Bei nicht-Einhaltung der Regeln werden nach Ermessen der Admins Strafen verteilt.";
	
	$HilfeText[1020][]="Changelog";
	$HilfeText[1020][]="Zeigt den Entwicklungsstand des Spiels und informiert über die Änderungen.

Das Changelog ist sortiert nach Datum und Kategorie. Jede größere Änderung am Spiel wird hier festgehalten und in die jeweilige Kategorie (Optimierung, Bugfix, Feature, Balancing...) eingeteilt. Somit kann jeder Spieler sehen, was und wie etwas geändert wurde.";
	
	$HilfeText[1021][]="Impressum";
	$HilfeText[1021][]="Zeigt Informationen zu der Lizenz des Spiels und über den Verantwortlichen an. Ausserdem kann hier auch immer die aktuelle Version als Quelltext heruntergeladen werden.";
	
	$HilfeText[1022][]="Abmelden";
	$HilfeText[1022][]="Sobald Sie das Spiel beenden wollen, klicken Sie bitte auf diesen Link.
Somit wird die Sitzung beendet und für ungültig erklärt, so dass kein Fremder in Ihren Account rein kommt. Ausserdem werden Sie dann in der Rangliste sofort als Offline gekennzeichnet.";
	
	$HilfeText[1023][]="Gruppen";
	$HilfeText[1023][]="Hier findet die komplette Gruppenverwaltung des Spiels statt.

Falls Sie noch keine Gruppe haben, dann können Sie hier:
a) Eine neue Gruppe erstellen (Plantage mind. Stufe 8 )
b) Einer bestehenden Gruppe beitreten (Plantage mind. Stufe 5 )

Falls Sie bereits eine Gruppe haben, sehen Sie hier das Gruppenportal mit dem Gruppenbild und der Beschreibung.
Wenn Ihnen der Gründer besondere Rechte zugewiesen hat, sehen Sie oben vielleicht noch ein paar extra Menüpunkte, je nach Ihren Rechten in der Gruppe.

Eine Gruppe kann maximal aus " . MAX_ANZAHL_GRUPPENMITGLIEDER . " Mitgliedern bestehen. Ist dieses Limit erreicht, können keine neuen Mitglieder der Gruppe beitreten.

Die Gruppenkasse ist ein vielfältiges Feature. Sie können hier zum Beispiel Ihr Geld lagern, falls Ihr Kontostand schon beim Maximum ist, oder sie können das Geld auch einzahlen und der Verwalter verteilt das Geld dann an die schwächeren Spieler der Gruppe als Aufbauhilfe. Geplant (noch nicht sicher wann) sind auch Gruppengebäude, welche jedem Mitglied der Gruppe einen Bonus geben.

Die diplomatischen Beziehungen brauchen immer das beidseitige Einverständnis. Ein Vertrag ist erst gültig, wenn beide Seiten angenommen haben. Jeder Vertrag dauert eine Woche und läuft danach automatisch aus.

[b]Jede diplomatische Beziehungsart hat eigene, spielbeeinflussende Werte:[/b]

- NAP: Nicht-Angriffs-Pakt: Die Spieler können einander nicht mehr angreifen, Waffenstillstand
- BND: Bisher das selbe wie NAP, jedoch vom Stellenwert her höher anzusehen
- Krieg: Es gelten keine Angriffsbeschränkungen mehr, jeder Spieler darf jeden anderen der gegnerischen Gruppe angreifen. Ausserdem gelten für jede Art des Angriffs nur noch die halben Sperrzeiten. Ein Krieg wird um einen vorab ausgehandelten Betrag geführt, und läuft solange, bis eine Seite kapituliert. Die Verlierer verlieren 5% ihrer Punkte und ihre Plantagen werden um 1 Stufe gesenkt. Der Gewinner erhält den umkämpften Betrag. Wird ein Krieg beendet, so haben die beiden Seiten anschließend einen NAP, welcher sofort und mindestens 1 Woche lang gültig ist.


[b]Wichtig:[/b]
Sobald alle Mitglieder eine Gruppe verlassen haben, wird diese umgehend gelöscht!";
?>