<?php

namespace App\AdministraciaModul\Administracia\Model;

use App\AdministraciaModul\Administracia\Kontroler\AdministraciaKontroler;
use Micho\ChybaUzivatela;
use Micho\Subory\Subor;
use Micho\Utility\Retazec;

/**
 ** Správca Administračného menu
 * Class AdministraciaManazer
 * @package App\AdministraciaModul\Administracia\Model
 */
class AdministraciaManazer
{
    /**
     ** Zostavý administraČné menu
     * @param string $url Url prave navstivenej Adresy
     * @return AdministraciaKontroler Objekt administacneho menu
     */
    public function zostavMenu($url)
    {
        $menu = new AdministraciaKontroler();
        $menu->menu($url);
        return $menu;
    }

    /**
     ** zmení kod, zahasuje uloží ho do súboru
     * @param $kod
     */
    public function zmenKod($kod)
    {
        //$koder = array('1' => 'a','3' => 'b', '7' => 'c', '9' => 'd');
        //$kod = strtr($kod, $koder);
        //$kod = strtr($kod, array_flip($koder));
        $subor = new Subor();
        $subor->ulozSubor($kod, 'subory/Data/kod.txt');

    }

    /**
     ** Načíta Kod
     * @return string
     * @throws ChybaUzivatela
     */
    public function nacitajKod()
    {
        $subor = new Subor();
        $kod = $subor->nacitajSubor('subory/Data/kod.txt');
        $kod = Retazec::vratOdZnaku($kod, ' ');
        return $kod;
    }
}

/*
 * Autor: MiCHo
 */