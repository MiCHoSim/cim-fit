<?php

namespace Micho\Formular;


use App\ZakladModul\System\Kontroler\SmerovacKontroler;

/**
 ** Kontrolkový predok pre kontorlky
 * Class Kontrolky
 * @package Micho\Formular
 */
abstract class Kontrolky
{
    /**
     ** Zakladné názvy konttrolky na úpravu
     */
    const NAZOV_DB = 'nazovDb';
    const FORMULAR = 'formular';
    const TRIEDA = 'trieda';
    const POZADOVANY = 'pozadovany';

    /**
     * @var string Parametre potrebné pre zostavenie Kontrolky
     */
    protected $nazovDb;
    protected $formular;
    protected $trieda;
    protected $pozadovany;
    protected $atributy;

    /**
     * Kontrolky constructor.
     * @param string $nazovDb name & id - Kontrolky, popripade názov ako je ulozená hodnota v DB
     * @param string $formular form - V prípade ze je kontrolka mimo formulára treba udať ku ktorému formuláru patrý
     * @param string $trieda class - Trieda kontrolky
     * @param bool $pozadovany required - či musí byť kontrolka vyplnená
     * @param string $atributy Všetky ostatné atributy ktore chem naviše
     */
    public function __construct($nazovDb, $formular, $trieda, $pozadovany, $atributy)
    {
        $this->nazovDb = $nazovDb;
        $this->formular = $formular;
        $this->trieda = $trieda;
        $this->pozadovany = $pozadovany;
        $this->atributy = $atributy;
    }

    /**
     * @return string Vytvorí HTML kontrolku
     */
    public abstract function vytvorKontrolku();

    /**
     ** Upravý parametre kontrolky
     * @param array $parametre Pole kde klúče su názvy parametrov a hodnoty sú nové hodnoty parametrov
     */
    public function upravParametre(array $parametre)
    {
        $smerovacKontroler = new SmerovacKontroler();
        foreach ($parametre as $parameter => $hodnota)
        {
            $this->$parameter = $smerovacKontroler->osetri($hodnota);
        }
    }

    /**
     ** Vráti Hodnotu požadovaného parametru
     * @return string Názov kontrolky
     */
    public function vratParameter($parameter)
    {
        return $this->$parameter;
    }
}
/*
 * Autor: MiCHo
 */
