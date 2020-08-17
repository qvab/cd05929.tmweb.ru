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

$volume_total = 0;
$volume_other_nds_total = 0;
$price_total = 0;
$price_other_nds_total = 0;

$counter_request_right = 'n';
if(isset($arResult['USER_RIGHTS']['REQUEST_RIGHT'])){
    if($arResult['USER_RIGHTS']['REQUEST_RIGHT'] == 'Y'){
        //у пользователя есть права на принятие ВП
        $counter_request_right = 'y';
    }elseif($arResult['USER_RIGHTS']['REQUEST_RIGHT'] == 'LIM'){
        //у пользователя закончились принятия ВП
        $counter_request_right = 'l';
    }
}
?>

<?if($arResult['MODE'] == 'agent'):?>
<div class="graph_href_with_parameters">
    <a href="javascript: void(0);" onclick="makeGraphDataForClient();">Отправить график покупателю</a>
</div>
<?endif;?>

<div class="error_msg <?if(!empty($arResult['ERROR_MESSAGE'])){?>active<?}?>">
    <?=ShowError($arResult['ERROR_MESSAGE']);?>
</div>

<?
global $USER;
$user_email = $USER->GetEmail();
if(checkEmailFromPhone($user_email)){
    $user_email = '';
}
?>
<?if($arResult['MODE'] != 'agent'):?>
    <div id="opening_top" class="opening_limit_available"><div class="result_message"></div><a href="/client/profile/counter_limits_history/">Доступно</a>:<div class="limit_val"><?=$arResult['USER_CON_REQ_OPENS_LIMIT']?:0;?></div><a href="javascript: void(0);" onclick="showCounterRequestFeedbackForm('<?=$user_email;?>', <?=rrsIblock::getConst('min_counter_req_limit');?>, <?=rrsIblock::getConst('counter_req_price');?>);">(подать заявку)</a></div>
<?endif;?>

<?if(isset($arResult['USER_NDS'])
&& isset($_GET['culture_id'])
&& isset($_GET['warehouse_id'])
){?>
    <div class="user_nds_value" style="display: none" data-culture="<?=htmlspecialcharsbx($_GET['culture_id'])?>" data-wh="<?=htmlspecialcharsbx($_GET['warehouse_id'])?>" data-nds="<?=($arResult['USER_NDS'] ? 'y' : 'n');?>" <?=(!empty($arResult['UF_CENTER_ID']) ? 'data-center="' . $arResult['UF_CENTER_ID'] . '"' : '');?> <?if(!empty($_GET['client_id']) && is_numeric($_GET['client_id'])){?>data-client="<?=htmlspecialcharsbx($_GET['client_id']);?>"<?}?> <?if(!empty($_GET['region_id']) && is_numeric($_GET['region_id'])){?>data-region="<?=htmlspecialcharsbx($_GET['region_id']);?>"<?}?> <?if(!empty($_GET['page']) && is_numeric($_GET['page'])){?>data-page="<?=htmlspecialcharsbx($_GET['page']);?>"<?}?> ></div>
<?}?>
    <div class="nds_value_div" style="display: none" data-ndsval="<?=rrsIblock::getConst('nds');?>" data-nds="<?=($arResult['USER_NDS'] ? 'y' : 'n');?>"></div>
<?
if (is_array($arResult["ITEMS"]) && sizeof($arResult["ITEMS"]) > 0){
    $nds_val = rrsIblock::getConst('nds');
    if(!empty($arResult['BEST_OFFER_DATA']['BASE_PRICE'])){
        //выводим данные лучшего предложения
        ?><div class="best_offer_data_div" style="display: none" data-price="<?=round($arResult['BEST_OFFER_DATA']['BASE_PRICE'])?>" data-wh="<?=$arResult['BEST_OFFER_DATA']['WH'];?>" data-nds="<?=$arResult['BEST_OFFER_DATA']['NDS_TYPE'];?>" data-cofferid="<?=$arResult['BEST_OFFER_DATA']['ID'];?>" data-offerid="<?=$arResult['BEST_OFFER_DATA']['OFFER_ID'];?>" data-requestid="<?=$arResult['BEST_OFFER_DATA']['REQUEST_ID'];?>"></div><?
    }
    ?>

    <div class="list_page_rows_area" data-host="<?=$GLOBALS['host'];?>">
        <div class="list_page_rows pairs_rows_list farmer_requests_list counter_request_client_list">

            <?if(isset($_GET['o'])
                && isset($_GET['r'])
                && is_numeric($_GET['o'])
                && is_numeric($_GET['r'])
            ){?>
                <div class="opened_c_req" data-offer="<?=htmlspecialcharsbx($_GET['o'])?>" data-request="<?=htmlspecialcharsbx($_GET['r'])?>"></div>
            <?}?>

            <div class="head_line">
                <div class="line_inner">
                    <div class="nds">СНО поставщика</div>

                    <div class="farmer_delivery_type">CPT/<br/>FCA/EXW</div>

                    <div class="tons">Объем</div>

                    <div class="price">Базисная цена<br/>от поставщика</div>

                    <div class="price_difference">Отклонение от цены запроса</div>

                    <div class="disregard">Использовать в расчете?</div>

                    <div class="opening_h">

                        <?if($arResult['MODE'] != 'agent'):?>
                            <div id="opening_head" class="opening_limit_available"><div class="result_message"></div><a href="/client/profile/counter_limits_history/">Доступно</a>:<div class="limit_val"><?=$arResult['USER_CON_REQ_OPENS_LIMIT']?:0;?></div><br/><a href="javascript: void(0);" onclick="showCounterRequestFeedbackForm('<?=$user_email;?>', <?=rrsIblock::getConst('min_counter_req_limit');?>, <?=rrsIblock::getConst('counter_req_price');?>);">(подать заявку)</a></div>
                        <?endif;?>
                    </div>

                    <div class="clear l"></div>

                </div>
            </div>

            <?
            $nds = rrsIblock::getConst('nds');

            foreach ($arResult["ITEMS"] as $cur_pos => $arItem){
                $active_volume = (is_numeric($arItem['UF_VOLUME']) && $arItem['UF_VOLUME'] > 0);
                $price_val = '';
                $additional_price = '';
                $nds_class = '';
                $additional_options_arr = array();
                if($arItem['UF_COFFER_TYPE'] == 'p'
                    && trim($arItem['UF_ADDIT_FIELDS']) != ''
                ){
                    $additional_options_arr = json_decode($arItem['UF_ADDIT_FIELDS'], true);
                }

                if($arResult['MODE'] == 'agent'){
                    $counter_request_right = 'n';
                    if(isset($arResult['USER_RIGHTS'][$arItem['UF_CLIENT_ID']]['REQUEST_RIGHT'])){
                        if($arResult['USER_RIGHTS'][$arItem['UF_CLIENT_ID']]['REQUEST_RIGHT'] == 'Y'){
                            //у пользователя есть права на принятие ВП
                            $counter_request_right = 'y';
                        }elseif($arResult['USER_RIGHTS'][$arItem['UF_CLIENT_ID']]['REQUEST_RIGHT'] == 'LIM'){
                            //у пользователя закончились принятия ВП
                            $counter_request_right = 'l';
                        }
                    }
                }


                if(isset($arResult['ADDITIONAL_DATA'][$cur_pos]) && count($arResult['ADDITIONAL_DATA'][$cur_pos]) > 0 ):
                    $additional_price = '';
                    //если АП без НДС, а покупатель с НДС, то выводить цену без, а ниже цену с НДС
                    if(isset($arItem['UF_NDS_FARMER'])
                        && isset($arItem['UF_NDS_CLIENT'])
                        && $arItem['UF_NDS_FARMER'] != $arItem['UF_NDS_CLIENT']
                    ){
                        $additional_price = '<span class="addit_nds_price" data-nds="' . ($arItem['UF_NDS_FARMER'] == 'yes' ? 'y' : 'n') . '">' . number_format($arResult['ADDITIONAL_DATA'][$cur_pos]['BASE_CONTR_PRICE'], 0, ',', ' ') . ' руб/т</span>';
                        $nds_class = ' low';
                    }

                    $price_val = number_format($arResult['ADDITIONAL_DATA'][$cur_pos]['BASE_PRICE'], 0, ',', ' ');
                endif;
                ?>
                <div class="offers line_area<?if(isset($_GET['o']) && isset($_GET['r']) && $_GET['o'] == $arItem['UF_OFFER_ID'] && $_GET['r'] == $arItem['UF_REQUEST_ID']){?> active<?} if(!$active_volume){?> inactive_counter_request<?}?><?=($arItem['UF_COFFER_TYPE'] == 'p' ? ' agent_contract' : '');?>" data-id="<?=$arItem['ID'];?>" >
                    <div class="line_inner">
                        <div class="nds<?=$nds_class;?>"><?=($arItem['UF_NDS_FARMER'] == 'yes' ? 'С НДС' : 'Без НДС')?></div>

                        <div class="farmer_delivery_type"><?=strtoupper($arItem['UF_DELIVERY']);?></div>

                        <div class="tons"><?=$arItem['UF_VOLUME'];?> т</div>

                        <?if($price_val != ''):?>
                            <div class="price"><span class="val decs_separators" data-nds="<?=($arItem['UF_NDS_CLIENT'] == 'yes' ? 'y' : 'n');?>"><?=$price_val;?></span> руб/т<?
                                if($additional_price != ''){
                                    echo '<br/>' . $additional_price;
                                }
                            ?></div>

                            <?

                            $p = number_format($arResult['ADDITIONAL_DATA'][$cur_pos]['CLIENT_BASE_PRICE'], 0, ',', ' ');
                            if($arItem['UF_NDS_CLIENT'] != $arItem['UF_NDS_FARMER']) {
                                if ($arItem['UF_NDS_CLIENT'] == 'yes' && $arItem['UF_NDS_FARMER'] == 'no') {
                                    //вычитаем НДС из цены
                                    $p = number_format($arResult['ADDITIONAL_DATA'][$cur_pos]['CLIENT_BASE_PRICE'] / (1 + 0.01 * $nds_val), 0, ',', ' ');
                                }elseif($arItem['UF_NDS_CLIENT'] == 'no' && $arItem['UF_NDS_FARMER'] == 'yes'){
                                    //добавляем НДС в цену
                                    $p = number_format($arResult['ADDITIONAL_DATA'][$cur_pos]['CLIENT_BASE_PRICE'] + $arResult['ADDITIONAL_DATA'][$cur_pos]['CLIENT_BASE_PRICE'] * 0.01 * $nds_val, 0, ',', ' ');
                                }
                            }

                            $title = "<span class='click'><div class='get_price_area'><div class='go_button btn get_price_but' onclick='getPricePopup({$arItem['UF_REQUEST_ID']});'>Уточнить цену</div></div>{$arItem['UF_DATE']}<span class='go_button btn' onclick='goFromGraph({$arItem['UF_REQUEST_ID']});'>Изменить запрос</span><span class='go_label'>Клик для изменения запроса</span></span>";
                            $title .= "<br><span class='price'>Моя цена:</span> <b>{$p} руб</b>";
                            if(isset($arResult['ADDITIONAL_DATA'][$cur_pos]['DIFFERENCE'])
                                && $arResult['ADDITIONAL_DATA'][$cur_pos]['DIFFERENCE'] != 0
                            ){?>
                                <div title="<?=$title;?>" data-request="<?=$arItem['UF_REQUEST_ID'];?>" class="price_difference<?=($additional_price != '' ? ' with_additional' : '');?> <?=($arResult['ADDITIONAL_DATA'][$cur_pos]['DIFFERENCE'] > 0 ? 'red">+' : 'green">')?><?=number_format($arResult['ADDITIONAL_DATA'][$cur_pos]['DIFFERENCE'], 0, ',', ' ')?> руб/т</div>
                            <?}else{?>
                                <div title="<?=$title;?>" data-request="<?=$arItem['UF_REQUEST_ID'];?>" class="price_difference<?=($additional_price != '' ? ' with_additional' : '');?>">-</div>
                            <?}?>
                        <?endif;?>

                        <div class="disregard">
                            <?
                            $arItem['CHECKED'] = false;
                            ?>
                            <?if($active_volume){?>
                                <?$name = "use_w_" . $arItem['UF_OFFER_ID'] . "_" . $arItem['UF_REQUEST_ID'];?>
                                <form action="" method="post">
                                <div class="radio_group">
                                    <div class="radio_area">
                                        <input type="checkbox"  name="<?=$name;?>" <?if(in_array($name, $_REQUEST['checked'])) { $arItem['CHECKED'] = true;?>checked="checked"<? } ?> value="Y" />
                                    </div>
                                </div>
                                </form>
                            <?}?>
                        </div>

                        <?if(/*$counter_request_right == 'y'
                            && */$active_volume
                            && $arResult['MODE'] == 'agent'
                            && isset($arResult['ADDITIONAL_DATA'][$cur_pos]['CULTURE_ID'])
                        ){
                            //ссылка от агента для покупателя (для принятия встречного предложения)

                            //если все обязательные поля профиля заполнены, то отображаем ссылку, иначе скрываем
                            if(isset($arResult['CLIENTS_PROFILE_DONE'][$arItem['UF_CLIENT_ID']])
                                && $arResult['CLIENTS_PROFILE_DONE'][$arItem['UF_CLIENT_ID']] == true
                            ){
                                $cr_url = '';
                                ?>
                                <div class="accept for_agent">
                                <a class="submit-btn" href="javascript: void(0);" data-href="<?=$cr_url;?>" data-wh="<?=$arItem['UF_CLIENT_WH_ID'];?>" data-email="<?=$arResult['USERS_EMAIL'][$arItem['UF_CLIENT_ID']];?>" data-uid="<?=$arItem['UF_CLIENT_ID'];?>" data-culture="<?=$arResult['ADDITIONAL_DATA'][$cur_pos]['CULTURE_ID'];?>">
                                    <?if($arItem['UF_COFFER_TYPE'] == 'p'):?>
                                        Контракт
                                    <?else:?>
                                        Принять
                                    <?endif;?>
                                </a></div><?
                            }else{
                                ?>
                                <div class="accept for_agent"><a class="submit-btn" href="javascript: void(0);" data-profile="<?=$arItem['UF_CLIENT_ID'];?>">
                                    <?if($arItem['UF_COFFER_TYPE'] == 'p'):?>
                                        Контракт
                                    <?else:?>
                                        Принять
                                    <?endif;?>
                                </a></div><?
                            }
                        }?>

                        <?if($counter_request_right == 'y'
                            && $active_volume
                            && $arResult['MODE'] != 'agent'
                        ){
                            //проверка является ли агентским предложением
                            if($arItem['UF_COFFER_TYPE'] == 'p'){?>
                                <div class="accept"><a class="submit-btn" href="javascript: void(0);">Контракт</a></div>
                            <?}else{?>
                                <div class="accept"><div class="rub_ico" title="Платное предложение"></div><a class="submit-btn" href="javascript: void(0);">Принять</a></div>
                            <?}
                        } elseif (!$active_volume) { ?>
                            <div class="accept"><div class="submit-btn" >Продано</div></div>
                        <? } elseif($arResult['MODE'] != 'agent') {
                            if($counter_request_right == 'l'
                                && $arItem['UF_COFFER_TYPE'] == 'p'
                            ){?>
                                <div class="accept"><div class="submit-btn" href="javascript: void(0);">Контракт</div></div>
                            <?}else{?>
                                <div class="accept"><div class="submit-btn inactive" href="javascript: void(0);">Принять</div></div>
                            <?}
                            ?>
                        <? } ?>
                        <div class="arw_list arw_icon_close"></div>
                        <div class="clear"></div>

                    </div>

                    <form action="" method="post" class="line_additional" <? if ($_GET['o'] == $arItem['UF_OFFER_ID'] && $_GET['r'] == $arItem['UF_REQUEST_ID']) { ?> style="display: block;"<? } ?>>

                        <input type="hidden" name="accept" value="y">
                        <input type="hidden" name="offer" value="<?=$arItem['UF_OFFER_ID'];?>">
                        <input type="hidden" name="request" value="<?=$arItem['UF_REQUEST_ID'];?>">
                        <input type="hidden" name="warehouse_f" value="<?=$arItem['UF_FARMER_WH_ID']?>">
                        <input type="hidden" name="warehouse_cl" value="<?=$arItem['UF_CLIENT_WH_ID']?>">
                        <?if(isset($_COOKIE['counter_request_referer'])&& is_numeric($_COOKIE['counter_request_referer'])){
                        ?><input type="hidden" name="partnerid" value="<?=htmlspecialcharsbx($_COOKIE['counter_request_referer'])?>"><?
                            setcookie('counter_request_referer', "", time() - 10, '/'); //удаляем ID орг. из кук
                        }
                        if($arResult['MODE'] == 'agent'){
                            if(isset($arItem['filter'])){
                                if(isset($arResult['FILTER_OFFERS'][$arItem['filter']])){
                                    if(isset($arResult['COMPANY_NAMES'][$arItem['UF_CLIENT_ID']])
                                        && trim($arResult['COMPANY_NAMES'][$arItem['UF_CLIENT_ID']]) != ''
                                    ){
                                        $link_text = $arResult['COMPANY_NAMES'][$arItem['UF_CLIENT_ID']];
                                    }else{
                                        $link_text = 'Похожие предложения: '.$arResult['FILTER_OFFERS'][$arItem['filter']]['COUNT'];
                                    }

                                    if($arResult['FULL_FILTER']===false){
                                        $link = $arResult['FILTER_OFFERS'][$arItem['filter']]['LINK'].'&r='.$arItem['UF_REQUEST_ID'].'&o='.$arItem['UF_OFFER_ID'];

                                        if(isset($arResult['COMPANY_NAMES'][$arItem['UF_CLIENT_ID']])
                                            && trim($arResult['COMPANY_NAMES'][$arItem['UF_CLIENT_ID']]) != ''
                                        ){
                                            $link_text = '<a class="other_off_link" href="'.$link.'">' . $arResult['COMPANY_NAMES'][$arItem['UF_CLIENT_ID']].'</a>';
                                        }else{
                                            $link_text = '<a class="other_off_link" href="'.$link.'">Похожие предложения: '.$arResult['FILTER_OFFERS'][$arItem['filter']]['COUNT'].'</a>';
                                        }
                                    }
                                    ?>
                                    <div class="prop_area adress_val">
                                        <div class="adress"><?=$link_text?></div>
                                    </div>
                                    <?
                                }
                            }
                        }
                        ?>
                        <?if(isset($arResult['ADDITIONAL_DATA'][$cur_pos]) && count($arResult['ADDITIONAL_DATA'][$cur_pos]) > 0 ):?>
                            <div class="prop_area prices_val">
                                <?if(isset($arResult['ADDITIONAL_DATA'][$cur_pos]['TARIF'])){
                                    ?>
                                    <div class="area_1" data-type="csm_price" data-nds="<?=($arItem['UF_NDS_FARMER'] == 'yes' ? 'y' : 'n')?>">
                                        <div class="name_1"><?=$arResult['ADDITIONAL_DATA'][$cur_pos]['CULTURE_NAME'];?> <span class="place_type">"с места"/(FCA)</span>, (<?=($arItem['UF_NDS_FARMER'] == 'yes' ? 'с НДС' : 'без НДС')?>):</div>

                                        <div class="val_1">
                                            <?if(isset($arResult['ADDITIONAL_DATA'][$cur_pos]['FARMER_CSM_PRICE'])){?>
                                            <span class="decs_separators"><?=number_format($arResult['ADDITIONAL_DATA'][$cur_pos]['FARMER_CSM_PRICE'], 0, ',', ' ');?></span> руб/т
                                            <?} else {
                                            ?>
                                                <span class="decs_separators"><?=number_format($arResult['ADDITIONAL_DATA'][$cur_pos]['CSM_FOR_CLIENT_VALUE'], 0, ',', ' ');?></span> руб/т
                                                <?
                                            }?>
                                        </div>


                                    </div>


                                    <?
                                    $arTariff = $arResult['ADDITIONAL_DATA'][$cur_pos]['TARIFF_RANGE'];
                                    $sDistance = '';
                                    $sDistance .= $arTariff['FROM'] > 0 ? $arTariff['FROM'] . '-' : 'до ';
                                    $sDistance .= $arTariff['TO'] . ' км';
                                    ?>
                                    <div class="area_1" data-type="tarif" data-distance="<?=$sDistance;?>">

                                        <div class="name_1">Тариф перевозки (<?=$sDistance;?>):</div>
                                        <div class="val_1">
                                            <span class="decs_separators"><?=number_format($arResult['ADDITIONAL_DATA'][$cur_pos]['TARIF'], 0, ',', ' ');?></span> руб/т
                                        </div>
                                    </div>

                                    <?/*if($nds_class != ''){
                                        //если типы НДС не совпадают - отображаем тариф
                                        ?>
                                        <div class="additional_nds_row"><?=number_format($arResult['ADDITIONAL_DATA'][$cur_pos]['TARIF'], 0, ',', ' ');?></span> руб/т</div>
                                    <?}*/?>
                                <?}?>

                                <?if(isset($arResult['ADDITIONAL_DATA'][$cur_pos]['DUMP_RUB'])){
                                    $dump = number_format($arResult['ADDITIONAL_DATA'][$cur_pos]['DUMP_RUB'], 0, ',', ' ');
                                    ?>
                                    <div class="area_1" data-type="dump">
                                        <div class="name_1">Прогноз сброса/прибавки:</div>
                                        <div class="val_1">
                                            <span class="decs_separators_s">
                                            <?if(isset($arResult['ADDITIONAL_DATA'][$cur_pos]['FARMER_DUMP_RUB'])){
                                                $arResult['ADDITIONAL_DATA'][$cur_pos]['FARMER_DUMP_RUB'] *= -1;
                                                ?>
                                                <?=(($arResult['ADDITIONAL_DATA'][$cur_pos]['FARMER_DUMP_RUB']) > 0)?  '+' : '';?><?=number_format($arResult['ADDITIONAL_DATA'][$cur_pos]['FARMER_DUMP_RUB'], 0, ',', ' ');?>
                                            <?}else{
//                                                $arResult['ADDITIONAL_DATA'][$cur_pos]['DUMP_RUB'] *= -1;
//                                                $dump *= -1;
                                                ?>
                                                <?=(($arResult['ADDITIONAL_DATA'][$cur_pos]['DUMP_RUB']) > 0)?  '+' : '';?><?=$dump?>
                                            <?}?>

                                            </span> руб/т
                                        </div>
                                    </div>

                                <?}?>

                                <?if(isset($arResult['ADDITIONAL_DATA'][$cur_pos]['BASE_PRICE'])){?>
                                    <div class="area_1" data-type="base_price" data-deliverytype="СРТ">
                                        <div class="name_1">Базисная цена (СРТ, <?=$arResult['CLIENT_WH_LIST'][$arItem['UF_CLIENT_WH_ID']]['NAME'];?>):</div>
                                        <div class="val_1">
                                            <span class="decs_separators">
                                                <?if(isset($arResult['ADDITIONAL_DATA'][$cur_pos]['FARMER_BASE_CONTR_PRICE'])){?>
                                                    <?=number_format($arResult['ADDITIONAL_DATA'][$cur_pos]['FARMER_BASE_CONTR_PRICE'], 0, ',', ' ');?>
                                                <?}else{?>
                                                    <?=number_format($arResult['ADDITIONAL_DATA'][$cur_pos]['BASE_PRICE'], 0, ',', ' ')?>
                                                <?}?>

                                            </span> руб/т
                                        </div>
                                    </div>

                                <?}?>

                                <?if($arItem['UF_COFFER_TYPE'] == 'p'){?>
                                    <div class="area_1 with_borders" data-type="agent_price">
                                        <div class="name_1">Услуги Агрохелпера, <span class="full_val"><?=$arItem['UF_PARTNER_PRICE'];?></span> руб:</div>
                                        <div class="val_1">
                                            <span class="decs_separators">
                                                <?=round($arItem['UF_PARTNER_PRICE'] / $arItem['UF_VOLUME']);?>
                                            </span> руб/т
                                        </div>
                                    </div>
                                <?}?>
                            </div>
                        <?endif;?>

                        <?if(isset($arResult['OFFER_PARAMS'][$arItem['UF_OFFER_ID']])){?>
                            <div class="prop_area adress_val">
                                <div class="adress">Параметры товара:<?=($arItem['UF_PARTNER_Q_APRVD'] == 1 ? ' <span class="quality_approved">(Подтверждено)</span>' : '');?></div>
                                    <?foreach ($arResult['OFFER_PARAMS'][$arItem['UF_OFFER_ID']] as $param) {
                                        if(isset($arResult['PARAMS_INFO'][$arResult['ADDITIONAL_DATA'][$cur_pos]['CULTURE_ID']][$param['QUALITY_ID']])){
                                        ?>
                                        <div class="val_adress">
                                            <?=$arResult['PARAMS_INFO'][$arResult['ADDITIONAL_DATA'][$cur_pos]['CULTURE_ID']][$param['QUALITY_ID']]['QUALITY_NAME']?>:
                                            <? if ($param['LBASE_ID'] > 0) { ?>
                                                <b><?=$arResult['LBASE_INFO'][$arResult['ADDITIONAL_DATA'][$cur_pos]['CULTURE_ID']][$param['QUALITY_ID']][$param['LBASE_ID']]?></b>
                                            <? } else { ?>
                                                <b><?=$param['BASE']?><?=($arResult['UNIT_INFO'][$param['QUALITY_ID']] != '')?' '.$arResult['UNIT_INFO'][$param['QUALITY_ID']]:''?></b>
                                            <? } ?>
                                        </div>
                                        <?}
                                    }?>
                            </div>
                        <?}?>
                        <?
                        if($arResult['MODE'] != 'agent'){
                            ?>
                            <?if($arItem['UF_COFFER_TYPE'] == 'p'):?>
                                <?
                                $bFirstNotAdd = false;
                                arsort($additional_options_arr);
                                ?>
                            <div class="prop_area adress_val">
                                <div class="adress">Услуги Агрохелпера:</div>

                                <div class="message-add"> <?=GetMessage("ADD:NAME");?></div>
                                <div class="val_adress slide-description">
                                    <div class="radio_group">
                                        <div class="radio_area">
                                            <input
                                                    data-text="<div class='custom_data_text'><span class='option-name'>Заключение договора<i></i></span></div>";
                                                    type="checkbox"  name="IS_AGENT_SERVICE" value="Y" checked="checked" disabled/>
                                        </div>
                                    </div>
                                    <div class="option-description">
                                        <p>Соберем полный пакет документов по требованию. Заключим договор между Вами и Агропроизводителем.</p>
                                    </div>
                                </div>

                                <?
                                $bShowedDocs = false;
                                foreach ($additional_options_arr as $sCode => $bVal):?>
                                    <?if($bVal == 0 && !$bFirstNotAdd):
                                        $bFirstNotAdd = true;
                                        ?>
                                        <div class="message-add"> <?=GetMessage("NOT_ADD:NAME");?></div>
                                    <?endif;?>
                                    <?switch ($sCode){
                                        case 'IS_ADD_CERT':
                                            ?>
                                            <div class="val_adress slide-description">
                                                <div class="radio_group">
                                                    <div class="small_radio">
                                                        <div class="radio_area">
                                                            <input type="checkbox" <?if(isset($additional_options_arr['IS_ADD_CERT']) && $additional_options_arr['IS_ADD_CERT'] == 1){?>checked="checked" readonly="readonly"<?}?> data-text="<?=GetMessage("IS_ADD_CERT:NAME");?>" name="IS_ADD_CERT" id="IS_ADD_CERT" value="Y" >
                                                        </div>
                                                    </div>
                                                </div>
                                                <?=GetMessage("IS_ADD_CERT:DESCRIPTION");?>
                                            </div>
                                            <?
                                            break;
                                        case 'IS_BILL_OF_HEALTH':
                                            ?>
                                            <div class="val_adress  slide-description">
                                                <?if(!$bShowedDocs){
                                                    $bShowedDocs = true;
                                                ?>
                                                <div><?=GetMessage("DOCS:NAME");?><br><br></div>
                                                <?=GetMessage("DOCS:DESCRIPTION");?><br>
                                                <?}?>
                                                <div class="radio_group">
                                                    <div class="inline_radio">
                                                        <div class="radio_area">
                                                            <input type="checkbox" <?if(isset($additional_options_arr[$sCode]) && $additional_options_arr[$sCode] == 1){?>checked="checked" readonly="readonly"<?}?> data-text="<?=GetMessage($sCode . ":NAME");?>" name="<?=$sCode;?>" id="<?=$sCode;?>" value="Y" >
                                                        </div>
                                                    </div>
                                            <?
                                            break;
                                        case 'IS_VET_CERT':
                                            ?>
                                            <div class="inline_radio">
                                                <div class="radio_area">
                                                    <input type="checkbox" <?if(isset($additional_options_arr[$sCode]) && $additional_options_arr[$sCode] == 1){?>checked="checked" readonly="readonly"<?}?> data-text="<?=GetMessage($sCode . ":NAME");?>" name="<?=$sCode;?>" id="<?=$sCode;?>" value="Y" >
                                                </div>
                                            </div>
                                            <?
                                            break;
                                        case 'IS_QUALITY_CERT':
                                            ?>
                                                    <div class="inline_radio">
                                                        <div class="radio_area">
                                                            <input type="checkbox" <?if(isset($additional_options_arr[$sCode]) && $additional_options_arr[$sCode] == 1){?>checked="checked" readonly="readonly"<?}?> data-text="<?=GetMessage($sCode . ":NAME");?>" name="<?=$sCode;?>" id="<?=$sCode;?>" value="Y" >
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <?
                                            break;
                                        case 'IS_TRANSFER':
                                            ?>
                                            <div class="val_adress  slide-description">
                                                <div class="radio_group">
                                                    <div class="small_radio">
                                                        <div class="radio_area">
                                                            <input type="checkbox" <?if(isset($additional_options_arr['IS_TRANSFER']) && $additional_options_arr['IS_TRANSFER'] == 1){?>checked="checked" readonly="readonly"<?}?> data-text="<?=GetMessage("IS_TRANSFER:NAME");?>" name="IS_TRANSFER" id="IS_TRANSFER" value="Y" >
                                                        </div>
                                                    </div>
                                                </div>

                                                <?=GetMessage("IS_TRANSFER:DESCRIPTION");?>
                                            </div>

                                            <?
                                            break;
                                        case 'IS_SECURE_DEAL':
                                            ?>
                                            <div class="val_adress  slide-description">
                                                <div class="radio_group">
                                                    <div class="small_radio">
                                                        <div class="radio_area">
                                                            <input type="checkbox" <?if(isset($additional_options_arr['IS_SECURE_DEAL']) && $additional_options_arr['IS_SECURE_DEAL'] == 1){?>checked="checked" readonly="readonly"<?}?> data-text="<?=GetMessage("IS_SECURE_DEAL:NAME");?>" name="IS_SECURE_DEAL" id="IS_SECURE_DEAL" value="Y" >
                                                        </div>
                                                    </div>
                                                </div>

                                                <?=GetMessage("IS_SECURE_DEAL:DESCRIPTION");?>
                                            </div>
                                            <?
                                            break;
                                        case 'IS_AGENT_SUPPORT':
                                            ?>

                                            <div class="val_adress  slide-description">
                                                <div class="radio_group">
                                                    <div class="small_radio">
                                                        <div class="radio_area">
                                                            <input type="checkbox" <?if(isset($additional_options_arr['IS_AGENT_SUPPORT']) && $additional_options_arr['IS_AGENT_SUPPORT'] == 1){?>checked="checked" readonly="readonly"<?}?> data-text="<?=GetMessage("IS_AGENT_SUPPORT:NAME");?>" name="IS_AGENT_SUPPORT" id="IS_AGENT_SUPPORT" value="Y" >
                                                        </div>
                                                    </div>
                                                </div>

                                                <?=GetMessage("IS_AGENT_SUPPORT:DESCRIPTION");?>
                                            </div>
                                            <?
                                            break;


                                    }?>

                                <?endforeach;?>

                                <div class="val_adress  ">
                                    <?if(($counter_request_right == 'y'
                                            || $counter_request_right == 'l'
                                        )
                                        && $active_volume
                                        && $arResult['MODE'] != 'agent'
                                    ){
                                        //проверка является ли агентским предложением
                                        if($arItem['UF_COFFER_TYPE'] == 'p'){?>
                                            <div class="accept"><a class="submit-btn js-agree" href="javascript: void(0);">Контракт</a></div>
                                        <?}else{?>
                                            <div class="accept"><a class="submit-btn js-agree" href="javascript: void(0);">Принять</a></div>
                                        <?}
                                    } elseif (!$active_volume) { ?>
                                        <div class="accept"><div class="submit-btn" >Продано</div></div>
                                    <? } ?>
                                </div>
                            </div>
                            <?else:?>
                            <div class="prop_area adress_val">
                                <div class="adress">Услуги Агрохелпера:</div>
                                <div class="val_adress slide-description">
                                    <div class="radio_group">
                                        <div class="small_radio">
                                            <div class="radio_area">
                                                <input type="checkbox" <?if(isset($additional_options_arr['IS_ADD_CERT']) && $additional_options_arr['IS_ADD_CERT'] == 1){?>checked="checked" readonly="readonly"<?}?> data-text="<?=GetMessage("IS_ADD_CERT:NAME");?>" name="IS_ADD_CERT" id="IS_ADD_CERT" value="Y" >
                                            </div>
                                        </div>
                                    </div>
                                    <?=GetMessage("IS_ADD_CERT:DESCRIPTION");?>
                                </div>

                                <div class="val_adress  slide-description">
                                    <div><?=GetMessage("DOCS:NAME");?><br><br></div>
                                    <?=GetMessage("DOCS:DESCRIPTION");?><br>
                                    <div class="radio_group">
                                        <div class="inline_radio">
                                            <div class="radio_area">
                                                <input type="checkbox" <?if(isset($additional_options_arr['IS_BILL_OF_HEALTH']) && $additional_options_arr['IS_BILL_OF_HEALTH'] == 1){?>checked="checked" readonly="readonly"<?}?> data-text="<?=GetMessage("IS_BILL_OF_HEALTH:NAME");?>" name="IS_BILL_OF_HEALTH" id="IS_BILL_OF_HEALTH" value="Y" >
                                            </div>
                                        </div>
                                        <div class="inline_radio">
                                            <div class="radio_area">
                                                <input type="checkbox" <?if(isset($additional_options_arr['IS_VET_CERT']) && $additional_options_arr['IS_VET_CERT'] == 1){?>checked="checked" readonly="readonly"<?}?> data-text="<?=GetMessage("IS_VET_CERT:NAME");?>" name="IS_VET_CERT" id="IS_VET_CERT" value="Y" >
                                            </div>
                                        </div>
                                        <div class="inline_radio">
                                            <div class="radio_area">
                                                <input type="checkbox" <?if(isset($additional_options_arr['IS_QUALITY_CERT']) && $additional_options_arr['IS_QUALITY_CERT'] == 1){?>checked="checked" readonly="readonly"<?}?> data-text="<?=GetMessage("IS_QUALITY_CERT:NAME");?>" name="IS_QUALITY_CERT" id="IS_QUALITY_CERT" value="Y" >
                                            </div>
                                        </div>
                                    </div>


                                </div>

                                <div class="val_adress  slide-description">
                                    <div class="radio_group">
                                        <div class="small_radio">
                                            <div class="radio_area">
                                                <input type="checkbox" <?if(isset($additional_options_arr['IS_TRANSFER']) && $additional_options_arr['IS_TRANSFER'] == 1){?>checked="checked" readonly="readonly"<?}?> data-text="<?=GetMessage("IS_TRANSFER:NAME");?>" name="IS_TRANSFER" id="IS_TRANSFER" value="Y" >
                                            </div>
                                        </div>
                                    </div>

                                    <?=GetMessage("IS_TRANSFER:DESCRIPTION");?>
                                </div>

                                <div class="val_adress  slide-description">
                                    <div class="radio_group">
                                        <div class="small_radio">
                                            <div class="radio_area">
                                                <input type="checkbox" <?if(isset($additional_options_arr['IS_SECURE_DEAL']) && $additional_options_arr['IS_SECURE_DEAL'] == 1){?>checked="checked" readonly="readonly"<?}?> data-text="<?=GetMessage("IS_SECURE_DEAL:NAME");?>" name="IS_SECURE_DEAL" id="IS_SECURE_DEAL" value="Y" >
                                            </div>
                                        </div>
                                    </div>

                                    <?=GetMessage("IS_SECURE_DEAL:DESCRIPTION");?>
                                </div>


                                <div class="val_adress  slide-description">
                                    <div class="radio_group">
                                        <div class="small_radio">
                                            <div class="radio_area">
                                                <input type="checkbox" <?if(isset($additional_options_arr['IS_AGENT_SUPPORT']) && $additional_options_arr['IS_AGENT_SUPPORT'] == 1){?>checked="checked" readonly="readonly"<?}?> data-text="<?=GetMessage("IS_AGENT_SUPPORT:NAME");?>" name="IS_AGENT_SUPPORT" id="IS_AGENT_SUPPORT" value="Y" >
                                            </div>
                                        </div>
                                    </div>

                                    <?=GetMessage("IS_AGENT_SUPPORT:DESCRIPTION");?>
                                </div>

                                <div class="val_adress  ">
                                    <?if(($counter_request_right == 'y'
                                            || $counter_request_right == 'l'
                                        )
                                        && $active_volume
                                        && $arResult['MODE'] != 'agent'
                                    ){
                                        //проверка является ли агентским предложением
                                        if($arItem['UF_COFFER_TYPE'] == 'p'){?>
                                            <div class="accept"><a class="submit-btn js-agree" href="javascript: void(0);">Контракт</a></div>
                                        <?}else{?>
                                            <div class="accept"><a class="submit-btn js-agree" href="javascript: void(0);">Принять</a></div>
                                        <?}
                                    } elseif (!$active_volume) { ?>
                                        <div class="accept"><div class="submit-btn" >Продано</div></div>
                                    <? } ?>
                                </div>
                            </div>
                            <?endif;?>
                            <?
                        }
                        ?>
                    </form>
                </div>
            <?
                if(is_numeric($arItem['UF_VOLUME']) && $arItem['CHECKED']) {
                    $volume_total += $arItem['UF_VOLUME'];

                    if(is_numeric($arResult['ADDITIONAL_DATA'][$cur_pos]['BASE_PRICE'])){
                        $price_total += $arResult['ADDITIONAL_DATA'][$cur_pos]['BASE_PRICE'] * $arItem['UF_VOLUME'];
                    }

                    //если типы НДС поставщика и покупателя не совпадают, то отдельно считаем для нужного НДС
                    if($nds_class != ''){
                        $volume_other_nds_total += $arItem['UF_VOLUME'];
                        $price_other_nds_total += $arResult['ADDITIONAL_DATA'][$cur_pos]['BASE_CONTR_PRICE'] * $arItem['UF_VOLUME'];
                    }
                }
            }

            if($volume_total != 0){
                if($price_total != 0){
                    $price_total = round($price_total / $volume_total);
                }

                $volume_total = number_format($volume_total, 0, ',', ' ') . ' т';
            }else{
                $volume_total = '-';
            }

            if($price_total != 0){
                $price_total = number_format($price_total, 0, ',', ' ') . ' руб/т';
            }else{
                $price_total = '-';
            }


            //рассчитываем данные для НДС поставщика, если его тип НДС отличается от типа НДС покупателя
            if($volume_other_nds_total != 0){
                if($price_other_nds_total != 0){
                    $price_other_nds_total = round($price_other_nds_total / $volume_other_nds_total);
                }

                $volume_other_nds_total = number_format($volume_other_nds_total, 0, ',', ' ') . ' т';
            }

            if($price_other_nds_total != 0){
                $price_other_nds_total = number_format($price_other_nds_total, 0, ',', ' ') . ' руб/т';
                $active_additional = true;
            }

            ?>

            <div class="list_page_rows_total_line">
                <div class="total_values"><div class="label">Всего:</div><div class="volume_val"><?=$volume_total;?></div><div class="price_val"><?=$price_total;?></div></div>
                <?//Формируем ссылку для покупаетля?>

                <div class="additional_values <?if($active_additional){?>active<?}?>"><div class="volume_val"><?='&nbsp;';//=$volume_other_nds_total;?></div><div class="price_val"><?=$price_other_nds_total;?></div></div>

                <?if($arResult['MODE'] == 'agent' && $arResult['SET_URL_PAGE']):?>
                    <div class="page-url"><a href="#">Ссылка<br>покупателю</a></div>
                    <script>
                        var pageDataObject = <?=CUtil::PhpToJSObject($arResult['URL_PAGE_PARAMS']);?>
                    </script>
                <?endif;?>
                <div class="clear"></div>
            </div>
        </div>
    </div>
    
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
<?}elseif(isset($_GET['o'])
    && is_numeric($_GET['o'])
    && isset($_GET['r'])
    && is_numeric($_GET['r'])){
    ?><br/>Предложение больше недоступно. Обратись к организатору<?
}else
    {
    //проверяем наличие предложений при наличии данных переадресации (чтобы исключить ситуацию, когда пользователь не видит предложений и не может сбросить фильтр без повторной авторизации - такая ситуация возможна например после принятия предложения)
    if(isset($arParams['CLIENT_ID'])){
        if(isset($_COOKIE['count_req_filter_culture'])
            && filter_var($_COOKIE['count_req_filter_culture'], FILTER_VALIDATE_INT)
            || isset($_COOKIE['count_req_filter_warehouse'])
            && filter_var($_COOKIE['count_req_filter_warehouse'], FILTER_VALIDATE_INT)
        ){
            if(client::checlAvailableOffers($arParams['CLIENT_ID'])){
                //есть предложения, сбрасываем куки
                $del_time = time() - 100;
                setcookie('count_req_filter_culture', '0', $del_time, '/');
                setcookie('count_req_filter_warehouse', '0', $del_time, '/');
                header('Location: /client/exclusive_offers/');
                exit;
            }
        }
    }elseif(isset($arParams['AGENT_ID'])){
        if(isset($_COOKIE['count_req_filter_client'])
            && filter_var($_COOKIE['count_req_filter_client'], FILTER_VALIDATE_INT)
            || isset($_COOKIE['count_req_filter_culture'])
            && filter_var($_COOKIE['count_req_filter_culture'], FILTER_VALIDATE_INT)
            || isset($_COOKIE['count_req_filter_region'])
            && filter_var($_COOKIE['count_req_filter_region'], FILTER_VALIDATE_INT)
            || isset($_COOKIE['count_req_filter_warehouse'])
            && filter_var($_COOKIE['count_req_filter_warehouse'], FILTER_VALIDATE_INT)
        ){
            $client_list = partner::getClients($arParams['AGENT_ID']);
            if(count($client_list) > 0
                && client::checlAvailableOffers($arParams['CLIENT_ID'])
            ){
                //есть предложения, сбрасываем куки
                $del_time = time() - 100;
                setcookie('count_req_filter_client', '0', $del_time, '/');
                setcookie('count_req_filter_culture', '0', $del_time, '/');
                setcookie('count_req_filter_region', '0', $del_time, '/');
                setcookie('count_req_filter_warehouse', '0', $del_time, '/');
                header('Location: /partner/client_exclusive_offers/');
                exit;
            }
        }
    }
    ?><br/>Ни одного предложения не найдено<?
}?>