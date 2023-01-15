<?php
/*
 * MIT Licence
 * Copyright (c) 2023 Simon Frankenberger
 *
 * Please see LICENCE.md for complete licence text.
 */
require_once '../include/functions.inc.php';
require_once '../include/database.class.php';

ob_start();
include '../cronjobs/cron.php';
