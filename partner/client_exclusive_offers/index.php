<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");?>
<?$APPLICATION->SetTitle("Предложения для покупателей");?>
    <h1 class="page_header"><?$APPLICATION->ShowTitle()?></h1>
<?
global $USER;
$APPLICATION->IncludeComponent(
    "bitrix:catalog.filter",
    //"client_counter_requests_filter",
    "client_counter_req_free_select_filter",
    Array(
        "CACHE_GROUPS" => "Y",
        "CACHE_TIME" => "36000000",
        "CACHE_TYPE" => "Y",
        "FIELD_CODE" => array("",""),
        "FILTER_NAME" => "arrFilter",
        "IBLOCK_ID" => "15",
        "IBLOCK_TYPE" => "client",
        "PAGER_PARAMS_NAME" => "arrPager",
        "PRICE_CODE" => array(),
        "PROPERTY_CODE" => array("",""),
        "SAVE_IN_SESSION" => "N",
        "LIST_URL" => "/partner/exclusive_offers/",
        'AGENT_ID' => $USER->GetID()
    )
);?>
<?$APPLICATION->IncludeComponent(
    "rarus:client.counter.requests_list",
    "",
    Array(
        "AGENT_ID" => $USER->GetID(),
        'DISPLAY_BOTTOM_PAGER' => 'Y',
        'NEWS_COUNT' => 20
    )
);?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>