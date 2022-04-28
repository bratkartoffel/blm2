-- Replace `blm2` with the source database (game version 1.9.0)
-- Replace `blm2-dev` with the target database (game version 1.10.0)

use `blm2-dev`;

begin;

truncate table `blm2-dev`.auftrag;
insert into `blm2-dev`.auftrag
select ID + 10,
       from_unixtime(`Start`),
       from_unixtime(`start` + `dauer`),
       Von,
       Was,
       Menge,
       Kosten,
       Punkte
from `blm2`.auftrag;

replace into `blm2-dev`.changelog
select *
from `blm2`.changelog;

replace into `blm2-dev`.forschung
select ID + 20,
       ID,
       Forschung1,
       Forschung2,
       Forschung3,
       Forschung4,
       Forschung5,
       Forschung6,
       Forschung7,
       Forschung8,
       Forschung9,
       Forschung10,
       Forschung11,
       Forschung12,
       Forschung13,
       Forschung14,
       Forschung15
from `blm2`.forschung;

replace into `blm2-dev`.gebaeude
select ID + 12,
       ID,
       Gebaeude1,
       Gebaeude2,
       Gebaeude3,
       Gebaeude4,
       Gebaeude5,
       Gebaeude6,
       Gebaeude7,
       Gebaeude8
from `blm2`.gebaeude;

truncate table `blm2-dev`.gruppe;
insert into `blm2-dev`.gruppe
select ID, Name, Kuerzel, Beschreibung, sha1(lower(Kuerzel)), Kasse
from `blm2`.gruppe;

truncate table `blm2-dev`.gruppe_diplomatie;
insert into `blm2-dev`.gruppe_diplomatie
select ID, Von, An, Typ, coalesce(from_unixtime(Seit), current_timestamp), IF(Bis is null, 0, 1), Betrag
from `blm2`.gruppe_diplomatie;

truncate table `blm2-dev`.gruppe_kasse;
insert into `blm2-dev`.gruppe_kasse
select null,
       Gruppe,
       ID,
       GruppeKassenStand
from `blm2`.mitglieder
where Gruppe is not null;

truncate table `blm2-dev`.gruppe_logbuch;
insert into `blm2-dev`.gruppe_logbuch
select ID, Gruppe, Spieler, from_unixtime(Datum), Text
from `blm2`.gruppe_logbuch;


truncate table `blm2-dev`.gruppe_nachrichten;
insert into `blm2-dev`.gruppe_nachrichten
select ID, Von, Gruppe, Nachricht, from_unixtime(Zeit), Festgepinnt
from `blm2`.gruppe_nachrichten;

truncate table `blm2-dev`.gruppe_rechte;
insert into `blm2-dev`.gruppe_rechte
select ID + 30,
       Gruppe,
       ID,
       1,
       1,
       1,
       1,
       1,
       1,
       1,
       1,
       1,
       1,
       1
from `blm2`.mitglieder
where Gruppe is not null;

replace into `blm2-dev`.lagerhaus
select ID + 40,
       ID,
       Lager1,
       Lager2,
       Lager3,
       Lager4,
       Lager5,
       Lager6,
       Lager7,
       Lager8,
       Lager9,
       Lager10,
       Lager11,
       Lager12,
       Lager13,
       Lager14,
       Lager15
from `blm2`.lagerhaus;

truncate table `blm2-dev`.marktplatz;
insert into `blm2-dev`.marktplatz
select *
from `blm2`.marktplatz;

replace into `blm2-dev`.mitglieder
select `ID`,
       `Name`,
       `EMail`,
       `EMailAct`,
       if(ID = 0, 'none', sha1(lower(`Name`))) AS `Passwort`,
       `RegistriertAm`,
       `Geld`,
       `Bank`,
       `Punkte`,
       `IgmGesendet`,
       `IgmEmpfangen`,
       `Admin`,
       `Betatester`,
       if(LastAction = 0 or LastAction is null, null, from_unixtime(`LastAction`)),
       if(LastLogin = 0 or LastLogin is null, null, from_unixtime(`LastLogin`)),
       if(LastMafia = 0 or LastMafia is null, null, from_unixtime(`LastMafia`)),
       `Notizblock`,
       `Beschreibung`,
       `EwigePunkte`,
       `OnlineZeit`,
       `Gruppe`,
       if(GruppeLastMessageZeit = 0 or GruppeLastMessageZeit is null, null, from_unixtime(`GruppeLastMessageZeit`)),
       `Verwarnungen`,
       `Gesperrt`
from `blm2`.`mitglieder`;

truncate table `blm2-dev`.nachrichten;
insert into `blm2-dev`.nachrichten
select ID, Von, An, Nachricht, Betreff, from_unixtime(Zeit), Gelesen
from `blm2`.nachrichten;

replace into `blm2-dev`.punkte
select ID + 50,
       ID,
       GebaeudePlus,
       ForschungPlus,
       ProduktionPlus,
       MafiaPlus,
       MafiaMinus,
       0
from `blm2`.punkte;

replace into `blm2-dev`.sitter
select ID + 60,
       ID,
       Passwort,
       Gebaeude,
       Forschung,
       Produktion,
       Mafia,
       Nachrichten,
       Gruppe,
       Vertraege,
       Marktplatz,
       Bioladen,
       Bank
from `blm2`.sitter;

replace into `blm2-dev`.statistik
select ID + 70,
       ID,
       AusgabenGebaeude,
       AusgabenForschung,
       AusgabenZinsen,
       AusgabenProduktion,
       AusgabenMarkt,
       AusgabenVertraege,
       AusgabenMafia,
       AusgabenSonstiges,
       EinnahmenGebaeude,
       EinnahmenVerkauf,
       EinnahmenZinsen,
       EinnahmenMarkt,
       EinnahmenVertraege,
       EinnahmenMafia
from `blm2`.statistik;

truncate table `blm2-dev`.vertraege;
insert into `blm2-dev`.vertraege
select *
from `blm2`.vertraege;


UPDATE `blm2-dev`.gruppe_logbuch
SET Text = REGEXP_REPLACE(Text, '<a href="\\./\\?p=profil&amp;uid=([0-9]+)&amp;[0-9]+">([^<]+)</a>',
                          '[player=\\2#\\1/]');
UPDATE `blm2-dev`.gruppe_logbuch
SET Text = REGEXP_REPLACE(Text, '<a href="\\./\\?p=profil&amp;uid=([0-9]+)">([^<]+)</a>',
                          '[player=\\2#\\1/]');

UPDATE `blm2-dev`.gruppe_logbuch
SET Text = REGEXP_REPLACE(Text, '<a href="\\./\\?p=gruppe&amp;id=([0-9]+)&amp;[0-9]+">([^<]+)</a>',
                          '[group=\\2#\\1/]');
UPDATE `blm2-dev`.gruppe_logbuch
SET Text = REGEXP_REPLACE(Text, '<a href="\\./\\?p=gruppe&amp;id=([0-9]+)">([^<]+)</a>',
                          '[group=\\2#\\1/]');

update `blm2-dev`.mitglieder
set ID = 0
WHERE Name = 'System';

commit;
