<?php

namespace App\AdministraciaModul\Uzivatel\Model;

use App\AdministraciaModul\Uzivatel\Kontroler\PrihlasenieKontroler;
use App\ZakladModul\Kontroler\EmailKontroler;
use Micho\ChybaValidacie;
use Micho\Db;
use Micho\ChybaUzivatela;
use Micho\OdosielacEmailov;
use Micho\Utility\DatumCas;
use Micho\Utility\Pole;
use Micho\Utility\Retazec;
use Nastavenia;
use DateTime;

use PDOException;


/**
 ** Správca uživateľov redakčného systému / Pracovanie z tabuľkou uzivatel
 * Class UzivatelManazer
 * @package App\UzivateliaModul\Model
 */
class UzivatelManazer
{
    /**
     * Názov Tabuľky pre Spracovanie Uživateľa
     */
    const UZIVATEL_TABULKA = 'uzivatel';

    /**
     * Konštanty Databázy 'kontroler'
     */
    const UZIVATEL_ID = 'uzivatel_id';
    const DATUM_REGISTRACIE = 'datum_registracie';
    const DATUM_PRIHLASENIA = 'datum_prihlasenia';
    const HESLO = 'heslo';
    const ADMIN = 'admin';
    const PROGRAMATOR = 'programator';

    const UDAJE_PRIHLAS_UZIVATELA = array(self::UZIVATEL_ID, OsobaDetailManazer::MENO, OsobaDetailManazer::PRIEZVISKO, OsobaDetailManazer::EMAIL, self::ADMIN, self::HESLO, self::PROGRAMATOR, TrenerManazer::AKTIVNY);

    /**
     * @var array|null Aktualne prihlaseny uživateľ alebo null
     */
    public static $uzivatel;

    /**
     ** Uloží aktuálne prihlaseného uživateľa
     */
    public function nacitajUzivatela()
    {
        self::$uzivatel = isset($_SESSION['uzivatel']) ? $_SESSION['uzivatel'] : null;
    }

    /**
     ** Vráti odtlačok hesla
     * @param string $heslo Heslo
     * @return string Odlačok hesla
     */
    private function vratOtlacokHesla($heslo)
    {
        return password_hash($heslo, PASSWORD_DEFAULT);
    }

    /**
     ** Uloží nového uživateľa do tabuľky uživateľov
     * @param string $heslo Heslo uživateľa
     * @return string Id novo prihlaseného uživateľa
     * @throws ChybaUzivatela
     */
    public function ulozUzivatela($heslo)
    {
        $uzivatel = array(self::HESLO => $this->vratOtlacokHesla($heslo));
        try
        {
            Db::vloz(self::UZIVATEL_TABULKA, $uzivatel);
        }
        catch (PDOException $chyba)
        {
            throw new ChybaUzivatela('Pri registrácií uživateľa nastala chyba.');
        }
        return Db::vratPosledneId();
    }

    /**
     ** Overý či sa zadané hesla zhodujú
     * @param string $heslo Heslo
     * @param string $hesloZnovu Heslo znova
     * @throws ChybaUzivatela
     */
    public function overZhoduHesiel($heslo, $hesloZnovu)
    {
        if($heslo !== $hesloZnovu)
            return'Heslo a Heslo znova sa nezhodujú.';
    }

    /**
     ** Prihlási uživateľa do systému
     * @param string $email Prihlasovací email uživateľa
     * @param string $heslo Prihlasovacie heslo uživateľa
     * @return string Chybová správa
     * @throws ChybaUzivatela
     */
    public function prihlas($email, $heslo)
    {
        $uzivatel = Db::dopytJedenRiadok('SELECT ' . implode(', ',self::UDAJE_PRIHLAS_UZIVATELA) . ' as trener
                                          FROM uzivatel 
                                          JOIN osoba USING (uzivatel_id)
                                          JOIN osoba_detail USING (osoba_detail_id)
                                          LEFT JOIN trener USING (osoba_id)
                                          WHERE email = ?', array($email));

        $uzivatel = is_array($uzivatel) ? Pole::filtrujKluce($uzivatel, array_merge(self::UDAJE_PRIHLAS_UZIVATELA, array('trener'))) : $uzivatel;

        if(!$uzivatel)
            throw new ChybaUzivatela('Účet pre zadaný email neexistuje.');

        if(!$uzivatel || !password_verify($heslo, $uzivatel[self::HESLO]))
            throw new ChybaUzivatela('Neplatný EMAIL alebo HESLO.');

        unset($uzivatel[self::HESLO]); // Odstránime heslo z poľa s uživateľov, aby sa nepredávalo na každej stránke webu
        $_SESSION['uzivatel'] = $uzivatel;

        $this->nacitajUzivatela();

        $this->zmenDatumPoslednehoPrihlasenia(UzivatelManazer::$uzivatel['uzivatel_id']);
    }

    /**
     ** Odhlási uživateľa
     */
    public function odhlas()
    {
        unset($_SESSION['uzivatel']);
    }

    /**
     ** Overi zhodsnoť zadaného hesla a hesla uloženého v DB
     * @param int $uzivatelId Id uživateľa ktoreho hesla chcem verifikovať
     * @param string $heslo Heslo na verfikáciu
     * @return string Chybová hlaška
     */
    private function verifikujHesla($uzivatelId, $heslo)
    {
        $uzivatelHeslo = Db::dopytSamotny('SELECT heslo
                                          FROM uzivatel WHERE uzivatel_id = ?', array($uzivatelId));

        if(!password_verify($heslo, $uzivatelHeslo))
            return ('Vami zadané staré heslo sa nezhoduje s Vaším Heslom uloženým v databáze');
    }


    /**
     ** Zvaliduje zmena hesla údaje od uzivatela
     * @param array $data Dáta od uzivateľa
     * @throws ChybaValidacie Vynimka z poľom chýb
     */
    public function overZmenaHeslaHodnoty($data)
    {
        $spravy = array();

        $spravy[] = $this->verifikujHesla(UzivatelManazer::$uzivatel[self::UZIVATEL_ID],$data['stare_heslo']);

        $spravy[] = $this->overZhoduHesiel($data[self::HESLO], $data['heslo_znova']);

        $spravy = array_filter($spravy);

        return $spravy;

        //if(!empty($spravy)) // ak nieje pole prázdne tak vyvolam vynimku na vypísanie správ
            //throw new ChybaValidacie('Nastali chyby validovania', 0, null, $spravy);
    }

    /**
     ** Uloží zmenu hesla d Databázi buď podľa Emailu alebo Id
     * @param string $heslo Nové heslo uživateľa
     * @param int $uzivatel_id Id uživateľa
     * @param string $email Email uživateľa
     */
    public function ulozZmenuHesla($heslo, $uzivatel_id, $email = false)
    {
        $odtlacokHesla = $this->vratOtlacokHesla($heslo);

        if($email)        //0,0067 / 0,0069 / 0,0080 / 0,0077 = 0,0073 Rýchlosť akcie v sekundách
            Db::dopyt('UPDATE uzivatel 
                        JOIN osoba USING (uzivatel_id)
                        JOIN osoba_detail USING (osoba_detail_id)                                                  
                         SET uzivatel.heslo = ? WHERE osoba_detail.email = ?', array($odtlacokHesla, $email));
        else
            Db::dopyt('UPDATE uzivatel                                                  
                         SET heslo = ? WHERE uzivatel_id = ?', array($odtlacokHesla, $uzivatel_id));


        /* Ostatné možnosti výberu z ich rýchlostami

        //0,0078 / 0,0075 / 0,0078 / 0,0095 = 0,00815
         Db::dopyt('UPDATE uzivatel
                         SET uzivatel.heslo = ? WHERE uzivatel_id =
                                                      (SELECT uzivatel_id
                                                      FROM osoba
                                                      JOIN osoba_detail USING (osoba_detail_id)
                                                      WHERE email = ?)', array($odtlacokHesla, $email));

         //0,0085 / 0,0073 / 0,0091 / 0,0079 = 0,0082
        Db::dopyt('UPDATE uzivatel
                         JOIN osoba ON (uzivatel.uzivatel_id = osoba.uzivatel_id)
                         JOIN osoba_detail ON (osoba.osoba_detail_id = osoba_detail.osoba_detail_id)
                         SET uzivatel.heslo = ? WHERE osoba_detail.email = ?', array($odtlacokHesla, $email));

          //0,0118 / 0,0085 / 0,0078 / 0,0115 = 0,0099
        Db::dopyt('UPDATE uzivatel
                         SET uzivatel.heslo = ? WHERE uzivatel_id =
                                                      (SELECT uzivatel_id
                                                      FROM osoba WHERE osoba_detail_id =
                                                                       (SELECT osoba_detail_id
                                                                       FROM osoba_detail
                                                                       WHERE email = ?)
                                                      )', array($odtlacokHesla, $email));
        */
    }

    /**
     * Odošle objednávku emailem spolu zo zprávou
     * @param int $objednavkaId ID objednávky
     * @param string $sprava Správa
     * @throws ChybaUzivatela
     */
    public function odosliStrateneHesloEmail($email) //upraviť
    {
        // Generovanie hesla
        $noveHeslo = Retazec::generujHeslo(true);

        //zmeni heslo uzivateľovi
        $this->ulozZmenuHesla($noveHeslo, false , $email);

        // Načitanie Šablony // Získanie obsahu emailu zo šablony kontroleru
        $prihlasenieKontoler = new PrihlasenieKontroler();
        $prihlasenieKontoler->sablonaZabudnuteHesloEmail($email, $noveHeslo);

        // Šablona rozložena emailu a taktiez Štýly
        $emailKontroler = new EmailKontroler();
        $emailKontroler->index($prihlasenieKontoler);

        ob_start();
        $emailKontroler->vypisPohlad();
        $emailObsah = ob_get_contents();
        ob_end_clean();

        $odosielacEmailov = new OdosielacEmailov();
        $odosielacEmailov->odosli($email, 'Zabudnuté Heslo:' . Nastavenia::$domenaNazov, $emailObsah, Nastavenia::$email);
    }

    /**
     ** Vráti všetkých administratorov
     * @return mixed
     */
    public function vratAdminov($kluce)
    {
        $data = Db::dopytVsetkyRiadky('SELECT ' . implode(', ',$kluce) . ', 
                                                CONCAT(COALESCE(meno, ""), " ", COALESCE(priezvisko, "")) AS osoba
                                            FROM uzivatel
                                            JOIN osoba USING (uzivatel_id)
                                            JOIN osoba_detail USING (osoba_detail_id)
                                            WHERE admin ORDER BY uzivatel_id
        ');
        $kluce[] = 'osoba';
        return is_array($data) ? Pole::filtrujKluce($data, $kluce) : $data;
    }

    /**
     ** Spracuje požiadavku na priradenia administrátorských práv uživateľovy
     * @param $email
     * @throws ChybaUzivatela
     */
    public function pridajAdmina($email)
    {
        $osobaManazer = new OsobaManazer();
        if (!$osobaManazer->overExistenciuEmailu($email))
            throw new ChybaUzivatela('Osoba s týmto emailom nieje registovaná');

        $this->nastavAdmina($email);
    }

    /**
     ** Nastavý uživateľa ako Admina
     * @param string $email Email uživateľa
     */
    private function nastavAdmina($email)
    {
        $a = (bool) Db::dopyt('UPDATE uzivatel 
                        JOIN osoba USING (uzivatel_id)
                        JOIN osoba_detail USING (osoba_detail_id)
                        SET admin = 1 WHERE email = ?', array($email));

        if (!$a)
            throw new ChybaUzivatela('Tento uživateľ už má nastavené administrátorske práva');
        $_SESSION['uzivatel']['admin'] = 1;
    }
    /**
     ** Zruší administrátorske práva uživateľa
     * @param int $uzivatelId ID uŽivatela ktorécho chem zrušit
     */
    public function odstranAdmina($uzivatelId)
    {
        Db::dopyt('UPDATE uzivatel SET admin = 0 WHERE uzivatel_id = ?', array($uzivatelId));

        if(UzivatelManazer::$uzivatel[self::UZIVATEL_ID] == $uzivatelId) // ak odstranuejm ako admina seba tak aj v session uzivatela to nastavim
            $_SESSION['uzivatel']['admin'] = 0;
    }

    /**
     ** Nastavi novú hodnotu pre dátum a čas prihlasenia Na aktuálny dátum a čas
     * @param int $uzivatelId ID uŽivatela ktorýmu menim dátum a Čas prihlásenia
     */
    private function zmenDatumPoslednehoPrihlasenia($uzivatelId)
    {
        $datumCasDB = DatumCas::dbTeraz(); // Aktualný Čas v DB formate

        Db::dopyt('UPDATE uzivatel 
                         SET datum_prihlasenia = ? WHERE uzivatel_id = ?', array($datumCasDB,$uzivatelId));
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