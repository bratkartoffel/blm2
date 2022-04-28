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
        if (!Database::getInstance()->existsTableEntry('mitglieder', array('ID' => $_SESSION['blm_user'], 'Passwort' => sha1($pwd_alt)))) {
            redirectTo('/?p=einstellungen', 121, __LINE__);
        }
        $passwords = Database::getInstance()->getPlayerAndSitterPasswordsById($_SESSION['blm_user']);
        if ($passwords === null) {
            redirectTo('/?p=einstellungen', 112, __LINE__);
        }
        if ($passwords['Sitter'] == sha1($new_pw1)) {
            redirectTo('/?p=einstellungen', 152, __LINE__);
        }

        Database::getInstance()->begin();
        if (Database::getInstance()->updateTableEntry('mitglieder', $_SESSION['blm_user'],
                array('Passwort' => sha1($new_pw1))) === null) {
            Database::getInstance()->rollBack();
            redirectTo('/?p=einstellungen', 141, __LINE__);
        }
        Database::getInstance()->commit();
        redirectTo('/?p=einstellungen', 219);
        break;

    // Reset account
    case 2:
        if (!Database::getInstance()->existsTableEntry('mitglieder', array('ID' => $_SESSION['blm_user'], 'Passwort' => sha1($pwd_alt)))) {
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
        if (!Database::getInstance()->existsTableEntry('mitglieder', array('ID' => $_SESSION['blm_user'], 'Passwort' => sha1($pwd_alt)))) {
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
        if (Database::getInstance()->updateTableEntry('mitglieder', $_SESSION['blm_user'],
                array('Beschreibung' => $beschreibung)) === null) {
            Database::getInstance()->rollBack();
            redirectTo('/?p=einstellungen&beschreibung=' . urlencode($beschreibung), 143, __LINE__);
        }
        Database::getInstance()->commit();
        redirectTo('/?p=einstellungen', 206);
        break;

    // Update profile picture
    case 5:
        if (filesize($_FILES['bild']['tmp_name']) > max_profile_image_size) {
            redirectTo('/?p=einstellungen', 103, __LINE__);
        }

        @unlink(sprintf('../pics/spieler/%s.jpg', $_SESSION['blm_user']));
        @unlink(sprintf('../pics/spieler/%s.png', $_SESSION['blm_user']));
        @unlink(sprintf('../pics/spieler/%s.gif', $_SESSION['blm_user']));
        if ($_FILES['bild']['size'] == 0) {
            redirectTo('/?p=einstellungen', 209);
        }

        $typ = $_FILES['bild']['type'];
        $suffix = 'dat';
        switch ($typ) {
            case 'image/jpeg':
            case 'image/jpg':
                $suffix = 'jpg';
                break;
            case 'image/gif':
                $suffix = 'gif';
                break;
            case 'image/png':
                $suffix = 'png';
                break;
            default:
                redirectTo('/?p=einstellungen', 107, __LINE__);
                break;
        }
        move_uploaded_file($_FILES['bild']['tmp_name'], sprintf('../pics/spieler/%s.%s', $_SESSION['blm_user'], $suffix));
        redirectTo('/?p=einstellungen', 210);
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
    <p>Doch bevor Sie Ihr eigenes Imperium aufbauen können, müssen Sie Ihren Account aktivieren. Klicken Sie hierzu bitte auf folgenden Link:</p>
    <p><a href="' . $email_activation_link . '">' . $email_activation_link . '</a></p>
    <p>Falls Sie sich nicht bei diesem Spiel registriert haben, so leiten Sie die EMail bitte ohne Bearbeitung weiter an: ' . admin_email . '</p>
    Grüsse ' . admin_name . '</body></html>'
        )) {
            redirectTo(sprintf('/?p=einstellungen&email=%s', $email), 150, __LINE__);
        }

        Database::getInstance()->begin();
        if (Database::getInstance()->updateTableEntry('mitglieder', $_SESSION['blm_user'],
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
            if (Database::getInstance()->deleteTableEntryWhere('sitter', array('user_id' => $_SESSION['blm_user'])) == 0) {
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
        if (Database::getInstance()->existsTableEntry('sitter', array('user_id' => $_SESSION['blm_user']))) {
            if (strlen($pw_sitter) > 0) {
                $fields['Passwort'] = sha1($pw_sitter);
                if (strlen($pw_sitter) < password_min_len) {
                    redirectTo('/?p=einstellungen', 147, __LINE__);
                }
                $passwords = Database::getInstance()->getPlayerAndSitterPasswordsById($_SESSION['blm_user']);
                if ($passwords === null) {
                    redirectTo('/?p=einstellungen', 112, __LINE__);
                }
                if ($passwords['Benutzer'] == $fields['Passwort']) {
                    redirectTo('/?p=einstellungen', 152, __LINE__);
                }
            }
            if (Database::getInstance()->updateTableEntry('sitter', null, $fields, array('user_id = :whr0' => $_SESSION['blm_user'])) === null) {
                Database::getInstance()->rollBack();
                redirectTo('/?p=einstellungen', 142, __LINE__);
            }
        } else {
            $fields['user_id'] = $_SESSION['blm_user'];
            $fields['Passwort'] = sha1($pw_sitter);

            if (strlen($pw_sitter) < password_min_len) {
                redirectTo('/?p=einstellungen', 147, __LINE__);
            }

            $passwords = Database::getInstance()->getPlayerAndSitterPasswordsById($_SESSION['blm_user']);
            if ($passwords === null) {
                redirectTo('/?p=einstellungen', 112, __LINE__);
            }
            if ($passwords['Benutzer'] == $fields['Passwort']) {
                redirectTo('/?p=einstellungen', 152, __LINE__);
            }
            if (Database::getInstance()->createTableEntry('sitter', $fields) == 0) {
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
