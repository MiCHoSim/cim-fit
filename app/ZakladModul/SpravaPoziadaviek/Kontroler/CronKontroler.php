<?php

namespace App\ZakladModul\SpravaPoziadaviek\Kontroler;

use App\RezervaciaModul\Model\PermanentkaManazer;
use App\ZakladModul\System\Kontroler\Kontroler;
use Micho\Db;


/**
 ** Spracuváva poziadavky na cron
 * Class Cron
 * @package App\ZakladModul\SpravaPoziadaviek\Kontroler
 */
class CronKontroler extends Kontroler
{

    /**
     ** Funkcia Cron, Aktualizuje hodnotu 'aktivna' pre tabuľku 'permanentka' v prípade ze je dátum v minulosti
     * @Action
     */
    public function casovaPermanentka()
    {
        Db::zmen(PermanentkaManazer::PERMANENTKA_TABULKA, array(PermanentkaManazer::AKTIVNA => 0), 'WHERE (aktivna AND zostatok_vstupov IS NULL AND datum < CURDATE()) || (aktivna AND zostatok_vstupov <= 0)');
    }

}


/*
 * Autor: MiCHo
 */