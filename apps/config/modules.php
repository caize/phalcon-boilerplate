<?php
/**
 * Register application modules
 */
$application->registerModules(array(
    'api'  => array(
        'className' => 'Promoziti\Modules\Api\Module',
        'path'      => '../apps/modules/api/Module.php',
    ),
    'business'  => array(
        'className' => 'Promoziti\Modules\Business\Module',
        'path'      => '../apps/modules/business/Module.php',
    ),
));