<?php
/***********************************
 * Delivery DHL Packadge
 * Maslov A.N. a@anmaslov.ru
 * 2017-05-11
 */

IncludeModuleLangFile(__FILE__);

define('ANMASLOV_DELIVERY_DHL_WRITE_LOG', 0); //write log for debugging

class CDeliveryAnmaslovDHL{

    function Init()
    {
        if ($arCurrency = CCurrency::GetByID('RUR')):
            $base_currency = 'RUR';
        else:
            $base_currency = 'RUB';
        endif;

        return array(
            "SID" => "anmaslov_dhl", // unique string identifier
            "NAME" => GetMessage('ANMASLOV_DHL_MODULE_NAME'), // services public title
            "DESCRIPTION" => GetMessage('ANMASLOV_DHL_MODULE_DESCRIPTION'), // services public dedcription
            "DESCRIPTION_INNER" => GetMessage('ANMASLOV_DHL_MODULE_DESCRIPTION_INNER'), // services private description for admin panel
            "BASE_CURRENCY" => $base_currency, // services base currency

            "HANDLER" => __FILE__, // services path

            "COMPABILITY" => array("CDeliveryAnmaslovDHL", "Compability"),
            "CALCULATOR" => array("CDeliveryAnmaslovDHL", "Calculate"),

            "DBGETSETTINGS" => array("CDeliveryDHLUSA", "GetSettings"),
            "DBSETSETTINGS" => array("CDeliveryDHLUSA", "SetSettings"),

            "GETCONFIG" => array("CDeliveryAnmaslovDHL", "GetConfig"),

            "PROFILES" => array(
                "courier" => array(
                    "TITLE" => GetMessage("ANMASLOV_DHL_COURIER_TITLE"),
                    "DESCRIPTION" => GetMessage("ANMASLOV_DHL_COURIER_DESCRIPTION"),

                    "RESTRICTIONS_WEIGHT" => array(0),
                    "RESTRICTIONS_SUM" => array(0),
                )
            )
        );
    }

    function SetSettings($arSettings)
    {
        return serialize($arSettings);
    }

    function GetSettings($strSettings)
    {
        return unserialize($strSettings);
    }

    function GetConfig()
    {
        $arConfig = array(
            "CONFIG_GROUPS" => array(
                "all" => GetMessage('ANMASLOV_DHL_CONFIG_TITLE'),
            ),

            "CONFIG" => array(
                "SERVER" => array(
                    "TYPE" => "STRING",
                    "DEFAULT" => "",
                    "TITLE" => GetMessage("ANMASLOV_DHL_DHL_SERVER_PATH"),
                    "GROUP" => "all"
                ),
                "SITE_ID" => array(
                    "TYPE" => "STRING",
                    "DEFAULT" => "",
                    "TITLE" => GetMessage("ANMASLOV_DHL_SITE_ID"),
                    "GROUP" => "all"
                ),
                "PASSWORD" => array(
                    "TYPE" => "STRING",
                    "DEFAULT" => "",
                    "TITLE" => GetMessage("ANMASLOV_DHL_PASSWORD"),
                    "GROUP" => "all"
                ),
                'ACCOUNT_NUMBER' => array(
                    'TYPE' => 'STRING',
                    'DEFAULT' => '',
                    'TITLE' => GetMessage("ANMASLOV_DHL_ACCOUNT"),
                    'GROUP' => 'all'
                ),
            ),
        );

        return $arConfig;
    }

    function Calculate($profile, $arConfig, $arOrder)
    {
        $location_from_zip = COption::GetOptionString('sale', 'location_zip');
        CDeliveryAnmaslovDHL::__Write2log($location_from_zip, "location_from_zip");

        $arOrder["WEIGHT"] = CSaleMeasure::Convert($arOrder["WEIGHT"], "G", "KG");
        if ($arOrder["WEIGHT"] <= 0) $arOrder["WEIGHT"] = 0.1;
        $arOrder["WEIGHT"] = round($arOrder["WEIGHT"], 3);

        $cache_id = "dhl_rus_mas"."|".$arConfig['SITE_ID']['VALUE']."|".$location_from_zip."|".$arOrder["LOCATION_ZIP"]."|".$arOrder['WEIGHT'];

        self::__Write2log($cache_id, "cache_id");

        $obCache = new CPHPCache();

        if ($obCache->InitCache(2592000, $cache_id, "/"))
        {
            self::__Write2log("Get data from cache");
            // cache found
            $vars = $obCache->GetVars();
            $result = $vars["VALUE"];
            $transit_time = $vars["TRANSIT"];

            return array(
                "RESULT" => "OK",
                "VALUE" => $result,
                "TRANSIT" => GetMessage('ANMASLOV_DHL_PERIOD').' '.$transit_time,
            );
        }

        $param = array(
            "DATE" =>  self::getDate(),
            "ZIP_FROM" => $location_from_zip,
            "ZIP_TO" => $arOrder["LOCATION_ZIP"],
            "WEIGHT" => $arOrder["WEIGHT"],
            "SITE_ID" => $arConfig['SITE_ID']['VALUE'],
            "PASSWORD" => $arConfig['PASSWORD']['VALUE'],
            "ACCOUNT" => $arConfig['ACCOUNT_NUMBER']['VALUE'],
        );

        self::__Write2log("Load data from remote URI");
        self::__Write2log($param, "param");

        $xml = new XMLGenerator($param);
        $data = $xml->generate()->save();

        $req = self::method_exec_post($arConfig['SERVER']['VALUE'], $data);

        if($req){
            $obCache->StartDataCache();
            self::__Write2log($req, "req");

            $obCache->EndDataCache(
                array(
                    "VALUE" => $req['PRICE'],
                    "TRANSIT" => $req['DAYS'],
                )
            );

        }else{
            return array(
                "RESULT" => "ERROR",
                "TEXT" => GetMessage('ANMASLOV_DHL_SERVICE_AN_AVAILABLE'),
            );
        }

        return array(
            "RESULT" => "OK",
            "VALUE" => $req['PRICE'],
            "TRANSIT" => GetMessage('ANMASLOV_DHL_PERIOD').' '.$req['DAYS'],
        );
    }

    function getDate()
    {
        $timestamp = strtotime(date('Y-m-d'));
        $timestamp = strtotime('+1 day', $timestamp);

        return date('Y-m-d', $timestamp);
    }

    function method_exec_post($server, $data = NULL)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $server);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 30);
        $out_data = curl_exec($ch);

        self::__Write2Log($out_data, "out_data");

        $xml = simplexml_load_string($out_data);
        if ($xml)
        {
            $info = $xml->GetQuoteResponse->BkgDetails->QtdShp;
            if ($info)
            {
                return array(
                    "PRICE" => strval($info->ShippingCharge),
                    "TAX" => strval($info->WeightChargeTax),
                    "DAYS" => strval($info->TotalTransitDays),
                );
            }
        }
        return false;
    }

    function __Write2Log($data, $name = "log")
    {
        if (defined('ANMASLOV_DELIVERY_DHL_WRITE_LOG') && ANMASLOV_DELIVERY_DHL_WRITE_LOG === 1)
        {
            AddMessage2Log($data, $name);
        }
    }
}

Class XMLGenerator{

    protected $_date;
    protected $_zip_from;
    protected $_zip_to;
    protected $_weight;

    protected $_siteId;
    protected $_password;
    protected $_account;

    protected $_xml;

    function __construct($param)
    {
        $this->_date = $param["DATE"];
        $this->_zip_from = $param["ZIP_FROM"];
        $this->_zip_to = $param["ZIP_TO"];
        $this->_weight = $param["WEIGHT"];

        $this->_siteId = $param["SITE_ID"];
        $this->_password = $param["PASSWORD"];
        $this->_account = $param["ACCOUNT"];


        $this->_xml = new \DOMDocument('1.0', 'UTF-8');
    }

    public function generate()
    {
        $p = $this->_xml->createElement("p:DCTRequest");
        $p = $this->_xml->appendChild($p);

        $p->setAttribute( "xmlns:p", "http://www.dhl.com" );
        $p->setAttribute( "xmlns:p1", "http://www.dhl.com/datatypes" );
        $p->setAttribute( "xmlns:p2", "http://www.dhl.com/DCTRequestdatatypes" );
        $p->setAttribute( "xmlns:xsi", "http://www.w3.org/2001/XMLSchema-instance" );

        $getQuote = $this->_xml->createElement("GetQuote");
        $getQuote = $p->appendChild($getQuote);

        $Request = $this->_xml->createElement("Request");
        $Request = $getQuote->appendChild($Request);

        $ServiceHeader = $this->_xml->createElement("ServiceHeader");
        $ServiceHeader = $Request->appendChild($ServiceHeader);

        $ServiceHeader->appendChild($this->_xml->createElement('MessageTime', date('Y-m-d').'T'.date('H:i:sP')));
        //$ServiceHeader->appendChild($this->_xml->createElement('MessageTime','2017-04-27T22:47:14.300+01:00'));
        $ServiceHeader->appendChild($this->_xml->createElement('MessageReference','1234567890123456789012345678901'));
        $ServiceHeader->appendChild($this->_xml->createElement('SiteID', $this->_siteId));
        $ServiceHeader->appendChild($this->_xml->createElement('Password', $this->_password));


        $From = $this->_xml->createElement("From");
        $From = $getQuote->appendChild($From);

        $From->appendChild($this->_xml->createElement('CountryCode','RU'));
        $From->appendChild($this->_xml->createElement('Postalcode',$this->_zip_from));

        $BkgDetails = $this->_xml->createElement("BkgDetails");
        $BkgDetails = $getQuote->appendChild($BkgDetails);

        $BkgDetails->appendChild($this->_xml->createElement('PaymentCountryCode','RU'));
        $BkgDetails->appendChild($this->_xml->createElement('Date', $this->_date));
        $BkgDetails->appendChild($this->_xml->createElement('ReadyTime','PT10H00M'));
        $BkgDetails->appendChild($this->_xml->createElement('DimensionUnit','CM'));
        $BkgDetails->appendChild($this->_xml->createElement('WeightUnit','KG'));
        $BkgDetails->appendChild($this->_xml->createElement('ShipmentWeight', $this->_weight));
        $BkgDetails->appendChild($this->_xml->createElement('PaymentAccountNumber', $this->_account));
        $BkgDetails->appendChild($this->_xml->createElement('IsDutiable','N'));
        $BkgDetails->appendChild($this->_xml->createElement('NetworkTypeCode','TD'));

        $QtdShp = $this->_xml->createElement("QtdShp");
        $QtdShp = $BkgDetails->appendChild($QtdShp);

        $QtdShp->appendChild($this->_xml->createElement('GlobalProductCode','N'));
        $QtdShp->appendChild($this->_xml->createElement('LocalProductCode','N'));

        $To = $this->_xml->createElement("To");
        $To = $getQuote->appendChild($To);

        $To->appendChild($this->_xml->createElement('CountryCode','RU'));
        $To->appendChild($this->_xml->createElement('Postalcode', $this->_zip_to));

        return $this;
    }

    public function save($format = false)
    {
        $this->_xml->formatOutput = $format;
        return $this->_xml->saveXML();
    }
}

AddEventHandler("sale", "onSaleDeliveryHandlersBuildList", array('CDeliveryAnmaslovDHL', 'Init'));

?>