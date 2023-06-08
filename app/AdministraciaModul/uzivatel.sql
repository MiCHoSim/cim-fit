-- Štruktúra tabuľky pre tabuľku uzivatel
CREATE TABLE IF NOT EXISTS uzivatel (
                                    uzivatel_id int(11) NOT NULL,
                                    datum_registracie datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                                    datum_prihlasenia datetime DEFAULT NULL,
                                    heslo varchar(255) CHARACTER SET utf8 COLLATE utf8_czech_ci DEFAULT NULL,
                                    admin int(11) DEFAULT NULL,
                                    programator int(11) DEFAULT NULL,
                                    trener int(11) DEFAULT NULL
                                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovak_ci;
-- --------------------------------------------------------

-- Sťahujem dáta pre tabuľku uzivatel
-- INSERT INTO uzivatel (uzivatel_id, heslo, admin, programator) VALUES();
-- --------------------------------------------------------

-- Indexy pre tabuľku uzivatel
ALTER TABLE uzivatel
    ADD PRIMARY KEY (uzivatel_id);
-- --------------------------------------------------------

-- AUTO_INCREMENT pre tabuľku uzivatel
ALTER TABLE uzivatel
    MODIFY uzivatel_id int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
-- ---------------------------------------------------------- --------------------------------------------------------

-- Štruktúra tabuľky pre tabuľku krajina
CREATE TABLE IF NOT EXISTS krajina (
                           krajina_id int(11) NOT NULL,
                           nazov varchar(255) COLLATE utf8_slovak_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovak_ci;
-- --------------------------------------------------------
--
-- Sťahujem dáta pre tabuľku krajina
INSERT INTO krajina (krajina_id, nazov) VALUES
(1, 'Slovenská republika');
-- --------------------------------------------------------

-- Indexy pre tabuľku krajina
ALTER TABLE krajina
    ADD PRIMARY KEY (krajina_id);
-- --------------------------------------------------------

-- AUTO_INCREMENT pre tabuľku krajina
ALTER TABLE krajina
    MODIFY krajina_id int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
-- ---------------------------------------------------------- --------------------------------------------------------

-- Štruktúra tabuľky pre tabuľku adresa
CREATE TABLE IF NOT EXISTS adresa (
                           adresa_id int(11) NOT NULL,
                           ulica varchar(30) COLLATE utf8_slovak_ci NOT NULL,
                           supisne_cislo int(11) NOT NULL,
                           orientacne_cislo int(11) NOT NULL DEFAULT '0',
                           mesto varchar(30) COLLATE utf8_slovak_ci NOT NULL,
                           psc varchar(10) COLLATE utf8_slovak_ci NOT NULL,
                           krajina_id int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovak_ci;
-- --------------------------------------------------------

-- Sťahujem dáta pre tabuľku adresa
-- INSERT INTO adresa (adresa_id, ulica, supisne_cislo, orientacne_cislo, mesto, psc, krajina_id) VALUES ();
-- --------------------------------------------------------

-- Indexy pre tabuľku adresa
ALTER TABLE adresa
    ADD PRIMARY KEY (adresa_id),
    ADD KEY krajina_id (krajina_id);
-- --------------------------------------------------------

-- AUTO_INCREMENT pre tabuľku adresa
ALTER TABLE adresa
    MODIFY adresa_id int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
-- --------------------------------------------------------

-- Obmedzenie pre tabuľku adresa
ALTER TABLE adresa
    ADD CONSTRAINT adresa_ibfk_1 FOREIGN KEY (krajina_id) REFERENCES krajina (krajina_id);
-- ---------------------------------------------------------- --------------------------------------------------------

-- Štruktúra tabuľky pre tabuľku bankove_konto
CREATE TABLE IF NOT EXISTS bankove_konto (
                                 bankove_konto_id int(11) NOT NULL,
                                 kod_banky varchar(4) COLLATE utf8_slovak_ci NOT NULL,
                                 cislo_uctu varchar(20) COLLATE utf8_slovak_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovak_ci;
-- --------------------------------------------------------

-- Indexy pre tabuľku bankove_konto
ALTER TABLE bankove_konto
    ADD PRIMARY KEY (bankove_konto_id);
-- --------------------------------------------------------

-- AUTO_INCREMENT pre tabuľku bankove_konto
ALTER TABLE bankove_konto
    MODIFY bankove_konto_id int(11) NOT NULL AUTO_INCREMENT;
-- ---------------------------------------------------------- --------------------------------------------------------

-- Štruktúra tabuľky pre tabuľku osoba_detail
CREATE TABLE IF NOT EXISTS osoba_detail (
                                osoba_detail_id int(11) NOT NULL,
                                meno varchar(30) COLLATE utf8_slovak_ci DEFAULT NULL,
                                priezvisko varchar(30) COLLATE utf8_slovak_ci DEFAULT NULL,
                                nazov_spolocnosti varchar(50) COLLATE utf8_slovak_ci DEFAULT NULL,
                                tel varchar(20) COLLATE utf8_slovak_ci NOT NULL,
                                email varchar(100) COLLATE utf8_slovak_ci NOT NULL,
                                dic varchar(20) COLLATE utf8_slovak_ci DEFAULT NULL,
                                ic int(15) DEFAULT NULL,
                                spisova_znacka varchar(100) COLLATE utf8_slovak_ci DEFAULT NULL,
                                pohlavie varchar(30) COLLATE utf8_slovak_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovak_ci;
-- --------------------------------------------------------

-- Sťahujem dáta pre tabuľku osoba_detail
-- INSERT INTO osoba_detail (osoba_detail_id, meno, priezvisko, nazov_spolocnosti, tel, email, dic, ic, spisova_znacka) VALUES ();
-- --------------------------------------------------------

-- Indexy pre tabuľku osoba_detail
ALTER TABLE osoba_detail
    ADD PRIMARY KEY (osoba_detail_id);
-- --------------------------------------------------------

-- AUTO_INCREMENT pre tabuľku osoba_detail
ALTER TABLE osoba_detail
    MODIFY osoba_detail_id int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
-- ---------------------------------------------------------- --------------------------------------------------------

-- Štruktúra tabuľky pre tabuľku osoba
CREATE TABLE IF NOT EXISTS osoba (
                            osoba_id int(11) NOT NULL,
                            osoba_detail_id int(11) NOT NULL,
                            adresa_id int(11) DEFAULT NULL,
                            dodacia_adresa_id int(11) DEFAULT NULL,
                            bankove_konto_id int(11) DEFAULT NULL,
                            uzivatel_id int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovak_ci;
-- --------------------------------------------------------

-- Sťahujem dáta pre tabuľku osoba
-- INSERT INTO osoba (osoba_id, osoba_detail_id, adresa_id, dodacia_adresa_id, bankove_konto_id, uzivatel_id) VALUES ();
-- --------------------------------------------------------

-- Indexy pre tabuľku osoba
ALTER TABLE osoba
    ADD PRIMARY KEY (osoba_id),
  ADD KEY uzivatel_id (uzivatel_id),
  ADD KEY bankove_konto_id (bankove_konto_id),
  ADD KEY dodacia_adresa_id (dodacia_adresa_id),
  ADD KEY adresa_id (adresa_id),
  ADD KEY osoba_detail_id (osoba_detail_id);
-- --------------------------------------------------------

-- AUTO_INCREMENT pre tabuľku osoba
ALTER TABLE osoba
    MODIFY osoba_id int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
-- --------------------------------------------------------

-- Obmedzenie pre tabuľku osoba
ALTER TABLE osoba
    ADD CONSTRAINT osoba_ibfk_1 FOREIGN KEY (osoba_detail_id) REFERENCES osoba_detail (osoba_detail_id),
  ADD CONSTRAINT osoba_ibfk_2 FOREIGN KEY (adresa_id) REFERENCES adresa (adresa_id),
  ADD CONSTRAINT osoba_ibfk_3 FOREIGN KEY (dodacia_adresa_id) REFERENCES adresa (adresa_id),
  ADD CONSTRAINT osoba_ibfk_4 FOREIGN KEY (bankove_konto_id) REFERENCES bankove_konto (bankove_konto_id),
  ADD CONSTRAINT osoba_ibfk_5 FOREIGN KEY (uzivatel_id) REFERENCES uzivatel (uzivatel_id);
-- ---------------------------------------------------------- --------------------------------------------------------


-- Štruktúra tabuľky pre tabuľku klient
CREATE TABLE IF NOT EXISTS klient (
                                klient_id int(11) NOT NULL,
                                trener_id int(11) NOT NULL,
                                osoba_klient_id int(11) NOT NULL
                                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovak_ci;
-- --------------------------------------------------------

-- Sťahujem dáta pre tabuľku osoba
-- INSERT INTO osoba (osoba_id, osoba_detail_id, adresa_id, dodacia_adresa_id, bankove_konto_id, uzivatel_id) VALUES ();
-- --------------------------------------------------------

-- Indexy pre tabuľku klient
ALTER TABLE klient
    ADD PRIMARY KEY (klient_id),
    ADD UNIQUE KEY (trener_id, osoba_klient_id);
-- --------------------------------------------------------

-- AUTO_INCREMENT pre tabuľku osoba
ALTER TABLE klient
    MODIFY klient_id int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
-- --------------------------------------------------------

-- Obmedzenie pre tabuľku klient
ALTER TABLE klient
    ADD CONSTRAINT klient_ibfk_1 FOREIGN KEY (trener_id) REFERENCES trener (trener_id),
    ADD CONSTRAINT klient_ibfk_2 FOREIGN KEY (osoba_klient_id) REFERENCES osoba (osoba_id);
-- ---------------------------------------------------------- --------------------------------------------------------
