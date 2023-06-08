<?php

namespace App\ZakladModul\Kontroler;

use App\ZakladModul\Model\KontaktManazer;
use App\ZakladModul\System\Kontroler\Kontroler;
use Micho\ChybaOchrany;
use Micho\ChybaUzivatela;
use Micho\ChybaValidacie;
use Micho\Formular\Formular;

use Micho\Formular\Input;
use Micho\Formular\Validator;
use Nastavenia;

/**
 ** Spracováva kontaktný formulár
 * Class KontaktKontroler
 * @package App\ZakladModul\Kontroler\KontaktKontroler
 */
class KontaktKontroler extends Kontroler
{
    /**
     ** Spracuje kontaktný formulár
     * @ Action Action oddelené o @ čim je znefunkčné, kvôli tomu, aby sa nemohlo volať URL
     */
    public function index()
    {
        $kontaktManazer = new KontaktManazer();

        //Vytvorenie formulára
        $formular = new Formular('kontakt', array(Formular::MENO, Formular::PRIEZVISKO, Formular::TEL, Formular::SPRAVA, Formular::ANTISPAM, Formular::SUHLAS));

        $formular->pridajInput('Email','kontakt_email', Input::TYP_EMAIL,'', '', '', '',true, Validator::PATTERN_EMAIL);

        $formular->pridajSubmit('Odoslať', 'kontakt-tlacidlo');

        if ($formular->odoslany() && $formular->overCSRF())
        {
            $formularData = $formular->osetriHodnoty();
            try
            {
                if(isset($formularData[Formular::SUHLAS]) && array_pop($formularData)) // zistím či je zaškrtnutý súhlas a odoberem ho z pola
                {
                    $formular->validuj($formularData);

                    $kontaktManazer->odosliKontaktnyEmail($formularData);

                    $this->pridajSpravu('Email bol úspešne odoslaný.', self::SPR_USPECH);
                    $this->presmeruj();
                }
                else
                {
                    $this->pridajSpravu('Pre odoslanie formulára je potrebný súhlas so spracovaním osobných údajov.', self::SPR_INFO);
                }
            }
            catch (ChybaValidacie $chyba)
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
        // Data pre Informácie o kontakte
        $this->data['kontakty'] = $kontaktManazer->nacitajInfoKontakt();

        // Data pre Kontaktný formulár
        $this->data['formular'] = $formular->vratFormular();

        $this->data['facebook'] = Nastavenia::$facebook;
        $this->data['instagram'] = Nastavenia::$instagram;

        $this->pohlad = 'index';  //nastavenie pohladu
    }

    /**
     ** Šablona pre odoslanie kontaktného emailu
     * @param array $emailData Data ktore chcem poslať emailom
     * @ Action
     */
    public function sablonaKontaktEmail($emailData)
    {
        $kontaktManazer = new KontaktManazer();
/*
         //testovacie data
        $emailData = array('meno' => 'Joži',
                            'priezvisko' => 'Podbrezovnik',
                            'email' => 'jozi.podbrezovnik@gmail.com',
                            'tel' => '0914278745',
                            'sprava' => 'Je to tu fajno');
*/
        $this->data['domenaNazov'] = Nastavenia::$domenaNazov;
        $this->data['domena'] = Nastavenia::$domena;
        $this->data['emailData'] = $emailData;
        $this->data['kontakty'] = $kontaktManazer->nacitajInfoKontakt();// Data pre Informácie o kontakte


        $this->pohlad = 'kontakt-email';
    }

    /**
     ** Šablona pre odoslanie skupinového emailu
     * @param string $sprava sprava
     * @ Action
     */
    public function sablonaSkupinovyEmail($sprava)
    {
        $this->data['domenaNazov'] = Nastavenia::$domenaNazov;
        $this->data['domena'] = Nastavenia::$domena;
        $this->data['sprava'] = $sprava;

        $this->pohlad = 'skupinovy-email';
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
