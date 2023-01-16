-- MIT Licence
-- Copyright (c) 2023 Simon Frankenberger
-- Please see LICENCE.md for complete licence text.

alter table log_login
    add anonymized bool not null default false;

create index anonymized on log_login (anonymized);
