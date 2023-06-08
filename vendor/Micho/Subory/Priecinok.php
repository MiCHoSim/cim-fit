<?php

namespace Micho\Subory;


/**
 ** Tríeda služiaca na správu priečinkov
 * Class Priecinok
 * @package Micho
 */
class Priecinok
{

    /**
     ** Zisti, či existuje priečinok ak nie tak ho vytvori
     * @param string $cesta CEsta umiestnenia priecinka
     * @return bool
     */
    public static function vytvorPriecinok($cesta)
    {
        if (file_exists($cesta))
            return true;

        mkdir($cesta, 0777);
        return $cesta;
    }

    /**
     ** Nájde podpriečinky vymaže z nich súbory a nakoniec vymaže aj hlavny priečinok
     * @param string $cesta Cesta k pričinku
     */
    public static function vymazPriecinok($cesta)
    {
        if (!file_exists($cesta))
            return false;

        $priecinky = scandir($cesta);

        array_shift($priecinky);
        array_shift($priecinky);

        if ($priecinky)
        {
            foreach ($priecinky as $podPriecinok)
            {
                if(mb_strpos($podPriecinok,'.') !== false)
                {
                    unlink($cesta . '/' . $podPriecinok);
                }
                else
                {
                    $novaCesta = $cesta . '/' . $podPriecinok;
                    self::vymazPriecinok($novaCesta); //rekruzivne prejde vŠetky prvky
                }
            }
        }
        rmdir($cesta);
    }

}
/*
 * Autor: MiCHo
 */