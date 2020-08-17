<?
//получение/обновление расстояний между складами поставщиков

if(empty($_SERVER['SHELL']))
die();

set_time_limit(3400);
//боевой
$_SERVER["DOCUMENT_ROOT"] = "/home/bitrix/www";
//песочница
//$_SERVER["DOCUMENT_ROOT"] = "/www/unorganized_projects/agrohelper/sandboxes/dmitrd/www";

define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS",true);
flush();
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
ob_end_flush();

CModule::IncludeModule('iblock');

    //обновляем устаревшие записи
    farmer::updateFarmersWHRoutes();

    //генерируем новые записи
    farmer::generateNewFarmersWHRoutes();
?>