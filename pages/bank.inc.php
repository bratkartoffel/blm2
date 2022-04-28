<?php
restrictSitter('Bank');

$data = Database::getInstance()->getPlayerNameAndBankAndMoneyAndGroupById($_SESSION['blm_user']);
$art = getOrDefault($_GET, 'art', 1);
$betrag = getOrDefault($_GET, 'betrag', .0);
$interestRates = calculateInterestRates();
?>
<div id="SeitenUeberschrift">
    <img src="/pics/big/bank.png" alt=""/>
    <span>Bank<?= createHelpLink(1, 9); ?></span>
</div>

<?= getMessageBox(getOrDefault($_GET, 'm', 0)); ?>

<p>
    Hier können Sie Ihr verdientes Geld anlegen oder auch Kredite aufnehmen, wenn es mal knapp wird. Die Zinsen
    werden jeden Tag neu ausgerechnet. Gebucht werden die Zinsen alle <?= cron_interval; ?> Minuten. Die
    maximale Summe, die Sie anlegen können, sind <?= formatCurrency(deposit_limit); ?>, Ihr Kreditlimit sind
    <span style="color: red;"><?= formatCurrency(credit_limit); ?></span>.
</p>
<p>
    <span style="color: red;">Wichtig! Falls der Kontostand unter <?= formatCurrency(dispo_limit) ?> fällt, wird Ihr Account automatisch resettet!</span>
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
            <select name="art" id="art" onchange="AuswahlBank(this.value); return true;">
                <option value="1"<?= ($art == 1 ? ' selected="selected"' : ''); ?> id="ac_deposit">Einzahlen</option>
                <option value="2"<?= ($art == 2 ? ' selected="selected"' : ''); ?> id="ac_withdraw">Auszahlen</option>
                <?= ($data['Gruppe'] != null ? '<option value="3"' . ($art == 3 ? ' selected="selected"' : '') . '>In die Gruppenkasse</option>' : ''); ?>
            </select>
        </div>
        <div>
            <label for="betrag">Betrag:</label>
            <input type="text" name="betrag" id="betrag" size="9"
                   value="<?= formatCurrency($betrag, false, false); ?>"/> €
        </div>
        <div>
            <input type="submit" value="Bestätigen" id="do_transaction" onclick="return submit(this);"/>
        </div>
    </form>
</div>

<script type="text/javascript">
    function AuswahlBank(option) {
        const Leer = '<?=formatCurrency(0, false); ?>';
        const KontostandAusgabe = '<?=formatCurrency($data['Bank'], false, false); ?>';
        const BargeldAusgabe = '<?=formatCurrency($data['Geld'], false, false); ?>';
        const Zeiger = document.form_bank.betrag;

        if (Zeiger.value === Leer || Zeiger.value === KontostandAusgabe || Zeiger.value === BargeldAusgabe) {
            if (option === "1" || option === "3") {
                Zeiger.value = BargeldAusgabe;
            } else {
                Zeiger.value = KontostandAusgabe;
            }
        }
    }

    AuswahlBank("<?=$art;?>");
</script>
