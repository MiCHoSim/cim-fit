<?php


namespace App\ZakladModul\System\Kontroler;

use App\AdministraciaModul\Uzivatel\Kontroler\PrihlasenieKontroler;
use App\AdministraciaModul\Uzivatel\Model\UzivatelManazer;
use App\ClanokModul\Kontroler\UvodKontroler;
use App\ClanokModul\Model\ClanokManazer;
use App\RezervaciaModul\Model\PermanentkaManazer;
use App\RezervaciaModul\Model\PermanentkaTypManazer;
use App\ZakladModul\Kontroler\CookiesKontroler;
use App\ZakladModul\Kontroler\KontaktKontroler;
use App\ZakladModul\Kontroler\MenuKontroler;
use App\ZakladModul\Kontroler\UpozornenieKontroler;
use App\ZakladModul\Model\CookiesManazer;
use App\ZakladModul\SpravaPoziadaviek\Kontroler\SpravaPoziadaviekKontroler;
use App\ZakladModul\SpravaPoziadaviek\Model\SpravaPoziadaviekManazer;
use Micho\upravaApp;
use micho\Utility\Retazec;
use Nastavenia;

/**
 ** Prvotný kontrolér->Smerovač, na ktorý sa uživateľ dostane po zadaní URL adresy
 * Class SmerovacKontroler
 * @package App\ZakladModul\System\Kontroler
 */
class SmerovacKontroler extends Kontroler
{
    /**
     ** Naparsovanie URL adresy a vytvorenie prislušného kontroléru
     * @param array $parametre Pod indexom 0 sa očakáva URL adresa na spracovanie -> Kontrolér
     */
    public function index($parametre)
    {
        //$up = new upravaApp();

        $parsovanaUrl = $this->parsujUrl($parametre[0]);
        $uzivatelManazer = new UzivatelManazer();
        $uzivatelManazer->nacitajUzivatela();

        if (empty($parsovanaUrl[0]))
            $parsovanaUrl[0] = 'uvod';
        if ($parsovanaUrl[0] == 'api') // spracovávame požiadavky na "api"
        {
            array_shift($parsovanaUrl); // odstráni prvý parameter "api"
            $this->spracujApiZiadost($parsovanaUrl);
        }
        else // spracovávame požiadavky na stránku... celý layout
            $this->spracujZiadostOKontroler($parsovanaUrl);
    }

    /**
     ** Spracuje žiadosť o kontrolér
     * @param array $parametre Pole parametrov z URL
     */
    private function spracujZiadostOKontroler($parametre)
    {
        // Volanie kontroléra, ktorý volá jednotlive podstránky
        $this->kontroler = new SpravaPoziadaviekKontroler();
        $this->kontroler->index($parametre);

        // Rozlišnie munu pred proihlasenim a po prihlaseni
        $prihlasenieKontroler = new PrihlasenieKontroler();
        $uzivatel = UzivatelManazer::$uzivatel;
        if (!$uzivatel)//ak je uzivateľ prihlaseni zobrazujem ine veci ako Ked nieje
        {
            $this->data['prihlaseny'] = false;
            $prihlasenieKontroler->neprihlasenyMenu();
            // Volanie kontroléra, ktorý spracuje výpis Rychle info o stránke Pred registrácoiu/Mimo prihlasenia
            $uvodKontroler = new UvodKontroler();
            $uvodKontroler->uvodInfo(CookiesManazer::UVOD_INFO);
            $this->data['uvodInfoKontroler'] = $uvodKontroler;
        }
        else
        {
            $this->data['prihlaseny'] = true;
            $prihlasenieKontroler->prihlasenyMenu();

            // Volanie kontroléra, ktorý spracuje výpis Rychle info o stránke Po registrácií a prihlásení
            $uvodKontroler = new UvodKontroler();
            $uvodKontroler->uvodInfo(CookiesManazer::UVOD_INFO_INSTRUKCIE);
            $this->data['uvodInfoKontroler'] = $uvodKontroler;

            $prepadnutiePermanentka = new UpozornenieKontroler();
            $prepadnutiePermanentka->prepadnutiePermanentka($uzivatel[UzivatelManazer::UZIVATEL_ID]);
            $this->data['prepadnutiePermanentka'] = $prepadnutiePermanentka;

        }

        $this->data['prihlasenie_menu'] = $prihlasenieKontroler;

        // Volanie kontroléra, ktorý spracuváva Kontakt a taktieŽ kontaktný formulár
        $kontaktKontroler = new KontaktKontroler();
        $kontaktKontroler->index();
        $this->data['kontaktKontroler'] = $kontaktKontroler;

        // Volanie kontroléra ktorý spracuje výpis Menu stránky
        $menuKontroler = new MenuKontroler();
        $menuKontroler->index($parametre[0]);
        $this->data['menuKontroler'] = $menuKontroler;

        // Volanie kontroléra, ktorý spracuje výpis Cookies stránky
        $cookiesKontroler = new CookiesKontroler();
        $cookiesKontroler->index(CookiesManazer::COOKIES);
        $this->data['cookiesKontroler'] = $cookiesKontroler;

        $clanokManazer = new ClanokManazer();

        // nastaveni premenných pre šablonu
        $this->data['domena'] = Nastavenia::$domena;
        $this->data['domenaNazov'] = Nastavenia::$domenaNazov;

        $this->data['titulok'] = Retazec::vratPoZnak(SpravaPoziadaviekManazer::$kontroler['titulok'], '<') ? : SpravaPoziadaviekManazer::$kontroler['titulok'];
        $this->data['popisok'] = SpravaPoziadaviekManazer::$kontroler['popisok'] . '. ' . Nastavenia::$domenaNazov . ', ' . Nastavenia::$domena . ', ' . Nastavenia::$vyhladavac;

        $this->data['autor'] = isset(SpravaPoziadaviekManazer::$kontroler['autor']) ? SpravaPoziadaviekManazer::$kontroler['autor'] : 'web: ' . Nastavenia::$autorWebu;
        $this->data['spravy'] = $this->vratSpravy();

        if(UzivatelManazer::$uzivatel) // informacny panel pre prihlaseneho uzivatela
            $clankyUrl = array('ochrana-osobnych-udajov', 'cookies', 'o-nas', 'gym-ceny', 'bobby-ceny', 'uvodne-info', 'instrukcie', 'pravidla-gymu', 'standalone-aplikacia');
        else
            $clankyUrl = array('ochrana-osobnych-udajov', 'cookies', 'o-nas', 'gym-ceny-neprihlaseny', 'bobby-ceny-neprihlaseny', 'uvodne-info', 'pravidla-gymu', 'standalone-aplikacia');

        $this->data['informacie'] = $clanokManazer->vratClankyUrl($clankyUrl,true);

        $this->pohlad = 'rozlozenie'; // nastavenie hlavného pohladu/šablony
    }


    /**
     ** Spracuje žiadosť na API
     * @param array $parametre Pole paremetrov z URL
     */
    private function spracujApiZiadost($parametre)
    {
        $polozky = explode('-', array_shift($parametre)); //rozbitie mennych priestorov podľa "-"
        array_splice($polozky, count($polozky) - 1, 0, 'Kontroler'); // pridanie "Kontroler"

        $kontrolerCesta = 'App\\' . implode('\\', $polozky);
        $kontrolerCesta .= 'Kontroler';

        if (preg_match('/^[a-zA-Z0-9\\\\]*$/u', $kontrolerCesta))// bezpečnostná kontrola cesty
        {
            $kontroler = new $kontrolerCesta(true);
            $kontroler->zavolajAkciuZParametrov($parametre, true);
            $kontroler->vypisPohlad();
        }
        else
            $this->presmeruj('chyba');
    }

    /**
     ** Naparsuje URL adresu podľa lomitok a vráti pole parametrov
     * @param string $url URL adresa
     * @return array Naparsovana URL adresa
     */
    private function parsujUrl($url)
    {
        $parsovanaUrl = parse_url($url); // naparsuje jednotlive časti URL adresy do asociativného pola

        $parsovanaUrl['path'] = ltrim($parsovanaUrl['path'], '/'); // odstráni začiatočné lomítko

        self::$aktualnaUrl = $parsovanaUrl['path'] = trim($parsovanaUrl['path']); // odstránenie bielich znakov okolo adresy

        $rozdelenaCesta = explode('/', $parsovanaUrl['path']); // rozbitie reťazca podľa lomítok

        return $rozdelenaCesta;
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
