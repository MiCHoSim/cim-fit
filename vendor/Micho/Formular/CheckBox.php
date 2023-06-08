<?php

namespace Micho\Formular;


/**
 ** Formulárová Kontrolka/Prvok CheckBox
 * Class CheckBox
 * @package Micho\Formular
 */
class CheckBox extends Kontrolky
{
    /**
     ** Zakladné názvy konttrolky na úpravu
     */
    const NAZOV = 'nazov';
    const ZASKRTNUTY = 'zaskrtnuty';
    const POPISOK = 'popisok';
    const TRIEDA_LABEL = 'triedaLabel';

    /**
     * @var string Parametre potrebné pre zostavenie Kontrolky
     */
    protected $nazov;
    protected $hodnota;
    public $zaskrtnuty;
    protected $popisok;
    protected $triedaLabel;

    /**
     * CheckBox constructor.
     * @param string $nazov label & placeholder - Názov pre kontrolku
     * @param string $nazovDb name & id - Kontrolky, popripade názov ako je ulozená hodnota v DB
     * @param string $hodnota value - Hodnota ktorú ma kontrolka vyplnenú
     * @param false $zaskrtnuty checked - Či ma byť zaškrtnutý
     * @param string $popisok title - Popisok pre kontrolku
     * @param string $formular form - V prípade ze je kontrolka mimo formulára treba udať ku ktorému formuláru patrý
     * @param string $trieda class - Trieda kontrolky
     * @param string $triedaLabel class - pre Label
     * @param bool $pozadovany required - či musí byť kontrolka vyplnená
     * @param string $atributy Všetky ostatné atributy ktore chem naviše
     */
    public function __construct($nazov, $nazovDb, $hodnota, $zaskrtnuty, $popisok, $formular, $trieda, $triedaLabel, $pozadovany, $atributy)
    {
        $this->nazov = $nazov;
        $this->hodnota = $hodnota;
        $this->zaskrtnuty = $zaskrtnuty;
        $this->popisok = $popisok;
        $this->triedaLabel = $triedaLabel;
        parent::__construct($nazovDb, $formular, $trieda, $pozadovany, $atributy);
    }

    /**
     * @return string Vytvorí HTML kontrolku
     */
    public function vytvorKontrolku()
    {
        $required = '';
        $checked = '';
        $form = '';
        $atributy = '';

        if (!empty($this->formular))
        {
            $form = 'form="' . $this->formular . '"';
        }
        if ($this->pozadovany)
        {
            $required = ' required ';
        }
        if ($this->zaskrtnuty)
        {
            $checked = ' checked ';
        }
        if ($this->atributy)
        {
            $atributy = ' ' . $this->atributy . ' ';
        }

        return '<label 
                            class="' . $this->triedaLabel . '" 
                            for="' . $this->nazovDb . '" 
                            title="' . $this->popisok . '">
                           <input 
                                ' . $form . '
                                class="' . $this->trieda . '" 
                                type="checkbox" 
                                name="' . $this->nazovDb . '" 
                                value="' . $this->hodnota . '" 
                                id="' . $this->nazovDb . '" 
                                ' . $required . $checked . ' ' . $atributy . '/>
                           ' . $this->nazov . '                   
                      </label>';
    }

    /**
     ** Zaskrtne kontrolku
     */
    public function zaskrtni()
    {
        $this->zaskrtnuty = true;

    }
}
/*
 * Autor: MiCHo
 */
