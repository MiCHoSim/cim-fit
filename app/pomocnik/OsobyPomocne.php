<?php


/**
 ** Pomocná triena na formatovvanie výstupu osôb
 * Class OsobyPomocne
 */
class OsobyPomocne
{
    /**
     *
     * @param $osoba Osoba
     * @return string Z
     */
    /**
     ** Sformatuje adresu osoby
     * @param array $osoba Údaje o osobe
     * @return string Sformatovaná adresa
     */
    public static function adresa(array $osoba)
    {
        $html = $osoba['ulica'] . ' ';
        if ($osoba['supisne_cislo'] && $osoba['orientacne_cislo'])
            $html .= $osoba['supisne_cislo'] . '/' . $osoba['orientacne_cislo'];
        else
            $html .= $osoba['supisne_cislo'] ? $osoba['supisne_cislo'] : $osoba['orientacne_cislo'];
        $html .= '<br>';
        $html .= $osoba['mesto'] . '<br>';
        $html .= $osoba['psc'];

        return $html;
    }

    /**
     ** Sformatuje meno fyzickej osoby, prípadne názov firmy (právnicka osoba)
     * @param array $osoba Údaje o osobe
     * @return mixed|string Sformatovaná adresa
     */
    public static function meno(array $osoba)
    {
        if ($osoba['nazov_spolocnosti'])
            return $osoba['nazov_spolocnosti'];
        if ($osoba['meno'])
            return $osoba['meno'] . ' ' . $osoba['priezvisko'];
        return 'Koncový zakáznik';
    }
}
/* Autor: http://www.itnetwork.cz */

/*
 * Niektoré časti sú upravené
 * Autor: MiCHo
 */