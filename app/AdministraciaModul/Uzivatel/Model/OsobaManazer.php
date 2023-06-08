<?php

namespace App\AdministraciaModul\Uzivatel\Model;

use App\AdministraciaModul\Uzivatel\Kontroler\RegistraciaKontroler;
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
 ** Správca osoby redakčného systému / Pracovanie z tabuľkou uživateľ osoba
 * Class OsobaManazer
 * @package App\UzivateliaModul\Model
 */
class OsobaManazer
{
    /**
     * Názov Tabuľky pre Spracovanie Osoby
     */
    const OSOBA_TABULKA = 'osoba';

    /**
     * Konštanty Databázy 'osoba'
     */
    const OSOBA_ID = 'osoba_id';
    const OSOBA_DETAIL_ID = OsobaDetailManazer::OSOBA_DETAIL_ID;
    const ADRESA_ID = 'adresa_id';
    const DODACIA_ADRESA_ID = 'dodacia_adresa_id';
    const BANKOVE_KONTO_ID = 'bankove_konto_id';
    const UZIVATEL_ID = UzivatelManazer::UZIVATEL_ID;

    /**
     ** Aktualizuje Osobné údaje Uživateľa
     * @param array $data Nové osobné údaje
     * @param bool $sessionUzivatel Či chem obnoviŤ naČitanie sessuon pre uživateľa
     * @throws ChybaValidacie
     */
    public function aktualizujOsobneUdaje($data, $sessionUzivatel = true)
    {
        $this->ulozOsobu($data);

        if($sessionUzivatel)
        {
            $uzivatelManazer = new UzivatelManazer();
            $_SESSION['uzivatel'] = array_merge($_SESSION['uzivatel'], Pole::filtrujKluce($data, UzivatelManazer::UDAJE_PRIHLAS_UZIVATELA));
            $uzivatelManazer->nacitajUzivatela();
        }
    }

    /**
     ** Vráti Osoba_id z databázi
     * @param int $uzivatelId Id uživateľa
     * @return mixed
     */
    public function vratOsobaId($uzivatelId)
    {
        return Db::dopytSamotny('SELECT osoba_id FROM osoba WHERE uzivatel_id = ?', array($uzivatelId));
    }

    /**
     ** Vráti osoba_id z databázi z emailu
     * @param string $email email uzivatela
     * @return mixed osoba_id
     */
    public function vratOsobaIdEmailu($email)
    {
        return Db::dopytSamotny('SELECT osoba_id 
                                       FROM osoba 
                                       JOIN osoba_detail USING (osoba_detail_id)
                                       WHERE email = ?', array($email));
    }


    /**
     ** Registruje nového uživatela do systému
     * @param int $rok Aktuálny rok ako antispam
     * @param array $osobneUdaje Osobné údaje potrebné na registráciu
     * @return string Id práve registrovaného uživateľa
     * @throws ChybaUzivatela
     */
    public function registruj(array $osobneUdaje)
    {
        $uzivatelManazer = new UzivatelManazer();

        unset($osobneUdaje['heslo_znova']); // odstránenie hodnoty ktorá sa neukladá do Databázi
        unset($osobneUdaje[Formular::ANTISPAM]);


        $uzivatelId = $uzivatelManazer->ulozUzivatela($heslo = $osobneUdaje[Formular::HESLO]); // uloženie uživateľa do tabuľky uživateľov
        unset($osobneUdaje[Formular::HESLO]);

        $this->ulozOsobu($osobneUdaje, $uzivatelId); // Uloženie osobných údajov uživateľa do tabuľky

        $osobneUdaje[Formular::HESLO] = $heslo;

        $this->odosliRegistracnyEmail($osobneUdaje); // odoslanie registračného Emailu
    }

    /**
     ** Pripravý šablonu/Pohľad pre emailovú správu
     * @param array $osobneUdaje Zadané osobné údaje
     * @throws ChybaUzivatela
     * @throws \ReflectionException
     */
    private function odosliRegistracnyEmail($osobneUdaje)
    {
        // Získanie obsahu emailu zo šablony kontroleru
        $registraciaKontoler = new RegistraciaKontroler();
        $registraciaKontoler->sablonaRegistraciaEmail($osobneUdaje);

        // Šablona rozložena emailu a taktiez Štýly
        $emailKontroler = new EmailKontroler();
        $emailKontroler->index($registraciaKontoler);

        ob_start();
        $emailKontroler->vypisPohlad();
        $sprava = ob_get_contents();
        ob_end_clean();

        $odosielacEmailov = new OdosielacEmailov();
        $odosielacEmailov->odosli($osobneUdaje[Formular::EMAIL], 'Registrácia na ' . Nastavenia::$domena, $sprava, Nastavenia::$email);
    }

    /**
     ** Uloží osobu (Vytvorí novu alebo upravý existujucu podla toho , či je zadané osoba_id
     ** Jednotlivé súčasti osoby sú vždy vytvorené znovu a stare sú vymazané, pokiaľ niesú napojené na iné tabuľky
     * @param array $osobneUdaje Údaje osoby na uloženie
     * @param int $uzivatelId Id uživateľa / vtedy ked sa vytvará osoba aj z registráciou
     * @return null Ak je uložená nová osoby vrátim jej id
     */
    public function ulozOsobu($osobneUdaje, $uzivatelId = null)
    {
        // Porozdelovanie prijatých údajov na polia ktoré sa ukladajú do jednotlivých tabuliek
        $osobaDetail = array_intersect_key($osobneUdaje, array_flip(RegistraciaKontroler::$osobaDetail));
        $osobaAdresa = array_intersect_key($osobneUdaje, array_flip(RegistraciaKontroler::$osobaAdresa));

        Db::zacatTranzakciu();
        // Osoba detail
        Db::vloz(OsobaDetailManazer::OSOBA_DETAIL_TABULKA, $osobaDetail);
        $osoba[self::OSOBA_DETAIL_ID] = Db::vratPosledneId();

        // Adresa
        if(!empty($osobaAdresa)) // plati vtedy ked ukladam záznam na objednavku do gymu pretože tam nepotrebujem údaje adresy... ak sa ropzhodnem ze budu sa moc rezervovat len prihlasený, tuto podmienku mozem odstrániť
        {
            Db::vloz('adresa', $osobaAdresa);
            $osoba[self::ADRESA_ID] = Db::vratPosledneId();
        }
        // Osoba
        if (!isset($osobneUdaje[self::OSOBA_ID])) // vkladám novú osobu
        {
            if ($uzivatelId) // ak je zadané Id uživateľa tak ukladám aj jeho // využitie v ešhope pri objednavky bez registrácie
                $osoba[UzivatelManazer::UZIVATEL_ID] = $uzivatelId;
            Db::vloz(self::OSOBA_TABULKA, $osoba);
            $osobaId = Db::vratPosledneId();
        }
        else // Upravujem existujúcu
        {
            $staraOsoba = Db::dopytJedenRiadok('SELECT * FROM osoba WHERE osoba_id = ?', array($osobneUdaje[self::OSOBA_ID]));

            Db::zmen(self::OSOBA_TABULKA, $osoba, 'WHERE osoba_id = ?', array($osobneUdaje[self::OSOBA_ID]));

            $osobaDetailManazer = new OsobaDetailManazer();
            // Vymazanie povodných záznamov pokial sa nevsťahujú na iné tabuľky
            $osobaDetailManazer->vymazOsobaDetail($staraOsoba[self::OSOBA_DETAIL_ID]);
            $this->vymazAdresu($staraOsoba[self::ADRESA_ID]);
        }
        Db::dokonciTransakciu();

        if (!isset($osobneUdaje[self::OSOBA_ID]))
            return $osobaId;
        return null;
    }

    /**
     ** Vymaže starú adresu, v prípade že sa neviaže na inú Tabuľku
     * @param int $adresaId Id adresy
     */
    public function vymazAdresu($adresaId)
    {
        try
        {
            Db::dopyt('DELETE FROM adresa WHERE adresa_id = ?', array($adresaId));
        }
        catch (PDOException $chy){} // položku sa nepodarilo odstrániť, pretože je napojená na inú tabuľku
    }

    /**
     ** Overý či sú registraČné hodnotý správne
     * @param array $data Dáta od uzivateľa
     * @return array Pole chybových správ
     * @throws ChybaUzivatela
     */
    public function overRegistracneHodnoty($data)
    {
        $spravy = array();
        $uzivatelManazer = new UzivatelManazer();

        //$spravy[] = in_array($data[Formular::KRAJINA_ID],$this->vratKrajiny()) ? '' : 'Chybne vyplnená hodnota: Krajina';
        $spravy[] = !$this->overExistenciuEmailu($data[Formular::EMAIL]) ? '' : 'Účet so zadanou emailovou adresou už existuje.';
        $spravy[] = $uzivatelManazer->overZhoduHesiel($data[Formular::HESLO], $data['heslo_znova']);
        $spravy = array_filter($spravy);

        return $spravy;
        //if(!empty($spravy)) // ak nieje pole prázdne tak vyvolam vynimku na vypísanie správ
        //throw new ChybaValidacie('Nastali chyby validovania', 0, null, $spravy);
    }

    /**
     **Overí, či osoba z daným emailom je už registrovaná
     * @param string $email Email k overeniu
     * @return bool či email existuje
     */
    public function overExistenciuEmailu($email)
    {
        return (bool) Db::dopytSamotny('SELECT COUNT(*) FROM osoba
                                              JOIN osoba_detail USING (osoba_detail_id)
                                              WHERE osoba_detail.email = ? AND osoba.uzivatel_id IS NOT NULL',
            array($email));
    }

    /**
     ** Načitá osobné údaje z DB
     * @param int $uzivatelId ID uživateľa
     * @param array $poleHodnot Poľe hodnot ktore chem z Db načitať
     * @return array|mixed Osobné údaje uživateľa
     */
    public function vratOsobneUdaje($uzivatelId, array $poleHodnot)
    {
        $data = Db::dopytJedenRiadok('SELECT ' . implode(', ', $poleHodnot) . '
                                          FROM uzivatel 
                                          JOIN osoba USING (uzivatel_id)
                                          JOIN osoba_detail USING (osoba_detail_id)
                                          LEFT JOIN trener USING (osoba_id)
                                          WHERE uzivatel_id = ?', array($uzivatelId));
        return is_array($data) ? array_intersect_key($data, array_flip($poleHodnot)) : $data;
    }

    /**
     ** Vráti zoznam vŠetkych uživateľov
     * @return array|mixed zoznam užvateľov
     */
    public function vratZoznamUzivatelov()
    {
        $kluce = array('osoba', OsobaDetailManazer::TEL, OsobaDetailManazer::EMAIL, self::UZIVATEL_ID, UzivatelManazer::DATUM_REGISTRACIE, UzivatelManazer::DATUM_PRIHLASENIA);

        $select = 'SELECT CONCAT(COALESCE(meno, ""), " ", COALESCE(priezvisko, "")) AS osoba, tel, email, uzivatel_id, datum_registracie, datum_prihlasenia';
        $from = ' FROM osoba ';
        $join = ' JOIN osoba_detail USING (osoba_detail_id) 
                  JOIN uzivatel USING (uzivatel_id) ';
        $where = ' WHERE osoba.uzivatel_id IS NOT NULL';
        $orederBy = ' ORDER BY osoba_id ';

        $data = Db::dopytVsetkyRiadky($select . $from . $join . $where . $orederBy);

        return is_array($data) ? Pole::filtrujKluce($data, $kluce) : $data;
    }

    /**
     ** Vráti parý osobaId
     * @return array
     */
    public function vratParyOsobaId()
    {
        return Db::dopytPary('SELECT osoba_id, CONCAT(COALESCE(meno, ""), " ", COALESCE(priezvisko, "")) AS osoba 
                            FROM osoba
                            JOIN osoba_detail USING (osoba_detail_id)
                            ORDER BY osoba', 'osoba', self::OSOBA_ID);
    }

    /**
     ** Vráti moznost výberu klientov, ktorých este nema priradených a taktiez seba saemho nevraciam
     * @param int $trenerId Id trenera ktorému ponukam klientov
     * @return array páry Meno -> id
     */
    public function vratKlientov($trenerId)
    {
        return Db::dopytPary('SELECT osoba_id, CONCAT(COALESCE(meno, ""), " ", COALESCE(priezvisko, "")) AS osoba
                            FROM osoba
                            JOIN osoba_detail USING (osoba_detail_id)
                            LEFT JOIN klient ON osoba_id = osoba_klient_id
                            WHERE (osoba_trener_id != ? OR osoba_trener_id IS NULL) AND osoba_id != ?
                            ORDER BY osoba', 'osoba', self::OSOBA_ID, array($trenerId, $trenerId));
    }

    /**
     ** Načita pary Osoba => email
     * @return array|mixed
     */
    public function nacitajParyOsobaEmail()
    {
        return Db::dopytPary('SELECT email, CONCAT(COALESCE(meno, ""), " ", COALESCE(priezvisko, "")) AS osoba
                            FROM osoba
                            JOIN osoba_detail USING (osoba_detail_id)
                            ORDER BY osoba', 'osoba', OsobaDetailManazer::EMAIL);
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