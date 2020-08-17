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

$sSendGrapTemplate = popupTemplates::getOfferGraphTemplate();
if($sSendGrapTemplate != ''){?>
    <div class="graph_template_data" style="display: none;"><?=$sSendGrapTemplate;?></div>
<?}
?>
<div class="add_limit_line ended"><div class="result_message"></div>Исчерпан лимит создания товаров (лимит товаров: <span class="val"></span>), <a href="javascript: void(0);">подайте заявку</a> на пополнение</div>
<div class="add_limit_line available"><div class="result_message"></div>Текущий лимит товаров: <span class="val"></span> (доступно товаров: <span class="remains"></span>).<br/>Вы можете <a href="javascript: void(0);">подать заявку</a> на пополнение</div>
<?
if (sizeof($arResult["ITEMS"]) > 0) {

    if(is_numeric($arResult['NAV_RESULT']->NavRecordCount) && $arResult['NAV_RESULT']->NavRecordCount > 10 || count($arResult["ITEMS"]) > 10)
    {?>
        <div class="additional_href_data">
            <a href="/farmer/offer/new/">+ Добавить товар</a>
        </div>
    <?}
?>
    <div class="list_page_rows farmer_offer">
        <?
        foreach ($arResult["ITEMS"] as $arItem) {
        ?>
            <a name="<?=$arItem['ID']?>"></a>
            <div class="line_area <?=$arItem["PROPERTIES"]['ACTIVE']['VALUE_XML_ID']?>" data-graphmode="month" data-culture="<?=$arItem['PROPERTIES']['CULTURE']['VALUE'];?>" data-wh="<?=$arItem['PROPERTIES']['WAREHOUSE']['VALUE']?>">
                <div class="line_inner" data-offer="<?=$arItem['ID']?>">
                    <div class="name"><?=$arResult['CULTURE_LIST'][$arItem['PROPERTIES']['CULTURE']['VALUE']]['NAME']?></div>
                    <div class="tons">#<?=$arItem['ID']?></div>
                    <div class="date"><?=reset(explode(' ', $arItem['DATE_CREATE']))?></div>
                    <div class="arw_list arw_icon_close"></div>
                    <?if(isset($arResult['WAREHOUSES_LIST'][$arItem['PROPERTIES']['WAREHOUSE']['VALUE']]['NAME'])){?>
                        <div class="clear l no_border"></div>
                        <div class="warhouse_name"><?=$arResult['WAREHOUSES_LIST'][$arItem['PROPERTIES']['WAREHOUSE']['VALUE']]['NAME'];?></div>
                    <?}?>
                    <div class="clear l"></div>
                </div>
                <form action="" method="post" class="line_additional">
                    <input type="hidden" name="offer" value="<?=$arItem['ID']?>">




                    <div class="prop_area adress_val req_total">
                        <div class="adress ">Параметры товара</div>
                        <?
                        $params = $arResult['OFFER_PARAMS'][$arItem['ID']];
                        foreach ($params as $param) {
                            if(!isset($arResult['PARAMS_INFO'][$arItem['PROPERTIES']['CULTURE']['VALUE']][$param['QUALITY_ID']])){
                                //деактивированные свойства
                                continue;
                            }
                        ?>
                            <div class="val_adress  hidden" >
                                <?=$arResult['PARAMS_INFO'][$arItem['PROPERTIES']['CULTURE']['VALUE']][$param['QUALITY_ID']]['QUALITY_NAME']?>:
                                <? if ($param['LBASE_ID'] > 0) { ?>
                                    <b ><?=$arResult['LBASE_INFO'][$arItem['PROPERTIES']['CULTURE']['VALUE']][$param['QUALITY_ID']][$param['LBASE_ID']]?></b>
                                <? } else { ?>
                                    <b ><?=$param['BASE']?><?=($arResult['UNIT_INFO'][$param['QUALITY_ID']] != '')?' '.$arResult['UNIT_INFO'][$param['QUALITY_ID']]:''?></b>
                                <? } ?>
                            </div>
                        <?
                        }
                        ?>
                        <div class="clear"></div>
                        <div class="show-more-requests">Показать параметры товара</div>
                        <div class="hide-more-request">Скрыть</div>
                    </div>
                    <?
                    if ($arItem['PROPERTIES']['WAREHOUSE']['VALUE']
                        && is_array($arResult['WAREHOUSES_LIST'][$arItem['PROPERTIES']['WAREHOUSE']['VALUE']])
                    ) {
                        ?>
                        <div class="prop_area adress_val">
                            <div class="adress">Адрес склада отгрузки:</div>
                            <div class="val_adress">
                                <?=$arResult['WAREHOUSES_LIST'][$arItem['PROPERTIES']['WAREHOUSE']['VALUE']]['ADDRESS']?>
                            </div>
                        </div>
                        <?
                    }
                    ?>
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
                    <div class="js_anchor_form"> </div>
                    <?
                    if (sizeof($arResult['DEALS'][$arItem['ID']]) > 0) {
                    ?>
                        <div class="prop_area adress_val prices_val">
                            <div class="adress">Пары по данному товару:</div>
                            <?
                            foreach ($arResult['DEALS'][$arItem['ID']] as $deal) {
                            ?>
                                <div class="val_adress val_1 basis_price_table">
                                    <div class="deal_link">
                                        <a href="/farmer/deals/<?=$deal['ID']?>/">#<?=$deal['ID']?> от <?=date('d.m.Y', strtotime($deal['DATE_CREATE']))?></a>
                                    </div>
                                    <div class="deal_volume"><?=$deal['VOLUME']?> т.</div>
                                    <div class="deal_status"><?=$deal['STATUS']?></div>
                                </div>
                            <?
                            }
                            ?>
                        </div>
                    <?
                    }
                    ?>
                    <div class="prop_area total">
                        <div class="val"></div>
                        <a href="<?=$arItem['DETAIL_PAGE_URL']?>" class="submit-btn copy_offer">Копировать товар</a>
                    </div>
                    <div class="prop_area additional_submits">
                        <?
                        if ($arItem['PROPERTIES']['ACTIVE']['VALUE_XML_ID'] == 'yes') {
                            ?>
                            <input type="submit" class="reject_but" name="deactivate" value="Деактивировать товар" />
                            <?
                        }
                        ?>
                        <div class="hide_but">Свернуть</div>
                    </div>
                </form>
            </div>
        <?
        }
        ?>
    </div>
<?
}
else {
?>
    <div class="list_page_rows requests no-item">
        Ни одного товара не найдено
    </div>
<?
}
?>
<a href="/farmer/offer/new/" class="add_blue_button">Добавить товар<div class="but_addit"><div class="ico">+</div></div></a>