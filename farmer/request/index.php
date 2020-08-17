<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

LocalRedirect('/farmer/');
exit;
?>
<?$APPLICATION->SetTitle("Запросы покупателей");?>
<h1 class="page_header"><?$APPLICATION->ShowTitle()?></h1>
<?$APPLICATION->IncludeComponent(
    "bitrix:catalog.filter",
    "farmer_requests_filter",
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
        "TEXT_WIDTH" => "20",
        "LIST_URL" => "/farmer/request/",
        "FARMER_ID" => $USER->GetID()
    )
);?>
<?$APPLICATION->IncludeComponent(
    "rarus:v3.lead.list",
    "",
    Array(
        "FARMER_ID" => $USER->GetID(),
        "OFFER_LIST_URL" => "/farmer/offer/",
        "DEAL_LIST_URL" => "/farmer/deals/",
        "TYPE" => "farmer",
        'DISPLAY_BOTTOM_PAGER' => 'Y',
        'NEWS_COUNT' => 20
    )
);?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>