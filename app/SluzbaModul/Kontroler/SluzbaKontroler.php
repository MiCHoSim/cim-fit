<?php

namespace App\SluzbaModul\Kontroler;


use App\AdministraciaModul\Uzivatel\Model\UzivatelManazer;
use App\ClanokModul\Kontroler\ClanokKontroler;
use App\ClanokModul\Model\ClanokTypManazer;
use App\RezervaciaModul\Kontroler\RezervaciaKontroler;
use App\SluzbaModul\Model\SluzbaManazer;
use App\ZakladModul\SpravaPoziadaviek\Model\SpravaPoziadaviekManazer;
use App\ZakladModul\System\Kontroler\Kontroler;
use Micho\Subory\Priecinok;
use Micho\Subory\Subor;

/**
 * Class SluzbaKontroler
 * @package App\SluzbyModul\Kontroler
 */
class SluzbaKontroler extends Kontroler
{
    /**
     ** Spracovenie stránky pre gym
     * @param string $url url sekcie  článok/ rezervácia, ...
     * @param null $rok Vybraty Rok prípade rezervácii
     * @param null $mesiac Vybraty Mesiac prípade rezervácii
     * @param null $den Vybraty Deň prípade rezervácii
     * @throws \Exception
     * @Action
     */
    public function gym($url = 'gym', $rok = null, $mesiac = null, $den = null)
    {
        $typSluzby = (explode('/', self::$aktualnaUrl))[1]; // zistenie ze som naozaj v gym

        if($url === 'gym' || $url === 'gym-ceny') // nacitavam aj článok
        {
            $clanokKontroler = new ClanokKontroler(); // Načitanie článku
            $clanokKontroler->index($url === 'gym' ? $url : (UzivatelManazer::$uzivatel ? $url : 'gym-ceny-neprihlaseny')); // cenik aj z cenami sa zobrazuje iba prihlasenému uzivatelovy
            $this->data['clanokKontroler'] = $clanokKontroler;

            if($url === 'gym')
            {
                $this->data['cestaObrazok'] = 'obrazky/galeria/gym/nahlad';
                $this->data['galeria'] = Subor::vratNazvySuborov($this->data['cestaObrazok']);
            }
        }
        elseif($url === 'rezervacia')
        {
            $rezervaciaKontroler = new RezervaciaKontroler($typSluzby, $rok, $mesiac, $den);
            $rezervaciaKontroler->rezervacia();
            $this->data['rezervacia'] = $rezervaciaKontroler;
        }
        else
            $this->presmeruj('chyba');

        $this->data['menu'] = (new SluzbaManazer())->zostavMenu($url, $typSluzby);
        $this->pohlad = 'gym';
    }

    /**
     ** Spracovenie stránky pre Bobbyho
     * @param string $url url sekcie  článok/ rezervácia, ...
     * @param null $rok Vybraty Rok prípade rezervácii
     * @param null $mesiac Vybraty Mesiac prípade rezervácii
     * @param null $den Vybraty Deň prípade rezervácii
     * @Action
     */
    public function bobby($url = 'bobby')//, $clanky = false)
    {
        $clanokKontroler = new ClanokKontroler(); // Načitanie článku

        $typSluzby = (explode('/', self::$aktualnaUrl))[1]; // zistenie ze som naozaj v gym

        if($url === 'bobby' || $url === 'bobby-ceny') // nacitavam aj článokclano
        {
            $clanokKontroler->index($url === 'bobby' ? $url : (UzivatelManazer::$uzivatel ? $url : 'bobby-ceny-neprihlaseny')); // cenik aj z cenami sa zobrazuje iba prihlasenému uzivatelovy

            if($url === 'bobby')
                SpravaPoziadaviekManazer::$kontroler['titulok'] = 'BobbyBlúú';

        }
        elseif($url === 'trening' || $url === 'strava') // naČita zoznam článkov o treningoch alebo strave
        {
            $clanokKontroler->clanky($url);
            SpravaPoziadaviekManazer::$kontroler['titulok'] = ClanokTypManazer::TYPY_CLANKOV_URL_NAZOV[$url];
        }
        else
            $this->presmeruj('chyba');

        $this->data['menu'] = (new SluzbaManazer())->zostavMenu($url, $typSluzby);
        $this->data['clanokKontroler'] = $clanokKontroler;

        $this->pohlad = 'bobby';
    }

    /**
     *
     * @param string $url Url prave navstivenej Adresy
     */
    /**
     ** Zostavý menu
     * @param string $url url podstranky sluzi pre zvirazenie prave nacitanej stranky menu
     * @param string $typ Url prave navstivenej Adresy/ metody daneho kontrolera
     */
    public function menu($url, $typ)
    {
        if ($typ === 'gym')
        {
            $this->data['menu'] = array('Gym' => array('url' => 'sluzba/gym', 'aktivna' => $url === 'gym' ? true : false),
                                        'Ceny & služby' => array('url' => 'sluzba/gym/gym-ceny', 'aktivna' => $url === 'gym-ceny' ? true : false),
                                        'Rezervačný kalendár' => array('url' => 'sluzba/gym/rezervacia', 'aktivna' => $url === 'rezervacia' ? true : false));
        }
        if ($typ === 'bobby')
        {
            $this->data['menu'] = array('O mne' => array('url' => 'sluzba/bobby', 'aktivna' => $url === 'bobby' ? true : false),
                                        'Ceny & služby' => array('url' => 'sluzba/bobby/bobby-ceny', 'aktivna' => $url === 'bobby-ceny' ? true : false),
                                        'Tréning' => array('url' => 'sluzba/bobby/trening', 'aktivna' => $url === 'trening' ? true : false),
                                        'Strava' => array('url' => 'sluzba/bobby/strava', 'aktivna' => $url === 'strava' ? true : false),
                );
        }

        $this->data['titulok'] = $url === 'rezervacia' ? '' : SpravaPoziadaviekManazer::$kontroler['titulok'];
        $this->pohlad = 'menu'; // Nastavenie šablony
    }
}

/*
 * Niektoré časti sú upravené
 * Autor: MiCHo
 */