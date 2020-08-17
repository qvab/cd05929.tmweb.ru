<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
    <?
    $arResult['CULTURE_LIST'] = rrsIblock::getElementList(rrsIblock::getIBlockId('cultures'));
    $arResult['OFFER_PARAMS'] = farmer::getParamsList($arResult['ELEMENTS']);
    $arResult['GRAPH_DATA'] = array();
    $arResult['FARMER_ID'] = 0;
    $arResult['PARTNER_DATA'] = '';

    $res = CIBlockElement::GetList(
        array('SORT' => 'ASC', 'ID' => 'ASC'),
        array('IBLOCK_ID' => rrsIblock::getIBlockId('quality'), 'ACTIVE' => 'Y'),
        false,
        false,
        array('ID', 'NAME', 'PROPERTY_UNIT')
    );
    while ($ob = $res->Fetch()) {
        $arResult['UNIT_INFO'][$ob['ID']] = $ob['PROPERTY_UNIT_VALUE'];
    }

    $res = CIBlockElement::GetList(
        array('SORT' => 'ASC', 'ID' => 'ASC'),
        array('IBLOCK_ID' => rrsIblock::getIBlockId('basis_values'), 'ACTIVE' => 'Y'),
        false,
        false,
        array('ID', 'NAME', 'PROPERTY_QUALITY', 'PROPERTY_CULTURE')
    );
    while ($ob = $res->Fetch()) {
        foreach ($ob['PROPERTY_CULTURE_VALUE'] as $culture_id) {
            $arResult['LBASE_INFO'][$culture_id][$ob['PROPERTY_QUALITY_VALUE']][$ob['ID']] = $ob['NAME'];
        }
    }

    $res = CIBlockElement::GetList(
        array('ID' => 'DESC'),
        array('IBLOCK_ID' => rrsIblock::getIBlockId('characteristics'), 'ACTIVE' => 'Y'),
        false,
        false,
        array('ID', 'NAME', 'PROPERTY_CULTURE', 'PROPERTY_QUALITY', 'PROPERTY_QUALITY.NAME')
    );
    while ($ob = $res->Fetch()) {
        $arResult['PARAMS_INFO'][$ob['PROPERTY_CULTURE_VALUE']][$ob['PROPERTY_QUALITY_VALUE']] = array(
            'ID' => $ob['ID'],
            'QUALITY_NAME' => $ob['PROPERTY_QUALITY_NAME']
        );
    }

    $warehousesIds = array();
    foreach ($arResult["ITEMS"] as $arItem) {
        $warehousesIds[$arItem['PROPERTIES']['WAREHOUSE']['VALUE']] = true;
    }
    $arResult['WAREHOUSES_LIST'] = farmer::getWarehouseParamsList(array_keys($warehousesIds));

    $res = CIBlockElement::GetList(
        array('ID' => 'DESC'),
        array('IBLOCK_ID' => rrsIblock::getIBlockId('deals_deals'), 'ACTIVE' => 'Y', 'PROPERTY_OFFER' => $arResult['ELEMENTS']),
        false,
        false,
        array('ID', 'NAME', 'DATE_CREATE', 'PROPERTY_VOLUME', 'PROPERTY_STATUS', 'PROPERTY_OFFER')
    );
    while ($ob = $res->Fetch()) {
        $arResult['DEALS'][$ob['PROPERTY_OFFER_VALUE']][] = array(
            'ID' => $ob['ID'],
            'DATE_CREATE' => $ob['DATE_CREATE'],
            'VOLUME' => $ob['PROPERTY_VOLUME_VALUE'],
            'STATUS' => $ob['PROPERTY_STATUS_VALUE']
        );
    }

    //отдельно сохраняем данные складов и культур для передачи имен складов в component_epilog
    $this->__component->arResult["CACHED_WAREHOUSES_LIST"] = array();
    foreach($arResult['WAREHOUSES_LIST'] as $cur_id => $cur_data){
        $this->__component->arResult["CACHED_WAREHOUSES_LIST"][$cur_id] = $cur_data['NAME'];
    }
    $this->__component->SetResultCacheKeys(array("CACHED_WAREHOUSES_LIST"));
    $this->__component->arResult["CACHED_CULTURES_LIST"] = array();
    foreach($arResult['CULTURE_LIST'] as $cur_id => $cur_data){
        $this->__component->arResult["CACHED_CULTURES_LIST"][$cur_id] = $cur_data['NAME'];
    }
    $this->__component->SetResultCacheKeys(array("CACHED_CULTURES_LIST"));

    unset($warehousesIds, $res, $ob);
    $arResult['COUNTER_REQUEST'] = farmer::getCounterRequestsData($arResult['ELEMENTS']);

    //получение данных организатора для вывода во встречном предложении
    if(!empty($arParams['PARTNER_ID'])){
        $obRes = CUser::GetList(
            ($by = 'ID'), ($order = 'ASC'),
            array('ID' => $arParams['PARTNER_ID']),
            array('FIELDS' => array('NAME', 'LAST_NAME'))
        );
        if($arData = $obRes->Fetch()){
            if(!empty($arData['NAME'])){
                $arResult['PARTNER_DATA'] = trim($arData['NAME']);
            }
            if($arResult['PARTNER_DATA'] == ''){
                $arResult['PARTNER_DATA'] = trim($arData['LAST_NAME']);
            }
        }

        $obRes = CIBlockElement::GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('partner_profile'),
                'PROPERTY_USER' => $arParams['PARTNER_ID'],
                '!PROPERTY_PHONE' => false,
            ),
            false,
            array('nTopCount' => 1),
            array('PROPERTY_PHONE')
        );
        if($arData = $obRes->Fetch()){
            $arResult['PARTNER_DATA'] = 'Телефон для связи: ' . $arData['PROPERTY_PHONE_VALUE'] . ($arResult['PARTNER_DATA'] != '' ? ', ' . $arResult['PARTNER_DATA'] : '');
        }
    }

    //получение данных для отправки встречных предложений
    //а также получение данных запросов по товарам
    if(isset($arResult['ELEMENTS'])
        && count($arResult['ELEMENTS']) > 0
    ){
        //получение данных для отправки встречных предложений
        global $USER;
        //проверка прав на отправление ВП
//        $user_rights = farmer::checkRights('counter_request', $USER->GetID());
//        if(isset($user_rights['REQUEST_RIGHT'])
//            && $user_rights['REQUEST_RIGHT'] == 'Y'
//            && (
//                !isset($_REQUEST['status'])
//                || $_REQUEST['status'] != 'no'
//            )
//        ) {
            //получение наличия встречных запросов для товаров (в этом случае нельзя создавать повторные встречные предложения)
            $counter_requests_data = farmer::getCounterRequestsData($arResult['ELEMENTS']);
            //получение начальной цены и ограничений для ввода (работа с данными соответствий)
            //получаем соответствия для товаров
            $arLeads = lead::getLeadList(array('UF_OFFER_ID' => $arResult['ELEMENTS']), ['UF_CSM_PRICE' => 'DESC']);
            $offerRequestApply = lead::createLeadList($arLeads);
            $arReqsCsmCounted = array();
            //получаем максимальную (+10% к максимальной цене) и минимальную цены (-10% от минимальной цены) для ограничений, а также цену для установления по умолчанию (берется максимальная из имеющихся цен в $offerRequestApply)
            $temp_val = 0;
            $min_val = 0;
            $max_val = 0;
            $set_val = 0;
            $req_ids = array();
            $arrTemp = reset($arResult['ITEMS']);
            $user_data = farmer::getProfile($arrTemp['PROPERTIES']['FARMER']['VALUE']);
            $arResult['FARMER_ID'] = (!empty($arrTemp['PROPERTIES']['FARMER']['VALUE']) ? $arrTemp['PROPERTIES']['FARMER']['VALUE'] : 0);

            $nds_val = ($user_data['PROPERTY_NDS_CODE'] == 'Y' ? 'y' : 'n');
            foreach ($arResult['ELEMENTS'] as $cur_id) {
                $descr = '';
                if (!isset($counter_requests_data[$cur_id]['UF_VOLUME_REMAINS'])
                    || intval($counter_requests_data[$cur_id]['UF_VOLUME_REMAINS']) == 0
                ) {
                    $temp_val = 0;
                    $min_val = 0;
                    $max_val = 0;
                    $set_val = 0;
                    $req_ids = array();
                    foreach ($offerRequestApply as $cur_data) {
                        if ($cur_data['OFFER']['ID'] == $cur_id) {
                            $arReqsCsmCounted[$cur_data['OFFER']['ID']][$cur_data['REQUEST']['ID']] = $cur_data['REQUEST']['BEST_PRICE']['ACC_PRICE_CSM'];
                            $temp_val = round($cur_data['REQUEST']['BEST_PRICE']['ACC_PRICE_CSM']);
                            if ($min_val > $temp_val || $min_val == 0) {
                                $min_val = $temp_val;
                            }
                            if ($max_val < $temp_val || $max_val == 0) {
                                $max_val = $temp_val;
                            }

                            $req_ids[$cur_data['REQUEST']['ID']] = true;

//                            if (isset($counter_requests_data[$cur_id]['UF_VOLUME_REMAINS'])
//                                && intval($counter_requests_data[$cur_id]['UF_VOLUME_REMAINS']) == 0
//                            ) {
//                                $descr = $counter_requests_data[$cur_id]['UF_DATE'].' для культуры '.$cur_data['OFFER']['CULTURE_NAME'].' со склада '.str_replace('"', '&quot;', $cur_data['OFFER']['WH_NAME']).' направлено предложение с заявленным объёмом '.$counter_requests_data[$cur_id]['UF_VOLUME'].' т. и ценой '.$counter_requests_data[$cur_id]['UF_FARMER_PRICE'].' руб/т. Тип доставки установлен как '.$counter_requests_data[$cur_id]['UF_DELIVERY'].'.';
//                            }
                        }
                    }
                    $set_val = round($max_val);
                    $max_val = round($max_val + $max_val * 0.2);
                    $min_val = round($min_val - $min_val * 0.2);

                    $rec_value = deal::getRecommendedPriceText($cur_id,true, $nds_val, array('rec_price' => true));
//                    p($rec_value);
//                    p(array($cur_id,true, $nds_val, array('rec_price')), 1);
//                    p($rec_value);

                    //выводим данные в форму (эти данные встаятся в форму в script.js)
                    if ($set_val > 0) {
                        $arrOfferData = farmer::getOfferById($cur_id);
                        ob_start();
                        ?>
                        <div class="prop_area adress_val counter_data">
                            <div class="val_adress">
                                <div class="counter_request_additional_data">
                                    <div class="row first_row">
                                        <?if(!empty($rec_value['rec_price'])){?>
                                        <div>
                                            <div class="r_price_block">
                                                <div class="pr_1">Рекомендация цены: <div class="pr_val_rec rowed"><span class="val_span"><?=number_format($rec_value['rec_price'], 0, '.', ' ');?></span> руб/т</div></div>
                                            </div>
                                        </div>
                                        <?}?>
                                        <div class="flex-row"><div class="row_head">Моя цена "с места":</div><div class="row_val min_max_val"><div class="min_price"><?=number_format($min_val, 0, '.', ' ');?><span>min</span></div><span class="minus minus_bg" data-step="50" onclick="farmerClickCounterMinPrice(this);" data-min="<?=$min_val;?>"></span><input type="text" name="price" placeholder="" value=""><span class="plus plus_bg" data-step="50" onclick="farmerClickCounterMaxPrice(this);" data-max="<?=$max_val;?>"></span><div class="max_price"><?=number_format($max_val, 0, '.', ' ');?><span>max</span></div></div></div>
                                        <div class="clear no_line"></div>
                                    </div>
                                    <div class="row">
                                        <div class="flex-row">
                                            <div class="row_head">Указать количество тонн:</div>
                                            <div class="row_val"><input type="text" data-checkval="y" data-checktype="pos_int" name="volume" placeholder="" value="<?=(!empty($arParams['VOLUME_VAL']) ? $arParams['VOLUME_VAL'] : '');?>"><span class="ton_pos">т.</span></div>
                                        </div>
                                        <div class="clear no_line"></div>
                                    </div>
                                    <?if(!empty($arParams['PARTNER_ID'])){?>
                                        <div class="row">
                                            <div class="flex-row">
                                                <div class="row_head">Стоимость услуги, руб:</div>
                                                <div class="row_val"><input type="text" readonly="readonly" placeholder="" name="serv_price" value="<?=(!empty($arrOfferData['Q_APPROVED']) ? number_format(rrsIblock::getConst('counter_option_lab'), 0, '.', ' ') : 0)?>"><div class="partner_price_part"><span class="val">0</span> руб/т</div></div>
                                            </div>
                                            <div class="clear no_line"></div>
                                        </div>
                                    <?}?>
                                    <input type="button" name="save" value="Отправить предложение" class="submit-btn counter_request_submit"><div class="refinement_text">Срок действия предложения - 7 дней.</div>
                                </div>
                            </div>
                        </div>
                        <?
                        $arResult['COUNTER_DATA'] = ob_get_clean();
                        $arResult['COUNTER_EXIST'] = 1;

                        //echo '<div class="send_counter_req_data" data-offer="' . $cur_id . '" data-setval="' . $set_val . '" data-minval="' . $min_val . '" data-maxval="' . $max_val . '" data-rec="'.$rec_text.'" data-reqs="' . implode(',', array_keys($req_ids)) . '" style="display: none;"></div>';
                    }else{
                        //нет предложений и соответствий
                        $arResult['COUNTER_DATA'] = '<div class="prop_area adress_val counter_data">
                            <div class="val_adress">
                                Нет запросов для отправки предложения.
                            </div>
                        </div>';
                    }
                }else{
                    //предложение было отправлено ранее
                    foreach ($offerRequestApply as $cur_data) {
                        if ($cur_data['OFFER']['ID'] == $cur_id) {
                            ob_start();
                            //разделяем тексты для организатора и поставщика
                            if(!empty($arParams['PARTNER_ID'])) {
                                ?><span class="copy-text"><?= $counter_requests_data[$cur_id]['UF_DATE']; ?> <br/>Вы сделали предложение <?= $counter_requests_data[$cur_id]['UF_VOLUME']; ?> т товара <?= $cur_data['OFFER']['CULTURE_NAME']; ?>, со склада <?= $cur_data['OFFER']['WH_NAME']; ?> по цене "с места": <?= $counter_requests_data[$cur_id]['UF_FARMER_PRICE']; ?> руб/т.<?
                                if(!empty($arResult['PARTNER_DATA'])){
                                    echo '<br/>' . $arResult['PARTNER_DATA'];
                                }

                                //запрещаем организатору отменять предложение, если оно отправлено не им
                                if ($arParams['PARTNER_ID'] == $counter_requests_data[$cur_id]['UF_BY_PARTNER_REAL']) {
                                    ?><div class="cancel_counter_offer_area"><a class="submit-btn" href="javascript: void(0);">Отменить предложение</a></div><?
                                }else{
                                    ?><div class="no_cancel_counter_offer_area"></div><?
                                }
                                ?>
                                </span>
                                <div class="js-copy copy-left html_val">Скопировать</div>
                                <?
                            }else{
                                ?><?= $counter_requests_data[$cur_id]['UF_DATE']; ?> для культуры "<?= $cur_data['OFFER']['CULTURE_NAME']; ?>" со склада <?= $cur_data['OFFER']['WH_NAME']; ?> направлено предложение с заявленным объёмом <?= $counter_requests_data[$cur_id]['UF_VOLUME']; ?> т. и ценой <?= $counter_requests_data[$cur_id]['UF_FARMER_PRICE']; ?> руб/т. Тип доставки установлен как <?= $counter_requests_data[$cur_id]['UF_DELIVERY']; ?>.
                                <div class="cancel_counter_offer_area"><?
                                ?><a class="submit-btn" href="javascript: void(0);">Отменить предложение</a></div><?
                            }
                            $descr = ob_get_clean();
                            break;
                        }
                    }

                    ob_start();?>
                        <div class="prop_area adress_val counter_data">
                            <div class="val_adress no_pad">
                                <?=$descr;?>
                            </div>
                        </div>
                    <?
                    $arResult['COUNTER_DATA'] = ob_get_clean();
                }
            }
//        }
    }


//отправляем данные в component_epilog.php
$cp = $this->__component;
$cp->SetResultCacheKeys(array('FARMER_ID'));