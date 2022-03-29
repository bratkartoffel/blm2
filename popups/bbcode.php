<?php
/**
 * Popup zum hinzufeügen von BBCode-Tags zu einer IGM
 *
 * @version 1.0.0
 * @author Simon Frankenberger <simonfrankenberger@web.de>
 * @package blm2.popups
 */
header('Content-type: text/html; charset="utf-8"', true);        // Das Dokument ist UTF-8 kodiert...
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/2002/REC-xhtml1-20020801/DTD/xhtml1-strict.dtd">
<!--
	Site generated: 	<?= date("r", time()); ?>
	Client: 		<?= $_SERVER['REMOTE_ADDR'] ?>
	Server: 		<?= $_SERVER['SERVER_ADDR']; ?>
	Script: 		<?= $_SERVER['PHP_SELF']; ?>
-->
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de" lang="de">
<head>
    <link rel="stylesheet" type="text/css" href="../styles/style.css"/>
    <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
    <meta http-equiv="creator" content="Simon Frankenberger"/>
    <title>BLM2 - BBCode</title>
    <style type="text/css">
        td, th {
            padding: 4px 10px 4px 10px;
        }
    </style>
</head>
<body>
<table class="Liste" cellspacing="0">
    <tr>
        <th>Code:</th>
        <th>Wirkung:</th>
    </tr>
    <tr>
        <td>[b]text[/b]</td>
        <td><b>text</b></td>
    </tr>
    <tr>
        <td>[i]text[/i]</td>
        <td><i>text</i></td>
    </tr>
    <tr>
        <td>[u]text[/u]</td>
        <td><u>text</u></td>
    </tr>
    <tr>
        <td>[ul]<br/>
            * Eintrag 1<br/>
            * Eintrag 2<br/>
            [/ul]
        </td>
        <td>
            <ul>
                <li>Eintrag 1</li>
                <li>Eintrag 2</li>
            </ul>
        </td>
    </tr>
    <tr>
        <td>[color=red]text[/color]</td>
        <td><span style="color: red;">text</span></td>
    </tr>
    <tr>
        <td>[size=16]text[/size]</td>
        <td><span style="font-size: 16pt;">text</span></td>
    </tr>
</table>
<br/>
<div style="text-align: center;">
    <a href="../?p=nachrichten_schreiben" onclick="self.close(); return false;">Fenster schlie&szlig;en</a>
</div>
</body>
</html>
