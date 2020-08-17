<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
/** @var CBitrixComponent $this */
/** @var array $arParams */
/** @var array $arResult */
/** @var string $componentPath */
/** @var string $componentName */
/** @var string $componentTemplate */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

/** @global CIntranetToolbar $INTRANET_TOOLBAR */
global $INTRANET_TOOLBAR;

use Bitrix\Main\Context,
	Bitrix\Main\Type\DateTime,
	Bitrix\Main\Loader,
	Bitrix\Iblock;

CPageOption::SetOptionString("main", "nav_page_in_session", "N");

//Объекты
$oElement = new CIBlockElement();
$agentObj = new agent();
$dealObj = new deal();
//$arResult['USER_DEALS_RIGHTS'] = $dealObj->checkRights($arParams['FARMER_ID']);
if($arParams['TYPE'] != 'agent') {
    $arResult['USER_RIGHTS'] = farmer::checkRights('counter_request', $arParams['FARMER_ID']);
}

//создание встречного предложения типа "accepted"
if (!empty($_REQUEST['accept'])) {

    // Запуск транзакции
    $DB->StartTransaction();

    try {
        // Проверяем сессию
        if(!check_bitrix_sessid()) {
            throw new Exception('Ваша сессия истекла');
        }

        //Параметры
        $offer_id       = intval($_REQUEST['offer']);
        $request_id     = intval($_REQUEST['request']);
        $warehouse_id   = intval($_REQUEST['warehouse']);
        $volume         = trim($_REQUEST['volume']);
        if ($arParams['TYPE'] == 'farmer') {
            $offer_farmer_id = $arParams['FARMER_ID'];
        } else {
            $offer_farmer_id = $agentObj->getFarmerByOffer($offer_id);
        }

        if ($offer_farmer_id == 0) {
            LocalRedirect($arParams['OFFER_LIST_URL']);
            exit;
        }

        if (empty($volume)) {
            throw new Exception('Не задан объем');
        }

        //получение пары запрос-товар
        $arLead = lead::getLead($offer_farmer_id, $request_id, $offer_id);
        $arLead['ID'] = intval($arLead['ID']);
        if (empty($arLead['ID'])) {
            throw new Exception('Не удалось получить "Пара запрос-товар"');
        }

        /*
         *  старая логика (с добавлением сделки)
         *
                //получение детальной информации о запросе покупателя
                $arRequest = client::getRequestById($request_id);

                //проверка прав
                if (!(count($arResult['USER_DEALS_RIGHTS'][$offer_farmer_id]) == 0
                    || (count($arResult['USER_DEALS_RIGHTS'][$offer_farmer_id]) == 1
                        && $arResult['USER_DEALS_RIGHTS'][$offer_farmer_id]['fin'] == 'no_p'
                        && $arRequest['PAYMENT'] == 'post')
                )) {
                    throw new Exception('Не хвататет прав для совершения сделки');
                }*/

        if($arParams['TYPE'] != 'agent') {
            if(!isset($arResult['USER_RIGHTS']['REQUEST_RIGHT'])
                || $arResult['USER_RIGHTS']['REQUEST_RIGHT'] != 'Y'
            ){
                throw new Exception('Не хвататет прав для принятия запроса');
            }
        }

        //получаем все запросы по данному товару
        $arLeads = lead::getLeadList(array('UF_OFFER_ID' => $offer_id));
        $offerRequestApply = lead::createLeadList($arLeads);
        foreach ($offerRequestApply as $cur_data) {
            $sendData = array(
                'offer_id' => $offer_id,
                'selected_requests' => $cur_data['REQUEST']['ID'],
                'price' => $arLead['UF_CSM_PRICE'],
                'volume' => $_REQUEST['volume'],
                'type' => 'a', //"accepted"
                'farmer_id' => $offer_farmer_id
            );
            farmer::addCounterRequest($sendData,$arParams['TYPE']);
        }

        /*
         * старая логика (с добавлением сделки)


        $remains0 = $arRequest['REMAINS'];
        if ($remains0 < $volume) {
            throw new Exception('Данный объем не требуется. Проверьте правильность указанного объема');
        }

        //обновление остатка в запросе покупателя
        $remains = $remains0 - $volume;
        $prop = array('REMAINS' => $remains);
        if ($remains == 0) {
            $prop['ACTIVE'] = rrsIblock::getPropListKey('client_request', 'ACTIVE', 'no');
        }
        CIBlockElement::SetPropertyValuesEx($arRequest['ID'], rrsIblock::getIBlockId('client_request'), $prop);

        //получение детальной информации по товару
        $arOffer = farmer::getOfferById($offer_id);

        //стоимость на выбранном складе
        $arCost = $arRequest['COST'][$warehouse_id];

        if ($arRequest['NEED_DELIVERY'] == 'N')
            $type = 'fca';
        else
            $type = 'cpt';

        //сброс по параметрам
        $dumpValue = deal::getDump($arRequest['PARAMS'], $arOffer['PARAMS']);

        $arAgrohelperTariffs = model::getAgrohelperTariffs();
        $arCulturesGroup = culture::getCulturesGroup();

        //расчет цен БЦ, РЦ, ЦСМ (CPT/FCA)
        $price = farmer::bestPriceCalculation(
            array(
                'CLIENT_ID' => $arRequest['CLIENT_ID'],
                'CLIENT_WH_ID' => $warehouse_id,
                'CENTER' => $arCost['CENTER'],
                'ROUTE' => $arLead['UF_ROUTE'],
                'DDP_PRICE_CLIENT' => $arCost['DDP_PRICE_CLIENT'],
                'CLIENT_NDS' => $arRequest['USER_NDS'],
                'FARMER_NDS' => $arOffer['USER_NDS'],
                'TYPE' => $type,
                'DUMP' => $dumpValue,
                'TARIFF_LIST' => $arAgrohelperTariffs,
                'CULTURE_GROUP_ID' => $arCulturesGroup[$arRequest['CULTURE_ID']]
            )
        );

        //заполнение свойств
        $arUpdateValues = $arUpdatePropertyValues = array();

        $arUpdateValues['IBLOCK_ID'] = rrsIblock::getIBlockId('deals_deals');
        $arUpdateValues['ACTIVE'] = 'Y';
        $arUpdateValues['NAME'] = date("d.m.Y H:i:s");
        $arUpdateValues['ACTIVE_FROM'] = date("d.m.Y H:i:s");

        $arUpdatePropertyValues['CULTURE'] = $arRequest['CULTURE_ID'];
        $arUpdatePropertyValues['CLIENT'] = $arRequest['CLIENT_ID'];
        $arUpdatePropertyValues['REQUEST'] = $arRequest['ID'];
        $arUpdatePropertyValues['VOLUME_0'] = $arRequest['REMAINS'];
        $arUpdatePropertyValues['CENTER'] = $price['CENTER'];
        $arUpdatePropertyValues['CLIENT_WAREHOUSE'] = $price['WH_ID'];
        $arUpdatePropertyValues['PARITY_PRICE'] = $arRequest['COST'][$warehouse_id]['PARITY_PRICE'];
        $arUpdatePropertyValues['A_NDS'] = ($arRequest['USER_NDS'] == 'yes')?'Y':'N';
        $arUpdatePropertyValues['B_NDS'] = ($arOffer['USER_NDS'] == 'yes')?'Y':'N';
        $arUpdatePropertyValues['BASE_PRICE'] = round($price['BASE_PRICE'], 2);
        //$arUpdatePropertyValues['NDS_VAL'] = round($ndsValue, 2);
        $arUpdatePropertyValues['DUMP'] = $dumpValue;
        $arUpdatePropertyValues['ACC_PRICE'] = round($price['ACC_PRICE'], 2);
        $arUpdatePropertyValues['ROUTE'] = $price['ROUTE'];
        //$arUpdatePropertyValues['PRICE'] = round($price_acc_exw_comm, 2);
        $arUpdatePropertyValues['ACC_PRICE_CSM'] = round($price['ACC_PRICE_CSM'], 2);

        $arUpdatePropertyValues['FARMER'] = $arOffer['FARMER_ID'];
        $arUpdatePropertyValues['OFFER'] = $arOffer['ID'];
        $arUpdatePropertyValues['VOLUME'] = $volume;
        $arUpdatePropertyValues['FARMER_WAREHOUSE'] = $arOffer['WH_ID'];
        $arUpdatePropertyValues['DELIVERY'] = rrsIblock::getPropListKey('deals_deals', 'DELIVERY', $_REQUEST['delivery']);

        $partnerId = farmer::getPartnerIdByFarmer($arOffer['FARMER_ID']);
        $arUpdatePropertyValues['PARTNER'] = $partnerId;

        $arUpdatePropertyValues['STAGE'] = rrsIblock::getPropListKey('deals_deals', 'STAGE', 'new');
        $arUpdatePropertyValues['DATE_STAGE'] = date('d.m.Y H:i:s');
        $arUpdatePropertyValues['STATUS'] = rrsIblock::getPropListKey('deals_deals', 'STATUS', 'open');

        // Агент
        $arAgent = array_shift($agentObj->getAgentsByFarmers($offer_farmer_id));

        if (!empty($arAgent['ID'])) {

            $arUpdatePropertyValues['AGENT_USER'] = $arAgent['ID'];

            // Вознаграждение агенту
            if (empty($arAgent['REWARD_PERCENT'])) {
                $arAgent['REWARD_PERCENT'] = rrsIblock::getConst('REWARD_PERCENT_AGENT');
            }
            $arUpdatePropertyValues['REWARD_PERCENT_AGENT'] = $arAgent['REWARD_PERCENT'];

            // Вознаграждение агенту за транспортировку
            if (empty($arAgent['PERCENT_TRANSPORTATION'])) {
                $arAgent['PERCENT_TRANSPORTATION'] = rrsIblock::getConst('REWARD_PERCENT_TRANSPORTATION_AGENT');
            }
            $arUpdatePropertyValues['REWARD_PERCENT_TRANSPORTATION_AGENT'] = $arAgent['PERCENT_TRANSPORTATION'];
        }

        // Агент клиента
        $clientAgent = $agentObj->getProfileByClientID($arRequest['CLIENT_ID']);

        // Текущий процент вознаграждения агента клиента
        if (!empty($clientAgent['USER']['ID'])) {
            $arUpdatePropertyValues['AGENT_CLIENT_USER']            = $clientAgent['USER']['ID'];
            $arUpdatePropertyValues['REWARD_PERCENT_AGENT_CLIENT']  = $clientAgent['PROPERTY_REWARD_PERCENT_VALUE'];
        }


        // Вознаграждение оператора АХ от организатора АП
        $arUpdatePropertyValues['REWARD_PERCENT_OPERATOR_AH'] = rrsIblock::getConst('REWARD_PERCENT_OPERATOR_AH');

        //  Вознаграждение организатору покупателя от вознаграждения организатора АП (если они разные)
        $arUpdatePropertyValues['REWARD_PERCENT_ORGANIZER'] = rrsIblock::getConst('REWARD_PERCENT_ORGANIZER');

        // За транспортировку - Вознаграждение оператору АХ от вознаграждения организатора АП
        $arUpdatePropertyValues['REWARD_PERCENT_TRANSPORTATION_OPERATOR_AH'] = rrsIblock::getConst('REWARD_PERCENT_TRANSPORTATION_OPERATOR_AH');

        $arUpdateValues['PROPERTY_VALUES'] = $arUpdatePropertyValues;

        $ID = $oElement->Add($arUpdateValues);
        if (!$ID) {
            throw new Exception('Не удалось добавить сделку: "' . $oElement->LAST_ERROR . '"');
        }

        if ($remains == 0) {
            //удаление пар запрос-товар
            $filter = array(
                'UF_REQUEST_ID' => $arRequest['ID']
            );
            $arLeads = lead::getLeadList($filter);
            if (is_array($arLeads) && sizeof($arLeads) > 0) {
                lead::deleteLeads($arLeads);
            }
        }

        log::addDealStatusLog($ID, 'new', 'Новая сделка');

        //отправка уведомлений
        $noticeList = notice::getNoticeList();
        $culture = culture::getName($arRequest['CULTURE_ID']);

        //уведомления покупателю
        $clientProfile = client::getProfile($arRequest['CLIENT_ID'], true);
        $url = '/client/deals/' . $ID . '/';

        if (in_array($noticeList['e_d']['ID'], $clientProfile['PROPERTY_NOTICE_VALUE'])) {
            $arEventFields = array(
                'REQUEST_ID' => $arRequest['ID'],
                'CULTURE' => $culture['NAME'],
                'VOLUME' => $volume,
                'ID' => $ID,
                'URL' => $GLOBALS['host'] . $url,
                'EMAIL' => $clientProfile['USER']['EMAIL'],
            );
            CEvent::Send('CLIENT_CREATE_NEW_DEAL', 's1', $arEventFields);
        }
        if (in_array($noticeList['c_d']['ID'], $clientProfile['PROPERTY_NOTICE_VALUE'])) {
            notice::addNotice($clientProfile['USER']['ID'], 'd', 'Новая сделка', $url, '#' . $ID);
        }
        if (in_array($noticeList['s_d']['ID'], $clientProfile['PROPERTY_NOTICE_VALUE']) && $clientProfile['PROPERTY_PHONE_VALUE']) {
            $phone = str_replace(array('+', ' ', '(', ')', '-'), "", $clientProfile['PROPERTY_PHONE_VALUE']);
            notice::sendNoticeSMS($phone, 'Новая сделка: ' . $GLOBALS['host'] . $url);
        }

        //уведомления агенту покупателя

        $url = '/client_agent/deals/' . $ID . '/';
        if (isset($clientAgent['DEALS_RIGHTS']) && $clientAgent['DEALS_RIGHTS']) {
            if (in_array($noticeList['e_d']['ID'], $clientAgent['PROPERTY_NOTICE_VALUE'])) {
                $arEventFields = array(
                    'REQUEST_ID' => $arRequest['ID'],
                    'CULTURE' => $culture['NAME'],
                    'VOLUME' => $volume,
                    'ID' => $ID,
                    'URL' => $GLOBALS['host'] . $url,
                    'EMAIL' => $clientAgent['USER']['EMAIL'],
                );
                CEvent::Send('CLIENT_CREATE_NEW_DEAL', 's1', $arEventFields);
            }
            if (in_array($noticeList['c_d']['ID'], $clientAgent['PROPERTY_NOTICE_VALUE'])) {
                notice::addNotice($clientAgent['USER']['ID'], 'd', 'Новая сделка', $url, '#' . $ID);
            }
        }

        //уведомления организатору
        $partnerProfile = partner::getProfile($partnerId, true);
        $url = '/partner/deals/' . $ID . '/';

        if (in_array($noticeList['e_d']['ID'], $partnerProfile['PROPERTY_NOTICE_VALUE'])) {
            $arEventFields = array(
                'REQUEST_ID' => $arRequest['ID'],
                'CULTURE' => $culture['NAME'],
                'VOLUME' => $volume,
                'ID' => $ID,
                'URL' => $GLOBALS['host'] . $url,
                'EMAIL' => $partnerProfile['USER']['EMAIL'],
            );
            CEvent::Send('PARTNER_CREATE_NEW_DEAL', 's1', $arEventFields);
        }
        if (in_array($noticeList['c_d']['ID'], $partnerProfile['PROPERTY_NOTICE_VALUE'])) {
            notice::addNotice($partnerProfile['USER']['ID'], 'd', 'Новая сделка', $url, '#' . $ID);
        }
        if (in_array($noticeList['s_d']['ID'], $partnerProfile['PROPERTY_NOTICE_VALUE']) && $partnerProfile['PROPERTY_PHONE_VALUE']) {
            $phone = str_replace(array('+', ' ', '(', ')', '-'), "", $partnerProfile['PROPERTY_PHONE_VALUE']);
            notice::sendNoticeSMS($phone, 'Новая сделка: ' . $GLOBALS['host'] . $url);
        }

        if ($remains == 0) {
            $fca_dap = ($arRequest['NEED_DELIVERY'] == 'Y') ? 'CPT' : 'FCA';
            $REQ_DATA = $culture['NAME'] . " (" . $fca_dap . "), " . $arRequest['VOLUME'] . ' т, ' . client::getCostWHNames($arRequest['ID']);

            //уведомление покупателя
            $url = '/client/request/new/?id=' . $arRequest['ID'];
            if (in_array($noticeList['e_r']['ID'], $clientProfile['PROPERTY_NOTICE_VALUE'])) {
                $arEventFields = array(
                    'REQ_DATA' => $REQ_DATA,
                    'ID' => $arRequest['ID'],
                    'URL' => $GLOBALS['host'] . $url,
                    'EMAIL' => $clientProfile['USER']['EMAIL'],
                );
                CEvent::Send('CLIENT_REQUEST_NO_VOLUME', 's1', $arEventFields);
            }
            if (in_array($noticeList['c_r']['ID'], $clientProfile['PROPERTY_NOTICE_VALUE'])) {
                notice::addNotice($clientProfile['USER']['ID'], 'r', 'Объем по запросу исчерпан', $url, '#' . $arRequest['ID']);
            }
            if (in_array($noticeList['s_r']['ID'], $clientProfile['PROPERTY_NOTICE_VALUE']) && $clientProfile['PROPERTY_PHONE_VALUE']) {
                $phone = str_replace(array('+', ' ', '(', ')', '-'), "", $clientProfile['PROPERTY_PHONE_VALUE']);
                notice::sendNoticeSMS($phone, 'Объем по запросу исчерпан: ' . $GLOBALS['host'] . $url);
            }

            $push_body = $REQ_DATA;
            $tokens = client::getPushTokens(array($clientProfile['USER']['ID']));

            if (isset($tokens[$clientProfile['USER']['ID']]) && count($tokens[$clientProfile['USER']['ID']]) > 0) {
                foreach ($tokens[$clientProfile['USER']['ID']] as $token) {
                    Push::SendPush($token, $push_body, array('type' => 'request_completed', 'request_id' => $arRequest['ID']), 'Объем исчерпан');
                }
            }

            //уведомление агента покупателя
            if (isset($clientAgent['DEALS_RIGHTS']) && $clientAgent['DEALS_RIGHTS']) {
                if (in_array($noticeList['e_r']['ID'], $clientAgent['PROPERTY_NOTICE_VALUE'])) {
                    $arEventFields = array(
                        'REQ_DATA' => $REQ_DATA,
                        'ID' => $arRequest['ID'],
                        'URL' => $GLOBALS['host'] . '/client_agent/request/new/?id=' . $arRequest['ID'],
                        'EMAIL' => $clientAgent['USER']['EMAIL'],
                    );
                    CEvent::Send('CLIENT_REQUEST_NO_VOLUME', 's1', $arEventFields);
                }
                if (in_array($noticeList['c_r']['ID'], $clientAgent['PROPERTY_NOTICE_VALUE'])) {
                    notice::addNotice($clientAgent['USER']['ID'], 'r', 'Объем по запросу исчерпан', $url, '#' . $arRequest['ID']);
                }
            }
        }

        //вычисление новой паритетной цены для рег. центра по культуре
        $arPrices = model::parityPriceCalculation($arCost['CENTER'], $arRequest['CULTURE_ID']);
        if (is_array($arPrices) && sizeof($arPrices) > 0) {
            //сохранение новой паритетной цены
            $id = model::saveParityPrice($arCost['CENTER'], $arRequest['CULTURE_ID'], $arPrices);
            if ($id > 0) {
                //логирование изменения паритетной цены
                log::addParityPriceLog($arCost['CENTER'], $arRequest['CULTURE_ID'], 'новая сделка', 'deal', $arPrices);
            }
        }

        // Сохраняем все изменения в БД

        */

        $DB->Commit();
        /*
        LocalRedirect($arParams['DEAL_LIST_URL'] . $ID . '/');
        */

        setcookie('success_counter_request', 'y', time() + 60, '/');

        if($arParams['TYPE'] == 'agent'){
            LocalRedirect('/partner/farmer_request/');
        }else{
            LocalRedirect('/farmer/request/');
        }

        exit;

    } catch (Exception $e) {
        // Откат изменений
        $DB->Rollback();
        $arResult["MESSAGE"] = $e->getMessage();
    }
}
elseif (!empty($_REQUEST['reject'])) {
    if ($arParams['TYPE'] == 'farmer') {
        $offer_farmer_id = $arParams['FARMER_ID'];
    }
    else {
        $offer_farmer_id = $agentObj->getFarmerByOffer($_POST['offer']);
    }

    if (!empty($offer_farmer_id)) {
        //отклонение запроса приводит к удалению пары
        $arLeads[] = lead::getLead($offer_farmer_id, $_POST['request'], $_POST['offer']);
        if (is_array($arLeads) && sizeof($arLeads) > 0) {
            lead::deleteLeads($arLeads);
        }
        //удаление ВП
        client::removeCountersByRequestID($_POST['request']);
    }

    LocalRedirect($APPLICATION->GetCurPageParam(null, ['reject', 'request', 'offer']));
    exit;
}
//получение пар запрос-товар
$arFilter = array(
    'UF_FARMER_ID' => $arParams['FARMER_ID']
);
$culture_offer = 0;
if (intval($_GET['culture']) > 0) {
    $arFilter['UF_CULTURE_ID'] = intval($_GET['culture']);
    $culture_offer = intval($_GET['culture']);
}
if (intval($_GET['wh']) > 0) {
    $arFilter['UF_FARMER_WH_ID'] = intval($_GET['wh']);
}

if(intval($_GET['type_nds']) > 0) {
    switch ($_GET['type_nds']) {
        case 1:
            $arFilter['UF_NDS'] = 'yes';
            break;
        case 2:
            $arFilter['UF_NDS'] = 'no';
            break;
    }
}
if(isset($_GET['region_id'])){
    if(!empty($_GET['region_id'])){
        $farmerWHs = array();
        //получаем склады по выбранному региону
        $res = CIBlockElement::GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('farmer_warehouse'),
                'ACTIVE' => 'Y',
                'PROPERTY_REGION' => $_GET['region_id'],
            ),
            false,
            false,
            array('ID')
        );
        while ($ob = $res->Fetch()) {
            $farmerWHs[$ob['ID']] = 1;
        }
        $arFilter['UF_FARMER_WH_ID'] = array_keys($farmerWHs);
    }
}

$arLeads = lead::getLeadList($arFilter);

//получение данных для последующего использования при выводе количеств в фильтре
$params = array();
if(isset($arFilter['UF_FARMER_ID'])) {
    if($arParams['TYPE'] != 'agent') {
        $params['UF_FARMER_ID'] = $arFilter['UF_FARMER_ID'];
    }else{
        $agentObj = new agent();
        $params['UF_FARMER_ID'] = $agentObj->getFarmers($USER->GetID());
    }
    $arResult['FILTER_COUNT_DATA'] = farmer::getLeadsForFilterCount($params);

    //получение регионов складов
    $wh_ids = array();
    foreach($arResult['FILTER_COUNT_DATA'] as $cur_data){
        $wh_ids[$cur_data['UF_FARMER_WH_ID']] = true;
    }
    if(count($wh_ids) > 0) {
        $arResult['FILTER_WH_TO_REG'] = farmer::getRegionsByWhs(array_keys($wh_ids));
    }

    unset($wh_ids);
}

$offersIds = array();

if (sizeof($arLeads) < 1) {
    $arResult['ERROR'] = "Ни одного запроса не найдено";
}

if (!$arResult['ERROR']) {

    //добавляем параметры пагинации
    if(!isset($arParams['NEWS_COUNT'])
        || !filter_var($arParams['NEWS_COUNT'], FILTER_VALIDATE_INT)
    ){
        $arParams['NEWS_COUNT'] = 20;
    }

    $page_number = 1;
    if(isset($_GET['page'])
        && filter_var($_GET['page'], FILTER_VALIDATE_INT)
        && $_GET['page'] > 1
    ){
        $page_number = $_GET['page'];
    }

    $offerRequestApply = lead::createLeadList($arLeads);

    //сортировка запросов по культурам и по цене
    usort($offerRequestApply, "orderRcPrice");
    //$offerRequestApply = deal::leadsSort($offerRequestApply);
    $offerRequestApply = deal::leadsSort($offerRequestApply, true);

    //получение страницы элемента, если требуется
    $check_element = 0;
    if(isset($_GET['o'])
        && is_numeric($_GET['o'])
        && isset($_GET['r'])
        && is_numeric($_GET['r'])
    ){
        $check_element = lead::getIDByOfferAndRequest($_GET['o'], $_GET['r']);
    }

    //получение количества элементов
    $elements_cnt = 0;

    //если нужна переадресация на элемент, который находится на другой странице
    $my_c = 0;
    if($check_element > 0){
        $found_elem = false;
        foreach($offerRequestApply as $cur_data){
            if(!$found_elem) {
                foreach($cur_data as $cur_element){
                    $my_c++;
                    if($cur_element['LEAD']['ID'] == $check_element){
                        $found_elem = true;
                        break;
                    }
                }
            }
            $elements_cnt += count($cur_data);
        }
    }else{
        foreach($offerRequestApply as $cur_data){
            $elements_cnt += count($cur_data);
        }
    }
    $pages_cnt = ceil($elements_cnt / $arParams['NEWS_COUNT']);
    if($page_number > $pages_cnt){
        $page_number = $pages_cnt;
    }

    //проверка соответствия текущей страницы и страницы элемента (если нужно развернуть данные элемента)
    if($check_element > 0 && $found_elem){
        $redirect_page = ceil($my_c / $arParams['NEWS_COUNT']);
        if($redirect_page != $page_number){
            if($redirect_page == 1){
                LocalRedirect($APPLICATION->GetCurPageParam('' . (isset($_GET['region_id']) ? "region_id=" . $_GET['region_id'] : ''), ['page', 'region_id']));
            }else{
                LocalRedirect($APPLICATION->GetCurPageParam('page=' . $redirect_page . (isset($_GET['region_id']) ? "&amp;region_id=" . $_GET['region_id'] : ''), ['page', 'region_id']));
            }
            exit;
        }
    }

    //пагинация
    $nav = new \Bitrix\Main\UI\PageNavigation("page");
    $nav->allowAllRecords(false)
        ->setPageSize($arParams['NEWS_COUNT'])
        ->initFromUri();
    $nav->setRecordCount($elements_cnt);
    $nav->setCurrentPage($page_number);
    $arResult['NAV_OBJ'] = $nav;

    //ограничение элементов согласно пагинации
    $offerRequestApply = deal::leadsGetPageElements($offerRequestApply, $arParams['NEWS_COUNT'], $page_number);

    $arGroupItems   = [];
    $arFarmerId     = [];
    $arCultureId    = [];
    $arClientsId    = [];
    $arRequestWarehouseId = [];

    if ($arParams['TYPE'] == 'farmer' || $arParams['TYPE'] == 'public') {
        $arFarmerId[] = $arParams['FARMER_ID'];
    }
    elseif ($arParams['TYPE'] == 'agent') {
        $agentId = $USER->GetID();
        $rs = $oElement->GetList(
            array('ID' => 'DESC'),
            array(
                'IBLOCK_ID'         => rrsIblock::getIBlockId('farmer_agent_link'),
                'PROPERTY_AGENT_ID' => $agentId,
            ),
            false,
            false,
            array('PROPERTY_USER_ID')
        );
        while ($arRow = $rs->Fetch()) {
            $arFarmerId[] = $arRow['PROPERTY_USER_ID_VALUE'];
        }
    }

    foreach ($arFarmerId as $iFarmerId) {
        if (empty($offerRequestApply[$iFarmerId])) {
            continue;
        }

        foreach ($offerRequestApply[$iFarmerId] as $arItem) {
            $iCultureId = $arItem['OFFER']['CULTURE_ID'];
            $arCultureId[$iCultureId] = $iCultureId;
            $offersIds[$arItem['OFFER']['ID']] = true;

            // Группируем по культуре и по АП
            $arGroupItems[$iCultureId][$iFarmerId][] = $arItem;

            if (!empty($arItem['REQUEST']['CLIENT_ID'])) {
                $arClientsId[$arItem['REQUEST']['CLIENT_ID']] = true;
            }

            if(!empty($arItem['REQUEST']['BEST_PRICE']['WH_ID'])) {
                $arRequestWarehouseId[$arItem['REQUEST']['BEST_PRICE']['WH_ID']] = true;
            }
        }
    }
    unset($offerRequestApply, $iCultureId, $iFarmerId);

    // Склады
    if (!empty($arRequestWarehouseId)) {
        $arRequestWarehouseId = array_keys($arRequestWarehouseId);
        $arResult['REQUEST_WAREHOUSES_LIST'] = client::getWarehouseParamsList($arRequestWarehouseId);
    }

    /**
     * Разбиваем группировку на блоки [АП - Цена по каждой культуре]
     */
    $nBlockNum = 0;
    $arResult['ITEMS'] = [];

    $offersCultures = array();

    loopStart:
    $bGoTo = false;
    foreach ($arFarmerId as $iFarmerId) {

        foreach ($arCultureId as $iCultureId) {

            if(empty($arGroupItems[$iCultureId][$iFarmerId])) {
                continue;
            }

            $tmp = array_shift($arGroupItems[$iCultureId][$iFarmerId]);
            $offersCultures[$tmp['OFFER']['ID']] = $tmp['OFFER']['CULTURE_ID'];
            //убираем фильтрацию по культуре внутри цикла
//            if($culture_offer>0){
//                if($tmp['OFFER']['CULTURE_ID'] == $culture_offer)
//                    $arResult['ITEMS'][$nBlockNum][] = $tmp;
//            }else{
                $arResult['ITEMS'][$nBlockNum][] = $tmp;
//            }
            if(!empty($arGroupItems[$iCultureId][$iFarmerId])) {
                $bGoTo = true;
            }
        }
        $nBlockNum++;
    }

    if($bGoTo) {
        goto loopStart;
    }

//    foreach($arResult['ITEMS'] as $cur_block){
//        foreach()
//    }

    //проставляем в массиве для фильтра культуру из предложений
    for($i=0,$c=sizeof($arResult['FILTER_COUNT_DATA']);$i<$c;$i++){
        if(isset($offersCultures[$arResult['FILTER_COUNT_DATA'][$i]['UF_OFFER_ID']])){
            $arResult['FILTER_COUNT_DATA'][$i]['UF_CULTURE_ID'] = $offersCultures[$arResult['FILTER_COUNT_DATA'][$i]['UF_OFFER_ID']];
        }
    }

    if ($arParams['TYPE'] == 'agent') {
        //получение данных поставщиков
        $arResult['FARMERS_DATA'] = array();

        //получить права агентов на создание сделок по запросам поставщиков
        $arResult['AGENT_RIGHTS_LIST'] = rrsIblock::getPropListKey('farmer_agent_link', 'AGENT_RIGHTS');

        if (count($arFarmerId) > 0) {
            $arResult['FARMERS_DATA'] = $agentObj->getFarmersForSelect($agentId, $arFarmerId, true, true);

            //получение данных заполненности обязательных полей профиля
            $agentObj = new agent();
            $arResult['FARMERS_PROFILE_DONE'] = $agentObj->getFarmersRegistrationRights($arFarmerId);
        }
        if((sizeof($arResult['FARMERS_DATA']))&&(is_array($arResult['FARMERS_DATA']))){
            foreach ($arResult['FARMERS_DATA'] as $k=>$v){
                $profile = farmer::getProfile($k);
                $arResult['FARMERS_DATA'][$k]['PHONE'] = $profile['PROPERTY_PHONE_VALUE'];
            }
            unset($profile);
        }

    }
}
//получение в разрезе каждого фильтра принятий покупателя...
if((sizeof($arResult['FILTER_COUNT_DATA']))&&(is_array($arResult['FILTER_COUNT_DATA']))){
    $regions_limits = array();
    $whids_limits = array();
    $cultures_limits = array();
    $farmers_limits = array();
    $nds_limits = array();

    $clients_whids = array();
    $clients_farmers = array();
    $clients_cultures = array();
    $clients_nds = array();
    $clients = array();

    $nds = array(
        'yes'=>1,
        'no'=>2
    );
    foreach($arResult['FILTER_COUNT_DATA'] as $item){
        $clients_farmers[$item['UF_FARMER_ID']][$item['UF_CLIENT_ID']] = 1;
        $clients_cultures[$item['UF_CULTURE_ID']][$item['UF_CLIENT_ID']] = 1;
        $clients_whids[$item['UF_FARMER_WH_ID']][$item['UF_CLIENT_ID']] = 1;
        $clients_nds[$nds[$item['UF_NDS']]][$item['UF_CLIENT_ID']] = 1;
        $clients[$item['UF_CLIENT_ID']] = 1;
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
        //лимиты принятий по складам
        foreach ($clients_whids as $wh_id => $clients_vals) {
            if (!empty($wh_id)) {
                $whids_limits[$wh_id] = 0;
                foreach ($clients_vals as $client_id => $val) {
                    if ($users_limits[$client_id] > 0) {
                        $whids_limits[$wh_id] = 1;
                        break;
                    }
                }
            }
        }
        //лимиты принятий по регионам
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
        //лимиты принятий по культуре
        foreach ($clients_cultures as $cult_id => $clients_vals) {
            if (!empty($cult_id)) {
                $cultures_limits[$cult_id] = 0;
                foreach ($clients_vals as $client_id => $val) {
                    if ($users_limits[$client_id] > 0) {
                        $cultures_limits[$cult_id] = 1;
                        break;
                    }
                }
            }
        }
        //лимиты принятий по поставщикам
        foreach ($clients_farmers as $farm_id => $clients_vals) {
            if (!empty($farm_id)) {
                $farmers_limits[$farm_id] = 0;
                foreach ($clients_vals as $client_id => $val) {
                    if ($users_limits[$client_id] > 0) {
                        $farmers_limits[$farm_id] = 1;
                        break;
                    }
                }
            }
        }
        //лимиты принятий по типам НДС
        foreach ($clients_nds as $nds_id => $clients_vals) {
            if (!empty($nds_id)) {
                $nds_limits[$nds_id] = 0;
                foreach ($clients_vals as $client_id => $val) {
                    if ($users_limits[$client_id] > 0) {
                        $nds_limits[$nds_id] = 1;
                        break;
                    }
                }
            }
        }
        $arResult['LIMITS'] = array(
            'regions_limits'=>$regions_limits,
            'cultures_limits'=>$cultures_limits,
            'farmers_limits'=>$farmers_limits,
            'nds_limits'=>$nds_limits,
            'whids_limits'=>$whids_limits,
        );
    }
}

//получение данных о встречных предложениях
$arResult['COUNTER_REQUESTS_DATA'] = farmer::getCounterRequestsData(array_keys($offersIds));

$this->includeComponentTemplate();