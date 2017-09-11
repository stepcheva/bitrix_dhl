<?php

Class CDeliveryAnmaslovDHL
{
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

    function Compability($arOrder, $arConfig)
    {
        $dhlApi = new DHLApi(new DomDocumentGenerator());
        $response = $dhlApi->Calculate($arOrder, $arConfig);

        $profile_list = array();
        if ($response['STATUS'] == 'OK') {
            $profile_list[] = 'courier';
        }

        return $profile_list;
    }

    function Calculate($profile, $arConfig, $arOrder)
    {
        $dhlApi = new DHLApi(new DomDocumentGenerator());
        $response = $dhlApi->Calculate($arOrder, $arConfig);

        if ($response['STATUS'] == 'OK') {
            return array(
                'RESULT' => 'OK',
                'VALUE' => GetMessage("ANMASLOV_DHL_PERIOD").$response['MESSAGE'][0],
                'TRANSIT' => $response['MESSAGE'][1]
            );
        }

        return array(
            'RESULT' => 'ERROR',
            'TEXT' => $response['MESSAGE']
        );
    }
}