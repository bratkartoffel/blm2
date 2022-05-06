alter table mitglieder
    auto_increment 100;

DELIMITER $$
CREATE PROCEDURE InsertUsers(P_count int)
BEGIN
    DECLARE NR int;
    insertLoop:
    LOOP
        SET NR = coalesce(NR, 0) + 1;

        INSERT INTO `mitglieder` (`ID`, `Name`, `EMail`, `Passwort`)
        VALUES (10 + NR, concat('test', NR), concat('test', NR, '@example.com'), sha1('changeit'));
        INSERT INTO `forschung` (`user_id`) VALUES (10 + NR);
        INSERT INTO `gebaeude` (`user_id`) VALUES (10 + NR);
        INSERT INTO `lagerhaus` (`user_id`) VALUES (10 + NR);
        INSERT INTO `punkte` (`user_id`) VALUES (10 + NR);
        INSERT INTO `statistik` (`user_id`) VALUES (10 + NR);

        IF NR = P_count THEN LEAVE insertLoop; END IF;
    END LOOP insertLoop;
END $$
DELIMITER ;

CALL InsertUsers(4);
