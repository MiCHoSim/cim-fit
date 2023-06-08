<?php

namespace App\AdministraciaModul\Uzivatel\Model;

use App\AdministraciaModul\Uzivatel\Kontroler\RegistraciaKontroler;
use App\RezervaciaModul\Model\PoznamkaManazer;
use App\RezervaciaModul\Model\RezervaciaManazer;
use App\RezervaciaModul\Model\SkupinaManazer;
use App\ZakladModul\Kontroler\EmailKontroler;
use Micho\ChybaValidacie;
use Micho\Db;
use Micho\ChybaUzivatela;
use Micho\Formular\Formular;
use Micho\OdosielacEmailov;
use Nastavenia;
use Micho\Utility\Pole;

use PDOException;

/**
 ** Správca Klientov
 * Class KlientManazer
 * @package App\AdministraciaModul\Uzivatel\Model
 */
class KlientManazer
{
    /**
     * Názov Tabuľky pre Spracovanie Klientov
     */
    const KLIENT_TABULKA = 'klient';

    /**
     * Konštanty Databázy 'klient'
     */
    const KLIENT_ID = 'klient_id';
    const TRENER_ID = 'trener_id';
    const OSOBA_KLIENT_ID = 'osoba_klient_id';

    /**
     ** Prída klienta a teda Osobe priradí Id trenera
     * @param int $uzivatelId Id uživateľa ... a teda trénera
     * @param int $osobaId Id osoby ktroej priraďujem trenera
     */
    public function pridajKlienta($uzivatelId, $osobaId)
    {
        $data[self::OSOBA_KLIENT_ID] = $osobaId;

        $trenrManazer = new TrenerManazer();

        $data[self::TRENER_ID] = $trenrManazer->vratTrenerId($uzivatelId);

        try
        {
            Db::vloz(self::KLIENT_TABULKA, $data);
        }
        catch (PDOException $chy)
        {
            throw new ChybaUzivatela('Tohto Klienta už máte uloženého');
        }
    }

    /**
     ** Načita klientov konkrétného trénera
     * @param int $trenerId Id trenera ktorému chem nacitat klientov
     * @param bool $detail Či chem zobraziť aj detail uzovatela tel, email
     * @return array|mixed
     */
    public function nacitajKlientov($trenerId, $detail)
    {
        $kluce = array('osoba', self::KLIENT_ID, UzivatelManazer::UZIVATEL_ID);

        $dopyt = 'SELECT CONCAT(COALESCE(meno, ""), " ", COALESCE(priezvisko, "")) AS osoba, klient_id, uzivatel_id ';

        if($detail)
        {
            $dopyt .= ', tel, email';
            $kluce = array_merge($kluce, array(OsobaDetailManazer::TEL, OsobaDetailManazer::EMAIL));
        }

        $dopyt .= '
                  FROM klient
                  JOIN osoba ON osoba_id = osoba_klient_id
                  JOIN osoba_detail USING (osoba_detail_id)
                  WHERE trener_id = ?
                  ORDER BY osoba';

        $data = Db::dopytVsetkyRiadky($dopyt, array($trenerId));

        return is_array($data) ? Pole::filtrujKluce($data, $kluce) : $data;
    }

    /**
     ** Načita pary klientov Osoba => osoba_id
     * @param int $trenerId Id trenera ktoremu chem nacitat klientov
     * @return array|mixed
     */
    public function nacitajParyKlientov($trenerId)
    {
        return Db::dopytPary('SELECT osoba_id, CONCAT(COALESCE(meno, ""), " ", COALESCE(priezvisko, "")) AS osoba
                            FROM klient
                            JOIN osoba ON osoba_id = osoba_klient_id
                            JOIN osoba_detail USING (osoba_detail_id)
                            WHERE trener_id = ?
                            ORDER BY osoba', 'osoba', OsobaManazer::OSOBA_ID, array($trenerId));
    }

    /**
     ** Vráti moznost výberu klientov, Nacita vŠetkych registrovanách uživateľov
     * @param int $osobaId Id osoby ktorému ponukam klientov/ ak nema Osoba ID tak vraty vsetkých/ inak nevracie prihlaseného
     * @return array páry Meno -> id
     */
    public function vratMoznostiKlientov($osobaId = false)
    {
        return Db::dopytPary('SELECT osoba_id, CONCAT(COALESCE(priezvisko, ""), " ", COALESCE(meno, "")) AS osoba
                            FROM osoba
                            JOIN osoba_detail USING (osoba_detail_id)
                            WHERE osoba_id != ?
                            ORDER BY osoba', 'osoba', OsobaManazer::OSOBA_ID, array($osobaId));
        /*

                return Db::dopytPary('SELECT osoba_id, CONCAT(COALESCE(meno, ""), " ", COALESCE(priezvisko, "")) AS osoba
                            FROM osoba
                            JOIN osoba_detail USING (osoba_detail_id)
                            LEFT JOIN klient ON osoba_id = osoba_klient_id
                            WHERE (osoba_trener_id != ? OR osoba_trener_id IS NULL) AND osoba_id != ?
                            ORDER BY osoba', 'osoba', 'osoba_id', array($trenerId,$trenerId));
         */
    }

    /**
     ** Odstráni klienta z DB
     * @param int $klientId Id klientua
     * @param int $trenerId Id trenera
     */
    public function odstranKlienta($klientId, $trenerId)
    {
        if (!Db::dopyt('DELETE FROM klient WHERE klient_id = ? AND trener_id = ?', array($klientId, $trenerId)))
            throw new ChybaUzivatela('Nemáte povolenie na odstránenie tohoto klienta');
    }



}

/*
 * Autor: MiCHo
 */
