-- MIT Licence
-- Copyright (c) 2023 Simon Frankenberger
-- Please see LICENCE.md for complete licence text.

create table runtime_config
(
    conf_name  varchar(32) not null primary key,
    conf_value varchar(64) not null
);
