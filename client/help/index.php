<?php
/**
 * Created by PhpStorm.
 * User: bragev
 * Date: 22.06.2018
 * Time: 10:54
 */
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
    $APPLICATION->SetTitle("Обратная связь");?>
<?
$APPLICATION->IncludeComponent(
    "rarus:system.response.form",
    "auth",
    Array(
        "PROFILE_URL" => "/",
        "SHOW_ERRORS" => "Y",
        "GROUP_ID" => 9
    )
);
?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>