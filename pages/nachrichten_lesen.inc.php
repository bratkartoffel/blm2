<?php
/*
 * MIT Licence
 * Copyright (c) 2023 Simon Frankenberger
 *
 * Please see LICENCE.md for complete licence text.
 */

restrictSitter('Nachrichten');

$id = getOrDefault($_GET, 'id', 0);
$data = Database::getInstance()->getMessageByIdAndAnOrVonEquals($id, $_SESSION['blm_user']);
requireEntryFound($data, '/?p=nachrichten_liste');

if ($data['An'] == $_SESSION['blm_user']) {
    Database::getInstance()->begin();
    if (Database::getInstance()->updateTableEntry(Database::TABLE_MESSAGES, $data['ID'], array('Gelesen' => 1)) === null) {
        Database::getInstance()->rollBack();
        redirectTo('/?p=nachrichten_liste', 142, __LINE__);
    }
    Database::getInstance()->commit();
}
?>
<div id="SeitenUeberschrift">
    <img src="./pics/big/xfmail.webp" alt=""/>
    <span>Nachricht lesen<?= createHelpLink(1, 13); ?></span>
</div>

<?= getMessageBox(getOrDefault($_GET, 'm', 0)); ?>

<div class="form NachrichtLesen">
    <header>Nachricht lesen</header>
    <div>
        <label>Absender:</label>
        <span id="sender"><?= createProfileLink($data['VonID'], $data['VonName']); ?></span>
    </div>
    <div>
        <label>Empfänger:</label>
        <span id="receiver"><?= createProfileLink($data['AnID'], $data['AnName']); ?></span>
    </div>
    <div>
        <label>Zeit:</label>
        <span><?= formatDateTime(strtotime($data['Zeit'])); ?></span>
    </div>
    <div>
        <label>Betreff:</label>
        <span id="subject"><?= escapeForOutput($data['Betreff']); ?></span>
    </div>
    <div id="message"><?= replaceBBCode($data['Nachricht']); ?></div>
</div>

<div>
    <a href="./?p=nachrichten_liste">&lt;&lt; Zurück</a>
    <?php
    if ($data['An'] == $_SESSION['blm_user'] && $data['Von'] != 0 && $data['VonID'] !== null) {
        ?>
        | <a href="./?p=nachrichten_schreiben&amp;reply=<?= $data['ID']; ?>">Antworten</a>
        <?php
    }
    ?>
</div>
