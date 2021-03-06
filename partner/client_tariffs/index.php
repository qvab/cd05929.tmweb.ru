<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
LocalRedirect('/partner/');
exit;
?>
<?$APPLICATION->SetTitle("Тарифы на перевозку");?>
<h1 class="page_header"><?$APPLICATION->ShowTitle()?></h1>
<?$APPLICATION->IncludeComponent(
    "bitrix:catalog.filter",
    "client_agent_tariff_filter",
    Array(
        "CACHE_GROUPS" => "Y",
        "CACHE_TIME" => "36000000",
        "CACHE_TYPE" => "A",
        "FIELD_CODE" => array("",""),
        "FILTER_NAME" => "arrFilter",
        "IBLOCK_ID" => rrsIblock::getIBlockId('client_tariffs'),
        "IBLOCK_TYPE" => "client",
        "LIST_HEIGHT" => "5",
        "NUMBER_WIDTH" => "5",
        "PAGER_PARAMS_NAME" => "arrPager",
        "PRICE_CODE" => array(),
        "PROPERTY_CODE" => array(),
        "SAVE_IN_SESSION" => "N",
        "TEXT_WIDTH" => "20",
        "LIST_URL" => "/partner/client_tariffs/",
        "AGENT_ID" => $USER->GetID()
    )
);?>
<?
if ($GLOBALS['client_tariff_error'] != '') {
    ShowError($GLOBALS['client_tariff_error']);
}
else {
    ?>
    <?$APPLICATION->IncludeComponent(
        "bitrix:news.list",
        "client_tariffs",
        Array(
            "ACTIVE_DATE_FORMAT" => "d.m.Y",
            "ADD_SECTIONS_CHAIN" => "N",
            "AJAX_MODE" => "N",
            "AJAX_OPTION_ADDITIONAL" => "",
            "AJAX_OPTION_HISTORY" => "N",
            "AJAX_OPTION_JUMP" => "N",
            "AJAX_OPTION_STYLE" => "Y",
            "CACHE_FILTER" => "N",
            "CACHE_GROUPS" => "Y",
            "CACHE_TIME" => "36000000",
            "CACHE_TYPE" => "A",
            "CHECK_DATES" => "Y",
            "DETAIL_URL" => "",
            "DISPLAY_BOTTOM_PAGER" => "N",
            "DISPLAY_DATE" => "N",
            "DISPLAY_NAME" => "N",
            "DISPLAY_PICTURE" => "N",
            "DISPLAY_PREVIEW_TEXT" => "N",
            "DISPLAY_TOP_PAGER" => "N",
            "FIELD_CODE" => array("NAME",""),
            "FILTER_NAME" => "arrFilter",
            "HIDE_LINK_WHEN_NO_DETAIL" => "N",
            "IBLOCK_ID" => "74",
            "IBLOCK_TYPE" => "client",
            "INCLUDE_IBLOCK_INTO_CHAIN" => "N",
            "INCLUDE_SUBSECTIONS" => "Y",
            "MESSAGE_404" => "",
            "NEWS_COUNT" => "100",
            "PAGER_BASE_LINK_ENABLE" => "N",
            "PAGER_DESC_NUMBERING" => "N",
            "PAGER_DESC_NUMBERING_CACHE_TIME" => "36000",
            "PAGER_SHOW_ALL" => "N",
            "PAGER_SHOW_ALWAYS" => "N",
            "PAGER_TEMPLATE" => ".default",
            "PAGER_TITLE" => "Новости",
            "PARENT_SECTION" => "",
            "PARENT_SECTION_CODE" => "",
            "PREVIEW_TRUNCATE_LEN" => "",
            "PROPERTY_CODE" => array("USER", ""),
            "SET_BROWSER_TITLE" => "N",
            "SET_LAST_MODIFIED" => "N",
            "SET_META_DESCRIPTION" => "N",
            "SET_META_KEYWORDS" => "N",
            "SET_STATUS_404" => "N",
            "SET_TITLE" => "N",
            "SHOW_404" => "N",
            "SORT_BY1" => "ID",
            "SORT_BY2" => "SORT",
            "SORT_ORDER1" => "DESC",
            "SORT_ORDER2" => "ASC",
            "LIST_URL" => '/partner/client_tariffs/?client='.$_GET['client'],
            "MIN_TARIFF" => rrsIblock::getConst('min_tariff'),
            "MAX_TARIFF" => rrsIblock::getConst('max_tariff'),
            "CLIENT_ID" => $_GET['client'],
        )
    );?>
    <?
}
?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>