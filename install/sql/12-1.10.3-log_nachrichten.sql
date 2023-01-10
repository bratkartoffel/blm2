-- MIT Licence
-- Copyright (c) 2023 Simon Frankenberger
-- Please see LICENCE.md for complete licence text.

create table log_nachrichten
(
    ID           int auto_increment primary key,
    created      datetime default current_timestamp() not null,
    senderId     int                                  not null,
    senderName   varchar(20)                          not null,
    receiverId   int,
    receiverName varchar(20),
    subject      varchar(128)                         not null,
    message      varchar(4096)                        not null
) collate = utf8mb4_unicode_ci;
create index senderId on log_nachrichten (senderId);
create index receiverId on log_nachrichten (receiverId);
