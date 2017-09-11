<?php

IncludeModuleLangFile(__FILE__);

class CUtilsDhl
{
    const MODULE_ID = "anmaslov.dhl";
    const WRITE_LOG = 'Y';

    public static function addLog($data, $object = 'anmaslov.dhl', $severity = 'DEBUG')
    {
        //$isLog = COption::GetOptionString(self::MODULE_ID, 'PROPERTY_MAKE_LOG', 'N');

        if (self::WRITE_LOG == 'Y'){

            if (is_array($data))
                $data = serialize($data);

            CEventLog::Add(array(
                "SEVERITY" => $severity,
                "AUDIT_TYPE_ID" => "PESHKARIKI_TYPE",
                "MODULE_ID" => self::MODULE_ID,
                "ITEM_ID" => $object,
                "DESCRIPTION" => $data,
            ));
        }
    }

    public static function getZip($location)
    {
        $ID = CSaleLocation::getLocationIDbyCODE($location);
        $zipList = CSaleLocation::GetLocationZIP($ID);

        if ($arZip = $zipList->Fetch())
        {
            if (!empty($arZip['ZIP']))
            {
                return $arZip['ZIP'];
            }
        }

        return false;
    }

    public static function getNextDay()
    {
        $timestamp = strtotime(date('Y-m-d'));
        $timestamp = strtotime('+1 day', $timestamp);

        return date('Y-m-d', $timestamp);
    }

    public function ASD_OnEventLogGetAuditTypes()
    {
        return array('DHL_TYPE' => GetMessage('ANMASLOV_DHL_OWN_TYPE'));
    }
}