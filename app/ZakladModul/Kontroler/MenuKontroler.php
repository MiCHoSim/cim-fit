<?php

namespace App\ZakladModul\Kontroler;


use App\ClanokModul\Model\ClanokManazer;
use App\ZakladModul\System\Kontroler\Kontroler;

/**
 ** Spracováva Menu Stránky
 * Class MenuKontroler
 * @package App\ZakladModul\Kontroler\MenuKontroler
 */
class MenuKontroler extends Kontroler
{
    /**
     ** Spracuje Výpis Menu
     * @param string $url Url adresa otvorenej stranky
     */
    public function index($url)
    {
        $clanokManazer = new ClanokManazer();

        $this->data['menu'] = $clanokManazer->vratClankyMenu(true);

        // trieda či majú byť karty menu veľké alebo malé
        $this->data['trieda'] = $url === 'uvod' ? '' : 'karty-mensie';
        $this->pohlad = 'index';  //nastavenie pohladu
    }
}
/*
 * Autor: MiCHo
 */