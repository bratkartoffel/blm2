create table update_info
(
    ID       int auto_increment primary key,
    Executed datetime    not null default current_timestamp,
    Script   varchar(64) not null,
    Checksum char(40)    not null
);
create unique index Script on update_info (Script);

