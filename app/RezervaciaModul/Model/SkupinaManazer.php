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
 ** Správca Skupiny Spracováva skupinovú rezerváciu
 * Class GymManazer
 * @package App\KalendarModul\Model
 */
class SkupinaManazer
{
    /**
     * Názov Tabuľky pre Spracovanie skupina
     */
    const SKUPINA_TABULKA = 'skupina';

    /**
     * Konštanty Databázy 'skupina'
     */
    const SKUPINA_ID = 'skupina_id';
    const REZERVACIA_ID = RezervaciaManazer::REZERVACIA_ID;
    const OSOBA_ID = OsobaManazer::OSOBA_ID;

    /**
     * Uloži údaje do tabuľy skupina
     * @param array $osobyId Pole osoba_id ktore ukladam
     * @param int $rezervaciaId Id rezervacie ku ktorej skupina patrí
     * @return string Id posledne ulozenej rezervácie
     */
    public function ulozSkupinu(array $osobyId, $rezervaciaId)
    {
        foreach ($osobyId as $osobaId)
        {
            $skupiny[] = array(SkupinaManazer::REZERVACIA_ID => $rezervaciaId, SkupinaManazer::OSOBA_ID => $osobaId);
        }

        Db::vloz(self::SKUPINA_TABULKA, $skupiny);
    }


    /**
     ** Odstraneni záznamu osoby zo skupinového tréningu
     * @param int $skupinaId  Id zaznamu osoby v skupinovom tréningu
     * @param int $osobaId Id prihlasenej osoby
     * @return void
     */
    public function vymazOsobu($skupinaId, $osobaId)
    {
        // odstranenie a taktiez podmienky aby sa mohol odstraniť buď trener ktoreho to je alebo admin alebo programator
        $odstranenie = (bool) Db::dopyt('DELETE skupina FROM skupina
                                                JOIN rezervacia USING (rezervacia_id)
                                                JOIN osoba ON osoba.osoba_id = ?
                                                JOIN uzivatel USING (uzivatel_id)
                                                WHERE skupina_id = ? AND ((rezervacia.osoba_id = ? AND concat(datum, " ", cas_od) >= concat(CURDATE(), " ", CURTIME())) OR admin OR programator)', array($osobaId, $skupinaId, $osobaId));
        if(!$odstranenie)
            throw new PDOException();
    }

}
/*
 * Autor: MiCHo
 */