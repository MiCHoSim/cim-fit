<?php

// require_once '../vendor/autoload.php';

/**
 ** Autoloader Automatické načítanie tried
 * @param string $trieda Plný názov triedy, vrátane menného priestoru
 * @throws Exception Ak sa trieda nenájde
 */
function autoloader($trieda)
{
    if (mb_strpos($trieda, '\\') === FALSE && preg_match('/Pomocne$/', $trieda)) // nacita pomocne triedy -> není v namespace a končí na Pomocnik
            $trieda = 'app\\pomocnik\\' . $trieda;
    elseif (mb_strpos($trieda, 'App\\') !== FALSE) // načíta triedy z app
            $trieda = 'a' . ltrim ($trieda, 'A'); // zmení App na app
    else // načíta ostatné triedy z vendor
        $trieda = 'vendor\\' . $trieda;

    $cesta = str_replace('\\', '/', $trieda) . '.php'; // Nahrada spätného lomítka a pridanie koncovky k triede
    
    //echo $cesta . "<br/>";

    if (file_exists('../' . $cesta)) // nacitanie popripade vyvolanie vynimky
        include('../' . $cesta);
}

spl_autoload_register("autoloader");

/*
 * Tento kód spadá pod licenci ITnetwork Premium - http://www.itnetwork.cz/licence
 * Je určen pouze pro osobní užití a nesmí být šířen ani využíván v open-source projektech.
 */

/*
 * Niektoré časti sú upravené
 * Autor: MiCHo
 */
