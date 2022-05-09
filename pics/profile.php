<?php
require_once('../include/functions.inc.php');

$uid = getOrDefault($_GET, 'uid', 0);
$gid = getOrDefault($_GET, 'gid', 0);

if ($uid != 0) {
    $prefix = "u";
    $id = $uid;
} else if ($gid != 0) {
    $prefix = "g";
    $id = $gid;
} else {
    die('missing uid/gid parameter');
}

header("Cache-Control: max-age=86400, public");
$suffixes = array('png' => 'image/png', 'jpg' => 'image/jpeg', 'gif' => 'image/gif', 'webp' => 'image/webp');
foreach ($suffixes as $suffix => $mimetype) {
    $filename = sprintf('uploads/%s_%d.%s', $prefix, $id, $suffix);
    if (file_exists($filename)) {
        header(sprintf("Content-Type: %s", $mimetype));
        readfile($filename);
        die();
    }
}

// no image found, show fallback image
header("Content-Type: image/webp");
readfile('style/nopic.webp');
