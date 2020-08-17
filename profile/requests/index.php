<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

if (!$USER->IsAuthorized()
    || !isset($_GET['uid'])
    || !is_numeric($_GET['uid'])
) {
    LocalRedirect('/');
    exit;
}

$group = getUserType($_GET['uid']);
?>
<h1><?$APPLICATION->ShowTitle();?></h1>

<?$linked_user = $APPLICATION->IncludeComponent(
    'rarus:public_profile_menu',
    '',
    Array(
        'U_ID' => $_GET['uid'],
        'TYPE' => $group['TYPE'],
        'TAB' => 'requests'
    )
);
if (!$linked_user) {
    LocalRedirect('/profile/?uid=' . $_GET['uid']);
    exit;
}?>
<?$APPLICATION->IncludeComponent(
    "bitrix:catalog.filter",
    "farmer_requests_filter_public",
    Array(
        "CACHE_GROUPS" => "Y",
        "CACHE_TIME" => "36000000",
        "CACHE_TYPE" => "A",
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
        "TEXT_WIDTH" => "20",
        "LIST_URL" => "/profile/requests/?uid={$_GET['uid']}",
        "FARMER_ID" => $_GET['uid']
    )
);
?>
<?$APPLICATION->IncludeComponent(
    "rarus:v3.lead.list",
    "",
    Array(
        "FARMER_ID" => $_GET['uid'],
        "OFFER_LIST_URL" => "/farmer/offer/",
        "DEAL_LIST_URL" => "/farmer/deals/",
        "TYPE" => "public",
    )
);?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>