<?php


/**
 ** Pomocná trieda služiaca na zostávenie modálneho dialogu pomocou Bootstrap
 * Class DialogPomocne
 */
class DialogPomocne
{
    const PRODUKT = 'produkt';
    const DOPRAVA_PLATBA = 'doprava-platba';

    /**
     ** Zostavý Modálny dialog Odstránenia
     * @param int $id Id položky, ktorú chem odstrániť
     * @param string $url Url adresa na metódu pomocov ktorej sa daná položka odstráňuje
     * @param string $text Textová hlášky pri odstránení
     * @param string $tlacidlo Html kod ako ma tlačislo vyzeraŤ
     * @param string $nazovTl Nazov Tlačidla v dialogu
     * @return string Html Kód Modálneho dialogu
     */
    public static function zostavDialogOdstranenia($id, $url, $text, $tlacidlo = false, $nazovTl = 'Odstrániť')
    {
        if (!$tlacidlo)
        {
            $tlacidlo =
                '<a href="#" data-toggle="modal" class="btn btn-light btn-sm border-dark kontrolka" data-target="#dialog_' . $id . '" title="Odstrániť">
                <i class="fa fa-trash-alt"></i>
             </a>';
        }

        $dialog =
            '<div class="modal fade" id="dialog_' . $id . '" tabindex="-1" role="dialog" aria-labelledby="dialog-label" aria-hidden="true">
                <div class="modal-dialog modal-sm" role="document">
                    <div class="modal-content border-info">
                        <div class="modal-header bg-info text-white pb-1">
                            <h4 class="modal-title " id="dialog-label">Odstrániť</h4>
                            ' . KontrolkaPomocne::zrusit('modal') . '
                        </div>
                        <div class="modal-body text-left text-dark">
                           <strong class="text-wrap">' . $text . ' </strong>
                        </div>
                        <div class="modal-footer bg-info py-2">
                            <a href="' . $url . '" class="btn btn-warning text-white">' . $nazovTl . '</a>
                            <button type="button" class="btn btn-info" data-dismiss="modal">Zavrieť</button>
                        </div>
                    </div>
                </div>
             </div>';

        return $tlacidlo . $dialog;
    }

    /**
     ** Zostavý Dialog Carouselu Zobrazenia obrázkov Galérie
     * @param array $galeria Pole názvov Obrázkov
     * @param string $cesta Cesta k obrázkom
     * @return string HTML
     */
    public static function zostavDialogCarouselGaleria($galeria, $cesta)
    {
        $html = '<div class="row no-gutters justify-content-center ">';
        $carousel = '<div id="galeria" class="carousel slide" data-ride="carousel">';
        $carouselIndikator = '<ol class="carousel-indicators ">';
        $carouselObsah = '<div class="carousel-inner" >';

        foreach ($galeria as $kluc => $obrazok)
        {
            $html .= ' <div>
                            <a data-obrazok="' . $kluc . '" href="#" data-toggle="modal" data-target="#dialog_obr">
                                <img class="img-thumbnail img-cover" src="' . $cesta . '/' . $obrazok . '?timestamp=' . time() . '" alt="' . $obrazok . '">
                            </a>
                        </div>';
            $aktivna = $kluc === 0 ? 'active' : '';
            $carouselIndikator .= '<li data-target="#galeria" data-slide-to="' . $kluc . '" class="' . $aktivna . '"></li>';

            $cestaOriginal = str_replace('nahlad', 'original',$cesta) . '/' . $obrazok;

            $carouselObsah .= '<div class="carousel-item ' . $aktivna . '">
                                    <img class="modal-obrazok"  src="' . $cestaOriginal . '?timestamp=' . time() . '" alt="' . $obrazok . '">
                                </div>';
        }
        $html .= '</div>';

        $html .=
            '<div class="modal fade " id="dialog_obr" tabindex="-1" role="dialog" aria-labelledby="dialog-label" aria-hidden="true">
                <div class="modal-dialog modal-velky" role="document">
                    <div class="modal-content border-info ">
                        <div class="modal-header bg-danger text-white">
                        <h4 class="modal-title " id="dialog-label">Fotogaléria</h4>
                            ' . KontrolkaPomocne::zrusit('modal') . '
                        </div>
                        <div class="modal-body p-1 ">
                           ' . $carousel . $carouselIndikator .'

                           </ol>
                           ' . $carouselObsah . '
                           </div>
                               <a class="carousel-control-prev" href="#galeria" role="button" data-slide="prev">
                                   <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                   <span class="sr-only">Predchadzajúci</span>
                               </a>
                               <a class="carousel-control-next" href="#galeria" role="button" data-slide="next">
                                   <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                   <span class="sr-only">Další</span>
                               </a>
                           </div>
                        </div>
                    </div>
                </div>
             </div>';

        return $html;
    }





















    /**
     * zostavi Modálny dialog pomocou Bootstrapu Na zobrazenie obrazka produktu
     * @param $produkt Informacie o produkte
     * @param $urlMiniatury Url adresa na zostavenie buton borazka
     * @param $urlObrazka Url Originalného obrázka
     * @param $title title k obrazku
     * @param $uprava či chcem pridať možnost upravy
     * @param $carousel či cehm pridať carelousel
     * @return string Zostaveny HTML
     */
    private function zostavDialogObrazka($produkt, $urlMiniatury, $urlObrazka, $title, $uprava = false, $carousel = false)
    {
        if($carousel)
        {
            $carousel = '<div id="carousel_' . $produkt['produkt_objednavka_id']  . '_' . $title . '" class="carousel slide" data-interval="false">
                        <ol class="carousel-indicators">';

            for($i = 0; $i < ($produkt['pocet_obr'] && $title === 'Vaša-fotografia' ? $produkt['pocet_obr'] : $produkt['pocet_obrazkov']); $i++)
            {
                $class = $i === 0 ? 'active' : '';
                $carousel .= '<li data-target="#carousel_' . $produkt['produkt_objednavka_id']  . '_' . $title . '" data-slide-to="' . $i . '" class="'. $class . '"></li>
                        ';
            }
            $carousel .= '</ol><div class="carousel-inner text-center">';

            $idObrazka = array_reverse(explode('/', $urlObrazka))[0]; //zistenie id obrazka z Url Obrazka
            $rozmer = isset($produkt['rozmer_obr_id']) ? "rozmer" : "null"; // rozmerovy produkt

            for($i = 0; $i < ($produkt['pocet_obr'] && $title === 'Vaša-fotografia' ? $produkt['pocet_obr'] : $produkt['pocet_obrazkov']); $i++)
            {
                $urlvysledna = $urlObrazka . '_' . $i . '.jpg?timestamp='.time();
                $class = $i === 0 ? 'active' : '';
                $carousel .= '<div class="carousel-item ' . $class . '">

                     ' . ($uprava !== false ? '

                        <div class="text-center">
                            <button
                                    data-typ-obrazka="foto' . $uprava . '"
                                    data-id-obrazka="'. $idObrazka . '"
                                    data-poradove-cislo="' . $i . '"
                                    data-pocet-obr="' . $produkt['pocet_obr'] . '"
                                    data-objednavka-id="' . $produkt['objednavka_id'] . '"
                                    data-rozmer="' . $rozmer . '"
                                    type="button" class="upravit-obrazok border-0 p-0 ">
                                <i title="Upraviť fotografiu" class="far fa-edit border-dark btn btn-warning btn-sm"></i>
                            </button>
                        </div>
                        '
                        : '') . '
                                <img class="slide-image img-fluid" src="' . $urlvysledna . '" alt="' . $produkt['titulok'] . '" title="' . $title . '">
                          </div>';
            }
            $carousel .='</div><a class="carousel-control-prev" href="#carousel_' . $produkt['produkt_objednavka_id']  . '_' . $title . '" role="button" data-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="sr-only">Predchádzajúci</span>
                        </a>
                        <a class="carousel-control-next" href="#carousel_' . $produkt['produkt_objednavka_id']  . '_' . $title . '" role="button" data-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="sr-only">Nasledujúci</span>
                        </a>
                    </div>';
            $obrazky = $carousel;
        }
        else
            $obrazky = '<img title="' . $title . '" src="' . $urlObrazka . ($title === 'Fotografia-sa-nenašla' ? '' : '_0.jpg') . '?timestamp=' . time() . '" class="border-dark img-thumbnail rounded img-fluid" alt="' . $produkt['titulok'] . '" />';

        return '
        <button type="button" class="" data-toggle="modal" data-target="#dialog_' . (isset($produkt['produkt_id']) ? $produkt['produkt_objednavka_id'] : $produkt['doprava_platba_id']) . '_' . $title . '">
            <img title="' . $title . '" src="' . $urlMiniatury . '?timestamp=' . time() . '" class="border-dark rounded" alt="' . $produkt['titulok'] . '" />
        </button>
        <div class="modal fade" id="dialog_' . (isset($produkt['produkt_id']) ? $produkt['produkt_objednavka_id'] : $produkt['doprava_platba_id']) . '_' . $title . '" tabindex="-1" role="dialog" aria-labelledby="dialog-label" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header pozadie-fuji-mod text-white">
                        <h5 class="modal-title" id="dialog-label">' . $produkt['titulok'] . '</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Zavřít">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">

                    '. $obrazky .'

                    </div>
                    <div class="modal-footer pozadie-fuji-zel">
                        <button type="button" class="btn pozadie-fuji-zel text-white tlacidlo" data-dismiss="modal">Zavrieť</button>
                    </div>
                </div>
            </div>
        </div>';
    }

    /**
     *
     * @return string Zostaveny HTML
     */
    /**Zostavi komplet aj z podmienkami pre Modálny dialog pomocou Bootstrapu Na zobrazenie obrazka produktu
     * @param $produkt Informacie o produkte
     * @param $objednavka Informacie o Objednavke
     * @param $typ Typ produktu produkt/doprava-platba
     * @param $spravaObjednavok či pracujem v sprave obejdnavok
     * @param $rozmer či sa jedna o rozmerovy produkt ak ano tak ak je mka hodnotu 1 tak zobrazujem iba obr foto a j 2 tak iba obr produktu
     * @return string
     */
    public static function zostavKompletDialogObrazka($produkt, $objednavka, $typ, $spravaObjednavok = false, $rozmer = false)
    {
        $objednavkaManazer = new ObjednavkaManazer();
        $HTML = '';
        if ($typ === self::PRODUKT && !$spravaObjednavok)
            $url = file_exists('obrazky/produkty/' . $produkt['produkt_id'] . '_miniatura_objednavka.png') ?
                'obrazky/produkty/' . $produkt['produkt_id'] :
                'obrazky/produkty/ziadny_nahlad';
        elseif ($typ === self::DOPRAVA_PLATBA && !$spravaObjednavok)
            $url = file_exists('obrazky/doprava-platba/' . $produkt['doprava_platba_id'] . '_miniatura_objednavka.png') ?
                'obrazky/doprava-platba/' . $produkt['doprava_platba_id'] :
                'obrazky/doprava-platba/ziadny_nahlad';
        if (!$spravaObjednavok) {
            $HTML = $rozmer ? '' : self::zostavDialogObrazka($produkt, $url . '_miniatura_objednavka.png', $url, 'Obrazok-produktu') ;
            $priecinok = 'docastne/';
        } else
            $priecinok = '';

        if ($typ === self::PRODUKT && $produkt['fotografia']) {

            $urlObrazka = 'obrazky/objednavka-foto/' . $priecinok . $objednavka['objednavka_id'] . '/' . $objednavkaManazer->zostavNazovObjednavkaFoto($produkt['produkt_id'], $objednavka['objednavka_id'], $produkt['produkt_objednavka_id']);

            if (file_exists($urlObrazka . '_miniatura_objednavka.png'))
            {
                // ak je sprava objednavky tak url bude "-sp";
                $uprava = $priecinok === 'docastne/' ? '' : '-sp';
                $produkt['objednavka_id'] = $objednavka['objednavka_id']; // kvoli tomu ze k uprave potrebujem Id objednavky aby soms a dostal do preicinka
                $HTML .= self::zostavDialogObrazka($produkt,$urlObrazka . '_miniatura_objednavka.png', $urlObrazka,'Vaša-fotografia', $uprava, true);

            }
            else {
                if($priecinok = 'docastne/')
                {
                    $urlObrazka = 'obrazky/objednavka-foto/fotografia_sa_nenasla_';
                }
                else
                {
                    $urlObrazka = 'obrazky/objednavka-foto/vyberte_svoju_fotografiu_';
                }
                $HTML .= self::zostavDialogObrazka($produkt,
                    $urlObrazka . 'miniatura_objednavka.png',
                    $urlObrazka  . 'miniatura.png',
                    'Fotografia-sa-nenašla');
            }
        }
        return $HTML;
    }


    /**
     * zostavi Modálny dialog pomocou Bootstrapu Na informáciu že nije dostatok produktu na sklade, toto si bude spracovavať daný skrypt je to len forma
     * @return string
     */
    public static function zostavDialogNedostatokProduktuForma()
    {
        return '
        <button id="nedostatok" type="button" class="d-none" data-toggle="modal" data-target="#dialog">

        </button>

        <div class="modal fade" id="dialog" tabindex="-1" role="dialog" aria-labelledby="dialog-label" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header pozadie-fuji-mod text-white">
                        <h5 class="modal-title" id="dialog-label">Nedostatok kusov na sklade</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Zavrieť">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div id="hlaska" class="modal-body text-center">
                       sklad
                    </div>
                    <div class="modal-footer pozadie-fuji-zel" id="vymazat">
                        <button type="button" id="nastavit-maximum" class="btn pozadie-fuji-zel text-white btn-outline-dark border-dark tlacidlo" data-dismiss="modal">Nastaviť maximum</button>
                        <button type="button" id="zrusit" class="btn pozadie-fuji-zel text-white tlacidlo" data-dismiss="modal">Zavrieť</button>
                    </div>
                </div>
            </div>
        </div>';
    }

    /**
     * zostavi dialog pre informáciu
     * @param $nadpis Nadpsi dialogu
     * @param $informacia Informácia ktoru zobrazujem
     * @return string zostavený dialog
     */
    public static function zostavDialogInformacie($nadpis, $informacia)
    {
        return '
        <button id="informacia" type="button" class="d-none" data-toggle="modal" data-target="#dialog-informacia">

        </button>

        <div class="modal fade" id="dialog-informacia" tabindex="-1" role="dialog" aria-labelledby="dialog-label" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header pozadie-fuji-mod text-white">
                        <h5 class="modal-title nadpis" id="dialog-label">' . $nadpis . '</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Zavrieť">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div id="info" class="modal-body text-center">
                       ' . $informacia . '
                    </div>
                    <div class="modal-footer pozadie-fuji-zel" id="vymazat">
                        <button type="button" id="zrusit-info" class="btn pozadie-fuji-zel text-white tlacidlo" data-dismiss="modal">OK</button>
                    </div>
                </div>
            </div>
        </div>';
    }

    /**
     * zostavi dialog pre upravu Obrázka
     * @return string Zostaveny HTML
     */
    public static function zostavDialogUpravyObrazka()
    {
        return '
        <button id="uprava-button" type="button" class="d-none border-0 p-0" data-toggle="modal" data-target="#dialog-upravit-obrazok">
        </button>

        <div class="modal fade" id="dialog-upravit-obrazok" tabindex="-1" role="dialog" aria-labelledby="dialog-label" aria-hidden="true">
            dsf<div class="modal-dialog modal-dialog-velkost modal-full" role="document">
                <div class="modal-content modal-dialog-velkost">
                    <div class="modal-header pozadie-fuji-mod text-white ">
                        <h5 class="modal-title p-0" id="dialog-label">Uprava</h5>
                        <button type="button" class="close text-white zavriet" data-dismiss="modal" aria-label="Zavrieť">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div id="telo" class="modal-body text-center">
                       <iframe id="uprava-iframe" src="api" class="p-0 m-0 border-0" width="100%" height="100%"></iframe>
                    </div>
                    <div class="modal-footer pozadie-fuji-zel" id="vymazat">

                    </div>
                </div>
            </div>
        </div>';
    }
}
/*
 * Autor: MiCHo
 */
