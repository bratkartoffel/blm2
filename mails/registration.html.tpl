<!DOCTYPE html>
<html lang="de">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>{{GAME_TITLE}} - Registrierung</title>
</head>
<body>
<h3>Hallo {{USERNAME}} und Willkommen beim Bioladenmanager 2,</h3>
<p>
    Doch bevor du dein eigenes Imperium aufbauen kannst, musst du deinen Account aktivieren. Klicke hierzu bitte
    auf folgenden Link:
</p>
<p>
    <a href="{{ACTIVATION_LINK}}">{{ACTIVATION_LINK}}</a>
</p>
<p>
    Wenn du die Registrierung nicht selbst erstellt hast, so leite diese EMail bitte ohne Bearbeitung weiter an:
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