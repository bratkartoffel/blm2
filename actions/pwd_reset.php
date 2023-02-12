<?php
/*
 * MIT Licence
 * Copyright (c) 2023 Simon Frankenberger
 *
 * Please see LICENCE.md for complete licence text.
 */
require_once __DIR__ . '/../include/functions.inc.php';
require_once __DIR__ . '/../include/database.class.php';
require_once __DIR__ . '/../include/captcha.class.php';

ob_start();

$email = getOrDefault($_POST, 'email');

function sendRecoveryMail(string $email, array $data, string $token): bool
{
    return sendMail($email, Config::get(Config::SECTION_BASE, 'game_title') . ': Passwort vergessen',
        sprintf('<html lang="de"><body><h3>Hallo %s,</h3>

die Funktion "Passwort vergessen" wurde bei deinem Account ausgelöst. Wenn du das selbst warst, dann kannst du über folgende Link
dein Passwort zurücksetzen:
<p>
    <a href="%s/actions/pwd_reset.php?a=2&amp;id=%d&amp;token=%s">%s/actions/pwd_reset.php?a=2&amp;id=%d&amp;token=%s</a>
</p>
<p>
Klicke bitte nur auf den Link, wenn du die Anfrage auch selbst ausgelöst hast. Wenn du die Anfrage nicht gestellt hast,
dann wende dich bitte an einen Administrator. Diesen kannst du entweder im Spiel oder auch per Mail erreichen: %s
</p>

Grüsse,
%s
</body></html>
', escapeForOutput($data['Name']), Config::get(Config::SECTION_BASE, 'base_url'), $data['ID'], $token, Config::get(Config::SECTION_BASE, 'base_url'), $data['ID'], $token, Config::get(Config::SECTION_BASE, 'admin_email'), Config::get(Config::SECTION_BASE, 'admin_name')
        ));
}

function sendPasswordMail(string $email, string $name, string $password): bool
{
    return sendMail($email, Config::get(Config::SECTION_BASE, 'game_title') . ': Passwort vergessen',
        sprintf('<html lang="de"><body><h3>Hallo %s,</h3>

dein Passwort wurde zurückgesetzt auf:
<p>%s</p>

Grüsse,
%s
</body></html>
', escapeForOutput($name), $password, Config::get(Config::SECTION_BASE, 'admin_name')
        ));
}

switch (getOrDefault($_REQUEST, 'a')) {
    // request reset token
    case 1:
        $captcha_code = getOrDefault($_POST, 'captcha_code');
        $captcha_id = getOrDefault($_POST, 'captcha_id', 0);
        $back_link = sprintf('/?p=passwort_vergessen&email=%s', urlencode($email));
        if (!Config::getBoolean(Config::SECTION_BASE, 'testing') && !Captcha::verifyCode($captcha_code, $captcha_id)) {
            redirectTo($back_link, 130, __LINE__);
        }

        $data = Database::getInstance()->getPlayerIdAndNameByEmail($email);
        requireEntryFound($data, '/?p=anmelden', 244);

        $request = Database::getInstance()->getPasswordRequestByUserId($data['ID']);
        if ($request !== null) {
            // existing request found, resend mail if older than 4h
            if (strtotime($request['created']) < time() - (3600 * 4)) {
                Database::getInstance()->begin();
                if (Database::getInstance()->updateTableEntry(Database::TABLE_PASSWORD_RESET, $request['ID'],
                        array('created' => date("Y-m-d H:i:s"))) !== 1) {
                    Database::getInstance()->rollBack();
                    redirectTo($back_link, 142, __LINE__);
                }
                if (!sendRecoveryMail($email, $data, $request['token'])) {
                    Database::getInstance()->rollBack();
                    redirectTo($back_link, 172, __LINE__);
                }
                Database::getInstance()->commit();
            } else {
                // mail was sent within the last 4h, so just ignore this request
                // insert minimum delay for security reasons
                usleep(random_int(300000, 800000));
            }
            redirectTo('/?p=anmelden', 244);
        }

        $token = createRandomCode();
        Database::getInstance()->begin();
        if (Database::getInstance()->createTableEntry(Database::TABLE_PASSWORD_RESET, array(
                'user_id' => $data['ID'],
                'token' => $token
            )) !== 1) {
            Database::getInstance()->rollBack();
            redirectTo($back_link, 141, __LINE__);
        }
        if (!sendRecoveryMail($email, $data, $token)) {
            Database::getInstance()->rollBack();
            redirectTo($back_link, 172, __LINE__);
        }
        Database::getInstance()->commit();
        redirectTo('/?p=anmelden', 244);
        break;

    case 2:
        $id = getOrDefault($_GET, 'id', 0);
        $token = getOrDefault($_GET, 'token');
        Database::getInstance()->begin();
        if (Database::getInstance()->deleteTableEntryWhere(Database::TABLE_PASSWORD_RESET, array('user_id' => $id, 'token' => $token)) !== 1) {
            Database::getInstance()->rollBack();
            redirectTo('/?p=passwort_vergessen', 154);
        }

        $data = Database::getInstance()->getPlayerNameAndEmailById($id);
        $pwd = createRandomPassword();
        if (Database::getInstance()->updateTableEntry(Database::TABLE_USERS, $id, array('Passwort' => hashPassword($pwd))) !== 1) {
            Database::getInstance()->rollBack();
            redirectTo('/?p=passwort_vergessen', 142, __LINE__);
        }
        if (!sendPasswordMail($data['EMail'], $data['Name'], $pwd)) {
            Database::getInstance()->rollBack();
            redirectTo('/?p=passwort_vergessen', 172, __LINE__);
        }
        Database::getInstance()->commit();
        redirectTo('/?p=anmelden', 245);
        break;

    default:
        redirectTo('/?p=anmelden', 112, __LINE__);
}
