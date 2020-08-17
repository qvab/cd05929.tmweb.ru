<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

if (!$USER->IsAuthorized()
    || !isset($_GET['uid'])
    || !is_numeric($_GET['uid'])
) {
    LocalRedirect('/');
    exit;
}
//проверяем текущего пользователя, если это не организатор, то выходим
$group = getUserType($USER->getID());
if ($group['TYPE'] != 'p') {
    LocalRedirect('/');
    exit;
}

//если мы не в профиле клиента, то выходим
$group_client = getUserType($_GET['uid']);
if ($group_client['TYPE'] != 'c') {
    LocalRedirect('/');
    exit;
}

//если клиент не принадлежит организатору, то выходим
$partner_id = client::getLinkedPartnerVerified($_GET['uid']);
if($partner_id != $USER->getID()){
    LocalRedirect('/');
    exit;
}
?>
    <h1><?$APPLICATION->ShowTitle();?></h1>
<?
$show_menu = $APPLICATION->IncludeComponent(
    'rarus:public_profile_menu',
    '',
    Array(
        'U_ID'  => $_GET['uid'],
        'TYPE'  => $group_client['TYPE'],
        'TAB'   => 'blacklist'
    )
);
?>
<?
$GLOBALS['arBLFilter'] = array('PROPERTY_USER_ID' => $_GET['uid']);
?>
<?
$APPLICATION->IncludeComponent(
    "bitrix:news.list",
    "blacklist_ap",
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
        "CACHE_TYPE" => "N",
        "CHECK_DATES" => "Y",
        "DETAIL_URL" => "",
        "DISPLAY_BOTTOM_PAGER" => "Y",
        "DISPLAY_DATE" => "Y",
        "DISPLAY_NAME" => "Y",
        "DISPLAY_PICTURE" => "N",
        "DISPLAY_PREVIEW_TEXT" => "N",
        "DISPLAY_TOP_PAGER" => "N",
        "FIELD_CODE" => array("NAME"),
        "FILTER_NAME" => "arBLFilter",
        "HIDE_LINK_WHEN_NO_DETAIL" => "N",
        "IBLOCK_ID" => rrsIblock::getIBlockId('blacklist_ap'),
        "IBLOCK_TYPE" => "client",
        "INCLUDE_IBLOCK_INTO_CHAIN" => "N",
        "INCLUDE_SUBSECTIONS" => "Y",
        "MESSAGE_404" => "",
        "NEWS_COUNT" => 10,
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
        "PROPERTY_CODE" => array("USER_ID","FARMER_ID","PARTNER_ID",""),
        "SET_BROWSER_TITLE" => "N",
        "SET_LAST_MODIFIED" => "N",
        "SET_META_DESCRIPTION" => "N",
        "SET_META_KEYWORDS" => "N",
        "SET_STATUS_404" => "N",
        "SET_TITLE" => "N",
        "SHOW_404" => "N",
        "SORT_BY1" => "DATE_CREATE",
        "SORT_ORDER1" => "DESC",
        "AUTO_READ" => true
    )
);?>