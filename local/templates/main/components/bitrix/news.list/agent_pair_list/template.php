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
$message = '';
?>
<?if (sizeof($arResult["ITEMS"]) > 0):

    $nCounterOptionContract = rrsIblock::getConst('counter_option_contract');
    $nCounterOptionLab = rrsIblock::getConst('counter_option_lab');
    $nCounterOptionSupport = rrsIblock::getConst('counter_option_support');
    $sCounterOptionLab = number_format($nCounterOptionLab, 0, ',', ' ');
    $sCounterOptionVet = number_format(rrsIblock::getConst('counter_option_vet'), 0, ',', ' ');
    $sCounterOptionKar = number_format(rrsIblock::getConst('counter_option_kar'), 0, ',', ' ');
    $sCounterOptionDec = number_format(rrsIblock::getConst('counter_option_dec'), 0, ',', ' ');

    if ($arParams['USER_TYPE'] == 'FARMER') {
        $prop = 'CLIENT';
    }
    elseif (in_array($arParams['USER_TYPE'], array('CLIENT'))) {
        $prop = 'FARMER';
    }

    if(isset($_COOKIE['counter_request_add_success'])
        && $_COOKIE['counter_request_add_success'] == 'y'
    ){
        $message = 'Предложение принято';
        $message_type = 'success';
        setcookie('counter_request_add_success', '', -1, '/');
    }

    if($message != ''){
        ?>
        <div class="message_area <?=$message_type;?>">
            <div class="message"><?=$message;?></div>
        </div>
    <?
    }?>
    <script type="text/javascript">
            <?if($nCounterOptionContract){?>var counter_option_contract = parseInt(<?=$nCounterOptionContract;?>);<?}?>
            <?if($nCounterOptionLab){?>var counter_option_lab = parseInt(<?=$nCounterOptionLab;?>);<?}?>
            <?if($nCounterOptionSupport){?>var counter_option_support = parseInt(<?=$nCounterOptionSupport;?>);<?}?>
    </script>
    <div class="list_page_rows requests" data-usertype="<?=($arParams['USER_TYPE'] == 'FARMER' ? 'f' : 'c');?>">
        <?foreach ($arResult["ITEMS"] as $arItem):
            $base_price_value = $arItem['PROPERTIES']['BASE_CONTR_PRICE']['VALUE'];
            //для старых сделок (у которых нет новых данных в бд) выводим значениt цены по старой логике
            if($arItem['PROPERTIES']['BASE_CONTR_PRICE']['VALUE'] == 0
                && $arItem['PROPERTIES']['BASE_PRICE']['VALUE'] != 0
            ){
                $base_price_value = $arItem['PROPERTIES']['BASE_PRICE']['VALUE'];
            }

            //расчёт стоимости опций
            //стоимость заключения договора
            $iPriceDogovor = partner::countCounterOfferPartnerPriceDogovor($arItem['PROPERTIES']['ACC_PRICE_CSM']['VALUE'], $arItem['PROPERTIES']['VOLUME']['VALUE'], $nCounterOptionContract);
            //стоимость услуг лаборатории
            $iPriceLab = 0;
            if($arItem['PROPERTIES']['IS_ADD_CERT']['VALUE'] == 'Y'){
                $iPriceLab = $nCounterOptionLab;
            }
            //стоимость услуг сопровождения сделки
            $iPriceSupport = partner::countCounterOfferPartnerPriceSupport($arItem['PROPERTIES']['VOLUME']['VALUE'], $nCounterOptionSupport);
            $nPriceSupport = $nCounterOptionSupport;
            if($arItem['PROPERTIES']['IS_AGENT_SUPPORT']['VALUE'] != 'Y'){
                $iPriceSupport = 0;
            }
            $iTotalPartnerPrice = $iPriceDogovor + $iPriceLab + $iPriceSupport;
            ?>
            <div class="line_area" id="<?=$this->GetEditAreaId($arItem['ID']);?>" data-id="<?=$arItem['ID'];?>" data-fid="<?=$arItem['PROPERTIES']['FARMER']['VALUE'];?>" data-cid="<?=$arItem['PROPERTIES']['CLIENT']['VALUE'];?>" data-offer="<?=$arItem['PROPERTIES']['OFFER']['VALUE'];?>" data-request="<?=$arItem['PROPERTIES']['REQUEST']['VALUE'];?>" data-volume="<?=$arItem["PROPERTIES"]['VOLUME']['VALUE']?>">
                <div class="line_inner">
                    <div class="black_list_ico" title="<?=($arParams['USER_TYPE'] == 'FARMER' ? 'Покупатель' : 'Поставщик');?> добавлен в черный список"></div>
                    <div class="name"><?=current($arItem['DISPLAY_PROPERTIES']['CULTURE']['LINK_ELEMENT_VALUE'])['NAME']?></div>
                    <div class="id_date">#<?=$arItem['ID']?> от <?=reset(explode(' ', $arItem['DATE_CREATE']));?></div>
                    <div class="tons"><?=number_format($arItem["PROPERTIES"]['VOLUME']['VALUE'], 0, ',', ' ')?> т</div>
                    <div class="price"><?=number_format($base_price_value, 0, ',', ' ')?> руб/т</div>
                    <?if ($arResult['USER_LIST'][$arItem['PROPERTIES'][$prop]['VALUE']]['COMPANY'] != ''):?>
                        <div class="wh_name">
                            <?=$arResult['USER_LIST'][$arItem['PROPERTIES'][$prop]['VALUE']]['COMPANY']?>
                        </div>
                    <?endif;?>
                    <div class="clear"></div>
                </div>
                <div  class="line_additional" <? if ($_GET['id'] == $arItem['ID']) { ?> style="display: block;"<? } ?>>
                    <?if($arParams['USER_TYPE'] == 'FARMER') { ?>
                        <div class="prop_area refinement_text">
                            Товар
                            <a target="_blank" href="/partner/offer/?id=<?=$arItem['PROPERTIES']['OFFER']['VALUE']?>#<?=$arItem['PROPERTIES']['OFFER']['VALUE']?>">
                                #<?=$arItem['PROPERTIES']['OFFER']['VALUE']?>
                            </a>
                        </div>
                    <? } ?>
                        <?if(intval($arItem['PROPERTIES']['CLIENT']['VALUE']) > 0):?>
                            <?$arUser = $arResult['USER_LIST'][$arItem['PROPERTIES']['CLIENT']['VALUE']];?>
                            <?if(!empty($arItem['PROPERTIES']['DEL_CLIENT_ORG']['VALUE'])|| !empty($arUser['COMPANY'])){?>
                                <div class="prop_area adress_val">
                                    <div class="adress"><?=GetMessage("LABEL::" . 'CLIENT')?>:</div>
                                    <div class="val_adress">
                                        <?if(!empty($arItem['PROPERTIES']['DEL_CLIENT_ORG']['VALUE'])){?>
                                            <?=$arItem['PROPERTIES']['DEL_CLIENT_ORG']['VALUE'];?>
                                        <?}else{?>
                                            <?=$arUser['COMPANY'];?>
                                        <?}?>
                                    </div>
                                </div>
                            <?}?>
                            <div class="prop_area adress_val">
                                <div class="adress">Реквизиты:</div>
                                <div class="val_adress">
                                <?if(!empty($arItem['PROPERTIES']['DEL_CLIENT_REQUISITES']['VALUE'])){?>
                                    <?=$arItem['PROPERTIES']['DEL_CLIENT_REQUISITES']['VALUE']['TEXT'];?>
                                <?}else{
                                    $isFirst = true;
                                    if($arUser['PROPERTY_INN_VALUE'] != '') {
                                        $isFirst = false; ?>
                                        ИНН: <?=$arUser['PROPERTY_INN_VALUE'];?>,
                                    <? } ?>
                                    <?if($arUser['PROPERTY_YUR_ADRESS_VALUE'] != '') { ?>
                                        Юридический адрес: <?=$arUser['PROPERTY_YUR_ADRESS_VALUE'];?>,
                                    <? } ?>
                                    <?if($arUser['PROPERTY_POST_ADRESS_VALUE'] != '') { ?>
                                        Адрес для корреспонденции: <?=$arUser['PROPERTY_POST_ADRESS_VALUE'];?>,
                                    <? } ?>
                                    <?if($arUser['PROPERTY_KPP_VALUE'] != '') { ?>
                                        КПП: <?=$arUser['PROPERTY_KPP_VALUE'];?>,
                                    <? } ?>
                                    <?if($arUser['PROPERTY_OGRN_VALUE'] != '') { ?>
                                        ОГРН<?=$arUser['IS_IP'] ? ' ИП' : ''?>: <?=$arUser['PROPERTY_OGRN_VALUE'];?>,
                                    <? } ?>
                                    <?if($arUser['PROPERTY_OKPO_VALUE'] != '') { ?>
                                        ОКПО: <?=$arUser['PROPERTY_OKPO_VALUE'];?>
                                    <?}
                                }?>
                                </div>
                            </div>
                            <?if(!empty($arItem['PROPERTIES']['DEL_CLIENT_FIO']['VALUE']) || !empty($arUser['USER_DATA'])){?>
                                <div class="prop_area adress_val">
                                    <div class="adress"><?=GetMessage('LABEL_USERDATA')?>:</div>
                                    <div class="val_adress">
                                        <?if(!empty($arItem['PROPERTIES']['DEL_CLIENT_FIO']['VALUE'])){?>
                                            <?=$arItem['PROPERTIES']['DEL_CLIENT_FIO']['VALUE'];?>
                                        <?}elseif(!empty($arUser['USER_DATA'])){?>
                                            <?=$arUser['USER_DATA'];?>
                                        <?}?>
                                    </div>
                                </div>
                            <?}?>
                            <?//Телефон?>
                            <?if(!empty($arItem['PROPERTIES']['DEL_CLIENT_PHONE']['VALUE']) || !empty($arUser['PROPERTY_PHONE_VALUE'])){?>
                                <div class="prop_area adress_val">
                                    <div class="adress">Телефон:</div>
                                    <div class="val_adress">
                                    <?if(!empty($arItem['PROPERTIES']['DEL_CLIENT_PHONE']['VALUE'])){?>
                                        <?=$arItem['PROPERTIES']['DEL_CLIENT_PHONE']['VALUE'];?>
                                    <?}elseif($arUser['PROPERTY_PHONE_VALUE'] != ''){?>
                                        <?=$arUser['PROPERTY_PHONE_VALUE'];?>
                                    <? } ?>
                                    </div>
                                </div>
                            <?}?>
                        <?endif;?>
                        <?if(1 || $arItem['PAIR_TYPE'] == 'p'){//выводим организатора покупателя
                            if (intval($arItem['C_PARTNER_ID']) > 0) {
                                $arUser = $arResult['USER_LIST'][$arItem['C_PARTNER_ID']];
                                if (!empty($arUser['USER_DATA'])) { ?>
                                    <div class="prop_area adress_val">
                                        <div class="adress">Организатор покупателя:</div>
                                        <div class="val_adress">
                                            <?if (!empty($arUser['USER_DATA'])) { ?>
                                                <?= $arUser['USER_DATA']; ?><?
                                            } ?>
                                        </div>
                                        <?if (!empty($arUser['PROPERTY_PHONE_VALUE'])) { ?>
                                            <div class="val_adress">
                                                <? if ($arUser['PROPERTY_PHONE_VALUE'] != '') {
                                                    ?>
                                                    <?= $arUser['PROPERTY_PHONE_VALUE']; ?>
                                                <? } ?>
                                            </div>
                                        <? } ?>
                                    </div><?
                                } ?>
                            <?}
                        }?>
                        <?//Адрес склада покупателя?>
                        <?if(!empty($arItem['PROPERTIES']['DEL_CLIENT_WHADRESS']['VALUE'])
                            || $arItem['PROPERTIES']['CLIENT_WAREHOUSE']['VALUE']
                            && is_array($arResult['CLIENT_WAREHOUSES_LIST'][$arItem['PROPERTIES']['CLIENT_WAREHOUSE']['VALUE']])
                        ){?>
                            <div class="prop_area adress_val">
                                <div class="adress">Адрес склада покупателя:</div>
                                <div class="val_adress">
                                <?if(!empty($arItem['PROPERTIES']['DEL_CLIENT_WHADRESS']['VALUE'])){?>
                                    <?=$arItem['PROPERTIES']['DEL_CLIENT_WHADRESS']['VALUE']?>
                                <?}elseif ($arItem['PROPERTIES']['CLIENT_WAREHOUSE']['VALUE']
                                    && is_array($arResult['CLIENT_WAREHOUSES_LIST'][$arItem['PROPERTIES']['CLIENT_WAREHOUSE']['VALUE']])
                                ) { ?>
                                    <?=$arResult['CLIENT_WAREHOUSES_LIST'][$arItem['PROPERTIES']['CLIENT_WAREHOUSE']['VALUE']]['ADDRESS']?>
                                <? } ?>
                                </div>
                            </div>
                        <?}?>

                        <?if (intval($arItem['PROPERTIES']['FARMER']['VALUE']) > 0):?>
                            <?$arUser = $arResult['USER_LIST'][$arItem['PROPERTIES']['FARMER']['VALUE']]; ?>
                            <?if(!empty($arItem['PROPERTIES']['DEL_FARMER_ORG']['VALUE']) || !empty($arUser['COMPANY'])){?>
                                <div class="prop_area adress_val">
                                    <div class="adress"><?=GetMessage("LABEL::".'FARMER')?>:</div>
                                    <div class="val_adress">
                                        <?if(!empty($arItem['PROPERTIES']['DEL_FARMER_ORG']['VALUE'])){?>
                                            <?=$arItem['PROPERTIES']['DEL_FARMER_ORG']['VALUE'];?>
                                        <?}else{?>
                                            <?=$arUser['COMPANY'];?>
                                        <?}?>
                                    </div>
                                </div>
                            <?}?>
                            <div class="prop_area adress_val">
                                <div class="adress">Реквизиты:</div>
                                <div class="val_adress">
                                <?if(!empty($arItem['PROPERTIES']['DEL_FARMER_REQUISITES']['VALUE'])){?>
                                    <?=$arItem['PROPERTIES']['DEL_FARMER_REQUISITES']['VALUE']['TEXT'];?>
                                <?}else {
                                    $isFirst = true;
                                    if ($arUser['PROPERTY_INN_VALUE'] != '') {
                                        $isFirst = false; ?>
                                        ИНН: <?= $arUser['PROPERTY_INN_VALUE']; ?>,
                                    <? } ?>
                                    <?if ($arUser['PROPERTY_YUR_ADRESS_VALUE'] != '') { ?>
                                        Юридический адрес: <?= $arUser['PROPERTY_YUR_ADRESS_VALUE']; ?>,
                                    <? } ?>
                                    <?if ($arUser['PROPERTY_POST_ADRESS_VALUE'] != '') { ?>
                                        Адрес для корреспонденции: <?= $arUser['PROPERTY_POST_ADRESS_VALUE']; ?>,
                                    <? } ?>
                                    <?if ($arUser['PROPERTY_KPP_VALUE'] != '') { ?>
                                        КПП: <?= $arUser['PROPERTY_KPP_VALUE']; ?>,
                                    <? } ?>
                                    <?if ($arUser['PROPERTY_OGRN_VALUE'] != '') { ?>
                                        ОГРН<?= $arUser['IS_IP'] ? ' ИП' : '' ?>: <?= $arUser['PROPERTY_OGRN_VALUE']; ?>,
                                    <? } ?>
                                    <?if ($arUser['PROPERTY_OKPO_VALUE'] != '') { ?>
                                        ОКПО: <?= $arUser['PROPERTY_OKPO_VALUE']; ?>
                                    <? }
                                }?>
                                </div>
                            </div>
                            <?if(!empty($arItem['PROPERTIES']['DEL_FARMER_FIO']['VALUE']) || !empty($arUser['USER_DATA'])){?>
                                <div class="prop_area adress_val">
                                    <div class="adress"><?=GetMessage('LABEL_USERDATA')?>:</div>
                                    <div class="val_adress">
                                        <?if(!empty($arItem['PROPERTIES']['DEL_FARMER_FIO']['VALUE'])){?>
                                            <?=$arItem['PROPERTIES']['DEL_FARMER_FIO']['VALUE'];?>
                                        <?}elseif(isset($arUser['USER_DATA'])
                                            && $arUser['USER_DATA'] != ''
                                        ){?>
                                            <?=$arUser['USER_DATA'];?>
                                        <?}?>
                                    </div>
                                </div>
                            <?}?>
                            <?//Телефон?>
                            <?if(!empty($arItem['PROPERTIES']['DEL_FARMER_PHONE']['VALUE'])
                                || !empty($arUser['PROPERTY_PHONE_VALUE'])
                            ){?>
                                <div class="prop_area adress_val">
                                    <div class="adress">Телефон:</div>
                                    <div class="val_adress">
                                    <?if(!empty($arItem['PROPERTIES']['DEL_FARMER_PHONE']['VALUE'])){?>
                                        <?=$arItem['PROPERTIES']['DEL_FARMER_PHONE']['VALUE'];?>
                                    <?}elseif($arUser['PROPERTY_PHONE_VALUE'] != '') { ?>
                                        <?=$arUser['PROPERTY_PHONE_VALUE'];?>
                                    <? } ?>
                                    </div>
                                </div>
                            <?}
                            if (intval($arItem['F_PARTNER_ID']) > 0) {//выводим организатора поставщика
                                $arUser = $arResult['USER_LIST'][$arItem['F_PARTNER_ID']];
                                if (!empty($arUser['USER_DATA'])) { ?>
                                    <div class="prop_area adress_val">
                                        <div class="adress">Организатор поставщика:</div>
                                        <div class="val_adress">
                                            <?if (!empty($arUser['USER_DATA'])) { ?>
                                                <?= $arUser['USER_DATA']; ?><?
                                            } ?>
                                        </div>
                                        <?if (!empty($arUser['PROPERTY_PHONE_VALUE'])) { ?>
                                        <div class="val_adress">
                                            <? if ($arUser['PROPERTY_PHONE_VALUE'] != '') {
                                                ?>
                                                <?= $arUser['PROPERTY_PHONE_VALUE']; ?>
                                            <? } ?>
                                        </div>
                                        <? } ?>
                                    </div><?
                                }
                            }?>
                        <?endif;?>
                        <?//Адрес склада АП?>
                        <?if(!empty($arItem['PROPERTIES']['DEL_FARMER_WHADRESS']['VALUE'])
                            || $arItem['PROPERTIES']['FARMER_WAREHOUSE']['VALUE']
                            && is_array($arResult['FARMER_WAREHOUSES_LIST'][$arItem['PROPERTIES']['FARMER_WAREHOUSE']['VALUE']])
                        ){?>
                            <div class="prop_area adress_val">
                                <div class="adress">Адрес склада поставщика:</div>
                                <div class="val_adress">
                                <?if(!empty($arItem['PROPERTIES']['DEL_FARMER_WHADRESS']['VALUE'])){?>
                                    <?=$arItem['PROPERTIES']['DEL_FARMER_WHADRESS']['VALUE']?>
                                <?}elseif ($arItem['PROPERTIES']['FARMER_WAREHOUSE']['VALUE']
                                    && is_array($arResult['FARMER_WAREHOUSES_LIST'][$arItem['PROPERTIES']['FARMER_WAREHOUSE']['VALUE']])
                                ) {?>
                                    <?=$arResult['FARMER_WAREHOUSES_LIST'][$arItem['PROPERTIES']['FARMER_WAREHOUSE']['VALUE']]['ADDRESS']?>
                                <? }?>
                                </div>
                            </div>
                        <?}?>

                    <div class="prop_area adress_val">
                        <div class="adress">Название продукции, объем:</div>
                        <div class="val_adress">
                            <?=current($arItem['DISPLAY_PROPERTIES']['CULTURE']['LINK_ELEMENT_VALUE'])['NAME']?>, <?=number_format($arItem["PROPERTIES"]['VOLUME']['VALUE'], 0, ',', ' ');?> т
                        </div>
                    </div>

                    <?
                    $params = $arResult['OFFER_PARAMS'][$arItem['PROPERTIES']['OFFER']['VALUE']];
                    $culture = $arItem['PROPERTIES']['CULTURE']['VALUE'];
                    if(count($params) > 0
                        || !empty($arItem['PROPERTIES']['DEL_PARAMS']['VALUE']['TEXT'])
                    ){
                        ?>
                        <div class="prop_area adress_val">
                            <div class="adress">Параметры качества<?=(!empty($arResult['OFFERS_APPROVED'][$arItem['PROPERTIES']['OFFER']['VALUE']]) ? '<span class="approved_quality">(Подтверждено)</span>' : '');?>: </div>
                            <?
                            if(!empty($arItem['PROPERTIES']['DEL_PARAMS']['VALUE']['TEXT'])){?>
                                <?=htmlspecialchars_decode($arItem['PROPERTIES']['DEL_PARAMS']['VALUE']['TEXT']);?>
                            <?}else {
                                foreach ($params as $param) {
                                    if (!isset($arResult['PARAMS_INFO'][$culture][$param['QUALITY_ID']]['QUALITY_NAME'])) {
                                        continue;
                                    }
                                    ?>
                                    <div class="val_adress">
                                        <?= $arResult['PARAMS_INFO'][$culture][$param['QUALITY_ID']]['QUALITY_NAME'] ?>:
                                        <? if ($param['LBASE_ID'] > 0) { ?>
                                            <b><?= $arResult['LBASE_INFO'][$culture][$param['QUALITY_ID']][$param['LBASE_ID']] ?></b>
                                        <? } else { ?>
                                            <b><?= $param['BASE'] ?><?= ($arResult['UNIT_INFO'][$param['QUALITY_ID']] != '') ? ' ' . $arResult['UNIT_INFO'][$param['QUALITY_ID']] : '' ?></b>
                                        <? } ?>
                                    </div>
                                    <?
                                }
                            }
                        ?></div><?
                    }
                    ?>

                    <div class="prop_area prices_val">

                        <div class="area_1">
                            <div class="name_1"><?=current($arItem['DISPLAY_PROPERTIES']['CULTURE']['LINK_ELEMENT_VALUE'])['NAME']?> "с места"/(FCA), (<?=($arItem['PROPERTIES']['B_NDS']['VALUE'] == 'Y' ? 'с НДС' : 'без НДС')?>):</div>
                            <div class="val_1">
                                <span class="decs_separators"><?=number_format($arItem['PROPERTIES']['ACC_PRICE_CSM']['VALUE'], 0, ',', ' ');?></span> руб/т
                            </div>
                        </div>

                        <?
                        $arTariffRange = client::getTariffRange($arItem['PROPERTIES']['ROUTE']['VALUE'], $arResult['AX_TARIFS']);
                        ?>
                        <div class="area_1">
                            <div class="name_1">Тариф перевозки (<?=($arTariffRange['FROM'] > 0 ? $arTariffRange['FROM'] . '-' : 'до ') . $arTariffRange['TO'] . ' км'?>):</div>
                            <div class="val_1">
                                <span class="decs_separators"><?=number_format($arItem['PROPERTIES']['TARIF']['VALUE'], 0, ',', ' ')?></span> руб/т
                            </div>
                        </div>
                        <?
                        $dump_val = $arItem['PROPERTIES']['DUMP_RUB']['VALUE'];
                        //$dump_val = -1 * $dump_val;
                        ?>

                        <div class="area_1">
                            <div class="name_1">Прогноз сброса/прибавки:</div>
                            <div class="val_1">
                                <span class="decs_separators"><?=($dump_val > 0 ? '+' : '');?><?=number_format($dump_val, 0, ',', ' ')?></span> руб/т
                            </div>
                        </div>

                        <div class="area_1">
                            <div class="name_1">Базисная цена (СРТ, <?=$arResult['CLIENT_WAREHOUSES_LIST'][$arItem['PROPERTIES']['CLIENT_WAREHOUSE']['VALUE']]['NAME'];?>):</div>
                            <div class="val_1">
                                <span class="decs_separators"><?=number_format($base_price_value, 0, ',', ' ')?></span> руб/т
                            </div>
                        </div>
                        <?if($arItem['PAIR_TYPE'] == 'p'){?>
                            <div class="area_1 agent_price">
                                <div class="name_1">Услуги Агрохелпера, <span class="val"><?=number_format($arItem['PROPERTIES']['PARTNER_PRICE']['VALUE'], 0, ',', ' ');?></span> руб:</div>
                                <div class="val_1">
                                    <span class="decs_separators"><?=number_format(round($arItem['PROPERTIES']['PARTNER_PRICE']['VALUE'] / $arItem['PROPERTIES']['VOLUME']['VALUE']), 0, ',', ' ')?></span> руб/т
                                </div>
                            </div>
                        <?}?>
                    </div>

                    <?
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

                    <form action="" method="post" class="options_form" >
                        <input type="hidden" name="accept" value="y">
                        <input type="hidden" name="pair" value="<?=$arItem['ID'];?>">
                        <input type="hidden" name="offer" value="<?=$arItem['PROPERTIES']['OFFER']['VALUE'];?>">
                        <input type="hidden" name="request" value="<?=$arItem['PROPERTIES']['REQUEST']['VALUE'];?>">
                        <div class="prop_area adress_val">
                            <div class="adress">Услуги Агрохелпера:</div>
                            <?
                            //вывод отмеченных опций (для покупателя)
                            if($arItem['PAIR_TYPE'] == 'p') {
                                $bCheckedOptions = false;
                                $bUnCheckedOptions = false;
                                foreach ($arResult['SHOW_SERVICES'] as $sPropCode => $bFlag) {
                                    if (isset($arItem['PROPERTIES'][$sPropCode]['VALUE'])) {
                                        if($arItem['PROPERTIES'][$sPropCode]['VALUE'] == 'Y') {
                                            $bCheckedOptions = true;
                                        }else{
                                            $bUnCheckedOptions = true;
                                        }
                                        if(
                                            $bCheckedOptions
                                            && $bUnCheckedOptions
                                        ){
                                            break;
                                        }
                                    }
                                }

                                $bShowedDocs = false;
                                ?>
                                <div class="checked_options">
                                <div class="message-add">Включены в стоимость</div>
                                <div class="val_adress slide-description">
                                    <div class="radio_group">
                                        <div class="radio_area">
                                            <input
                                                    data-text="<div class='custom_data_text'><span class='option-name'>Заключение договора<i></i></span></div>";
                                                    type="checkbox" data-checked="y" name="IS_AGENT_SERVICE" value="Y" checked="checked" disabled/>
                                        </div>
                                        <div class="price_value"><?=number_format($iPriceDogovor, 0, ',', ' ');?> руб</div>
                                    </div>
                                    <div class="option-description">
                                        <p>Соберем полный пакет документов по требованию. Заключим договор между Вами и Агропроизводителем.</p>
                                    </div>
                                </div>
                                <?

                                //выводим блок выбранных опций
                                //if ($bCheckedOptions) {
                                foreach ($arResult['SHOW_SERVICES'] as $sPropCode => $bFlag) {
                                    ?>
                                    <div class="val_adress slide-description<?if($arItem['PROPERTIES'][$sPropCode]['VALUE'] != 'Y'){?> inactive<?}?>">
                                        <?if(
                                            !$bShowedDocs
                                            &&(
                                                $sPropCode == 'IS_BILL_OF_HEALTH'
                                                || $sPropCode == 'IS_VET_CERT'
                                                || $sPropCode == 'IS_QUALITY_CERT'
                                            )
                                        ){
                                            $bShowedDocs = true;
                                            ?>
                                            <div><?=GetMessage("DOCS:NAME");?><br><br></div>
                                            <?=GetMessage("DOCS:DESCRIPTION");?><br/>
                                        <?}?>
                                        <div class="radio_group">
                                            <div class="radio_area">
                                                <input type="checkbox"
                                                       data-text="<?= GetMessage("{$sPropCode}:NAME"); ?>"
                                                       name="<?=$sPropCode?>"
                                                       value="Y"
                                                       data-checked="y"
                                                       checked="checked"
                                                    <?if(
                                                        $arItem['PROPERTIES'][$sPropCode]['VALUE'] != 'Y'
                                                        || !isset($arResult['ALOWED_TO_CHANGE_OPTIONS'][$sPropCode])
                                                        || (
                                                            $sPropCode == 'IS_ADD_CERT'
                                                            && !empty($arResult['OFFERS_APPROVED'][$arItem['PROPERTIES']['OFFER']['VALUE']])
                                                        )
                                                    ){?>
                                                        disabled
                                                    <?}?>
                                                >
                                            </div>
                                            <?if($sPropCode == 'IS_BILL_OF_HEALTH'):
                                                ?><div class="price_value"><?=$sCounterOptionKar;?> руб/т</div><?
                                            elseif($sPropCode == 'IS_VET_CERT'):
                                                ?><div class="price_value"><?=$sCounterOptionVet?> руб/т</div><?
                                            elseif($sPropCode == 'IS_QUALITY_CERT'):
                                                ?><div class="price_value"><?=$sCounterOptionDec?> руб</div><?
                                            elseif(
                                                $sPropCode == 'IS_TRANSFER'
                                                && !empty($arItem['PROPERTIES']['TARIF']['VALUE'])
                                            ):
                                                ?><div class="price_value"><?=number_format($arItem['PROPERTIES']['TARIF']['VALUE'], 0, ',', ' ');?> руб/т</div><?
                                            elseif(
                                                $sPropCode == 'IS_AGENT_SUPPORT'
                                                && $nPriceSupport
                                            ):
                                                ?><div class="price_value"><?=number_format($nPriceSupport, 0, ',', ' ');?> руб/т</div><?
                                            elseif(
                                                $sPropCode == 'IS_ADD_CERT'
                                                && $nCounterOptionLab
                                            ):
                                                ?><div class="price_value"><?=number_format($nCounterOptionLab, 0, ',', ' ');?> руб</div><?
                                            endif;
                                            ?>
                                        </div>
                                        <?= GetMessage("{$sPropCode}:DESCRIPTION"); ?>
                                    </div>
                                    <?
                                }
                                //}
                                ?></div><?

                                ?>
                                <div class="no_checked_options">
                            <div class="message-add<?=($bUnCheckedOptions ? '' : ' inactive');?>">Возможные к заказу</div><?
                                //if($bUnCheckedOptions){
                                foreach ($arResult['SHOW_SERVICES'] as $sPropCode => $bFlag) {
                                    ?>
                                    <div class="val_adress slide-description<?if($arItem['PROPERTIES'][$sPropCode]['VALUE'] == 'Y'){?> inactive<?}?>">
                                        <?if(
                                            !$bShowedDocs
                                            &&(
                                                $sPropCode == 'IS_BILL_OF_HEALTH'
                                                || $sPropCode == 'IS_VET_CERT'
                                                || $sPropCode == 'IS_QUALITY_CERT'
                                            )
                                        ){
                                            $bShowedDocs = true;
                                            ?>
                                            <div><?=GetMessage("DOCS:NAME");?><br><br></div>
                                            <?=GetMessage("DOCS:DESCRIPTION");?><br/>
                                        <?}?>
                                        <div class="radio_group">
                                            <div class="radio_area">
                                                <input type="checkbox"
                                                       data-text="<?= GetMessage("{$sPropCode}:NAME"); ?>"
                                                       name="<?=$sPropCode?>"
                                                       value="Y"
                                                    <?if(
                                                        $arItem['PROPERTIES'][$sPropCode]['VALUE'] == 'Y'
                                                        || !isset($arResult['ALOWED_TO_CHANGE_OPTIONS'][$sPropCode])
                                                    ):?>
                                                        disabled
                                                    <?endif;?>
                                                >
                                            </div>
                                            <?if($sPropCode == 'IS_BILL_OF_HEALTH'):
                                                ?><div class="price_value"><?=$sCounterOptionKar;?> руб/т</div><?
                                            elseif($sPropCode == 'IS_VET_CERT'):
                                                ?><div class="price_value"><?=$sCounterOptionVet?> руб/т</div><?
                                            elseif($sPropCode == 'IS_QUALITY_CERT'):
                                                ?><div class="price_value"><?=$sCounterOptionDec?> руб</div><?
                                            elseif(
                                                $sPropCode == 'IS_TRANSFER'
                                                && !empty($arItem['PROPERTIES']['TARIF']['VALUE'])
                                            ):
                                                ?><div class="price_value"><?=number_format($arItem['PROPERTIES']['TARIF']['VALUE'], 0, ',', ' ');?> руб/т</div><?
                                            elseif(
                                                $sPropCode == 'IS_AGENT_SUPPORT'
                                                && $nPriceSupport
                                            ):
                                                ?><div class="price_value"><?=number_format($nPriceSupport, 0, ',', ' ');?> руб/т</div><?
                                            elseif(
                                                $sPropCode == 'IS_ADD_CERT'
                                                && $nCounterOptionLab
                                            ):
                                                ?><div class="price_value"><?=number_format($nCounterOptionLab, 0, ',', ' ');?> руб</div><?
                                            endif;
                                            ?>
                                        </div>
                                        <?= GetMessage("{$sPropCode}:DESCRIPTION"); ?>
                                    </div>
                                    <?
                                }
                                //}
                                ?></div><?
                            }else{
                                ?>
                                <div class="val_adress slide-description">
                                    <div class="radio_group">
                                        <div class="radio_area">
                                            <input type="checkbox"
                                                   data-text="<?=GetMessage("IS_ADD_CERT:NAME");?>"
                                                   name="IS_ADD_CERT"
                                                   value="Y"
                                                <?if($arItem['PROPERTIES']['IS_ADD_CERT']['VALUE'] === 'Y'):?>
                                                    data-checked="y"
                                                    checked="checked"
                                                    disabled
                                                <?endif;?>
                                            >
                                        </div>
                                    </div>
                                    <?=GetMessage("IS_ADD_CERT:DESCRIPTION");?>
                                </div>

                                <div class="val_adress slide-description">
                                    <div><?=GetMessage("DOCS:NAME");?><br><br></div>
                                    <?=GetMessage("DOCS:DESCRIPTION");?><br/>
                                    <div class="radio_group">
                                        <div class="radio_area">
                                            <input type="checkbox"
                                                   data-text="<?=GetMessage("IS_BILL_OF_HEALTH:NAME");?>"
                                                   name="IS_BILL_OF_HEALTH"
                                                   value="Y"
                                                <?if($arItem['PROPERTIES']['IS_BILL_OF_HEALTH']['VALUE'] === 'Y'):?>
                                                    data-checked="y"
                                                    checked="checked"
                                                    disabled
                                                <?endif;?>
                                            >
                                        </div>
                                        <div class="radio_area">
                                            <input type="checkbox"
                                                   data-text="<?=GetMessage("IS_VET_CERT:NAME");?>"
                                                   name="IS_VET_CERT"
                                                   value="Y"
                                                <?if($arItem['PROPERTIES']['IS_VET_CERT']['VALUE'] === 'Y'):?>
                                                    data-checked="y"
                                                    checked="checked"
                                                    disabled
                                                <?endif;?>
                                            >
                                        </div>
                                        <div class="radio_area">
                                            <input type="checkbox"
                                                   data-text="<?=GetMessage("IS_QUALITY_CERT:NAME");?>"
                                                   name="IS_QUALITY_CERT"
                                                   value="Y"
                                                <?if($arItem['PROPERTIES']['IS_QUALITY_CERT']['VALUE'] === 'Y'):?>
                                                    data-checked="y"
                                                    checked="checked"
                                                    disabled
                                                <?endif;?>
                                            >
                                        </div>
                                    </div>
                                </div>

                                <div class="val_adress  slide-description">
                                    <div class="radio_group">
                                        <div class="radio_area">
                                            <input type="checkbox"
                                                   data-text="<?=GetMessage("IS_TRANSFER:NAME");?>"
                                                   name="IS_TRANSFER"
                                                   value="Y"
                                                <?if($arItem['PROPERTIES']['IS_TRANSFER']['VALUE'] === 'Y'):?>
                                                    data-checked="y"
                                                    checked="checked"
                                                    disabled
                                                <?endif;?>
                                            >
                                        </div>
                                    </div>

                                    <?=GetMessage("IS_TRANSFER:DESCRIPTION");?>
                                </div>

                                <div class="val_adress  slide-description">
                                    <div class="radio_group">
                                        <div class="radio_area">
                                            <input type="checkbox"
                                                   data-text="<?=GetMessage("IS_SECURE_DEAL:NAME");?>"
                                                   name="IS_SECURE_DEAL"
                                                   value="Y"
                                                <?if($arItem['PROPERTIES']['IS_SECURE_DEAL']['VALUE'] === 'Y'):?>
                                                    data-checked="y"
                                                    checked="checked"
                                                    disabled
                                                <?endif;?>
                                            >
                                        </div>
                                    </div>

                                    <?=GetMessage("IS_SECURE_DEAL:DESCRIPTION");?>
                                </div>

                                <div class="val_adress  slide-description">
                                    <div class="radio_group">
                                        <div class="radio_area">
                                            <input type="checkbox"
                                                   data-text="<?=GetMessage("IS_AGENT_SUPPORT:NAME");?>"
                                                   name="IS_AGENT_SUPPORT"
                                                   value="Y"
                                                <?if($arItem['PROPERTIES']['IS_AGENT_SUPPORT']['VALUE'] === 'Y'):?>
                                                    data-checked="y"
                                                    checked="checked"
                                                    disabled
                                                <?endif;?>
                                            >
                                        </div>
                                    </div>
                                    <?=GetMessage("IS_AGENT_SUPPORT:DESCRIPTION");?>
                                </div>
                                <?
                            }
                            ?>

                            <div class="val_adress  ">
                                <div class="accept empty"><input type="submit" class="submit-btn" value="Заказать" ></div>
                            </div>
                        </div>
                    </form>

                    <div class="prop_area adress_val black_list_row">
                        <div class="val_adress no_tpad">
                            <div class="accept">
                                <div class="black_list_ico"><?=($arParams['USER_TYPE'] == 'FARMER' ? 'Покупатель' : 'Поставщик');?> добавлен в <a href="/<?=($arParams['USER_TYPE'] == 'FARMER' ? 'farmer' : 'client');?>/blacklist/">черный список</a></div>
                            </div>
                        </div>
                    </div>

                    <div class="prop_area adress_val pot_black_list_row">
                        <div class="val_adress no_tpad">
                            <div class="accept">
                                <input type="button" class="submit-btn" value="Отправить <?=($arParams['USER_TYPE'] == 'FARMER' ? 'покупателя' : 'поставщика');?> в черный список" />
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        <?endforeach;?>

        <?if($arParams["DISPLAY_BOTTOM_PAGER"]):?>
            <br /><?=$arResult["NAV_STRING"]?>
        <?endif;?>
    </div>
<?else:?>
    <div class="list_page_rows requests no-item">
        Ни одной пары не найдено
    </div>
<?endif;?>
