<?php
// Обрабатывает и сохраняет данные о пользователях организатора в highload инфоблок "STATFORPARTNER"

//if(empty($_SERVER['SHELL']))
//    die();

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

$d_obj = new dashboardP();
$partners_data = $d_obj->createDashboardData();
