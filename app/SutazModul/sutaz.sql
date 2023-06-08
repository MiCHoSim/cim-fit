-- Štruktúra tabuľky pre tabuľku sutaz_typ
CREATE TABLE IF NOT EXISTS sutaz_typ (
                            sutaz_typ_id int(11) NOT NULL,
                            nazov varchar(255) COLLATE utf8_slovak_ci NOT NULL,
                            popis varchar(450) COLLATE utf8_slovak_ci DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovak_ci;
-- --------------------------------------------------------
--
-- Sťahujem dáta pre tabuľku clanok_typ
-- INSERT INTO sutaz_typ (sutaz_typ_id, nazov, popis) VALUES
-- (1, 'Informácie', '');
-- --------------------------------------------------------

-- Indexy pre tabuľku sutaz_typ
ALTER TABLE sutaz_typ
    ADD PRIMARY KEY (sutaz_typ_id);
-- --------------------------------------------------------

-- AUTO_INCREMENT pre tabuľku sutaz_typ
ALTER TABLE sutaz_typ
    MODIFY sutaz_typ_id int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
-- --------------------------------------------------------

-- Štruktúra tabuľky pre tabuľku sutaz
CREATE TABLE IF NOT EXISTS sutaz (
                              sutaz_id int(11) NOT NULL,
                              nazov varchar(255) COLLATE utf8_slovak_ci NOT NULL,
                              url varchar(255) COLLATE utf8_slovak_ci DEFAULT NULL,
                              sutaz_typ_id int(11) NOT NULL,
                              info text COLLATE utf8_slovak_ci,
                              datum_sutaz date NOT NULL,
                              cas_sutaz time NOT NULL,
                              datum_prihlasenie date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovak_ci;
-- --------------------------------------------------------

-- Sťahujem dáta pre tabuľku sutaz
-- INSERT INTO sutaz (sutaz_id, nazov, sutaz_typ_id, info, datum_sutaz, datum_registracia) VALUES ();
-- --------------------------------------------------------

-- Indexy pre tabuľku sutaz
ALTER TABLE sutaz
    ADD PRIMARY KEY (sutaz_id),
    ADD UNIQUE KEY url (url),
    ADD KEY sutaz_typ_id (sutaz_typ_id);
-- --------------------------------------------------------

-- AUTO_INCREMENT pre tabuľku sutaz
ALTER TABLE sutaz
    MODIFY sutaz_id int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
-- --------------------------------------------------------

-- Obmedzenie pre tabuľku sutaz
ALTER TABLE sutaz
    ADD CONSTRAINT sutaz_ibfk_1 FOREIGN KEY (sutaz_typ_id) REFERENCES sutaz_typ (sutaz_typ_id);
-- --------------------------------------------------------

-- Štruktúra tabuľky pre tabuľku sutaz_prihlaseny
CREATE TABLE IF NOT EXISTS sutaz_prihlaseny (
                                            sutaz_prihlaseny_id int(11) NOT NULL,
                                            sutaz_id int(11) NOT NULL,
                                            osoba_id int(11) NOT NULL
                                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovak_ci;
-- --------------------------------------------------------

-- Sťahujem dáta pre tabuľku sutaz_prihlaseny
-- INSERT INTO sutaz_prihlaseny (sutaz_prihlaseny_id, sutaz_id, osoba_id) VALUES ();
-- --------------------------------------------------------

-- Indexy pre tabuľku sutaz_prihlaseny
ALTER TABLE sutaz_prihlaseny
                            ADD PRIMARY KEY (sutaz_prihlaseny_id),
                            ADD KEY sutaz_id (sutaz_id),
                            ADD KEY osoba_id (osoba_id);
-- --------------------------------------------------------

-- AUTO_INCREMENT pre tabuľku sutaz_prihlaseny
ALTER TABLE sutaz_prihlaseny
    MODIFY sutaz_prihlaseny_id int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
-- --------------------------------------------------------

-- Obmedzenie pre tabuľku sutaz_prihlaseny
ALTER TABLE sutaz_prihlaseny
    ADD CONSTRAINT sutaz_prihlaseny_ibfk_1 FOREIGN KEY (sutaz_id) REFERENCES sutaz (sutaz_id) ON DELETE CASCADE,
    ADD CONSTRAINT sutaz_prihlaseny_ibfk_2 FOREIGN KEY (osoba_id) REFERENCES osoba (osoba_id);