<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");?>
<?$APPLICATION->SetTitle("Личный кабинет покупателя");?>
<h1 class="page_header"><?$APPLICATION->ShowTitle()?></h1>
<?
global $USER;
if(client::checkIfFirstProfile($USER->GetID())) {
?>
    <?$APPLICATION->IncludeComponent(
        "bitrix:menu",
        "profile",
        array(
            "ROOT_MENU_TYPE" => "profile_demo",
            "MENU_CACHE_TYPE" => "A",
            "MENU_CACHE_TIME" => "36000000",
            "MENU_CACHE_USE_GROUPS" => "Y",
            "MENU_CACHE_GET_VARS" => array(),
            "MAX_LEVEL" => "1",
            "CHILD_MENU_TYPE" => "profile_demo",
            "USE_EXT" => "N",
            "DELAY" => "N",
            "ALLOW_MULTI_SELECT" => "N"
        ),
        false
    );?>
    <?$APPLICATION->IncludeComponent(
        "rarus:client_profile_demo",
        ".default",
        Array(
            'U_ID' => $USER->GetID(),
            "DEMO" => 'Y',
            'EDIT_PROPS_LIST' => array(
                'PHONE',
                'REGION',
                'UL_TYPE',
                'INN',
                'FULL_COMPANY_NAME',
                'IP_FIO',
                'REG_DATE',
                'YUR_ADRESS',
                'POST_ADRESS',
                'KPP',
                'OGRN',
                'OKPO',
                'FIO_DIR',
                'OSNOVANIE_PRAVA_PODPISI_FILE',
                'NDS',
                'BANK',
                'BIK',
                'RASCH_SCHET',
                'KOR_SCHET'
            )
        ),
        false
    );?>
<?
}
else {
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
            "USE_EXT" => "N",
            "DELAY" => "N",
            "ALLOW_MULTI_SELECT" => "N"
        ),
        false
    );?>
    <?$APPLICATION->IncludeComponent(
        "rarus:client_profile",
        ".default",
        Array(
            'U_ID' => $USER->GetID(),
            'EDIT_PROPS_LIST' => array(
                'PHONE',
                'REGION',
                'UL_TYPE',
                'INN',
                'FULL_COMPANY_NAME',
                'IP_FIO',
                'YUR_ADRESS',
                'POST_ADRESS',
                'KPP',
                'OGRN',
                'OKPO',
                'FIO_DIR',
                'NDS',
                'SIGNER',
                'POST',
                'FIO_SIGN',
                'FOUND',
                'FOUND_NUM',
                'FOUND_DATE',
                'OSNOVANIE_PRAVA_PODPISI_FILE',
                'BANK',
                'BIK',
                'RASCH_SCHET',
                'KOR_SCHET'
            ),
            'TYPE' => 1
        ),
        false
    );?>
<?
}
?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>