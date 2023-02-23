<?php
/*
 * MIT Licence
 * Copyright (c) 2023 Simon Frankenberger
 *
 * Please see LICENCE.md for complete licence text.
 */

restrictSitter('Mafia');

$opponent = getOrDefault($_GET, 'opponent', 0);
$action = getOrDefault($_GET, 'action');
$level = getOrDefault($_GET, 'level');

$data = Database::getInstance()->getPlayerPointsAndMoneyAndNextMafiaAndGroupById($_SESSION['blm_user']);
if ($data['Punkte'] < Config::getFloat(Config::SECTION_MAFIA, 'min_points')) {
    redirectTo('/?p=index', 169, __LINE__);
}

$nextMafiaTs = $data['NextMafia'] === null ? 0 : strtotime($data['NextMafia']);
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
        Die Mafia kann erst ab <?= formatPoints(Config::getFloat(Config::SECTION_MAFIA, 'min_points')); ?> verwendet
        werden.
    </p>
    <?php
}
?>

<table class="Liste Mafia">
    <tr>
        <th>Aktion</th>
        <th>Wirkung</th>
        <th>Sperrzeit</th>
    </tr>
    <tr>
        <td>Spionage</td>
        <td>Sammelt Informationen über den Gegner</td>
        <td><?= (Config::getInt(Config::SECTION_MAFIA_ESPIONAGE, 'wait_time') / 60); ?> Minuten</td>
    </tr>
    <tr>
        <td>Raub</td>
        <td>Stiehlt dem Gegner
            zwischen <?= formatPercent(Config::getFloat(Config::SECTION_MAFIA_ROBBERY, 'min_rate')); ?>
            und <?= formatPercent(Config::getFloat(Config::SECTION_MAFIA_ROBBERY, 'max_rate')); ?> seines Bargeldes
        </td>
        <td><?= (Config::getInt(Config::SECTION_MAFIA_ROBBERY, 'wait_time') / 60); ?> Minuten</td>
    </tr>
    <tr>
        <td>Diebstahl</td>
        <td>Stiehlt dem Gegner
            zwischen <?= formatPercent(Config::getFloat(Config::SECTION_MAFIA_HEIST, 'min_rate')); ?>
            und <?= formatPercent(Config::getFloat(Config::SECTION_MAFIA_HEIST, 'max_rate')); ?>
            seiner Waren aus dem Lager
        </td>
        <td><?= (Config::getInt(Config::SECTION_MAFIA_HEIST, 'wait_time') / 60); ?> Minuten</td>
    </tr>
    <tr>
        <td>Anschlag</td>
        <td>Zerstört die Plantage des Gegners, senkt das Gebäudelevel um eine Stufe</td>
        <td><?= (Config::getInt(Config::SECTION_MAFIA_ATTACK, 'wait_time') / 60); ?> Minuten</td>
    </tr>
</table>

<div class="form MafiaNewAction">
    <form action="/actions/mafia.php" method="post">
        <header>Angriff ausführen</header>
        <div>
            <label for="opponent">Gegner</label>
            <?= createPlayerDropdownForMafia($opponent, $data['Punkte'], $_SESSION['blm_user'], $data['Gruppe']); ?>
        </div>
        <div>
            <label for="action">Aktion</label>
            <select name="action" id="action">
                <option value="<?= mafia_action_espionage; ?>"<?= ($action == mafia_action_espionage ? ' selected' : ''); ?>>
                    Spionage
                </option>
                <option value="<?= mafia_action_robbery; ?>"<?= ($action == mafia_action_robbery ? ' selected' : ''); ?>>
                    Raub
                </option>
                <option value="<?= mafia_action_heist; ?>"<?= ($action == mafia_action_heist ? ' selected' : ''); ?>>
                    Diebstahl
                </option>
                <option value="<?= mafia_action_attack; ?>"<?= ($action == mafia_action_attack ? ' selected' : ''); ?>>
                    Anschlag
                </option>
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
            <input type="submit" value="Angriff!" id="attack"/>
        </div>
    </form>
</div>
<script nonce="<?= getCspNonce(); ?>">
    // add handler for action selection
    let actionElement = document.getElementById('action');
    actionElement.oninput = () => MafiaActionChange();

    // cost and duration data for mafia actions
    let mafia_cost_data = <?=json_encode(array(
        mafia_action_espionage => Config::getSection(Config::SECTION_MAFIA_ESPIONAGE),
        mafia_action_robbery => Config::getSection(Config::SECTION_MAFIA_ROBBERY),
        mafia_action_heist => Config::getSection(Config::SECTION_MAFIA_HEIST),
        mafia_action_attack => Config::getSection(Config::SECTION_MAFIA_ATTACK),
    ));?>;

    // calculate cost dropdown values
    MafiaActionChange();
</script>
