<?php

namespace Micho;

use App\AdministraciaModul\Uzivatel\Model\OsobaDetailManazer;
use App\AdministraciaModul\Uzivatel\Model\UzivatelManazer;
use App\RezervaciaModul\Model\RezervaciaManazer;
use Micho\Db;
use Micho\Utility\Pole;
use PDOException;

/**
 * Class upravaApp Slúzi na komplexnu úpravu sytemu app
 * @package App\AdministraciaModul\Uzivatel\Model
 */
class upravaApp
{

    public function __construct()
    {

        //$this->preulozTabulkuRezervacie()
        //$this->vytvorTabulkuTrener();
        //$this->zapisTrenerov();
        //$this->odstranStlpecUzivatel();
        //$this->upravTabulkuKlientov();
    }




    public function preulozTabulkuRezervacie()
    {
        $kluce = array (RezervaciaManazer::REZERVACIA_ID, RezervaciaManazer::DATUM_VYTVORENIA, 'typ', RezervaciaManazer::DATUM, RezervaciaManazer::CAS_OD, RezervaciaManazer::CAS_DO, RezervaciaManazer::OSOBA_ID, 'trener_id', 'poznamka');

        $dopyt = 'SELECT ' . implode(', ',$kluce) . '
                  FROM rezervacia
                  ORDER BY
                          rezervacia_id, datum, cas_od, cas_do

        ';

        $data = Db::dopytVsetkyRiadky($dopyt, array());

        $data = is_array($data) ? Pole::filtrujKluce($data, $kluce) : $data;
        /*
                foreach ($data as $da)
                {
                    echo "<hr>";
                    print_r($da);

                }
        */
        //die();
        $this->vytvorTabulkuRezervacia();
        $this->vytvorTabulkuPoznamka();
        $this->vytvorTabulkuSkupina();

        $rezervaciaId = 0;

        foreach ($data as $kluc => $da)
        {
            echo "<hr>";
//cele to robit vkladanim postupne do DB
            if($da['typ'] === 'gym-trener')// ak ukladam skupinovy tréning
            {

                if ($da[RezervaciaManazer::OSOBA_ID] === $da['trener_id']) // ulozenie treningu kktory vytvoril trener a teda jedo treningu
                {
                    $datumVytvorenia = $da['datum_vytvorenia'] === NULL ? "0000-00-00 00:00:00" : $da['datum_vytvorenia'];

                    $rezervaciaTab = array(
                        'datum_vytvorenia' => $datumVytvorenia,
                        'datum' => $da['datum'],
                        'cas_od' => $da['cas_od'],
                        'cas_do' => $da['cas_do'],
                        'osoba_id' => $da['osoba_id']);

                    Db::vloz('rezervacia', $rezervaciaTab);
                    //echo ("Trener:");
                    //print_r($rezervaciaTab);
                    $rezervaciaId =Db::vratPosledneId(); //$kluc;

                    if(!empty($da['poznamka'])) // ak je poznamkja tak ju ulozim
                    {
                        $poznamkaTab = array('rezervacia_id' =>  $rezervaciaId,
                            'poznamka' => $da['poznamka']);

                        Db::vloz('poznamka', $poznamkaTab);
                        //echo ("poznamka:");
                        //print_r($poznamkaTab);
                    }
                }
                else // ulozenie ostatnych osob skupiny treningu
                {
                    $skupinaTab = array('rezervacia_id' => $rezervaciaId,
                        'osoba_id' => $da['osoba_id']);
                    Db::vloz('skupina', $skupinaTab);
                    //echo ("skupina:");
                    //print_r($skupinaTab);
                }
            }
            else // ukladanie bezneho treningu
            {
                $datumVytvorenia = $da['datum_vytvorenia'] === NULL ? "0000-00-00 00:00:00" : $da['datum_vytvorenia'];
                $rezervaciaTab = array(
                    'datum_vytvorenia' => $datumVytvorenia,
                    'datum' => $da['datum'],
                    'cas_od' => $da['cas_od'],
                    'cas_do' => $da['cas_do'],
                    'osoba_id' => $da['osoba_id']);
                Db::vloz('rezervacia', $rezervaciaTab);
                //echo ("Sam:");
                //print_r($rezervaciaTab);
            }
        }
        echo "<h1>Hotovo</h1>";
    }




    public function vytvorTabulkuSkupina()
    {
        try {
            //vytvorenie tabulky
            Db::dopyt('CREATE TABLE IF NOT EXISTS skupina (
                                                            skupina_id int(11) NOT NULL,
                                                            rezervacia_id int(11) NOT NULL,
                                                            osoba_id int(11) NOT NULL
                                                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovak_ci');
            //indexy
            Db::dopyt('ALTER TABLE skupina
                                            ADD PRIMARY KEY (skupina_id),
                                            ADD KEY rezervacia_id (rezervacia_id),
                                            ADD KEY osoba_id (osoba_id)
                                            ');

            //autoincrement
            Db::dopyt('ALTER TABLE skupina
                                            MODIFY skupina_id int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1');

            //obmedzenia
            Db::dopyt('ALTER TABLE skupina
                                        ADD CONSTRAINT skupina_ibfk_1 FOREIGN KEY (rezervacia_id) REFERENCES rezervacia (rezervacia_id) ON DELETE CASCADE,
                                        ADD CONSTRAINT skupina_ibfk_2 FOREIGN KEY (osoba_id) REFERENCES osoba (osoba_id)
                                        ');

            echo('Tabulka "skupina" bola vytvorená <br>');
        }
        catch (PDOException)
        {
            echo('Tabulka "skupina" už je vytvorená <br>');
        }
    }

    public function vytvorTabulkuPoznamka()
    {
        try {
            //vytvorenie tabulky
            Db::dopyt('CREATE TABLE IF NOT EXISTS poznamka (
                                                                poznamka_id int(11) NOT NULL,
                                                                rezervacia_id int(11) NOT NULL,
                                                                poznamka varchar(255) COLLATE utf8_slovak_ci DEFAULT NULL
                                                                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovak_ci');
            //indexy
            Db::dopyt('ALTER TABLE poznamka
                                                    ADD PRIMARY KEY (poznamka_id),
                                                    ADD KEY rezervacia_id (rezervacia_id)
                                            ');
            //autoincrement
            Db::dopyt('ALTER TABLE poznamka
                                             MODIFY poznamka_id int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1');

            //obmedzenia
            Db::dopyt('ALTER TABLE poznamka
                                        ADD CONSTRAINT poznamka_ibfk_1 FOREIGN KEY (rezervacia_id) REFERENCES rezervacia (rezervacia_id) ON DELETE CASCADE
                                        ');
            echo('Tabulka "poznamka" bola vytvorená <br>');
        }
        catch (PDOException)
        {
            echo('Tabulka "poznamka" už je vytvorená <br>');
        }
    }

    public function vytvorTabulkuRezervacia()
    {
        try {
            // odstránenie Tabulky
            Db::dopyt('DROP TABLE rezervacia ');

            //vytvorenie tabulky
            Db::dopyt('CREATE TABLE IF NOT EXISTS rezervacia (
                                                            rezervacia_id int(11) NOT NULL,
                                                            datum_vytvorenia datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                                                            datum date NOT NULL,
                                                            cas_od time NOT NULL,
                                                            cas_do time NOT NULL,
                                                            osoba_id int(11) NOT NULL
                                                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovak_ci');
            //indexy
            Db::dopyt('ALTER TABLE rezervacia
                                            ADD PRIMARY KEY (rezervacia_id),
                                            ADD KEY datum (datum),
                                            ADD KEY osoba_id (osoba_id)
                                            ');
            //autoincrement
            Db::dopyt('ALTER TABLE rezervacia
                                            MODIFY rezervacia_id int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1');

            //obmedzenia
            Db::dopyt('ALTER TABLE rezervacia
                                        ADD CONSTRAINT rezervacia_ibfk_1 FOREIGN KEY (osoba_id) REFERENCES osoba (osoba_id)
                                        ');

            echo('Tabulka "rezervacia" bola vytvorená <br>');
        }
        catch (PDOException $ch)
        {
            echo('Tabulka "rezervacia" už je vytvorená <br>') . $ch->getMessage();

        }
    }




























    public function vytvorTabulkuTrener()
    {
        try {
            //vytvorenie tabulky
            Db::dopyt('CREATE TABLE IF NOT EXISTS trener (
                                                                trener_id int(11) NOT NULL,
                                                                osoba_id int(11) NOT NULL,
                                                                prezivka varchar(30) COLLATE utf8_slovak_ci DEFAULT NULL,
                                                                farba char(7) NOT NULL,
                                                                aktivny int(11) DEFAULT NULL
                                                                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovak_ci');
            //indexy
            Db::dopyt('ALTER TABLE trener
                                            ADD PRIMARY KEY (trener_id),
                                            ADD UNIQUE KEY (prezivka),
                                            ADD UNIQUE KEY (farba),
                                            ADD UNIQUE KEY osoba_id (osoba_id)');
            //autoincrement
            Db::dopyt('ALTER TABLE trener
                                            MODIFY trener_id int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1');

            //obmedzenia
            Db::dopyt('ALTER TABLE trener
                                            ADD CONSTRAINT trener_ibfk_1 FOREIGN KEY (osoba_id) REFERENCES osoba (osoba_id)');
            echo('Tabulka "trener" bola vytvorená <br>');
        }
        catch (PDOException)
        {
            echo('Tabulka "trener" už je vytvorená <br>');
        }
    }

    public function zapisTrenerov()
    {
        try {
            Db::vlozVsetko('trener', array(array('osoba_id' => 2, 'prezivka' => 'Bobby Blúú', 'farba' => '#007bff', 'aktivny' => 1),
                array('osoba_id' => 14, 'prezivka' => 'Lea', 'farba' => '#dc3545', 'aktivny' => 1)));
            echo('Hodnoty do tabuľky "trener" boli pridané <br>');
        }
        catch (PDOException)
        {
            echo('Hodnoty do tabuľky "trener" už existujú <br>');
        }

    }
    public function odstranStlpecUzivatel() //
    {
        try {
            Db::dopyt('ALTER TABLE uzivatel DROP COLUMN trener');
            echo('Stĺpec tabuľky "uzivatel" bol odstránený <br>');
        }
        catch (PDOException)
        {
            echo('Stĺpec tabuľky "uzivatel" už bol odstránený <br>');
        }
    }
    public function upravTabulkuKlientov()
    {
        // nacitanie starych dát
        $klienti = Db::dopytVsetkyRiadky('SELECT * FROM klient ORDER BY klient_id');

        // nove dáta
        if(isset($klienti[0]['osoba_trener_id']))
        {
            $klienti = Pole::filtrujKluce($klienti, array('osoba_trener_id', 'osoba_klient_id'));

            foreach ($klienti as $kluc => $klient)
            {
                if($klient['osoba_trener_id'] == 2)
                {
                    $novyKlienti[$kluc]['trener_id'] = 1;
                }
                if($klient['osoba_trener_id'] == 14)
                {
                    $novyKlienti[$kluc]['trener_id'] = 2;
                }
                $novyKlienti[$kluc]['osoba_klient_id'] = $klient['osoba_klient_id'];
            }
            // Vytvorenie novej Tabulky
            try {
                // odstránenie Tabulky
                Db::dopyt('DROP TABLE klient');

                //vytvorenie tabulky
                Db::dopyt('CREATE TABLE IF NOT EXISTS klient (
                                                            klient_id int(11) NOT NULL,
                                                            trener_id int(11) NOT NULL,
                                                            osoba_klient_id int(11) NOT NULL
                                                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovak_ci');
                //indexy
                Db::dopyt('ALTER TABLE klient
                                            ADD PRIMARY KEY (klient_id),
                                            ADD UNIQUE KEY (trener_id, osoba_klient_id)');
                //autoincrement
                Db::dopyt('ALTER TABLE klient
                                            MODIFY klient_id int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1');

                //obmedzenia
                Db::dopyt('ALTER TABLE klient
                                        ADD CONSTRAINT klient_ibfk_1 FOREIGN KEY (trener_id) REFERENCES trener (trener_id),
                                        ADD CONSTRAINT klient_ibfk_2 FOREIGN KEY (osoba_klient_id) REFERENCES osoba (osoba_id)');
                //Vlozěnie nových dát Dát "osoba_trener_id" => "trener_id" napr: Tomáš má - 2 => 1 ... Lea má - 14 => 2

                Db::vlozVsetko('klient', $novyKlienti);

                echo('Tabulka "klient" bola vytvorená <br>');
            }
            catch (PDOException)
            {
                echo('Tabulka "klient" už je vytvorená <br>');
            }
        }
        else
            echo('Tabulka "klient" už bola aktualizovaná <br>');
    }


    /*
        public function vytvorTabulkuKlientRezervacia()
        {
            try {
                //vytvorenie tabulky
                Db::dopyt('CREATE TABLE IF NOT EXISTS klient_rezervacia (
                                                                              klient_rezervacia_id int(11) NOT NULL,
                                                                              rezervacia_id int(11) NOT NULL,
                                                                              klient_osoba_id int(11) NOT NULL
                                                                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovak_ci');
                //indexy
                Db::dopyt('ALTER TABLE klient_rezervacia
                                                            ADD PRIMARY KEY (klient_rezervacia_id),
                                                            ADD KEY rezervacia_id (rezervacia_id),
                                                            ADD KEY klient_osoba_id (klient_osoba_id)');
                //autoincrement
                Db::dopyt('ALTER TABLE klient_rezervacia
                                                MODIFY klient_rezervacia_id int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1');

                //obmedzenia
                Db::dopyt('ALTER TABLE klient_rezervacia
                                                            ADD CONSTRAINT klient_rezervacia_ibfk_1 FOREIGN KEY (rezervacia_id) REFERENCES rezervacia (rezervacia_id),
                                                            ADD CONSTRAINT klient_rezervacia_ibfk_2 FOREIGN KEY (klient_osoba_id) REFERENCES osoba (osoba_id)');
                echo('Tabulka "klient_rezervacia" bola vytvorená <br>');
            }
            catch (PDOException)
            {
                echo('Tabulka "klient_rezervacia" už je vytvorená <br>');
            }
        }

        public function zapisKlientRezervacie()
        {

        }

        public function odstranStlpecRezervacia()
        {

        }
    */

}


/*
 * Niektoré časti sú upravené
 * Autor: MiCHo
 */
