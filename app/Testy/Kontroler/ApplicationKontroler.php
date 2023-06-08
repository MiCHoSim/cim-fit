<?php



namespace App\Testy\Kontroler;





use AmazonAdvertisingApi\Client;

use App\ZakladModul\System\Kontroler\Kontroler;



class ApplicationKontroler extends Kontroler

{

    /**

     * @return void

     * @Action

     */

    public function index()

    {

$clientId = 'amzn1.application-oa2-client.5b72d04b7a17446ca3178cefb5f99ff0';
        $redirectUri = 'https://www.cim-fit.eu/testy';
        $clientSecret = '7ef1b2fdc0e16bd2ace2a62a67037de596792221518f8312c06fdf026096658f';


        if (!$_GET)
        {

            $url = '';

            // set post fields
            $params = [
                'client_id' => $clientId,
                'scope' => 'advertising::campaign_management',
                'response_type' => 'code',
                'redirect_uri' => $redirectUri
            ];

            $url .= "https://eu.account.amazon.com/ap/oa?";
            foreach ($params as $k => $v)
            {
                $url .= "{$k}=".($v)."&";
            }
            $url = rtrim($url, "&");

            echo '<a href="' . $url . '">Presmeruj</a>';
        }
        else
        {
            // set post fields
            $post = [
                'grant_type' => 'authorization_code',
                'code' => $_GET['code'],
                'redirect_uri'   => $redirectUri,
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://api.amazon.co.uk/auth/o2/token');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));

            $response = curl_exec($ch);
            print_r(json_decode($response,true));
        }        


        //RouterController::$subPageControllerArray['title'] = 'Password change'; // pridanie doplnujúceho description k hlavnému

        //RouterController::$subPageControllerArray['description'] = 'Saving a new password, Changing Password'; // pridanie doplnujúceho description k hlavnému



        $this->view = 'index';

    }



}



/*

 * Autor: MiCHo

 */