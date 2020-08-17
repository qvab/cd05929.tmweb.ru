<?
/*
 * Класс для работы cо сделками
 */

class Deals {
    public static function Exec($model, $data) {
        $headers['HTTP'] = 200;

        //проверка авторизованности пользователя
        $resultData = Auth::CheckAuthorize($data["x-auth-key"], $data["x-auth-timestamp"], $data["x-auth-token"]);

        if(isset($data['Test_user']) && $data['Test_user'] == 1){
            $resultData['USER_ID'] = 56;
        }

        if (intval($resultData['USER_ID']) > 0) {
            $data['userAccID'] = $resultData['USER_ID'];

            $resultData['ERROR'] = Agrohelper::getErrorMessage('OldAppMessage');

//            switch ($model) {
//                case 'post':
//                    $resultData = self::CreateDeal($data);
//                    $headers['location'] = $resultData['ID'];
//                    $outputData = $resultData;
//                    break;
//                default:
//                    $resultData['ERROR'] = Agrohelper::getErrorMessage('incorrectRequest');
//                    $headers['HTTP'] = 404;
//            }
        }

        if (sizeof($resultData['ERROR']) > 0) {
            $headers['location'] = '';
            $outputData = array('error' => $resultData['ERROR']);
        }

        return array('HEADERS' => $headers, 'DATA' => $outputData);
    }

    /**
     * Добавление новой сделки
     *
     * @access  public
     * @param   [] $data массив с полями
     * @return  [] идентификатор созданной сделки
     */
    public static function CreateDeal($data) {
        $data['offer_id'] =  trim($data['offer_id']);
        $data['request_id'] =  trim($data['request_id']);
        $data['volume'] =  trim($data['volume']);
        $data['delivery'] =  trim($data['delivery']);

        //проверка на наличие всех обязательных полей
        if ($data['offer_id'] == '' || $data['request_id'] == '' || $data['volume'] == '') {
            return array('ERROR' => Agrohelper::getErrorMessage('CreateDealNoInfo'));
        }

        if ($data['volume'] < 1) {
            return array('ERROR' => Agrohelper::getErrorMessage('CreateDealNoVolume'));
        }

        //получение пары запрос-товар
        $arFilter = array(
            'UF_FARMER_ID' => $data['userAccID'],
            'UF_REQUEST_ID' => $data['request_id'],
            'UF_OFFER_ID' => $data['offer_id']
        );
        $arLeads = lead::getLeadList($arFilter);

        if (sizeof($arLeads) < 1) {
            return array('ERROR' => Agrohelper::getErrorMessage('GetRequestError'));
        }

        $lead = $arLeads[0];

        if ($lead['UF_FARMER_ID'] != $data['userAccID']) {
            return array('ERROR' => Agrohelper::getErrorMessage('GetRequestPermissions'));
        }

        $request = client::getRequestById($data['request_id']);
        if ($request['ID'] < 1) {
            return array('ERROR' => Agrohelper::getErrorMessage('CreateDealNoRequest'));
        }

        $userDealsRights = farmer::checkDealsRights($data['userAccID']);
        $tmp = array_intersect(array('n1','n2','n3'), $userDealsRights);

        if (is_array($tmp) && sizeof($tmp) > 0
            || (in_array('no_p', $userDealsRights) && $request['PAYMENT'] == 'pre')
        ) {
            return array('ERROR' => Agrohelper::getErrorMessage('CreateDealNoDocs'));
        }

        $offer = farmer::getOfferById($data['offer_id']);
        if ($offer['ID'] < 1) {
            return array('ERROR' => Agrohelper::getErrorMessage('CreateDealNoOffer'));
        }

        if ($request['NEED_DELIVERY'] == 'Y' && !in_array($data['delivery'], array('b','c'))) {
            return array('ERROR' => Agrohelper::getErrorMessage('CreateDealNoDelivery'));
        }

        $remains0 = $request['REMAINS'];
        if ($data['volume'] > $remains0) {
            return array('ERROR' => Agrohelper::getErrorMessage('CreateDealVolumeError'));
        }

        //remains updating
        $remains = $remains0 - $data['volume'];
        $prop = array('REMAINS' => $remains);
        if ($remains == 0) {
            $prop['ACTIVE'] = rrsIblock::getPropListKey('client_request', 'ACTIVE', 'no');
            logRequestDeactivating($request['ID']); //пишем лог о деактивации запроса
        }
        CIBlockElement::SetPropertyValuesEx($request['ID'], rrsIblock::getIBlockId('client_request'), $prop);

        $arCost = $request['COST'][$lead['UF_CLIENT_WH_ID']];
        $deal = array(
            'OFFER' => $offer,
            'REQUEST' => $request
        );

        if ($deal['REQUEST']['NEED_DELIVERY'] == 'N')
            $type = 'fca';
        else
            $type = 'cpt';

        $dumpValue = deal::getDump($deal['REQUEST']['PARAMS'], $deal['OFFER']['PARAMS']);

        $arAgrohelperTariffs = model::getAgrohelperTariffs();
        $arCulturesGroup = culture::getCulturesGroup();

        $price = farmer::bestPriceCalculation(
            array(
                'CLIENT_ID' => $deal['REQUEST']['CLIENT_ID'],
                'CLIENT_WH_ID' => $lead['UF_CLIENT_WH_ID'],
                'CENTER' => $arCost['CENTER'],
                'ROUTE' => $lead['UF_ROUTE'],
                'DDP_PRICE_CLIENT' => $arCost['DDP_PRICE_CLIENT'],
                'CLIENT_NDS' => $deal['REQUEST']['USER_NDS'],
                'FARMER_NDS' => $deal['OFFER']['USER_NDS'],
                'TYPE' => $type,
                'DUMP' => $dumpValue,
                'TARIFF_LIST' => $arAgrohelperTariffs,
                'CULTURE_GROUP_ID' => $arCulturesGroup[$deal['REQUEST']['CULTURE_ID']]
            )
        );

        $oElement = new CIBlockElement();
        $arUpdateValues = $arUpdatePropertyValues = array();

        $arUpdateValues['IBLOCK_ID'] = rrsIblock::getIBlockId('deals_deals');
        $arUpdateValues['ACTIVE'] = 'Y';
        $arUpdateValues['NAME'] = date('d.m.Y H:i:s');
        $arUpdateValues['ACTIVE_FROM'] = date('d.m.Y H:i:s');

        $arUpdatePropertyValues['CULTURE'] = $deal['REQUEST']['CULTURE_ID'];
        $arUpdatePropertyValues['CLIENT'] = $deal['REQUEST']['CLIENT_ID'];
        $arUpdatePropertyValues['REQUEST'] = $deal['REQUEST']['ID'];
        $arUpdatePropertyValues['VOLUME_0'] = $deal['REQUEST']['REMAINS'];
        $arUpdatePropertyValues['CENTER'] = $price['CENTER'];
        $arUpdatePropertyValues['CLIENT_WAREHOUSE'] = $price['WH_ID'];
        $arUpdatePropertyValues['PARITY_PRICE'] = $arCost['PARITY_PRICE'];
        $arUpdatePropertyValues['A_NDS'] = ($deal['REQUEST']['USER_NDS'] == 'yes')?'Y':'N';
        $arUpdatePropertyValues['B_NDS'] = ($deal['OFFER']['USER_NDS'] == 'yes')?'Y':'N';
        $arUpdatePropertyValues['BASE_PRICE'] = round($price['BASE_PRICE'], 2);
        //$arUpdatePropertyValues['NDS_VAL'] = round($ndsValue, 2);
        $arUpdatePropertyValues['DUMP'] = $dumpValue;
        $arUpdatePropertyValues['ACC_PRICE'] = round($price['ACC_PRICE'], 2);
        $arUpdatePropertyValues['ROUTE'] = $price['ROUTE'];
        //$arUpdatePropertyValues['PRICE'] = round($price_acc_exw_comm, 2);
        $arUpdatePropertyValues['ACC_PRICE_CSM'] = round($price['ACC_PRICE_CSM'], 2);

        $arUpdatePropertyValues['FARMER'] = $deal['OFFER']['FARMER_ID'];
        $arUpdatePropertyValues['OFFER'] = $deal['OFFER']['ID'];
        $arUpdatePropertyValues['VOLUME'] = $data['volume'];
        $arUpdatePropertyValues['FARMER_WAREHOUSE'] = $deal['OFFER']['WH_ID'];
        $arUpdatePropertyValues['DELIVERY'] = rrsIblock::getPropListKey('deals_deals', 'DELIVERY', $data['delivery']);

        $partnerId = farmer::getPartnerIdByFarmer($deal['OFFER']['FARMER_ID']);
        $arUpdatePropertyValues['PARTNER'] = $partnerId;

        $arUpdatePropertyValues['STAGE'] = rrsIblock::getPropListKey('deals_deals', 'STAGE', 'new');
        $arUpdatePropertyValues['DATE_STAGE'] = date('d.m.Y H:i:s');
        $arUpdatePropertyValues['STATUS'] = rrsIblock::getPropListKey('deals_deals', 'STATUS', 'open');

        $arUpdateValues['PROPERTY_VALUES'] = $arUpdatePropertyValues;

        if (!$ID = $oElement->Add($arUpdateValues)) {
            //remains return
            $prop = array(
                'REMAINS' => $remains0,
                'ACTIVE' => rrsIblock::getPropListKey('client_request', 'ACTIVE', 'yes')
            );
            CIBlockElement::SetPropertyValuesEx($deal['REQUEST']['ID'], rrsIblock::getIBlockId('client_request'), $prop);
            return array('ERROR' => Agrohelper::getErrorMessage('CreateDealError'));
        }
        else {
            if ($remains == 0) {
                //удаление пар запрос-товар
                $filter = array(
                    'UF_REQUEST_ID' => $deal['REQUEST']['ID']
                );
                $arLeads = lead::getLeadList($filter);
                if (is_array($arLeads) && sizeof($arLeads) > 0) {
                    lead::deleteLeads($arLeads);
                }
            }

            log::addDealStatusLog($ID, 'new', 'Новая сделка');

            //create order deal for partner
            //$paramsInfo = culture::getParamsListByCultureId($deal['REQUEST']['CULTURE_ID']);

            //$fca_dap = ($deal['REQUEST']['NEED_DELIVERY'] == 'Y')?'CPT':'FCA';
            $culture = culture::getName($deal['REQUEST']['CULTURE_ID']);

            //send notice
            $noticeList = notice::getNoticeList();

            //to client
            $clientProfile = client::getProfile($deal['REQUEST']['CLIENT_ID'], true);
            $url = '/client/deals/' . $ID . '/';

            if (in_array($noticeList['e_d']['ID'], $clientProfile['PROPERTY_NOTICE_VALUE'])) {
                $arEventFields = array(
                    'REQUEST_ID' => $deal['REQUEST']['ID'],
                    'CULTURE' => $culture['NAME'],
                    'VOLUME' => $data['volume'],
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
            $agentObj = new agent();
            $url = '/client_agent/deals/' . $ID . '/';
            $clientAgent = $agentObj->getProfileByClientID($clientProfile['USER']['ID']);
            if(isset($clientAgent['DEALS_RIGHTS']) && $clientAgent['DEALS_RIGHTS']){
                if (in_array($noticeList['e_d']['ID'], $clientAgent['PROPERTY_NOTICE_VALUE'])) {
                    $arEventFields = array(
                        'REQUEST_ID' => $deal['REQUEST']['ID'],
                        'CULTURE' => $culture['NAME'],
                        'VOLUME' => $data['volume'],
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

            //to partner
            $partnerProfile = partner::getProfile($partnerId, true);
            $url = '/partner/deals/' . $ID . '/';

            if (in_array($noticeList['e_d']['ID'], $partnerProfile['PROPERTY_NOTICE_VALUE'])) {
                $arEventFields = array(
                    'REQUEST_ID' => $deal['REQUEST']['ID'],
                    'CULTURE' => $culture['NAME'],
                    'VOLUME' => $data['volume'],
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
                $fca_dap = ($deal['REQUEST']['NEED_DELIVERY'] == 'Y')?'CPT':'FCA';
                $REQ_DATA = $culture['NAME'] ." (".$fca_dap."), ".$deal['REQUEST']['VOLUME'].' т, '.client::getCostWHNames($deal['REQUEST']['ID']);

                $url = '/client/request/new/?id='. $deal['REQUEST']['ID'];

                if (in_array($noticeList['e_r']['ID'], $clientProfile['PROPERTY_NOTICE_VALUE'])) {
                    $arEventFields = array(
                        'REQ_DATA' => $REQ_DATA,
                        'ID' => $deal['REQUEST']['ID'],
                        'URL' => $GLOBALS['host'].$url,
                        'EMAIL' => $clientProfile['USER']['EMAIL'],
                    );
                    CEvent::Send('CLIENT_REQUEST_NO_VOLUME', 's1', $arEventFields);
                }
                if (in_array($noticeList['c_r']['ID'], $clientProfile['PROPERTY_NOTICE_VALUE'])) {
                    notice::addNotice($clientProfile['USER']['ID'], 'r', 'Объем по запросу исчерпан', $url, '#' . $deal['REQUEST']['ID']);
                }
                if (in_array($noticeList['s_r']['ID'], $clientProfile['PROPERTY_NOTICE_VALUE']) && $clientProfile['PROPERTY_PHONE_VALUE']) {
                    $phone = str_replace(array('+', ' ', '(', ')', '-'), "", $clientProfile['PROPERTY_PHONE_VALUE']);
                    notice::sendNoticeSMS($phone, 'Объем по запросу исчерпан: '.$GLOBALS['host'].$url);
                }


                $push_body = $REQ_DATA;
                $tokens = client::getPushTokens(array($deal['REQUEST']['CLIENT_ID']));

                if(isset($tokens[$deal['REQUEST']['CLIENT_ID']]) && count($tokens[$deal['REQUEST']['CLIENT_ID']]) > 0){
                    foreach($tokens[$deal['REQUEST']['CLIENT_ID']] as $token){
                        Push::SendPush($token, $push_body, array( 'type' => 'request_completed', 'request_id' => $deal['REQUEST']['ID'] ), 'Объем исчерпан');
                    }
                }
            }

            //calculating new parity price
            $arPrices = model::parityPriceCalculation($arCost['CENTER'], $deal['REQUEST']['CULTURE_ID']);
            if (is_array($arPrices) && sizeof($arPrices) > 0) {
                //saving new parity price
                $id = model::saveParityPrice($arCost['CENTER'], $deal['REQUEST']['CULTURE_ID'], $arPrices);
                if ($id > 0) {
                    //saving information of changing parity price to log
                    log::addParityPriceLog($arCost['CENTER'], $deal['REQUEST']['CULTURE_ID'], 'новая сделка', 'deal', $arPrices);
                }
            }

            $arFarmer = CUser::GetByID($deal['OFFER']['FARMER_ID'])->Fetch();

            $url = '/farmer/deals/' . $ID . '/';
            if ($arFarmer['UF_API_KEY'])
                $url .= '?dkey='.$arFarmer['UF_API_KEY'];
            $result = array(
                'url' => $GLOBALS['host'] . $url
            );
            return $result;
        }
    }
}
?>