<?php
/**
 * Javascript Funktionen bezüglich eines Specials
 *
 * @version 1.0.0
 * @author Simon Frankenberger <simonfrankenberger@web.de>
 * @package blm2.includes
 */
require_once("../include/config.inc.php");
require_once("../include/functions.inc.php");

if (!SPECIAL_RUNNING) {
    die();
}

srand(time() + microtime());

$rand = rand(0, 100);

if($rand <= 25) {
$hash = sha1($_SESSION['blm_user'] . date("sdmYHi") . rand(0, 1337) . DB_PASSWORT);

ConnectDB();
$sql_abfrage = "SELECT
    UNIX_TIMESTAMP(Wann) AS Wann
FROM
    special
WHERE
    Wer='" . $_SESSION['blm_user'] . "'
;";
$sql_ergebnis = mysql_query($sql_abfrage);

$last = mysql_fetch_object($sql_ergebnis);
$last = $last->Wann;

if(time() - intval($last) > 3600) {
$sql_abfrage = "INSERT INTO
    special
(
    Wer,
    Wann,
    Hash,
    Abgeholt
)
VALUES
(
    '" . $_SESSION['blm_user'] . "',
    NOW(),
    '" . $hash . "',
    0
);";
$sql_ergebnis = mysql_query($sql_abfrage);

DisconnectDB();

srand(microtime() + time());
?>
var z = document.getElementById('weihnachtsspecial');

z.width = '40';
z.height = '40';
z.style.display = 'block';
z.style.left = '<?=rand(200, 800); ?>px';
z.style.top = '<?=rand(5, 50); ?>px';

z.innerHTML = '<a href="actions/special.php?hash=<?=$hash; ?>"><img src="pics/weihnachten.png" width="32" height="32" alt="Special" style="border: none;" /></a>';
<?php
}
}
