-- Štruktúra tabuľky pre tabuľku trener
CREATE TABLE IF NOT EXISTS trener (
                                    trener_id int(11) NOT NULL,
                                    osoba_id int(11) NOT NULL,
                                    meno varchar(30) COLLATE utf8_slovak_ci DEFAULT NULL,
                                    farba char(7) NOT NULL,
                                    aktivny int(11) DEFAULT NULL
                                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovak_ci;
-- --------------------------------------------------------

-- Sťahujem dáta pre tabuľku trener
-- INSERT INTO trener (uzivatel_id, heslo, admin, programator) VALUES();
-- --------------------------------------------------------

-- Indexy pre tabuľku trener
ALTER TABLE trener
            ADD PRIMARY KEY (trener_id),
            ADD UNIQUE KEY osoba_id (osoba_id);
-- --------------------------------------------------------

-- AUTO_INCREMENT pre tabuľku trener
ALTER TABLE trener
    MODIFY trener_id int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
-- --------------------------------------------------------

-- Obmedzenie pre tabuľku trener
ALTER TABLE trener
            ADD CONSTRAINT trener_ibfk_1 FOREIGN KEY (osoba_id) REFERENCES osoba (osoba_id);
-- ---------------------------------------------------------- --------------------------------------------------------