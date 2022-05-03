<?php
restrictSitter('Gruppe');

$gruppe = getOrDefault($_GET, 'gruppe');
$rights = Database::getInstance()->getGroupRightsByUserId($_SESSION['blm_user']);
requireEntryFound($rights, '/?p=gruppe');

$diplomacy_db = Database::getInstance()->getAllGroupDiplomacyById($rights['group_id']);
$diplomacy = array(group_diplomacy_bnd => array(), group_diplomacy_nap => array(), group_diplomacy_war => array());
foreach ($diplomacy_db as $entry) {
    $diplomacy[intval($entry['Typ'])][] = $entry;
}

function printDiplomacyTable($diplomacy, $name, $hasRights)
{
    ?>
    <table class="Liste">
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
                    echo sprintf('<td>%s</td>', formatDateTime(strtotime($row['Seit'])));
                    if ($hasRights) {
                        echo sprintf('<td><a href="/actions/gruppe.php?a=15&amp;id=%d&amp;token=%s"
                                onclick="return confirm(\'Wollen Sie die %s Beziehung mit %s wirklich kündigen?\')">Kündigen</a></td>',
                            $row['ID'], $_SESSION['blm_xsrf_token'], $name, escapeForOutput($row['GruppeName']));
                    } else {
                        echo '<td>Keine Rechte</td>';
                    }
                } else {
                    echo '<td>- noch nicht aktiv -</td>';
                    if ($hasRights) {
                        echo sprintf('<td><a href="/actions/gruppe.php?a=16&amp;id=%d&amp;token=%s"
                           onclick="return confirm(\'Wollen Sie die %s Anfrage an %s wirklich zurückziehen?\')">Zurückziehen</a></td>',
                            $row['ID'], $_SESSION['blm_xsrf_token'], $name, escapeForOutput($row['GruppeName']));
                    } else {
                        echo '<td>Keine Rechte</td>';
                    }
                }
                ?>
            </tr>
            <?php
        }

        if (count($diplomacy) == 0) {
            echo '<tr><td colspan="3" style="text-align: center;"><i>Keine Einträge vorhanden</i></td></tr>';
        }
        ?>
    </table>
    <?php
}

?>
<div id="SeitenUeberschrift">
    <img src="/pics/big/Community_Help.png" alt=""/>
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
                <td><?= formatCurrency($row['Betrag']); ?></td>
                <td>
                    <?php
                    if ($rights['group_diplomacy'] == 1) {
                        ?>
                        <a href="/actions/gruppe.php?a=17&amp;id=<?= $row['ID']; ?>&amp;token=<?= $_SESSION['blm_xsrf_token']; ?>"
                           onclick="return confirm('Wollen Sie in dem Krieg mit <?= escapeForOutput($row['GruppeName']); ?> wirklich kapitulieren? Der umkämpfte Betrag (<?= formatCurrency($row['Betrag']); ?>) geht an den Gegner, jeder Ihrer Gruppenmitglieder verliert <?= formatPercent(group_war_loose_points); ?> seiner Punkte und <?= group_war_loose_plantage; ?> Stufe(n) seiner Plantagen!')">Aufgeben</a>
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
                <td>
                    <?php
                    if ($rights['group_diplomacy'] == 1) {
                        ?>
                        <a href="/actions/gruppe.php?a=16&amp;id=<?= $row['ID']; ?>&amp;token=<?= $_SESSION['blm_xsrf_token']; ?>"
                           onclick="return confirm('Wollen Sie die Kriegserklärung an <?= escapeForOutput($row['GruppeName']); ?> wirklich zurückziehen?')">Zurückziehen</a>
                        <?php
                    } else {
                        echo 'Keine Rechte';
                    }
                    ?>
                </td>
                <?php
            }
            ?>
        </tr>
        <?php
    }

    if (count($diplomacy[group_diplomacy_war]) == 0) {
        echo '<tr><td colspan="4" style="text-align: center;"><i>Keine Einträge vorhanden</i></td></tr>';
    }
    ?>
</table>

<?php
if ($rights['group_diplomacy'] == 1) {
    $typ = getOrDefault($_GET, 'typ', 0);
    $amount = getOrDefault($_GET, 'amount', group_war_min_amount);
    ?>
    <h3>Neue diplomatische Anfrage stellen</h3>
    <div class="form GroupNewDiplomacy">
        <header>Anfrage</header>
        <form action="/actions/gruppe.php" method="post">
            <input type="hidden" name="a" value="18"/>
            <div>
                <label for="typ">Typ:</label>
                <select name="typ" id="typ" onchange="CheckKrieg(this);">
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
                <input type="number" name="amount" id="amount" value="<?= formatCurrency($amount, false, false); ?>"
                       min="<?= group_war_min_amount; ?>"/>
            </div>
            <div>
                <label for="group">Gruppe:</label>
                <input type="text" name="group" id="group" value="<?= escapeForOutput($gruppe); ?>"/>
            </div>
            <div>
                <input type="submit" value="Abschicken" onclick="return submit(this);"/>
            </div>
        </form>
    </div>
    <script>CheckKrieg(document.getElementById('typ'));</script>

    <h3>Offene fremde Anfragen</h3>
    <table class="Liste GroupOpenRequests">
        <tr>
            <th>Typ</th>
            <th>Gruppe</th>
            <th>Aktion</th>
        </tr>
        <?php
        $data = Database::getInstance()->getAllPendingGroupDiplomacyById($rights['group_id']);
        foreach ($data as $row) {
            ?>
            <tr>
                <td><?= getGroupDiplomacyTypeName($row['Typ']); ?></td>
                <td><?= createGroupLink($row['VonId'], $row['VonName']); ?></td>
                <td>
                    <a href="/actions/gruppe.php?a=19&amp;id=<?= $row['ID']; ?>">Annehmen</a>
                    <a href="/actions/gruppe.php?a=20&amp;id=<?= $row['ID']; ?>">Ablehnen</a>
                </td>
            </tr>
            <?php
        }
        if (count($data) == 0) {
            echo '<tr><td colspan="3" style="text-align: center;"><i>Keine Einträge vorhanden</i></td></tr>';
        }
        ?>
    </table>
    <?php
}
?>

