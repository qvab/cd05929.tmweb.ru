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
        'TAB' => 'pair'
    )
);
if (!$linked_user) {
    LocalRedirect('/profile/?uid=' . $_GET['uid']);
    exit;
}?>
<?$APPLICATION->IncludeComponent(
    "bitrix:catalog.filter",
    "pair_filter_farmer",
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
        "LIST_URL" => "/profile/pair/?uid=".$_GET['uid'],
        "USER_TYPE" => "FARMER",
        "PROFILE" => 'Y',
        "STATUS_LIST" => array("new")
    )
);?>
<?
//получение у кого в черном списке состоит текущий пользователь для дополнительнйо фильтрации вывода
global $USER;
$opp_filter = farmer::getBlackListWhereOpponent($_GET['uid']);
if(count($opp_filter) > 0){
    $GLOBALS['arrFilter']['!PROPERTY_CLIENT'] = $opp_filter;
}

$APPLICATION->IncludeComponent(
    "bitrix:news.list",
    "agent_pair_list",
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
        "CACHE_TYPE" => "A",
        "CHECK_DATES" => "Y",
        "DISPLAY_BOTTOM_PAGER" => "Y",
        "DISPLAY_DATE" => "Y",
        "DISPLAY_NAME" => "Y",
        "DISPLAY_PICTURE" => "Y",
        "DISPLAY_PREVIEW_TEXT" => "Y",
        "DISPLAY_TOP_PAGER" => "N",
        "FIELD_CODE" => array(
            "DATE_CREATE",
        ),
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
        "PROPERTY_CODE" => array("VOLUME","CULTURE",),
        "SET_BROWSER_TITLE" => "Y",
        "SET_LAST_MODIFIED" => "N",
        "SET_META_DESCRIPTION" => "Y",
        "SET_META_KEYWORDS" => "Y",
        "SET_STATUS_404" => "N",
        "SET_TITLE" => "N",
        "SHOW_404" => "N",
        "SORT_BY1" => "DATE_CREATE",
        "SORT_BY2" => "PROPERTY_PAIR_STATUS",
        "SORT_ORDER1" => "DESC",
        "SORT_ORDER2" => "ASC",
        "USER_TYPE" => "PARTNER",
    )
);
?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>