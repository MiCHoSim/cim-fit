<?php

namespace App\SutazModul\Model;


use App\AdministraciaModul\Uzivatel\Model\OsobaManazer;
use Micho\Db;
use Micho\Utility\DatumCas;
use Micho\Utility\Pole;

/**
 ** Metóda pokytujuce Spŕavu údajov pre Súťaže prihlasenie
 * Class SutazPrihlasenyManazer
 * @package App\SutazModul\Model
 */
class SutazPrihlasenyManazer
{
    /**
     * Názov Tabuľky pre Spracovanie sutaz typ
     */
    const SUTAZ_PRIHLASENY_TABULKA = 'sutaz_prihlaseny';

    /**
     * Konštanty Databázy 'sutaz_prihlaseny'
     */
    const SUTAZ_PRIHLASENY_ID = 'sutaz_prihlaseny_id';
    const SUTAZ_ID = SutazManazer::SUTAZ_ID;
    const OSOBA_ID = OsobaManazer::OSOBA_ID;

    /**
     ** Overí, či je osoba už prihlasná na konkrétnu sútaž
     * @param int $sutazId Id Súťaže
     * @param int $osobaId Id osoby
     * @return bool Či  je osoba už prihlasená na sútaž
     */
    public function overExistenciuPrihlasenia($sutazId, $osobaId)
    {
        return (bool) Db::dopytSamotny('SELECT COUNT(*) FROM sutaz_prihlaseny
                                              WHERE sutaz_id = ? AND osoba_id = ?',
            array($sutazId, $osobaId));
    }

    /**
     ** Uloží záznam o prihlasený  do DB
     * @param array $parametre
     */
    public function ulozPrihlasenie($parametre)
    {
        Db::vloz(self::SUTAZ_PRIHLASENY_TABULKA, $parametre);
    }

    /**
     ** Odstráni záznam o prihlasený  z DB
     * @param int $sutazId Id Súťaže
     * @param int $osobaId Id osoby
     * @return bool Či sa zaznam podarilo vymazaŤ
     */
    public function odstranPrihlasenie($sutazId, $osobaId)
    {
        return (bool) Db::dopyt('DELETE FROM sutaz_prihlaseny WHERE sutaz_id = ? AND osoba_id = ?', array($sutazId, $osobaId));
    }

    /**
     ** Načíta Učastnikov Súťaže
     * @param int $sutaz_id Id Súťaže
     * @return array|mixed
     */
    public function nacitajDetailUcastnikovSutaze($sutaz_id)
    {
        $kluce = array('meno', 'priezvisko', 'sutaz_prihlaseny_id', 'uzivatel_id');

        $dopyt = 'SELECT meno, priezvisko, sutaz_prihlaseny_id, uzivatel_id
                                            FROM sutaz_prihlaseny 
                                            JOIN osoba USING (osoba_id) 
                                            JOIN osoba_detail USING (osoba_detail_id) 
                                            WHERE sutaz_id = ? ORDER BY sutaz_prihlaseny_id';

        $data = Db::dopytVsetkyRiadky($dopyt, array($sutaz_id));

        return is_array($data) ? Pole::filtrujKluce($data, $kluce) : $data;
    }

    /**
     ** overí či sa na sútaŽ dá este prihlásiť/ odhlásiŤ
     * @param int $sutazID Id suťaŽe
     * @return bool či sa da alebo enda vykonať akciu
     */
    public function overMoznostAkcie($sutazID)
    {
        $sutazManazer = new SutazManazer();
        $datumPrihlasenie = $sutazManazer->vratSutazId($sutazID, array('datum_prihlasenie'))['datum_prihlasenie'];
        if (DatumCas::dbDatumTeraz() <= $datumPrihlasenie)
            return true;
        return false;
    }

}

/*
 * Autor: MiCHo
 */