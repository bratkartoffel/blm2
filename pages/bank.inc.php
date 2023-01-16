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
?>
<div id="SeitenUeberschrift">
    <img src="/pics/big/kwallet.webp" alt=""/>
    <span>Bank<?= createHelpLink(1, 9); ?></span>
</div>

<?= getMessageBox(getOrDefault($_GET, 'm', 0)); ?>

<p>
    Hier können Sie Ihr verdientes Geld anlegen oder auch Kredite aufnehmen, wenn es mal knapp wird. Die Zinsen
    werden jeden Tag neu ausgerechnet. Gebucht werden die Zinsen
    alle <?= Config::getInt(Config::SECTION_BASE, 'cron_interval'); ?> Minuten. Die
    maximale Summe, die Sie anlegen können,
    sind <?= formatCurrency(Config::getInt(Config::SECTION_BANK, 'deposit_limit')); ?>, Ihr Kreditlimit sind
    <span style="color: red;"><?= formatCurrency(Config::getInt(Config::SECTION_BANK, 'credit_limit')); ?></span>.
</p>
<p>
    <span style="color: red;">Wichtig! Falls der Kontostand unter <?= formatCurrency(Config::getInt(Config::SECTION_BANK, 'dispo_limit')) ?> fällt, wird Ihr Account automatisch resettet!
        Von einer durchschnittlichen Zinsrate von 2% ausgehend wird diese Marke nach etwa 48 Stunden überschritten.</span>
</p>
<p>
    Die aktuellen Anlagenzinsen: <?= formatPercent($interestRates['Debit']); ?><br/>
    Die aktuellen Kreditzinsen: <?= formatPercent($interestRates['Credit']); ?>
</p>
<h3 id="cur_bank_account">Ihr Kontostand: <?php echo formatCurrency($data['Bank']); ?></h3>

<div class="form Bank">
    <form action="/actions/bank.php" method="post" name="form_bank">
        <header>Transaktion durchführen</header>
        <div>
            <label for="art">Art:</label>
            <div class="inline-block">
                <input type="radio" id="einzahlen" name="art" value="1"
                       onchange="AuswahlBank(this.value);" <?= ($art == 1 ? 'checked' : ''); ?>>
                <label for="einzahlen">Einzahlen</label><br>
                <input type="radio" id="auszahlen" name="art" value="2"
                       onchange="AuswahlBank(this.value);" <?= ($art == 2 ? 'checked' : ''); ?>>
                <label for="auszahlen">Auszahlen</label><br>
                <?php
                if ($data['Gruppe'] != null) {
                    ?>
                    <input type="radio" id="gruppen_kasse" name="art" value="3"
                           onchange="AuswahlBank(this.value);" <?= ($art == 3 ? 'checked' : ''); ?>>
                    <label for="gruppen_kasse">In die Gruppenkasse</label>
                    <?php
                }
                ?>
            </div>
        </div>
        <div>
            <label for="betrag">Betrag:</label>
            <input type="number" name="betrag" id="betrag" size="12" min="0" step="0.01" value="<?= $betrag; ?>"/> €
        </div>
        <div>
            <input type="submit" value="Bestätigen" id="do_transaction"/>
        </div>
    </form>
</div>

<script>
    function AuswahlBank(option) {
        const Zeiger = document.form_bank.betrag;
        const KontostandAusgabe = <?=$data['Bank'];?>;
        const BargeldAusgabe = <?=$data['Geld'];?>;
        const currentValue = Number.parseFloat(Zeiger.value);
        if (currentValue === 0.0 || currentValue === KontostandAusgabe || currentValue === BargeldAusgabe) {
            if (option === "1" || option === "3") {
                Zeiger.value = BargeldAusgabe;
            } else if (KontostandAusgabe >= 0) {
                Zeiger.value = KontostandAusgabe;
            }
        }
    }

    AuswahlBank("<?=$art;?>");
</script>
