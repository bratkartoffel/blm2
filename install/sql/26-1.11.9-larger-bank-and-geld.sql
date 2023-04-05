-- MIT Licence
-- Copyright (c) 2023 Simon Frankenberger
-- Please see LICENCE.md for complete licence text.

alter table mitglieder
    change Geld Geld decimal(12, 2) not null default 0,
    change Bank Bank decimal(13, 2) not null default 0;

alter table log_bank
    change amount amount decimal(13, 2) not null default 0;

alter table statistik
    change AusgabenZinsen AusgabenZinsen decimal(13, 2) not null default 0;
