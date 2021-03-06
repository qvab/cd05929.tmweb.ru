<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");?>
<?$APPLICATION->SetTitle("Список сделок");?>
<h1 class="page_header"><?$APPLICATION->ShowTitle()?></h1>

<div class="tab_form">
    <div class="item active"><span>Открытые</span></div>
    <div class="item"><a href="/admin/deals/cancelled/">Отмененные</a></div>
    <div class="clear"></div>
</div>

<?$APPLICATION->IncludeComponent(
    "bitrix:catalog.filter",
    "admin_deals_filter",
    Array(
        "CACHE_GROUPS" => "Y",
        "CACHE_TIME" => "36000000",
        "CACHE_TYPE" => "A",
        "FIELD_CODE" => array("",""),
        "FILTER_NAME" => "arrFilter",
        "IBLOCK_ID" => "38",
        "IBLOCK_TYPE" => "deals",
        "LIST_HEIGHT" => "5",
        "NUMBER_WIDTH" => "5",
        "PAGER_PARAMS_NAME" => "arrPager",
        "PRICE_CODE" => array(),
        "PROPERTY_CODE" => array("",""),
        "SAVE_IN_SESSION" => "N",
        "TEXT_WIDTH" => "20",
        "LIST_URL" => "/admin/deals/",
        "USER_TYPE" => "ADMIN",
        "STATUS_LIST" => array("all", "open", "close", "cancel")
    )
);?>
<?$APPLICATION->IncludeComponent(
    "bitrix:news.list",
    "admin_deals_list",
    Array(
        "ACTIVE_DATE_FORMAT" => "d.m.Y",
        "ADD_SECTIONS_CHAIN" => "Y",
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
        "DETAIL_URL" => "/client/deals/#ELEMENT_ID#/",
        "DISPLAY_BOTTOM_PAGER" => "Y",
        "DISPLAY_DATE" => "Y",
        "DISPLAY_NAME" => "Y",
        "DISPLAY_PICTURE" => "Y",
        "DISPLAY_PREVIEW_TEXT" => "Y",
        "DISPLAY_TOP_PAGER" => "N",
        "FIELD_CODE" => array("DATE_CREATE",""),
        "FILTER_NAME" => "arrFilter",
        "HIDE_LINK_WHEN_NO_DETAIL" => "N",
        "IBLOCK_ID" => "38",
        "IBLOCK_TYPE" => "deals",
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
        "PROPERTY_CODE" => array("PRICE","VOLUME","CULTURE"),
        "SET_BROWSER_TITLE" => "Y",
        "SET_LAST_MODIFIED" => "N",
        "SET_META_DESCRIPTION" => "Y",
        "SET_META_KEYWORDS" => "Y",
        "SET_STATUS_404" => "N",
        "SET_TITLE" => "N",
        "SHOW_404" => "N",
        "SORT_BY1" => "PROPERTY_STAGE",
        "SORT_BY2" => "ACTIVE_FROM",
        "SORT_ORDER1" => "ASC",
        "SORT_ORDER2" => "ASC"
    )
);?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>