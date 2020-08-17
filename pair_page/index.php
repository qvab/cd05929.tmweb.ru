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
    ){
        $arTemp = explode('?', $arPageParams['URL']);
        if(!empty($arTemp[1])){
            parse_str($arTemp[1], $arTemp);

            //получаем данные предложения
            if(
                isset($arTemp['o'])
                && filter_var($arTemp['o'], FILTER_VALIDATE_INT)
                && isset($arTemp['r'])
                && filter_var($arTemp['r'], FILTER_VALIDATE_INT)
            ){
                CModule::IncludeModule('highloadblock');
                $addResult = addPairByCounterOffer(array('o' => $arTemp['o'], 'r' => $arTemp['r'], 'c' => $arTemp['cid']), (!empty($arPageParams['AUTHOR_UID']) ? $arPageParams['AUTHOR_UID'] : 0));

                //если найдена пара, то отображаем её данные
                if(filter_var($addResult, FILTER_VALIDATE_INT)) {
                    $GLOBALS['arrFilter']['ID'] = $addResult;
                    $success_message = '';

                    $APPLICATION->SetTitle('Создана пара');
                    ?><h1 class="public_h1"><?$APPLICATION->ShowTitle();?></h1><?
                    if(!empty($GLOBALS['ADDED_PAIR'])){
                        $APPLICATION->SetTitle('Принята пара');
                        ?><h3 class="centered">Ожидайте звонка организатора</h3><?
                    }elseif(!empty($_SESSION['showSubTitle'])){
                        unset($_SESSION['showSubTitle']);
                        ?><h3 class="centered">Ожидайте звонка организатора</h3><?
                    }
                    ?><div class="spec_id" data-code="<?=htmlspecialcharsbx($_REQUEST['spec_href'])?>"></div><?
                    $APPLICATION->IncludeComponent(
                        "bitrix:news.list",
                        "pair_list",
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
                            "NEWS_COUNT" => "1",
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
                            "PROPERTY_CODE" => array("VOLUME", "CULTURE",),
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
                            "PROFILE" => 'N',
                            "SUCCESS_MESSAGE" => $success_message,
                            "CLIENT_ID" => (isset($GLOBALS['CLIENT_ID']) ? $GLOBALS['CLIENT_ID'] : 0),
                        )
                    );
                }else{
                    if(trim($addResult) != '') {
                        echo $addResult;
                    }else{
                        echo '<div class="no_volume">Объем, который Вы выбрали, продан. В ближайшее время мы вышлем Вам новое предложение.</div>';
                    }
                }
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
        echo '<div class="no_volume">Объем, который Вы выбрали, продан. В ближайшее время мы вышлем Вам новое предложение.</div>';
        //echo 'Ссылка устарела или неверна';
    }

    require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php');
}else {
    require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');
    header('Location: ' . $GLOBALS['host']);
    exit;
}