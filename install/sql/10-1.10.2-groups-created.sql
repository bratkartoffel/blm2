alter table gruppe
    add Erstellt datetime not null default current_timestamp after Kuerzel;
