<?php
/**
 * AJAX-Javascript Funktionen.
 *
 * @version 1.0.1
 * @author Simon Frankenberger <simonfrankenberger@web.de>
 * @package blm2.includes
 */

/*
Changelog:

[1.0.1]
    - Serverpfad durch Konstante ersetzt
    - Admin-Mailadresse durch Konstante ersetzt

*/
header('Content-Type: application/javascript');
?>
function CreateNewAJAXreq() {
    try {
        req = new XMLHttpRequest();
    } catch (e) {
        try {
            req = new ActiveXObject("Msxml2.XMLHTTP");
        } catch (e) {
            try {
                req = new ActiveXObject("Microsoft.XMLHTTP");
            } catch (failed) {
                req = null;
            }
        }
    }

    return req;
}

var req = CreateNewAJAXreq();
var req2 = CreateNewAJAXreq();
var req3 = CreateNewAJAXreq();

function delGruppeNachricht(id, container) {
    if (req == null) {
        return;
    }

    req.open("GET", '<?=AJAX_SERVER_PFAD; ?>/actions/gruppe.php?a=5&id=' + id + '&ajax=1', true);
    req.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    req.send(null);

    req.onreadystatechange = function () {
        switch (req.readyState) {
            case 4:
                if (req.responseText == "1") {
                    container.style.display = 'none';

                } else {
                    alert("Das darfst du nicht :)");
                }
                break;
            default:
                return false;
                break;
        }
    };
}

function updGruppeRechte(id, recht, bild) {
    if (req == null) {
        return;
    }

    req.open("GET", '<?=AJAX_SERVER_PFAD; ?>/actions/gruppe.php?a=6&id=' + id + '&recht=' + recht + '&ajax=1', true);
    req.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    req.send(null);

    req.onreadystatechange = function () {
        switch (req.readyState) {
            case 4:
                if (req.responseText == "1") {
                    if (bild.src == '<?=AJAX_SERVER_PFAD; ?>/pics/small/error.png') {
                        bild.src = '<?=AJAX_SERVER_PFAD; ?>/pics/small/ok.png';
                    } else {
                        bild.src = '<?=AJAX_SERVER_PFAD; ?>/pics/small/error.png';
                    }
                } else {
                    alert("Das darfst du nicht :)");
                }
                break;
            default:
                return false;
                break;
        }
    };
}

function delNachricht(id, zeile) {
    if (req == null) {
        return;
    }

    req.open("GET", '<?=AJAX_SERVER_PFAD; ?>/actions/nachrichten.php?a=2&id=' + id + '&ajax=1', true);
    req.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    req.send(null);

    req.onreadystatechange = function () {
        switch (req.readyState) {
            case 4:
                if (req.responseText == "1") {
                    zeile.style.display = 'none';
                } else {
                    alert("Das darfst du nicht :)");
                }
                break;
            default:
                return false;
                break;
        }
    };
}

function VertragAnnehmen(id, zeile) {
    if (req == null) {
        return;
    }

    req.open("GET", '<?=AJAX_SERVER_PFAD; ?>/actions/vertraege.php?a=2&vid=' + id + '&ajax=1', true);
    req.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    req.send(null);

    req.onreadystatechange = function () {
        switch (req.readyState) {
            case 4:
                if (req.responseText == "1") {
                    zeile.style.display = 'none';
                } else {
                    document.location.href = "./?p=vertraege_liste&m=" + req.responseText;
                }
                break;
            default:
                return false;
                break;
        }
    };
}

function VertragAblehnen(id, zeile) {
    if (req == null) {
        return;
    }

    req.open("GET", '<?=AJAX_SERVER_PFAD; ?>/actions/vertraege.php?a=3&vid=' + id + '&ajax=1', true);
    req.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    req.send(null);

    req.onreadystatechange = function () {
        switch (req.readyState) {
            case 4:
                if (req.responseText == "1") {
                    zeile.style.display = 'none';
                } else {
                    document.location.href = "./?p=vertraege_liste&m=" + req.responseText;
                }
                break;
            default:
                return false;
                break;
        }
    };
}

function ajaxCheckUserName() {
    if (req3 == null) {
        return false;
    }
    var uname = document.form_login.name.value;							// Zeiger auf das Namensfeld

    var ubild = document.getElementById("UserUnique").getElementsByTagName("img")[0];		// Zeiger auf das Bild neben dem Namen
    var utext = document.getElementById("UserUnique").getElementsByTagName("span")[0];	// Zeiger auf den Text neben dem Bild

    var submit_btn = document.form_login.Submit;		// Zeiger auf den Abschicken-Button

    req3.open("GET", '<?=AJAX_SERVER_PFAD; ?>/js/check_username.php?uname=' + uname, true);
    req3.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    req3.send(null);

    req3.onreadystatechange = function () {
        switch (req3.readyState) {
            case 4:
                switch (req3.responseText) {
                    case "1":
                        utext.innerHTML = "<b>OK</b>";				// Und als Text schreiben wir einfach "OK" hin.
                        ubild.src = "./pics/small/ok.png";		// Wenn er es bis hierher schafft, dann passt der Benutzername

                        return;
                    case "0":
                        utext.innerHTML = "Bitte einen gültigen Namen eingeben!";		// Setzt den Hinweistext auf eine entsprechende Meldung
                        ubild.src = "./pics/small/error.png";								// Dann darf er das nicht, die minimale Länge für Benutzernamen sind 2 Zeichen
                        submit_btn.enabled = "";														//
                        submit_btn.disabled = "disabled";										// Der Submitbutton wird deaktiviert.

                        return;
                    case "2":
                        utext.innerHTML = "Der Name wird bereits verwendet.";		// Setzt den Hinweistext auf eine entsprechende Meldung
                        ubild.src = "./pics/small/error.png";								// Dann darf er das nicht, die minimale Länge für Benutzernamen sind 2 Zeichen
                        submit_btn.enabled = "";														//
                        submit_btn.disabled = "disabled";										// Der Submitbutton wird deaktiviert.

                        return;
                }
                break;
            default:
                return;
                break;
        }
    };
}

function ajaxCheckEMail() {
    if (req2 == null) {
        return false;
    }
    var uemail = document.form_login.email.value;							// Zeiger auf das Namensfeld

    var ubild = document.getElementById("EMailUnique").getElementsByTagName("img")[0];		// Zeiger auf das Bild neben dem Namen
    var utext = document.getElementById("EMailUnique").getElementsByTagName("span")[0];	// Zeiger auf den Text neben dem Bild

    var submit_btn = document.form_login.Submit;		// Zeiger auf den Abschicken-Button

    req2.open("GET", '<?=AJAX_SERVER_PFAD; ?>/js/check_email.php?uemail=' + uemail, true);
    req2.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    req2.send(null);

    req2.onreadystatechange = function () {
        switch (req2.readyState) {
            case 4:
                switch (req2.responseText) {
                    case "1":
                        ubild.src = "./pics/small/ok.png";		// Wenn er es bis hierher schafft, dann passt der Benutzername
                        utext.innerHTML = "<b>OK</b>";				// Und als Text schreiben wir einfach "OK" hin.

                        return;
                    case "0":
                        utext.innerHTML = "Bitte eine gültige EMail-Adresse eingeben!";		// Setzt den Hinweistext auf eine entsprechende Meldung
                        ubild.src = "./pics/small/error.png";								// Dann darf er das nicht, die minimale Länge für Benutzernamen sind 2 Zeichen
                        submit_btn.enabled = "";														//
                        submit_btn.disabled = "disabled";										// Der Submitbutton wird deaktiviert.

                        return;
                    case "2":
                        utext.innerHTML = "Diese EMail-Adresse wird bereits verwendet.";		// Setzt den Hinweistext auf eine entsprechende Meldung
                        ubild.src = "./pics/small/error.png";								// Dann darf er das nicht, die minimale Länge für Benutzernamen sind 2 Zeichen
                        submit_btn.enabled = "";														//
                        submit_btn.disabled = "disabled";										// Der Submitbutton wird deaktiviert.

                        return;
                }
                break;
            default:
                return;
                break;
        }
    };
}
