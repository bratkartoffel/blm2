<?php
/*
 * MIT Licence
 * Copyright (c) 2023 Simon Frankenberger
 *
 * Please see LICENCE.md for complete licence text.
 */

require_once __DIR__ . '/../include/functions.inc.php';

$uid = getOrDefault($_GET, 'uid', 0);
$gid = getOrDefault($_GET, 'gid', 0);

if ($uid != 0) {
    $prefix = 'u';
    $id = $uid;
} else if ($gid != 0) {
    $prefix = 'g';
    $id = $gid;
} else {
    die('missing uid/gid parameter');
}

$filename = sprintf('uploads/%s_%d.webp', $prefix, $id);
if (!file_exists($filename)) {
    $filename = 'style/nopic.webp';
}

header('Content-Type: image/webp');
readfile($filename);
