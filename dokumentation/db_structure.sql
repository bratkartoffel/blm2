-- phpMyAdmin SQL Dump
-- version 3.0.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 24. Januar 2009 um 17:38
-- Server Version: 5.0.67
-- PHP-Version: 5.2.6

SET FOREIGN_KEY_CHECKS = 0;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";

--
-- Datenbank: `d0076b20`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `auftrag`
--

DROP TABLE IF EXISTS `auftrag`;
CREATE TABLE `auftrag`
(
    `ID`     int(11)        NOT NULL auto_increment,
    `Was`    int(11)        NOT NULL,
    `Von`    int(11)        NOT NULL,
    `Start`  int(11)        NOT NULL,
    `Dauer`  int(11)        NOT NULL,
    `Menge`  int(11)       default NULL,
    `Kosten` decimal(10, 2) NOT NULL,
    `Punkte` decimal(8, 2) default NULL,
    PRIMARY KEY (`ID`),
    UNIQUE KEY `Was` (`Was`, `Von`),
    KEY `Von` (`Von`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `changelog`
--

DROP TABLE IF EXISTS `changelog`;
CREATE TABLE `changelog`
(
    `ID`        int(11)                               NOT NULL auto_increment,
    `Datum`     date                                  NOT NULL,
    `Kategorie` varchar(64) collate utf8_unicode_ci   NOT NULL,
    `Aenderung` varchar(1024) collate utf8_unicode_ci NOT NULL,
    PRIMARY KEY (`ID`)
) ENGINE = MyISAM
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `forschung`
--

DROP TABLE IF EXISTS `forschung`;
CREATE TABLE `forschung`
(
    `ID`          int(11)     NOT NULL auto_increment,
    `Forschung1`  smallint(2) NOT NULL default '0',
    `Forschung2`  smallint(2) NOT NULL default '0',
    `Forschung3`  smallint(2) NOT NULL default '0',
    `Forschung4`  smallint(2) NOT NULL default '0',
    `Forschung5`  smallint(2) NOT NULL default '0',
    `Forschung6`  smallint(2) NOT NULL default '0',
    `Forschung7`  smallint(2) NOT NULL default '0',
    `Forschung8`  smallint(2) NOT NULL default '0',
    `Forschung9`  smallint(2) NOT NULL default '0',
    `Forschung10` smallint(2) NOT NULL default '0',
    `Forschung11` smallint(2) NOT NULL default '0',
    `Forschung12` smallint(2) NOT NULL default '0',
    `Forschung13` smallint(2)          default '0',
    `Forschung14` smallint(2)          default '0',
    `Forschung15` smallint(2) NOT NULL default '0',
    PRIMARY KEY (`ID`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `gebaeude`
--

DROP TABLE IF EXISTS `gebaeude`;
CREATE TABLE `gebaeude`
(
    `ID`        int(11)     NOT NULL auto_increment,
    `Gebaeude1` smallint(2) NOT NULL default '1',
    `Gebaeude2` smallint(2) NOT NULL default '0',
    `Gebaeude3` smallint(2) NOT NULL default '1',
    `Gebaeude4` smallint(2) NOT NULL default '0',
    `Gebaeude5` smallint(2) NOT NULL default '0',
    `Gebaeude6` smallint(2) NOT NULL default '0',
    `Gebaeude7` smallint(2) NOT NULL default '0',
    `Gebaeude8` smallint(2) NOT NULL default '0',
    PRIMARY KEY (`ID`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `gruppe`
--

DROP TABLE IF EXISTS `gruppe`;
CREATE TABLE `gruppe`
(
    `ID`           int(11)                             NOT NULL auto_increment,
    `Name`         varchar(32) collate utf8_unicode_ci NOT NULL,
    `Kuerzel`      varchar(6) collate utf8_unicode_ci  NOT NULL,
    `Beschreibung` varchar(2048) collate utf8_unicode_ci        default NULL,
    `Passwort`     char(40) collate utf8_unicode_ci    NOT NULL,
    `Kasse`        decimal(12, 2)                      NOT NULL default '0.00',
    PRIMARY KEY (`ID`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `gruppe_diplomatie`
--

DROP TABLE IF EXISTS `gruppe_diplomatie`;
CREATE TABLE `gruppe_diplomatie`
(
    `ID`          int(11)       NOT NULL auto_increment,
    `Von`         int(11)       NOT NULL,
    `An`          int(11)       NOT NULL,
    `Typ`         int(11)       NOT NULL,
    `Seit`        int(11)                default NULL,
    `Bis`         int(11)                default NULL,
    `PunktePlus`  decimal(9, 2) NOT NULL default '0.00',
    `PunkteMinus` decimal(9, 2) NOT NULL default '0.00',
    `Betrag`      int(11)                default NULL,
    PRIMARY KEY (`ID`),
    UNIQUE KEY `Von_2` (`Von`, `An`),
    KEY `Von` (`Von`),
    KEY `An` (`An`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `gruppe_logbuch`
--

DROP TABLE IF EXISTS `gruppe_logbuch`;
CREATE TABLE `gruppe_logbuch`
(
    `ID`      int(11)                               NOT NULL auto_increment,
    `Gruppe`  int(11)                               NOT NULL,
    `Spieler` int(11)                               NOT NULL,
    `Datum`   int(11)                               NOT NULL,
    `Text`    varchar(2048) collate utf8_unicode_ci NOT NULL,
    PRIMARY KEY (`ID`),
    KEY `Spieler` (`Spieler`),
    KEY `Gruppe_2` (`Gruppe`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `gruppe_nachrichten`
--

DROP TABLE IF EXISTS `gruppe_nachrichten`;
CREATE TABLE `gruppe_nachrichten`
(
    `ID`        int(11)                               NOT NULL auto_increment,
    `Von`       int(11)                               NOT NULL,
    `Gruppe`    int(11)                               NOT NULL,
    `Nachricht` varchar(4096) collate utf8_unicode_ci NOT NULL,
    `Zeit`      int(11)                               NOT NULL,
    PRIMARY KEY (`ID`),
    KEY `Von` (`Von`),
    KEY `Gruppe` (`Gruppe`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `lagerhaus`
--

DROP TABLE IF EXISTS `lagerhaus`;
CREATE TABLE `lagerhaus`
(
    `ID`      int(11) NOT NULL auto_increment,
    `Lager1`  int(5)  NOT NULL default '0',
    `Lager2`  int(5)  NOT NULL default '0',
    `Lager3`  int(5)  NOT NULL default '0',
    `Lager4`  int(5)  NOT NULL default '0',
    `Lager5`  int(5)  NOT NULL default '0',
    `Lager6`  int(5)  NOT NULL default '0',
    `Lager7`  int(5)  NOT NULL default '0',
    `Lager8`  int(5)  NOT NULL default '0',
    `Lager9`  int(5)  NOT NULL default '0',
    `Lager10` int(5)  NOT NULL default '0',
    `Lager11` int(5)  NOT NULL default '0',
    `Lager12` int(5)  NOT NULL default '0',
    `Lager13` int(5)  NOT NULL default '0',
    `Lager14` int(5)  NOT NULL default '0',
    `Lager15` int(5)  NOT NULL default '0',
    PRIMARY KEY (`ID`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;
-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `log_bank`
--

CREATE TABLE IF NOT EXISTS `log_bank`
(
    `Wer`       int(11)       NOT NULL,
    `Wann`      datetime      NOT NULL,
    `Wieviel`   decimal(9, 2) NOT NULL,
    `Einzahlen` tinyint(1)    NOT NULL,
    PRIMARY KEY (`Wer`, `Wann`),
    KEY `Wer` (`Wer`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci
  ROW_FORMAT = COMPACT;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `log_bioladen`
--

CREATE TABLE IF NOT EXISTS `log_bioladen`
(
    `Wer`     int(11)       NOT NULL,
    `Wann`    datetime      NOT NULL,
    `Was`     int(11)       NOT NULL,
    `Wieviel` int(11)       NOT NULL,
    `Preis`   decimal(4, 2) NOT NULL,
    PRIMARY KEY (`Wer`, `Wann`, `Was`),
    KEY `Wer` (`Wer`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci
  ROW_FORMAT = COMPACT;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `log_gruppenkasse`
--

CREATE TABLE IF NOT EXISTS `log_gruppenkasse`
(
    `Wer`     int(11)       NOT NULL,
    `Wen`     int(11)       NOT NULL,
    `Wann`    datetime      NOT NULL,
    `Wieviel` decimal(9, 2) NOT NULL,
    `Bank`    tinyint(1)    NOT NULL,
    PRIMARY KEY (`Wer`, `Wen`, `Wann`),
    KEY `Wer` (`Wer`),
    KEY `Wen` (`Wen`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci
  ROW_FORMAT = COMPACT;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `log_login`
--

CREATE TABLE IF NOT EXISTS `log_login`
(
    `IP`     varchar(15) character set latin1 NOT NULL,
    `Wer`    int(11)                          NOT NULL,
    `Wann`   datetime                         NOT NULL,
    `Sitter` tinyint(1)                       NOT NULL,
    PRIMARY KEY (`IP`, `Wann`),
    KEY `Wer` (`Wer`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci
  ROW_FORMAT = COMPACT;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `log_mafia`
--

CREATE TABLE IF NOT EXISTS `log_mafia`
(
    `Wer`         int(11)    NOT NULL,
    `Wen`         int(11)    NOT NULL,
    `Wann`        datetime   NOT NULL,
    `Wie`         int(11)    NOT NULL,
    `Erfolgreich` tinyint(1) NOT NULL,
    PRIMARY KEY (`Wer`, `Wen`, `Wann`),
    KEY `Wer` (`Wer`),
    KEY `Wen` (`Wen`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci
  ROW_FORMAT = COMPACT;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `log_nachrichten`
--

CREATE TABLE IF NOT EXISTS `log_nachrichten`
(
    `Wer`       int(11)                               NOT NULL,
    `Wen`       int(11)                               NOT NULL,
    `Wann`      datetime                              NOT NULL,
    `Betreff`   varchar(128) collate utf8_unicode_ci  NOT NULL,
    `Nachricht` varchar(4096) collate utf8_unicode_ci NOT NULL,
    `Gelesen`   tinyint(1)                            NOT NULL,
    `Geloescht` tinyint(1)                            NOT NULL default '0',
    `Orig_ID`   int(11)                               NOT NULL,
    PRIMARY KEY (`Wer`, `Wen`, `Wann`),
    KEY `Wen` (`Wen`),
    KEY `Wer` (`Wer`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci
  ROW_FORMAT = COMPACT;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `log_vertraege`
--

CREATE TABLE IF NOT EXISTS `log_vertraege`
(
    `Wer`        int(11)       NOT NULL,
    `Wen`        int(11)       NOT NULL,
    `Wann`       datetime      NOT NULL,
    `Was`        int(11)       NOT NULL,
    `Wieviel`    int(11)       NOT NULL,
    `Preis`      decimal(4, 2) NOT NULL,
    `Angenommen` tinyint(1)    NOT NULL,
    PRIMARY KEY (`Wer`, `Wen`, `Wann`),
    KEY `Wer` (`Wer`),
    KEY `Wen` (`Wen`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci
  ROW_FORMAT = COMPACT;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `marktplatz`
--

DROP TABLE IF EXISTS `marktplatz`;
CREATE TABLE `marktplatz`
(
    `ID`    int(11)       NOT NULL auto_increment,
    `Von`   int(11)       NOT NULL,
    `Was`   int(11)       NOT NULL,
    `Menge` int(11)       NOT NULL,
    `Preis` decimal(4, 2) NOT NULL,
    PRIMARY KEY (`ID`),
    KEY `Von` (`Von`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `mitglieder`
--

DROP TABLE IF EXISTS `mitglieder`;
CREATE TABLE `mitglieder`
(
    `ID`                    int(11)                             NOT NULL auto_increment,
    `Name`                  varchar(20) collate utf8_unicode_ci NOT NULL,
    `EMail`                 varchar(32) collate utf8_unicode_ci NOT NULL,
    `EMailAct`              char(40) collate utf8_unicode_ci             default NULL,
    `Passwort`              char(40) collate utf8_unicode_ci    NOT NULL,
    `RegistriertAm`         int(11)                             NOT NULL,
    `Geld`                  decimal(11, 2)                      NOT NULL,
    `Bank`                  decimal(9, 2)                       NOT NULL default '0.00',
    `Punkte`                decimal(9, 2)                       NOT NULL default '0.00',
    `IgmGesendet`           smallint(5)                         NOT NULL default '0',
    `IgmEmpfangen`          smallint(5)                         NOT NULL default '0',
    `Admin`                 tinyint(1)                          NOT NULL default '0',
    `Betatester`            tinyint(1)                          NOT NULL default '0',
    `LastAction`            int(11)                             NOT NULL default '0',
    `LastLogin`             int(11)                             NOT NULL default '0',
    `LastMafia`             int(11)                             NOT NULL default '0',
    `LastBannerView`        int(11)                             NOT NULL default '0',
    `Notizblock`            varchar(4096) collate utf8_unicode_ci        default NULL,
    `Beschreibung`          varchar(4096) collate utf8_unicode_ci        default NULL,
    `EwigePunkte`           int(2)                              NOT NULL default '0',
    `OnlineZeit`            int(7)                              NOT NULL default '0',
    `BannerViews`           int(5)                              NOT NULL default '0',
    `Gruppe`                int(11)                                      default NULL,
    `GruppeRechte`          int(11)                                      default NULL,
    `GruppeLastMessageZeit` int(11)                                      default NULL,
    `GruppeKassenStand`     decimal(11, 2)                               default NULL,
    `Verwarnungen`          int(11)                             NOT NULL default '0',
    `Gesperrt`              tinyint(1)                          NOT NULL default '0',
    PRIMARY KEY (`ID`),
    UNIQUE KEY `Name` (`Name`),
    UNIQUE KEY `EMail` (`EMail`),
    KEY `Gruppe` (`Gruppe`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `nachrichten`
--

DROP TABLE IF EXISTS `nachrichten`;
CREATE TABLE `nachrichten`
(
    `ID`        int(11)                               NOT NULL auto_increment,
    `Von`       int(11) default NULL,
    `An`        int(11)                               NOT NULL,
    `Nachricht` varchar(4096) collate utf8_unicode_ci NOT NULL,
    `Betreff`   varchar(128) collate utf8_unicode_ci  NOT NULL,
    `Zeit`      int(11)                               NOT NULL,
    `Gelesen`   tinyint(1)                            NOT NULL,
    PRIMARY KEY (`ID`),
    KEY `Von` (`Von`),
    KEY `An` (`An`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `punkte`
--

DROP TABLE IF EXISTS `punkte`;
CREATE TABLE `punkte`
(
    `ID`             int(11)        NOT NULL,
    `GebaeudePlus`   decimal(11, 2) NOT NULL default '0.00',
    `ForschungPlus`  decimal(11, 2) NOT NULL default '0.00',
    `ProduktionPlus` decimal(11, 2) NOT NULL default '0.00',
    `MafiaPlus`      decimal(11, 2) NOT NULL default '0.00',
    `MafiaMinus`     decimal(11, 2) NOT NULL default '0.00',
    PRIMARY KEY (`ID`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `sitter`
--

DROP TABLE IF EXISTS `sitter`;
CREATE TABLE `sitter`
(
    `ID`          int(11)                          NOT NULL,
    `Passwort`    char(40) collate utf8_unicode_ci NOT NULL,
    `Gebaeude`    tinyint(1)                       NOT NULL default '0',
    `Forschung`   tinyint(1)                       NOT NULL default '0',
    `Produktion`  tinyint(1)                       NOT NULL default '0',
    `Mafia`       tinyint(1)                       NOT NULL default '0',
    `Nachrichten` tinyint(1)                       NOT NULL default '0',
    `Gruppe`      tinyint(1)                       NOT NULL default '0',
    `Vertraege`   tinyint(1)                       NOT NULL default '0',
    `Marktplatz`  tinyint(1)                       NOT NULL default '0',
    `Bioladen`    tinyint(1)                       NOT NULL default '0',
    `Bank`        tinyint(1)                       NOT NULL default '0',
    PRIMARY KEY (`ID`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `special`
--

DROP TABLE IF EXISTS `special`;
CREATE TABLE `special`
(
    `Wer`      int(11)                       NOT NULL,
    `Wann`     datetime                      NOT NULL,
    `Hash`     char(40) character set latin1 NOT NULL,
    `Abgeholt` tinyint(1)                    NOT NULL default '0',
    PRIMARY KEY (`Hash`),
    KEY `Wer` (`Wer`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci
  ROW_FORMAT = COMPACT;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `statistik`
--

DROP TABLE IF EXISTS `statistik`;
CREATE TABLE `statistik`
(
    `ID`                 int(11)        NOT NULL,
    `AusgabenGebaeude`   decimal(12, 2) NOT NULL default '0.00',
    `AusgabenForschung`  decimal(12, 2) NOT NULL default '0.00',
    `AusgabenZinsen`     decimal(12, 2) NOT NULL default '0.00',
    `AusgabenProduktion` decimal(12, 2) NOT NULL default '0.00',
    `AusgabenMarkt`      decimal(12, 2) NOT NULL default '0.00',
    `AusgabenVertraege`  decimal(12, 2) NOT NULL default '0.00',
    `AusgabenMafia`      decimal(12, 2) NOT NULL default '0.00',
    `AusgabenSonstiges`  decimal(12, 2) NOT NULL default '0.00',
    `EinnahmenGebaeude`  decimal(12, 2) NOT NULL default '0.00',
    `EinnahmenVerkauf`   decimal(12, 2) NOT NULL default '0.00',
    `EinnahmenZinsen`    decimal(12, 2) NOT NULL default '0.00',
    `EinnahmenMarkt`     decimal(12, 2) NOT NULL default '0.00',
    `EinnahmenVertraege` decimal(12, 2) NOT NULL default '0.00',
    `EinnahmenMafia`     decimal(12, 2) NOT NULL default '0.00',
    PRIMARY KEY (`ID`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vertraege`
--

DROP TABLE IF EXISTS `vertraege`;
CREATE TABLE `vertraege`
(
    `ID`    int(11)       NOT NULL auto_increment,
    `Von`   int(11)       NOT NULL,
    `An`    int(11) default NULL,
    `Was`   int(11)       NOT NULL,
    `Menge` int(11)       NOT NULL,
    `Preis` decimal(4, 2) NOT NULL,
    `Wann`  datetime      NOT NULL,
    PRIMARY KEY (`ID`),
    KEY `Von` (`Von`),
    KEY `An` (`An`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

--
-- Constraints der exportierten Tabellen
--

--
-- Constraints der Tabelle `auftrag`
--
ALTER TABLE `auftrag`
    ADD CONSTRAINT `auftrag_ibfk_1` FOREIGN KEY (`Von`) REFERENCES `mitglieder` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints der Tabelle `forschung`
--
ALTER TABLE `forschung`
    ADD CONSTRAINT `forschung_ibfk_1` FOREIGN KEY (`ID`) REFERENCES `mitglieder` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints der Tabelle `gebaeude`
--
ALTER TABLE `gebaeude`
    ADD CONSTRAINT `gebaeude_ibfk_1` FOREIGN KEY (`ID`) REFERENCES `mitglieder` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints der Tabelle `gruppe_diplomatie`
--
ALTER TABLE `gruppe_diplomatie`
    ADD CONSTRAINT `gruppe_diplomatie_ibfk_1` FOREIGN KEY (`An`) REFERENCES `gruppe` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT `gruppe_diplomatie_ibfk_2` FOREIGN KEY (`Von`) REFERENCES `gruppe` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints der Tabelle `gruppe_logbuch`
--
ALTER TABLE `gruppe_logbuch`
    ADD CONSTRAINT `gruppe_logbuch_ibfk_3` FOREIGN KEY (`Gruppe`) REFERENCES `gruppe` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT `gruppe_logbuch_ibfk_4` FOREIGN KEY (`Spieler`) REFERENCES `mitglieder` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints der Tabelle `gruppe_nachrichten`
--
ALTER TABLE `gruppe_nachrichten`
    ADD CONSTRAINT `gruppe_nachrichten_ibfk_1` FOREIGN KEY (`Von`) REFERENCES `mitglieder` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT `gruppe_nachrichten_ibfk_2` FOREIGN KEY (`Gruppe`) REFERENCES `gruppe` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints der Tabelle `lagerhaus`
--
ALTER TABLE `lagerhaus`
    ADD CONSTRAINT `lagerhaus_ibfk_1` FOREIGN KEY (`ID`) REFERENCES `mitglieder` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints der Tabelle `log_bank`
--
ALTER TABLE `log_bank`
    ADD CONSTRAINT `log_bank_ibfk_1` FOREIGN KEY (`Wer`) REFERENCES `mitglieder` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints der Tabelle `log_bioladen`
--
ALTER TABLE `log_bioladen`
    ADD CONSTRAINT `log_bioladen_ibfk_1` FOREIGN KEY (`Wer`) REFERENCES `mitglieder` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints der Tabelle `log_gruppenkasse`
--
ALTER TABLE `log_gruppenkasse`
    ADD CONSTRAINT `log_gruppenkasse_ibfk_1` FOREIGN KEY (`Wer`) REFERENCES `mitglieder` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT `log_gruppenkasse_ibfk_2` FOREIGN KEY (`Wen`) REFERENCES `mitglieder` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints der Tabelle `log_mafia`
--
ALTER TABLE `log_mafia`
    ADD CONSTRAINT `log_mafia_ibfk_1` FOREIGN KEY (`Wer`) REFERENCES `mitglieder` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT `log_mafia_ibfk_2` FOREIGN KEY (`Wen`) REFERENCES `mitglieder` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints der Tabelle `log_vertraege`
--
ALTER TABLE `log_vertraege`
    ADD CONSTRAINT `log_vertraege_ibfk_1` FOREIGN KEY (`Wer`) REFERENCES `mitglieder` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT `log_vertraege_ibfk_2` FOREIGN KEY (`Wen`) REFERENCES `mitglieder` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints der Tabelle `marktplatz`
--
ALTER TABLE `marktplatz`
    ADD CONSTRAINT `marktplatz_ibfk_1` FOREIGN KEY (`Von`) REFERENCES `mitglieder` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints der Tabelle `mitglieder`
--
ALTER TABLE `mitglieder`
    ADD CONSTRAINT `mitglieder_ibfk_1` FOREIGN KEY (`Gruppe`) REFERENCES `gruppe` (`ID`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints der Tabelle `nachrichten`
--
ALTER TABLE `nachrichten`
    ADD CONSTRAINT `nachrichten_ibfk_2` FOREIGN KEY (`Von`) REFERENCES `mitglieder` (`ID`) ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT `nachrichten_ibfk_1` FOREIGN KEY (`An`) REFERENCES `mitglieder` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints der Tabelle `punkte`
--
ALTER TABLE `punkte`
    ADD CONSTRAINT `punkte_ibfk_1` FOREIGN KEY (`ID`) REFERENCES `mitglieder` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints der Tabelle `sitter`
--
ALTER TABLE `sitter`
    ADD CONSTRAINT `sitter_ibfk_1` FOREIGN KEY (`ID`) REFERENCES `mitglieder` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints der Tabelle `special`
--
ALTER TABLE `special`
    ADD CONSTRAINT `special_ibfk_1` FOREIGN KEY (`Wer`) REFERENCES `mitglieder` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints der Tabelle `statistik`
--
ALTER TABLE `statistik`
    ADD CONSTRAINT `statistik_ibfk_1` FOREIGN KEY (`ID`) REFERENCES `mitglieder` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints der Tabelle `vertraege`
--
ALTER TABLE `vertraege`
    ADD CONSTRAINT `vertraege_ibfk_2` FOREIGN KEY (`An`) REFERENCES `mitglieder` (`ID`) ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT `vertraege_ibfk_1` FOREIGN KEY (`Von`) REFERENCES `mitglieder` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

SET FOREIGN_KEY_CHECKS = 1;


-- 
-- Die Views
-- 
CREATE VIEW log_bioladen_view AS
SELECT m.Name                AS Wer,
       l.Wann,
       l.Was,
       l.Wieviel,
       l.Preis               AS Einzelpreis,
       (l.Wieviel * l.Preis) AS Gesamtpreis
FROM log_bioladen l
         LEFT OUTER JOIN
     mitglieder m
     ON l.Wer = m.ID
ORDER BY Wer ASC,
         Wann DESC,
         Was ASC,
         Gesamtpreis DESC;


CREATE VIEW log_bank_view AS
SELECT m.Name                                    AS Wer,
       l.Wann,
       l.Wieviel,
       IF(l.Einzahlen, 'Einzahlen', 'Auszahlen') AS Aktion
FROM log_bank l
         LEFT OUTER JOIN
     mitglieder m
     ON l.Wer = m.ID
ORDER BY Wer ASC,
         Wann DESC;


CREATE VIEW log_gruppenkasse_view AS
SELECT m1.Name                         AS Wer,
       m2.Name                         AS Wen,
       g.Name                          AS Gruppe,
       l.Wann,
       l.Wieviel,
       IF(l.Bank, 'Bankkonto', 'Hand') AS Wohin
FROM (
         (
             log_gruppenkasse l
                 LEFT OUTER JOIN
                 mitglieder m1
                 ON l.Wer = m1.ID
             )
             LEFT OUTER JOIN
             mitglieder m2
             ON l.Wen = m2.ID
         )
         LEFT OUTER JOIN
     gruppe g
     ON
         g.ID = m1.Gruppe
ORDER BY Wer ASC,
         Wann DESC,
         Wen ASC;


CREATE VIEW log_login_view AS
SELECT m.Name                           AS Wer,
       l.IP,
       l.Wann,
       IF(l.Sitter, 'Sitter', 'Normal') AS Art
FROM log_login l
         LEFT OUTER JOIN
     mitglieder m
     ON l.Wer = m.ID
ORDER BY IP DESC,
         Wer ASC,
         Wann DESC;


CREATE VIEW log_mafia_view AS
SELECT m1.Name                         AS Wer,
       m2.Name                         AS Wen,
       l.Wann,
       IF(
                   l.Wie = 1,
                   'Spionage',
                   IF(
                               l.Wie = 4,
                               'Angriff',
                               IF(
                                           l.Wie = 2,
                                           'Diebstahl',
                                           'Bomben'
                                   )
                       )
           )                           AS Art,
       IF(l.Erfolgreich, 'Ja', 'Nein') AS Erfolgreich
FROM (
         log_mafia l
             LEFT OUTER JOIN
             mitglieder m1
             ON l.Wer = m1.ID
         )
         LEFT OUTER JOIN
     mitglieder m2
     ON l.Wen = m2.ID
ORDER BY Wer ASC,
         Wann DESC,
         Wen ASC;


CREATE VIEW log_vertraege_view AS
SELECT m1.Name                        AS Wer,
       m2.Name                        AS Wen,
       l.Wann,
       l.Was                          AS Ware,
       l.Wieviel,
       l.Preis                        AS Einzelpreis,
       (l.Wieviel * l.Preis)          AS Gesamtpreis,
       IF(l.Angenommen, 'Ja', 'Nein') AS Angenommen
FROM (
         log_vertraege l
             LEFT OUTER JOIN
             mitglieder m1
             ON l.Wer = m1.ID
         )
         LEFT OUTER JOIN
     mitglieder m2
     ON l.Wen = m2.ID
ORDER BY Wer ASC,
         Wann DESC,
         Wen ASC;


CREATE VIEW log_nachrichten_view AS
SELECT m1.Name AS Wer,
       m2.Name AS Wen,
       l.Wann,
       l.Betreff,
       l.Nachricht,
       IF(l.Gelesen = 1,
          'Ja',
          'Nein'
           )   AS Gelesen,
       IF(l.Geloescht = 1,
          'Ja',
          'Nein'
           )   AS Geloescht,
       l.Orig_ID
FROM (
         log_nachrichten l
             LEFT OUTER JOIN
             mitglieder m1
             ON l.Wer = m1.ID
         )
         LEFT OUTER JOIN
     mitglieder m2
     ON l.Wen = m2.ID
ORDER BY Wer ASC,
         Wann DESC,
         Wen ASC;

