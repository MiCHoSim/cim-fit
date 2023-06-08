<?php

namespace Micho\Formular;

/**
 ** Trieda služiaca na validáciu dát
 * Class Validator
 */
class Validator
{
    /**
     * Predefinované konštanty
     */
    const PATTERN_EMAIL = array('popis' => 'Zadajte email vo formáte ____@___.___',
                                'pattern' => '[a-z0-9._-]+@[a-z0-9.-]+\.[a-z]{2,4}$'); // Pattern pre email

    const PATTERN_RETAZEC = array('popis' => 'Zadajte reťazec z dĺžkou minimálne 2 znaky',
                                          'pattern' => '[A-Ža-ž]{2,}'); // Pattern pre meno a priezvisko

    const PATTERN_TEL = array('popis' => 'Zadajte Telefónne číslo v klasickom formáte 09...',
                              'pattern' => '[0]{1}[9]{1}[0-9]{8}'); // Pattern pre telefonné číslo

    const PATTERN_SUPIS_CISLO = array('popis' => 'Zadajte svoje súpisné číslo',
                              'pattern' => '[0-9]{1,}'); // Pattern pre súpisné číslo

    const PATTERN_PSC = array('popis' => 'Zadajte poštové smerovacie číslo.',
                              'pattern' => '\d{3}[ ]?\d{2}'); // Pattern pre PSC

    const PATTERN_ANTISPAM_ROK = array('popis' => 'Zadajte aktuálny rok na 4 číslice.',
                                       'pattern' => '[0-9]{4,4}'); // Pattern pre Antispam

    const HESLO_DLZKA = 8; // Dĺžka hesla
    const PATTERN_HESLO = array('popis' =>' Heslo musí obsahovať minimálne ' . self::HESLO_DLZKA . ' znakov, jedno číslo, jedno veľké a malé písmeno.',
                                'pattern' => '(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{' . self::HESLO_DLZKA . ',}') ; // Pattern pre heslo, ktoré musí obsahovať 8 alebo viac znakov, ktoré majú aspoň jedno číslo a jedno veľké a malé písmeno

    const PATTERN_PIN = array('popis' => 'Zadajte registračný pin. Pin Vám poskytne Čim Fit',
         'pattern' => '[0-9]{4,4}'); // Pattern pre Antispam
}
/*
 * Autor: MiCHo
 */