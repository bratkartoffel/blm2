<?php
require_once('../include/config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/database.class.php');

ob_start();
requireLogin();
restrictSitter('NeverAllow');

$pwd_alt = getOrDefault($_POST, 'pwd_alt');
$new_pw1 = getOrDefault($_POST, 'new_pw1');
$new_pw2 = getOrDefault($_POST, 'new_pw2');

switch (getOrDefault($_POST, 'a', 0)) {
    // Change password
    case 1:
        if ($new_pw1 != $new_pw2) {
            redirectTo('/?p=einstellungen', 105, __LINE__);
        }
        if (strlen($new_pw1) < password_min_len) {
            redirectTo('/?p=einstellungen', 147, __LINE__);
        }
        $passwords = Database::getInstance()->getPlayerAndSitterPasswordsById($_SESSION['blm_user']);
        requireEntryFound($passwords, '/?p=einstellungen', 112, __LINE__);
        if (!verifyPassword($pwd_alt, $passwords['Benutzer'])) {
            redirectTo('/?p=einstellungen', 121, __LINE__);
        }
        if ($passwords['Sitter'] != null && verifyPassword($new_pw1, $passwords['Sitter'])) {
            redirectTo('/?p=einstellungen', 152, __LINE__);
        }

        Database::getInstance()->begin();
        if (Database::getInstance()->updateTableEntry(Database::TABLE_USERS, $_SESSION['blm_user'],
                array('Passwort' => hashPassword($new_pw1))) === null) {
            Database::getInstance()->rollBack();
            redirectTo('/?p=einstellungen', 141, __LINE__);
        }
        Database::getInstance()->commit();
        redirectTo('/?p=einstellungen', 219);
        break;

    // Reset account
    case 2:
        $passwords = Database::getInstance()->getPlayerAndSitterPasswordsById($_SESSION['blm_user']);
        requireEntryFound($passwords, '/?p=einstellungen', 112, __LINE__);
        if (!verifyPassword($pwd_alt, $passwords['Benutzer'])) {
            redirectTo('/?p=einstellungen', 121, __LINE__);
        }
        Database::getInstance()->begin();
        $resetStatus = resetAccount($_SESSION['blm_user']);
        if ($resetStatus !== null) {
            Database::getInstance()->rollBack();
            redirectTo('/?p=einstellungen&rs=' . $resetStatus, 151, __LINE__);
        } else {
            Database::getInstance()->commit();
            redirectTo('/?p=einstellungen', 220);
        }
        break;

    // Delete account
    case 3:
        $passwords = Database::getInstance()->getPlayerAndSitterPasswordsById($_SESSION['blm_user']);
        requireEntryFound($passwords, '/?p=einstellungen', 112, __LINE__);
        if (!verifyPassword($pwd_alt, $passwords['Benutzer'])) {
            redirectTo('/?p=einstellungen', 121, __LINE__);
        }
        Database::getInstance()->begin();
        $status = deleteAccount($_SESSION['blm_user']);
        if ($status !== null) {
            Database::getInstance()->rollBack();
            redirectTo('/?p=einstellungen', 143, __LINE__ . '_' . $status);
        }
        Database::getInstance()->commit();
        session_destroy();
        redirectTo('/?p=index', 205);
        break;

    // Change description
    case 4:
        $beschreibung = getOrDefault($_POST, 'beschreibung');
        if (strlen($beschreibung) == 0) $beschreibung = null;

        Database::getInstance()->begin();
        if (Database::getInstance()->updateTableEntry(Database::TABLE_USERS, $_SESSION['blm_user'],
                array('Beschreibung' => $beschreibung)) === null) {
            Database::getInstance()->rollBack();
            redirectTo('/?p=einstellungen&beschreibung=' . urlencode($beschreibung), 143, __LINE__);
        }
        Database::getInstance()->commit();
        redirectTo('/?p=einstellungen', 206);
        break;

    // Update profile picture
    case 5:

        Database::getInstance()->begin();
        if (Database::getInstance()->updateTableEntry(Database::TABLE_USERS, $_SESSION['blm_user'], array('LastImageChange' => date('Y-m-d H:i:s'))) !== 1) {
            Database::getInstance()->rollBack();
            redirectTo('/?p=gruppe_einstellungen', 142, __LINE__);
        }
        Database::getInstance()->commit();
        $status = uploadProfilePicture($_FILES['bild'], sprintf('../pics/uploads/u_%d.webp', $_SESSION['blm_user']));
        if ($status > 0) {
            redirectTo('/?p=einstellungen', $status, __LINE__);
        } else {
            redirectTo('/?p=einstellungen', 210);
        }
        break;

    // Change email address
    case 6:
        $email = getOrDefault($_POST, 'email');
        $confirm = getOrDefault($_POST, 'confirm');

        if ($email != $confirm) {
            redirectTo('/?p=einstellungen&email=' . urlencode($email), 149, __LINE__);
        }

        $email_activation_code = createRandomCode();
        $email_activation_link = base_url . '/actions/activate.php?email=' . urlencode($email) . '&amp;code=' . $email_activation_code;

        if (!sendMail($email, game_title . ': Aktivierung Ihres Accounts',
            '<html lang="de"><body><h3>Willkommen beim Bioladenmanager 2,</h3>
    <p>Doch bevor Sie Ihr eigenes Imperium aufbauen k??nnen, m??ssen Sie Ihren Account aktivieren. Klicken Sie hierzu bitte auf folgenden Link:</p>
    <p><a href="' . $email_activation_link . '">' . $email_activation_link . '</a></p>
    <p>Falls Sie sich nicht bei diesem Spiel registriert haben, so leiten Sie die EMail bitte ohne Bearbeitung weiter an: ' . admin_email . '</p>
    Gr??sse ' . admin_name . '</body></html>'
        )) {
            redirectTo(sprintf('/?p=einstellungen&email=%s', $email), 150, __LINE__);
        }

        Database::getInstance()->begin();
        if (Database::getInstance()->updateTableEntry(Database::TABLE_USERS, $_SESSION['blm_user'],
                array('EMail' => $email, 'EMailAct' => $email_activation_code)) === null) {
            Database::getInstance()->rollBack();
            redirectTo('/?p=einstellungen', 141, __LINE__);
        }
        Database::getInstance()->commit();
        session_destroy();
        redirectTo('/?p=index', 238);
        break;

    // Update sitter rights
    case 7:
        $aktiviert = getOrDefault($_POST, 'aktiviert', 0);
        $pw_sitter = getOrDefault($_POST, 'pw_sitter');
        $gebaeude = getOrDefault($_POST, 'gebaeude', 0);
        $forschung = getOrDefault($_POST, 'forschung', 0);
        $produktion = getOrDefault($_POST, 'produktion', 0);
        $nachrichten = getOrDefault($_POST, 'nachrichten', 0);
        $gruppe = getOrDefault($_POST, 'gruppe', 0);
        $vertraege = getOrDefault($_POST, 'vertraege', 0);
        $marktplatz = getOrDefault($_POST, 'marktplatz', 0);
        $bioladen = getOrDefault($_POST, 'bioladen', 0);
        $bank = getOrDefault($_POST, 'bank', 0);

        Database::getInstance()->begin();
        if (!$aktiviert) {
            if (Database::getInstance()->deleteTableEntryWhere(Database::TABLE_SITTER, array('user_id' => $_SESSION['blm_user'])) == 0) {
                Database::getInstance()->rollBack();
                redirectTo('/?p=einstellungen', 143, __LINE__);
            }
            Database::getInstance()->commit();
            redirectTo('/?p=einstellungen', 239);
        }

        $fields = array(
            'Gebaeude' => $gebaeude,
            'Forschung' => $forschung,
            'Produktion' => $produktion,
            'Nachrichten' => $nachrichten,
            'Gruppe' => $gruppe,
            'Vertraege' => $vertraege,
            'Marktplatz' => $marktplatz,
            'Bioladen' => $bioladen,
            'Bank' => $bank
        );
        if (Database::getInstance()->existsTableEntry(Database::TABLE_SITTER, array('user_id' => $_SESSION['blm_user']))) {
            if (strlen($pw_sitter) > 0) {
                if (strlen($pw_sitter) < password_min_len) {
                    Database::getInstance()->rollBack();
                    redirectTo('/?p=einstellungen', 147, __LINE__);
                }
                $passwords = Database::getInstance()->getPlayerAndSitterPasswordsById($_SESSION['blm_user']);
                requireEntryFound($passwords, '/?p=einstellungen', 112, __LINE__);
                if (verifyPassword($pw_sitter, $passwords['Benutzer'])) {
                    Database::getInstance()->rollBack();
                    redirectTo('/?p=einstellungen', 152, __LINE__);
                }
                $fields['Passwort'] = hashPassword($pw_sitter);
            }
            if (Database::getInstance()->updateTableEntry(Database::TABLE_SITTER, null, $fields, array('user_id = :whr0' => $_SESSION['blm_user'])) === null) {
                Database::getInstance()->rollBack();
                redirectTo('/?p=einstellungen', 142, __LINE__);
            }
        } else {
            if (strlen($pw_sitter) < password_min_len) {
                Database::getInstance()->rollBack();
                redirectTo('/?p=einstellungen', 147, __LINE__);
            }

            $passwords = Database::getInstance()->getPlayerAndSitterPasswordsById($_SESSION['blm_user']);
            requireEntryFound($passwords, '/?p=einstellungen', 112, __LINE__);
            if (verifyPassword($pw_sitter, $passwords['Benutzer'])) {
                Database::getInstance()->rollBack();
                redirectTo('/?p=einstellungen', 152, __LINE__);
            }
            $fields['user_id'] = $_SESSION['blm_user'];
            $fields['Passwort'] = hashPassword($pw_sitter);
            if (Database::getInstance()->createTableEntry(Database::TABLE_SITTER, $fields) == 0) {
                Database::getInstance()->rollBack();
                redirectTo('/?p=einstellungen', 141, __LINE__);
            }
        }

        Database::getInstance()->commit();
        redirectTo('/?p=einstellungen', 240);
        break;

    // unknown action
    default:
        redirectTo('/?p=einstellungen', 112, __LINE__);
        break;
}
