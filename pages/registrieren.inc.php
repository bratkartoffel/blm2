<?php
/*
 * MIT Licence
 * Copyright (c) 2023 Simon Frankenberger
 *
 * Please see LICENCE.md for complete licence text.
 */

if (Config::getBoolean(Config::SECTION_BASE, 'registration_closed')) {
    redirectTo('/?p=anmelden', 148);
}

$name = getOrDefault($_GET, 'name');
$email = getOrDefault($_GET, 'email');

require_once __DIR__ . '/../include/captcha.class.php';
$captcha = new Captcha();
$captcha->createCaptcha();
?>
<div id="SeitenUeberschrift">
    <img src="./pics/big/babelfish.webp" alt=""/>
    <span>Registrieren</span>
</div>

<?= getMessageBox(getOrDefault($_GET, 'm', 0)); ?>

<p>
    Hier können Sie einen neuen Spieler anlegen. Bitte geben Sie hierzu einen Spielernamen ein, welcher noch nicht
    belegt ist und wählen Sie ein Passwort, welches nur Sie wissen sollten.
</p>

<div class="form RegisterForm">
    <form action="./actions/registrieren.php" method="post">
        <header>Neuen Benutzer anlegen</header>

        <div>
            <label for="name">Benutzername:</label>
            <input name="name" id="name" type="text" size="20" required value="<?= escapeForOutput($name); ?>"
                   minlength="<?= Config::getInt(Config::SECTION_BASE, 'username_min_len'); ?>"
                   maxlength="<?= Config::getInt(Config::SECTION_BASE, 'username_max_len'); ?>"/>
        </div>
        <div>
            <label for="email">EMail-Adresse:</label>
            <input name="email" id="email" type="email" size="20" required value="<?= escapeForOutput($email); ?>"
                   maxlength="<?= Config::getInt(Config::SECTION_BASE, 'email_max_len'); ?>"/>
        </div>
        <div>
            <label for="pwd1">Passwort:</label>
            <input name="pwd1" id="pwd1" type="password" size="20" required
                   minlength="<?= Config::getInt(Config::SECTION_BASE, 'password_min_len'); ?>"/>
        </div>
        <div>
            <label for="pwd2">Bestätigung:</label>
            <input name="pwd2" id="pwd2" type="password" size="20" required
                   minlength="<?= Config::getInt(Config::SECTION_BASE, 'password_min_len'); ?>"/>
        </div>
        <div>
            <img src="<?= $captcha->getImageUrl(); ?>" alt="Captcha" id="captcha"/>
        </div>
        <div>
            <label for="captcha_code">Sicherheitscode:</label>
            <input name="captcha_code" id="captcha_code" type="text" size="8" required/>
        </div>

        <div>
            <input type="hidden" name="captcha_id" value="<?= $captcha->getId(); ?>"/>
            <input type="submit" id="register" value="Registrieren"/>
        </div>
    </form>
</div>
