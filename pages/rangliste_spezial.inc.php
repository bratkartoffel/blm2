<?php
$type = getOrDefault($_GET, 'type', 0);

$title = null;
$description = null;
$loader = function () {
};
$formatter = function ($row) {
};
switch ($type) {
    case 0:
        $title = 'Der Bioladenfreak';
        $description = 'Sobald ein Spieler dessen Namen hört, läuft ihm bereits ein kalter Schauer über den Rücken. <i>"Der, der nie schläft..."</i> wird über ihn gemunkelt. Er ist immer da und kann bei jedem Angriff sofort reagieren.';
        $loader = function () {
            return Database::getInstance()->getLeaderOnlineTime(5);
        };
        $formatter = function ($row) {
            return sprintf('<tr><td>%%d</td><td>%s</td><td>%s</td></tr>',
                createProfileLink($row['ID'], $row['Name']),
                formatDuration($row['Onlinezeit']));
        };
        break;

    case 1:
        $title = 'Der Pate';
        $description = 'Vor ihm erzittern alle Spieler. Er ist der Mann ohne Gnade, und wer sich ihm in den Weg stellt, wird einfach plattgemacht. Der Pate ist ein aggresiver Spieler, welcher jedes Vergehen gegen ihn sofort zurückzahlt.';
        $loader = function () {
            return Database::getInstance()->getLeaderMafia(5);
        };
        $formatter = function ($row) {
            return sprintf('<tr><td>%%d</td><td>%s</td><td>%s</td></tr>',
                createProfileLink($row['ID'], $row['Name']),
                formatCurrency($row['AusgabenMafia']));
        };
        break;

    case 2:
        $title = 'Der Händlerkönig';
        $description = 'Dieser Spieler ist im ganzen Lande hoch angesehen. Wenn er kommt, dann kann man sicher sein, dass seine Waren von ihm begutachtet und bei gutem Preis gleich gekauft werden. Niemand weiß, wohin er die Waren bringt, aber sein Lager muss Riesengroß sein...';
        $loader = function () {
            return Database::getInstance()->getLeaderMarket(5);
        };
        $formatter = function ($row) {
            return sprintf('<tr><td>%%d</td><td>%s</td><td>%s</td></tr>',
                createProfileLink($row['ID'], $row['Name']),
                formatCurrency($row['AusgabenMarkt']));
        };
        break;

    case 3:
        $title = 'Der Baumeister';
        $description = 'Alle Spieler erstarren beim ersten Anblick seines Imperiums. Er ist bekannt dafür, am Bau an nichts zu sparen, und so darf er die größten Gebäude des Spiels sein Eigen nennen.';
        $loader = function () {
            return Database::getInstance()->getLeaderBuildings(5);
        };
        $formatter = function ($row) {
            return sprintf('<tr><td>%%d</td><td>%s</td><td>%s</td></tr>',
                createProfileLink($row['ID'], $row['Name']),
                formatCurrency($row['AusgabenGebaeude']));
        };
        break;

    case 4:
        $title = 'Das Genie';
        $description = 'Dieser Spieler ist bekannt für seine verrückten Ideen. Dadurch ist es ihm gelangen, seine Gemüsesorten dermaßen hoch zu forschen, dass er von allen beneided wird. Seine Pflanzen sind die größten und schönsten im ganzen Land.';
        $loader = function () {
            return Database::getInstance()->getLeaderResearch(5);
        };
        $formatter = function ($row) {
            return sprintf('<tr><td>%%d</td><td>%s</td><td>%s</td></tr>',
                createProfileLink($row['ID'], $row['Name']),
                formatCurrency($row['AusgabenForschung']));
        };
        break;

    case 5:
        $title = 'Der Top-Bauer';
        $description = 'Der Top-Bauer ist ständig auf dem Feld anzutreffen. Er kümmert sich um die Pflanzen wie um seine eigenen Kinder. Er sorgt dafür, dass es ihnen an nichts mangelt. Er züchtet die grössten Früchte und seine produzierten Mengen können ein kleines Land ernähren.';
        $loader = function () {
            return Database::getInstance()->getLeaderProduction(5);
        };
        $formatter = function ($row) {
            return sprintf('<tr><td>%%d</td><td>%s</td><td>%s</td></tr>',
                createProfileLink($row['ID'], $row['Name']),
                formatCurrency($row['AusgabenProduktion']));
        };
        break;

    case 6:
        $title = 'Der Kapitalist';
        $description = 'Der Kapitalist ist der größte Schrecken der Banken. Durch geschicktes Anlegen seines Geldes hat er schon die eine oder andere Bank in den Ruin getrieben, so munkelt man. Er ist immer auf der Suche nach den besten Zinsen und nie lange bei einer Bank...';
        $loader = function () {
            return Database::getInstance()->getLeaderInterest(5);
        };
        $formatter = function ($row) {
            return sprintf('<tr><td>%%d</td><td>%s</td><td>%s</td></tr>',
                createProfileLink($row['ID'], $row['Name']),
                formatCurrency($row['EinnahmenZinsen']));
        };
        break;

    case 7:
        $title = 'Der Mitteilunsbedürftige';
        $description = 'Der Spieler kommt nie zu Ruhe. Immer hat er irgendwas zu schreiben dabei. Sei es auch nur eine Kleinigkeit, so muss er es trotzdem jedem mitteilen. Sein Postfach läuft über, und der Postbote kommt nicht mehr mit der Ausstellung seiner Briefe nach.';
        $loader = function () {
            return Database::getInstance()->getLeaderIgmSent(5);
        };
        $formatter = function ($row) {
            return sprintf('<tr><td>%%d</td><td>%s</td><td>%s</td></tr>',
                createProfileLink($row['ID'], $row['Name']),
                formatPoints($row['IgmGesendet']));
        };
        break;

    default:
        redirectTo('/?p=rangliste', 112, __LINE__);
        break;
}
?>

<div id="SeitenUeberschrift">
    <img src="/pics/big/Login_Manager.webp" alt=""/>
    <span>Die Spezial-Rangliste<?= createHelpLink(1, 17); ?></span>
</div>

<?= getMessageBox(getOrDefault($_GET, 'm', 0)); ?>

<h2><?= $title; ?></h2>
<p><?= $description; ?></p>

<table class="Liste Rangliste RanglisteSpezial">
    <tr>
        <th>Platz</th>
        <th>Spieler</th>
        <th>Wert</th>
    </tr>
    <?php
    $nr = 1;
    $entries = $loader();
    foreach ($entries as $row) {
        echo sprintf($formatter($row), $nr++);
    }
    ?>
</table>

<div>
    <a href="/?p=rangliste">&lt;&lt; Zurück</a>
</div>
