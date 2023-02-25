<?php
/*
 * MIT Licence
 * Copyright (c) 2023 Simon Frankenberger
 *
 * Please see LICENCE.md for complete licence text.
 */

restrictSitter('Notizblock');

$data = Database::getInstance()->getNotizblock($_SESSION['blm_user']);

if (isset($_GET['notizblock'])) $data = $_GET['notizblock'];
?>
<div id="SeitenUeberschrift">
    <img src="/pics/big/knotes.webp" alt=""/>
    <span>Büro<?= createHelpLink(1, 14); ?></span>
</div>

<?= getMessageBox(getOrDefault($_GET, 'm', 0)); ?>

<p>
    Hier können Sie verschiedene Informationen speichern.
</p>

<div class="form">
    <form action="/actions/notizblock.php" method="post">
        <header><label for="notizblock">Notizblock</label></header>
        <textarea id="notizblock" name="notizblock" maxlength="4096"><?= escapeForOutput($data, false); ?></textarea>
        <div>
            Noch <span id="chars_left">4096</span> Zeichen übrig.
            <input type="submit" value="Speichern" id="save_notes"/>
        </div>
    </form>
</div>
