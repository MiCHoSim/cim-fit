<?php

use Micho\Utility\DatumCas;
use Micho\Utility\Retazec;

/**
 * Trieda/Wraper obaľuje najpouživanejšie formatovacie metódy
 * Class FormatPomocne
 */
class FormatPomocne
{
    /**
     ** Vypiše deň v týždni po slovensky
     * @param $datumCas
     */
    public static function denSlovensky($datum)
    {
        return DatumCas::denSlovensky($datum);
    }

    /**
     ** Prevedie prvé pismeno textu na veľké
     * @param string $text Text na prevedenie
     * @return string Prevedený text
     */
    public static function prveVelke($text)
    {
        return Retazec::prveVelke($text);
    }

    /**
     ** Skráti text na požadovanú dĺžku pričom v požadovanej dĺžke na konci reťazca sa nachádzajú tri bodky
     * @param string $text Text na skrátenie
     * @param int $dlzka Požadovaná dĺžka textu
     * @return string Skrátený text
     */
    public static function skrat($text, $dlzka)
    {
        return Retazec::skrat($text, $dlzka);
    }

    /**
     ** Sformatuje dátum ľubovoľnej stringovej podoby na tvar: Dnes/Vcera/Zajtra
     * @param string $datum Dátum na sformatovanie
     * @return string Sformatovaná datum
     */
    public static function peknyDatum($datum)
    {
        return DatumCas::peknyDatum($datum);
    }

    /**
     ** Sformátuje dátum z ľubovoľnej stringovej podoby do tvaru (01.01.2020)
     * @param string $datum Dátum na sformátovanie
     * @return string Sformatovaný dátum
     */
    public static function formatujDatumSlovensko(string $datum)
    {
        return DatumCas::formatujDatum($datum);
    }

    /**
     ** Sformatuje dátum a Čas ľubovoľnej stringovej podoby na tvar: Dnes/Vcera/Zajtra 01:01:01
     * @param  string $datumCas Dátum a čas na sformatovanie
     * @return string Sformatovaná datum a cas
     */
    public static function peknyDatumCas($dat, $format)
    {
        return DatumCas::peknyDatumCas($dat, $format);
    }

    /**
     ** prevedie DateTime na string
     * @param DateTime $datum Dátum
     * @param string $format Format na ktory chem darum formatovať
     * @return string Dátum
     */
    public static function formatujDateTime(DateTime $datum, $format = DatumCas::DATUM_FORMAT)
    {
        return $datum->format($format);
    }

    /**
     ** Formatuje Dátum Čas na poZˇadovaný format
     * @param string $datumCas Dátum čas
     * @param string $format Format uprafvi podla pravidiel DATETIME
     * @return false|string Sformatovaný dátum cas
     */
    public static function formatujDatumCasNaTvar($datumCas, $format)
    {
        return DatumCas::formatujNaTvar($datumCas, $format);
    }

    /**
     ** Sformatuje dátum  na tvar d.m. Y
     * @param string $dat Datum
     * @return string Datum v tvare d.m. Y
     */
    public static function ciselnyDatum($datum)
    {
        $datumCas = new DateTime($datum);
        return $datumCas->format(DatumCas::DATUM_FORMAT);
    }

    /**
     ** Sformatuje čiastku na  desatinné miesta a pripojí danú menu
     * @param float $ciastka čiastka
     * @param string $mena Mena napr "€"
     * @return string čiastka na 2 desatinné miesta s menou
     */
    public static function mena($ciastka, $mena = '€')
    {
        return number_format($ciastka, 2, ',', ' ') . ' ' . $mena;
    }

    /**
     ** Sformatuje boolean na tvar Áno/Nie
     * @param bool $hodnota Booleoska hodnota
     * @return string Hodnota Áno alebo Nie
     */
    public static function boolean($hodnota)
    {
        return $hodnota ? 'Áno' : 'Ne';
    }

    /**
     ** Vyskloňuje slovo
     * @param string $zaklad Základ slova
     * @param int $pocet Požet položiek / veci
     * @param string $pr Tvar prvého skloňovania
     * @param string $dr Tvar druhého skloňovania
     * @param string $tr Tvar tretieho skloňovania
     * @return string Vyskloňovaný reťazec
     */
    public static function sklonuj($zaklad, $pocet, $pr, $dr , $tr)
    {
        $koncovka = ($pocet == 1) ? $pr : (($pocet >= 2 && $pocet <= 4) ? $dr : $tr);
        return $zaklad . $koncovka;
    }
}
/* Autor: http://www.itnetwork.cz */

/*
 * Niektoré časti sú upravené
 * Autor: MiCHo
 */
