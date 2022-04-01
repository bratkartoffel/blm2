<?php
include("../include/functions.inc.php");

$deleted = getOrDefault($_GET, 'deleted', 0);
$popup = getOrDefault($_GET, 'popup', 0);

session_destroy();

if ($popup == 1) {
    die('<script type="text/javascript">self.close();</script>');
}

redirectTo('../?p=anmelden', $deleted == 1 ? 205 : 203);
