-- Štruktúra tabuľky pre tabuľku permanentka_typ
CREATE TABLE IF NOT EXISTS permanentka_typ (
                                permanentka_typ_id int(11) NOT NULL,
                                nazov varchar(255) COLLATE utf8_slovak_ci NOT NULL
                                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovak_ci;
-- --------------------------------------------------------
--
-- Sťahujem dáta pre tabuľku permanentka_typ
INSERT INTO permanentka_typ (permanentka_typ_id, nazov) VALUES
                            (1, 'Tyždenná'),
                            (2, '10-Vstupová'),
                            (3, 'Mesačná'),
                            (4, 'Polročná');
-- --------------------------------------------------------

-- Indexy pre tabuľku permanentka_typ
ALTER TABLE permanentka_typ
    ADD PRIMARY KEY (permanentka_typ_id);
-- --------------------------------------------------------

-- AUTO_INCREMENT pre tabuľku permanentka_typ
ALTER TABLE permanentka_typ
    MODIFY permanentka_typ_id int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
-- -------------------------------------------------------- --------------------------------------------------------

-- Štruktúra tabuľky pre tabuľku permanentka
CREATE TABLE IF NOT EXISTS permanentka (
                              permanentka_id int(11) NOT NULL,
                              permanentka_typ_id int(11) NOT NULL,
                              datum date NOT NULL,
                              datum_zneaktivnenia datetime DEFAULT NULL,
                              aktivna int(11) DEFAULT 0,
                              osoba_id int(11) NOT NULL,
                              zostatok_vstupov int(11) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovak_ci;
-- --------------------------------------------------------

-- Sťahujem dáta pre tabuľku permanentka
-- INSERT INTO rezervacia (permanentka_id, datum_id, cas_id, osoba_id) VALUES ();
-- --------------------------------------------------------

-- Indexy pre tabuľku permanentka
ALTER TABLE permanentka
    ADD PRIMARY KEY (permanentka_id),
    ADD KEY permanentka_typ (permanentka_typ_id),
    ADD KEY osoba_id (osoba_id);
-- --------------------------------------------------------

-- AUTO_INCREMENT pre tabuľku permanentka
ALTER TABLE permanentka
    MODIFY permanentka_id int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
-- --------------------------------------------------------

-- Obmedzenie pre tabuľku permanentka
ALTER TABLE permanentka
    ADD CONSTRAINT permanentka_ibfk_1 FOREIGN KEY (osoba_id) REFERENCES osoba (osoba_id),
    ADD CONSTRAINT permanentka_ibfk_2 FOREIGN KEY (permanentka_typ_id) REFERENCES permanentka_typ (permanentka_typ_id);