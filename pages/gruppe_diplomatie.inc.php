<?php
/*
 * MIT Licence
 * Copyright (c) 2023 Simon Frankenberger
 *
 * Please see LICENCE.md for complete licence text.
 */

restrictSitter('Gruppe');

$gruppe = getOrDefault($_GET, 'gruppe');
$rights = Database::getInstance()->getGroupRightsByUserId($_SESSION['blm_user']);
requireEntryFound($rights, '/?p=gruppe');

$diplomacy_db = Database::getInstance()->getAllGroupDiplomacyById($rights['group_id']);
$diplomacy = array(group_diplomacy_bnd => array(), group_diplomacy_nap => array(), group_diplomacy_war => array());
foreach ($diplomacy_db as $entry) {
    if ($entry['Aktiv'] == 0 && $entry['An'] == $rights['group_id']) continue;
    $diplomacy[intval($entry['Typ'])][] = $entry;
}

function printDiplomacyTable($diplomacy, $name, $hasRights)
{
    ?>
    <table class="Liste" id="diplomacy_<?= $name; ?>" data-count="<?= count($diplomacy); ?>">
        <tr>
            <th>Partner</th>
            <th>Gültig seit</th>
            <th>Aktion</th>
        </tr>
        <?php
        foreach ($diplomacy as $row) {
            ?>
            <tr>
                <td><?= createGroupLink($row['GruppeID'], $row['GruppeName']); ?></td>
                <?php
                if ($row['Aktiv'] == 1) {
                    printf('<td>%s</td>', formatDateTime(strtotime($row['Seit'])));
                    if ($hasRights) {
                        printf('<td><a class="cancel_relation" data-type="%s" data-partner="%s" href="/actions/gruppe.php?a=15&amp;id=%d&amp;token=%s">Kündigen</a></td>',
                            $name, escapeForOutput($row['GruppeName']), $row['ID'], $_SESSION['blm_xsrf_token']);
                    } else {
                        echo '<td>Keine Rechte</td>';
                    }
                } else {
                    echo '<td>- noch nicht aktiv -</td>';
                    if ($hasRights) {
                        printf('<td><a class="retract_offer" data-type="%s" data-partner="%s" href="/actions/gruppe.php?a=16&amp;id=%d&amp;token=%s">Zurückziehen</a></td>',
                            $name, escapeForOutput($row['GruppeName']), $row['ID'], $_SESSION['blm_xsrf_token']);
                    } else {
                        echo '<td>Keine Rechte</td>';
                    }
                }
                ?>
            </tr>
            <?php
        }

        if (count($diplomacy) == 0) {
            echo '<tr><td colspan="3" class="center"><i>Keine Einträge vorhanden</i></td></tr>';
        }
        ?>
    </table>
    <?php
}

?>
<div id="SeitenUeberschrift">
    <img src="/pics/big/Community_Help.webp" alt=""/>
    <span>Gruppe - Diplomatie<?= createHelpLink(1, 23); ?></span>
</div>

<?= getMessageBox(getOrDefault($_GET, 'm', 0)); ?>
<?= createGroupNaviation(3, $rights['group_id']); ?>

<h3>Nicht-Angriffs-Pakte (NAPs):</h3>
<?php printDiplomacyTable($diplomacy[group_diplomacy_nap], 'NAP', $rights['group_diplomacy'] == 1); ?>

<h3>Bündnisse (BNDs):</h3>
<?php printDiplomacyTable($diplomacy[group_diplomacy_bnd], 'BND', $rights['group_diplomacy'] == 1); ?>

<h3>Kriege:</h3>
<table class="Liste GroupExistingDiplomacy">
    <tr>
        <th>Gegner</th>
        <th>Kriegsbeginn</th>
        <th>Umkämpfter Betrag</th>
        <th>Aktion</th>
    </tr>
    <?php
    foreach ($diplomacy[group_diplomacy_war] as $row) {
        ?>
        <tr>
            <td><?= createGroupLink($row['GruppeID'], $row['GruppeName']); ?></td>
            <?php
            if ($row['Aktiv'] == 1) {
                ?>
                <td><?= formatDateTime(strtotime($row['Seit'])); ?></td>
                <td><?= formatCurrency(2 * $row['Betrag']); ?></td>
                <td>
                    <?php
                    if ($rights['group_diplomacy'] == 1) {
                        ?>
                        <a href="/actions/gruppe.php?a=17&amp;id=<?= $row['ID']; ?>&amp;token=<?= $_SESSION['blm_xsrf_token']; ?>"
                           onclick="return confirm('Wollen Sie in dem Krieg mit <?= escapeForOutput($row['GruppeName']); ?> wirklich kapitulieren? Der umkämpfte Betrag (<?= formatCurrency(2 * $row['Betrag']); ?>) geht an den Gegner, jeder Ihrer Gruppenmitglieder verliert <?= formatPercent(Config::getFloat(Config::SECTION_GROUP, 'war_loose_points')); ?> seiner Punkte und <?= Config::getInt(Config::SECTION_GROUP, 'war_loose_plantage'); ?> Stufe(n) seiner Plantagen!')">Aufgeben</a>
                        <?php
                    } else {
                        echo 'Keine Rechte';
                    }
                    ?>
                </td>
                <?php
            } else {
                ?>
                <td>- noch nicht aktiv-</td>
                <td><?= formatCurrency($row['Betrag']); ?></td>
                <td>-</td>
                <?php
            }
            ?>
        </tr>
        <?php
    }

    if (count($diplomacy[group_diplomacy_war]) == 0) {
        echo '<tr><td colspan="4" class="center"><i>Keine Einträge vorhanden</i></td></tr>';
    }
    ?>
</table>

<?php
if ($rights['group_diplomacy'] == 1) {
    $typ = getOrDefault($_GET, 'typ', 0);
    $amount = getOrDefault($_GET, 'amount', Config::getInt(Config::SECTION_GROUP, 'war_min_amount'));
    ?>
    <h3>Neue diplomatische Anfrage stellen</h3>
    <div class="form GroupNewDiplomacy">
        <header>Anfrage</header>
        <form action="/actions/gruppe.php" method="post">
            <input type="hidden" name="a" value="18"/>
            <div>
                <label for="typ">Typ:</label>
                <select name="typ" id="typ">
                    <option value="<?= group_diplomacy_nap; ?>"<?= ($typ === group_diplomacy_nap ? ' selected' : ''); ?>>
                        Nichtangriffspakt
                    </option>
                    <option value="<?= group_diplomacy_bnd; ?>"<?= ($typ === group_diplomacy_bnd ? ' selected' : ''); ?>>
                        Bündnis
                    </option>
                    <option value="<?= group_diplomacy_war; ?>"<?= ($typ === group_diplomacy_war ? ' selected' : ''); ?>>
                        Krieg
                    </option>
                </select>
            </div>
            <div id="kriegBetrag">
                <label for="amount">Umkämpfter Betrag:</label>
                <input type="number" name="amount" id="amount" value="<?= $amount; ?>"
                       min="<?= Config::getInt(Config::SECTION_GROUP, 'war_min_amount'); ?>"/>
            </div>
            <div>
                <label for="group">Gruppe:</label>
                <input type="text" name="group" id="group" value="<?= escapeForOutput($gruppe); ?>"/>
            </div>
            <div>
                <input type="submit" value="Abschicken" id="send_diplomacy_offer"/>
            </div>
        </form>
    </div>

    <?php
    $data = Database::getInstance()->getAllPendingGroupDiplomacyById($rights['group_id']);
    ?>
    <h3>Offene fremde Anfragen</h3>
    <table class="Liste GroupOpenRequests" data-count="<?= count($data); ?>" id="IncomingRequests">
        <tr>
            <th>Typ</th>
            <th>Gruppe</th>
            <th>Aktion</th>
        </tr>
        <?php
        foreach ($data as $row) {
            ?>
            <tr data-id="<?= $row['ID']; ?>">
                <td><?= getGroupDiplomacyTypeName($row['Typ']); ?></td>
                <td><?= createGroupLink($row['VonId'], $row['VonName']); ?></td>
                <td>
                    <a id="accept_<?= $row['ID']; ?>"
                       href="/actions/gruppe.php?a=19&amp;id=<?= $row['ID']; ?>                    &amp;token=<?= $_SESSION['blm_xsrf_token']; ?>">Annehmen</a>
                    <a id="refuse_<?= $row['ID']; ?>"
                       href="/actions/gruppe.php?a=20&amp;id=<?= $row['ID']; ?>                    &amp;token=<?= $_SESSION['blm_xsrf_token']; ?>">Ablehnen</a>
                </td>
            </tr>
            <?php
        }
        if (count($data) == 0) {
            echo '<tr><td colspan="3" class="center"><i>Keine Einträge vorhanden</i></td></tr>';
        }
        ?>
    </table>
    <?php
}
?>

<script nonce="<?= getCspNonce(); ?>">
    let typElement = document.getElementById('typ');
    typElement.onchange = () => CheckKrieg(typElement);
    CheckKrieg(typElement);

    for (let cancelLink of document.getElementsByClassName('cancel_relation')) {
        let type = cancelLink.getAttribute('data-type');
        let partner = cancelLink.getAttribute('data-partner');
        cancelLink.onclick = () => confirm('Wollen Sie die ' + type + ' Beziehung mit "' + partner + '" wirklich kündigen?');
    }

    for (let retractLink of document.getElementsByClassName('retract_offer')) {
        let type = retractLink.getAttribute('data-type');
        let partner = retractLink.getAttribute('data-partner');
        retractLink.onclick = () => confirm('Wollen Sie die ' + type + ' Anfrage mit "' + partner + '" wirklich zurückziehen?');
    }
</script>
