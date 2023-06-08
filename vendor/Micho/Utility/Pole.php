<?php

namespace Micho\Utility;

/**
 ** Pomocná trieda na prácu s poľom
 * Class Pole
 */
class Pole
{
    /**
     ** Prefiltruje kľúče v poly
     * @param array $vstupnePole Vstupné pole, ktoré chceme filtrovať
     * @param array $poleKlucov Pole povolených kľúčov
     * @return array Výsledne prefiltrované pole
     */
    public static function filtrujKluce(array $vstupnePole, array $poleKlucov)
    {
        $p = $vstupnePole;
        if(is_array(array_shift($p)))
        {
            $pole = array();
            foreach ($vstupnePole as $kluc => $hodnota)
            {
                $pole[$kluc] = self::filtrujKluce($vstupnePole[$kluc], $poleKlucov);
            }
            return $pole;
        }
        else
            return array_intersect_key($vstupnePole, array_flip($poleKlucov));
    }

    /**
     ** Prefiltruje kľuče poľa tak, aby obsahovalo len tie zo zadanou predponou
     * @param string $predpona Predpona
     * @param array $vstupnePole Vstupné pole, ktoré chceme filtrovať
     * @return array Výsledne prefiltrované pole
     */
    public static function filtrujKluceSPredponou($predpona, array $vstupnePole)
    {
        $vystup = array();
        foreach ($vstupnePole as $kluc => $hodnota)
        {
            if (mb_strpos($kluc, $predpona) === 0)
                $vystup[$kluc] = $hodnota;
        }
        return $vystup;
    }

    /**
     ** Mapuje pole riadkov (asociatívnych polí) tak, že je výsledkom jedno asociatívne pole, pričom jeho klúče a hodntoy odpovedáju určitým kľúčom jednotlivých riadkov
     * @param array $poleRiadkov Vstupné pole riadkov
     * @param string $klucKluc Klúč riadku, ktorý bude kľúčom výstupného poľa
     * @param string $hodnotaKluc Klúč riadku, ktorý bude hodnotou výstupného poľa
     * @return array Výsledné asociatívne pole
     */
    public static function ziskajPary(array $poleRiadkov, $klucKluc, $hodnotaKluc)
    {
        if(empty($poleRiadkov))
            return false;

        foreach ($poleRiadkov as $riadok)
        {
            $kluc = $riadok[$klucKluc];
            // Kontrola kolizií klúčov
            if (isset($pary[$kluc]))
            {
                $i = 1;
                while (isset($pary[$kluc . ' (' . $i . ')'])) // zväčuje sa číslo pokiaľ je kolízia
                {
                    $i++;
                }
                $kluc .= ' (' . $i . ')';
            }
            $pary[$kluc] = $riadok[$hodnotaKluc];
        }
        return $pary;
    }

    /**
     ** Mapuje pole riadkov (asociatívnych polí) tak, že je výsledkom jedno pole, do ktorého sú vložené hodnoty z riadkov po daným klúčom
     * @param array $poleRiadkov Vstupné pole riadkov
     * @param string $kluc Názov klúča, ktorého hodnotu vkladáme do výstupného poľa
     * @return array Výsledné pole hodnôt
     */
    public static function ziskajHodnoty(array $poleRiadkov, $kluc)
    {
        $hodnoty = array();
        foreach ($poleRiadkov as $riadok)
        {
            $hodnoty[] = $riadok[$kluc];
        }
        return $hodnoty;
    }

    /**
     ** Rekurzivne pridá predpony kľúčom v poli
     * @param string $predpona Predpona klúča, ktorú chceme pridať
     * @param array $vstupnePole
     * @return array Výsledne pole
     */
    public static function pridajPredponu($predpona, array $vstupnePole)
    {
        $vystup = array();
        foreach ($vstupnePole as $kluc => $hodnota)
        {
            $kluc = $predpona . $kluc;
            if (is_array($hodnota))
                $hodnota = self::pridajPredponu($predpona, $hodnota);
            $vystup[$kluc] = $hodnota;
        }
        return $vystup;
    }

    /**
     ** Rekurzivne odstráni predpony kľúčom v poli
     * @param string $predpona Predpona klúča, ktorú chceme odstrániť
     * @param array $vstupnePole
     * @return array Výsledne pole
     */
    public static function odstranPredponu($predpona, array $vstupnePole)
    {
        $vystup = array();
        foreach ($vstupnePole as $kluc => $hodnota)
        {
            if (strpos($kluc, $predpona) === 0)
                $kluc = substr($kluc, mb_strlen($predpona));
            if (is_array($hodnota))
                $hodnota = self::odstranPredponu($predpona, $hodnota);
            $vystup[$kluc] = $hodnota;
        }
        return $vystup;
    }

    /**
     ** Prevedie camel notaciu kľúča poľa na podčiarkovnikovú
     * @param array $vstupnePole Vstupné pole
     * @return array Výsledne pole
     */
    public static function camelNaPodciarkovnik($vstupnePole)
    {
        $vystup = array();
        foreach ($vstupnePole as $kluc => $hodnota)
        {
            $kluc= Retazec::camelNaPodciarkovnik($kluc);
            if (is_array($hodnota))
                $hodnota = self::camelNaPodciarkovnik($hodnota);
            $vystup[$kluc] = $hodnota;
        }
        return $vystup;
    }

    /**
     ** Prevedie  podčiarkovnikovú notaciu kľúča poľa na camel
     * @param array $vstupnePole Vstupné pole
     * @return array Výsledne pole
     */
    public static function podciarkovnikNaCamel($vstupnePole)
    {
        $vystup = array();
        foreach ($vstupnePole as $kluc => $hodnota)
        {
            $kluc = Retazec::podciarkovnikNaCamel($kluc);
            if (is_array($hodnota))
                $hodnota = self::podciarkovnikNaCamel($hodnota);
            $vystup[$kluc] = $hodnota;
        }
        return $vystup;
    }

    /**
     ** Zisti Či sa v poli klúčov nachadza klúč s hľadaným podreŤazcom
     * @param string $podretazecKluc kľadaný podretazec klúča
     * @param array $data Pole hodnôt
     * @return bool či sa naŠei dany podretazec v poli klúča
     */
    public static function najdyPodretazecKluca($podretazecKluc ,$data)
    {
        return !empty(preg_filter('~' . preg_quote($podretazecKluc, '~') . '~', null, array_flip($data)));
    }

    /**
     ** Mapuje pole riadkov (asociatívnych polí) tak, že je výsledkom jedno pole, do ktorého sú vložené hodnoty z riadkov po daným klúčom avŠak sú unikátne
     * @param array $poleRiadkov Vstupné pole riadkov
     * @param string $kluc Názov klúča, ktorého hodnotu vkladáme do výstupného poľa
     * @return array Výsledné pole hodnôt
     */
    public static function ziskajUnikatneHodnoty(array $poleRiadkov, $kluc)
    {
        $hodnoty = self::ziskajHodnoty($poleRiadkov, $kluc);
        return array_merge(array_unique($hodnoty), array());
    }

    /**
     ** Vráti unikatne Pole poli Viecerých klúčov naraz
     * @param array $poleRiadkov Vstupné pole riadkov
     * @param array $kluce Klúče ktore majú byť unikátne
     */
    public static function ziskajUnikatnePole(array $poleRiadkov, $kluce)
    {
        $zlucene = '';
        $poleTriedene = array();
        foreach ($poleRiadkov as $kluc => $rez)
        {
            $zluc = '';
            foreach ($kluce as $kl) // vytvori rezec ktory ma byt unikatni v poli ...
            {
                $zluc .= $rez[$kl];
            }
            if ($zlucene !== $zluc) // ak je unikatni tak ho ulozim
            {
                $poleTriedene[$kluc] = $rez;
            }

            $zlucene = $zluc;
        }
        return $poleTriedene;
    }

}
/* Autor: http://www.itnetwork.cz */

/*
 * Niektoré časti sú upravené
 * Autor: MiCHo
 */
