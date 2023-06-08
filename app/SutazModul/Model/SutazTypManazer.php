<?php

namespace App\SutazModul\Model;


use Micho\Db;

/**
 ** Metóda pokytujuce Spŕavu údajov pre Súťaže typ
 * Class SutazTypManazer
 * @package App\SutazModul\Model
 */
class SutazTypManazer
{
    /**
     * Názov Tabuľky pre Spracovanie sutaz typ
     */
    const SUTAZ_TYP_TABULKA = 'sutaz_typ';

    /**
     * Konštanty Databázy 'sutaz_typ'
     */
    const SUTAZ_TYP_ID = 'sutaz_typ_id';
    const NAZOV = 'nazov';
    const POPIS = 'popis';

    /**
     ** Načíta všetky možnosti typov sútaži ako pár Nazov=>Id
     * @return array Páry Názov => Id
     */
    public function vratTypySutaziNazovId()
    {
        if ($data = Db::dopytPary('SELECT sutaz_typ_id, nazov FROM sutaz_typ ORDER BY nazov', self::NAZOV, self::SUTAZ_TYP_ID))
            return $data;
        return array();
    }

    /**
     ** Uloží nový typ článku do DB
     * @param array $sutazTyp Pole údajov na uloženie
     * @return string správa o ulození
     */
    public function ulozSutazTyp($sutazTyp)
    {
        Db::vloz(self::SUTAZ_TYP_TABULKA, $sutazTyp);
        return 'Nový typ súťaže bol úspešne uložený.';
    }

}

/*
 * Autor: MiCHo
 */