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
/*
if (!empty($_REQUEST['accept'])) {
    $offer_id = $_REQUEST['offer'];
    $request_id = $_REQUEST['request'];
    $warehouse_id = $_REQUEST['warehouse'];
    $volume = $_REQUEST['volume'];

    if ($volume > 0) {
        //получение пары запрос-пердложение
        $arLead = lead::getLead($arParams['FARMER_ID'], $request_id, $offer_id);
        if (intval($arLead['ID']) > 0) {
            //получение детальной информации о запросе покупателя
            $arRequest = client::getRequestById($request_id);

            //проверка прав
            if (count($arResult['USER_DEALS_RIGHTS']) == 0
                || (count($arResult['USER_DEALS_RIGHTS']) == 1
                    && $arResult['USER_DEALS_RIGHTS']['fin'] == 'no_p'
                    && $arRequest['PAYMENT'] == 'post')
            ) {
                $remains0 = $arRequest['REMAINS'];
                if ($remains0 >= $volume) {
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

                    //расчет цен БЦ, РЦ, ЦСМ (CPT/FCA)
                    $price = farmer::bestPriceCalculation(
                        $warehouse_id,
                        $arCost['CENTER'],
                        $arLead['UF_ROUTE'],
                        $arCost['DDP_PRICE_CLIENT'],
                        $arOffer['USER_NDS'],
                        $arRequest['USER_NDS'],
                        $type,
                        $dumpValue
                    );

                    //заполнение свойств
                    $oElement = new CIBlockElement();
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

                    $arUpdateValues['PROPERTY_VALUES'] = $arUpdatePropertyValues;

                    if (!$ID = $oElement->Add($arUpdateValues)) {
                        $arResult['MESSAGE'] = $oElement->LAST_ERROR;

                        //возврат остатка, если сделку создать не удалось
                        $prop = array(
                            'REMAINS' => $remains0,
                            'ACTIVE' => rrsIblock::getPropListKey('client_request', 'ACTIVE', 'yes')
                        );
                        CIBlockElement::SetPropertyValuesEx($arRequest['ID'], rrsIblock::getIBlockId('client_request'), $prop);
                    }
                    else {
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

                        LocalRedirect('/farmer/deals/' . $ID . '/');
                    }
                }
                else {
                    $arResult["MESSAGE"] = 'Данный объем не требуется. Проверьте правильность указанного объема';
                }
            }
            else {
                $arResult["MESSAGE"] = 'Не хвататет прав для совершения сделки';
            }
        }
        else {
            $arResult["MESSAGE"] = 'Ошибка! Запрос не найден';
        }
    }
}
elseif (!empty($_REQUEST['reject'])) {
    //отклонение запроса приводит к удалению пары
    $arLeads[] = lead::getLead($arParams['FARMER_ID'], $_POST['request'], $_POST['offer']);
    if (is_array($arLeads) && sizeof($arLeads) > 0) {
        lead::deleteLeads($arLeads);
    }

    LocalRedirect('/farmer/request/');
}*/

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