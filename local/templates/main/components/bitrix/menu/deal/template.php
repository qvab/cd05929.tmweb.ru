<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<div class="tab_form deals">
    <div class="tab_form_inner" id="tab_form_inner">
        <?
        $size = sizeof($arResult);
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
</div>