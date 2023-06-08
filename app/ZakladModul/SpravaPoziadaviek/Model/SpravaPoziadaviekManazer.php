<?php


namespace App\ZakladModul\SpravaPoziadaviek\Model;

use Micho\Db;
use Micho\ChybaUzivatela;
use Micho\Utility\Pole;

use PDOException;


/**
 ** Trieda poskytuje metódy pre správu kontrolérov v redakčnom systéme
 * Class SpravaPoziadaviekManazer
 * @package App\ZakladModul\SpravaPoziadaviek\Model
 */
class SpravaPoziadaviekManazer
{
    /**
     * Názov Tabuľky pre Spracovanie Kontrolérov
     */
    const KONTROLER_TABULKA = 'kontroler';

    /**
     * Konštanty Databázy 'kontroler'
     */
    const KONTROLER_ID = 'kontroler_id';
    const TITULOK = 'titulok';
    const URL = 'url';
    const POPISOK = 'popisok';
    const KONTROLER = 'kontroler';

    /**
     * @var array Aktuálne načitaný kontrolér
     */
    public static $kontroler;

    /**
     ** Načíta kontrolér z Db a uloží ho do statickej vlastnosti $kontroler
     * @param string $url Url kontroléra
     */
    public function nacitajKontroler($url)
    {
        $kluce = array (self::KONTROLER_ID, self::TITULOK, self::URL, self::POPISOK, self::KONTROLER); // názvy stĺpcov,ktoré chcem z tabuľky načitať
        self::$kontroler = $this->vratKontroler($url, $kluce);
    }

    /**
     ** Vráti kontrolér z db podľa jeho URL
     * @param string $url Url kontroléra
     * @param array $kluce Klúče Ktoré chcem načitať
     * @return array|mixed Pole s kontrolérom alebo FALSE pri neúspechu
     */
    public function vratKontroler($url, $kluce)
    {
        $data = Db::dopytJedenRiadok('SELECT ' . implode(', ',$kluce) . ' 
                                    FROM kontroler WHERE url = ?', array($url));

        return is_array($data) ? Pole::filtrujKluce($data, $kluce) : $data;
    }

    /**
     ** Uloži Kontroler. Pokiaľ je id false, Vloží nový, inak vykona editáciu
     * @param array $kontroler Pole s Kontrolérom
     * @throws ChybaUzivatela
     */
    public function ulozKontroler($kontroler)
    {
        if(!$kontroler[self::KONTROLER_ID])
        {
            unset($kontroler[self::KONTROLER_ID]); // aby prebehol autoinkrement, hodnota musi byť NULL, alebo stĺpec z dopytu musíme vynechať
            try
            {
                Db::vloz(self::KONTROLER_TABULKA, $kontroler);
                return 'Kontrolér bol úspešne uložený.';
            }
            catch (PDOException $ex)
            {
                throw new ChybaUzivatela('Kontrolér s touto URL adresov už existuje');
            }
        }
        else
        {
            Db::zmen(self::KONTROLER_TABULKA, $kontroler, 'WHERE kontroler_id = ?', array($kontroler[self::KONTROLER_ID]));
            return 'Kontrolér bol aktuálizovaný.';
        }
    }

    /**
     ** Vráti zoznam kontrolérov v db
     * @return mixed Zoznam kontrolérov
     */
    public function vratKontrolery()
    {
        return Db::dopytVsetkyRiadky('SELECT kontroler_id, titulok, url, popisok, kontroler
                                      FROM kontroler ORDER BY kontroler_id DESC ');
    }

    /**
     ** Odstráni kontrolér
     * @param string $url URL kontoléru
     */
    public function odstranKontroler($url)
    {
        Db::dopyt('DELETE FROM kontroler WHERE url = ?', array($url));
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