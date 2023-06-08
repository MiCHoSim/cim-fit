<?php


namespace App\ZakladModul\System\Kontroler;

use App\ZakladModul\SpravaPoziadaviek\Model\SpravaPoziadaviekManazer;
use Nastavenia;

/**
 ** Spracovava chybovú stránku
 * Class ChybaKontroler
 * @package App\ZakladModul\System\Kontroler
 */
class ChybaKontroler extends Kontroler
{
    /**
     ** Odošle chybovú hlavičku
     * @Action
     */
    public function index()
    {
        // hlavička požiadavky
        header('HTTP/1.0 404 Not Found');

        SpravaPoziadaviekManazer::$kontroler['titulok'] = '';

        $this->data['domenaNazov'] = Nastavenia::$domenaNazov;

        $this->pohlad = "index";
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