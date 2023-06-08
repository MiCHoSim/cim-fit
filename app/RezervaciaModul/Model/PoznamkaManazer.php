<?php

namespace App\RezervaciaModul\Model;

use App\AdministraciaModul\Uzivatel\Model\OsobaDetailManazer;
use App\AdministraciaModul\Uzivatel\Model\OsobaManazer;
use App\AdministraciaModul\Uzivatel\Model\TrenerManazer;
use App\AdministraciaModul\Uzivatel\Model\UzivatelManazer;
use App\ZakladModul\System\Kontroler\Kontroler;
use Micho\Cas;
use Micho\ChybaKalendar;
use Micho\ChybaUzivatela;
use Micho\Db;
use Micho\Formular\Formular;
use Micho\Kalendar;
use Micho\Utility\DatumCas;
use Micho\Utility\Pole;
use Micho\Utility\Retazec;
use DateTime;
use DialogPomocne;

/**
 ** Spracováva poznamku skupinovej rezervácie
 * Class PoznamkaManazer
 * @package App\RezervaciaModul\Model
 */
class PoznamkaManazer
{
    /**
     * Názov Tabuľky pre Spracovanie poznamka
     */
    const POZNAMKA_TABULKA = 'poznamka';

    /**
     * Konštanty Databázy 'poznamka'
     */
    const POZNAMKA_ID = 'poznamka_id';
    const REZERVACIA_ID = RezervaciaManazer::REZERVACIA_ID;
    const POZNAMKA = 'poznamka';

    /**
     * Uloží poznámku do tabulky
     * @param string $poznamka
     * @param $rezervaciaId
     */
    public function ulozPoznamku(string $poznamka, $rezervaciaId)
    {
        $poznamka = array(self::REZERVACIA_ID => $rezervaciaId, self::POZNAMKA => $poznamka);

        Db::vloz(self::POZNAMKA_TABULKA, $poznamka);
    }
}


/*
 * Autor: MiCHo
 */