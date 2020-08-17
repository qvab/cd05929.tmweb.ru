<?php
/**
 * Created by PhpStorm.
 * User: bragev
 * Date: 26.06.2018
 * Time: 14:45
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
        "GROUP_ID" => 10
    )
);
?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>