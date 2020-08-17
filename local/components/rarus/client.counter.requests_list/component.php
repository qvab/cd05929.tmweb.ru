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
CPageOption::SetOptionString("main", "nav_page_in_session", "N");

$arResult['ERROR_MESSAGE'] = '';
$arResult['MODE'] = 'client';
$user_id = 0;
$arrCheckClientsIds = array(); // массив ID покупателей для проверки наличия предложений при отстутствии предложений для текущего фильтра
//проверка обязательных полей
if(isset($arParams['CLIENT_ID'])
    && is_numeric($arParams['CLIENT_ID'])
){
    $user_id = $arParams['CLIENT_ID'];
    $arrCheckClientsIds[$user_id] = true;
}else {
    if (isset($arParams['AGENT_ID'])
        && is_numeric($arParams['AGENT_ID'])
    ) {
        $arResult['MODE'] = 'agent';
        $agent_obj = new agent();

        $arResult['CLIENT_LIST'] = $agent_obj->getClients($arParams['AGENT_ID']);
        if(count($arResult['CLIENT_LIST']) > 0){
            $arResult['CLIENT_LIST'] = array_flip($arResult['CLIENT_LIST']);

            $arrCheckClientsIds = $arResult['CLIENT_LIST'];
        }

        $arResult['USERS_EMAIL'] = array();
        $users_info = rrsIblock::getUsersInfo(array_keys($arResult['CLIENT_LIST']));
        foreach ($users_info as $uid=>$fields){
            $arResult['USERS_EMAIL'][$uid] = $fields['EMAIL'];
        }

        if(isset($_GET['client_id'])
            && is_numeric($_GET['client_id'])
            && isset($arResult['CLIENT_LIST'][$_GET['client_id']])
        ){
            $user_id = array($_GET['client_id']);
        }else{
            if(count($arResult['CLIENT_LIST']) > 0){
                $user_id = array_keys($arResult['CLIENT_LIST']);
            }
        }
    }else{
        $arResult['ERROR_MESSAGE'] = 'Не указан ID пользователя';
        return false;
    }
}

//проверка прав на принятие Предложения
$arResult['USER_RIGHTS'] = client::checkRights('counter_request', $user_id);
//для текущего пользователя получаем количество доступных принятий
if(isset($arResult['USER_RIGHTS']['REQUEST_RIGHT'])
    && $arResult['USER_RIGHTS']['REQUEST_RIGHT'] == 'Y'
){
    $arResult['USER_CON_REQ_OPENS_LIMIT'] = client::openerCountGet($user_id);
}

$arResult['ITEMS'] = array();
if ($arResult['ERROR_MESSAGE'] == '') {
    if(isset($_POST['accept'])
        && $_POST['accept'] == 'y'
        && isset($_POST['request']) && is_numeric($_POST['request'])
        && isset($_REQUEST['warehouse_cl']) && is_numeric($_REQUEST['warehouse_cl']) && $_REQUEST['warehouse_cl'] > 0
        && isset($_POST['offer']) && is_numeric($_POST['offer'])
        && isset($_POST['warehouse_f']) && is_numeric($_POST['warehouse_f'])
        && isset($arResult['USER_RIGHTS']['REQUEST_RIGHT'])
        && isset($arParams['CLIENT_ID'])
        && ($arResult['USER_RIGHTS']['REQUEST_RIGHT'] == 'Y'
            || $arResult['USER_RIGHTS']['REQUEST_RIGHT'] == 'LIM'
        )
    ) {
        //создание пары (может создавать только покупатель, но не агент)
        $arRequest = client::getRequestById($_POST['request']);
        $arOffer = farmer::getOfferById($_POST['offer']);
        $arLead = lead::getLead($arOffer['FARMER_ID'], $_POST['request'], $_POST['offer']);
        $arCounterRequestData = reset(client::getCounterRequestData($user_id, array(
            'UF_REQUEST_ID' => $_POST['request'],
            'UF_CLIENT_WH_ID' => $_REQUEST['warehouse_cl'],
            'UF_OFFER_ID' => $_POST['offer'],
            'UF_FARMER_WH_ID' => $_POST['warehouse_f']
        )));

        //если есть принятия или предложение является агентским
        if ($arResult['USER_RIGHTS']['REQUEST_RIGHT'] != 'LIM'
            || $arCounterRequestData['UF_COFFER_TYPE'] == 'p'
        ) {

            $volume = $arCounterRequestData['UF_VOLUME'];
            if ($volume == 0) {
                return false;
            }
            $warehouse_id = $_REQUEST['warehouse_cl'];

            $remains0 = $arRequest['REMAINS'];
            if ($remains0 < $volume) {
                $volume = $remains0;
                //throw new Exception('Данный объем не требуется. Проверьте правильность указанного объема');
            }

            //обновление остатка в запросе покупателя
            $remains = $remains0 - $volume;
            $prop = array('REMAINS' => $remains);
            if ($remains == 0) {
                $prop['ACTIVE'] = rrsIblock::getPropListKey('client_request', 'ACTIVE', 'no');
                logRequestDeactivating($arRequest['ID']); //пишем лог о деактивации запроса
            }
            CIBlockElement::SetPropertyValuesEx($arRequest['ID'], rrsIblock::getIBlockId('client_request'), $prop);

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

            //расчет цен БЦ, БЦ контракта, РЦ, ЦСМ для покупателя
            $price = client::pairPriceCalculation(
                array(
                    'CLIENT_ID' => $arRequest['CLIENT_ID'],
                    'CLIENT_WH_ID' => $warehouse_id,
                    'CENTER' => $arCost['CENTER'],
                    'ROUTE' => $arLead['UF_ROUTE'],
                    'RCSM' => $arCounterRequestData['UF_FARMER_PRICE'],
                    'CLIENT_NDS' => $arRequest['USER_NDS'],
                    'FARMER_NDS' => $arOffer['USER_NDS'],
                    'TYPE' => $arCounterRequestData['UF_DELIVERY'],
                    'DUMP' => $dumpValue,
                    'TARIFF_LIST' => $arAgrohelperTariffs,
                    'CULTURE_GROUP_ID' => $arCulturesGroup[$arRequest['CULTURE_ID']]
                ),
                true,
                true
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
            $arUpdatePropertyValues['PARITY_PRICE'] = $price['PARITY_PRICE'];
            $arUpdatePropertyValues['A_NDS'] = ($arRequest['USER_NDS'] == 'yes') ? 'Y' : 'N';
            $arUpdatePropertyValues['B_NDS'] = ($arOffer['USER_NDS'] == 'yes') ? 'Y' : 'N';
            $arUpdatePropertyValues['BASE_PRICE'] = round($price['BASE_PRICE'], 2);
            $arUpdatePropertyValues['DUMP'] = $dumpValue;
            $arUpdatePropertyValues['DUMP_RUB'] = $price['DUMP_RUB'];
            if (isset($price['CSM_FOR_CLIENT']['SBROS_RUB']) && $price['CSM_FOR_CLIENT']['SBROS_RUB'] != 0) {
                $arUpdatePropertyValues['DUMP_RUB_CLIENT_NDS'] = $price['CSM_FOR_CLIENT']['SBROS_RUB'];
            }
            $arUpdatePropertyValues['TARIF'] = $price['TARIF'];
            $arUpdatePropertyValues['ACC_PRICE'] = round($price['ACC_PRICE'], 2);
            $arUpdatePropertyValues['ROUTE'] = $price['ROUTE'];
            $arUpdatePropertyValues['BASE_CONTR_PRICE'] = round($arCounterRequestData['UF_BASE_CONTR_PRICE'], 2);
            if (isset($price['CSM_FOR_CLIENT']['UF_CSM_PRICE'])) {
                $arUpdatePropertyValues['ACC_PRICE_CSM_CLIENT_NDS'] = $price['CSM_FOR_CLIENT']['UF_CSM_PRICE'];
            }
            $arUpdatePropertyValues['ACC_PRICE_CSM'] = round($arCounterRequestData['UF_FARMER_PRICE'], 2);
            $arUpdatePropertyValues['FARMER'] = $arOffer['FARMER_ID'];
            $arUpdatePropertyValues['OFFER'] = $arOffer['ID'];
            $arUpdatePropertyValues['VOLUME'] = $volume;
            $arUpdatePropertyValues['FARMER_WAREHOUSE'] = $arOffer['WH_ID'];
            $arUpdatePropertyValues['DELIVERY'] = rrsIblock::getPropListKey('deals_deals', 'DELIVERY', $_REQUEST['delivery']);

            $arUpdatePropertyValues['PARTNER'] = reset(farmer::getLinkedPartnerList($arOffer['FARMER_ID'], true));

            $arUpdatePropertyValues['STAGE'] = rrsIblock::getPropListKey('deals_deals', 'STAGE', 'new');
            $arUpdatePropertyValues['DATE_STAGE'] = date('d.m.Y H:i:s');
            $arUpdatePropertyValues['PAIR_STATUS'] = rrsIblock::getPropListKey('deals_deals', 'PAIR_STATUS', 'new');
            $arUpdatePropertyValues['DELIVERY_TYPE'] = $arCounterRequestData['UF_DELIVERY'];

            //устанавливаем того, кто отправил ссылку, если есть данные
            if(isset($_REQUEST['partnerid'])
                && is_numeric($_REQUEST['partnerid'])
            ) {
                $arUpdatePropertyValues['DEAL_REFERER'] = $_REQUEST['partnerid'];
            }

            //Доп опции
            if ($arCounterRequestData['UF_COFFER_TYPE'] == 'p') {
                //если является агентским предложением, то вносим эти данные в пару
                $arUpdatePropertyValues['PARTNER_PRICE'] = $arCounterRequestData['UF_PARTNER_PRICE'];
                $arUpdatePropertyValues['COFFER_BY_PARTNER'] = $arCounterRequestData['UF_CREATE_BY_PARTNER'];
                //проставляем данные, внесенные организатором (обязательные)
                $temp_addit_data = array();
                if (trim($arCounterRequestData['UF_ADDIT_FIELDS']) != '') {
                    $temp_addit_data = json_decode($arCounterRequestData['UF_ADDIT_FIELDS'], true);
                    if ((
                            isset($temp_addit_data['IS_ADD_CERT'])
                            && $temp_addit_data['IS_ADD_CERT'] == 1
                        ) || !empty($arOffer['Q_APPROVED'])
                    ) {
                        $_REQUEST['IS_ADD_CERT'] = 'Y';
                    }
                    if (isset($temp_addit_data['IS_BILL_OF_HEALTH']) && $temp_addit_data['IS_BILL_OF_HEALTH'] == 1) {
                        $_REQUEST['IS_BILL_OF_HEALTH'] = 'Y';
                    }
                    if (isset($temp_addit_data['IS_VET_CERT']) && $temp_addit_data['IS_VET_CERT'] == 1) {
                        $_REQUEST['IS_VET_CERT'] = 'Y';
                    }
                    if (isset($temp_addit_data['IS_QUALITY_CERT']) && $temp_addit_data['IS_QUALITY_CERT'] == 1) {
                        $_REQUEST['IS_QUALITY_CERT'] = 'Y';
                    }
                    if (isset($temp_addit_data['IS_TRANSFER']) && $temp_addit_data['IS_TRANSFER'] == 1) {
                        $_REQUEST['IS_TRANSFER'] = 'Y';
                    }
                    if (isset($temp_addit_data['IS_SECURE_DEAL']) && $temp_addit_data['IS_SECURE_DEAL'] == 1) {
                        $_REQUEST['IS_SECURE_DEAL'] = 'Y';
                    }
                    if (isset($temp_addit_data['IS_AGENT_SUPPORT']) && $temp_addit_data['IS_AGENT_SUPPORT'] == 1) {
                        $_REQUEST['IS_AGENT_SUPPORT'] = 'Y';
                    }
                }
            }
            $arUpdatePropertyValues['COFFER_TYPE'] = $arCounterRequestData['UF_COFFER_TYPE'];
            $arUpdatePropertyValues['IS_ADD_CERT'] = $_REQUEST['IS_ADD_CERT'] ?: 'N';
            $arUpdatePropertyValues['IS_BILL_OF_HEALTH'] = $_REQUEST['IS_BILL_OF_HEALTH'] ?: 'N';
            $arUpdatePropertyValues['IS_VET_CERT'] = $_REQUEST['IS_VET_CERT'] ?: 'N';
            $arUpdatePropertyValues['IS_QUALITY_CERT'] = $_REQUEST['IS_QUALITY_CERT'] ?: 'N';
            $arUpdatePropertyValues['IS_TRANSFER'] = $_REQUEST['IS_TRANSFER'] ?: 'N';
            $arUpdatePropertyValues['IS_SECURE_DEAL'] = $_REQUEST['IS_SECURE_DEAL'] ?: 'N';
            $arUpdatePropertyValues['IS_AGENT_SUPPORT'] = $_REQUEST['IS_AGENT_SUPPORT'] ?: 'N';

            $arUpdateValues['PROPERTY_VALUES'] = $arUpdatePropertyValues;

            $oElement = new CIBlockElement;
            $ID = $oElement->Add($arUpdateValues);
            if (!$ID) {
                throw new Exception('Не удалось добавить пару: "' . $oElement->LAST_ERROR . '"');
            }
            //убираем того, кто отправил ссылку, если есть данные
            if (isset($arUpdatePropertyValues['DEAL_REFERER'])) {
                setcookie('counter_request_referer', "", time() - 10, '/');
            }

            //создание записи платежа
            $arUpdateValues = array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('deals_payments'),
                'ACTIVE' => 'Y',
                'NAME' => 'Платеж по сделке ' . $ID,
                'PROPERTY_VALUES' => array(
                    'CLIENT' => $arRequest['CLIENT_ID'],
                    'FARMER' => $arOffer['FARMER_ID'],
                    'DEAL' => $ID,
                    'CULTURE' => $arRequest['CULTURE_ID'],
                    'VOLUME' => $volume,
                    'DEAL_PRICE' => ''
                )
            );
            $oElement->Add($arUpdateValues);

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

            //удаление записи встречного Предложения
            log::_deleteEntity(log::getIdByName('COUNTEROFFERS'), $arCounterRequestData['ID']);
            //уменьшение объема по связанным встречным предложениям (у которых те же товары ап, но другие запросы)
            $remains_volume = $arCounterRequestData['UF_VOLUME_REMAINS'] - $volume;
            if ($remains_volume < 0) $remains_volume = 0;
            client::counterRequestsRecountVolume($_POST['offer'], $remains_volume, $arRequest['CULTURE_ID'], $arRequest['CULTURE_NAME']);

            //списывание одного принятия при его использовании (если не агенсткое предложение)
            if ($arCounterRequestData['UF_COFFER_TYPE'] != 'p') {
                client::counterReqLimitQuantityChange('use', -1, $arParams['CLIENT_ID']);
            }

            /*
            log::addDealStatusLog($ID, 'new', 'Новая пара');

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
            */

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
            $DB->Commit();

            /*
            LocalRedirect($arParams['DEAL_LIST_URL'] . $ID . '/');
            */

            $sList = 'Услуги Агрохелпера:<ul>';
            $arFields = [
                'IS_ADD_CERT' => 'Отбор проб и лабораторная диагностика',
                'IS_BILL_OF_HEALTH' => 'Карантинное свидетельство',
                'IS_VET_CERT' => 'Ветеринарные свидетельства',
                'IS_QUALITY_CERT' => 'Сертификаты качества',
                'IS_TRANSFER' => 'Транспортировка',
                'IS_SECURE_DEAL' => 'Безопасная сделка',
                'IS_AGENT_SUPPORT' => 'Сопровождение сделки',
            ];
            $arSList = [];

            //Отправка уведомлений
            $arSendedUsers = array();
            //Доп опции
            foreach ($arFields as $sName => $sTranslate) {
                if (isset($_REQUEST[$sName]) && $_REQUEST[$sName] === 'Y') {
                    $arSList[] = "<li>" . $sTranslate . "</li>";
                }
            }

            if (count($arSList) > 0) {
                $sList .= implode('', $arSList);

                $sList .= '</ul>';
            } else {

                $sList = '';
            }


            $sList_admin = $sList;
            if (!empty($ID)) {
                $sList .= '<br><a target="_blank" href="' . $GLOBALS['host'] . '/partner/pair/?id=' . $ID . '">Пара #' . $ID . '</a>';
                $sList_admin .= '<br><a target="_blank" href="' . $GLOBALS['host'] . '/bitrix/admin/iblock_element_edit.php?IBLOCK_ID=38&type=deals&ID=' . $ID . '">Пара #' . $ID . '</a>';
            }

            $sUserName = getUserName($USER->GetID());
            $sUrl = $GLOBALS['host'] . '/profile/?uid=' . $USER->GetID();

            /*if ($arUpdatePropertyValues['AGENT_CLIENT_USER']) {
                $arUser = rrsIblock::getUserInfo($arUpdatePropertyValues['AGENT_CLIENT_USER']);
                $arEventFields = array(
                    'FIO' => $sUserName,
                    'ID' => $arRequest['ID'],
                    'URL' => $GLOBALS['host'] . '/profile/?uid=' . $USER->GetID(),
                    'LIST' => $sList,
                    'EMAIL' => $arUser['EMAIL'],
                );
                CEvent::Send('ADD_NEW_PAIR', 's1', $arEventFields);
            }
            if (!empty($arUpdatePropertyValues['DEAL_REFERER'])) {
                $arUsers = is_array($arUpdatePropertyValues['DEAL_REFERER']) ? $arUpdatePropertyValues['DEAL_REFERER'] : [$arUpdatePropertyValues['DEAL_REFERER']];

                foreach ($arUsers as $iUseID) {
                    $arUser = rrsIblock::getUserInfo($arUpdatePropertyValues['AGENT_CLIENT_USER']);
                    $arEventFields = array(
                        'FIO' => $sUserName,
                        'ID' => $arRequest['ID'],
                        'URL' => $GLOBALS['host'] . '/profile/?uid=' . $USER->GetID(),
                        'LIST' => $sList,
                        'EMAIL' => $arUser['EMAIL'],
                    );
                    CEvent::Send('ADD_NEW_PAIR', 's1', $arEventFields);
                }
            }*/

            if (!empty($ID)) {
                //отправляем уведомления организаторам

                //получаем дополнительные данные - покупателя, поставщика из пары и их организаторов
                $sClientCompanyName = reset(client::getUserCompanyNames(array($arRequest['CLIENT_ID'])));
                $sClientPhone = reset(client::getPhoneList(array($arRequest['CLIENT_ID'])));
                if($sClientPhone != ''){
                    $sClientPhone = 'Телефон: ' . $sClientPhone . '<br/>';
                }
                $sClientName = getUserName($arRequest['CLIENT_ID']);
                $sClientData = "<br/>ФИО: {$sClientName}<br/>{$sClientPhone}<br/>";
                $sFarmerCompanyName = reset(farmer::getUserCompanyNames($arOffer['FARMER_ID']));
                $sFarmerPhone = reset(farmer::getPhoneList(array($arOffer['FARMER_ID'])));
                if($sFarmerPhone != ''){
                    $sFarmerPhone = 'Телефон: ' . $sFarmerPhone . '<br/>';
                }
                $sFarmerName = getUserName($arOffer['FARMER_ID']);
                $sFarmerData = "<br/>ФИО: {$sFarmerName}<br/>{$sFarmerPhone}<br/>";
                $sClientPartnerData = '';
                $iClientPartner = 0;
                if(!empty($arUpdatePropertyValues['DEAL_REFERER'])){
                    $iClientPartner = $arUpdatePropertyValues['DEAL_REFERER'];
                }else{
                    $arrTemp = client::getLinkedPartnerList($arRequest['CLIENT_ID'], true);
                    if(!empty($arrTemp[0])){
                        $iClientPartner = $arrTemp[0];
                    }
                }
                if($iClientPartner > 0) {
                    $arrClientPartnerData = partner::getPartnerInfo($iClientPartner);
                    if (!empty($arrClientPartnerData['NAME'])) {
                        $sPartnerPhone = $arrClientPartnerData['PHONE'];
                        if ($sPartnerPhone != '') {
                            $sPartnerPhone = 'Телефон: ' . $sPartnerPhone . '<br/>';
                        }
                        $sPartnerName = $arrClientPartnerData['NAME'];
                        $sClientPartnerData = "<br/>Данные организатора покупателя:<br/>ФИО: {$sPartnerName}<br/>{$sPartnerPhone}";
                    }
                }
                $sFarmerPartnerData = '';
                $iFarmerPartner = 0;
                if(!empty($arUpdatePropertyValues['COFFER_BY_PARTNER'])){
                    $iFarmerPartner = $arUpdatePropertyValues['COFFER_BY_PARTNER'];
                }else{
                    $arrTemp = farmer::getLinkedPartnerList($arOffer['FARMER_ID'], true);
                    if(!empty($arrTemp[0])){
                        $iFarmerPartner = $arrTemp[0];
                    }
                }
                if($iFarmerPartner > 0) {
                    $arrFarmerPartnerData = partner::getPartnerInfo($iFarmerPartner);
                    if (!empty($arrFarmerPartnerData['NAME'])) {
                        $sPartnerPhone = $arrFarmerPartnerData['PHONE'];
                        if ($sPartnerPhone != '') {
                            $sPartnerPhone = 'Телефон: ' . $sPartnerPhone . '<br/>';
                        }
                        $sPartnerName = $arrFarmerPartnerData['NAME'];
                        $sFarmerPartnerData = "<br/>Данные организатора поставщика:<br/>ФИО: {$sPartnerName}<br/>{$sPartnerPhone}";
                    }
                }
                $sFarmerNds = '';
                $sClientNds = '';
//                $sFarmerNds = ($arOffer['USER_NDS'] == 'yes' ? ' (с НДС)' : ' (без НДС)');
//                $sClientNds = ($arRequest['USER_NDS'] == 'yes' ? ' (с НДС)' : ' (без НДС)');

                /**
                 * отправляем админам
                 */
                $sUserInfo = "Предложение товара \"{$arOffer['CULTURE_NAME']}\" в объёме {$volume} т по цене «с места»{$sFarmerNds} {$arUpdatePropertyValues['ACC_PRICE_CSM']} руб/т, на складе \"{$arOffer['WH_NAME']}\" от \"{$sFarmerCompanyName}\" принято покупателем.<br/>";
                $arEventFields = array(
                    'FIO' => $sUserName,
                    'ID' => $arRequest['ID'],
                    'URL' => $GLOBALS['host'] . '/profile/?uid=' . $USER->GetID(),
                    'LIST' => $sList_admin,
                    'USERINFO' => $sUserInfo . $sFarmerData,
                );
                $arFilter = array('GROUPS_ID' => 1, 'ACTIVE' => 'Y');
                $res = CUser::GetList(($by = "id"), ($order = "asc"), $arFilter, array('FIELDS' => array('ID', 'EMAIL', 'ACTIVE', 'NAME', 'LAST_NAME', 'LOGIN')));
                while ($arUser = $res->Fetch()) {
//                    if(!isset($arSendedUsers[$arUser['ID']])) {
//                        $arSendedUsers[$arUser['ID']] = true;
                        $arEventFields['EMAIL'] = $arUser['EMAIL'];
                        CEvent::Send('ADD_NEW_PAIR', 's1', $arEventFields);
//                    }
                }

                $arrClientPartnersList = client::getLinkedPartnerList(!empty($arRequest['CLIENT_ID']) ? $arRequest['CLIENT_ID'] : 0);
                $arrFarmerPartnersList = farmer::getLinkedPartnerList(!empty($arOffer['FARMER_ID']) ? $arOffer['FARMER_ID'] : 0);
                $arrEmails = array();
                if(
                    count($arrClientPartnersList) > 0
                    || count($arrFarmerPartnersList) > 0
                ) {
                    $obRes = CUser::GetList(
                        ($by = 'ID'), ($order = 'ASC'),
                        array(
                            'ID' => implode(' | ', array_unique(array_merge($arrClientPartnersList, $arrFarmerPartnersList)))
                        ),
                        array('FIELDS' => array('ID', 'EMAIL'))
                    );
                    while ($arrData = $obRes->Fetch()) {
                        if (
                            $arrData['EMAIL']
                            && !checkEmailFromPhone($arrData['EMAIL'])
                        ) {
                            $arrEmails[$arrData['ID']] = $arrData['EMAIL'];
                        }
                    }
                }

                $arEventFields = array(
                    'FIO' => $sUserName,
                    'ID' => $arRequest['ID'],
                    'URL' => $sUrl,
                    'LIST' => $sList,
                    'USERINFO' => '',
                );
                $culture = culture::getName($arRequest['CULTURE_ID']);
                $farmer_wh_name = trim(farmer::getWHNameById($arOffer['WH_ID']));
                //партнерам поставщика
                $sUserInfo = "Предложение товара \"{$arOffer['CULTURE_NAME']}\" в объёме {$volume} т по цене «с места»{$sFarmerNds} {$arUpdatePropertyValues['ACC_PRICE_CSM']} руб/т, на складе \"{$arOffer['WH_NAME']}\" от \"{$sFarmerCompanyName}\" принято покупателем.<br/>";
                $message = 'Принято встречное предложение по товару "' . $culture['NAME'] . '" на складе "' . $farmer_wh_name . '"';
                if (!empty($arOffer['FARMER_ID'])) {
                    if (is_array($arrFarmerPartnersList)) {
                        foreach ($arrFarmerPartnersList as $partner_id) {
//                            if(!isset($arSendedUsers[$partner_id])) {
//                                $arSendedUsers[$partner_id] = true;

                                $partner_link = 'https://agrohelper.ru/partner/pair/?id=' . $ID;
                                notice::addNotice($partner_id, 'd', $message, $partner_link, '#' . $ID,
                                    array('SEND_USER' => $arOffer['FARMER_ID'], 'PAIR_ID' => $ID));

                                if(isset($arrEmails[$partner_id])){

                                    $arEventFields['USERINFO'] = $sUserInfo;
                                    //добавляем данные организатора покупателя
//                                    if($partner_id != $iClientPartner){
                                        $arEventFields['USERINFO'] .= $sClientPartnerData;
//                                    }
                                    $arEventFields['USERINFO'] .= $sFarmerData;

                                    $arEventFields['EMAIL'] = $arrEmails[$partner_id];
                                    CEvent::Send('ADD_NEW_PAIR', 's1', $arEventFields);
                                }
//                            }
                        }
                    }
                }
                //партнерам покупателя
                $arEventFields['USERINFO'] = '';
                $client_wh_name = '';
                if(
                    is_array($arRequest['COST'])
                    && count($arRequest['COST']) == 1
                ){
                    $arrTemp = reset($arRequest['COST']);
                    if(!empty($arrTemp['WH_NAME'])){
                        $client_wh_name = trim($arrTemp['WH_NAME']);
                    }
                }
                $sUserInfo = "Предложение товара \"{$arOffer['CULTURE_NAME']}\" в объёме {$volume} т по цене «с места»{$sFarmerNds} {$arUpdatePropertyValues['ACC_PRICE_CSM']} руб/т, на складе \"{$arOffer['WH_NAME']}\" от \"{$sFarmerCompanyName}\" принято покупателем \"{$sClientCompanyName}\" на складе \"{$client_wh_name}\" с ценой СРТ{$sClientNds} {$arUpdatePropertyValues['BASE_PRICE']} руб/т.<br/>";
                if (!empty($arRequest['CLIENT_ID'])) {
                    if (is_array($arrClientPartnersList)) {
                        foreach ($arrClientPartnersList as $partner_id) {
//                            if(!isset($arSendedUsers[$partner_id])) {
//                                $arSendedUsers[$partner_id] = true;
                                $partner_link = 'https://agrohelper.ru/partner/pair/?id=' . $ID;
                                notice::addNotice($partner_id, 'd', $message, $partner_link, '#' . $ID,
                                    array('SEND_USER' => $arRequest['CLIENT_ID'], 'PAIR_ID' => $ID));

                                if(isset($arrEmails[$partner_id])){

                                    $arEventFields['USERINFO'] = $sUserInfo;
                                    //добавляем данные организатора поставщика
//                                    if($partner_id != $iFarmerPartner){
                                        $arEventFields['USERINFO'] .= $sFarmerPartnerData;
//                                    }
                                    $arEventFields['USERINFO'] .= $sClientData;

                                    $arEventFields['EMAIL'] = $arrEmails[$partner_id];
                                    CEvent::Send('ADD_NEW_PAIR', 's1', $arEventFields);
                                }
//                            }
                        }
                    }
                }
            }

            setcookie('counter_request_add_success', 'y', time() + 60, '/');
            LocalRedirect('/client/pair/?offer_id=' . $arOffer['ID'] . '&request_id=' . $arRequest['ID']);
            exit;
        }
    }

    //получение встречных предложений покупателя
    $additional_filter = array();
    if(isset($_REQUEST['culture_id'])
        && is_numeric($_REQUEST['culture_id'])
        && $_REQUEST['culture_id'] > 0
    ){
        $additional_filter['culture_id'] = $_REQUEST['culture_id'];
    }
    if(isset($_REQUEST['warehouse_id'])
        && is_numeric($_REQUEST['warehouse_id'])
        && $_REQUEST['warehouse_id'] > 0
    ){
        $additional_filter['warehouse_id'] = $_REQUEST['warehouse_id'];
    }

    if(!empty($_GET['region_id'])){
        $clientWHs = array();
        //получаем склады по выбранному региону
        $res = CIBlockElement::GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('client_warehouse'),
                'ACTIVE' => 'Y',
                'PROPERTY_REGION' => $_GET['region_id'],
            ),
            false,
            false,
            array('ID')
        );
        while ($ob = $res->Fetch()) {
            $clientWHs[$ob['ID']] = 1;
        }

        //если установлен и регион и склад, то берем пересечение складов региона и выбранного склада (по умолчанию берем все склады региона)
        if(!isset($additional_filter['warehouse_id'])) {
            $additional_filter['UF_CLIENT_WH_ID'] = array_keys($clientWHs);
        }elseif(!isset($clientWHs[$additional_filter['warehouse_id']])){
            $additional_filter['warehouse_id'] = 0; //при выбранном регионе выбран склад из другого региона
        }
    }

    if(isset($arParams['AGENT_ID'])
        && isset($_GET['client_id'])
        && is_numeric($_GET['client_id'])
        && $_GET['client_id'] > 0
    ){
        $user_id = $_GET['client_id'];
    }

    if($arResult['MODE'] == 'agent'){
        $filters_count = array();
        $arResult['ITEMS'] = client::getCounterRequestData($user_id, $additional_filter, $filters_count,true);
        $arResult['FILTERS_COUNT'] = $filters_count;
    }else{
        $arResult['ITEMS'] = client::getCounterRequestData($user_id, $additional_filter);
    }

    //если установлены склад и культура, то выводим тип ндс покупателя для графика
    if(isset($additional_filter['culture_id'])
        && is_numeric($additional_filter['culture_id'])
        && $additional_filter['culture_id'] > 0
        && isset($additional_filter['warehouse_id'])
        && is_numeric($additional_filter['warehouse_id'])
        && $additional_filter['warehouse_id'] > 0
    ){
        //получаем тип ндс пользователя по складу из профиля пользователя
        $res = CIBlockElement::GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('client_warehouse'),
                'ID' => $additional_filter['warehouse_id'],
            ),
            false,
            array('nTopCount' => 1),
            array('PROPERTY_CLIENT')
        );
        if($data = $res->Fetch()){
            $res = CIBlockElement::GetList(
                array('ID' => 'ASC'),
                array(
                    'IBLOCK_ID' => rrsIblock::getIBlockId('client_profile'),
                    'PROPERTY_USER' => $data['PROPERTY_CLIENT_VALUE']
                ),
                false,
                array('nTopCount' => 1),
                array('PROPERTY_NDS.CODE')
            );
            if($data = $res->Fetch()){
                $arResult['USER_NDS'] = ($data['PROPERTY_NDS_CODE'] == 'Y');
            }
        }
    }

    $arResult['ADDITIONAL_DATA'] = array(); //дополнительные данные (прогноз сброса/прибавки, прогноз тарифа на перевозку, параметры качества)
    $arResult['OFFER_PARAMS'] = array(); //параметры товара (возможно будут отображаться за плату)

    if(count($arResult['ITEMS']) > 0){
        //ставим активными значения фильтра, если они не заданы
        if(!isset($_REQUEST['culture_id'])
            || !is_numeric($_REQUEST['culture_id'])
            || !isset($_REQUEST['warehouse_id'])
            || !is_numeric($_REQUEST['warehouse_id'])
        ){
            $culture_id = 0;
            $warehouse_id = 0;
            $client_id = 0;
            $region_id = 0;

            if(isset($_COOKIE['count_req_filter_culture'])
                && is_numeric($_COOKIE['count_req_filter_culture'])
                && isset($_COOKIE['count_req_filter_warehouse'])
                && is_numeric($_COOKIE['count_req_filter_warehouse'])
            ){
                $culture_id = $_COOKIE['count_req_filter_culture'];
                $warehouse_id = $_COOKIE['count_req_filter_warehouse'];
                if(isset($_COOKIE['count_req_filter_client'])
                    && is_numeric($_COOKIE['count_req_filter_client'])){
                    $client_id = $_COOKIE['count_req_filter_client'];
                }
                if(isset($_COOKIE['count_req_filter_region'])
                    && is_numeric($_COOKIE['count_req_filter_region'])){
                    $region_id = $_COOKIE['count_req_filter_region'];
                }
            }else{
                $temp_arr = reset($arResult['ITEMS']);
                if(!empty($temp_arr['UF_REQUEST_ID'])){
                    $arResult['ACTIVE_REQUEST_DATA'] = client::getRequestById($temp_arr['UF_REQUEST_ID']);

                    if(!isset($arResult['ACTIVE_REQUEST_DATA']['ID'])){
                        //первый запрос был удален или деактивирован (данной ситуации на основном сайте быть не должно,
                        // т.к. при деактивации/удалении запроса связанные встречные предложения удаляются)
                    }
                    if(isset($arResult['ACTIVE_REQUEST_DATA']['CULTURE_ID'])){
                        $culture_id = $arResult['ACTIVE_REQUEST_DATA']['CULTURE_ID'];
                    }
                    if(isset($arResult['ACTIVE_REQUEST_DATA']['COST'])
                        && is_array($arResult['ACTIVE_REQUEST_DATA']['COST'])
                    ){
                        foreach($arResult['ACTIVE_REQUEST_DATA']['COST'] as $cur_wh_id => $cur_data){
                            if($temp_arr['UF_CLIENT_WH_ID'] == $cur_wh_id) {
                                $warehouse_id = $cur_wh_id;
                                break;
                            }
                        }
                    }
                }
            }

            $url_params = array();
            if($culture_id > 0){
                setcookie('count_req_filter_culture', $culture_id, 0, '/'); //ставим куки фильтра на трое суток
                $url_params['culture_id'] = $culture_id;
            }
            if($warehouse_id > 0){
                setcookie('count_req_filter_warehouse', $warehouse_id, 0, '/'); //ставим куки фильтра на трое суток
                $url_params['warehouse_id'] = $warehouse_id;
            }
            if($region_id > 0) {
                setcookie('count_req_filter_region', $region_id, 0, '/'); //ставим куки фильтра на трое суток
                $url_params['region_id'] = $region_id;
            }
            if($client_id > 0){
                setcookie('count_req_filter_client', $client_id, 0, '/'); //ставим куки фильтра на трое суток
                $url_params['client_id'] = $client_id;
            }

            $new_url = http_build_query($url_params);
            if(
                isset($_GET)
                && $new_url != ''
                && $new_url != http_build_query($_GET)
            ){
                LocalRedirect((isset($arParams['AGENT_ID']) ? '/partner/client_exclusive_offers/' : '/client/exclusive_offers/') . (count($url_params) > 0 ? '?' . http_build_query($url_params) : ''));
                exit;
            }
        }

        //получаем дополнительные данные встречных предложений (прогноз сброса/прибавки, прогноз тарифа на перевозку, параметры качества)
        $arLeads = array();
        $arLeadsKeys = array(); //массив, ключи которого '<offer_id>_<offer_warehouse>_<request_id>_<request_warehouse>' (уникальный ключ для пары)

        $FILTER_OFFERS = array();   //число уникальных фильтров для предложений
        $client_wh_ids = array();   //склады клиента
        $wh_regions_ids = array();  //регионы складов
        $arFilter = array();
        $uids = array();
        foreach($arResult['ITEMS'] as $cur_counter_request) {
            $arFilter['UF_OFFER_ID'][] = $cur_counter_request['UF_OFFER_ID'];
            $arFilter['UF_REQUEST_ID'][] = $cur_counter_request['UF_REQUEST_ID'];
            $uids[$cur_counter_request['UF_CLIENT_ID']] = true;
            $client_wh_ids[$cur_counter_request['UF_CLIENT_WH_ID']] = 1;

        }

        $rsWH = CIBlockElement::GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('client_warehouse'),
                'ACTIVE' => 'Y',
                'ID' => array_keys($client_wh_ids),
                'PROPERTY_ACTIVE' => rrsIblock::getPropListKey('client_warehouse', 'ACTIVE', 'yes')
            ),
            false,
            false,
            array(
                'ID',
                'NAME',
                'SORT',
                'PROPERTY_REGION',
                'PROPERTY_ADDRESS',
                'PROPERTY_MAP',
            )
        );
        while ($arWH = $rsWH->Fetch()) {
            $arResult['CLIENT_WH_LIST'][$arWH['ID']] = $arWH;
        }


        if($arResult['MODE'] == 'agent'){
            //получаем регионы из складов
            $res = CIBlockElement::GetList(
                array('ID' => 'ASC'),
                array(
                    'IBLOCK_ID' => rrsIblock::getIBlockId('client_warehouse'),
                    'ACTIVE' => 'Y',
                    'ID' => array_keys($client_wh_ids),
                ),
                false,
                false,
                array('ID','PROPERTY_REGION')
            );
            while ($ob = $res->Fetch()) {
                $wh_regions_ids[$ob['ID']] = $ob['PROPERTY_REGION_VALUE'];
            }
        }


        if(count($arFilter) > 0){
            $arLeads = lead::getLeadList($arFilter);
        }
        if(count($arLeads) > 0){
            foreach($arLeads as $cur_pos => $curLead){
                $arLeadsKeys[$curLead['UF_OFFER_ID'] . '_' . $curLead['UF_FARMER_WH_ID'] . '_' . $curLead['UF_REQUEST_ID'] . '_' . $curLead['UF_CLIENT_WH_ID']] = $cur_pos;
            }
            $offers_data = farmer::getOfferListByIDs($arFilter['UF_OFFER_ID']);
            $requests_data = client::getRequestListByIDs($arFilter['UF_REQUEST_ID'], false, true);
            $arCulturesGroup = culture::getCulturesGroup();
            $arAgrohelperTariffs = model::getAgrohelperTariffs();

            if(count($offers_data) > 0
                && count($requests_data) > 0
            ){
                $nds = rrsIblock::getConst('nds');
                $commissionVal = rrsIblock::getConst('commission');

                //параметры товара (примеси, стекловидность и т.д.)
                $arResult['OFFER_PARAMS'] = farmer::getParamsList($arFilter['UF_OFFER_ID']);

                //прогноз сброса, прогноз тарифа
                foreach($arResult['ITEMS'] as $cur_pos => $cur_counter_request){
                    $lead_key = $cur_counter_request['UF_OFFER_ID'] . '_' . $cur_counter_request['UF_FARMER_WH_ID']
                        . '_' . $cur_counter_request['UF_REQUEST_ID'] . '_' . $cur_counter_request['UF_CLIENT_WH_ID'];

                    if(isset($arLeadsKeys[$lead_key])
                        && isset($offers_data[$cur_counter_request['UF_OFFER_ID']])
                        && isset($requests_data[$cur_counter_request['UF_REQUEST_ID']])
                    ) {
                        $lead = $arLeads[$arLeadsKeys[$lead_key]];
                        $discount = deal::getDump($requests_data[$cur_counter_request['UF_REQUEST_ID']]['PARAMS'], $offers_data[$cur_counter_request['UF_OFFER_ID']]['PARAMS']);

                        if ($requests_data[$cur_counter_request['UF_REQUEST_ID']]['NEED_DELIVERY'] == 'N')
                            $type = 'fca';
                        else
                            $type = 'cpt';

                        //тариф всегда берется как fca, расчет всегда идет как dap
                        $tarif = client::getTarif($requests_data[$cur_counter_request['UF_REQUEST_ID']]['CLIENT_ID'], $arCulturesGroup[$requests_data[$cur_counter_request['UF_REQUEST_ID']]['CULTURE_ID']], 'fca', $lead['UF_CENTER_ID'], $lead['UF_ROUTE'], $arAgrohelperTariffs);
                        $arTariffRange = client::getTariffRange($lead['UF_ROUTE'], $arAgrohelperTariffs);

                        $best_price_data = lead::makeBaseFromCSM($cur_counter_request['UF_FARMER_PRICE'], $requests_data[$cur_counter_request['UF_REQUEST_ID']]['USER_NDS'] == 'yes', $offers_data[$cur_counter_request['UF_OFFER_ID']]['USER_NDS'] == 'yes', $discount, $tarif, array('delivery_type' => 'cpt', 'get_base_client' => true, 'nds' => $nds, 'comissionVal' => $commissionVal), true);

                        $arResult['ADDITIONAL_DATA'][$cur_pos] = array(
                            'CULTURE_ID'   => $requests_data[$cur_counter_request['UF_REQUEST_ID']]['CULTURE_ID'],
                            'CULTURE_NAME' => $requests_data[$cur_counter_request['UF_REQUEST_ID']]['CULTURE_NAME'],
                        );

                        if(isset($best_price_data['CSM_FOR_CLIENT']['SBROS_RUB'])){
                            $arResult['ADDITIONAL_DATA'][$cur_pos]['DUMP_RUB'] = $best_price_data['CSM_FOR_CLIENT']['SBROS_RUB'];
                        }

                        if(isset($best_price_data['BASE_PRICE'])){
                            $arResult['ADDITIONAL_DATA'][$cur_pos]['BASE_PRICE'] = $best_price_data['BASE_PRICE'];

                            if(isset($requests_data[$cur_counter_request['UF_REQUEST_ID']]['COST'][$cur_counter_request['UF_CLIENT_WH_ID']]['DDP_PRICE_CLIENT'])){
                                $arResult['ADDITIONAL_DATA'][$cur_pos]['CLIENT_BASE_PRICE'] = $requests_data[$cur_counter_request['UF_REQUEST_ID']]['COST'][$cur_counter_request['UF_CLIENT_WH_ID']]['DDP_PRICE_CLIENT'];
                                $temp_client_base_price = $arResult['ADDITIONAL_DATA'][$cur_pos]['CLIENT_BASE_PRICE'];
                                $temp_farmer_base_price = $arResult['ADDITIONAL_DATA'][$cur_pos]['BASE_PRICE'];
//                                $difference_nds = false;
                                if($cur_counter_request['UF_NDS_CLIENT'] == 'no'
                                    && $cur_counter_request['UF_NDS_FARMER'] == 'yes'
                                ){
                                    //добавляем НДС к цене
                                    $temp_client_base_price = $temp_client_base_price + ($temp_client_base_price * 0.01 * $nds);
                                    $temp_farmer_base_price = $best_price_data['BASE_CONTR_PRICE'];
//                                    $difference_nds = true;
                                }elseif($cur_counter_request['UF_NDS_CLIENT'] == 'yes'
                                    && $cur_counter_request['UF_NDS_FARMER'] == 'no'
                                ){
                                    //вычитаем НДС из цены
                                    $temp_client_base_price = $temp_client_base_price / (1 + 0.01 * $nds);
                                    $temp_farmer_base_price = $best_price_data['BASE_CONTR_PRICE'];
//                                    $difference_nds = true;
                                }
                                $arResult['ADDITIONAL_DATA'][$cur_pos]['DIFFERENCE'] = round($temp_farmer_base_price) - round($temp_client_base_price);
//                                if($difference_nds){
//                                    $arResult['ADDITIONAL_DATA'][$cur_pos]['DIFFERENCE'] *= -1; //меняем знак, если базисная цена покупателя менялась (приводилась в соответствие с НДС поставщика)
//                                }
                            }
                        }

                        if(isset($best_price_data['BASE_CONTR_PRICE'])){
                            $arResult['ADDITIONAL_DATA'][$cur_pos]['BASE_CONTR_PRICE'] = $best_price_data['BASE_CONTR_PRICE'];
                        }

                        if(isset($best_price_data['CSM_FOR_CLIENT']['UF_CSM_PRICE'])){
                            $arResult['ADDITIONAL_DATA'][$cur_pos]['CSM_FOR_CLIENT_VALUE'] = $best_price_data['CSM_FOR_CLIENT']['UF_CSM_PRICE'];
                        }

                        //если типы НДС поставщика и покупателя разнятся, то сохраняем также данные для поставщика
                        if(isset($cur_counter_request['UF_NDS_FARMER'])
                            && isset($cur_counter_request['UF_NDS_CLIENT'])
                            && $cur_counter_request['UF_NDS_FARMER'] != $cur_counter_request['UF_NDS_CLIENT']
                        ){
                            $arResult['ADDITIONAL_DATA'][$cur_pos]['FARMER_DUMP_RUB'] = -1 * $best_price_data['DUMP_RUB'];
                            $arResult['ADDITIONAL_DATA'][$cur_pos]['FARMER_BASE_CONTR_PRICE'] = $best_price_data['BASE_CONTR_PRICE'];
                            $arResult['ADDITIONAL_DATA'][$cur_pos]['FARMER_CSM_PRICE'] = $cur_counter_request['UF_FARMER_PRICE'];
                        }

                        $arResult['ADDITIONAL_DATA'][$cur_pos]['TARIF'] = $tarif;
                        $arResult['ADDITIONAL_DATA'][$cur_pos]['TARIFF_RANGE'] = $arTariffRange;

                        if($arResult['MODE'] == 'agent') {
                            //для партнера формируем для каждого предложение значение фильтра для него, и собираем в массив все варианты подобных фильтров
                            $reg_id = 0;
                            if (isset($wh_regions_ids[$cur_counter_request['UF_CLIENT_WH_ID']])) {
                                $reg_id = $wh_regions_ids[$cur_counter_request['UF_CLIENT_WH_ID']];
                            }
                            $tmp_filter = array(
                                'region_id' => $reg_id,
                                'client_id' => $cur_counter_request['UF_CLIENT_ID'],
                                'wh_id' => $cur_counter_request['UF_CLIENT_WH_ID'],
                                'culture_id' => $arResult['ADDITIONAL_DATA'][$cur_pos]['CULTURE_ID']
                            );
                            if(($tmp_filter['region_id']>0)&&($tmp_filter['client_id']>0)&&($tmp_filter['wh_id']>0)&&($tmp_filter['culture_id']>0)){
                                $FILTER_OFFERS[$tmp_filter['client_id'].'|'.$tmp_filter['culture_id'].'|'.$tmp_filter['wh_id']] = array('FILTER'=>$tmp_filter);
                                $arResult['ITEMS'][$cur_pos]['filter'] = $tmp_filter['client_id'].'|'.$tmp_filter['culture_id'].'|'.$tmp_filter['wh_id'];
                            }
                        }
                    }
                }
            }
        }
        if($arResult['MODE'] == 'agent') {
            //для уникальных значений фильтров получаем кол-во предложений по каждому
            foreach ($FILTER_OFFERS as $k => $item_filter) {
                if(isset($arResult['FILTERS_COUNT'][$k])){
                    $FILTER_OFFERS[$k]['COUNT'] = $arResult['FILTERS_COUNT'][$k];
                    $FILTER_OFFERS[$k]['LINK'] = '/partner/client_exclusive_offers/?region_id=' . $item_filter['FILTER']['region_id'] . '&client_id=' . $item_filter['FILTER']['client_id'] . '&culture_id=' . $item_filter['FILTER']['culture_id'] . '&warehouse_id=' . $item_filter['FILTER']['wh_id'];
                }
            }
            $arResult['FILTER_OFFERS'] = $FILTER_OFFERS;
        }

        //вспомогательные данные для $arResult['OFFER_PARAMS']
        $el_obj = new CIBlockElement;
        $res = $el_obj->GetList(
            array('SORT' => 'ASC', 'ID' => 'ASC'),
            array('IBLOCK_ID' => rrsIblock::getIBlockId('quality'), 'ACTIVE' => 'Y'),
            false,
            false,
            array('ID', 'NAME', 'PROPERTY_UNIT')
        );
        while ($ob = $res->Fetch()) {
            $arResult['UNIT_INFO'][$ob['ID']] = $ob['PROPERTY_UNIT_VALUE'];
        }

        $res = $el_obj->GetList(
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

        $res = $el_obj->GetList(
            array('ID' => 'DESC'),
            array('IBLOCK_ID' => rrsIblock::getIBlockId('characteristics'), 'ACTIVE' => 'Y'),
            false,
            false,
            array('ID', 'NAME', 'PROPERTY_CULTURE', 'PROPERTY_QUALITY', 'PROPERTY_QUALITY.NAME')
        );
        while ($ob = $res->Fetch()){
            $arResult['PARAMS_INFO'][$ob['PROPERTY_CULTURE_VALUE']][$ob['PROPERTY_QUALITY_VALUE']] = array(
                'ID' => $ob['ID'],
                'QUALITY_NAME' => $ob['PROPERTY_QUALITY_NAME']
            );
        }

        //получаем данные по пользователям для агента - авторизовывались ли пользотвалеи на сайте
        //также получаем данные заполненности обязательных полей профиля
        if($arResult['MODE'] == 'agent'
            && count($uids) > 0
        ){
            //проверка авторизовывались ли пользотвалеи на сайте
            $arResult['USERS_NOT_LOGIN'] = array();
            $u_obj = new CUser;
            $res = $u_obj->GetList(
                ($by = 'id'), ($order = 'asc'),
                array(
                    'ID' => implode(' | ', array_keys($uids)),
                    'UF_FIRST_LOGIN' => 1
                ),
                array('FIELDS' => array('ID'))
            );
            while($ob = $res->Fetch()){
                $arResult['USERS_NOT_LOGIN'][$ob['ID']] = true;
            }

            //получение данных заполненности обязательных полей профиля
            $agentObj = new agent();
            $arResult['CLIENTS_PROFILE_DONE'] = $agentObj->getClientsRegistrationRights(array_keys($uids));
        }
    }
    //проверка на наличие предожений при текущем фильтре
    elseif(
        count($arrCheckClientsIds) > 0
        && client::isUsersCounterRequestsAvailable(array_keys($arrCheckClientsIds))
    ){
        //сброс фильтров и обновление страницы
        if(
            !empty($_COOKIE['count_req_filter_culture'])
            || !empty($_COOKIE['count_req_filter_warehouse'])
            || !empty($_COOKIE['count_req_filter_client'])
            || !empty($_COOKIE['count_req_filter_region'])
        ){
            setcookie('count_req_filter_culture', 0, -1, '/');
            setcookie('count_req_filter_warehouse', 0, -1, '/');
            setcookie('count_req_filter_client', 0, -1, '/');
            setcookie('count_req_filter_region', 0, -1, '/');

            LocalRedirect((isset($arParams['AGENT_ID']) ? '/partner/client_exclusive_offers/' : '/client/exclusive_offers/'));
            exit;
        }
    }


    if(isset($_REQUEST['culture_id']) && is_numeric($_REQUEST['culture_id'])){
        setcookie('count_req_filter_culture', $_REQUEST['culture_id'], 0, '/'); //ставим куки фильтра на трое суток
    }

    if(isset($_REQUEST['warehouse_id']) && is_numeric($_REQUEST['warehouse_id'])) {
        setcookie('count_req_filter_warehouse', $_REQUEST['warehouse_id'], 0, '/'); //ставим куки фильтра на трое суток
    }

    if(isset($_REQUEST['client_id']) && is_numeric($_REQUEST['client_id'])){
        setcookie('count_req_filter_client', $_REQUEST['client_id'], 0, '/'); //ставим куки фильтра на трое суток
    }

    if(isset($_REQUEST['region_id']) && is_numeric($_REQUEST['region_id'])){
        setcookie('count_req_filter_region', $_REQUEST['region_id'], 0, '/'); //ставим куки фильтра на трое суток
    }

    //получаем данные для графиков (если будет кеширование, то перенести данные в кешируемый arResult ключ для component_epilog.php
    $arResult['UF_CENTER_ID'] = '';
    foreach($arLeads as $cur_lead){
        if(isset($cur_lead['UF_CENTER_ID'])
            && is_numeric($cur_lead['UF_CENTER_ID'])
        ){
            $arResult['UF_CENTER_ID'] = $cur_lead['UF_CENTER_ID'];
            break;
        }
    }
    //сортировка по разнице в цене-------------------------------
    $arResult['ITEMS_DIFF'] = array();
    foreach ($arResult['ITEMS'] as $cur_pos=>$item){
        if($arResult['ADDITIONAL_DATA'][$cur_pos]['DIFFERENCE']){
            $arResult['ITEMS_DIFF'][$cur_pos] = $arResult['ADDITIONAL_DATA'][$cur_pos]['DIFFERENCE'];
        }else{
            $arResult['ITEMS_DIFF'][$cur_pos] = 0;
        }
    }
    asort($arResult['ITEMS_DIFF']);
    $NEW_ADDITIONAL_DATA = array();
    $NEW_ITEMS = array();
    $i = 0;
    foreach($arResult['ITEMS_DIFF'] as $pos=>$dif){
        if(isset($arResult['ITEMS'][$pos])){
            $NEW_ITEMS[$i] = $arResult['ITEMS'][$pos];
        }
        if(isset($arResult['ADDITIONAL_DATA'][$pos])){
            $NEW_ADDITIONAL_DATA[$i] = $arResult['ADDITIONAL_DATA'][$pos];
        }
        $i++;
    }
    if((sizeof($NEW_ADDITIONAL_DATA)==sizeof($arResult['ADDITIONAL_DATA']))
        &&(sizeof($NEW_ITEMS)==sizeof($arResult['ITEMS']))){
        $arResult['ITEMS'] = $NEW_ITEMS;
        $arResult['ADDITIONAL_DATA'] = $NEW_ADDITIONAL_DATA;
    }
    //-------------------------------------------------------------------------

    //пагинация

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
    $elements_cnt = count($arResult['ITEMS']);
    $pages_cnt = ceil($elements_cnt / $arParams['NEWS_COUNT']);
    if($page_number > $pages_cnt){
        $page_number = $pages_cnt;
    }

    //определение нужной страницы элемента, для переадресации, если требуется
    $check_element = 0;
    if(isset($_GET['o'])
        && is_numeric($_GET['o'])
        && isset($_GET['r'])
        && is_numeric($_GET['r'])
        && count($arResult['ITEMS']) > 0
    ){
        $check_element = getCounterRequestIDByOfferAndRequest($_GET['o'], $_GET['r']);
        $my_c = 0;
        $found_elem = false;
        foreach($arResult['ITEMS'] as $cur_element){
            $my_c++;
            if($cur_element['ID'] == $check_element){
                $found_elem = true;
                break;
            }
        }

        //проверка соответствия текущей страницы и страницы элемента (если нужно развернуть данные элемента)
        if($found_elem){
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
    }
    elseif (!isset($_GET['o'])
        && isset($_REQUEST['warehouse_id'])
        && !empty($_REQUEST['warehouse_id'])
        && isset($_REQUEST['culture_id'])
        && !empty($_REQUEST['culture_id'])
        && count($arResult['ITEMS']) == 0) {
        // если предложение было удалено, но покупатель перешел по ссылке орга #13005
        setcookie('count_req_filter_culture', 0, 0, '/');
        setcookie('count_req_filter_warehouse', 0, 0, '/');
        LocalRedirect($APPLICATION->GetCurPageParam("warehouse_id=0&culture_id=0", ["warehouse_id", "culture_id", "r", "o"]));
    }

    //объект пагинации
    $nav = new \Bitrix\Main\UI\PageNavigation("page");
    $nav->allowAllRecords(false)
        ->setPageSize($arParams['NEWS_COUNT'])
        ->initFromUri();
    $nav->setRecordCount($elements_cnt);
    $nav->setCurrentPage($page_number);
    $arResult['NAV_OBJ'] = $nav;

    //получение первого активного элемента (предложения с первой страницы с лучшей ценой)
    $arResult['BEST_OFFER_DATA'] = array();
    for($i = 0; $i < count($arResult['ADDITIONAL_DATA']); $i++){
        if(isset($arResult['ITEMS'][$i])){
            if(isset($arResult['ITEMS'][$i]['UF_VOLUME_REMAINS'])
                && $arResult['ITEMS'][$i]['UF_VOLUME_REMAINS'] > 0
            ){
                $arResult['BEST_OFFER_DATA']['BASE_PRICE'] = $arResult['ADDITIONAL_DATA'][$i]['BASE_PRICE'];
                $arResult['BEST_OFFER_DATA']['WH'] = $arResult['ITEMS'][$i]['UF_CLIENT_WH_ID'];
                $arResult['BEST_OFFER_DATA']['NDS_TYPE'] = ($arResult['ITEMS'][$i]['UF_NDS_FARMER'] == 'yes' ? 'y' : 'n');
                $arResult['BEST_OFFER_DATA']['ID'] = $arResult['ITEMS'][$i]['ID'];
                $arResult['BEST_OFFER_DATA']['OFFER_ID'] = $arResult['ITEMS'][$i]['UF_OFFER_ID'];
                $arResult['BEST_OFFER_DATA']['REQUEST_ID'] = $arResult['ITEMS'][$i]['UF_REQUEST_ID'];
                break;
            }
        }
    }

    //ограничение вывода
    $arResult['ITEMS'] = deal::counterOffersGetPageElements($arResult['ITEMS'], $arParams['NEWS_COUNT'], $page_number);

    if($arResult['MODE'] == 'agent') {
        //проверяем выбраны ли все значение в фильтре (актуально только для партнера)
        $arResult['FULL_FILTER'] = false;
        if (((isset($_GET['region_id'])) && ($_GET['region_id']))
            && ((isset($_GET['client_id'])) && ($_GET['client_id']))
            && ((isset($_GET['culture_id'])) && ($_GET['culture_id']))
            && ((isset($_GET['warehouse_id'])) && ($_GET['warehouse_id']))) {
            $arResult['FULL_FILTER'] = true;
        }

        //получаем название организаций
        $user_ids = array();
        foreach($arResult['ITEMS'] as $cur_data){
            $user_ids[$cur_data['UF_CLIENT_ID']] = true;
        }
        if(count($user_ids) > 0) {
            $arResult['COMPANY_NAMES'] = client::getUsersCompanyList(array_keys($user_ids));
        }
    }

}
/**
 * Если это агент и установлен в фильтр база + культура
 */
$arResult['SET_URL_PAGE'] = false;
if($arResult['MODE'] == 'agent') {

    if(isset($additional_filter['culture_id'])
        && is_numeric($additional_filter['culture_id'])
        && $additional_filter['culture_id'] > 0
        && isset($additional_filter['warehouse_id'])
        && is_numeric($additional_filter['warehouse_id'])
        && $additional_filter['warehouse_id'] > 0
    ) {
        $arResult['SET_URL_PAGE'] = true;
        $iUID                     = false;

        /**
         * Если задан UID то передаем, если нет, то определяем по Базе
         */
        if(isset($_REQUEST['client_id'])
            && is_numeric($_REQUEST['client_id'])
            && $_REQUEST['client_id'] > 0) {

            $iUID = $_REQUEST['client_id'];
        }
        else {
            $iUID = client::getClientByWH($additional_filter['warehouse_id']);

        }
        if($iUID > 0) {
            $arNames = culture::getName($additional_filter['culture_id']);
            $arResult['URL_PAGE_PARAMS'] = [
                'WH_ID'        => $additional_filter['warehouse_id'],
                'WH_NAME'      => client::getWHNameById($additional_filter['warehouse_id'])?: '-',
                'CULTURE_ID'   => $additional_filter['culture_id'],
                'CULTURE_NAME' => $arNames['NAME']?: '-',
                'USER_EMAIL'   => $arResult['USERS_EMAIL'][$iUID],
                'USER_ID'      => $iUID,
                'PAGE'         => $_GET['page']
            ];
        }
        else {
            $arResult['SET_URL_PAGE'] = false;
        }

    }

}


$this->includeComponentTemplate();