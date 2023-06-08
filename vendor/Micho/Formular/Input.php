<?php

namespace Micho\Formular;


use App\ZakladModul\System\Kontroler\SmerovacKontroler;

/**
 ** Formulárová Kontrolka/Prvok Input
 * Class Input
 * @package Micho\Formular
 */
class Input extends Kontrolky
{
    /**
     ** Zakladné názvy konttrolky na úpravu
     */
    const NAZOV = 'nazov';
    const TYP = 'typ';
        const TYP_PASSWORD = 'password';
        const TYP_HIDDEN = 'hidden';
        const TYP_TEXT = 'text';
        const TYP_EMAIL = 'email';
        const TYP_TEL = 'tel';
        const TYP_DATE = 'date';
        const TYP_TIME = 'time';
        const TYP_COLOR = 'color';
        const TYP_NUMBER = 'number';


    const HODNOTA = 'hodnota';
    const TRIEDA_LABEL = 'triedaLabel';
    const VZOR = 'vzor';
    const ZABLOKOVANY = 'zablakovany';

    /**
     * @var string Parametre potrebné pre zostavenie Kontrolky
     */
    protected $nazov;
    protected $typ;
    protected $hodnota;
    protected $triedaLabel;
    protected $vzor;
    protected $zablakovany;


    /**
     ** Input constructor.
     * @param string $nazov label & placeholder - Názov pre kontrolku
     * @param string $nazovDb name & id - Kontrolky, popripade názov ako je ulozená hodnota v DB
     * @param string $typ type - O aky typ kontrolky sa jedna text/number/email/tel,...
     * @param string $hodnota value - Hodnota ktorú ma kontrolka vyplnenú
     * @param string $formular form - V prípade ze je kontrolka mimo formulára treba udať ku ktorému formuláru patrý
     * @param string $trieda class - Trieda kontrolky
     * @param string $triedaLabel class - pre Label
     * @param bool $pozadovany required - či musí byť kontrolka vyplnená
     * @param false|array $vzor pattern - Patern/Vzor/Pravidlo pre kontrolku array(popis,pattern)
     * @param false $zablakovany Či sa dá meniť hodnota prvku
     * @param string $atributy Všetky ostatné atributy ktore chem naviše
     */
    public function __construct($nazov, $nazovDb, $typ, $hodnota, $formular, $trieda, $triedaLabel, $pozadovany, $vzor, $zablakovany, $atributy)
    {
        $this->nazov = $nazov;
        $this->typ = $typ;
        $this->hodnota = $hodnota;
        $this->triedaLabel = $triedaLabel;
        $this->vzor = $vzor;
        $this->zablakovany = $zablakovany;

        $pozadovany = $this->typ === self::TYP_HIDDEN ? false : $pozadovany; // pre Input type Hidden nieje podporovaný required

        parent::__construct($nazovDb, $formular, $trieda, $pozadovany, $atributy);
    }

    /**
     * @return string Vytvorí HTML kontrolku
     */
    public function vytvorKontrolku()
    {
        $required = '';
        $title = '';
        $pattern = '';
        $disabled = '';
        $form = '';
        $atributy = '';
        $placeholder = '';
        $id = '';

        $label = '';

        if (!empty($this->formular))
        {
            $form = 'form="' . $this->formular . '"';
        }
        if ($this->pozadovany)
        {
            $required = ' required ';
        }
        if ($this->vzor)
        {
            $title = 'title = "' . $this->vzor['popis'] . '"';
            $pattern = 'pattern = "' . $this->vzor['pattern'] . '"';
        }
        if ($this->zablakovany)
        {
            $disabled = ' disabled ';
        }
        if ($this->atributy)
        {
            $atributy = ' ' . $this->atributy . ' ';
        }

        if($this->typ !== self::TYP_HIDDEN) // pre Input type Hidden nieje podporovaný Placeholder
            $placeholder = 'placeholder="' . $this->nazov . '"';

        if($this->nazovDb !== 'csrf') // pre Input s názvom csrf sa nebude generovať id ktoré je rovnaké ako Name, kvôli duplicitným ID a kedže nieje ID tak negenerujem ani label lebo ho neviem prideliť bez ID
        {
            $id = 'id="' . $this->nazovDb . '"';
            $label = '<label
                        class="' . $this->triedaLabel . '"
                        for="' . $this->nazovDb . '">
                        ' . $this->nazov . '
                  </label>';
        }
        $input = '<input
                        ' . $form . '
                        value="' . $this->hodnota . '"
                        class="' . $this->trieda . '"
                        type="' . $this->typ . '"
                        name="' . $this->nazovDb . '"
                        ' . $id . '
                        ' . $placeholder . '
                        ' . $title . '
                        ' . $pattern . '
                        ' . $required . $disabled . ' ' . $atributy . '
                 />';
        return $label . $input;
    }

    /**
     **Vráti Vzor/Pattern podľa ktorej sa kontroluje správnosť údajov vo formulári
     * @return string vzor/pattern
     */
    public function vratVzor()
    {
        if ($this->vzor)
            return $this->vzor['pattern'];
        return false;
    }
}
/*
 * Autor: MiCHo
 */
