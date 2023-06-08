<?php

namespace App\ZakladModul\Kontroler;

use App\ClanokModul\Model\ClanokManazer;
use App\ZakladModul\Model\CookiesManazer;
use App\ZakladModul\System\Kontroler\Kontroler;
use Nastavenia;

/**
 ** Spracováva Cookies
 * Class CookiesKontroler
 * @package App\ZakladModul\Kontroler\CookiesKontroler
 */
class CookiesKontroler extends Kontroler
{
    /**
     ** Spracuje zobrazenie informácie o CookiesCookies
     * @param string $nazov Názov uloženia Cookies
     * @ Action Action oddelené o @ čim je znefunkčné, kvôli tomu, aby sa nemohlo volať URL
     */
    public function index($nazov)
    {
        $cookiesManazer = new CookiesManazer();

        if (!$cookiesManazer->vratUlozeneCookies($nazov))
        {
            //if($nazov === CookiesManazer::COOKIES)
            //{
                $popisok = $cookiesManazer->vratPopisokCookies();
                $popisok = str_replace('stránke', '<a class="text-white" href="">stránke</a>',$popisok);
                $this->data[ClanokManazer::POPISOK] = $popisok;
            //}

            $this->data['presmeruj'] = self::$aktualnaUrl;
            $this->data['nazov'] = $nazov;

            $this->pohlad = $nazov;  //nastavenie pohladu
        }
    }

    /**
     ** Uloži Do cookies informáciu o zaškrtnutí do cookies prehľiadača
     * @param string $nazov Názov uloženia Cookies
     * @Action
     */
    public function uloz($nazov)
    {
        $cookiesManazer = new CookiesManazer();
        $cookiesManazer->ulozCookies($nazov);
        $this->presmeruj();
    }
}
/*
 * Autor: MiCHo
 */
