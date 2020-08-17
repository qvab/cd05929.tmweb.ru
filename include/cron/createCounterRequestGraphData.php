<?

/*
 * Создание данных для построения графиков во встречных предложениях
 * (запускается раз в сутки ночью)
 * */

/*if(empty($_SERVER['SHELL']))
die();*/

set_time_limit(12000);
//боевой
$_SERVER["DOCUMENT_ROOT"] = "/home/bitrix/www";
//песочница
//$_SERVER["DOCUMENT_ROOT"] = "/home/aledem/sandboxes/agrouber/www";

define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS",true);
flush();
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
ob_end_flush();

CModule::IncludeModule('iblock');

//запуск расчёта
client::saveCounterRequestsGraphsData();

?>