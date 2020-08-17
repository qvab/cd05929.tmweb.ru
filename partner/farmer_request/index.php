<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

LocalRedirect('/partner/');
exit;
?>
<?$APPLICATION->SetTitle("Запросы для поставщиков");?>
    <h1 class="page_header"><?$APPLICATION->ShowTitle()?></h1>
<?$APPLICATION->IncludeComponent(
    "bitrix:catalog.filter",
    "agent_leads_filter",
    Array(
        "CACHE_GROUPS" => "Y",
        "CACHE_TIME" => "36000000",
        "CACHE_TYPE" => "N",
        "FIELD_CODE" => array("",""),
        "FILTER_NAME" => "arrFilter",
        "IBLOCK_ID" => "20",
        "IBLOCK_TYPE" => "client",
        "LIST_HEIGHT" => "5",
        "NUMBER_WIDTH" => "5",
        "PAGER_PARAMS_NAME" => "arrPager",
        "PRICE_CODE" => array(),
        "PROPERTY_CODE" => array("",""),
        "SAVE_IN_SESSION" => "N",
        "TEXT_WIDTH" => "20",
        "LIST_URL" => "/partner/farmer_request/",
        "STATUS_LIST" => array("all", "yes", "no"),
        "AGENT_ID" => $USER->GetID()
    )
);?>

<?
$farmers_list = array();
if (isset($arrFilter['PROPERTY_FARMER'])
    && is_array($arrFilter['PROPERTY_FARMER'])
    && count($arrFilter['PROPERTY_FARMER']) > 0
) {
    //если фильтр задан
    $farmers_list = $arrFilter['PROPERTY_FARMER'];
}
if (count($farmers_list) == 0) {
    //если фильтр пуст
    $agentObj = new agent();
    $farmers_list = $agentObj->getFarmers($USER->GetID());
}
?>

<?$APPLICATION->IncludeComponent(
    "rarus:v3.lead.list",
    "",
    Array(
        "FARMER_ID" => $farmers_list,
        "OFFER_LIST_URL" => "/partner/offer/",
        "DEAL_LIST_URL" => "/partner/deals/",
        "TYPE" => "agent",
        'DISPLAY_BOTTOM_PAGER' => 'Y',
        'NEWS_COUNT' => 20,
        //"TYPE_NDS"  => $arrFilter['TYPE_NDS'],
    )
);?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>