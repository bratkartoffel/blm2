<?php
/*
 * MIT Licence
 * Copyright (c) 2023 Simon Frankenberger
 *
 * Please see LICENCE.md for complete licence text.
 */

require_once __DIR__ . '/config.class.php';

// enum constant for group diplomacy types
const group_diplomacy_nap = 1;
const group_diplomacy_bnd = 2;
const group_diplomacy_war = 3;

// enum constants for mafia actions
const mafia_action_espionage = 1;
const mafia_action_robbery = 2;
const mafia_action_heist = 3;
const mafia_action_attack = 4;

// enum constants for buildings
const building_plantage = 1;
const building_research_lab = 2;
const building_shop = 3;
const building_kebab_stand = 4;
const building_building_yard = 5;
const building_school = 6;
const building_fence = 7;
const building_pizzeria = 8;
const building_bank = 9;

// enum constants for items
const item_potatoes = 1;
const item_carrots = 2;
const item_tomatoes = 3;
const item_salad = 4;
const item_apples = 5;
const item_pears = 6;
const item_cherries = 7;
const item_bananas = 8;
const item_cucumbers = 9;
const item_grapes = 10;
const item_tobaco = 11;
const item_pineapple = 12;
const item_strawberries = 13;
const item_oranges = 14;
const item_kiwi = 15;

// enum constant for the job types
// factor to separate the various types
const job_type_factor = 100;
// building has 1xx
const job_type_building = 1;
// production has 2xx
const job_type_production = 2;
// research has 3xx
const job_type_research = 3;

// number of implemented wares
const count_wares = 15;

// number of implemented items
const count_buildings = 9;

function abortWithErrorPage(string $body)
{
    http_response_code(500);
    die(sprintf('<!DOCTYPE html><html lang="de"><body><img src="/pics/big/clock.webp" alt="maintenance"/><h2>%s</h2></body></html>', $body));
}

function verifyInstallation()
{
    if (!file_exists(__DIR__ . '/../config/config.ini')) {
        abortWithErrorPage('<h2 class="red">Ungültige Installation</h2><p>Es konnte keine <code>config.ini</code> gefunden werden.</p>');
    }
    try {
        Database::getInstanceForInstallCheck();
    } catch (PDOException $e) {
        abortWithErrorPage('<h2 class="red">Ungültige Installation</h2><p>Es konnte keine Datenbankverbindung hergestellt werden, bitte überprüfe deine <code>config.inc.php</code></p>');
    }
    if (!Database::getInstance()->tableExists('mitglieder')) {
        abortWithErrorPage('<h2 class="red">Ungültige Installation</h2><p>Datenbankschema nicht installiert, bitte führe ein Update durch.</p>');
    }
}

function getOrderChefboxDescription(int $order_type): string
{
    switch (floor($order_type / job_type_factor)) {
        case job_type_building:
            $result = sprintf('G: %s', getBuildingName($order_type % job_type_factor));
            break;
        case job_type_production:
            $result = sprintf('A: %s', getItemName($order_type % job_type_factor));
            break;
        case job_type_research:
            $result = sprintf('F: %s', getItemName($order_type % job_type_factor));
            break;
        default:
            trigger_error(sprintf('invalid order_type given: %d', $order_type), E_USER_ERROR);
    }
    if (strlen($result) > 14) {
        $result = substr($result, 0, 14) . '...';
    }
    return $result;
}

function CheckAuftraege(int $blm_user): bool
{
    $auftraege = Database::getInstance()->getAllExpiredAuftraegeByVon($blm_user);

    foreach ($auftraege as $auftrag) {
        switch (floor($auftrag['item'] / job_type_factor)) {
            // Gebäude
            case job_type_building:
                if (Database::getInstance()->updateTableEntryCalculate(Database::TABLE_USERS, $blm_user,
                        array('Gebaeude' . ($auftrag['item'] % job_type_factor) => 1)) != 1) {
                    return false;
                }
                break;

            // Produktion
            case job_type_production:
                if (Database::getInstance()->updateTableEntryCalculate(Database::TABLE_USERS, $blm_user,
                        array('Lager' . ($auftrag['item'] % job_type_factor) => $auftrag['amount'])) != 1) {
                    return false;
                }
                break;

            // Forschung
            case job_type_research:
                if (Database::getInstance()->updateTableEntryCalculate(Database::TABLE_USERS, $blm_user,
                        array('Forschung' . ($auftrag['item'] % job_type_factor) => 1)) != 1) {
                    return false;
                }
                break;

            // Unknown
            default:
                break;
        }
        if (Database::getInstance()->deleteTableEntry(Database::TABLE_JOBS, $auftrag['ID']) != 1) {
            return false;
        }
    }
    return true;
}

function isRoundOver(): bool
{
    return Config::getInt(Config::SECTION_DBCONF, 'roundstart') + Config::getInt(Config::SECTION_BASE, 'game_round_duration') <= time();
}

function isGameLocked(): bool
{
    return (Config::getInt(Config::SECTION_DBCONF, 'roundstart') >= time());
}

function getMessageBox(int $msg_id): ?string
{
    if ($msg_id == 0) {
        return null;
    }
    if ($msg_id >= 200 && $msg_id < 300) {
        $image = 'MessageOk';
    } else {
        $image = 'MessageError';
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
            $text = 'Das Bild ist zu gross. Die maximale Grösse des Bildes ist ' . (Config::getInt(Config::SECTION_BASE, 'max_profile_image_size') / 1024) . ' KB.';
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
            $text = 'Die hochgeladene Datei ist kein Bild vom Typ jpg, gif, png oder webp!';
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
            $text = 'Bei einem Krieg muss der Betrag, um welchen gekämpft wird, größer als ' . formatCurrency(Config::getInt(Config::SECTION_GROUP, 'war_min_amount')) . ' sein!';
            break;
        case 133:
            $text = "Bitte geben Sie eine Dauer zwischen 1 und " . Config::getInt(Config::SECTION_PLANTAGE, 'production_hours_max') . " Stunden ein!";
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
            $text = sprintf('Der neue Benutzer wurde zwar erstellt, jedoch konnte die Aktivierungsmail nicht versendet werden. Bitte wende dich per EMail an den Admin: <a href="mailto:%s">%s</a>', Config::get(Config::SECTION_BASE, 'admin_email'), Config::get(Config::SECTION_BASE, 'admin_email'));
            break;
        case 145:
            $text = 'Sie müssen zuerst mal ein Forschungszentrum bauen, bevor Sie Forschungen starten können!';
            break;
        case 146:
            $text = 'Der Benutzername darf nur zwischen ' . Config::getInt(Config::SECTION_BASE, 'username_min_len') . ' und ' . Config::getInt(Config::SECTION_BASE, 'username_max_len') . ' Zeichen enthalten';
            break;
        case 147:
            $text = 'Das gewählte Passwort ist zu kurz, es muss mindestens aus ' . Config::getInt(Config::SECTION_BASE, 'password_min_len') . ' Zeichen bestehen.';
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
            $text = 'Ungültiger Gruppenname (Darf nur maximal ' . Config::getInt(Config::SECTION_GROUP, 'max_name_length') . ' Zeichen lang sein)';
            break;
        case 159:
            $text = 'Ungültiges Gruppenkürzel  (Darf nur maximal ' . Config::getInt(Config::SECTION_GROUP, 'max_tag_length') . ' Zeichen lang sein)';
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
            $text = 'Der Name darf kein "#" enthalten';
            break;
        case 165:
            $text = 'Sie können keine diplomatische Beziehung mit Ihnen selbst eingehen';
            break;
        case 166:
            $text = 'In der Gruppenkasse befindet sich genügend Geld für den Krieg';
            break;
        case 167:
            $text = 'Eine diplomatische Beziehung kann erst nach frühestens ' . Config::getInt(Config::SECTION_GROUP, 'diplomacy_min_duration') . ' Tagen aufgekündigt werden';
            break;
        case 168:
            $text = 'Sie können sich selbst keine Nachrichten schicken!';
            break;
        case 169:
            $text = 'Die Mafia ist erst ab ' . formatPoints(Config::getFloat(Config::SECTION_MAFIA, 'min_points')) . ' Punkten verfügbar';
            break;
        case 170:
            $text = 'Die Mafia erholt sich noch vom letzten Auftrag.';
            break;
        case 171:
            $text = 'Sind sie verwirrt? Sie können sich nicht selbst angreifen';
            break;
        case 172:
            $text = 'Die EMail konnte nicht gesendet werden, bitte wende dich an einen Administrator.';
            break;
        case 173:
            $text = 'Sie können sich selbst keine Verträge schicken.';
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
        case 244:
            $text = 'Falls die angegebene Adresse registriert ist, so wurde eine EMail mit den weiteren Schritten an diese gesendet.';
            break;
        case 245:
            $text = 'Das Passwort wurde erfolgreich zurückgesetzt und Ihnen in einer neuen EMail zugeschickt';
            break;
        case 246:
            $text = 'Der Benutzer wurde gelöscht.';
            break;
        case 247:
            $text = 'Der Benutzer wurde gespeichert.';
            break;
        case 248:
            $text = 'Die Gruppe wurde gespeichert.';
            break;


        case 999:
            $text = sprintf('Das Spiel ist zur Zeit pausiert.<br />Die neue Runde startet am %s', date("d.m.Y \u\m H:i", Config::getInt(Config::SECTION_DBCONF, 'roundstart')));
            break;
        default:
            $text = sprintf('Meldungsnummer konnte nicht gefunden werden: %d', $msg_id);
            break;
    }

    return sprintf('<div class="MessageBox" id="meldung_%d" %s>
            <div class="MessageImage" id="%s"></div>
            <a id="close">X</a>
            <span>%s</span>
            <script nonce="%s">document.getElementById(\'close\').onclick = () => document.getElementById(\'meldung_%d\').remove();</script>
        </div>',
        $msg_id,
        ($msg_id == 207 || $msg_id == 220 || $msg_id == 222) ? 'reload-chefbox' : '',
        $image, $text, getCspNonce(), $msg_id);
}

function getBuildingName(int $building_id): string
{
    switch ($building_id) {
        case building_plantage:
            return 'Plantage';
        case building_research_lab:
            return 'Forschungszentrum';
        case building_shop:
            return 'Bioladen';
        case building_kebab_stand:
            return 'Dönerstand';
        case building_building_yard:
            return 'Bauhof';
        case building_school:
            return 'Verkäuferschule';
        case building_fence:
            return 'Zaun';
        case building_pizzeria:
            return 'Pizzeria';
        case building_bank:
            return 'Bankschliessfach';
        default:
            trigger_error(sprintf('invalid building_id given: %d', $building_id), E_USER_ERROR);
    }
}

function getItemName(int $item_id): string
{
    switch ($item_id) {
        case item_potatoes:
            return 'Kartoffeln';
        case item_carrots:
            return 'Karotten';
        case item_tomatoes:
            return 'Tomaten';
        case item_salad:
            return 'Salat';
        case item_apples:
            return 'Äpfel';
        case item_pears:
            return 'Birnen';
        case item_cherries:
            return 'Kirschen';
        case item_bananas:
            return 'Bananen';
        case item_cucumbers:
            return 'Gurken';
        case item_grapes:
            return 'Weintrauben';
        case item_tobaco:
            return 'Tabak';
        case item_pineapple:
            return 'Ananas';
        case item_strawberries:
            return 'Erdbeeren';
        case item_oranges:
            return 'Orangen';
        case item_kiwi:
            return 'Kiwi';
        default:
            trigger_error(sprintf('invalid item_id given: %d', $item_id), E_USER_ERROR);
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
    $result = escapeForOutput($text);
    $result = preg_replace(
        array(
            '/\[center](.*)\[\/center]/Uis',
            "/\[url=&quot;(https?:\/\/|www.|https?:\/\/www.)([a-z\d\-_.]{3,32}\.[a-z]{2,4})&quot;](.*)\[\/url]/SiU",
            "/\[img=&quot;(https?:\/\/[a-z\d\-_.\/]{3,32}\.[a-z]{3,4})&quot;](.*)\[\/img]/SiU",
            "@\[player=(.+)#(\d{1,8})/]@SUi",
            "@\[group=(.+)#(\d{1,8})/]@SUi",
        ),
        array(
            '<div class="center">\1</div>',
            '<a href="\1\2">\3</a>',
            '<a href="\1" target="_blank"><img src="\1" alt="\2"/></a>',
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
                "/\[([bui])](.*)\[\/\\1]/Uis",
                '/\[quote](.*)\[\/quote]/Uism'
            ),
            array(
                '<\1>\2</\1>',
                '<blockquote>\1</blockquote>'
            ),
            $result);
    }

    return $result;
}

function deleteAccount(int $blm_user): ?string
{
    // reset everything associated with this user
    $status = resetAccount($blm_user);
    if ($status !== null) {
        return 'reset_' . $status;
    }

    // delete all data which was not removed by the reset
    if (Database::getInstance()->deleteTableEntryWhere(Database::TABLE_PASSWORD_RESET, array('user_id' => $blm_user)) === null) {
        return 'delete_' . Database::TABLE_PASSWORD_RESET;
    }
    if (Database::getInstance()->deleteTableEntryWhere(Database::TABLE_SITTER, array('user_id' => $blm_user)) === null) {
        return 'delete_' . Database::TABLE_SITTER;
    }
    if (Database::getInstance()->deleteTableEntryWhere(Database::TABLE_STATISTICS, array('user_id' => $blm_user)) === null) {
        return 'delete_' . Database::TABLE_STATISTICS;
    }
    if (Database::getInstance()->deleteTableEntryWhere(Database::TABLE_MESSAGES, array('An' => $blm_user)) === null) {
        return 'delete_' . Database::TABLE_MESSAGES;
    }
    if (Database::getInstance()->deleteTableEntry(Database::TABLE_USERS, $blm_user) === null) {
        return 'delete_' . Database::TABLE_USERS;
    }

    // delete his profile picture
    @unlink(sprintf("../pics/uploads/u_%d.webp", $blm_user));
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
            return 'delete_group_' . $status;
        }
        @unlink(sprintf("../pics/uploads/g_%d.webp", $player['Gruppe']));
    }

    // reset all values to the starting defaults
    if (Database::getInstance()->updateTableEntry(Database::TABLE_USERS, $blm_user,
            Config::getSection(Config::SECTION_STARTING_VALUES)) === null) {
        return 'reset_' . Database::TABLE_USERS;
    }

    // delete all other data associated with this user
    $deleteTables = array(
        Database::TABLE_JOBS => 'user_id',
        Database::TABLE_MARKET => 'Von',
        Database::TABLE_SITTER => 'user_id',
        Database::TABLE_GROUP_RIGHTS => 'user_id',
        Database::TABLE_GROUP_CASH => 'user_id',
        Database::TABLE_GROUP_MESSAGES => 'Von',
        Database::TABLE_CONTRACTS => 'Von',
        Database::TABLE_STATISTICS => 'user_id',
    );
    foreach ($deleteTables as $table => $field) {
        if (Database::getInstance()->deleteTableEntryWhere($table, array($field => $blm_user)) === null) {
            return 'delete_' . $table;
        }
    }

    // recreate the statistics entry
    if (Database::getInstance()->createTableEntry(Database::TABLE_STATISTICS, array('user_id' => $blm_user)) === null) {
        return 'create_' . Database::TABLE_STATISTICS;
    }

    // handle all contracts which where sent to this user
    $data = Database::getInstance()->getAllContractsByAnEquals($blm_user);
    foreach ($data as $entry) {
        if (Database::getInstance()->updateTableEntryCalculate(Database::TABLE_USERS, $entry['Von'],
                array('Lager' . $entry['Was'] => $entry['Menge'])) !== 1) {
            return 'retract_contract_' . Database::TABLE_USERS;
        }
    }
    if (Database::getInstance()->deleteTableEntryWhere(Database::TABLE_CONTRACTS, array('An' => $blm_user)) === null) {
        return 'retract_contract_' . Database::TABLE_CONTRACTS;
    }

    // Delete all message from System to the given user
    if (Database::getInstance()->deleteTableEntryWhere(Database::TABLE_MESSAGES, array('Von' => 0, 'An' => $blm_user)) === null) {
        return 'delete_' . Database::TABLE_MESSAGES . '_' . __LINE__;
    }

    return null;
}

function updateLastAction(): void
{
    if (!isGameLocked()) {
        Database::getInstance()->updatePlayerOnlinezeit($_SESSION['blm_user']);
    }
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
            if ($value === "") return $default;
            else return intval($value);
        } else if (is_double($default) || is_float($default)) {
            return doubleval(str_replace(',', '.', $value));
        } else {
            error_log(sprintf('Unknown type of default: "%s"', var_export($default, true)));
            return $value;
        }
    }
    if (is_callable($default)) {
        return $default();
    } else {
        return $default;
    }
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

function createProfileLink(?int $blm_user, string $name, string $page = 'profil'): string
{
    if ($blm_user === null || $blm_user === 0) return $name;
    return sprintf('<a href="/?p=%s&amp;id=%d">%s</a>', $page, $blm_user, escapeForOutput($name));
}

function createGroupLink(?int $group_id, string $name): string
{
    if ($group_id == 0) return $name;
    return sprintf('<a href="/?p=gruppe&amp;id=%d">%s</a>', $group_id, escapeForOutput($name));
}

function formatCurrency(float $amount, bool $withSuffix = true, bool $withThousandsSeparator = true, int $decimals = 2): string
{
    if (substr(getOrDefault($_SERVER, 'HTTP_ACCEPT_LANGUAGE', 'de'), 0, 2) === 'en') {
        return number_format($amount, $decimals, '.', $withThousandsSeparator ? ',' : '') . ($withSuffix ? ' €' : '');
    } else {
        return number_format($amount, $decimals, ',', $withThousandsSeparator ? '.' : '') . ($withSuffix ? ' €' : '');
    }
}

function formatWeight(float $amount, bool $withSuffix = true, int $decimals = 0, bool $withThousandsSeparator = true): string
{
    return number_format($amount, $decimals, ',', $withThousandsSeparator ? '.' : '') . ($withSuffix ? ' kg' : '');
}

function formatPoints(int $amount): string
{
    return number_format($amount, 0, "", ".");
}

function formatDate(int $date): string
{
    if ($date > 0) {
        return date("d.m.Y", $date);
    } else {
        return 'Nie';
    }
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

function createDropdown(array $elementsWithIDAndName, ?int $selectedID, string $elementName, bool $withAllEntry = true, bool $withSystemEntry = false, bool $withNoneEntry = false): string
{
    $entries = array();
    if ($withAllEntry) {
        $entries[] = '<option value="">- Alle -</option>';
    }
    if ($withSystemEntry) {
        $entries[] = '<option value="0">- System -</option>';
    }
    if ($withNoneEntry) {
        if ($selectedID === null || $selectedID === -1) {
            $entries[] = '<option value="-1" selected>- Kein -</option>';
        } else {
            $entries[] = '<option value="-1">- Kein -</option>';
        }
    }
    for ($i = 0; $i < count($elementsWithIDAndName); $i++) {
        $entry = $elementsWithIDAndName[$i];
        if ($entry["ID"] == $selectedID) {
            $entries[] = sprintf('<option value="%d" selected>%s</option>', $entry["ID"], $entry["Name"]);
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

function redirectBack(string $redirectTo, ?int $m = null, ?string $anchor = null): void
{
    if (!empty($_SERVER['HTTP_REFERER'])) {
        $location = str_replace("\n", '', $_SERVER['HTTP_REFERER']);
    } else {
        $location = $redirectTo;
    }

    redirectTo($location, $m, $anchor);
}

function requireFieldSet(?array $array, string $field, string $redirectTo, ?string $anchor = null): void
{
    if ($array === null || !array_key_exists($field, $array) || empty($array[$field])) {
        redirectTo($redirectTo, $anchor);
    }
}

function requireEntryFound($result, string $redirectTo, int $m = 154, ?string $anchor = null)
{
    if ($result === null || (is_array($result) && count($result) == 0)) {
        redirectTo($redirectTo, $m, $anchor);
    }
    return $result;
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

function isAccessAllowedIfSitter(?string $requiredRight): bool
{
    if ($requiredRight === null) return true;
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
    if (Config::getBoolean(Config::SECTION_BASE, 'testing')) {
        // "changeit"
        return '07313f0e320f22cbfa35cfc220508eb3ff457c7e';
    }
    return sha1(openssl_random_pseudo_bytes(32));
}

function createRandomPassword(): string
{
    if (Config::getBoolean(Config::SECTION_BASE, 'testing')) {
        return 'changeit';
    }
    return str_replace('+', '_', base64_encode(openssl_random_pseudo_bytes(12)));
}

function sendMail(string $recipient, string $subject, string $message): bool
{
    if (Config::getBoolean(Config::SECTION_BASE, 'testing')) {
        return true;
    }

    if (Config::getBoolean(Config::SECTION_BASE, 'redirect_all_mails_to_admin')) {
        $subject .= ' (original recipient ' . $recipient . ')';
        $recipient = Config::get(Config::SECTION_BASE, 'admin_email');
    }

    $headers = array(
        'From' => sprintf('%s <%s>', Config::get(Config::SECTION_BASE, 'admin_name'), Config::get(Config::SECTION_BASE, 'admin_email')),
        'Reply-To' => sprintf('%s <%s>', Config::get(Config::SECTION_BASE, 'admin_name'), Config::get(Config::SECTION_BASE, 'admin_email')),
        'X-Mailer' => 'PHP/blm2-' . game_version,
        'Date' => date(DATE_RFC2822),
        'MIME-Version' => '1.0',
        'Content-type' => 'text/html; charset=utf-8',
    );

    return mail($recipient, $subject, $message, $headers, '-f ' . Config::get(Config::SECTION_BASE, 'admin_email'));
}

function createNavigationLink(string $target, string $text, ?string $sitterRightsRequired = null): string
{
    if (isAccessAllowedIfSitter($sitterRightsRequired)) {
        return sprintf('<div class="NaviLink"><a href="/?p=%s" id="link_%s" class="%s">%s</a></div>%s',
            $target, $target, $target === getCurrentPage() ? "active" : "inactive", $text, "\n");
    }
    return "";
}

function createHelpLink(int $module, int $category): string
{
    if (isLoggedIn()) {
        return sprintf(' <a href="/?p=hilfe&amp;mod=%d&amp;cat=%d" id="link_show_help"><img id="help_image" src="/pics/style/help.webp" alt="Hilfe" /></a>', $module, $category);
    }
    return "";
}

function getCurrentPage(): string
{
    $p = array_key_exists('p', $_GET) ? $_GET['p'] : 'index';
    if (isLoggedIn()) {
        switch ($p) {
            case "admin":
            case "admin_benutzer":
            case "admin_benutzer_bearbeiten":
            case "admin_gruppe":
            case "admin_gruppe_bearbeiten":
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
            case "admin_log_marktplatz":
            case "admin_log_nachrichten":
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
            case "passwort_vergessen":
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
        case building_plantage:
        case building_research_lab:
        case building_shop:
            return true;
        case building_kebab_stand:
        case building_school:
            return $player[$attribute] > 0 || $player['Gebaeude' . building_shop] >= 5;
        case building_building_yard:
            return $player[$attribute] > 0 || ($player['Gebaeude' . building_plantage] >= 8 && $player['Gebaeude' . building_research_lab] >= 9);
        case building_fence:
        case building_pizzeria:
            return $player[$attribute] > 0 || mafiaRequirementsMet($player['Punkte']);
        case building_bank:
            return $player[$attribute] > 0 || ($player['Gebaeude' . building_plantage] >= 20 && $player['EinnahmenZinsen'] >= 100000);
        default:
            return false;
    }
}

function productionRequirementsMet(int $item_id, int $plantage_level, int $research_level): bool
{
    return $item_id == item_potatoes || $research_level > 0 && $plantage_level >= $item_id * 1.5;
}

function researchRequirementsMet(int $item_id, int $plantage_level, int $research_lab_level): bool
{
    return $item_id == item_potatoes || $plantage_level >= $item_id * 1.5 && $research_lab_level >= $item_id * 1.5;
}

function mafiaRequirementsMet(float $points): bool
{
    return $points >= Config::getFloat(Config::SECTION_MAFIA, 'min_points');
}

function calculateProductionDataForPlayer(int $item_id, int $plantage_level, int $research_level): array
{
    return array(
        'Menge' => ($plantage_level * Config::getInt(Config::SECTION_PLANTAGE, 'production_amount_per_level')) + ($item_id * Config::getInt(Config::SECTION_PLANTAGE, 'production_amount_per_item_id')) + Config::getInt(Config::SECTION_PLANTAGE, 'production_base_amount') + ($research_level * Config::getInt(Config::SECTION_RESEARCH_LAB, 'production_amount_per_level')),
        'Kosten' => round(Config::getInt(Config::SECTION_PLANTAGE, 'production_base_cost') + ($research_level * Config::getInt(Config::SECTION_RESEARCH_LAB, 'production_cost_per_level')), 2)
    );
}

function calculateResearchDataForPlayer(int $item_id, int $research_lab_level, int $research_level, int $level_increment = 1): array
{
    return array(
        'Kosten' => round((Config::getInt(Config::SECTION_RESEARCH_LAB, 'cost_item_id_factor') * $item_id) + (Config::getInt(Config::SECTION_RESEARCH_LAB, 'research_base_cost') * pow(Config::getFloat(Config::SECTION_RESEARCH_LAB, 'research_factor_cost'), $research_level + $level_increment)), 2),
        'Dauer' => (int)floor(max(Config::getInt(Config::SECTION_RESEARCH_LAB, 'research_min_duration'), (Config::getInt(Config::SECTION_RESEARCH_LAB, 'research_base_duration') * pow(Config::getFloat(Config::SECTION_RESEARCH_LAB, 'research_factor_duration'), $research_level + $level_increment)) * pow(1 - Config::getFloat(Config::SECTION_RESEARCH_LAB, 'bonus_factor'), $research_lab_level))),
    );
}

function calculateBuildingDataForPlayer(int $building_id, array $player, int $level_increment = 1): array
{
    switch ($building_id) {
        case building_plantage:
            $section = Config::SECTION_PLANTAGE;
            break;
        case building_research_lab:
            $section = Config::SECTION_RESEARCH_LAB;
            break;
        case building_shop:
            $section = Config::SECTION_SHOP;
            break;
        case building_kebab_stand:
            $section = Config::SECTION_KEBAB_STAND;
            break;
        case building_building_yard:
            $section = Config::SECTION_BUILDING_YARD;
            break;
        case building_school:
            $section = Config::SECTION_SCHOOL;
            break;
        case building_fence:
            $section = Config::SECTION_FENCE;
            break;
        case building_pizzeria:
            $section = Config::SECTION_PIZZERIA;
            break;
        case building_bank:
            $section = Config::SECTION_BANK;
            break;
        default:
            trigger_error(sprintf('Unknown building id given: %d, %d, %d', $building_id, $player['ID'], $level_increment), E_USER_ERROR);
    }

    $result = array(
        'Kosten' => round(Config::getInt($section, 'base_cost') * pow(Config::getFloat($section, 'factor_cost'), $player['Gebaeude' . $building_id] + $level_increment), 2),
        'Dauer' => Config::getInt($section, 'base_duration') * pow(Config::getFloat($section, 'factor_duration'), $player['Gebaeude' . $building_id] + $level_increment),
    );

    $result['Dauer'] *= pow(1 - Config::getFloat(Config::SECTION_BUILDING_YARD, 'bonus_factor'), $player['Gebaeude' . building_building_yard]);
    $result['Dauer'] = (int)floor($result['Dauer']);
    $result['Punkte'] = floor($result['Kosten'] / Config::getInt(Config::SECTION_BASE, 'expense_points_factor'));
    return $result;
}

function calculateSellPrice(int $item_id, int $resarch_level, int $shop_level, int $school_level, ?float $rate = null): float
{
    if ($rate === null) {
        $rate = calculateSellRates()[$item_id];
    }
    return round(
        (Config::getFloat(Config::SECTION_SHOP, 'item_price_base')
            + $resarch_level * Config::getFloat(Config::SECTION_SHOP, 'item_price_research_bonus')
            + $shop_level * Config::getFloat(Config::SECTION_SHOP, 'item_price_shop_bonus')
            + $school_level * Config::getFloat(Config::SECTION_SHOP, 'item_price_school_bonus')
            + $item_id * Config::getFloat(Config::SECTION_SHOP, 'item_price_item_id_factor')
        ) * $rate
        , 2);
}

function calculateSellRates(): array
{
    if (Config::getBoolean(Config::SECTION_BASE, 'testing')) {
        srand(1337);
    } else {
        srand(intval(date("ymdH", time())) + crc32(Config::get(Config::SECTION_BASE, 'random_secret')));
    }
    $result = array();
    $factor = 100;
    for ($i = 1; $i <= count_wares; $i++) {
        $result[$i] = rand(Config::getFloat(Config::SECTION_SHOP, 'sell_rate_min') * $factor, Config::getFloat(Config::SECTION_SHOP, 'sell_rate_max') * $factor) / $factor;
    }
    srand(mt_rand());
    return $result;
}

function calculateInterestRates(): array
{
    if (Config::getBoolean(Config::SECTION_BASE, 'testing')) {
        srand(1337);
    } else {
        srand(intval(date("ymd", time())) + crc32(Config::get(Config::SECTION_BASE, 'random_secret')));
    }
    $factor = 10000;
    $result = array(
        'Debit' => rand(Config::getFloat(Config::SECTION_BANK, 'interest_debit_rate_min') * $factor, Config::getFloat(Config::SECTION_BANK, 'interest_debit_rate_max') * $factor) / $factor,
        'Credit' => rand(Config::getFloat(Config::SECTION_BANK, 'interest_credit_rate_min') * $factor, Config::getFloat(Config::SECTION_BANK, 'interest_credit_rate_max') * $factor) / $factor
    );
    srand(mt_rand());
    return $result;
}

function calculateResetCreditLimit(): int
{
    $resetMedianRates = (Config::getFloat(Config::SECTION_BANK, 'interest_credit_rate_min') + Config::getFloat(Config::SECTION_BANK, 'interest_credit_rate_max')) / 2;
    $resetCreditLimit = (int)(Config::getInt(Config::SECTION_BANK, 'credit_limit') * pow(1 + $resetMedianRates, 96));
    $resetCreditLimit -= 10000 + ($resetCreditLimit % 10000);
    return $resetCreditLimit;
}

function getRandomRate(float $min, float $max)
{
    $factor = 10000;
    if (Config::getBoolean(Config::SECTION_BASE, 'testing')) {
        return ($min + $max) / 2;
    }
    return mt_rand($min * $factor, $max * $factor) / $factor;
}

function getLastIncomeTimestamp(): int
{
    return Config::getInt(Config::SECTION_DBCONF, 'lastcron');
}

function getIncome(int $shop_level, int $kebab_stand_level): int
{
    return (Config::getInt(Config::SECTION_BASE, 'income_base')
        + ($shop_level * Config::getInt(Config::SECTION_SHOP, 'income_bonus'))
        + ($kebab_stand_level * Config::getInt(Config::SECTION_KEBAB_STAND, 'income_bonus')));
}

function createBBProfileLink(int $user_id, string $user_name): string
{
    return sprintf("[player=%s#%d/]", $user_name, $user_id);
}

function createBBGroupLink(int $group_id, string $group_name): string
{
    return sprintf("[group=%s#%d/]", $group_name, $group_id);
}

function createGroupNaviation(int $activePage, int $group_id): string
{
    $items = array();

    if ($activePage == 0) $items[] = '<span>Board</span>';
    else $items[] = '<span><a href="/?p=gruppe" id="gruppe_board">Board</a></span>';

    if ($activePage == 1) $items[] = '<span>Mitgliederverwaltung</span>';
    else $items[] = '<span><a href="/?p=gruppe_mitgliederverwaltung" id="gruppe_mitgliederverwaltung">Mitgliederverwaltung</a></span>';

    if ($activePage == 2) $items[] = '<span>Einstellungen</span>';
    else $items[] = '<span><a href="/?p=gruppe_einstellungen" id="gruppe_einstellungen">Einstellungen</a></span>';

    $count = Database::getInstance()->countPendingGroupDiplomacy($group_id);
    if ($activePage == 3) $items[] = sprintf('<span>Diplomatie (%d)</span>', $count);
    else $items[] = sprintf('<span><a href="/?p=gruppe_diplomatie" id="gruppe_diplomatie">Diplomatie (%d)</a></span>', $count);

    if ($activePage == 4) $items[] = '<span>Gruppenkasse</span>';
    else $items[] = '<span><a href="/?p=gruppe_kasse" id="gruppe_kasse">Gruppenkasse</a></span>';

    if ($activePage == 5) $items[] = '<span>Logbuch</span>';
    else $items[] = '<span><a href="/?p=gruppe_logbuch" id="gruppe_logbuch">Logbuch</a></span>';

    $items[] = '<span><a href="/actions/gruppe.php?a=3&amp;token=' . $_SESSION['blm_xsrf_token'] . '" id="leave_group">Gruppe verlassen</a></span>';
    return sprintf('<div id="GroupNavigation">%s</div>
<script nonce="' . getCspNonce() . '">
    document.getElementById(\'leave_group\').onclick = () => confirm(\'Wollen Sie wirklich aus der Gruppe austreten?\');
</script>', implode(" | ", $items));
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
    $nextStart = strtotime(date('Y-m-d H:00:00', time() + Config::getInt(Config::SECTION_BASE, 'game_pause_duration')));

    // determine information for mail
    $expenseBuildings = '';
    foreach (Database::getInstance()->getLeaderBuildings(3) as $entry) {
        $expenseBuildings .= sprintf('<li><a href="%s/?p=profil&amp;id=%d">%s</a> (%s)</li>',
            Config::get(Config::SECTION_BASE, 'base_url'), $entry['ID'], escapeForOutput($entry['Name']), formatCurrency($entry['AusgabenGebaeude']));
    }
    $expenseResearch = '';
    foreach (Database::getInstance()->getLeaderResearch(3) as $entry) {
        $expenseResearch .= sprintf('<li><a href="%s/?p=profil&amp;id=%d">%s</a> (%s)</li>',
            Config::get(Config::SECTION_BASE, 'base_url'), $entry['ID'], escapeForOutput($entry['Name']), formatCurrency($entry['AusgabenForschung']));
    }
    $expenseMafia = '';
    foreach (Database::getInstance()->getLeaderMafia(3) as $entry) {
        $expenseMafia .= sprintf('<li><a href="%s/?p=profil&amp;id=%d">%s</a> (%s)</li>',
            Config::get(Config::SECTION_BASE, 'base_url'), $entry['ID'], escapeForOutput($entry['Name']), formatCurrency($entry['AusgabenMafia']));
    }
    $rankingPoints = '';
    $eternalPoints = 5;
    foreach (Database::getInstance()->getRanglisteUserEntries(0, 5) as $entry) {
        $rankingPoints .= sprintf('<li><a href="%s/?p=profil&amp;id=%d">%s</a> (%s)</li>',
            Config::get(Config::SECTION_BASE, 'base_url'), $entry['BenutzerID'], escapeForOutput($entry['BenutzerName']), formatPoints($entry['Punkte']));
        if (Database::getInstance()->updateTableEntryCalculate(Database::TABLE_USERS, $entry['BenutzerID'], array('EwigePunkte' => $eternalPoints--)) !== 1) {
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
die aktuelle Runde des Spiels "<a href="__URL__">__TITLE__</a>" geht zu Ende.<br/>
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
        array(Config::get(Config::SECTION_BASE, 'game_title'), formatDateTime($nextStart), Config::get(Config::SECTION_BASE, 'admin_name'), Config::get(Config::SECTION_BASE, 'base_url')),
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

    $tables = array(Database::TABLE_JOBS, Database::TABLE_LOG_BANK, Database::TABLE_LOG_SHOP,
        Database::TABLE_LOG_GROUP_CASH, Database::TABLE_LOG_LOGIN, Database::TABLE_LOG_MAFIA,
        Database::TABLE_LOG_MARKET, Database::TABLE_LOG_MESSAGES, Database::TABLE_LOG_CONTRACTS);
    $status = Database::getInstance()->truncateTables($tables);
    if ($status !== null) {
        Database::getInstance()->rollBack();
        die('Could not reset tables with status ' . $status);
    }

    if (Database::getInstance()->updateTableEntry(Database::TABLE_RUNTIME_CONFIG, null, array('conf_value' => $nextStart), array('conf_name' => 'roundstart')) !== 1) {
        Database::getInstance()->rollBack();
        die('Could not set new roundstart');
    }

    // send the mails to all active players
    foreach ($players as $player) {
        if ($player['EMailAct'] !== null || $player['LastLogin'] === null) continue;
        if (!sendMail($player['EMail'], Config::get(Config::SECTION_BASE, 'game_title') . ': Rundenende', str_replace('__NAME__', escapeForOutput($player['Name']), $mail))) {
            trigger_error(sprintf("Could not send mail to %s", $player['EMail']), E_USER_WARNING);
        }
    }
}

function hashPassword(string $password): string
{
    return password_hash($password, PASSWORD_ARGON2ID, Config::get(Config::SECTION_BASE, 'password_hash_options'));
}

function verifyPassword(string $password, string $hash): string
{
    return password_verify($password, $hash);
}

function passwordNeedsUpgrade(string $hash): bool
{
    return password_needs_rehash($hash, PASSWORD_ARGON2ID, Config::get(Config::SECTION_BASE, 'password_hash_options'));
}

function maybeMafiaOpponents(int $pointsLeft, int $pointsRight, ?int $groupDiplomacy): bool
{
    if ($groupDiplomacy === group_diplomacy_nap || $groupDiplomacy === group_diplomacy_bnd) {
        return false;
    } else if ($groupDiplomacy === group_diplomacy_war) {
        return true;
    } else {
        $a = min($pointsLeft, $pointsRight);
        $b = max($pointsLeft, $pointsRight);
        return $a >= $b / Config::getFloat(Config::SECTION_MAFIA, 'points_factor') && $a <= $b * Config::getFloat(Config::SECTION_MAFIA, 'points_factor');
    }
}

function createPlayerDropdownForMafia(int $opponent, float $myPoints, int $myId, ?int $myGroup): ?string
{
    if ($myPoints < Config::getFloat(Config::SECTION_MAFIA, 'min_points')) return null;
    $data = Database::getInstance()->getAllPlayerIdAndNameWhereMafiaPossible($myPoints, $myId, $myGroup, Config::getFloat(Config::SECTION_MAFIA, 'points_factor'));
    $entries = array();
    foreach ($data as $entry) {
        $entries[] = sprintf('<option value="%d"%s>%s</option>', $entry['ID'], $entry['ID'] == $opponent ? ' selected' : '', $entry['Name']);
    }
    if (count($entries) == 0) {
        return '<select name="opponent" id="opponent" disabled><option>Keine verfügbaren Gegner</option></select>';
    } else {
        return '<select name="opponent" id="opponent">' . implode("\n", $entries) . '</select>';
    }
}

function uploadProfilePicture(array $file, string $filename): int
{
    if (filesize($file['tmp_name']) > Config::getInt(Config::SECTION_BASE, 'max_profile_image_size')) {
        return 103;
    }

    @unlink($filename);
    if ($file['size'] == 0) {
        return 209;
    }

    switch ($file['type']) {
        case 'image/jpeg':
        case 'image/jpg':
            $data = imagecreatefromjpeg($file['tmp_name']);
            break;
        case 'image/gif':
            $data = imagecreatefromgif($file['tmp_name']);
            break;
        case 'image/png':
            $data = imagecreatefrompng($file['tmp_name']);
            break;
        case 'image/webp':
            $data = imagecreatefromwebp($file['tmp_name']);
            break;
        default:
            return 107;
    }
    imagepalettetotruecolor($data);
    imagewebp($data, $filename, 50);
    imagedestroy($data);
    return 0;
}

function obfuscate(string $text): string
{
    $parts = explode('@', $text, 2);
    if (count($parts) == 1) {
        return bin2hex($text);
    } else {
        return bin2hex($parts[0]) . '@' . $parts[1];
    }
}

function getMafiaConfigSection(int $action): string
{
    switch ($action) {
        case mafia_action_espionage:
            return Config::SECTION_MAFIA_ESPIONAGE;
        case mafia_action_robbery:
            return Config::SECTION_MAFIA_ROBBERY;
        case mafia_action_heist:
            return Config::SECTION_MAFIA_HEIST;
        case mafia_action_attack:
            return Config::SECTION_MAFIA_ATTACK;
        default:
            trigger_error(sprintf('invalid mafia action given: %d', $action), E_USER_ERROR);
    }
}

function getMafiaChance(string $cfgSection, int $level, int $pizzeriaLevel = 0, int $fenceLevel = 0): float
{
    $chance = floatval(Config::get($cfgSection, "chance")[$level]);
    $chance += $pizzeriaLevel * Config::getFloat(Config::SECTION_PIZZERIA, 'mafia_bonus');
    $chance -= $fenceLevel * Config::getFloat(Config::SECTION_FENCE, 'mafia_bonus');
    if (Config::getBoolean(Config::SECTION_BASE, 'testing')) {
        return $chance;
    } else {
        return max(0.01, min($chance, 0.95));
    }
}

function calculateDepositLimit(int $bankLevel): float
{
    $limit = pow(Config::getFloat(Config::SECTION_BANK, 'bonus_factor_upgrade'), $bankLevel) * Config::getInt(Config::SECTION_BANK, 'deposit_limit');
    return ceil($limit / 50000) * 50000;
}

function trimAndRemoveControlChars(string $string): string
{
    // remove all control characters and trim spaces
    // https://stackoverflow.com/a/66587087
    return trim(preg_replace('/[^\PCc^\PCn^\PCs]/u', '', $string));
}

function getCspNonce(): string
{
    $_REQUEST['CSP_NONCE'] = getOrDefault($_REQUEST, 'CSP_NONCE', bin2hex(openssl_random_pseudo_bytes(12)));
    return $_REQUEST['CSP_NONCE'];
}

function sendCspHeader(): void
{
    header(sprintf("Content-Security-Policy: script-src 'nonce-%s'; img-src 'self' data:; style-src 'nonce-%s';", getCspNonce(), getCspNonce()));
}

function printHeaderCss(array $styles): void
{
    foreach ($styles as $style) {
        printf('<link rel="stylesheet" type="text/css" href="%s?%s" nonce="%s"/>' . "\n", $style, game_version, getCspNonce());
    }
}

function printHeaderJs(array $scripts): void
{
    foreach ($scripts as $script) {
        printf('<script src="%s?%s" nonce="%s"></script>' . "\n", $script, game_version, getCspNonce());
    }
}

function getAllBuildingFields(string $separator = ','): string
{
    return getAllFields('Gebaeude', count_buildings, $separator);
}

function getAllResearchFields(string $separator = ','): string
{
    return getAllFields('Forschung', count_wares, $separator);
}

function getAllStockFields(string $separator = ','): string
{
    return getAllFields('Lager', count_wares, $separator);
}

function getAllFields(string $prefix, int $count, string $separator = ','): string
{
    $result = array();
    for ($i = 1; $i <= $count; $i++) {
        $result[] = $prefix . $i;
    }
    return implode($separator, $result);
}

// set the timezone for this game
date_default_timezone_set(Config::get(Config::SECTION_BASE, 'timezone'));

// check for maintenance
if (Config::getBoolean(Config::SECTION_BASE, "maintenance_active")) {
    if (defined('IS_CRON')) {
        die("Maintenance active\n");
    } else {
        abortWithErrorPage(Config::get(Config::SECTION_BASE, 'maintenance_message'));
    }
}

// start a http session
session_start();
