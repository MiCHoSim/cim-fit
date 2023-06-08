<?php

namespace Micho\Subory;


use Micho\ChybaUzivatela;
use Micho\Utility\Retazec;

/**
 ** Tríeda služiaca na správu Súborov
 * Class Priecinok
 * @package Micho
 */
class Subor
{
    /**
     ** Uloži popripade vytvorí súbor a uloŽí text
     * @param string $text Text na uloženie
     * @param string $cesta Cesta uloŽenia súboru
     * @throws ChybaUzivatela
     */
    public function ulozSubor($text, $cesta)
    {
        $navrat = file_put_contents($cesta, 'Kod: '. $text);
        if($navrat === FALSE)
            throw new ChybaUzivatela('Pri uložení došlo k chybe');
    }

    /**
     ** Načíta súbor
     * @param string $cesta Cesta uloŽenia súboru
     * @return string Načítaný text
     * @throws ChybaUzivatela
     */
    public function nacitajSubor($cesta)
    {
        $text = file_get_contents($cesta);
        if($text === FALSE)
            throw new ChybaUzivatela('Pri čítani došlo k chybe');
        return $text;
    }

    /**
     ** Zisti názvy súborov a vráti ich
     * @param string $cesta Cesta k súboru
     * @return array|false Načitané názvy súborov
     */
    public static function vratNazvySuborov($cesta)
    {
        $zlozka = scandir($cesta);
        array_shift($zlozka);
        array_shift($zlozka);
        return $zlozka;
    }

    /**
     ** Vráti Celý názov hladaného súboru z daneho podretazca
     * @param string $cesta Cesta priecinka k hladému súboru
     * @param string $podretazec časť názvu ktory hladám
     * @return string Názov súboru;
     */
    public static function vratNazovSuboruPodretazec($cesta, $podretazec)
    {
        if (file_exists($cesta))
        {
            $subory = self::vratNazvySuborov($cesta); // nacita nazvy suborov v priecinku

            foreach ($subory as $kluc => $subor)
            {
                if(Retazec::obsahuje($subor, $podretazec)) // najde subor obrazka z danim nazvom vyhodui z cyklu
                    return $subor;
            }
        }

        return false;
    }

    /**
     ** skopiruje Subor
     * @param string $zdroj Zdroj kopirovania
     * @param string $nazovStary Nazov stareho suboru
     * @param string $ciel Cieľ kopirovania
     * @param string $nazovNovy Nazov noveho suboru porekopirovaneho
     * @return bool či sa kopirovanie podarilo
     */
    public static function skopirujSubor($zdroj, $nazovStary, $ciel, $nazovNovy)
    {
        Priecinok::vytvorPriecinok($ciel); //vytvorenie priečinka v pripade ze neexistuje

        if (copy($zdroj. '/' . $nazovStary, $ciel . '/' . $nazovNovy))
            return true;
        return false;
    }

}
/*
 * Autor: MiCHo
 */