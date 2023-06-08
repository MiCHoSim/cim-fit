<?php

namespace Micho\Formular;


use App\ZakladModul\System\Kontroler\SmerovacKontroler;

/**
 ** Formulárová Kontrolka/Prvok Input
 * Class Input
 * @package Micho\Formular
 */
class File extends Kontrolky
{
    /**
     ** Zakladné názvy konttrolky na úpravu
     */
    const NAZOV = 'nazov';
    const TRIEDA_LABEL = 'triedaLabel';
    const VIACNASOBNY = 'viacnasobny';
    const AKCEPTOVANE = 'akceptovane';
            const AUDIO = 'audio/*';
            const VIDEO = 'video/*';
            const IMAGE = 'image/*';
            const PNG = 'image/png';
            const JPG = 'image/jpeg';

    /**
     * @var string Parametre potrebné pre zostavenie Kontrolky
     */
    protected $nazov;
    protected $triedaLabel;
    protected $viacnasobny;
    protected $akceptovane;

    /**
     ** File constructor.
     * @param string $nazov label & placeholder - Názov pre kontrolku
     * @param string $nazovDb name & id - Kontrolky, popripade názov ako je ulozená hodnota v DB
     * @param string $formular form - V prípade ze je kontrolka mimo formulára treba udať ku ktorému formuláru patrý
     * @param string $trieda class - Trieda kontrolky
     * @param string $triedaLabel class - pre Label
     * @param bool $pozadovany required - či musí byť kontrolka vyplnená
     * @param bool $viacnasobny multiple - či je povoľený výber viacerých súborov
     * @param string $akceptovane accept - Povoľené formáty z možnosti Konštant
     * @param string $atributy Všetky ostatné atributy ktore chem naviše
     */
    public function __construct($nazov, $nazovDb, $formular, $trieda, $triedaLabel, $pozadovany, $viacnasobny, $akceptovane, $atributy)
    {
        $this->nazov = $nazov;
        $this->triedaLabel = $triedaLabel;
        $this->pozadovany = $pozadovany;
        $this->viacnasobny = $viacnasobny;
        $this->akceptovane = $akceptovane;
        parent::__construct($nazovDb, $formular, $trieda, $pozadovany, $atributy);
    }

    /**
     * @return string Vytvorí HTML kontrolku
     */
    public function vytvorKontrolku()
    {
        $required = '';
        $form = '';
        $multiple = '';
        $accept = '';
        $atributy = '';

        if (!empty($this->formular))
        {
            $form = ' form="' . $this->formular . '" ';
        }
        if ($this->pozadovany)
        {
            $required = ' required ';
        }
        if ($this->viacnasobny)
        {
            $multiple = ' multiple ';
            $this->nazovDb .= '[]';
        }
        if ($this->akceptovane)
        {
            $accept = ' accept="' . $this->akceptovane . '" ';
        }
        if ($this->atributy)
        {
            $atributy = ' ' . $this->atributy . ' ';
        }

        $label = '<label 
                        class="' . $this->triedaLabel . '" 
                        for="' . $this->nazovDb . '">    
                        ' . $this->nazov . '
                  </label>';
        $file = '<input 
                        ' . $form . '
                        class="' . $this->trieda . '" 
                        type="file" 
                        name="' . $this->nazovDb . '" 
                        id="' . $this->nazovDb . '" 
                        ' . $required . $multiple . $accept . ' ' . $atributy . '
                 />';
        return $label . $file;
    }
}
/*
 * Autor: MiCHo
 */
