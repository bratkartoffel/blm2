<!DOCTYPE html>
<html lang="de">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>{{GAME_TITLE}} - Rundenende</title>
</head>
<body>
<h3>Hallo {{USERNAME}},</h3>
<p>
    Die aktuelle Runde des Bioladenmanagers geht zu Ende. Die Auswertung ist in vollem Gange, die folgenden Spieler
    stachen aber bereits besonders hervor:
</p>

<h3>Höchste Ausgaben für Gebäude</h3>
<ol>
    {{EXPENSE_BUILDINGS_LIST}}
</ol>

<h3>Höchste Ausgaben für Forschung</h3>
<ol>
    {{EXPENSE_RESEARCH_LIST}}
</ol>


<h3>Höchste Ausgaben für Mafia</h3>
<ol>
    {{EXPENSE_MAFIA_LIST}}
</ol>

<h3>Die meisten Mafia Aktionen</h3>
<ol>
    {{MAFIA_GODFATHER_LIST}}
</ol>

<h3>Die Mafia Opfer</h3>
<ol>
    {{MAFIA_VICTIM_LIST}}
</ol>

<h3>MMafia Aktionen nach Typ:</h3>
<ol>
    {{MAFIA_ATTACKS_LIST}}
</ol>

<h3>Die höchsten Punkstände</h3>
<ol>
    {{POINTS_LIST}}
</ol>

<p>
    Vielen Dank, dass du deinen Teil zum Spiel beigetragen hast. Hoffentlich bist du bei der nächsten Runde auch wieder
    am Start. Die nächste Runde startet voraussichtlich am {{NEXT_START}}, halte dich bereit.
</p>

<p>
    Grüsse,<br>
    {{ADMIN_NAME}}
</p>

<footer>
    <p>
        Diese EMail wurde vom Spiel {{GAME_TITLE}}, erreichbar unter <a href="{{GAME_URL}}">{{GAME_URL}}</a> an alle
        registrierten und aktivierten Benutzer verschickt.
    </p>
</footer>
</body>
</html>