<?php

namespace Micho\Formular;



/**
 ** Formulárová Kontrolka/Prvok Select
 * Class Select
 * @package Micho\Formular
 */
class Select extends Kontrolky
{
    /**
     ** Zakladné názvy konttrolky na úpravu
     */
    const NAZOV = 'nazov';
    const MOZNOSTI = 'moznosti';
    const HODNOTA = 'hodnota';
    const VIACNASOBNY = 'viacnasobny';
    const TRIEDA_LABEL = 'triedaLabel';

    /**
     * @var string Parametre potrebné pre zostavenie Kontrolky
     */
    protected $nazov;
    protected $moznosti;
    protected $hodnota;
    protected $viacnasobny;
    protected $triedaLabel;

    /**
     * Select constructor.
     * @param string $nazov label & placeholder - Názov pre kontrolku
     * @param string $nazovDb name & id - Kontrolky, popripade názov ako je ulozená hodnota v DB
     * @param array $moznosti Pole možnosti kde klúč je názov a hodnota je Hodnota value
     * @param string $hodnota Hodnota práve vybratého SELECTU
     * @param bool $viacnasobny multiple - či je povoľený výber viacerých súborov
     * @param string $formular form - V prípade ze je kontrolka mimo formulára treba udať ku ktorému formuláru patrý
     * @param string $trieda class - Trieda kontrolky
     * @param string $triedaLabel class - pre Label
     * @param bool $pozadovany required - či musí byť kontrolka vyplnená
     * @param string $atributy Všetky ostatné atributy ktore chem naviše
     */
    public function __construct($nazov, $nazovDb, array $moznosti, $hodnota, $viacnasobny, $formular, $trieda, $triedaLabel, $pozadovany, $atributy)
    {
        $this->nazov = $nazov;
        $this->moznosti = $moznosti;
        $this->hodnota = $hodnota;
        $this->viacnasobny = $viacnasobny;
        $this->triedaLabel = $triedaLabel;
        parent::__construct($nazovDb, $formular, $trieda, $pozadovany, $atributy);
    }

    /**
     * @return string Vytvorí HTML kontrolku
     */
    public function vytvorKontrolku()
    {
        $required = '';
        $form = '';
        $atributy = '';
        $multiple = '';

        if (!empty($this->formular))
        {
            $form = 'form="' . $this->formular . '"';
        }

        if ($this->pozadovany)
        {
            $required = ' required ';
        }
        if ($this->atributy)
        {
            $atributy = ' ' . $this->atributy . ' ';
        }
        if ($this->viacnasobny)
        {
            $multiple = ' multiple ';
            $this->nazovDb .= '[]';
        }


        $label = '<label class="' . $this->triedaLabel . '"
                         for="' . $this->nazovDb . '">
                         ' . $this->nazov . '
                  </label>';
        $options = '';


        if(is_array($this->hodnota)) // kvoli multiple select
            $i = 0;

        foreach ($this->moznosti  as $nazov => $hodnota)
        {
            $selected = '';

            if(isset($i) &&  isset($this->hodnota[$i]) && $this->hodnota[$i] == $hodnota) // kvoli multiple select
            {
                $selected = ' selected ';
                $i++;
            }
            elseif ($this->hodnota == $hodnota)
                $selected = ' selected ';

            $options .= '
                    <option 
                        ' . $form . '
                        value="' . $hodnota . '"                                             
                        ' . $selected . ' >
                        ' . $nazov . '
                    </option>';
        }

        $select = '<select 
                        ' . $form . '
                        class="' . $this->trieda . '"
                        name="' . $this->nazovDb . '" 
                        id="' . $this->nazovDb . '"
                        ' . $required . $multiple .' ' . $atributy . '>
                        ' . $options . '  
                   </select>';

        return $label . $select;
    }
}
/*
 * Autor: MiCHo
 */
