<?php


namespace Micho\Antispam;



/**
 ** Antispam použivajúci k Overeniu Aktuálny rok
 * Class AntispamRok
 * @package Micho\Antispam
 */
class AntispamRok implements Antispam
{
    /*
    public function vypis()
    {
        echo('Zadejte aktuální rok: ');
        echo('<input type="text" name="overeni" />');
    }
    **/

    /**
     ** Overí spravnosť vyplnenia antispamu
     * @param int $rok Rok zadaný uživateľom
     * @throws ChybaUzivatela Ak je antispam zle vyplnený tak sa vyvolá vynimka
     */
    public function over($rok)
    {
        if($rok != date('Y'))
            return 'Chybne vyplnená hodnota: Antispam.';
    }
}
/*
 * Autor: MiCHo
 */