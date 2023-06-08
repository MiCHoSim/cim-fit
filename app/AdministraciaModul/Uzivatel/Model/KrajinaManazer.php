<?php

namespace App\AdministraciaModul\Uzivatel\Model;

use App\AdministraciaModul\Uzivatel\Kontroler\RegistraciaKontroler;
use App\ZakladModul\Kontroler\EmailKontroler;
use Micho\ChybaValidacie;
use Micho\Db;
use Micho\ChybaUzivatela;
use Micho\Formular\Formular;
use Micho\OdosielacEmailov;
use Nastavenia;
use Micho\Utility\Pole;

use PDOException;


/**
 ** Správa Krajiny
 * Class KrajinaManazer
 * @package App\AdministraciaModul\Uzivatel\Model
 */
class KrajinaManazer
{
    /**
     * Názov Tabuľky pre Spracovanie Krajiny
     */
    const KRAJINA_TABULKA = 'krajina';

    /**
     * Konštanty Databázy 'Krajina'
     */
    const KRAJINA_ID = 'krajina_id';
    const NAZOV = 'nazov';

    /**
     ** Načíta všetky krajiný uložené v DB
     * @return array uložené krajiny
     */
    public function vratKrajiny()
    {
        return Db::dopytPary('SELECT krajina_id, nazov FROM krajina ORDER BY nazov DESC', self::NAZOV, self::KRAJINA_ID);
    }

    /**
     ** Vráti názov krajiny podˇal jej ID
     * @param int $krajinaId Id krajiny
     * @return mixed
     */
    public function vratKrajinu($krajinaId)
    {
        return Db::dopytJedenRiadok('SELECT nazov FROM krajina WHERE krajina_id = ?', array($krajinaId))[self::NAZOV];
    }
}

/*
 * Autor: MiCHo
 */