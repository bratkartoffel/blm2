<?php
require_once('../include/config.inc.php');
require_once('../include/functions.inc.php');

ob_start();
session_destroy();

if (getOrDefault($_GET, 'popup', 0) == 1) {
    die('<script>self.close();</script>');
}

redirectTo('/?p=anmelden', getOrDefault($_GET, 'deleted', 0) == 1 ? 205 : 203);
