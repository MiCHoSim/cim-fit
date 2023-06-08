<?php

namespace Micho\Formular;

use App\ZakladModul\System\Kontroler\SmerovacKontroler;
use micho\Utility\Retazec;
use Micho\Formular\Validator;

/**
 ** Formulárová Kontrolka/Prvok TextArea
 * Class TextArea
 * @package Micho\Formular
 */
class TextArea extends Kontrolky
{
    /**
     ** Zakladné názvy konttrolky na úpravu
     */
    const NAZOV = 'nazov';
    const PLACEHOLDER = 'placeholder';
    const HODNOTA = 'hodnota';
    const RIADKY = 'riadky';
    const TRIEDA_LABEL = 'triedaLabel';

    /**
     * @var string Parametre potrebné pre zostavenie Kontrolky
     */
    protected $nazov;
    protected $placeholder;
    protected $hodnota;
    protected $riadky;
    protected $triedaLabel;

    /**
     * TextArea constructor.
     * @param string $nazov label - Názov pre kontrolku
     * @param string $nazovDb name & id - Kontrolky, popripade názov ako je ulozená hodnota v DB
     * @param string $placeholder placeholder - Placeholder pre Kontrolku
     * @param string $hodnota value - Hodnota ktorú ma kontrolka vyplnenú
     * @param string $formular form - V prípade ze je kontrolka mimo formulára treba udať ku ktorému formuláru patrý
     * @param int $riadky rows - Počet riadkov Kontrolky
     * @param string $trieda class - Trieda kontrolky
     * @param string $triedaLabel class - pre Label
     * @param bool $pozadovany required - či musí byť kontrolka vyplnená
     * @param string $atributy Všetky ostatné atributy ktore chem naviše
     */
    public function __construct($nazov, $nazovDb, $placeholder, $hodnota, $formular, $riadky, $trieda, $triedaLabel, $pozadovany, $atributy)
    {
        $this->nazov = $nazov;
        $this->placeholder = $placeholder;
        $this->hodnota = $hodnota;
        $this->riadky = $riadky;
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

        $label = '<label
                        class="' . $this->triedaLabel . '"
                        for="' . $this->nazovDb . '">
                        ' . $this->nazov . '
                  </label>';
        $input = '<textarea
                        ' . $form . '
                        class="' . $this->trieda . '"
                        name="' . $this->nazovDb . '"
                        id="' . $this->nazovDb . '"
                        placeholder="' . $this->placeholder . '"
                        ' . $required . '
                        wrap="hard"
                        rows="' . $this->riadky . '" cols="1" ' . $atributy . '>' . $this->hodnota . '</textarea>';
        return  $label . $input;
    }
}
/*
 * Autor: MiCHo
 */
