<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
if (!empty($arResult)) {
    $size = sizeof($arResult);
?>
    <div class="tab_form">
        <?
        foreach ($arResult as $k => $arItem) {
        ?>
        <div class="item<? if ($arItem['SELECTED']) { ?> active<? } if ($k == $size - 1) { ?> last<? } ?>">
            <?
            if ($arItem['SELECTED']) {
            ?>
                <span><?=$arItem["TEXT"]?></span>
            <?
            }
            else {
            ?>
                <a href="<?=$arItem["LINK"]?>"><?=$arItem["TEXT"]?></a>
            <?
            }
            ?>
        </div>
        <?
        }
        ?>
        <div class="clear"></div>
    </div>
<?
}
?>