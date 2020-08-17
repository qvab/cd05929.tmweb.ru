<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
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
$this->setFrameMode(false);
?>
<?
if (!empty($arResult["ERRORS"])) {
?>
    <?ShowError(implode("<br />", $arResult["ERRORS"]))?>
<?
}

if (strlen($arResult["MESSAGE"]) > 0) {
?>
    <?ShowNote($arResult["MESSAGE"])?>
<?
}
$k = 1;
?>
<a class="go_back cross" href="<?=$arResult['BACK_URL']?>"></a>
<form name="iblock_add" class="request_add" action="<?=POST_FORM_ACTION_URI?>" method="post" enctype="multipart/form-data">

    <div class="fblock_fm row">
        <div class="row_val">
            <select <? if (count($arResult['CLIENTS_DATA']) > 3) { ?>data-search="y"<? } ?> name="client_id" placeholder="Выберите поставщика">
                <option value="0">Все покупатели</option>
                <?
                foreach($arResult['CLIENTS_DATA'] as $cur_id => $cur_data){
                    $cur_active = false;
                    if(isset($arResult["ELEMENT_PROPERTIES"]['CLIENT']['VALUE'])
                        && $arResult["ELEMENT_PROPERTIES"]['CLIENT']['VALUE'] == $cur_id
                    ){
                        $cur_active = true;
                    }

                    //проверка прав агента покупателя на сохранение запроса (для каждого случая выводится своя ошибка)
                    $user_right = 'n';
                    if(!isset($cur_data['VERIFIED'])
                        || $cur_data['VERIFIED'] != 'Y'
                    ){
                        $user_right = 'nv';
                    }elseif($cur_data['UF_DEMO'] == 1){
                        $user_right = 'nd';
                    }elseif(!isset($cur_data['LINK_DOC'])
                        || $cur_data['LINK_DOC'] != 'Y'
                    ){
                        $user_right = 'ndoc';
                    }else{
                        $user_right = 'y';
                    }

                    if($cur_data['NICK'] != ''){?>
                        <option data-right="<?=$user_right?>" value="<?=$cur_id;?>" <?if($cur_active){?>selected="selected"<?}?> ><?=$cur_data['NICK'];?></option>
                    <?}elseif($cur_data['NAME'] == ''){?>
                        <option data-right="<?=$user_right?>" value="<?=$cur_id;?>" <?if($cur_active){?>selected="selected"<?}?> ><?=$cur_data['EMAIL'];?></option>
                    <?}else{?>
                        <option data-right="<?=$user_right?>" value="<?=$cur_id;?>" <?if($cur_active){?>selected="selected"<?}?> ><?=$cur_data['NAME'];?> (<?=$cur_data['EMAIL'];?>)</option>
                    <?}
                }?>
            </select>
        </div>
    </div>
    <?=bitrix_sessid_post()?>
    <input type="hidden" name="save" value="Y">
    <input type="hidden" name="back_url" value="<?=$arResult['BACK_URL']?>">
    <?
    if ($_GET["bitrix_include_areas"] == "Y") {
    ?>
        <input type="hidden" name="debug" value="Y">
    <?
    }
    ?>
    <div class="request-block1 row">
        <div class="request-block-intro">
            <div class="step-title row_head"><?=$k++?>. Выберите тип товара</div>
            <div class="row_val">
                <div class="radio_group">
                    <?
                    foreach ($arResult['CULTURE_GROUP_LIST'] as $item) {
                    ?>
                        <div class="radio_area">
                            <input type="radio" name="cgroup" data-text="<?=$item['NAME']?>" id="cgroup<?=$item['ID']?>" value="<?=$item['ID']?>" <? if ($item["ID"] == $arResult["VALUES"]["CGROUP"]) { ?>checked="checked"<? } ?>>
                        </div>
                    <?
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
    <div class="request-block2 row">
        <?
        if ($arResult["SAVE"] == "Y" && sizeof($arResult['CULTURE_LIST']) > 0) {
        ?>
            <div class="request-block-intro">
                <div class="step-title row_head"><?=$k++?>. Выберите товар</div>
                <div class="row_val">
                    <div class="radio_group">
                    <?
                    foreach ($arResult['CULTURE_LIST'] as $item) {
                    ?>
                        <div class="radio_area">
                            <input type="radio" name="csort" data-text="<?=$item['NAME']?>" id="csort<?=$item['ID']?>" value="<?=$item['ID']?>" <? if ($item["ID"] == $arResult["VALUES"]["CSORT"]) { ?>checked="checked"<? } ?>>
                        </div>
                    <?
                    }
                    ?>
                    </div>
                </div>
            </div>
        <?
        }
        ?>
    </div>
    <div class="request-block3 row">
        <?
        if ($arResult["SAVE"] == "Y" && sizeof($arResult['CULTURE_LIST']) > 0) {
        ?>
            <div class="request-block-intro">
                <div class="step-title row_head"><?=$k++?>. Укажите параметры ограничений и сетку сбросов качества запрашиваемого продукта</div>
                <div class="row_val">
                    <?
                    foreach ($arResult['PARAMS_LIST'] as $item) {
                        if ($item["LBASE_ID"] > 0) {
                        ?>
                            <div class="sub_row">
                                <div class="quality-param-title"><?=$item["QUALITY_NAME"]?>:</div>
                                <div class="quality-param-intro">
                                    <select name="param[<?=$item['QUALITY_ID']?>][LBASE]">
                                        <?
                                        foreach ($item["LIST"] as $l) {
                                        ?>
                                            <option value="<?=$l['ID']?>" <? if ($l["ID"] == $arResult["VALUES"]["PARAM"][$item['QUALITY_ID']]["LBASE"]) { ?>selected=""<? } ?>><?=$l["NAME"]?></option>
                                        <?
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                        <?
                        }
                        else {
                            if ($arResult["VALUES"]["PARAM"][$item['QUALITY_ID']]["MIN"] != $item['MIN']
                                || $arResult["VALUES"]["PARAM"][$item['QUALITY_ID']]["MAX"] != $item['MAX']
                                || sizeof($arResult["VALUES"]["PARAM"][$item['QUALITY_ID']]["DUMP"]) > 0
                            ) {
                                $change = 1;
                            }
                            else {
                                $change = 0;
                            }
                            ?>

                            <div class="sub_row txt">
                                <div class="quality-param-intro txt">
                                    <div class="name"><?=$item["QUALITY_NAME"]?></div>
                                    <div class="prop_cntrl_area">
                                        <span class="minus minus_bg" data-step="<?=$item['STEP']?>" onclick="rrsClickMin(this);"></span>
                                        <input type="text" name="param[<?=$item['QUALITY_ID']?>][BASE]" value="<?=$arResult["VALUES"]["PARAM"][$item['QUALITY_ID']]["BASE"]?>">
                                        <span class="plus plus_bg" data-step="<?=$item['STEP']?>" onclick="rrsClickMax(this);"></span>
                                        <?
                                        if ($change == 1) {
                                        ?>
                                            <div class="add-dump collapse">- Свернуть</div>
                                        <?
                                        }
                                        else {
                                            if ($item["TYPE_ID"] == 41) {
                                            ?>
                                                <div class="add-dump">+ Добавить ограничения и сбросы</div>
                                            <?
                                            }
                                            else {
                                            ?>
                                                <div class="add-dump">+ Добавить ограничения</div>
                                            <?
                                            }
                                        }
                                        ?>
                                    </div>
                                </div>

                                <div class="quality-dump-intro<? if ($change == 1) { ?> active<? } ?>" <? if ($item["TYPE_ID"] == 41) { ?>data-dump="Y"<? } ?>>
                                    <div class="quality-param-intro fst">
                                        <span class="minus minus_bg" data-step="<?=$item['STEP']?>" onclick="rrsClickMin(this);"></span>
                                        <input type="text" name="param[<?=$item['QUALITY_ID']?>][MIN]" value="<?=$arResult["VALUES"]["PARAM"][$item['QUALITY_ID']]["MIN"]?>">
                                        <span class="plus plus_bg" data-step="<?=$item['STEP']?>" onclick="rrsClickMax(this);"></span>
                                        <div class="min">min</div>
                                    </div>
                                    <div class="quality-param-intro sec">
                                        <span class="minus minus_bg" data-step="<?=$item['STEP']?>" onclick="rrsClickMin(this);"></span>
                                        <input type="text" name="param[<?=$item['QUALITY_ID']?>][MAX]" value="<?=$arResult["VALUES"]["PARAM"][$item['QUALITY_ID']]["MAX"]?>">
                                        <span class="plus plus_bg" data-step="<?=$item['STEP']?>" onclick="rrsClickMax(this);"></span>
                                        <div class="max">max</div>
                                        <?
                                        if ($item["TYPE_ID"] == 41) {
                                        ?>
                                            <div class="add-dump-table<?if(sizeof($arResult["VALUES"]["PARAM"][$item['QUALITY_ID']]["DUMP"]) > 0){?> inactive<?}?>">+ Назначить сброс/прибавку</div>
                                            <?/*<div class="add_straight_or<?if(sizeof($arResult["VALUES"]["PARAM"][$item['QUALITY_ID']]["DUMP"]) > 0){?> inactive<?}?>">или</div>
                                            <div class="add-dump-table straight<?if(sizeof($arResult["VALUES"]["PARAM"][$item['QUALITY_ID']]["DUMP"]) > 0){?> inactive<?}?>">прямой сброс</div>*/?>
                                        <?
                                        }
                                        ?>
                                    </div>

                                    <?
                                    if ($item["TYPE_ID"] == 41) {
                                    ?>
                                        <div class="quality-dump-table-intro<? if (sizeof($arResult["VALUES"]["PARAM"][$item['QUALITY_ID']]["DUMP"]) > 0) { ?> active<? } ?>" data-step="<?=$item['STEP']?>" data-param="<?=$item['QUALITY_ID']?>">
                                            <div class="add-dump-item<? if (sizeof($arResult["VALUES"]["PARAM"][$item['QUALITY_ID']]["DUMP"]) == 6 || $arResult["VALUES"]["PARAM"][$item['QUALITY_ID']]['DIRECT_DUMP'] == 'Y') { ?> inactive<? } ?>" onclick="rrsAddDumpItem(this);" >+ Добавить еще</div>
                                            <?
                                            if (sizeof($arResult["VALUES"]["PARAM"][$item['QUALITY_ID']]["DUMP"]) > 0) {
                                                foreach ($arResult["VALUES"]["PARAM"][$item['QUALITY_ID']]["DUMP"] as $dumpItem) {
                                                ?>
                                                    <div class="quality-dump-table-item">
                                                        <div class="quality-param-intro d_fst">
                                                            <span class="minus minus_bg" data-step="<?=$item['STEP']?>" onclick="rrsClickMin(this);"></span>
                                                            <input type="text" name="param[<?=$item['QUALITY_ID']?>][DUMP][MIN][]" value="<?=$dumpItem['MIN']?>">
                                                            <span class="plus plus_bg" data-step="<?=$item['STEP']?>" onclick="rrsClickMax(this);"></span>
                                                        </div>
                                                        <div class="quality-param-intro d_sec">
                                                            <span class="minus minus_bg" data-step="<?=$item['STEP']?>" onclick="rrsClickMin(this);"></span>
                                                            <input type="text" name="param[<?=$item['QUALITY_ID']?>][DUMP][MAX][]" value="<?=$dumpItem['MAX']?>">
                                                            <span class="plus plus_bg" data-step="<?=$item['STEP']?>" onclick="rrsClickMax(this);"></span>
                                                        </div>
                                                        <div class="quality-param-intro percent_block">
                                                            <span class="minus minus_bg" data-step=0.5 onclick="rrsClickMin(this);"></span>
                                                            <input type="text" name="param[<?=$item['QUALITY_ID']?>][DUMP][DISCOUNT][]" value="<?=$dumpItem['DISCOUNT']?>">
                                                            <span class="plus plus_bg" data-step=0.5 onclick="rrsClickMax(this);"></span>
                                                            <div class="prc_pic">%</div>
                                                            <?
                                                            if ($dumpItem['DISCOUNT'] > 0)
                                                                $text = 'прибавка';
                                                            elseif ($dumpItem['DISCOUNT'] < 0)
                                                                $text = 'сброс';
                                                            else
                                                                $text = '';
                                                            ?>
                                                            <div class="min"><?=$text?></div>
                                                        </div>
                                                        <div class="delete-dump-item" onclick="rrsDeleteDumpItem(this);">удалить</div>
                                                        <div class="clear"></div>
                                                        <?if($arResult["VALUES"]["PARAM"][$item['QUALITY_ID']]['DIRECT_DUMP'] == 'Y'){?>
                                                            <input name="param[<?=$item['QUALITY_ID']?>][DUMP][STRAIGHT]" value="Y" type="hidden" />
                                                        <?}?>
                                                    </div>
                                                <?
                                                }
                                            }
                                            ?>
                                        </div>
                                    <?
                                    }
                                    ?>
                                </div>

                            </div>
                        <?
                        }
                    }
                    ?>
                </div>
            </div>
        <?
        }
        ?>
    </div>

    <div class="request-blocks" <? if (!$arResult["ELEMENT"]["ID"]) { ?>style="display: none;"<? } ?>>

        <div class="request-block4 row">
            <div class="request-block-intro">
                <div class="step-title row_head"><?=$k++?>. Укажите количество</div>
                <div class="row_val">
                    <input type="text" name="volume" placeholder="Введите количество тонн" value="<?=($arResult['ELEMENT_PROPERTIES']['VOLUME']['VALUE'] ?: 5000);?>" />
                    <span class="ton_pos">т.</span>
                </div>
            </div>
        </div>
        <div class="request-block5 row">
            <div class="request-block-intro">
                <div class="step-title row_head"><?=$k++?>. Условия доставки</div>
                <div class="row_val">
                    <select name="delivery">
                        <option>выбрать</option>
                        <?
                        foreach ($arResult["DELIVERY_LIST"] as $item) {
                        ?>
                            <option value="<?=$item['ID']?>" <? if ($item["ID"] == $arResult["VALUES"]["DELIVERY"]) { ?>selected="selected"<? } ?>><?=$item["NAME"]?></option>
                        <?
                        }
                        ?>
                    </select>
                </div>
                <span class="tarif_info">
                    Цены, предлагаемые продавцу, будут снижены на размер тарифа перевозки.
                    <a class="request_tariffs" href="/client_agent/tariffs/?client=<?=$arResult["ELEMENT_PROPERTIES"]['CLIENT']['VALUE']?>" target="_blank">Справка по действующим тарифам в Агрохелпере.</a>
                </span>
                <div class="additional_row remoteness<? if ($arResult["VALUES"]["DELIVERY"] == 385) { ?> active<? } ?>">
                    <input type="text" class="min-remoteness" placeholder="Минимальная удаленность, км." name="min_remoteness" value="<?=$arResult['VALUES']['MIN_REMOTENESS']?>">
                    <input type="text" placeholder="Максимальная удаленность, км." name="remoteness" value="<?=$arResult['VALUES']['REMOTENESS']?>">
                </div>
            </div>
        </div>
        <div class="request-block6 row">
            <div class="request-block-intro">
                <div class="step-title row_head"><?=$k++?>. Потребность в документах</div>
                <div class="row_val">
                    <div class="radio_group">
                        <?
                        foreach ($arResult['DOCS_LIST'] as $item) {
                        ?>
                            <div class="radio_area">
                                <input type="checkbox" data-text="<?=$item['NAME']?>" name="docs[<?=$item['ID']?>]" id="docs[<?=$item['ID']?>]" value="Y" <? if (in_array($item['ID'], $arResult["VALUES"]["DOCS"])) { ?>checked="checked"<? } ?>>
                            </div>
                        <?
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="request-block7 row">
            <div class="request-block-intro">
                <div class="step-title row_head"><?=$k++?>. Тип оплаты</div>
                <div class="row_val">
                    <select name="payment">
                        <option>выбрать</option>
                        <?
                        foreach ($arResult["PAYMENT_LIST"] as $item) {
                        ?>
                            <option value="<?=$item['ID']?>" <? if ($item["ID"] == $arResult["VALUES"]["PAYMENT"]) { ?>selected="selected"<? } ?>><?=$item["VALUE"]?></option>
                        <?
                        }
                        ?>
                    </select>
                </div>
                <div class="additional_row percent<? if ($arResult["VALUES"]["PAYMENT"] == 81) { ?> active<? } ?>">
                    <input type="text" placeholder="Процент предоплаты" name="percent" value="<?=$arResult['VALUES']['PERCENT']?>">
                </div>
                <div class="additional_row delay<? if ($arResult["VALUES"]["PAYMENT"] == 82) { ?> active<? } ?>">
                    <input type="text" placeholder="Количество дней отсрочки" name="delay" value="<?=$arResult['VALUES']['DELAY']?>">
                </div>
            </div>
        </div>

        <?
        if ($arResult['PROFILE']['PROPERTY_NDS_CODE'] == 'Y') {
        ?>
            <div class="request-block8 row">
                <div class="request-block-intro">
                    <div class="step-title row_head"><?=$k++?>. Отправлять мой запрос продавцам:</div>
                    <div class="row_val">
                        <div class="radio_group">
                            <?
                            foreach ($arResult['NDS_LIST'] as $item) {
                            ?>
                                <div class="radio_area">
                                    <input type="checkbox" data-text="<?=$item['VALUE']?>" name="nds[<?=$item['ID']?>]" id="nds[<?=$item['ID']?>]" value="<?=$item['ID']?>" <? if (empty($arResult["VALUES"]["NDS"]) || in_array($item['ID'], $arResult["VALUES"]["NDS"])) { ?>checked="checked"<? } ?>>
                                </div>
                            <?
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        <?
        }
        ?>

        <?/*<div class="request-block9 row">
            <div class="request-block-intro">
                <div class="step-title row_head"><?=$k++?>. Укажите срочность и утвердите стоимость и адрес доставки</div>
                <div class="row_val">
                    <div class="radio_group">
                    <?
                    if (is_array($arResult['MARGIN_LIST']) && sizeof($arResult['MARGIN_LIST']) > 0) {
                        if (!$arResult["VALUES"]["URGENCY"]) {
                            $checked = 'standart';
                        }
                        foreach ($arResult['MARGIN_LIST'] as $item) {
                        ?>
                            <div class="radio_area">
                                <input data-text="<?=$item["NAME"]?>" type="radio" id="<?=$item['CODE']?>" name="urgency" value="<?=$item['ID']?>" <? if ($item['ID'] == $arResult["VALUES"]["URGENCY"] || $item['CODE'] == $checked) { ?>checked="checked"<? } ?> />
                            </div>
                        <?
                        }
                    }
                    ?>
                    </div>
                </div>
            </div>
        </div>*/?>

        <div class="row">
            <input type="button" class="empty_but calculate-btn" value="Рассчитать" onclick="rrsCalculateRequest(this);">
        </div>

        <div class="request-block-r row">
            <?
            if ($arResult['ELEMENT']['ID'] > 0) {

            ?>
                <div class="request-block-intro">
                    <?
                    if (is_array($arResult['CLIENT_WAREHOUSES']) && sizeof($arResult['CLIENT_WAREHOUSES']) > 0) {
                    ?>
                        <div style="margin-bottom: 24px;">Базисная цена</div>
                        <div class="radio_group">
                            <?
                            $qw = 0;
                            foreach ($arResult['CLIENT_WAREHOUSES'] as $warehouse) {
                                $checked = false;
                                if (in_array($warehouse['ID'], array_keys($arResult['ELEMENT_COST']))) {
                                    $checked = true;
                                    $qw++;
                                }
                                ?>

                                <div class="wh_price">
                                    <div class="sub_row txt">
                                        <div class="min_price">
                                            <?=number_format($arResult['PRICES'][$warehouse['CENTER_ID']]['BASE_PRICE_PASSIVE'], 0, ',', ' ')?>
                                            <span>min</span>
                                        </div>
                                        <div class="quality-param-intro txt">
                                            <span class="minus minus_bg" data-step="50" onclick="rrsClickMinPrice(this);" data-min="<?=$arResult['PRICES'][$warehouse['CENTER_ID']]['BASE_PRICE_PASSIVE']?>"></span>
                                            <input type="text" name="" value="<?=number_format($arResult['PRICES'][$warehouse['CENTER_ID']]['BASE_PRICE_STANDART'], 0, ',', ' ')?>" readonly>
                                            <span class="plus plus_bg" data-step="50" onclick="rrsClickMaxPrice(this);" data-max="<?=$arResult['PRICES'][$warehouse['CENTER_ID']]['BASE_PRICE_ACTIVE']?>"></span>
                                        </div>
                                        <div class="max_price">
                                            <?=number_format($arResult['PRICES'][$warehouse['CENTER_ID']]['BASE_PRICE_ACTIVE'], 0, ',', ' ')?>
                                            <span>max</span>
                                        </div>
                                    </div>
                                    <div class="cost-item radio_area">
                                        <input type="checkbox" id="warehouse[<?=$warehouse['ID']?>]" name="warehouse[<?=$warehouse['ID']?>]" value="<?=$arResult['PRICES'][$warehouse['CENTER_ID']]['BASE_PRICE_STANDART']?>" <? if ($checked) { ?>checked="checked"<? } ?> onclick="rrsCheckSubmit();">
                                        <label>
                                            <span class="name"><?=$warehouse['NAME']?></span>
                                            <span class="address"><?=$warehouse['ADDRESS']?></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="clear"></div>
                            <?
                            }
                            ?>
                        </div>
                    <?
                    }
                    else {
                        echo '<div class="form_line_error">Ошибка! Не найдено ни одного склада.</div>';
                    }
                    ?>
                </div>
            <?
            }
            ?>
        </div>

        <?
        if ($GLOBALS['DEMO'] != 'Y' && $arResult['LINKED_PARTNER'] > 0 && $arResult['VERIFIED_PARTNER'] > 0) {
            ?>
            <div class="request-block10 row" <? if (!$arResult['ELEMENT']['ID']) { ?>style="display: none;"<? } ?>>
                <div class="request-block-intro">
                    <div class="step-title row_head"><?=$k++?>. Подтвердите ваше согласие с условиями работы площадки</div>
                    <div class="radio_group">
                        <div class="radio_area">
                            <input type="checkbox" data-text="Согласен осуществить сделку по указанной цене" name="agree-cost" id="agree-cost" value="Y" checked="checked">
                        </div>
                        <div class="radio_area">
                            <input type="checkbox" data-text="Принимаю пользовательское соглашение" name="agreement" id="agreement" value="Y" checked="checked">
                        </div>
                    </div>
                </div>
            </div>
        <?
        }
        ?>

    </div>

    <div class="request-block10 row">
        <div class="request-block-intro">
            <div class="step-title row_head"><?=$k++?>. Подтвердите согласие с условиями работы площадки</div>
            <div class="radio_group">
                <div class="radio_area">
                    <input name="agree-cost" id="agree-cost" type="checkbox" checked="checked" value="Y" data-text="Подтверждаю, что покупатель согласен осуществить сделку по указанной цене" />
                </div>
                <div class="radio_area">
                    <input name="agreement" id="agreement" type="checkbox" checked="checked" value="Y" data-text="Подтверждаю, что покупатель ознакомлен и принимает пользовательское соглашение" />
                </div>
            </div>
        </div>
    </div>

    <div class="spec_row_default">
        <div class="row">
            <div class="no_deal_rights spec_row_client_link" <?if(isset($arResult["ELEMENT_PROPERTIES"]['CLIENT']['VALUE']) && is_numeric($arResult["ELEMENT_PROPERTIES"]['CLIENT']['VALUE']))
            {?> style="display: none;"<?}?>>Выберите покупателя</div>
            <div class="no_deal_rights spec_row_ok">Для создания запроса необходимо, чтобы организатор подтвердил привязку покупателя</div>
            <div class="no_deal_rights spec_row_nodoc">Для создания запроса необходимо, чтобы организатор загрузил договор привязки покупателя</div>
            <div class="no_deal_rights spec_row_undemo">Для создания запроса необходимо <a href="/profile/make_full_mode/?uid=">перевести покупателя из демо-режима в полноценный режим работы</a></div>
            <div class="no_deal_rights spec_row_noag">У вас нет прав на создание запроса для выбранного покупателя</div>
        </div>
        <div class="row">
            <input type="button" name="iblock_submit" class="submit-btn <? if ($arResult['SAVE'] == 'Y' && $qw > 0) { ?> active<? } else { ?> inactive<? } ?>" value="<?=GetMessage("IBLOCK_FORM_SUBMIT")?>" />
        </div>
    </div>
    <?/*
    if ($GLOBALS['DEMO'] != 'Y') {
        if (!$arResult['VERIFIED_PARTNER']) {
            ?>
            <div class="row">
                <div class="no_deal_rights">Для создания запроса необходимо чтобы организатор подтвердил привязку покупателя</div>
            </div>
            <div class="row">
                <input type="button" name="iblock_submit" class="submit-btn inactive" value="<?=GetMessage("IBLOCK_FORM_SUBMIT")?>" />
            </div>
        <?
        }
        else {
        ?>
            <div class="row">
                <input type="button" name="iblock_submit" class="submit-btn<? if ($arResult['SAVE'] == 'Y' && $qw > 0) { ?> active<? } else { ?> inactive<? } ?>" value="<?=GetMessage("IBLOCK_FORM_SUBMIT")?>" />
            </div>
        <?
        }
    }else{?>
        <div class="row">
            <div class="no_deal_rights">Для создания запроса необходимо <a href="/client/profile/">перейти из демо-режима в полноценный режим работы</a></div>
        </div>
        <div class="row">
            <input type="button" name="iblock_submit" class="submit-btn inactive" value="<?=GetMessage("IBLOCK_FORM_SUBMIT")?>" />
        </div>
    <?}*/
    ?>
</form>