<?php

namespace App\ClanokModul\Kontroler;

use App\ClanokModul\Model\ClanokManazer;
use App\ZakladModul\Model\CookiesManazer;
use App\ZakladModul\SpravaPoziadaviek\Model\SpravaPoziadaviekManazer;
use App\ZakladModul\System\Kontroler\Kontroler;
use http\Encoding\Stream\Inflate;
use Nastavenia;

/**
 ** Spracováva stránku pre úvod
 * Class UvodKontroler
 * @package ClanokModul\Uvod\Kontroler
 */
class UvodKontroler extends Kontroler
{
    /**
     ** Spracuje Uvod v Tomto prípade nieje
     * @Action
     */
    public function index()
    {
        $clanokKontroler = new ClanokKontroler();
        $clanokKontroler->index('uvod');
        SpravaPoziadaviekManazer::$kontroler['titulok'] = Nastavenia::$domenaNazov;
        $this->data['uvod'] = $clanokKontroler;

        $this->pohlad = 'index';  //nastavenie pohladu
    }

    /**
     ** Metoda načita uvodne info pre uzivateľa
     * @param string $nazov Názov zobrazovaneho / uloženeho v cookies
     */
    public function uvodInfo($nazov)
    {
        $cookiesManazer = new CookiesManazer();
        $clanokManazer = new ClanokManazer();

        if (!$cookiesManazer->vratUlozeneCookies($nazov) && $clanokManazer->zistiVerejnostClanku($nazov)) // zisti ce je dane cookies ulozene a sucastne ci je verejny clanok
        {
            //if($nazov === CookiesManazer::UVOD_INFO)
            //{
                $clanokManazer = new ClanokManazer();
                $clanok = $clanokManazer->vratClanok($nazov, array(ClanokManazer::TITULOK, ClanokManazer::OBSAH));
            //}

            $this->data['presmeruj'] = self::$aktualnaUrl;
            $this->data['nazov'] = $nazov;
            $this->data['clanok'] = $clanok;

            //$this->pohlad = $nazov;  //nastavenie pohladu
            $this->pohlad = 'uvodne-info';  //nastavenie pohladu
        }
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
