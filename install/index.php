<?php

IncludeModuleLangFile(__FILE__);

Class anmaslov_dhl extends CModule
{
    var $MODULE_ID = "anmaslov.dhl";
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $MODULE_NAME;
    var $MODULE_DESCRIPTION;
    var $PARTNER_NAME;
    var $PARTNER_URI;
    var $NEED_MODULES = array("sale");

    function anmaslov_dhl()
    {
        $arModuleVersion = array();
        $path = str_replace("\\", "/", __FILE__);
        $path = substr($path, 0, strlen($path) - strlen("/index.php"));

        include($path . "/version.php");
        $this->MODULE_VERSION = $arModuleVersion["VERSION"];
        $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];

        $this->MODULE_NAME = GetMessage('DHL_MODULE_NAME');
        $this->MODULE_DESCRIPTION = GetMessage('DHL_MODULE_DESCRIPTION');
        
        $this->PARTNER_NAME = GetMessage('DHL_PARTNER_NAME');
        $this->PARTNER_URI = GetMessage('DHL_PARTNER_URI');;
    }

    function InstallEvents()
    {
        RegisterModuleDependences("sale", "onSaleDeliveryHandlersBuildList",
            $this->MODULE_ID, "CDeliveryAnmaslovDHL", "Init");

        RegisterModuleDependences("main", "OnEventLogGetAuditTypes",
            $this->MODULE_ID, "CUtilsDHL", "ASD_OnEventLogGetAuditTypes");

        return true;
    }

    function UnInstallEvents()
    {
        UnRegisterModuleDependences("sale", "onSaleDeliveryHandlersBuildList",
            $this->MODULE_ID, "CDeliveryAnmaslovDHL", "Init");

        UnRegisterModuleDependences("main", "OnEventLogGetAuditTypes",
            $this->MODULE_ID, "CUtilsDHL", "ASD_OnEventLogGetAuditTypes");

        return true;
    }

    function DoInstall()
    {
        $this->InstallEvents();
        RegisterModule($this->MODULE_ID);
    }

    function DoUninstall()
    {
        $this->UnInstallEvents();
        UnRegisterModule($this->MODULE_ID);
    }
}
?>