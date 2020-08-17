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
    <div class="list_page_rows requests">
        <?
        foreach($arResult["ITEMS"] as $arItem) {
        ?>
            <div class="line_area <?=$arItem['PROPERTIES']['STATUS']['VALUE_XML_ID']?>" id="<?=$this->GetEditAreaId($arItem['ID']);?>">
                <div class="line_inner">

                    <div class="name"><?=current($arItem['DISPLAY_PROPERTIES']['CULTURE']['LINK_ELEMENT_VALUE'])['NAME']?></div>
                    <div class="id_date">#<?=$arItem['ID']?> от <?=reset(explode(' ', $arItem['DATE_CREATE']));?></div>
                    <div class="tons"><?=number_format($arItem["PROPERTIES"]['VOLUME']['VALUE'], 0, ',', ' ')?> т</div>
                    <div class="price"><?=number_format($arItem["PROPERTIES"]['ACC_PRICE_CSM']['VALUE'], 0, ',', ' ')?> руб/т</div>
                    <div class="wh_name">
                        <b>Статус: <?=$arItem['PROPERTIES']['STAGE']['VALUE']?></b>
                    </div>
                    <div class="clear"></div>
                </div>

                <form action="" method="post" class="line_additional" <? if ($_GET['id'] == $arItem['ID']) { ?> style="display: block;"<? } ?>>
                    <input type="hidden" name="deal" value="<?=$arItem['ID']?>">

                    <div class="prop_area adress_val">
                        <div class="adress">Текущий статус сделки:</div>
                        <div class="val_adress">
                            <?=$arItem['PROPERTIES']['STAGE']['VALUE']?>
                        </div>
                    </div>

                    <?
                    if (intval($arItem['PROPERTIES']['CLIENT']['VALUE']) > 0 && isset($arResult['CLIENT_LIST'][$arItem['PROPERTIES']['CLIENT']['VALUE']])) {
                        $arClient = $arResult['CLIENT_LIST'][$arItem['PROPERTIES']['CLIENT']['VALUE']];
                        ?>
                        <div class="prop_area adress_val">
                            <div class="adress">Покупатель:</div>
                            <div class="val_adress">
                                <a href="/profile/?uid=<?=$arClient['PROPERTY_USER_VALUE']?>"><?=$arClient['COMPANY']?></a>
                            </div>
                        </div>
                    <?
                    }

                    if (intval($arItem['PROPERTIES']['FARMER']['VALUE']) > 0 && isset($arResult['FARMER_LIST'][$arItem['PROPERTIES']['FARMER']['VALUE']])) {
                        $arFarmer = $arResult['FARMER_LIST'][$arItem['PROPERTIES']['FARMER']['VALUE']];
                        ?>
                        <div class="prop_area adress_val">
                            <div class="adress">Поставщик:</div>
                            <div class="val_adress">
                                <a href="/profile/?uid=<?=$arFarmer['PROPERTY_USER_VALUE']?>"><?=$arFarmer['COMPANY']?></a>
                            </div>
                        </div>
                    <?
                    }

                    if (intval($arItem['PROPERTIES']['PARTNER']['VALUE']) > 0 && isset($arResult['PARTNER_LIST'][$arItem['PROPERTIES']['PARTNER']['VALUE']])) {
                        $arPartner = $arResult['PARTNER_LIST'][$arItem['PROPERTIES']['PARTNER']['VALUE']];
                        ?>
                        <div class="prop_area adress_val">
                            <div class="adress">Организатор:</div>
                            <div class="val_adress">
                                <a href="/profile/?uid=<?=$arPartner['PROPERTY_USER_VALUE']?>"><?=$arPartner['COMPANY']?></a>
                            </div>
                        </div>
                    <?
                    }

                    if (intval($arItem['PROPERTIES']['TRANSPORT']['VALUE']) > 0 && isset($arResult['TRANSPORT_LIST'][$arItem['PROPERTIES']['TRANSPORT']['VALUE']])) {
                        $arPartner = $arResult['TRANSPORT_LIST'][$arItem['PROPERTIES']['TRANSPORT']['VALUE']];
                        ?>
                        <div class="prop_area adress_val">
                            <div class="adress">Перевозчик:</div>
                            <div class="val_adress">
                                <a href="/profile/?uid=<?=$arPartner['PROPERTY_USER_VALUE']?>"><?=$arPartner['COMPANY']?></a>
                            </div>
                        </div>
                    <?
                    }
                    ?>
                    <div class="prop_area adress_val">
                        <div class="adress">Восстановить сделку:</div>
                        <div class="val_adress">
                            <input type="submit" class="submit-btn" name="accept" value="Выполнить">
                        </div>
                    </div>
                </form>
            </div>
        <?
        }
        ?>

        <?if($arParams["DISPLAY_BOTTOM_PAGER"]):?>
            <br /><?=$arResult["NAV_STRING"]?>
        <?endif;?>
    </div>
<?
}
else {
?>
    <div class="list_page_rows requests no-item">
        Ни одной сделки не найдено
    </div>
<?
}
?>