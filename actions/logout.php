<?php
include("../include/functions.inc.php");

session_destroy();

if (isset($_GET['popup'])) {        // Das Script wurde vom Popup aufgerufen, das heisst, das Popup soll geschlossen werden
    echo '<script type="text/javascript">self.close();</script>';
    die();
}

redirectTo('../?p=anmelden', getOrDefault($_GET, 'del', 0) == 1 ? 205 : 203);
