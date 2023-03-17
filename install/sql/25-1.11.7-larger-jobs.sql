-- MIT Licence
-- Copyright (c) 2023 Simon Frankenberger
-- Please see LICENCE.md for complete licence text.

alter table auftrag
    change cost cost decimal(11, 2) not null default 0;
