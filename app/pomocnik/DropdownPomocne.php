<?php


/**
 ** Trieda na vytvorenie dropdownu
 * Class DropdownPomocne
 */
class DropdownPomocne
{

    /**
     ** Vytvorý drop -down -right atď. Podľa zadania
     * @param string$smer Určuje Smer drop -down -right atď.
     * @param array $parametre Pole odkazov array(url => nazov)
     * @return mixed HTML kod drop-downu -rightu, ...
     */
    /**
     ** Vytvorý drop -down -right atď. Podľa zadania
     * @param string $smer Určuje Smer drop -down -right atď.
     * @param string $nazov Názov zostaveoveneho dropdown tlacidla
     * @param array $polozky Pole poloziek menu
     * @return string HTML kod drop-downu -rightu, ...
     */
    public static function drop($smer, $nazov, array $polozky)
    {
        $html = '<div class="drop' . $smer . ' ">';

        if (count($polozky) <= 1) // aj je položky url tak
        {
            $polozka = array_shift($polozky);
            $html .= '<a class="btn btn-primary ' . $polozka['aktivna'] . '" href="' . $polozka['url'] . '">' . $nazov . '</a>';
        }
        else
        {
            $menu = '';
            $aktivna = 0;
            foreach ($polozky as $nazovPod => $param) // koli tomu to tu je aby som vedel pridať hlavnemu tlačidlu aktivnu triedu
            {
                $menu .= '<a class="dropdown-item ' . $param['aktivna'] . '" href="' . $param['url'] . '">' . $nazovPod . '</a>';
                $param['aktivna'] === 'aktivna' ? $aktivna++ : '';
            }

            $aktivna = $aktivna > 0 ? ' aktivna-tlacidlo ' : '';
            $html .= '<button class="btn btn-primary dropdown-toggle  ' . $aktivna . '" type="button" id="dropdown-tlacidlo" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        ' . $nazov . '
                     </button>';

            $html .= '<div class="dropdown-menu dropdown-menu-left bg-white border-dark" aria-labelledby="dropdown-tlacidlo">';

            $html .= $menu;

            $html .= '</div>';
        }
        $html .= '</div>';
        return $html;
    }
}
/*
 * Autor: MiCHo
 */