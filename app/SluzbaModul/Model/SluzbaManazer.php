<?php

namespace App\SluzbaModul\Model;


use App\SluzbaModul\Kontroler\SluzbaKontroler;

/**
 * Class SluzbaManazer
 * @package App\SluzbyModul\Model
 */
class SluzbaManazer
{
    /**
     ** Zostavý menu
     * @param string $url Url prave navstivenej Adresy
     * @return SluzbaKontroler Objekt Sluzy menu
     */
    public function zostavMenu($url, $typ)
    {
        $menu = new SluzbaKontroler();
        $menu->menu($url, $typ);
        return $menu;
    }
}
/*
 * Autor: MiCHo
 */