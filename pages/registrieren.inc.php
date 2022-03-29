<?php
/**
 * Wird in die index.php eingebunden; Formular zum Registrieren eines neuen Accounts
 *
 * @version 1.0.1
 * @author Simon Frankenberger <simonfrankenberger@web.de>
 * @package blm2.pages
 */
?>
<table id="SeitenUeberschrift">
    <tr>
        <td style="width: 80px;"><img src="pics/big/register.png" alt="Registrieren"/></td>
        <td style="font-size: 16pt; font-weight: bold; text-decoration: underline">Registrieren</td>
    </tr>
</table>

<?= $m; ?>

<b>
    Hier k&ouml;nnen Sie einen neuen Spieler anlegen. Bitte geben Sie hierzu einen Spielernamen ein, welcher noch nicht
    belegt ist und w&auml;hlen Sie ein Passwort, welches nur Sie wissen sollten.
</b>
<br/>
<br/>
<script type="text/javascript">
    <!--
    function CheckAll() {
        ajaxCheckUserName();
        CheckPassword();
        ajaxCheckEMail();
    }

    function CheckPassword() {
        // Überprüft, ob die eingegebenen Passwörter übereinstimmen
        var pwd1 = document.form_login.pwd1;		// Zeiger auf das erste Passwort
        var pwd2 = document.form_login.pwd2;		// und auf das zweite Passwort

        var pbild = document.getElementById("PasswordOK").getElementsByTagName("img")[0];		// Zeiger auf das Bild und dem
        var ptext = document.getElementById("PasswordOK").getElementsByTagName("span")[0];	// Text neben den Eingabefeldern

        var submit_btn = document.form_login.Submit;									// Zeiger auf den Absenden-Button

        if (pwd1.value.length < 4) {		// Wenn das Passwort zu kurz ist
            pbild.src = "./pics/small/error.png";										//
            ptext.innerHTML = "Das Passwort ist zu kurz!";	// brauchen wir hier gar nicht weitermachen,
            submit_btn.enabled = "";																// also abbrechen und Meldung ausgeben
            submit_btn.disabled = "disabled";												//
            return false;																					//
        }

        if (pwd1.value == "") {			// Wenn das erste Feld schon mal leer ist, dann...
            pbild.src = "./pics/small/error.png";										//
            ptext.innerHTML = "Bitte geben Sie ein Passwort ein!";	// brauchen wir hier gar nicht weitermachen,
            submit_btn.enabled = "";																// also abbrechen und Meldung ausgeben
            submit_btn.disabled = "disabled";												//
            return;																					//
        }

        if (pwd1.value != pwd2.value) {		// Wenn die beiden Passwörter nicht übereinstimmen, dann
            pbild.src = "./pics/small/error.png";															//
            ptext.innerHTML = "Bitte geben Sie 2x das selbe Passwort ein!";		// Das selbe wie oben, abbrechen
            submit_btn.enabled = "";																					// und Meldung anzeigen
            submit_btn.disabled = "disabled";																	//
            return;																										//
        }

        pbild.src = "./pics/small/ok.png";		// Wenn er es so weit geschafft hat, dann
        ptext.innerHTML = "<b>OK</b>";								// passt die Eingabe, was wir gleich azeigen lassen

        submit_btn.disabled = "";							//
        submit_btn.enabled = "enabled";				// und schließlich wird auch noch der Abschickenbutton aktiviert.

        return;
    }

    -->
</script>
<form action="./actions/registrieren.php" method="post" name="form_login">
    <table class="Liste" style="width: 550px" cellspacing="0">
        <tr>
            <th colspan="3">Einen neuen Benutzer anlegen:</th>
        </tr>
        <tr>
            <td style="width: 110px; text-align: right;">
                Benutzername:
            </td>
            <td>
                <input name="name" type="text" size="15" maxlength="20" onkeyup="CheckAll(); return false;"/>
            </td>
            <td id="UserUnique" style="width: 330px;">
                <img src="./pics/small/error.png" alt="Benutzernamenprüfung" style="margin-right: 10px;"/>
                <span></span>
            </td>
        </tr>
        <tr>
            <td style="text-align: right;">
                (*) EMail-Adresse:
            </td>
            <td>
                <input name="email" type="text" size="15" maxlength="32" onkeyup="CheckAll(); return false;"/>
            </td>
            <td id="EMailUnique" style="width: 330px;">
                <img src="./pics/small/error.png" alt="EMail-Adressen-Überprüfung" style="margin-right: 10px;"/>
                <span></span>
            </td>
        </tr>
        <tr>
            <td style="text-align: right;">Passwort:</td>
            <td><input name="pwd1" type="password" size="15" onkeyup="CheckAll(); return false;"/></td>
            <td rowspan="2" id="PasswordOK"><img src="./pics/small/error.png" alt="Passwort&uuml;berpr&uuml;fung"
                                                 style="margin-right: 10px;"/> <span></span></td>
        </tr>
        <tr>
            <td style="text-align: right;">Best&auml;tigung:</td>
            <td><input name="pwd2" type="password" size="15" onkeyup="CheckAll(); return false;"/></td>
        </tr>
        <tr>
            <td rowspan="2">Sicherheitscode</td>
            <td colspan="2" style="text-align: center;">
                <?php
                $captcha = new Captcha();
                $captcha->erstelle();
                ?>
                <img src="include/captcha_class/pics/<?= basename($captcha->holeBildpfad()); ?>" alt="Sicherheitscode"
                     id="Captcha"/>
                <input type="hidden" name="bild" value="<?= basename($captcha->holeBildpfad()); ?>"/>
            </td>
        </tr>
        <tr>
            <td colspan="2" style="text-align: center;"><input type="text" name="captcha_code" maxlength="6" size="5"/>
            </td>
        </tr>
        <tr>
            <td colspan="3" style="text-align: center;">
			  <span style="color: red;">
					Mit Ihrer Registrierung akzeptieren Sie die <a
                          href="./?p=regeln">Regeln</a> des Bioladenmanagers.<br/>
					Zuwiderhandlungen kann von einer Verwarnung bishin zu einem Ausschluss vom Spielgeschehen führen.<br/>
				</span>
                <br/>
                <script type="text/javascript">
                    <!--
                    // Falls der Browser JavaScript kann, dann lassen wir den deaktivierten Button ausgeben.
                    document.write('<input name="Submit" type="submit" value="Registrieren" disabled="disabled" />');
                    -->
                </script>
                <noscript>
                    <!--
                        Falls der Browser kein Javascript kann, dann zeigen wir einfach den Button sofort an.
                    -->
                    <input name="Submit" type="submit" value="Registrieren"/>
                </noscript>
            </td>
        </tr>
    </table>
</form>
<script type="text/javascript">
    <!--
    CheckAll();
    -->
</script>
<h3>(*): An diese Adresse wird der Aktivierungscode verschickt. Ohne diesen kann das Spiel nicht gespielt werden!</h3>
