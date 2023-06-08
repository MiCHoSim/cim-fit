<?php

namespace App\AdministraciaModul\Administracia\Model;

use App\RezervaciaModul\Model\RezervaciaManazer;
use Micho\Db;
use Micho\Utility\DatumCas;
use Micho\Utility\Pole;
use DateTime;

/**
 ** Správca Štatistik webu
 * Class StatistikaManazer
 * @package App\AdministraciaModul\Statistika\Model
 */
class StatistikaManazer
{
    const POCET_DNI = 'pocet_dni';
    const DATUM_OD = 'datum_od';
    const DATUM_DO = 'datum_do';
    const LIMIT = 'limit';

    /** @var DateTime|array Dnešný dátum */
    private $datumDnes;

    /** @var int|mixed Limit zobrazenia (počet zátnamov) */
    private $limit;
    /** @var array vrati interval dátumov array(od=> , do=>) */
    private $datumVyber;

    /**
     * @param false|array $datumVyber vrati interval dátumov array(od=> , do=>)
     * @param int $pocetDni Počet dni za ktoré chem zobraziŤ poČet rezervácii
     * @param int $limit Počet záznamov ktore chcem vrátiť
     */
    public function __construct($datumVyber, $pocetDni = false, $limit = 3)
    {
        $this->datumDnes = new DateTime();

        if($pocetDni)
        {
            $this->datumVyber['do'] = $this->datumDnes->format(DatumCas::DB_DATUM_FORMAT);
            $this->datumVyber['od'] = ($this->datumDnes->modify(' - ' . $pocetDni - 1 . ' day'))->format(DatumCas::DB_DATUM_FORMAT);
        }
        else
            $this->datumVyber = $datumVyber;

        $this->limit = $limit;
    }

    /**
     ** Vrati počet jednotlivých rezervácii (individualne/skupinove) za určitý počet dni
     * @param bool $unikatne či chcem zaratať kaŽdeho uživateľa len raz, teda je zobrazeny unikatny počet
     * @return array|mixed
     */
    public function pocetRezervaciiZa($unikatne = false)
    {
        $kluce = array('pocet_individualne', 'pocet_skupina');

        $dopyt = 'SELECT ';
        $select1 = '(SELECT COUNT(';
        $select2 = '(SELECT COUNT(';
        $join2= 'JOIN rezervacia USING (rezervacia_id) ';

        if($unikatne) // ak chem unikatne hodnoty teda kazdy uzivatel je zaratany len raz
        {
            $select1 .= 'DISTINCT CONCAT(COALESCE(osoba_detail.meno, ""), " ", COALESCE(osoba_detail.priezvisko, ""))) ';
            $join1 = ' JOIN osoba USING (osoba_id)
                                JOIN osoba_detail USING (osoba_detail_id) ';
            $select2 .= 'DISTINCT CONCAT(COALESCE(osoba_detail.meno, ""), " ", COALESCE(osoba_detail.priezvisko, ""))) ';
            $join2 .= '         JOIN osoba ON rezervacia.osoba_id = osoba.osoba_id
                                JOIN osoba_detail USING (osoba_detail_id) ';
        }
        else
        {
            $select1 .= '*) ';
            $join1 = ' ';
            $select2 .= 'DISTINCT skupina.rezervacia_id) '; // nechem aby zapocitavalo viac krat trenera ked ma viac ludi
        }

        $from1 = 'FROM rezervacia ';
        $join1 .= 'LEFT JOIN skupina USING (rezervacia_id) ';

        $where1 = 'WHERE skupina_id IS NULL AND datum BETWEEN ? AND ?) AS pocet_individualne, ';
        $where2 = 'WHERE datum BETWEEN ? AND ?) AS pocet_skupina ';

        $from2 = 'FROM skupina ';

        $pocetRezervacii = Db::dopytJedenRiadok($dopyt . $select1 . $from1 . $join1 . $where1 .
            $select2 . $from2 . $join2 . $where2
            , array($this->datumVyber['od'], $this->datumVyber['do'], $this->datumVyber['od'], $this->datumVyber['do']));

        return is_array($pocetRezervacii) ? Pole::filtrujKluce($pocetRezervacii, $kluce) : $pocetRezervacii;
    }

    /**
     ** Vráti počet  rezervacii daného uživateľa za určitý poČet dní Limit 3
     * @return array
     */
    public function vratPocetRezervaciiUzivatelov()
    {
        $kluce = array ('osoba', 'pocet', 'uzivatel_id'); // názvy stĺpcov,ktoré chcem z tabuľky načitať

        // Dopyt pre načitanieiba individualnych treningov
        $dopyt1 = 'SELECT CONCAT(COALESCE(osoba_detail.meno, ""), " ", COALESCE(osoba_detail.priezvisko, "")) AS osoba,
                  COUNT(*) AS pocet, uzivatel_id
                  FROM rezervacia
                  JOIN osoba USING (osoba_id)
                  JOIN osoba_detail USING (osoba_detail_id)
                  LEFT JOIN skupina USING (rezervacia_id)
                  WHERE skupina_id IS NULL AND datum BETWEEN ? AND ?
                  GROUP BY uzivatel_id ORDER BY pocet DESC, osoba LIMIT ?';

        // dopyt pre načitanie iba treningov skupinových, teda trenera
        $dopyt2 = 'SELECT prezivka AS osoba,
                  COUNT(DISTINCT skupina.rezervacia_id) AS pocet, uzivatel_id
                  FROM skupina
                  JOIN rezervacia USING (rezervacia_id)
                  JOIN osoba ON rezervacia.osoba_id = osoba.osoba_id
                  LEFT JOIN trener ON rezervacia.osoba_id = trener.osoba_id
                  WHERE datum BETWEEN ? AND ?
                  GROUP BY uzivatel_id ORDER BY pocet DESC, osoba LIMIT ?';

        $pocetRezervacii['individualne'] = Db::dopytVsetkyRiadky($dopyt1, array($this->datumVyber['od'], $this->datumVyber['do'], $this->limit));
        $pocetRezervacii['skupinove'] = Db::dopytVsetkyRiadky($dopyt2, array($this->datumVyber['od'], $this->datumVyber['do'], $this->limit));

        $pocetRezervacii = is_array($pocetRezervacii) ? Pole::filtrujKluce($pocetRezervacii, $kluce) : $pocetRezervacii;

        return $pocetRezervacii;
    }

    /**
     ** Vráti vybratý dátum
     * @return array|false
     */
    public function vratVybratyDatum()
    {
        return $this->datumVyber;
    }

    /**
     ** Načita štatistiky od zaadaného dátumu po dnesný mesiac
     ** počet všetkych rezervácii/ počet uživateľov ktoré, rezervácie vytvorili/ počet skupinových rezervácii ako aj počet trénerov ktorý rezervácie vytvorili
     ** počet rezervácii pre jednotlivých uživateľov
     * @return array
     * @throws \Exception
     */
    public function pocetRezervaciiMesiace()
    {
        $datum = new DateTime($this->datumVyber['od']);

        while ($datum <= $this->datumDnes)
        {
            $this->datumVyber['od'] = DatumCas::prvyDenMesiaca($datum)->format(DatumCas::DB_DATUM_FORMAT); // prvý den mesiaca
            $this->datumVyber['do'] = DatumCas::poslednyDenMesiaca($datum)->format(DatumCas::DB_DATUM_FORMAT); // posledný den mesiaca


            $rok = $datum->format('Y');
            $mesiac = DatumCas::mesiacSlovne($datum);

            $pocetRezervacii = $this->pocetRezervaciiZa();
            $pocetUnikatnychRezervacii = $this->pocetRezervaciiZa(true);
            $pocetRezervaciiUzivatelov = $this->vratPocetRezervaciiUzivatelov();
            $rezervacieMesiace[$rok][$mesiac] = array('pocetRezervacii' => $pocetRezervacii,
                                                        'pocetUnikatnychRezervacii' => $pocetUnikatnychRezervacii);
            $rezervaciiMesiaceUzivatelov[$rok][$mesiac] = $pocetRezervaciiUzivatelov;

            $datum->modify('+ 1 day'); // posunutie sa na další mesiac

        }

        return array('rezervaciiMesiace' => $rezervacieMesiace, 'rezervaciiMesiaceUzivatelov' => $rezervaciiMesiaceUzivatelov);

    }

}

/*
 * Autor: MiCHo
 */
