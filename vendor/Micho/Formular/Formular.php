<?php

namespace Micho\Formular;

use App\AdministraciaModul\Uzivatel\Model\KrajinaManazer;
use App\AdministraciaModul\Uzivatel\Model\OsobaDetailManazer;
use App\AdministraciaModul\Uzivatel\Model\OsobaManazer;
use App\AdministraciaModul\Uzivatel\Model\UzivatelManazer;
use App\ClanokModul\Model\ClanokManazer;
use Micho\Antispam\AntispamRok;
use Micho\ChybaOchrany;
use Micho\ChybaUzivatela;
use Micho\ChybaValidacie;
use Micho\Formular\Validator;
use micho\Utility\Retazec;

/**
 ** Trieda Formulár služi na renderovanie jednotlivých kontroliek Formuláru
 ** Kontrolky z určitími názvami sa generujú podlašabloný, ine možeme pridavať samostatne
 * Class Formular
 * @package Micho\Formular
 */
class Formular
{
    /**
     * Typy Formulárov
     */
    const TYP_REGISTRACIA = 'registracia';
    const TYP_ZABUDNUTE_HESLO = 'zabudnuteHeslo';
    const TYP_ZMENA_HESLA = 'zmenaHesla';

    /**
     * Zoznam Názvov Kontrolie, ktoré je možné generovaŤ automaticky
     */
    const MENO = 'meno';
    const PRIEZVISKO = 'priezvisko';
    const EMAIL = 'email';
    const TEL= 'tel';
    const SPRAVA = 'sprava';
    const ANTISPAM = 'antispam';
    const SUHLAS = 'suhlas';
    const ULICA = 'ulica';
    const SUPISNE_CISLO = 'supisne_cislo';
    const MESTO = 'mesto';
    const PSC ='psc';
    const KRAJINA_ID = 'krajina_id';
    const HESLO = 'heslo';
    const POHLAVIE = 'pohlavie';

    private $InputTrieda;
    private $InputTriedaLabel;
    private $CheckRadioTrieda;
    private $CheckRadioTriedaLabel;
    private $FileTrieda;
    private $FileTriedaLabel;

    /**
     * @var string
     */
    public $formularId; // ID / nazov formulára kvoli generovaniu kontroliek mimo formulára ale taktiež kvoli ochrane => "CSRF" pri generovani tokenu konkretnému formuláru

    /**
     * @var array Pole kontroliek Fromulára
     */
    private $kontrolky = array();
    private $odoslany = false;

    /**
     ** Konštuktor v ktorom automatický vygenerujem vŠetky kontrolky ktorých šablony mám uložené
     * Formular constructor.
     * @param string $formularId ID / nazov formulára
     * @param array $kontrolky Pole názvov Kontroliek Zo zoznamu kontorliek koré sa generujú automaticky, pretože sa Často popužívaju
     * @param string $InputTrieda Nastavenie Triedy pre Input
     * @param string $InputTriedaLabel Nastavenie Triedy pre Input Label
     * @param string $CheckRadioTrieda Nastavenie Triedy pre CheckRadio Label
     * @param string $CheckRadioTriedaLabel Nastavenie Triedy pre CheckRadio Label
     */
    public function __construct($formularId, $kontrolky = array(), $InputTrieda = 'form-control', $InputTriedaLabel = 'sr-only', $CheckRadioTrieda = 'form-check-input', $CheckRadioTriedaLabel = 'form-check-label', $FileTrieda = 'form-control-file', $FileTriedaLabel = '')
    {
        $this->formularId = $formularId;
        $this->InputTrieda = $InputTrieda;
        $this->InputTriedaLabel = $InputTriedaLabel;
        $this->CheckRadioTrieda = $CheckRadioTrieda;
        $this->CheckRadioTriedaLabel = $CheckRadioTriedaLabel;
        $this->FileTrieda = $FileTrieda;
        $this->FileTriedaLabel = $FileTriedaLabel;

        $this->nastavCSRF(); // vytvory kontrolku => ochrana proty útoku => "CSRF"

        foreach ($kontrolky as $nazovKontrolka)
        {
            $nazovMetody = 'nastav' . Retazec::podciarkovnikNaCamel($nazovKontrolka,false);

            if(method_exists($this, $nazovMetody))
                $this->$nazovMetody(); //zavola metodú kontrolky, pomocov ktorej sa vytvorý kontrolka
        }
    }

    /**
     * @return bool Či bolo stlačené tlačidlo na odoslanie formulára
     */
    public function odoslany()
    {
        return $this->odoslany;
    }

    public function nastavInputTrieda($trieda)
    {
        $this->InputTrieda = $trieda;
    }
    public function nastavInputTriedaLabel($trieda)
    {
        $this->InputTriedaLabel = $trieda;
    }
    public function nastavCheckRadioTrieda($trieda)
    {
        $this->CheckRadioTrieda = $trieda;
    }
    public function nastavCheckRadioTriedaLabel($trieda)
    {
        $this->CheckRadioTriedaLabel = $trieda;
    }


    //----------------------------------- Predpripravene kontrolky -----------------------------------
    /**
     * Objekt Kontrolky - Input csrf  => ochrana proti útoku => "CSRF"
     */
    private function nastavCSRF()
    {
        if (empty($_SESSION['token'][$this->formularId]))
        {
            $_SESSION['token'][$this->formularId] = bin2hex(random_bytes(32)); // Generovanie autorizačného tokenu pre dany Formulár/Tlačidlo
        }

        $this->pridajInput('CSRF', 'csrf', Input::TYP_HIDDEN, $_SESSION['token'][$this->formularId]);
    }

    /**
     * Objekt Kontrolky - Input meno
     */
    private function nastavMeno()
    {
        $this->pridajInput('Meno', self::MENO, Input::TYP_TEXT,'', '', $this->InputTrieda, $this->InputTriedaLabel,true, Validator::PATTERN_RETAZEC);
    }
    /**
     * Objekt Kontrolky - Input priezvisko
     */
    private function nastavPriezvisko()
    {
        $this->pridajInput('Priezvisko', self::PRIEZVISKO, Input::TYP_TEXT,'', '', $this->InputTrieda, $this->InputTriedaLabel,true, Validator::PATTERN_RETAZEC);
    }
    /**
     * Objekt Kontrolky - Input email
     */
    private function nastavEmail()
    {
        $this->pridajInput('Email',self::EMAIL, Input::TYP_EMAIL,'', '', $this->InputTrieda, $this->InputTriedaLabel,true, Validator::PATTERN_EMAIL);
    }
    /**
     * Objekt Kontrolky - Input tel
     */
    private function nastavTel()
    {
        $this->pridajInput('Telefón', self::TEL, Input::TYP_TEL,'', '', $this->InputTrieda, $this->InputTriedaLabel,true, Validator::PATTERN_TEL);
    }
    /**
     * Objekt Kontrolky - Input ulica
     */
    private function nastavUlica()
    {
        $this->pridajInput('Ulica', self::ULICA, Input::TYP_TEXT,'', '', $this->InputTrieda, $this->InputTriedaLabel,true, Validator::PATTERN_RETAZEC);
    }
    /**
     * Objekt Kontrolky - Input supisne_cislo
     */
    private function nastavSupisneCislo()
    {
        $this->pridajInput('Súpisné číslo', self::SUPISNE_CISLO, Input::TYP_TEXT,'', '', $this->InputTrieda, $this->InputTriedaLabel,true, Validator::PATTERN_SUPIS_CISLO);
    }
    /**
     * Objekt Kontrolky - Input mesto
     */
    private function nastavMesto()
    {
        $this->pridajInput('Mesto', self::MESTO, Input::TYP_TEXT,'', '', $this->InputTrieda, $this->InputTriedaLabel,true,Validator::PATTERN_RETAZEC);
    }
    /**
     * Objekt Kontrolky - Input psc
     */
    private function nastavPsc()
    {
        $this->pridajInput('PSČ', self::PSC, Input::TYP_TEXT,'', '', $this->InputTrieda, $this->InputTriedaLabel,true, Validator::PATTERN_PSC);
    }
    /**
     * Objekt Kontrolky - Input heslo
     */
    private function nastavHeslo()
    {
        $this->pridajInput('Heslo', self::HESLO, Input::TYP_PASSWORD,'', '', $this->InputTrieda, $this->InputTriedaLabel,true, Validator::PATTERN_HESLO);
    }
    /**
     * Objekt Kontrolky - Radio pohlavie
     */
    private function nastavPohlavie()
    {
        $this->pridajRadio(array('Muž' => 'muz', 'Žena' => 'zena'), self::POHLAVIE);
    }
    /**
     * @return Select Objekt Kontrolky - Select Krajina
     */
    private function nastavKrajinaId()
    {
        $this->pridajSelect('Krajina', KrajinaManazer::KRAJINA_ID, array(), '', false,$this->InputTrieda,$this->InputTriedaLabel, true);
    }
    /**
     * @return TextArea Objekt Kontrolky - TextArea sprava
     */
    private function nastavSprava()
    {
        $this->pridajTextArea('Správa', self::SPRAVA, 'Tu napíšte svoje otázky a požiadavky', '', '',3, $this->InputTrieda, $this->InputTriedaLabel, true);
    }
    /**
     * Objekt Kontrolky - Input antispam
     */
    private function nastavAntispam()
    {
        $this->pridajInput('Aktuálny rok (AntiSpam)', self::ANTISPAM, Input::TYP_TEXT,'', '', $this->InputTrieda, $this->InputTriedaLabel,true, Validator::PATTERN_ANTISPAM_ROK);
    }
    /**
     * @return CheckBox Objekt Kontrolky - CheckBox suhlas
     */
    private function nastavSuhlas()
    {
        $clanokManazer = new ClanokManazer();
        $spracovanieOsUD = $clanokManazer->vratClanok('ochrana-osobnych-udajov', array(ClanokManazer::TITULOK, ClanokManazer::POPISOK, ClanokManazer::URL));
        $this->pridajCheckBox('súhlasím s: <a target="_blank" href="clanok/' . $spracovanieOsUD[ClanokManazer::URL] . '">' . $spracovanieOsUD[ClanokManazer::TITULOK] . '</a>', self::SUHLAS, 1,false, $spracovanieOsUD[ClanokManazer::POPISOK],'', $this->CheckRadioTrieda, $this->CheckRadioTriedaLabel, true);
    }

    //----------------------------------- Ine kontrolky -----------------------------------

    /**
     ** Vytvorí Novú kontrolku Input
     * @param string $nazov label & placeholder - Názov pre kontrolku
     * @param string $nazovDb name & id - Kontrolky, popripade názov ako je ulozená hodnota v DB
     * @param string $typ type - O aky typ kontrolky sa jedna text/number/email/tel,...
     * @param string $hodnota value - Hodnota ktorú ma kontrolka vyplnenú
     * @param string $formular form - V prípade ze je kontrolka mimo formulára treba udať ku ktorému formuláru patrý
     * @param string $trieda class - Trieda kontrolky
     * @param string $triedaLabel class - pre Label
     * @param bool $pozadovany required - či musí byť kontrolka vyplnená
     * @param array $vzor pattern - Patern/Vzor/Pravidlo pre kontrolku array(popis,pattern)
     * @param false $zablakovany Či sa dá meniť hodnota prvku
     * @param string $atributy Všetky ostatné atributy ktore chem naviše
     */
    public function pridajInput($nazov, $nazovDb, $typ , $hodnota = '', $formular = '', $trieda = '', $triedaLabel = '', $pozadovany = true, $vzor = false, $zablakovany = false, $atributy = false)
    {
        $trieda = empty($trieda) ? $this->InputTrieda : $trieda;
        $triedaLabel = empty($triedaLabel) ? $this->InputTriedaLabel : $triedaLabel;
        $this->kontrolky[$nazovDb] = new Input($nazov, $nazovDb, $typ , $hodnota, $formular, $trieda, $triedaLabel, $pozadovany, $vzor, $zablakovany, $atributy);
    }

    /**
     * @param string $nazov label & placeholder - Názov pre kontrolku
     * @param string $nazovDb name & id - Kontrolky, popripade názov ako je ulozená hodnota v DB
     * @param array $moznosti Pole možnosti kde klúč je názov a hodnota je Hodnota value
     * @param string $hodnota Hodnota práve vybratého SELECTU
     * @param bool $viacnasobny multiple - či je povoľený výber viacerých súborov
     * @param string $formular form - V prípade ze je kontrolka mimo formulára treba udať ku ktorému formuláru patrý
     * @param string $trieda class - Trieda kontrolky
     * @param string $triedaLabel class - pre Label
     * @param bool $pozadovany required - či musí byť kontrolka vyplnená
     * @param string $atributy Všetky ostatné atributy ktore chem naviše
     */
    public function pridajSelect($nazov, $nazovDb, array $moznosti , $hodnota = '', $viacnasobny = false, $formular = '', $trieda = '', $triedaLabel = '', $pozadovany = true, $atributy = false)
    {
        $trieda = empty($trieda) ? $this->InputTrieda : $trieda;
        $triedaLabel = empty($triedaLabel) ? $this->InputTriedaLabel : $triedaLabel;
        $this->kontrolky[$nazovDb] = new Select($nazov, $nazovDb, $moznosti, $hodnota, $viacnasobny, $formular, $trieda, $triedaLabel, $pozadovany, $atributy);
    }

    /**
     ** Vytvorí Novú kontrolku CheckBox
     * @param string $nazov label & placeholder - Názov pre kontrolku
     * @param string $nazovDb name & id - Kontrolky, popripade názov ako je ulozená hodnota v DB
     * @param false $zaskrtnuty checked - Či ma byť zaškrtnutý
     * @param string $popisok title - Popisok pre kontrolku
     * @param string $formular form - V prípade ze je kontrolka mimo formulára treba udať ku ktorému formuláru patrý
     * @param string $trieda class - Trieda kontrolky
     * @param string $triedaLabel class - pre Label
     * @param bool $pozadovany required - či musí byť kontrolka vyplnená
     * @param string $atributy Všetky ostatné atributy ktore chem naviše
     */
    public function pridajCheckBox($nazov, $nazovDb, $hodnota, $zaskrtnuty = false, $popisok = '', $formular = '', $trieda = '', $triedaLabel = '', $pozadovany = true, $atributy = false)
    {
        $trieda = empty($trieda) ? $this->CheckRadioTrieda : $trieda;
        $triedaLabel = empty($triedaLabel) ? $this->CheckRadioTriedaLabel : $triedaLabel;
        $this->kontrolky[$nazovDb] = new CheckBox($nazov, $nazovDb, $hodnota, $zaskrtnuty, $popisok, $formular, $trieda, $triedaLabel, $pozadovany, $atributy);
    }
    /**
     * Vytvorý novú kontrolku radio Buttonov
     * @param array $nazovHodnota Pole kde klúče su názvy Labelov a hodnoty Sú ich Hodnoty array (Label => value)
     * @param string $nazovDb $nazovDb name & id - Kontrolky, popripade názov ako je ulozená hodnota v DB
     * @param false $zaskrtnuty checked - Či ma byť zaškrtnutý
     * @param string $popisok title - Popisok pre kontrolku
     * @param string $formular form - V prípade ze je kontrolka mimo formulára treba udať ku ktorému formuláru patrý
     * @param string $trieda class - Trieda kontrolky
     * @param string $triedaLabel class - pre Label
     * @param bool $pozadovany required - či musí byť kontrolka vyplnená
     * @param string $atributy Všetky ostatné atributy ktore chem naviše
     */
    public function pridajRadio(array $nazovHodnota, $nazovDb, $zaskrtnuty = false, $popisok  = '', $formular = '', $trieda = '', $triedaLabel = '', $pozadovany = true, $atributy = false)
    {
        $trieda = empty($trieda) ? $this->CheckRadioTrieda : $trieda;
        $triedaLabel = empty($triedaLabel) ? $this->CheckRadioTriedaLabel : $triedaLabel;
        foreach ($nazovHodnota as $nazov => $hodnota)
        {
            $this->kontrolky[$nazovDb][$hodnota] = new Radio($nazov, $nazovDb, $hodnota, $zaskrtnuty, $popisok, $formular, $trieda, $triedaLabel, $pozadovany, $atributy);
        }
    }
    /**
     ** Vytvorí Novú kontrolku textArea
     * @param string $nazov label - Názov pre kontrolku
     * @param string $nazovDb name & id - Kontrolky, popripade názov ako je ulozená hodnota v DB
     * @param string $placeholder placeholder - Placeholder pre Kontrolku
     * @param string $hodnota value - Hodnota ktorú ma kontrolka vyplnenú
     * @param string $formular form - V prípade ze je kontrolka mimo formulára treba udať ku ktorému formuláru patrý
     * @param int $riadky rows - Počet riadkov Kontrolky
     * @param string $trieda class - Trieda kontrolky
     * @param string $triedaLabel class - pre Label
     * @param bool $pozadovany required - či musí byť kontrolka vyplnená
     * @param string $atributy Všetky ostatné atributy ktore chem naviše
     */
    public function pridajTextArea($nazov, $nazovDb, $placeholder = '' , $hodnota = '', $formular = '', $riadky = 3, $trieda = '', $triedaLabel = '', $pozadovany = true, $atributy = false)
    {
        $trieda = empty($trieda) ? $this->InputTrieda : $trieda;
        $triedaLabel = empty($triedaLabel) ? $this->InputTriedaLabel : $triedaLabel;
        $this->kontrolky[$nazovDb] = new TextArea($nazov, $nazovDb, $placeholder, $hodnota, $formular, $riadky, $trieda, $triedaLabel, $pozadovany, $atributy);
    }

    /**
     ** Vytvorí Novú kontrolku File
     * @param string $nazov label & placeholder - Názov pre kontrolku
     * @param string $nazovDb name & id - Kontrolky, popripade názov ako je ulozená hodnota v DB
     * @param string $formular form - V prípade ze je kontrolka mimo formulára treba udať ku ktorému formuláru patrý
     * @param string $trieda class - Trieda kontrolky
     * @param string $triedaLabel class - pre Label
     * @param bool $pozadovany required - či musí byť kontrolka vyplnená
     * @param bool $viacnasobny multiple - či je povoľený výber viacerých súborov
     * @param string $akceptovane accept - Povoľené formáty z možnosti Konštant
     * @param string $atributy Všetky ostatné atributy ktore chem naviše
     */
    public function pridajFile($nazov, $nazovDb, $formular = '', $trieda = '', $triedaLabel = '', $pozadovany = true, $viacnasobny = false, $akceptovane = false, $atributy = false)
    {
        $trieda = empty($trieda) ? $this->FileTrieda : $trieda;
        $triedaLabel = empty($triedaLabel) ? $this->FileTriedaLabel : $triedaLabel;
        $this->kontrolky[$nazovDb] = new File($nazov, $nazovDb, $formular, $trieda, $triedaLabel, $pozadovany, $viacnasobny, $akceptovane, $atributy);
    }

    /**
     ** Vytvorí Nové Odosielacie Tlačidlo
     * @param string $hodnota value - Hodnota ktorú ma kontrolka vyplnenú -> Názov Tlačidla
     * @param string $nazovDb name & id - Kontrolky, popripade názov ako je ulozená hodnota v DB
     * @param string $formular form - V prípade ze je kontrolka mimo formulára treba udať ku ktorému formuláru patrý
     * @param string $trieda class - Trieda kontrolky
     * @param string $atributy Všetky ostatné atributy ktore chem naviše
     */
    public function pridajSubmit($hodnota, $nazovDb, $formular = '', $trieda = 'btn btn-lg btn-outline-danger btn-block', $atributy = false)
    {
        $this->kontrolky[$nazovDb] = new Submit($hodnota, $nazovDb, $formular, $trieda, $atributy);

        if ($_POST && isset($_POST[$nazovDb]))
        {
            $this->odoslany = true;
        }
    }

    //----------------------------------- Vrátenie a nastevenie kontroliek kontrolkky -----------------------------------

    /**
     * @return array Vráti pole HTML všetkých kontroliek formulára
     */
    public function vratFormular()
    {
        $formular['formularId'] = $this->formularId;

        foreach (array_keys($this->kontrolky) as $nazov)
        {
            if(is_object($this->kontrolky[$nazov]))
                $formular[$nazov] = $this->kontrolky[$nazov]->vytvorKontrolku();
            else// ak nieje objekt znamená to, že pracujem z Radio ktorý je v kontorlke uložený ako Názov DB a podtým array(moznosti výberu)
            {   // $nazov array(polozky1, polozka2,...)
                foreach (array_keys($this->kontrolky[$nazov]) as $podnazov)
                {
                    $formular[$nazov][$podnazov] = $this->kontrolky[$nazov][$podnazov]->vytvorKontrolku();
                }
            }
        }
        return $formular;
    }

    /**
     ** Upravi parametre kontrolky
     * @param string $kontrolka Názov kontrolky
     * @param array $parametre Nové parametre
     */
    public function upravParametreKontrolky($kontrolka, array $parametre)
    {
        $this->kontrolky[$kontrolka]->upravParametre($parametre);
    }


    /**
     ** Ošetrý a vráti hodnoty získane z $_POST a $_FILE => ochrana proti útoku => "Mass assignment"
     * @param string $subor Názov Kontorlky ktorá obsahuje File
     * @return array ošetrené hodnoty z $_POST a $_FILE
     */
    public function osetriHodnoty($subor = false)
    {
        array_pop($_POST); // Odstránenie údajov tlačidla
        array_shift($_POST); // Odstránenie údajov csrf
        $formularData = array_intersect_key($_POST, $this->kontrolky); // orezanie údajov iba na potrebné hodnoty

        if ($subor)
        {
            $formularData[$subor] = $_FILES[$subor]; // orezanie údajov iba na potrebné hodnoty
        }
        return $formularData;
    }

    /**
     ** Nastavý hodnoty kontrolkám
     * @param array $formularData Pole Hodnôt
     */
    public function nastavHodnotyKontrolkam($formularData)
    {
        foreach ($formularData as $nazov => $hodnota)
        {
            if(isset($this->kontrolky[$nazov]))
            {
                if($this->kontrolky[$nazov] instanceof CheckBox) // Nastaveni Hodnoty, Teda zaškrutnutie políčka
                {
                    if ($hodnota)
                        $this->kontrolky[$nazov]->zaskrtni();
                }
                elseif(is_object($this->kontrolky[$nazov])) //Nastavenie hodnoty pre kontrolky ktorým sa hodnotá zadáva ručne
                    $this->kontrolky[$nazov]->upravParametre(array('hodnota' => $hodnota));

                else // ak nieje objekt znamená to, že pracujem z Radio ktorý je v kontorlke uložený ako Názov DB a podtým array(moznosti výberu)
                    // $nazov array(polozky1, polozka2,...)
                    $this->kontrolky[$nazov][$hodnota]->zaskrtni();
            }
        }
    }

    /**
     ** Zvaliduje údaje od uzivateľa
     * @param array $data Dáta od uzivateľa
     * @param false $typ Typ formulára ktory kontrolujem, co mam prizeraŤ
     * @throws ChybaOchrany
     * @throws ChybaUzivatela
     * @throws ChybaValidacie
     */
    public function validuj($data, $typ = false)
    {
        $spravy = array();
        $osobaManazer = new OsobaManazer();

        if($typ === self::TYP_REGISTRACIA) // zavolanie overenia hodnot pre registráciu
        {
            $spravy = array_merge($spravy, $osobaManazer->overRegistracneHodnoty($data));
        }
        elseif($typ === self::TYP_ZMENA_HESLA) // zavolanie overenia hodnot pre Zmenu hesla
        {
            $uzivatelManazer = new UzivatelManazer();
            $spravy = array_merge($spravy, $uzivatelManazer->overZmenaHeslaHodnoty($data));
        }
        elseif($typ === self::TYP_ZABUDNUTE_HESLO) // zavolanie overenia hodnot pre registráciu
        {
            if (!$osobaManazer->overExistenciuEmailu($data[Formular::EMAIL]))
                $spravy[] = 'Osoba s týmto emailom nieje registovaná';
        }
        foreach ($data as $nazov => $hodnota)
        {
            //radio Button nekontorlujem ak nieje objekt znamená to, že pracujem z Radio ktorý je v kontorlke uložený ako Názov DB a podtým array(moznosti výberu)
            if (is_object($this->kontrolky[$nazov]) && $this->kontrolky[$nazov]->vratParameter(Kontrolky::POZADOVANY)) // overenie či je hodnota vyplnená ak má byť vyplnená
            {
                if (($this->kontrolky[$nazov] instanceof File && empty($data[$nazov]['name'][0])) || (empty($data[$nazov]) && $data[$nazov] === false))
                    throw new ChybaValidacie('Nastali chyby validovania', 0, null, array('Neboli vyplnené všetky požadované hodnoty'));
            }

            if ($nazov === self::ANTISPAM) // overujem zhodu antispamu
            {
                $antispam = new AntispamRok();
                $spravy[] = $antispam->over($data[self::ANTISPAM]);
            }
            elseif (is_object($this->kontrolky[$nazov]) && method_exists($this->kontrolky[$nazov], 'vratVzor')) // validovanie Kontroliek ktore maju pattern
            {
                $vzor = $this->kontrolky[$nazov]->vratVzor();
                if ($vzor && !preg_match('/^' . $vzor . '/',$hodnota))
                    $spravy[] = 'Chybne vyplnená hodnota: ' . $this->kontrolky[$nazov]->vratParameter('nazov');
            }
            elseif ($this->kontrolky[$nazov] instanceof CheckBox) // validovanie Kontolky CheckBox na hodnoty ktoré má uložené v Hodnote
            {
                $hodnotaCheckboxu = $this->kontrolky[$nazov]->vratParameter(Select::HODNOTA);
                if ($data[$nazov] != $hodnotaCheckboxu)
                    throw new ChybaValidacie('Nastali chyby validovania', 0, null, array('Kontrolka: ' . $this->kontrolky[$nazov]->vratParameter('nazov') . ' má nepravdivú hodnotu'));

            }
            elseif ($this->kontrolky[$nazov] instanceof Select) // validovanie Kontolky Select na hodnoty ktoré am v options
            {
                $moznosti = $this->kontrolky[$nazov]->vratParameter(Select::MOZNOSTI);

                if ($this->kontrolky[$nazov]->vratParameter(Select::VIACNASOBNY)) // pre multiple select prechaddam polozku po polozke
                {
                    foreach ($data[$nazov] as $moznost)
                    {
                        if (!in_array($moznost, $moznosti))
                            throw new ChybaValidacie('Nastali chyby validovania', 0, null, array('Vybraná hodnota pre kontrolku: ' . $this->kontrolky[$nazov]->vratParameter('nazov') . ' neexistuje'));
                    }
                }
                elseif (!in_array($data[$nazov], $moznosti))
                    throw new ChybaValidacie('Nastali chyby validovania', 0, null, array('Vybraná hodnota pre kontrolku: ' . $this->kontrolky[$nazov]->vratParameter('nazov') . ' neexistuje'));
            }
            elseif ($this->kontrolky[$nazov] instanceof File && $this->kontrolky[$nazov]->vratParameter(File::AKCEPTOVANE)) // validovanie Kontolky typu file na akceptovaný formát
            {
                $akceptovaneTypPole = explode('/', $this->kontrolky[$nazov]->vratParameter(File::AKCEPTOVANE)); // rozdelenie akceptovania podla lomitka

                if (is_array($data[$nazov]['tmp_name'])) // ak je pole prejdem vsetky polozky  akontoluje mich format
                {
                    foreach ($data[$nazov]['tmp_name'] as $subor)  // prejdenie vŠetkých suborov
                    {
                        $typPole = explode('/', mime_content_type($subor)); // zistenie typu suboru a rozdelenie ho podla lomitka

                        if ($typPole[0] !== $akceptovaneTypPole[0]) // ak sa nezhoduje prvá Časť vyvolám vynimku
                            throw new ChybaValidacie('Nastali chyby validovania', 0, null, array('Vybrané súbory nemajú povolený formát'));

                        if($akceptovaneTypPole[1] !== '*' && $typPole[1] !== $akceptovaneTypPole[1]) // ak sa druháčast nerovna "*" tak porovnávam aj druhu
                            throw new ChybaValidacie('Nastali chyby validovania', 0, null, array('Vybrané súbory nemajú povolený formát'));
                    }
                }
                else // kontrolujem format iba toho jedneho obrazka
                {
                    $typPole = explode('/', mime_content_type($data[$nazov]['tmp_name'])); // zistenie typu suboru a rozdelenie ho podla lomitka

                    if ($typPole[0] !== $akceptovaneTypPole[0]) // ak sa nezhoduje prvá Časť vyvolám vynimku
                        throw new ChybaValidacie('Nastali chyby validovania', 0, null, array('Vybrané súbory nemajú povolený formát'));

                    if($akceptovaneTypPole[1] !== '*' && $typPole[1] !== $akceptovaneTypPole[1]) // ak sa druháčast nerovna "*" tak porovnávam aj druhu
                        throw new ChybaValidacie('Nastali chyby validovania', 0, null, array('Vybrané súbory nemajú povolený formát'));
                }

            }
        }

        $spravy = array_filter($spravy);

        if(!empty($spravy)) // ak nieje pole prázdne tak vyvolam vynimku na vypísanie správ
            throw new ChybaValidacie('Nastali chyby validovania', 0, null, $spravy);
    }

    /**
     ** Vráti Klúče/Názvy Kontorliek
     * @param false $tlacidlo ci chem vrátit ak tlacidlo
     * @return array $klúče
     */
    public function vratKluceKontroliek($tlacidlo = false)
    {

        $kluce = array_keys($this->kontrolky);
        array_shift($kluce); // odstranenie kontrolky csrf
        if (!$tlacidlo)
            array_pop($kluce);
        return $kluce;
    }

    /**
     ** overi ci je splnená podmineika tokenu pre "CSRF"
     * @return bool
     */
    public function overCSRF()
    {
        if(!empty($_POST['csrf'])) // pripomienka pre programatora Že nastava bezpečnostná chyba typu CSRF
        {
            if (hash_equals($_SESSION['token'][$this->formularId], $_POST['csrf'])) // porovnanie tokenov
            {
                return true;
            }
        }
        return false;
    }

}
/*
 * Autor: MiCHo
 */
