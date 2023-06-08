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
 * Class OsobaDetailManazer
 * @package App\AdministraciaModul\Uzivatel\Model
 */
class OsobaDetailManazer
{
    /**
     * Názov Tabuľky pre Spracovanie Osoby detail
     */
    const OSOBA_DETAIL_TABULKA = 'osoba_detail';

    /**
     * Konštanty Databázy 'osoba_detail'
     */
    const OSOBA_DETAIL_ID = 'osoba_detail_id';
    const MENO = 'meno';
    const PRIEZVISKO = 'priezvisko';
    const NAZOV_SPOLOCNOSTI = 'nazov_spolocnosti';
    const TEL = 'tel';
    const EMAIL = 'email';
    const DIC = 'dic';
    const IC = 'ic';
    const SPISOVA_ZNACKA = 'spisova_znacka';
    const POHLAVIE = 'pohlavie';

    /**
     ** Vymaže starý detail osoby, v prípade že sa neviaže na inú Tabuľku
     * @param int $osobaDetailId ID detailu osoby
     */
    public function vymazOsobaDetail($osobaDetailId)
    {
        try
        {
            Db::dopyt('DELETE FROM osoba_detail WHERE osoba_detail_id = ?', array($osobaDetailId));
        }
        catch (PDOException $chy){} // položku sa nepodarilo odstrániť, pretože je napojená na inú tabuľku
    }

    /**
     ** Vráti email uživateľa
     * @param int $uzivatelId id uzivatela ktorej chem ziskať email
     * @return false|mixed email uživateľa
     */
    public function vratEmail($uzivatelId)
    {
        $email = Db::dopytSamotny('SELECT email
                                    FROM osoba_detail
                                    JOIN osoba USING (osoba_detail_id)
                                    WHERE uzivatel_id = ?', array($uzivatelId));
        return $email;
    }


}
/*
 * Tento kód spadá pod licenci ITnetwork Premium - http://www.itnetwork.cz/licence
 * Je určen pouze pro osobní užití a nesmí být šířen ani využíván v open-source projektech.
 */

/*
 * Niektoré časti sú upravené
 * Autor: MiCHo
 */