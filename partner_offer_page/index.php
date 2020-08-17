<?php

//устанавливаем пустой публичный шаблон
define('PUBLIC_EMPTY_TMPL', 1);

if(
    isset($_REQUEST['spec_href'])
    && mb_strlen($_REQUEST['spec_href']) > 0
){
    require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');

    CModule::IncludeModule('iblock');
    $arPageParams = getStraightHrefDataByCode($_REQUEST['spec_href']);

    $bBadPageParams = false;
    if(
        isset($arPageParams['URL'])
        && $arPageParams['URL']
        && isset($arPageParams['AUTHOR_UID'])
        && filter_var($arPageParams['AUTHOR_UID'], FILTER_VALIDATE_INT)
    ){
        $arTemp = explode('?', $arPageParams['URL']);
        if(!empty($arTemp[1])){
            parse_str($arTemp[1], $arTemp);

            if(
                isset($arTemp['offer_id'])
                && filter_var($arTemp['offer_id'], FILTER_VALIDATE_INT)
                && stripos($arPageParams['URL'], 'partner_offer_page')
            ) {
                $GLOBALS['arrFilter']['ID'] = $arTemp['offer_id'];

                $APPLICATION->SetTitle('Отправить предложение');
                ?><h1 class="public_h1"><?$APPLICATION->ShowTitle();?></h1><?

                $APPLICATION->IncludeComponent(
                    "bitrix:news.list",
                    "offers_list",
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
                        "CACHE_TYPE" => "N",
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
                        "NEWS_COUNT" => "1",
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
                        "PROPERTY_CODE" => array("FARMER", ""),
                        "SET_BROWSER_TITLE" => "N",
                        "SET_LAST_MODIFIED" => "N",
                        "SET_META_DESCRIPTION" => "Y",
                        "SET_META_KEYWORDS" => "Y",
                        "SET_STATUS_404" => "N",
                        "SET_TITLE" => "N",
                        "SHOW_404" => "N",
                        "SORT_BY1" => "ACTIVE_FROM",
                        "SORT_BY2" => "SORT",
                        "SORT_ORDER1" => "DESC",
                        "SORT_ORDER2" => "ASC",
                        "VOLUME_VAL" => 0,
                        "SENDED_BY_PARTNER" => (!empty($arPageParams['AUTHOR_UID']) ? $arPageParams['AUTHOR_UID'] : 0),
                        "PARTNER_PAGE" => "Y",
                        "PARTNER_ID" => $arPageParams['AUTHOR_UID'],
                    )
                );
            }else{
                $bBadPageParams = true;
            }
        }else{
            $bBadPageParams = true;
        }
    }else{
        $bBadPageParams = true;
    }

    //вывод сообщения
    if($bBadPageParams){
        echo '<div class="no_volume">Ссылка некорректна или устарела.</div>';
        //echo 'Ссылка устарела или неверна';
    }

    require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php');
}else {
    require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');
    header('Location: ' . $GLOBALS['host']);
    exit;
}