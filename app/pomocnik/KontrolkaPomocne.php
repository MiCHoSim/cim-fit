<?php

use App\ZakladModul\System\Kontroler\Kontroler;

/**
 ** Trieda slúži na zostavenie ovladacích kontroliek
 * Class KontrolkaPomocne
 */
class KontrolkaPomocne
{
    /**
     ** Vytvorí ovladaciu kontrolku na ktok späť
     * @param string $url Url adresi an rpesmerovanie späť
     * @return string Html Hod kontrolky
     */
    public static function spat($url)
    {
        $kontrolka =
            '<a href="' . $url . '" class="btn btn-light btn-sm border-dark kontrolka">
                <i title="Späť" class="fa fa-backward"></i>
             </a>';
        return $kontrolka;
    }

    /**
     ** Vytvori ovladaciu kontrolku pre zrúšenie
     * @param string $dataDismis JE to v podstate Trieda ktorou JS bootstrapu reaguje na klik modal/alert/...
     * @return string Html Hod kontrolky
     */
    public static function zrusit($dataDismis)
    {
        $kontrolka =
            '<button type="button" class="close" data-dismiss="' . $dataDismis . '" aria-label="Zavrieť">
                <i class="fa fa-times-circle" aria-hidden="true"></i>
             </button>   ';
        return $kontrolka;
    }

    /**
     ** Vytvori Kontrolku pre informačnú správu
     * @param string $typ Týp správi ktorý je k dispozícíí pri správe
     * @return string Html Hod kontrolky
     */
    public static function spravaKontrolka($typ)
    {
        $typKontrolky = array(
                    Kontroler::SPR_INFO => 'fa fa-info-circle',
                    Kontroler::SPR_USPECH=> 'fa fa-check-circle',
                    Kontroler::SPR_CHYBA => 'fa fa-exclamation-circle');
        $kontrolka =
            '<i class="' . $typKontrolky[$typ] . ' p-0 pr-2" aria-hidden="true"></i>';
        return $kontrolka;
    }
}
/*
 * Autor: MiCHo
 */