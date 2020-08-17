<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
if (!empty($arResult)) {
?>
    <div class="main_menu tot<?=count($arResult);?>">
        <?
        foreach ($arResult as $cur_pos => $arItem) {
        ?>
            <div class="item_area<?if($cur_pos == 0){?> first<?}?><? if ($arItem['SELECTED']) { ?> active<? } ?> <?=$arItem['PARAMS']['class']?>">
                <a href="<?=$arItem["LINK"]?>"><div class="ico"></div><?=$arItem["TEXT"]?></a>
                <img class="arw" src="<?=SITE_TEMPLATE_PATH?>/images/item_arw.png" />
            </div>
        <?
        }
        ?>
        <div class="item_area mob_menu_item last">
            <a href="javascript: void(0);" onclick="showHideMenu();"><div class="ico"></div></a>
        </div>
    </div>
<?
}
?>