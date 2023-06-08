<?php

namespace App\RezervaciaModul\Model;

use App\AdministraciaModul\Uzivatel\Kontroler\RegistraciaKontroler;
use App\AdministraciaModul\Uzivatel\Model\OsobaDetailManazer;
use App\AdministraciaModul\Uzivatel\Model\OsobaManazer;
use App\AdministraciaModul\Uzivatel\Model\TrenerManazer;
use App\AdministraciaModul\Uzivatel\Model\UzivatelManazer;
use App\ZakladModul\System\Kontroler\Kontroler;
use Micho\Cas;
use Micho\ChybaKalendar;
use Micho\ChybaUzivatela;
use Micho\Db;
use Micho\Formular\Formular;
use Micho\Kalendar;
use Micho\Utility\DatumCas;
use Micho\Utility\Pole;
use Micho\Utility\Retazec;
use DateTime;
use DialogPomocne;
use PDOException;

/**
 ** Správca Rezervácie
 * Class GymManazer
 * @package App\KalendarModul\Model
 */
class RezervaciaManazer
{
    /**
     * Názov Tabuľky pre Spracovanie rezervacie
     */
    const REZERVACIA_TABULKA = 'rezervacia';

    /**
     * Konštanty Databázy 'rezervacia'
     */
    const REZERVACIA_ID = 'rezervacia_id';
    const DATUM_VYTVORENIA = 'datum_vytvorenia';
    const DATUM = 'datum';
    const CAS_OD = 'cas_od';
    const CAS_DO = 'cas_do';
    const OSOBA_ID = OsobaManazer::OSOBA_ID;

    /**
     * @var Kalendar Objekt vytvoreného Kalendára
     */
    public $kalendar;

    const VSETKY = 'vsetky';
    const MINULE = 'minule';

    /**
     * Konstatni pri výbere zobrazenia permanentky
     */
    const MOZNOSTI_VYBERU = array('Minulé' => self::MINULE, 'Dnes' => 'dnes', 'Zajtra' => 'zajtra', 'Všetky' => self::VSETKY);
    const VYBER_CAS_MODIFY = array('dnes' => ' + 0 day', 'zajtra' => ' + 1 day');

    /**
     * RezervaciaManazer constructor.
     * @param int $vybratyRok Vybratý Rok
     * @param int $vybratyMesiac Vybratý Mesiac
     * @param int $vybratyDen Vybratý deň
     * @throws ChybaKalendar
     */
    public function __construct($vybratyRok = false, $vybratyMesiac = false, $vybratyDen = false)
    {
        if(($vybratyRok && $vybratyMesiac && $vybratyDen) !== false)
            $this->kalendar = new Kalendar($vybratyRok, $vybratyMesiac, $vybratyDen);
    }

    /**
     * @param string $typ Služba pre ktorú vytváram kalendár gym, ...
     * @param false $vybratyTyzden Či chcem vrátit konkrétny týŽdeň
     * @return string HTML kalendár
     */
    public function zostavHtmlKalendar($typ, $vybratyTyzden = false)
    {
        $od = $this->kalendar->vratVybratyDatum()->format('Y-m') . '-1';
        $do = $this->kalendar->vratVybratyDatum()->format('Y-m') . '-' . $this->kalendar->vratPoslednyDenMesiac();
        $rezervaciePocet= $this->nacitajPocetRezervaciiOdDo($od, $do);

        $triedaAktualny = ' btn-secondary ';
        $triedaVybraty = ' btn-success ';

        $html = '<table class="table table-sm table-dark m-0">
                        <thead class="bg-danger text-center">
                            <tr>
                                <th>Po</th><th>Ut</th><th>St</th><th>Št</th><th>Pi</th><th>So</th><th>Ne</th>
                            </tr>
                        </thead>
                        <tbody>';

        $dniMesiaca = $this->kalendar->zostavTyzdneMesiaca($vybratyTyzden);
        foreach ($dniMesiaca as $tyzden)
        {
            $html .= '<tr>';
            foreach ($tyzden as $den)
            {
                $trieda = ($den == $this->kalendar->vratAktualnyDen() && $this->kalendar->vratAktualnyMesiac() == $this->kalendar->vratVybratyMesiac() ? $triedaAktualny : '');
                $trieda .= ($den == $this->kalendar->vratVybratyDen() ? $triedaVybraty : '');
                $trieda .= empty($trieda) ? ' btn-dark ' : '';

                $href = 'href="sluzba/' . $typ . '/rezervacia/' . $this->kalendar->vratVybratyRok() . '/' . $this->kalendar->vratVybratyMesiac(). '/' . $den.'"';

                $pocet = isset($rezervaciePocet[$den]) ? '<span class="badge badge-warning vpravo-hore small" style="z-index: 1;">' . $rezervaciePocet[$den] . '</span>' : '';

                $odkaz = empty($den) ? '' : '<a ' . $href . ' class="d-block btn font-weight-bold ' . $trieda . '"><span style="z-index: 5000;">' . $den . '</span>'. $pocet . '</a>';

                $html .= '<td class="p-0"><div class="position-relative">' . $odkaz . '</div></td>';
            }

            $html .= '</tr>';
        }
        $html .= '</tbody></table>';
        return $html;
    }

    /**
     ** Vygeneruje checkboxi Časov pre formulár
     * @param Formular $formular Objekt Formuláru do ktorého pridavam checkboxy
     * @param int $start Štarovný Čas ako cele čislo 7 / 8 / 9 ...
     * @param int $koniec Koncový Čas ako cele čislo 7 / 8 / 9 ...
     * @param int $krok Krok času v minutách 15 / 30 ...
     * @return int Počet vygenerovaných Časov
     *//*
    public function zostavCheckboxy(Formular $formular, $start, $koniec, $krok)
    {
        $cas = new Cas();
        $casy = $cas->generujCasy($start, $koniec, $krok);

        foreach ($casy as $nazov => $cas)
        {
            $formular->pridajCheckBox($cas, $nazov, $cas,false,'','',' ','form-check-label text-left btn btn-sm btn-outline-success',false);
        }
        return count($casy);
    }
*/

    /**
     ** Zmenežuje uloženie dát do databázi
     * @param array $rezervaciaData Data rezervácie
     * @param bool $podmienkaCasuMinulosť Či chcem overiť podmienku času, či je zadaný v minulosty
     * @throws ChybaUzivatela
     * @return string Id posledne ulozenej rezervácie
     */
    public function rezervovat($rezervaciaData, $podmienkaCasuMinulosť = true)
    {
        $datum = $rezervaciaData[self::DATUM];
        $casOd = $rezervaciaData[self::CAS_OD];
        $casDo = $rezervaciaData[self::CAS_DO];

        $osobaManazer = new OsobaManazer();

        if(!isset($rezervaciaData[self::OSOBA_ID])) // ak pride z datmi aj Id uživateľa tak uloži Id ktore prišlo, inak uložim Id prihlaseného uživateľa
        {
            $osobaId = $osobaManazer->vratOsobaId(UzivatelManazer::$uzivatel[UzivatelManazer::UZIVATEL_ID]);
            $rezervaciaData[self::OSOBA_ID] = $osobaId;
        }

        $this->overPodmienkyCasu($casOd, $casDo, $datum, $podmienkaCasuMinulosť);

        /* Toto tu je pre moznost pouzivania kalendara pre neregistovaneho uzivatela
        if (isset($rezervaciaData['email']) && $osobaManazer->overExistenciuEmailu($rezervaciaData['email'])) // ak niesom prihlaseny, overi ci je email registrovany a teda sa uzivatel moze prihlasit
            throw new ChybaUzivatela('Váš email máme zaznamenaný na našej stránke, pre vytvorenie rezervácie sa prihláste');

        unset($rezervaciaData[Formular::ANTISPAM]);


        if(isset($rezervaciaData[Formular::EMAIL])) // niesom prihlasený, tak ukladam zadané údaje
        {
            $osobneUdaje = array_intersect_key($rezervaciaData, array_flip(RegistraciaKontroler::$osobaDetail)); // vytiahne osobné údaje z dát
            $rezervaciaData = array_diff($rezervaciaData,$osobneUdaje); // Oddelenie Časou odostatný dát

            $rezervaciaData['osoba_id'] = $this->overEmailVytvorenieRezervacie($osobneUdaje['email']);

            if(!$rezervaciaData['osoba_id']) // ak v DB neexistuje zaznam o minulej rezervacii tak vy tvorim nový záznam, inak Uladam do rezervácie id osoby z daným emailom
                $rezervaciaData['osoba_id'] = $osobaManazer->ulozOsobu($osobneUdaje);
        }
        else // som prihlaseny tak do rezervaciu ukladam Id prihlaseneho uzivateľa
        */

        $this->overDuplikatZaznamu($casOd, $casDo, $datum, $osobaId);

        return $this->ulozRezervaciu($rezervaciaData);
    }

    /**
     ** Zisti či uživateľ z daným emailom bol už vytvorený pre rezerváciu
     * @param string $email Email uživateľa
     * @return false|mixed Či je uloŽený v DB / jeho ID
     */
    private function overEmailVytvorenieRezervacie($email)
    {
        $osobaId = Db::dopytJedenRiadok('SELECT osoba_id FROM osoba
                                              JOIN osoba_detail USING (osoba_detail_id)
                                              WHERE osoba_detail.email = ? AND osoba.uzivatel_id IS NULL', array($email));
        if($osobaId)
            return $osobaId[self::OSOBA_ID];
        return false;
    }

    /**
     ** Overí či uŽ neexistuje duplikát rezervácie
     * @param string $casOd čas príchodu ... od
     * @param string $casDo čas odchodu ... do
     * @param string $datum Dátum rezervácie
     * @param int $osobaId Id osoby overovaného záznamu
     * @throws ChybaUzivatela
     */
    private function overDuplikatZaznamu($casOd, $casDo, $datum, $osobaId)
    {
        $dopyt = 'SELECT cas_od FROM rezervacia
                  WHERE datum = ? AND (cas_od = ? OR cas_do = ?) AND (osoba_id = ?';
        $parametre = array($datum, $casOd, $casDo);
        if(is_array($osobaId)) // ak mi pride ze vytvram trening tak tam pride viec osob Ktore ukladam naraz a preto musim overit fulplikati pre větký osoby
        {
            $dopyt .= str_repeat(' OR osoba_id = ? ', sizeOf($osobaId)-1);
            $parametre = array_merge($parametre, $osobaId);
        }
        else
            $parametre[] = $osobaId;
        $dopyt .= ')';
        // dalo by sa to vylúčit pridadim unikatnej skupiny do MYSQL
        $zaznam = Db::dopytJedenRiadok($dopyt, $parametre);
        if($zaznam)
            throw new ChybaUzivatela('Na tento termín už máte vytvorenú rezerváciu.');
    }

    /**
     * Uloží Rezerváciu do tabuľky
     * @param array $rezervaciaData Dáta na uloženie
     * @return string Id posledne ulozenej rezervácie
     */
    private function ulozRezervaciu($rezervaciaData)
    {
        Db::vloz(self::REZERVACIA_TABULKA, $rezervaciaData);
        return Db::vratPosledneId();
    }

    /**
     ** zisti Čo bol niejaky Čas vybratý
     * @param array $data Pole dohnot v ktorom hľadam podretazec klúča
     * @throws ChybaUzivatela
     */
    public function overVybratieCasu($data)
    {
        if(!Pole::najdyPodretazecKluca('cas_', $data))
            throw new ChybaUzivatela('Nevybrali ste žiadné časy/termíny.');
    }

    /**
     ** Overi či sa Čas prichodu a odchodu zhodujú
     * @param string $casOd čas príchodu ... od
     * @param string $casDo čas odchodu ... do
     * @param string $datum Dátum rezervácie
     * @param bool $podmienkaCasuMinulosť Či chcem overiť podmienku času, či je zadaný v minulosty
     * @throws ChybaUzivatela
     */
    private function overPodmienkyCasu($casOd, $casDo, $datum, $podmienkaCasuMinulosť = true)
    {
        if($casOd === $casDo)
            throw new ChybaUzivatela('Čas príchodu a odchodu sa nesmú zhodovať.');
        if((int)$casOd > (int)$casDo)
            throw new ChybaUzivatela('Čas príchodu nesmie byť väčší ako čas odchodu.');

        if ($podmienkaCasuMinulosť) // overujem ci je zaadany spravny datum a cas
        {
            $aktualnyDatum = new DateTime();
            $vybratyDatum = new DateTime($datum . ' ' . $casOd);
            if($vybratyDatum <= $aktualnyDatum)
                throw new ChybaUzivatela('Vybratí Dátum a Čas je v minulosti.');
        }
    }

    /**
     ** Načita všetky rezervácie uzivatela
     * @param string $uzivatelId Id uživateľa ktorého rezervácie chcem zobraziť
     * @return array|mixed
     */
    public function nacitajRezervacieUzivatelaVsetky($uzivatelId)
    {
        $rezervacie['buduce'] = $this->nacitajRezervacieUzivatela($uzivatelId, 'buduce');

        $rezervacie['minule'] = $this->nacitajRezervacieUzivatela($uzivatelId, 'minule');

        return $rezervacie;
    }

    /**
     ** Načita rezervácie uzivatela
     * @param string $uzivatelId Id uživateľa ktorého rezervácie chcem zobraziť
     * @param strin $ake Ake chcem ci minule alebo buduce
     * @return array|mixed
     */
    private function nacitajRezervacieUzivatela($uzivatelId, $ake)
    {
        $kluce = array (self::REZERVACIA_ID, self::CAS_OD, self::CAS_DO, self::DATUM); // názvy stĺpcov,ktoré chcem z tabuľky načitať

        $dopyt = 'SELECT rezervacia_id, TIME_FORMAT(cas_od, "%H:%i") AS cas_od, TIME_FORMAT(cas_do, "%H:%i")AS cas_do, datum
                    FROM rezervacia
                    JOIN osoba USING (osoba_id)
                    LEFT JOIN skupina USING (rezervacia_id)
                    WHERE skupina_id IS NULL AND uzivatel_id = ?';

        if($ake === 'buduce')
            $dopyt .= ' AND TIMESTAMP(datum, cas_od) >= CURRENT_TIMESTAMP()
            ORDER BY datum ,';
        if($ake === 'minule')
            $dopyt .= ' AND TIMESTAMP(datum, cas_od) <= CURRENT_TIMESTAMP()
            ORDER BY datum DESC,';

        $dopyt .=  'cas_od, cas_do ';

        $rezervacie = Db::dopytVsetkyRiadky($dopyt, array($uzivatelId));
        return is_array($rezervacie) ? Pole::filtrujKluce($rezervacie, $kluce) : $rezervacie;
    }

    /**
     ** Vrati počet jednotlivých rezervácii uživateľa
     * @param int $uzivatelId Id uživateľa
     * @return array|mixed
     */
    public function vratPocetRezervaciiUzivatela($uzivatelId)
    {
        $kluce = array('rezervacie_individualne', 'rezervacie_s_klientom', 'rezervacie_s_trenerom');
        $pocetRezervacii = Db::dopytJedenRiadok('SELECT
                                        (SELECT COUNT(*)
                                FROM rezervacia
                                JOIN osoba USING (osoba_id)
                                LEFT JOIN skupina USING (rezervacia_id)
                                WHERE skupina_id IS NULL AND uzivatel_id = ?) AS rezervacie_individualne,

                                        (SELECT COUNT(DISTINCT skupina.rezervacia_id)
                                FROM skupina
                                JOIN rezervacia USING (rezervacia_id)
                                JOIN osoba ON rezervacia.osoba_id = osoba.osoba_id
                                WHERE uzivatel_id = ?) as rezervacie_s_klientom,

                                        (SELECT COUNT(*)
                                FROM skupina
                                JOIN osoba USING (osoba_id)
                                WHERE uzivatel_id = ?) as rezervacie_s_trenerom

                                        ', array($uzivatelId, $uzivatelId, $uzivatelId));
        return is_array($pocetRezervacii) ? Pole::filtrujKluce($pocetRezervacii, $kluce) : $pocetRezervacii;
    }

    /**
     ** Zostavy html na výpis rezervácie
     * @param string $datum Dátum v Db podobe ktorý chem zobraziť rezervácie
     * @param string $typ Typ rezervácie ktoru naČitávam gym,...
     * @param array $casy Pole Časov ktoré vypisujem vo výpise rezervácie
     * @return string
     *
     */
    public function zostavHtmlRezervacie($datum, $typ, $casy)
    {
        $rezervacie = $this->nacitajRezervacieKalendar($datum);

        $html = '<table class="table table-sm table-hover table-bordered table-dark text-center align-bottom">
                    <thead class="bg-danger">
                        <tr>
                            <th>Príchod</th>
                            <th>Odchod</th>
                            <th>Meno</th>
                            <th>Edit.</th>
                        </tr>
                    </thead>
                    <tbody>';

        $zoskupenie = array();
        foreach ($rezervacie as $rezervacia) // zoskupenie rezervacie pre lepší výpis
        {
            $zoskupenie[$rezervacia[self::CAS_OD]][] = $rezervacia;
        }
        unset($casy[array_key_last($casy)]) ; //odobratie poledneho casu
        $rezervacie = array_merge($casy, $zoskupenie);

        $poradieCas = 0;
        $vynechaneCasy = array();

        $uzivatelId = UzivatelManazer::$uzivatel[UzivatelManazer::UZIVATEL_ID]; // Id prihlaseného uživateľa
        foreach ($rezervacie as $cas => $rezervacia)
        {
            if (is_array($rezervacia)) // vypisovanie terminov rezervacií
            {
                foreach ($rezervacia as $kluc => $hodnota)
                {
                    $zrusit = '...';
                    $hodina = explode(':', $hodnota[self::CAS_OD])[0]; // vydolovanie hodiny a minut yna porovnanie , kvoli prideleniu tlacitla na odstránenie
                    $minuta = explode(':', $hodnota[self::CAS_OD])[1];

                    $vybratiDatumCas = $this->kalendar->vratVybratyDatum()->setTime($hodina, $minuta);
                    $aktulanyDatumCas = $this->kalendar->vratAktualnyDatum();
                    $dialogOdstranenia = ((UzivatelManazer::$uzivatel && (UzivatelManazer::$uzivatel[UzivatelManazer::ADMIN] || UzivatelManazer::$uzivatel[UzivatelManazer::PROGRAMATOR])) || $hodnota[OsobaManazer::UZIVATEL_ID] === $uzivatelId) && $vybratiDatumCas >= $aktulanyDatumCas;

                    if($dialogOdstranenia)
                        $zrusit = DialogPomocne::zostavDialogOdstranenia($hodnota[self::REZERVACIA_ID], 'rezervacia/zrus-rezervaciu/'. $hodnota[self::REZERVACIA_ID] . '?presmeruj=' . Kontroler::$aktualnaUrl, 'Skutočne si prajete zrušiť skupinový trening?');

                    if($hodnota['skupina'] > 0) // ak je v skupine viacej ludi, cize cislo je vecsie ako 0 tak som nastaveny na skupinovom treningu
                    {
                        $vynechaneCasy[$poradieCas]['od'] = $hodnota[self::CAS_OD]; // intervali casu na vynechanie
                        $vynechaneCasy[$poradieCas]['do'] = $hodnota[self::CAS_DO];
                        $poradieCas++;

                        $html .= ($vybratiDatumCas >= $aktulanyDatumCas) ? '<tr class="bg-cookies font-weight-bold" style="color: ' . $hodnota[TrenerManazer::FARBA] . '">' : '<tr class="text-secondary">';

                        $html .= ' <td>' . $hodnota[self::CAS_OD] . '</td>
                                <td>' . $hodnota[self::CAS_DO] . '</td>
                                <td class="">' . $hodnota[TrenerManazer::PREZIVKA] . ' <small>(Súkromný tréning)</small></td>';
                    }
                    else
                    {
                        $html .= ($vybratiDatumCas >= $aktulanyDatumCas) ? '<tr class="text-warning">' : '<tr class="text-secondary">';

                        $html .= '  <td>' . $hodnota[self::CAS_OD] . '</td>';
                        $html .= ' <td>' . $hodnota[self::CAS_DO] . '</td>
                                <td>' . $hodnota[OsobaDetailManazer::MENO] . ' ' . Retazec::skrat($hodnota[OsobaDetailManazer::PRIEZVISKO],1) . '</td>';
                    }
                    $html .= '<td>' . $zrusit . '</td>
                              </tr>';
                }
            }
            else // vypsi casu bez rezervácie
            {
                $html .= '<tr >
                            <td>' . $cas. '</td>
                          </tr>';
            }
        }
        $html .= '</tbody>
                </table>';
        return array('html' => $html, 'vynechaneCasy' => $vynechaneCasy);
    }

    /**
     ** Načíta rezervaciu podľa Id
     * @param int $rezervaciaId Id rezervácie
     * @return array|mixed Rezervácia
     */
    public function nacitajRezervaciu($rezervaciaId)
    {
        $poleHodnot = array(self::OSOBA_ID, self::DATUM, self::CAS_OD);

        $data = Db::dopytJedenRiadok('SELECT ' . implode(', ', $poleHodnot) . '
                                          FROM rezervacia
                                          WHERE rezervacia_id = ?', array($rezervaciaId));
        return is_array($data) ? array_intersect_key($data, array_flip($poleHodnot)) : $data;
    }

       /**
     ** Odstráni rezerváciu z DB
     * @param int $rezervaciaId Id rezervácie ktoru odstraňujem
     * @param int $osobaId Id prihlasenej osoby
     */
    public function odstranRezervaciu($rezervaciaId, $osobaId)
    {
        //prepisanie hodnoty kvoli tomu ze to stale vypisovalo chybu pri odstatranovani
        //docastne riesenie ak by sa to vyriesilo tak to potom vymaaat
        Db::$nastavenie[\PDO::ATTR_EMULATE_PREPARES] = true;
        Db::$spojenie = null;
        Db::pripoj(\Nastavenia::$db['host'], \Nastavenia::$db['user'], \Nastavenia::$db['password'], \Nastavenia::$db['database']); // pripojenie k databáze

        // odstranenie a taktiez podmienky aby sa mohol odstraniť buď trener ktoreho to je alebo admin alebo programator
        $odstranenie = (bool) Db::dopyt('DELETE rezervacia FROM rezervacia
                                                JOIN osoba ON osoba.osoba_id = ?
                                                JOIN uzivatel USING (uzivatel_id)
                                                WHERE rezervacia_id = ? AND ((rezervacia.osoba_id = ? AND concat(datum, " ", cas_od) >= concat(CURDATE(), " ", CURTIME())) OR admin OR programator)', array($osobaId, $rezervaciaId, $osobaId));
        if(!$odstranenie)
             throw new PDOException();
    }

    /**
     ** Vráti pocet záznamov urcitého dátumu pre danu osobou
     * @param string $permanentkaDatum Dátum vytvorenia permanentky
     * @param int $osobaId Id osoby ktorej prepocitavam permanentku
     * @return false|mixed
     */
    public function vratPocetZaznamov($permanentkaDatum, $osobaId)
    {
        $permanentkaDatum = DatumCas::formatujNaTvar($permanentkaDatum, DatumCas::DB_DATUM_FORMAT);

        $datumCas = new DateTime();
        $datum = $datumCas->format(DatumCas::DB_DATUM_FORMAT);
        $cas = $datumCas->format(DatumCas::DB_CAS_FORMAT);

        $dopyt = 'SELECT COUNT(*)
                      FROM rezervacia
                      WHERE (datum >= ? AND datum < ? OR (datum = ? AND cas_od <= ?)) AND osoba_id = ? ';
        $paramentre = array($permanentkaDatum, $datum, $datum, $cas, $osobaId);

        return Db::dopytSamotny($dopyt, $paramentre);
    }

    /**
     ** Načita rezervácie pre zobrazenie adminovy ako obsadenosť Gymu
     * @param DateTime $datum Dátum v DB formate
     * @param bool $vyber Výber nacitania.. dnes, zajtra , vsetky
     * @return array|mixed Pole Rezervácii
     */
    public function nacitajRezervacieAdmin(DateTime $datum, $vyber)
    {


        $kluceRezervacie = array (self::REZERVACIA_ID, self::DATUM_VYTVORENIA, self::DATUM, self::CAS_OD, self::CAS_DO,
                        'rezervacia_meno', 'rezervacia_priezvisko', 'rezervacia_uzivatel_id',
                        'klient_meno', 'klient_priezvisko', 'klient_uzivatel_id',
                        PermanentkaTypManazer::NAZOV,
                        TrenerManazer::PREZIVKA, TrenerManazer::FARBA);

        $permanentkaManazer = new PermanentkaManazer();
        $permanentkaManazer->overAktivnostVstupovychPermanentiek();

        $dopyt = 'SELECT rezervacia_id, datum_vytvorenia, rezervacia.datum, TIME_FORMAT(cas_od, "%H:%i") AS cas_od, TIME_FORMAT(cas_do, "%H:%i")AS cas_do,
                            od1.meno AS rezervacia_meno, od1.priezvisko AS rezervacia_priezvisko, os1.uzivatel_id AS rezervacia_uzivatel_id,
                            od2.meno AS klient_meno, od2.priezvisko AS klient_priezvisko, os2.uzivatel_id AS klient_uzivatel_id,
                            nazov, prezivka, farba

                         FROM rezervacia

                         JOIN osoba os1 ON rezervacia.osoba_id = os1.osoba_id
                         JOIN osoba_detail od1 ON od1.osoba_detail_id = os1.osoba_detail_id

                         LEFT JOIN skupina USING (rezervacia_id)
                         LEFT JOIN osoba os2 ON skupina.osoba_id = os2.osoba_id
                         LEFT JOIN osoba_detail od2 ON od2.osoba_detail_id = os2.osoba_detail_id

                         LEFT JOIN permanentka ON rezervacia.osoba_id = permanentka.osoba_id AND aktivna
                         LEFT JOIN permanentka_typ USING (permanentka_typ_id)

                         LEFT JOIN trener ON rezervacia.osoba_id = trener.osoba_id AND aktivny

                         WHERE
                         ';

        if (array_key_exists($vyber, self::VYBER_CAS_MODIFY)) //ak je vo v=ýbere modifikacie
        {
            $datum->modify(self::VYBER_CAS_MODIFY[$vyber]); // modifikujem cas ako +0day + 1 day podla vyberu
            $dopyt .= 'rezervacia.datum = ? ORDER BY cas_od, cas_do, rezervacia_id';
        }

        else // ak nieje vo výberebere
        {
            if($vyber === self::VSETKY)
            {
                $dopyt .= 'rezervacia.datum >= ?
                                ORDER BY rezervacia.datum=CURDATE() DESC,
                                rezervacia.datum>CURDATE() DESC,
                                rezervacia.datum,
                                rezervacia.datum, cas_od, cas_do, rezervacia_id';
            }
            elseif($vyber === self::MINULE)
            {
                $dopyt .= 'rezervacia.datum < ?
                                ORDER BY rezervacia.datum DESC,
                                rezervacia.datum, cas_od, cas_do, rezervacia_id LIMIT 101';
            }
        }



        $datum = $datum->format(DatumCas::DB_DATUM_FORMAT);

        // Načitanie rezervácií
        $rezervacie = Db::dopytVsetkyRiadky($dopyt, array($datum));

        $rezervacie = is_array($rezervacie) ? Pole::filtrujKluce($rezervacie, $kluceRezervacie) : $rezervacie;

        $roztriedeneRezervacie = $this->roztriedRezervacie($rezervacie);

        return $roztriedeneRezervacie;
    }

    /**
     ** Rozdtriedi rezervácie pre jednoduchší výpis
     * @param array $rezervacieDb Pole rezervácii načitaných z Databázy
     * @return array roztriedené rezervácie
     */
    private function roztriedRezervacie($rezervacieDb)
    {
        $roztriedeneRezervacie = array();

        // Pomocné kluče pre separovanie jednotlivych hodnot
        $kluceRezervacia = array(RezervaciaManazer::DATUM_VYTVORENIA, RezervaciaManazer::DATUM, RezervaciaManazer::CAS_OD, RezervaciaManazer::CAS_DO,
            'rezervacia_meno', 'rezervacia_priezvisko', 'rezervacia_uzivatel_id');
        $kluceKlient = array('klient_meno', 'klient_priezvisko', 'klient_uzivatel_id');
        $klucePermanentka = array(PermanentkaTypManazer::NAZOV);
        $kluceTrener = array(TrenerManazer::PREZIVKA, TrenerManazer::FARBA);

        // Triedenie a zoskupovanie
        foreach ($rezervacieDb as $rezervacia)
        {
            $rezervaciaId = array_shift($rezervacia);

            if (!array_key_exists($rezervaciaId, $roztriedeneRezervacie)) // ak neexistuje pole z danim klucom RezervaciaID tak vytvorim nove  a priradi mu hodnoty
            {
                $roztriedeneRezervacie[$rezervaciaId] = array_intersect_key($rezervacia, array_flip($kluceRezervacia));
                if(!empty($rezervacia[TrenerManazer::PREZIVKA])) // ak je to trener tak mu pridelim aj potrebne hodntoy
                    $roztriedeneRezervacie[$rezervaciaId]['trener'] = array_intersect_key($rezervacia, array_flip($kluceTrener));

                if(!empty($rezervacia[PermanentkaTypManazer::NAZOV])) // pridelenie permanentky popripade napsianie neaktivna
                    $roztriedeneRezervacie[$rezervaciaId]['permanentka'] = array_intersect_key($rezervacia, array_flip($klucePermanentka));

                else
                    $roztriedeneRezervacie[$rezervaciaId]['permanentka']['nazov'] = 'Neaktívna';

            }
            if (!empty($rezervacia['klient_uzivatel_id'])) // ak ma rezervacia klientov teda je skupinova tak ulozim ich potrebne údaje
            {
                $roztriedeneRezervacie[$rezervaciaId]['klient'][] = array_intersect_key($rezervacia, array_flip($kluceKlient));
            }
        }
        return $roztriedeneRezervacie;
    }

    /**
     ** zisti či ma uživateľ dneska rezerváciu bud individualnu alebo s trenerom
     * @param int $uzivatelId Id uživateľa
     * @return bool
     */
    public function zistiDneskaRezervacia($uzivatelId)
    {
         $individualna = (bool) Db::dopytSamotny('SELECT COUNT(*) FROM rezervacia
                                              JOIN osoba USING (osoba_id)
                                              WHERE uzivatel_id = ? AND datum = CURDATE() LIMIT 1', array($uzivatelId));
         $sTrenerom = (bool) Db::dopytSamotny('SELECT COUNT(*) FROM skupina
                                              JOIN osoba USING (osoba_id)
                                              JOIN rezervacia USING (rezervacia_id)
                                              WHERE uzivatel_id = ? AND datum = CURDATE() LIMIT 1', array($uzivatelId));

        return $individualna || $sTrenerom;
    }

    /**
     ** Načita všetky skupinové rezervácie trénera
     * @param int $uzivatelId Id uzivatela ktorej tréningi nacitavam
     * @param bool $detail Či chem zobraziť aj detail uzovatela tel, email
     * @return array|mixed Načitané skupiny
     */
    public function nacitajRezervacieTreneraVsetky($uzivatelId, bool $detail)
    {
        $osobaManazer = new OsobaManazer();
        $osobaId = $osobaManazer->vratOsobaId($uzivatelId);

        $rezervacie['buduce'] = $this->nacitajRezervacieTrenera($osobaId, $detail, 'buduce');

        $rezervacie['minule'] = $this->nacitajRezervacieTrenera($osobaId, $detail, 'minule');

        return $rezervacie;
    }

    /**
     ** Načita skupinové rezervácie trénera
     * @param int $osobaId Id osoby ktorej tréningi nacitavam
     * @param bool $detail Či chem zobraziť aj detail uzovatela tel, email
     * @return array|mixed Načitané skupiny
     */
    private function nacitajRezervacieTrenera($osobaId, bool $detail, $ake)
    {
        $kluce = array (self::REZERVACIA_ID, self::DATUM, self::CAS_OD, self::CAS_DO, 'osoba', UzivatelManazer::UZIVATEL_ID, PoznamkaManazer::POZNAMKA, SkupinaManazer::SKUPINA_ID); // názvy stĺpcov,ktoré chcem z tabuľky načitať

        $dopyt = 'SELECT rezervacia_id, datum, TIME_FORMAT(cas_od, "%H:%i") AS cas_od, TIME_FORMAT(cas_do, "%H:%i") AS cas_do, CONCAT(COALESCE(meno, ""), " ", COALESCE(priezvisko, "")) AS osoba, uzivatel_id, poznamka, skupina_id ';

        if($detail)
        {
            $dopyt .= ', tel, email';
            $kluce = array_merge($kluce, array(OsobaDetailManazer::TEL, OsobaDetailManazer::EMAIL));
        }

        $dopyt .= '
                  FROM rezervacia
                  RIGHT JOIN skupina USING (rezervacia_id)
                  JOIN osoba ON skupina.osoba_id = osoba.osoba_id
                  JOIN osoba_detail USING (osoba_detail_id)
                  LEFT JOIN poznamka USING (rezervacia_id)
                  WHERE rezervacia.osoba_id = ?
                  ';

        if($ake === 'buduce')
            $dopyt .= ' AND TIMESTAMP(datum, cas_od) >= CURRENT_TIMESTAMP()
            ORDER BY datum ,';
        if($ake === 'minule')
            $dopyt .= ' AND TIMESTAMP(datum, cas_od) <= CURRENT_TIMESTAMP()
            ORDER BY datum DESC,';

        $dopyt .=  ' cas_od, cas_do, rezervacia_id';

        $data = Db::dopytVsetkyRiadky($dopyt, array($osobaId));

        $rezervacie = is_array($data) ? Pole::filtrujKluce($data, $kluce) : $data;

        return $this->roztriedRezervacieTrenera($rezervacie);
    }


    /**
     ** Načita všetky skupinove rezervácie uživateľa (sTrénerom)
     * @param int $uzivatelId Id uzivatela ktoreho treningy zobrazujem
     * @return array|mixed Načitané skupiny
     */
    public function nacitajSkupinoveRezervacieUzivatelaVsetky($uzivatelId)
    {
        $rezervacie['buduce'] = $this->nacitajSkupinoveRezervacieUzivatela($uzivatelId,'buduce');

        $rezervacie['minule'] = $this->nacitajSkupinoveRezervacieUzivatela($uzivatelId,'minule');

        return $rezervacie;
    }

    /**
     ** Načita skupinove rezervácie uživateľa (sTrénerom)
     * @param int $uzivatelId Id uzivatela ktoreho treningy zobrazujem
     * @param string $ake Ake chcem ci minule alebo buduce
     * @return array|mixed Načitané skupiny
     */
    private function nacitajSkupinoveRezervacieUzivatela($uzivatelId, $ake)
    {
        $osobaManazer = new OsobaManazer();
        $osoba_id = $osobaManazer->vratOsobaId($uzivatelId);
        $kluce = array (self::REZERVACIA_ID, RezervaciaManazer::DATUM, RezervaciaManazer::CAS_OD, RezervaciaManazer::CAS_DO,
            TrenerManazer::TRENER_ID, TrenerManazer::PREZIVKA, TrenerManazer::FARBA, OsobaDetailManazer::TEL, OsobaDetailManazer::EMAIL, OsobaManazer::UZIVATEL_ID); // názvy stĺpcov,ktoré chcem z tabuľky načitať

        $dopyt = 'SELECT skupina.rezervacia_id, datum, TIME_FORMAT(cas_od, "%H:%i") AS cas_od, TIME_FORMAT(cas_do, "%H:%i") AS cas_do, trener_id, prezivka, farba, tel, email, uzivatel_id
                  FROM skupina
                  JOIN rezervacia USING (rezervacia_id)
                  JOIN trener ON rezervacia.osoba_id = trener.osoba_id
                  JOIN osoba ON trener.osoba_id = osoba.osoba_id
                  JOIN osoba_detail USING (osoba_detail_id)

                  WHERE skupina.osoba_id = ? ';

        if($ake === 'buduce')
            $dopyt .= ' AND TIMESTAMP(datum, cas_od) >= CURRENT_TIMESTAMP()
            ORDER BY datum,';
        if($ake === 'minule')
            $dopyt .= ' AND TIMESTAMP(datum, cas_od) <= CURRENT_TIMESTAMP()
            ORDER BY datum DESC,';

        $dopyt .= ' cas_od, cas_do ';

        $rezervacie = Db::dopytVsetkyRiadky($dopyt, array($osoba_id));
        return is_array($rezervacie) ? Pole::filtrujKluce($rezervacie, $kluce) : $rezervacie;
    }

    /**
     ** Načita rezervácie pre zobrazenie v kalendáry
     * @param string $datum Dátum v Db podobe ktorý chem zobraziť rezervácie
     * @return array|mixed
     */
    public function nacitajRezervacieKalendar($datum)
    {
        $kluce = array (self::REZERVACIA_ID, self::CAS_OD, self::CAS_DO,
                        OsobaManazer::UZIVATEL_ID,
                        OsobaDetailManazer::MENO, OsobaDetailManazer::PRIEZVISKO,
                        'skupina',
                        TrenerManazer::PREZIVKA,TrenerManazer::FARBA); // názvy stĺpcov,ktoré chcem z tabuľky načitať

        $dopyt = 'SELECT rezervacia_id, TIME_FORMAT(cas_od, "%H:%i") AS cas_od, TIME_FORMAT(cas_do, "%H:%i")AS cas_do,
                        uzivatel_id,
                        meno, priezvisko,
                        (SELECT COUNT(*) FROM skupina WHERE rezervacia.rezervacia_id = skupina.rezervacia_id) as skupina,
                        prezivka, farba

                    FROM rezervacia

                    JOIN osoba USING (osoba_id)
                    JOIN osoba_detail USING (osoba_detail_id)

                    LEFT JOIN skupina USING (rezervacia_id)

                    LEFT JOIN trener ON trener.osoba_id = rezervacia.osoba_id

                    WHERE datum = ?

                    GROUP BY rezervacia_id';

        $rezervacie = Db::dopytVsetkyRiadky($dopyt, array($datum));

        return is_array($rezervacie) ? Pole::filtrujKluce($rezervacie, $kluce) : $rezervacie;
    }

    /**
     ** Rozdtriedi rezervácie trenera pre jednoduchší výpis
     * @param array $rezervacieDb Pole rezervácii načitaných z Databázy
     * @return array roztriedené rezervácie
     */
    private function roztriedRezervacieTrenera($rezervacieDb)
    {
        // Pomocné kluče pre separovanie jednotlivych hodnot
        $kluceRezervaciaInfo = array(RezervaciaManazer::DATUM, RezervaciaManazer::CAS_OD, RezervaciaManazer::CAS_DO, PoznamkaManazer::POZNAMKA,);
        $kluceDetail = array('osoba', UzivatelManazer::UZIVATEL_ID, SkupinaManazer::SKUPINA_ID, OsobaDetailManazer::TEL,OsobaDetailManazer::EMAIL);

        $skupiny = array();
        // Triedenie a zoskupovanie
        foreach ($rezervacieDb as $skupina)
        {
            $rezervaciaId = array_shift($skupina);

            if (!array_key_exists($rezervaciaId, $skupiny)) // ak neexistuje pole z danim klucom RezervaciaID tak vytvorim nove  a priradi mu hodnoty
            {
                $skupiny[$rezervaciaId] = array_intersect_key($skupina, array_flip($kluceRezervaciaInfo));
            }

            $skupiny[$rezervaciaId]['klient'][] = array_intersect_key($skupina, array_flip($kluceDetail));
        }
        return $skupiny;
    }

    /**
     ** Načita počet rezervácií v danom Dni v požadovanom rozmedzí dátumov
     * @param int $od Dátum Zaciatku
     * @param int $do Dátum Konca
     * @return array|mixed Počet rezervácii v daných dnoch
     */
    public function nacitajPocetRezervaciiOdDo($od, $do)
    {
       return Db::dopytPary('SELECT DATE_FORMAT(datum, "%e") as datum, COUNT(*) as pocet
                                                   FROM rezervacia
                                                   WHERE datum BETWEEN ? AND ? GROUP BY datum ORDER BY datum', 'datum', 'pocet', array($od, $do));
    }





}
/*
 * Autor: MiCHo
 */
