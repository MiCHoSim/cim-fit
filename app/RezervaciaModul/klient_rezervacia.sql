-- Štruktúra tabuľky pre tabuľku klient_rezervacia
CREATE TABLE IF NOT EXISTS klient_rezervacia (
                              klient_rezervacia_id int(11) NOT NULL,
                              rezervacia_id int(11) NOT NULL,
                              klient_osoba_id int(11) NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovak_ci;
-- --------------------------------------------------------

-- Sťahujem dáta pre tabuľku klient_rezervacia
-- INSERT INTO rezervacia (rezervacia_id, datum_id, cas_id, osoba_id) VALUES ();
-- --------------------------------------------------------

-- Indexy pre tabuľku klient_rezervacia
ALTER TABLE klient_rezervacia
    ADD PRIMARY KEY (klient_rezervacia_id),
    ADD KEY rezervacia_id (rezervacia_id),
    ADD KEY klient_osoba_id (klient_osoba_id);
-- --------------------------------------------------------

-- AUTO_INCREMENT pre tabuľku klient_rezervacia
ALTER TABLE klient_rezervacia
    MODIFY klient_rezervacia_id int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
-- --------------------------------------------------------

-- Obmedzenie pre tabuľku klient_rezervacia
ALTER TABLE klient_rezervacia
    ADD CONSTRAINT klient_rezervacia_ibfk_1 FOREIGN KEY (rezervacia_id) REFERENCES rezervacia (rezervacia_id),
    ADD CONSTRAINT klient_rezervacia_ibfk_2 FOREIGN KEY (klient_osoba_id) REFERENCES osoba (osoba_id);
