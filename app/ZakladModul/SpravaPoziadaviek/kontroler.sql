-- Vytvorenie databázi 1_micho
CREATE DATABASE IF NOT EXISTS 1_micho CHARACTER SET utf8 COLLATE utf8_slovak_ci;
-- --------------------------------------------------------

-- Štruktúra tabuľky pre tabuľku kontroler
CREATE TABLE IF NOT EXISTS kontroler (
                           kontroler_id int(11) NOT NULL,
                           titulok varchar(255) COLLATE utf8_slovak_ci NOT NULL,
                           url varchar(255) COLLATE utf8_slovak_ci NOT NULL,
                           popisok varchar(255) COLLATE utf8_slovak_ci NOT NULL,
                           kontroler varchar(255) COLLATE utf8_slovak_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovak_ci;
-- --------------------------------------------------------

-- Sťahujem dáta pre tabuľku kontroler
INSERT INTO kontroler (kontroler_id, titulok, url, popisok, kontroler) VALUES
    (1, 'Správa požiadaviek', 'sprava-poziadaviek', 'Spracovananie požiadaviek pre kontroléry', 'ZakladModul\\SpravaPoziadaviek\\Kontroler\\SpravaPoziadaviek'),
    (2, 'Úvod', 'uvod', 'Úvodná stránka pre poskytovanie Služieb ohľadom Trenérstva (Gym, Fitness, Strava, Kondičný tréning) a Maserských služieb (masáže)', 'ZakladModul\\Kontroler\\Uvod'),
    (3, 'Chyba', 'chyba', 'Zobrazenie chybovej stránky', 'ZakladModul\\System\\Kontroler\\Chyba'),
    (4, 'Kontakt', 'kontakt', 'Kontaktný formulár', 'ZakladModul\\Kontroler\\Kontakt'),
    (5, 'Registrácia Kontrolér', 'registrovat', 'Registrácia Osoby', 'AdministraciaModul\Uzivatel\\Kontroler\\Registracia'),
    (6, 'Prihlasenie', 'prihlasit', 'Prihlásenie uživateľa', 'AdministraciaModul\\Uzivatel\\Kontroler\\Prihlasenie'),
    (7, 'Administŕacia', 'administracia', 'Administračné menu', 'AdministraciaModul\\Administracia\\Kontroler\\Administracia',
    (8, 'Cookies', 'cookies', 'Zásadi použivanie cookies na stránke', 'ZakladModul\\Kontroler\\Cookies',
    (9, 'Menu', 'menu', 'Menu stránky', 'ZakladModul\\Kontroler\\Menu');
-- --------------------------------------------------------

-- Indexy pre tabuľku kontroler
ALTER TABLE kontroler
    ADD PRIMARY KEY (kontroler_id),
    ADD UNIQUE KEY url (url);
-- --------------------------------------------------------

-- AUTO_INCREMENT pre tabuľku kontroler
ALTER TABLE kontroler
    MODIFY kontroler_id int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
-- --------------------------------------------------------
