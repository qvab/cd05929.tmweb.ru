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

$message_type = '';
if(isset($_COOKIE['send_counter_requests_success'])
    && $_COOKIE['send_counter_requests_success'] == 'y'
){
    $arResult['MESSAGE'] = 'Предложения отправлены покупателям';
    $message_type = 'success';
    setcookie('send_counter_requests_success', '', -1, '/');
}

if($arParams['TYPE'] == 'agent') {
    $agent_obj = new agent();
}
?>
<?if(!empty($arResult['MESSAGE'])):?>
    <div class="error_msg <?=$message_type;?>">
        <?=ShowError($arResult['MESSAGE'])?>
    </div>
<?endif;?>
<?if(isset($_COOKIE['success_counter_request'])
&& $_COOKIE['success_counter_request'] == 'y'
){
    setcookie('success_counter_request', '', time() - 1, '/');
    ?>
    <div class="success_message">Предложения направлены покупателям</div>
<?}?>
<?if (is_array($arResult["ITEMS"]) && sizeof($arResult["ITEMS"]) > 0):?>
    <div class="list_page_rows pairs_rows_list farmer_requests_list low_margin by_agent" data-host="<?=$GLOBALS['host']?>">
        <?foreach ($arResult["ITEMS"] as $arBlock):?>
            <?foreach ($arBlock as $arItem):?>
                <?
                $arOffer = $arItem['OFFER'];
                $isCounterSend = isset($arResult['COUNTER_REQUESTS_DATA'][$arOffer['ID']]) && $arResult['COUNTER_REQUESTS_DATA'][$arOffer['ID']]['UF_TYPE'] != 'a';
                $arRequest = $arItem['REQUEST'];
                $diff_date = secondTimesFormat($arRequest['DATE_DIFF'], false);
                $arCost = $arRequest['BEST_PRICE'];
                $price = number_format($arCost['ACC_PRICE_CSM'], 0, ',', ' ');
                $fca_dap = ($arRequest['NEED_DELIVERY'] == 'Y')?'CPT':'FCA';

                $deal_allow = false;
                if ($arParams['TYPE'] == 'farmer') {
                    $deal_allow = true;
                }
                elseif ($arParams['TYPE'] == 'agent') {
                    $agentId = $USER->GetID();
                    $deal_allow = true;
                }

                ?>
                <div class="line_area<? if ($_GET['o'] == $arOffer['ID'] && $_GET['r'] == $arRequest['ID']) { ?> active<? } ?>">
                    <div class="line_inner <?=$isCounterSend? 'answered' : '';?>">
                        <div class="name"><?=$arOffer['CULTURE_NAME']?></div>
                        <div class="tons">
                            <?if ($arItem['REQUEST']['NEED_DELIVERY'] == 'Y'):?>
                                <span class="val decs_separators"><?=$arCost['ROUTE']?></span> км
                            <?endif;?>
                        </div>
                        <div class="price"><span class="val decs_separators"><?=$price?></span> руб/т</div>

                        <?if(!isset($arResult['COUNTER_REQUESTS_DATA'][$arOffer['ID']])){
                            if ($arParams['TYPE'] == 'farmer'){
                                ?><div class="cp_accept"><a class="submit-btn" href="javascript: void(0);"  data-href="/farmer/request/counter/?offer_id=<?=$arOffer['ID'];?><?if(isset($_REQUEST['culture'])
                                    && $_REQUEST['culture'] != ''
                                ){
                                    ?>&culture=<?echo htmlspecialcharsbx($_REQUEST['culture']);
                                }if(isset($_REQUEST['wh'])
                                    && $_REQUEST['wh'] != ''
                                ){
                                    ?>&wh=<?echo htmlspecialcharsbx($_REQUEST['wh']);
                                }?>" >Сделать предложение</a></div><?
                            }
                            if ($arParams['TYPE'] == 'agent'){
                                ?><div class="cp_accept"><a class="submit-btn agent" href="javascript: void(0);"  data-href="/partner/farmer_request/counter/?offer_id=<?=$arOffer['ID'];?><?if(isset($_REQUEST['culture'])
                                    && $_REQUEST['culture'] != ''
                                ){
                                    ?>&culture=<?echo htmlspecialcharsbx($_REQUEST['culture']);
                                }if(isset($_REQUEST['wh'])
                                    && $_REQUEST['wh'] != ''
                                ){
                                    ?>&wh=<?echo htmlspecialcharsbx($_REQUEST['wh']);
                                }?>" >Сделать предложение</a></div><?
                            }
                        }?>

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
                        <div class="clear.l"></div>
                        <div class="wh_name"><?=$arOffer['WH_NAME']?></div>
                        <div class="clear l"></div>
                    </div>

                    <form action="" method="post" class="line_additional" <? if ($_GET['o'] == $arOffer['ID'] && $_GET['r'] == $arRequest['ID']) { ?> style="display: block;"<? } ?>>

                        <?=bitrix_sessid_post()?>

                        <input type="hidden" name="offer" value="<?=$arOffer['ID']?>">
                        <input type="hidden" name="request" value="<?=$arRequest['ID']?>">
                        <input type="hidden" name="warehouse" value="<?=$arCost['WH_ID']?>">

                        <?if ($arParams['TYPE'] == 'public'):?>
                            <div class="prop_area refinement_text">
                                Запрос на товар
                                <a target="_blank" href="/profile/offers/?uid=<?=$arParams['FARMER_ID'];?>&id=<?=$arOffer['ID']?>#<?=$arOffer['ID']?>">
                                    #<?=$arOffer['ID']?> от <?=date("d.m.Y", strtotime($arOffer['DATE_CREATE']))?>
                                </a>
                            </div>
                        <?else:?>
                            <div class="prop_area refinement_text">
                                Запрос на <?if($arParams['TYPE']=='farmer'):?>Ваш <?endif;?>товар
                                <a target="_blank" href="<?=$arParams['OFFER_LIST_URL']?>?id=<?=$arOffer['ID']?>#<?=$arOffer['ID']?>">
                                    #<?=$arOffer['ID']?> от <?=date("d.m.Y", strtotime($arOffer['DATE_CREATE']))?>
                                </a>
                            </div>
                        <?endif;?>
                        <?if ($arParams['TYPE'] == 'agent'):?>
                            <div class="prop_area refinement_text">
                                <a target="_blank" href="<?=$arParams['OFFER_LIST_URL']?>?id=<?=$arOffer['ID']?>&affair=y">
                                    Дела по товару
                                </a>
                            </div>
                        <?endif;?>
                        <?if ($arParams['TYPE'] == 'agent'):?>
                            <div class="prop_area prices_val">
                                <div class="area_1">
                                    <div class="name_1">Поставщик</div>
                                </div>
                                <div class="area_1">
                                    <div class="name_1">ФИО:</div>
                                    <div class="val_1">
                                        <span><?echo $arResult['FARMERS_DATA'][$arOffer['FARMER_ID']]['NAME'];?></span>
                                    </div>
                                </div>
                                <div class="area_1">
                                    <div class="name_1">Телефон:</div>
                                    <div class="val_1">
                                        <span><?echo $arResult['FARMERS_DATA'][$arOffer['FARMER_ID']]['PHONE'];?></span>
                                    </div>
                                </div>
                            </div>
                        <?endif;?>
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

                        <?if ($arParams['TYPE'] == 'agent'):?>
                            <?if ($arRequest['NEED_DELIVERY'] == 'N'):?>
                                <div class="prop_area adress_val">
                                    <div class="adress">Сформировать коммерческое предложение</div>
                                    <div class="val_adress make_k_offer_area">
                                        <div class="make_k_offer"
                                             data-deliverytype="fca"
                                             data-name="<?=$arRequest['CULTURE_NAME'];?>"
                                             data-rating="10"
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
                                             data-rating="10"
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
                        <?endif;?>

                        <div class="prop_area adress_val one_line">
                            <div class="adress">Объем:</div>
                            <div class="val_adress"><?=number_format($arRequest['REMAINS'], 0, '.', ' ')?> т.</div>
                            <div class="clear"></div>
                        </div>
                        <?if(isset($arResult['COUNTER_REQUESTS_DATA'][$arOffer['ID']])):
                            ?>
                            <div class="prop_area adress_val one_line counter_request_already_sent">
                                <?if($arResult['COUNTER_REQUESTS_DATA'][$arOffer['ID']]['UF_TYPE'] == 'a'):?>
                                    <?=$arResult['COUNTER_REQUESTS_DATA'][$arOffer['ID']]['UF_DATE'];?> для культуры "<?=$arRequest['CULTURE_NAME']?>" со склада "<?=$arOffer['WH_NAME']?>" принята цена покупателя на объём
                                    <?=$arResult['COUNTER_REQUESTS_DATA'][$arOffer['ID']]['UF_VOLUME_OFFER']?> т.
                                <?else:?>
                                    <?=$arResult['COUNTER_REQUESTS_DATA'][$arOffer['ID']]['UF_DATE'];?> для культуры "<?=$arRequest['CULTURE_NAME']?>" со склада "<?=$arOffer['WH_NAME']?>" направлено предложение с заявленным объёмом
                                    <?=$arResult['COUNTER_REQUESTS_DATA'][$arOffer['ID']]['UF_VOLUME_OFFER']?> т.
                                    и ценой <?=$arResult['COUNTER_REQUESTS_DATA'][$arOffer['ID']]['UF_FARMER_PRICE']?> руб/т.
                                    Тип доставки установлен как <?=$arResult['COUNTER_REQUESTS_DATA'][$arOffer['ID']]['UF_DELIVERY'];?>.
                                <?endif;?>
                            </div>
                        <?else:?>
                            <?if ($arParams['TYPE'] == 'farmer'):?>


                                <div id="req_cp_<?=$arRequest['ID']?>" class="prop_area adress_val one_line prop_area_n_border">
                                    <div class="adress">Отправка предложения:</div>
                                    <div class="clear"></div>
                                </div>
                                <div class="prop_area refinement_text prop_area_n_border">
                                    Сделайте предложение, чтобы покупатель увидел ваши намерения и связался с вами в случае заинтересованности.
                                    Срок действия предложения - 48 часов.
                                </div>


                                <div class="prop_area prop_area_n_border tonn_val_" data-price="<?=$arCost['ACC_PRICE_CSM'];?>" data-remains="<?=$arRequest['REMAINS']?>">
                                    <div class="vol row">
                                        <div class="row_val">
                                            <input type="text" data-checkval="y" data-checktype="pos_int" name="volume" placeholder="Указать количество тонн" value=""><span class="ton_pos">т.</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="prop_area total">
                                    <?
                                    $total_disabled = false;
                                    if(!isset($arResult['USER_RIGHTS']['REQUEST_RIGHT'])
                                        || $arResult['USER_RIGHTS']['REQUEST_RIGHT'] != 'Y'
                                    ){
                                        $total_disabled = true;
                                        echo '<div class="no_deal_rights">Необходимо <a href="/farmer/profile/">добавить ИНН</a>, чтобы принять запрос или отправить предложение</div>';
                                    }
                                    ?>
                                    <div class="prolongate_area">
                                        <a href="/farmer/request/counter/?offer_id=<?=$arOffer['ID'];?><?if(isset($_REQUEST['culture'])
                                            && $_REQUEST['culture'] != ''
                                        ){
                                            ?>&culture=<?echo htmlspecialcharsbx($_REQUEST['culture']);
                                        }if(isset($_REQUEST['wh'])
                                            && $_REQUEST['wh'] != ''
                                        ){
                                            ?>&wh=<?echo htmlspecialcharsbx($_REQUEST['wh']);
                                        }?>" class="submit-btn req_prolongation counter_request_add<?if($total_disabled){?> inactive hard_disabled<?}?>">Отправить предложение</a>
                                        <div class="clear"></div>
                                    </div>
                                    <div class="clear"></div>
                                    <div class="name">Итого стоимость: </div><div class="val"><span class="decs_separators">0</span> руб<span class="val_type"></span></div>
                                    <input type="submit" class="submit-btn inactive<?if($total_disabled){?> hard_disabled<?}?>" name="accept" value="Принять цену" />
                                    <div class="clear"></div>
                                </div>
                            <?elseif($arParams['TYPE'] == 'agent'):?>
                                <div id="req_cp_<?=$arRequest['ID']?>" class="prop_area adress_val one_line prop_area_n_border">
                                    <div class="adress">Отправка предложения:</div>
                                    <div class="clear"></div>
                                </div>
                                <div class="prop_area refinement_text prop_area_n_border">
                                    Сделайте предложение, чтобы покупатель увидел ваши намерения и связался с вами в случае заинтересованности.
                                    Срок действия предложения - 48 часов.
                                </div>
                                <div class="prop_area prop_area_n_border tonn_val_" data-price="<?=$arCost['ACC_PRICE_CSM'];?>" data-remains="<?=$arRequest['REMAINS']?>">
                                    <div class="vol row">
                                        <div class="row_val">
                                            <input type="text" data-checkval="y" data-checktype="pos_int" name="volume" placeholder="Указать количество тонн" value=""><span class="ton_pos">т.</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="prop_area total">
                                    <?
                                    $total_disabled = false;?>
                                    <div class="prolongate_area">
                                        <a href="/partner/farmer_request/counter/?offer_id=<?=$arOffer['ID'];?><?if(isset($_REQUEST['culture'])
                                            && $_REQUEST['culture'] != ''
                                        ){
                                            ?>&culture=<?echo htmlspecialcharsbx($_REQUEST['culture']);
                                        }if(isset($_REQUEST['wh'])
                                            && $_REQUEST['wh'] != ''
                                        ){
                                            ?>&wh=<?echo htmlspecialcharsbx($_REQUEST['wh']);
                                        }?>" class="submit-btn req_prolongation counter_request_add<?if($total_disabled){?> inactive hard_disabled<?}?>">Отправить предложение</a>
                                        <div class="clear"></div>
                                    </div>
                                    <div class="clear"></div>
                                    <div class="name">Итого стоимость: </div><div class="val"><span class="decs_separators">0</span> руб<span class="val_type"></span></div>
                                    <input type="submit" class="submit-btn inactive<?if($total_disabled){?> hard_disabled<?}?>" name="accept" value="Принять цену" />
                                    <div class="clear"></div>
                                </div><?
                                if(isset($arResult['FARMERS_PROFILE_DONE'][$arOffer['FARMER_ID']])
                                    && $arResult['FARMERS_PROFILE_DONE'][$arOffer['FARMER_ID']]
                                ){
                                    //формирование ссылки для агента
                                    $agent_href = '';
                                    ?>
                                    <div class="prop_area adress_val">
                                        <div class="submit-btn make_by_agent_counter_href" data-uid="<?=$arOffer['FARMER_ID'];?>" data-href="<?=$agent_href;?>">Ссылка для создания предложения</div>
                                        <input class="counter_volume_input" type="text" name="counter_volue" placeholder="Объём" />
                                        <div class="clear"></div>
                                    </div>
                                    <?
                                }else{
                                    ?>
                                    <div class="prop_area adress_val">
                                        Для создания ссылки на предложение необходимо <a href="/profile/make_full_mode/?uid=<?=$arOffer['FARMER_ID'];?>">заполнить профиль</a> поставщика
                                    </div>
                                    <?
                                }
                                endif;?>
                        <?endif;?>
                        <?if($arParams['TYPE'] != 'agent'):?>
                            <div class="prop_area additional_submits">
                                <?if ($deal_allow):?>
                                    <input type="submit" class="reject_but" name="reject" value="Отклонить запрос" />
                                <?endif;?>
                                <div class="hide_but">Свернуть</div>
                            </div>
                        <?endif;?>
                    </form>
                </div>
            <?endforeach;?>
            <?if ($arParams['TYPE'] == 'agent'):?>
                <div class="empty-block"></div>
            <?endif;?>
        <?endforeach;?>

        <?//пагинация
        if(isset($arParams['DISPLAY_BOTTOM_PAGER'])
            && $arParams['DISPLAY_BOTTOM_PAGER'] == 'Y'
        ){
            $APPLICATION->IncludeComponent("bitrix:main.pagenavigation", ".default",
                array(
                    "NAV_OBJECT" => $arResult['NAV_OBJ'],
                    "SEF_MODE" => "N"
                ),
                false
            );
        }
        ?>
    </div>
<?else:?>
    Ни одного запроса не найдено
<?endif;?>
<div id="filter_counts_data" style="display: none">
<?
//вывод данных для фильтра
    foreach($arResult['FILTER_COUNT_DATA'] as $cur_data){
        ?><div class="item"
               data-req="<?=$cur_data['UF_REQUEST_ID'];?>"
               data-region="<?=(isset($arResult['FILTER_WH_TO_REG'][$cur_data['UF_FARMER_WH_ID']]) ? $arResult['FILTER_WH_TO_REG'][$cur_data['UF_FARMER_WH_ID']] : 0);?>"
               data-farmer="<?=$cur_data['UF_FARMER_ID'];?>"
               data-culture="<?=$cur_data['UF_CULTURE_ID'];?>"
               data-wh="<?=$cur_data['UF_FARMER_WH_ID'];?>"
               data-nds="<?=($cur_data['UF_NDS'] != 'yes' ? 2 : 1);?>"
        ></div><?
    }
?>
</div>
<?
if((sizeof($arResult['LIMITS']))&&(is_array($arResult['LIMITS']))){
    if((sizeof($arResult['LIMITS']['regions_limits']))&&(is_array($arResult['LIMITS']['regions_limits']))){
        foreach ($arResult['LIMITS']['regions_limits'] as $region_id => $limits) {?>
        <div class="region_limits" style="display: none;" data-regionid="<?=$region_id; ?>" data-plimits="<?=$limits?>"></div><?
        }
    }
    if((sizeof($arResult['LIMITS']['cultures_limits']))&&(is_array($arResult['LIMITS']['cultures_limits']))){
        foreach ($arResult['LIMITS']['cultures_limits'] as $culture_id => $limits) {?>
        <div class="cultures_limits" style="display: none;" data-cultureid="<?=$culture_id; ?>" data-plimits="<?=$limits?>"></div><?
        }
    }
    if((sizeof($arResult['LIMITS']['farmers_limits']))&&(is_array($arResult['LIMITS']['farmers_limits']))){
        foreach ($arResult['LIMITS']['farmers_limits'] as $farmer_id => $limits) {?>
        <div class="farmers_limits" style="display: none;" data-farmerid="<?=$farmer_id;?>" data-plimits="<?=$limits?>"></div><?
        }
    }
    if((sizeof($arResult['LIMITS']['nds_limits']))&&(is_array($arResult['LIMITS']['nds_limits']))){
        foreach ($arResult['LIMITS']['nds_limits'] as $nds_id => $limits) {?>
        <div class="nds_limits" style="display: none;" data-ndsid="<?= $nds_id; ?>" data-plimits="<?=$limits?>"></div><?
        }
    }
    if((sizeof($arResult['LIMITS']['whids_limits']))&&(is_array($arResult['LIMITS']['whids_limits']))){
        foreach ($arResult['LIMITS']['whids_limits'] as $wh_id=>$limits){
            ?><div class="wh_limits" style="display: none;" data-whid="<?=$wh_id;?>" data-plimits="<?=$limits?>"></div><?
        }
    }
}
?>
