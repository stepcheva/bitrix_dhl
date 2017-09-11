<?php

IncludeModuleLangFile(__FILE__);

class CUtilsDhl
{
    const MODULE_ID = "anmaslov.dhl";

    public static function addLog($data, $object = 'anmaslov.dhl', $severity = 'DEBUG')
    {
        //$isLog = COption::GetOptionString(self::MODULE_ID, 'PROPERTY_MAKE_LOG', 'N');

        //if ($isLog == 'Y'){

            if (is_array($data))
                $data = serialize($data);

            CEventLog::Add(array(
                "SEVERITY" => $severity,
                "AUDIT_TYPE_ID" => "PESHKARIKI_TYPE",
                "MODULE_ID" => self::MODULE_ID,
                "ITEM_ID" => $object,
                "DESCRIPTION" => $data,
            ));
        //}
    }

    public function ASD_OnEventLogGetAuditTypes()
    {
        return array('DHL_TYPE' => GetMessage('ANMASLOV_DHL_OWN_TYPE'));
    }
}