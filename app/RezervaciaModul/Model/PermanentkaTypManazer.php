<?php

namespace App\RezervaciaModul\Model;

use App\AdministraciaModul\Uzivatel\Model\OsobaManazer;
use Micho\Db;
use DateTime;
use Micho\Utility\DatumCas;
use Micho\Utility\Pole;


/**
 * Class PermanentkaTypManazer
 * @package App\RezervaciaModul\Model
 */
class PermanentkaTypManazer
{
    /**
     * Názov Tabuľky pre Spracovanie permanentky typ
     */
    const PERMANENTKA_TYP_TABULKA = 'permanentka_typ';

    /**
     * Konštanty Databázy 'permanentka_typ'
     */
    const PERMANENTKA_TYP_ID = 'permanentka_typ_id';
    const NAZOV = 'nazov';

    const TYZDENNA = 1;
    const DESAT_VSTUPOVA = 2;
    const MESACNA = 3;
    const POLROCNA = 4;




    /**
     ** Načitá typy permanentiek
     * @return array typy permanentiek
     */
    public function nacitajTypyPermanentiek()
    {
        return Db::dopytPary('SELECT permanentka_typ_id, nazov FROM permanentka_typ ORDER BY permanentka_typ_id', self::NAZOV, self::PERMANENTKA_TYP_ID);
    }
  }
/*
 * Autor: MiCHo
 */