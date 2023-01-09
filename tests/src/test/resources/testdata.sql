-- MIT Licence
-- Copyright (c) 2023 Simon Frankenberger
-- Please see LICENCE.md for complete licence text.

alter table mitglieder
    auto_increment 100;

-- all passwords are "changeit"

REPLACE INTO `mitglieder` (`ID`, `Name`, `EMail`, `Passwort`)
VALUES (11, 'test1', 'test1@example.com',
        '$argon2i$v=19$m=16384,t=8,p=2$cFlRVVl2WTdFREFkaU8zQg$kphB/S9ZP41FplBbhUH1uYSURQD4kK8JYQvjtieU/ZM');
REPLACE INTO `statistik` (`user_id`)
VALUES (11);

REPLACE INTO `mitglieder` (`ID`, `Name`, `EMail`, `Passwort`)
VALUES (12, 'test2', 'test2@example.com',
        '$argon2i$v=19$m=16384,t=8,p=2$cFlRVVl2WTdFREFkaU8zQg$kphB/S9ZP41FplBbhUH1uYSURQD4kK8JYQvjtieU/ZM');
REPLACE INTO `statistik` (`user_id`)
VALUES (12);

REPLACE INTO `mitglieder` (`ID`, `Name`, `EMail`, `Passwort`)
VALUES (13, 'test3', 'test3@example.com',
        '$argon2i$v=19$m=16384,t=8,p=2$cFlRVVl2WTdFREFkaU8zQg$kphB/S9ZP41FplBbhUH1uYSURQD4kK8JYQvjtieU/ZM');
REPLACE INTO `statistik` (`user_id`)
VALUES (13);

REPLACE INTO `mitglieder` (`ID`, `Name`, `EMail`, `Passwort`)
VALUES (14, 'test4', 'test4@example.com',
        '$argon2i$v=19$m=16384,t=8,p=2$cFlRVVl2WTdFREFkaU8zQg$kphB/S9ZP41FplBbhUH1uYSURQD4kK8JYQvjtieU/ZM');
REPLACE INTO `statistik` (`user_id`)
VALUES (14);

REPLACE INTO `mitglieder` (`ID`, `Name`, `EMail`, `Passwort`)
VALUES (15, 'test5', 'test5@example.com',
        '$argon2i$v=19$m=16384,t=8,p=2$cFlRVVl2WTdFREFkaU8zQg$kphB/S9ZP41FplBbhUH1uYSURQD4kK8JYQvjtieU/ZM');
REPLACE INTO `statistik` (`user_id`)
VALUES (15);

REPLACE INTO `mitglieder` (`ID`, `Name`, `EMail`, `Admin`, `Passwort`)
VALUES (9, 'admin', 'admin@example.com', 1,
        '$argon2i$v=19$m=16384,t=8,p=2$cFlRVVl2WTdFREFkaU8zQg$kphB/S9ZP41FplBbhUH1uYSURQD4kK8JYQvjtieU/ZM');
REPLACE INTO `statistik` (`user_id`)
VALUES (9);
