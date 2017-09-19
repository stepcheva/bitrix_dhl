<?php

use \Bitrix\Main\Web\HttpClient;

IncludeModuleLangFile(__FILE__);

class DHLApi
{
    private $xmlGenerator;

    private $params;

    public function __construct($xmlGenerator)
    {
        $this->xmlGenerator = $xmlGenerator;
    }

    private function setDeliveryParam($arOrder, $arConfig)
    {
        $param = new ParamsDto(
            $arConfig['SITE_ID']['VALUE'],
            $arConfig['PASSWORD']['VALUE'],
            $arConfig['ACCOUNT_NUMBER']['VALUE']
        );

        $param->zip_from = COption::GetOptionString('sale', 'location_zip');

        $arLocation = CUtilsDhl::getLocation($arOrder["LOCATION_TO"]);
        $param->country_to = Country::getCountryCode($arLocation[0]);

        if ($param->country_to == 'RU') // zip only ru
        {
            $param->zip_to = empty($arOrder["LOCATION_ZIP"]) ? CUtilsDhl::getZip($arOrder["LOCATION_TO"]) : $arOrder["LOCATION_ZIP"];
            if (empty($param->zip_to))
            {
                CUtilsDhl::addLog('params zip to is empty');
                $this->params = null;
                return false;
            }
        } else {
            if (empty($arLocation[1]))
            {
                CUtilsDhl::addLog('City is not found');
                $this->params = null;
                return false;
            }
            $param->city_to = $arLocation[1];
        }

        AddMessage2Log($param, 'param');

        $weight = CSaleMeasure::Convert($arOrder["WEIGHT"], "G", "KG");
        if ($weight <= 0) $weight = 0.1;

        $param->weight = round($weight, 3);
        $param->date = CUtilsDhl::getNextDay();

        $this->params = $param;

        return $this;
    }

    public function Calculate($arOrder, $arConfig)
    {
        $result = array('STATUS' => 'ERROR', 'MESSAGE' => GetMessage('ANMASLOV_DHL_CALULATE_ERROR'));
        self::setDeliveryParam($arOrder, $arConfig);

        if ($this->params != null) {
            $obCache = new CPHPCache();
            $life_time = 10*60;
            $p = $this->params;
            $cache_id = "dhl_rus_mas"."|".$arConfig['SITE_ID']['VALUE']."|".$p->zip_from."|".
                $p->country_to."|".$p->zip_to."|".$p->city_to."|".$p->weight;

            CUtilsDhl::addLog($cache_id);

            if ($obCache->InitCache($life_time, $cache_id)) {
                CUtilsDhl::addLog('Get data from cache');
                $vars = $obCache->GetVars();
                $result = $vars["VALUE"];
            } else {
                $httpClient = new HttpClient();
                $httpClient->setHeader('Content-Type', 'application/xml', true);

                try {
                    $outResponce = $httpClient->post($arConfig['SERVER']['VALUE'], $this->xmlGenerator->generate($p) );
                    $response = CUtilsDhl::parseResult($outResponce);

                    if (!$response) {
                        $result['MESSAGE'] = GetMessage('ANMASLOV_DHL_SERVICE_IS_NOT_AVIABLE');
                    } else {
                        $result['STATUS'] = 'OK';
                        $result['MESSAGE'] = array($response['PRICE'], $response['DAYS']);
                    }

                } catch (Exception $e) {
                    $result['MESSAGE'] = GetMessage('ANMASLOV_DHL_CONNECTION_ERROR');
                }

                $obCache->StartDataCache($life_time, $cache_id);
                $obCache->EndDataCache(array('VALUE' => $result));
            }
        }

        return $result;
    }
}