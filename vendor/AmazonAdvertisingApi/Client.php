<?php
namespace AmazonAdvertisingApi;

require_once "Versions.php";
require_once "Regions.php";
require_once "CurlRequest.php";

class Client
{
    private $config = array(
        "clientId" => null,
        "clientSecret" => null,
        "region" => null,
        "accessToken" => null,
        "refreshToken" => null,
        "sandbox" => false);

    private $apiVersion = null;
    private $applicationVersion = null;
    private $userAgent = null;
    private $endpoint = null;
    private $tokenUrl = null;
    private $requestId = null;
    private $endpoints = null;
    private $versionStrings = null;

    public $profileId = null;

    public function __construct($config)
    {
        $regions = new Regions();
        $this->endpoints = $regions->endpoints;

        $versions = new Versions();
        $this->versionStrings = $versions->versionStrings;

        $this->apiVersion = $this->versionStrings["apiVersion"];
        $this->applicationVersion = $this->versionStrings["applicationVersion"];
        $this->userAgent = "AdvertisingAPI PHP Client Library v{$this->applicationVersion}";

        $this->_validateConfig($config);
        $this->_validateConfigParameters();
        $this->_setEndpoints();

        if (is_null($this->config["accessToken"]) && !is_null($this->config["refreshToken"])) {
            /* convenience */
            $this->doRefreshToken();
        }
    }

    /**
     ** Obnoviť prístupový token
     * @return array
     * @throws \Exception
     */
    public function doRefreshToken()
    {
        $headers = array(
            "Content-Type: application/x-www-form-urlencoded;charset=UTF-8",
            "User-Agent: {$this->userAgent}"
        );

        $refresh_token = rawurldecode($this->config["refreshToken"]);

        $params = array(
            "grant_type" => "refresh_token",
            "refresh_token" => $refresh_token,
            "client_id" => $this->config["clientId"],
            "client_secret" => $this->config["clientSecret"]);

        $data = "";
        foreach ($params as $k => $v) {
            $data .= "{$k}=".rawurlencode($v)."&";
        }

        $url = "https://{$this->tokenUrl}";
        $request = new CurlRequest();
        $request->setOption(CURLOPT_URL, $url);
        $request->setOption(CURLOPT_HTTPHEADER, $headers);
        $request->setOption(CURLOPT_USERAGENT, $this->userAgent);
        $request->setOption(CURLOPT_POST, true);
        $request->setOption(CURLOPT_POSTFIELDS, rtrim($data, "&"));

        $response = $this->_executeRequest($request);

        $response_array = json_decode($response["response"], true);
        if (array_key_exists("access_token", $response_array)) {
            $this->config["accessToken"] = $response_array["access_token"];
        } else {
            $this->_logAndThrow("Unable to refresh token. 'access_token' not found in response. ". print_r($response, true));
        }

        return $response;
    }

    /**
     ** Získajte zoznam profilov
     * @return array
     */
    public function listProfiles()
    {
        return $this->_operation("profiles");
    }

    public function registerProfile($data)
    {
        return $this->_operation("profiles/register", $data, "PUT");
    }

    public function registerProfileStatus($profileId)
    {
        return $this->_operation("profiles/register/{$profileId}/status");
    }

    /**
     ** Načíta jeden profil podľa ID.
     * @param $profileId
     * @return array
     */
    public function getProfile($profileId)
    {
        return $this->_operation("profiles/{$profileId}");
    }

    /**
     ** Aktualizuje jeden alebo viac profilov.
     * Inzerenti sú identifikovaní pomocou ich profileIds
     * @param array $data
     * @return array
     */
    public function updateProfiles(array $data)
    {
        return $this->_operation("profiles", $data, "PUT");
    }

    /**
     ** Načíta kampaň podľa ID.
     * Upozorňujeme, že toto volanie vráti minimálnu množinu polí kampane,
     * ale je efektívnejšie ako getCampaignEx
     * @param string $campaignId
     * @return array
     */
    public function getCampaign(string $campaignId)
    {
        return $this->_operation("campaigns/{$campaignId}");
    }

    public function getCampaignEx($campaignId)
    {
        return $this->_operation("campaigns/extended/{$campaignId}");
    }

    /**
     ** Vytvorí jednu alebo viac kampaní.
     * Úspešne vytvoreným kampaniam budú priradené jedinečné campaignIds.
     * @param array $data
     * @return array
     */
    public function createCampaigns(array $data)
    {
        return $this->_operation("campaigns", $data, "POST");
    }

    /**
     ** Aktualizuje jednu alebo viacero kampaní.
     * Kampane sú identifikované pomocou ich campaignIds
     * @param array $data
     * @return array
     */
    public function updateCampaigns(array $data)
    {
        return $this->_operation("campaigns", $data, "PUT");
    }

    /**
     ** Nastaví stav kampane na archivovaný.
     * Rovnakú operáciu možno vykonať prostredníctvom aktualizácie,
     * ale pre úplnosť je zahrnutá.
     * @param string $campaignId
     * @return array
     */
    public function archiveCampaign(string $campaignId)
    {
        return $this->_operation("campaigns/{$campaignId}", null, "DELETE");
    }

    /**
     ** Načíta zoznam kampaní,
     * ktoré spĺňajú voliteľné kritériá.
     * @param array|null $data
     * @return array
     */
    public function listCampaigns(array $data = null)
    {
        return $this->_operation("campaigns", $data);
    }

    public function listCampaignsEx($data = null)
    {
        return $this->_operation("campaigns/extended", $data);
    }

    /**
     ** Načíta reklamnú skupinu podľa ID.
     * Upozorňujeme, že toto volanie vráti minimálnu množinu polí reklamnej skupiny,
     * ale je efektívnejšie ako getAdGroupEx.
     * @param string $adGroupId
     * @return array
     */
    public function getAdGroup(string $adGroupId)
    {
        return $this->_operation("adGroups/{$adGroupId}");
    }

    public function getAdGroupEx($adGroupId)
    {
        return $this->_operation("adGroups/extended/{$adGroupId}");
    }

    /**
     ** Vytvorí jednu alebo viacero reklamných skupín.
     * Úspešne vytvoreným reklamným skupinám budú priradené jedinečné adGroupIds.
     * @param array $data
     * @return array
     */
    public function createAdGroups(array $data)
    {
        return $this->_operation("adGroups", $data, "POST");
    }

    /**
     ** Aktualizuje jednu alebo viacero reklamných skupín.
     * Reklamné skupiny sú identifikované pomocou ich adGroupIds
     * @param array $data
     * @return array
     */
    public function updateAdGroups(array $data)
    {
        return $this->_operation("adGroups", $data, "PUT");
    }

    /**
     ** Nastaví stav reklamnej skupiny na archivovanú.
     * Rovnakú operáciu možno vykonať prostredníctvom aktualizácie,
     * ale pre úplnosť je zahrnutá.
     * @param string $adGroupId
     * @return array
     */
    public function archiveAdGroup(string $adGroupId)
    {
        return $this->_operation("adGroups/{$adGroupId}", null, "DELETE");
    }

    /**
     ** Načíta zoznam reklamných skupín,
     * ktoré spĺňajú voliteľné kritériá.
     * @param array|null $data
     * @return array
     */
    public function listAdGroups(array $data = null)
    {
        return $this->_operation("adGroups", $data);
    }

    public function listAdGroupsEx($data = null)
    {
        return $this->_operation("adGroups/extended", $data);
    }

    /**
     ** Načíta kľúčové slovo podľa ID.
     * Upozorňujeme, že toto volanie vráti minimálnu množinu polí kľúčových slov,
     * ale je efektívnejšie ako getBiddableKeywordEx.
     * @param string $keywordId
     * @return array
     */
    public function getBiddableKeyword(string $keywordId)
    {
        return $this->_operation("keywords/{$keywordId}");
    }

    public function getBiddableKeywordEx($keywordId)
    {
        return $this->_operation("keywords/extended/{$keywordId}");
    }

    /**
     ** Vytvorí jedno alebo viac kľúčových slov.
     * Úspešne vytvoreným kľúčovým slovám budú priradené jedinečné keywordIds.
     * @param array $data
     * @return array
     */
    public function createBiddableKeywords(array $data)
    {
        return $this->_operation("keywords", $data, "POST");
    }

    /**
     ** Aktualizuje jedno alebo viac kľúčových slov.
     * Kľúčové slová sa identifikujú pomocou ich keywordIds.
     * @param array $data
     * @return array
     */
    public function updateBiddableKeywords(array $data)
    {
        return $this->_operation("keywords", $data, "PUT");
    }

    /**
     ** Nastaví stav kľúčového slova na archivované.
     * Rovnakú operáciu možno vykonať prostredníctvom aktualizácie,
     * ale pre úplnosť je zahrnutá.
     * @param string $keywordId
     * @return array
     */
    public function archiveBiddableKeyword(string $keywordId)
    {
        return $this->_operation("keywords/{$keywordId}", null, "DELETE");
    }

    /**
     ** Načíta zoznam kľúčových slov,
     * ktoré spĺňajú voliteľné kritériá.
     * @param array|null $data
     * @return array
     */
    public function listBiddableKeywords(array $data = null)
    {
        return $this->_operation("keywords", $data);
    }

    public function listBiddableKeywordsEx($data = null)
    {
        return $this->_operation("keywords/extended", $data);
    }

    /**
     ** Načíta vylučujúce kľúčové slovo podľa ID.
     * Upozorňujeme, že toto volanie vráti minimálnu množinu polí kľúčových slov,
     * ale je efektívnejšie ako getNegativeKeywordEx.
     * @param string $keywordId
     * @return array
     */
    public function getNegativeKeyword(string $keywordId)
    {
        return $this->_operation("negativeKeywords/{$keywordId}");
    }

    public function getNegativeKeywordEx($keywordId)
    {
        return $this->_operation("negativeKeywords/extended/{$keywordId}");
    }

    /**
     ** Vytvorí jedno alebo viacero vylučujúcich kľúčových slov.
     * Úspešne vytvoreným kľúčovým slovám budú priradené jedinečné identifikátory kľúčových slov.
     * @param array $data
     * @return array
     */
    public function createNegativeKeywords(array $data)
    {
        return $this->_operation("negativeKeywords", $data, "POST");
    }

    /**
     ** Aktualizuje jedno alebo viacero vylučujúcich kľúčových slov.
     * Kľúčové slová sa identifikujú pomocou ich keywordIds.
     * @param array $data
     * @return array
     */
    public function updateNegativeKeywords(array $data)
    {
        return $this->_operation("negativeKeywords", $data, "PUT");
    }

    /**
     ** Nastaví stav vylučujúceho kľúčového slova na archivované.
     * Tá istá operácia môže byť vykonaná prostredníctvom aktualizácie stavu,
     * ale je zahrnutá pre úplnosť.
     * @param string $keywordId
     * @return array
     */
    public function archiveNegativeKeyword(string $keywordId)
    {
        return $this->_operation("negativeKeywords/{$keywordId}", null, "DELETE");
    }

    /**
     ** Načíta zoznam vylučujúcich kľúčových slov,
     * ktoré spĺňajú voliteľné kritériá.
     * @param array|null $data
     * @return array
     */
    public function listNegativeKeywords(array $data = null)
    {
        return $this->_operation("negativeKeywords", $data);
    }

    public function listNegativeKeywordsEx($data = null)
    {
        return $this->_operation("negativeKeywords/extended", $data);
    }

    public function getCampaignNegativeKeyword($keywordId)
    {
        return $this->_operation("campaignNegativeKeywords/{$keywordId}");
    }

    /**
     ** Načíta vylučujúce kľúčové slovo kampane podľa ID.
     * Upozorňujeme, že toto volanie vráti minimálnu množinu polí kľúčových slov,
     * ale je efektívnejšie ako getCampaignNegativeKeywordEx.
     * @param string $keywordId
     * @return array
     */
    public function getCampaignNegativeKeywordEx(string $keywordId)
    {
        return $this->_operation("campaignNegativeKeywords/extended/{$keywordId}");
    }

    /**
     ** Vytvorí jedno alebo viacero vylučujúcich kľúčových slov kampane.
     * Úspešne vytvoreným kľúčovým slovám budú priradené jedinečné keywordIds.
     * @param array $data
     * @return array
     */
    public function createCampaignNegativeKeywords(array $data)
    {
        return $this->_operation("campaignNegativeKeywords", $data, "POST");
    }

    /**
     ** Aktualizuje jedno alebo viacero vylučujúcich kľúčových slov kampane.
     * Kľúčové slová sa identifikujú pomocou ich keywordIds.
     * Vylučujúce kľúčové slová kampane je možné v súčasnosti iba odstrániť.
     * @param array $data
     * @return array
     */
    public function updateCampaignNegativeKeywords(array $data)
    {
        return $this->_operation("campaignNegativeKeywords", $data, "PUT");
    }

    /**
     ** Nastaví stav vylučujúceho kľúčového slova kampane na odstránené.
     * Tá istá operácia môže byť vykonaná prostredníctvom aktualizácie stavu,
     * ale je zahrnutá pre úplnosť.
     * @param string $keywordId
     * @return array
     */
    public function removeCampaignNegativeKeyword(string $keywordId)
    {
        return $this->_operation("campaignNegativeKeywords/{$keywordId}", null, "DELETE");
    }

    /**
     ** Načíta zoznam vylučujúcich kľúčových slov kampane,
     * ktoré spĺňajú voliteľné kritériá.
     * @param array|null $data
     * @return array
     */
    public function listCampaignNegativeKeywords(array $data = null)
    {
        return $this->_operation("campaignNegativeKeywords", $data);
    }

    public function listCampaignNegativeKeywordsEx($data = null)
    {
        return $this->_operation("campaignNegativeKeywords/extended", $data);
    }

    /**
     ** Načíta reklamu na produkt podľa ID.
     * Upozorňujeme, že toto volanie vráti minimálnu množinu polí reklamy na produkt,
     * ale je efektívnejšie ako getProductAdEx.
     * @param string $productAdId
     * @return array
     */
    public function getProductAd($productAdId)
    {
        return $this->_operation("productAds/{$productAdId}");
    }

    public function getProductAdEx($productAdId)
    {
        return $this->_operation("productAds/extended/{$productAdId}");
    }

    /**
     ** Vytvorí jednu alebo viacero reklám na produkty.
     * Úspešne vytvoreným produktovým reklamám budú priradené jedinečné adIds.
     * @param array $data
     * @return array
     */
    public function createProductAds(array $data)
    {
        return $this->_operation("productAds", $data, "POST");
    }

    /**
     ** Aktualizuje jednu alebo viacero reklám na produkty.
     * Produktové reklamy sú identifikované pomocou ich adIds.
     * @param array $data
     * @return array
     */
    public function updateProductAds(array $data)
    {
        return $this->_operation("productAds", $data, "PUT");
    }

    /**
     ** Nastaví stav reklamy na produkt na archivovaný.
     * Rovnakú operáciu možno vykonať prostredníctvom aktualizácie,
     * ale pre úplnosť je zahrnutá.
     * @param string $productAdId
     * @return array
     */
    public function archiveProductAd(string $productAdId)
    {
        return $this->_operation("productAds/{$productAdId}", null, "DELETE");
    }

    /**
     ** Načíta zoznam reklám na produkty,
     * ktoré spĺňajú voliteľné kritériá.
     * @param array|null $data
     * @return array
     */
    public function listProductAds(array $data = null)
    {
        return $this->_operation("productAds", $data);
    }

    public function listProductAdsEx($data = null)
    {
        return $this->_operation("productAds/extended", $data);
    }

    /**
     ** Vyžiadajte si odporúčania cenovej ponuky pre zadanú reklamnú skupinu.
     * @param string $adGroupId
     * @return array
     */
    public function getAdGroupBidRecommendations(string $adGroupId)
    {
        return $this->_operation("adGroups/{$adGroupId}/bidRecommendations");
    }

    /**
     ** Vyžiadajte si odporúčania cenovej ponuky pre zadané kľúčové slovo.
     * @param string $keywordId
     * @return array
     */
    public function getKeywordBidRecommendations(string $keywordId)
    {
        return $this->_operation("keywords/{$keywordId}/bidRecommendations");
    }

    /**
     ** Vyžiadajte si odporúčania cenovej ponuky pre zoznam až 100 kľúčových slov.
     * @param string $adGroupId
     * @param array $data
     * @return array
     */
    public function bulkGetKeywordBidRecommendations(string $adGroupId, array $data)
    {
        $data = array(
            "adGroupId" => $adGroupId,
            "keywords" => $data);
        return $this->_operation("keywords/bidRecommendations", $data, "POST");
    }

    /**
     ** Vyžiadajte si návrhy kľúčových slov pre zadanú reklamnú skupinu.
     * @param array $data
     * @return array
     */
    public function getAdGroupKeywordSuggestions(array $data)
    {
        $adGroupId = $data["adGroupId"];
        unset($data["adGroupId"]);
        return $this->_operation("adGroups/{$adGroupId}/suggested/keywords", $data);
    }

    /**
     ** Vyžiadajte si návrhy kľúčových slov pre špecifikovanú reklamnú skupinu, rozšírenú verziu.
     * Pridáva možnosť vrátiť odporúčanie cenovej ponuky pre vrátené kľúčové slová.
     * @param array $data
     * @return array
     */
    public function getAdGroupKeywordSuggestionsEx(array $data)
    {
        $adGroupId = $data["adGroupId"];
        unset($data["adGroupId"]);
        return $this->_operation("adGroups/{$adGroupId}/suggested/keywords/extended", $data);
    }

    /**
     ** Vyžiadajte si návrhy kľúčových slov pre zadaný asin.
     * @param array $data
     * @return array
     */
    public function getAsinKeywordSuggestions(array $data)
    {
        $asin = $data["asin"];
        unset($data["asin"]);
        return $this->_operation("asins/{$asin}/suggested/keywords", $data);
    }

    /**
     ** Vyžiadajte si návrhy kľúčových slov pre zoznam asin.
     * @param array $data
     * @return array
     */
    public function bulkGetAsinKeywordSuggestions(array $data)
    {
        return $this->_operation("asins/suggested/keywords", $data, "POST");
    }

    /**
     ** Vyžiadajte si prehľad stavu pre všetky entity jedného typu.
     * @param string $recordType
     * @param array|null $data
     * @return array
     */
    public function requestSnapshot(string $recordType, array $data = null)
    {
        return $this->_operation("{$recordType}/snapshot", $data, "POST");
    }

    /**
     ** Získajte predtým požadovaný prehľad.
     * @param string $snapshotId
     * @return array
     */
    public function getSnapshot(string $snapshotId)
    {
        $req = $this->_operation("snapshots/{$snapshotId}");
        if ($req["success"]) {
            $json = json_decode($req["response"], true);
            if ($json["status"] == "SUCCESS") {
                return $this->_download($json["location"]);
            }
        }
        return $req;
    }

    /**
     ** Požiadajte o prispôsobený prehľad výkonnosti pre všetky entity jedného typu,
     * ktoré majú údaje o výkonnosti na vykazovanie.
     * @param string $recordType
     * @param array|null $data
     * @return array
     */
    public function requestReport(string $recordType, array $data = null)
    {
        return $this->_operation("{$recordType}/report", $data, "POST");
    }

    /**
     ** Získajte predtým požadovaný prehľad.
     * @param string $reportId
     * @return array
     */
    public function getReport(string $reportId)
    {
        $req = $this->_operation("reports/{$reportId}");
        if ($req["success"]) {
            $json = json_decode($req["response"], true);
            if ($json["status"] == "SUCCESS") {
                return $this->_download($json["location"]);
            }
        }
        return $req;
    }

    private function _download($location, $gunzip = false)
    {
        $headers = array();

        if (!$gunzip) {
            /* only send authorization header when not downloading actual file */
            array_push($headers, "Authorization: bearer {$this->config["accessToken"]}");
        }

        if (!is_null($this->profileId)) {
            array_push($headers, "Amazon-Advertising-API-Scope: {$this->profileId}");
        }

        $request = new CurlRequest();
        $request->setOption(CURLOPT_URL, $location);
        $request->setOption(CURLOPT_HTTPHEADER, $headers);
        $request->setOption(CURLOPT_USERAGENT, $this->userAgent);

        if ($gunzip) {
            $response = $this->_executeRequest($request);
            $response["response"] = gzdecode($response["response"]);
            return $response;
        }

        return $this->_executeRequest($request);
    }

    private function _operation($interface, $params = array(), $method = "GET")
    {
        $headers = array(
            "Authorization: bearer {$this->config["accessToken"]}",
            "Content-Type: application/json",
            "User-Agent: {$this->userAgent}"
        );

        if (!is_null($this->profileId)) {
            array_push($headers, "Amazon-Advertising-API-Scope: {$this->profileId}");
        }

        $request = new CurlRequest();
        $url = "{$this->endpoint}/{$interface}";
        $this->requestId = null;
        $data = "";

        switch (strtolower($method)) {
            case "get":
                if (!empty($params)) {
                    $url .= "?";
                    foreach ($params as $k => $v) {
                        $url .= "{$k}=".rawurlencode($v)."&";
                    }
                    $url = rtrim($url, "&");
                }
                break;
            case "put":
            case "post":
            case "delete":
                if (!empty($params)) {
                    $data = json_encode($params);
                    $request->setOption(CURLOPT_POST, true);
                    $request->setOption(CURLOPT_POSTFIELDS, $data);
                }
                break;
            default:
                $this->_logAndThrow("Unknown verb {$method}.");
        }

        $request->setOption(CURLOPT_URL, $url);
        $request->setOption(CURLOPT_HTTPHEADER, $headers);
        $request->setOption(CURLOPT_USERAGENT, $this->userAgent);
        $request->setOption(CURLOPT_CUSTOMREQUEST, strtoupper($method));
        return $this->_executeRequest($request);
    }

    protected function _executeRequest($request)
    {
        $response = $request->execute();
        $this->requestId = $request->requestId;
        $response_info = $request->getInfo();
        $request->close();

        if ($response_info["http_code"] == 307) {
            /* application/octet-stream */
            return $this->_download($response_info["redirect_url"], true);
        }

        if (!preg_match("/^(2|3)\d{2}$/", $response_info["http_code"])) {
            $requestId = 0;
            $json = json_decode($response, true);
            if (!is_null($json)) {
                if (array_key_exists("requestId", $json)) {
                    $requestId = json_decode($response, true)["requestId"];
                }
            }
            return array("success" => false,
                    "code" => $response_info["http_code"],
                    "response" => $response,
                    "requestId" => $requestId);
        } else {
            return array("success" => true,
                    "code" => $response_info["http_code"],
                    "response" => $response,
                    "requestId" => $this->requestId);
        }
    }

    private function _validateConfig($config)
    {
        if (is_null($config)) {
            $this->_logAndThrow("'config' cannot be null.");
        }

        foreach ($config as $k => $v) {
            if (array_key_exists($k, $this->config)) {
                $this->config[$k] = $v;
            } else {
                $this->_logAndThrow("Unknown parameter '{$k}' in config.");
            }
        }
        return true;
    }

    private function _validateConfigParameters()
    {
        foreach ($this->config as $k => $v) {
            if (is_null($v) && $k !== "accessToken" && $k !== "refreshToken") {
                $this->_logAndThrow("Missing required parameter '{$k}'.");
            }
            switch ($k) {
                case "clientId":
                    if (!preg_match("/^amzn1\.application-oa2-client\.[a-z0-9]{32}$/i", $v)) {
                        $this->_logAndThrow("Invalid parameter value for clientId.");
                    }
                    break;
                case "clientSecret":
                    if (!preg_match("/^[a-z0-9]{64}$/i", $v)) {
                        $this->_logAndThrow("Invalid parameter value for clientSecret.");
                    }
                    break;
                case "accessToken":
                    if (!is_null($v)) {
                        if (!preg_match("/^Atza(\||%7C|%7c).*$/", $v)) {
                            $this->_logAndThrow("Invalid parameter value for accessToken.");
                        }
                    }
                    break;
                case "refreshToken":
                    if (!is_null($v)) {
                        if (!preg_match("/^Atzr(\||%7C|%7c).*$/", $v)) {
                            $this->_logAndThrow("Invalid parameter value for refreshToken.");
                        }
                    }
                    break;
                case "sandbox":
                    if (!is_bool($v)) {
                        $this->_logAndThrow("Invalid parameter value for sandbox.");
                    }
                    break;
            }
        }
        return true;
    }

    private function _setEndpoints()
    {
        /* check if region exists and set api/token endpoints */
        if (array_key_exists(strtolower($this->config["region"]), $this->endpoints)) {
            $region_code = strtolower($this->config["region"]);
            if ($this->config["sandbox"]) {
                $this->endpoint = "https://{$this->endpoints[$region_code]["sandbox"]}/{$this->apiVersion}";
            } else {
                $this->endpoint = "https://{$this->endpoints[$region_code]["prod"]}/{$this->apiVersion}";
            }
            $this->tokenUrl = $this->endpoints[$region_code]["tokenUrl"];
        } else {
            $this->_logAndThrow("Invalid region.");
        }
        return true;
    }

    private function _logAndThrow($message)
    {
        error_log($message, 0);
        throw new \Exception($message);
    }
}
