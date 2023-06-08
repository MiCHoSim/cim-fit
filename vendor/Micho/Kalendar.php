<?php

namespace Micho;

use DateTime;
use Micho\Utility\DatumCas;


/**
 ** Tríeda služiaca na správu kalendára
 * Class Kalendar
 * @package Micho
 */
class Kalendar
{
    /**
     * Konštanty mesiaca
     */
    const ROKY = array('2022' => '2022', '2023' => '2023');
    const MESIACE = array('január' => 1, 'február' => 2, 'marec' => 3, 'apríl' => 4, 'máj' => 5, 'jún' => 6, 'júl' => 7, 'august' => 8, 'september' => 9, 'október' => 10, 'november' => 11, 'december' => 12);

    /**
     * @var DateTime Objekt aktualného času
     */
    private $aktualnyDatumCas;
    /**
     * @var string|int Jednotlive Časti aktualneho Dátumu
     */
    private $aktualnyRok;
    private $aktualnyMesiac;
    private $aktualnyDen;

    /**
     * @var DateTime Objekt Vybratého času
     */
    private $vybratyDatum;
    /**
     * @var string|int Jednotlive Časti vybratého Dátumu
     */
    private $vybratyRok;
    private $vybratyMesiac;
    private $vybratyDen;

    /**
     * @var Posledny den mesiaca ako 30/31/28
     */
    private $poslednyDenMesiac;

    /**
     ** Služi na presuvanie sa v mesiaci (kvoli presunu na zaciatok, koniec mesiaca, ...)
     * @var
     */
    private $vybratyDatumModyfikacie;

    /**
     * Kalendar constructor.
     * @param int $vybratyRok Vybratý Rok
     * @param int $vybratyMesiac Vybratý Mesiac
     * @param int $vybratyDen Vybratý deň
     * @throws ChybaKalendar
     */
    public function __construct($vybratyRok, $vybratyMesiac, $vybratyDen)
    {
        if(!checkdate($vybratyMesiac, $vybratyDen, $vybratyRok))
            throw new ChybaKalendar('Nexistujúci dátum');

        $this->aktualnyDatumCas = new DateTime();
        $this->aktualnyRok = $this->aktualnyDatumCas->format('Y');
        $this->aktualnyMesiac = $this->aktualnyDatumCas->format('n');
        $this->aktualnyDen = $this->aktualnyDatumCas->format('j');

        // objekt vybratého dátumu
        $this->vybratyDatum = new DateTime($vybratyRok . '-' . $vybratyMesiac . '-' . $vybratyDen);
        $this->vybratyRok = $vybratyRok;
        $this->vybratyMesiac = $vybratyMesiac;
        $this->vybratyDen = $vybratyDen;

        // objekt vybrateho datumu na modfikovanie
        $this->vybratyDatumModyfikacie = new DateTime($vybratyRok . '-' . $vybratyMesiac . '-' . $vybratyDen);

        $this->poslednyDenMesiac = DatumCas::poslednyDenMesiaca($this->vybratyDatumModyfikacie)->format('j');
    }

    /**
     ** Vygeneruje jednotlive týždne a ich dni mesiaca
     * @param false $vybratyTyzden Či chcem vrátit konkrétny týŽdeň
     * @return array vygenerovany týždeň
     */
    public function zostavTyzdneMesiaca($vybratyTyzden = false)
    {
        $prvyDenMesiaca = DatumCas::prvyDenMesiaca($this->vybratyDatumModyfikacie)->format('w');
        $pripocitatZaciatok = $prvyDenMesiaca == 0 ? 6 : $prvyDenMesiaca - 1; // Kolko prázdnych policok sa pripočíta k začiatku mnesiaca

        $pocetDniMesiaca = $this->vybratyDatumModyfikacie->format('t');

        $poslednyDenMesiaca = DatumCas::poslednyDenMesiaca($this->vybratyDatumModyfikacie)->format('w');
        $pripocitavamKoniec = $poslednyDenMesiaca == 0 ? 0 :  7 - $poslednyDenMesiaca; // Kolko prázdnych policok sa pripočíta ku koncu mnesiaca

        $kompletPocetDni = $pripocitatZaciatok + $pocetDniMesiaca + $pripocitavamKoniec; // celkovi pocet dni na zobrázenie aj s prázdnimi polickami

        $tyzden = 1;
        $do = $kompletPocetDni;
        $od = 1;
        $den = 1;

        if($vybratyTyzden)
        {
            if($vybratyTyzden * 7 > $kompletPocetDni)
                throw new ChybaKalendar('Požadovaný týždeň neexistuje');
            $tyzden = $vybratyTyzden;
            $do = 7 * $tyzden;
            $od = $do - 6;
            $den = ($od - $pripocitatZaciatok) < 0 ? $od : $od - $pripocitatZaciatok;
        }

        for ($od; $od <= $do; $od++) // generovanie mesiaca
        {
            if($od <= $pripocitatZaciatok || $den > $pocetDniMesiaca)
            {
                $dniMesiaca[$tyzden][] = '';
            }
            else
            {
                $dniMesiaca[$tyzden][] = $den++;
            }
            if (!($od % 7))
                $tyzden++;
        }
        return $dniMesiaca; //Vráti bude cely mesiac alebo iba týZďeň
    }

    /**
     ** Vráti dátum bud ako DATETIME alebo uz sformatovaný na poŽadovaný formát
     * @param false $format či ho chem formatovať na výpis
     * @return string Objekt/string na výpis
     */
    public function vratAktualnyDatum($format = false)
    {
        if ($format)
            return $this->aktualnyDatumCas->format($format);
        return $this->aktualnyDatumCas;
    }

    /**
     * @return int|string Jednotlivé časti dátumu
     */
    public function vratAktualnyRok()
    {
        return $this->aktualnyRok;
    }
    public function vratAktualnyMesiac()
    {
        return $this->aktualnyMesiac;
    }
    public function vratAktualnyDen()
    {
        return $this->aktualnyDen;
    }


    /**
     ** Vráti dátum bud ako DATETIME alebo uz sformatovaný na poŽadovaný formát
     * @param false $format či ho chem formatovať na výpis
     * @return string Objekt/string na výpis
     */
    public function vratVybratyDatum($format = false)
    {
        if ($format)
            return $this->vybratyDatum->format($format);
        return $this->vybratyDatum;
    }
    /**
     * @return int|string Jednotlivé časti dátumu
     */
    public function vratVybratyRok()
    {
        return $this->vybratyRok;
    }
    public function vratVybratyMesiac()
    {
        return $this->vybratyMesiac;
    }
    public function vratVybratyDen()
    {
        return $this->vybratyDen;
    }

    /**
     * @return int Vráti posledny den mesiaca ako cislo 31/30/28
     */
    public function vratPoslednyDenMesiac()
    {
        return $this->poslednyDenMesiac;
    }


}
/*
 * Autor: MiCHo
 */
