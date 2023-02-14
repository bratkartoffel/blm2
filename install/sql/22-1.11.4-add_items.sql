-- MIT Licence
-- Copyright (c) 2023 Simon Frankenberger
-- Please see LICENCE.md for complete licence text.

alter table mitglieder
    add Forschung16 smallint(2) not null default 0 after Forschung15,
    add Forschung17 smallint(2) not null default 0 after Forschung16,
    add Forschung18 smallint(2) not null default 0 after Forschung17,
    add Lager16 int(5) not null default 0 after Lager15,
    add Lager17 int(5) not null default 0 after Lager16,
    add Lager18 int(5) not null default 0 after Lager17;
