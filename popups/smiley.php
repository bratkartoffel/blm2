<?php
/**
 * Bietet die Möglichkeit, Smileys zu einer IGM hinzuzufügen
 *
 * @version 1.0.0
 * @author Simon Frankenberger <simonfrankenberger@web.de>
 * @package blm2.includes
 */
header('Content-type: text/html; charset="utf-8"');        // Das Dokument ist UTF-8 kodiert...
?>
<!DOCTYPE html>
<!--
	Site generated:   <?= date("r", time()) . "\n"; ?>
	Client:           <?= sichere_ausgabe($_SERVER['REMOTE_ADDR']) . "\n"; ?>
	Server:           <?= sichere_ausgabe($_SERVER['SERVER_ADDR']) . "\n"; ?>
	Script:           <?= sichere_ausgabe($_SERVER['PHP_SELF']) . "\n"; ?>
	Query-String:     <?= sichere_ausgabe($_SERVER['QUERY_STRING']) . "\n"; ?>
	User-Agent:       <?= sichere_ausgabe($_SERVER['HTTP_USER_AGENT']) . "\n"; ?>
	Referer:          <?= sichere_ausgabe($_SERVER['HTTP_REFERER']) . "\n"; ?>
-->
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de" lang="de">
<head>
    <link rel="stylesheet" type="text/css" href="../styles/style.css"/>
    <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
    <meta http-equiv="creator" content="Simon Frankenberger"/>
    <title>BLM2 - Emoticons</title>
    <style type="text/css">
        td, th {
            padding: 4px 10px 4px 10px;
        }
    </style>
    <script type="text/javascript">
        <!--
        function Auswahl(text) {
            const z = opener.document.form_message;
            let y;

            z.nachricht.value += " " + text;
            y = z.getElementsByTagName("span")[0].innerHTML;

            y -= text.length;
            z.getElementsByTagName("span")[0].innerHTML = y;
        }

        // -->
    </script>
</head>
<body>
<table class="Liste" cellspacing="0">
    <tr>
        <th>Code:</th>
        <th style="border-right: solid 1px #555555;">Smiley:</th>
        <th>Code:</th>
        <th>Smiley:</th>
    </tr>
    <tr>
        <td style="font-weight: bold;"> ;P</td>
        <td style="border-right: solid 1px #555555;"><a href="#" onclick="Auswahl(' ;P'); return false;"><img
                        style="border: none;" src="../pics/emoticons/kopete006.png"/></a></td>
        <td style="font-weight: bold;"> $)</td>
        <td><a href="#" onclick="Auswahl(' $)'); return false;"><img style="border: none;"
                                                                     src="../pics/emoticons/kopete007.png"/></a></td>
    </tr>
    <tr>
        <td style="font-weight: bold;"> 8)</td>
        <td style="border-right: solid 1px #555555;"><a href="#" onclick="Auswahl(' 8)'); return false;"><img
                        style="border: none;" src="../pics/emoticons/kopete008.png"/></a></td>
        <td style="font-weight: bold;"> ^^</td>
        <td><a href="#" onclick="Auswahl(' ^^'); return false;"><img style="border: none;"
                                                                     src="../pics/emoticons/kopete010.png"/></a></td>
    </tr>
    <tr>
        <td style="font-weight: bold;"> :O</td>
        <td style="border-right: solid 1px #555555;"><a href="#" onclick="Auswahl(' :0'); return false;"><img
                        style="border: none;" src="../pics/emoticons/kopete011.png"/></a></td>
        <td style="font-weight: bold;"> :((</td>
        <td><a href="#" onclick="Auswahl(' :(('); return false;"><img style="border: none;"
                                                                      src="../pics/emoticons/kopete012.png"/></a></td>
    </tr>
    <tr>
        <td style="font-weight: bold;"> ;)</td>
        <td style="border-right: solid 1px #555555;"><a href="#" onclick="Auswahl(' ;)'); return false;"><img
                        style="border: none;" src="../pics/emoticons/kopete013.png"/></a></td>
        <td style="font-weight: bold;"> :~</td>
        <td><a href="#" onclick="Auswahl(' :~'); return false;"><img style="border: none;"
                                                                     src="../pics/emoticons/kopete014.png"/></a></td>
    </tr>
    <tr>
        <td style="font-weight: bold;"> :|</td>
        <td style="border-right: solid 1px #555555;"><a href="#" onclick="Auswahl(' :|'); return false;"><img
                        style="border: none;" src="../pics/emoticons/kopete015.png"/></a></td>
        <td style="font-weight: bold;"> :p</td>
        <td><a href="#" onclick="Auswahl(' :P'); return false;"><img style="border: none;"
                                                                     src="../pics/emoticons/kopete016.png"/></a></td>
    </tr>
    <tr>
        <td style="font-weight: bold;"> :D</td>
        <td style="border-right: solid 1px #555555;"><a href="#" onclick="Auswahl(' :D'); return false;"><img
                        style="border: none;" src="../pics/emoticons/kopete017.png"/></a></td>
        <td style="font-weight: bold;"> :Ö</td>
        <td><a href="#" onclick="Auswahl(' :ö'); return false;"><img style="border: none;"
                                                                          src="../pics/emoticons/kopete018.png"/></a>
        </td>
    </tr>
    <tr>
        <td style="font-weight: bold;"> :(</td>
        <td style="border-right: solid 1px #555555;"><a href="#" onclick="Auswahl(' :('); return false;"><img
                        style="border: none;" src="../pics/emoticons/kopete019.png"/></a></td>
        <td style="font-weight: bold;"> :)</td>
        <td><a href="#" onclick="Auswahl(' :)'); return false;"><img style="border: none;"
                                                                     src="../pics/emoticons/kopete020.png"/></a></td>
    </tr>
</table>
<br/>
<div style="text-align: center;">
    <a href="../?p=nachrichten_schreiben" onclick="self.close(); return false;">Fenster schliessen</a>
</div>
</body>
</html>
