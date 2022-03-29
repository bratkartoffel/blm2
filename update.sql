/*
	Neuerungen seit der Version 1.9.2
*/

INSERT INTO changelog (`ID`,
                       `Datum`,
                       `Kategorie`,
                       `Aenderung`)
VALUES (NULL, '2009-01-23', 'Bugfix',
        '(Rangliste): Das Umblättern in den verschiedenen Ranglisten funktioniert nun einwandfrei.'),
       (NULL, '2009-01-23', 'Feature',
        '(Rangliste): In der Rangliste kann nun nach einem Spieler gesucht werden. (Danke an Kaktus)'),
       (NULL, '2009-01-23', 'Feature',
        '(Allgemein): Installationsanweisung rudimentär verfasst und dem Source beigelegt. (Danke an Eumele)'),
       (NULL, '2009-01-23', 'Bugfix',
        '(Verträge): Die Waren aller Verträge, welche noch offen standen und dessen Empfänger sich gelöscht hat, gingen verloren (Danke an donnergot88)'),
       (NULL, '2009-01-24', 'Feature',
        '(Nachrichten): Die Nachrichten des Absender bleiben beim Empfänger erhalten, auch wenn sich der Absender löscht.'),
       (NULL, '2009-01-24', 'Bugfix',
        '(Verträge): Aufgrund vieler Verstöße gegen die Regeln habe ich beschlossen, den Preis für Verträge selbst zu begrenzen zwischen 3 und 15 € / kg.'),
       (NULL, '2009-01-27', 'Bugfix',
        '(Plantage): Aufgrund von Rundungsfehlern wich der angezeigte Preis für die Produktions teils sehr stark von den echten Kosten ab. (Danke an donnergott88)');

ALTER TABLE `vertraege`
    CHANGE `An` `An` INT(11) NULL;
ALTER TABLE `nachrichten`
    CHANGE `Von` `Von` INT(11) NULL;

ALTER TABLE `gruppe_nachrichten`
    ADD `Festgepinnt` BOOL NOT NULL DEFAULT '0';

ALTER TABLE `mitglieder`
    CHANGE `EMail` `EMail` VARCHAR(96);

alter table log_login
    CHANGE `IP` `IP` VARCHAR(64);

INSERT INTO mitglieder (ID, Name, EMail, EMailAct, Passwort, RegistriertAm, Geld, Bank, Punkte, IgmGesendet,
                             IgmEmpfangen, Admin, Betatester, LastAction, LastLogin, LastMafia, LastBannerView,
                             Notizblock, Beschreibung, EwigePunkte, OnlineZeit, BannerViews, Gruppe, GruppeRechte,
                             GruppeLastMessageZeit, GruppeKassenStand, Verwarnungen, Gesperrt)
VALUES (0, 'System', 'none', null, 'none', 1648500676, 0.00, 0.00, 0.00, 0, 0, 0, 0, 0, 0, 0, 0, null, null, 0, 0, 0,
        null, null, null, null, 0, 0);

alter table mitglieder
    drop column LastBannerView;

alter table mitglieder
    drop column BannerViews;


-- Verträge.An Relationen Mitglieder.ID:						ON DELETE SET NULL
-- Mitglieder.Gruppe Relation Gruppe.ID:						ON DELETE SET NULL
-- Nachrichten.Von Relation Mitglieder.ID:					ON DELETE SET NULL
