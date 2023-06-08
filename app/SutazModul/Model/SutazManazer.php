<?php

namespace App\SutazModul\Model;


use Micho\ChybaUzivatela;
use Micho\Db;
use Micho\Utility\Pole;
use PDOException;

/**
 ** Metóda pokytujuce Spŕavu údajov pre Súťaže
 * Class SutazManazer
 * @package App\SutazModul\Model
 */
class SutazManazer
{
    /**
     * Názov Tabuľky pre Spracovanie sutaz
     */
    const SUTAZ_TABULKA = 'sutaz';

    /**
     * Konštanty Databázy 'sutaz'
     */
    const SUTAZ_ID = 'sutaz_id';
    const NAZOV = 'nazov';
    const URL= 'url';
    const SUTAZ_TYP_ID = SutazTypManazer::SUTAZ_TYP_ID;
    const INFO = 'info';
    const DATUM_SUTAZ = 'datum_sutaz';
    const CAS_SUTAZ = 'cas_sutaz';
    const DATUM_PRIHLASENIE = 'datum_prihlasenie';


    /**
     ** Uloži Článok. Pokiaľ je id false, Vloží nový, inak vykona editáciu
     * @param array $clanok Pole s Článkom
     * @throws ChybaUzivatela
     */
    public function ulozSutaz($sutaz)
    {
        if(!$sutaz[self::SUTAZ_ID])
        {
            unset($sutaz[self::SUTAZ_ID]); // aby prebehol autoinkrement, hodnota musí byť NULL, alebo stĺpec z dopytu musíme vynechať
            try
            {
                Db::vloz(self::SUTAZ_TABULKA, $sutaz);
                return 'Súťaž bola úspešne uložená.';
            }
            catch (PDOException $ex)
            {
                throw new ChybaUzivatela('Súťaž s touto URL adresov už existuje');
            }
        }
        else
        {
            Db::zmen(self::SUTAZ_TABULKA, $sutaz, 'WHERE sutaz_id = ?', array($sutaz[self::SUTAZ_ID]));
            return 'Súťaž bola aktualizovaná.';
        }
    }


    /**
     ** Vráti SúťaŽe z db podľa jeho URL
     * @param string $url Url súťaže
     * @param array $kluce Klúče Ktoré chcem načítať
     * @return array|mixed Pole so Súťažov alebo FALSE pri neúspechu
     */
    public function vratSutazUrl($url, $kluce)
    {
        $dopyt = 'SELECT ' . implode(', ',$kluce) . ' FROM sutaz';

        $data = Db::dopytJedenRiadok($dopyt . ' WHERE sutaz.url = ?', array($url));

        if(empty($data))
            throw new ChybaUzivatela('Súťaž s danou url adresou nexistuje');

        return is_array($data) ? Pole::filtrujKluce($data, $kluce) : $data;
    }

    /**
     ** Vráti SúťaŽe z db podľa jeho Id
     * @param string $sutazId Id súťaže
     * @param array $kluce Klúče Ktoré chcem načítať
     * @return array|mixed Pole so Súťažov alebo FALSE pri neúspechu
     */
    public function vratSutazId($sutazId, $kluce)
    {
        $dopyt = 'SELECT ' . implode(', ',$kluce) . ' FROM sutaz';

        $data = Db::dopytJedenRiadok($dopyt . ' WHERE sutaz_id = ?', array($sutazId));

        if(empty($data))
            throw new ChybaUzivatela('Súťaž s danou url adresou nexistuje');

        return is_array($data) ? Pole::filtrujKluce($data, $kluce) : $data;
    }

    /**
     ** Vráti Sútaž detzail
     * @param string $url Url súťaže
     * @return array|mixed Pole so Súťažov alebo FALSE pri neúspechu
     */
    public function vratSutazDetail($url)
    {
        $kluce = array('sutaz_id', 'sutaz_nazov', 'url', 'info', 'datum_sutaz', 'cas_sutaz', 'datum_prihlasenie','sutaz_typ_nazov', 'popis');

        $dopyt = 'SELECT sutaz_id, sutaz.nazov as sutaz_nazov, url, info, datum_sutaz, cas_sutaz, datum_prihlasenie, sutaz_typ.nazov as sutaz_typ_nazov, popis
                                            FROM sutaz 
                                            JOIN sutaz_typ USING (sutaz_typ_id) 
                                            WHERE sutaz.url = ?';

        $data = Db::dopytJedenRiadok($dopyt, array($url));

        return is_array($data) ? Pole::filtrujKluce($data, $kluce) : $data;
    }

    /**
     ** Vráti zoznam Súťaži
     * @param int $osobaId Id prihlasenej osoby
     * @param false $aktivna Či zobrazujem aktivni súťaže
     * @return array|mixed
     */
    public function vratSutazeZoznam($osobaId, $aktivna = false)
    {
        $kluce = array('sutaz_id', 'sutaz_nazov', 'url', 'info', 'datum_sutaz', 'cas_sutaz', 'datum_prihlasenie','sutaz_typ_nazov', 'popis', 'prihlasenych', 'prihlaseny', 'prebieha', 'prihlasenie_ukoncene');

        $dopyt = 'SELECT sutaz_id, sutaz.nazov as sutaz_nazov, url, info, datum_sutaz, cas_sutaz, datum_prihlasenie, sutaz_typ.nazov as sutaz_typ_nazov, popis, 
                                            (SELECT COUNT(*) FROM sutaz_prihlaseny WHERE sutaz_prihlaseny.sutaz_id = sutaz.sutaz_id) as prihlasenych,
                                            (SELECT COUNT(*) FROM sutaz_prihlaseny WHERE sutaz_prihlaseny.sutaz_id = sutaz.sutaz_id AND osoba_id = ? ) as prihlaseny,
                                            IF(datum_sutaz = CURDATE(), true, false) AS prebieha,
                                            IF(CURDATE() >= datum_prihlasenie, true, false) AS prihlasenie_ukoncene
                                            FROM sutaz 
                                            JOIN sutaz_typ USING (sutaz_typ_id) ';  // prihlaseny -> zisti ci je uzivatel prihlasený na danú sutaž

        if($aktivna)
        {
            $dopyt .= ' WHERE  datum_sutaz >= CURDATE() ';
        }
        $dopyt .= ' ORDER BY datum_sutaz ';

        $data = Db::dopytVsetkyRiadky($dopyt, array($osobaId));

        return is_array($data) ? Pole::filtrujKluce($data, $kluce) : $data;
    }

    /**
     ** Vráti zoznam Súťaži daného uživateľa
     * @param int $osobaId Id prihlasenej osoby
     * @return array|mixed
     */
    public function vratSutazeUzivatelaZoznam($osobaId)
    {
        $kluce = array('sutaz_id', 'sutaz_nazov', 'url', 'info', 'datum_sutaz', 'cas_sutaz', 'datum_prihlasenie','sutaz_typ_nazov', 'popis', 'prihlasenych', 'prebieha', 'stara', 'prihlasenie_ukoncene');

        $dopyt = 'SELECT sutaz_id, sutaz.nazov as sutaz_nazov, url, info, datum_sutaz, cas_sutaz, datum_prihlasenie, sutaz_typ.nazov as sutaz_typ_nazov, popis, 
                                            (SELECT COUNT(*) FROM sutaz_prihlaseny WHERE sutaz_prihlaseny.sutaz_id = sutaz.sutaz_id) as prihlasenych,
                                            IF(datum_sutaz = CURDATE(), true, false) AS prebieha,
                                            IF(CURDATE() > datum_sutaz, true, false) AS stara,
                                            IF(CURDATE() >= datum_prihlasenie, true, false) AS prihlasenie_ukoncene
                                            FROM sutaz
                                            JOIN sutaz_typ USING (sutaz_typ_id) 
                                            JOIN sutaz_prihlaseny USING (sutaz_id) 
                                            WHERE osoba_id = ? ';  // prihlaseny -> zisti ci je uzivatel prihlasený na danú sutaž

        $dopyt .= 'ORDER BY datum_sutaz=CURDATE() DESC,
                              datum_sutaz>CURDATE() DESC,
                              datum_sutaz, 
                              datum_sutaz<CURDATE() DESC ';

        $data = Db::dopytVsetkyRiadky($dopyt, array($osobaId));

        return is_array($data) ? Pole::filtrujKluce($data, $kluce) : $data;
    }


    /**
     ** Vráti zoznam Súťaži kratky
     * @return array|mixed
     */
    public function vratSutazeZoznamKratky()
    {
        $kluce = array('sutaz_id', 'sutaz_nazov', 'url', 'datum_sutaz', 'sutaz_typ_nazov', 'prebieha', 'stara');

        $dopyt = 'SELECT sutaz_id, sutaz.nazov as sutaz_nazov, url, datum_sutaz, sutaz_typ.nazov as sutaz_typ_nazov,
                                            IF(datum_sutaz = CURDATE(), true, false) AS prebieha,
                                            IF(CURDATE() > datum_sutaz, true, false) AS stara
                                            FROM sutaz 
                                            JOIN sutaz_typ USING (sutaz_typ_id) ';

        $dopyt .= 'ORDER BY datum_sutaz=CURDATE() DESC,
                              datum_sutaz>CURDATE() DESC,
                              datum_sutaz, 
                              datum_sutaz<CURDATE() DESC ';

        $data = Db::dopytVsetkyRiadky($dopyt, array());

        return is_array($data) ? Pole::filtrujKluce($data, $kluce) : $data;
    }

    /**
     ** Odstráni Súťaž
     * @param string $url URL súťaže
     */
    public function odstranSutaz($url)
    {
        Db::dopyt('DELETE FROM sutaz WHERE url = ?', array($url));
    }



}

/*
 * Autor: MiCHo
 */