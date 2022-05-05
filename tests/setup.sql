-- ===================================
-- Database schema 1.10.0
-- ===================================

create table auftrag
(
    ID       int auto_increment primary key,
    created  datetime default current_timestamp() not null,
    finished datetime                             not null,
    user_id  int                                  not null,
    item     smallint(3)                          not null,
    amount   int                                  null,
    cost     decimal(10, 2)                       not null,
    points   decimal(8, 2)                        null,
    constraint auftrag unique (user_id, item)
) collate = utf8_unicode_ci;
create index user_id on auftrag (user_id);


create table changelog
(
    ID          int auto_increment primary key,
    created     date          not null,
    category    varchar(64)   not null,
    description varchar(1024) not null
) collate = utf8_unicode_ci;


create table forschung
(
    ID          int auto_increment primary key,
    user_id     int                   null,
    Forschung1  smallint(2) default 0 not null,
    Forschung2  smallint(2) default 0 not null,
    Forschung3  smallint(2) default 0 not null,
    Forschung4  smallint(2) default 0 not null,
    Forschung5  smallint(2) default 0 not null,
    Forschung6  smallint(2) default 0 not null,
    Forschung7  smallint(2) default 0 not null,
    Forschung8  smallint(2) default 0 not null,
    Forschung9  smallint(2) default 0 not null,
    Forschung10 smallint(2) default 0 not null,
    Forschung11 smallint(2) default 0 not null,
    Forschung12 smallint(2) default 0 not null,
    Forschung13 smallint(2) default 0 null,
    Forschung14 smallint(2) default 0 null,
    Forschung15 smallint(2) default 0 not null,
    constraint user_id unique (user_id)
) collate = utf8_unicode_ci;


create table gebaeude
(
    ID        int auto_increment primary key,
    user_id   int                   null,
    Gebaeude1 smallint(2) default 1 not null,
    Gebaeude2 smallint(2) default 0 not null,
    Gebaeude3 smallint(2) default 1 not null,
    Gebaeude4 smallint(2) default 0 not null,
    Gebaeude5 smallint(2) default 0 not null,
    Gebaeude6 smallint(2) default 0 not null,
    Gebaeude7 smallint(2) default 0 not null,
    Gebaeude8 smallint(2) default 0 not null,
    constraint user_id unique (user_id)
) collate = utf8_unicode_ci;


create table gruppe
(
    ID           int auto_increment primary key,
    Name         varchar(32)                 not null,
    Kuerzel      varchar(6)                  not null,
    Beschreibung varchar(2048)               null,
    Passwort     varchar(128)                not null,
    Kasse        decimal(12, 2) default 0.00 not null,
    constraint Kuerzel unique (Kuerzel),
    constraint Name unique (Name)
) collate = utf8_unicode_ci;


create table gruppe_diplomatie
(
    ID     int auto_increment primary key,
    Von    int                                    not null,
    An     int                                    not null,
    Typ    int                                    not null,
    Seit   datetime   default current_timestamp() not null,
    Aktiv  tinyint(1) default 0                   not null,
    Betrag decimal(12, 2)                         null,
    constraint relation unique (Von, An)
) collate = utf8_unicode_ci;
create index An on gruppe_diplomatie (An);
create index Von on gruppe_diplomatie (Von);


create table gruppe_kasse
(
    id       int auto_increment primary key,
    group_id int                         not null,
    user_id  int                         not null,
    amount   decimal(12, 2) default 0.00 not null,
    constraint relation unique (group_id, user_id)
) collate = utf8_unicode_ci;
create index group_id on gruppe_kasse (group_id);
create index user_id on gruppe_kasse (user_id);


create table gruppe_logbuch
(
    ID      int auto_increment primary key,
    Gruppe  int                                  not null,
    Spieler int                                  not null,
    Datum   datetime default current_timestamp() not null,
    Text    varchar(2048)                        not null
) collate = utf8_unicode_ci;
create index Gruppe on gruppe_logbuch (Gruppe);
create index Spieler on gruppe_logbuch (Spieler);


create table gruppe_nachrichten
(
    ID          int auto_increment primary key,
    Von         int                                    not null,
    Gruppe      int                                    not null,
    Nachricht   varchar(4096)                          not null,
    Zeit        datetime   default current_timestamp() not null,
    Festgepinnt tinyint(1) default 0                   not null
) collate = utf8_unicode_ci;
create index Gruppe on gruppe_nachrichten (Gruppe);
create index Von on gruppe_nachrichten (Von);


create table gruppe_rechte
(
    id               int auto_increment primary key,
    group_id         int                  not null,
    user_id          int                  not null,
    message_write    tinyint(1) default 0 not null,
    message_pin      tinyint(1) default 0 null,
    message_delete   tinyint(1) default 0 not null,
    edit_description tinyint(1) default 0 not null,
    edit_password    tinyint(1) default 0 not null,
    edit_image       tinyint(1) default 0 not null,
    member_rights    tinyint(1) default 0 not null,
    member_kick      tinyint(1) default 0 not null,
    group_cash       tinyint(1) default 0 not null,
    group_diplomacy  tinyint(1) default 0 not null,
    group_delete     tinyint(1) default 0 not null,
    constraint relation unique (group_id, user_id)
) collate = utf8_unicode_ci;
create index group_id on gruppe_rechte (group_id);
create index user_id on gruppe_rechte (user_id);

create table lagerhaus
(
    ID      int auto_increment primary key,
    user_id int              null,
    Lager1  int(5) default 0 not null,
    Lager2  int(5) default 0 not null,
    Lager3  int(5) default 0 not null,
    Lager4  int(5) default 0 not null,
    Lager5  int(5) default 0 not null,
    Lager6  int(5) default 0 not null,
    Lager7  int(5) default 0 not null,
    Lager8  int(5) default 0 not null,
    Lager9  int(5) default 0 not null,
    Lager10 int(5) default 0 not null,
    Lager11 int(5) default 0 not null,
    Lager12 int(5) default 0 not null,
    Lager13 int(5) default 0 not null,
    Lager14 int(5) default 0 not null,
    Lager15 int(5) default 0 not null,
    constraint user_id unique (user_id)
) collate = utf8_unicode_ci;


create table log_bank
(
    ID         int auto_increment primary key,
    created    datetime default current_timestamp() not null,
    playerId   int                                  not null,
    playerName varchar(20)                          not null,
    amount     decimal(11, 2)                       not null,
    target     enum ('BANK', 'HAND')                not null
) collate = utf8_unicode_ci;
create index playerName on log_bank (playerName);


create table log_bioladen
(
    ID         int auto_increment primary key,
    created    datetime default current_timestamp() not null,
    playerId   int                                  not null,
    playerName varchar(20)                          not null,
    amount     int(6)                               not null,
    item       smallint(2)                          not null,
    price      decimal(5, 2)                        not null
) collate = utf8_unicode_ci;
create index playerName on log_bioladen (playerName);


create table log_gruppenkasse
(
    ID           int auto_increment primary key,
    created      datetime default current_timestamp() not null,
    groupId      int                                  not null,
    groupName    varchar(32)                          not null,
    senderId     int                                  not null,
    senderName   varchar(20)                          not null,
    receiverId   int                                  null,
    receiverName varchar(20)                          null,
    amount       decimal(11, 2)                       not null
) collate = utf8_unicode_ci;
create index groupId on log_gruppenkasse (groupId);
create index senderName on log_gruppenkasse (senderName);
create index receiverName on log_gruppenkasse (receiverName);


create table log_login
(
    ID         int auto_increment primary key,
    created    datetime default current_timestamp() not null,
    ip         varchar(64) charset latin1           not null,
    playerId   int                                  not null,
    playerName varchar(20)                          null,
    success    tinyint(1)                           not null,
    sitter     tinyint(1)                           not null
) collate = utf8_unicode_ci;
create index ip on log_login (ip);
create index playerName on log_login (playerName);
create index sitter on log_login (sitter);


create table log_mafia
(
    ID           int auto_increment primary key,
    created      datetime default current_timestamp()             not null,
    senderId     int                                              not null,
    senderName   varchar(20)                                      null,
    receiverId   int                                              not null,
    receiverName varchar(20)                                      null,
    action       enum ('ESPIONAGE', 'ROBBERY', 'HEIST', 'ATTACK') not null,
    item         smallint(3)                                      null,
    amount       decimal(12, 2)                                   null,
    chance       decimal(5, 3)                                    not null,
    success      tinyint(1)                                       not null
) collate = utf8_unicode_ci;
create index senderName on log_mafia (senderName);
create index receiverName on log_mafia (receiverName);


create table log_vertraege
(
    ID           int auto_increment primary key,
    created      datetime default current_timestamp() not null,
    senderId     int                                  not null,
    senderName   varchar(20)                          not null,
    receiverId   int                                  not null,
    receiverName varchar(20)                          not null,
    item         smallint(2)                          not null,
    amount       int                                  not null,
    price        decimal(5, 2)                        not null,
    accepted     tinyint(1)                           not null
) collate = utf8_unicode_ci;
create index senderId on log_vertraege (senderId);
create index receiverId on log_vertraege (receiverId);


create table marktplatz
(
    ID    int auto_increment primary key,
    Von   int           not null,
    Was   int           not null,
    Menge int           not null,
    Preis decimal(4, 2) not null
) collate = utf8_unicode_ci;
create index Von on marktplatz (Von);


create table mitglieder
(
    ID                    int auto_increment primary key,
    Name                  varchar(20)                                not null,
    EMail                 varchar(96)                                not null,
    EMailAct              char(40)                                   null,
    Passwort              varchar(128)                               not null,
    RegistriertAm         datetime       default current_timestamp() null,
    Geld                  decimal(11, 2) default 0.00                not null,
    Bank                  decimal(9, 2)  default 0.00                not null,
    Punkte                decimal(9, 2)  default 0.00                not null,
    IgmGesendet           smallint(5)    default 0                   not null,
    IgmEmpfangen          smallint(5)    default 0                   not null,
    Admin                 tinyint(1)     default 0                   not null,
    Betatester            tinyint(1)     default 0                   not null,
    LastAction            datetime                                   null,
    LastLogin             datetime                                   null,
    NextMafia             datetime                                   null,
    Notizblock            varchar(4096)                              null,
    Beschreibung          varchar(4096)                              null,
    EwigePunkte           int(2)         default 0                   not null,
    OnlineZeit            int(7)         default 0                   not null,
    Gruppe                int                                        null,
    GruppeLastMessageZeit datetime                                   null,
    Verwarnungen          int            default 0                   not null,
    Gesperrt              tinyint(1)     default 0                   not null,
    constraint EMail unique (EMail),
    constraint Name unique (Name)
) collate = utf8_unicode_ci;
create index Gruppe on mitglieder (Gruppe);


create table nachrichten
(
    ID        int auto_increment primary key,
    Von       int                                    null,
    An        int                                    not null,
    Nachricht varchar(4096)                          not null,
    Betreff   varchar(128)                           not null,
    Zeit      datetime   default current_timestamp() not null,
    Gelesen   tinyint(1) default 0                   not null
) collate = utf8_unicode_ci;
create index An on nachrichten (An);
create index Von on nachrichten (Von);


create table passwort_reset
(
    ID      int auto_increment primary key,
    user_id int                                  not null,
    created datetime default current_timestamp() not null,
    token   char(40)                             not null,
    constraint user_id unique (user_id)
);


create table punkte
(
    ID             int auto_increment primary key,
    user_id        int                         null,
    GebaeudePlus   decimal(11, 2) default 0.00 not null,
    ForschungPlus  decimal(11, 2) default 0.00 not null,
    ProduktionPlus decimal(11, 2) default 0.00 not null,
    MafiaPlus      decimal(11, 2) default 0.00 not null,
    MafiaMinus     decimal(11, 2) default 0.00 not null,
    KriegMinus     decimal(11, 2) default 0.00 not null,
    constraint user_id unique (user_id)
) collate = utf8_unicode_ci;


create table sitter
(
    ID          int auto_increment primary key,
    user_id     int                  null,
    Passwort    varchar(128)         not null,
    Gebaeude    tinyint(1) default 0 not null,
    Forschung   tinyint(1) default 0 not null,
    Produktion  tinyint(1) default 0 not null,
    Mafia       tinyint(1) default 0 not null,
    Nachrichten tinyint(1) default 0 not null,
    Gruppe      tinyint(1) default 0 not null,
    Vertraege   tinyint(1) default 0 not null,
    Marktplatz  tinyint(1) default 0 not null,
    Bioladen    tinyint(1) default 0 not null,
    Bank        tinyint(1) default 0 not null,
    constraint user_id unique (user_id)
) collate = utf8_unicode_ci;


create table statistik
(
    ID                 int auto_increment primary key,
    user_id            int                         null,
    AusgabenGebaeude   decimal(12, 2) default 0.00 not null,
    AusgabenForschung  decimal(12, 2) default 0.00 not null,
    AusgabenZinsen     decimal(12, 2) default 0.00 not null,
    AusgabenProduktion decimal(12, 2) default 0.00 not null,
    AusgabenMarkt      decimal(12, 2) default 0.00 not null,
    AusgabenVertraege  decimal(12, 2) default 0.00 not null,
    AusgabenMafia      decimal(12, 2) default 0.00 not null,
    EinnahmenGebaeude  decimal(12, 2) default 0.00 not null,
    EinnahmenVerkauf   decimal(12, 2) default 0.00 not null,
    EinnahmenZinsen    decimal(12, 2) default 0.00 not null,
    EinnahmenMarkt     decimal(12, 2) default 0.00 not null,
    EinnahmenVertraege decimal(12, 2) default 0.00 not null,
    EinnahmenMafia     decimal(12, 2) default 0.00 not null,
    constraint user_id unique (user_id)
) collate = utf8_unicode_ci;


create table vertraege
(
    ID    int auto_increment primary key,
    Von   int                                  not null,
    An    int                                  null,
    Was   int                                  not null,
    Menge int                                  not null,
    Preis decimal(4, 2)                        not null,
    Wann  datetime default current_timestamp() not null
) collate = utf8_unicode_ci;
create index An on vertraege (An);
create index Von on vertraege (Von);


INSERT INTO `mitglieder` (`Name`, `EMail`, `Passwort`)
VALUES ('System', 'none', 'none');

UPDATE `mitglieder`
SET `ID` = 0
WHERE `Name` = 'System';

-- ===================================
-- test data
-- ===================================

alter table mitglieder
    auto_increment 100;

INSERT INTO `mitglieder` (`ID`, `Name`, `EMail`, `Passwort`, `Punkte`)
VALUES (11, 'test1', 'test1@example.com', sha1('changeit'), 0);
INSERT INTO `forschung` (`user_id`)
VALUES (11);
INSERT INTO `gebaeude` (`user_id`)
VALUES (11);
INSERT INTO `lagerhaus` (`user_id`)
VALUES (11);
INSERT INTO `punkte` (`user_id`)
VALUES (11);
INSERT INTO `statistik` (`user_id`)
VALUES (11);

INSERT INTO `mitglieder` (`ID`, `Name`, `EMail`, `Passwort`, `Punkte`)
VALUES (12, 'test2', 'test2@example.com', sha1('changeit'), 0);
INSERT INTO `forschung` (`user_id`)
VALUES (12);
INSERT INTO `gebaeude` (`user_id`)
VALUES (12);
INSERT INTO `lagerhaus` (`user_id`)
VALUES (12);
INSERT INTO `punkte` (`user_id`)
VALUES (12);
INSERT INTO `statistik` (`user_id`)
VALUES (12);

INSERT INTO `mitglieder` (`ID`, `Name`, `EMail`, `Passwort`, `Punkte`)
VALUES (13, 'test3', 'test3@example.com', sha1('changeit'), 0);
INSERT INTO `forschung` (`user_id`)
VALUES (13);
INSERT INTO `gebaeude` (`user_id`)
VALUES (13);
INSERT INTO `lagerhaus` (`user_id`)
VALUES (13);
INSERT INTO `punkte` (`user_id`)
VALUES (13);
INSERT INTO `statistik` (`user_id`)
VALUES (13);

INSERT INTO `mitglieder` (`ID`, `Name`, `EMail`, `Passwort`, `Punkte`)
VALUES (14, 'test4', 'test4@example.com', sha1('changeit'), 0);
INSERT INTO `forschung` (`user_id`)
VALUES (14);
INSERT INTO `gebaeude` (`user_id`)
VALUES (14);
INSERT INTO `lagerhaus` (`user_id`)
VALUES (14);
INSERT INTO `punkte` (`user_id`)
VALUES (14);
INSERT INTO `statistik` (`user_id`)
VALUES (14);
