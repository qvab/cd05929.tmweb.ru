<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
define('PUBLIC_AREA', 'Y');
require_once($_SERVER["DOCUMENT_ROOT"] . "/include/permission.php"); //redirect to user folder if authorized

IncludeTemplateLangFile(__FILE__);?>
<!DOCTYPE HTML>
<html>
    <head>
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, shrink-to-fit=no">
        <meta property="og:title" content="АГРОХЕЛПЕР" />
        <meta property="og:image" content="/local/templates/main_public/images/img_check_field.jpg">
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
        $assetInst->addCss(SITE_TEMPLATE_PATH . '/css/owl/owl.carousel.min.css');
        $assetInst->addCss(SITE_TEMPLATE_PATH . '/css/owl/owl.theme.default.min.css');

        $assetInst->addJs(SITE_TEMPLATE_PATH . '/js/jquery-1.11.1.js');
        $assetInst->addJs(SITE_TEMPLATE_PATH . '/js/select2.js');
        $assetInst->addJs(SITE_TEMPLATE_PATH . '/js/select2_lang.js');
        $assetInst->addJs('/local/templates/main/js/script_func.js');
        $assetInst->addJs(SITE_TEMPLATE_PATH . '/js/script.js');
        $assetInst->addJs(SITE_TEMPLATE_PATH . '/js/jquery.inputmask.js');
        $assetInst->addJs(SITE_TEMPLATE_PATH . '/js/owl.carousel.min.js');

        $APPLICATION->SetPageProperty("robots", "noindex, nofollow");

        CJSCore::Init(array("fx"));
        $APPLICATION->ShowHead();
        $body_class = '';
        if(isset($_GET['change_password']) && $_GET['change_password'] == 'yes'
            || (isset($_GET['reg']) && trim($_GET['reg']) != ''
                && isset($_GET['hash']) && trim($_GET['hash']) != ''
            )
            || (isset($_GET['backurl'])
                &&(
                    (mb_substr($_GET['backurl'],0, 25) == '/client/exclusive_offers/')
                    || (mb_substr($_GET['backurl'],0, 24) == '/farmer/request/counter/')
                    || (mb_substr($_GET['backurl'],0, 14) == '/farmer/offer/')
                )
            )
        ){
            $body_class .= ' no_reg';
        }
        ?>

        <link href="https://fonts.googleapis.com/css?family=Roboto+Slab:300,400,700&amp;subset=cyrillic,cyrillic-ext" rel="stylesheet">

        <title><?$APPLICATION->ShowTitle()?></title>
        <?/*<script src="https://www.google.com/recaptcha/api.js" async defer></script>*/?>
        <script src="https://www.google.com/recaptcha/api.js?onload=recaptchaCallback&render=explicit" async defer></script>
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
    <body class="<?=$body_class;?>">

        <div id="id_check"></div>

	    <div id="bitrix_panel_wrapper"><?$APPLICATION->ShowPanel();?></div>

        <div id="page_wrapper">
            <div id="header">

                <div class="head-blocks-container">

                    <!-- Верхний блок -->
                    <div class="head-top-block">
                        <!-- Логотип -->
                        <div id="header_title">
                            <a title="На главную" href="/">
                                <div id="logo" class="color white"></div>
                                <span>Агрохелпер</span>
                            </a>
                        </div>

                        <!-- Заголовок и кнопки -->
                        <!--<div class="page_sub_title">Агрегатор сделок</div>-->
                        <div class="href_row">
                            <a onclick="showPublicReg(this);" href="javascript:void(0);" data-action="register" class="agro_button">Регистрация</a>
                        </div>
                        <div class="href_row sec">
                            <a onclick="showPublicAuth(this);" href="javascript:void(0);" data-action="auth" class="agro_button">Войти</a>
                        </div>
                    </div>

                    <!-- Ссылка "Стать частью команды" -->
                    <? /*<a onclick="showPublicOtherForm('.become_agrohelper_page', this);" href="javascript:void(0);" data-action="become_agrohelper" id="become_agrohelper_href">Стать частью команды АГРОХЕЛПЕР</a>*/?>
                    <?//<a id="become_agrohelper_href" href="/becomeagrohelper/">Стать партнером АГРОХЕЛПЕР</a>?>

                    <!-- Нижний блок (стата) -->
                    <?$arSysStat = log::getLastUserActivityLog();?>
                    <?if($arSysStat['ID'] > 0):?>
                        <div class="head-bottom-block">
                            <div class="site-stat-block">
                                <div class="stat-title">Активных пользователей</div>
                                <div class="stat-row">Покупателей: <?=$arSysStat['UF_CLIENT_NUM']?></div>
                                <div class="stat-row">Поставщиков: <?=$arSysStat['UF_FARMER_NUM']?></div>
                            </div>
                        </div>
                    <?endif;?>

                </div>

            </div>
            <div id="page_body" class="content<?if(
                    isset($_REQUEST['backurl']) && trim($_REQUEST['backurl']) != ''
                    || isset($_REQUEST['change_password']) && $_REQUEST['change_password'] == 'yes'
                    || isset($_REQUEST['reg']) && $_REQUEST['reg'] == 'yes'
                ){?> no_auth<?}?>">

                <?/*if($APPLICATION->GetCurDir() != '/'):?>
                    <h1 class="page_header"><?$APPLICATION->ShowTitle()?></h1>
                <?endif;*/?>

                <div id="public_content" class="public_form active">