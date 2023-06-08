-- Štruktúra tabuľky pre tabuľku clanok_typ
CREATE TABLE IF NOT EXISTS clanok_typ (
                            clanok_typ_id int(11) NOT NULL,
                            nazov varchar(255) COLLATE utf8_slovak_ci NOT NULL,
                            url varchar(255) COLLATE utf8_slovak_ci DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovak_ci;
-- --------------------------------------------------------
--
-- Sťahujem dáta pre tabuľku clanok_typ
INSERT INTO clanok_typ (clanok_typ_id, nazov) VALUES
(1, 'Informácie');
-- --------------------------------------------------------

-- Indexy pre tabuľku clanok_typ
ALTER TABLE clanok_typ
    ADD PRIMARY KEY (clanok_typ_id);
-- --------------------------------------------------------

-- AUTO_INCREMENT pre tabuľku clanok_typ
ALTER TABLE clanok_typ
    MODIFY clanok_typ_id int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
-- -------------------------------------------------------- --------------------------------------------------------

-- Štruktúra tabuľky pre tabuľku clanok
CREATE TABLE IF NOT EXISTS clanok (
                              clanok_id int(11) NOT NULL,
                              titulok varchar(255) COLLATE utf8_slovak_ci DEFAULT NULL,
                              clanok_typ_id int(11) NOT NULL,
                              obsah text COLLATE utf8_slovak_ci,
                              url varchar(255) COLLATE utf8_slovak_ci DEFAULT NULL,
                              odkaz varchar(255) COLLATE utf8_slovak_ci DEFAULT NULL,
                              popisok varchar(255) COLLATE utf8_slovak_ci DEFAULT NULL,
                              verejny tinyint(4) NOT NULL DEFAULT '0',
                              autor_id int(11) DEFAULT NULL,
                              upravil_autor_id int(11) DEFAULT NULL,
                              datum_vytvorenia datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                              datum_upravy datetime ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovak_ci;
-- --------------------------------------------------------

-- Sťahujem dáta pre tabuľku clanok
-- INSERT INTO clanok (clanok_id, tabulka, titulok, obsah, url, odkaz, popisok, verejny) VALUES ();
-- --------------------------------------------------------

-- Indexy pre tabuľku clanok
ALTER TABLE clanok
    ADD PRIMARY KEY (clanok_id),
    ADD UNIQUE KEY url (url),
    ADD KEY clanok_typ_id (clanok_typ_id),
    ADD KEY uzivatel_id (autor_id),
    ADD KEY (upravil_autor_id);
-- --------------------------------------------------------

-- AUTO_INCREMENT pre tabuľku clanok
ALTER TABLE clanok
    MODIFY clanok_id int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
-- --------------------------------------------------------

-- Obmedzenie pre tabuľku adresa
ALTER TABLE clanok
    ADD CONSTRAINT clanok_ibfk_1 FOREIGN KEY (clanok_typ_id) REFERENCES clanok_typ (clanok_typ_id),
    ADD CONSTRAINT clanok_ibfk_2 FOREIGN KEY (autor_id) REFERENCES uzivatel (uzivatel_id),
    ADD CONSTRAINT clanok_ibfk_3 FOREIGN KEY (upravil_autor_id) REFERENCES uzivatel (uzivatel_id);