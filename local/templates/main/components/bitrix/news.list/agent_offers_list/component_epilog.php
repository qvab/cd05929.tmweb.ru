<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();?>
<?
if ($_REQUEST['offer'] > 0) {
    farmer::deactivateOffer($_REQUEST['offer']);
}

//проверка необходимости переадресации на другую страницу
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


global $USER;
$iPartnerId = $USER->GetID();
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
            //проверку прав убираем, т.к. текущий пользователь агент
            $farmer_id = farmer::getOfferFarmer($_POST['offer_id']);
            /*$user_rights = farmer::checkRights('counter_request', $iPartnerId);
            if(isset($user_rights['REQUEST_RIGHT'])
                && $user_rights['REQUEST_RIGHT'] == 'Y'
            ) {*/
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
                $partner_quality_approved = $addit_partner_id = $addit_partner_price = $addit_is_add_cert = $addit_is_bill_of_health = $addit_is_vet_cert = $addit_is_quality_cert = $addit_is_transfer = $addit_is_secure_deal = $addit_is_agent_support = 0;
                $partner_quality_approved_d = '';

                if(isset($_POST['coffer_type'])
                    && $_POST['coffer_type'] == 'p'
                ){
                    $coffer_type = 'p';

                    if(!empty($_POST['partner_quality_approved'])){
                        $partner_quality_approved = 1;

                        if(!empty($_POST['partner_quality_approved_d'])){
                            $partner_quality_approved_d = $_POST['partner_quality_approved_d'];
                        }
                    }
                    $addit_partner_price = partner::countCounterOfferPartnerPrice($_POST['price'], $_POST['volume'], ($partner_quality_approved == 1), !empty($_POST['addit_is_agent_support']), $counter_option_contract, $counter_option_lab, $counter_option_support);
                    //$addit_partner_price = (!empty($_POST['addit_partner_price']) ? str_replace(' ', '', $_POST['addit_partner_price']) : 0);
                    $addit_is_add_cert = (!empty($_POST['addit_is_add_cert']) || $partner_quality_approved ? 1 : 0);
                    $addit_is_bill_of_health = (!empty($_POST['addit_is_bill_of_health']) ? 1 : 0);
                    $addit_is_vet_cert = (!empty($_POST['addit_is_vet_cert']) ? 1 : 0);
                    $addit_is_quality_cert = (!empty($_POST['addit_is_quality_cert']) ? 1 : 0);
                    $addit_is_transfer = (!empty($_POST['addit_is_transfer']) ? 1 : 0);
                    $addit_is_secure_deal = (!empty($_POST['addit_is_secure_deal']) ? 1 : 0);
                    $addit_is_agent_support = (!empty($_POST['addit_is_agent_support']) ? 1 : 0);
                    $addit_partner_id = $iPartnerId;
                }

                $arRequestData = array();
                $arRequestIds = array();
                foreach($offerRequestApply as $cur_data){
                    $arRequestIds[$cur_data['REQUEST']['ID']] = true;
                }
                if(count($arRequestIds) > 0){
                    $arRequestData = client::getRequestListByIDs(array_keys($arRequestIds));
                }
                $arOfferData = array();
                if(!empty($_POST['offer_id'])){
                    $arOfferData = farmer::getOfferById($_POST['offer_id']);
                }

                $real_partner_id = $iPartnerId;
                foreach ($offerRequestApply as $cur_data) {
                    $sendData = array(
                        'offer_id' => $_POST['offer_id'],
                        'selected_requests' => $cur_data['REQUEST']['ID'],
                        'price' => $_POST['price'],
                        'volume' => $_POST['volume'],
                        'type' => 'c', //"counter"
                        'farmer_id' => $farmer_id,
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
                        'real_partner_id' => $real_partner_id,
                    );
                    farmer::addCounterRequest($sendData, 'farmer', $arOfferData, (isset($arRequestData[$cur_data['REQUEST']['ID']]) ? array($arRequestData[$cur_data['REQUEST']['ID']]) : array()));

                    $my_c++;
                }

                if($my_c > 0)
                {
                    $partnerData = partner::getPartnerInfo($iPartnerId, true);
                    //генерируем текст для организатора и кнопку отмены предложения
?><div class="prop_area counter_offer_area adress_val">
    <div class="success_message">Предложения отправлены</div>
	<span class="copy-text"><?=date('d.m.Y H:i');?><br>Вы сделали предложение <?=$_POST['volume'];?> т товара <?=$arOfferData['CULTURE_NAME'];?>, со склада <?=$arOfferData['WH_NAME'];?> по цене "с места": <?=$_POST['price'];?> руб/т.<br><?
    if(!empty($partnerData['PHONE'])){?>Телефон для связи <?=$partnerData['PHONE'];?><?if($partnerData['NAME']){?>, <?=$partnerData['NAME'];?><?}?><br><?}?><span class="cancel_counter_offer_area"></span></span>
    <div class="js-copy copy-left html_val">Скопировать</div>
    <div class="cancel_counter_offer_area"><a class="submit-btn" href="javascript: void(0);">Отменить предложение</a></div>
</div><?
                    exit;
                }else{
                    //ошибка не добавлено ни одно ВП
                    ob_start();
                    echo "==========================\n", date('r'), "\n";
                    echo "ошибка не добавлено ни одно ВП\n";
                    echo $iPartnerId, "\n";
                    p($_POST);
                    echo "\n\n";
                    file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/err_log.txt', ob_get_clean(), FILE_APPEND);
                    mail("somefor@yandex.ru", "Ошибка на агрохелпере", "ошибка не добавлено ни одно ВП (организатор)");
                }
            /*}else{
                //ошибка не хватает прав на добавление ВП
                ob_start();
                echo "==========================\n", date('r'), "\n";
                echo "ошибка не хватает прав на добавление ВП\n";
                echo $iPartnerId, "\n";
                p($_POST);
                var_dump($user_rights);
                echo "\n\n";
                file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/err_log.txt', ob_get_clean(), FILE_APPEND);
                //mail("somefor@yandex.ru", "Ошибка на агрохелпере", "ошибка не хватает прав на добавление ВП");
            }*/
        }else{
            //ошибка ранее были отправлены ВП по данному предложению
            ob_start();
            echo "==========================\n", date('r'), "\n";
            echo "ошибка ранее были отправлены ВП по данному предложению\n";
            echo $iPartnerId, "\n";
            p($_POST);
            p($counter_request_data);
            echo "\n\n";
            file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/err_log.txt', ob_get_clean(), FILE_APPEND);
            mail("somefor@yandex.ru", "Ошибка на агрохелпере", "ошибка ранее были отправлены ВП по данному предложению (организатор)");
        }
    }else{
        //ошибка в передаваемых данных offer_id, volume или price
        ob_start();
        echo "==========================\n", date('r'), "\n";
        echo "ошибка в передаваемых данных offer_id, volume или price\n";
        echo $iPartnerId, "\n";
        p($_POST);
        echo "\n\n";
        file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/err_log.txt', ob_get_clean(), FILE_APPEND);
        mail("somefor@yandex.ru", "Ошибка на агрохелпере", "ошибка в передаваемых данных offer_id, volume или price (организатор)");
    }

    echo 1; //ошибка
    exit;
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
//получаем максимальную (+10% к максимальной цене) и минимальную цены (-10% от минимальной цены) для ограничений, а также цену для установления по умолчанию (берется максимальная из имеющихся цен в $offerRequestApply)
$temp_val = 0;
$min_val = 0;
$max_val = 0;
$set_val = 0;
$req_ids = array();

$arAgentInfo = partner::getProfile($iPartnerId, true);
$arReqsCsmCounted = array();
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

        $rec_text = deal::getRecommendedPriceText($cur_id, true, $nds_val);

        //выводим данные в форму (эти данные встаятся в форму в script.js)
        if ($set_val > 0) {
            echo '<div class="send_counter_req_data" data-offer="' . $cur_id . '" data-setval="' . $set_val . '" data-minval="' . $min_val . '" data-maxval="' . $max_val . '" data-rec="' . $rec_text . '" data-reqs="' . implode(',', array_keys($req_ids)) . '" style="display: none;"></div>';
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

        ?><div class="send_counter_req_data" style="display: none;" data-offer="<?=$cur_id;?>"  data-reqs="" style="display: none;"><?=$descr;?></div><?
    }
}

//получение данных отправки встречных предложений
//а также получение данных запросов на товары
global $USER;
$agentObj = new agent();
$uids = $agentObj->getFarmersForSelect($USER->GetID());
$uids = array_keys($uids);
$arrElements = farmer::getFarmersOffers($uids);

if(count($arrElements) > 0){

    $counter_requests_data = farmer::getCounterRequestsData($arrElements);
    $arrFarmerIds = array();
    foreach($counter_requests_data as $arData){
        if(!empty($arData['UF_FARMER_ID'])){
            $arrFarmerIds[$arData['UF_FARMER_ID']] = true;
        }
    }

    //получение данных для отправки встречных предложений
    if(count($arrFarmerIds) > 0) {
        //проверка прав на отправление ВП
        $regions_limits = array();
        $regions_acontr = array();
        $cultures_limits = array();
        $cultures_acontr = array();
        $farmers_limits = array();
        $farmers_acontr = array();
        $nds_limits = array();
        $nds_acontr = array();
        $clients_whids = array();
        $clients_farmers = array();
        $clients_cultures = array();
        $clients_nds = array();
        $clients = array();
        //получение данных запросов по товарам (в дальнейшем может понадобиться перенос на ajax)
        $req_ids = array();
        $list = lead::getLeadList(['UF_OFFER_ID' => $arrElements], ['UF_CSM_PRICE' => 'DESC']);
        $arrCulturesList = culture::getNames();
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
            //вывод данных
            $users_ids = array();
            $users_contracts = array();
            foreach ($list as $cur_data) {
                if (
                    isset($arrCulturesList[$cur_data['UF_CULTURE_ID']])
                    && isset($req_ids[$cur_data['UF_REQUEST_ID']])
                    && $req_ids[$cur_data['UF_REQUEST_ID']]['volume'] > 0
                ) {
                    $users_ids[$cur_data['UF_CLIENT_ID']] = true;
                    $clients_farmers[$cur_data['UF_FARMER_ID']][$cur_data['UF_CLIENT_ID']] = 1;
                    $clients_cultures[$cur_data['UF_CULTURE_ID']][$cur_data['UF_CLIENT_ID']] = 1;
                    $clients_whids[$cur_data['UF_FARMER_WH_ID']][$cur_data['UF_CLIENT_ID']] = 1;
                    $clients_nds[$cur_data['UF_NDS']][$cur_data['UF_CLIENT_ID']] = 1;
                    $clients[$cur_data['UF_CLIENT_ID']] = 1;
                    ?>
                    <div class="requests_list_data" style="display: none;"
                         data-offerid="<?= $cur_data['UF_OFFER_ID']; ?>"
                         data-reqid="<?= $cur_data['UF_REQUEST_ID'] ?>"
                         data-dtype="<?= $req_ids[$cur_data['UF_REQUEST_ID']]['type']; ?>"
                         data-volume="<?= $req_ids[$cur_data['UF_REQUEST_ID']]['volume']; ?>"
                         data-route="<?= $cur_data['UF_ROUTE']; ?>"
                         data-price="<?= (
                             //берем пересчитанную актуальную цену, если есть
                         isset($arReqsCsmCounted[$cur_data['UF_OFFER_ID']][$cur_data['UF_REQUEST_ID']])
                             ? $arReqsCsmCounted[$cur_data['UF_OFFER_ID']][$cur_data['UF_REQUEST_ID']]
                             : $cur_data['UF_CSM_PRICE']
                         ); ?>"
                         data-wh="<?=(!empty($arResult['CACHED_WAREHOUSES_LIST'][$cur_data['UF_FARMER_WH_ID']]) ? str_replace('"', '', $arResult['CACHED_WAREHOUSES_LIST'][$cur_data['UF_FARMER_WH_ID']]) : '');?>"
                         data-cultureid="<?= $cur_data['UF_CULTURE_ID']; ?>"
                         data-culture="<?= $arrCulturesList[$cur_data['UF_CULTURE_ID']] ?>"><?=(!empty($arResult['CACHED_WAREHOUSES_LIST'][$cur_data['UF_FARMER_WH_ID']]) ? str_replace('"', '', $arResult['CACHED_WAREHOUSES_LIST'][$cur_data['UF_FARMER_WH_ID']]) : '');?></div><?
                }
            }
            if (count($users_ids) > 0) {
                $users_contracts = partner::getUsersContractsForPartner(array_keys($users_ids));
            }
            if ((sizeof($clients)) && (is_array($clients))) {
                //поиск П и А меток
                $found_p = false;
                $found_a = false;

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
                $whids_acontr = array();
                foreach ($clients_whids as $offer_id => $clients_vals) {
                    $whids_limits[$offer_id] = 0;
                    $whids_acontr[$offer_id] = 0;
                    $found_p = false;
                    $found_a = false;
                    foreach ($clients_vals as $client_id => $val) {
                        if ($users_limits[$client_id] > 0) {
                            $whids_limits[$offer_id] = 1;
                            $found_p = true;
                        }
                        if (isset($users_contracts[$client_id])) {
                            $whids_acontr[$offer_id] = 1;
                            $found_a = true;
                        }

                        if ($found_p
                            && $found_a
                        ) {
                            break;
                        }
                    }
                }
                if ((sizeof($whids_limits)) && (is_array($whids_limits))
                    || (sizeof($whids_acontr)) && (is_array($whids_acontr))
                ) {
                    //получаем регионы из складов
                    $res = CIBlockElement::GetList(
                        array('ID' => 'ASC'),
                        array(
                            'IBLOCK_ID' => rrsIblock::getIBlockId('farmer_warehouse'),
                            'ACTIVE' => 'Y',
                            'ID' => array_merge(array_keys($whids_limits), array_keys($whids_acontr)),
                        ),
                        false,
                        false,
                        array('ID', 'PROPERTY_REGION')
                    );
                    while ($ob = $res->Fetch()) {
                        if (!empty($whids_limits[$ob['ID']])) {
                            $regions_limits[$ob['PROPERTY_REGION_VALUE']] = 1;
                        }
                        if (!empty($whids_acontr[$ob['ID']])) {
                            $regions_acontr[$ob['PROPERTY_REGION_VALUE']] = 1;
                        }
                    }
                }
                foreach ($clients_cultures as $offer_id => $clients_vals) {
                    $cultures_limits[$offer_id] = 0;
                    $cultures_acontr[$offer_id] = 0;
                    $found_p = false;
                    $found_a = false;

                    foreach ($clients_vals as $client_id => $val) {
                        if ($users_limits[$client_id] > 0) {
                            $cultures_limits[$offer_id] = 1;
                            $found_p = true;
                        }
                        if (isset($users_contracts[$client_id])) {
                            $cultures_acontr[$offer_id] = 1;
                            $found_a = true;
                        }

                        if ($found_p
                            && $found_a
                        ) {
                            break;
                        }
                    }
                }
                foreach ($clients_farmers as $offer_id => $clients_vals) {
                    $farmers_limits[$offer_id] = 0;
                    $farmers_acontr[$offer_id] = 0;
                    $found_p = false;
                    $found_a = false;

                    foreach ($clients_vals as $client_id => $val) {
                        if ($users_limits[$client_id] > 0) {
                            $farmers_limits[$offer_id] = 1;
                            $found_p = true;
                        }
                        if (isset($users_contracts[$client_id])) {
                            $farmers_acontr[$offer_id] = 1;
                            $found_a = true;
                        }

                        if ($found_p
                            && $found_a
                        ) {
                            break;
                        }
                    }
                }
                foreach ($clients_nds as $nds_id => $clients_vals) {
                    if ($nds_id == 'yes')
                        $nds_id = 1;
                    else
                        $nds_id = 2;
                    $nds_limits[$nds_id] = 0;
                    $nds_acontr[$nds_id] = 0;
                    $found_p = false;
                    $found_a = false;
                    foreach ($clients_vals as $client_id => $val) {
                        if ($users_limits[$client_id] > 0) {
                            $nds_limits[$nds_id] = 1;
                            $found_p = true;
                        }
                        if (isset($users_contracts[$client_id])) {
                            $nds_acontr[$nds_id] = 1;
                            $found_a = true;
                        }

                        if ($found_p
                            && $found_a
                        ) {
                            break;
                        }
                    }
                }

                foreach ($regions_limits as $region_id => $limits) { ?>
                <div class="region_limits" style="display: none;" data-regionid="<?= $region_id; ?>"
                     data-plimits="<?= $limits ?>"></div><?
                }
                foreach ($regions_acontr as $region_id => $is_contract) { ?>
                <div class="region_acontracts" style="display: none;" data-regionid="<?= $region_id; ?>"
                     data-pcontract="<?= $is_contract ?>"></div><?
                }
                foreach ($cultures_limits as $culture_id => $limits) { ?>
                <div class="cultures_limits" style="display: none;" data-cultureid="<?= $culture_id; ?>"
                     data-plimits="<?= $limits ?>"></div><?
                }
                foreach ($cultures_acontr as $culture_id => $is_contract) { ?>
                <div class="cultures_acontracts" style="display: none;" data-cultureid="<?= $culture_id; ?>"
                     data-pcontract="<?= $is_contract ?>"></div><?
                }
                foreach ($farmers_limits as $farmer_id => $limits) { ?>
                <div class="farmers_limits" style="display: none;" data-farmerid="<?= $farmer_id; ?>"
                     data-plimits="<?= $limits ?>"></div><?
                }
                foreach ($farmers_acontr as $farmer_id => $is_contract) { ?>
                <div class="farmers_acontracts" style="display: none;" data-farmerid="<?= $farmer_id; ?>"
                     data-pcontract="<?= $is_contract ?>"></div><?
                }
                foreach ($nds_limits as $nds_id => $limits) { ?>
                <div class="nds_limits" style="display: none;" data-ndsid="<?= $nds_id; ?>"
                     data-plimits="<?= $limits ?>"></div><?
                }
                foreach ($nds_acontr as $nds_id => $is_contract) { ?>
                <div class="nds_acontracts" style="display: none;" data-ndsid="<?= $nds_id; ?>"
                     data-pcontract="<?= $is_contract ?>"></div><?
                }
            }
            ?>
            <script type="text/javascript">
                $(document).ready(function () {
                    $('.list_page_rows.farmer_offer .line_area form').each(function (cInd, cObj) {
                        var offer_input = $(cObj).find('input[name="offer"]');
                        //получение и вывод всех запросов по текущему товару
                        if (offer_input.length == 1) {
                            var req_elems = $('.requests_list_data[data-offerid="' + offer_input.val() + '"]');
                            if (req_elems.length > 0) {
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

                                if (req_elems.length > 1) {
                                    work_area.append('<div class="show-more-requests">Другие запросы по товару</div>' +
                                        '<div class="hide-more-request">Скрыть</div>');
                                }
                            }
                        }
                    });

                    //установка метки П
                    if ($('.region_limits').length > 0) {
                        $('.region_limits').each(function (cInd, cObj) {
                            if ($(this).attr('data-plimits') > 0) {
                                $('select[name="region_id"]').find('option[value="' + $(this).attr('data-regionid') + '"]').attr('data-plimit', '1');
                            }
                        });
                    }
                    if ($('.cultures_limits').length > 0) {
                        $('.cultures_limits').each(function (cInd, cObj) {
                            if ($(this).attr('data-plimits') > 0) {
                                $('select[name="culture"]').find('option[value="' + $(this).attr('data-cultureid') + '"]').attr('data-plimit', '1');
                            }
                        });
                    }
                    if ($('.farmers_limits').length > 0) {
                        $('.farmers_limits').each(function (cInd, cObj) {
                            if ($(this).attr('data-plimits') > 0) {
                                $('select[name="farmer_id[]"]').find('option[value="' + $(this).attr('data-farmerid') + '"]').attr('data-plimit', '1');
                            }
                        });
                    }
                    if ($('.nds_limits').length > 0) {
                        $('.nds_limits').each(function (cInd, cObj) {
                            if ($(this).attr('data-plimits') > 0) {
                                $('select[name="type_nds"]').find('option[value="' + $(this).attr('data-ndsid') + '"]').attr('data-plimit', '1');
                            }
                        });
                    }

                    //установка метки А
                    if ($('.region_acontracts').length > 0) {
                        $('.region_acontracts').each(function (cInd, cObj) {
                            if ($(this).attr('data-pcontract') > 0) {
                                $('select[name="region_id"]').find('option[value="' + $(this).attr('data-regionid') + '"]').attr('data-pcontract', '1');
                            }
                        });
                    }
                    if ($('.cultures_acontracts').length > 0) {
                        $('.cultures_acontracts').each(function (cInd, cObj) {
                            if ($(this).attr('data-pcontract') > 0) {
                                $('select[name="culture"]').find('option[value="' + $(this).attr('data-cultureid') + '"]').attr('data-pcontract', '1');
                            }
                        });
                    }
                    if ($('.farmers_acontracts').length > 0) {
                        $('.farmers_acontracts').each(function (cInd, cObj) {
                            if ($(this).attr('data-pcontract') > 0) {
                                $('select[name="farmer_id[]"]').find('option[value="' + $(this).attr('data-farmerid') + '"]').attr('data-pcontract', '1');
                            }
                        });
                    }
                    if ($('.nds_acontracts').length > 0) {
                        $('.nds_acontracts').each(function (cInd, cObj) {
                            if ($(this).attr('data-pcontract') > 0) {
                                $('select[name="type_nds"]').find('option[value="' + $(this).attr('data-ndsid') + '"]').attr('data-pcontract', '1');
                            }
                        });
                    }
                });
            </script>
            <?
        }
    }
}

//проверяем данные ограничений пользователей
if(count($uids) > 0){
    $agent_req_limits = $agentObj->checkAvailableOfferLimit($uids);

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