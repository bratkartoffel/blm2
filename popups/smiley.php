<?php
/**
 * Bietet die Möglichkeit, Smileys zu einer IGM hinzuzufügen
 *
 * @version 1.0.0
 * @author Simon Frankenberger <simonfrankenberger@web.de>
 * @package blm2.includes
 */
header('Content-type: text/html; charset="utf-8"', true);        // Das Dokument ist UTF-8 kodiert...
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/2002/REC-xhtml1-20020801/DTD/xhtml1-strict.dtd">
<!--
	Site generated: 	<?= date("r", time()); ?>
	Client: 		<?= getenv("REMOTE_ADDR") ?>
	Server: 		<?= getenv("SERVER_ADDR"); ?>
	Script: 		<?= $_SERVER['PHP_SELF']; ?>
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
            var z = opener.document.form_message;
            var y = 0;

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
        <td style="font-weight: bold;"> :&Ouml;</td>
        <td><a href="#" onclick="Auswahl(' :&ouml;'); return false;"><img style="border: none;"
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
    <a href="../?p=nachrichten_schreiben" onclick="self.close(); return false;">Fenster schlie&szlig;en</a>
</div>
</body>
</html>
