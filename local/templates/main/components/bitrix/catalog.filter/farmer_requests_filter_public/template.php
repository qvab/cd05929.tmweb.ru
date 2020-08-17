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
$filter = false;
?>
<div class="tab_form">
    <form action="" method="get" class="select_item">
        <input type="hidden" name="uid" value="<?=$arParams['FARMER_ID'];?>" />
        <?
        if (is_array($arResult['CULTURE_LIST']) && sizeof($arResult['CULTURE_LIST']) > 0) {
            $filter = true;
            ?>
            <div class="row fblock_cl">
                <select <? if (sizeof($arResult['CULTURE_LIST']) > 4) { ?>data-search="y"<? } ?> name="culture" placeholder="Выберите культуру">
                    <option value="0">Все культуры</option>
                    <?
                    foreach ($arResult['CULTURE_LIST'] as $cur_id => $arItem) {
                    ?>
                        <option value="<?=$arItem['ID']?>" <? if ($arItem['ID'] == $_GET['culture']) { ?>selected="selected"<? } ?> ><?=$arItem['NAME']?></option>
                    <?
                    }
                    ?>
                </select>
            </div>
        <?
        }

        if (is_array($arResult['WH_LIST']) && sizeof($arResult['WH_LIST']) > 0) {
            $filter = true;
            ?>
            <div class="row fblock_wh">
                <select <? if (sizeof($arResult['WH_LIST']) > 4) { ?>data-search="y"<? } ?> name="wh" placeholder="Выберите склад">
                    <option value="0">Все склады</option>
                    <?
                    foreach ($arResult['WH_LIST'] as $cur_id => $arItem) {
                    ?>
                        <option value="<?=$arItem['ID']?>" <? if ($arItem['ID'] == $_GET['wh']) { ?>selected="selected"<? } ?> ><?=$arItem['NAME']?> (<?=$arItem['ADDRESS']?>)</option>
                    <?
                    }
                    ?>
                </select>
            </div>
        <?
        }
        ?>
        <div class="clear"></div>

        <?
        if ($filter) {
        ?>
            <div class="row fbtn_submit">
                <input name="" class="submit-btn left" value="Применить" type="submit">
                <div class="clear"></div>
            </div>

            <div class="row fbtn_cancel">
                <a href="<?=$arParams['LIST_URL']?>" class="cancel_filter">Сбросить</a>
                <div class="clear"></div>
            </div>

            <div class="clear"></div>
        <?
        }
        ?>
    </form>
</div>