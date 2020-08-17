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
<?
if (sizeof($arResult["ITEMS"]) > 0) {
?>
        <?
        foreach($arResult["ITEMS"] as $arItem) {
        ?>
            <div>
                <div class="crow radio_group clear">
                    <div class="cname">
                        <?=$arItem['NAME']?><br/>
                        <span class="comment_sp">(время формирования: <?=$arItem['ACTIVE_FROM']?>)</span>
                    </div>
                    <div class="download_block">
                        <a href="<?=$arItem['PROPERTIES']['REPORT']['VALUE']?>"><div class="download_xls"></div></a>
                    </div>
                </div>
            </div>
        <?
        }
        ?>
        <?if($arParams["DISPLAY_BOTTOM_PAGER"]):?>
            <br /><?=$arResult["NAV_STRING"]?>
        <?endif;?>
<?
}
$templateData = $arResult;
?>