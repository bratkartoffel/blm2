<?php
/*
 * MIT Licence
 * Copyright (c) 2023 Simon Frankenberger
 *
 * Please see LICENCE.md for complete licence text.
 */

define('hilfe_texte', array(
    101 => array(
        'Registrieren',
        'Hier können Sie einen neuen Spieler registrieren. Zugang zum Spiel haben nur registrierte Spieler.
Füllen Sie hierzu alle Felder aus und klicken Sie dann auf "Abschicken" um die Registrierung abzuschließen.
Wenn die Registrierung erfolgreich ist, dann werden Sie sofort auf die Anmeldeseite weitergeleitet, wo Sie sich im Spiel anmelden können.',
    ),
    102 => array(
        'Anmelden',
        'Damit Sie das Spiel spielen können, müssen Sie am Server angemeldet sein.
Dazu geben Sie bitte Ihre Daten, welche Sie für die Registrierung verwendet haben, ein und schicken das Formular ab.
Wenn die Daten überprüft wurden und richtig sind, befinden Sie sich im Spiel und die Navigationsleiste links bietet nun andere Optionen.'
    ),
    103 => array(
        'Startseite',
        'Hier sehen Sie eine kurze Beschreibung des Spiels und eine kurze Liste mit den wichtigsten News oder Änderungen des Spiels.
Wenn die Runde beendet ist, sehen Sie dort ausserdem noch die letzten Gewinner.
Wenn die Runde läuft, dann sehen Sie einige Informationen über die Runde, zum Beispiel wann diese gestartet ist und wann diese endet.'
    ),
    104 => array(
        'Gebäude',
        'Über diesen Menüpunkt können Sie neue Gebäude bauen oder bereits vorhandene ausbauen. Von Anfang an sind jedoch nicht alle Gebäude freigeschaltet,
da man manche erst bei fortschreitenden Spielverlauf bekommt.
	
Bei jedem Gebäude steht ein kurzer Informationstext, der den Zweck des Gebäudes erklärt. Ausserdem stehen auch immer die Ausbauzeit und Ausbaukosten dabei.
Diese werden von Stufe zu Stufe erhöht, meist um einen Faktor von ~1,3 je Stufe.

Hier ist noch eine Liste mit den verfügbaren Gebäuden und deren Voraussetzungen:

[b]Beim Spielstart:[/b]
- Plantage (für den Anbau von neuen Obst- und Gemüsesorten)
- Forschungszentrum (für die Erforschung / Verbesserung von Obst und Gemüse)
- Bioladen (erhöht den Verkaufspreis der Waren)

[b]Bioladen Stufe >= 5:[/b]
- Dönerstand (erhöhtes Grundeinkommen)
- Verkäuferschule (erhöhter Verkaufspreis der Waren)

[b]Plantage Stufe >= 8 und Forschungszentrum Stufe >= 9:[/b]
- Bauhof (gesenkte Bauzeit für Gebäude)

[b]Plantage Stufe >= 9 und Ausgaben für Mafia >= 10.000 €:[/b]
- Zaun (gesenkte Erfolgschance für gegnerischen Mafiaangriff)

[b]Plantage Stufe >= 11 und Ausgaben für Mafia >= 25.000 €:[/b]
- Pizzeria (Erhöht die Erfolgschancen der Mafia)'
    ),
    105 => array(
        'Plantage',
        'Die Plantage ist das wichtigste Gebäude im Spiel, denn hier bauen Sie Ihr erforschtes Gemüse an.

Je weiter die Plantage ausgebaut ist, desto mehr andere Gebäude werden bekannt.
Ausserdem muss die Plantage eine bestimmte Stufe besitzen, um ein Gemüse forschen oder überhaupt anbauen zu können.

[b]Dabei gilt:[/b]
Kartoffeln: Stufe 1
Karotten: Stufe 3
Tomaten: Stufe 4
Salat: Stufe 6
Äpfel: Stufe 7
usw.

Sobald die Plantage durch einen Angriff unter die für ein Gemüse erforderliche Stufe fällt,
ist der Anbau des Gemüses auch nicht mehr möglich bis die Anforderungen wieder erreicht sind,
egal welche Stufe des Gemüses erforscht ist.

Die Produktionsmenge kann frei bestimmt werden, jedoch beträgt die maximale Produktionsdauer am Stück 12 Stunden.
Die in Auftrag gegebene Menge wird erst am Ende der eingestellten Produktionszeit vollständig ins Lager übertragen.
Zudem gibt es eine durchgehende Basisproduktion. Alle ' . Config::getInt(Config::SECTION_BASE, 'cron_interval') . ' Minuten werden ' . Config::getInt(Config::SECTION_PLANTAGE, 'production_cron_base') . ' kg / Forschungslevel eines jeden Gemüses generiert.'
    ),
    106 => array(
        'Forschungszentrum',
        'In Ihrem Forschungszentrum können Sie neue Gemüsesorten erforschen oder bestehende verbessern.

Die Stufe des Forschungszentrum ist ein Kriterium dafür, welche Gemüsesorten Sie forschen können.
Dabei gilt das selbe wie für den Anbau der Gemüsesorten auf der Plantage:

Kartoffeln: Stufe 1
Karotten: Stufe 3
Tomaten: Stufe 4
Salat: Stufe 6
Äpfel: Stufe 7
usw.

Ausserdem werden die Forschungen für jede Stufe, welche das Gebäude erreicht, schneller abgeschlossen.
Stufe 1 einer Forschung ermöglicht den Anbau des Gemüses, jede weitere Stufe erhöht die Anbaumenge
um ' . formatWeight(Config::getInt(Config::SECTION_RESEARCH_LAB, 'production_amount_per_level')) . ', erhöht aber zugleich auch die Kosten für den Anbau
um ' . formatCurrency(Config::getInt(Config::SECTION_RESEARCH_LAB, 'production_cost_per_level')) . '.'
    ),
    107 => array(
        'Bioladen',
        'In diesem Gebäude können Sie Ihre produzierten Pflanzen an virtuelle Kunden verkaufen.

Es gibt keine Beschränkung, wie viel Gemüse Sie am Tag verkaufen können. Der Verkaufspreis wird aus einem Grundpreis,
dem Marktkurs, der Stufe der Forschung des entsprechenden Gemüses, der Stufe des Bioladens und der Verkäuferschule
berechnet. Dabei erhöht jede Stufe des Bioladens den Verkaufspreis um ' . formatCurrency(Config::getInt(Config::SECTION_SHOP, 'item_price_shop_bonus')) . '.

Der Verkaufspreis kann nicht selbst direkt eingegeben oder verändert werden.'
    ),
    108 => array(
        'Büro',
        'Das Büro ist eine Art Schaltzentrale des Spiels, wo alle wichtigen Informationen Ihres Accounts zusammenlaufen.
	
Hier sehen Sie zum Beispiel die aktuellen Marktkurse (bewegen sich zwischen 75% und 100%, werden stündlich neu berechnet),
eine Übersicht über Ihre Eingaben und Ausgaben, sowie eine Aufschlüsselung über die Punkterechnung.'
    ),
    109 => array(
        'Bank',
        'Diese verwaltet Ihr Vermögen, gibt Zinsen auf Anlagen und vergibt Kredite.

Sie haben von Anfang an ein Bankkonto mit ' . formatCurrency(Config::getSection(Config::SECTION_STARTING_VALUES)['Geld']) . ' Startguthaben.
Die maximale Summe, welche Sie einzahlen können liegt bei ' . formatCurrency(Config::getInt(Config::SECTION_BANK, 'deposit_limit')) . '
(Bitte beachten: Bei diesem Betrag bekommen Sie auch keine Zinsen mehr!), die maximale Kreditsumme beträgt ' . formatCurrency(Config::getInt(Config::SECTION_BANK, 'credit_limit')) . '.

Die Zinsen werden alle ' . Config::getInt(Config::SECTION_BASE, 'cron_interval') . ' Minuten abgerechnet.
Das Geld auf der Bank kann nicht (im Gegensatz zum Bargeld) von anderen Spielern geklaut werden.

[color=red]Wichtig: Falls Ihr Kontostand unter ' . formatCurrency(Config::getInt(Config::SECTION_BANK, 'dispo_limit')) . ' fällt, wird Ihr Account automatisch resettet![/color]'
    ),
    110 => array(
        'Verträge',
        'Hier können Sie Waren direkt an einen anderen Mitspieler schicken. Die Preise können frei gewählt werden.

Auf dieser Seite haben Sie auch eine Übersicht darüber, welche Verträge noch ausstehen.
Die Verträge sind einmalig und werden nicht über Zeiträume abgerechnet.

Anders als beim Marktplatz gibt es hier keine extra Gebühren, das heißt, der Verkaufspreis geht zu 100% beim Verkäufer ein.

Die Waren werden direkt beim Versand des Vertrags reserviert und aus dem Lager entfernt.
Verträge können wieder zurückgezogen werden, solange der Gegenüber diesen noch nicht angenommen hat.'
    ),
    111 => array(
        'Marktplatz',
        'Hier können alle Spieler Ihre Waren zu frei wählbaren Preisen zum Verkauf anbieten oder andere Angebote kaufen.

Es können keine Teilmengen gekauft werden, es muss also das gesamte Angebot gekauft werden.
Deshalb ist zu empfehlen, nicht 1x 10.000kg zu verkaufen, sondern besser 4x 2500kg einzustellen.

Der Markt verlangt ' . formatPercent(Config::getInt(Config::SECTION_MARKET, 'provision_rate')) . ' des Gesamtpreises als Provision. Diese wird beim Kauf direkt vom Erlös abgezogen.

[b]Wichtig![/b]
Man kann Angebote wieder vom Markt zurückziehen, jedoch gehen dabei ' . formatPercent(1 - Config::getFloat(Config::SECTION_MARKET, 'retract_rate')) . ' der Ware verloren!'
    ),
    112 => array(
        'Mafia',
        'Die Mafia ermöglicht Angriffe auf andere Spieler, um diese zu bestehlen oder deren Plantagen anzugreifen.

Bei der Mafia gibt es 4 verschiedene Arten von Attacken:
- Spionage
- Diebstahl
- Angriff
- Bomben

Die Erfolgschancen hängen von den gewünschten Kosten für die Aktion ab, und können per DropDown-Menü ausgewählt werden.

Bei der Spionage wird der Lagerstand und das Bargeld des Angegriffenen ausspioniert und per IGM an den Angreifer geschickt.
Da dies ein relativ billiger Vorgang ist, welcher maximal 50 % Erfolgsaussicht hat, ist dies ein perfektes Mittel um weitere Angriffe auf den Gegner zu planen.

Der Raub zielt auf das Barvermögen des Gegners und stiehlt diesem per Zufall zwischen ' . formatPercent(Config::getFloat(Config::SECTION_MAFIA, 'raub_min_rate')) . '
und ' . formatPercent(Config::getFloat(Config::SECTION_MAFIA, 'raub_max_rate')) . ' seines Barvermögens und schreibt es dem Angreifer gut.

Beim Diebstahl wird versucht, das Lager des Gegners leer zu räumen. Gelingt dieser Vorgang, so werden die Waren des Gegners dem Angreifer gutgeschrieben.

Der Anschlag ist die teuerste, aber auch die fieseste Waffe gegen Ihre Konkurrenten. Dadurch ist bei dieser Art von Angriff die Obergrenze
für den Erfolg bei ' . formatPercent(getMafiaChance(Config::SECTION_MAFIA_ATTACK, 3)) . '.
Gelingt der Angriff, wird die Plantage des Gegners um eine Stufe verringert. Befindet sich diese schon auf dem niedrigsten Level, so passiert nichts.

Bei allen Angriffsarten wird nur bei einem Fehlschlag dem Opfer eine Nachricht mit dem Namen des Angreifers zugestellt.
Einzige Ausnahme ist der Anschlag, hier erfährt der Angegriffene immer, wer ihn angegriffen hat.

[b]Wichtig:[/b]
Man kann nur Spieler angreifen,
a) die mindestens ' . formatPoints(Config::getFloat(Config::SECTION_MAFIA, 'min_points')) . ' Punkte haben
b) deren Punkte maximal um ' . formatPercent(Config::getFloat(Config::SECTION_MAFIA, 'points_factor') - 1) . ' auseinander liegen.

[b]Hinweis:[/b]
Im Krieg zählen die oben genannten Angriffsbeschränkungen nicht!'
    ),
    113 => array(
        'Nachrichten (IGM)',
        'Hier können Sie empfangene Nachrichten lesen oder löschen, und neue Nachrichten verfassen .

In diesem Modul findet die komplette Kommunikation zwischen den Spielern und dem System statt .
[b]Bei den Nachrichten können auch BB - Code und Smileys verwendet werden . [/b]'
    ),
    114 => array(
        'Notizblock',
        'Hier können Sie eigene Notizen festhalten und bearbeiten. Die maximale Länge des Notizblock sind 4096 Zeichen.'
    ),
    115 => array(
        'Einstellungen',
        'Hier können Sie die Einstellungen zu Ihrem Konto ändern.

Sie können:
- das Passwort ändern
- das Konto resetten
- das Konto löschen
- Ihre Profilbeschreibung ändern
- Ein Profilbild hochladen / löschen
- Das Sitting ein- / ausschalten und die Rechte festlegen

Um ein bereits hochgeladenes Bild zu löschen, klicken Sie einfach auf den Button "Absenden" ohne eine Datei auszuwählen.
Die maximale Größe des Profilbildes beträgt ' . (Config::getInt(Config::SECTION_BASE, 'max_profile_image_size') / 1024) . ' KiB

[b]Was ist ein Sitter?[/b]

Ein Sitter hat einen separaten Zugang zu Ihrem Konto und darf, je nach Ihren Einstellungen, bestimmte Sachen regeln.
Der Sitter loggt sich mit Ihrem Namen und seinem speziellen Passwort ein.

[b]Was darf ein Sitter auf keinen Fall?[/b]
- Einstellungen des Accounts ändern
- Laufende Aufträge abbrechen
- Admin- / Betatesterfunktionen nutzen

[b]Wozu einen Sitter?[/b]
Multiaccounts sind laut den Regeln verboten. Dazu zählen auch wiederholte Anmeldungen bei fremden Accounts.
Deshalb habe ich die Sitteraccounts eingeführt. So kann man die Kontrolle über den Account völlig legal und
vor Allem ohne Risiko und ohne das Hauptpasswort mitteilen zu müssen, jemand anderen einen bestimmten Teil des
Accounts managen lassen. Dies kann zum Beispiel während eines längeren Urlaubs sehr von Vorteil sein.

[b]Wichtig![/b]
Sobald Sie das Konto resettet oder gelöscht haben, kann man es nicht mehr wiederherstellen!'
    ),
    116 => array(
        'Chefbox',
        'Die wichtigsten Informationen zu den aktuellen Vorgängen und Aufträgen auf einen Blick.

Diese Box wird in einem neuen Fenster geöffnet und zeigt auf einen Blick alle aktive Aufträge mit der verbleibenden Dauer,
die Anzahl der zur Zeit angemeldeten Spieler, ob neue Nachrichten, Verträge, Marktplatzangebote vorliegen und wann
das nächste Grundeinkommen oder die Zinsen verbucht werden.'
    ),
    117 => array(
        'Rangliste',
        'Zeigt die besten Spieler nach Punkten sortiert an.
Zusätzlich gibt es noch spezielle Sondertitel, beispielsweise den "Bioladenfreak" mit der längsten Onlinezeit.'
    ),
    118 => array(
        'Serverstatistik',
        'Zeigt verschiedene spieler übergreifende Statistiken an, zum Beispiel:

- Anzahl der bisher erteilten Aufträge,
- Gesamteinnahmen,
- Gesamtausgaben,
- Gewinn / Auftrag,
- Anzahl aller Forschungslevel,
- Anzahl aller bisher verschickten IGMs

Und noch viele mehr.'
    ),
    119 => array(
        'Regeln',
        'Wie bei jedem Spiel gibt es auch bei diesem hier Regeln.
Hier wird der Umgang mit anderen Spielern und eventuelle Problemfaktoren geregelt. Alle Spieler müssen sich an diese Regeln halten.
Bei nicht-Einhaltung der Regeln werden nach Ermessen der Admins Strafen verteilt.'
    ),
    121 => array(
        'Impressum',
        'Zeigt Informationen zu der Lizenz des Spiels und über den Verantwortlichen an.
Ausserdem kann hier auch immer die aktuelle Version als Quelltext heruntergeladen werden.'
    ),
    123 => array(
        'Gruppen',
        'Hier findet die komplette Gruppenverwaltung des Spiels statt.

Falls Sie noch keine Gruppe haben, dann können Sie hier:
a) Eine neue Gruppe erstellen (Plantage mind. Stufe 8)
b) Einer bestehenden Gruppe beitreten (Plantage mind. Stufe 5)

Falls Sie bereits eine Gruppe haben, sehen Sie hier das Gruppenportal mit dem Gruppenbild und der Beschreibung.
Wenn Ihnen der Gründer besondere Rechte zugewiesen hat, sehen Sie oben vielleicht noch ein paar extra Menüpunkte, je nach Ihren Rechten in der Gruppe.

Eine Gruppe kann maximal aus ' . Config::getInt(Config::SECTION_GROUP, 'max_members') . ' Mitgliedern bestehen. Ist dieses Limit erreicht, können keine neuen Mitglieder der Gruppe beitreten.

Die Gruppenkasse ist ein vielfältiges Feature. Sie können hier zum Beispiel Ihr Geld lagern, falls Ihr Kontostand schon beim Maximum ist
oder sie können das Geld auch einzahlen und der Verwalter verteilt das Geld dann an die schwächeren Spieler der Gruppe als Aufbauhilfe.
Geplant (noch nicht sicher wann) sind auch Gruppengebäude, welche jedem Mitglied der Gruppe einen Bonus geben.

Die diplomatischen Beziehungen brauchen immer das beidseitige Einverständnis. Ein Vertrag ist erst gültig, wenn beide Seiten angenommen haben.
Jeder Vertrag dauert mindestens eine Woche und kann erst danach aufgekündigt werden.

[b]Jede diplomatische Beziehungsart hat eigene, Spiel beeinflussende Werte:[/b]

- NAP: Nicht-Angriffs-Pakt: Die Spieler können einander nicht mehr angreifen, Waffenstillstand
- BND: Bisher das selbe wie NAP, jedoch vom Stellenwert her höher anzusehen
- Krieg: Es gelten keine Angriffsbeschränkungen mehr, jeder Spieler darf jeden anderen der gegnerischen Gruppe angreifen.

Ein Krieg wird um einen vorab ausgehandelten Betrag geführt und läuft solange, bis eine Seite kapituliert.
Die Verlierer verlieren 5% ihrer Punkte und ihre Plantagen werden je um 1 Stufe gesenkt.
Der Gewinner erhält den umkämpften Betrag. Wird ein Krieg beendet, so haben die beiden Seiten anschließend einen NAP, welcher sofort und mindestens 1 Woche lang gültig ist.

[b]Wichtig:[/b]
Sobald alle Mitglieder eine Gruppe verlassen haben, wird diese automatisch gelöscht!'
    ),
));
