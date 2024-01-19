<?php
/*
 * MIT Licence
 * Copyright (c) 2023 Simon Frankenberger
 *
 * Please see LICENCE.md for complete licence text.
 */

requireFieldSet($_GET, 'id', '/?p=admin_markt');
$id = getOrDefault($_GET, 'id', 0);
$offset = getOrDefault($_GET, 'o', 0);
?>
<div id="SeitenUeberschrift">
    <img src="./pics/big/kservices.webp" alt=""/>
    <span>Administrationsbereich - Angebot bearbeiten</span>
</div>

<?= getMessageBox(getOrDefault($_GET, 'm', 0)); ?>

<?php
$entry = Database::getInstance()->getMarktplatzEntryById($id);
requireEntryFound($entry, '/?p=admin_markt');

if (isset($_GET['von'])) $entry['Von'] = $_GET['von'];
if (isset($_GET['ware'])) $entry['Was'] = $_GET['ware'];
if (isset($_GET['menge'])) $entry['Menge'] = $_GET['menge'];
if (isset($_GET['preis'])) $entry['Preis'] = $_GET['preis'];
?>
<div class="form AdminEditMarket">
    <form action="./actions/admin_markt.php" method="post">
        <input type="hidden" name="a" value="2"/>
        <input type="hidden" name="o" value="<?= $offset; ?>"/>
        <input type="hidden" name="id" value="<?= escapeForOutput($entry['ID']); ?>"/>
        <header>Angebot bearbeiten</header>
        <div>
            <label for="von">Von:</label>
            <span><?= createDropdown(Database::getInstance()->getAllPlayerIdsAndName(), $entry['Von'], 'von', false, true); ?></span>
        </div>
        <div>
            <label for="ware">Was:</label>
            <span><?= createWarenDropdown($entry['Was'], 'ware', false); ?></span>
        </div>
        <div>
            <label for="menge">Menge:</label>
            <input type="number" min="1" name="menge" id="menge"
                   value="<?= $entry['Menge']; ?>" size="6"/> kg
        </div>
        <div>
            <label for="preis">Preis:</label>
            <input type="number" min="0.01" step="0.01" name="preis" id="preis"
                   value="<?= $entry['Preis']; ?>" size="6"/> €
        </div>
        <div>
            <input type="submit" value="Speichern" id="save_entry"/>
        </div>
    </form>
</div>

<div>
    <a href="./?p=admin_markt&amp;o=<?= $offset; ?>">&lt;&lt; Zurück</a>
</div>
