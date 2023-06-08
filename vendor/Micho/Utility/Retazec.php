<?php

namespace Micho\Utility;

/**
 ** Pomocná trieda na prácu s Textovými reťazcami
 * Class Retazec
 */
class Retazec
{
    /**
     ** Zistí, či podreťazec začína určitým podreťazcom
     * @param string $text Text
     * @param string $podretazec Podreťazec
     * @return bool Či text začína podreťacom
     */
    public static function zacina($text, $podretazec)
    {
        return (mb_strpos($text, $podretazec) === 0);
    }

    /**
     ** Zistí, či sa daný podreŤazec nachádza v reťazci
     * @param string $text text v ktorom hľadám
     * @param string $podretazec podreŤazec ktorý hľadám
     * @return bool či sa nachádza
     */
    public static function obsahuje($text, $podretazec)
    {
        return(mb_strpos($text, $podretazec) !== false);
    }



    /**
     ** Zistí, či podreťazec končí určitým podreťazcom
     * @param string $text Text
     * @param string $podretazec Podreťazec
     * @return bool Či text končí podreťacom
     */
    public static function konci($text, $podretazec)
    {
        return ((mb_strlen($text) >= mb_strlen($podretazec)) && ((mb_strpos($text, $podretazec, mb_strlen($text) - mb_strlen($podretazec))) !== false));
    }

    /**
     ** Prvé písmeno textu zmení na veľké
     * @param string $text Text, ktorý chceme upraviť
     * @return string Zmenení text
     */
    public static function prveVelke($text)
    {
        return mb_strtoupper(mb_substr($text, 0, 1)) . mb_substr($text, 1, mb_strlen($text));
    }

    /**
     ** Prvé písmeno textu zmení na malé
     * @param string $text Text, ktorý chceme upraviť
     * @return string Zmenení text
     */
    public static function prveMale($text)
    {
        return mb_strtolower(mb_substr($text, 0, 1)) . mb_substr($text, 1, mb_strlen($text));
    }

    /**
     ** Skráti text na požadovanú dĺžku pričom v požadovanej dĺžke na konci reťazca sa nachádzajú tri bodky
     * @param string $text Text na skrátenie
     * @param int $dlzka Požadovaná dĺžka textu
     * @return string Skrátený text
     */
    public static function skrat($text, $dlzka)
    {
        if($dlzka <= 1)
            $text = mb_substr($text, 0, $dlzka) . '.';

        elseif (mb_strlen($text) - 3 > $dlzka)
            $text = mb_substr($text, 0, $dlzka - 3) . '...';
        return $text;
    }

    /**
     ** Odstráni s textu diakritiku
     * @param string $text Text s diakritikou
     * @return string Text bez diakritikou
     */
    public static function odstranDiakritiku($text)
    {
        $znaky = array(
            chr(195) . chr(128) => 'A', chr(195) . chr(129) => 'A',
            chr(195) . chr(130) => 'A', chr(195) . chr(131) => 'A',
            chr(195) . chr(132) => 'A', chr(195) . chr(133) => 'A',
            chr(195) . chr(135) => 'C', chr(195) . chr(136) => 'E',
            chr(195) . chr(137) => 'E', chr(195) . chr(138) => 'E',
            chr(195) . chr(139) => 'E', chr(195) . chr(140) => 'I',
            chr(195) . chr(141) => 'I', chr(195) . chr(142) => 'I',
            chr(195) . chr(143) => 'I', chr(195) . chr(145) => 'N',
            chr(195) . chr(146) => 'O', chr(195) . chr(147) => 'O',
            chr(195) . chr(148) => 'O', chr(195) . chr(149) => 'O',
            chr(195) . chr(150) => 'O', chr(195) . chr(153) => 'U',
            chr(195) . chr(154) => 'U', chr(195) . chr(155) => 'U',
            chr(195) . chr(156) => 'U', chr(195) . chr(157) => 'Y',
            chr(195) . chr(159) => 's', chr(195) . chr(160) => 'a',
            chr(195) . chr(161) => 'a', chr(195) . chr(162) => 'a',
            chr(195) . chr(163) => 'a', chr(195) . chr(164) => 'a',
            chr(195) . chr(165) => 'a', chr(195) . chr(167) => 'c',
            chr(195) . chr(168) => 'e', chr(195) . chr(169) => 'e',
            chr(195) . chr(170) => 'e', chr(195) . chr(171) => 'e',
            chr(195) . chr(172) => 'i', chr(195) . chr(173) => 'i',
            chr(195) . chr(174) => 'i', chr(195) . chr(175) => 'i',
            chr(195) . chr(177) => 'n', chr(195) . chr(178) => 'o',
            chr(195) . chr(179) => 'o', chr(195) . chr(180) => 'o',
            chr(195) . chr(181) => 'o', chr(195) . chr(182) => 'o',
            chr(195) . chr(182) => 'o', chr(195) . chr(185) => 'u',
            chr(195) . chr(186) => 'u', chr(195) . chr(187) => 'u',
            chr(195) . chr(188) => 'u', chr(195) . chr(189) => 'y',
            chr(195) . chr(191) => 'y',
            // Decompositions for Latin Extended-A
            chr(196) . chr(128) => 'A', chr(196) . chr(129) => 'a',
            chr(196) . chr(130) => 'A', chr(196) . chr(131) => 'a',
            chr(196) . chr(132) => 'A', chr(196) . chr(133) => 'a',
            chr(196) . chr(134) => 'C', chr(196) . chr(135) => 'c',
            chr(196) . chr(136) => 'C', chr(196) . chr(137) => 'c',
            chr(196) . chr(138) => 'C', chr(196) . chr(139) => 'c',
            chr(196) . chr(140) => 'C', chr(196) . chr(141) => 'c',
            chr(196) . chr(142) => 'D', chr(196) . chr(143) => 'd',
            chr(196) . chr(144) => 'D', chr(196) . chr(145) => 'd',
            chr(196) . chr(146) => 'E', chr(196) . chr(147) => 'e',
            chr(196) . chr(148) => 'E', chr(196) . chr(149) => 'e',
            chr(196) . chr(150) => 'E', chr(196) . chr(151) => 'e',
            chr(196) . chr(152) => 'E', chr(196) . chr(153) => 'e',
            chr(196) . chr(154) => 'E', chr(196) . chr(155) => 'e',
            chr(196) . chr(156) => 'G', chr(196) . chr(157) => 'g',
            chr(196) . chr(158) => 'G', chr(196) . chr(159) => 'g',
            chr(196) . chr(160) => 'G', chr(196) . chr(161) => 'g',
            chr(196) . chr(162) => 'G', chr(196) . chr(163) => 'g',
            chr(196) . chr(164) => 'H', chr(196) . chr(165) => 'h',
            chr(196) . chr(166) => 'H', chr(196) . chr(167) => 'h',
            chr(196) . chr(168) => 'I', chr(196) . chr(169) => 'i',
            chr(196) . chr(170) => 'I', chr(196) . chr(171) => 'i',
            chr(196) . chr(172) => 'I', chr(196) . chr(173) => 'i',
            chr(196) . chr(174) => 'I', chr(196) . chr(175) => 'i',
            chr(196) . chr(176) => 'I', chr(196) . chr(177) => 'i',
            chr(196) . chr(178) => 'IJ', chr(196) . chr(179) => 'ij',
            chr(196) . chr(180) => 'J', chr(196) . chr(181) => 'j',
            chr(196) . chr(182) => 'K', chr(196) . chr(183) => 'k',
            chr(196) . chr(184) => 'k', chr(196) . chr(185) => 'L',
            chr(196) . chr(186) => 'l', chr(196) . chr(187) => 'L',
            chr(196) . chr(188) => 'l', chr(196) . chr(189) => 'L',
            chr(196) . chr(190) => 'l', chr(196) . chr(191) => 'L',
            chr(197) . chr(128) => 'l', chr(197) . chr(129) => 'L',
            chr(197) . chr(130) => 'l', chr(197) . chr(131) => 'N',
            chr(197) . chr(132) => 'n', chr(197) . chr(133) => 'N',
            chr(197) . chr(134) => 'n', chr(197) . chr(135) => 'N',
            chr(197) . chr(136) => 'n', chr(197) . chr(137) => 'N',
            chr(197) . chr(138) => 'n', chr(197) . chr(139) => 'N',
            chr(197) . chr(140) => 'O', chr(197) . chr(141) => 'o',
            chr(197) . chr(142) => 'O', chr(197) . chr(143) => 'o',
            chr(197) . chr(144) => 'O', chr(197) . chr(145) => 'o',
            chr(197) . chr(146) => 'OE', chr(197) . chr(147) => 'oe',
            chr(197) . chr(148) => 'R', chr(197) . chr(149) => 'r',
            chr(197) . chr(150) => 'R', chr(197) . chr(151) => 'r',
            chr(197) . chr(152) => 'R', chr(197) . chr(153) => 'r',
            chr(197) . chr(154) => 'S', chr(197) . chr(155) => 's',
            chr(197) . chr(156) => 'S', chr(197) . chr(157) => 's',
            chr(197) . chr(158) => 'S', chr(197) . chr(159) => 's',
            chr(197) . chr(160) => 'S', chr(197) . chr(161) => 's',
            chr(197) . chr(162) => 'T', chr(197) . chr(163) => 't',
            chr(197) . chr(164) => 'T', chr(197) . chr(165) => 't',
            chr(197) . chr(166) => 'T', chr(197) . chr(167) => 't',
            chr(197) . chr(168) => 'U', chr(197) . chr(169) => 'u',
            chr(197) . chr(170) => 'U', chr(197) . chr(171) => 'u',
            chr(197) . chr(172) => 'U', chr(197) . chr(173) => 'u',
            chr(197) . chr(174) => 'U', chr(197) . chr(175) => 'u',
            chr(197) . chr(176) => 'U', chr(197) . chr(177) => 'u',
            chr(197) . chr(178) => 'U', chr(197) . chr(179) => 'u',
            chr(197) . chr(180) => 'W', chr(197) . chr(181) => 'w',
            chr(197) . chr(182) => 'Y', chr(197) . chr(183) => 'y',
            chr(197) . chr(184) => 'Y', chr(197) . chr(185) => 'Z',
            chr(197) . chr(186) => 'z', chr(197) . chr(187) => 'Z',
            chr(197) . chr(188) => 'z', chr(197) . chr(189) => 'Z',
            chr(197) . chr(190) => 'z', chr(197) . chr(191) => 's',
            // Euro Sign
            chr(226) . chr(130) . chr(172) => 'E',
            // GBP (Pound) Sign
            chr(194) . chr(163) => ''
        );
        return strtr($text, $znaky);
    }

    /**
     ** Medzery medzi slovami prevedie na pomlčky
     * @param string $text Text na úpravu
     * @return string prevedený text
     */
    public static function pomlckuj($text)
    {
        return preg_replace("/\-{2,}/u", "-", preg_replace("/[^a-z0-9]/u", "-", mb_strtolower(self::odstranDiakritiku($text))));
    }

    /**
     ** Prevedie text na CamelCase podľa oddeľovača
     * @param string $text Text na prevedenie
     * @param string $oddelovac Oddelovač slov
     * @param bool $prveMale či chcem prvé male
     * @return string Prevedený text
     */
    private static function prevedNaCamel($text, $oddelovac, $prveMale = true)
    {
        $vysledok = str_replace(' ', '', mb_convert_case(str_replace($oddelovac, ' ', $text), MB_CASE_TITLE));
        if ($prveMale)
            $vysledok = self::prveMale($vysledok);
        return $vysledok;
    }

    /**
     ** Prevedie text z CamelCase podľa oddeľovača
     * @param string $text Text na prevedenie
     * @param string $oddelovac Oddelovač slov
     * @return string Prevedený text
     */
    private static function prevedZCamel($text, string $oddelovac)
    {
        return ltrim(mb_strtolower(preg_replace('/[A-Z]/', $oddelovac . '$0', $text)), $oddelovac);
    }

    /**
     ** Prevedie pomlčky na CamelCase
     * @param string $text Text na prevedenie
     * @param bool $prveMale či chcem prvé male
     * @return string Prevedený text
     */
    public static function pomlckyNaCamel($text, $prveMale = true)
    {
        return self::prevedNaCamel($text, '-', $prveMale);
    }

    /**
     ** Prevedie podčiarkovník na CamelCase
     * @param string $text Text na prevedenie
     * @parambool $prveMale či chcem prvé male
     * @return string Prevedený text
     */
    public static function podciarkovnikNaCamel($text, $prveMale = true)
    {
        return self::prevedNaCamel($text, '_', $prveMale);
    }

    /**
     ** Prevedie CamelCase na pomlčky
     * @param string $text Text na prevedenie
     * @return string Prevedený text
     */
    public static function camelNaPomlcky($text)
    {
        return self::prevedZCamel($text, '-');
    }

    /**
     ** Prevedie CamelCase na podčiarkovník
     * @param string $text Text na prevedenie
     * @return string Prevedený text
     */
    public static function camelNaPodciarkovnik($text)
    {
        return self::prevedZCamel($text, '_');
    }

    /**
     ** Vygeneruje náhodný textový reťazec
     * @param string $od ASCII znak od ktorého chceme generovať
     * @param string $do ASCII znak do ktorého chceme generovať
     * @param int $dlzka Dĺžka reťazca
     * @return string Výsledný reťazec
     */
    private static function nahodnyRetazec($od, $do, $dlzka)
    {
        $retazec = '';
        if ($dlzka > 1)
            $retazec .= self::nahodnyRetazec($od, $do, --$dlzka);
        return $retazec . chr(rand(ord($od), ord($do))); // chr -> získanie znaku pomocou ASCII kodu; ord -> získanie ASCII kodu zo znaku
    }

    /**
     ** Vygeneruje náhodne heslo
     * @param bool $pridatSpecialnyZnak či chcem pridať špeciálny znak
     * @return string Náhodne heslo
     */
    public static function generujHeslo($pridatSpecialnyZnak = false)
    {
        $cisla = self::nahodnyRetazec('0', '9', 3);
        $malePismena = self::nahodnyRetazec('a', 'z', 2);
        $velkePismena = self::nahodnyRetazec('A', 'Z', 2);
        $specialneZnaky = array('!', '@', '#', '$', '%', '^', '&', '*', '(', ')', '.', ',');
        $heslo = $cisla . $malePismena . $velkePismena;
        if ($pridatSpecialnyZnak)
            $heslo .= $specialneZnaky[array_rand($specialneZnaky)];
        return str_shuffle($heslo);
    }

    /**
     ** Vráti retazec od začiatku po určitý znak
     * @param string $retazec Reťazec z ktorrého získavam podreťazec
     * @param string $znak Znak po ktorý chem reťazec získať
     * @param false $vratane či chem vrátit retaže vrátane znaku
     * @return string Nový reťazec
     */
    public static function vratPoZnak($retazec, $znak, $vratane = false)
    {
        if (($pozicia = mb_strpos($retazec, $znak)) !== false) // ak obsahuje znak tak ho skrátim
            return mb_substr($retazec, 0, $pozicia + ($vratane ? (mb_strlen($vratane) + mb_strlen($znak)) : 0));

        return false;
    }

    /**
     ** Vráti reťazec od Znaku po koniec reťazca
     * @param string $retazec Reťazec z ktorrého získavam podreťazec
     * @param string $znak Znak od ktorého chem reťazec získať
     * @param false $vratane či chem vrátit retaže vrátane znaku
     * @return string Nový reťazec
     */
    public static function vratOdZnaku($retazec, $znak, $vratane = false)
    {
        if (($pozicia = mb_strpos($retazec, $znak)) !== false)
            return mb_substr($retazec, $pozicia + ($vratane ? 0 : mb_strlen($znak)), mb_strlen($retazec));
        return false;
    }

    /**
     ** Vráti retace, ktoré sa nachadzajú medzi dvoma znakmi/retazcami vrátane
     * @param string $retazec Retažec v ktorom hľadám
     * @param string $zacina Začiatočny retazec
     * @param string $konci Koncový reťazec
     * @param bool $vratane či chem vrátit retaže vrátane znaku
     * @return array|mixed Najdené reťazce
     */
    public static function vratRetazecMedzi($retazec, $zacina, $konci)
    {
        $retazecOdZnaku = self::vratOdZnaku($retazec, $zacina); // reťazec od hľadaného znaku po koniec
        $retazecMezi = self::vratPoZnak($retazecOdZnaku, $konci); // reťazec od začiatku po hľadaný znak

        if ($retazecMezi)  // ak sa vráti novy reťazec znamená to, že sa koniec našiel
            return $retazecMezi;

        return false;
    }
}

/* Autor: http://www.itnetwork.cz */

/*
 * Niektoré časti sú upravené
 * Autor: MiCHo
 */