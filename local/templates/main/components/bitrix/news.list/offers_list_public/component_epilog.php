<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();?>
<?
if ($_REQUEST['offer'] > 0) {
    farmer::deactivateOffer($_REQUEST['offer']);
}
//првоерка необходимости переадресации на другую страницу
$off_id = 0;
if (isset($_GET['id'])
    && $_GET['id'] > 0
){
    $off_id = $_GET['id'];
}
if(isset($_GET['offer_id'])
    && $_GET['offer_id'] > 0
) {
    $off_id = $_GET['offer_id'];
}
if($off_id > 0){
    //получение страницы переадресации (проверка)
    $new_url = farmer::getOfferListRedirectById($off_id, $arParams['NEWS_COUNT'], (isset($_GET['PAGEN_1']) ? $_GET['PAGEN_1'] : 1));

    if($new_url != '') {
        LocalRedirect($new_url);
        exit;
    }
}

if ($_GET['id'] > 0) {
    if(!in_array($_GET['id'], $arResult['ELEMENTS'])
        && (!isset($_GET['status']) || $_GET['status'] != 'no')
    ){
        LocalRedirect('/farmer/offer/?status=no&id=' . $_GET['id'] . '#' . $_GET['id']);
    }else{
        ?><script type="text/javascript">
            $(document).ready(function(){
                var offerInptObj = $('input[type=hidden][value="<?=$_GET['id'];?>"]');
                if(offerInptObj.length == 1){
                    var offerObj = offerInptObj.parents('.line_area');
                    offerObj.find('.line_inner').trigger('click');
                    setTimeout(function(){
                        var offsetObj = offerObj.offset();
                        $(document).scrollTop(offsetObj.top - 30);
                    }, 500);
                }
            });
        </script><?
    }
}

if(isset($_GET['offer_id'])){
    if(in_array($_GET['offer_id'], $arResult['ELEMENTS'])){
        ?><script type="text/javascript">
            $(document).ready(function(){
                var offerInptObj = $('input[type=hidden][value="<?=$_GET['offer_id'];?>"]');
                if(offerInptObj.length == 1){
                    var offerObj = offerInptObj.parents('.line_area');
                    offerObj.find('.line_inner').trigger('click');
                    setTimeout(function(){
                        var offsetObj = offerObj.offset();
                        $(document).scrollTop(offsetObj.top - 30);
                    }, 500);
                }
            });
        </script><?
    }
}

if ($_GET['id'] > 0) {
    if(!in_array($_GET['id'], $arResult['ELEMENTS'])
        && (!isset($_GET['status']) || $_GET['status'] != 'no')
    ){
        LocalRedirect('/partner/offer/?status=no&id=' . $_GET['id']);
    }else{?>
        <script type="application/javascript">
            $(document).ready(function(){
                var offerInptObj = $('input[type=hidden][value="<?=$_GET['id'];?>"]');
                if(offerInptObj.length == 1){

                    var offerObj = offerInptObj.parents('.line_area');
                    offerObj.find('.line_inner').trigger('click');

                    <?if(!empty($_GET['affair']) && $_GET['affair'] == 'y'):?>

                    // Раскрываем форму добавления дела
                    var blockAffair = $('#affair_<?=$_GET['id']?>');
                    blockAffair.find('.btn-show-form-add-new').trigger('click');
                    // Скроллим
                    setTimeout(function(){
                        var offsetObj = blockAffair.offset();
                        $(document).scrollTop(offsetObj.top - 30);
                    }, 500);
                    <?else:?>
                    setTimeout(function(){
                        var offsetObj = offerObj.offset();
                        $(document).scrollTop(offsetObj.top - 30);
                    }, 500);
                    <?endif;?>
                }
            });
        </script>
    <?}
}

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

global $USER;
$iPartnerId = $USER->GetID();
$arAgentInfo = partner::getProfile($iPartnerId, true);
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
        $nds_val = 'n';
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

                if ($cur_data['OFFER']['USER_NDS'] == 'yes') {
                    $nds_val = 'y';
                }

                if (isset($counter_requests_data[$cur_id]['UF_VOLUME_REMAINS'])
                    && intval($counter_requests_data[$cur_id]['UF_VOLUME_REMAINS']) == 0
                ) {
                    $descr = "{$counter_requests_data[$cur_id]['UF_DATE']}<br>"
                        . "Вы сделали предложение {$counter_requests_data[$cur_id]['UF_VOLUME_OFFER']} т товара {$cur_data['OFFER']['CULTURE_NAME']}, со склада " . str_replace('"', '&quot;', $cur_data['OFFER']['WH_NAME']) . " по цене &quot;с места&quot;: {$counter_requests_data[$cur_id]['UF_FARMER_PRICE']} руб/т. <br>"
                        . "Телефон для связи {$arAgentInfo['PROPERTY_PHONE_VALUE']}, {$arAgentInfo['USER']['NAME']}";
                }
            }
        }
        $set_val = round($max_val);
        $max_val = round($max_val + $max_val * 0.2);
        $min_val = round($min_val - $min_val * 0.2);

        $rec_text = deal::getRecommendedPriceText($cur_id,true, $nds_val);

        //выводим данные в форму (эти данные встаятся в форму в script.js)
        if ($set_val > 0) {
            echo '<div class="send_counter_req_data" data-offer="' . $cur_id . '" data-setval="' . $set_val . '" data-minval="' . $min_val . '" data-maxval="' . $max_val . '" data-rec="'.$rec_text.'" data-reqs="' . implode(',', array_keys($req_ids)) . '" style="display: none;"></div>';
        }else{
            ?><div class="send_counter_req_data" data-offer="<?=$cur_id;?>" data-reqs="<?=(count($req_ids) > 0 ? implode(', ', array_keys($req_ids)) : '');?>" style="display: none;"><?=$descr;?></div><?
        }
    }else{
        foreach ($offerRequestApply as $cur_data) {
            if ($cur_data['OFFER']['ID'] == $cur_id) {
                $descr = "{$counter_requests_data[$cur_id]['UF_DATE']}<br>"
                . "Вы сделали предложение {$counter_requests_data[$cur_id]['UF_VOLUME_OFFER']} т товара {$cur_data['OFFER']['CULTURE_NAME']}, со склада " . str_replace('"', '&quot;', $cur_data['OFFER']['WH_NAME']) . " по цене &quot;с места&quot;: {$counter_requests_data[$cur_id]['UF_FARMER_PRICE']} руб/т. <br>"
                . "Телефон для связи {$arAgentInfo['PROPERTY_PHONE_VALUE']}, {$arAgentInfo['USER']['NAME']}<br/>";

                if($iPartnerId == $counter_requests_data[$cur_id]['UF_BY_PARTNER_REAL']){
                    $descr .= '<span class="cancel_counter_offer_area"></span>';
                }else{
                    $descr .= '<span class="no_cancel_counter_offer_area"></span>';
                }

                break;
            }
        }

        ?><div class="send_counter_req_data" data-offer="<?=$cur_id;?>"  data-reqs="" style="display: none;"><?=$descr?></div><?
    }
}

//получение данных отправки встречных предложений
//а также получение данных запросов на товары
if(isset($arResult['ELEMENTS'])
    && count($arResult['ELEMENTS']) > 0
){
    //получение данных для отправки встречных предложений
    //получение лимитов для фильтров
    //получаем ID товаров без выбранных фильтров

    $regions_limits = array();
    $cultures_limits = array();
    $farmers_limits = array();
    $nds_limits = array();
    $clients_whids = array();
    $clients_farmers = array();
    $clients_cultures = array();
    $clients_nds = array();
    $clients = array();
    //получение данных запросов по товарам (в дальнейшем может понадобиться перенос на ajax)
    $req_ids = array();
    $list = lead::getLeadList(['UF_OFFER_ID' => $arResult['ELEMENTS']], ['UF_CSM_PRICE' => 'DESC']);
    if(count($list) > 0){
        foreach($list as $cur_data){
            $req_ids[$cur_data['UF_REQUEST_ID']] = array('type' => 'CPT', 'volume' => 0);
        }
        CModule::IncludeModule('iblock');
        $el_obj = new CIBlockElement;
        //получение объёмов запросов
        if(count($req_ids) > 0){
            $active_prop = rrsIblock::getPropListKey('client_request', 'ACTIVE', 'yes');
            $res = $el_obj->GetList(
                array('ID' => 'ASC'),
                array(
                    'IBLOCK_ID' => rrsIblock::getIBlockId('client_request'),
                    'ID' => array_keys($req_ids),
                    'ACTIVE' => 'Y',
                    'PROPERTY_ACTIVE' => $active_prop,
                    '>PROPERTY_REMAINS' => 0
                ),
                false,
                false,
                array('ID', 'PROPERTY_REMAINS', 'PROPERTY_DELIVERY.CODE')
            );
            while($data = $res->Fetch()){
                $req_ids[$data['ID']]['volume'] = $data['PROPERTY_REMAINS_VALUE'];
                if($data['PROPERTY_DELIVERY_CODE'] != 'Y'){
                    $req_ids[$data['ID']]['type'] = 'FCA';
                }
            }
        }
        //вывод данных
        foreach($list as $cur_data){
            if(isset($arResult['CACHED_WAREHOUSES_LIST'][$cur_data['UF_FARMER_WH_ID']])
                && isset($arResult['CACHED_CULTURES_LIST'][$cur_data['UF_CULTURE_ID']])
                && isset($req_ids[$cur_data['UF_REQUEST_ID']])
                && $req_ids[$cur_data['UF_REQUEST_ID']]['volume'] > 0
            ){
                $clients_farmers[$cur_data['UF_FARMER_ID']][$cur_data['UF_CLIENT_ID']] = 1;
                $clients_cultures[$cur_data['UF_CULTURE_ID']][$cur_data['UF_CLIENT_ID']] = 1;
                $clients_whids[$cur_data['UF_FARMER_WH_ID']][$cur_data['UF_CLIENT_ID']] = 1;
                $clients_nds[$cur_data['UF_NDS']][$cur_data['UF_CLIENT_ID']] = 1;
                $clients[$cur_data['UF_CLIENT_ID']] = 1;
                ?><div class="requests_list_data" style="display: none;"
                       data-offerid="<?=$cur_data['UF_OFFER_ID'];?>"
                       data-reqid="<?=$cur_data['UF_REQUEST_ID']?>"
                       data-dtype="<?=$req_ids[$cur_data['UF_REQUEST_ID']]['type'];?>"
                       data-volume="<?=$req_ids[$cur_data['UF_REQUEST_ID']]['volume'];?>"
                       data-route="<?=$cur_data['UF_ROUTE'];?>"
                       data-price="<?=(
                               //берем пересчитанную актуальную цену, если есть
                               isset($arReqsCsmCounted[$cur_data['UF_OFFER_ID']][$cur_data['UF_REQUEST_ID']])
                                   ? $arReqsCsmCounted[$cur_data['UF_OFFER_ID']][$cur_data['UF_REQUEST_ID']]
                                   : $cur_data['UF_CSM_PRICE']
                       );?>"
                       data-wh="<?=str_replace('"', '', $arResult['CACHED_WAREHOUSES_LIST'][$cur_data['UF_FARMER_WH_ID']]);?>"
                       data-cultureid="<?=$cur_data['UF_CULTURE_ID'];?>"
                       data-culture="<?=$arResult['CACHED_CULTURES_LIST'][$cur_data['UF_CULTURE_ID']]?>"></div><?
            }
        }
        if ((sizeof($clients)) && (is_array($clients))) {
            $users_limits = array();
            $arFilter = array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('client_profile'),
                'ACTIVE' => 'Y',
                'PROPERTY_USER' => array_keys($clients)
            );
            $el_obj = new CIBlockElement;
            $res = $el_obj->GetList(
                array('ID' => 'ASC'),
                $arFilter,
                false,
                false,
                array('ID', 'PROPERTY_USER', 'PROPERTY_COUNTER_REQUEST_LIMIT')
            );
            while ($arRow = $res->Fetch()) {
                $limit = $arRow['PROPERTY_COUNTER_REQUEST_LIMIT_VALUE'];
                if (empty($limit)) {
                    $limit = 0;
                }
                $users_limits[$arRow['PROPERTY_USER_VALUE']] = $limit;
            }
            $whids_limits = array();
            foreach ($clients_whids as $offer_id => $clients_vals) {
                $whids_limits[$offer_id] = 0;
                foreach ($clients_vals as $client_id => $val) {
                    if ($users_limits[$client_id] > 0) {
                        $whids_limits[$offer_id] = 1;
                        break;
                    }
                }
            }
            if ((sizeof($whids_limits)) && (is_array($whids_limits))) {
                //получаем регионы из складов
                $res = CIBlockElement::GetList(
                    array('ID' => 'ASC'),
                    array(
                        'IBLOCK_ID' => rrsIblock::getIBlockId('farmer_warehouse'),
                        'ACTIVE' => 'Y',
                        'ID' => array_keys($whids_limits),
                    ),
                    false,
                    false,
                    array('ID', 'PROPERTY_REGION')
                );
                while ($ob = $res->Fetch()) {
                    $regions_limits[$ob['PROPERTY_REGION_VALUE']] = 1;
                }
            }
            foreach ($clients_cultures as $offer_id => $clients_vals) {
                $cultures_limits[$offer_id] = 0;
                foreach ($clients_vals as $client_id => $val) {
                    if ($users_limits[$client_id] > 0) {
                        $cultures_limits[$offer_id] = 1;
                        break;
                    }
                }
            }
            foreach ($clients_farmers as $offer_id => $clients_vals) {
                $farmers_limits[$offer_id] = 0;
                foreach ($clients_vals as $client_id => $val) {
                    if ($users_limits[$client_id] > 0) {
                        $farmers_limits[$offer_id] = 1;
                        break;
                    }
                }
            }
            foreach ($clients_nds as $nds_id => $clients_vals) {
                if($nds_id=='yes')
                    $nds_id = 1;
                else
                    $nds_id = 2;
                $nds_limits[$nds_id] = 0;
                foreach ($clients_vals as $client_id => $val) {
                    if ($users_limits[$client_id] > 0) {
                        $nds_limits[$nds_id] = 1;
                        break;
                    }
                }
            }

            foreach ($regions_limits as $region_id => $limits) {?>
            <div class="region_limits" style="display: none;" data-regionid="<?= $region_id; ?>"
                 data-plimits="<?=$limits?>"></div><?
            }
            foreach ($cultures_limits as $culture_id => $limits) {?>
            <div class="cultures_limits" style="display: none;" data-cultureid="<?= $culture_id; ?>"
                 data-plimits="<?=$limits?>"></div><?
            }
            foreach ($farmers_limits as $farmer_id => $limits) {?>
            <div class="farmers_limits" style="display: none;" data-farmerid="<?= $farmer_id; ?>"
                 data-plimits="<?=$limits?>"></div><?
            }
            foreach ($nds_limits as $nds_id => $limits) {?>
            <div class="nds_limits" style="display: none;" data-ndsid="<?= $nds_id; ?>"
                 data-plimits="<?=$limits?>"></div><?
            }
        }
        ?>
        <script type="text/javascript">
            $(document).ready(function(){
                $('.list_page_rows.farmer_offer .line_area form').each(function(cInd, cObj){
                    var offer_input = $(cObj).find('input[name="offer"]');
                    //получение и вывод всех запросов по текущему товару
                    if(offer_input.length == 1){
                        var req_elems = $('.requests_list_data[data-offerid="' + offer_input.val() + '"]');
                        if(req_elems.length > 0) {
                            $(cObj).find('.prop_area.total').before('<div class="prop_area adress_val for_requests"><div class="adress">Запросы по данному товару:</div></div>');
                            var work_area = $(cObj).find('.prop_area.adress_val.for_requests');
                            req_elems.each(function (reqInd, reqObj) {
                                var wObj = $(reqObj);
                                var url_val = '/partner/farmer_request/?o=' + wObj.attr('data-offerid') + '&r=' + wObj.attr('data-reqid') + '&culture=' + wObj.attr('data-cultureid');
                                work_area.append('<div class="val_adress val_1 basis_price_table ' + (reqInd > 0 ? 'hidden' : '') + '"><div class="deal_link">' +
                                    '<b>' + wObj.attr('data-culture') + '</b><br>(' + wObj.attr('data-dtype') +
                                    (wObj.attr('data-dtype') == 'CPT' ? ', ' + number_format(wObj.attr('data-route'), 0, '.', ' ') + ' км, ' : ', ') +
                                    wObj.attr('data-wh') + ')</div>' +
                                    '<div class="road_len">' + number_format(wObj.attr('data-price'), 0, '.', ' ') + ' руб/т</div>\n' +
                                    '<div class="deal_volume">' + number_format(wObj.attr('data-volume'), 0, '.', ' ') + ' т.</div><div class="clear"></div></div>');
                            });

                            if(req_elems.length > 1) {
                                work_area.append('<div class="show-more-requests">Другие запросы по товару</div>' +
                                    '<div class="hide-more-request">Скрыть</div>');
                            }
                        }
                    }
                });
                if($('.region_limits').length > 0){
                    $('.region_limits').each(function(cInd, cObj){
                        if($(this).attr('data-plimits')>0){
                            $('select[name="region_id"]').find('option[value="'+$(this).attr('data-regionid')+'"]').attr('data-plimit','1');
                        }
                    });
                }
                if($('.cultures_limits').length > 0){
                    $('.cultures_limits').each(function(cInd, cObj){
                        if($(this).attr('data-plimits')>0){
                            $('select[name="culture"]').find('option[value="'+$(this).attr('data-cultureid')+'"]').attr('data-plimit','1');
                        }
                    });
                }
                if($('.farmers_limits').length > 0){
                    $('.farmers_limits').each(function(cInd, cObj){
                        if($(this).attr('data-plimits')>0){
                            $('select[name="farmer_id[]"]').find('option[value="'+$(this).attr('data-farmerid')+'"]').attr('data-plimit','1');
                        }
                    });
                }
                if($('.nds_limits').length > 0){
                    $('.nds_limits').each(function(cInd, cObj){
                        if($(this).attr('data-plimits')>0){
                            $('select[name="type_nds"]').find('option[value="'+$(this).attr('data-ndsid')+'"]').attr('data-plimit','1');
                        }
                    });
                }
            });
        </script>
        <?
    }
}

//проверяем данные ограничений пользователей

$agentObj = new agent();
$uids = $agentObj->getFarmersForSelect($arParams['UID']);
if(count($uids) > 0){
    $agent_req_limits = $agentObj->checkAvailableOfferLimit(array_keys($uids));

    if($agent_req_limits['REMAINS'] > 0){
        //активируем кнопки добавления
        ?>
        <script type="text/javascript">
            $(document).ready(function(){
                $('.additional_href_data, .add_blue_button').addClass('active');
            });
        </script>
        <?
    }else{
        //показываем сообщение, что нельзя добавлять запросы
        ?>
        <script type="text/javascript">
            $(document).ready(function(){
                $('.add_limit_end').addClass('active');
            });
        </script>
        <?
    }
}

//дополняем данные константами
?>
    <script type="text/javascript">
        <?
        //дополняем данные коэффициентом партнерских услуг
        $nCoef = rrsIblock::getConst('partner_pair_price');
        if($nCoef){?>
        var partner_price_coef = parseInt('<?=$nCoef;?>');
        <?
        }
        //дополняем данные константами для рассчета стоимости агенстких услуг
        $nCoef = rrsIblock::getConst('counter_option_contract');
        if($nCoef){?>
        var counter_option_contract = parseInt('<?=$nCoef;?>');
        <?
        }
        $nCoef = rrsIblock::getConst('counter_option_lab');
        if($nCoef){?>
        var counter_option_lab = parseInt('<?=$nCoef;?>');
        <?
        }
        $nCoef = rrsIblock::getConst('counter_option_support');
        if($nCoef){?>
        var counter_option_support = parseInt('<?=$nCoef;?>');
        <?
        }
        ?>
    </script>
<?