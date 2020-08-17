<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
CJSCore::Init(array('date'));
?>
<?$APPLICATION->SetTitle("Товары"); ?>
    <h1 class="page_header"><?$APPLICATION->ShowTitle()?></h1>
<?
global $USER;
$APPLICATION->IncludeComponent(
    "bitrix:catalog.filter",
    "agent_offers_filter",
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
        "LIST_URL" => "/partner/offer/",
        "STATUS_LIST" => array("all", "yes", "no"),
        "AGENT_ID" => $USER->GetID()
    )
);
?>
<?
if(!isset($arrFilter['PROPERTY_FARMER'])
    || !is_array($arrFilter['PROPERTY_FARMER'])
    || count($arrFilter['PROPERTY_FARMER']) == 0
)
{?>
    <br/>
    <div class="empty_list">К вам не привязан ни один поставщик</div>
<?}
else{

    $sCacheType = 'A';
    if(!empty($_GET['AJAX']) && $_GET['AJAX'] == 'Y' && ($_GET['ADD_AFFAIR'] == 'Y' || $_GET['SHOW_MORE'] == 'Y')) {
        $sCacheType = 'N';
    }
    $APPLICATION->IncludeComponent(
        "bitrix:news.list",
        "agent_offers_list",
        Array(
            "ACTIVE_DATE_FORMAT" => "d.m.Y",
            "ADD_SECTIONS_CHAIN" => "Y",
            "AJAX_MODE" => "N",
            "AJAX_OPTION_ADDITIONAL" => "",
            "AJAX_OPTION_HISTORY" => "N",
            "AJAX_OPTION_JUMP" => "N",
            "AJAX_OPTION_STYLE" => "Y",
            "CACHE_FILTER" => "Y",
            "CACHE_GROUPS" => "N",
            "CACHE_TIME" => "36000000",
            "CACHE_TYPE" => $sCacheType,
            "CHECK_DATES" => "Y",
            "DETAIL_URL" => "",
            "DISPLAY_BOTTOM_PAGER" => "Y",
            "DISPLAY_DATE" => "Y",
            "DISPLAY_NAME" => "Y",
            "DISPLAY_PICTURE" => "Y",
            "DISPLAY_PREVIEW_TEXT" => "Y",
            "DISPLAY_TOP_PAGER" => "N",
            "FIELD_CODE" => array("NAME", "DATE_CREATE"),
            "FILTER_NAME" => "arrFilter",
            "HIDE_LINK_WHEN_NO_DETAIL" => "N",
            "IBLOCK_ID" => "21",
            "IBLOCK_TYPE" => "farmer",
            "INCLUDE_IBLOCK_INTO_CHAIN" => "Y",
            "INCLUDE_SUBSECTIONS" => "Y",
            "MESSAGE_404" => "",
            "NEWS_COUNT" => "20",
            "PAGER_BASE_LINK_ENABLE" => "N",
            "PAGER_DESC_NUMBERING" => "N",
            "PAGER_DESC_NUMBERING_CACHE_TIME" => "36000",
            "PAGER_SHOW_ALL" => "N",
            "PAGER_SHOW_ALWAYS" => "N",
            "PAGER_TEMPLATE" => "rarus",
            "PAGER_TITLE" => "Новости",
            "PARENT_SECTION" => "",
            "PARENT_SECTION_CODE" => "",
            "PREVIEW_TRUNCATE_LEN" => "",
            "PROPERTY_CODE" => array("FARMER", ""),
            "SET_BROWSER_TITLE" => "N",
            "SET_LAST_MODIFIED" => "N",
            "SET_META_DESCRIPTION" => "Y",
            "SET_META_KEYWORDS" => "Y",
            "SET_STATUS_404" => "N",
            "SET_TITLE" => "N",
            "SHOW_404" => "N",
            "SORT_BY1" => "ID",
            "SORT_BY2" => "SORT",
            "SORT_ORDER1" => "DESC",
            "SORT_ORDER2" => "ASC",
            "UID" => $USER->GetID()
        )
    );
}
?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>