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

if (sizeof($arResult["ITEMS"]) > 0) {
?>
        <?foreach ($arResult["ITEMS"] as $arItem) {?>
        <h3 class="centered"><span class="culture_name"><?=$arResult['CULTURE_LIST'][$arItem['PROPERTIES']['CULTURE']['VALUE']]['NAME'];?></span>, <span class="warehouse_name"><?=$arResult['WAREHOUSES_LIST'][$arItem['PROPERTIES']['WAREHOUSE']['VALUE']]['NAME'];?></span></h3>
        <div class="list_page_rows farmer_offer" <?if(!empty($arParams['PARTNER_ID'])){?>data-pid="<?=$arParams['PARTNER_ID'];?>"<?}?> <?if(!empty($arResult['PARTNER_DATA'])){?>data-pext="<?=$arResult['PARTNER_DATA'];?>"<?}?>>
            <div class="line_area <?=$arItem["PROPERTIES"]['ACTIVE']['VALUE_XML_ID']?> active" data-graphmode="month" data-culture="<?=$arItem['PROPERTIES']['CULTURE']['VALUE'];?>" data-wh="<?=$arItem['PROPERTIES']['WAREHOUSE']['VALUE']?>" <?=(!empty($arParams['VOLUME_VAL']) ? 'data-vol="' . $arParams['VOLUME_VAL'] . '"' : '');?> <?if(!empty($arItem['PROPERTIES']['Q_APPROVED']['VALUE'])){?>data-approved="1"<?}?>>
                <div class="line_additional">
                    <input type="hidden" name="offer" value="<?=$arItem['ID']?>">

                    <?=$arResult['COUNTER_DATA'];?>

                    <div class="prop_area adress_val with_graph">
                        <div class="adress">График товаров и сделок:</div>
                        <div class="val_adress">
                            <div class="graph_area_tab" data-viewmode="year">Год</div>
                            <div class="graph_area_tab" data-viewmode="month">Месяц</div>
                            <div class="graph_area_tab" data-viewmode="week">Неделя</div>
                            <div class="clear"></div>
                            <div class="graph_area" data-viewmode="year"></div>
                            <div class="graph_area" data-viewmode="month"></div>
                            <div class="graph_area" data-viewmode="week">Неделя</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?
    }
    ?>
<?
}
else {
?>
    <div class="no_volume">Ссылка некорректна или устарела.</div>
<?
}