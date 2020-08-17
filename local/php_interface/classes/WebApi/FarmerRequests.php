<?
/*
 * Класс для работы c запросами поставщика
 */

class FarmerRequests {
    public static function Exec($model, $data) {
        $headers['HTTP'] = 200;

        //проверка авторизованности пользователя
        $resultData = Auth::CheckAuthorize($data["x-auth-key"], $data["x-auth-timestamp"], $data["x-auth-token"]);
        if (intval($resultData['USER_ID']) > 0) {
            $data['userAccID'] = $resultData['USER_ID'];

            switch ($model) {
                case 'get':
                    if (intval($data['offer_id']) > 0 && intval($data['request_id']) > 0) {
                        //Получение информации о запросе
                        $resultData = self::GetRequest($data);
                    }
                    else {
                        //Получение списка запросов
                        $resultData = self::GetRequests($data);
                    }
                    $outputData = $resultData;
                    break;
                case 'post':
                    //Отклонение запроса
                    $resultData = self::DeleteRequest($data);
                    $outputData = array('success' => 1);
                    break;
                default:
                    $resultData['ERROR'] = Agrohelper::getErrorMessage('incorrectRequest');
                    $headers['HTTP'] = 404;
            }
        }

        if (sizeof($resultData['ERROR']) > 0) {
            $headers['location'] = '';
            $outputData = array('error' => $resultData['ERROR']);
        }

        return array('HEADERS' => $headers, 'DATA' => $outputData);
    }

    /**
     * Получение списка запросов
     *
     * @access  public
     * @param   [] $data массив с полями
     * @return  [] список запросов поставщика
     */
    public static function GetRequests($data) {
        //получение пар запрос-товар
        $arFilter = array(
            'UF_FARMER_ID' => $data['userAccID']
        );
        $arLeads = lead::getLeadList($arFilter);

        $offersIds = array();
        $arResult['COUNTER_REQUESTS_DATA'] = array();

        if (sizeof($arLeads) < 1) {
            return array('ERROR' => Agrohelper::getErrorMessage('GetRequestsNoRequests'));
        }

        $offerRequestApply = lead::createLeadList($arLeads);

        if (!is_array($offerRequestApply) || sizeof($offerRequestApply) < 1) {
            return array('ERROR' => Agrohelper::getErrorMessage('GetRequestsNoRequests'));
        }

        //сортировка запросов по культурам и по цене
        usort($offerRequestApply, "orderRcPrice");
        $offerRequestApply = deal::leadsSort($offerRequestApply);

        $result = array();
        $deliveryList = rrsIblock::getPropListKey('farmer_offer', 'DELIVERY');
        $transportList = rrsIblock::getElementList(rrsIblock::getIBlockId('transport_type'));
        $docList = rrsIblock::getElementList(rrsIblock::getIBlockId('need_docs'));

        foreach ($offerRequestApply as $key => $item) {
            $requestWarehouseIds[$item['REQUEST']['BEST_PRICE']['WH_ID']] = true;
            $offersIds[$item['OFFER']['ID']] = true;
        }
        if (is_array($requestWarehouseIds) && sizeof($requestWarehouseIds) > 0) {
            $requestWarehouses = client::getWarehouseParamsList(array_keys($requestWarehouseIds));
        }

        $arFarmer = CUser::GetByID($data['userAccID'])->Fetch();
        $url = $GLOBALS['host'].'/farmer/documents/';
        if ($arFarmer['UF_API_KEY'])
            $url .= '?dkey='.$arFarmer['UF_API_KEY'];

        //получение прав пользователя на создание встречного предложения
        $arResult['USER_RIGHTS'] = farmer::checkRights('counter_request', $data['userAccID']);
        //проверяем можно ли создать встречное предложение для данного запроса
        $counter_request_allow = true;
        $counter_request_text = '';
        $counter_request_type = '';
        $counter_request_href = '';
        if(!isset($arResult['USER_RIGHTS']['REQUEST_RIGHT'])
            || $arResult['USER_RIGHTS']['REQUEST_RIGHT'] != 'Y'){
            //нет прав
            $counter_request_allow = false;
            $counter_request_text = 'Необходимо добавить ИНН, чтобы принять запрос или отправить предложение';
            $counter_request_href = $GLOBALS['host'] . '/farmer/profile/' . ($arFarmer['UF_API_KEY'] ? '?dkey=' . $arFarmer['UF_API_KEY'] : '');
            $counter_request_type = 'ref';
        }elseif(count($offersIds) > 0){
            $arResult['COUNTER_REQUESTS_DATA'] = farmer::getCounterRequestsData(array_keys($offersIds));
        }

        $cultures = $whs = array();
        $arAgrohelperTariffs = model::getAgrohelperTariffs();
        foreach ($offerRequestApply as $key => $item) {
            $fca_dap = ($item['REQUEST']['NEED_DELIVERY'] == 'Y')?'CPT':'FCA';

            //проверяем можно ли создать встречное предложение для данного запроса
            if($counter_request_allow){
                if(!isset($arResult['COUNTER_REQUESTS_DATA'][$item['OFFER']['ID']])){
                    //можно отправить встречное предложение
                    $counter_request_text = 'Создать предложение';
                    $counter_request_href = $GLOBALS['host'] . '/farmer/request/counter/?offer_id=' . $item['OFFER']['ID'] . ($arFarmer['UF_API_KEY'] ? '&dkey=' . $arFarmer['UF_API_KEY'] : '');
                    $counter_request_type = 'but';
                }else{
                    //встречное предложение уже было направлено
                    $counter_request_text = 'Предложение направлено';
                    $counter_request_href = $GLOBALS['host'] . '/farmer/request/?r=' . $item['REQUEST']['ID'] . '&o=' . $item['OFFER']['ID'] . ($arFarmer['UF_API_KEY'] ? '&dkey=' . $arFarmer['UF_API_KEY'] : '');
                    $counter_request_type = 'ref';
                }
            }

            $tmp = array(
                'offer_id' => $item['OFFER']['ID'],
                'offer_date' => strtotime($item['OFFER']['DATE_CREATE']),
                'request_id' => $item['REQUEST']['ID'],
                'culture' => $item['REQUEST']['CULTURE_NAME'],
                'culture_id' => intval($item['REQUEST']['CULTURE_ID']),
                'acc_price' => number_format($item['REQUEST']['BEST_PRICE']['ACC_PRICE_CSM'], 0, ',', ' ').' руб/т',
                'base_price' => number_format($item['REQUEST']['BEST_PRICE']['BASE_PRICE'], 0, ',', ' ').' руб/т',
                'volume' => number_format($item['REQUEST']['REMAINS'], 0, '.', ' ').' т.',
                'delivery' => $fca_dap,
                'need_delivery' => false,
                'date' => strtotime($item['REQUEST']['DATE_CREATE']),
                'date_act' => strtotime($item['REQUEST']['DATE_ACTIVE_TO']),
                'wh_name' => $item['OFFER']['WH_NAME'],
                'wh_id' => intval($item['OFFER']['WH_ID']),
                'send_counter_request' => $counter_request_text,
                'counter_request_href' => $counter_request_href,
                'counter_request_type' => $counter_request_type
            );

            if ($arFarmer['UF_API_KEY']
                && $tmp['counter_request_href'] != ''
            ) {
                //бесшовная авторизация
                $tmp['counter_request_href'] .= '&dkey=' . $arFarmer['UF_API_KEY'];
            }

            $cultures[$item['REQUEST']['CULTURE_ID']] = $item['REQUEST']['CULTURE_NAME'];
            $whs[$item['OFFER']['WH_ID']] = $item['OFFER']['WH_NAME'] . ' (' . $item['OFFER']['WH_ADDRESS'] . ')';

            if ($item['REQUEST']['NEED_DELIVERY'] == 'Y') {
                $tmp['distance'] = number_format($item['REQUEST']['BEST_PRICE']['ROUTE'], 0, ',', ' ').' км';
                //$tmp['tarif'] = model::getTarif($item['REQUEST']['BEST_PRICE']['CENTER'], $item['REQUEST']['BEST_PRICE']['ROUTE']).' руб/т';
                $tmp['tarif'] = client::getTarif(0, 0, 'cpt', $item['REQUEST']['BEST_PRICE']['CENTER'], $item['REQUEST']['BEST_PRICE']['ROUTE'], $arAgrohelperTariffs).' руб/т';

            }

            if (sizeof($item['REQUEST']['DOCS']) > 0) {
                foreach ($item['REQUEST']['DOCS'] as $val) {
                    $tmp['docs'][] = $docList[$val]['NAME'];
                }
            }

            if ($item['REQUEST']['NEED_DELIVERY'] == 'Y') {
                $tmp['need_delivery'] = true;
                if (sizeof($requestWarehouses[$item['REQUEST']['BEST_PRICE']['WH_ID']]['TRANSPORT']) > 0) {
                    foreach ($requestWarehouses[$item['REQUEST']['BEST_PRICE']['WH_ID']]['TRANSPORT'] as $val) {
                        $tmp['transport'][] = $transportList[$val]['NAME'];
                    }
                }
            }

            if ($item['REQUEST']['PAYMENT'] == 'pre') {
                $tmp['payment'] = 'Предоплата';
            }
            else {
                $tmp['payment'] = 'Постоплата';
            }

            $tmp['accept'] = true;

            $result['requests'][] = $tmp;
        }

        $result['accept'] = true;

        foreach ($deliveryList as $item) {
            $result['delivery_list'][] = array(
                'code' => $item['XML_ID'],
                'text' => $item['VALUE'],
            );
        }

        asort($cultures);
        asort($whs);

        foreach ($cultures as $key => $item) {
            $result['cultures_list'][] = array(
                'id' => $key,
                'name' => $item,
            );
        }
        foreach ($whs as $key => $item) {
            $result['wh_list'][] = array(
                'id' => $key,
                'name' => $item,
            );
        }

        return $result;
    }

    /**
     * Получение информации о запросе
     *
     * @access  public
     * @param   [] $data массив с полями
     * @return  [] информация о запросе поставщика
     */
    public static function GetRequest($data) {
        $data['offer_id'] =  intval(trim($data['offer_id']));
        $data['request_id'] =  intval(trim($data['request_id']));

        //проверка на наличие всех обязательных полей
        if (!$data['offer_id'] || !$data['request_id'] ) {
            return array('ERROR' => Agrohelper::getErrorMessage('GetRequestNoInfo'));
        }

        //получение пары запрос-товар
        $arFilter = array(
            'UF_FARMER_ID' => $data['userAccID'],
            'UF_REQUEST_ID' => $data['request_id'],
            'UF_OFFER_ID' => $data['offer_id']
        );
        $arLeads = lead::getLeadList($arFilter);

        if ($arLeads[0]['UF_FARMER_ID'] != $data['userAccID']) {
            return array('ERROR' => Agrohelper::getErrorMessage('GetRequestPermissions'));
        }

        if (sizeof($arLeads) < 1) {
            return array('ERROR' => Agrohelper::getErrorMessage('GetRequestError'));
        }

        $offerRequestApply = lead::createLeadList($arLeads);

        if (!is_array($offerRequestApply) || sizeof($offerRequestApply) < 1) {
            return array('ERROR' => Agrohelper::getErrorMessage('GetRequestError'));
        }

        $result = array();
        $deliveryList = rrsIblock::getPropListKey('farmer_offer', 'DELIVERY');
        $transportList = rrsIblock::getElementList(rrsIblock::getIBlockId('transport_type'));
        $docList = rrsIblock::getElementList(rrsIblock::getIBlockId('need_docs'));
        $userDealsRights = farmer::checkDealsRights($data['userAccID']);

        foreach ($offerRequestApply as $key => $item) {
            $requestWarehouseIds[$item['REQUEST']['BEST_PRICE']['WH_ID']] = true;
        }
        if (is_array($requestWarehouseIds) && sizeof($requestWarehouseIds) > 0) {
            $requestWarehouses = client::getWarehouseParamsList(array_keys($requestWarehouseIds));
        }

        $item = $offerRequestApply[0];

        $fca_dap = ($item['REQUEST']['NEED_DELIVERY'] == 'Y')?'CPT':'FCA';

        $arAgrohelperTariffs = model::getAgrohelperTariffs();

        //получение прав пользователя на создание встречного предложения
        $arResult['USER_RIGHTS'] = farmer::checkRights('counter_request', $data['userAccID']);
        //проверяем можно ли создать встречное предложение для данного запроса
        $counter_request_text = '';
        $counter_request_type = '';
        $counter_request_href = '';
        if(!isset($arResult['USER_RIGHTS']['REQUEST_RIGHT'])
            || $arResult['USER_RIGHTS']['REQUEST_RIGHT'] != 'Y'){
            //нет прав
            $counter_request_text = 'Необходимо добавить ИНН, чтобы принять запрос или отправить предложение';
            $counter_request_href = $GLOBALS['host'] . '/farmer/profile/';
            $counter_request_type = 'ref';
        }else{
            //есть права, проверяем конкретный товар
            //получение данных о встречных предложениях
            $arResult['COUNTER_REQUESTS_DATA'] = farmer::getCounterRequestsData($data['offer_id']);

            if(!isset($arResult['COUNTER_REQUESTS_DATA'][$data['offer_id']])){
                //можно отправить встречное предложение
                $counter_request_text = 'Создать предложение';
                $counter_request_href = $GLOBALS['host'] . '/farmer/request/counter/?offer_id=' . $item['OFFER']['ID'];
                $counter_request_type = 'but';
            }else{
                //встречное предложение уже было направлено
                $counter_request_text = 'Предложение направлено';
                $counter_request_href = $GLOBALS['host'] . '/farmer/request/?r=' . $item['REQUEST']['ID'] . '&o=' . $item['OFFER']['ID'];
                $counter_request_type = 'ref';
            }
        }

        $tmp = array(
            'offer_id' => $item['OFFER']['ID'],
            'offer_date' => strtotime($item['OFFER']['DATE_CREATE']),
            'request_id' => $item['REQUEST']['ID'],
            'culture' => $item['REQUEST']['CULTURE_NAME'],
            'acc_price' => number_format($item['REQUEST']['BEST_PRICE']['ACC_PRICE_CSM'], 0, ',', ' ').' руб/т',
            'base_price' => number_format($item['REQUEST']['BEST_PRICE']['BASE_PRICE'], 0, ',', ' ').' руб/т',
            'volume' => number_format($item['REQUEST']['REMAINS'], 0, '.', ' ').' т.',
            'delivery' => $fca_dap,
            'need_delivery' => false,
            'date' => strtotime($item['REQUEST']['DATE_CREATE']),
            'date_act' => strtotime($item['REQUEST']['DATE_ACTIVE_TO']),
            'wh_name' => $item['OFFER']['WH_NAME'],
            'send_counter_request' => $counter_request_text,
            'counter_request_href' => $counter_request_href,
            'counter_request_type' => $counter_request_type
        );

        if ($item['REQUEST']['NEED_DELIVERY'] == 'Y') {
            $tmp['distance'] = number_format($item['REQUEST']['BEST_PRICE']['ROUTE'], 0, ',', ' ').' км';
            //$tmp['tarif'] = model::getTarif($item['REQUEST']['BEST_PRICE']['CENTER'], $item['REQUEST']['BEST_PRICE']['ROUTE']).' руб/т';
            $tmp['tarif'] = client::getTarif(0, 0, 'cpt', $item['REQUEST']['BEST_PRICE']['CENTER'], $item['REQUEST']['BEST_PRICE']['ROUTE'], $arAgrohelperTariffs).' руб/т';
        }

        if (sizeof($item['REQUEST']['DOCS']) > 0) {
            foreach ($item['REQUEST']['DOCS'] as $val) {
                $tmp['docs'][] = $docList[$val]['NAME'];
            }
        }

        if ($item['REQUEST']['NEED_DELIVERY'] == 'Y') {
            $tmp['need_delivery'] = true;
            if (sizeof($requestWarehouses[$item['REQUEST']['BEST_PRICE']['WH_ID']]['TRANSPORT']) > 0) {
                foreach ($requestWarehouses[$item['REQUEST']['BEST_PRICE']['WH_ID']]['TRANSPORT'] as $val) {
                    $tmp['transport'][] = $transportList[$val]['NAME'];
                }
            }
        }

        if ($item['REQUEST']['PAYMENT'] == 'pre') {
            $tmp['payment'] = 'Предоплата';
        }
        else {
            $tmp['payment'] = 'Постоплата';
        }

        $arFarmer = CUser::GetByID($data['userAccID'])->Fetch();

        $tmp['accept'] = true;

        if ($arFarmer['UF_API_KEY']
            && $tmp['counter_request_href'] != ''
        ) {
            //бесшовная авторизация
            if(substr($tmp['counter_request_href'], -1, 1) == '/'){
                $tmp['counter_request_href'] .= '?dkey=' . $arFarmer['UF_API_KEY'];
            }else{
                $tmp['counter_request_href'] .= '&dkey=' . $arFarmer['UF_API_KEY'];
            }
        }

        $result['request'] = $tmp;

        if ($item['REQUEST']['NEED_DELIVERY'] == 'Y') {
            foreach ($deliveryList as $item) {
                $result['delivery_list'][] = array(
                    'code' => $item['XML_ID'],
                    'text' => $item['VALUE'],
                );
            }
        }

        return $result;
    }

    /**
     * Отклонение запроса
     *
     * @access  public
     * @param   [] $data массив с полями
     * @return  bool true в случае успешного удаления запроса
     */
    public static function DeleteRequest($data) {
        $data['offer_id'] =  intval(trim($data['offer_id']));
        $data['request_id'] =  intval(trim($data['request_id']));

        //проверка на наличие всех обязательных полей
        if (!$data['offer_id'] || !$data['request_id'] ) {
            return array('ERROR' => Agrohelper::getErrorMessage('DeleteRequestNoInfo'));
        }

        $offer = farmer::getOfferById($data['offer_id']);
        if ($offer['ID'] < 1) {
            return array('ERROR' => Agrohelper::getErrorMessage('DeleteRequestNoOffer'));
        }
        elseif ($offer['FARMER_ID'] != $data['userAccID']) {
            return array('ERROR' => Agrohelper::getErrorMessage('DeleteRequestPermissions'));
        }

        $request = client::getRequestById($data['request_id']);
        if ($request['ID'] < 1) {
            return array('ERROR' => Agrohelper::getErrorMessage('DeleteRequestNoRequest'));
        }

        $arLeads[] = lead::getLead($data['userAccID'], $data['request_id'], $data['offer_id']);
        if (is_array($arLeads) && sizeof($arLeads) > 0) {
            lead::deleteLeads($arLeads);
            return true;
        }

        return false;

        /*if(!log::addRejectLead($data['userAccID'], $data['request_id'], $data['offer_id']))
            return false;

        return true;*/
    }
}
?>