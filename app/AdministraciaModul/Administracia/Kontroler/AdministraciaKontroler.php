<?php

namespace App\AdministraciaModul\Administracia\Kontroler;

use App\AdministraciaModul\Administracia\Model\StatistikaManazer;
use App\AdministraciaModul\Uzivatel\Model\KlientManazer;
use App\AdministraciaModul\Uzivatel\Model\OsobaDetailManazer;
use App\AdministraciaModul\Uzivatel\Model\TrenerManazer;
use App\ClanokModul\Model\ClanokManazer;
use App\AdministraciaModul\Administracia\Model\AdministraciaManazer;
use App\AdministraciaModul\Uzivatel\Kontroler\RegistraciaKontroler;
use App\AdministraciaModul\Uzivatel\Model\OsobaManazer;
use App\AdministraciaModul\Uzivatel\Model\UzivatelManazer;
use App\ClanokModul\Model\ClanokTypManazer;
use App\RezervaciaModul\Kontroler\RezervaciaKontroler;
use App\RezervaciaModul\Model\PermanentkaManazer;
use App\RezervaciaModul\Model\PermanentkaTypManazer;
use App\RezervaciaModul\Model\PoznamkaManazer;
use App\RezervaciaModul\Model\RezervaciaManazer;
use App\RezervaciaModul\Model\SkupinaManazer;
use App\SutazModul\Model\SutazManazer;
use App\SutazModul\Model\SutazPrihlasenyManazer;
use App\SutazModul\Model\SutazTypManazer;
use App\ZakladModul\Model\KontaktManazer;
use App\ZakladModul\SpravaPoziadaviek\Model\SpravaPoziadaviekManazer;
use App\ZakladModul\System\Kontroler\Kontroler;
use Micho\Cas;
use Micho\ChybaOchrany;
use Micho\ChybaUzivatela;
use Micho\ChybaValidacie;
use Micho\Db;
use Micho\Formular\Formular;
use Micho\Formular\Input;
use Micho\Formular\TextArea;
use Micho\Obrazok;
use Micho\Subory\Priecinok;
use Micho\Subory\Subor;
use Micho\upravaApp;
use Micho\Utility\DatumCas;
use DateTime;
use Micho\Utility\Pole;
use Micho\Utility\Retazec;

/**
 ** Spracovava prístup do administracnej sekcie
 * Class AdministraciaKontroler
 * @package App\AdministraciaModul\Administracia\Kontroler
 */
class AdministraciaKontroler extends Kontroler
{
    /**
     ** Spracuje požiadavku o zmenu Hesla uživateľa
     * @Action
     */
    public function zmenaHesla()
    {
        $this->overUzivatela();

        $urlNav = explode('/', self::$aktualnaUrl)[1];

        $formular = new Formular('zmena-hesla', array(Formular::HESLO));
        $formular->pridajInput('Staré heslo','stare_heslo',Input::TYP_PASSWORD);
        $formular->pridajInput('Heslo znova','heslo_znova',Input::TYP_PASSWORD);
        $formular->pridajSubmit('Zmeniť', 'zmena-hesla-tlacidlo', '', 'btn btn-outline-primary btn-block my-3');
        if ($formular->odoslany() && $formular->overCSRF())
        {
            $formularData = $formular->osetriHodnoty();
            try
            {
                $formular->validuj($formularData, Formular::TYP_ZMENA_HESLA);
                $uzivatelManazer = new UzivatelManazer();
                $uzivatelManazer->ulozZmenuHesla($formularData[UzivatelManazer::HESLO], UzivatelManazer::$uzivatel[UzivatelManazer::UZIVATEL_ID]);

                $this->pridajSpravu('Heslo bolo zmenené.', self::SPR_USPECH);
                $this->presmeruj('administracia/zmena-hesla');
            }
            catch (ChybaValidacie $chyba) // odchytáva chyby validácie
            {
                $this->pridajSpravu($chyba->vratChyby(), self::SPR_CHYBA);
            }
        }

        SpravaPoziadaviekManazer::$kontroler['titulok'] = 'Zmena Hesla';
        SpravaPoziadaviekManazer::$kontroler['popisok'] = 'Zabudnuté heslo, Zmena hesla, Nové heslo';

        $this->data['menu'] = (new AdministraciaManazer())->zostavMenu($urlNav);
        $this->data['formular'] = $formular->vratFormular();
        $this->pohlad = 'zmena-hesla';
    }

    /**
     ** Spracuje požiadavku na výpis zoznamu Kontrolérov/Článkov
     * @param string $typ Či sa jedná o Kontrolér alebo Článok
     * @Action
     */
    public function zoznam($typ, $typClanku = 'vsetky')
    {
        $urlNav = explode('/', self::$aktualnaUrl);
        $urlNav = $urlNav[1] . '/' . $urlNav[2];
        if ($typ === 'kontroler')
        {
            $this->overUzivatela(false, true);

            $spravaPoziadaviekManazer = new SpravaPoziadaviekManazer();
            $this->data['kontrolery'] = $spravaPoziadaviekManazer->vratKontrolery();

            SpravaPoziadaviekManazer::$kontroler['titulok'] = 'Zoznam Kontrolérov';

            $this->data['menu'] = (new AdministraciaManazer())->zostavMenu($urlNav);
            $this->pohlad = 'zoznam-kontrolerov';
        }
        elseif ($typ === 'clanok')
        {
            $this->overUzivatela(true,true);

            $clanokManazer = new ClanokManazer();
            $clanokTypManazer = new ClanokTypManazer();

            $formular = new Formular('zoznam-clankov');
            $formular->pridajSelect('Typ článku',ClanokTypManazer::URL, array_merge(array('Všetky' => 'vsetky'), $clanokTypManazer->vratTypyClankovNazovUrl()), $typClanku);
            $formular->pridajSubmit('Zobraziť','zobrazit','','sr-only', '');
            if ($formular->odoslany() && $formular->overCSRF()) // je odoslaný formulár
            {
                $formularData = $formular->osetriHodnoty();

                try
                {
                    $formular->validuj($formularData);
                }
                catch (ChybaValidacie $chyba) // odchytáva chyby validácie
                {
                    $this->pridajSpravu($chyba->vratChyby(), self::SPR_CHYBA);
                    $this->presmeruj();
                }
                $this->presmeruj('administracia/zoznam/clanok/' . $formularData[ClanokTypManazer::URL]);
            }

            $this->data['clanky'] = $clanokManazer->vratClankyZoznam($typClanku === 'vsetky' ? false : $typClanku); // ak je vestky anvcita vsetky inak konkretny typ clankov
            SpravaPoziadaviekManazer::$kontroler['titulok'] = 'Zoznam Článkov';
            SpravaPoziadaviekManazer::$kontroler['popisok'] = 'Zoznam, Administrácia, Úprava, Aktualizácia, Odstránenie článkov';

            $this->data['presmeruj'] = self::$aktualnaUrl;

            $this->data['menu'] = (new AdministraciaManazer())->zostavMenu($urlNav);

            $this->data['formular'] = $formular->vratFormular();

            $this->pohlad = 'zoznam-clankov';
        }
        elseif ($typ === 'sutaz')
        {
            $this->overUzivatela(true,true);

            $sutazeManazer = new SutazManazer();

            SpravaPoziadaviekManazer::$kontroler['titulok'] = 'Zoznam ';
            SpravaPoziadaviekManazer::$kontroler['popisok'] = 'Zoznam, Administrácia, Úprava, Aktualizácia, Odstránenie článkov';

            $this->data['presmeruj'] = self::$aktualnaUrl;

            $this->data['menu'] = (new AdministraciaManazer())->zostavMenu($urlNav);

            $this->data['sutaze'] = $sutazeManazer->vratSutazeZoznamKratky();

            $this->pohlad = 'zoznam-sutazi';
        }
        else
        {
            $this->pridajSpravu('Zoznam na ' . $typ . ' sa nenašiel', self::SPR_CHYBA);
            $this->presmeruj('chyba');
        }
    }

    /**
     ** Spracuje požiadavku na editáciu Kontroléru/Článku
     * @param string $typ Či sa jedná o Kontrolér alebo Článok
     * @param string $url Unikátna URL adresa Kontroléra ktorý chcem editovať
     * @Action
     */
    public function editor($typ, $url = '')
    {
        $urlNav = explode('/', self::$aktualnaUrl);
        $urlNav = $urlNav[1] . '/' . $urlNav[2];
        if ($typ === 'kontroler')
        {
            $this->overUzivatela(false, true);

            $formular = new Formular('editor-kontrolerov');
            $formular->pridajInput('Kontrolér ID',SpravaPoziadaviekManazer::KONTROLER_ID, Input::TYP_HIDDEN,'','','','',false);
            $formular->nastavInputTrieda('col-9 form-control');
            $formular->nastavInputTriedaLabel('col-3 col-form-label');
            $formular->pridajInput('Titulok',SpravaPoziadaviekManazer::TITULOK, Input::TYP_TEXT);
            $formular->pridajInput('URL',SpravaPoziadaviekManazer::URL, Input::TYP_TEXT);
            $formular->pridajInput('Popisok',SpravaPoziadaviekManazer::POPISOK, Input::TYP_TEXT);
            $formular->pridajInput('Kontrolér cesta',SpravaPoziadaviekManazer::KONTROLER, Input::TYP_TEXT);
            $formular->pridajSubmit('Uložiť kontrolér', 'kontroler-tlacidlo','','btn btn-outline-primary btn-block my-3');

            SpravaPoziadaviekManazer::$kontroler['titulok'] = 'Nový Kontrolér';
            $spravaPoziadaviekManazer = new SpravaPoziadaviekManazer();

            if ($formular->odoslany() && $formular->overCSRF()) // je odoslaný formulár
            {
                $formularData = $formular->osetriHodnoty();
                try
                {
                    $formular->validuj($formularData);
                    $sprava = $spravaPoziadaviekManazer->ulozKontroler($formularData);
                    $this->pridajSpravu($sprava,self::SPR_USPECH);
                    $this->presmeruj('');
                }
                catch (ChybaValidacie $chyba) // odchytáva chyby validácie
                {
                    $formular->nastavHodnotyKontrolkam($formularData);
                    $this->pridajSpravu($chyba->vratChyby(), self::SPR_CHYBA);
                }
                catch (ChybaUzivatela $chyba)
                {
                    $formular->nastavHodnotyKontrolkam($formularData);
                    $this->pridajSpravu($chyba->getMessage(), self::SPR_CHYBA);
                }
            }
            elseif ($url) // Je zadaná URL kontroléra na editáciu
            {
                SpravaPoziadaviekManazer::$kontroler['titulok'] = 'Editácia Kontroléra';

                if ($nacitanyKontroler = $spravaPoziadaviekManazer->vratKontroler($url, $formular->vratKluceKontroliek()))
                    $formular->nastavHodnotyKontrolkam($nacitanyKontroler);

                else
                    $this->pridajSpravu('Kontrolér sa nenašiel.', self::SPR_CHYBA);
            }

            $this->data['formular'] = $formular->vratFormular();
            $this->data['menu'] = (new AdministraciaManazer())->zostavMenu($urlNav);

            $this->pohlad = 'editor-kontrolerov';
        }
        elseif ($typ === 'clanok')
        {
            $this->overUzivatela(true,true);

            $clanokManazer = new ClanokManazer();
            $clanokTypManazer = new ClanokTypManazer();

            $formular = new Formular('editor-clankov');
            $formular->pridajInput('Članok ID',ClanokManazer::CLANOK_ID, Input::TYP_HIDDEN, '','','','',false);
            $formular->nastavInputTrieda('col-10 form-control');
            $formular->nastavInputTriedaLabel('col-2 col-form-label');
            $formular->pridajCheckBox('Zverejniť článok', ClanokManazer::VEREJNY, 1,false,'Článok bude viditeľný pre všetkých uživateľov','','','',false);
            $formular->pridajSelect('Typ článku',ClanokManazer::CLANOK_TYP_ID, $clanokTypManazer->vratTypyClankovNazovId());
            $formular->pridajInput('URL',ClanokManazer::URL, Input::TYP_TEXT);
            $formular->pridajInput('Titulok',ClanokManazer::TITULOK, Input::TYP_TEXT);
            $formular->pridajInput('Popisok',ClanokManazer::POPISOK, Input::TYP_TEXT);
            $formular->pridajTextArea('Obsah',ClanokManazer::OBSAH,'',' ',$formular->formularId,25);
            $formular->upravParametreKontrolky(ClanokManazer::OBSAH,array(TextArea::POZADOVANY => false));

            $formular->pridajFile('Titulný obrázok', 'obrazok', '', '', '', false);

            $formular->pridajSubmit('Uložiť článok', 'clanok-tlacidlo','editor-clankov','btn btn-outline-primary my-3');

            SpravaPoziadaviekManazer::$kontroler['titulok'] = 'Nový Článok';
            SpravaPoziadaviekManazer::$kontroler['popisok'] = 'Vytvorenie nového článku';
            $this->data['titulnyObrazok'] = false; // titulny obrazok nieje

            $cestaDocasny = 'obrazky/clanky/docasny'; // cesta pre ukladanie a preukladanie docastnych obrázkov pouzitie pri editacii aby titulna fotka ostala v pridae ze sa nezada nová

            if ($formular->odoslany() && $formular->overCSRF()) // je odoslaný formulár
            {
                $formularData = $formular->osetriHodnoty('obrazok');

                // priecinok na ulozenie obrazkov
                $cesta = 'obrazky/clanky/' . (isset($formularData[ClanokManazer::URL]) ? $formularData[ClanokManazer::URL] : $url); // cesta pre ulozenie obrázkov dakedy je url blokovane takze nepride vo formualri
                try
                {
                    $formular->validuj($formularData);
                    Priecinok::vymazPriecinok($cesta); // vymyze priecinok  obrazkov praveukladaného článku

                    if ($formularData['clanok_typ_id'] == ClanokTypManazer::CLANOK_TRENING || $formularData['clanok_typ_id'] == ClanokTypManazer::CLANOK_STRAVA) // ak je typu strava alebo trening je potrebné zadat ,obrazok
                    {
                        // clanok id tam je kvoli tomu aby mi to enskakalo ked je vytvoreni docastni ale zaroven je nto novy clanok a ked nezadal obrazok tak mi tam skocil obrazok z docastnyhc
                        if (empty($formularData['obrazok']['name']) && !$formularData['clanok_id'] && !Subor::vratNazovSuboruPodretazec($cestaDocasny, 'titulna')) // ak nieje zadany obrazok vyvolam vynimku
                            throw new ChybaUzivatela('Pre pridanie článku typu Tréning/Strava, je potrebné pridať titulný obrázok');

                        else // inak uložím obrázok
                        {
                            if(!empty($formularData['obrazok']['name'])) // ak je zadaný nový obrazok
                            {
                                $obrazok = new Obrazok($formularData['obrazok']['tmp_name']);
                                $obrazok->zmenRozmerKSirke(500);

                                Priecinok::vytvorPriecinok($cesta);
                                $obrazok->uloz($cesta . '/titulna_' . $formularData['obrazok']['name'], $obrazok->vratObrazokTyp());
                            }
                            else // ak nieje nový obrázok tak  ulozenie obrázka z docastneho priecinku
                            {
                                if ($nazovTitObrazok = Subor::vratNazovSuboruPodretazec($cestaDocasny, 'titulna'))
                                {
                                    // skopiruje docasti titulni obrazok naspet do preicinka
                                    Subor::skopirujSubor($cestaDocasny, $nazovTitObrazok, $cesta, $nazovTitObrazok);
                                }
                            }
                        }
                    }
                    // Vymazanie priečinka z docastným obrázkom
                    Priecinok::vymazPriecinok($cestaDocasny);

                    unset($formularData['obrazok']); // odstránenie ten neukaldam do DB

                    // najdenie obrázkov v obsahu, uloženie obrázkov, vrátenie obsahu z novými odkazmi na obrázky v priečinku
                    $formularData['obsah'] = $clanokManazer->ulozObrazky($formularData, $cesta);

                    $sprava = $clanokManazer->ulozClanok($formularData);

                    $this->pridajSpravu($sprava,self::SPR_USPECH);

                    $url = !$formularData[ClanokManazer::CLANOK_ID] ? 'clanok/' . $formularData[ClanokManazer::URL] : '';

                    $this->presmeruj($url);
                }
                catch (ChybaValidacie $chyba) // odchytáva chyby validácie
                {
                    $formular->nastavHodnotyKontrolkam($formularData);
                    $this->pridajSpravu($chyba->vratChyby(), self::SPR_CHYBA);
                }
                catch (ChybaUzivatela $chyba)
                {
                    $formular->nastavHodnotyKontrolkam($formularData);
                    $this->pridajSpravu($chyba->getMessage(), self::SPR_CHYBA);
                }
            }
            elseif ($url) // Je zadaná URL článku na editáciu
            {
                SpravaPoziadaviekManazer::$kontroler['titulok'] = 'Editácia Článku';
                SpravaPoziadaviekManazer::$kontroler['popisok'] = 'Editácia, Úprava, Aktualizácia článku';

                $kluceKontroliek = $formular->vratKluceKontroliek();

                unset($kluceKontroliek[array_search('obrazok', $kluceKontroliek)]); // odstranenie hodnoty obrazok z kontroliek kovli nacitaniu hodnot z DB

                try
                {
                    $nacitanyClanok = $clanokManazer->vratClanok($url, $kluceKontroliek);
                    $nacitanyClanok[ClanokManazer::OBSAH] = $clanokManazer->nacitajObrazky($nacitanyClanok);

                    // zablokovanie menenia id a url
                    if ($nacitanyClanok[ClanokManazer::CLANOK_TYP_ID] === ClanokTypManazer::CLANOK_INFORMACIA || $nacitanyClanok[ClanokManazer::CLANOK_TYP_ID] === ClanokTypManazer::CLANOK_SLUZBA)
                    {
                        $formular->upravParametreKontrolky(ClanokManazer::CLANOK_TYP_ID, array(Input::ZABLOKOVANY => true));
                        $formular->upravParametreKontrolky(ClanokManazer::URL, array(Input::ZABLOKOVANY => true));
                    }

                    $formular->nastavHodnotyKontrolkam($nacitanyClanok);
                    //$formular->upravParametreKontrolky('obsah',array(TextArea::RIADKY => ($riadky = mb_strlen($nacitanyClanok['obsah']) / 112) >= 20 ? $riadky : 20));

                    //ak je obrazok typu trening/strava nacivam jeho obrazok
                    if($nacitanyClanok['clanok_typ_id'] === ClanokTypManazer::CLANOK_TRENING || $nacitanyClanok['clanok_typ_id'] === ClanokTypManazer::CLANOK_STRAVA)
                    {
                        $cesta = 'obrazky/clanky/' . $url; // cesta pre nacitanie obrázkov v prípade editacie

                        if ($nazovTitObrazok = Subor::vratNazovSuboruPodretazec($cesta, 'titulna'))
                        {
                            // Vymazanie priečinka z docastným obrázkom
                            Priecinok::vymazPriecinok($cestaDocasny);

                            // prekopirovanie obrazka do docastneho priecinka, pretoze sa predpokladá ze sa bude ukladaťclanok nanovo a obrazky sa vymazu
                            Subor::skopirujSubor($cesta, $nazovTitObrazok, $cestaDocasny, $nazovTitObrazok);

                            // cesta k docastnemu obrázku
                            $this->data['titulnyObrazok'] = $cestaDocasny . '/' . $nazovTitObrazok; // titulny obrazok nieje cesta k tytulnemu obrazku
                        }
                    }
                }
                catch (ChybaUzivatela $chyba)
                {
                    $this->pridajSpravu($chyba->getMessage(), self::SPR_CHYBA);
                }
            }

            $this->data['formular'] = $formular->vratFormular();
            $this->data['menu'] = (new AdministraciaManazer())->zostavMenu($urlNav);

            $this->pohlad = 'editor-clankov';
        }
        elseif ($typ === 'sutaz')
        {
            $this->overUzivatela(true,true);

            $sutazManazer = new SutazManazer();
            $sutazTypManazer = new SutazTypManazer();

            $typSutaz =$sutazTypManazer->vratTypySutaziNazovId();

            // formulár pre edytáciu a pridanie SúťaŽe
            $formularEditor = new Formular('editor-sutazi_sutaz');
            $formularEditor->pridajInput('Sútaž ID',SutazManazer::SUTAZ_ID, Input::TYP_HIDDEN, '','','','',false);

            $formularEditor->nastavInputTrieda('col-10 form-control');
            $formularEditor->nastavInputTriedaLabel('col-2 col-form-label');
            $formularEditor->pridajInput('Názov',SutazManazer::NAZOV, Input::TYP_TEXT);
            $formularEditor->pridajInput('URL',SutazManazer::URL, Input::TYP_TEXT);
            $formularEditor->pridajSelect('Typ súťaže',SutazManazer::SUTAZ_TYP_ID, $typSutaz);
            $formularEditor->pridajTextArea('Info',SutazManazer::INFO,'Popis súťaže','','',5);

            $formularEditor->nastavInputTrieda('form-control');
            $formularEditor->nastavInputTriedaLabel('');
            $formularEditor->pridajInput('Dátum súťaže',SutazManazer::DATUM_SUTAZ, Input::TYP_DATE, (new DateTime())->format(DatumCas::DB_DATUM_FORMAT));
            $formularEditor->pridajInput('Čas súťaže',SutazManazer::CAS_SUTAZ, Input::TYP_TIME, (new DateTime())->format(DatumCas::DB_CAS_FORMAT_SHORT));
            $formularEditor->pridajInput('Dátum prihlasenia',SutazManazer::DATUM_PRIHLASENIE, Input::TYP_DATE, (new DateTime())->format(DatumCas::DB_DATUM_FORMAT));

            $formularEditor->pridajSubmit('Uložiť súťaž', 'sutaz-tlacidlo','','btn btn-outline-primary my-3');

            // Formulár pre pridanie typu súťaže
            $formularTyp = new Formular('editor-sutazi_typ');

            $formularTyp->pridajSelect('Aktuálne uložené Typy',SutazManazer::SUTAZ_TYP_ID, $typSutaz, '','','','','',false);
            $formularTyp->pridajInput('Názov',SutazTypManazer::NAZOV, Input::TYP_TEXT);
            $formularTyp->pridajInput('Popis',SutazTypManazer::POPIS, Input::TYP_TEXT);

            $formularTyp->pridajSubmit('Uložiť typ', 'typ-tlacidlo','','btn btn-outline-primary my-3');

            SpravaPoziadaviekManazer::$kontroler['titulok'] = 'Nová súťaž';
            SpravaPoziadaviekManazer::$kontroler['popisok'] = 'Vytvorenie novej súťaže/typu súťaže';

            //Uloženie novej súťaže
            if ($formularEditor->odoslany()) // je odoslaný formulár
            {
                $formularEditorData = $formularEditor->osetriHodnoty();
                try
                {
                    $formularEditor->validuj($formularEditorData);

                    $sprava = $sutazManazer->ulozSutaz($formularEditorData);

                    $this->pridajSpravu($sprava,self::SPR_USPECH);

                    $this->presmeruj('');
                }
                catch (ChybaValidacie $chyba) // odchytáva chyby validácie
                {
                    $formularEditor->nastavHodnotyKontrolkam($formularEditorData);
                    $this->pridajSpravu($chyba->vratChyby(), self::SPR_CHYBA);
                }
                catch (ChybaUzivatela $chyba)
                {
                    $formularEditor->nastavHodnotyKontrolkam($formularEditorData);
                    $this->pridajSpravu($chyba->getMessage(), self::SPR_CHYBA);
                }
            }
            //Uloženie nového typu
            elseif ($formularTyp->odoslany()) // je odoslaný formulár
            {
                $formularTypData = $formularTyp->osetriHodnoty();

                // odstránienie SUTAZ_TYP_ID pretoze sa neuklada do DB
                unset($formularTypData[SutazTypManazer::SUTAZ_TYP_ID]);

                try
                {
                    $formularTyp->validuj($formularTypData);

                    $sprava = $sutazTypManazer->ulozSutazTyp($formularTypData);

                    $this->pridajSpravu($sprava,self::SPR_USPECH);

                    $this->presmeruj('');
                }
                catch (ChybaValidacie $chyba) // odchytáva chyby validácie
                {
                    $formularEditor->nastavHodnotyKontrolkam($formularTypData);
                    $this->pridajSpravu($chyba->vratChyby(), self::SPR_CHYBA);
                }
            }
            elseif ($url) // Je zadaná URL článku na editáciu => edytujem clanok
            {

                SpravaPoziadaviekManazer::$kontroler['titulok'] = 'Editácia súťaže';
                SpravaPoziadaviekManazer::$kontroler['popisok'] = 'Editácia, Úprava, Aktualizácia súťaže';

                $kluceKontroliek = $formularEditor->vratKluceKontroliek();

                try
                {
                    $nacitanaSutaz = $sutazManazer->vratSutazUrl($url, $kluceKontroliek);
                    $formularEditor->nastavHodnotyKontrolkam($nacitanaSutaz);
                }
                catch (ChybaUzivatela $chyba)
                {
                    $this->pridajSpravu($chyba->getMessage(), self::SPR_CHYBA);
                }
            }

            $this->data['formularEditor'] = $formularEditor->vratFormular();
            $this->data['formularTyp'] = $formularTyp->vratFormular();
            $this->data['menu'] = (new AdministraciaManazer())->zostavMenu($urlNav);

            $this->pohlad = 'editor-sutazi';
        }
        else
        {
            $this->pridajSpravu('Editor na ' . $typ . ' sa nenašiel.', self::SPR_CHYBA);
            $this->presmeruj('chyba');
        }
    }

    /**
     ** Odstránenie kontroléra/článku/Sutaze
     * @param string $typ Či sa jedná o Kontrolér alebo Článok
     * @param string $url Url adresa kontzroléra/článku ktorý chcem odstrániť
     * @Action
     */
    public function odstran($typ, $url)
    {
        if ($typ === 'kontroler')
        {
            $this->overUzivatela(false, true);
            $spravaPoziadaviekManazer = new SpravaPoziadaviekManazer();
            $spravaPoziadaviekManazer->odstranKontroler($url);
            $this->pridajSpravu('Kontrolér bol úspešne odstránený.',self::SPR_USPECH);
            $this->presmeruj('administracia/zoznam/kontroler');
        }
        elseif ($typ === 'clanok')
        {
            $this->overUzivatela(true,true);

            $clanokManazer = new ClanokManazer();
            $clanokManazer->odstranClanok($url);

            $this->pridajSpravu('Článok bol úspešne odstránený.',self::SPR_USPECH);
            $this->presmeruj('administracia/zoznam/clanok');
        }
        elseif ($typ === 'sutaz')
        {
            $this->overUzivatela(true,true);

            $sutazManazer = new SutazManazer();
            $sutazManazer->odstranSutaz($url);

            $this->pridajSpravu('Súťaž bola úspešne odstránená.',self::SPR_USPECH);
            $this->presmeruj('administracia/zoznam/sutaz');
        }
        else
        {
            $this->pridajSpravu('Článok/Kontrolér na odstránenie sa nenašiel.', self::SPR_CHYBA);
            $this->presmeruj('chyba');
        }
    }

    /**
     ** Spracuje úpravu osobných údajov/ vlôastné a v pripade administratóra aj ostatné
     * @param false $uzivatelId ak je zadané ID uzivatela zobrazuje osobne udaje administrator ostatnych uzivatelov
     * @Action
     */
    public function osobneUdaje($uzivatelId = false)
    {
        SpravaPoziadaviekManazer::$kontroler['titulok'] = 'Osobné údaje';
        $osobaManazer = new OsobaManazer();

        if($uzivatelId) // ak zobazujem iného uživateľa tak overim či som programator alebo admin
        {
            $this->overUzivatela(true,true);
            SpravaPoziadaviekManazer::$kontroler['titulok'] .= ' uživateľa';
            $this->pohlad = 'profil-uzivatela';
            $this->data['osobneUdaje'] = $osobaManazer->vratOsobneUdaje($uzivatelId,array(Formular::EMAIL, Formular::TEL, UzivatelManazer::DATUM_REGISTRACIE, UzivatelManazer::DATUM_PRIHLASENIA, TrenerManazer::AKTIVNY)); // pre href="mailto: a tel: + ostatné
            $trener = $this->data['osobneUdaje'][TrenerManazer::AKTIVNY]; // ci je uzivatel ktoreho zobrazujem tréner

            $osobaDetailManazer = new OsobaDetailManazer();
            $email = $osobaDetailManazer->vratEmail($uzivatelId);

            $permanentkaManazer = new PermanentkaManazer();

            // Načitanie aktivnej permanentky

            $this->data['permanentka'] = $permanentkaManazer->nacitajPermanentku(array(PermanentkaTypManazer::NAZOV, PermanentkaManazer::DATUM, PermanentkaManazer::ZOSTATOK_VSTUPOV, PermanentkaManazer::DATUM_ZNEAKTIVNENIA, PermanentkaManazer::AKTIVNA), $uzivatelId);

            $this->data['permanentky'] = $permanentkaManazer->pocetPermanentiekUzivatela($uzivatelId);

            $rezervaciaManazer = new RezervaciaManazer();

            $this->data['pocetRezervacii'] = $rezervaciaManazer->vratPocetRezervaciiUzivatela($uzivatelId);

            // rezervácie

            $this->data['rezervacieUzivatela'] = $rezervaciaManazer->nacitajRezervacieUzivatelaVsetky($uzivatelId);
            $this->data['rezervacieSkupinove'] = $rezervaciaManazer->nacitajSkupinoveRezervacieUzivatelaVsetky($uzivatelId);
            $this->data['rezervacieTrenera'] = $rezervaciaManazer->nacitajRezervacieTreneraVsetky($uzivatelId, false);
/*
if($typ === 'gym-trener')
{
$skupinaManazer = new SkupinaManazer();
$this->data['rezervacie'] = $rezervaciaManazer->nacitajSkupinoveRezervacieUzivatela(UzivatelManazer::$uzivatel[UzivatelManazer::UZIVATEL_ID]);
}
*/

            $sessionUzivatel = false; //ci obnovujem session pre uzivatele ci upravujem inu osobu alebo seba saemho
        }
        else // zobrazujem seba
        {
            $this->overUzivatela();
            $uzivatelId = UzivatelManazer::$uzivatel[UzivatelManazer::UZIVATEL_ID]; // ID prihlaseneho uzivatela
            $this->data['registracia'] = false;
            $this->pohlad = 'AdministraciaModul/Uzivatel/Pohlad/Registracia/index';
            $sessionUzivatel = true;  //ci obnovujem session pre uzivatele ci upravujem inu osobu alebo seba saemho
            $trener =  UzivatelManazer::$uzivatel['trener']; // ci je tréner aktualne prihlasený uživateľ
            $email = UzivatelManazer::$uzivatel[OsobaDetailManazer::EMAIL];
            $this->data['trener'] = $trener;
        }

        $urlNav = explode('/', self::$aktualnaUrl)[1];

        $premenne = RegistraciaKontroler::$osobaDetail;

        $formular = new Formular('osobne_udaje', $premenne);

        $formular->upravParametreKontrolky(Formular::EMAIL, array(Input::ZABLOKOVANY => true));

        if($trener)
        {
            $formular->pridajInput('Názov(prezívka)', TrenerManazer::PREZIVKA,Input::TYP_TEXT);
            $formular->pridajInput('Farba', TrenerManazer::FARBA,Input::TYP_COLOR,'', '','form-control p-0','',true, false,false, 'title = "Farba"');
        }

        $formular->pridajSubmit('Upraviť', 'upravit-tlacidlo', '', 'btn btn-outline-success btn-block mt-1');

        if ($formular->odoslany() && $formular->overCSRF())
        {
            $formularData = $formular->osetriHodnoty();

            try
            {
                $formular->validuj($formularData);

                $formularData[OsobaManazer::OSOBA_ID] = $osobaManazer->vratOsobaId($uzivatelId); // priradenie ID prihlaseneho uzivatela alebo uzivatela ktorého upravujem
                $formularData[OsobaDetailManazer::EMAIL] = $email;

                //print_r($formularData);die;

                $osobneUdaje = array_intersect_key($formularData, array_flip(array_merge(RegistraciaKontroler::$osobaDetail, RegistraciaKontroler::$osobaAdresa, array(OsobaManazer::OSOBA_ID))));
                $osobaManazer->aktualizujOsobneUdaje($osobneUdaje, $sessionUzivatel);

                if($trener) // ak je trener tak akualizujem aj jeho údaje
                {
                    $trenerUdaje = array_intersect_key($formularData, array_flip(TrenerManazer::$TrenerDetail));
                    $trenerManazer = new TrenerManazer();
                    $trenerManazer->aktualizujUdajeTrenera($trenerUdaje);
                }


                $this->pridajSpravu('Údaje boli aktualizované.', self::SPR_USPECH);
                $this->presmeruj();
            }
            catch (ChybaValidacie $chyba) // odchytáva chyby validácie
            {
                $formular->nastavHodnotyKontrolkam($formularData);
                $this->pridajSpravu($chyba->vratChyby(), self::SPR_CHYBA);
            }
        }
        else
            $formular->nastavHodnotyKontrolkam($osobaManazer->vratOsobneUdaje($uzivatelId,$formular->vratKluceKontroliek()));

        SpravaPoziadaviekManazer::$kontroler['popisok'] = 'Osobné údaje, Zmena, Uprava, Aktuálizácia, Oprava údajov';

        //print_r(UzivatelManazer::$uzivatel);

        $this->data['formular'] = $formular->vratFormular();
        $this->data['menu'] = (new AdministraciaManazer())->zostavMenu($urlNav);
    }

    /**
     ** Správa administratórov webu
     * @Action
     */
    public function admin()
    {
        $this->overUzivatela(true,true);

        $urlNav = explode('/', self::$aktualnaUrl)[1];

        $uzivatelManazer = new UzivatelManazer();
        $admin = $uzivatelManazer->vratAdminov(array (UzivatelManazer::UZIVATEL_ID));
        $formular = new Formular('admin', array(Formular::EMAIL), 'form-control text-center');
        $formular->upravParametreKontrolky(Formular::EMAIL, array('nazov' => 'Email administrátora'));
        $formular->pridajSubmit('Pridať administrátora','admin-tlacidlo','','btn btn-outline-primary btn-block my-3');

        if ($formular->odoslany() && $formular->overCSRF())
        {
            $formularData = $formular->osetriHodnoty();
            try
            {
                $formular->validuj($formularData);
                $uzivatelManazer->pridajAdmina($formularData[Formular::EMAIL]);
                $this->pridajSpravu('Administrátor bol úspešne pridaný.', self::SPR_USPECH);
                $this->presmeruj('administracia/admin');
            }
            catch (ChybaValidacie $chyba) // odchytáva chyby validácie
            {
                $formular->nastavHodnotyKontrolkam($formularData);
                $this->pridajSpravu($chyba->vratChyby(), self::SPR_CHYBA);
            }
            catch (ChybaUzivatela $chyba)
            {
                $this->pridajSpravu($chyba->getMessage(), self::SPR_CHYBA);
            }
        }
        SpravaPoziadaviekManazer::$kontroler['titulok'] = 'Správa administrátorov';
        SpravaPoziadaviekManazer::$kontroler['popisok'] = 'Správa, Zoznam, Odstránenie, Pridanie Administrátora';

        $this->data['admin'] = $admin;
        $this->data['formular'] = $formular->vratFormular();
        $this->data['menu'] = (new AdministraciaManazer())->zostavMenu($urlNav);

        $this->pohlad = 'zoznam-admin';
    }

    /**
     ** Spracuje požiadavklu na zruŠenie administrátorskách práv
     * @param int $uzivatelId Id uživateľa
     * @Action
     */
    public function odstranAdmin($uzivatelId)
    {
        $this->overUzivatela(true,true);

        $uzivatelManager = new UzivatelManazer();

        $uzivatelManager->odstranAdmina($uzivatelId);

        $this->pridajSpravu('Administrátorske práva admina boli zrušené.', self::SPR_USPECH);

        $presmerovanie = UzivatelManazer::$uzivatel[UzivatelManazer::UZIVATEL_ID] == $uzivatelId ? ' ' : 'administracia/admin'; // presmerovanie na úvod alebo an aktualnu

        $this->presmeruj($presmerovanie);
    }


    /**
     ** Správa Trénerov
     * @Action
     */
    public function trenery()
    {
        $this->overUzivatela(true,true);
        $urlNav = explode('/', self::$aktualnaUrl)[1];

        $trenerManazer = new TrenerManazer();
        $trener = $trenerManazer->vratTrenerov();

        $formular = new Formular('trenery', array(Formular::EMAIL));
        $formular->upravParametreKontrolky(Formular::EMAIL, array('nazov' => 'Email trénera'));
        $formular->pridajInput('Názov(prezívka)', TrenerManazer::PREZIVKA,Input::TYP_TEXT);
        $formular->pridajInput('Farba', TrenerManazer::FARBA,Input::TYP_COLOR,'', '','form-control p-0','',true, false,false, 'title = "Farba"');
        //farba

        $formular->pridajSubmit('Pridať trénera','trener-tlacidlo','','btn btn-outline-primary my-3');

        if ($formular->odoslany() && $formular->overCSRF())
        {
            $formularData = $formular->osetriHodnoty();
            try
            {
                $formular->validuj($formularData);

                $trenerManazer->pridajTrenera($formularData);

                $this->pridajSpravu('Tréner bol úspešne pridaný.', self::SPR_USPECH);
                $this->presmeruj('administracia/trenery');
            }
            catch (ChybaValidacie $chyba) // odchytáva chyby validácie
            {
                $formular->nastavHodnotyKontrolkam($formularData);
                $this->pridajSpravu($chyba->vratChyby(), self::SPR_CHYBA);
            }
            catch (ChybaUzivatela $chyba)
            {
                $formular->nastavHodnotyKontrolkam($formularData);
                $this->pridajSpravu($chyba->getMessage(), self::SPR_CHYBA);
            }
        }
        SpravaPoziadaviekManazer::$kontroler['titulok'] = 'Správa trénerov';
        SpravaPoziadaviekManazer::$kontroler['popisok'] = 'Správa, Zoznam, Odstránenie, Pridanie Trénera';

        $this->data['trener'] = $trener;
        $this->data['formular'] = $formular->vratFormular();
        $this->data['menu'] = (new AdministraciaManazer())->zostavMenu($urlNav);

        $this->pohlad = 'zoznam-trener';
    }

    /**
     ** Spracuje požiadavklu na zpravu trenera akivujem/ deaktivujem
     * @param int $trenerId Id trenera
     * @Action
     */
    public function upravStavTrenera($trenerId)
    {
        $this->overUzivatela(true,true);

        $trenerManager = new TrenerManazer();

        $stav = $trenerManager->upravStav($trenerId);

        $this->pridajSpravu('Stav trenéra bol zmenený na : ' . $stav , self::SPR_USPECH);

        $this->presmeruj('administracia/trenery');
    }

    /**
     * Vypiši zoznam rezervácii daného uživateľa
     * @param string $typ O aky typ rezervacie sa jenda .. gym/ mase , ...
     * @Action
     */
    public function rezervacie($typ)
    {
        $this->overUzivatela();

        $slovnik = array('gym' => 'Individuálne', 'gym-trener' => 'S Trénerom', 'maser' => 'Maśer', 'tejper' => 'Tejper');

        $rezervaciaManazer = new RezervaciaManazer();

        if($typ === 'gym')
            $this->data['rezervacie'] = $rezervaciaManazer->nacitajRezervacieUzivatelaVsetky(UzivatelManazer::$uzivatel[UzivatelManazer::UZIVATEL_ID]);
        if($typ === 'gym-trener')
        {
            //$skupinaManazer = new SkupinaManazer();
            $this->data['rezervacie'] = $rezervaciaManazer->nacitajSkupinoveRezervacieUzivatelaVsetky(UzivatelManazer::$uzivatel[UzivatelManazer::UZIVATEL_ID]);
        }

        SpravaPoziadaviekManazer::$kontroler['titulok'] = 'Rezervácie: ' . $slovnik[$typ];
        SpravaPoziadaviekManazer::$kontroler['popisok'] = 'Správa, Zoznam, Odstránenie, Rezervácia';

        $administraciaManazer = new AdministraciaManazer();
        $this->data['kod'] = $rezervaciaManazer->zistiDneskaRezervacia(UzivatelManazer::$uzivatel[UzivatelManazer::UZIVATEL_ID]) ? $administraciaManazer->nacitajKod() : false;
        $this->data['aktualnaUrl'] = Kontroler::$aktualnaUrl;
        $this->data['typ'] = $typ;
        $this->data['permanentka'] = (new PermanentkaManazer())->nacitajPermanentku(array(PermanentkaTypManazer::NAZOV, PermanentkaManazer::DATUM, PermanentkaManazer::DATUM_ZNEAKTIVNENIA, PermanentkaManazer::ZOSTATOK_VSTUPOV, PermanentkaManazer::AKTIVNA), UzivatelManazer::$uzivatel[UzivatelManazer::UZIVATEL_ID]);
        $this->data['menu'] = (new AdministraciaManazer())->zostavMenu($typ);

        $this->pohlad = 'rezervacia';
    }

    /**
     ** Vypiši zoznam Všetkých uživateľov
     * @param string $url url vybrateho menu
     * @param false $vyber VYber pre rezervacie
     *  @Action
     */
    public function spravaUzivatelov($url, $vyber = false)
    {
        $this->overUzivatela(true,true);

        $slovnik = array('rezervacie' => 'Rezervácie','permanentka' => 'Permanentky', 'vsetci' => 'Všetci', 'rezervacia-pridat' => 'Pridať rezerváciu');

        $osobaManazer = new OsobaManazer();
        if($url === 'rezervacie' && in_array($vyber,RezervaciaManazer::MOZNOSTI_VYBERU))
        {
            $formular = new Formular('rezervacie');
            $formular->pridajSelect('Rezervácie','rezervacia_vyber',RezervaciaManazer::MOZNOSTI_VYBERU, $vyber);
            $formular->pridajSubmit('Zobraziť','zobrazit','','sr-only', '');
            if ($formular->odoslany() && $formular->overCSRF()) // je odoslaný formulár
            {
                $formularData = $formular->osetriHodnoty();
                try
                {
                    $formular->validuj($formularData);
                }
                catch (ChybaValidacie $chyba) // odchytáva chyby validácie
                {
                    $this->pridajSpravu($chyba->vratChyby(), self::SPR_CHYBA);
                    $this->presmeruj();
                }
                $this->presmeruj('administracia/sprava-uzivatelov/rezervacie/' . $formularData['rezervacia_vyber']);
            }

            $datum = new DateTime(); // aktualný dátum
            $rezervaciaManazer = new RezervaciaManazer();

            $rezervacie = $rezervaciaManazer->nacitajRezervacieAdmin($datum, $vyber);
            $this->data['rezervacie'] = $rezervacie;

            $this->data['formular'] = $formular->vratFormular();
            $this->data['tabulka'] = 'rezervacie'; // nacitanie podpohladu
        }
        elseif($url === 'permanentka')
        {
            $permanentkaManezer = new PermanentkaManazer();
            $permanentkaTypManazer = new PermanentkaTypManazer();

            $formular = new Formular('permanentka');
            $formular->pridajSelect('Uživateľ',PermanentkaManazer::OSOBA_ID, $osobaManazer->vratParyOsobaId());
            $formular->pridajSelect('Permanentka',PermanentkaTypManazer::PERMANENTKA_TYP_ID, $permanentkaTypManazer->nacitajTypyPermanentiek());
            $formular->pridajInput('Platnosť od','datum_od', Input::TYP_DATE, (new DateTime())->format(DatumCas::DB_DATUM_FORMAT));
            $formular->pridajSubmit('Pridať','pridat-tlacidlo','','btn btn-outline-success btn-block');

            if ($formular->odoslany() && $formular->overCSRF())
            {
                $formularData = $formular->osetriHodnoty();
                try
                {
                    $formular->validuj($formularData);
                    $permanentkaManezer->ulozPermanentku($formularData);
                    $this->pridajSpravu('Permanentka bola pridelená.', self::SPR_USPECH);
                    $this->presmeruj();
                }
                catch (ChybaValidacie $chyba) // odchytáva chyby validácie
                {
                    $this->pridajSpravu($chyba->vratChyby(), self::SPR_CHYBA);
                }
            }
            $this->data['formular'] = $formular->vratFormular();
            $this->data['uzivatelia'] = $permanentkaManezer->nacitajPermanentky();
            $this->data['tabulka'] = 'permanentky'; // nacitanie podpohladu

        }
        elseif ($url === 'vsetci')
        {
            $this->data['uzivatelia'] = $osobaManazer->vratZoznamUzivatelov();
            $this->data['tabulka'] = 'vsetci'; // nacitanie podpohladu
        }
        elseif($url === 'rezervacia-pridat')
        {
            $klientManazer = new KlientManazer();
            $moznostiUzivatelov = $klientManazer->vratMoznostiKlientov();

            $cas = new Cas(RezervaciaKontroler::START,RezervaciaKontroler::KONIEC,RezervaciaKontroler::KROK);
            $aktualnyDatum = (new DateTime())->format(DatumCas::DB_DATUM_FORMAT);
            $casy = $cas->generujCasy(); // nevyberiam ziaden datum lebo chem aby generovalo vsetky casy... az pri odoslani formulára skontrolujem ci je cas správny.. dakedy dorobit cez JS

            $formular = new Formular('rezervacia-pridat');
            $formular->pridajSelect('Uživateľ',OsobaManazer::OSOBA_ID, $moznostiUzivatelov, '',false,'','',' ');
            $formular->pridajInput('Dátum',RezervaciaManazer::DATUM, Input::TYP_DATE, $aktualnyDatum,'','',' ');
            $formular->pridajSelect('Príchod',RezervaciaManazer::CAS_OD, $casy['cas_od'],'',false,'','',' ');
            $formular->pridajSelect('Odchod',RezervaciaManazer::CAS_DO, $casy['cas_do'],'',false,'','',' ');
            $formular->pridajSubmit('Pridať Rezerváciu','rezervacia-pridat-tlacidlo','','btn btn-outline-success h-100');

            if ($formular->odoslany() && $formular->overCSRF())
            {
                $formularData = $formular->osetriHodnoty();
                try
                {
                    $formular->validuj($formularData);
                    //$formular->nastavHodnotyKontrolkam($formularData);

                    $rezervaciaManazer = new RezervaciaManazer();
                    $rezervaciaManazer->rezervovat($formularData, false);

                    $this->pridajSpravu('Rezervácia bola pridaná.', self::SPR_USPECH);
                    $this->presmeruj();
                }
                catch (ChybaValidacie $chyba) // odchytáva chyby validácie
                {
                    $formular->nastavHodnotyKontrolkam($formularData);
                    $this->pridajSpravu($chyba->vratChyby(), self::SPR_CHYBA);
                }
                catch (ChybaUzivatela $chyba) // odchytáva chyby validácie
                {
                    $formular->nastavHodnotyKontrolkam($formularData);
                    $this->pridajSpravu($chyba->getMessage(), self::SPR_CHYBA);
                }

            }
            $this->data['formular'] = $formular->vratFormular();
            $this->data['tabulka'] = 'rezervacia-pridat'; // nacitanie podpohladu
        }
        else
            $this->presmeruj('chyba');

        SpravaPoziadaviekManazer::$kontroler['titulok'] = $slovnik[$url];
        SpravaPoziadaviekManazer::$kontroler['popisok'] = 'Zoznam uživateľov, registrovaný uživateľia, správa uživateľov, permanetka';

        $this->data['vyber'] = $vyber;
        $urlNav = explode('/', self::$aktualnaUrl)[1];
        $this->data['menu'] = (new AdministraciaManazer())->zostavMenu($urlNav);
        $this->data['typ'] = $url;
        $this->pohlad = 'sprava-uzivatelia';
    }

    /**
     ** Odstráni permanentku z databázi
     * @param int $permanentkaId Id permanetky
     * @Action
     */
    public function odstranPermanentku($permanentkaId)
    {
        $this->overUzivatela(true,true);

        $permanentkaManager = new PermanentkaManazer();
        $permanentkaManager->odstranPermanentku($permanentkaId);

        $this->pridajSpravu('Permanentka bola odstranená.', self::SPR_USPECH);
        $this->presmeruj('administracia/sprava-uzivatelov/permanentka');
    }

    /**
     ** Odstráni permanentku z databázi
     * @param int $permanentkaId Id permanetky
     * @Action
     */
    public function zmenaKodu()
    {
        $this->overUzivatela(true,true);

        $urlNav = explode('/', self::$aktualnaUrl)[1];

        $administraciaManazer = new AdministraciaManazer();

        $kod = $administraciaManazer->nacitajKod();

        $formular = new Formular('zmena-kodu');
        $formular->pridajInput('Kód','kod_1',Input::TYP_TEXT,$kod[0]);
        $formular->pridajInput('Kód','kod_2',Input::TYP_TEXT,$kod[1]);
        $formular->pridajInput('Kód','kod_3',Input::TYP_TEXT,$kod[2]);
        $formular->pridajInput('Kód','kod_4',Input::TYP_TEXT,$kod[3]);
        $formular->pridajSubmit('Zmeniť kod','kod-tlacidlo','','btn btn-outline-primary btn-block my-3');

        if ($formular->odoslany() && $formular->overCSRF())
        {
            $formularData = $formular->osetriHodnoty();
            try
            {
                $formular->validuj($formularData);

                $kod = implode('', $formularData);
                $administraciaManazer->zmenKod($kod);
                $this->pridajSpravu('Kód bol zmenený.', self::SPR_USPECH);
                $this->presmeruj('administracia/zmena-kodu');
            }
            catch (ChybaValidacie $chyba) // odchytáva chyby validácie
            {
                $formular->nastavHodnotyKontrolkam($formularData);
                $this->pridajSpravu($chyba->vratChyby(), self::SPR_CHYBA);
            }
        }
        SpravaPoziadaviekManazer::$kontroler['titulok'] = 'Zmena Kódu';
        SpravaPoziadaviekManazer::$kontroler['popisok'] = 'Zmena Kódu';

        $this->data['formular'] = $formular->vratFormular();
        $this->data['menu'] = (new AdministraciaManazer())->zostavMenu($urlNav);
        $this->pohlad = 'zmena-kodu';
    }

    /**
     ** Zobrazí zoznam Klientov
     * @Action
     */
    public function zoznamKlientov()
    {
        $this->overTrenera();

        $trenerManazer = new TrenerManazer();
        $osobaManazer = new OsobaManazer();

        $trenerId =  $trenerManazer->vratTrenerId(UzivatelManazer::$uzivatel[UzivatelManazer::UZIVATEL_ID]);
        $osobaId = $osobaManazer->vratOsobaId(UzivatelManazer::$uzivatel[UzivatelManazer::UZIVATEL_ID]);

        $klientManazer = new KlientManazer();
        $klienty = $klientManazer->vratMoznostiKlientov($osobaId);

        $formular = new Formular('pridat-klienta');
        $formular->pridajSelect('Uživateľ',OsobaManazer::OSOBA_ID, $klienty);
        $formular->pridajSubmit('Pridať klienta','pridat-klienta-tlacidlo','','btn btn-outline-success btn-block');

        if ($formular->odoslany() && $formular->overCSRF())
        {
            $formularData = $formular->osetriHodnoty();
            try
            {
                $formular->validuj($formularData);
                //$formular->nastavHodnotyKontrolkam($formularData);

                $klientManazer->pridajKlienta(UzivatelManazer::$uzivatel[UzivatelManazer::UZIVATEL_ID], $formularData[OsobaManazer::OSOBA_ID]);

                $this->pridajSpravu('Klient bol pridaný.', self::SPR_USPECH);
                $this->presmeruj();
            }
            catch (ChybaValidacie $chyba) // odchytáva chyby validácie
            {
                $this->pridajSpravu($chyba->vratChyby(), self::SPR_CHYBA);
                $this->presmeruj();
            }
            catch (ChybaUzivatela $chyba) // odchytáva chyby validácie
            {
                $this->pridajSpravu($chyba->getMessage(), self::SPR_CHYBA);
                $this->presmeruj();
            }
        }

        SpravaPoziadaviekManazer::$kontroler['titulok'] = 'Zoznam klientov';
        SpravaPoziadaviekManazer::$kontroler['popisok'] = 'Zoznam klientov, pridanie klienata, správa klientov';

        $this->data['formular'] = $formular->vratFormular();

        $odkazDetail = (bool) !(UzivatelManazer::$uzivatel[UzivatelManazer::ADMIN] || UzivatelManazer::$uzivatel[UzivatelManazer::PROGRAMATOR]);

        $this->data['klienti'] = $klientManazer->nacitajKlientov($trenerId, $odkazDetail);
        $this->data['odkazDetail'] = (bool) (UzivatelManazer::$uzivatel[UzivatelManazer::ADMIN] || UzivatelManazer::$uzivatel[UzivatelManazer::PROGRAMATOR]);

$urlNav = explode('/', self::$aktualnaUrl)[1];
        $this->data['menu'] = (new AdministraciaManazer())->zostavMenu($urlNav);
        $this->pohlad = 'zoznam-klientov';
    }

    /**
     ** Odstráni klienta z databázi
     * @param int $permanentkaId Id permanetky
     * @Action
     */
    public function zrusKlienta($klientID)
    {
        $this->overTrenera();
        $klientManazer = new KlientManazer();

        $trenrManazer = new TrenerManazer();
        $trenerId = $trenrManazer->vratTrenerId(UzivatelManazer::$uzivatel[UzivatelManazer::UZIVATEL_ID]);

        try
        {
            $klientManazer->odstranKlienta($klientID, $trenerId);
            $this->pridajSpravu('Klient bol odstránený.', self::SPR_USPECH);
        }
        catch (ChybaUzivatela $chyba) // odchytáva chyby validácie
        {
            $this->pridajSpravu($chyba->getMessage(), self::SPR_CHYBA);
        }
        $this->presmeruj('administracia/zoznam-klientov');
    }

    /**
     ** Zostaví sa trening Trénera
     * @Action
     */
    public function rezervaciaTreningu()
    {

//$upravApp = new upravaApp();


        $this->overTrenera();
        $rezervaciaManazer = new RezervaciaManazer();
        $trenerManazer = new TrenerManazer();

        $trenerId = $trenerManazer->vratTrenerId(UzivatelManazer::$uzivatel[UzivatelManazer::UZIVATEL_ID]);

        $cas = new Cas(RezervaciaKontroler::START,RezervaciaKontroler::KONIEC,RezervaciaKontroler::KROK);
        $aktualnyDatum = (new DateTime())->format(DatumCas::DB_DATUM_FORMAT);
        $casy = $cas->generujCasy(); // nevyberiam ziaden datum lebo chem aby generovalo vsetky casy... az pri odoslani formulára skontrolujem ci je cas správny.. dakedy dorobit cez JS

        $klientManazer = new KlientManazer();
        $klient = $klientManazer->nacitajParyKlientov($trenerId);
        $pocetZobrazenychSelect = $klient && ($a = count($klient)) < 4 ? $a : '4';

        $formular = new Formular('rezervovat');
        $formular->pridajSelect('Klient',KlientManazer::KLIENT_ID, $klient ? $klient : array(), '',true,'','','',true,'size="' . $pocetZobrazenychSelect . '"');
        $formular->pridajInput('Dátum',RezervaciaManazer::DATUM, Input::TYP_DATE, $aktualnyDatum,'','',' ');
        $formular->pridajSelect('Príchod',RezervaciaManazer::CAS_OD, $casy['cas_od'],'',false,'','',' ');
        $formular->pridajSelect('Odchod',RezervaciaManazer::CAS_DO, $casy['cas_do'],'',false,'','',' ');
        $formular->pridajInput('Poznamka',PoznamkaManazer::POZNAMKA, Input::TYP_TEXT,'','','',' ',false);
        $formular->pridajInput('Počet', 'pocet', Input::TYP_NUMBER,'1','','form-control sirka-75px',' ');
        $formular->pridajSubmit('Rezervovať','rezervovat-tlacidlo','','btn btn-outline-success h-100');

        if ($formular->odoslany() && $formular->overCSRF())
        {
            $formularData = $formular->osetriHodnoty();

            try
            {
                $formular->validuj($formularData);

                $pocetTyzdnov = $formularData['pocet']; // PoČet rezervacii Viacnasobných ktore sa vytvoria na v daný deň ačás v týŽdny ... napriklad kazdy utorok odpiatej do siestej 3 tyzdne
                unset($formularData['pocet']);

                $skupinaOsobaId = array_shift($formularData); // osoba_id jednotlivých klientov
                $poznamka = array_pop($formularData); // poznamka ku skupinovej rezervácii

                for($i = 1; $i <= $pocetTyzdnov; $i++)
                {
                    Db::zacatTranzakciu();

                    $rezervaciaId = $rezervaciaManazer->rezervovat($formularData, false);

                    $skupinaManazer = new SkupinaManazer();
                    $skupinaManazer->ulozSkupinu($skupinaOsobaId, $rezervaciaId); // ulozenie osob skupiny

                    if(!empty($poznamka)) // ak je zadana poznámka uložim ju
                    {
                        $poznamkaManazer = new PoznamkaManazer();
                        $poznamkaManazer->ulozPoznamku($poznamka, $rezervaciaId);
                    }
                    Db::dokonciTransakciu();

                    $datum = new DateTime($formularData[RezervaciaManazer::DATUM]);

                    $this->pridajSpravu('Rezervácia Skupiny bola vytvorená na dátum: ' . $datum->format(DatumCas::DATUM_FORMAT), self::SPR_USPECH);

                    $datum->modify('+ 7 day');

                    $formularData[RezervaciaManazer::DATUM] =  $datum->format(DatumCas::DB_DATUM_FORMAT);
                }

                $this->presmeruj();
            }
            catch (ChybaValidacie $chyba) // odchytáva chyby validácie
            {
                $formular->nastavHodnotyKontrolkam($formularData);
                $this->pridajSpravu($chyba->vratChyby(), self::SPR_CHYBA);
            }
            catch (ChybaUzivatela $chyba) // odchytáva chyby validácie
            {
                $formular->nastavHodnotyKontrolkam($formularData);
                $this->pridajSpravu($chyba->getMessage(), self::SPR_CHYBA);
            }
        }

        SpravaPoziadaviekManazer::$kontroler['titulok'] = 'Zostavenie skupiny';
        SpravaPoziadaviekManazer::$kontroler['popisok'] = 'Zostavenie skupiny';

        $odkazDetail = (bool) !(UzivatelManazer::$uzivatel[UzivatelManazer::ADMIN] || UzivatelManazer::$uzivatel[UzivatelManazer::PROGRAMATOR]);
        $this->data['skupiny'] = $rezervaciaManazer->nacitajRezervacieTreneraVsetky(UzivatelManazer::$uzivatel[UzivatelManazer::UZIVATEL_ID], $odkazDetail);

        $this->data['formular'] = $formular->vratFormular();
        $this->data['aktualnaUrl'] = Kontroler::$aktualnaUrl;

        $urlNav = explode('/', self::$aktualnaUrl)[1];
        $this->data['menu'] = (new AdministraciaManazer())->zostavMenu($urlNav);

        $this->pohlad = 'rezervacia-trening';
    }

    /**
     ** Spáva odosielania Emailu
     * @Action
     */
    public function email()
    {
        $this->overUzivatela(true,true);

        $osobaManazer = new OsobaManazer();

        $paryOsobaEmail = $osobaManazer->nacitajParyOsobaEmail();

        $formular = new Formular('email');
        $formular->pridajSelect('Osoba','osoba', $paryOsobaEmail, '',true,'','','',true,'size="18"');
        $formular->pridajInput('Predmet','predmet', Input::TYP_TEXT);
        $formular->pridajTextArea('Email',OsobaDetailManazer::EMAIL,'',' ','',14,'','',true);
        $formular->pridajSubmit('Odoslať', 'email-tlacidlo','', 'btn btn-outline-success');

        if ($formular->odoslany() && $formular->overCSRF())
        {
            $formularData = $formular->osetriHodnoty();

            try
            {
                $formular->validuj($formularData);

                $kontaktManazer = new KontaktManazer();
                $kontaktManazer->odosliSkupinovyEmail($formularData['osoba'], $formularData['predmet'], $formularData[OsobaDetailManazer::EMAIL]);

                $this->pridajSpravu('Email bol odoslaný vybratým uživateľom.', self::SPR_USPECH);

                $this->presmeruj();
            }
            catch (ChybaValidacie $chyba) // odchytáva chyby validácie
            {
                $formular->nastavHodnotyKontrolkam($formularData);
                $this->pridajSpravu($chyba->vratChyby(), self::SPR_CHYBA);
            }
            catch (ChybaUzivatela $chyba) // odchytáva chyby validácie
            {
                $formular->nastavHodnotyKontrolkam($formularData);
                $this->pridajSpravu($chyba->getMessage(), self::SPR_CHYBA);
            }
        }

        SpravaPoziadaviekManazer::$kontroler['titulok'] = 'Odosielač emailov';
        SpravaPoziadaviekManazer::$kontroler['popisok'] = 'Odosielanie emailu, skupinového emailu';

        $this->data['formular'] = $formular->vratFormular();

        $urlNav = explode('/', self::$aktualnaUrl)[1];
        $this->data['menu'] = (new AdministraciaManazer())->zostavMenu($urlNav);

        $this->pohlad = 'email';
    }

    /**
     ** Zobrazí detail súťaže aj z jeho účastnikmi
     * @param string $url url súťaže
     * @Action
     */
    public function sutaz($url)
    {
        $this->overUzivatela(true,true);
        $sutazManazer = new SutazManazer();
        $sutazPrihlasenyManazer = new SutazPrihlasenyManazer();

        $sutaz = $sutazManazer->vratSutazDetail($url);
        $ucastnici = $sutazPrihlasenyManazer->nacitajDetailUcastnikovSutaze($sutaz['sutaz_id']);

        SpravaPoziadaviekManazer::$kontroler['titulok'] = 'Súťaž: ' . $sutaz['sutaz_nazov'];
        SpravaPoziadaviekManazer::$kontroler['popisok'] = 'Zobrazenie súťaže aj s učastníkmi pre administratóra';

        $this->data['ucastnici'] = $ucastnici;
        $this->data['sutaz'] = $sutaz;

        $urlNav = explode('/', self::$aktualnaUrl)[1];
        $this->data['menu'] = (new AdministraciaManazer())->zostavMenu($urlNav);

        $this->pohlad = 'sutaz-detail';
    }

    /**
     ** Zobrazí súťaže na ktore je konkretný uživateľ prihlásený
     * @Action
     */
    public function mojeSutaz()
    {
        $this->overUzivatela();

        $sutazManazer = new SutazManazer();

        $osobaManazer = new OsobaManazer();

        $osobaId = $osobaManazer->vratOsobaId(UzivatelManazer::$uzivatel['uzivatel_id']);

        //Načitanie Súťaže z DB
        $sutaze = $sutazManazer->vratSutazeUzivatelaZoznam($osobaId);
        $this->data['sutaze'] = $sutaze;

        SpravaPoziadaviekManazer::$kontroler['titulok'] = 'Moje súťaže';
        SpravaPoziadaviekManazer::$kontroler['popisok'] = 'Súťaže na ktoré som prihlásený';

        $this->data['presmeruj'] = self::$aktualnaUrl;

        $urlNav = explode('/', self::$aktualnaUrl)[1];
        $this->data['menu'] = (new AdministraciaManazer())->zostavMenu($urlNav);

        $this->pohlad = 'moje-sutaze';
    }



    /**
     ** Vypiši zoznam Všetkých uživateľov
     * @param string $url url vybrateho menu
     * @param false $vyber VYber pre rezervacie
     *  @Action
     */
    public function statistiky()
    {
        $this->overUzivatela(true,true);
        SpravaPoziadaviekManazer::$kontroler['titulok'] = 'Štatistiky';
        SpravaPoziadaviekManazer::$kontroler['popisok'] = 'štatistiky, Návštevnosť, počet rezervácií, rezervácie za čas, počet rezervácií uživateľa';

        // predvoelne hodnoty datum od zaciatku mesiaca do dneska vrátane
        $datumVyber = array('od' => DatumCas::prvyDenMesiaca()->format(DatumCas::DB_DATUM_FORMAT), 'do' => DatumCas::dbDatumTeraz()); //false; //
        $limit = 3;

        $formular = new Formular('statistika');
        $formular->pridajInput('od',StatistikaManazer::DATUM_OD, Input::TYP_DATE, $datumVyber['od'],'','',' ');
        $formular->pridajInput('do',StatistikaManazer::DATUM_DO, Input::TYP_DATE, $datumVyber['do'],'','',' ');
        $formular->pridajInput('Limit', StatistikaManazer::LIMIT, Input::TYP_NUMBER,$limit,'','form-control sirka-75px',' ');
        $formular->pridajSubmit('Zobraziť', 'zobrazit', '', 'btn btn-outline-success h-100');

        if ($formular->odoslany() && $formular->overCSRF())
        {
            $formularData = $formular->osetriHodnoty();
            try
            {
                $formular->validuj($formularData);
                $datumVyber = array('od' => $formularData[StatistikaManazer::DATUM_OD], 'do' => $formularData[StatistikaManazer::DATUM_DO]); //false; //
                $limit = $formularData[StatistikaManazer::LIMIT];

                $formular->nastavHodnotyKontrolkam($formularData);
            }
            catch (ChybaValidacie $chyba) // odchytáva chyby validácie
            {
                $formular->nastavHodnotyKontrolkam($formularData);
                $this->pridajSpravu($chyba->vratChyby(), self::SPR_CHYBA);
            }
        }
        $this->data['formular'] = $formular->vratFormular();

        $statistikaManazer = new StatistikaManazer($datumVyber, false, $limit);
        $this->data['pocetDni'] = DatumCas::vratPocetDni($datumVyber['od'], $datumVyber['do']);
        $this->data['pocetRezervacii'] = $statistikaManazer->pocetRezervaciiZa();
        $this->data['pocetUnikatnychRezervacii'] = $statistikaManazer->pocetRezervaciiZa(true);
        $this->data['pocetRezervaciiUzivatelov'] = $statistikaManazer->vratPocetRezervaciiUzivatelov();



        $statistikaManazerMesaice = new StatistikaManazer(array('od' => '2021-01-01', 'do' => ''), false, 3);
        $this->data['pocetRezervaciiMesiace'] = $statistikaManazerMesaice->pocetRezervaciiMesiace();
        $this->data['mesiace'] = DatumCas::$mesiace;

        $urlNav = explode('/', self::$aktualnaUrl)[1];
        $this->data['menu'] = (new AdministraciaManazer())->zostavMenu($urlNav);

        $this->pohlad = 'statistiky';
    }















    /**
     ** Zostavy adminstračné menu
     * @param string $url Url prave navstivenej Adresy
     */
    public function menu($url)
    {
        $this->data['menuUzivatel']['Správa Účtu'] =
            array('Osobné Údaje' => array('url' => 'administracia/osobne-udaje', 'aktivna' => $url === 'osobne-udaje' ? 'aktivna' : ''),
                'Zmena Hesla' => array('url' => 'administracia/zmena-hesla', 'aktivna' => $url === 'zmena-hesla' ? 'aktivna' : ''));

        $this->data['menuUzivatel']['Rezervácie'] =
            array('Individuálne' => array('url' => 'administracia/rezervacie/gym', 'aktivna' => $url === 'gym' ? 'aktivna' : ''),
                'S trénerom' => array('url' => 'administracia/rezervacie/gym-trener', 'aktivna' => $url === 'gym-trener' ? 'aktivna' : ''));
        $this->data['menuUzivatel']['Moje súťaže'] =
            array('Moje súťaže' => array('url' => 'administracia/moje-sutaz', 'aktivna' => $url === 'moje-sutaz' ? 'aktivna-tlacidlo' : 'text-white'));
        //'Masér' => array('url' => 'administracia/rezervacie/maser', 'aktivna' => $url === 'maser' ? 'aktivna' : ''),
        //'Trenér' => array('url' => 'administracia/rezervacie/trener', 'aktivna' => $url === 'trener' ? 'aktivna' : ''),
        //'Tejper' => array('url' => 'administracia/rezervacie/tejper', 'aktivna' => $url === 'tejper' ? 'aktivna' : ''));

        if(UzivatelManazer::$uzivatel && (UzivatelManazer::$uzivatel['trener'] || UzivatelManazer::$uzivatel[UzivatelManazer::PROGRAMATOR])) // bobby
        {
            $this->data['menuBobby']['Správa klientov'] =
                array('Zoznam klientov' => array('url' => 'administracia/zoznam-klientov', 'aktivna' => $url === 'zoznam-klientov' ? 'aktivna' : ''),
                    'Rezervácia skupiny' => array('url' => 'administracia/rezervacia-treningu', 'aktivna' => $url === 'rezervacia-treningu' ? 'aktivna' : ''));
            //'Permanentky Gym' => array('url' => 'administracia/permanentka', 'aktivna' => $url === 'permanentka' ? 'aktivna' : ''));
        }

        if(UzivatelManazer::$uzivatel && (UzivatelManazer::$uzivatel[UzivatelManazer::ADMIN] || UzivatelManazer::$uzivatel[UzivatelManazer::PROGRAMATOR])) // admin
        {
            $this->data['menuAdmin']['Správa uživateľov'] =
                array('Zoznam uživateľov' => array('url' => 'administracia/sprava-uzivatelov/rezervacie/dnes', 'aktivna' => $url === 'sprava-uzivatelov' ? 'aktivna-tlacidlo' : 'text-white'));
            //'Zoznam rezervácii' => array('url' => 'administracia/zoznam-rezervacie', 'aktivna' => $url === 'zoznam-rezervacie' ? 'aktivna' : ''),
            //'Permanentky Gym' => array('url' => 'administracia/permanentka', 'aktivna' => $url === 'permanentka' ? 'aktivna' : ''));

            $this->data['menuAdmin']['Štatistiky'] =
                array('Štatistiky' => array('url' => 'administracia/statistiky', 'aktivna' => $url === 'statistiky' ? 'aktivna-tlacidlo' : 'text-white'));

            $this->data['menuAdmin']['Články'] =
                array('Zoznam Článkov' => array('url' => 'administracia/zoznam/clanok', 'aktivna' => $url === 'zoznam/clanok' ? 'aktivna' : ''),
                    'Nový Článok' => array('url' => 'administracia/editor/clanok', 'aktivna' => $url === 'editor/clanok' ? 'aktivna' : ''));

            $this->data['menuAdmin']['Správa administrátorov'] =
                array('Správa administrátorov' => array('url' => 'administracia/admin', 'aktivna' => $url === 'admin' ? 'aktivna-tlacidlo' : 'text-white'));
            $this->data['menuAdmin']['Správa trénerov'] =
                array('Správa trénerov' => array('url' => 'administracia/trenery', 'aktivna' => $url === 'trenery' ? 'aktivna-tlacidlo' : 'text-white'));
            $this->data['menuAdmin']['Zmena KÓDU'] =
                array('Zmena KÓDU' => array('url' => 'administracia/zmena-kodu', 'aktivna' => $url === 'zmena-kodu' ? 'aktivna-tlacidlo' : 'text-white'));
            $this->data['menuAdmin']['Email'] =
                array('Email' => array('url' => 'administracia/email', 'aktivna' => $url === 'email' ? 'aktivna-tlacidlo' : 'text-white'));

            $this->data['menuAdmin']['Súťaže'] =
                array('Zoznam Sútaži' => array('url' => 'administracia/zoznam/sutaz', 'aktivna' => $url === 'zoznam/sutaz' ? 'aktivna' : ''),
                    'Nová Sútaž' => array('url' => 'administracia/editor/sutaz', 'aktivna' => $url === 'editor/sutaz' ? 'aktivna' : ''));
        }
        if(UzivatelManazer::$uzivatel && UzivatelManazer::$uzivatel[UzivatelManazer::PROGRAMATOR]) // programator
        {
            $this->data['menuProgramator']['Kontroléry'] =
                array('Zoznam Kontrolérov' => array('url' => 'administracia/zoznam/kontroler', 'aktivna' => $url === 'zoznam/kontroler' ? 'aktivna' : ''),
                    'Nový Kontrolér' => array('url' => 'administracia/editor/kontroler', 'aktivna' => $url === 'editor/kontroler' ? 'aktivna' : ''));
        }
        $this->data['titulok'] = SpravaPoziadaviekManazer::$kontroler['titulok'];
        $this->pohlad = 'menu'; // Nastavenie šablony
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
