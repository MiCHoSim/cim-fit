<?php

namespace Micho;

use PDO;
use Micho\Utility\Pole;

/**
 ** Wraper pre lahšiu prácu s databázou s použitim PDO a automatyckým zabezpečením parametrov (premenných) v dopytoch
 * Class Db
 * @package Micho
 */
class Db 
{
    /**
     * @var PDO Databazové spojenie 
     */
    public static $spojenie;
    
    /**
     * @var array Zakladné nastavenie ovladača
     */
    public static $nastavenie = array(
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
    PDO::ATTR_EMULATE_PREPARES => false,);  // nastavenie pre databazu

    /**
     ** Pripojenie sa k databázi pomocou zadaých údajov
     * @param string $hostitel Hostiteľ
     * @param string $uzivatel Uživateľské meno
     * @param string $heslo Heslo
     * @param string $databaza Názov databázi
     */
    public static function pripoj($hostitel, $uzivatel, $heslo, $databaza)
    {
        if (!isset(self::$spojenie))    //zistenie ci existuje spojenie
        {
            self::$spojenie = @new PDO("mysql:host=$hostitel;dbname=$databaza", $uzivatel, $heslo, self::$nastavenie); //vytvorenie spojenia k databaze
        }
    }
    
    /**
     ** Spusti dopyt a vrati z neho prvý riadok
     * @param string $dopyt Dopyt sql
     * @param array $parametre Parametre k dopytu
     * @return mixed Pole výsledkov alebo FALSE
     */
    public static function dopytJedenRiadok($dopyt, $parametre = array())
    {
        $hodnota = self::$spojenie->prepare($dopyt); // Hodnota a Dopyt sa predajú oddelene Kvôli => "SQL injection"
        $hodnota->execute($parametre);
        return $hodnota->fetch();
    }
    
    /**
     ** Spustí dopyt a vrati z neho všetky riadky
     * @param string $dopyt Dopyt sql
     * @param array $parametre Parametre k dopyu
     * @return mixed Pole výsledkov alebo FALSE
     */
    public static function dopytVsetkyRiadky($dopyt, $parametre = array())
    {
        $hodnota = self::$spojenie->prepare($dopyt); // Hodnota a Dopyt sa predajú oddelene Kvôli => "SQL injection"
        $hodnota->execute($parametre);
        return $hodnota->fetchAll();
    }
    
    /**
     ** Spustí dopyt a vrati z neho prvý stlpce prvého riadku
     * @param string $dopyt Dopyt sql
     * @param array $parametre Parametre k dopyt
     * @return mixed Prvá hodnota výsledkov alebo FALSE
     */
    public static function dopytSamotny($dopyt, $parametre = array())
    {
        $vysledok = self::dopytJedenRiadok($dopyt, $parametre);
        return isset($vysledok[0]) ? $vysledok[0] : false;
    }
    
    /**
     ** Spusti dopyt a vráti počet ovplivnených riadkov
     * @param string $dopyt Dopyt sql
     * @param array $parametre Parametre k dopytu
     * @return int Počet ovplyvnených riadkov
     */
    public static function dopyt($dopyt, $parametre = array())
    {
        $hodnota = self::$spojenie->prepare($dopyt); // Hodnota a Dopyt sa predajú oddelene Kvôli => "SQL injection"
        $hodnota->execute($parametre);
        return $hodnota->rowCount();
    }
    
    /**
     ** Vloží do tabulky nový riadok ako data za asociativného pola
     * @param string $tabulka Názov Tabuľky
     * @param array $parametre Asociativne pole z datmi
     * @return int Počet ovplyvnených riadkov
     */        
    public static function vloz($tabulka, $parametre = array()) //vlozi clanky do databaze
    {
        $dopyt = 'INSERT INTO ' . $tabulka;
        if(isset($parametre[0]) && is_array($parametre[0])) // ak je prichadzajui parameter pole ulozim vŠetky riadky
        {
            $dopyt .= ' (' . implode(', ', array_keys($parametre[0])) . ')';
            $dopyt .= ' VALUES ';
            $param = '';
            $poslednaPolozka = count($parametre);
            foreach ($parametre as $parameter)
            {
                $dopyt .= '(' . str_repeat('?, ', sizeOf($parameter)-1) . '?)';
                if(0 !== --$poslednaPolozka)
                {
                    $dopyt .= ', ';
                }

                $param .= implode(',', $parameter) . ',';
            }
            $parametre = explode(',', $param);
            array_pop($parametre);
        }
        else
        {
            $dopyt .= ' (' . implode(', ', array_keys($parametre)) . ')';
            $dopyt .= ' VALUES (' . str_repeat('?, ', sizeOf($parametre)-1) . '?)';
        }
        $parametre = array_values($parametre);
        return self::dopyt($dopyt, $parametre);
    }
    
    /**
     ** Zmeni riadok v tabulke tak, aby obsahoval data z asociativného poľa
     * @param string $tabulka Názov tabuľky
     * @param array $hodnoty Asociatívne pole z dátami
     * @param string $podmienka Časť SQL dopytu s podmienkov vrátane WHERE
     * @param array $parametre Parametre dopytu
     * @return int Počet ovplyvnených riadkov
     */
    public static function zmen($tabulka, $hodnoty = array(), $podmienka, $parametre = array()) //upravi clanok v databazi
    {
        return self::dopyt("UPDATE `$tabulka` SET `".
                implode('` = ?, `', array_keys($hodnoty)).
                "` = ? " . $podmienka,
                array_merge(array_values($hodnoty), $parametre));
    }
    
    /**
     * @return string Vracia ID posledného vloženého záznamu
     */
    public static function vratPosledneId()
    {
        return self::$spojenie->lastInsertId(); //vrati id posledneho vloženého zaznamu
    } 
    
    /**
     ** Vytvori páry z dopyty
     * @param string $dopyt Dopyt na dB
     * @param string $klucStlpec  Klúč riadku, ktorý bude kľúčom výstupného poľa
     * @param string $hodnotaStlpec hodnoty
     * @param array $parametre Klúč riadku, ktorý bude hodnotou výstupného poľa
     * @return array pary
     */
    public static function dopytPary($dopyt, $klucStlpec, $hodnotaStlpec, $parametre = array())
    {
        return Pole::ziskajPary(self::dopytVsetkyRiadky($dopyt, $parametre), $klucStlpec, $hodnotaStlpec);
    }
    
    /**
     ** Uloženie viacerých riadkov do DB súčastne
     * @param string $tabulka nazov tabuľky
     * @param array $parametre paramatre v poli
     * @return int Počet ovplyvnených riadkov
     */
    public static function vlozVsetko($tabulka, $parametre = array())
    {
        $parameter = array();
        $dopyt = rtrim("INSERT INTO `$tabulka` (`".
        implode('`, `', array_keys($parametre[0])).
        "`) VALUES " . str_repeat('(' . str_repeat('?,', sizeOf($parametre[0])-1)."?), ", sizeOf($parametre)), ', ');
        
        foreach ($parametre as $riadky)
        {
            $parameter = array_merge($parameter, array_values($riadky));
        }
        return self::dopyt($dopyt, $parameter);
    }
    
    /**
     ** Začne transakciu
     */
    public static function zacatTranzakciu()
    {
        self::$spojenie->beginTransaction();
    }
    
    /**
     ** Dokončí transakciu
     */
    public static function dokonciTransakciu()
    {
        self::$spojenie->commit();
    }
    
    /**
    ** Stornuje transakciu
    */
    public static function vratSpat()
    {
        self::$spojenie->rollBack();
    }
}
/* Autor: http://www.itnetwork.cz */

/*
 * Niektoré časti sú upravené
 * Autor: MiCHo
 */