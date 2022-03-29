function ChefboxZeigen(seite) {
    // Öffnet ein Popupfenster
    chefboxPopup = window.open(seite, 'chefbox', 'height=690,width=240,scrollbars=yes,resizable=yes');
    chefboxPopup.focus();		// Setzt das Popupfenster in den Vordergrund
    return false;
}

function SmileyPopupZeigen(seite) {
    // Öffnet ein Popupfenster
    smileyPopup = window.open(seite, 'smiley', 'height=350,width=250,scrollbars=yes,resizable=yes');
    smileyPopup.focus();		// Setzt das Popupfenster in den Vordergrund
    return false;
}

function BBCodePopupZeigen(seite) {
    // Öffnet ein Popupfenster
    BBCodePopup = window.open(seite, 'BBCode', 'height=320,width=250,scrollbars=yes,resizable=yes');
    BBCodePopup.focus();		// Setzt das Popupfenster in den Vordergrund
    return false;
}

function ZeichenUebrig(Feld, Text) {
    // Schreibt die übrige Zeichenazahl in ein SPAN-Feld (z.B.: bei den Nachrichten, Beschreibung im PRofil)
    var z_n = Feld;		// Zeiger auf das Nachrichtenfeld
    var z_t = Text;		// Zeiger auf das Feld für die Anzeige

    if (parseInt(4096 - z_n.value.length) < 0) {		// Wenn der Text länger als die maximale Anzahl an Zeichen ist,
        z_t.innerHTML = "0";												// Dann schreibe als verbleibende Anzahl "0" rein
        z_n.value = z_n.value.substr(0, 4096);				// und kürze den Text

        z_n.selectionStart = z_n.value.length;		// Und dann geh noch ...
        z_n.selectionEnd = z_n.value.length;			// ans Ende des Textfeldes

        return false;		// Danach abbrechen, wir sind hier fertig.
    }

    z_t.innerHTML = 4096 - z_n.value.length;				// Wenn er hier ankommt, dann kann er die verbleibende Zeichenanzahl in das Feld schreiben
    return true;		// Womit wir fertig wären
}

function MeldungAusblenden(id) {
    // Funktion zum ausblenden einer Meldung mit dem roten Kreuz in der rechten oberen Ecke
    var z = document.getElementById(id).style;		// setzt einen Zeiger auf den Style des gesuchten Elements

    z.display = "none";				//
    z.visiblity = "hidden";		// Blendet die Box aus

    return true;
}

function Navigation(Button) {
    if (Button.getElementsByTagName('a')[0].target == "_blank") {
        window.open(Button.getElementsByTagName('a')[0].href);
    } else {
        document.location.href = Button.getElementsByTagName('a')[0].href;
    }

    return false;
}

function RechneProduktionsKosten(BasisMenge, BasisPreis, Menge, Geld, TextFeld) {
    // Rechnet die Produktionskosten aus und schreibt diese in ein Feld
    // Deaktiviert den Submit-Button, falls die Kosten gößer als das Geld des Benutzers sind

    // Rechnet die Kosten für den Auftrag aus
    var kosten = Menge * (BasisMenge / BasisPreis);

    // Schreibt die Produktionskosten für die angegebene Menge in das Feld
    TextFeld.innerHTML = "Kosten: " + kosten.toFixed(2) + " €";

    // Kann sich der Benutzer die Produktion leisten?
    if (kosten <= Geld) {
        Button.enabled = "";					// Wenn nicht, dann Button deaktivieren
        Button.disabled = "disabled";
    } else {
        Button.disabled = "";						// dann Button aktivieren
        Button.enabled = "enabled";
    }

    return;
}

function CheckKrieg(e) {
    if (e.selectedIndex == 2) {
        document.getElementById('krieg').style.display = 'block';
    } else {
        document.getElementById('krieg').style.display = 'none';
    }
}

function AllesAuswaehlen(formular, status) {
    var z = formular.getElementsByTagName('input');
    for (var i = 0; i < z.length; i++) {
        z[i].checked = status;
    }

    return false;
}
