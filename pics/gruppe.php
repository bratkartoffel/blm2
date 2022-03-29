<?php
/**
 * Liest ein Gruppenbild aus und gibt es zurück
 *
 * @version 1.0.0
 * @author Simon Frankenberger <simonfrankenberger@web.de>
 * @package blm2.includes
 */

if (!$_GET['id'])
    die();        // Falls keine User_ID angegeben wurde, dann abbrechen

$pfad = "./gruppe/" . intval($_GET['id']);

if (file_exists($pfad . ".jpg"))        // Der Benutzer hat ein JPG-Bild hochgeladen?
    $suffix = "jpg";
else
    if (file_exists($pfad . ".png"))        // oder doch ein PNG?
        $suffix = "png";
    else
        if (file_exists($pfad . ".gif"))        // Ne, aber ein GIF
            $suffix = "gif";

// Je nachdem, was der Benutzer für einen Typ beim Benutzerbild hat, reagieren wir.
// Standardmäßig habe ich mal nur JPG, GIF und PNG erlaubt.

// TODO: Transparente PNGs funzen nicht :(
switch ($suffix) {
    case "jpg":
        $bild = ImageCreateFromJPEG($pfad . "." . $suffix);
        header("content-type: image/jpeg");
        ImageJPEG($bild, "", 75);
        break;
    case "png":
        $bild = ImageCreateFromPNG($pfad . "." . $suffix);
        header("content-type: image/png");
        ImagePNG($bild);
        break;
    case "gif":
        $bild = ImageCreateFromGIF($pfad . "." . $suffix);
        header("content-type: image/gif");
        ImageGIF($bild);
        break;
}

// Fertisch :)
