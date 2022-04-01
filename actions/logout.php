<?php
include("../include/config.inc.php");
include("../include/functions.inc.php");

session_destroy();

if (isset($_GET['popup'])) {        // Das Script wurde vom Popup aufgerufen, das heisst, das Popup soll geschlossen werden
    echo '<script type="text/javascript">self.close();</script>';
    die();
}

if (isset($_GET['del']) && intval($_GET['del']) > 0) {        // Hat sich der Benutzer gelöscht?
    header("location: ../?p=anmelden&m=205");        // Ja: Bye bye...
} else {
    header("location: ../?p=anmelden&m=203");        // Nein: Bis bald :)
}
