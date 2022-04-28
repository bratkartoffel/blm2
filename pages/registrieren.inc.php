<?php
if (registration_closed) {
    redirectTo('/?p=anmelden', 148);
}

$name = getOrDefault($_GET, 'name');
$email = getOrDefault($_GET, 'email');
?>
<div id="SeitenUeberschrift">
    <img src="/pics/big/register.png" alt=""/>
    <span>Registrieren</span>
</div>

<?= getMessageBox(getOrDefault($_GET, 'm', 0)); ?>

<h3>
    Hier können Sie einen neuen Spieler anlegen. Bitte geben Sie hierzu einen Spielernamen ein, welcher noch nicht
    belegt ist und wählen Sie ein Passwort, welches nur Sie wissen sollten.
</h3>

<div class="form">
    <form action="/actions/registrieren.php" method="post">
        <header>Neuen Benutzer anlegen</header>

        <div>
            <label for="name">Benutzername:</label>
            <input name="name" id="name" type="text" size="20" required value="<?= escapeForOutput($name); ?>"
                   minlength="<?= username_min_len; ?>" maxlength="<?= username_max_len; ?>"/>
        </div>
        <div>
            <label for="name">EMail-Adresse:</label>
            <input name="email" id="email" type="email" size="20" required value="<?= escapeForOutput($email); ?>"
                   maxlength="<?= email_max_len; ?>"/>
        </div>
        <div>
            <label for="pwd1">Passwort:</label>
            <input name="pwd1" id="pwd1" type="password" size="20" required
                   minlength="<?= password_min_len; ?>"/>
        </div>
        <div>
            <label for="pwd2">Bestätigung:</label>
            <input name="pwd2" id="pwd2" type="password" size="20" required
                   minlength="<?= password_min_len; ?>"/>
        </div>
        <div>
            <?php
            require_once('include/captcha_class/captcha.php');
            $captcha = new Captcha();
            $captcha->erstelle();
            ?>
            <img src="include/captcha_class/pics/<?= basename($captcha->holeBildpfad()); ?>" alt="" id="captcha"/>
        </div>
        <div>
            <label for="captcha_code">Sicherheitscode:</label>
            <input name="captcha_code" id="captcha_code" type="text" size="6" required
                   minlength="<?= CAPTCHA_STD_LAENGE; ?>" maxlength="<?= CAPTCHA_STD_LAENGE; ?>"/>
        </div>

        <div>
            <input type="hidden" name="captcha_bild" value="<?= basename($captcha->holeBildpfad()); ?>"/>
            <input type="submit" id="register" value="Registrieren"/>
        </div>
    </form>
</div>
