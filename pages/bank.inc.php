<?php
restrictSitter('Bank');

$art = getOrDefault($_GET, 'art', 1);
$betrag = getOrDefault($_GET, 'betrag', .0);
?>
<table id="SeitenUeberschrift">
    <tr>
        <td><img src="/pics/big/bank.png" alt=""/></td>
        <td>Die Bank <a href="./?p=hilfe&amp;mod=1&amp;cat=9"><img src="/pics/help.gif" alt="Hilfe"/></a></td>
    </tr>
</table>

<?= CheckMessage(getOrDefault($_GET, 'm', 0)); ?>

<h3>
    <p>
        Hier können Sie Ihr verdientes Geld anlegen oder auch Kredite aufnehmen, wenn es mal knapp wird. Die Zinsen
        werden jeden Tag neu ausgerechnet. Gebucht werden die Zinsen alle <?php echo(ZINSEN_DAUER / 60); ?> Minuten. Die
        maximale Summe, die Sie anlegen können, sind <?= formatCurrency(DEPOSIT_LIMIT); ?>, Ihr Kreditlimit sind
        <span style="color: red;"><?= formatCurrency(CREDIT_LIMIT); ?></span>.
    </p>
    <p>
        <span style="color: red;">Wichtig! Falls der Kontostand unter <?= formatCurrency(DISPO_LIMIT) ?> fällt, wird Ihr Account automatisch resettet!</span>
    </p>
    <p>
        Die aktuellen Anlagenzinsen: <?= formatCurrency(($ZinsenAnlage - 1) * 100, false); ?> %<br/>
        Die aktuellen Kreditzinsen: <?= formatCurrency(($ZinsenKredit - 1) * 100, false); ?> %
    </p>
</h3>
<h2>Ihr Kontostand: <?php echo formatCurrency($ich->Bank); ?></h2>

<form action="/actions/bank.php" method="post" name="form_bank">
    <table class="Liste" style="width: 250px">
        <tr>
            <th colspan="2">Transaktion durchführen</th>
        </tr>
        <tr>
            <td style="text-align: right;"><label for="art">Art:</label></td>
            <td>
                <select name="art" id="art" onchange="AuswahlBank(this.value); return true;">
                    <option value="1"<?= ($art == 1 ? ' selected="selected"' : ''); ?>>Einzahlen</option>
                    <option value="2"<?= ($art == 2 ? ' selected="selected"' : ''); ?>>Auszahlen</option>
                    <?= ($ich->Gruppe != null ? '<option value="3"' . ($art == 3 ? ' selected="selected"' : '') . '>In die Gruppenkasse</option>' : ''); ?>
                </select>
            </td>
        </tr>
        <tr>
            <td style="text-align: right;"><label for="betrag">Betrag:</label></td>
            <td><input type="text" name="betrag" id="betrag" size="9" value="<?= formatCurrency($betrag, false); ?>"/> €
            </td>
        </tr>
        <tr>
            <td colspan="2" style="text-align: center;">
                <input type="submit" value="Bestätigen"
                       onclick="this.form.submit(); this.disabled = 'disabled'; this.value = 'Bitte warten...'; return false;"
                />
            </td>
        </tr>
    </table>
</form>

<script type="text/javascript">
    function AuswahlBank(option) {
        const Leer = '<?=formatCurrency(0, false); ?>';
        const KontostandAusgabe = '<?=formatCurrency($ich->Bank, false); ?>';
        const BargeldAusgabe = '<?=formatCurrency($ich->Geld, false); ?>';
        const Zeiger = document.form_bank.betrag;

        if (Zeiger.value === Leer || Zeiger.value === KontostandAusgabe || Zeiger.value === BargeldAusgabe) {
            if (option === "1" || option === "3") {
                Zeiger.value = BargeldAusgabe;
            } else {
                Zeiger.value = KontostandAusgabe;
            }
        }
    }

    AuswahlBank("1");
</script>
