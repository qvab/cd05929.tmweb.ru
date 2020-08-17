<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");?>
<?$APPLICATION->SetTitle("Личный кабинет транспортной компании");?>
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
<?$APPLICATION->IncludeComponent(
    "rarus:transport_profile",
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