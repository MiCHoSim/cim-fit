<?php

namespace App\RezervaciaModul\Kontroler;

use App\AdministraciaModul\Uzivatel\Kontroler\RegistraciaKontroler;
use App\AdministraciaModul\Uzivatel\Model\OsobaManazer;
use App\AdministraciaModul\Uzivatel\Model\UzivatelManazer;
use App\ClanokModul\Model\ClanokManazer;
use App\RezervaciaModul\Model\PermanentkaManazer;
use App\RezervaciaModul\Model\PermanentkaTypManazer;
use App\RezervaciaModul\Model\RezervaciaManazer;
use App\RezervaciaModul\Model\SkupinaManazer;
use App\ZakladModul\SpravaPoziadaviek\Model\SpravaPoziadaviekManazer;
use App\ZakladModul\System\Kontroler\Kontroler;
use Micho\Cas;
use Micho\ChybaKalendar;
use Micho\ChybaOchrany;
use Micho\ChybaUzivatela;
use Micho\ChybaValidacie;
use Micho\Formular\Formular;
use Micho\Kalendar;
use DateTime;
use PDOException;


/**
 ** Spracuje rezervaciu
 * Class RezervaciaKontroler
 * @package App\Rezervacia\Modul\Kontroler
 */
class RezervaciaKontroler extends Kontroler
{
    private $typ;
    private $rok;
    private $mesiac;
    private $den;
    private $rezervaciaManazer;

    /**
     * časý v ktorých sa da rezervovať termín
     */
    const START = 5;
    const KONIEC = 23;
    const KROK = 30;

    private $cas;

    /**
     * RezervaciaKontroler constructor.
     * @param string $typ Typ o aku rezervaciu sa jedná ... teda gym ,... kvoli presmerovaniu ale taktiez kvoli ulozeniu do db
     * @param null $rok Vybratý rok
     * @param null $mesiac Vybratý mesiac
     * @param null $den VYbratý deň
     */
    public function __construct($typ = false, $rok = null, $mesiac = null, $den = null)
    {
        if ((!$rok || !$mesiac || !$den || !in_array($rok, Kalendar::ROKY)) && $typ) // ak nieje zadané v url tak zadam dnešne údaje.. ten typ kvoli tomu aby mi prešlo odstránenie rezervacie
            $this->presmeruj('sluzba/' . $typ . '/rezervacia/' . (new DateTime())->format('Y/n/j'));

        $this->typ = $typ;
        $this->rok = $rok;
        $this->mesiac = $mesiac;
        $this->den = $den;
        try
        {
            $this->rezervaciaManazer = new RezervaciaManazer($this->rok, $this->mesiac, $this->den);
        }
        catch (ChybaKalendar $chyba)
        {
            $this->pridajSpravu($chyba->getMessage(), self::SPR_CHYBA);
            $this->presmeruj('sluzba/' . $this->typ . '/rezervacia/');
        }
    }

    /**
     ** Spracovanie komplet rezervácie
     * @throws \Exception
     */
    public function rezervacia()
    {
        //Spracovanie zobrazenia kalendára -->
        $kalendar = $this->vytvorKalendar();

        $rezervacieHtml = null;
        if(!empty(UzivatelManazer::$uzivatel)) // doČastné obmedzenie ... kalendar a rezerváciu moŽe vykonať len registovaný uživateľ
        {
            //Spracovanie rezervacie konkretného času -->

            $this->cas = new Cas(self::START,self::KONIEC,self::KROK);
            $vybratyDatum = $this->rezervaciaManazer->kalendar->vratVybratyDatum('Y-m-d');

            $this->data['permanentka'] = (new PermanentkaManazer())->nacitajAktivnuPermanentku(array(PermanentkaTypManazer::NAZOV, PermanentkaManazer::DATUM, PermanentkaManazer::ZOSTATOK_VSTUPOV), UzivatelManazer::$uzivatel[UzivatelManazer::UZIVATEL_ID]);
            // Nacitanie Rezervácií -->

            $rezervacie = $this->rezervaciaManazer->zostavHtmlRezervacie($vybratyDatum, $this->typ, $this->cas->generujCasyVypis(self::START,00,self::KONIEC,self::KROK));

            $rezervacieHtml = $rezervacie['html'];

            $this->spracujRezervaciu($vybratyDatum, $rezervacie['vynechaneCasy']);

        }

        SpravaPoziadaviekManazer::$kontroler['titulok'] = 'Rezervačný kalendár';
        SpravaPoziadaviekManazer::$kontroler['popisok'] = 'rezervácia, kalendár, rezervačný kalendár';

        $this->data['formularKalendar'] = $kalendar['formularKalendar'];
        $this->data['rezervaciaGymKalendar'] = $kalendar['rezervaciaGymKalendar'];

        $this->data['aktualnyDatum'] = $this->rezervaciaManazer->kalendar->vratAktualnyDatum();
        $this->data['vybratyDatum'] = $this->rezervaciaManazer->kalendar->vratVybratyDatum();

        $this->data['prihlaseny'] = !empty(UzivatelManazer::$uzivatel[UzivatelManazer::UZIVATEL_ID]);

        $this->data['rezervacieHtml'] = $rezervacieHtml;

        $this->data['uzivatel'] = UzivatelManazer::$uzivatel;
        $this->data['typ'] = $this->typ;

        $this->pohlad = 'rezervacia';
    }

    /**
     * Vytvorí rezervaČný kalendár ... zobrazenie vrátane fomuláru na výber rok/mesiac
     */
    private function vytvorKalendar()
    {
        $rezervaciaGymKalendar = $this->rezervaciaManazer->zostavHtmlKalendar($this->typ);


        $formularKalendar = new Formular('kalendar');
        $formularKalendar->pridajSelect('Rok','rok',Kalendar::ROKY, $this->rezervaciaManazer->kalendar->vratVybratyRok());
        $formularKalendar->pridajSelect('Mesiac','mesiac',Kalendar::MESIACE, $this->rezervaciaManazer->kalendar->vratVybratyMesiac());

        $formularKalendar->pridajSubmit('Zobraziť','zobrazit','','btn btn-outline-primary');

        if ($formularKalendar->odoslany()) // je odoslaný formulár
        {
            $formularKalendarData = $formularKalendar->osetriHodnoty();
            $this->presmeruj('sluzba/' . $this->typ . '/rezervacia/' . $formularKalendarData['rok'] . '/' . $formularKalendarData['mesiac'] . '/' . $this->rezervaciaManazer->kalendar->vratVybratyDen());
        }

        return array('formularKalendar' => $formularKalendar->vratFormular(), 'rezervaciaGymKalendar' => $rezervaciaGymKalendar);
    }

    /**
     ** Sppracuje žiadosť o rezerváciu vrátane uloženia do DB
     * @param string $vybratyDatum Vybratý dátum
     * @param array $vynechaneCasy Časy kotré nechem davať v ponuke výberu
     */
    private function spracujRezervaciu($vybratyDatum, $vynechaneCasy)
    {
        $casy = $this->cas->generujCasy($vybratyDatum, $vynechaneCasy);

        $formularRezervacia = false; // ak generujem casy tak generujem aj formula inak nie
        if($casy)
        {
            $formularRezervacia = new Formular('rezervovat');//empty(UzivatelManazer::$uzivatel['uzivatel_id']) ? array_merge(RegistraciaKontroler::$osobaDetail, array(Formular::ANTISPAM, Formular::SUHLAS)) : array()); // ak nieje prihlaseny gernerujem kontrolky
            $formularRezervacia->pridajSelect('Príchod',RezervaciaManazer::CAS_OD, $casy['cas_od'],'',false);
            $formularRezervacia->pridajSelect('Odchod',RezervaciaManazer::CAS_DO, $casy['cas_do'],'',false);
            $formularRezervacia->pridajSubmit('Rezervovať', 'rezervovat-tlacidlo', '', 'btn btn-outline-primary btn-block my-3');
            if ($formularRezervacia->odoslany()) // je odoslaný formulár
            {
                $formularRezervaciaData = $formularRezervacia->osetriHodnoty();
                try
                {
                    if (isset($formularRezervaciaData[Formular::SUHLAS]) || !empty(UzivatelManazer::$uzivatel[UzivatelManazer::UZIVATEL_ID])) // zistím či je zaškrtnutý súhlas a
                    {
                        unset($formularRezervaciaData[Formular::SUHLAS]); //odoberem ho z poľa

                        $formularRezervacia->validuj($formularRezervaciaData);

                        $formularRezervaciaData[RezervaciaManazer::DATUM] = $vybratyDatum;
                        $this->rezervaciaManazer->rezervovat($formularRezervaciaData);

                        // zostavenie správy
                        $sprava = '<span class="text-nowrap">Vaša rezervácia bola prijatá.</span> ';//<span class="d-inline-block text-dark mx-2 p-2 bg-warning border border-dark rounded">Permanentka: ';
                        //$permanentkaManazer = new PermanentkaManazer();

                       // $sprava .= $permanentkaManazer->zostavSpravu($this->data['permanentka']);
                        $sprava .= '</span><span class="text-nowrap">Tešíme sa na Vašu návštevu!</span>';

                        $this->pridajSpravu($sprava, self::SPR_USPECH);
                        $this->presmeruj();
                    }
                    else
                    {
                        $formularRezervacia->nastavHodnotyKontrolkam($formularRezervaciaData);
                        $this->pridajSpravu('Pre odoslanie formulára je potrebný súhlas so spracovaním osobných údajov.', self::SPR_INFO);
                    }
                }
                catch (ChybaValidacie $chyba) // odchytáva chyby validácie
                {
                    $formularRezervacia->nastavHodnotyKontrolkam($formularRezervaciaData);
                    $this->pridajSpravu($chyba->vratChyby(), self::SPR_CHYBA);
                }
                catch (ChybaUzivatela $chyba)
                {
                    $formularRezervacia->nastavHodnotyKontrolkam($formularRezervaciaData);
                    $this->pridajSpravu($chyba->getMessage(), self::SPR_INFO);
                }
            }
        }
        $this->data['formularRezervacia'] = $formularRezervacia ? $formularRezervacia->vratFormular() : false;
    }

    /**
     * Spracuje požiadavku na zrušenie rezervácie
     * @param int $rezervaciaId Id rezervácie ktoru odstraňujem
     * @throws \Exception
     * @Action
     */
    public function zrusRezervaciu(int $rezervaciaId)
    {
        $this->overUzivatela(); // uživateľ musí byť prihlasený

        $osobaId = (new OsobaManazer())->vratOsobaId(UzivatelManazer::$uzivatel[UzivatelManazer::UZIVATEL_ID]); // Id prihlaseného uživateľa
        $rezervaciaManazer = new RezervaciaManazer();

        try
        {
            $rezervaciaManazer->odstranRezervaciu($rezervaciaId, $osobaId);
            $this->pridajSpravu('Rezervácia bola zrušená.', self::SPR_USPECH);
        }
        catch (PDOException $chy)
        {
            //echo $chy->getMessage();die;
            $this->pridajSpravu('Nemáte povolenie na túto operáciu.', self::SPR_CHYBA);
        }

        $this->presmeruj();
    }

    /**
     * Spracuje požiadavku na Odstranenie záznamu osoby zo skupinového tréningu
     * @param int $skupinaId Id zaznamu skupiny osoby ktoru chem odstránit
     * @throws \Exception
     * @Action
     */
    public function vymazOsobu(int $skupinaId)
    {
        $this->overUzivatela(); // uživateľ musí byť prihlasený

        $osobaId = (new OsobaManazer())->vratOsobaId(UzivatelManazer::$uzivatel[UzivatelManazer::UZIVATEL_ID]); // Id prihlaseného uživateľa
        $skupinaManazer = new SkupinaManazer();

        try
        {
            $skupinaManazer->vymazOsobu($skupinaId, $osobaId);
            $this->pridajSpravu('Osoba bola vymazaná zo skupinového tréningu.', self::SPR_USPECH);
        }
        catch (PDOException $chy)
        {
            $this->pridajSpravu('Nemáte povolenie na túto operáciu.', self::SPR_CHYBA);
        }

        $this->presmeruj();
    }
}

/*
 * Autor: MiCHo
 */
