<?php

namespace App\ZakladModul\System\Kontroler;

use App\AdministraciaModul\Uzivatel\Model\TrenerManazer;
use App\AdministraciaModul\Uzivatel\Model\UzivatelManazer;
use Micho\Utility\Retazec;
use Nastavenia;

use Exception;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

/**
 ** Predok pre kontroléry v aplikácii
 * Class Kontroler
 * @package App\ZakladModul\System\Kontroler
 */
abstract class Kontroler
{
    /**
     ** Možnosti typov Správ
     */
    const SPR_INFO = 'info'; // nazvy sa zhodujú s triedami v Bootstrape
    const SPR_USPECH = 'uspech';
    const SPR_CHYBA = 'chyba';

    public static $aktualnaUrl = ' ';
    /**
     * @var bool či bol kontroler vytvorený API miesto člankom
     */
    protected $vytvorilApi;

    /**
     * @var array Pole, ktorého indexi sú viditeľné v šablone ako bežné premenné
     */
    protected $data = array();

    /**
     * @var string Názov šablony
     * Pokiaľ sa pohľad nachadzá v inej Časti treba zadať jeho cestu
     */
    protected $pohlad = '';

    /**
     * @var Kontroler Instancia kontroléru
     */
    protected $kontroler;

    /**
     ** Inicializuje instanciu
     * Kontroler constructor.
     * @param false $vytvorilApi Ci bol kontroler vytvoreny API miesto  Člankom
     */
    public function __construct($vytvorilApi = false)
    {
        $this->vytvorilApi = $vytvorilApi;
    }

    /**
     ** Ošetri premennú pre výpis do HTML stránky
     * @param null $x Premenná na ošetrenie
     * @return array|mixed|string|null Ošetrená premenná
     */
    public function osetri($x = null)
    {
        if (!isset($x))  // aj nieje inicializované vrátime null
            return null;
        elseif (is_string($x))  //ak je reťazec
            return htmlspecialchars($x, ENT_QUOTES); // Ochrana proti => "XSS"
        elseif (is_array($x))   //ak pole
        {
            foreach($x as $k => $v) //Rekurzívne sa ošetria všetky položky poľa
            {
                $x[$k] = $this->osetri($v); //
            }
            return $x;
        }
        else
            return $x;
    }

    /**
     ** Vypiše pohľad / pokiaľ sa pohľad nachadzá v inej Časti treba zadať jeho cestu
     * Načíta pohľad podľa zostavenej cesty
     * @throws ReflectionException
     */
    public function vypisPohlad()
    {
        if ($this->pohlad)
        {
            extract($this->osetri($this->data));
            extract($this->data, EXTR_PREFIX_ALL, '');  //extraktujeme + prida sa prefix _

            if (mb_strpos($this->pohlad, '/') === FALSE) // Ak nieje zadaná cesta k pohľadu tak ju zostavim
            {
                // Nemôžeme použiť funkciu pre zistenie namespace pretože by vrátila ten z abstraktného kontroléra
                $reflect = new ReflectionClass(get_class($this));

                $cesta = str_replace('Kontroler', 'Pohlad', str_replace('\\', '/', $reflect->getNamespaceName()));

                $kontrolerMeno = str_replace('Kontroler', '', $reflect->getShortName());

                //zostavenie celej cesty k pohľadu, je potrebné previest aj App na app
                $cesta = '../a' . ltrim($cesta, 'A') . '/' . $kontrolerMeno . '/' . $this->pohlad . '.phtml';
            }
            else
                $cesta = '../app/' .  $this->pohlad  . '.phtml';

            //echo "<p style='background: blue'>" . get_class($this) . "<p>";
            require($cesta);
        }
    }

    /**
     ** Pridá správu do SESSION
     * @param string $obsah Obsah správy
     * @param string $typ Typ spravy
     */
    public function pridajSpravu($obsah, $typ = self::SPR_INFO)   //ukladanie správ
    {
        if(is_array($obsah)) // ak je zadane hodnota obsahu pole tak rekurzivne prejdem polozky a pridam ich ako spravy ... tu chodia správy z validatora
        {
            foreach ($obsah as $ob)
            {
                $this->pridajSpravu($ob, $typ);
            }
            return;
        }
        $sprava = array('obsah' => $obsah, 'typ' => $typ);

        if (isset($_SESSION['spravy'])) //zisti či je vytvorené superglobalne pole ak nie tak ho vytvorí
            $_SESSION['spravy'][] = $sprava;
        else
            $_SESSION['spravy'] = array($sprava);
    }

    /**
     ** Vráti správu pre uživateľa
     * @return array|mixed Správy pre uživateľov
     */
    public function vratSpravy() //vratenie spravy
    {
        if (isset($_SESSION['spravy']))
        {
            $spravy = $_SESSION['spravy'];
            unset($_SESSION['spravy']); //vyprazdnenie
            return $spravy;
        }
        else
            return array(); //prázdne pole
    }

    /**
     ** Presmeruje na dané URL
     * @param string $url Url na ktorú chcem presmerovať
     */
    public function presmeruj($url = '')
    {
        if (!$url)
            $url = self::$aktualnaUrl;
        if (isset($_GET['presmeruj']))
            $url = $_GET['presmeruj'];

        header("Location: /$url");
        header('Connection: close');
        exit;
    }

    /**
     ** Overí, či je uživateľ prihlasený a či spĺňa podmienku administrátora/programatora, prípadne presmeruje na Prihlásenie
     * @param false $admin Musí byŤ admin
     * @param false $programator Musí byŤ programator
     */
    public function overUzivatela($admin = false, $programator = false)
    {
        $uzivatel = UzivatelManazer::$uzivatel;

        if(!$uzivatel || ($programator && !$uzivatel[UzivatelManazer::PROGRAMATOR]))
        {
            if(!$uzivatel || ($admin && !$uzivatel[UzivatelManazer::ADMIN]))
            {
                 if (!$this->vytvorilApi)// Pokiaľ bola požiadavka na autentizaciu z článku, presmerujeme na prihlásenie
                 {
                     $this->pridajSpravu('Nie ste prihlásený alebo nemáte dostatočné oprávnenia.', self::SPR_CHYBA);
                     $this->presmeruj(' ');
                 }
                 else //Pokiaľ bola požiadavka z API, vrátime time chybový  kód
                 {
                     header('HTTP/1.1 401 Unauthorized');
                     die('Nedostatočné oprávnenie');
                 }
            }
        }
    }
    /**
     ** Overí, či je uživateľ prihlasený a či spĺňa podmienku Trénera, prípadne presmeruje na Prihlásenie
     */
    public function overTrenera()
    {
        $uzivatel = UzivatelManazer::$uzivatel;
        if(!$uzivatel || (!$uzivatel['trener'] && !$uzivatel[UzivatelManazer::PROGRAMATOR]))
        {
            if (!$this->vytvorilApi)// Pokiaľ bola požiadavka na autentizaciu z článku, presmerujeme na prihlásenie
            {
                $this->pridajSpravu('Nie ste prihlásený alebo nemáte dostatočné oprávnenia.', self::SPR_CHYBA);
                $this->presmeruj(' ');
            }
            else //Pokiaľ bola požiadavka z API, vrátime time chybový  kód
            {
                header('HTTP/1.1 401 Unauthorized');
                die('Nedostatočné oprávnenie');
            }
        }
    }

    /**
     ** Spusti akciu kontoléra podľa parametrov z URL adresy
     * @param array $parametre Parametre z URL adresy: prvý  je nazov akcie, pokiaľ nie je uvedený, predpokladá sa s akcia index()
     * @param bool $api Či chcem renderovať ako API, teda bez layoutu
     * @throws ReflectionException
     */
    public function zavolajAkciuZParametrov($parametre, $api = false)
    {
        $akcia = Retazec::pomlckyNaCamel($parametre ? array_shift($parametre) : 'index'); // volanie konkrétnej metódy v triede
        try //získanie informácii o metóde
        {
            $metoda = new ReflectionMethod(get_class($this), $akcia);
        }
        catch (ReflectionException $exception)
        {
            $this->vyvolajVynimkuSmerovania("Neplatná akcia - $akcia");
        }
        $phpDok = $metoda->getDocComment();// Kontorlá  práv pristupu pomocov PhpDoc
        $anotacia = $api ? '@ApiAction' : '@Action';
        if (mb_strpos($phpDok, $anotacia) === FALSE) // zitenie či sa môže metóda zavolať
            $this->vyvolajVynimkuSmerovania("Neplatná akcia - $akcia");

        $nacitajPocetParametrov = $metoda->getNumberOfRequiredParameters();
        if (count($parametre) < $nacitajPocetParametrov) // zistenie či sme danej metóde zadali potrebný počet parametrov
            $this->vyvolajVynimkuSmerovania("Akcii neboli predané potrebné parametre. Potrebný počet: $nacitajPocetParametrov");
        $metoda->invokeArgs($this, $parametre); // zavolaníe konkrétnej metódy v Kontroléry
    }

    /**
     ** V ladiacom móde vyvolá výnimku, inak poresmeruje na 404 chybovu stránku
     * @param string $sprava Správa, ktorá sa ma zobraziť
     * @throws Exception
     */
    private function vyvolajVynimkuSmerovania($sprava)
    {
        if (Nastavenia::$ladit)
            throw new Exception($sprava);
        else
            $this->presmeruj('chyba');
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
