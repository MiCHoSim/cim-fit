<?php


namespace Micho;

use GdImage;

/**
 ** Trieda pre prácu z obrázkom
 * Class Obrazok
 * @package Micho
 */
class Obrazok
{
    /**
     * Obrázok typu PNG
     */
    const OBRAZOKTYP_PNG = IMAGETYPE_PNG;
    /**
     * Obrázok typu GIF
     */
    const OBRAZOKTYP_GIF = IMAGETYPE_GIF;
    /**
     * Obrázok typu JPEG
     */
    const OBRAZOKTYP_JPEG = IMAGETYPE_JPEG;

    /**
     * @var resource Načítaný Obrázok
     */
    private $obrazok;
    /**
     * @var int Typ obrázka
     */
    private $obrazokTyp;
    /**
     * @var int Šírka obrázka v pixeloch
     */
    private $sirka;
    /**
     * @var int Výška obrázka v pixelech
     */
    private $vyska;

    /**
     ** Zistí či je daný súbor obrázok
     * @param string $cestaObrazok Cesta k súboru
     * @return bool či je daný súbor obrázok
     */
    public static function jeObrazok($cestaObrazok)
    {
        $typ = exif_imagetype($cestaObrazok);
        return ($typ == self::OBRAZOKTYP_JPEG || $typ == self::OBRAZOKTYP_GIF || $typ == self::OBRAZOKTYP_PNG);
    }

    /**
     ** vytvorti base64 z obrázka
     * @param string $cestaObrazok Cesta k obrázku
     * @return string reťazec base64
     */
    public static function vratBase64($cestaObrazok)
    {
        $retazec = file_get_contents($cestaObrazok);
        $typ = pathinfo($cestaObrazok, PATHINFO_EXTENSION); // vraty typ png,gif,...

        $base64Retazec =  base64_encode($retazec);

        return 'data:image/' . $typ . ';base64,' . $base64Retazec;
    }

    /**
     * Obrazok constructor.
     * @param string $cestaObrazok Cesta k súboru, z kterého sa má obrázok načítať
     */
    public function __construct($cestaObrazok)
    {
        $obrazokData = explode(',', $cestaObrazok);
        // ak je "cesta" zadana ako base 64 tak ho tak aj spracujem
        if(mb_strpos($obrazokData[0],'base64') !== false)
        {
            $base64 = $obrazokData[1]; // ziskanie čisto base64
            $obrazok = base64_decode($base64); // dekodovanie

            $this->obrazok = imagecreatefromstring($obrazok); // nacitanie obrazka ako objekt GD

            $rozmerObrazok = getimagesizefromstring($obrazok);
            $this->sirka = $rozmerObrazok[0];
            $this->vyska = $rozmerObrazok[1];
            $this->obrazokTyp = $rozmerObrazok[2];
        }
        else
        {
            $rozmerObrazok = getimagesize($cestaObrazok);
            $this->sirka = $rozmerObrazok[0];
            $this->vyska = $rozmerObrazok[1];
            $this->obrazokTyp = $rozmerObrazok[2];

            if($this->obrazokTyp == self::OBRAZOKTYP_JPEG)
                $this->obrazok = imagecreatefromjpeg($cestaObrazok);

            elseif ($this->obrazokTyp == self::OBRAZOKTYP_GIF)
            {
                $obrazok = imagecreatefromgif($cestaObrazok);
                $this->obrazok = $this->vytvorPozadie($this->sirka, $this->vyska, true);
                imagecopy($this->obrazok, $obrazok, 0,0,0,0, $this->sirka, $this->vyska);
                imagedestroy($obrazok);
            }
            elseif ($this->obrazokTyp == self::OBRAZOKTYP_PNG)
            {
                $this->obrazok = imagecreatefrompng($cestaObrazok);
                imagealphablending($this->obrazok,true);  // zapnutie alfakanalu
                imagesavealpha($this->obrazok,true); // ulozenie alfakanalu
            }
        }
    }


    /**
     * Uvoľni Obrázok z pamäte
     */
    public function uvolniPamet()
    {
        imagedestroy($this->obrazok);
    }

    /**
     ** Pridá do pravého dolného rohu obrázka vodoznak
     * @param string $cesta Cesta k obrázku vodoznaku
     * @param int $ofset Šírka okraja mezdi vodoznakom a hranou obrázka v pixeloch
     */
    public function pridajVodoznak($cesta, $ofset = 8)
    {
        $vodoznak = imagecreatefrompng($cesta);
        $sirka = imagesx($vodoznak);
        $vyska = imagesy($vodoznak);
        imagecopy($this->obrazok, $vodoznak, $this->sirka - $sirka - $ofset, $this->vyska - $vyska - $ofset, 0,0, $sirka, $vyska);
        imagedestroy($vodoznak);
    }

    /**
     ** Oreže obrázok, reŽe sa od ľavého horného rohu
     * @param int $sirka Šírka obrázka
     * @param int $vyska Výška obrázka
     */
    public function orez($sirka, $vyska)
    {
        $obrazok = $this->vytvorPozadie($sirka, $vyska, true);
        imagecopy($obrazok, $this->obrazok, 0,0,0,0,$sirka, $vyska);
        $this->obrazok = $obrazok;
        $this->sirka = $sirka;
        $this->vyska = $vyska;
    }

    /**
     ** Zmeni rozmery obrázka
     * @param int $sirka Šírka obrázka
     * @param int $vyska Výška obrázka
     */
    public function zmenRozmery($sirka, $vyska)
    {
        $obrazok = $this->vytvorPozadie($sirka, $vyska, true);
        imagecopyresampled($obrazok, $this->obrazok,0,0,0,0,$sirka, $vyska, $this->sirka, $this->vyska);
        $this->obrazok = $obrazok;
        $this->sirka = $sirka;
        $this->vyska = $vyska;
    }

    /**
     ** Zmení rozmer obrázka vzhľadom na požadovanú šírku
     * @param int $sirka Šírka obrázka
     */
    public function zmenRozmerKSirke($sirka)
    {
        $pomer = $sirka / $this->sirka;
        $vyska = $this->vyska * $pomer;
        $this->zmenRozmery($sirka, $vyska);
    }

    /**
     ** Zmení rozmer obrázka vzhľadom na požadovanú výšku
     * @param int $vyska Výška obrázka
     */
    public function zmenRozmerKVyske($vyska)
    {
        $pomer = $vyska / $this->vyska;
        $sirka = $this->sirka * $pomer;
        $this->zmenRozmery($sirka, $vyska);
    }

    /**
     ** Prispôsoby rozmer obrázka tak aby jeho najväčší rozmer neprekročil dĺžku zadanej hrany a dodržania pomeru strán
     * @param int $hrana Dĺžka hrany obrázka do ktorej sa ma obrazok zmestiť
     * @return bool či bol obrazok zmenšený
     */
    public function zmenRozmerNaHranuMaximalna($hrana)
    {
        if(($this->sirka > $hrana) || ($this->vyska > $hrana))
        {
            if ($this->sirka > $this->vyska)
                $this->zmenRozmerKSirke($hrana);
            else
                $this->zmenRozmerKVyske($hrana);
            return true;
        }
        return false;
    }

    /**
     ** Prisposoby rozmer obrázka tak aby sa roztiahol cez celu širku daného rozmeru za dodržania pomeru strán -> obrázok pokryje celú plochu
     * @param int $hrana Dĺžka hrany obrazka na ktorú sa ma obrazok roztiahnuť
     * @return bool či bol obrazok zmenšený
     */
    public function zmenRozmerNaHranuMinimalna($hrana)
    {
        if (!($this->sirka == $hrana && $this->vyska >= $hrana) || ($this->vyska == $hrana && $this->sirka >= $hrana))
        {
            if ($this->sirka < $this->vyska)
                $this->zmenRozmerKSirke($hrana);
            else
                $this->zmenRozmerKVyske($hrana);
            return true;
        }
        return false;
    }

    /**
     ** Škáluje obrazok v danom pomere
     * @param int $mierka Pomer v %
     */
    public function skaluj($mierka)
    {
        $sirka = $this->sirka * $mierka / 100;
        $vyska = $this->vyska * $mierka / 100;
        $this->zmenRozmery($sirka, $vyska);
    }

    /**
     ** Uloži obrázok do súboru pre rôzne formáty
     * @param string $nazovObrazok Cesta a názov uloŽeného obrázka
     * @param int $obrazokTyp Typ obrázka
     * @param int $kompresia Kompresia Kvalita obrázka pre typ JPEG
     * @param bool $priesvitnost Či chem  priesvitnosť pre typ GIF
     * @param null $povolenia Možnost pridelenia práv pre nový súbor
     */
    public function uloz($nazovObrazok, $obrazokTyp = self::OBRAZOKTYP_JPEG, $kompresia = 85, $priesvitnost = true, $povolenia = null)
    {
        if ($obrazokTyp == self::OBRAZOKTYP_JPEG)
        {
            $vystup = $this->vytvorPozadie($this->sirka, $this->vyska, false);
            imagecopy($vystup, $this->obrazok,0,0,0,0,$this->sirka, $this->vyska);
            imagejpeg($vystup, $nazovObrazok, $kompresia);
            imagedestroy($vystup);
        }
        elseif ($obrazokTyp == self::OBRAZOKTYP_GIF)
        {
            $obrazok = $this->vytvorPozadie($this->sirka, $this->vyska, true);
            if ($priesvitnost)
            {
                $farba = imagecolorallocatealpha($obrazok,0,0,0,127);
                imagecolortransparent($obrazok, $farba);
            }
            imagecopyresampled($obrazok, $this->obrazok,0,0,0,0,$this->sirka, $this->vyska, $this->sirka, $this->vyska);
            imagegif($obrazok, $nazovObrazok);
            imagedestroy($obrazok);
        }
        elseif ($obrazokTyp == self::OBRAZOKTYP_PNG)
            imagepng($this->obrazok, $nazovObrazok);
        if ($povolenia != null)
            chmod($nazovObrazok, $povolenia);
    }

    /**
     ** Obrazok sa priehliadačom stiahne
     * @param int $obrazokTyp Typ obrázka
     * @param int $kompresia Kompresia Kvalita obrázka pre typ JPEG
     * @param bool $priesvitnost Či chem  priesvitnosť pre typ GIF
     */
    public function stiahniObrazok($obrazokTyp = self::OBRAZOKTYP_JPEG, $kompresia = 85, $presvitnost = true)
    {
        $this->uloz(null, $obrazokTyp, $kompresia, $presvitnost);
    }

    /**
     ** Vytvorí pozadie obrázka
     * @param int $sirka šírka pozadia
     * @param int $vyska výška pozadia
     * @param bool $priesvitnost Či chem priesvitné pozadie
     * @return false|\GdImage|resource Vytvorené pozadie
     */
    private function vytvorPozadie($sirka, $vyska, $priesvitnost = true)
    {
        $obrazok = imagecreatetruecolor($sirka, $vyska);
        if ($priesvitnost)
        {
            imagealphablending($obrazok, true);
            $farba = imagecolorallocatealpha($obrazok,0,0,0, 127);
        }
        else
            $farba = imagecolorallocate($obrazok,255,255,255);
        imagefill($obrazok,0,0, $farba);
        if ($priesvitnost)
            imagesavealpha($obrazok, true);
        return $obrazok;
    }

    /**
     ** Vráti typ obrázka
     * @return mixed
     */
    public function vratObrazokTyp()
    {
        return $this->obrazokTyp;
    }

    /**
     ** Vráti Šírku Obrázka
     * @return mixed
     */
    public function vratSirku()
    {
        return $this->sirka;
    }

    /**
     ** Vráti Výšku Obrázka
     * @return mixed
     */
    public function vratVysku()
    {
        return $this->vyska;
    }

}
/*
 * Tento kód spadá pod licenci ITnetwork Premium - http://www.itnetwork.cz/licence
 * Je určen pouze pro osobní užití a nesmí být šířen ani využíván v open-source projektech.
 */
/*
 * Niektoré časti sú upravené
 * Autor: MiCHo
 */