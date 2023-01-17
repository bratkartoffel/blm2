# Der Bioladenmanager 2 (BLM 2)

> Ein einfaches Open Source Aufbaustrategie Spiel

## Installation

### 0) Voraussetzungen

- Apache 2.4+ (für `.htaccess` Zugriffsbeschränkungen)
  - mod_alias
  - (empfohlen) mod_headers
  - (empfohlen) mod_brotli und mod_setenvif
- PHP 7.4+
  - gd (mit WebP-Unterstützung)
  - mcrypt
  - pdo_mysql
  - zip
- MariaDB (10.1+) oder MySQL (5.7+)

Die automatischen Tests laufen mit:

- Apache `2.4.54`
- PHP `7.4.33`
- MariaDB `10.10.2`.

Auf dem [Livesystem](https://blm2.fraho.eu) läuft die Anwendung (Stand Jannuar 2023) mit:

- Apache `2.4`
- PHP `8.1`
- MariaDB `10.6`

### 1) Dateien entpacken / hochladen

Das Projekt kann entweder direkt als git-Repository oder entpackt aus einem Zip-Archiv installiert werden.
Hierzu einfach alle Dateien des Projekts auf dem Webspace laden.

### 2) Konfiguration erstellen

Anschliessend muss noch eine minimale Konfiguration erstellt und als `config/config.ini` gespeichert werden:

```ini
[base]

; a random secret which is used to initialize the random number generators
; for the deterministic interest and item selling rates
random_secret = "!!replace this!!"

; a random secret which is used to initialize the random number generators
upgrade_secret = "!!replace this!!"

; base url for this game (needed for absolute urls like in mails)
base_url = "https://blm2.example.com"

; operator name
admin_name = "Insert Name Here"

; operator email address
admin_email = "contact-address@example.com"

; address line 1 (for impressum)
admin_addr_line_1 = "Street Name, may be empty"

; address line 2 (for impressum)
admin_addr_line_2 = "Zip-Code and Country, may be empty"

[database]
; hostname to connect to
hostname = "localhost"
; database name to connect to
database = "blm2"
; username for connection
username = "blm2"
; password for connection
password = "blm2"
```

Die als `!!replace this!!` markierten Felder müssen mit zufälligen Werten gefüllt werden.
Dies dient der Fairness und Sicherheit des Spiels und sorgt dafür, dass die zufällig generierten Werte (unter Anderem
die Warenkurse des Bioladens und die Zinsraten der Bank) nicht im voraus berechnet werden können.
Hierzu kann der Generator von
[dieser Seite](https://www.random.org/strings/?num=5&len=20&digits=on&upperalpha=on&loweralpha=on&unique=on&format=html&rnd=new)
verwendet werden.

Eine Liste der verfügbaren Parameter kann der [Standardkonfiguration](config/config-defaults.ini) entnommen werden.
Nicht explizit gesetzte Optionen werden von dieser Datei übernommen.

**Wichtig:** Die Standardkonfiguration sollte nicht geändert werden, da diese mit jedem Release neu ausgeliefert wird.
Stattdessen sollten alle von der Standardkonfiguration abweichenden Einstellungen in der `config.ini` gesetzt werden.

### 3) Installationsprozess starten

Nachdem die Konfiguration angelegt wurde, kann die Installation der Datenbank beginnen.
Hierzu muss die `install/update.php?secret=__upgrade_secret__` aufgerufen werden.
Der Parameter `secret` muss in der Anfrage durch das konfigurierte `upgrade_secret` ersetzt werden.

Die Installation läuft automtisch ab und kann einige Sekunden dauern.
Währenddessen lädt die Seite und es wird erst nach Abschluss der Installation der Status ausgegeben.

```text
Checking installation for version 1.10.3+master
=========================================
Verifying database connection:
> OK

Checking base installation:
> Base installation not found, executing setup script
> OK

Checking for update information:
> Update information not found, execute first update script
> OK

Enumerating update scripts:
> Found 7 scripts
> Skipping sql/00-1.10.0-setup.sql
> Skipping sql/01-1.10.1-update_info.sql
> Verify update script: sql/10-1.10.2-groups-created.sql
>> Script unknown, begin execution
>> OK
> Verify update script: sql/11-1.10.3-log_marktplatz.sql
>> Script unknown, begin execution
>> OK
> Verify update script: sql/12-1.10.3-log_nachrichten.sql
>> Script unknown, begin execution
>> OK
> Verify update script: sql/13-1.10.3-fix_points.sql
>> Script unknown, begin execution
>> OK
> Verify update script: sql/14-1.10.3-drop_statistik-ProduktionPlus.sql
>> Script unknown, begin execution
>> OK
> OK

Saving update information:
> OK

Verifying existing accounts:
> No accounts found, creating new admin account
> Created new user 'admin' with password 'gCcKhP0KiSwjtXlS'
> OK

Update finished successfully!
> Execution took 1,232.94 ms
> 81 queries were executed
```

Die initiale Installtion erstellt auch einen Admin-Benutzer (`admin`) mit einem zufällig generiertem Passwort.
Das Passwort wird in der Ausgabe angezeigt und ist in dem obigen Beispiel `gCcKhP0KiSwjtXlS`.

### Spiel aktualisieren

Nach der Installation einer neuen Version sollte das `update.php` nochmals aufgerufen werden.
Dies sorgt dafür, dass etwaige Datenbankänderungen nachinstalliert werden.

Anpassungen an der `config.ini` werden hingegen **nicht** automatisch durchgeführt, diese müssen manuell eingetragen
werden.
Welche Parameter sich zwischen den Versionen geändert haben können dem [CHANGELOG.md](CHANGELOG.md) entnommen werden.

## Entwicklung

### Voraussetzungen

- Git
- Docker mit docker-compose
- [Java 17+](https://adoptium.net/temurin/releases/) (für Tests und closure-compiler)
    - [closure-compiler.jar](https://search.maven.org/artifact/com.google.javascript/closure-compiler) (Minify
      Javascript)
- [NodeJS LTS 18+](https://nodejs.org/en/) (für csso-cli)
    - [csso-cli](https://www.npmjs.com/package/csso-cli) (Minify CSS Stylesheets)

### Minify Javascript

Die Javascript Dateien werden mittels des closure-compilers optimiert und minimiert.
Das sorgt dafür, dass die eingebundenen Javascript Bibliotheken schneller geladen werden.

Linux / MacOs:

```shell
java -jar ~/bin/closure-compiler-v20230103.jar --compilation_level SIMPLE_OPTIMIZATIONS --js js/functions.js
```

Windows:

```shell
java -jar %HOME%\bin\closure-compiler-v20230103.jar --compilation_level SIMPLE_OPTIMIZATIONS --js js\functions.js
```

### Minify CSS

Die CSS Dateien werden mittels csso optimiert und minimiert.
Das sorgt dafür, dass die eingebundenen CSS Stylesheets schneller geladen werden.
Da csso-cli nur als Linux Shell-Script vorliegt, kann dieses unter Windows nur über die Git-Bash (oder WSL) ausgeführt
werden.

Initial muss `csso-cli` via `npm` installiert werden:
```npm install -g csso-cli```

Anschlissend können alle Styles mit folgendem Script minimiert werden:

```shell
#!/bin/sh
export NODE_HOME=~/devel/node-v18.13.0-linux-x64
export PATH=$NODE_HOME:$PATH

for file in $( find styles -name \*.css -and -not -name \*.min.\* ); do
  $NODE_HOME/node_modules/csso-cli/bin/csso \
    -i $file -o ${file%%.*}.min.css --no-restructure --stat
done
```

### IDE (PhpStorm)

Die primäre Entwicklung wird mit der IDE [PhpStorm](https://www.jetbrains.com/phpstorm/) durchgeführt.
Um die CSS und Javascript Dateien automatisch mit dem Minify zu konvertieren können "Watcher" auf den Dateien angelegt
werden.

Im [watchers.xml](development/watchers.xml) liegen die Watchers als Beispielkonfiguration.
Diese können in den Einstellungen unter `File / Settings / Tools > File Watchers` importiert werden.

### Tests

Das Spiel wird mit jeder Änderung durch automatisierte Tests überpüft.
Hierzu wird ein Java-Projekt mit JUnit und Selenium im `tests/` Ordner verwendet.
Die Tests benötigen eine spezielle Konfiguration des Spiels.
Hierzu muss der [docker-compose stack](tests/src/test/resources/docker-compose.yaml) aus dem `src/test/resources` Ordner
gestartet sein. Anschlissend können die Tests aus der IDE oder mittels `./gradlew test` ausgeführt werden.
