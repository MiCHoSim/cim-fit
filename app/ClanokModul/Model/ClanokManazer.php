<?php

namespace App\ClanokModul\Model;

use App\AdministraciaModul\Uzivatel\Model\OsobaDetailManazer;
use App\AdministraciaModul\Uzivatel\Model\OsobaManazer;
use App\AdministraciaModul\Uzivatel\Model\UzivatelManazer;
use Micho\ChybaUzivatela;
use Micho\Db;
use Micho\Obrazok;
use Micho\Subory\Priecinok;
use Micho\Subory\Subor;
use Micho\Utility\Pole;
use Micho\Utility\Retazec;
use PDOException;

/**
 ** Správca Článkov webu
 * Class ClanokManazer
 * @package App\ClanokModul\Model
 */
class ClanokManazer
{
    /**
     * Názov Tabuľky pre Spracovanie člankov
     */
    const CLANOK_TABULKA = 'clanok';

    /**
     * Konštanty Databázy 'clanok'
     */
    const CLANOK_ID = 'clanok_id';
    const TITULOK = 'titulok';
    const CLANOK_TYP_ID = ClanokTypManazer::CLANOK_TYP_ID;
    const OBSAH = 'obsah';
    const URL = 'url';
    const ODKAZ = 'odkaz';
    const POPISOK = 'popisok';
    const VEREJNY = 'verejny';
    const AUTOR_ID = 'autor_id';
    const UPRAVIL_AUTOR_ID = 'upravil_autor_id';
    const DATUM_VYTVORENIA = 'datum_vytvorenia';
    const DATUM_UPRAVY = 'datum_upravy';


    /**
     ** Zisti Či je článok možne zobraziť
     * @param string $url Url článku
     * @return bool Či je verejny
     */
    public function zistiVerejnostClanku($url)
    {
        return (bool) Db::dopytSamotny('SELECT verejny FROM clanok WHERE clanok.url = ?', array($url));
    }

    /**
     ** Vráti zoznam Článkov v db podla jeho typu
     * @param false $clanokTypUrl Url typu článku ktorý chem vrátit
     * @return array|mixed
     */
    public function vratClankyZoznam($clanokTypUrl = false)
    {
        $kluce = array('clanok_id', 'titulok', 'nazov', 'url', 'popisok','verejny', 'verejny_nazov', 'typ_url');

        $dopyt = 'SELECT clanok_id, titulok, nazov, clanok.url, popisok, verejny, IF(verejny, "Verejný", "Skrytý") AS verejny_nazov, 
                                           IF(clanok_typ.url = "trening" OR clanok_typ.url = "strava", "clanok", clanok_typ.url) AS typ_url
                                            FROM clanok 
                                            JOIN clanok_typ USING (clanok_typ_id) ';
        $parametre = array();
        if ($clanokTypUrl)
        {
            $dopyt .= 'WHERE clanok_typ.url = ? ';
            $parametre = array($clanokTypUrl);
        }

        $dopyt .= 'ORDER BY clanok_typ_id, nazov DESC ';

        $data = Db::dopytVsetkyRiadky($dopyt, $parametre);

        return is_array($data) ? Pole::filtrujKluce($data, $kluce) : $data;
    }

    /**
     ** Vráti zoznam Článkov v db podla jeho typu pre karty a teda clanky pre treningy a tak
     * @param false $clanokTypUrl Url typu článku ktorý chem vrátit
     * @return array|mixed
     */
    public function vratClankyZoznamKarty($clanokTypUrl)
    {
        $kluce = array('clanok_id', 'titulok', 'clanok.url', 'popisok', 'datum_vytvorenia');

        $dopyt = 'SELECT ' . implode(', ',$kluce) . '
                  FROM clanok 
                  JOIN clanok_typ USING (clanok_typ_id)
                  WHERE verejny AND clanok_typ.url = ?
                  ORDER BY datum_vytvorenia DESC';
        $data = Db::dopytVsetkyRiadky($dopyt, array($clanokTypUrl));

        $kluce[2] = 'url'; // prepis kvoli filtrovaniu klucov
        return is_array($data) ? Pole::filtrujKluce($data, $kluce) : $data;
    }

    /**
     ** Uloži Článok. Pokiaľ je id false, Vloží nový, inak vykona editáciu
     * @param array $clanok Pole s Článkom
     * @throws ChybaUzivatela
     */
    public function ulozClanok($clanok)
    {
        if(!$clanok[self::CLANOK_ID])
        {
            unset($clanok[self::CLANOK_ID]); // aby prebehol autoinkrement, hodnota musí byť NULL, alebo stĺpec z dopytu musíme vynechať
            try
            {
                $clanok[self::AUTOR_ID] = UzivatelManazer::$uzivatel[UzivatelManazer::UZIVATEL_ID];
                Db::vloz(self::CLANOK_TABULKA, $clanok);
                return 'Článok bol úspešne uložený.';
            }
            catch (PDOException $ex)
            {
                throw new ChybaUzivatela('Článok s touto URL adresov už existuje');
            }
        }
        else
        {
            $clanok[self::UPRAVIL_AUTOR_ID] = UzivatelManazer::$uzivatel[UzivatelManazer::UZIVATEL_ID];
            $clanok[self::VEREJNY] = isset($clanok[self::VEREJNY]) ? : 0; // ak nieje zaškrtnuté tak sa neodosiela a preto ho musím prepisať na 0
            Db::zmen(self::CLANOK_TABULKA, $clanok, 'WHERE clanok_id = ?', array($clanok[self::CLANOK_ID]));
            return 'Článok bol aktualizovaný.';
        }
    }

    /**
     ** Vráti článok z db podľa jeho URL
     * @param string $url Url článku
     * @param array $kluce Klúče Ktoré chcem načitať
     * @return array|mixed Pole s článkom alebo FALSE pri neúspechu
     */
    public function vratClanok($url, $kluce)
    {
        $dopyt = 'SELECT ' . implode(', ',$kluce) . ', IF(verejny, "Verejný", "Skrytý") AS verejny_nazov FROM clanok';

        $data = Db::dopytJedenRiadok($dopyt . ' WHERE clanok.url = ?', array($url));

        if(empty($data))
            throw new ChybaUzivatela('Článok s danou url adresou nexistuje');

        $kluce[] = 'verejny_nazov'; // uprava kvoli filttrovaniu klucov
        return is_array($data) ? Pole::filtrujKluce($data, $kluce) : $data;
    }
    /**
     ** Odstráni Článok
     * @param string $url URL článku
     */
    public function odstranClanok($url)
    {
        Db::dopyt('DELETE FROM clanok WHERE url = ?', array($url));

        //vymaze priecinok aj z fotkami
        $src= 'obrazky/clanky/' . $url;
        Priecinok::vymazPriecinok($src);
    }

    /**
     ** Vráti zakladné info pre článok Na zobrazenie odkazu
     * @param string $clanokTypId url typu članku, ktorý chem zobraziť
     * @param bool $verejny Či chem zobraziť iba verejné články
     * @return array|mixed
     */
    public function vratClanky($clanokTypId = false, $vsetkyVerejne = true)
    {
        $kluce = array(self::TITULOK, self::POPISOK, self::URL);
        $parametre = array();

        $dopyt = 'SELECT titulok, clanok.url, popisok
                  FROM clanok 
                    ';
        if ($clanokTypId)
        {
            $dopyt .= 'WHERE clanok_typ_id = ? ';
            $parametre[] = $clanokTypId;
        }
        if ($vsetkyVerejne)
        {
            $dopyt .= 'AND verejny ';
        }
        $dopyt .= 'ORDER BY clanok_typ_id';

        $data = Db::dopytVsetkyRiadky($dopyt, $parametre);

        return is_array($data) ? Pole::filtrujKluce($data, $kluce) : $data;
    }

    /**
     ** Vráti zakladné info pre článok Na zobrazenie odkazu podla url
     * @param array $url pole clankov na nacitanie
     * @param bool $verejny Či chem zobraziť iba verejné články
     * @return array|mixed
     */
    public function vratClankyUrl(array $url, $vsetkyVerejne = true)
    {
        $kluce = array(self::TITULOK, self::POPISOK, self::URL);

        $dopyt = 'SELECT titulok, clanok.url, popisok
                  FROM clanok 
                    ';

        $dopyt .= 'WHERE ';

        for($i = 0 ; $i < count($url) -1 ; $i++)
        {
            $dopyt .= 'url = ? OR ';
        }
        $dopyt .= 'url = ? ';


        if ($vsetkyVerejne)
        {
            $dopyt .= 'AND verejny ';
        }

        $data = Db::dopytVsetkyRiadky($dopyt, $url);

        return is_array($data) ? Pole::filtrujKluce($data, $kluce) : $data;
    }



    /**
     ** Spracovanie obrázka ktorý príde v obsahu článku cez initTinyMce
     * @param array $clanok Pole z článkom
     * @param string $cesta cesta k ulozeniu obrazka
     * @return mixed|string|string[] Nový obsah pre uloženie do DB
     */
    public function ulozObrazky($clanok, $cesta)
    {
        $obsah = $clanok[self::OBSAH];

        // robim to cez explode lebo to ide rychlejsie ako ked som to robil cez mb_substr(
        $castiObrazok = explode('<img ', $obsah); // rozsekanie retazca na podla img

        //Priecinok::vymazPriecinok($srcNove); // vŽdy vymaže rpeičinok a obrazky uloži nanovo .. nevimazujem rpetoze mi tov ymqaze aj titulny obrazok

        if (isset($castiObrazok[1]))
        {
            // Zostavenie nového src atributu pre tágu img pre obsah na uloženie do Databázy
            //Priecinok::vymazPriecinok($srcNove); nevimazujem rpetoze mi tov ymqaze aj titulny ob vymazem to v kontorley na zaciatku
            Priecinok::vytvorPriecinok($cesta);
            foreach ($castiObrazok as $kluc => $cast)   // prechadzanie časti obrazkov
            {
                if (mb_strpos($cast, 'title=') !== false) // ak pole neobsahuje reťazec 'title=' znamená to, že to nieje obrazok
                {
                    $obrazok = explode(' />', $cast)[0]; // rozesekane pola podla /> a teda najdenie ukoncenia img a vratenie iba obrázka

                    $titulok = Retazec::vratRetazecMedzi($obrazok,'title="', '"');
                    $src = Retazec::vratRetazecMedzi($obrazok,'src="', '"');
                    $width = Retazec::vratRetazecMedzi($obrazok,'width="', '"');
                    $height = Retazec::vratRetazecMedzi($obrazok,'height="', '"');

                    // Nahradenie povodneho src novým ktory sa uloz do databazi z odkazom na obrazok
                    $obsah = str_replace('src="' . $src . '"', 'src="' . $cesta . '/' . $titulok  . '" class="img-fluid" ', $obsah);

                    // uloženie Obrázkov do priečinka
                    $obrazok = new Obrazok($src);
                    $obrazok->zmenRozmery($width, $height);
                    $obrazok->uloz($cesta . '/'. $titulok, $obrazok->vratObrazokTyp());
                }
            }
        }
        return $obsah; // obsah na ulozenie do DB
    }

    /**
     ** Spracovanie obrázka ktorý príde z databázi a chem ho zobraziť v initTinyMce
     * @param array $clanok Pole z článkom
     * @return mixed|string|string[] Nový obsah pre zobrazenie v initTinyMce
     */
    public function nacitajObrazky(array $clanok)
    {
        $obsah = $clanok[self::OBSAH];

        $castiObrazok = explode('<img ', $obsah); // rozsekanie retazca na podla img

        if (isset($castiObrazok[1]))
        {
            foreach ($castiObrazok as $kluc => $cast)   // prechadzanie časti obrazkov
            {
                if (mb_strpos($cast, 'title=') !== false) // ak pole neobsahuje reťazec 'title=' znamená to, že to nieje obrazok
                {
                    $obrazok = explode(' />', $cast)[0]; // rozesekane pola podla /> a teda najdenie ukoncenia img a vratenie iba obrázka

                    $srcStare = explode('" ', explode('src="', $obrazok)[1])[0];

                    $srcBase64 = Obrazok::vratBase64($srcStare);

                    $obsah = str_replace($srcStare, $srcBase64, $obsah);

                }
            }
        }
        return $obsah; // obsah na ulozenie do DB
    }

    /**
     ** Vráti zakladné info pre články menu
     * @param bool $verejny Či chem zobraziť iba verejné články
     * @return array|mixed
     */
    public function vratClankyMenu($vsetkyVerejne = true)
    {
        $urlClanok = array('o-nas', 'gym', 'bobby');
        $kluce = array(self::TITULOK, self::POPISOK, self::URL, 'typ_url');

        $dopyt = 'SELECT titulok, clanok.url, popisok, clanok_typ.url AS typ_url
                  FROM clanok 
                  JOIN clanok_typ USING (clanok_typ_id) 
                  WHERE (clanok.url = ?';

        for($i = 1; $i < count($urlClanok); $i++)
        {
            $dopyt .= ' OR clanok.url = ?';
        }
        $dopyt.=')';
        if ($vsetkyVerejne)
        {
            $dopyt .= ' AND verejny ';
        }
        $dopyt .= 'ORDER BY clanok_id';
        $data = Db::dopytVsetkyRiadky($dopyt, $urlClanok);
        return is_array($data) ? Pole::filtrujKluce($data, $kluce) : $data;
    }

    /**
     ** Pridá k článku autora
     * @param array $clanok Pole z hodnotami článku
     * @return array Pole z článkom aj autorom
     */
    public function pridajAutora($clanok)
    {
        $osobaManazer = new OsobaManazer();

        $clanok['autor'] = $osobaManazer->vratOsobneUdaje($clanok[self::AUTOR_ID], array(OsobaDetailManazer::MENO, OsobaDetailManazer::PRIEZVISKO));
        $clanok['upravil_autor'] = $osobaManazer->vratOsobneUdaje($clanok[self::UPRAVIL_AUTOR_ID], array(OsobaDetailManazer::MENO, OsobaDetailManazer::PRIEZVISKO));
        unset($clanok[self::AUTOR_ID]);
        unset($clanok[self::UPRAVIL_AUTOR_ID]);
        return $clanok;
    }

    /**
     ** priradí člankom titulne OBrázky
     * @param array $clanky Pole článkov
     * @return array Pole Clánkov obsahujúce cestu k titulnému Obrázku
     */
    public function priradClankuObrazok(array $clanky)
    {
        foreach ($clanky as $kluc => $clanok)
        {
            $cesta = 'obrazky/clanky/' . $clanok[ClanokManazer::URL]; // cesta pre nacitanie obrázkov v prípade editacie
            if ($nazovTitObrazok = Subor::vratNazovSuboruPodretazec($cesta, 'titulna'))
            {
                $clanky[$kluc]['titulnyObrazok'] = $cesta . '/' . $nazovTitObrazok;
            }
        }

        return $clanky;
    }
}
/*
 * Autor: MiCHo
 */