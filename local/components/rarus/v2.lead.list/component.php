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

$arResult['USER_DEALS_RIGHTS'] = farmer::checkDealsRights($arParams['FARMER_ID']);

//создание сделки
if (!empty($_REQUEST['accept'])) {

    // Запуск транзакции
    $DB->StartTransaction();

    try {

        // Проверяем сессию
        if(!check_bitrix_sessid()) {
            throw new Exception('Ваша сессия истекла');
        }

        /**
         * Параметры
         */
        $offer_id       = intval($_REQUEST['offer']);
        $request_id     = intval($_REQUEST['request']);
        $warehouse_id   = intval($_REQUEST['warehouse']);
        $volume         = trim($_REQUEST['volume']);

        /**
         * Объекты
         */
        $oElement = new CIBlockElement();
        $agentObj = new agent();


        if(empty($volume)) {
            throw new Exception('Не задан объем');
        }

        //получение пары запрос-пердложение
        $arLead = lead::getLead($arParams['FARMER_ID'], $request_id, $offer_id);

        $arLead['ID'] = intval($arLead['ID']);
        if(empty($arLead['ID'])) {
            throw new Exception('Не удалось получить "Пара запрос-пердложение"');
        }

        //получение детальной информации о запросе покупателя
        $arRequest = client::getRequestById($request_id);

        if(!(count($arResult['USER_DEALS_RIGHTS']) == 0
            || (count($arResult['USER_DEALS_RIGHTS']) == 1
                && $arResult['USER_DEALS_RIGHTS']['fin'] == 'no_p'
                && $arRequest['PAYMENT'] == 'post')
        )) {
            throw new Exception('Не хвататет прав для совершения сделки');
        }

        $remains0 = $arRequest['REMAINS'];
        if ($remains0 < $volume) {
            throw new Exception('Данный объем не требуется. Проверьте правильность указанного объема');
        }


        //обновление остатка в запросе покупателя
        $remains = $remains0 - $volume;
        $prop = array('REMAINS' => $remains);
        if ($remains == 0) {
            $prop['ACTIVE'] = rrsIblock::getPropListKey('client_request', 'ACTIVE', 'no');
            logRequestDeactivating($arRequest['ID']); //пишем лог о деактивации запроса
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
        $arAgent = array_shift($agentObj->getAgentsByFarmers($arParams['FARMER_ID']));

        if(!empty($arAgent['ID'])) {

            $arUpdatePropertyValues['AGENT_USER'] = $arAgent['ID'];

            // Вознаграждение агенту
            if(empty($arAgent['REWARD_PERCENT'])) {
                $arAgent['REWARD_PERCENT'] = rrsIblock::getConst('REWARD_PERCENT_AGENT');
            }
            $arUpdatePropertyValues['REWARD_PERCENT_AGENT'] = $arAgent['REWARD_PERCENT'];

            // Вознаграждение агенту за транспортировку
            if(empty($arAgent['PERCENT_TRANSPORTATION'])) {
                $arAgent['PERCENT_TRANSPORTATION'] = rrsIblock::getConst('REWARD_PERCENT_TRANSPORTATION_AGENT');
            }
            $arUpdatePropertyValues['REWARD_PERCENT_TRANSPORTATION_AGENT'] = $arAgent['PERCENT_TRANSPORTATION'];
        }

        // Агент клиента
        $clientAgent = $agentObj->getProfileByClientID($arRequest['CLIENT_ID']);

        // Текущий процент вознаграждения агента клиента
        if(!empty($clientAgent['USER']['ID'])) {
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
        if(!$ID) {
            throw new Exception('Не удалось добавить сделку: "'.$oElement->LAST_ERROR.'"');
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
                'URL' => $GLOBALS['host'].$url,
                'EMAIL' => $clientProfile['USER']['EMAIL'],
            );
            CEvent::Send('CLIENT_CREATE_NEW_DEAL', 's1', $arEventFields);
        }
        if (in_array($noticeList['c_d']['ID'], $clientProfile['PROPERTY_NOTICE_VALUE'])) {
            notice::addNotice($clientProfile['USER']['ID'], 'd', 'Новая сделка', $url, '#' . $ID);
        }
        if (in_array($noticeList['s_d']['ID'], $clientProfile['PROPERTY_NOTICE_VALUE']) && $clientProfile['PROPERTY_PHONE_VALUE']) {
            $phone = str_replace(array('+', ' ', '(', ')', '-'), "", $clientProfile['PROPERTY_PHONE_VALUE']);
            notice::sendNoticeSMS($phone, 'Новая сделка: '.$GLOBALS['host'].$url);
        }

        //уведомления агенту покупателя

        /*$url = '/client_agent/deals/' . $ID . '/';
        if(isset($clientAgent['DEALS_RIGHTS']) && $clientAgent['DEALS_RIGHTS']){
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
        }*/

        //уведомления организатору
        $partnerProfile = partner::getProfile($partnerId, true);
        $url = '/partner/deals/' . $ID . '/';

        if (in_array($noticeList['e_d']['ID'], $partnerProfile['PROPERTY_NOTICE_VALUE'])) {
            $arEventFields = array(
                'REQUEST_ID' => $arRequest['ID'],
                'CULTURE' => $culture['NAME'],
                'VOLUME' => $volume,
                'ID' => $ID,
                'URL' => $GLOBALS['host'].$url,
                'EMAIL' => $partnerProfile['USER']['EMAIL'],
            );
            CEvent::Send('PARTNER_CREATE_NEW_DEAL', 's1', $arEventFields);
        }
        if (in_array($noticeList['c_d']['ID'], $partnerProfile['PROPERTY_NOTICE_VALUE'])) {
            notice::addNotice($partnerProfile['USER']['ID'], 'd', 'Новая сделка', $url, '#' . $ID);
        }
        if (in_array($noticeList['s_d']['ID'], $partnerProfile['PROPERTY_NOTICE_VALUE']) && $partnerProfile['PROPERTY_PHONE_VALUE']) {
            $phone = str_replace(array('+', ' ', '(', ')', '-'), "", $partnerProfile['PROPERTY_PHONE_VALUE']);
            notice::sendNoticeSMS($phone, 'Новая сделка: '.$GLOBALS['host'].$url);
        }

        if ($remains == 0) {
            $fca_dap = ($arRequest['NEED_DELIVERY'] == 'Y')?'CPT':'FCA';
            $REQ_DATA = $culture['NAME'] ." (".$fca_dap."), ".$arRequest['VOLUME'].' т, '.client::getCostWHNames($arRequest['ID']);

            //уведомление покупателя
            $url = '/client/request/new/?id='. $arRequest['ID'];
            if (in_array($noticeList['e_r']['ID'], $clientProfile['PROPERTY_NOTICE_VALUE'])) {
                $arEventFields = array(
                    'REQ_DATA' => $REQ_DATA,
                    'ID' => $arRequest['ID'],
                    'URL' => $GLOBALS['host'].$url,
                    'EMAIL' => $clientProfile['USER']['EMAIL'],
                );
                CEvent::Send('CLIENT_REQUEST_NO_VOLUME', 's1', $arEventFields);
            }
            if (in_array($noticeList['c_r']['ID'], $clientProfile['PROPERTY_NOTICE_VALUE'])) {
                notice::addNotice($clientProfile['USER']['ID'], 'r', 'Объем по запросу исчерпан', $url, '#' . $arRequest['ID']);
            }
            if (in_array($noticeList['s_r']['ID'], $clientProfile['PROPERTY_NOTICE_VALUE']) && $clientProfile['PROPERTY_PHONE_VALUE']) {
                $phone = str_replace(array('+', ' ', '(', ')', '-'), "", $clientProfile['PROPERTY_PHONE_VALUE']);
                notice::sendNoticeSMS($phone, 'Объем по запросу исчерпан: '.$GLOBALS['host'].$url);
            }

            $push_body = $REQ_DATA;
            $tokens = client::getPushTokens(array($clientProfile['USER']['ID']));

            if(isset($tokens[$clientProfile['USER']['ID']]) && count($tokens[$clientProfile['USER']['ID']]) > 0){
                foreach($tokens[$clientProfile['USER']['ID']] as $token){
                    Push::SendPush($token, $push_body, array( 'type' => 'request_completed', 'request_id' => $arRequest['ID'] ), 'Объем исчерпан');
                }
            }

            //уведомление агента покупателя
            /*if(isset($clientAgent['DEALS_RIGHTS']) && $clientAgent['DEALS_RIGHTS']){
                if (in_array($noticeList['e_r']['ID'], $clientAgent['PROPERTY_NOTICE_VALUE'])) {
                    $arEventFields = array(
                        'REQ_DATA' => $REQ_DATA,
                        'ID' => $arRequest['ID'],
                        'URL' => $GLOBALS['host'].'/client_agent/request/new/?id='. $arRequest['ID'],
                        'EMAIL' => $clientAgent['USER']['EMAIL'],
                    );
                    CEvent::Send('CLIENT_REQUEST_NO_VOLUME', 's1', $arEventFields);
                }
                if (in_array($noticeList['c_r']['ID'], $clientAgent['PROPERTY_NOTICE_VALUE'])) {
                    notice::addNotice($clientAgent['USER']['ID'], 'r', 'Объем по запросу исчерпан', $url, '#' . $arRequest['ID']);
                }
            }*/
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


        $DB->Commit();

        LocalRedirect('/farmer/deals/' . $ID . '/');
        die();

    } catch (Exception $e) {
        // Откат изменений
        $DB->Rollback();
        $arResult["MESSAGE"] = $e->getMessage();
    }
}
elseif (!empty($_REQUEST['reject'])) {
    //отклонение запроса приводит к удалению пары
    $arLeads[] = lead::getLead($arParams['FARMER_ID'], $_POST['request'], $_POST['offer']);
    if (is_array($arLeads) && sizeof($arLeads) > 0) {
        lead::deleteLeads($arLeads);
    }

    LocalRedirect($APPLICATION->GetCurPageParam(null, ['reject', 'request', 'offer']));
    die();
}

//получение пар запрос-товар
$arFilter = array(
    'UF_FARMER_ID' => $arParams['FARMER_ID']
);

if (intval($_GET['culture']) > 0) {
    $arFilter['UF_CULTURE_ID'] = intval($_GET['culture']);
}
if (intval($_GET['wh']) > 0) {
    $arFilter['UF_FARMER_WH_ID'] = intval($_GET['wh']);
}

$arLeads = lead::getLeadList($arFilter);

if (sizeof($arLeads) < 1) {
    $arResult['ERROR'] = "Ни одного запроса не найдено";
}

if (!$arResult['ERROR']) {
    $offerRequestApply = lead::createLeadList($arLeads);

    //сортировка запросов по культурам и по цене
    usort($offerRequestApply, "orderRcPrice");
    $offerRequestApply = deal::leadsSort($offerRequestApply);

    $arResult['ITEMS'] = $offerRequestApply;

    foreach ($offerRequestApply as $key => $item) {
        $requestWarehouseIds[$item['REQUEST']['BEST_PRICE']['WH_ID']] = true;
    }
    if (is_array($requestWarehouseIds) && sizeof($requestWarehouseIds) > 0) {
        $arResult['REQUEST_WAREHOUSES_LIST'] = client::getWarehouseParamsList(array_keys($requestWarehouseIds));
    }
}

$this->includeComponentTemplate();