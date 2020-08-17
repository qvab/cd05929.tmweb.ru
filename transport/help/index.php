<?php
/**
 * Created by PhpStorm.
 * User: bragev
 * Date: 26.06.2018
 * Time: 15:14
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
        "GROUP_ID" => 12
    )
);
?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>