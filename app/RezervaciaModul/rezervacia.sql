
-- Štruktúra tabuľky pre tabuľku rezervacia
CREATE TABLE IF NOT EXISTS poznamka (
    poznamka_id int(11) NOT NULL,
    rezervacia_id int(11) NOT NULL,
    poznamka varchar(255) COLLATE utf8_slovak_ci DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovak_ci;
-- --------------------------------------------------------

-- Sťahujem dáta pre tabuľku rezervacia
-- INSERT INTO rezervacia (rezervacia_id, datum_id, cas_id, osoba_id) VALUES ();
-- --------------------------------------------------------

-- Indexy pre tabuľku rezervacia
ALTER TABLE poznamka
    ADD PRIMARY KEY (poznamka_id),
    ADD KEY rezervacia_id (rezervacia_id);
-- --------------------------------------------------------

-- AUTO_INCREMENT pre tabuľku rezervacia
ALTER TABLE poznamka
    MODIFY poznamka_id int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
-- --------------------------------------------------------

-- Obmedzenie pre tabuľku rezervacia
ALTER TABLE poznamka
    ADD CONSTRAINT poznamka_ibfk_1 FOREIGN KEY (rezervacia_id) REFERENCES rezervacia (rezervacia_id) ON DELETE CASCADE;


-------------------------------------------------------------------------------------------------------------------



-- Štruktúra tabuľky pre tabuľku skupina
CREATE TABLE IF NOT EXISTS skupina (
    skupina_id int(11) NOT NULL,
    rezervacia_id int(11) NOT NULL,
    osoba_id int(11) NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovak_ci;
-- --------------------------------------------------------

-- Sťahujem dáta pre tabuľku rezervacia
-- INSERT INTO rezervacia (rezervacia_id, datum_id, cas_id, osoba_id) VALUES ();
-- --------------------------------------------------------

-- Indexy pre tabuľku skupina
ALTER TABLE skupina
    ADD PRIMARY KEY (skupina_id),
                                            ADD KEY rezervacia_id (rezervacia_id),
                                            ADD KEY osoba_id (osoba_id);
-- --------------------------------------------------------

-- AUTO_INCREMENT pre tabuľku skupina
ALTER TABLE skupina
    MODIFY skupina_id int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
-- --------------------------------------------------------

-- Obmedzenie pre tabuľku skupina
ALTER TABLE skupina
                                        ADD CONSTRAINT skupina_ibfk_1 FOREIGN KEY (rezervacia_id) REFERENCES rezervacia (rezervacia_id) ON DELETE CASCADE,
                                        ADD CONSTRAINT skupina_ibfk_2 FOREIGN KEY (osoba_id) REFERENCES osoba (osoba_id)   ;


-------------------------------------------------------------------------------------------------------------------


-- Štruktúra tabuľky pre tabuľku rezervacia
CREATE TABLE IF NOT EXISTS rezervacia (
    rezervacia_id int(11) NOT NULL,
    datum_vytvorenia datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    datum date NOT NULL,
    cas_od time NOT NULL,
    cas_do time NOT NULL,
    osoba_id int(11) NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovak_ci;
-- --------------------------------------------------------

-- Sťahujem dáta pre tabuľku rezervacia
-- INSERT INTO rezervacia (rezervacia_id, datum_id, cas_id, osoba_id) VALUES ();
-- --------------------------------------------------------

-- Indexy pre tabuľku rezervacia
ALTER TABLE rezervacia
    ADD PRIMARY KEY (rezervacia_id),
                                            ADD KEY datum (datum),
                                            ADD KEY osoba_id (osoba_id);
-- --------------------------------------------------------

-- AUTO_INCREMENT pre tabuľku rezervacia
ALTER TABLE rezervacia
    MODIFY rezervacia_id int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
-- --------------------------------------------------------&

-- Obmedzenie pre tabuľku rezervacia
ALTER TABLE rezervacia
ALTER TABLE rezervacia
    ADD CONSTRAINT rezervacia_ibfk_1 FOREIGN KEY (osoba_id) REFERENCES osoba (osoba_id);
