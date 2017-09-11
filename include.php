<?php

$module_id = 'anmaslov.dhl';

CModule::AddAutoloadClasses(
    $module_id,
    array(
        "DhlAPI" => "classes/general/DhlApi.php",
        "DomDocumentGenerator" => "classes/general/XMLGenerator.php",
        "CUtilsDhl" => "classes/general/Utils.php",
        "CDeliveryAnmaslovDhl" => "classes/general/DhlDelivery.php",
    )
);