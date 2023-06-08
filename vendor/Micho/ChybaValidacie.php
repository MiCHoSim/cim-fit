<?php

namespace Micho;

use Exception;
use Throwable;

/**
 ** Výnimka, zachytavajuca Chybu pri validovani
 * Class ChybaValidacie
 * @package Micho
 */
class ChybaValidacie extends Exception
{
    /**
     ** uloženie poľa chýb ktore mi generuje Validator
     * @var array|mixed
     */
    private $chyby = array();

    /**
     * @param array $chyby Moja hodnota Slúžiaca na poslanie chýb
     */
    public function __construct($message = "", $code = 0, Throwable $previous = null, $chyby = array())
    {
        parent::__construct($message, $code, $previous);
        $this->chyby = $chyby;
    }

    /**
     ** Vráti uložené chyby
     * @return array|mixed Pole chýb
     */
    public function vratChyby()
    {
        return $this->chyby;
    }

}

/*
 * Autor: MiCHo
 */