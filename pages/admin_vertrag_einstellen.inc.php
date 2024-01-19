<?php
/*
 * MIT Licence
 * Copyright (c) 2023 Simon Frankenberger
 *
 * Please see LICENCE.md for complete licence text.
 */

$von = getOrDefault($_GET, 'von', 0);
$an = getOrDefault($_GET, 'an', 0);
$ware = getOrDefault($_GET, 'ware', 0);
$menge = getOrDefault($_GET, 'menge', 0);
$preis = getOrDefault($_GET, 'preis', .0);
?>
<div id="SeitenUeberschrift">
    <img src="./pics/big/kservices.webp" alt=""/>
    <span>Administrationsbereich - Vertrag erstellen</span>
</div>

<?= getMessageBox(getOrDefault($_GET, 'm', 0)); ?>

<div class="form AdminCreateContract">
    <form action="./actions/admin_vertrag.php" method="post">
        <input type="hidden" name="a" value="1"/>
        <header>Vertrag einstellen</header>
        <div>
            <label for="von">Absender:</label>
            <span><?= createDropdown(Database::getInstance()->getAllPlayerIdsAndName(), $von, 'von', false, true); ?></span>
        </div>
        <div>
            <label for="an">Empfänger:</label>
            <span><?= createDropdown(Database::getInstance()->getAllPlayerIdsAndName(), $an, 'an', false); ?></span>
        </div>
        <div>
            <label for="ware">Was:</label>
            <span><?= createWarenDropdown($ware, 'ware', false); ?></span>
        </div>
        <div>
            <label for="menge">Menge:</label>
            <input type="number" min="1" name="menge" id="menge" value="<?= $menge; ?>" size="6"/> kg
        </div>
        <div>
            <label for="preis">Preis:</label>
            <input type="number" min="0.01" step="0.01" name="preis" id="preis" value="<?= $preis; ?>" size="6"/> €
        </div>
        <div>
            <input type="submit" value="Speichern"/>
        </div>
    </form>
</div>

<div>
    <a href="./?p=admin_vertrag">&lt;&lt; Zurück</a>
</div>
