<?php

namespace App\ZakladModul\Model;

use Micho\Db;
use PDOException;

/**
 ** Trieda poskytuje metódy pre správu Upozornení
 * Class UpozornenieManazer
 * @package App\ZakladModul\Model
 */
class UpozornenieManazer
{
    /**
     ** Uloži potvdrenie do DB
     * @param int $permanantkaId Id prepadnutej permanentky
     * @param int $uzivatelId Id puživateľa ktoremu permanentka patrý
     * @return void Výnimka v pripade ze sa zaznam neuložil
     */
    public function ulozPotvrdenie($permanantkaId, $uzivatelId)
    {
        $ulozene = (bool) Db::dopyt('UPDATE permanentka
                                    JOIN osoba USING (osoba_id)
                                    SET potvrdenie_prepadnutia = 1 WHERE permanentka_id = ? AND uzivatel_id = ?', array($permanantkaId, $uzivatelId));

        if(!$ulozene)
            throw new PDOException();
    }
}
/*
 * Autor: MiCHo
 */
