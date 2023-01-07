alter table mitglieder
    auto_increment 100;

DELIMITER $$
CREATE OR REPLACE PROCEDURE InsertUsers(P_count int)
BEGIN
    DECLARE NR int;
    insertLoop:
    LOOP
        SET NR = coalesce(NR, 0) + 1;

        REPLACE INTO `mitglieder` (`ID`, `Name`, `EMail`, `Passwort`)
        VALUES (10 + NR, concat('test', NR), concat('test', NR, '@example.com'),
                   -- password is "changeit"
                '$argon2i$v=19$m=16384,t=8,p=2$cFlRVVl2WTdFREFkaU8zQg$kphB/S9ZP41FplBbhUH1uYSURQD4kK8JYQvjtieU/ZM');
        INSERT INTO `statistik` (`user_id`) VALUES (10 + NR);

        IF NR = P_count THEN LEAVE insertLoop; END IF;
    END LOOP insertLoop;
END $$
DELIMITER ;

CALL InsertUsers(5);

REPLACE INTO `mitglieder` (`ID`, `Name`, `EMail`, `Admin`, `Passwort`)
VALUES (9, 'admin', 'admin@example.com', 1,
           -- password is "changeit"
        '$argon2i$v=19$m=16384,t=8,p=2$cFlRVVl2WTdFREFkaU8zQg$kphB/S9ZP41FplBbhUH1uYSURQD4kK8JYQvjtieU/ZM');
INSERT INTO `statistik` (`user_id`) VALUES (9);
