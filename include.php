<?php

$module_id = 'anmaslov.dhl';

CModule::AddAutoloadClasses(
    $module_id,
    array(
        "DhlAPI" => "classes/general/DhlApi.php",
        "XMLGenerator" => "classes/general/XMLGenerator.php",
        "CDeliveryAnmaslovDhl" => "classes/general/DhlDelivery.php",
        "CUtilsDhl" => "classes/general/Utils.php",
    )
);