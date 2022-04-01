<table id="SeitenUeberschrift">
    <tr>
        <td><img src="/pics/big/login.png" alt=""/></td>
        <td>Anmelden</td>
    </tr>
</table>

<?= CheckMessage(getOrDefault($_GET, 'm', 0)); ?>

<h3>
    Hier können Sie sich im Spiel anmelden. Geben Sie hierzu Ihre Logindaten ein.
</h3>

<form action="/actions/login.php" method="post">
    <table class="Liste" style="width: 250px">
        <tr>
            <th colspan="2">Login für registrierte Benutzer</th>
        </tr>
        <tr>
            <td style="text-align: right;"><label for="name">Benutzername:</label></td>
            <td><input name="name" id="name" type="text" size="20" maxlength="20"/></td>
        </tr>
        <tr>
            <td style="text-align: right;"><label for="pwd">Passwort:</label></td>
            <td><input name="pwd" id="pwd" type="password" size="20"/></td>
        </tr>
        <tr>
            <td style="text-align: center;" colspan="2">
                <?php
                if (CheckGameLock()) {
                    echo '<input type="submit" value="Anmelden" /><br /><span class="MeldungR">Das Spiel ist zur Zeit pausiert, Anmeldungen sind erst wieder bei Rundenstart möglich.</span>';
                } else {
                    echo '<input type="submit" value="Anmelden" />';
                }
                ?>
            </td>
        </tr>
    </table>
</form>
