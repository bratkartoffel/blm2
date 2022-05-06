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
