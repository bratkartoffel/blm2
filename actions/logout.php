<?php
/*
 * MIT Licence
 * Copyright (c) 2023 Simon Frankenberger
 *
 * Please see LICENCE.md for complete licence text.
 */
require_once __DIR__ . '/../include/functions.inc.php';

ob_start();
session_destroy();

if (getOrDefault($_GET, 'popup', 0) == 1) {
    die('<script>self.close();</script>');
}

redirectTo('/?p=anmelden', getOrDefault($_GET, 'deleted', 0) == 1 ? 205 : 203);
