<?php

namespace Micho;

use Micho\Antispam\AntispamRok;
use Nastavenia;

/**
 ** Pomocná trieda, poskytujuca metody pre odosielanie emailu
 * Class OdosielacEmailov
 * @package Micho
 */
class OdosielacEmailov 
{
    /**
     * Odošle email ako HTML, dajú sa použiť zakladné HTML tagy a nové
     * riadky je potrebné pisať ako <br> alebo použivať odstavce. Kodovanie je
     * odladené pre UTF-8
     * @param string $komu Adresa na ktorú sa posiela/príjemnca
     * @param string $predmet Predmet správy
     * @param string $sprava Správa Može býť aj generovaná ako HTML šablona
     * @param string $od Adresa odosielateľa
     * @param bool $obchPodmOdstupZmluv ci posielam obchodné podmienky
     * @param string|bool $faktura či posielam faktúru ak ańo tak čsilo fakturý
     * @throws ChybaUzivatela
     */
    public function odosli($komu, $predmet, $sprava, $od, $obchPodmOdstupZmluv = false, $faktura = false)
    {
        $semi_rand = md5(time());
        $mime_boundary = "==Multipart_Boundary_x{$semi_rand}x";

        $hlavicka = "From: <" . $od . ">";// Hlavička pre informácie o odosielateľovi
        $hlavicka .= "\nMIME-Version: 1.0\n" . "Content-Type: multipart/mixed;\n" . " boundary=\"{$mime_boundary}\"";// Hlavičky pre prílohy

        $sprava = "--{$mime_boundary}\n" . "Content-Type: text/html; charset=\"UTF-8\"\n" . "Content-Transfer-Encoding: 7bit\n\n" . $sprava . "\n\n";// Multipart boundary

        // Prílohy
        if($obchPodmOdstupZmluv)
            $subory = array("../verejny/pdf/Obchodné podmienky.pdf", "../verejny/pdf/Odstúpenie od zmluvy.pdf");
        if($faktura)
            $subory = array('../verejny/pdf/' . $faktura . '.pdf', 'F');


        // Príprava prílohy
        if(!empty($subory))
        {
            for($i=0;$i<count($subory);$i++){
                if(is_file($subory[$i]))
                {
                    $file_name = basename($subory[$i]);
                    $file_size = filesize($subory[$i]);

                    $sprava .= "--{$mime_boundary}\n";
                    $fp =    @fopen($subory[$i], "rb");
                    $data =  @fread($fp, $file_size);
                    @fclose($fp);
                    $data = chunk_split(base64_encode($data));
                    $sprava .= "Content-Type: application/octet-stream; name=\"".$file_name."\"\n" .
                        "Content-Description: ".$file_name."\n" .
                        "Content-Disposition: attachment;\n" . " filename=\"".$file_name."\"; size=".$file_size.";\n" .
                        "Content-Transfer-Encoding: base64\n\n" . $data . "\n\n";
                }
            }
        }

        $sprava .= "--{$mime_boundary}--";
        $returnpath = "-f" . $od;

        if (Nastavenia::$ladit)
        {
            file_put_contents('subory/emaily/' . uniqid() .'.html', $sprava);
            return;
        }

        if (!@mail($komu, $predmet, $sprava, $hlavicka, $returnpath))
            throw new ChybaUzivatela('Email sa nepodarilo odoslať.');

        if($faktura)
            unlink('../verejny/pdf/' . $faktura . '.pdf'); // vymaže docastnú PDF faktúru po odoslani emailu
    }
    
    /**
     * Skontroluje, či je zadaný aktuálny rok ako antispam a odošle email
     * @param int $rok Aktuálny rok
     * @param string $komu Emailova Adresa
     * @param string $predmet Predmet
     * @param string $sprava Sprava
     * @param string $od Adresa odosielateľa
     * @throws ChybaUzivatela
     */
    public function odosliSAntispamom($rok, $komu, $predmet, $sprava, $od)
    {
        $antispam = new AntispamRok();
        $antispam->over($rok);
        $this->odosli($komu, $predmet, $sprava, $od);
    }
}
/* Autor: http://www.itnetwork.cz */

/*
 * Niektoré časti sú upravené
 * Autor: MiCHo
 */
