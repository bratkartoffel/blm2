-- MIT Licence
-- Copyright (c) 2023 Simon Frankenberger
-- Please see LICENCE.md for complete licence text.

alter table gruppe
    add Erstellt datetime not null default current_timestamp after Kuerzel;
