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
$assetObj->addJs($this->GetFolder() . '/js/script.js');

$assetObj->addCss(SITE_TEMPLATE_PATH . '/css/jquery.rating.css');
$assetObj->addJs(SITE_TEMPLATE_PATH . '/js/jquery.rating-2.0.js');
?>
<?if(!empty($arResult['MESSAGE'])):?>
    <div class="error_msg">
        <?=ShowError($arResult['MESSAGE'])?>
    </div>
<?endif;?>
<? if (is_array($arResult["ITEMS"]) && sizeof($arResult["ITEMS"]) > 0):?>
<div class="list_page_rows pairs_rows_list farmer_requests_list by_agent">

    <?foreach ($arResult["ITEMS"] as $arBlock):?>
        <?foreach ($arBlock as $arItem):?>

            <?
            $arOffer = $arItem['OFFER'];
            $arRequest = $arItem['REQUEST'];
            $diff_date = secondTimesFormat($arRequest['DATE_DIFF'],false);
            $arCost = $arRequest['BEST_PRICE'];
            $price = number_format($arCost['ACC_PRICE_CSM'], 0, ',', ' ');
            $fca_dap = ($arRequest['NEED_DELIVERY'] == 'Y')?'CPT':'FCA';

            if($arOffer['USER_NDS'] == 'yes') {
                $sNDS = '(с НДС)';
            } else {
                $sNDS = '(без НДС)';
            }

            $deal_allow = false;
            if(isset($arResult['FARMER_AGENT_RIGHTS'][$arOffer['FARMER_ID']][$USER->GetID()]['AGENT_RIGHTS'])
                && $arResult['FARMER_AGENT_RIGHTS'][$arOffer['FARMER_ID']][$USER->GetID()]['AGENT_RIGHTS'] == $arResult['AGENT_RIGHTS_LIST']['control_w_deals']['ID']
            ){
                $deal_allow = true;
            }

            $rating['RATE'] = '10.0';
            if (isset($arResult['CLIENT_RATING'][$arItem['REQUEST']['CLIENT_ID']])) {
                $rating = $arResult['CLIENT_RATING'][$arItem['REQUEST']['CLIENT_ID']];
            }
            ?>
            <div class="line_area<? if ($_GET['o'] == $arOffer['ID'] && $_GET['r'] == $arRequest['ID']) { ?> active<? } ?>">
                <div class="line_inner">
                    <div class="name"><?=$arRequest['CULTURE_NAME']?> <span>(<?=$fca_dap?>)</span></div>
                    <div class="tons">
                        <?
                        if ($arItem['REQUEST']['NEED_DELIVERY'] == 'Y') {
                            echo '<span class="val decs_separators">' . $arCost['ROUTE']  . '</span> км';
                        }
                        ?>
                    </div>
                    <div class="price"><span class="val decs_separators"><?=$price;?></span> руб/т</div>
                    <div class="date" data-active_to-second="<?=strtotime($arRequest['DATE_ACTIVE_TO'])?>">
                        <?=$diff_date;?>
                    </div>
                    <div class="arw_list arw_icon_close"></div>
                    <?if(isset($arResult['FARMERS_DATA'][$arItem['OFFER']['FARMER_ID']])){
                        $cur_farmer = $arResult['FARMERS_DATA'][$arItem['OFFER']['FARMER_ID']];
                        ?>
                        <div class="clear l no_border"></div>
                        <?
                        if($cur_farmer['NICK'] != ''){?>
                            <div class="farmer_name warhouse_name"><?=$cur_farmer['NICK'];?> <?=$sNDS?></div>
                        <?}elseif($cur_farmer['NAME'] == ''){?>
                            <div class="farmer_name warhouse_name"><?=$cur_farmer['EMAIL'];?> <?=$sNDS?></div>
                        <?}else{?>
                            <div class="farmer_name warhouse_name"><?=$cur_farmer['NAME'];?> (<?=$cur_farmer['EMAIL'];?>) <?=$sNDS?></div>
                        <?}
                    }
                    ?>
                    <div class="clear.l"></div>
                    <div class="wh_name"><?=$arOffer['WH_NAME']?></div>
                    <div class="clear l"></div>
                </div>

                <form action="" method="post" class="line_additional" <? if ($_GET['o'] == $arOffer['ID'] && $_GET['r'] == $arRequest['ID']) { ?> style="display: block;"<? } ?>>

                    <?=bitrix_sessid_post()?>

                    <input type="hidden" name="offer" value="<?=$arOffer['ID']?>">
                    <input type="hidden" name="request" value="<?=$arRequest['ID']?>">
                    <input type="hidden" name="warehouse" value="<?=$arCost['WH_ID']?>">

                    <div class="prop_area refinement_text">Запрос на товар <a target="_blank" href="/agent/offer/?id=<?=$arOffer['ID']?>">#<?=$arOffer['ID']?> от <?=date("d.m.Y", strtotime($arOffer['DATE_CREATE']))?></a></div>
                <div class="prop_area refinement_text">
                    <a target="_blank" href="/agent/offer/?id=<?=$arOffer['ID']?>&affair=y">
                        Дела по товару
                    </a>
                </div>

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
                        <?
                        if (isset($arCost['TARIFF_VAL'])) {
                            ?>
                            <div class="area_1">
                                <div class="name_1">Ваш тариф на перевозку:</div>
                                <div class="val_1">
                                    <span class="">-<?=number_format($arCost['TARIFF_VAL'], 0, ',', ' ')?></span> руб/т
                                </div>
                            </div>
                            <?
                        }
                        ?>
                        <div class="area_1">
                            <div class="name_1">Прогноз цены c места:</div>
                            <div class="val_1">
                                <span class="decs_separators"><?=$price;?></span> руб/т
                            </div>
                        </div>
                    </div>

                <?if ($arRequest['NEED_DELIVERY'] == 'N'):?>
                    <div class="prop_area adress_val">
                        <div class="adress">Сформировать коммерческое предложение</div>
                        <div class="val_adress make_k_offer_area">
                            <div class="make_k_offer"
                                 data-deliverytype="fca"
                                 data-name="<?=$arRequest['CULTURE_NAME'];?>"
                                 data-rating="<?=number_format($rating['RATE'], 1, '.', ' ');?>"
                                 data-bcprice="<?=number_format($arCost['BASE_PRICE'], 0, ',', ' ');?>"
                                 data-cmprice="<?=$price;?>"
                                 data-date="<?=secondToHours($arRequest['DATE_DIFF']);?>"
                                 data-seconds="<?=$arRequest['DATE_DIFF'];?>"
                                 data-showtext="Открыть"
                                 data-hidetext="Закрыть"
                            >Открыть</div>
                            <div class="kp_copy_text">
                                <textarea></textarea>
                                <input type="button" class="submit-btn copy_clip" value="Скопировать"/>
                            </div>
                        </div>
                    </div>
                <?else:?>
                    <div class="prop_area adress_val">
                        <div class="adress">Сформировать коммерческое предложение</div>
                        <div class="val_adress make_k_offer_area">
                            <div class="make_k_offer"
                                 data-deliverytype="cpt"
                                 data-tarif="<?=number_format($arCost['TARIFF_VAL'], 0, ',', ' ');?>"
                                 data-km="<?=$arCost['ROUTE'];?>"
                                 data-name="<?=$arRequest['CULTURE_NAME'];?>"
                                 data-rating="<?=number_format($rating['RATE'], 1, '.', ' ');?>"
                                 data-bcprice="<?=number_format($arCost['BASE_PRICE'], 0, ',', ' ');?>"
                                 data-cmprice="<?=$price;?>"
                                 data-date="<?=secondToHours($arRequest['DATE_DIFF']);?>"
                                 data-seconds="<?=$arRequest['DATE_DIFF'];?>"
                                 data-showtext="Открыть"
                                 data-hidetext="Закрыть"
                            >Открыть</div>
                            <div class="kp_copy_text">
                                <textarea></textarea>
                                <input type="button" class="submit-btn copy_clip" value="Скопировать"/>
                            </div>
                        </div>
                    </div>
               <?endif;?>

                    <div class="prop_area adress_val one_line">
                        <div class="adress">Объем:</div>
                        <div class="val_adress"><?=number_format($arRequest['REMAINS'], 0, '.', ' ')?> т.</div>
                        <div class="clear"></div>
                    </div>

                    <?
                    if (isset($arResult['CLIENT_RATING'][$arItem['REQUEST']['CLIENT_ID']])) {
                        $class = '';

                        if (!is_numeric($rating['RATE'])) $class = 'grey';
                        elseif ($rating['RATE'] > 7) $class = 'green';
                        elseif ($rating['RATE'] > 4) $class = 'yellow';
                        else $class = 'red';

                        ?>
                        <div class="prop_area adress_val">
                            <div class="adress rate_total">Рейтинг покупателя: <div class="rate_val <?//=$class?>"><input type="hidden" name="val" value="<?=$rating['RATE']/2?>" /></div></div>
                            <div class="client_rating">
                                <?
                                if (!is_numeric($rating['REC'])) $class = 'grey';
                                elseif ($rating['REC'] > 7) $class = 'green';
                                elseif ($rating['REC'] > 4) $class = 'yellow';
                                else $class = 'red';
                                ?>
                                <div class="rate_item i1">
                                    <div class="rate_title">Своевременность приемки продукции</div>
                                    <div class="rate_val <?=$class?>"><?=sprintf('%.1f', $rating['REC']);?></div>
                                </div>

                                <?
                                if (!is_numeric($rating['LAB'])) $class = 'grey';
                                elseif ($rating['LAB'] > 7) $class = 'green';
                                elseif ($rating['LAB'] > 4) $class = 'yellow';
                                else $class = 'red';
                                ?>
                                <div class="rate_item i2">
                                    <div class="rate_title">Оценка качества продукции</div>
                                    <div class="rate_val <?=$class?>"><?=sprintf('%.1f', $rating['LAB']);?></div>
                                </div>

                                <?
                                if (!is_numeric($rating['PAY'])) $class = 'grey';
                                elseif ($rating['PAY'] > 7) $class = 'green';
                                elseif ($rating['PAY'] > 4) $class = 'yellow';
                                else $class = 'red';
                                ?>
                                <div class="rate_item i3">
                                    <div class="rate_title">Своевременность оплаты</div>
                                    <div class="rate_val <?=$class?>"><?=sprintf('%.1f', $rating['PAY']);?></div>
                                </div>
                            </div>
                            <div class="clear"></div>
                        </div>
                        <?
                    }

                    if ($arRequest['NEED_DELIVERY'] == 'N') {
                        ?>
                        <div class="prop_area adress_val">
                            <div class="adress">FCA</div>
                            <div class="val_adress" style="padding-left: 0;">Отгрузка с места</div>
                            <div class="clear"></div>
                        </div>

                        <div class="prop_area"><b>Доставка не требуется</b></div>
                        <input type="hidden" value="a" name="delivery" />
                        <?
                    }
                    else {
                        if (sizeof($arResult['REQUEST_WAREHOUSES_LIST'][$arCost['WH_ID']]['TRANSPORT']) > 0) {
                            ?>
                            <div class="prop_area adress_val">
                                <div class="adress">Тип транспорта:</div>
                                <?
                                foreach ($arResult['REQUEST_WAREHOUSES_LIST'][$arCost['WH_ID']]['TRANSPORT'] as $val) {
                                    ?>
                                    <div class="val_adress"><?=$arResult['TRANSPORT_LIST'][$val]['NAME']?></div>
                                    <?
                                }
                                ?>
                                <div class="clear"></div>
                            </div>
                            <?
                        }
                        ?>
                        <div class="prop_area adress_val">
                            <div class="adress">CPT</div>
                            <div class="val_adress" style="padding-left: 0;">С доставкой покупателю</div>
                            <div class="clear"></div>
                        </div>


                        <?
                        $tarif = client::getTarif(
                            0,
                            0,
                            'cpt',
                            $arCost['CENTER'],
                            $arCost['ROUTE'],
                            model::getAgrohelperTariffs()
                        );
                        ?>

                        <div class="prop_area delivery_type">
                            <div class="name">Способ доставки:</div>
                            <div class="val">
                                <div class="row_area radio_group">
                                    <?
                                    foreach ($arResult['DELIVERY_LIST'] as $item) {
                                        ?>
                                        <div class="radio_area delivery_self">
                                            <input <?if(!$deal_allow){?>readonly="readonly"<?}?> data-text="<?=$item['VALUE']?>" type="radio" <? if ($item['XML_ID'] == 'c') { ?>checked="checked"<? } ?> value="<?=$item['XML_ID']?>" name="delivery" />
                                        </div>
                                        <?
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="clear"></div>
                        </div>
                        <?
                    }
                    ?>

                    <div class="prop_area text_definition one_line">
                        <div class="name">Оплата:</div>
                        <div class="data">
                            <?
                            if ($arRequest['PAYMENT'] == 'pre') {
                                echo "Предоплата";
                            }
                            else {
                                echo "Постоплата";
                            }
                            ?>
                        </div>
                        <div class="clear"></div>
                    </div>

                    <?
                    if (sizeof($arRequest['DOCS']) > 0) {
                        ?>
                        <div class="prop_area adress_val">
                            <div class="adress">Потребность в документах:</div>
                            <?
                            foreach ($arRequest['DOCS'] as $val) {
                                ?>
                                <div class="val_adress"><?=$arResult['DOCS_LIST'][$val]['NAME']?></div>
                                <?
                            }
                            ?>
                            <div class="clear"></div>
                        </div>
                        <?
                    }
                    ?>

                    <div class="prop_area tonn_val" data-price="<?=$arCost['ACC_PRICE_CSM'];?>" data-remains="<?=$arRequest['REMAINS']?>">
                        <div class="name">Введите количество тонн: </div>
                        <div class="val deal_volume"><input type="text" name="volume" /> т.</div>
                    </div>
                    <div class="prop_area total">
                        <?
                        $total_disabled = false;
                        if(!$deal_allow){
                            echo '<div class="no_deal_rights">У вас нет прав на создание сделки для данного поставщика</div>';
                            $total_disabled = true;
                        }else{
                            if(!isset($arResult['USER_DEALS_RIGHTS'][$arOffer['FARMER_ID']]) ){
                                $total_disabled = true;
                            }
                            else{
                                foreach ($arResult['USER_DEALS_RIGHTS'][$arOffer['FARMER_ID']] as $cur_val) {
                                    switch($cur_val) {
                                        case 'n0':
                                            echo '<div class="no_deal_rights">Для заключения сделки необходимо <a href="/profile/make_full_mode/?uid=' . $arOffer['FARMER_ID'] . '">активировать полноценный режим работы</a></div>';
                                            $total_disabled = true;
                                            break;
                                        case 'n1':
                                            echo '<div class="no_deal_rights">Произошла отвязка от организватора</div>';
                                            $total_disabled = true;
                                            break;
                                        case 'n2':
                                            echo '<div class="no_deal_rights">Для заключения сделки необходимо подтверждение привязки к поставщику от организатора</div>';
                                            $total_disabled = true;
                                            break;
                                        case 'n3':
                                            echo '<div class="no_deal_rights' . (count($arResult['USER_DEALS_RIGHTS']) > 1 ? ' top_s2' : '') . '">Для заключения сделки необходимо загрузить документы</div>';
                                            $total_disabled = true;
                                            break;
                                        case 'no_p':
                                            if ($arRequest['PAYMENT'] == 'pre') {
                                                echo '<div class="no_deal_rights top_s' . count($arResult['USER_DEALS_RIGHTS']) . '">Для заключения сделки необходимо загрузить документы баланса</div>';
                                                $total_disabled = true;
                                            }
                                            break;
                                    }
                                }
                            }
                        }
                        ?>
                        <div class="name">Итого стоимость: </div><div class="val"><span class="decs_separators">0</span> руб<span class="val_type"></span></div>
                        <input type="submit" class="submit-btn inactive<?if($total_disabled){?> hard_disabled<?}?>" name="accept" value="Принять" />
                    </div>

                    <div class="prop_area additional_submits">
                        <?if($deal_allow){?>
                            <input type="submit" class="reject_but" name="reject" value="Отклонить запрос" />
                        <?}?>
                        <div class="hide_but">Свернуть</div>
                    </div>
                </form>
            </div>
        <?endforeach;?>

        <div class="empty-block"></div>

    <?endforeach;?>
</div>
<?else:?>
    Ни одного запроса не найдено
<?endif;?>