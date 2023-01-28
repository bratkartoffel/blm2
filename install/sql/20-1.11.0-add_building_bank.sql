-- MIT Licence
-- Copyright (c) 2023 Simon Frankenberger
-- Please see LICENCE.md for complete licence text.

alter table mitglieder
    add Gebaeude9 smallint(2) not null default 0 after Gebaeude8;
