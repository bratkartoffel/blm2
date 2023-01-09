<?php
/*
 * MIT Licence
 * Copyright (c) 2023 Simon Frankenberger
 *
 * Please see LICENCE.md for complete licence text.
 */

$von = getOrDefault($_GET, 'von', 0);
$ware = getOrDefault($_GET, 'ware', 0);
$menge = getOrDefault($_GET, 'menge', 0);
$preis = getOrDefault($_GET, 'preis', .0);
?>
<div id="SeitenUeberschrift">
    <img src="/pics/big/kservices.webp" alt=""/>
    <span>Administrationsbereich - Angebot einstellen</span>
</div>

<?= getMessageBox(getOrDefault($_GET, 'm', 0)); ?>

<div id="FilterForm">
    <form action="/actions/admin_markt.php" method="post">
        <input type="hidden" name="a" value="1"/>
        <table class="Liste">
            <tr>
                <th colspan="2">Angebot einstellen</th>
            </tr>
            <tr>
                <td><label for="von">Von:</label></td>
                <td><?= createDropdown(Database::getInstance()->getAllPlayerIdsAndName(), $von, 'von', false, true); ?></td>
            </tr>
            <tr>
                <td><label for="ware">Was:</label></td>
                <td><?= createWarenDropdown($ware, 'ware', false); ?></td>
            </tr>
            <tr>
                <td><label for="menge">Menge:</label></td>
                <td><input type="text" name="menge" id="menge" value="<?= formatWeight($menge, false); ?>" size="6"/> kg
                </td>
            </tr>
            <tr>
                <td><label for="preis">Preis:</label></td>
                <td><input type="text" name="preis" id="preis" value="<?= formatCurrency($preis, false); ?>" size="6"/>
                    €
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <input type="submit" value="Speichern"/>
                </td>
            </tr>
        </table>
    </form>
</div>

<div>
    <a href="/?p=admin_markt">&lt;&lt; Zurück</a>
</div>
