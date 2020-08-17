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

if (sizeof($arResult["ITEMS"]) > 0) {
    ?>
    <div class="additional_href_data">
        <a href="/partner/offer/new/">+ Добавить товар</a>
    </div>

    <div class="list_page_rows farmer_offer"  data-host="<?=$GLOBALS['host']?>">
        <?
        foreach ($arResult["ITEMS"] as $arItem) {
        ?>
            <div class="line_area <?=$arItem["PROPERTIES"]['ACTIVE']['VALUE_XML_ID']?>" id="<?=$arItem['ID']?>" data-graphmode="month" data-culture="<?=$arItem['PROPERTIES']['CULTURE']['VALUE'];?>" data-wh="<?=$arItem['PROPERTIES']['WAREHOUSE']['VALUE']?>" <?
                if(
                    !empty($arResult['FARMERS_DATA'][$arItem['PROPERTIES']['FARMER']['VALUE']]['EMAIL'])
                    && !checkEmailFromPhone($arResult['FARMERS_DATA'][$arItem['PROPERTIES']['FARMER']['VALUE']]['EMAIL'])
                ) {
                    echo 'data-email="' . $arResult['FARMERS_DATA'][$arItem['PROPERTIES']['FARMER']['VALUE']]['EMAIL'] . '"';
                }?> >
                <div class="line_inner" data-offer="<?=$arItem['ID']?>">
                    <div class="name"><?=$arResult['CULTURE_LIST'][$arItem['PROPERTIES']['CULTURE']['VALUE']]['NAME']?></div>
                    <div class="tons">#<?=$arItem['ID']?></div>
                    <?if($arItem['PROPERTIES']['ACTIVE']['VALUE_XML_ID'] == 'no'){?>
                        <div class="avail_status no"><?=$arResult['STATUS_AVAILABLE_LIST']['no']['VALUE'];?></div>
                    <?}else{?>
                        <div class="avail_status<?if($arItem['PROPERTIES']['STATUS_AVAILABLE']['VALUE_XML_ID'] == 'no'){?> no<?}?>"><?=(!empty($arItem['PROPERTIES']['STATUS_AVAILABLE']['VALUE']) ? $arItem['PROPERTIES']['STATUS_AVAILABLE']['VALUE'] : $arResult['STATUS_AVAILABLE_LIST']['yes']['VALUE']);?></div>
                    <?}?>
                    <div class="date"><?=reset(explode(' ', $arItem['DATE_CREATE']))?></div>
                    <div class="arw_list arw_icon_close"></div>
                    <?if(isset($arResult['FARMERS_DATA'][$arItem['PROPERTIES']['FARMER']['VALUE']])){
                        $cur_farmer = $arResult['FARMERS_DATA'][$arItem['PROPERTIES']['FARMER']['VALUE']];
                        $sUrlFarmerProfile = '/profile/?uid=' . $arItem['PROPERTIES']['FARMER']['VALUE'];
                        ?>
                            <div class="clear l no_border"></div>
                        <?
                        if($cur_farmer['NICK'] != ''){?>
                            <div class="farmer_name warhouse_name"><a target="_blank" href="<?=$sUrlFarmerProfile?>"><?=$cur_farmer['NICK'];?></a></div>
                        <?}elseif($cur_farmer['NAME'] == ''){?>
                            <div class="farmer_name warhouse_name"><a target="_blank" href="<?=$sUrlFarmerProfile?>"><?=(!checkEmailFromPhone($cur_farmer['EMAIL']) ? $cur_farmer['EMAIL'] : $arItem['PROPERTIES']['FARMER']['VALUE']);?></a></div>
                        <?}else{?>
                            <div class="farmer_name warhouse_name"><a target="_blank" href="<?=$sUrlFarmerProfile?>"><?=$cur_farmer['NAME'];?></a><?if(!checkEmailFromPhone($cur_farmer['EMAIL'])){?> (<?=$cur_farmer['EMAIL'];?>)<?}?></div>
                        <?}
                    }
                    ?>
                    <?if(isset($arResult['WAREHOUSES_LIST'][$arItem['PROPERTIES']['WAREHOUSE']['VALUE']]['NAME'])){?>
                        <div class="clear l no_border"></div>
                        <div class="warhouse_name with_name"><?=$arResult['WAREHOUSES_LIST'][$arItem['PROPERTIES']['WAREHOUSE']['VALUE']]['NAME'];?></div>
                    <?}?>
                    <div class="clear l"></div>
                </div>
                <form action="" method="post" class="line_additional">
                    <input type="hidden" name="offer" value="<?=$arItem['ID']?>">
                    <input type="hidden" name="farmerID" value="<?=$arItem['PROPERTIES']['FARMER']['VALUE'];?>">
                    <input type="hidden" name="farmerAccess" value="<?=(isset($arResult['FARMERS_PROFILE_DONE'][$arItem['PROPERTIES']['FARMER']['VALUE']])
                        && $arResult['FARMERS_PROFILE_DONE'][$arItem['PROPERTIES']['FARMER']['VALUE']])?>">
                    <?
                    $email = '';
                    if(isset($arResult['FARMERS_DATA'][$arItem['PROPERTIES']['FARMER']['VALUE']])){
                        $cur_farmer = $arResult['FARMERS_DATA'][$arItem['PROPERTIES']['FARMER']['VALUE']];
                        if(
                            !empty($cur_farmer['EMAIL'])
                            && !checkEmailFromPhone($cur_farmer['EMAIL'])
                        ){
                            $email = $cur_farmer['EMAIL'];
                        }
                    }
                    if(!empty($email)){
                        ?><input type="hidden" name="haveEmail" value="1"><?
                    }else{
                        ?><input type="hidden" name="haveEmail" value="0"><?
                    }
                    ?>

                    <?if(
                        $arItem['PROPERTIES']['ACTIVE']['VALUE_XML_ID'] == 'yes'
                        && !empty($arResult['STATUS_AVAILABLE_LIST'])
                    ){?>
                        <div class="prop_area adress_val">
                            <div class="adress">Статус "<?=$arResult['STATUS_AVAILABLE_LIST']['yes']['VALUE']?>" / "<?=$arResult['STATUS_AVAILABLE_LIST']['no']['VALUE']?>"</div>
                            <div class="val_adress radio_group">
                                <?if(!empty($arItem['PROPERTIES']['STATUS_AVAILABLE_DATE']['VALUE'])){?>
                                    <div class="avail_date">Дата изменения: <span class="val"><?=$arItem['PROPERTIES']['STATUS_AVAILABLE_DATE']['VALUE'];?></span></div>
                                <?}?>
                                <div class="radio_area">
                                    <input type="checkbox" data-yestext="<?=$arResult['STATUS_AVAILABLE_LIST']['yes']['VALUE'];?>" data-yesid="<?=$arResult['STATUS_AVAILABLE_LIST']['yes']['ID'];?>" data-notext="<?=$arResult['STATUS_AVAILABLE_LIST']['no']['VALUE'];?>" data-noid="<?=$arResult['STATUS_AVAILABLE_LIST']['no']['ID'];?>" <?if($arResult['STATUS_AVAILABLE_LIST']['no']['ID'] == $arItem['PROPERTIES']['STATUS_AVAILABLE']['VALUE_ENUM_ID']){?>checked="checked"<?}?> name="STATUS_AVAILABLE" data-text="<?=$arResult['STATUS_AVAILABLE_LIST']['no']['VALUE'];?>" value="y" />
                                </div>
                            </div>
                        </div>
                    <?}?>

                    <div class="prop_area adress_val req_total">
                        <div class="adress">Параметры товара:</div>
                        <?
                        $params = $arResult['OFFER_PARAMS'][$arItem['ID']];
                        foreach ($params as $param) {
                        ?>
                            <div class="val_adress hidden">
                                <?=$arResult['PARAMS_INFO'][$arItem['PROPERTIES']['CULTURE']['VALUE']][$param['QUALITY_ID']]['QUALITY_NAME']?>:
                                <? if ($param['LBASE_ID'] > 0) { ?>
                                    <b><?=$arResult['LBASE_INFO'][$arItem['PROPERTIES']['CULTURE']['VALUE']][$param['QUALITY_ID']][$param['LBASE_ID']]?></b>
                                <? } else { ?>
                                    <b><?=$param['BASE']?><?=($arResult['UNIT_INFO'][$param['QUALITY_ID']] != '')?' '.$arResult['UNIT_INFO'][$param['QUALITY_ID']]:''?></b>
                                <? } ?>
                            </div>
                        <?
                        }
                        ?>
                        <div class="clear"></div>
                        <div class="show-more-requests">Показать параметры товара</div>
                        <div class="hide-more-request">Скрыть</div>
                        <div class="radio_group quality_approved_padding">
                            <?if($arItem['PROPERTIES']['Q_APPROVED']['VALUE'] == 1
                                && $arItem['PROPERTIES']['Q_APPROVED_DATA']['VALUE']
                            ){?>
                                <div class="date_area">Дата подтверждения:<span class="date_val"><?=$arItem['PROPERTIES']['Q_APPROVED_DATA']['VALUE'];?></span></div>
                            <?}?>
                            <div class="radio_area"><input type="checkbox" data-offerid="<?=$arItem['ID']?>" name="quality_approved" data-text="Подтверждено" <?=($arItem['PROPERTIES']['Q_APPROVED']['VALUE'] == 1 ? ' checked="checked"' : '');?> /></div>
                        </div>
                    </div>
                    <?
                    if ($arItem['PROPERTIES']['WAREHOUSE']['VALUE']
                        && is_array($arResult['WAREHOUSES_LIST'][$arItem['PROPERTIES']['WAREHOUSE']['VALUE']])
                    ) {
                        ?>
                        <div class="prop_area adress_val wh_addr">
                            <div class="adress">Адрес склада отгрузки:</div>
                            <div class="val_adress" data-regionname="<?=$arResult['WAREHOUSES_LIST'][$arItem['PROPERTIES']['WAREHOUSE']['VALUE']]['REGION_NAME'];?>"><?=trim($arResult['WAREHOUSES_LIST'][$arItem['PROPERTIES']['WAREHOUSE']['VALUE']]['ADDRESS'])?></div>
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

                    <div class="prop_area total">
                        <div class="val"></div>
                        <a href="<?=str_replace('farmer', 'partner', $arItem['DETAIL_PAGE_URL']);?>" class="submit-btn copy_offer">Копировать товар</a>
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

                    <?
                    if (sizeof($arResult['DEALS'][$arItem['ID']]) > 0) {
                    ?>
                        <div class="prop_area adress_val prices_val">
                            <div class="adress">Сделки по данному товару:</div>
                            <?
                            foreach ($arResult['DEALS'][$arItem['ID']] as $deal) {
                            ?>
                                <div class="val_adress val_1 basis_price_table">
                                    <div class="deal_link">
                                        #<?=$deal['ID']?> от <?=date('d.m.Y', strtotime($deal['DATE_CREATE']))?>
                                        <?/*
                                        <a href="/farmer/deals/<?=$deal['ID']?>/">#<?=$deal['ID']?> от <?=date('d.m.Y', strtotime($deal['DATE_CREATE']))?></a>
                                            */?>
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
                    <div class="prop_area adress_val" id="affair_<?=$arItem['ID']?>">
                        <div class="adress">Дела по товару:</div>
                        <?$APPLICATION->IncludeComponent(
                            "rarus:agent.farmer.offer.affairs",
                            "",
                            array(
                                'OFFER_ID' => $arItem['ID'],
                            ),
                            $component
                        );?>
                    </div>
                </form>
            </div>
        <?
        }
        ?>
    </div>

    <?if($arParams["DISPLAY_BOTTOM_PAGER"]):?>
        <br /><?=$arResult["NAV_STRING"]?>
    <?endif;?>
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
<a href="/partner/offer/new/" class="add_blue_button">Добавить товар<div class="but_addit"><div class="ico">+</div></div></a>

<div class="add_limit_end">У привязанных к Вам поставщиков исчерпаны лимиты создания товаров</div>