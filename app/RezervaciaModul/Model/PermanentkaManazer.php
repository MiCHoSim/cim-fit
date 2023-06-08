<?php

namespace App\RezervaciaModul\Model;

use App\AdministraciaModul\Uzivatel\Model\OsobaDetailManazer;
use App\AdministraciaModul\Uzivatel\Model\OsobaManazer;
use App\AdministraciaModul\Uzivatel\Model\UzivatelManazer;
use Micho\Db;
use DateTime;
use Micho\Utility\DatumCas;
use Micho\Utility\Pole;


/**
 ** Správca permanentiek
 * Class GymManazer
 * @package App\KalendarModul\Model
 */

/**
 ** Správca permanentiek
 * Class PermanentkaManazer
 * @package App\PermanentkaModul\Model
 */
class PermanentkaManazer
{
    /**
     * Názov Tabuľky pre Spracovanie permanentky
     */
    const PERMANENTKA_TABULKA = 'permanentka';

    /**
     * Konštanty Databázy 'permanentka'
     */
    const PERMANENTKA_ID = 'permanentka_id';
    const PERMANENTKA_TYP_ID = PermanentkaTypManazer::PERMANENTKA_TYP_ID;
    const DATUM = 'datum';
    const DATUM_ZNEAKTIVNENIA = 'datum_zneaktivnenia';
    const AKTIVNA = 'aktivna';
    const OSOBA_ID = OsobaManazer::OSOBA_ID;
    const ZOSTATOK_VSTUPOV = 'zostatok_vstupov';
    const POTVRDENIE_PREPADNUTIA = 'potvrdenie_prepadnutia';

    /**
     ** Uloži permanentku
     * @param array $data Pole hodnot na uloženie
     */
    public function ulozPermanentku($data)
    {
        $platnostDo = new DateTime($data['datum_od']);
        unset($data['datum_od']);

        switch ($data[self::PERMANENTKA_TYP_ID])
        {
            case 1:
                $platnostDo->modify('+ 6 day');
                break;
            case 2:
                $data[self::ZOSTATOK_VSTUPOV] = 10;
                break;
            case 3:
                $platnostDo->modify('+ 1 month - 1 day');
                break;
            case 4:
                $platnostDo->modify('+ 6 month - 1 day');
                break;
        }
        $data['datum'] = $platnostDo->format(DatumCas::DB_DATUM_FORMAT);

        $aktulanyDatum = new DateTime('today');

        if($platnostDo >= $aktulanyDatum || $data[self::PERMANENTKA_TYP_ID] == PermanentkaTypManazer::DESAT_VSTUPOVA)
            $data[self::AKTIVNA] = 1;

        Db::vloz(self::PERMANENTKA_TABULKA, $data);
    }

    /**
     ** Odstráni permanentku z DB
     * @param int $permanentkaId Id permanentky
     */
    public function odstranPermanentku($permanentkaId)
    {
        Db::dopyt('DELETE FROM permanentka WHERE permanentka_id = ?', array($permanentkaId));
    }

    /**
     ** Vráti Aktivnu alebo naposledý aktívnu permanentku
     * @param array $poleHodnot Pole hodnôt, ktoré chcem z DB získať
     * @param int $uzivatelId Id uživateľa, ktorého permanentku načítávam
     * @return array|false|int|mixed Načitana permanentka
     */
    public function nacitajPermanentku(array $poleHodnot, $uzivatelId)
    {
        $permanentka = $this->nacitajAktivnuPermanentku($poleHodnot, $uzivatelId);
        if(!empty($permanentka)) // ak existuje aktivna permanentka tka ju vrátim
            return $permanentka;

        // ak neexistuje aktivna permanentka tak sa pokusim načítať posledne aktívnu permanentku
        return $this->nacitajPosledneAktivnuPermanentku($poleHodnot, $uzivatelId);
    }

    /**
     ** Načíta aktívnu permanentku podľa id uživateľa
     * @param array $poleHodnot Pole hodnôt, ktoré chcem z DB získať
     * @param int $uzivatelId Id uživateľa, ktorého permanentku načítávam
     * @return array|false|int|mixed Načitana permanentka alebu null v pridane ze nieje najdena žiadna permanentka
     */
    public function nacitajAktivnuPermanentku($poleHodnot, $uzivatelId)
    {
        $poleHodnot = array_unique(array_merge($poleHodnot, array(self::PERMANENTKA_ID, self::PERMANENTKA_TYP_ID, self::DATUM, self::OSOBA_ID, self::ZOSTATOK_VSTUPOV)));
        $data = Db::dopytJedenRiadok('SELECT ' . implode(', ', $poleHodnot) . '
                                            FROM permanentka
                                            JOIN permanentka_typ USING (permanentka_typ_id)
                                            JOIN osoba USING (osoba_id)
                                            WHERE uzivatel_id = ? AND aktivna', array($uzivatelId));
        $permanentka =  is_array($data) ? array_intersect_key($data, array_flip($poleHodnot)) : $data;

        if($permanentka && $permanentka[self::PERMANENTKA_TYP_ID] == PermanentkaTypManazer::DESAT_VSTUPOVA) // ak je typ 10 vstupovej tak ju prepocitava
            $permanentka = $this->prepocitajZostatokVstupov($permanentka); // vráti permanetku alebo false v prípade, že permanetka už nieje aktívna

        return $permanentka;
    }

    // Načíta posledne aktívnu permanentku
    public function nacitajPosledneAktivnuPermanentku($poleHodnot, $uzivatelId)
    {
        $poleHodnot = array_unique(array_merge($poleHodnot, array(self::PERMANENTKA_ID, self::PERMANENTKA_TYP_ID, self::DATUM, self::OSOBA_ID)));//, self::ZOSTATOK_VSTUPOV)));
        $data = Db::dopytJedenRiadok('SELECT ' . implode(', ', $poleHodnot) . '
                                            FROM permanentka
                                            JOIN permanentka_typ USING (permanentka_typ_id)
                                            JOIN osoba USING (osoba_id)
                                            WHERE uzivatel_id = ? AND !aktivna ORDER BY datum DESC', array($uzivatelId));
        $permanentka =  is_array($data) ? array_intersect_key($data, array_flip($poleHodnot)) : $data;

        return $permanentka;
    }

    /**
     ** Vráti počet permanentiek žiadaného uživateľa
     * @param int $uzivatelId Id uživateľa
     * @return array|mixed
     */
    public function pocetPermanentiekUzivatela($uzivatelId)
    {
        $kluce = array(PermanentkaTypManazer::NAZOV, 'pocet');
        $data = Db::dopytVsetkyRiadky('SELECT permanentka_typ.nazov, COUNT(*) AS pocet FROM permanentka
                                JOIN permanentka_typ USING (permanentka_typ_id)
                                JOIN osoba USING (osoba_id)
                                WHERE uzivatel_id = ? GROUP BY permanentka_typ.nazov', array($uzivatelId));

        $permanentky =  is_array($data) ? Pole::filtrujKluce($data, $kluce) : $data;
        return $permanentky;

    }

    /**
     ** prepočíta zostatok vstupov v pŕipade 10 vstupovej permanetky
     * @param array $permanentka Pole údajov permanentky
     * @return false False v pripade ze prepocet zistil ze permanentka uŽ nieje aktívna alebo permanetku z novou hosnotou zostatok_vstupov
     */
    private function prepocitajZostatokVstupov($permanentka)
    {
        $rezervaciaManazer = new RezervaciaManazer();
        $pocetZaznamov = $rezervaciaManazer->vratPocetZaznamov($permanentka[self::DATUM], $permanentka[self::OSOBA_ID]);

        $rozdielNovychVstupov = 10 - $pocetZaznamov;

        if ($rozdielNovychVstupov !== $permanentka[self::ZOSTATOK_VSTUPOV])
        {
            $zostatokVstupov = $this->upravPocetVstupov($rozdielNovychVstupov, $permanentka[self::PERMANENTKA_ID]);
            if(!$zostatokVstupov)
                return false;
            else
                $permanentka[self::ZOSTATOK_VSTUPOV] = $zostatokVstupov;
        }
        return $permanentka;
    }

    /**
     ** Upravý počet vstupov a v prípade, že ich je nula tak permanentku zneaktívni
     * @param int $zostatokVstupov Zostatok vstupov na nastavenie
     * @param int $permanentkaId Id upravovanej permanentky
     * * @return int Zostatok nových vstupov
     */
    private function upravPocetVstupov($zostatokVstupov, $permanentkaId)
    {
        if($zostatokVstupov > 0)
        {
            Db::dopyt('UPDATE permanentka
                             SET zostatok_vstupov = ?
                             WHERE permanentka_id = ?', array($zostatokVstupov, $permanentkaId));
            return $zostatokVstupov;
        }
        else
        {
            $datumCasDB = DatumCas::dbTeraz(); // Aktualný Čas v DB formate

            Db::dopyt('UPDATE permanentka
                             SET zostatok_vstupov = 0, aktivna = 0, datum_zneaktivnenia = ?
                             WHERE permanentka_id = ?', array($datumCasDB, $permanentkaId));
            return false; // pernanetka je uz enaktivna
        }
    }

    /**
     ** Deaktivuje permanentku
     * @param int $permanentkaId Id permanentky
     */
    private function deaktuvujPermanentku($permanentkaId)
    {
        Db::zmen(self::PERMANENTKA_TABULKA, array(self::AKTIVNA => 0), 'WHERE permanentka_id = ?', array($permanentkaId));
    }

    /**
     ** Zostavy správu pre permanetky
     * @param $permanentka
     * @return string
     */
    public function zostavSpravu($permanentka)
    {
        $sprava = '';
        if ($permanentka)
        {
            $sprava .=  $permanentka[PermanentkaTypManazer::NAZOV];
            if($permanentka[self::ZOSTATOK_VSTUPOV] !== NULL && $permanentka[self::ZOSTATOK_VSTUPOV] > 0)
                $sprava .= ' | Zostatok vstupov: ' . (($permanentka[self::ZOSTATOK_VSTUPOV] > 0) ? $permanentka[self::ZOSTATOK_VSTUPOV] : $permanentka[self::ZOSTATOK_VSTUPOV]);
            else
                $sprava .= ' | Platná do: ' . $permanentka[self::DATUM];
        }
        else
            $sprava .=  '<span class="btn-sm btn-danger ml-2 mr-1">Neaktívna</span>';

        return $sprava;
    }

    /**
     ** Overi Či su 10 dnové permanetky Správne aktívne
     */
    public function overAktivnostVstupovychPermanentiek()
    {
        $permanentky = $this->nacitajVstupovePermanentky();
        foreach ($permanentky as $permanentka)
        {
            $this->prepocitajZostatokVstupov($permanentka);
        }
    }

    /**
     ** Načita zoznam permanentiek ktoré sú typu 10 dnovej
     * @return array|mixed Zoznam permanentiek
     */
    private function nacitajVstupovePermanentky()
    {
        $kluce = array (self::DATUM, self::OSOBA_ID, self::ZOSTATOK_VSTUPOV, self::PERMANENTKA_ID); // názvy stĺpcov,ktoré chcem z tabuľky načitať

        $data = Db::dopytVsetkyRiadky('SELECT ' . implode(', ',$kluce) . '
                                            FROM permanentka
                                            WHERE aktivna AND permanentka_typ_id = 2');

        return is_array($data) ? Pole::filtrujKluce($data, $kluce) : $data;
    }

    /**
     ** Načita permanetky vratane osoby
     * @return array|mixed Pole hodnot z permanentiek
     */
    public function nacitajPermanentky()
    {
        $this->overAktivnostVstupovychPermanentiek();

        $kluce = array(self::PERMANENTKA_TYP_ID, PermanentkaTypManazer::NAZOV, self::PERMANENTKA_ID, self::AKTIVNA, self::ZOSTATOK_VSTUPOV, UzivatelManazer::UZIVATEL_ID, self::DATUM_ZNEAKTIVNENIA);

        $select = 'SELECT CONCAT(COALESCE(meno, ""), " ", COALESCE(priezvisko, "")) AS osoba, DATE_FORMAT(datum, "%e.%c.%Y") AS datum,
                         ' . implode(', ',$kluce);
        $kluce[] = 'osoba';
        $kluce[] = self::DATUM;

        $from = ' FROM permanentka ';
        $join = ' JOIN permanentka_typ USING (permanentka_typ_id) ';
        $join .= ' JOIN osoba USING (osoba_id) ';
        $join .= ' JOIN osoba_detail USING (osoba_detail_id) ';

        $orederBy = ' ORDER BY aktivna DESC, permanentka.datum, osoba  ';

        $data = Db::dopytVsetkyRiadky($select . $from . $join . $orederBy);

        return is_array($data) ? Pole::filtrujKluce($data, $kluce) : $data;
    }

    /**
     ** Pokusi sa načitať prepadnutú, nepotvrdenú permanentku uživateľa
     * @param int $uzivatelId Id uživateľa ktorého permanentku zobrazujem
     * @return array|false|mixed False alebo načitana nepotvrdená permanentka
     */
    public function nacitajPrepadnutuPermanentku($uzivatelId)
    {
        $permanentka = $this->nacitajAktivnuPermanentku(array(), $uzivatelId);

        if($permanentka) // Ak načítam aktívnu permanentku, tak neriešim zobrazenie upozornenia, pretože permanentka je aktívna
            return false; // vráti false akoze netreba zobrazit upozornenie

        $permanentka = $this->nacitajPosledneAktivnuPermanentku(array(PermanentkaTypManazer::NAZOV, PermanentkaManazer::DATUM, PermanentkaManazer::DATUM_ZNEAKTIVNENIA,
                                                                      PermanentkaManazer::POTVRDENIE_PREPADNUTIA), $uzivatelId);
        if(!$permanentka)
            return false;  // vráti False v prípade, že sa nenašla Žiadna minula permanentka

        if(!$permanentka[PermanentkaManazer::POTVRDENIE_PREPADNUTIA]) // Vratim prepadnutu permanentku v prípade ze ešte nieje potvrdené zobrazenie upozornenia;
            return $permanentka;

        return false; // vrati false v pripade ze permanentka ma uz potvrdenie zobrazenia prepadnutia
    }

}
/*
 * Autor: MiCHo
 */
