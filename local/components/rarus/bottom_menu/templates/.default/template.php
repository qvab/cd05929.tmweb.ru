<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
$this->setFrameMode(true);
?>
<div class="bot_menu">

    <?if(!empty($arResult['URL_PROFILE'])):?>
        <div class="item_area settings <?=$arResult['bActiveProfile'] ? 'active' : ''?>">
            <a href="<?=$arResult['URL_PROFILE']?>"><div class="ico"></div>Профиль</a>
            <img class="arw" src="<?=SITE_TEMPLATE_PATH?>/images/item_arw.png">
            <div class="arw"></div>
        </div>
    <?endif;?>

    <?if(!empty($arResult['URL_HELP'])):?>
        <div class="item_area help <?=$arResult['bActiveHelp'] ? 'active' : ''?>">
            <a href="<?=$arResult['URL_HELP']?>"><div class="ico"></div>Помощь</a>
            <img class="arw" src="<?=SITE_TEMPLATE_PATH?>/images/item_arw.png">
            <div class="arw"></div>
        </div>
    <?endif;?>

    <div class="item_area quit">
        <a href="/?logout=yes"><div class="ico"></div>Выйти</a>
        <div class="arw"></div>
    </div>
</div>
