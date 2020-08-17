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

global $USER;
$req_limit = client::checkAvailableRequestLimit($USER->GetID());

$allowed_save = true;
?>
<a class="go_back cross" href="<?=$arResult['BACK_URL']?>"></a>
<form name="iblock_add" class="request_add" action="<?=POST_FORM_ACTION_URI?>" method="post" enctype="multipart/form-data" data-nds="<?=$arResult['NDS_VAL'];?>">
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
                <div class="step-title row_head"><?=$k++?>. Указана стандартная сетка прямых сбросов, скорректируйте по необходимости</div>
                <div class="row_val">
                    <?
                    $nNdsValue = rrsIblock::getConst('nds');

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
                    <select name="delivery" data-fcaid="<?
                        foreach ($arResult["DELIVERY_LIST"] as $item) {
                            if($item['CODE'] == 'N'){
                                echo $item['ID'];break;
                            }
                        }
                    ?>">
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
                    <?/*Цены, предлагаемые продавцу, будут снижены на размер тарифа перевозки.
                    <a href="/client/tariffs/" target="_blank">Справка по действующим тарифам в Агрохелпере.</a>*/?>
                </span>
                <?/*
                <div class="additional_row remoteness<? if ($arResult["VALUES"]["DELIVERY"] == 385) { ?> active<? } ?>">
                    <input type="text" class="min-remoteness" placeholder="Минимальная удаленность, км." name="min_remoteness" value="<?=$arResult['VALUES']['MIN_REMOTENESS']?>">
                    <input type="text" placeholder="Максимальная удаленность, км." name="remoteness" value="<?=$arResult['VALUES']['REMOTENESS']?>">
                </div>
                */?>
            </div>
        </div>
        <?/*<div class="request-block6 row">
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
            </div>
        </div>
        */?>
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

        <?/*<div class="row">
            <input type="button" class="empty_but calculate-btn" value="Рассчитать" onclick="rrsCalculateRequest(this);">
        </div>*/?>

        <div class="request-block-r row">
            <?
            if ($arResult['ELEMENT']['ID'] > 0) {
                $bNDS = (rrsIblock::getPropListId('client_request', 'USER_NDS', $arResult['ELEMENT_PROPERTIES']['USER_NDS']['VALUE']) == 'yes');
            ?>
                <div class="request-block-intro">
                    <div class="step-title row_head"><?=$k++;?>. Выберите склад и установите цену (<span class="fca_val">CPT</span>) закупки:</div>
                    <?
                    if (is_array($arResult['CLIENT_WAREHOUSES']) && sizeof($arResult['CLIENT_WAREHOUSES']) > 0) {
                    ?>
                        <?/*<div style="margin-bottom: 24px;">Базисная цена, (<?= $bNDS ? 'с' : 'без'?> НДС)</div>
                        if(isset($_GET['mode'])
                            && $_GET['mode'] == 'change'
                        ){*/?>
                            <div class="prices_switcher">
                                <div class="prices_base">Мои цены</div><div class="prices_diver">/</div><div class="prices_math">Цены Агрохелпер</div>
                            </div>
                        <?/*}*/?>
                        <div class="radio_group">
                            <?
                            $qw = 0;
                            foreach ($arResult['CLIENT_WAREHOUSES'] as $warehouse) {
                                $checked = false;
                                if (in_array($warehouse['ID'], array_keys($arResult['ELEMENT_COST']))) {
                                    $checked = true;
                                    $qw++;
                                }

                                //получаем значения цен, округленные до 50 (<=25 округляется до 0, <=75 округляется до 50, >75 округляется до 100)
                                $iTempMod = $arResult['PRICES'][$warehouse['CENTER_ID']]['BASE_PRICE_PASSIVE'] % 100;
                                $iMinPrice = floor($arResult['PRICES'][$warehouse['CENTER_ID']]['BASE_PRICE_PASSIVE'] / 100) * 100 + ($iTempMod > 75 ? 100 : ($iTempMod > 25 ? 50 : 0));
                                $iTempMod = $arResult['PRICES'][$warehouse['CENTER_ID']]['BASE_PRICE_ACTIVE'] % 100;
                                $iMaxPrice = floor($arResult['PRICES'][$warehouse['CENTER_ID']]['BASE_PRICE_ACTIVE'] / 100) * 100 + ($iTempMod > 75 ? 100 : ($iTempMod > 25 ? 50 : 0));
                                $iTempMod = $arResult['PRICES'][$warehouse['CENTER_ID']]['BASE_PRICE_STANDART'] % 100;
                                $iCurrentPrice = floor($arResult['PRICES'][$warehouse['CENTER_ID']]['BASE_PRICE_STANDART'] / 100) * 100 + ($iTempMod > 75 ? 100 : ($iTempMod > 25 ? 50 : 0));

                                //значения цен с противоположной СНО покупателю, округленные до 50
                                $nPassiveSno = 0;
                                $nActiveSno = 0;
                                $nStandartSno = 0;
                                //в $nCurrentPriceValue учитывается вариант, если берется цена из изменяемого запроса, а не из нового
                                $nCurrentPriceValue = (isset($arResult['DEFAULT_PRICES'][$warehouse['ID']]) ? $arResult['DEFAULT_PRICES'][$warehouse['ID']] : $iCurrentPrice);
                                $iMinPriceSno = 0;
                                $iMaxPriceSno = 0;
                                $iCurrentPriceSno = 0;
                                if($bNDS){
                                    //вычитаем НДС из цены
                                    $nPassiveSno = $iMinPrice / (1 + 0.01 * $nNdsValue);
                                    $nActiveSno = $iMaxPrice / (1 + 0.01 * $nNdsValue);
                                    $nStandartSno = $nCurrentPriceValue / (1 + 0.01 * $nNdsValue);
                                }else{
                                    //добавляем НДС в цену
                                    $nPassiveSno = $iMinPrice + ($iMinPrice * 0.01 * $nNdsValue);
                                    $nActiveSno = $iMaxPrice + ($iMaxPrice * 0.01 * $nNdsValue);
                                    $nStandartSno = $nCurrentPriceValue + ($nCurrentPriceValue * 0.01 * $nNdsValue);
                                }
                                $iTempMod = $nPassiveSno % 100;
                                $iMinPriceSno = floor($nPassiveSno / 100) * 100 + ($iTempMod > 75 ? 100 : ($iTempMod > 25 ? 50 : 0));
                                $iTempMod = $nActiveSno % 100;
                                $iMaxPriceSno = floor($nActiveSno / 100) * 100 + ($iTempMod > 75 ? 100 : ($iTempMod > 25 ? 50 : 0));
                                $iTempMod = $nStandartSno % 100;
                                $iCurrentPriceSno = floor($nStandartSno / 100) * 100 + ($iTempMod > 75 ? 100 : ($iTempMod > 25 ? 50 : 0));

                                ?>

                                <div class="wh_price">
                                    <div class="sub_row txt">
                                        <div class="min_price">
                                            <?=number_format($iMinPrice, 0, ',', ' ')?>
                                            <span>min</span>
                                        </div>
                                        <div class="quality-param-intro txt">
                                            <span class="minus minus_bg" data-step="50" onclick="rrsClickMinPrice(this);" data-min="<?=$iMinPrice?>"></span>
                                            <?if(isset($arResult['DEFAULT_PRICES'][$warehouse['ID']])){?>
                                                <input type="text" name="wh_prc" value="<?=number_format($arResult['DEFAULT_PRICES'][$warehouse['ID']], 0, ',', ' ')?>" data-math="<?=$iCurrentPrice?>" data-def="<?=$arResult['DEFAULT_PRICES'][$warehouse['ID']]?>">
                                            <?}else{?>
                                                <input type="text" name="wh_prc" value="<?=number_format($iCurrentPrice, 0, ',', ' ')?>" data-math="<?=$iCurrentPrice?>" data-def="0">
                                            <?}?>
                                            <span class="plus plus_bg" data-step="50" onclick="rrsClickMaxPrice(this);" data-max="<?=$iMaxPrice?>"></span>
                                        </div>
                                        <div class="max_price">
                                            <?=number_format($iMaxPrice, 0, ',', ' ')?>
                                            <span>max</span>
                                        </div>
                                        <div class="other_sno_area">
                                            <div class="label_cur"><?=($bNDS ? 'С' : 'Без');?> НДС</div>
                                            <div class="label"><?=($bNDS ? 'Без' : 'С');?> НДС</div>
                                            <div class="minimum_price"><?=number_format($iMinPriceSno, 0, ',', ' ');?></div>
                                            <div class="current_price"><?=number_format($iCurrentPriceSno, 0, ',', ' ');?></div>
                                            <div class="maximum_price"><?=number_format($iMaxPriceSno, 0, ',', ' ');?></div>
                                        </div>
                                    </div>
                                    <div class="cost-item radio_area">
                                        <?if(isset($arResult['DEFAULT_PRICES'][$warehouse['ID']])){?>
                                            <input type="radio" data-whid="<?=$warehouse['ID'];?>" id="warehouse[<?=$warehouse['ID']?>]" name="warehouse" value="<?=$warehouse['ID']?>|<?=$arResult['DEFAULT_PRICES'][$warehouse['ID']]?>" <? if ($checked) { ?>checked="checked"<? } ?> onclick="rrsCheckSubmit();">
                                        <?}else{?>
                                            <input type="radio" data-whid="<?=$warehouse['ID'];?>" id="warehouse[<?=$warehouse['ID']?>]" name="warehouse" value="<?=$warehouse['ID']?>|<?=$iCurrentPrice?>" <? if ($checked) { ?>checked="checked"<? } ?> onclick="rrsCheckSubmit();">
                                        <?}?>
                                        <label>
                                            <span class="name"><?=$warehouse['NAME']?></span>
                                            <span class="address"><?=$warehouse['ADDRESS']?></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="clear"></div>
                            <?
                            }

                            /*
                             *                     <div class="radio_area">
                            <input type="radio" name="csort" data-text="<?=$item['NAME']?>" id="csort<?=$item['ID']?>" value="<?=$item['ID']?>" <? if ($item["ID"] == $arResult["VALUES"]["CSORT"]) { ?>checked="checked"<? } ?>>
                        </div>
                             * */

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
        //проверяем выполнение лимита созданных запросов
        if($req_limit['REMAINS'] == 0
            && (!isset($arResult['ACTIVE_REQ'])
                || $arResult['ACTIVE_REQ'] != 'Y'
            )
        ){
            $allowed_save = false;
            ?>
            <div class="row">
                <div class="form_line_error">Исчерпан лимит создания запросов<br/>(лимит запросов: <?=$req_limit['CNT'];?>)</div>
            </div>
        <?}
        ?>

        <div class="row regions_row">
            <div class="request-block-intro">
                <div class="step-title row_head"><?=$k++;?>. Выберите регионы для отправки запроса</div>
                <div class="row_val radio_group additional_row">
                    <div class="linked_regions_label remove_href"><a href="javascript: void(0);" onclick="removeCheckCurRegions(this);">Снять все</a></div>
                    <div class="linked_regions_label select_href"><a href="javascript: void(0);" onclick="checkCurRegions(this);">Выбрать все</a></div>
                    <div class="linked_regions_data"></div>
                    <div class="other_regions_label">
                        <a href="javascript: void(0);" onclick="showHideOtherRegions(this);" data-showtext="Другие регионы" data-hidetext="Скрыть регионы">Другие регионы</a>
                    </div>
                    <div class="other_regions_data">
                        <div class="remove_href"><a href="javascript: void(0);" onclick="removeCheckAllRegions(this);">Снять все</a></div>
                        <div class="select_href"><a href="javascript: void(0);" onclick="checkAllRegions(this);">Выбрать все</a></div>
                        <?foreach($arResult['REGIONS_DATA'] as $reg_id => $reg_name){?>
                            <div class="radio_area<?if(isset($arResult['LINKED_TO_WH_REGIONS'][$reg_id])){
                                foreach($arResult['LINKED_TO_WH_REGIONS'][$reg_id] as $cur_wh){
                                    ?> wh<?
                                    echo $cur_wh;
                                }
                            }?>"><input type="checkbox" <?if(isset($arResult['ELEMENT_PROPERTIES']['USE_REGIONS'][$reg_id])){?>checked="checked"<?}?> name="regions_list[]" value="<?=$reg_id?>" data-text="<?=addslashes($reg_name);?>" /></div>
                        <?}?>
                        <div class="clear"></div>
                    </div>
                </div>
            </div>
        </div>

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

    </div>
    <div class="row">
	<input type="button" name="iblock_submit" class="submit-btn<? if ($arResult['SAVE'] == 'Y' && $qw > 0) { ?> active<? } else { ?> inactive<? } ?><?=($allowed_save ? '' : ' total_disabled');?>" value="<? if($arParams['ID']>0) echo GetMessage("IBLOCK_FORM_SUBMIT_EDIT"); else echo  GetMessage("IBLOCK_FORM_SUBMIT");  ?>" />
    </div>
</form>

<? if (!empty($arResult['ELEMENT']['ID'])){
    //признак того, что нужно отобразить выбранные регионы
    ?>
    <script type="text/javascript">
        var load_regions_with_checked = true;
    </script>
<?}?>
