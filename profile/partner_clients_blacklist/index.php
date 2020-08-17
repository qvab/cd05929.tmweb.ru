<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

if (!$USER->IsAuthorized()
    || !isset($_GET['uid'])
    || !is_numeric($_GET['uid'])
) {
    LocalRedirect('/');
    exit;
}

//если смотрим не профиль фермера, то выходим
$group = getUserType($_GET['uid']);
if ($group['TYPE'] != 'f') {
    LocalRedirect('/');
    exit;
}

//проверяем текущего пользователя, если это не организатор, то выходим
$a_group = getUserType($USER->getID());
if ($a_group['TYPE'] != 'p') {
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
        'TYPE'  => $group['TYPE'],
        'TAB'   => 'partner_clients_blacklist'
    )
);?>
<?
$GLOBALS['arCLBLFilter'] = array('PROPERTY_PARTNER_ID' => $USER->getID());
?>
<?
$APPLICATION->IncludeComponent(
    "bitrix:news.list",
    "partner_clients_ap",
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
        "FIELD_CODE" => array("NAME","DATE_CREATE"),
        "FILTER_NAME" => "arCLBLFilter",
        "HIDE_LINK_WHEN_NO_DETAIL" => "N",
        "IBLOCK_ID" => rrsIblock::getIBlockId('client_partner_link'),
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
        "PROPERTY_CODE" => array("USER_ID","PARTNER_ID",""),
        "SET_BROWSER_TITLE" => "N",
        "SET_LAST_MODIFIED" => "N",
        "SET_META_DESCRIPTION" => "N",
        "SET_META_KEYWORDS" => "N",
        "SET_STATUS_404" => "N",
        "SET_TITLE" => "N",
        "SHOW_404" => "N",
        "SORT_BY1" => "DATE_CREATE",
        "SORT_ORDER1" => "DESC",
        "AUTO_READ" => true,
        "U_ID"  => $_GET['uid'],
    )
);?>