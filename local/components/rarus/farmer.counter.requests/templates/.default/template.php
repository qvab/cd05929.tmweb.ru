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
$assetObj = \Bitrix\Main\Page\Asset::getInstance();
?>
<div class="error_msg <?if(!empty($arResult['MESSAGE'])){?>active<?}?>">
    <?=ShowError($arResult['MESSAGE'])?>
</div>
<?if (is_array($arResult["ITEMS"]) && sizeof($arResult["ITEMS"]) > 0):?>
    <form action="" method="post" class="counter_request_additional_data">
        <?foreach ($arResult["ITEMS"] as $arBlock):
            foreach ($arBlock as $arItem):?>
                <input type="hidden" name="selected_requests[]" value="<?=$arItem['REQUEST']['ID'];?>" />
            <?endforeach;
        endforeach;?>
        <input type="hidden" name="offer_id" value="<?=$arResult['OFFER_ID'];?>" />
        <input type="hidden" name="send_data" value="y" />

        <?if(isset($_REQUEST['culture'])):?>
            <input type="hidden" name="culture" value="<?=htmlspecialcharsbx($_REQUEST['culture']);?>" />
        <?endif;?>
        <?if(isset($_REQUEST['wh'])):?>
            <input type="hidden" name="wh" value="<?=htmlspecialcharsbx($_REQUEST['wh']);?>" />
        <?endif;?>

        <div class="row">
            <div class="row_val">
                <input type="text" name="volume" placeholder="Указать количество тонн" value="<?=htmlspecialcharsbx($_REQUEST['vol']);?>" /><span class="ton_pos">т.</span>
            </div>
        </div>

        <div class="row">
            <div class="row_val">
                <?php
                if(!empty($arResult['RECOMMEND_MESSAGE'])){
                    echo $arResult['RECOMMEND_MESSAGE'];
                }
                ?>
            </div>
            <div class="row_head">Моя цена "с места"</div>
            <div class="row_val min_max_val">
                <div class="min_price"><?=number_format($arResult['MIN_PRICE'], 0, ',', ' ');?><span>min</span></div>
                <span class="minus minus_bg" data-step="50" onclick="farmerClickCounterMinPrice(this);" data-min="<?=$arResult['MIN_PRICE'];?>"></span>
                <input type="text" name="price" placeholder='' value="<?=number_format($arResult['SET_VALUE'], 0, ',', ' ');?>" />
                <span class="plus plus_bg" data-step="50" onclick="farmerClickCounterMaxPrice(this);" data-max="<?=$arResult['MAX_PRICE'];?>"></span>
                <div class="max_price"><?=number_format($arResult['MAX_PRICE'], 0, ',', ' ');?><span>max</span></div>
                <div class="clear"></div>
            </div>
        </div>

        <div class="row two_lines_checkbox">
            <div class="row_val">
                <div class="radio_group fst">
                    <div class="radio_area"><input type="checkbox" name="can_deliver" value="1" data-text="МОГУ ОТВЕЗТИ <br/>за прибавку в цене" /></div>
                </div>
                <div class="radio_group">
                    <div class="radio_area"><input type="checkbox" name="lab_trust" value="1" data-text="ДОВЕРЮСЬ <br/>лаборатории покупателя" /></div>
                </div>
                <div class="clear"></div>
            </div>
        </div>


        <input type="submit" name="save" value="Отправить предложение" class="submit-btn counter_request_submit">

        <div class="check_requests_area">
            <a href="#" class="ch_all_tr">Выбрать все</a>
            <a href="#" class="no_tr">Снять все</a>
        </div>
    </form>

    <div class="list_page_rows pairs_rows_list farmer_requests_list counter_request_list">
        <?foreach ($arResult["ITEMS"] as $arBlock):
            foreach ($arBlock as $arItem):?>
                <?
                $arOffer = $arItem['OFFER'];
                $arRequest = $arItem['REQUEST'];
                $arCost = $arRequest['BEST_PRICE'];
                $price = number_format($arCost['ACC_PRICE_CSM'], 0, ',', ' ');
                $fca_dap = ($arRequest['NEED_DELIVERY'] == 'Y')?'CPT':'FCA';
                ?>
                <div class="counter_request_area">
                    <div class="line_area<? if ($_GET['o'] == $arOffer['ID'] && $_GET['r'] == $arRequest['ID']) { ?> active<? } ?>">
                        <div class="line_inner">
                            <div class="name"><?=$arRequest['CULTURE_NAME']?> <span>(<?=$fca_dap?>)</span></div>
                            <div class="tons">
                                <?if ($arItem['REQUEST']['NEED_DELIVERY'] == 'Y'):?>
                                    <span class="val decs_separators"><?=$arCost['ROUTE']?></span> км
                                <?endif;?>
                            </div>
                            <div class="price"><span class="val decs_separators"><?=$price?></span> руб/т</div>
                            <div class="arw_list arw_icon_close"></div>
                            <?if (isset($arResult['FARMERS_DATA'][$arOffer['FARMER_ID']])):
                                $cur_farmer = $arResult['FARMERS_DATA'][$arOffer['FARMER_ID']];
                                if ($arOffer['USER_NDS'] == 'yes') $sNDS = '(с НДС)';
                                else $sNDS = '(без НДС)';
                                ?>
                                <div class="clear l no_border"></div>
                                <?if ($cur_farmer['NICK'] != ''):?>
                                    <div class="farmer_name warhouse_name"><?=$cur_farmer['NICK'];?> <?=$sNDS?></div>
                                <?elseif ($cur_farmer['NAME'] == ''):?>
                                    <div class="farmer_name warhouse_name"><?=$cur_farmer['EMAIL'];?> <?=$sNDS?></div>
                                <?else:?>
                                    <div class="farmer_name warhouse_name"><?=$cur_farmer['NAME'];?> (<?=$cur_farmer['EMAIL'];?>) <?=$sNDS?></div>
                                <?endif;
                            endif;
                            ?>
                            <div class="wh_name"><?=$arOffer['WH_NAME']?></div>
                            <div class="clear l"></div>
                        </div>

                        <form action="" method="post" class="line_additional" <? if ($_GET['o'] == $arOffer['ID'] && $_GET['r'] == $arRequest['ID']) { ?> style="display: block;"<? } ?>>

                            <input type="hidden" name="offer" value="<?=$arOffer['ID']?>">
                            <input type="hidden" name="request" value="<?=$arRequest['ID']?>">
                            <input type="hidden" name="warehouse" value="<?=$arCost['WH_ID']?>">

                            <div class="prop_area prices_val">
                                <div class="area_1">
                                    <div class="name_1">Базисная цена договора:</div>
                                    <div class="val_1">
                                        <span class="decs_separators"><?=number_format($arCost['BASE_PRICE'], 0, ',', ' ')?></span> руб/т
                                    </div>
                                </div>
                                <div class="area_1">
                                    <div class="name_1">Прогноз сброса/прибавки:</div>
                                    <div class="val_1">
                                        <?
                                        $dump = number_format($arCost['SBROS_RUB'], 0, ',', ' ');
                                        if ($arCost['SBROS_RUB'] > 0) $dump = '+'.$dump;
                                        ?>
                                        <span class=""><?=$dump?></span> руб/т
                                    </div>
                                </div>
                                <?if (isset($arCost['TARIFF_VAL'])):?>
                                    <div class="area_1">
                                        <div class="name_1">Ваш тариф на перевозку:</div>
                                        <div class="val_1">
                                            <span class="">-<?=number_format($arCost['TARIFF_VAL'], 0, ',', ' ')?></span> руб/т
                                        </div>
                                    </div>
                                <?endif;?>
                                <div class="area_1">
                                    <div class="name_1">Прогноз цены c места:</div>
                                    <div class="val_1">
                                        <span class="decs_separators"><?=$price?></span> руб/т
                                    </div>
                                </div>
                            </div>

                            <div class="prop_area adress_val one_line">
                                <div class="adress">Объем:</div>
                                <div class="val_adress"><?=number_format($arRequest['REMAINS'], 0, '.', ' ')?> т.</div>
                                <div class="clear"></div>
                            </div>

                            <?if ($arRequest['NEED_DELIVERY'] == 'N'):?>
                                <div class="prop_area adress_val">
                                    <div class="adress">FCA</div>
                                    <div class="val_adress" style="padding-left: 0;">Отгрузка с места</div>
                                    <div class="clear"></div>
                                </div>
                                <div class="prop_area"><b>Доставка не требуется</b></div>
                                <input type="hidden" value="a" name="delivery" />
                            <?else:
                                if (sizeof($arResult['REQUEST_WAREHOUSES_LIST'][$arCost['WH_ID']]['TRANSPORT']) > 0):?>
                                    <div class="prop_area adress_val">
                                        <div class="adress">Тип транспорта:</div>
                                        <?foreach ($arResult['REQUEST_WAREHOUSES_LIST'][$arCost['WH_ID']]['TRANSPORT'] as $val):?>
                                            <div class="val_adress"><?=$arResult['TRANSPORT_LIST'][$val]['NAME']?></div>
                                        <?endforeach;?>
                                        <div class="clear"></div>
                                    </div>
                                <?endif;?>
                                <div class="prop_area adress_val">
                                    <div class="adress">CPT</div>
                                    <div class="val_adress" style="padding-left: 0;">С доставкой покупателю</div>
                                    <div class="clear"></div>
                                </div>
                            <?endif;?>

                            <div class="prop_area text_definition one_line">
                                <div class="name">Оплата:</div>
                                <div class="data">
                                    <?if ($arRequest['PAYMENT'] == 'pre'):?>
                                        Предоплата
                                    <?else:?>
                                        Постоплата
                                    <?endif;?>
                                </div>
                                <div class="clear"></div>
                            </div>

                            <?if (sizeof($arRequest['DOCS']) > 0):?>
                                <div class="prop_area adress_val">
                                    <div class="adress">Потребность в документах:</div>
                                    <?foreach ($arRequest['DOCS'] as $val):?>
                                        <div class="val_adress"><?=$arResult['DOCS_LIST'][$val]['NAME']?></div>
                                    <?endforeach;?>
                                    <div class="clear"></div>
                                </div>
                            <?endif;?>
                        </form>
                    </div>
                    <form class="checkout_area radio_area">
                        <div class="radio_group">
                            <input type="checkbox" name="choose_request" value="<?=$arRequest['ID'];?>" checked="checked" />
                        </div>
                    </form>
                </div>
            <?endforeach;
        endforeach;?>
    </div>
<?else:?>
    Ни одного запроса не найдено
<?endif;?>