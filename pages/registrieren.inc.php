<table id="SeitenUeberschrift">
    <tr>
        <td><img src="/pics/big/register.png" alt="Registrieren"/></td>
        <td>Registrieren</td>
    </tr>
</table>

<?= CheckMessage(getOrDefault($_GET, 'm', 0)); ?>

<h3>
    Hier können Sie einen neuen Spieler anlegen. Bitte geben Sie hierzu einen Spielernamen ein, welcher noch nicht
    belegt ist und wählen Sie ein Passwort, welches nur Sie wissen sollten.
</h3>

<form action="/actions/registrieren.php" method="post" name="form_login">
    <table class="Liste" style="width: 350px">
        <tr>
            <th colspan="2">Einen neuen Benutzer anlegen:</th>
        </tr>
        <tr>
            <td style="width: 110px; text-align: right;"><label for="name">Benutzername:</label></td>
            <td><input name="name" id="name" type="text" size="20" required
                       minlength="<?= USERNAME_MIN_LENGTH; ?>" maxlength="<?= USERNAME_MAX_LENGTH; ?>"/></td>
        </tr>
        <tr>
            <td style="text-align: right;"><label for="email">EMail-Adresse:</label></td>
            <td><input name="email" id="email" type="email" size="20" required maxlength="<?= EMAIL_MAX_LENGTH; ?>"/>
            </td>
        </tr>
        <tr>
            <td style="text-align: right;"><label for="pwd1">Passwort:</label></td>
            <td><input name="pwd1" id="pwd1" type="password" size="20" required
                       minlength="<?= PASSWORD_MIN_LENGTH; ?>"/></td>
        </tr>
        <tr>
            <td style="text-align: right;"><label for="pwd2">Bestätigung:</label></td>
            <td><input name="pwd2" id="pwd2" type="password" size="20" required
                       minlength="<?= PASSWORD_MIN_LENGTH; ?>"/></td>
        </tr>
        <tr>
            <td rowspan="2"><label for="captcha_code">Sicherheitscode</label></td>
            <td><?php
                $captcha = new Captcha();
                $captcha->erstelle();
                ?>
                <img src="include/captcha_class/pics/<?= basename($captcha->holeBildpfad()); ?>" alt="" id="Captcha"/>
                <input type="hidden" name="captcha_bild" value="<?= basename($captcha->holeBildpfad()); ?>"/>
            </td>
        </tr>
        <tr>
            <td><input type="text" id="captcha_code" name="captcha_code" required minlength="<?= CAPTCHA_STD_LAENGE; ?>"
                       maxlength="<?= CAPTCHA_STD_LAENGE; ?>"/></td>
        </tr>
        <tr>
            <td colspan="3" style="text-align: center;">
                <p>
                    Mit Ihrer Registrierung akzeptieren Sie die <a href="/?p=regeln">Regeln</a> des
                    Bioladenmanagers.<br/>
                    Zuwiderhandlungen kann von einer Verwarnung oder zum Ausschluss vom Spielgeschehen führen.
                </p>
                <input name="Submit" type="submit" value="Registrieren"/>
            </td>
        </tr>
    </table>
</form>
