<?php



/**
 * Subor formatovacich metod ore strankovanie
 * Class StrankovaniePomocne
 */
class StrankovaniePomocne
{
    /**
     ** Nahradi podreťazec "{strana}" v url adrese zadanim číslom stránky
     * @param string $url URL Adresa
     * @param int $strana Číslo stranky
     * @return string Výsledna URL adresa
     */
    private static function stranaUrl($url, $strana)
    {
        return str_replace('{strana}', $strana, $url);
    }
    
    /**
     ** Vygeneruje widget so strankovaním
     * @param int $strana Aktuálna strana
     * @param int $strany Celkový pocet strán
     * @param string $url URL adresa preprechod na jednotlive stránky s placeholdrem {strana} miesto čisla strany
     * @return string Výsledne stánkovanie
     */
    public static function strankovanie($strana, $strany, $url)
    {
        $polomer = 5; // Poloměr oblasti kolem aktuální stránky
        $html = '<nav class="text-center"><ul class="pagination justify-content-center">';
        // Šípka vľavo
        if ($strana > 1)
            $html .= '<li class="page-item"><a class="page-link" href="' . self::stranaUrl($url, $strana - 1) . '">&laquo;</a></li>';
        else
            $html .= '<li class="page-item disabled"><a class="page-link">&raquo;</a></li>';
        $left = $strana - $polomer >= 1 ? $strana - $polomer : 1;
        $right = $strana + $polomer <= $strany ? $strana + $polomer : $strany;
        // Umiestnenie jednotky
        if ($left > 1)
            $html .= '<li class="page-item"><a class="page-link" href="' . self::stranaUrl($url, 1) . '">1</a></li>';
        // Bodky vľavo
        if ($left > 2)
            $html .= '<li class="page-item disabled"><a class="page-link">&hellip;</a></li>';
        // Stránky v radiuse
        for ($i = $left; $i <= $right; $i++)
        {
            if ($i == $strana) // Aktivní stránka
                $html .= '<li class="page-item active"><a class="page-link">' . $i . '</a></li>';
            else
                $html .= '<li class="page-item"><a class="page-link" href="' . self::stranaUrl($url, $i) . '">' . $i . '</a></li>';
        }
        // Bodky vpravo
        if ($right < $strany - 1)
            $html .= '<li class="page-item disabled"><a class="page-link">' . '&hellip;' . '</a></li>';
        // Umiestnenie poslednej stránky
        if ($right < $strany)
            $html .= '<li class="page-item"><a class="page-link" href="' . self::stranaUrl($url, $strany) . '">' . $strany . '</a></li>';
        // Šípka vpravo
        if ($strana < $strany)
            $html .= '<li class="page-item"><a class="page-link" href="' . self::stranaUrl($url, $strana + 1) . '">&raquo;</a></li>';
        else
            $html .= '<li class="page-item disabled"><a class="page-link">&laquo;</a></li>';
        $html .= '</ul></nav>';
        return $html;
    }
}
/*
 * Tento kód spadá pod licenci ITnetwork Premium - http://www.itnetwork.cz/licence
 * Je určen pouze pro osobní užití a nesmí být šířen ani využíván v open-source projektech.
 */
/*
 * Niektoré časti sú upravené
 * Autor: MiCHo
 */