<?php

    use App\ZakladModul\System\Kontroler\SmerovacKontroler;
    use Micho\Db;

//header("Location: https://cim-fit.metalo.sk");
//die();


    ini_set('session.cookie_httponly', 1);      // Ochrana proti ukradnutiu PHPSESSID => "XSS"

    session_start();

ini_set("display_errors", 1);		// nastavenie pre zobrazovanie chýb
error_reporting(E_ERROR | E_WARNING);
//ini_set('max_file_uploads', '100');	// nastavenie maximalny pocet uloadu
//ini_set('memory_limit', '128M');


header("X-Frame-Options: DENY"); // ochrana proti => "Clickjacking"

mb_internal_encoding("UTF-8"); // Nastavenie kodovania pre prácu z reťazcami

    require_once('../konfiguracia/Nastavenia.php'); // načitanie konfiguracie stránky

    require('autoloader.php'); // Registrovanie autoloaderu

    Db::pripoj(Nastavenia::$db['host'], Nastavenia::$db['user'], Nastavenia::$db['password'], Nastavenia::$db['database']); // pripojenie k databáze
    Db::dopyt('SET sql_mode=(SELECT REPLACE(@@sql_mode,"ONLY_FULL_GROUP_BY",""))'); // Zrusenie nastavenia ONLY_FULL_GROUP_BY ... vypisovalo to chybu ked som robil Group BY Z viecerých JOIN tabuliek


//$_SESSION['naseptavac'] = ProduktPomocne::vratTitulokProduktov(); // načíta z DB našeptávač

    $smerovac = new SmerovacKontroler(); // vytvorenie rutra
    $smerovac->index(array($_SERVER['REQUEST_URI'])); // Spracovanie parametrov z url a následne spracovanie celej App
    $smerovac->vypisPohlad(); // vypisanie Hlavnej šablony rozloženia Layout

/*
 * Tento kód spadá pod licenci ITnetwork Premium - http://www.itnetwork.cz/licence
 * Je určen pouze pro osobní užití a nesmí být šířen ani využíván v open-source projektech.
 */

/*
 * Niektoré časti sú upravené
 * Autor: MiCHo
 */
