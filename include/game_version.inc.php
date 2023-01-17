<?php
/*
 * MIT Licence
 * Copyright (c) 2023 Simon Frankenberger
 *
 * Please see LICENCE.md for complete licence text.
 */
function getVersionExtra()
{
    $version_extra = null;
    $git_head = dirname(__FILE__) . '/../.git/HEAD';
    if (file_exists($git_head)) {
        $content = file_get_contents($git_head);
        if (strpos($content, 'ref:') !== false) {
            $tmp = explode('/', $content);
            $content = trim($tmp[count($tmp) - 1]);
        }
        $version_extra = '+' . substr($content, 0, 8);
    }
    return $version_extra;
}

define('game_version', "1.10.7" . getVersionExtra());
