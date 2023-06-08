<?php

namespace App\ClanokModul\Kontroler;

use App\AdministraciaModul\Uzivatel\Model\OsobaManazer;
use App\AdministraciaModul\Uzivatel\Model\UzivatelManazer;
use App\ClanokModul\Model\ClanokManazer;
use App\ClanokModul\Model\ClanokTypManazer;
use App\ZakladModul\SpravaPoziadaviek\Model\SpravaPoziadaviekManazer;
use App\ZakladModul\System\Kontroler\Kontroler;
use Micho\ChybaUzivatela;
use Micho\Subory\Priecinok;
use Micho\Subory\Subor;

/**
 ** Spracováva stránku pre články
 * Class ClanokKontroler
 * @package ClanokModul\Uvod\Kontroler
 */
class ClanokKontroler extends Kontroler
{
    /**
     ** Všetký hodnoty ktoré použivam pri práci z Tabuľkov Člankov
     * @var string[]
     */
    public static $clanokData =  array(ClanokManazer::CLANOK_ID, ClanokManazer::URL, ClanokManazer::TITULOK , ClanokManazer::POPISOK, ClanokManazer::OBSAH, ClanokManazer::VEREJNY,
        ClanokManazer::AUTOR_ID, ClanokManazer::UPRAVIL_AUTOR_ID, ClanokManazer::DATUM_VYTVORENIA, ClanokManazer::DATUM_UPRAVY, ClanokManazer::CLANOK_TYP_ID);

    /**
     ** Spracuje zobrazenie článkov webu
     * @param string $url Url článku
     * @Action
     */
    public function index($url)
    {
        $clanokManazer = new ClanokManazer();
        try
        {
            $clanok = $clanokManazer->vratClanok($url, self::$clanokData);
        }
        catch (ChybaUzivatela $chyba)
        {
            $this->pridajSpravu($chyba->getMessage(), self::SPR_INFO);
            $this->presmeruj('chyba');
        }
        //$autor = false;
        //$clanok[ClanokManazer::VEREJNY] = 0;


        // ak je članok url instrukcie tak ho nezobrazujem až po prihlaseni
        if($url === 'instrukcie' || $url === 'bobby-ceny' || $url === 'gym-ceny')
            $this->overUzivatela();

        $autor = true;
        if ($clanok[ClanokManazer::CLANOK_TYP_ID] === ClanokTypManazer::CLANOK_INFORMACIA || $clanok[ClanokManazer::CLANOK_TYP_ID] === ClanokTypManazer::CLANOK_UVOD || $url === 'gym' || $url === 'bobby') // ak je článok typu info alebo je zo uvidný članok tak nezobrazujem autora
              $autor = false;

        if ($clanok[ClanokManazer::VEREJNY]) // ak je verejný
        {
            // zobrazenie článku
            if ($autor) // zobrazenie článku aj autora
                $clanok = $clanokManazer->pridajAutora($clanok);
        }
        else // ak nieje verejný
        {
            if(UzivatelManazer::$uzivatel && (UzivatelManazer::$uzivatel[UzivatelManazer::ADMIN] || UzivatelManazer::$uzivatel[UzivatelManazer::PROGRAMATOR]))
            {
                // zobrazenie článku
                if ($autor) // zobrazenie článku aj autora
                    $clanok = $clanokManazer->pridajAutora($clanok);
            }
            elseif ($url !== 'uvod')
            {
                $this->overUzivatela(true,true); // overenie ci je prihlaseny admin
                // zobrazenie článku
                if ($autor) // zobrazenie článku aj autora
                    $clanok = $clanokManazer->pridajAutora($clanok);
            }
            else
            {
                $clanok = false;
            }
        }

        SpravaPoziadaviekManazer::$kontroler['titulok'] = $clanok ? $clanok[ClanokManazer::TITULOK] : ' ';

        SpravaPoziadaviekManazer::$kontroler['popisok'] .= $clanok ? ', ' . $clanok[ClanokManazer::TITULOK] . ', ' . $clanok[ClanokManazer::POPISOK] : '';
        SpravaPoziadaviekManazer::$kontroler['autor'] = (isset($clanok['autor']['meno']) ? ($clanok['autor']['meno'] . ' ' . $clanok['autor']['priezvisko']) : '') . (isset($clanok['upravil_autor']['meno']) ? (', ' .$clanok['upravil_autor']['meno'] . ' ' . $clanok['upravil_autor']['priezvisko']) : '');

        $this->data['clanok'] = $clanok;
        $this->data['admin'] = UzivatelManazer::$uzivatel && (UzivatelManazer::$uzivatel[UzivatelManazer::ADMIN] || UzivatelManazer::$uzivatel[UzivatelManazer::PROGRAMATOR]);

        $this->data['presmeruj'] = self::$aktualnaUrl; // presmerovanie po editacii


        if($url === 'o-nas') // ak je to o nás, tak načítam galériu obrázkov
        {
            $this->data['cestaObrazok'] = 'obrazky/galeria/onas/nahlad';
            $this->data['galeria'] = Subor::vratNazvySuborov($this->data['cestaObrazok']);
        }

        $this->pohlad = 'index';
    }


    /**
     ** Spracuje zobrazenie zoznamu článkov webu
     * @param string $typUrl url Typov Článkov ktore chem zobraziť ako akrty
     * @ Action
     */
    public function clanky($typUrl)
    {
        $clanokManazer = new ClanokManazer();

        $clanky = $clanokManazer->vratClankyZoznamKarty($typUrl);

        SpravaPoziadaviekManazer::$kontroler['titulok'] = $clanky ? ClanokTypManazer::TYPY_CLANKOV_URL_NAZOV[$typUrl] : '';
        SpravaPoziadaviekManazer::$kontroler['popisok'] = $clanky ? 'Tipy a návody pre: ' . ClanokTypManazer::TYPY_CLANKOV_URL_NAZOV[$typUrl] : '';

        //priradí članku titulný obrázok
        if(!empty($clanky))
        {
            $clanky = $clanokManazer->priradClankuObrazok($clanky);
        }

        $this->data['clanky'] = $clanky;

        $this->pohlad = 'clanky-karty';
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