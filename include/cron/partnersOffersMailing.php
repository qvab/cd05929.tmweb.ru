<?//Пересчет рейтига покупателей, берутся оценки покупаталей за последний год
//1 день

if(empty($_SERVER['SHELL']))
	die();

set_time_limit(12000);
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
CModule::IncludeModule('highload');

//запускаем логику сбора данных и отправку писем
partner::dailyPartnersOfferMailing();