<?php
/*
 * MIT Licence
 * Copyright (c) 2023 Simon Frankenberger
 *
 * Please see LICENCE.md for complete licence text.
 */

$email = getOrDefault($_GET, 'email');

require_once('include/captcha.class.php');
$captcha = new Captcha();
$captcha->createCaptcha();
?>
<div id="SeitenUeberschrift">
    <img src="/pics/big/Password.webp" alt=""/>
    <span>Passwort vergessen</span>
</div>

<?= getMessageBox(getOrDefault($_GET, 'm', 0)); ?>

<p>
    Falls Sie ihren Benutzernamen oder Passwort vergessen haben, so können Sie sich hier den Namen und ein neues
    Passwort per EMail schicken lassen.
</p>

<div class="form">
    <form action="/actions/pwd_reset.php" method="post">
        <input type="hidden" name="a" value="1"/>
        <header>Passwort wiederherstellen</header>

        <div>
            <label for="email">E-Mail Adresse:</label>
            <input name="email" id="email" type="text" size="32" maxlength="<?= Config::getInt(Config::SECTION_BASE, 'email_max_len'); ?>"
                   value="<?= escapeForOutput($email); ?>"/>
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
            <input type="submit" id="login" value="Abschicken"/>
        </div>
    </form>
</div>
