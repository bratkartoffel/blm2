<?php
restrictSitter('Gruppe');

$receiver = getOrDefault($_GET, 'receiver');
$amount = getOrDefault($_GET, 'amount', .0);
$rights = Database::getInstance()->getGroupRightsByUserId($_SESSION['blm_user']);
requireEntryFound($rights, '/?p=gruppe');
?>
    <div id="SeitenUeberschrift">
        <img src="/pics/big/Community_Help.png" alt=""/>
        <span>Gruppe - Kasse<?= createHelpLink(1, 23); ?></span>
    </div>

<?= getMessageBox(getOrDefault($_GET, 'm', 0)); ?>
<?= createGroupNaviation(4, $rights['group_id']); ?>

<?php
$data = Database::getInstance()->getAllGroupCashById($rights['group_id']);
requireEntryFound($data, '/?p=gruppe', __LINE__);
$users = array();
?>
    <div class="form GruppeKasse">
        <header>Kassenstände der Mitglieder</header>
        <?php
        $sum = 0;
        foreach ($data as $entry) {
            $sum += $entry['amount'];
            if ($entry['IstMitglied'] === "1") {
                $users[$entry['UserName']] = $entry['UserID'];
            }
            ?>
            <div>
                <label><?php
                    echo createProfileLink($entry['UserID'], $entry['UserName']);
                    if ($entry['IstMitglied'] !== "1" && $entry['UserID'] !== null) {
                        echo ' (ausgetreten)';
                    }
                    ?></label>
                <span id="gk_m_<?= $entry['UserID']; ?>"><?= formatCurrency($entry['amount']); ?></span>
            </div>
            <?php
        }
        ?>
        <div>
            <label>Bilanz:</label>
            <span><?= formatCurrency($sum); ?></span>
        </div>
    </div>

<?php
$kassenstand = Database::getInstance()->getGroupCashById($rights['group_id']);
if ($amount == 0) $amount = $kassenstand;
?>
    <h3 id="gk_amount">In der Kasse befinden sich: <?= formatCurrency($kassenstand); ?></h3>

<?php
if ($rights['group_cash'] == 1) {
    ?>
    <div class="form GruppeKasseAction">
        <form action="/actions/gruppe.php" method="post">
            <input type="hidden" name="a" value="8"/>
            <header>Auszahlen</header>
            <div>
                <label for="receiver">Empfänger</label>
                <select name="receiver" id="receiver">
                    <?php
                    foreach ($users as $name => $id) {
                        echo sprintf('<option value="%d"%s>%s</option>', $id, $receiver == $id ? ' selected' : '', escapeForOutput($name));
                    }
                    ?>
                </select>
            </div>
            <div>
                <label for="amount">Betrag</label>
                <input type="number" min="0" step="0.01" name="amount" id="amount"
                       value="<?= formatCurrency($amount, false, false); ?>"/>
            </div>
            <div>
                <input type="submit" value="Überweisen" id="gk_transfer" onclick="return submit(this);"/>
            </div>
        </form>
    </div>
    <?php
}
