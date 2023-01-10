<?php
/*
 * MIT Licence
 * Copyright (c) 2023 Simon Frankenberger
 *
 * Please see LICENCE.md for complete licence text.
 */

$id = getOrDefault($_GET, 'id', $_SESSION['blm_user']);

$data = Database::getInstance()->getPlayerCardByID($id);
requireEntryFound($data, '/?p=rangliste');

?>
<div id="SeitenUeberschrift">
    <img src="/pics/big/kuser.webp" alt=""/>
    <span>Profil von <?= escapeForOutput($data['Name']); ?></span>
</div>

<?= getMessageBox(getOrDefault($_GET, 'm', 0)); ?>

<p>
    Hier sehen Sie das Profil des Benutzers "<?= escapeForOutput($data['Name']); ?>". Jeder Spieler ist für
    sein Profil selbst verantwortlich!
</p>

<div class="form Profil">
    <header>Profil</header>
    <div>
        <label for="name">Name:</label>
        <span><?= escapeForOutput($data['Name']); ?></span>
    </div>
    <div>
        <label for="image">Bild:</label>
        <span><img src="/pics/profile.php?uid=<?= $data['ID']; ?>&amp;ts=<?= ($data['LastImageChange'] == null ? 0 : strtotime($data['LastImageChange'])); ?>"
                   alt="Profilbild"/></span>
    </div>
    <div>
        <label for="description">Beschreibung:</label>
        <span><?= replaceBBCode($data['Beschreibung']); ?></span>
    </div>
    <div>
        <label for="group">Gruppe:</label>
        <span><?= createGroupLink($data['GruppeID'], $data['GruppeName']); ?></span>
    </div>
    <div>
        <label for="registered">Registriert am:</label>
        <span><?= formatDate(strtotime($data['RegistriertAm'])); ?></span>
    </div>
    <div>
        <label for="lastLogin">Letzter Login:</label>
        <span><?= $data['LastLogin'] !== null ? formatDate(strtotime($data['LastLogin'])) : '<i>- Nie -</i>'; ?></span>
    </div>
    <div>
        <label for="warnings">Verwarnungen:</label>
        <span><?= $data['Verwarnungen']; ?></span>
    </div>
    <div>
        <label for="locked">Gesperrt:</label>
        <span><?= getYesOrNo($data['Gesperrt']); ?></span>
    </div>
    <div>
        <label for="points">Punkte:</label>
        <span><?= formatPoints($data['Punkte']); ?> (Platz: <?php
            $rank = Database::getInstance()->getPlayerRankById($data['ID']);
            echo sprintf('<a href="/?p=rangliste&amp;q=%s">%d</a>', urlencode($data['Name']), $rank);
            ?>)</span>
    </div>
    <?php
    if ($data['ID'] != $_SESSION['blm_user']) {
        ?>
        <div>
            <label for="contact">Kontakt:</label>
            <span><?= sprintf('<a href="/?p=nachrichten_schreiben&receiver=%s">IGM</a> | <a href="/?p=vertraege_neu&empfaenger=%s">Vertrag</a>',
                    urlencode($data['Name']), urlencode($data['Name'])); ?></span>
        </div>
        <?php
    }
    ?>
    <div>
        <label for="igmSent">Gesendete Nachrichten:</label>
        <span><?= formatPoints($data['IgmGesendet']); ?></span>
    </div>
    <div>
        <label for="igmReceived">Empfangene Nachrichten:</label>
        <span><?= formatPoints($data['IgmEmpfangen']); ?></span>
    </div>
</div>
