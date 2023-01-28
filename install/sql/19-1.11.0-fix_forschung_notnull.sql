-- MIT Licence
-- Copyright (c) 2023 Simon Frankenberger
-- Please see LICENCE.md for complete licence text.

alter table mitglieder
    modify Forschung13 smallint(2) default 0 not null,
    modify Forschung14 smallint(2) default 0 not null;
