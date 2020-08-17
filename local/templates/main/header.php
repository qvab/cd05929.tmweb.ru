<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
require_once($_SERVER["DOCUMENT_ROOT"] . "/include/permission.php");

IncludeTemplateLangFile(__FILE__);
?>
<!DOCTYPE HTML>
<html>
    <head>
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
        <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
        <link rel="manifest" href="/site.webmanifest">
        <link rel="mask-icon" href="/safari-pinned-tab.svg" color="#5bbad5">
        <meta name="msapplication-TileColor" content="#00aba9">
        <meta name="theme-color" content="#ffffff">

        <?
        //add css & js
        $assetInst = \Bitrix\Main\Page\Asset::getInstance();
        $assetInst->addCss(SITE_TEMPLATE_PATH . '/css/select2.css');
        $assetInst->addCss(SITE_TEMPLATE_PATH . '/css/extra_style.css');
        $assetInst->addCss(SITE_TEMPLATE_PATH . '/css/jquery-ui.css');

        $assetInst->addJs(SITE_TEMPLATE_PATH . '/js/jquery-1.11.1.js');
        $assetInst->addJs(SITE_TEMPLATE_PATH . '/js/jquery-ui.js');
        $assetInst->addJs(SITE_TEMPLATE_PATH . '/js/select2.js');
        $assetInst->addJs(SITE_TEMPLATE_PATH . '/js/select2_lang.js');
        $assetInst->addJs(SITE_TEMPLATE_PATH . '/js/script_func.js');
        $assetInst->addJs(SITE_TEMPLATE_PATH . '/js/script.js');
        $assetInst->addJs(SITE_TEMPLATE_PATH . '/js/highcharts.js');
        $assetInst->addJs(SITE_TEMPLATE_PATH . '/js/jquery.inputmask.js');

        $APPLICATION->SetPageProperty("robots", "noindex, nofollow");

        $APPLICATION->ShowHead();?>

        <title><?$APPLICATION->ShowTitle()?></title>
        <!-- Yandex.Metrika counter -->
        <script type="text/javascript" >
            (function(m,e,t,r,i,k,a){m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
                m[i].l=1*new Date();k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)})
            (window, document, "script", "https://mc.yandex.ru/metrika/tag.js", "ym");
            ym(56488642, "init", {
                clickmap:true,
                trackLinks:true,
                accurateTrackBounce:true,
                webvisor:true
            });
        </script>
        <noscript><div><img src="https://mc.yandex.ru/watch/56488642" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
        <!-- /Yandex.Metrika counter -->
    </head>
    <body>

        <div id="id_check"></div>

	    <div id="bitrix_panel_wrapper"><?$APPLICATION->ShowPanel();?></div>
        <div id="page_wrapper">
	        <div id="header">

                <div id="header_title">
                    <a title="На главную" href="/">
                        <div id="logo" class="color white"></div>
                        <span class="val">АГРОХЕЛПЕР</span>
                    </a>
                </div>

                <a href="javascript: void(0);" class="mobile_close" onclick="showHideMenu();"></a>

                <?/*<div class="main_menu">
                    <div class="item_area mess_avail">
                        <a href="#"><div class="ico"></div>Центр уведомлений</a>
                        <img class="arw" src="<?=SITE_TEMPLATE_PATH?>/images/item_arw.png" />
                    </div>
                    <div class="item_area active req">
                        <a href="#"><div class="ico"></div>Запросы</a>
                        <img class="arw" src="<?=SITE_TEMPLATE_PATH?>/images/item_arw.png" />
                    </div>
                    <div class="item_area deal">
                        <a href="#"><div class="ico"></div>Сделки</a>
                        <img class="arw" src="<?=SITE_TEMPLATE_PATH?>/images/item_arw.png" />
                    </div>
                    <div class="item_area tran">
                        <a href="#"><div class="ico"></div>Транспортные компании</a>
                        <img class="arw" src="<?=SITE_TEMPLATE_PATH?>/images/item_arw.png" />
                    </div>
                </div>*/?>

                <?$APPLICATION->IncludeComponent(
                    "bitrix:menu",
                    "top", array(
                        "ROOT_MENU_TYPE" => "top",
                        "MENU_CACHE_TYPE" => "A",
                        "MENU_CACHE_TIME" => "36000000",
                        "MENU_CACHE_USE_GROUPS" => "Y",
                        "MENU_CACHE_GET_VARS" => array(),
                        "MAX_LEVEL" => "1",
                        "CHILD_MENU_TYPE" => "top",
                        "USE_EXT" => "N",
                        "DELAY" => "N",
                        "ALLOW_MULTI_SELECT" => "N"
                    ),
                    false
                );?>

                <?if($USER->IsAuthorized()){?>
                    <?$APPLICATION->IncludeComponent(
                        "rarus:bottom_menu", "",
                        array(
                            "PERM_LEVEL" => $GLOBALS['rrs_user_perm_level'],
                        ),
                        false
                    );?>
                <?}else{?>
                    <div class="bot_menu">
                        <div class="item_area help">
                            <a href="#"><div class="ico"></div>Помощь</a>
                            <div class="arw"></div>
                        </div>
                        <div class="item_area quit">
                            <a href="/login/"><div class="ico"></div>Вход</a>
                            <div class="arw"></div>
                        </div>
                    </div>
                <?}?>

                <?/*
                <div id="header_auth">
                    <?$APPLICATION->IncludeComponent("bitrix:system.auth.form", "info", array(
                        "REGISTER_URL" => SITE_DIR."login/",
                        "PROFILE_URL" => SITE_DIR."personal/",
                        "SHOW_ERRORS" => "N"
                        ),
                        false,
                        Array()
                    );?>
                </div>

	            <div id="main_menu">
                    $APPLICATION->IncludeComponent("bitrix:menu", "horizontal_multilevel", array(
                        "ROOT_MENU_TYPE" => "top",
                        "MENU_CACHE_TYPE" => "A",
                        "MENU_CACHE_TIME" => "36000000",
                        "MENU_CACHE_USE_GROUPS" => "N",
                        "MENU_CACHE_GET_VARS" => array(),
                        "MAX_LEVEL" => "1",
                        "CHILD_MENU_TYPE" => "top",
                        "USE_EXT" => "Y",
                        "DELAY" => "N",
                        "ALLOW_MULTI_SELECT" => "N"
                        ),
                        false
                    );
                </div>
                */?>

                <div class="bottom_mob_part"></div>
	        </div>

            <div id="page_body" class="content">

                <?$arCurDir = explode("/", $APPLICATION->GetCurDir());?>

                <?/*if($APPLICATION->GetCurDir() != '/'):?>
                    <h1 class="page_header"><?$APPLICATION->ShowTitle()?></h1>
                <?endif;*/?>