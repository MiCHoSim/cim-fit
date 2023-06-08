<?php

namespace App\SutazModul\Kontroler;


use App\AdministraciaModul\Uzivatel\Model\OsobaManazer;
use App\AdministraciaModul\Uzivatel\Model\UzivatelManazer;
use App\SutazModul\Model\SutazManazer;
use App\SutazModul\Model\SutazPrihlasenyManazer;
use App\SutazModul\Model\SutazTypManazer;
use App\ZakladModul\SpravaPoziadaviek\Model\SpravaPoziadaviekManazer;
use App\ZakladModul\System\Kontroler\Kontroler;
use Micho\Utility\DatumCas;

/**
 ** Spracovanie Sútaži
 * Class SutazKontroler
 * @package App\SutazModul\Kontroler
 */
class SutazKontroler extends Kontroler
{
    /**
     ** Zobrazenie konkrétnej Súťažea prihlasenie sa na ňu
     * @Action
     */
    public function index()
    {
        $sutazManazer = new SutazManazer();

        $osobaManazer = new OsobaManazer();

        $osobaId = isset(UzivatelManazer::$uzivatel) ? $osobaManazer->vratOsobaId(UzivatelManazer::$uzivatel['uzivatel_id']) : '';

        //Načitanie Súťaže z DB
        $sutaze = $sutazManazer->vratSutazeZoznam($osobaId,true);

        $this->data['sutaze'] = $sutaze;

        $this->data['prihlaseny'] = isset(UzivatelManazer::$uzivatel);

        $this->data['presmeruj'] = self::$aktualnaUrl;

        $this->pohlad = 'index';
    }

    /**
     ** Prihlasenie sa na konkrétnu súťaž
     * @param int $sutazId Id sútaze na ktoru sa prihlasuje
     * @Action
     */
    public function prihlas($sutazId)
    {
        $this->overUzivatela();

        $sutazPrihlasenyManazer = new SutazPrihlasenyManazer();
        $osobaManazer = new OsobaManazer();

        if (!$sutazPrihlasenyManazer->overMoznostAkcie($sutazId))
        {
            $this->pridajSpravu('Na túto sútaž sa už nedá prihlásiť', self::SPR_CHYBA);
            $this->presmeruj();
        }

        $osobaId = $osobaManazer->vratOsobaId(UzivatelManazer::$uzivatel['uzivatel_id']);

        //overenie ci uz nieje na sutaz registrovany
        if (!$sutazPrihlasenyManazer->overExistenciuPrihlasenia($sutazId, $osobaId))
        {
            // ulozenie prihlasenia do DB
            $sutazPrihlasenyManazer->ulozPrihlasenie(array(SutazPrihlasenyManazer::SUTAZ_ID => $sutazId, SutazPrihlasenyManazer::OSOBA_ID => $osobaId));

            $this->pridajSpravu('Boli ste prihlasený na sútaž, tešíme sa na Vás', self::SPR_USPECH);
        }
        else
            $this->pridajSpravu('Na túto sútaž ste už prihlasený', self::SPR_INFO);

        //presmerovnaie naspat
        $this->presmeruj();
    }

    /**
     ** Odhlásenie sa zo súŤaže
     * @param int $sutazId Id sútaze na ktoru sa prihlasuje
     * @Action
     */
    public function odhlas($sutazId)
    {
        $this->overUzivatela();

        $sutazPrihlasenyManazer = new SutazPrihlasenyManazer();
        $osobaManazer = new OsobaManazer();

        if (!$sutazPrihlasenyManazer->overMoznostAkcie($sutazId))
        {
            $this->pridajSpravu('Zo súťaže sa už nedá odhlásiť', self::SPR_CHYBA);
            $this->presmeruj();
        }

        $osobaId = $osobaManazer->vratOsobaId(UzivatelManazer::$uzivatel['uzivatel_id']);

        //odstránenie sútaze
        if ($sutazPrihlasenyManazer->odstranPrihlasenie($sutazId, $osobaId))
        {
            $this->pridajSpravu('Boli ste odhlásený zo Súťaže', self::SPR_USPECH);
        }
        else
            $this->pridajSpravu('Na túto sútaž ešte nieste prihlasený', self::SPR_INFO);

        //presmerovnaie naspat
        $this->presmeruj();
    }

}

/*
 * Autor: MiCHo
 */