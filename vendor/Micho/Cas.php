<?php

namespace Micho;

use DateTime;

/**
 ** Tríeda služiaca na správu Casu
 * Class Cas
 * @package Micho
 */
class Cas
{
    private $start;
    private $koniec;
    private $krok;

    /**
     * Cas constructor.
     * @param int $start Štarovný Čas ako cele čislo 7 / 8 / 9 ...
     * @param int $koniec Koncový Čas ako cele čislo 7 / 8 / 9 ...
     * @param int $krok Krok času v minutách 15 / 30 ...
     */
    public function __construct($start, $koniec, $krok)
    {
        $this->start = $start;
        $this->koniec = $koniec;
        $this->krok = $krok;
    }

    /**
     ** Generuje časy v danom rozsahu v závislosti od aktualného Času
     * @param DateTime $vybratyDatum Aktualen vybratí dátum
     * @param array $vynechaneCasy Časy kotré nechem davať v ponuke výberu
     * @return mixed Poľe hodnôt vygenerovaných Časov
     * @throws \Exception
     */
    public function generujCasy($vybratyDatum = false, $vynechaneCasy = false)
    {
        $aktualnyDatumCas = new DateTime();
        $aktualnyDatum = $aktualnyDatumCas->format('Y-m-d');

        $startMinuty = '00';
        $hodiny = $aktualnyDatumCas->format('H');
        if($vybratyDatum === $aktualnyDatum && $hodiny > $this->start ) // ak mam vybratí dnešný deň a taktiez aktualne hodiny su vecsie ako start hodiny generovania tak kontrolujem aky je aktualny čas  a do ponuky dávam iba Časi ktore su po aktualnom Čase
        {
            $minuty = $aktualnyDatumCas->format('i');

            if($this->krok > $minuty)
                $startMinuty = $this->krok;
            else
                $hodiny++;

            if($hodiny >= $this->start && $hodiny < $this->koniec)
                $this->start = $hodiny;
            else                // v daný deň sa už nedá rezervovať
                return false;
        }
        elseif ($vybratyDatum && $vybratyDatum < $aktualnyDatum)
            return false;

        $casy = $this->generujCasyVypis($this->start, $startMinuty, $this->koniec, $this->krok);

        $cas = $casy; // pomocne pretoze to potrebuejm aj tam aj tamk
        array_pop($cas);
        $generovaneCasy['cas_od'] = $cas;
        $cas = $casy; // pomocne pretoze to potrebuejm aj tam aj tamk
        array_shift($cas); // odobratie prveho prvku casu aby sa casi od a do nezhodovali
        $generovaneCasy['cas_do'] = $cas;

        if($vynechaneCasy) // ak treba niejake casy vynechat tak ich odstránmin
        {
            foreach ($casy as $kluc => $cas)
            {
                foreach ($vynechaneCasy as $vynechanyCas)
                {
                    if ($cas >= $vynechanyCas['od'] && $cas < $vynechanyCas['do']) // odstranovanie časov od
                    {
                        if(isset($generovaneCasy['cas_od'][$kluc]))
                            unset($generovaneCasy['cas_od'][$kluc]);
                    }
                    if ($cas > $vynechanyCas['od'] && $cas <= $vynechanyCas['do']) // odstranovanie časov do
                    {
                        if(isset($generovaneCasy['cas_do'][$kluc]))
                            unset($generovaneCasy['cas_do'][$kluc]);
                    }
                }
            }

        }

        return  $generovaneCasy;
    }

    /**
     ** generuje Časy nezavisel na aktuálnom case
     * @param int $start Štarovný Čas ako cele čislo 7 / 8 / 9 ...
     * @param int $startMinuty Štart ekdy začnu minuty 00 / 15 /30 ...
     * @param int $koniec Koncový Čas ako cele čislo 7 / 8 / 9 ...
     * @param int $krok Krok času v minutách 15 / 30 ...
     * @return mixed Poľe hodnôt vygenerovaných Časov
     * @throws \Exception
     */
    public function generujCasyVypis($start, $startMinuty, $koniec, $krok)
    {
        $cas = new DateTime($start . ':' . $startMinuty);

        while ($cas->format('H:i') != $koniec . ':' . $krok)
        {
            $hodnota = $cas->format('H:i');
            $casy[$hodnota] = $hodnota;
            $cas->modify('+' . $krok . ' min');
        }
        return $casy;
    }
}
/*
 * Autor: MiCHo
 */