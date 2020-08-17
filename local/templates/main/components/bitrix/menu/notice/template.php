<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
if (!empty($arResult)) {
?>
    <div class="tab_form">
        <div class="tab_form_inner" id="tab_form_inner">
            <?
            $my_c = count($arResult) - 1;
            foreach ($arResult as $cur_pos => $arItem) {
                $count = '';
                if(isset($arItem['COUNT'])){
                    $count=' ('.$arItem['COUNT'].')';
                }
            ?>
                <div class="item<? if ($arItem['SELECTED']) { ?> active<? } if($cur_pos == $my_c){?> last<?}?>">
                    <? if ($arItem['SELECTED']) { ?>
                        <span><?=$arItem["TEXT"]?><?=$count?></span>
                    <? } else { ?>
                        <a href="<?=$arItem["LINK"]?>"><?=$arItem["TEXT"]?><?=$count?></a>
                    <? } ?>
                </div>
            <?
            }
            ?>
            <div class="clear"></div>
        </div>
    </div>
<?
}
?>