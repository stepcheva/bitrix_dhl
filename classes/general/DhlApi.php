<?php

use \Bitrix\Main\Web\HttpClient;

IncludeModuleLangFile(__FILE__);

class DHLApi
{
    public static function Calculate($arOrder, $arConfig)
    {
        $result = array('STATUS' => 'ERROR', 'BODY' => GetMessage('ANMASLOV_DHL_CALULATE_ERROR'));
        
        

        return $result;
    }
}