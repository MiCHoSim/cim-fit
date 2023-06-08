<?php

namespace App\ClanokModul\Model;

use App\AdministraciaModul\Uzivatel\Model\OsobaManazer;
use App\AdministraciaModul\Uzivatel\Model\UzivatelManazer;
use Micho\ChybaUzivatela;
use Micho\Db;
use Micho\Obrazok;
use Micho\Subory\Priecinok;
use Micho\Utility\Pole;
use Micho\Utility\Retazec;
use PDOException;

/**
 * Class ClanokTypManazer
 * @package App\ClanokModul\Model
 */
class ClanokTypManazer
{
    /**
     * Názov Tabuľky pre Spracovanie člankov
     */
    const CLANOK_TYP_TABULKA = 'clanok_typ';

    /**
     * Konštanty Databázy 'clanok'
     */
    const CLANOK_TYP_ID = 'clanok_typ_id';
    const NAZOV = 'nazov';
    const URL = 'url';

    /**
     * ID typov Článkov
     */
    const CLANOK_INFORMACIA = 1;
    const CLANOK_UVOD = 2;
    const CLANOK_SLUZBA = 3;
    const CLANOK_TRENING = 4;
    const CLANOK_STRAVA = 5;

    const TYPY_CLANKOV_URL_ID = array('clanok' => self::CLANOK_INFORMACIA, 'uvod' => self::CLANOK_UVOD, 'sluzba' => self::CLANOK_SLUZBA, 'trening' => self::CLANOK_TRENING, 'strava' => self::CLANOK_STRAVA);

    const TYPY_CLANKOV_URL_NAZOV = array('clanok' => 'Článok informácia', 'uvod' => 'Úvod', 'sluzba' => 'Služba', 'trening' => 'Tréning', 'strava' => 'Strava');


    /**
     ** Načíta všetky možnosti typov článkov ako pár Nazov=>Id
     * @return array uložené krajiny
     */
    public function vratTypyClankovNazovId()
    {
        return Db::dopytPary('SELECT clanok_typ_id, nazov FROM clanok_typ ORDER BY clanok_typ_id', self::NAZOV, self::CLANOK_TYP_ID);
    }

    /**
     ** Načíta všetky možnosti typov článkov ako pár Nazov=>url
     * @return array uložené krajiny
     */
    public function vratTypyClankovNazovUrl()
    {
        return Db::dopytPary('SELECT url, nazov FROM clanok_typ ORDER BY nazov', self::NAZOV, self::URL);
    }
}
/*
 * Autor: MiCHo
 */