<?php

namespace App\ZakladModul\SpravaPoziadaviek\Kontroler;

use App\AdministraciaModul\Administracia\Kontroler\AdministraciaKontroler;
use App\AdministraciaModul\Uzivatel\Model\UzivatelManazer;
use App\ZakladModul\SpravaPoziadaviek\Model\SpravaPoziadaviekManazer;
use App\AdministraciaModul\Uzivatel\Model\OsobaManazer;
use App\ZakladModul\System\Kontroler\Kontroler;

/**
 ** Spracováva požiadavky na kontrolér
 * Class SpravaPoziadaviekKontroler
 * @package App\ZakladModul\SpravaPoziadaviek\Kontroler
 */
class SpravaPoziadaviekKontroler extends Kontroler
{
    /**
     ** Načitanie vnoreného kontroléra
     * @param array $parametre Pole parametrov pre kontrolér, pokiaľ niejaké má
     */
    public function index($parametre)
    {
        if(($parametre[0] === 'clanok' && $parametre[1] !== 'clanky') || ($parametre[0] === 'sutaz' && (isset($parametre[1]) && ($parametre[1] !== 'prihlas') && $parametre[1] !== 'odhlas'))) // nechem zadavat do url index, tak ho pridam az tu a teda volam index metodu, ak su vsak clynky tak zobrazujem zoznam clankov
            $parametre = array_merge(array(array_shift($parametre),'index'), $parametre);

        $spravaPoziadaviekManazer = new SpravaPoziadaviekManazer(); // vytvorenie instancie modelu pre správu kontrolérov

        $kontrolerUrl = array_shift($parametre);
        $spravaPoziadaviekManazer->nacitajKontroler($kontrolerUrl); // ziskanie kontoléru podľa URL

        if (!SpravaPoziadaviekManazer::$kontroler) // pokiaľ nebol článok s danou URL najdeny, Presmeruje na ChybaKontrolér
            $this->presmeruj('chyba');


        $kontrolerCesta = 'App\\' . SpravaPoziadaviekManazer::$kontroler['kontroler'] . 'Kontroler';
        $this->kontroler = new $kontrolerCesta(); // instancia vnoreného kontroléra

        $this->kontroler->zavolajAkciuZParametrov($parametre);
        $this->data['titulok'] = SpravaPoziadaviekManazer::$kontroler['titulok'];

        $this->data['kontrolerUrl'] = $kontrolerUrl;
        //nastavenie Šablony
        $this->pohlad = 'index';
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
