<?php

IncludeModuleLangFile(__FILE__);

Class anmaslov_dhl extends CModule
{
    var $MODULE_ID = "anmaslov.dhl";
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $MODULE_NAME;
    var $MODULE_DESCRIPTION;
    var $MODULE_CSS;

    function anmaslov_dhl()
    {
        $arModuleVersion = array();
        $path = str_replace("\\", "/", __FILE__);
        $path = substr($path, 0, strlen($path) - strlen("/index.php"));
        include($path."/version.php");
        if (is_array($arModuleVersion) && array_key_exists("VERSION", $arModuleVersion))
        {
            $this->MODULE_VERSION = $arModuleVersion["VERSION"];
            $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        }
        $this->MODULE_NAME = GetMessage('DHL_MODULE_NAME');
        $this->MODULE_DESCRIPTION = GetMessage('DHL_MODULE_DESCRIPTION');
        $this->PARTNER_NAME = GetMessage('DHL_PARTNER_NAME');
        $this->PARTNER_URI = GetMessage('DHL_PARTNER_URI');;
    }

    function InstallFiles($arParams = array())
    {
        CopyDirFiles($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/anmaslov.dhl/install/delivery", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/sale/delivery", true, true);
        CopyDirFiles($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/anmaslov.dhl/install/delivery/anmaslov_dhl/lang/ru", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/sale/lang/ru/delivery/anmaslov_dhl", true, true);

        return true;
    }

    //TODO check dir after delete
    function UnInstallFiles()
    {
       /* DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/anmaslov.stall/install/admin/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin");
        DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/anmaslov.stall/install/themes/.default/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/themes/.default");
        DeleteDirFilesEx("/bitrix/themes/.default/start_menu/anmaslov.stall");
       */ return true;
    }

    function DoInstall()
    {
        global $USER, $APPLICATION;
        if ($USER->IsAdmin())
        {
            $this->InstallFiles();
            RegisterModule($this->MODULE_ID);
            $APPLICATION->IncludeAdminFile(GetMessage("DHL_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/anmaslov.dhl/install/step.php");
        }
    }

    function DoUninstall()
    {
        global $USER, $APPLICATION;
        if ($USER->IsAdmin())
        {
            $this->UnInstallFiles();
            UnRegisterModule($this->MODULE_ID);
            $APPLICATION->IncludeAdminFile(GetMessage("DHL_UNINSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/anmaslov.dhl/install/unstep.php");
        }
    }
}
?>