<?php

namespace App\AdministraciaModul\Uzivatel\Kontroler;

use App\AdministraciaModul\Uzivatel\Model\OsobaDetailManazer;
use App\AdministraciaModul\Uzivatel\Model\OsobaManazer;
use App\AdministraciaModul\Uzivatel\Model\UzivatelManazer;
use App\ZakladModul\SpravaPoziadaviek\Model\SpravaPoziadaviekManazer;
use App\ZakladModul\System\Kontroler\Kontroler;
use Micho\ChybaOchrany;
use Micho\ChybaUzivatela;
use Micho\ChybaValidacie;
use Micho\Formular\Formular;
use Micho\Formular\Input;
use Nastavenia;

/**
 ** Spracovava prihlasovanie uživateľov
 * Class PrihlasenieKontroler
 * @package App\AdministraciaModul\Uzivatel\Kontroler
 */
class PrihlasenieKontroler extends Kontroler
{
    /**
     ** Prihlási uživateľa
     * @ Action Action oddelené o @ čim je znefunkčné, kvôli tomu, aby sa nemohlo volať URL
     */
    public function neprihlasenyMenu()
    {
        $uzivatelManazer = new UzivatelManazer();

        $formular = new Formular('prihlasit', array(Formular::EMAIL, Formular::HESLO));
        $formular->upravParametreKontrolky(Formular::EMAIL, array(Input::TRIEDA => 'form-control mr-1'));
        $formular->upravParametreKontrolky(Formular::HESLO, array(Input::TRIEDA => 'form-control mr-1'));
        $formular->pridajSubmit('Prihlásiť', 'prihlasit-tlacidlo', '', 'btn btn-outline-primary mt-1 mt-sm-0');
        if ($formular->odoslany() && $formular->overCSRF())
        {
            $formularData = $formular->osetriHodnoty();

            try
            {
                $formular->validuj($formularData);
                $uzivatelManazer->prihlas($formularData[Formular::EMAIL], $formularData[Formular::HESLO]);

                session_regenerate_id(true); // Po prihlasení zemním SID // Ochrana => "Session hijacking -> session fixation"

                $this->pridajSpravu('Boli ste úspešne prihlásený.', self::SPR_USPECH);
                $this->presmeruj(self::$aktualnaUrl ? '' : (UzivatelManazer::$uzivatel[UzivatelManazer::ADMIN] ? 'administracia/sprava-uzivatelov/rezervacie/dnes' : 'administracia/osobne-udaje'));
            }
            catch (ChybaUzivatela $chyba)
            {
                $this->pridajSpravu($chyba->getMessage(), self::SPR_CHYBA);
                $this->presmeruj(); // musim presmerovat kvoli vygenerovnaiu noveho CSRF
            }
        }
        $this->data['formular'] = $formular->vratFormular();
        $this->pohlad = 'neprihlasenyMenu';
    }
    /**
     ** Zobrazúje administračné menu namiesto prihlasovacieho menu
     * @ Action Action oddelené o @ čim je znefunkčné, kvôli tomu, aby sa nemohlo volať URL
     */
    public function prihlasenyMenu()
    {
        $this->data['presmeruj'] = mb_strpos(self::$aktualnaUrl, 'administracia') === false ? self::$aktualnaUrl : ' '; // ak je administrácia tak rpesmerujem na úvod, lebo inak mi vyhodý dve hlášky
        $this->data['meno'] = UzivatelManazer::$uzivatel[OsobaDetailManazer::MENO] . ' ';// . ' <small>(' .UzivatelManazer::$uzivatel[OsobaDetailManazer::EMAIL] . ')</small>';
        $this->pohlad = 'prihlasenyMenu';
    }

    /**
     ** Odhlásenie uživateľa
     * @Action
     */
    public function odhlasit()
    {
        $uzivatelManazer = new UzivatelManazer();
        $uzivatelManazer->odhlas();
        $this->pridajSpravu('Boli ste úspešne odhlásený.', self::SPR_USPECH);
        $this->presmeruj();
    }

    /**
     ** Spracovanie nového hesla
     * @Action
     */
    public function zabudnuteHeslo() //upraviť
    {

        if (UzivatelManazer::$uzivatel) // upravit presmerovanie
            $this->presmeruj('administracia/osobne-udaje'); //ak je prihláseny tak presmeruje

        //Vytvorenie formulára
        $formular = new Formular('zabudnute-heslo', array(Formular::EMAIL));
        $formular->upravParametreKontrolky(Formular::EMAIL, array(Input::NAZOV => 'Prihlasovací email', Input::TRIEDA => 'form-control text-center'));
        $formular->pridajSubmit('Odoslať nové heslo na email', 'zabudnute-heslo-tlacidlo', '', 'btn btn-outline-primary btn-block my-3');

        if ($formular->odoslany() && $formular->overCSRF())
        {
            $formularData = $formular->osetriHodnoty();
            $uzivatelManazer = new UzivatelManazer();
            try
            {
                $formular->validuj($formularData, Formular::TYP_ZABUDNUTE_HESLO);

                $uzivatelManazer->odosliStrateneHesloEmail($formularData[Formular::EMAIL]);
                $this->pridajSpravu('Nové heslo bolo zaslané na Váš email: ' . $formularData[Formular::EMAIL] . '.', self::SPR_USPECH);
                $this->presmeruj();
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
        SpravaPoziadaviekManazer::$kontroler['titulok'] = 'Zabudnuté heslo';
        SpravaPoziadaviekManazer::$kontroler['popisok'] = 'Zabudnute heslo, generovanie hesla, nové heslo';

        $this->data['formular'] = $formular->vratFormular();
        $this->pohlad = 'zabudnute-heslo';
    }

    /**
     ** akcia vyplni sablonu pre stratené heslo a odosle ju ako email
     * @param $objednavkaId
     * @param $sprava
     * @ Action
     */
    public function sablonaZabudnuteHesloEmail($email, $heslo)
    {
        //data pre pohlad
        $this->data['email'] = $email;
        $this->data[UzivatelManazer::HESLO] = $heslo;

        $this->data['domenaNazov'] = Nastavenia::$domenaNazov;
        $this->data['domena'] = Nastavenia::$domena;

        $this->pohlad = 'zabudnute-heslo-email';
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
