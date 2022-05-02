<?php
function getOrderChefboxDescription(int $order_type): string
{
    switch (floor($order_type / 100)) {
        case 1:
            $result = sprintf('G: %s', getBuildingName($order_type % 100));
            break;
        case 2:
            $result = sprintf('A: %s', getItemName($order_type % 100));
            break;
        case 3:
            $result = sprintf('F: %s', getItemName($order_type % 100));
            break;
        default:
            $result = sprintf('Unbekannt (%d)', $order_type);
            break;
    }
    if (strlen($result) > 14) {
        $result = substr($result, 0, 14) . '...';
    }
    return $result;
}

function CheckAllAuftraege(): void
{
    $players = Database::getInstance()->getAllPlayerIdsAndName();
    foreach ($players as $player) {
        CheckAuftraege($player['ID']);
    }
}

function CheckAuftraege(int $blm_user): bool
{
    Database::getInstance()->begin();
    $auftraege = Database::getInstance()->getAllExpiredAuftraegeByVon($blm_user);

    foreach ($auftraege as $auftrag) {
        switch (floor($auftrag['item'] / 100)) {
            // Gebäude
            case 1:
                if (Database::getInstance()->updateTableEntryCalculate('gebaeude', null,
                        array('Gebaeude' . ($auftrag['item'] % 100) => 1), array('user_id = :whr0' => $blm_user)) != 1) {
                    return false;
                }
                if (Database::getInstance()->updateTableEntryCalculate('punkte', null,
                        array('GebaeudePlus' => $auftrag['points']),
                        array('user_id = :whr0' => $blm_user)) != 1) {
                    return false;
                }
                break;

            // Produktion
            case 2:
                if (Database::getInstance()->updateTableEntryCalculate('lagerhaus', null,
                        array('Lager' . ($auftrag['item'] % 100) => $auftrag['amount']),
                        array('user_id = :whr0' => $blm_user)) != 1) {
                    return false;
                }
                break;

            // Forschung
            case 3:
                if (Database::getInstance()->updateTableEntryCalculate('forschung', null,
                        array('Forschung' . ($auftrag['item'] % 100) => 1),
                        array('user_id = :whr0' => $blm_user)) != 1) {
                    return false;
                }
                if (Database::getInstance()->updateTableEntryCalculate('punkte', null,
                        array('ForschungPlus' => $auftrag['points']),
                        array('user_id = :whr0' => $blm_user)) != 1) {
                    return false;
                }
                break;

            // Unknown
            default:

                break;
        }
        if ($auftrag['points'] > 0) {
            if (Database::getInstance()->updateTableEntryCalculate('mitglieder', $blm_user,
                    array('Punkte' => $auftrag['points'])) != 1) {
                return false;
            }
        }
        if (Database::getInstance()->deleteTableEntry('auftrag', $auftrag['ID']) != 1) {
            return false;
        }
    }
    Database::getInstance()->commit();
    return true;
}

function isRoundOver(): bool
{
    return (last_reset + game_round_duration <= time() + 5);
}

function isGameLocked(): bool
{
    return (last_reset >= time() + 5);
}

function getMessageBox(int $msg_id): ?string
{
    if ($msg_id == 0) {
        return null;
    }
    if ($msg_id >= 200 && $msg_id < 300) {
        $image = 'ok';
    } else {
        $image = 'error';
    }

    switch ($msg_id)        // Überprüft die Fehlernummer
    {
        case 101:
            $text = 'Seite konnte nicht gefunden werden!';
            break;
        case 102:
            $text = 'Sie sind nicht angemeldet. Bitte melden Sie sich erst an.';
            break;
        case 103:
            $text = 'Das Bild ist zu gross. Die maximale Grösse des Bildes ist 64 KB.';
            break;
        case 104:
            $text = 'Bitte füllen Sie alle Felder aus.';
            break;
        case 105:
            $text = 'Bitte geben Sie Ihr gewünschtes Passwort 2x ein, um Tipfehler zu vermeiden.';
            break;
        case 106:
            $text = 'Der Benutzername oder EMail-Adresse ist bereits vergeben.';
            break;
        case 107:
            $text = 'Die hochgeladene Datei ist kein Bild vom Typ jpg, gif oder png!';
            break;
        case 108:
            $text = 'Unbekannter Benutzername und / oder falsches Passwort!';
            break;
        case 109:
            $text = 'Sie haben Ihr Kreditlimit schon erreicht oder ein zu grosser Betrag wurde ausgewählt!';
            break;
        case 110:
            $text = 'Ungültiger Betrag!';
            break;
        case 111:
            $text = 'Sie haben nicht genügend Geld!';
            break;
        case 112:
            $text = 'Das darfst du nicht!';
            break;
        case 113:
            $text = 'Der Auftrag wurde bereits erteilt!';
            break;
        case 114:
            $text = 'Ihr Account wurde soeben resettet, da Sie Ihren Kredit bei der Bank nicht decken konnten und die Bank alles gepfändet hat.';
            break;
        case 115:
            $text = 'Bitte geben Sie eine gültige Menge ein.';
            break;
        case 116:
            $text = 'Sie haben gar nicht so viel Waren auf Lager!';
            break;
        case 117:
            $text = 'Ungültige Angaben oder Angaben nicht vollständig.';
            break;
        case 118:
            $text = 'Der Benutzer konnte nicht gefunden werden.';
            break;
        case 119:
            $text = 'Das Angebot mit der ID konnte nicht gefunden werden. Vermutlich war jemand schneller als Sie.';
            break;
        case 120:
            $text = 'Bitte geben Sie eine Menge und einen Preis grösser 1 ein!';
            break;
        case 121:
            $text = 'Das alte Kennwort ist nicht korrekt!';
            break;
        case 122:
            $text = 'Sie haben keine Waren auf Lager, was wollen Sie da verkaufen?';
            break;
        case 124:
            $text = 'Die angegebene Nachricht konnte nicht gefunden werden!';
            break;
        case 125:
            $text = 'Ungültige Menge eingegeben!';
            break;
        case 126:
            $text = 'Es existiert bereits eine Gruppe mit diesem Namen oder Kürzel!';
            break;
        case 127:
            $text = 'Entweder existiert die eingegebene Gruppe nicht oder das eingegebene Passwort ist falsch!';
            break;
        case 128:
            $text = 'Bitte geben Sie eine Nachricht mit mindestens 4 Zeichen im Betreff und 8 Zeichen als Text ein!';
            break;
        case 129:
            $text = 'Es besteht bereits eine Beziehung mit dieser Gruppe!';
            break;
        case 130:
            $text = 'Der eingegebene Sicherheitscode ist nicht korrekt!';
            break;
        case 131:
            $text = 'Der Kontostand des Mitglieds ist bereits auf dem Maximum, die Bank weigert sich die Überweisung anzunehmen!';
            break;
        case 132:
            $text = 'Bei einem Krieg muss der Betrag, um welchen gekämpft wird, größer als ' . formatCurrency(group_war_min_amount) . ' sein!';
            break;
        case 133:
            $text = "Bitte geben Sie eine Dauer zwischen 1 und " . production_hours_max . " Stunden ein!";
            break;
        case 134:
            $text = "Bitte geben Sie eine gültige EMail-Adresse ein!";
            break;
        case 135:
            $text = "Ihr Account ist noch nicht aktiviert.<br />Bitte klicken Sie auf den Link, den Sie per EMail erhalten haben.<br />Falls Sie keinen Link erhalten haben, so wenden Sie sich bitte mit Ihrem Benutzernamen und <br />der registrierten EMailadresse als Absender an die im Impressum angegebene Adresse.";
            break;
        case 136:
            $text = "Bitte geben Sie ein Sitterpasswort ein!";
            break;
        case 137:
            $text = "Es ist Weihnachten. Die Mafiabosse haben untereinander bis zum Ende der Feiertage einen Waffenstillstand geschlossen.";
            break;
        case 138:
            $text = "Sitter dürfen beim Spiel nicht teilnehmen, tut mir Leid...";
            break;
        case 139:
            $text = "Ihr Account wurde von einem Administrator gesperrt. Bitte kontaktieren Sie einen Administrator im Forum für weitere Informationen. Falls diese Sperre dauerhaft ist, dann wird Ihr Account zwei Wochen nach Beginn der Sperre gelöscht.";
            break;
        case 140:
            $text = "Die Gruppe wurde gefunden und das Passwort ist korrekt, jedoch hat die Gruppe die maximale Mitgliederzahl schon erreicht.";
            break;
        case 141:
            $text = "Datenbankfehler, konnte neuen Eintrag nicht anlegen";
            break;
        case 142:
            $text = "Datenbankfehler, konnte bestehenden Eintrag nicht bearbeiten";
            break;
        case 143:
            $text = "Datenbankfehler, konnte bestehenden Eintrag nicht löschen";
            break;
        case 144:
            $text = sprintf('Der neue Benutzer wurde zwar erstellt, jedoch konnte die Aktivierungsmail nicht versendet werden. Bitte wende dich per EMail an den Admin: <a href="mailto:%s">%s</a>', admin_email, admin_email);
            break;
        case 145:
            $text = 'Sie müssen zuerst mal ein Forschungszentrum bauen, bevor Sie Forschungen starten können!';
            break;
        case 146:
            $text = 'Der Benutzername darf nur zwischen ' . username_min_len . ' und ' . username_max_len . ' Zeichen enthalten';
            break;
        case 147:
            $text = 'Das gewählte Passwort ist zu kurz, es muss mindestens aus ' . password_min_len . ' Zeichen bestehen.';
            break;
        case 148:
            $text = 'Die Registrierung ist aktuell geschlossen';
            break;
        case 149:
            $text = 'Bitte geben Sie die neue EMail-Adresse 2x ein, um Schreibfehler zu vermeiden.';
            break;
        case 150:
            $text = 'Die EMail-Adresse konnte nicht geändert werden, weil die Bestätigungsmail nicht gesendet werden konnte.';
            break;
        case 151:
            $text = 'Der Account konnte auf Grund von Fehlern mit der Datenbank nicht zurückgesetzt werden.';
            break;
        case 152:
            $text = 'Die Passwörter für den Sitter und den Hauptzugang müssen unterschiedlich sein.';
            break;
        case 153:
            $text = 'Ungültiger Preis angegeben.';
            break;
        case 154:
            $text = 'Eintrag konnte nicht gefunden werden';
            break;
        case 155:
            $text = 'Der andere Spieler kann nicht angegriffen werden, da er ausserhalb des Punktebereichs liegt.';
            break;
        case 156:
            $text = 'Ihre Gruppe hat ein NAP oder BND mit der Gruppe des anderen Spielers.';
            break;
        case 157:
            $text = 'Sie befinden sich bereits in einer Gruppe';
            break;
        case 158:
            $text = 'Ungültiger Gruppenname (Darf nur maximal ' . group_max_name_length . ' Zeichen lang sein)';
            break;
        case 159:
            $text = 'Ungültiges Gruppenkürzel  (Darf nur maximal ' . group_max_tag_length . ' Zeichen lang sein)';
            break;
        case 160:
            $text = 'Token in Anfrage nicht gefunden, Aktion verweigert';
            break;
        case 161:
            $text = 'Mit Ihrem Austritt gäbe es kein Mitglied mehr, welches Rechte vergeben könnte';
            break;
        case 162:
            $text = 'Sie sind das letzte Mitglied dieser Gruppe, ein Austritt ist nicht möglich';
            break;
        case 163:
            $text = 'Die Gruppe kann erst gelöscht werden, wenn alle diplomatischen Beziehungen entfernt wurden';
            break;
        case 164:
            $text = 'Der Name darf kein &quot;#&quot; enthalten';
            break;
        case 165:
            $text = 'Sie können keine diplomatische Beziehung mit Ihnen selbst eingehen';
            break;
        case 166:
            $text = 'In der Gruppenkasse befindet sich genügend Geld für den Krieg';
            break;
        case 167:
            $text = 'Eine diplomatische Beziehung kann erst nach frühestens ' . group_diplomacy_min_duration . ' Tagen aufgekündigt werden';
            break;
        case 168:
            $text = 'Sie können sich selbst keine Nachrichten schicken!';
            break;
        case 169:
            $text = 'Die Mafia ist erst ab ' . formatPoints(min_points_mafia) . ' Punkten verfügbar';
            break;


        case 201:
            $text = 'Der neue Benutzer wurde erfolgreich erstellt. Sobald Sie Ihre EMail-Adresse bestätigt haben, können Sie sich einloggen.';
            break;
        case 202:
            $text = 'Sie haben sich erfolgreich angemeldet.';
            if (array_key_exists('blm_sitter', $_SESSION) && $_SESSION['blm_sitter']) {
                $text .= ' (Sitterzugang)';
            }
            break;
        case 203:
            $text = 'Sie haben sich erfolgreich abgemeldet.';
            break;
        case 204:
            $text = 'Nachricht wurde gesendet.';
            break;
        case 205:
            $text = 'Ihr Account wurde gelöscht. Ich hoffe, das Spiel hat Ihnen gefallen!';
            break;
        case 206:
            $text = 'Die Beschreibung wurde gespeichert.';
            break;
        case 207:
            $text = 'Der Auftrag wurde erteilt.';
            break;
        case 208:
            $text = 'Die Waren wurden verkauft.';
            break;
        case 209:
            $text = 'Das Bild wurde gelöscht.';
            break;
        case 210:
            $text = 'Das Bild wurde erfolgreich hochgeladen.';
            break;
        case 211:
            $text = 'Die Nachricht wurde gelöscht.';
            break;
        case 212:
            $text = 'Alle Nachrichten wurden gelöscht.';
            break;
        case 213:
            $text = 'Der Notizblock wurde gespeichert.';
            break;
        case 214:
            $text = 'Der Vertrag wurde versandt.';
            break;
        case 215:
            $text = 'Der Vertrag wurde angenommen. Sie finden die Waren in Ihrem Lager.';
            break;
        case 216:
            $text = 'Der Vertrag wurde abgelehnt.';
            break;
        case 217:
            $text = 'Das Angebot wurde gekauft.';
            break;
        case 218:
            $text = 'Das Angebot wurde eingestellt.';
            break;
        case 219:
            $text = 'Das Passwort wurde erfolgreich geändert.';
            break;
        case 220:
            $text = 'Der Account wurde wieder auf Standardeinstellungen zurückgesetzt.';
            break;
        case 221:
            $text = 'Das Angebot wurde zurückgezogen.';
            break;
        case 222:
            $text = 'Der Auftrag wurde gelöscht.';
            break;
        case 223:
            $text = 'Die Gruppe wurde erstellt!';
            break;
        case 224:
            $text = 'Sie sind der Gruppe erfolgreich beigetreten!';
            break;
        case 225:
            $text = 'Sie haben die Gruppe nun verlassen.';
            break;
        case 226:
            $text = 'Die Rechte wurden gespeichert.';
            break;
        case 227:
            $text = 'Das Mitglied wurde aus der Gruppe verwiesen.';
            break;
        case 228:
            $text = 'Die Gruppe wurde gelöscht!';
            break;
        case 229:
            $text = 'Die Beziehung wurde eingetragen!';
            break;
        case 230:
            $text = 'Der Vertrag wurde aufgelöst.';
            break;
        case 231:
            $text = 'Der Vertrag wurde angenommen.';
            break;
        case 233:
            $text = 'Das Angebot wurde gelöscht.';
            break;
        case 234:
            $text = 'Das Angebot wurde bearbeitet.';
            break;
        case 235:
            $text = 'Das Geld wurde in die Gruppenkasse überwiesen.';
            break;
        case 236:
            $text = 'Das Geld wurde an das Mitglied überwiesen.';
            break;
        case 237:
            $text = 'Der Krieg wurde beendet. Leider war kein Sieg mehr in Aussicht.';
            break;
        case 238:
            $text = 'Die EMail-Adresse wurde geändert.';
            break;
        case 239:
            $text = 'Der Sitterzugang wurde gelöscht.';
            break;
        case 240:
            $text = 'Der Sitterzugang wurde erfolgreich bearbeitet.';
            break;
        case 241:
            $text = 'Account wurde erfolgreich aktiviert.';
            break;
        case 242:
            $text = 'Die Aktion wurde erfolgreich ausgeführt.';
            break;
        case 243:
            $text = 'Die aktuelle Runde ist nun beendet und das Spiel pausiert. Aktuell laufen die Auswertungen, schau später nochmal vorbei.';
            break;


        case 999:
            $text = sprintf('Das Spiel ist zur Zeit pausiert.<br />Die neue Runde startet am %s', date("d.m.Y \u\m H:i", last_reset));
            break;
        default:
            $text = sprintf('Meldungsnummer konnte nicht gefunden werden: %d', $msg_id);
            break;
    }

    return sprintf('<div class="Meldung" id="meldung_%d">
            <img src="/pics/small/%s.png" alt=""/>
            <a id="close" onclick="document.getElementById(\'meldung_%d\').remove();">X</a>
            <span>%s</span>
        </div>', $msg_id, $image, $msg_id, $text);
}

function getBuildingImage(int $building_id): string
{
    switch ($building_id) {
        case 1:
        case 2:
        case 3:
        case 4:
        case 5:
        case 6:
            return sprintf('/pics/gebaeude/%d.png', $building_id);
        default:
            return sprintf('/pics/gebaeude/%d.jpg', $building_id);
    }
}

function getBuildingName(int $building_id): string
{
    switch ($building_id) {
        case 1:
            return 'Plantage';
        case 2:
            return 'Forschungszentrum';
        case 3:
            return 'Bioladen';
        case 4:
            return 'Dönerstand';
        case 5:
            return 'Bauhof';
        case 6:
            return 'Verkäuferschule';
        case 7:
            return 'Zaun';
        case 8:
            return 'Pizzeria';
        default:
            return 'Unbekannt (' . $building_id . ')';
    }
}

function getItemImage(int $item_id): string
{
    switch ($item_id) {
        case 1:
        case 2:
        case 3:
        case 4:
        case 5:
        case 6:
        case 7:
        case 8:
            return sprintf('/pics/obst/%d.png', $item_id);
        default:
            return sprintf('/pics/obst/%d.jpg', $item_id);
    }
}

function getResearchImage(int $item_id): string
{
    switch ($item_id) {
        case 1:
        case 2:
        case 3:
        case 4:
        case 5:
        case 6:
        case 7:
        case 8:
            return sprintf('/pics/forschung/%d.png', $item_id);
        default:
            return sprintf('/pics/forschung/%d.jpg', $item_id);
    }
}

function getItemName(int $item_id): string
{
    switch ($item_id) {
        case 1:
            return 'Kartoffeln';
        case 2:
            return 'Karotten';
        case 3:
            return 'Tomaten';
        case 4:
            return 'Salat';
        case 5:
            return 'Äpfel';
        case 6:
            return 'Birnen';
        case 7:
            return 'Kirschen';
        case 8:
            return 'Bananen';
        case 9:
            return 'Gurken';
        case 10:
            return 'Weintrauben';
        case 11:
            return 'Tabak';
        case 12:
            return 'Ananas';
        case 13:
            return 'Erdbeeren';
        case 14:
            return 'Orangen';
        case 15:
            return 'Kiwi';
        default:
            return 'Unbekannt (' . $item_id . ')';
    }
}

function isAdmin(): bool
{
    return getOrDefault($_SESSION, 'blm_admin', 0) == 1;
}

function isLoggedIn(): bool
{
    return getOrDefault($_SESSION, 'blm_user', -1) != -1;
}

function getYesOrNo(int $bool): string
{
    if ($bool == 0)
        return "Nein";
    else
        return "Ja";
}

function replaceBBCode(string $text): string
{
    $colors[] = "aqua";
    $colors[] = "black";
    $colors[] = "blue";
    $colors[] = "fuchsia";
    $colors[] = "gray";
    $colors[] = "green";
    $colors[] = "lime";
    $colors[] = "maroon";
    $colors[] = "navy";
    $colors[] = "olive";
    $colors[] = "orange";
    $colors[] = "purple";
    $colors[] = "red";
    $colors[] = "silver";
    $colors[] = "teal";
    $colors[] = "white";
    $colors[] = "yellow";

    $result = escapeForOutput($text);
    $result = preg_replace(
        array(
            '/\[center](.*)\[\/center]/Uis',
            "/\[size=([12]\d)](.*)\[\/size]/Uis",
            "/\[url=&quot;(http:\/\/|www.|http:\/\/www.)([a-z\d\-_.]{3,32}\.[a-z]{2,4})&quot;](.*)\[\/url]/SiU",
            "/\[img=&quot;(http:\/\/[a-z\d\-_.\/]{3,32}\.[a-z]{3,4})&quot;](.*)\[\/img]/SiU",
            "/\[email=&quot;([a-z\d\-_.]{3,32}@[a-z\d\-_.]{3,32}\.[a-z]{2,4})&quot;](.*)\[\/email]/SiU",
            "/\[emoticon=&quot;([a-z\d\-_.\/]{3,64})&quot; \/]/Si",
            "@\[player=(.+)#(\d{1,8})/]@SUi",
            "@\[group=(.+)#(\d{1,8})/]@SUi",
        ),
        array(
            '<div style="text-align: center;">\1</div>',
            '<span style="font-size: \1px;">\2</span>',
            '<a href="http://\2">\3</a>',
            '<a href="\1" target="_blank"><img src="\1" alt="\2" style="border: none;"/></a>',
            '<a href="mailto:\1">\2</a>',
            '<img src="\1" alt="\2" style="border: none;"/>',
            '<a href="/?p=profil&amp;id=\2">\1</a>',
            '<a href="/?p=gruppe&amp;id=\2">\1</a>',
        ),
        $result
    );

    // the following tags may be nested, so run in a loop until everything is replaced
    $last_text = null;
    while ($last_text != $result) {
        $last_text = $result;

        $result = preg_replace(
            array(
                "/\[color=(#[\da-f]{6}|" . implode("|", $colors) . ")](.*)\[\/color]/is",
                "/\[([bui])](.*)\[\/\\1]/Uis",
                '/\[quote](.*)\[\/quote]/Uism'
            ),
            array(
                '<span style="color: \1;">\2</span>',
                '<\1>\2</\1>',
                '<blockquote>\1</blockquote>'
            ),
            $result);
    }

    return $result;
}

function deleteAccount(int $blm_user): ?string
{
    // delete everything associated with this user
    $status = resetAccount($blm_user);
    if ($status !== null) {
        return 'reset_' . $status;
    }

    // delete the user itself
    foreach (starting_values as $table => $ignored) {
        if ($table == 'mitglieder') {
            $wheres = array('ID' => $blm_user);
        } else {
            $wheres = array('user_id' => $blm_user);
        }
        if (Database::getInstance()->deleteTableEntryWhere($table, $wheres) === null) {
            return 'delete_' . $table;
        }
    }
    return null;
}

function resetAccount(int $blm_user): ?string
{
    // delete group if the user is the only member
    $player = Database::getInstance()->getPlayerNameAndGroupIdAndGroupRightsById($blm_user);
    if ($player === null) {
        return "loading player";
    }
    if ($player['Gruppe'] !== null && Database::getInstance()->getGroupMemberCountById($player['Gruppe']) == 1) {
        $status = Database::getInstance()->deleteGroup($player['Gruppe']);
        if ($status !== null) {
            return $status;
        }
    }

    // reset all values to the starting defaults
    foreach (starting_values as $table => $values) {
        if ($table == 'mitglieder') {
            $idField = $blm_user;
            $wheres = array();
        } else {
            $idField = null;
            $wheres = array('user_id = :whr0' => $blm_user);
        }
        if (Database::getInstance()->updateTableEntry($table, $idField, $values, $wheres) === null) {
            return $table;
        }
    }

    // delete all other data associated with this user
    $deleteTables = array(
        'auftrag' => 'user_id',
        'marktplatz' => 'Von',
        'sitter' => 'ID',
        'gruppe_rechte' => 'user_id',
        'gruppe_kasse' => 'user_id',
        'vertraege' => 'Von',
    );
    foreach ($deleteTables as $table => $field) {
        if (Database::getInstance()->deleteTableEntryWhere($table, array($field => $blm_user)) === null) {
            return $table;
        }
    }

    // handle all contracts which where sent to this user
    $data = Database::getInstance()->getAllContractsByAnEquals($blm_user);
    foreach ($data as $entry) {
        if (Database::getInstance()->updateTableEntryCalculate('lagerhaus', $entry['Von'], array('Lager' . $entry['Was'] => $entry['Menge'])) !== 1) {
            return 'lagerhaus';
        }
    }
    if (Database::getInstance()->deleteTableEntryWhere('vertraege', array('An' => $blm_user)) === null) {
        return $table;
    }

    return null;
}

function updateLastAction(): void
{
    Database::getInstance()->begin();
    Database::getInstance()->updateTableEntryCalculate('mitglieder', $_SESSION['blm_user'], array('OnlineZeit' => time() - $_SESSION['blm_lastAction']));
    Database::getInstance()->updateTableEntry('mitglieder', $_SESSION['blm_user'], array('LastAction' => date('Y-m-d H:i:s')));
    Database::getInstance()->commit();
    $_SESSION['blm_lastAction'] = time();
}

function escapeForOutput(?string $text, bool $withNl2Br = true): string
{
    if ($text === null) return "";
    $data = htmlentities(stripslashes($text), ENT_QUOTES, 'UTF-8');
    if ($withNl2Br) {
        return nl2br($data);
    } else {
        return $data;
    }
}

function getOrDefault(array $array, string $name, $default = null)
{
    if (isset($array[$name])) {
        $value = $array[$name];
        if ($default === null) {
            return $value;
        } else if (is_string($default)) {
            return $value;
        } else if (is_integer($default)) {
            return intval($value);
        } else if (is_double($default) || is_float($default)) {
            return doubleval(str_replace(',', '.', $value));
        } else {
            trigger_error("Unknown type of default '" . var_export($default, true) . "'");
            return $value;
        }
    }
    return $default;
}

function verifyOffset(int $offset, int $entriesCount, int $entriesPerPage): int
{
    if ($offset < 0) {
        return 0;
    } elseif ($entriesPerPage * $offset > $entriesCount) {
        return floor($entriesCount / $entriesPerPage);
    } else {
        return $offset;
    }
}

function createProfileLink(?int $blm_user, string $name): string
{
    if ($blm_user == 0) return $name;
    return sprintf('<a href="/?p=profil&amp;id=%d">%s</a>', $blm_user, escapeForOutput($name));
}

function createGroupLink(?int $group_id, string $name): string
{
    if ($group_id == 0) return $name;
    return sprintf('<a href="/?p=gruppe&amp;id=%d">%s</a>', $group_id, escapeForOutput($name));
}

function formatCurrency(float $amount, bool $withSuffix = true, bool $withThousandsSeparator = true, int $decimals = 2): string
{
    return number_format($amount, $decimals, ',', $withThousandsSeparator ? '.' : '') . ($withSuffix ? ' €' : '');
}

function formatWeight(float $amount, bool $withSuffix = true, int $decimals = 0, bool $withThousandsSeparator = true): string
{
    return number_format($amount, $decimals, ',', $withThousandsSeparator ? '.' : '') . ($withSuffix ? ' kg' : '');
}

function formatPoints(float $amount): string
{
    return number_format($amount, 0, "", ".");
}

function formatDate(int $date): string
{
    return date("d.m.Y", $date);
}

function formatDateTime(?int $date): string
{
    if ($date === null)
        return 'Jetzt';
    else
        return date("d.m.Y H:i:s", $date);
}

function formatTime(int $date): string
{
    return date("H:i:s", $date);
}

function formatDuration(int $amount, bool $withHours = true): string
{
    $days = floor($amount / 86400);
    if ($days > 0) {
        if ($withHours) {
            return sprintf('%d Tage %s', $days, gmdate('H:i:s', $amount % 86400));
        } else {
            return sprintf('%d Tage', $days);
        }
    } else {
        return gmdate('H:i:s', $amount % 86400);
    }
}

function formatPercent(float $amount, bool $withSuffix = true, int $precision = 2): string
{
    return number_format($amount * 100, $precision, ',', '') . ($withSuffix ? ' %' : '');
}

function createPaginationTable(string $linkBase, int $currentPage, int $entriesCount, int $entriesPerPage, string $offsetField = 'o', ?string $anchor = null): string
{
    $pages = array();
    for ($i = 0; $i < $entriesCount; $i += $entriesPerPage) {
        $page = floor($i / $entriesPerPage);
        if ($page != $currentPage) {
            $pages[] = sprintf('<a href="%s&amp;%s=%d%s">%d</a>',
                $linkBase, $offsetField, $page, $anchor == null ? '' : "#$anchor", $page + 1);
        } else {
            $pages[] = $page + 1;
        }
    }
    if (count($pages) == 0) {
        $pages[] = "1";
    }

    return sprintf('<div class="Pagination">Seite: %s</div>', implode(" | ", $pages));
}

function createDropdown(array $elementsWithIDAndName, int $selectedID, string $elementName, bool $withAllEntry = true): string
{
    $entries = array();
    if ($withAllEntry) {
        $entries[] = '<option value="">- Alle -</option>';
    }
    for ($i = 0; $i < count($elementsWithIDAndName); $i++) {
        $entry = $elementsWithIDAndName[$i];
        if ($entry["ID"] == $selectedID) {
            $entries[] = sprintf('<option value="%d" selected="selected">%s</option>', $entry["ID"], $entry["Name"]);
        } else {
            $entries[] = sprintf('<option value="%d">%s</option>', $entry["ID"], $entry["Name"]);
        }
    }
    return sprintf('<select name="%s">%s</select>', $elementName, implode("\n", $entries));
}

function createWarenDropdown(int $selectedValue, string $name, bool $withAllEntry = true, array $onlyStock = array()): string
{
    $entries = array();
    if ($withAllEntry) {
        $entries[] = '<option value="">- Alle -</option>';
    }
    for ($i = 1; $i <= count_wares; $i++) {
        if (array_key_exists('Lager' . $i, $onlyStock) && $onlyStock['Lager' . $i] == 0) continue;
        if ($i == $selectedValue) {
            $entries[] = sprintf('<option value="%d" selected="selected">%s</option>', $i, getItemName($i));
        } else {
            $entries[] = sprintf('<option value="%d">%s</option>', $i, getItemName($i));
        }
    }
    return sprintf('<select name="%s" id="%s">%s</select>', $name, $name, implode("\n", $entries));
}

function redirectTo(string $location, ?int $m = null, ?string $anchor = null): void
{
    $location = preg_replace('/&m=(\\d+)/', '', $location);
    if ($m != null) {
        $location .= "&m=" . $m;
    }
    if ($anchor != null) {
        $location .= "#" . urlencode($anchor);
    }
    header('Location: ' . $location);
    die();
}

function redirectBack(string $redirectTo, ?int $m = null): void
{
    if (!empty($_SERVER['HTTP_REFERER'])) {
        $location = str_replace("\n", '', $_SERVER['HTTP_REFERER']);
    } else {
        $location = $redirectTo;
    }

    redirectTo($location, $m);
}

function requireFieldSet(?array $array, string $field, string $redirectTo, ?string $anchor = null): void
{
    if ($array === null || !array_key_exists($field, $array) || empty($array[$field])) {
        redirectTo($redirectTo, $anchor);
    }
}

function requireEntryFound($result, string $redirectTo, int $m = 154, ?string $anchor = null): void
{
    if ($result === null || (is_array($result) && count($result) == 0)) {
        redirectTo($redirectTo, $m, $anchor);
    }
}

function requireAdmin(): void
{
    if (!isAdmin()) {
        redirectTo("/?p=index", 112, __LINE__);
    }
}

function requireLogin(): void
{
    if (!isLoggedIn()) {
        redirectTo("/?p=index", 102, __LINE__);
    }
}

function isAccessAllowedIfSitter(string $requiredRight): bool
{
    return !$_SESSION['blm_sitter'] || Database::getInstance()->getSitterPermissions($_SESSION['blm_user'], $requiredRight) === "1";
}

function restrictSitter(string $requiredRight, string $backPage = "index"): void
{
    if (!isAccessAllowedIfSitter($requiredRight)) {
        redirectTo('/?p=' . $backPage, 112, __LINE__);
    }
}

function createRandomCode(): string
{
    if (is_testing) {
        // "changeit"
        return '07313f0e320f22cbfa35cfc220508eb3ff457c7e';
    }
    return sha1(openssl_random_pseudo_bytes(32));
}

function sendMail(string $recipient, string $subject, string $message): bool
{
    if (redirect_all_mails_to_admin) {
        $subject .= ' (original recipient ' . $recipient . ')';
        $recipient = admin_email;
    }

    $headers = sprintf('From: %s <%s>
Reply-To: %s <%s>
X-Mailer: PHP
MIME-Version: 1.0
Content-type: text/html; charset=utf-8
Date: %s', admin_name, admin_email, admin_name, admin_email, date(DATE_RFC2822));

    return mail($recipient, $subject, $message, $headers, '-f ' . admin_email);
}

function createNavigationLink(string $target, string $text, string $sitterRightsRequired): ?string
{
    if (isAccessAllowedIfSitter($sitterRightsRequired)) {
        return sprintf('<div class="NaviLink" onclick="Navigation(this);"><a href="/?p=%s">%s</a></div>', $target, $text);
    }
    return null;
}

function createHelpLink(int $module, int $category, ?string $linkExtraAttributes = null): ?string
{
    if (isLoggedIn()) {
        return sprintf(' <a href="/?p=hilfe&amp;mod=%d&amp;cat=%d" %s><img class="help" src="/pics/help.gif" alt="" /></a>', $module, $category, $linkExtraAttributes);
    }
    return null;
}

function getCurrentPage(): string
{
    $p = array_key_exists('p', $_GET) ? $_GET['p'] : 'index';
    if (isLoggedIn()) {
        switch ($p) {
            case "admin":
            case "admin_test":
            case "admin_markt":
            case "admin_vertrag":
            case "admin_vertrag_einstellen":
            case "admin_vertrag_bearbeiten":
            case "admin_markt_einstellen":
            case "admin_markt_bearbeiten":
            case "admin_log_bank":
            case "admin_log_bioladen":
            case "admin_log_gruppenkasse":
            case "admin_log_login":
            case "admin_log_mafia":
            case "admin_log_vertraege":
                if (!isAdmin()) {
                    redirectTo('/?p=index', 101, __LINE__);
                }
                $page = $p;
                break;
            case "bank":
            case "bioladen":
            case "buero":
            case "forschungszentrum":
            case "gebaeude":
            case "marktplatz_liste":
            case "marktplatz_verkaufen":
            case "plantage":
            case "vertraege_liste":
            case "vertraege_neu":
            case "mafia":
            case "statistik":
            case "gruppe":
            case "gruppe_einstellungen":
            case "gruppe_mitgliederverwaltung":
            case "gruppe_diplomatie":
            case "gruppe_kasse":
            case "gruppe_logbuch":
            case "gruppe_krieg_details":
            case "rangliste":
            case "rangliste_spezial":
            case "index":
            case "impressum":
            case "regeln":
            case "changelog":
            case "einstellungen":
            case "nachrichten_lesen":
            case "nachrichten_liste":
            case "nachrichten_schreiben":
            case "notizblock":
            case "hilfe":
            case "profil":
            case "special":
                $page = $p;
                break;
            default:
                $page = "index";
                break;
        }
    } else {
        switch ($p) {
            case "anmelden":
            case "registrieren":
            case "index":
            case "regeln":
            case "impressum":
                $page = $p;
                break;
            default:
                $page = "index";
                break;
        }
    }
    return $page;
}

function buildingRequirementsMet(int $building_id, array $player): bool
{
    $attribute = 'Gebaeude' . $building_id;
    switch ($building_id) {
        case 1:
        case 2:
        case 3:
            return true;
        case 4:
        case 6:
            return $player[$attribute] > 0 || $player['Gebaeude3'] >= 5;
        case 5:
            return $player[$attribute] > 0 || ($player['Gebaeude1'] >= 8 && $player['Gebaeude2'] >= 9);
        case 7:
            return $player[$attribute] > 0 || ($player['AusgabenMafia'] >= 10000 && $player['Gebaeude1'] > 9);
        case 8:
            return $player[$attribute] > 0 || ($player['AusgabenMafia'] >= 25000 && $player['Gebaeude1'] > 11);
        default:
            return false;
    }
}

function productionRequirementsMet(int $item_id, int $plantage_level, int $research_level): bool
{
    return $item_id == 1 || $research_level > 0 && $plantage_level >= $item_id * 1.5;
}

function researchRequirementsMet(int $item_id, int $plantage_level, int $research_lab_level): bool
{
    return $item_id == 1 || $plantage_level >= $item_id * 1.5 && $research_lab_level >= $item_id * 1.5;
}

function mafiaRequirementsMet(float $points): bool
{
    return $points >= min_points_mafia;
}

function calculateProductionDataForPlayer(int $item_id, int $plantage_level, int $research_level): array
{
    return array(
        'Menge' => ($plantage_level * production_plantage_item_id_factor) + ($item_id * production_weight_item_id_factor) + production_base_amount + ($research_level * research_production_weight_factor),
        'Kosten' => production_base_cost + ($research_level * research_production_cost_factor)
    );
}

function calculateResearchDataForPlayer(int $item_id, int $research_lab_level, int $research_level, int $level_increment = 1): array
{
    return array(
        'Kosten' => (100 * $item_id) + (research_base_cost * pow(research_factor_cost, $research_level + $level_increment)),
        'Dauer' => max(research_min_duration, (research_base_duration * pow(research_factor_duration, $research_level + $level_increment)) * (1 - research_lab_bonus_factor * $research_lab_level)),
        'Punkte' => (research_base_points * pow(research_factor_points, $research_level + $level_increment))
    );
}

function calculateBuildingDataForPlayer(int $building_id, array $player, int $level_increment = 1): array
{
    switch ($building_id) {
        case 1:
            $result = array(
                'Kosten' => plantage_base_cost * pow(plantage_factor_cost, $player['Gebaeude1'] + $level_increment),
                'Dauer' => plantage_base_duration * pow(plantage_factor_duration, $player['Gebaeude1'] + $level_increment),
                'Punkte' => plantage_base_points * pow(plantage_factor_points, $player['Gebaeude1'] + $level_increment)
            );
            break;

        case 2:
            $result = array(
                'Kosten' => research_lab_base_cost * pow(research_lab_factor_cost, $player['Gebaeude2'] + $level_increment),
                'Dauer' => research_lab_base_duration * pow(research_lab_factor_duration, $player['Gebaeude2'] + $level_increment),
                'Punkte' => research_lab_base_points * pow(research_lab_factor_points, $player['Gebaeude2'] + $level_increment)
            );
            break;

        case 3:
            $result = array(
                'Kosten' => shop_base_cost * pow(shop_factor_cost, $player['Gebaeude3'] + $level_increment),
                'Dauer' => shop_base_duration * pow(shop_factor_duration, $player['Gebaeude3'] + $level_increment),
                'Punkte' => shop_base_points * pow(shop_factor_points, $player['Gebaeude3'] + $level_increment)
            );
            break;

        case 4:
            $result = array(
                'Kosten' => kebab_stand_base_cost * pow(kebab_stand_factor_cost, $player['Gebaeude4'] + $level_increment),
                'Dauer' => kebab_stand_base_duration * pow(kebab_stand_factor_duration, $player['Gebaeude4'] + $level_increment),
                'Punkte' => kebab_stand_base_points * pow(kebab_stand_factor_points, $player['Gebaeude4'] + $level_increment)
            );
            break;

        case 5:
            $result = array(
                'Kosten' => building_yard_base_cost * pow(building_yard_factor_cost, $player['Gebaeude5'] + $level_increment),
                'Dauer' => building_yard_base_duration * pow(building_yard_factor_duration, $player['Gebaeude5'] + $level_increment),
                'Punkte' => building_yard_base_points * pow(building_yard_factor_points, $player['Gebaeude5'] + $level_increment)
            );
            break;

        case 6:
            $result = array(
                'Kosten' => school_base_cost * pow(school_factor_cost, $player['Gebaeude6'] + $level_increment),
                'Dauer' => school_base_duration * pow(school_factor_duration, $player['Gebaeude6'] + $level_increment),
                'Punkte' => school_base_points * pow(school_factor_points, $player['Gebaeude6'] + $level_increment)
            );
            break;

        case 7:
            $result = array(
                'Kosten' => fence_base_cost * pow(fence_factor_cost, $player['Gebaeude7'] + $level_increment),
                'Dauer' => fence_base_duration * pow(fence_factor_duration, $player['Gebaeude7'] + $level_increment),
                'Punkte' => fence_base_points * pow(fence_factor_points, $player['Gebaeude7'] + $level_increment)
            );
            break;

        case 8:
            $result = array(
                'Kosten' => pizzeria_base_cost * pow(pizzeria_factor_cost, $player['Gebaeude8'] + $level_increment),
                'Dauer' => pizzeria_base_duration * pow(pizzeria_factor_duration, $player['Gebaeude8'] + $level_increment),
                'Punkte' => pizzeria_base_points * pow(pizzeria_factor_points, $player['Gebaeude8'] + $level_increment)
            );
            break;

        default:
            $result = array(
                'Kosten' => null,
                'Dauer' => null,
                'Punkte' => null
            );
            break;
    }

    $result['Dauer'] *= (1 - (building_yard_bonus_factor * $player['Gebaeude5']));
    return $result;
}

function calculateSellPrice(int $item_id, int $resarch_level, int $shop_level, int $school_level, ?float $rate = null): float
{
    if ($rate === null) {
        $rate = calculateSellRates()[$item_id];
    }
    return round(
        (item_price_base
            + $resarch_level * item_price_research_bonus
            + $shop_level * item_price_shop_bonus
            + $school_level * item_price_school_bonus
            + $item_id * item_price_item_id_factor
        ) * $rate
        , 2);
}

function calculateSellRates(): array
{
    if (is_testing) {
        srand(1337);
    } else {
        srand(intval(date("ymdH", time())) + crc32(random_secret));
    }
    $result = array();
    $factor = 100;
    for ($i = 1; $i <= count_wares; $i++) {
        $result[$i] = rand(wares_rate_min * $factor, wares_rate_max * $factor) / $factor;
    }
    srand(mt_rand());
    return $result;
}

function calculateInterestRates(): array
{
    if (is_testing) {
        srand(1337);
    } else {
        srand(intval(date("ymd", time())) + crc32(random_secret));
    }
    $factor = 10000;
    $result = array(
        'Debit' => rand(interest_debit_rate_min * $factor, interest_debit_rate_max * $factor) / $factor,
        'Credit' => rand(interest_credit_rate_min * $factor, interest_credit_rate_max * $factor) / $factor
    );
    srand(mt_rand());
    return $result;
}

function getLastIncomeTimestamp(): int
{
    $now = time();
    return $now - ($now % (cron_interval * 60));
}

function getIncome(int $shop_level, int $kebab_stand_level): int
{
    return (income_base + ($shop_level * income_bonus_shop) + ($kebab_stand_level * income_bonus_kebab_stand));
}

function createBBProfileLink(int $user_id, string $user_name): string
{
    return sprintf("[player=%s#%d/]", $user_name, $user_id);
}

function createBBGroupLink(int $group_id, string $group_name): string
{
    return sprintf("[group=%s#%d/]", $group_name, $group_id);
}

function createGroupNaviation(int $activePage): string
{
    $items = array('<div id="GroupNavigation">');

    if ($activePage == 0) $items[] = '<span>Board</span>';
    else $items[] = '<span><a href="/?p=gruppe">Board</a></span>';

    if ($activePage == 1) $items[] = '<span>Mitgliederverwaltung</span>';
    else $items[] = '<span><a href="/?p=gruppe_mitgliederverwaltung">Mitgliederverwaltung</a></span>';

    if ($activePage == 2) $items[] = '<span>Einstellungen</span>';
    else $items[] = '<span><a href="/?p=gruppe_einstellungen">Einstellungen</a></span>';

    if ($activePage == 3) $items[] = '<span>Diplomatie</span>';
    else $items[] = '<span><a href="/?p=gruppe_diplomatie">Diplomatie</a></span>';

    if ($activePage == 4) $items[] = '<span>Gruppenkasse</span>';
    else $items[] = '<span><a href="/?p=gruppe_kasse">Gruppenkasse</a></span>';

    if ($activePage == 5) $items[] = '<span>Logbuch</span>';
    else $items[] = '<span><a href="/?p=gruppe_logbuch">Logbuch</a></span>';

    $items[] = '<span><a href="/actions/gruppe.php?a=3&amp;token=' . $_SESSION['blm_xsrf_token'] . '" onclick="return confirm(\'Wollen Sie wirklich aus der Gruppe austreten?\');">Gruppe verlassen</a></span>';
    $items[] = '</div>';
    return implode("\n", $items);
}

function getGroupDiplomacyTypeName(int $id): string
{
    switch ($id) {
        case group_diplomacy_nap:
            return 'Nichtangriffspakt';
        case group_diplomacy_bnd:
            return 'Bündnis';
        case group_diplomacy_war:
            return 'Krieg';
        default:
            return 'Unbekannt';
    }
}

function requireXsrfToken(string $link): void
{
    if (getOrDefault($_REQUEST, 'token') !== $_SESSION['blm_xsrf_token']) {
        redirectTo($link, 160, __LINE__);
    }
}

function handleRoundEnd(): void
{
    Database::getInstance()->begin();
    $nextStart = strtotime(date('Y-m-d H:00:00', time() + game_pause_duration));

    // determine information for mail
    $expenseBuildings = '';
    foreach (Database::getInstance()->getLeaderBuildings(3) as $entry) {
        $expenseBuildings .= sprintf('<li><a href="%s/?p=profil&amp;id=%d">%s</a> (%s)</li>',
            base_url, $entry['ID'], escapeForOutput($entry['Name']), formatCurrency($entry['AusgabenGebaeude']));
    }
    $expenseResearch = '';
    foreach (Database::getInstance()->getLeaderResearch(3) as $entry) {
        $expenseResearch .= sprintf('<li><a href="%s/?p=profil&amp;id=%d">%s</a> (%s)</li>',
            base_url, $entry['ID'], escapeForOutput($entry['Name']), formatCurrency($entry['AusgabenForschung']));
    }
    $expenseMafia = '';
    foreach (Database::getInstance()->getLeaderMafia(3) as $entry) {
        $expenseMafia .= sprintf('<li><a href="%s/?p=profil&amp;id=%d">%s</a> (%s)</li>',
            base_url, $entry['ID'], escapeForOutput($entry['Name']), formatCurrency($entry['AusgabenMafia']));
    }
    $rankingPoints = '';
    $eternalPoints = 5;
    foreach (Database::getInstance()->getRanglisteUserEntries(0, 5) as $entry) {
        $rankingPoints .= sprintf('<li><a href="%s/?p=profil&amp;id=%d">%s</a> (%s)</li>',
            base_url, $entry['BenutzerID'], escapeForOutput($entry['BenutzerName']), formatPoints($entry['Punkte']));
        if (Database::getInstance()->updateTableEntryCalculate('mitglieder', $entry['BenutzerID'], array('EwigePunkte' => $eternalPoints--)) !== 1) {
            Database::getInstance()->rollBack();
            die('Could not update eternal points for player ' . $entry['ID']);
        }
    }

    $mail = <<<EOF
<html lang="de">
<body>
<p>
Hallo __NAME__,
</p>

<p>
die aktuelle Runde des Spiels &quot;<a href="__URL__">__TITLE__</a>&quot; geht zu Ende.<br/>
Die Auswertung ist abgeschlossen und folgende Spieler stachen besonders heraus:
</p>

<h3>Höchste Ausgaben für Gebäude</h3>
<ol>
    $expenseBuildings
</ol>
<h3>Höchste Ausgaben für Forschung</h3>
<ol>
    $expenseResearch
</ol>
<h3>Höchste Ausgaben für die Mafia</h3>
<ol>
    $expenseMafia
</ol>

<h3>Die höchsten Punkstände</h3>
<ol>
    $rankingPoints
</ol>

<p>
Die nächste Runde startet am __NEXT_START__, halte dich bereit.
</p>

Grüsse,<br/>
__BETREIBER__
</body>
EOF;

    $mail = str_replace(
        array('__TITLE__', '__NEXT_START__', '__BETREIBER__', '__URL__'),
        array(game_title, formatDateTime($nextStart), admin_name, base_url),
        $mail);

    // reset all accounts
    $players = Database::getInstance()->getAllPlayerIdsAndNameAndEmailAndEmailActAndLastLogin();
    foreach ($players as $player) {
        if ($player['LastLogin'] === null) {
            $status = deleteAccount($player['ID']);
            if ($status !== null) {
                Database::getInstance()->rollBack();
                die('Could not delete player ' . $player['ID'] . ' with status ' . $status);
            }
        } else {
            $status = resetAccount($player['ID']);
            if ($status !== null) {
                Database::getInstance()->rollBack();
                die('Could not reset player ' . $player['ID'] . ' with status ' . $status);
            }
        }
    }
    Database::getInstance()->commit();

    $tables = array('auftrag', 'log_bank', 'log_bioladen', 'log_gruppenkasse', 'log_login', 'log_mafia', 'log_vertraege');
    $status = Database::getInstance()->truncateTables($tables);
    if ($status !== null) {
        Database::getInstance()->rollBack();
        die('Could not reset tables with status ' . $status);
    }

    // write the last reset file
    $startDatetime = date('Y-m-d H:i:s', $nextStart);
    $lastResetFile = dirname(__FILE__) . '/../include/last_reset.inc.php';
    $fp = fopen($lastResetFile, 'w');
    if ($fp === false) {
        die('Could not create ' . $lastResetFile . ', please check for write permissions!');
    }
    fwrite($fp, "<?php
 define('last_reset', strtotime('$startDatetime'));
");
    fclose($fp);

    // send the mails to all active players
    foreach ($players as $player) {
        if ($player['EMailAct'] !== null || $player['LastLogin'] === null) continue;
        if (!sendMail($player['EMail'], game_title . ': Rundenende', str_replace('__NAME__', escapeForOutput($player['Name']), $mail))) {
            trigger_error('Could not send mail to ' . $player['EMail'], E_USER_WARNING);
        }
    }
}

function hashPassword(string $password): string
{
    return password_hash($password, password_hash_algorithm, password_hash_options);
}

function verifyPassword(string $password, string $hash): string
{
    return password_verify($password, $hash);
}

function passwordNeedsUpgrade(string $hash): bool
{
    return strlen($hash) == 40 || password_needs_rehash($hash, password_hash_algorithm, password_hash_options);
}

function maybeMafiaOpponents(int $pointsLeft, int $pointsRight, int $groupDiplomacy): bool
{
    if ($groupDiplomacy === group_diplomacy_nap || $groupDiplomacy === group_diplomacy_bnd) {
        return false;
    } else if ($groupDiplomacy === group_diplomacy_war) {
        return true;
    } else {
        $a = min($pointsLeft, $pointsRight);
        $b = max($pointsLeft, $pointsRight);
        return $a > $b / mafia_faktor_punkte && $a < $b * mafia_faktor_punkte;
    }
}
