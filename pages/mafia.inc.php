<?php
restrictSitter('Mafia');

$opponent = getOrDefault($_GET, 'opponent', 0);
$action = getOrDefault($_GET, 'action');
$level = getOrDefault($_GET, 'level');

$data = Database::getInstance()->getPlayerPointsAndMoneyAndNextMafiaAndGroupById($_SESSION['blm_user']);
if ($data['Punkte'] < mafia_min_ponts) {
    redirectTo('/?p=index', 169, __LINE__);
}

$nextMafiaTs = strtotime($data['NextMafia']);
if ($nextMafiaTs <= time()) {
    $nextMafia = 'Sofort';
} else {
    $nextMafia = '<span class="countdown">' . formatDuration($nextMafiaTs - time()) . '</span>';
}

?>
<div id="SeitenUeberschrift">
    <img src="/pics/big/core.webp" alt=""/>
    <span>Mafia<?= createHelpLink(1, 12); ?></span>
</div>

<?= getMessageBox(getOrDefault($_GET, 'm', 0)); ?>

<p>
    Hier finden Sie Jungs, die für Sie die Konkurrenz in Bedrängnis bringen und die Drecksarbeit für
    Sie erledigen.<br/>
    Der nächste Einsatz der Mafia ist möglich: <b><?= $nextMafia; ?></b>
</p>

<?php
if (!mafiaRequirementsMet($data['Punkte'])) {
    ?>
    <p>
        Die Mafia kann erst ab <?= formatPoints(mafia_min_ponts); ?> verwendet werden.
    </p>
    <?php
}
?>

<table class="Liste Mafia">
    <tr>
        <th>Aktion</th>
        <th>Wirkung</th>
        <th>Sperrzeit</th>
        <th>Punkte</th>
    </tr>
    <tr>
        <td>Spionage</td>
        <td>Sammelt Informationen über den Gegner</td>
        <td><?= (mafia_sperrzeit_spionage / 60); ?> Minuten</td>
        <td><?= formatPoints(mafia_base_data[0]['points']); ?></td>
    </tr>
    <tr>
        <td>Raub</td>
        <td>Stiehlt dem Gegner zwischen <?= formatPercent(mafia_raub_min_rate); ?>
            und <?= formatPercent(mafia_raub_max_rate); ?> seines Bargeldes
        </td>
        <td><?= (mafia_sperrzeit_raub / 60); ?> Minuten</td>
        <td><?= formatPoints(mafia_base_data[1]['points']); ?></td>
    </tr>
    <tr>
        <td>Diebstahl</td>
        <td>Stiehlt dem Gegner alle Waren aus dem Lager</td>
        <td><?= (mafia_sperrzeit_diebstahl / 60); ?> Minuten</td>
        <td><?= formatPoints(mafia_base_data[2]['points']); ?></td>
    </tr>
    <tr>
        <td>Anschlag</td>
        <td>Zerstört die Plantage des Gegners, senkt das Gebäudelevel um eine Stufe</td>
        <td><?= (mafia_sperrzeit_bomben / 60); ?> Minuten</td>
        <td><?= formatPoints(mafia_base_data[3]['points']); ?></td>
    </tr>
</table>

<script>
    let mafia_cost_data = <?=json_encode(mafia_base_data); ?>;
</script>
<div class="form MafiaNewAction">
    <form action="/actions/mafia.php" method="post">
        <header>Angriff ausführen</header>
        <div>
            <label for="opponent">Gegner</label>
            <?=createPlayerDropdownForMafia($opponent, $data['Punkte'], $_SESSION['blm_user'], $data['Gruppe']); ?>
        </div>
        <div>
            <label for="action">Aktion</label>
            <select name="action" id="action" oninput="MafiaActionChange();">
                <option value="<?= mafia_action_espionage; ?>"<?= ($action == 0 ? ' selected' : ''); ?>>Spionage
                </option>
                <option value="<?= mafia_action_robbery; ?>"<?= ($action == 1 ? ' selected' : ''); ?>>Raub</option>
                <option value="<?= mafia_action_heist; ?>"<?= ($action == 2 ? ' selected' : ''); ?>>Diebstahl</option>
                <option value="<?= mafia_action_attack; ?>"<?= ($action == 3 ? ' selected' : ''); ?>>Anschlag</option>
            </select>
        </div>
        <div>
            <label for="level">Kosten / Chance</label>
            <select name="level" id="level">
                <option value="0"<?= ($level == 0 ? ' selected' : ''); ?>>x € / x%</option>
                <option value="1"<?= ($level == 1 ? ' selected' : ''); ?>>x € / x%</option>
                <option value="2"<?= ($level == 2 ? ' selected' : ''); ?>>x € / x%</option>
                <option value="3"<?= ($level == 3 ? ' selected' : ''); ?>>x € / x%</option>
            </select>
        </div>
        <div>
            <input type="submit" value="Angriff!" id="attack" onclick="return submit(this);"/>
        </div>
    </form>
</div>
<script>
    MafiaActionChange();
</script>
