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

        if (strlen($arOrder["LOCATION_ZIP"]) > 0)
            $param->zip_to = $arOrder["LOCATION_ZIP"];
        else
            $param->zip_to = CUtilsDhl::getZip($arOrder["LOCATION_TO"]);

        if (strlen($param->zip_to) == 0)
        {
            CUtilsDhl::WRITE_LOG('params zip to is empty');
            $this->params = null;
            return false;
        }

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
            $cache_id = "dhl_rus_mas"."|".$arConfig['SITE_ID']['VALUE']."|".$p->zip_from."|".$p->zip_to."|".$p->weight;
            CUtilsDhl::WRITE_LOG($cache_id);

            if ($obCache->InitCache($life_time, $cache_id)) {
                CUtilsDhl::WRITE_LOG('Get data from cache');
                $vars = $obCache->GetVars();
                $result = $vars["VALUE"];
            } else {
                $httpClient = new HttpClient();
                $httpClient->setHeader('Content-Type', 'application/xml', true);

                try {
                    $response = $httpClient->post(
                        $arConfig['SERVER']['VALUE'],
                        $this->xmlGenerator->generate($p)
                    );

                    if (isset($response->errors)) {
                        $result['BODY'] = "some error from site dhl";

                    } else {
                        $result['STATUS'] = 'OK';
                        $result['MESSAGE'] = array($response->price, $response->transition);
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