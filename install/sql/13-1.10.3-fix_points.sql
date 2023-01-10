-- MIT Licence
-- Copyright (c) 2023 Simon Frankenberger
-- Please see LICENCE.md for complete licence text.

update mitglieder m inner join statistik s on m.ID = s.user_id
set m.Punkte = GebaeudePlus + ForschungPlus + ProduktionPlus + MafiaPlus - MafiaMinus - KriegMinus
where 1;

alter table mitglieder
    modify Punkte int(9) default 0 not null;
