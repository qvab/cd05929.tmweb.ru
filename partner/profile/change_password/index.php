<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");?>
<?$APPLICATION->SetTitle("Личный кабинет организатора");?>
<h1 class="page_header"><?$APPLICATION->ShowTitle()?></h1>
<?$APPLICATION->IncludeComponent(
    "bitrix:menu",
    "profile",
    array(
        "ROOT_MENU_TYPE" => "profile",
        "MENU_CACHE_TYPE" => "A",
        "MENU_CACHE_TIME" => "36000000",
        "MENU_CACHE_USE_GROUPS" => "Y",
        "MENU_CACHE_GET_VARS" => array(),
        "MAX_LEVEL" => "1",
        "CHILD_MENU_TYPE" => "profile",
        "USE_EXT" => "N",
        "DELAY" => "N",
        "ALLOW_MULTI_SELECT" => "N"
    ),
    false
);?>
<?
require_once($_SERVER['DOCUMENT_ROOT'] . '/include/restricted_edit_fields.php');
?>
<?$APPLICATION->IncludeComponent(
    "rarus:change_password",
    ".default",
    Array(
        'U_ID' => $USER->GetID(),
        'IB_CODE' => 'partner_profile'
    ),
    false
);?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>