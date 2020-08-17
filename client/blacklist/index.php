<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");?>
<?$APPLICATION->SetTitle("Черный список");?>
    <h1 class="page_header"><?$APPLICATION->ShowTitle()?></h1>

<?
$APPLICATION->IncludeComponent(
    "bitrix:catalog.filter",
    "client_blacklist_filter",
    Array(
        "CACHE_GROUPS" => "Y",
        "CACHE_TIME" => "36000000",
        "CACHE_TYPE" => "N",
        "FIELD_CODE" => array("",""),
        "FILTER_NAME" => "arrFilter",
        "IBLOCK_ID" => rrsIblock::getIBlockId('client_black_list'),
        "IBLOCK_TYPE" => "client",
        "LIST_HEIGHT" => "5",
        "NUMBER_WIDTH" => "5",
        "PAGER_PARAMS_NAME" => "arrPager",
        "PRICE_CODE" => array(),
        "PROPERTY_CODE" => array("",""),
        "SAVE_IN_SESSION" => "N",
        "TEXT_WIDTH" => "20",
        "LIST_URL" => "/client/blacklist/",
        "USER_TYPE" => "CLIENT",
        "STATUS_LIST" => array("new")
    )
);?>
<?




global $USER;
$user_id = $USER->GetID();

//проверка прав на принятие Предложения
$arResult['USER_RIGHTS'] = client::checkRights('counter_request', $user_id);
//для текущего пользователя получаем количество доступных принятий
if(isset($arResult['USER_RIGHTS']['REQUEST_RIGHT'])
    && $arResult['USER_RIGHTS']['REQUEST_RIGHT'] == 'Y'
){
    $arResult['USER_CON_REQ_OPENS_LIMIT'] = client::openerCountGet($user_id);
}

$counter_request_right = 'n';
if(isset($arResult['USER_RIGHTS']['REQUEST_RIGHT'])){
    if($arResult['USER_RIGHTS']['REQUEST_RIGHT'] == 'Y'){
        //у пользователя есть права на принятие ВП
        $counter_request_right = 'y';
    }elseif($arResult['USER_RIGHTS']['REQUEST_RIGHT'] == 'LIM'){
        //у пользователя закончились принятия ВП
        $counter_request_right = 'l';
    }
}
$user_email = $USER->GetEmail();
if(checkEmailFromPhone($user_email)){
    $user_email = '';
}
$show_del_button = 0;
if($counter_request_right == 'l'){
    ?>
    <div class="opening_limit_ended"><div class="result_message"></div><span class="limit_val_no">Лимит принятий исчерпан</span>, <a href="javascript: void(0);" onclick="showCounterRequestFeedbackFormBL('<?=$user_email;?>', <?=rrsIblock::getConst('min_counter_req_limit');?>, <?=rrsIblock::getConst('counter_req_price');?>);">подайте заявку</a> на пополнение</div>
<?}elseif($counter_request_right == 'y'
    && isset($arResult['USER_CON_REQ_OPENS_LIMIT'])
    && intval($arResult['USER_CON_REQ_OPENS_LIMIT']) > 0
){
    $show_del_button = 1;
    ?>
    <div class="opening_limit_ended" style="display: none"><div class="result_message"></div><span class="limit_val_no">Лимит принятий исчерпан</span>, <a href="javascript: void(0);" onclick="showCounterRequestFeedbackFormBL('<?=$user_email;?>', <?=rrsIblock::getConst('min_counter_req_limit');?>, <?=rrsIblock::getConst('counter_req_price');?>);">подайте заявку</a> на пополнение</div>
    <div class="opening_limit_available"><div class="result_message"></div><a href="/client/profile/counter_limits_history/">Доступно принятий</a>:<div class="limit_val"><?=$arResult['USER_CON_REQ_OPENS_LIMIT'];?></div><a href="javascript: void(0);" onclick="showCounterRequestFeedbackFormBL('<?=$user_email;?>', <?=rrsIblock::getConst('min_counter_req_limit');?>, <?=rrsIblock::getConst('counter_req_price');?>);">(подать заявку)</a></div>
    <?
}

$APPLICATION->IncludeComponent(
    "bitrix:news.list",
    "blacklist_list_client",
    Array(
        "ACTIVE_DATE_FORMAT" => "d.m.Y",
        "ADD_SECTIONS_CHAIN" => "Y",
        "AJAX_MODE" => "N",
        "AJAX_OPTION_ADDITIONAL" => "",
        "AJAX_OPTION_HISTORY" => "N",
        "AJAX_OPTION_JUMP" => "N",
        "AJAX_OPTION_STYLE" => "Y",
        "CACHE_FILTER" => "Y",
        "CACHE_GROUPS" => "Y",
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
        "IBLOCK_ID" => rrsIblock::getIBlockId('client_black_list'),
        "IBLOCK_TYPE" => "client",
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
        "PROPERTY_CODE" => array("USER","OPPONENT","DEAL","ANSWERS","TEXT",""),
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
        "USER_TYPE" => "CLIENT",
        "SHOW_DEL_BUTTON" => $show_del_button,
    )
);?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>