<!DOCTYPE html>
<html lang="de">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>{{GAME_TITLE}} - Änderung EMail-Adresse</title>
</head>
<body>
<h3>Hallo {{USERNAME}},</h3>
<p>
    Du hast über die Einstellungen deine EMail-Adresse geändert. Klicke zur Bestätigung deiner neuen Adresse bitte auf
    folgenden Link:
</p>
<p>
    <a href="{{ACTIVATION_LINK}}">{{ACTIVATION_LINK}}</a>
</p>
<p>
    Wenn du die Änderung nicht selbst durchgeführt hast, so leite diese EMail bitte ohne Bearbeitung weiter an:
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