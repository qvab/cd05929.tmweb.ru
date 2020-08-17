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

if($arParams['USER_TYPE'] == 'CLIENT'){
    //выводим услуги в порядке выбранности организатором
    //$arAdditOpts = array('IS_ADD_CERT' => 1, 'IS_BILL_OF_HEALTH');
}

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
        $un_prop = 'FARMER';
    }
    elseif (in_array($arParams['USER_TYPE'], array('CLIENT'))) {
        $prop = 'FARMER';
        $un_prop = 'CLIENT';
    }

    if(isset($arParams['SUCCESS_MESSAGE'])
        && $arParams['SUCCESS_MESSAGE'] != ''
    ) {
        $message = $arParams['SUCCESS_MESSAGE'];
        $message_type = 'success';
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

            $sCultureName = current($arItem['DISPLAY_PROPERTIES']['CULTURE']['LINK_ELEMENT_VALUE'])['NAME'];
            $sTonsFormatted = number_format($arItem["PROPERTIES"]['VOLUME']['VALUE'], 0, ',', ' ');
            ?>
            <div class="line_area active" id="<?=$this->GetEditAreaId($arItem['ID']);?>" data-id="<?=$arItem['ID'];?>" data-fid="<?=$arItem['PROPERTIES']['FARMER']['VALUE'];?>" data-cid="<?=$arItem['PROPERTIES']['CLIENT']['VALUE'];?>" data-offer="<?=$arItem['PROPERTIES']['OFFER']['VALUE'];?>" data-request="<?=$arItem['PROPERTIES']['REQUEST']['VALUE'];?>">
                <div  class="line_additional" <? if ($_GET['id'] == $arItem['ID']) { ?> style="display: block;"<? } ?>>
                    <?if($arItem['PAIR_TYPE'] == 'c') { //созданое на основе платного предложения
                        if ($arParams['USER_TYPE'] != 'CLIENT') {
                            if ($arParams['USER_TYPE'] == 'FARMER') { ?>
                                <div class="prop_area refinement_text">
                                    Товар
                                    <a target="_blank"
                                       href="/farmer/offer/?id=<?= $arItem['PROPERTIES']['OFFER']['VALUE'] ?>#<?=$arItem['PROPERTIES']['OFFER']['VALUE'];?>">
                                        #<?=$arItem['PROPERTIES']['OFFER']['VALUE'];?>
                                    </a>
                                </div>
                            <?
                            }
                            if (intval($arItem['PROPERTIES'][$prop]['VALUE']) > 0):?>
                                <?
                                $arUser = $arResult['USER_LIST'][$arItem['PROPERTIES'][$prop]['VALUE']]; ?>
                                <?
                                if (!empty($arItem['PROPERTIES']['DEL_' . $prop . '_ORG']['VALUE'])
                                    || !empty($arUser['COMPANY'])
                                ) {
                                    ?>
                                    <div class="prop_area adress_val">
                                        <div class="adress"><?= GetMessage("LABEL::" . $prop) ?>:</div>
                                        <div class="val_adress">
                                            <?
                                            if (!empty($arItem['PROPERTIES']['DEL_' . $prop . '_ORG']['VALUE'])) {
                                                ?>
                                                <?= $arItem['PROPERTIES']['DEL_' . $prop . '_ORG']['VALUE']; ?>
                                            <?
                                            } else {
                                                ?>
                                                <?= $arUser['COMPANY']; ?>
                                            <?
                                            } ?>
                                        </div>
                                    </div>
                                <?
                                } ?>

                                <?
                                if (!empty($arItem['PROPERTIES']['DEL_' . $prop . '_FIO']['VALUE'])
                                    || !empty($arUser['USER_DATA']['F_NAME'])
                                ) {
                                    ?>
                                    <div class="prop_area adress_val">
                                        <div class="adress"><?= GetMessage('LABEL_USERDATA') ?>:</div>
                                        <div class="val_adress">
                                            <?
                                            if (!empty($arItem['PROPERTIES']['DEL_' . $prop . '_FIO']['VALUE'])) {
                                                ?>
                                                <?= $arItem['PROPERTIES']['DEL_' . $prop . '_FIO']['VALUE']; ?>
                                            <?
                                            } elseif (!empty($arUser['USER_DATA']['F_NAME'])) {
                                                ?>
                                                <?= $arUser['USER_DATA']['F_NAME']; ?>
                                            <?
                                            } ?>
                                        </div>
                                    </div>
                                <?
                                } ?>

                                <div class="prop_area adress_val">
                                    <div class="adress">Реквизиты:</div>
                                    <div class="val_adress">
                                        <?
                                        if (!empty($arItem['PROPERTIES']['DEL_' . $prop . '_REQUISITES']['VALUE'])) {
                                            ?>
                                            <?= $arItem['PROPERTIES']['DEL_' . $prop . '_REQUISITES']['VALUE']['TEXT']; ?>
                                        <?
                                        } else {
                                            $isFirst = true;
                                            if ($arUser['PROPERTY_INN_VALUE'] != '') {
                                                $isFirst = false; ?>
                                                ИНН: <?= $arUser['PROPERTY_INN_VALUE']; ?>,
                                            <? } ?>
                                            <?
                                            if ($arUser['PROPERTY_PROPERTY_YUR_ADRESS_VALUE'] != '') { ?>
                                                Юридический адрес: <?= $arUser['PROPERTY_PROPERTY_YUR_ADRESS_VALUE']; ?>,
                                            <? } ?>
                                            <?
                                            if ($arUser['PROPERTY_POST_ADRESS_VALUE'] != '') { ?>
                                                Адрес для корреспонденции: <?= $arUser['PROPERTY_POST_ADRESS_VALUE']; ?>,
                                            <? } ?>
                                            <?
                                            if ($arUser['PROPERTY_KPP_VALUE'] != '') { ?>
                                                КПП: <?= $arUser['PROPERTY_KPP_VALUE']; ?>,
                                            <? } ?>
                                            <?
                                            if ($arUser['PROPERTY_OGRN_VALUE'] != '') { ?>
                                                ОГРН<?= $arUser['IS_IP'] ? ' ИП' : '' ?>: <?= $arUser['PROPERTY_OGRN_VALUE']; ?>,
                                            <? } ?>
                                            <?
                                            if ($arUser['PROPERTY_OKPO_VALUE'] != '') { ?>
                                                ОКПО: <?= $arUser['PROPERTY_OKPO_VALUE']; ?>
                                            <? }
                                        } ?>
                                    </div>
                                </div>
                                <?
                                /**
                                 * Телефон
                                 */
                                ?>
                                <?
                                if (
                                    $arParams['USER_TYPE'] != 'CLIENT'
                                    && (
                                        !empty($arItem['PROPERTIES']['DEL_' . $prop . '_PHONE']['VALUE'])
                                        || !empty($arUser['PROPERTY_PHONE_VALUE'])
                                    )
                                ) { ?>
                                    <div class="prop_area adress_val">
                                        <div class="adress">Телефон:</div>
                                        <div class="val_adress">
                                            <? if (!empty($arItem['PROPERTIES']['DEL_' . $prop . '_PHONE']['VALUE'])) {
                                                ?>
                                                <?= $arItem['PROPERTIES']['DEL_' . $prop . '_PHONE']['VALUE']; ?>
                                            <? } elseif ($arUser['PROPERTY_PHONE_VALUE'] != '') {
                                                ?>
                                                <?= $arUser['PROPERTY_PHONE_VALUE']; ?>
                                            <? } ?>
                                        </div>
                                    </div>
                                <? } ?>
                            <?endif; ?>
                            <?
                        }
                    }elseif($arItem['PAIR_TYPE'] == 'p') { //создано на основе клиентского предложения
                        if($arParams['USER_TYPE'] != 'CLIENT') {
                            ?>
                            <? if ($arParams['USER_TYPE'] == 'FARMER') { ?>
                                <div class="prop_area refinement_text">
                                    Товар
                                    <a target="_blank"
                                       href="/farmer/offer/?id=<?= $arItem['PROPERTIES']['OFFER']['VALUE'] ?>#<?= $arItem['PROPERTIES']['OFFER']['VALUE'] ?>">
                                        #<?= $arItem['PROPERTIES']['OFFER']['VALUE'] ?>
                                    </a>
                                </div>
                            <? } ?>
                            <?

                            if (intval($arItem['PROPERTIES'][$un_prop]['VALUE']) > 0):?>
                                <? $arUser = $arResult['USER_LIST'][$arItem['PROPERTIES'][$un_prop]['VALUE']]; ?>
                                <? if (!empty($arItem['PROPERTIES']['DEL_' . $un_prop . '_ORG']['VALUE'])
                                    || !empty($arUser['COMPANY'])
                                ) {
                                    ?>
                                    <div class="prop_area adress_val">
                                        <div class="adress"><?= GetMessage("LABEL::" . $un_prop) ?>:</div>
                                        <div class="val_adress">
                                            <? if (!empty($arItem['PROPERTIES']['DEL_' . $un_prop . '_ORG']['VALUE'])) {
                                                ?>
                                                <?= $arItem['PROPERTIES']['DEL_' . $un_prop . '_ORG']['VALUE']; ?>
                                            <? } else {
                                                ?>
                                                <?= $arUser['COMPANY']; ?>
                                            <? } ?>
                                        </div>
                                    </div>
                                <? } ?>

                                <? if (!empty($arItem['PROPERTIES']['DEL_' . $un_prop . '_FIO']['VALUE'])
                                    || !empty($arUser['USER_DATA']['F_NAME'])
                                ) {
                                    ?>
                                    <div class="prop_area adress_val">
                                        <div class="adress"><?= GetMessage('LABEL_USERDATA') ?>:</div>
                                        <div class="val_adress">
                                            <? if (!empty($arItem['PROPERTIES']['DEL_' . $un_prop . '_FIO']['VALUE'])) {
                                                ?>
                                                <?= $arItem['PROPERTIES']['DEL_' . $un_prop . '_FIO']['VALUE']; ?>
                                            <? } elseif (!empty($arUser['USER_DATA']['F_NAME'])) {
                                                ?>
                                                <?= $arUser['USER_DATA']['F_NAME']; ?>
                                            <? } ?>
                                        </div>
                                    </div>
                                <? } ?>

                                <div class="prop_area adress_val">
                                    <div class="adress">Реквизиты:</div>
                                    <div class="val_adress">
                                        <? if (!empty($arItem['PROPERTIES']['DEL_' . $un_prop . '_REQUISITES']['VALUE'])) {
                                            ?>
                                            <?= $arItem['PROPERTIES']['DEL_' . $un_prop . '_REQUISITES']['VALUE']['TEXT']; ?>
                                        <? } else {
                                            $isFirst = true;
                                            if ($arUser['PROPERTY_INN_VALUE'] != '') {
                                                $isFirst = false; ?>
                                                ИНН: <?= $arUser['PROPERTY_INN_VALUE']; ?>,
                                            <? } ?>
                                            <? if ($arUser['PROPERTY_PROPERTY_YUR_ADRESS_VALUE'] != '') { ?>
                                                Юридический адрес: <?= $arUser['PROPERTY_PROPERTY_YUR_ADRESS_VALUE']; ?>,
                                            <? } ?>
                                            <? if ($arUser['PROPERTY_POST_ADRESS_VALUE'] != '') { ?>
                                                Адрес для корреспонденции: <?= $arUser['PROPERTY_POST_ADRESS_VALUE']; ?>,
                                            <? } ?>
                                            <? if ($arUser['PROPERTY_KPP_VALUE'] != '') { ?>
                                                КПП: <?= $arUser['PROPERTY_KPP_VALUE']; ?>,
                                            <? } ?>
                                            <? if ($arUser['PROPERTY_OGRN_VALUE'] != '') { ?>
                                                ОГРН<?= $arUser['IS_IP'] ? ' ИП' : '' ?>: <?= $arUser['PROPERTY_OGRN_VALUE']; ?>,
                                            <? } ?>
                                            <? if ($arUser['PROPERTY_OKPO_VALUE'] != '') { ?>
                                                ОКПО: <?= $arUser['PROPERTY_OKPO_VALUE']; ?>
                                            <? }
                                        } ?>
                                    </div>
                                </div>
                                <? //Телефон
                                if (!empty($arItem['PROPERTIES']['DEL_' . $un_prop . '_PHONE']['VALUE'])
                                    || !empty($arUser['PROPERTY_PHONE_VALUE'])
                                ) { ?>
                                    <div class="prop_area adress_val">
                                        <div class="adress">Телефон:</div>
                                        <div class="val_adress">
                                            <? if (!empty($arItem['PROPERTIES']['DEL_' . $un_prop . '_PHONE']['VALUE'])) {
                                                ?>
                                                <?= $arItem['PROPERTIES']['DEL_' . $un_prop . '_PHONE']['VALUE']; ?>
                                            <? } elseif ($arUser['PROPERTY_PHONE_VALUE'] != '') {
                                                ?>
                                                <?= $arUser['PROPERTY_PHONE_VALUE']; ?>
                                            <? } ?>
                                        </div>
                                    </div>
                                <? } ?>
                            <? endif;
                        }?>

                        <?
                    }

                    if (
                        intval($arItem['PARTNER_ID']) > 0
                        && isset($arResult['USER_LIST'][$arItem['PARTNER_ID']])
                    ){
                        $arUser = $arResult['USER_LIST'][$arItem['PARTNER_ID']];
                        if (!empty($arUser['USER_DATA']['F_NAME'])) {
                            $org_title = '';
                            if ($arParams['USER_TYPE'] == 'FARMER') {
                                $org_title = 'Организатор поставщика:';
                            } elseif ($arParams['USER_TYPE'] == 'CLIENT') {
                                $org_title = 'Организатор покупателя:';
                            }
                            if (!empty($org_title)) {
                                ?>
                                <div class="prop_area adress_val">
                                <div class="adress"><?= $org_title ?></div>
                                <div class="val_adress"><?
                                    if (!empty($arUser['USER_DATA']['F_NAME'])) { ?>
                                        <?=($arUser['USER_DATA']['NAME'] ? $arUser['USER_DATA']['NAME'] : $arUser['USER_DATA']['F_NAME']); ?><?
                                    } ?>
                                </div>
                                <?
                                if (!empty($arUser['PROPERTY_PHONE_VALUE'])) { ?>
                                    <div class="val_adress">
                                        <? if ($arUser['PROPERTY_PHONE_VALUE'] != '') { ?>
                                            <a href="tel:<?= $arUser['PROPERTY_PHONE_VALUE']; ?>"><?= $arUser['PROPERTY_PHONE_VALUE']; ?></a>
                                        <? } ?>
                                    </div>
                                <? } ?>
                                </div><?
                            }
                        }
                    }

                    /**
                     * Адрес склада покупателя
                     */
                    if($arParams['USER_TYPE'] != 'CLIENT') {
                        ?>
                        <? if (!empty($arItem['PROPERTIES']['DEL_CLIENT_WHADRESS']['VALUE'])
                            || $arItem['PROPERTIES']['CLIENT_WAREHOUSE']['VALUE']
                            && is_array($arResult['CLIENT_WAREHOUSES_LIST'][$arItem['PROPERTIES']['CLIENT_WAREHOUSE']['VALUE']])
                        ) {
                            ?>
                            <div class="prop_area adress_val">
                                <div class="adress">Адрес склада покупателя:</div>
                                <div class="val_adress">
                                    <? if (!empty($arItem['PROPERTIES']['DEL_CLIENT_WHADRESS']['VALUE'])) {
                                        ?>
                                        <?= $arItem['PROPERTIES']['DEL_CLIENT_WHADRESS']['VALUE'] ?>
                                    <? } elseif ($arItem['PROPERTIES']['CLIENT_WAREHOUSE']['VALUE']
                                        && is_array($arResult['CLIENT_WAREHOUSES_LIST'][$arItem['PROPERTIES']['CLIENT_WAREHOUSE']['VALUE']])
                                    ) { ?>
                                        <?= $arResult['CLIENT_WAREHOUSES_LIST'][$arItem['PROPERTIES']['CLIENT_WAREHOUSE']['VALUE']]['ADDRESS'] ?>
                                    <? } ?>
                                </div>
                            </div>
                        <? } ?>
                        <?
                        if (!(($arItem['PAIR_TYPE'] == 'p') && ($arParams['USER_TYPE'] == 'FARMER'))) {
                            /**
                             * Адрес склада АП
                             */ ?>
                            <? if (!empty($arItem['PROPERTIES']['DEL_FARMER_WHADRESS']['VALUE'])
                                || $arItem['PROPERTIES']['FARMER_WAREHOUSE']['VALUE']
                                && is_array($arResult['FARMER_WAREHOUSES_LIST'][$arItem['PROPERTIES']['FARMER_WAREHOUSE']['VALUE']])
                            ) { ?>
                                <div class="prop_area adress_val">
                                    <div class="adress">Адрес склада поставщика:</div>
                                    <div class="val_adress">
                                        <?
                                        if (!empty($arItem['PROPERTIES']['DEL_FARMER_WHADRESS']['VALUE'])) { ?>
                                            <?= $arItem['PROPERTIES']['DEL_FARMER_WHADRESS']['VALUE'] ?>
                                            <?
                                        } elseif ($arItem['PROPERTIES']['FARMER_WAREHOUSE']['VALUE']
                                            && is_array($arResult['FARMER_WAREHOUSES_LIST'][$arItem['PROPERTIES']['FARMER_WAREHOUSE']['VALUE']])
                                        ) { ?>
                                            <?= $arResult['FARMER_WAREHOUSES_LIST'][$arItem['PROPERTIES']['FARMER_WAREHOUSE']['VALUE']]['ADDRESS'] ?>
                                        <? } ?>
                                    </div>
                                </div>
                                <?
                            }
                        }
                    }

                    $params = $arResult['OFFER_PARAMS'][$arItem['PROPERTIES']['OFFER']['VALUE']];
                    $culture = $arItem['PROPERTIES']['CULTURE']['VALUE'];

                    $arTariffRange = client::getTariffRange($arItem['PROPERTIES']['ROUTE']['VALUE'], $arResult['AX_TARIFS']);
                    ?>

                    <div class="prop_area adress_val">
                        <div class="counter_href_area no_margin"><?=$sCultureName;?>, <span class="tons_val"><?=$sTonsFormatted?></span> т (<?=($arItem['PROPERTIES']['B_NDS']['VALUE'] == 'Y' ? 'с НДС' : 'без НДС')?>):
                            <br><br>Цена "с места" (FCA): <span class="csm_price"><?=number_format($arItem['PROPERTIES']['ACC_PRICE_CSM']['VALUE'], 0, ',', ' ');?></span> руб/т
                            <br><?
                            if(count($params) > 0){
                                ?><br/>Качество<?=(!empty($arResult['OFFERS_APPROVED'][$arItem['PROPERTIES']['OFFER']['VALUE']]) ? ' (подтверждено лабораторией)' : '');?>:<?
                                if(!empty($arItem['PROPERTIES']['DEL_PARAMS']['VALUE']['TEXT'])){?>
                                    <?=htmlspecialchars_decode($arItem['PROPERTIES']['DEL_PARAMS']['VALUE']['TEXT']);?><br/>
                                <?}else {
                                    foreach ($params as $param) {
                                        if (!isset($arResult['PARAMS_INFO'][$culture][$param['QUALITY_ID']]['QUALITY_NAME'])) {
                                            continue;
                                        }
                                        ?>
                                        <div class="val_adress">
                                            &nbsp; <?= $arResult['PARAMS_INFO'][$culture][$param['QUALITY_ID']]['QUALITY_NAME'] ?>:
                                            <? if ($param['LBASE_ID'] > 0) { ?>
                                                <b><?= $arResult['LBASE_INFO'][$culture][$param['QUALITY_ID']][$param['LBASE_ID']] ?></b>
                                            <? } else { ?>
                                                <b><?= $param['BASE'] ?><?= ($arResult['UNIT_INFO'][$param['QUALITY_ID']] != '') ? ' ' . $arResult['UNIT_INFO'][$param['QUALITY_ID']] : '' ?></b>
                                            <? } ?>
                                        </div>
                                        <?
                                    }
                                }
                            }?>
                            <br/>До склада <?=$arResult['CLIENT_WAREHOUSES_LIST'][$arItem['PROPERTIES']['CLIENT_WAREHOUSE']['VALUE']]['NAME'];?> (<?=($arTariffRange['FROM'] > 0 ? $arTariffRange['FROM'] . '-' : 'до ') . $arTariffRange['TO'] . ' км'?>) за <?=number_format($arItem['PROPERTIES']['TARIF']['VALUE'], 0, ',', ' ')?> руб/т<?
                            if($arItem['PROPERTIES']['DUMP_RUB']['VALUE']){
                                if($arItem['PROPERTIES']['DUMP_RUB']['VALUE'] > 0){?>,
                                    <br/>прибавка за качество <?=number_format($arItem['PROPERTIES']['DUMP_RUB']['VALUE'], 0, ',', ' ');?> руб/т<?
                                }else{?>
                                    <br/>сброс за качество <?=number_format(abs($arItem['PROPERTIES']['DUMP_RUB']['VALUE']), 0, ',', ' ');?> руб/т<?
                                }?> → прогноз CPT: <?=number_format($base_price_value, 0, ',', ' ')?> руб/т
                                <?
                            }else{?>
                                <br/>→ прогноз CPT: <?=number_format($base_price_value, 0, ',', ' ')?> руб/т
                            <?}?>
                            <?if($arItem['PAIR_TYPE'] == 'p'){
                                $agent_ops_names = array(
                                    'IS_ADD_CERT' => 'Отбор проб и лабораторная диагностика',
                                    'IS_BILL_OF_HEALTH' => 'Карантинное свидетельство',
                                    'IS_VET_CERT' => 'Ветеринарные свидетельства',
                                    'IS_QUALITY_CERT' => 'Декларация о соответствии',
                                    'IS_TRANSFER' => 'Транспортировка',
                                    'IS_SECURE_DEAL' => 'Безопасная сделка',
                                    'IS_AGENT_SUPPORT' => 'Сопровождение сделки'
                                );
                                if($arItem['PROPERTIES']['VOLUME']['VALUE'] == 0){ $arItem['PROPERTIES']['VOLUME']['VALUE'] = 1; }
                                ?>
                                <br/><br/>Услуги Агрохелпера (Заключение договора<?
                                foreach($agent_ops_names as $sCode => $sName){
                                    if(
                                        isset($arItem['PROPERTIES'][$sCode]['VALUE'])
                                        && $arItem['PROPERTIES'][$sCode]['VALUE'] == 'Y'
                                    ){
                                        ?>, <? echo $sName;
                                    }
                                }
                                ?>) - <span class="partner_price_val"><?=number_format($arItem['PROPERTIES']['PARTNER_PRICE']['VALUE'], 0, ',', ' ');?></span> руб (<span class="partner_price_per_ton_val"><?=number_format(round($arItem['PROPERTIES']['PARTNER_PRICE']['VALUE'] / $arItem['PROPERTIES']['VOLUME']['VALUE']), 0, ',', ' ');?></span> руб/т)
                            <?}?>
                        </div>
                        <div class="clear no_line"></div>
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

                        <?if($arItem['PAIR_TYPE'] == 'p'){

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
//                            if ($bCheckedOptions) {
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
//                            }
                            ?></div>

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
                                                ?><div class="price_value"><?=$sCounterOptionDec?> руб/т</div><?
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
                        }else{?>

                            <div class="val_adress slide-description">
                                <div class="radio_group">
                                    <div class="radio_area">
                                        <input type="checkbox"
                                               data-text="<?=GetMessage("IS_ADD_CERT:NAME");?>"
                                               name="IS_ADD_CERT"
                                               id="IS_ADD_CERT"
                                               value="Y"
                                                <?if($arItem['PROPERTIES']['IS_ADD_CERT']['VALUE'] === 'Y'):?>
                                                    data-checked="y"
                                                    checked="checked"
                                                    disabled
                                                <?endif;?>
                                                <?if($arParams['USER_TYPE'] == 'FARMER'):?>
                                                    disabled
                                                <?endif;?>
                                        >
                                    </div>
                                </div>
                                <?=GetMessage("IS_ADD_CERT:DESCRIPTION");?>
                            </div>

                            <div class="val_adress  slide-description">
                                <div><?=GetMessage("DOCS:NAME");?><br><br></div>
                                <?=GetMessage("DOCS:DESCRIPTION");?><br/>
                                <div class="radio_group">
                                    <div class="radio_area">
                                        <input type="checkbox"
                                               data-text="<?=GetMessage("IS_BILL_OF_HEALTH:NAME");?>"
                                               name="IS_BILL_OF_HEALTH"
                                               id="IS_BILL_OF_HEALTH"
                                               value="Y"
                                               <?if($arItem['PROPERTIES']['IS_BILL_OF_HEALTH']['VALUE'] === 'Y'):?>
                                                    data-checked="y"
                                                    checked="checked"
                                                    disabled
                                               <?endif;?>
                                               <?if($arParams['USER_TYPE'] == 'FARMER'):?>
                                                    disabled
                                               <?endif;?>
                                        >
                                    </div>
                                    <div class="radio_area">
                                        <input type="checkbox"
                                               data-text="<?=GetMessage("IS_VET_CERT:NAME");?>"
                                               name="IS_VET_CERT"
                                               id="IS_VET_CERT"
                                               value="Y"
                                               <?if($arItem['PROPERTIES']['IS_VET_CERT']['VALUE'] === 'Y'):?>
                                                    data-checked="y"
                                                    checked="checked"
                                                    disabled
                                               <?endif;?>
                                               <?if($arParams['USER_TYPE'] == 'FARMER'):?>
                                                    disabled
                                               <?endif;?>
                                        >
                                    </div>
                                    <div class="radio_area">
                                        <input type="checkbox"
                                               data-text="<?=GetMessage("IS_QUALITY_CERT:NAME");?>"
                                               name="IS_QUALITY_CERT"
                                               id="IS_QUALITY_CERT"
                                               value="Y"
                                               <?if($arItem['PROPERTIES']['IS_QUALITY_CERT']['VALUE'] === 'Y'):?>
                                                    data-checked="y"
                                                    checked="checked"
                                                    disabled
                                               <?endif;?>
                                               <?if($arParams['USER_TYPE'] == 'FARMER'):?>
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
                                               id="IS_TRANSFER"
                                               value="Y"
                                               <?if($arItem['PROPERTIES']['IS_TRANSFER']['VALUE'] === 'Y'):?>
                                                    data-checked="y"
                                                    checked="checked"
                                                    disabled
                                               <?endif;?>
                                               <?if($arParams['USER_TYPE'] == 'FARMER'):?>
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
                                               id="IS_SECURE_DEAL"
                                               value="Y"
                                               <?if($arItem['PROPERTIES']['IS_SECURE_DEAL']['VALUE'] === 'Y'):?>
                                                    ata-checked="y"
                                                    checked="checked"
                                                    disabled
                                               <?endif;?>
                                               <?if($arParams['USER_TYPE'] == 'FARMER'):?>
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
                                                id="IS_AGENT_SUPPORT"
                                                value="Y"
                                             <?if($arItem['PROPERTIES']['IS_AGENT_SUPPORT']['VALUE'] === 'Y'):?>
                                                 data-checked="y"
                                                 checked="checked"
                                                 disabled
                                             <?endif;?>
                                             <?if($arParams['USER_TYPE'] == 'FARMER'):?>
                                                  disabled
                                             <?endif;?>
                                            >
                                     </div>
                                 </div>
                                <?=GetMessage("IS_AGENT_SUPPORT:DESCRIPTION");?>
                            </div>
                        <?}?>

                        <div class="val_adress  ">
                            <?if($arParams['USER_TYPE'] == 'CLIENT'){?>
                                <div class="accept empty"><input type="submit" class="submit-btn" value="Заказать" ></div>
                            <?}?>
                        </div>
                    </div>
                    </form>

                    <div class="prop_area adress_val black_list_row">
                        <div class="val_adress no_tpad">
                            <div class="accept empty">
                                <div class="black_list_ico"><?=($arParams['USER_TYPE'] == 'FARMER' ? 'Покупатель' : 'Поставщик');?> добавлен в <a href="/<?=($arParams['USER_TYPE'] == 'FARMER' ? 'farmer' : 'client');?>/blacklist/">черный список</a></div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        <?endforeach;?>
    </div>
<?else:?>
    <div class="list_page_rows requests no-item">
        Ни одной пары не найдено
    </div>
<?endif;?>
