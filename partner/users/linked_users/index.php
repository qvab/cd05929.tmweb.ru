<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");?>
<?$APPLICATION->SetTitle("Пользователи");?>
<h1 class="page_header"><?$APPLICATION->ShowTitle()?></h1>
<?$APPLICATION->IncludeComponent(
    "bitrix:menu",
    "profile",
    array(
        "ROOT_MENU_TYPE" => "users",
        "MENU_CACHE_TYPE" => "A",
        "MENU_CACHE_TIME" => "36000000",
        "MENU_CACHE_USE_GROUPS" => "Y",
        "MENU_CACHE_GET_VARS" => array(),
        "MAX_LEVEL" => "1",
        "CHILD_MENU_TYPE" => "users",
        "USE_EXT" => "N",
        "DELAY" => "N",
        "ALLOW_MULTI_SELECT" => "N"
    ),
    false
);?>
<?
$APPLICATION->IncludeComponent(
    "bitrix:catalog.filter",
    "partner_farmers_filter",
    Array(
        "CACHE_GROUPS" => "Y",
        "CACHE_TIME" => "36000000",
        "CACHE_TYPE" => "N",
        "FIELD_CODE" => array("",""),
        "FILTER_NAME" => "arrFilter",
        "IBLOCK_ID" => "21",
        "IBLOCK_TYPE" => "farmer",
        "LIST_HEIGHT" => "5",
        "NUMBER_WIDTH" => "5",
        "PAGER_PARAMS_NAME" => "arrPager",
        "PRICE_CODE" => array(),
        "PROPERTY_CODE" => array("",""),
        "SAVE_IN_SESSION" => "N",
        "LIST_URL" => "/partner/users/linked_clients/",
    )
);
?>
<?
if(isset($_GET['success']))
{
    if($_GET['success'] == 'demo'){
        echo '<div class="success">Профиль изменён</div>';
    }elseif($_GET['success'] == 'add'){
        echo '<div class="success">Поставщик успешно добавлен</div>';
    }elseif($_GET['success'] == 'deactivated'){
        echo '<div class="success">Поставщик успешно деактивирован</div>';
    }elseif($_GET['success'] == 'deleted'){
        echo '<div class="success">Поставщик успешно удален</div>';
    }
}

$APPLICATION->IncludeComponent(
    "rarus:partner_farmers_list",
    "",
    Array()
);?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>