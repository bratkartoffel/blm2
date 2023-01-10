-- MIT Licence
-- Copyright (c) 2023 Simon Frankenberger
-- Please see LICENCE.md for complete licence text.

create table log_marktplatz
(
    ID         int auto_increment primary key,
    created    datetime default current_timestamp() not null,
    sellerId   int                                  not null,
    sellerName varchar(20)                          not null,
    buyerId    int,
    buyerName  varchar(20),
    item       smallint(2)                          not null,
    amount     int                                  not null,
    price      decimal(5, 2)                        not null
) collate = utf8mb4_unicode_ci;
create index sellerId on log_marktplatz (sellerId);
create index buyerId on log_marktplatz (buyerId);
