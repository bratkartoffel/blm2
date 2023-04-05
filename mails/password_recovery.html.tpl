<!DOCTYPE html>
<html lang="de">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>{{GAME_TITLE}} - Passwort vergessen</title>
</head>
<body>
<h3>Hallo {{USERNAME}},</h3>
<p>
    Die Funktion "Passwort vergessen" wurde bei deinem Account ausgelöst. Wenn du das selbst warst, dann kannst du über
    folgenden Link dein Passwort zurücksetzen:
</p>
<p>
    <a href="{{RESET_LINK}}">{{RESET_LINK}}</a>
</p>
<p>
    Klicke bitte nur auf den Link, wenn du die Anfrage auch selbst ausgelöst hast.
</p>
<p>
    Wenn du die Anfrage nicht selbst erstellt hast, so leite diese EMail bitte ohne Bearbeitung weiter an:
    <code>{{ADMIN_EMAIL}}</code>
</p>
<p>
    Grüsse,<br>
    {{ADMIN_NAME}}
</p>

<footer>
    <p>
        Diese EMail wurde vom Spiel {{GAME_TITLE}}, erreichbar unter <a href="{{GAME_URL}}">{{GAME_URL}}</a> verschickt.
    </p>
</footer>
</body>
</html>