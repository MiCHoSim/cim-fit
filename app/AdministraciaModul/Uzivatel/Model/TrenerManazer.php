<?php

namespace App\AdministraciaModul\Uzivatel\Model;

use App\AdministraciaModul\Uzivatel\Kontroler\RegistraciaKontroler;
use App\RezervaciaModul\Model\RezervaciaManazer;
use App\ZakladModul\Kontroler\EmailKontroler;
use Micho\ChybaValidacie;
use Micho\Db;
use Micho\ChybaUzivatela;
use Micho\Formular\Formular;
use Micho\OdosielacEmailov;
use Micho\Utility\Retazec;
use Nastavenia;
use Micho\Utility\Pole;

use PDOException;

/**
 ** Spracovanie tabuľky trénerov
 * Class TrenerManazer
 * @package App\AdministraciaModul\Uzivatel\Model
 */
class TrenerManazer
{
    /**
     * Názov Tabuľky pre Spracovanie Trenerov
     */
    const TRENER_TABULKA = 'trener';

    /**
     * Konštanty Databázy 'trener'
     */
    const TRENER_ID = 'trener_id';
    const OSOBA_ID = 'osoba_id';
    const PREZIVKA = 'prezivka';
    const FARBA = 'farba';
    const AKTIVNY = 'aktivny';

    public static $TrenerDetail = array(self::PREZIVKA, self::FARBA, self::AKTIVNY, self::OSOBA_ID);
    /**
     ** Vráti všetkých Trénerov
     * @return mixed
     */
    public function vratTrenerov()
    {
        $kluce = array(self::TRENER_ID, self::PREZIVKA, self::FARBA, self::AKTIVNY, 'osoba', UzivatelManazer::UZIVATEL_ID, 'aktivny_text');

        $data = Db::dopytVsetkyRiadky('SELECT trener_id, trener.prezivka, farba, if(aktivny, "Aktívny", "Neaktívny") as aktivny_text, aktivny, 
                                                CONCAT(COALESCE(osoba_detail.meno, ""), " ", COALESCE(osoba_detail.priezvisko, "")) AS osoba, uzivatel_id
                                            FROM trener
                                            JOIN osoba USING (osoba_id)
                                            JOIN osoba_detail USING (osoba_detail_id)
                                            ORDER BY aktivny DESC, trener_id
        ');

        return is_array($data) ? Pole::filtrujKluce($data, $kluce) : $data;
    }


    /**
     ** Nastyvý trenéra na neaktivného/aktivného
     * @param int $trenerId ID trenéra ktorého chcem nastaviť an neaktivného
     * @return false|mixed Nový stav Textovo
     */
    public function upravStav($trenerId)
    {
         Db::dopyt('UPDATE trener 
                            SET aktivny = IF (aktivny, 0, 1) 
                            WHERE trener_id = ?', array($trenerId));

        $data = Db::dopytJedenRiadok('SELECT if(aktivny, "Aktívny", "Neaktívny") as stav, uzivatel_id,  aktivny
                                            FROM trener 
                                            JOIN osoba USING (osoba_id)
                                            WHERE trener_id = ?', array($trenerId));
        $stav = $data['stav'];
        $uzivatelId = $data['uzivatel_id'];
        $aktivny = $data['aktivny'];

        if(UzivatelManazer::$uzivatel[UzivatelManazer::UZIVATEL_ID] == $uzivatelId) // ak odstranuejm ako trenera seba tak aj v session uzivatela to nastavim
             $_SESSION['uzivatel']['trener'] = $aktivny;

        return $stav;
    }

    /**
     ** Spracuje požiadavku na priradenia Trénera
     * @param array $dataTrenera Pole hodnot trenera
     * @throws ChybaUzivatela
     */
    public function pridajTrenera($dataTrenera)
    {
        $osobaManazer = new OsobaManazer();
        if (!$osobaManazer->overExistenciuEmailu($dataTrenera['email']))
            throw new ChybaUzivatela('Osoba s týmto emailom nieje registovaná');

        $dataTrenera['osoba_id'] = $osobaManazer->vratOsobaIdEmailu($dataTrenera['email']); // nacitanie osoba_id
        $dataTrenera['aktivny'] = 1;
        unset($dataTrenera['email']); // zrušenie osoba_id

        $this->nastavTrenera($dataTrenera);
    }

    /**
     ** Vytvorý nového trénera
     * @param array $dataTrenera Pole hodnot trenera
     * @throws ChybaUzivatela
     */
    private function nastavTrenera($dataTrenera)
    {
        try {
            Db::vloz('trener', $dataTrenera);
        }
        catch (PDOException $chyba)
        {
            if (Retazec::obsahuje($chyba->getMessage(), 'osoba_id'))
            {
                throw new ChybaUzivatela('Tento uživateľ už je zaradený medzi Trénerov');
            }
            if (Retazec::obsahuje($chyba->getMessage(), 'prezivka'))
            {
                throw new ChybaUzivatela('Tréner s touto "prezívkov" už existuje');
            }
            if (Retazec::obsahuje($chyba->getMessage(), 'farba'))
            {
                throw new ChybaUzivatela('Tréner s touto "farbou" už existuje');
            }
            echo $chyba->getMessage();
        }

        $_SESSION['uzivatel']['trener'] = 1;
    }

    /**
     ** Upravy udaje Trénera
     * @param array $dataTrener Pole úadajov na zmenu
     */
    public function aktualizujUdajeTrenera($dataTrener)
    {
        Db::zmen(self::TRENER_TABULKA, $dataTrener, 'WHERE osoba_id = ?', array($dataTrener[self::OSOBA_ID]));
    }

    /**
     ** vráti Id trenéra
     * @param int|string $uzivatelId Id uživateľa
     * @return false|mixed
     */
    public function vratTrenerId($uzivatelId)
    {
        return Db::dopytSamotny('SELECT trener_id 
                                            FROM trener 
                                            JOIN osoba USING (osoba_id)
                                            WHERE uzivatel_id = ?', array($uzivatelId));
    }

}

/*
 * Autor: MiCHo
 */