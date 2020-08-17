<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Личный кабинет поставщика");?>
<h1 class="page_header"><?$APPLICATION->ShowTitle()?></h1>
<?
if ($GLOBALS['DEMO'] == 'Y') {
    LocalRedirect('/farmer/profile/');
}
?>
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
        "USE_EXT" => "Y",
        "DELAY" => "N",
        "ALLOW_MULTI_SELECT" => "N",
        "MENU_CACHE_USE_USERS" => 'Y',
    ),
    false
);?>
<?$APPLICATION->IncludeComponent(
    "rarus:farmer_profile",
    ".default",
    Array(
        'U_ID' => $USER->GetID(),
        'EDIT_PROPS_LIST' => array(
            'UL_TYPE',
            'NDS'
        ),
        'TYPE' => 2
    ),
    false
);?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>