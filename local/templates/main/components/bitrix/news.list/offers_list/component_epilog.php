<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();?>
<?
if ($_REQUEST['offer'] > 0) {
    farmer::deactivateOffer($_REQUEST['offer']);
}

//отправка встречных предложений из списка (только для активных)
if(isset($_POST['send_counter_offer_ajax'])
    && $_POST['send_counter_offer_ajax'] == 'y'
    && isset($_POST['offer_id'])
    && isset($_POST['volume'])
    && isset($_POST['price'])
    && (
        !isset($_REQUEST['status'])
        || $_REQUEST['status'] != 'no'
    )
){
    //в любом случае обрабатываем ajax запрос
    $GLOBALS['APPLICATION']->RestartBuffer();

    $counter_option_contract = rrsIblock::getConst('counter_option_contract');
    $counter_option_lab = rrsIblock::getConst('counter_option_lab');
    $counter_option_support = rrsIblock::getConst('counter_option_support');

    if(is_numeric($_POST['offer_id'])
        && is_numeric($_POST['volume'])
        && is_numeric($_POST['price'])
    ){
        //проверяем не было ли ранее отправлено встречных предложений по данному предложению
        $counter_request_data = farmer::getCounterRequestsData(array($_POST['offer_id']));
        if(!isset($counter_request_data[$_POST['offer_id']]['UF_VOLUME_REMAINS'])
            || $counter_request_data[$_POST['offer_id']]['UF_VOLUME_REMAINS'] == 0
        ){
            //если есть ВП с нулевым объемом, то сначала удаляем их
            if(isset($counter_request_data[$_POST['offer_id']]['UF_VOLUME_REMAINS'])
                && $counter_request_data[$_POST['offer_id']]['UF_VOLUME_REMAINS'] == 0
            ){
                farmer::removeCountersByOfferID($_POST['offer_id']);
            }

            //собираем все подходящие запросы для отправки ВП
            $arLeads = lead::getLeadList(array('UF_OFFER_ID' => $_POST['offer_id']), ['UF_CSM_PRICE' => 'DESC']);
            $offerRequestApply = lead::createLeadList($arLeads);
            $my_c = 0;
            $sendData = array();
            global $USER;
            $user_rights = farmer::checkRights('counter_request', $USER->GetID());
//            if(isset($user_rights['REQUEST_RIGHT'])
//                && $user_rights['REQUEST_RIGHT'] == 'Y'
//            ) {
                $delivery_type = 'exw';

                if(isset($_POST['can_deliver'])
                    && $_POST['can_deliver'] == 1
                ){
                    $delivery_type = 'cpt';
                }elseif(isset($_POST['lab_trust'])
                    && $_POST['lab_trust'] == 1
                ){
                    $delivery_type = 'fca';
                }

                $coffer_type = 'c';
                $_POST['coffer_type'] = 'p';//всегда отправляем агентское предложение
                $arOfferData = farmer::getOfferById($_POST['offer_id']);
                $partner_quality_approved = $addit_partner_id = $addit_partner_price = $addit_is_add_cert = $addit_is_bill_of_health = $addit_is_vet_cert = $addit_is_quality_cert = $addit_is_transfer = $addit_is_secure_deal = $addit_is_agent_support = 0;
                $partner_quality_approved_d = '';
                if(isset($_POST['coffer_type'])
                    && $_POST['coffer_type'] == 'p'
                ){
                    $coffer_type = 'p';
                    $addit_partner_price = partner::countCounterOfferPartnerPrice($_POST['price'], $_POST['volume'], ($arOfferData['Q_APPROVED'] == 1), false, $counter_option_contract, $counter_option_lab, $counter_option_support);
                    //$addit_partner_price = (!empty($_POST['addit_partner_price']) ? str_replace(' ', '', $_POST['addit_partner_price']) : 0);
                    $addit_is_add_cert = (!empty($_POST['addit_is_add_cert']) || $arOfferData['Q_APPROVED'] == 1 ? 1 : 0);
                    $addit_is_bill_of_health = (!empty($_POST['addit_is_bill_of_health']) ? 1 : 0);
                    $addit_is_vet_cert = (!empty($_POST['addit_is_vet_cert']) ? 1 : 0);
                    $addit_is_quality_cert = (!empty($_POST['addit_is_quality_cert']) ? 1 : 0);
                    $addit_is_transfer = (!empty($_POST['addit_is_transfer']) ? 1 : 0);
                    $addit_is_secure_deal = (!empty($_POST['addit_is_secure_deal']) ? 1 : 0);
                    $addit_is_agent_support = (!empty($_POST['addit_is_agent_support']) ? 1 : 0);

                    $arTemp = farmer::getLinkedPartnerList($USER->GetID(), true);
                    $addit_partner_id = reset($arTemp);
                }

                if($arOfferData['Q_APPROVED'] == 1){
                    $partner_quality_approved = 1;

                    if(!empty($arOfferData['Q_APPROVED_DATA'])){
                        $partner_quality_approved_d = $arOfferData['Q_APPROVED_DATA'];
                    }
                }

                foreach ($offerRequestApply as $cur_data) {
                    $sendData = array(
                        'offer_id' => $_POST['offer_id'],
                        'selected_requests' => $cur_data['REQUEST']['ID'],
                        'price' => $_POST['price'],
                        'volume' => $_POST['volume'],
                        'type' => 'c', //"counter"
                        'farmer_id' => $USER->GetID(),
                        'delivery' => $delivery_type,
                        'coffer_type' => $coffer_type,
                        'addit_partner_price' => $addit_partner_price,
                        'addit_is_add_cert' => $addit_is_add_cert,
                        'addit_is_bill_of_health' => $addit_is_bill_of_health,
                        'addit_is_vet_cert' => $addit_is_vet_cert,
                        'addit_is_quality_cert' => $addit_is_quality_cert,
                        'addit_is_transfer' => $addit_is_transfer,
                        'addit_is_secure_deal' => $addit_is_secure_deal,
                        'addit_is_agent_support' => $addit_is_agent_support,
                        'addit_partner_id' => $addit_partner_id,
                        'partner_quality_approved' => $partner_quality_approved,
                        'partner_quality_approved_d' => $partner_quality_approved_d,
                    );
                    farmer::addCounterRequest($sendData);

                    $my_c++;
                }

                if($my_c > 0)
                {
                    echo 1;
                }else{
                    //ошибка не добавлено ни одно ВП
                    ob_start();
                    echo "==========================\n", date('r'), "\n";
                    echo "ошибка не добавлено ни одно ВП\n";
                    global $USER;
                    echo $USER->GetID(), "\n";
                    p($_POST);
                    echo "\n\n";
                    file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/err_log.txt', ob_get_clean(), FILE_APPEND);
                    mail("somefor@yandex.ru", "Ошибка на агрохелпере", "ошибка не добавлено ни одно ВП");
                }
//            }else{
//                //ошибка не хватает прав на добавление ВП
//                ob_start();
//                echo "==========================\n", date('r'), "\n";
//                echo "ошибка не хватает прав на добавление ВП\n";
//                global $USER;
//                echo $USER->GetID(), "\n";
//                p($_POST);
//                var_dump($user_rights);
//                echo "\n\n";
//                file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/err_log.txt', ob_get_clean(), FILE_APPEND);
//                mail("somefor@yandex.ru", "Ошибка на агрохелпере", "ошибка не хватает прав на добавление ВП");
//            }
        }else{
            //ошибка ранее были отправлены ВП по данному предложению
            ob_start();
            echo "==========================\n", date('r'), "\n";
            echo "ошибка ранее были отправлены ВП по данному предложению\n";
            global $USER;
            echo $USER->GetID(), "\n";
            p($_POST);
            p($counter_request_data);
            echo "\n\n";
            file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/err_log.txt', ob_get_clean(), FILE_APPEND);
            mail("somefor@yandex.ru", "Ошибка на агрохелпере", "ошибка ранее были отправлены ВП по данному предложению");
        }
    }else{
        //ошибка в передаваемых данных offer_id, volume или price
        ob_start();
        echo "==========================\n", date('r'), "\n";
        echo "ошибка в передаваемых данных offer_id, volume или price\n";
        global $USER;
        echo $USER->GetID(), "\n";
        p($_POST);
        echo "\n\n";
        file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/err_log.txt', ob_get_clean(), FILE_APPEND);
        mail("somefor@yandex.ru", "Ошибка на агрохелпере", "ошибка в передаваемых данных offer_id, volume или price");
    }

    exit;
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


//получение данных для отправки встречных предложений
//а также получение данных запросов по товарам
global $USER;
$arrElements = farmer::getFarmersOffers(array($USER->GetID()));
if(count($arrElements) > 0){
    //получение данных для отправки встречных предложений
    global $USER;
    //проверка прав на отправление ВП
    $user_rights = farmer::checkRights('counter_request', $USER->GetID());
    if(isset($user_rights['REQUEST_RIGHT'])
        && $user_rights['REQUEST_RIGHT'] == 'Y'
        && (
            !isset($_REQUEST['status'])
            || $_REQUEST['status'] != 'no'
        )
    ) {
        //получение наличия встречных запросов для товаров (в этом случае нельзя создавать повторные встречные предложения)
        $counter_requests_data = farmer::getCounterRequestsData($arrElements);
        //получение начальной цены и ограничений для ввода (работа с данными соответствий)
        //получаем соответствия для товаров
        $arLeads = lead::getLeadList(array('UF_OFFER_ID' => $arrElements), ['UF_CSM_PRICE' => 'DESC']);
        $offerRequestApply = lead::createLeadList($arLeads);
        $arReqsCsmCounted = array();
        //получаем максимальную (+10% к максимальной цене) и минимальную цены (-10% от минимальной цены) для ограничений, а также цену для установления по умолчанию (берется максимальная из имеющихся цен в $offerRequestApply)
        $temp_val = 0;
        $min_val = 0;
        $max_val = 0;
        $set_val = 0;
        $req_ids = array();
        $user_data = farmer::getProfile($USER->GetID());
        $nds_val = ($user_data['PROPERTY_NDS_CODE'] == 'Y' ? 'y' : 'n');
        foreach ($arrElements as $cur_id) {
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

                        if (isset($counter_requests_data[$cur_id]['UF_VOLUME_REMAINS'])
                            && intval($counter_requests_data[$cur_id]['UF_VOLUME_REMAINS']) == 0
                        ) {
                            $descr = $counter_requests_data[$cur_id]['UF_DATE'].' для культуры '.$cur_data['OFFER']['CULTURE_NAME'].' со склада '.str_replace('"', '&quot;', $cur_data['OFFER']['WH_NAME']).' направлено предложение с заявленным объёмом '.$counter_requests_data[$cur_id]['UF_VOLUME'].' т. и ценой '.$counter_requests_data[$cur_id]['UF_FARMER_PRICE'].' руб/т. Тип доставки установлен как '.$counter_requests_data[$cur_id]['UF_DELIVERY'].'.';
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
                        $descr = $counter_requests_data[$cur_id]['UF_DATE'].' для культуры \''.$cur_data['OFFER']['CULTURE_NAME'].'\' со склада \''.$cur_data['OFFER']['WH_NAME'].'\' направлено предложение с заявленным объёмом '.$counter_requests_data[$cur_id]['UF_VOLUME'].' т. и ценой '.$counter_requests_data[$cur_id]['UF_FARMER_PRICE'].' руб/т. Тип доставки установлен как '.$counter_requests_data[$cur_id]['UF_DELIVERY'].'.';
                        break;
                    }
                }

                ?><div class="send_counter_req_data" data-offer="<?=$cur_id;?>"  data-reqs="" style="display: none;"><?=$descr?></div><?
            }
        }
    }

    //получение лимитов для фильтров
    //получаем ID товаров без выбранных фильтров
    /*
    $cultures_limits = array();
    $whids_limits = array();
    $ALL_ELEMENTS = array();
    $el_obj = new CIBlockElement;
    $res = $el_obj->GetList(
        array('ID' => 'ASC'),
        array(
            'IBLOCK_ID' => rrsIblock::getIBlockId('farmer_offer'),
            'ACTIVE' => 'Y',
            'PROPERTY_FARMER' => $GLOBALS['arrFilter']['PROPERTY_FARMER'],
            'PROPERTY_ACTIVE' => $GLOBALS['arrFilter']['PROPERTY_ACTIVE'],
        ),
        false,
        false,
        array('ID')
    );
    while($data = $res->Fetch()){
        $ALL_ELEMENTS[] = $data['ID'];
    }
    if((sizeof($ALL_ELEMENTS))&&(is_array($ALL_ELEMENTS))) {
        $req_ids = array();
        $list = lead::getLeadList(array('UF_OFFER_ID' => $ALL_ELEMENTS));
        if (count($list) > 0) {
            foreach ($list as $cur_data) {
                $req_ids[$cur_data['UF_REQUEST_ID']] = array('type' => 'CPT', 'volume' => 0);
            }
            CModule::IncludeModule('iblock');
            $el_obj = new CIBlockElement;
            //получение объёмов запросов
            if (count($req_ids) > 0) {
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
                while ($data = $res->Fetch()) {
                    $req_ids[$data['ID']]['volume'] = $data['PROPERTY_REMAINS_VALUE'];
                    if ($data['PROPERTY_DELIVERY_CODE'] != 'Y') {
                        $req_ids[$data['ID']]['type'] = 'FCA';
                    }
                }
            }
            $clients_whids = array();
            $clients_cultures = array();
            $clients = array();
            //вывод данных
            foreach ($list as $cur_data) {
                if (isset($req_ids[$cur_data['UF_REQUEST_ID']])
                    && $req_ids[$cur_data['UF_REQUEST_ID']]['volume'] > 0
                ) {
                    $clients_cultures[$cur_data['UF_CULTURE_ID']][$cur_data['UF_CLIENT_ID']] = 1;
                    $clients_whids[$cur_data['UF_FARMER_WH_ID']][$cur_data['UF_CLIENT_ID']] = 1;
                    $clients[$cur_data['UF_CLIENT_ID']] = 1;
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
                foreach ($clients_whids as $offer_id => $clients_vals){
                    $whids_limits[$offer_id] = 0;
                    foreach ($clients_vals as $client_id=>$val){
                        if($users_limits[$client_id] > 0){
                            $whids_limits[$offer_id] = 1;
                            break;
                        }
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
                foreach ($whids_limits as $wh_id=>$limits){
                    ?><div class="wh_limits" style="display: none;" data-whid="<?=$wh_id;?>" data-plimits="<?=$limits?>"></div><?
                }
                foreach ($cultures_limits as $culture_id => $limits) {
                    ?>
                <div class="cultures_limits" style="display: none;" data-cultureid="<?= $culture_id; ?>"
                     data-plimits="<?= $limits ?>"></div><?
                }
            }
        }
    }
    */
    $cultures_limits = array();
    $whids_limits = array();
    $clients_whids = array();
    $clients_cultures = array();
    $clients = array();
    //получение данных запросов по товарам (в дальнейшем может понадобиться перенос на ajax)
    $req_ids = array();
    $list = lead::getLeadList(['UF_OFFER_ID' => $arrElements], ['UF_CSM_PRICE' => 'DESC']);
    $arrCulturesList = culture::getNames();
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
            if(
                isset($arrCulturesList[$cur_data['UF_CULTURE_ID']])
                && isset($req_ids[$cur_data['UF_REQUEST_ID']])
                && $req_ids[$cur_data['UF_REQUEST_ID']]['volume'] > 0
            ){
                $clients_cultures[$cur_data['UF_CULTURE_ID']][$cur_data['UF_CLIENT_ID']] = 1;
                $clients_whids[$cur_data['UF_FARMER_WH_ID']][$cur_data['UF_CLIENT_ID']] = 1;
                $clients[$cur_data['UF_CLIENT_ID']] = 1;
                ?><div class="requests_list_data" style="display: none;"
                       data-offerid="<?=$cur_data['UF_OFFER_ID'];?>"
                       data-reqid="<?=$cur_data['UF_REQUEST_ID']?>"
                       data-dtype="<?=$req_ids[$cur_data['UF_REQUEST_ID']]['type'];?>"
                       data-volume="<?=$req_ids[$cur_data['UF_REQUEST_ID']]['volume'];?>"
                       data-route="<?=$cur_data['UF_ROUTE'];?>"
                       data-price="<?=(
                               isset($arReqsCsmCounted[$cur_data['UF_OFFER_ID']][$cur_data['UF_REQUEST_ID']])
                                   ? $arReqsCsmCounted[$cur_data['UF_OFFER_ID']][$cur_data['UF_REQUEST_ID']]
                                   : $cur_data['UF_CSM_PRICE']
                       );?>"
                       data-wh="<?=(!empty($arResult['CACHED_WAREHOUSES_LIST'][$cur_data['UF_FARMER_WH_ID']]) ? str_replace('"', '', $arResult['CACHED_WAREHOUSES_LIST'][$cur_data['UF_FARMER_WH_ID']]) : '');?>"
                       data-whid="<?=$cur_data['UF_FARMER_WH_ID'];?>"
                       data-cultureid="<?=$cur_data['UF_CULTURE_ID'];?>"
                       data-culture="<?= $arrCulturesList[$cur_data['UF_CULTURE_ID']] ?>"><?=(!empty($arResult['CACHED_WAREHOUSES_LIST'][$cur_data['UF_FARMER_WH_ID']]) ? str_replace('"', '', $arResult['CACHED_WAREHOUSES_LIST'][$cur_data['UF_FARMER_WH_ID']]) : '');?></div><?
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
            foreach ($clients_whids as $offer_id => $clients_vals){
                $whids_limits[$offer_id] = 0;
                foreach ($clients_vals as $client_id=>$val){
                    if($users_limits[$client_id] > 0){
                        $whids_limits[$offer_id] = 1;
                        break;
                    }
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
            foreach ($whids_limits as $wh_id=>$limits){
                ?><div class="wh_limits" style="display: none;" data-whid="<?=$wh_id;?>" data-plimits="<?=$limits?>"></div><?
            }
            foreach ($cultures_limits as $culture_id => $limits) {
                ?>
            <div class="cultures_limits" style="display: none;" data-cultureid="<?= $culture_id; ?>"
                 data-plimits="<?= $limits ?>"></div><?
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
                            $(cObj).find('.js_anchor_form').after('<div class="prop_area adress_val for_requests"><div class="adress">Запросы по данному товару:</div></div>');
                            var work_area = $(cObj).find('.prop_area.adress_val.for_requests');
                            req_elems.each(function (reqInd, reqObj) {
                                var wObj = $(reqObj);
                                var url_val = '/farmer/request/?o=' + wObj.attr('data-offerid') + '&r=' + wObj.attr('data-reqid') + '&culture=' + wObj.attr('data-cultureid')+ '&wh=' + wObj.attr('data-whid');
                                work_area.append('<div class="val_adress val_1 basis_price_table ' + (reqInd > 0 ? 'hidden' : '') + '"><div class="deal_link">' +
                                    '<b>' + wObj.attr('data-culture') + '</b><br>(' + wObj.attr('data-wh') + ')</div>' +
                                    '<div class="road_len">' + number_format(wObj.attr('data-price'), 0, '.', ' ') + ' руб/т</div>\n' +
                                    '<div class="deal_volume">' + number_format(wObj.attr('data-volume'), 0, '.', ' ') + ' т.</div><div class="clear"></div></div>');
                            });

                            if(req_elems.length > 1) {
                                work_area.append('<div class="show-more-requests">Другие запросы по товару</div>' +
                                    '<div class="hide-more-request ">Скрыть</div>');
                            }
                        }
                    }
                });
                if($('.cultures_limits').length>0){
                    $('.cultures_limits').each(function(cInd, cObj){
                        if($(this).attr('data-plimits')>0){
                            $('select[name="culture_id"]').find('option[value="'+$(this).attr('data-cultureid')+'"]').attr('data-plimit','1');
                        }
                    });
                }
                if($('.wh_limits').length>0){
                    $('.wh_limits').each(function(cInd, cObj){
                        if($(this).attr('data-plimits')>0){
                            $('select[name="warehouse_id"]').find('option[value="'+$(this).attr('data-whid')+'"]').attr('data-plimit','1');
                        }
                    });
                }
            });
        </script>
        <?
    }
}

//открытие товара по get параметрам
if(isset($_GET['offer_id'])
    && is_numeric($_GET['offer_id'])
){
    ?>
    <script type="application/javascript">
        $(document).ready(function(){
            var open_offer_id = '<?=$_GET['offer_id']?>';
            var volume_val = parseInt('<?=(isset($_GET['vol']) && is_numeric($_GET['vol']) ? $_GET['vol'] : '0');?>');

            var inputOffer = $('.list_page_rows input[type="hidden"][name="offer"][value="' + open_offer_id + '"]');
            if(inputOffer.length == 1){
                var lineObj = inputOffer.parents('.line_area');
                lineObj.find('.line_inner').trigger('click');
                if(!isNaN(volume_val) && volume_val > 0){
                    var volumeObj = lineObj.find('input[name="volume"]');
                    if(volumeObj.length == 1){
                        volumeObj.val(volume_val);
                    }
                }
                var offsetObj = lineObj.offset();

                setTimeout(function(){
                    if($('#create' + open_offer_id, lineObj ).length > 0) {
                        offsetObj = $('#create' + open_offer_id, lineObj ).offset();
                    }
                    console.log(offsetObj.top);
                    $(document).scrollTop(offsetObj.top - 10);
                }, 500);
            }
        });
    </script>
    <?
}

//проверка наличия ограничения по созданию товара
$off_limit = farmer::checkAvailableOfferLimit($USER->GetID());
//получаем email пользователя
$email_val = $USER->GetEmail();
//если email из телефона, то не отображаем его
if(checkEmailFromPhone($email_val)){
    $email_val = '';
}
if($off_limit['REMAINS'] > 0){
    //активируем кнопки по созданию товара
    ?>
    <script type="text/javascript">
        $(document).ready(function(){
            $('.additional_href_data, .add_blue_button').addClass('active');
            var limObj = $('.add_limit_line.available');
            limObj.addClass('active').find('.val').text('<?=$off_limit['CNT'];?>');
            limObj.find('.remains').text('<?=$off_limit['REMAINS'];?>');
            limObj.find('a:first').on('click', function(){
                showOfferLimitsFeedbackForm('<?=$email_val;?>', '<?=rrsIblock::getConst('min_offer_limit_form');?>', '<?=rrsIblock::getConst('offer_limit_price');?>', '<?=rrsIblock::getConst('min_month_offer_limit');?>');
            });
        });
    </script>
    <?
}else{
    //показываем сообщение об исчерпании лимита
    ?>
    <script type="text/javascript">
        $(document).ready(function(){
            var limObj = $('.add_limit_line.ended');
            limObj.addClass('active').find('.val').text('<?=$off_limit['CNT'];?>');
            limObj.find('a:first').on('click', function(){
                showOfferLimitsFeedbackForm('<?=$email_val;?>', '<?=rrsIblock::getConst('min_offer_limit_form');?>', '<?=rrsIblock::getConst('offer_limit_price');?>', '<?=rrsIblock::getConst('min_month_offer_limit');?>');
            });
        });
    </script>
    <?
}
?>