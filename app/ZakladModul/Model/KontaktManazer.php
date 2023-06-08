<?php

namespace App\ZakladModul\Model;

use App\AdministraciaModul\Uzivatel\Kontroler\RegistraciaKontroler;
use App\AdministraciaModul\Uzivatel\Model\OsobaDetailManazer;
use App\AdministraciaModul\Uzivatel\Model\UzivatelManazer;
use App\ZakladModul\Kontroler\EmailKontroler;
use App\ZakladModul\Kontroler\KontaktKontroler;
use Micho\ChybaUzivatela;
use Micho\Formular\Formular;
use Micho\OdosielacEmailov;
use Nastavenia;

/**
 ** Správca Kontaktu
 * Class KontaktManazer
 */
class KontaktManazer
{
    /**
     ** Zostavý šablonu správy pre email
     * @param array $emailData Data prijaté z kontaktného formuláru
     */
    public function odosliKontaktnyEmail($emailData)
    {
        $kontaktKontroler = new KontaktKontroler();
        $kontaktKontroler->sablonaKontaktEmail($emailData);

        // Šablona rozložena emailu a taktiez Štýly
        $emailKontroler = new EmailKontroler();
        $emailKontroler->index($kontaktKontroler);

        ob_start();
        $emailKontroler->vypisPohlad();
        $sprava = ob_get_contents();
        ob_end_clean();

        $odosielacEmailov = new OdosielacEmailov();
        $odosielacEmailov->odosli(Nastavenia::$email, 'Email z webu: ' . Nastavenia::$domenaNazov, $sprava, $emailData['kontakt_email']);
    }

    /**
     ** Nacitá hodnoty pre kontaktné informácie
     * @return array Pole hodnôt
     */
    public function nacitajInfoKontakt()
    {
        $osobaManazer = new UzivatelManazer();
        $kontakty = $osobaManazer->vratAdminov(array(UzivatelManazer::UZIVATEL_ID, OsobaDetailManazer::TEL, OsobaDetailManazer::EMAIL));

        $slovnik = array('Technická podpora','Hlavný Tréner', 'Kontakt');

        if(UzivatelManazer::$uzivatel)
        {
            foreach ($kontakty as $kluc => $kontakt) // vytvori asociativne pole z pridelenou funkcion osobe
            {
                $kontaktyNove[$slovnik[$kontakt[UzivatelManazer::UZIVATEL_ID] - 1]] = $kontakt;
            }
            $kontaktyNove = array_reverse($kontaktyNove);

        }
        return isset($kontaktyNove) ? $kontaktyNove : array();
    }

    /**
     ** Odošle skupinový email
     * @param array $adresat Pole adresátov
     * @param string $predmet Predmet Správy
     * @param string $sprava Správa
     * @throws ChybaUzivatela
     * @throws \ReflectionException
     */
    public function odosliSkupinovyEmail(array $adresat, $predmet, $sprava)
    {
        // Získanie obsahu emailu zo šablony kontroleru
        $kontakKontoler = new KontaktKontroler();
        $kontakKontoler->sablonaSkupinovyEmail($sprava);

        // Šablona rozložena emailu a taktiez Štýly
        $emailKontroler = new EmailKontroler();
        $emailKontroler->index($kontakKontoler);

        ob_start();
        $emailKontroler->vypisPohlad();
        $sprava = ob_get_contents();
        ob_end_clean();

        $odosielacEmailov = new OdosielacEmailov();

        // odosielanie emailu
        foreach ($adresat as $email)
        {
            $odosielacEmailov->odosli($email, Nastavenia::$domenaNazov . ': ' . $predmet, $sprava, Nastavenia::$email);
        }
    }
}
/*
 * Autor: MiCHo
 */
