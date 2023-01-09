<?php
/*
 * MIT Licence
 * Copyright (c) 2023 Simon Frankenberger
 *
 * Please see LICENCE.md for complete licence text.
 */

$name = getOrDefault($_GET, 'name');
?>
<div id="SeitenUeberschrift">
    <img src="/pics/big/Password.webp" alt=""/>
    <span>Anmelden</span>
</div>

<?= getMessageBox(getOrDefault($_GET, 'm', 0)); ?>

<p>
    Hier können Sie sich im Spiel anmelden. Geben Sie hierzu Ihre Logindaten ein.
</p>

<?php
if (isGameLocked()) {
    echo '<h3>Das Spiel ist aktuell pausiert, ein Login ist noch nicht möglich.</h3>';
}
?>
<div class="form LoginForm">
    <form action="/actions/login.php" method="post">
        <header>Login für registrierte Benutzer</header>

        <div>
            <label for="name">Benutzername:</label>
            <input name="name" id="name" type="text" size="20" maxlength="20" value="<?= escapeForOutput($name); ?>"/>
        </div>
        <div>
            <label for="pwd">Passwort:</label>
            <input name="pwd" id="pwd" type="password" size="20"/>
        </div>

        <div>
            <input type="submit" id="login" value="Anmelden"/>
        </div>
    </form>
</div>

<div>
    <a hreF="/?p=passwort_vergessen">Passwort vergessen?</a>
</div>
