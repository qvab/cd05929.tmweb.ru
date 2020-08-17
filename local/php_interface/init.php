<?
CModule::AddAutoloadClasses(
    '', // не указываем имя модуля
    array(
        'rrsIblock'             => '/local/php_interface/classes/rrsIblock.php',
        'client'                => '/local/php_interface/classes/client.php',
        'farmer'                => '/local/php_interface/classes/farmer.php',
        'partner'               => '/local/php_interface/classes/partner.php',
        'transport'             => '/local/php_interface/classes/transport.php',
        'agent'                 => '/local/php_interface/classes/agent.php',
        'model'                 => '/local/php_interface/classes/model.php',
        'culture'               => '/local/php_interface/classes/culture.php',
        'deal'                  => '/local/php_interface/classes/deal.php',
        'log'                   => '/local/php_interface/classes/log.php',
        'pdf'                   => '/local/php_interface/classes/pdf.php',
        'notice'                => '/local/php_interface/classes/notice.php',
        'lead'                  => '/local/php_interface/classes/lead.php',
        'Agrohelper'            => '/local/php_interface/classes/WebApi/Agrohelper.php',
        'Users'                 => '/local/php_interface/classes/WebApi/Users.php',
        'Auth'                  => '/local/php_interface/classes/WebApi/Auth.php',
        'UsersDevices'          => '/local/php_interface/classes/WebApi/UsersDevices.php',
        'FarmerRequests'        => '/local/php_interface/classes/WebApi/FarmerRequests.php',
        'ClientRequests'        => '/local/php_interface/classes/WebApi/ClientRequests.php',
        'Push'                  => '/local/php_interface/classes/WebApi/Push.php',
        'Deals'                 => '/local/php_interface/classes/WebApi/Deals.php',
        'HighloadRequestsHash'  => '/local/php_interface/classes/WebApi/HighloadRequestsHash.php',
        'ParityPriceReport'     => '/local/php_interface/classes/ParityPriceReport.php',
        'ImportRegionalModification'     => '/local/php_interface/classes/ImportRegionalModification.php',
        'BlackList'             => '/local/php_interface/classes/BlackList.php',
        'dashboardP'            => '/local/php_interface/classes/dashboardP.php',
        'CDocument'             => '/local/php_interface/classes/CDocument.php',
        'CAffair'               => '/local/php_interface/classes/CAffair.php',
        'admin'                => '/local/php_interface/classes/admin.php',
        'popupTemplates'        => '/local/php_interface/classes/popupTemplates.php',
    )
);

require_once($_SERVER["DOCUMENT_ROOT"].'/include/transit/params.php');

/* В этом файле подключаем все функции */
require_once(__DIR__.'/functions/include.php');

require_once(__DIR__.'/include/functions.php');
require_once(__DIR__.'/include/handlers.php');
?>