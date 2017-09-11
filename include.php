<?php

$module_id = 'anmaslov.dhl';

CModule::AddAutoloadClasses(
    $module_id,
    array(
        "DhlAPI" => "classes/general/DhlApi.php",
        "CDeliveryAnmaslovDhl" => "classes/general/PeshkarikiDelivery.php",
        "CUtilsDhl" => "classes/general/Utils.php",
    )
);