<?php
/*
 * MIT Licence
 * Copyright (c) 2023 Simon Frankenberger
 *
 * Please see LICENCE.md for complete licence text.
 */

restrictSitter('Bank');

$data = Database::getInstance()->getPlayerNameAndBankAndMoneyAndGroupById($_SESSION['blm_user']);
$art = getOrDefault($_GET, 'art', 1);
$betrag = getOrDefault($_GET, 'betrag', .0);
$interestRates = calculateInterestRates();
$depositLimit = calculateDepositLimit($data['Gebaeude' . building_bank]);

$resetMedianRates = (Config::getFloat(Config::SECTION_BANK, 'interest_credit_rate_min') + Config::getFloat(Config::SECTION_BANK, 'interest_credit_rate_max')) / 2;
$resetCreditLimit = calculateResetCreditLimit($data['Gebaeude' . building_bank]);

?>
<div id="SeitenUeberschrift">
    <img src="./pics/big/kwallet.webp" alt=""/>
    <span>Bank<?= createHelpLink(1, 9); ?></span>
</div>

<?= getMessageBox(getOrDefault($_GET, 'm', 0)); ?>

<p>
    Hier können Sie Ihr verdientes Geld anlegen oder auch Kredite aufnehmen, wenn es mal knapp wird. Die Zinsen
    werden jeden Tag neu ausgerechnet. Gebucht werden die Zinsen
    alle <?= Config::getInt(Config::SECTION_BASE, 'cron_interval'); ?> Minuten. Die
    maximale Summe, die Sie anlegen können,
    sind <?= formatCurrency($depositLimit); ?>, Ihr Kreditlimit sind
    <span class="red"><?= formatCurrency(calculateCreditLimit($data['Gebaeude' . building_bank])); ?></span>.
</p>
<p>
    <span class="red">Wichtig! Falls der Kontostand unter <?= formatCurrency($resetCreditLimit); ?> fällt, wird Ihr Account automatisch resettet!
        Von einer durchschnittlichen Zinsrate von <?= formatPercent($resetMedianRates); ?> ausgehend wird diese Marke nach etwa 96 Stunden überschritten.</span>
</p>
<p>
    Die aktuellen Anlagenzinsen: <?= formatPercent($interestRates['Debit']); ?><br/>
    Die aktuellen Kreditzinsen: <?= formatPercent($interestRates['Credit']); ?>
</p>
<h3 id="cur_bank_account">Ihr Kontostand: <?php echo formatCurrency($data['Bank']); ?></h3>

<div class="form Bank">
    <form action="./actions/bank.php" method="post" name="form_bank" id="form_bank">
        <header>Transaktion durchführen</header>
        <div>
            <label for="art">Art:</label>
            <div class="inline-block">
                <input type="radio" id="einzahlen" name="art" value="1" <?= ($art == 1 ? 'checked' : ''); ?>>
                <label for="einzahlen">Einzahlen</label><br>
                <input type="radio" id="auszahlen" name="art" value="2" <?= ($art == 2 ? 'checked' : ''); ?>>
                <label for="auszahlen">Auszahlen</label><br>
                <?php
                if ($data['Gruppe'] != null) {
                    ?>
                    <input type="radio" id="gruppen_kasse" name="art" value="3" <?= ($art == 3 ? 'checked' : ''); ?>>
                    <label for="gruppen_kasse">In die Gruppenkasse</label>
                    <?php
                }
                ?>
            </div>
        </div>
        <div>
            <label for="betrag">Betrag:</label>
            <input type="number" name="betrag" id="betrag" size="12" min="0" step="0.01"
                   value="<?= $betrag; ?>" data-bank="<?= $data['Bank']; ?>"
                   data-geld="<?= $data['Geld']; ?>" data-deposit-limit="<?= $depositLimit; ?>"
            /> €
        </div>
        <div>
            <input type="submit" value="Bestätigen" id="do_transaction"/>
        </div>
    </form>
</div>
