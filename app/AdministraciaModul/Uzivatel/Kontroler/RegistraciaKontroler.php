<?php


namespace App\AdministraciaModul\Uzivatel\Kontroler;

use App\AdministraciaModul\Uzivatel\Model\KrajinaManazer;
use App\AdministraciaModul\Uzivatel\Model\OsobaManazer;
use App\AdministraciaModul\Uzivatel\Model\UzivatelManazer;
use App\ZakladModul\SpravaPoziadaviek\Model\SpravaPoziadaviekManazer;
use App\ZakladModul\System\Kontroler\Kontroler;
use Micho\ChybaOchrany;
use Micho\ChybaUzivatela;
use Micho\ChybaValidacie;
use Micho\Formular\Formular;
use Micho\Formular\Input;
use Micho\Formular\Validator;
use Nastavenia;

/**
 ** Spracováva registráciu uživateľov
 * Class RegistraciaKontroler
 * @package App\AdministraciaModul\Uzivatel\Kontroler
 */
class RegistraciaKontroler extends Kontroler
{
    // Zakladné premenné s ktorými pracujem v registrácií/uprave osobnych údajov
    public static $osobaDetail = array(Formular::MENO, Formular::PRIEZVISKO, Formular::EMAIL, Formular::TEL);//,  Formular::POHLAVIE);
    public static $osobaAdresa = array(Formular::ULICA, Formular::SUPISNE_CISLO, Formular::MESTO, Formular::PSC, Formular::KRAJINA_ID);
    public static $uzivatel = array(OsobaManazer::OSOBA_ID, Formular::HESLO, 'heslo_znova', Formular::ANTISPAM, Formular::SUHLAS);

    /**
     ** Registruje nového uživateľa
     * @Action
     */
    public function index()
    {
        if (UzivatelManazer::$uzivatel) // upravit presmerovanie
            $this->presmeruj('administracia/osobne-udaje'); //ak je prihlasený tak presmeruje

        $osobaManazer = new OsobaManazer();

        //Vytvorenie formulára
        $formular = new Formular('registracia', array_merge(self::$osobaDetail, self::$uzivatel));
        $formular->upravParametreKontrolky(Formular::EMAIL, array(Input::NAZOV => 'Prihlasovací email'));
        $formular->pridajInput('Heslo znova','heslo_znova',Input::TYP_PASSWORD);

        $formular->pridajInput('Pin','pin',Input::TYP_TEXT,'','','form-control text-center','', true, Validator::PATTERN_PIN);

        //$formular->upravParametreKontrolky(Formular::KRAJINA_ID, array(Select::MOZNOSTI => $osobaManazer->vratKrajiny()));
        $formular->pridajSubmit('Registrovať', 'registrovat-tlacidlo', '', 'btn btn-outline-primary btn-block my-3');

        if ($formular->odoslany() && $formular->overCSRF())
        {
            $formularData = $formular->osetriHodnoty();
            try
            {
                if(isset($formularData[Formular::SUHLAS]) && $formularData['pin'] === '2468') // zistím či je zaškrtnutý súhlas a odoberem ho z poľa
                {
                    unset($formularData[Formular::SUHLAS]);
                    unset($formularData['pin']);

                    $formular->validuj($formularData, Formular::TYP_REGISTRACIA);

                    $osobaManazer->registruj($formularData);

                    $uzivatelManazer = new UzivatelManazer();
                    $uzivatelManazer->prihlas($formularData[Formular::EMAIL], $formularData[Formular::HESLO]);

                    $this->pridajSpravu('Boli ste úspešne zaregistrovaný. Na Váš email boli odoslané registračné údaje.', self::SPR_USPECH);
                    $this->pridajSpravu('Boli ste úspešne prihlásený.', self::SPR_USPECH);

                    $this->presmeruj('administracia/osobne-udaje'); // presmeruje na uvod
                }
                else
                {
                    $formular->nastavHodnotyKontrolkam($formularData);

                    $sprava =  $formularData['pin'] !== '2468' ? 'Nesprávny Pin' : 'Pre odoslanie formulára je potrebný súhlas so spracovaním osobných údajov.';

                    $this->pridajSpravu($sprava, self::SPR_INFO);
                }
            }
            catch (ChybaValidacie $chyba) // odchytáva chyby validácie
            {
                $formular->nastavHodnotyKontrolkam($formularData);
                $this->pridajSpravu($chyba->vratChyby(), self::SPR_CHYBA);
            }
            catch (ChybaUzivatela $chyba)
            {
                $formular->nastavHodnotyKontrolkam($formularData);
                $this->pridajSpravu($chyba->getMessage(),self::SPR_CHYBA);
            }
        }

        SpravaPoziadaviekManazer::$kontroler['titulok'] = 'Registrácia';
        $this->data['registracia'] = true;

        // Data pre Kontaktný formulár
        $this->data['formular'] = $formular->vratFormular();
        $this->pohlad = 'index';
    }

    /**
     ** Šablona pre odoslanie registračného emailu
     * @param array $emailData Data ktore chcem poslať emailom
     * @ Action
     */
    public function sablonaRegistraciaEmail($osobneUdaje)
    {/*
        // testovacie data
        $osobneUdaje = array('meno' => 'Michal', 'priezvisko' => 'Šimaľa', 'tel' => '0914278743', 'email' => 'simalmichal@gmail.com',  'pohlavie' => 'muz',
            'ulica' => 'ČČČimhová', 'supisne_cislo' => '109','mesto' => 'Čimhová', 'psc' => '02712', 'krajina_id' => '1', 'heslo' => 'fsdfds');
*/
        $osobaManazer = new OsobaManazer();
        $krajinaManazer = new KrajinaManazer();

        $krajina = $krajinaManazer->vratKrajinu($osobneUdaje[KrajinaManazer::KRAJINA_ID]);
        unset($osobneUdaje[KrajinaManazer::KRAJINA_ID]);
        $osobneUdaje['krajina'] = $krajina;

        $this->data['domenaNazov'] = Nastavenia::$domenaNazov;
        $this->data['domena'] = Nastavenia::$domena;
        $this->data['emailData'] = $osobneUdaje;

        $this->pohlad = 'registracia-email';
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