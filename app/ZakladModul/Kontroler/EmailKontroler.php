<?php

namespace App\ZakladModul\Kontroler;

use App\ZakladModul\System\Kontroler\Kontroler;
use Nastavenia;

/**
 ** Spracuvava požiadavku pri odoslany emailu ... načita hlavný layout a hodi do neho údaje emailu
 * Class EmailKontroler
 * @package App\ZakladModul\Kontroler
 */
class EmailKontroler extends Kontroler
{

    /**
     *Načitanie hlavného rozlozenia emailu
     * @param object $kontroler Instancie vnoreného kontroléra teda emailu konkretného odosielača emailu
     */
    public function index($kontroler)
    {
        $this->data['kontroler'] = $kontroler;

        $this->data['domena'] = Nastavenia::$domena;
        $this->data['domenaNazov'] = Nastavenia::$domenaNazov;
        //nastavenie Šablony
        $this->pohlad = 'rozlozenie-email';
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