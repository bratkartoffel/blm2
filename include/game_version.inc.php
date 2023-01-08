<?php
$version_extra = null;
{
    $git_head = dirname(__FILE__) . '/../.git/HEAD';
    if (file_exists($git_head)) {
        $content = file_get_contents($git_head);
        if (strpos($content, 'ref:') !== false) {
            $tmp = explode('/', $content);
            $content = trim($tmp[count($tmp) - 1]);
        }
        $version_extra = '+' . substr($content, 0, 8);
    }
}
define('game_version', "1.10.1" . $version_extra);
