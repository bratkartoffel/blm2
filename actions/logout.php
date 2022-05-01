<?php
require_once('../include/config.inc.php');
require_once('../include/functions.inc.php');

ob_start();

$deleted = getOrDefault($_GET, 'deleted', 0);
$popup = getOrDefault($_GET, 'popup', 0);

session_destroy();

if ($popup == 1) {
    die('<script>self.close();</script>');
}

redirectTo('/?p=anmelden', $deleted == 1 ? 205 : 203);
