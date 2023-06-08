<?php

namespace Micho\Formular;


/**
 ** Formulárová Kontrolka/Prvok submit
 * Class Submit
 * @package Micho\Formular
 */
class Submit extends Kontrolky
{
    /**
     ** Zakladné názvy konttrolky na úpravu
     */
    const HODNOTA = 'hodnota';

    /**
     * @var string Parametre potrebné pre zostavenie Tlačídla
     */
    protected $hodnota;

    /**
     * Submit constructor.
     * @param string $hodnota value - Hodnota ktorú ma kontrolka vyplnenú -> Názov Tlačidla
     * @param string $nazovDb name & id - Kontrolky, popripade názov ako je ulozená hodnota v DB
     * @param string $formular form - V prípade ze je kontrolka mimo formulára treba udať ku ktorému formuláru patrý
     * @param string $trieda class - Trieda kontrolky
     * @param string $atributy Všetky ostatné atributy ktore chem naviše
     */
    public function __construct($hodnota, $nazovDb, $formular = '', $trieda = 'btn btn-lg btn-outline-danger btn-block', $atributy)
    {
        $this->hodnota = $hodnota;
        parent::__construct($nazovDb, $formular, $trieda, $pozadovany = true, $atributy);
    }

    /**
     * @return string Vytvorí HTML Tlačidlo
     */
    public function vytvorKontrolku()
    {
        $form = '';
        $atributy = '';

        if(!empty($this->formular))
        {
            $form = 'form="' . $this->formular . '"';
        }
        if ($this->atributy)
        {
            $atributy = ' ' . $this->atributy . ' ';
        }

        return '<input 
                            ' . $form . '
                            class="' . $this->trieda . '" 
                            type="submit" 
                            name="' . $this->nazovDb . '" 
                            value="' . $this->hodnota . '" 
                            id="' . $this->nazovDb . '" ' . $atributy . '
                       />';
    }
}
/*
 * Autor: MiCHo
 */
