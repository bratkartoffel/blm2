-- MIT Licence
-- Copyright (c) 2023 Simon Frankenberger
-- Please see LICENCE.md for complete licence text.

update log_nachrichten
set receiverName = 'Rundmail'
where receiverId is null;

alter table log_nachrichten
    change receiverName receiverName varchar(20) not null;
