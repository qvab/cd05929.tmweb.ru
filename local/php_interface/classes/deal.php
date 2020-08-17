<?
class deal {
    /**
     * Получение скидки по матрице сбросов
     * @param  [] $requestParams параметры запроса
     *         [] $offerParams параметры товара
     * @return number величина скидки
     */
    public static function getDump($requestParams, $offerParams) {
        $result = 0;
        foreach ($requestParams as $i => $param) {
            if (is_array($param['DUMPING']) && sizeof($param['DUMPING']) > 0) {
                $a = $param['BASE'];
                $b = $offerParams[$i]['BASE'];
                if ($a != $b) {
                    if ($param['DIRECT_DUMP'] == 'Y') {
                        $arDump = current($param['DUMPING']);
                        if ($b <= $arDump['MX'] && $b >= $arDump['MN']) {
                            $result += $arDump['DUMP'];
                        }
                    }
                    else {
                        if ($a < $b) {
                            $x1 = $a; $y1 = $b;
                        }
                        else {
                            $x1 = $b; $y1 = $a;
                        }

                        foreach ($param['DUMPING'] as $di) {
                            if ($di['MN'] < $di['MX']) {
                                $x2 = $di['MN']; $y2 = $di['MX'];
                            }
                            else {
                                $x2 = $di['MX']; $y2 = $di['MN'];
                            }

                            $sq = rrsIblock::sq($x1, $y1, $x2, $y2);
                            if ($sq > 0) {
                                $result += $sq*$di['DUMP'];
                            }
                        }
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Проверка соответствия параметров качества запроса и товара
     * @param  [] $offerParams параметры товара
     *         [] $requestParams параметры запроса
     * @return bool флаг соответствия
     */
    public static function checkOfferRequestParams($offerParams, $requestParams){
        foreach ($offerParams as $key => $param) {
            if (!isset($requestParams[$key]))
                continue;
            if (intval($param['LBASE_ID']) > 0) {
                if ($param['LBASE_ID'] != $requestParams[$key]['LBASE_ID']) {
                    return false;
                }
            }
            else {
                if ($param['BASE'] < $requestParams[$key]['MIN']
                    || $param['BASE'] > $requestParams[$key]['MAX']
                ) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Получение средневзвешенных цен по складам с учетом культур и дат (для графика) (берутся данные за год)
     * @param array $farmerWH id складов поставщиков (ключ - ID, значение - true)
     * @param array $culturesList id культур (ключ - ID, значение - true)
     * @param string $date_type тип даты для получения данных (необязательно)
     * @param boolean $farmer_nds флаг того, что СНО поставщика - с НДС
     * @return array массив с данными пар, сгрупированных по соответствующим складам и культурам
     */
    public static function getByWHAndCultures($farmerWH, $culturesList, $date_type = 'year', $farmer_nds = false, $iCheckOfferQuality = 0) {
        $result = array();

        if(count($farmerWH) > 0
            && count($culturesList) > 0
        ){
            CModule::IncludeModule('iblock');
            $nds_val = rrsIblock::getConst('nds');
            $arrTempOfferData = array();
            $arrTempTariffs = array();
            $arrTempCulturesGroup = array();
            $arrTempRoutes = array();

            $check_date = '';
            if($date_type != 'year'
                && $date_type != 'month'
                && $date_type != 'week'
            ){
                $check_date = $date_type;
            }else{
                $check_date = '-1 ' . $date_type;
            }

            $pair_data = array();
            if($iCheckOfferQuality){
                $arrTempOfferData = farmer::getOfferById($iCheckOfferQuality, true, false);
                $arrTempTariffs = model::getAgrohelperTariffs();
                $arrTempCulturesGroup = culture::getCulturesGroup();
            }
            //получение соответствующих пар
            $el_obj = new CIBlockElement;
            $res = $el_obj->GetList(
                array('DATE_CREATE' => 'ASC'),
                array(
                    'IBLOCK_ID' => rrsIblock::getIBlockId('deals_deals'),
                    'PROPERTY_FARMER_WAREHOUSE' => array_keys($farmerWH),
                    'PROPERTY_CULTURE' => array_keys($culturesList),
                    '>DATE_CREATE' => ConvertTimeStamp(strtotime($check_date))
                ),
                false,
                false,
                array('DATE_CREATE', 'PROPERTY_FARMER_WAREHOUSE', 'PROPERTY_CULTURE', 'PROPERTY_ACC_PRICE_CSM', 'PROPERTY_VOLUME', 'PROPERTY_B_NDS', 'PROPERTY_A_NDS', 'PROPERTY_CLIENT', 'PROPERTY_REQUEST', 'PROPERTY_CENTER', 'PROPERTY_CLIENT_WAREHOUSE', 'PROPERTY_BASE_PRICE')
            );
            while($data = $res->Fetch()){
                $temp_data = explode(' ', $data['DATE_CREATE']);
                $temp_price = $data['PROPERTY_ACC_PRICE_CSM_VALUE'];

                if(count($arrTempOfferData) ==  0) {
                    //делаем корректировку на наличие НДС в цене
                    if ($farmer_nds && $data['PROPERTY_B_NDS_VALUE'] != 'Y') {
                        //добавляем НДС в цену
                        $temp_price = $temp_price + ($temp_price * 0.01 * $nds_val);
                    } elseif (!$farmer_nds && $data['PROPERTY_B_NDS_VALUE'] == 'Y') {
                        //вычитаем НДС из цены
                        $temp_price = $temp_price / (1 + 0.01 * $nds_val);
                    }
                }else{
                    //корректировка на качество товара
                    $arrRequestData = client::getRequestById($data['REQUEST']);
                    $dump = self::getDump($arrRequestData['PARAMS'], $arrTempOfferData['PARAMS']);
                    if(!isset($arrTempRoutes[$arrTempOfferData['WH_ID']][$data['PROPERTY_CLIENT_WAREHOUSE_VALUE']])) {
                        //получаем расстояние между складами
                        $temp_route = log::getRouteCache($arrTempOfferData['WH_ID'], $data['PROPERTY_CLIENT_WAREHOUSE_VALUE']);
                        if(isset($temp_route[$arrTempOfferData['WH_ID']][$data['PROPERTY_CLIENT_WAREHOUSE_VALUE']])){
                            $arrTempRoutes[$arrTempOfferData['WH_ID']][$data['PROPERTY_CLIENT_WAREHOUSE_VALUE']] = $temp_route[$arrTempOfferData['WH_ID']][$data['PROPERTY_CLIENT_WAREHOUSE_VALUE']];
                        }else{
                            //добавляем расстояние между складами
                            $route = rrsIblock::getRoute($arrTempOfferData['WH_MAP'], $arrRequestData['REQUEST_WH_MAP']);
                            $arrTempRoutes[$arrTempOfferData['WH_ID']][$data['PROPERTY_CLIENT_WAREHOUSE_VALUE']] = $route;
                            log::addRouteCacheItem($arrTempOfferData['WH_ID'], $data['PROPERTY_CLIENT_WAREHOUSE_VALUE'], $route);
                        }

                    }

                    $tarif = client::getTarif($data['PROPERTY_CLIENT_VALUE'], $arrTempCulturesGroup[$data['PROPERTY_CULTURE_VALUE']], 'fca', $data['PROPERTY_CENTER_VALUE'], $arrTempRoutes[$arrTempOfferData['WH_ID']][$data['PROPERTY_CLIENT_WAREHOUSE_VALUE']], $arrTempTariffs);

                    //расчитываем цену
                    $arrTemp = lead::makeCSMFromClientBase($data['PROPERTY_BASE_PRICE_VALUE'], $data['PROPERTY_A_NDS_VALUE'] == 'Y', $arrTempOfferData['USER_NDS'] == 'yes', $dump, $tarif, array('delivery_type' => 'cpt')); //расчет идёт всегда по схеме dap (но тариф используется fca)
                    $temp_price = $arrTemp['UF_CSM_PRICE'];
                }

                $pair_data[$data['PROPERTY_FARMER_WAREHOUSE_VALUE']][$data['PROPERTY_CULTURE_VALUE']][$temp_data[0]][] = array(
                    'ACC_PRICE_CSM' => $temp_price,
                    'VOLUME' => $data['PROPERTY_VOLUME_VALUE'],
                );
            }

            //получение средневзвешенных значений
            if(count($pair_data) > 0){
                foreach($pair_data as $cur_wh => $wh_data){
                    foreach($wh_data as $cur_culture => $date_data){
                        foreach($date_data as $cur_date => $pair_arr){
                            $volume = 0;
                            $volume_x_price = 0;
                            foreach($pair_arr as $cur_pair) {
                                $volume += $cur_pair['VOLUME'];
                                $volume_x_price += $cur_pair['VOLUME'] * $cur_pair['ACC_PRICE_CSM'];
                            }
                            if($volume > 0){
                                //данные разрозненно лежат по складам, поэтому требуется отдельная сортировка по дате (сделана в рамках javascript, при выводе графика)
                                $result[$cur_wh][$cur_culture][$cur_date] = round($volume_x_price / $volume, 2);
                            }
                        }
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Получение подходящих пар товар-запрос
     * @param  [] $arOffers список товаров
     *         [] $arRequests список запросов
     *         Boolean $check_request флаг того, проверять ли для запроса данные доставки (выбранные регионы для FCA и удаленность и связанные регионы для CPT), по умолчанию false
     * @return
     */
    public static function getLeads($arOffers, $arRequests, $check_request = false) {
        $offerRequestApply = array();

        $offers_linked_regions = array(); //регионы (и связанные регионы) для товаров (для режима $check_request = true)
        $requests_by_offers_regions = array(); //ID запросов из $arRequests, полученных по данным из $offers_linked_regions (т.е. это запросы, сгруппированные по товарам, которым они подходят по регионам товаров и связанными регионами) (для режима $check_request = true)

        if($check_request){
            $offers_linked_regions = farmer::getLinkedRegionsForOffers(array_keys($arOffers));
            if(count($offers_linked_regions) > 0) {
                $requests_by_offers_regions = farmer::checkRequestsAllowForOffersByRegions($offers_linked_regions, array_keys($arRequests));
                unset($offers_linked_regions);
            }
        }

        //check params
        foreach ($arOffers as $offer) {
            foreach ($arRequests as $request) {

                //if ($offer['CULTURE_ID'] == $request['CULTURE_ID']) {
                    if (self::checkOfferRequestParams($offer['PARAMS'], $request['PARAMS'])
                            //дополнительно проверяем нужна ли проверка регионов для FCA
                        && (!$check_request
                            //если $check_request == true, то делаем дополнительную проверку
                            //для запроса с FCA доставкой - по выбранным регионам в запросе
                            //для запроса с CPT доставкой - по связанным регионам товара
                            || ($request['NEED_DELIVERY'] == 'N'
                                && in_array($offer['WH_REGION'], $request['USE_REGIONS'])
                                ||
                                $request['NEED_DELIVERY'] == 'Y'
                                && isset($requests_by_offers_regions[$offer['ID']][$request['ID']])
                            )
                        )
                    ) {
                        $offerRequestApply[] = array('OFFER' => $offer, 'REQUEST' => $request);
                    }
                //}
            }
        }

        if (sizeof($offerRequestApply) < 1) return false;

        //check NDS
        $ndsList = rrsIblock::getPropListKey('client_request', 'NDS');
        foreach ($ndsList as $i) {
            $ndsListCodes[$i['XML_ID']] = $i['ID'];
        }

        foreach ($offerRequestApply as $key => $item) {
            if (sizeof($item['REQUEST']['NDS']) > 0) {
                if (
                    ($item['OFFER']['USER_NDS'] == 'yes' && !in_array($ndsListCodes['yes'], array_keys($item['REQUEST']['NDS'])))
                    || ($item['OFFER']['USER_NDS'] == 'no' && !in_array($ndsListCodes['no'], array_keys($item['REQUEST']['NDS'])))
                ) {
                    unset($offerRequestApply[$key]);
                }
            }
        }

        if (sizeof($offerRequestApply) < 1) return false;

        //get route cache
        $farmerWHid = $clientWHid = array();
        foreach ($offerRequestApply as $item) {
            $farmerWHid[$item['OFFER']['WH_ID']] = true;
            if (is_array($item['REQUEST']['COST']) && sizeof($item['REQUEST']['COST']) > 0) {
                foreach ($item['REQUEST']['COST'] as $cost) {
                    $clientWHid[$cost['WH_ID']] = true;
                }
            }
        }
        $arRouteCache = log::getRouteCache(array_keys($farmerWHid), array_keys($clientWHid));

        //check remoteness
        $remoteness0 = rrsIblock::getConst('limit');
        foreach ($offerRequestApply as $key => $item) {
            $remoteness = $remoteness0;
//            убираем в рамках #13130 (вместо расстояния используется выбор регионов, который фильтрует набор товаров либо запросов до вызова deal::getLeads(), либо по условию $check_request)
//            if ($item['REQUEST']['NEED_DELIVERY'] == 'N' && $item['REQUEST']['REMOTENESS'] > 0) {
//                $remoteness = $item['REQUEST']['REMOTENESS'];
//            }
            if($item['REQUEST']['NEED_DELIVERY'] == 'N'){
                $remoteness = 50000;
            }

            $min_remoteness = 0;
//            if ($item['REQUEST']['NEED_DELIVERY'] == 'N') {
//                $min_remoteness = intval($item['REQUEST']['MIN_REMOTENESS']);
//            }

            foreach ($item['REQUEST']['COST'] as $k => $cost) {
                if (isset($arRouteCache[$item['OFFER']['WH_ID']][$cost['WH_ID']])
                    && intval($arRouteCache[$item['OFFER']['WH_ID']][$cost['WH_ID']]) > 0) {
                    $route = $arRouteCache[$item['OFFER']['WH_ID']][$cost['WH_ID']];
                }
                else {
                    $route = rrsIblock::getRoute($item['OFFER']['WH_MAP'], $cost['WH_MAP']);
                    $arRouteCache[$item['OFFER']['WH_ID']][$cost['WH_ID']] = $route;
                    log::addRouteCacheItem($item['OFFER']['WH_ID'], $cost['WH_ID'], $route);
                }

                if ($route > $remoteness || $route < $min_remoteness) {
                    unset($offerRequestApply[$key]['REQUEST']['COST'][$k]);
                }
                else {
                    $offerRequestApply[$key]['REQUEST']['COST'][$cost['WH_ID']]['ROUTE'] = $route;
                }
            }
        }

        foreach ($offerRequestApply as $key => $item) {
            if (sizeof($item['REQUEST']['COST']) < 1) {
                unset($offerRequestApply[$key]);
            }
        }

        if (sizeof($offerRequestApply) < 1) return false;

        //search best price
        $arAgrohelperTariffs = model::getAgrohelperTariffs();
        $arCulturesGroup = culture::getCulturesGroup();
        foreach ($offerRequestApply as $key => $item) {
            if ($item['REQUEST']['NEED_DELIVERY'] == 'N')
                $type = 'fca';
            else
                $type = 'cpt';

            //dumping
            $discount = self::getDump($item['REQUEST']['PARAMS'], $item['OFFER']['PARAMS']);

            $maxPrice = 0;
            foreach ($item['REQUEST']['COST'] as $cost) {
                $price = farmer::bestPriceCalculation(
                    array(
                        'CLIENT_ID' => $item['REQUEST']['CLIENT_ID'],
                        'CLIENT_WH_ID' => $cost['WH_ID'],
                        'CENTER' => $cost['CENTER'],
                        'ROUTE' => $cost['ROUTE'],
                        'DDP_PRICE_CLIENT' => $cost['DDP_PRICE_CLIENT'],
                        'CLIENT_NDS' => $item['REQUEST']['USER_NDS'],
                        'FARMER_NDS' => $item['OFFER']['USER_NDS'],
                        'TYPE' => $type,
                        'DUMP' => $discount,
                        'TARIFF_LIST' => $arAgrohelperTariffs,
                        'CULTURE_GROUP_ID' => $arCulturesGroup[$item['REQUEST']['CULTURE_ID']]
                    )
                );

                if ($price['ACC_PRICE_CSM'] >= $maxPrice) {
                    $maxPrice = $price['ACC_PRICE_CSM'];
                    $arPrice = $price;
                    $arPrice['REQUEST_ID'] = $item['REQUEST']['ID'];
                }
            }

            $offerRequestApply[$key]['REQUEST']['BEST_PRICE'] = $arPrice;
            unset($offerRequestApply[$key]['REQUEST']['COST']);
            unset($offerRequestApply[$key]['REQUEST']['PARAMS']);
            unset($offerRequestApply[$key]['OFFER']['PARAMS']);
        }

        return $offerRequestApply;
    }

    /**
     * Сортировка пар запросов
     * @param [] $offerRequest - запросы отсортированные по цене
     * @param bool $bGroupByAP - флаг группировки по поставщикам
     * @return array
     */
    public static function leadsSort($offerRequest, $bGroupByAP = false){
        $resultRequest = array();
        $CULTURE_GROUPS = array();
        $max_culture_count = 0; //максимальное число элементов одной из культур
        //сгруппируем запросы по культурам
        for($i=0,$c=sizeof($offerRequest);$i<$c;$i++){
            $CULTURE_GROUPS[$offerRequest[$i]['REQUEST']['CULTURE_ID']][] = $offerRequest[$i];
            $this_culture_count = sizeof($CULTURE_GROUPS[$offerRequest[$i]['REQUEST']['CULTURE_ID']]);
            if($this_culture_count>$max_culture_count)
                $max_culture_count = $this_culture_count;
        }

        //сформируем итоговый массив с учетом цен и культур
        for($i=0;$i<$max_culture_count;$i++){
            foreach ($CULTURE_GROUPS as $CULTURE_ID=>$offers){
                if(isset($offers[$i])){

                    if($bGroupByAP) {
                        $iFarmerId = $offers[$i]['OFFER']['FARMER_ID'];
                        $resultRequest[$iFarmerId][] = $offers[$i];
                    } else {
                        $resultRequest[] = $offers[$i];
                    }
                }
            }
        }
        return $resultRequest;
    }

    /*
     * Организация пагинации для списка соответствий
     * @param array $offerRequestList - массив соответствий
     * @param int $page_limit - ограничение количества элементов на одну страницу (для пагинации, необязательно)
     * @param int $page_number - номер страницы (для пагинации, необязательно)
     *
     * @return array - массив соответствий, дополненный данными
     * */
    public static function leadsGetPageElements($offerRequestList, $page_limit = 20, $page_number = 1){
        $result = array();

        $my_c = 0; //counter
        $start_element = 0;
        $num_limit = 100000000;

        if(!filter_var($page_limit, FILTER_VALIDATE_INT)
            || $page_limit < 1
        ){
            $page_limit = 1;
        }

        if(!filter_var($page_number, FILTER_VALIDATE_INT)
            || $page_number < 1
        ){
            $page_number = 1;
        }

        if($page_limit
            && $page_number
        ){
            $start_element = ($page_number - 1) * $page_limit;
            $num_limit = $start_element + $page_limit - 1;
        }

        foreach ($offerRequestList as $cur_id => $cur_data) {
            foreach($cur_data as $cur_pos => $lead) {
                //пропускаем лишние начальные записи
                if ($start_element > $my_c) {
                    $my_c++;
                    continue;
                }

                $result[$cur_id][$cur_pos] = $lead;

                //ограничиваем вывод данных
                $my_c++;
                if ($my_c > $num_limit) {
                    break(2);
                }
            }
        }

        return $result;
    }

    /*
     * Организация пагинации для списка предложений
     * @param array $items - массив элементов
     * @param int $page_limit - ограничение количества элементов на одну страницу (для пагинации, необязательно)
     * @param int $page_number - номер страницы (для пагинации, необязательно)
     *
     * @return array - массив соответствий, дополненный данными
     * */
    public static function counterOffersGetPageElements($items, $page_limit = 20, $page_number = 1){
        $result = array();

        $my_c = 0; //counter
        $start_element = 0;
        $num_limit = 100000000;

        if(!filter_var($page_limit, FILTER_VALIDATE_INT)
            || $page_limit < 1
        ){
            $page_limit = 1;
        }

        if(!filter_var($page_number, FILTER_VALIDATE_INT)
            || $page_number < 1
        ){
            $page_number = 1;
        }

        if($page_limit
            && $page_number
        ){
            $start_element = ($page_number - 1) * $page_limit;
            $num_limit = $start_element + $page_limit - 1;
        }

        foreach ($items as $cur_id => $cur_item) {
            //пропускаем лишние начальные записи
            if ($start_element > $my_c) {
                $my_c++;
                continue;
            }

            $result[$cur_id] = $cur_item;

            //ограничиваем вывод данных
            $my_c++;
            if ($my_c > $num_limit) {
                break;
            }
        }

        return $result;
    }

    /**
     * Поиск подходящих для запроса покупателя товаров (и создание записей соответствий в HL ИБ LEADLIST)
     * @param  number $request_id идентификатор запроса покупателя
     * @return []
     */
    public static function searchSuitableOffers($request_id)
    {
        $arResult = array(
            'FARMER_CNT' => 0,       // Количество АП
            'FARMER_BEST_PRICE_CNT' => 0,       // Количество АП c лучшей ценой
            'ERROR_MSG' => null,    // Ошибка
        );

        //получение информации о запросе покупателя
        $request = client::getRequestById($request_id);
        $arRequests[] = $request;

        //поиск аналогов культуры
        $cultureList = culture::getAnalog($request['CULTURE_ID']);

        $cultureList[] = $request['CULTURE_ID'];

        $region_ids = array();
        if (isset($request['COST'])
            && is_array($request['COST'])
        ) {
            foreach ($request['COST'] as $cur_data) {
                $region_ids[$cur_data['WH_REGION']] = true;
            }

            if (count($region_ids) > 0) {
                $region_ids = array_keys($region_ids);
            }
        }

        //Для FCA фильтруем по выбранным регионам, вместо региона склада
        if($request['NEED_DELIVERY'] == 'N'){
            $region_ids = $request['USE_REGIONS'];
        }

        if(count($region_ids) > 0) {
            //получение всех активных товаров по культуре для рассматриваемых регионов для данного региона (включая данный регион)
            $arOffers = farmer::getOfferListWithRegion($cultureList, $region_ids, $request['NEED_DELIVERY'] != 'N');

            if (is_array($arOffers) && sizeof($arOffers) > 0) {
                //подбор подходящих пар запрос-товар
                $offerRequestApply = self::getLeads($arOffers, $arRequests);
            }

            unset($arOffers, $arRequests);

            if (is_array($offerRequestApply) && sizeof($offerRequestApply) > 0) {
                //фермеры из черного списка клиента
                $allFarmersBlackList = BlackList::getClientFarmersBL($request['CLIENT_ID']);
                //данные личного черного списка покупателей
                $blackListData = array_flip(client::getBlackListTotal($request['CLIENT_ID']));

                //сбор данных для уведомлений
                $arData = array();
                $arFarmerId = array();
                $fca_dap = ($request['NEED_DELIVERY'] == 'Y') ? 'CPT' : 'FCA';

                $offer_prices = array();
                $best_offer_request = array();
                //добавление пар
                foreach ($offerRequestApply as $key => $item) {
                    if (!isset($allFarmersBlackList[$item['OFFER']['FARMER_ID']])
                        && !isset($blackListData[$item['OFFER']['FARMER_ID']])
                    ) {
                        //если фермер не в черном списке клиента то добавляем пару
                        $arFarmerId[$item['OFFER']['FARMER_ID']] = true;

                        $clientData = array(
                            'CULTURE' => $item['REQUEST']['CULTURE_ID'],
                            'CLIENT' => $item['REQUEST']['CLIENT_ID'],
                            'REQUEST' => $item['REQUEST']['ID'],
                            'WH' => $item['REQUEST']['BEST_PRICE']['WH_ID'],
                            'CENTER' => $item['REQUEST']['BEST_PRICE']['CENTER'],
                        );
                        $farmerData = array(
                            'FARMER' => $item['OFFER']['FARMER_ID'],
                            'OFFER' => $item['OFFER']['ID'],
                            'WH' => $item['OFFER']['WH_ID'],
                            'NDS' => $item['OFFER']['USER_NDS']
                        );
                        $route = $item['REQUEST']['BEST_PRICE']['ROUTE'];
                        $price = $item['REQUEST']['BEST_PRICE']['BC_DDP'];
                        $price_csm = $item['REQUEST']['BEST_PRICE']['ACC_PRICE_CSM'];
                        $price_base_contract = $item['REQUEST']['BEST_PRICE']['BASE_PRICE'];

                        //добавление данных о лучшей цене
                        $offer_prices[$farmerData['OFFER']] = $price_csm;
                        $best_offer_request[$farmerData['OFFER']] = $item['REQUEST']['BEST_PRICE']['REQUEST_ID'];

                        lead::addLead($clientData, $farmerData, $route, $price, $price_csm, $price_base_contract);

                        $arData[] = array(
                            "offer" => intval($item['OFFER']['ID']),
                            "request" => intval($item['REQUEST']['ID']),
                            "farmer" => intval($item['OFFER']['FARMER_ID']),
                            "type" => $fca_dap,
                            "culture" => $item['REQUEST']['CULTURE_NAME'],
                            "wh" => $item['OFFER']['WH_NAME'],
                            "price" => $item['REQUEST']['BEST_PRICE']['ACC_PRICE_CSM'],
                            "volume" => $item['REQUEST']['VOLUME']
                        );
                    }
                    /*if(empty($arLead['ID'])) {
                        $arResult['ERROR_MSG'] .= 'Не удалось добавить лид' . PHP_EOL;
                    }*/
                }

                if(isset($clientData['CULTURE'])
                    && is_numeric($clientData['CULTURE'])
                ) {
                    self::setBestPriceArr($request['CULTURE_ID'], $offer_prices, $best_offer_request);
                }

                //Считаем количество поставщиков
                $arResult['FARMER_CNT'] = count($arFarmerId);

                $arFarmerPrice = lead::getLeadList4bestPrice(array_keys($arFarmerId), $request_id, $request['CULTURE_ID']);

                $arFarmerBestPrice = array();
                foreach ($offerRequestApply as $key => $item) {
                    $price = $item['REQUEST']['BEST_PRICE']['BC_DDP'];
                    if (!isset($arFarmerPrice[$item['OFFER']['FARMER_ID']])) {
                        $arFarmerBestPrice[$item['OFFER']['FARMER_ID']] = true;
                    } elseif ($price > $arFarmerPrice[$item['OFFER']['FARMER_ID']]) {
                        $arFarmerBestPrice[$item['OFFER']['FARMER_ID']] = true;
                    }
                }

                $arResult['FARMER_BEST_PRICE_CNT'] = count($arFarmerBestPrice);

                farmer::createNewCounterOfferByRequest($request_id, $offerRequestApply);

                //Добавление в очередь рассылки уведомлений
                notice::addNoticeLog('searchSuitableOffers', $request_id, $arData);

                return $arResult;
            }
        }

        return 0;
    }

    /**
     * Попытка создания соответствий между выбранными запросами и товарами (без проверки на черный список)
     * @param array $requests_data - массив с данными запросов
     * @param array $offers_data - массив с данными товаров
     * @return []
     */
    public static function searchSuitableLeads($requests_data, $offers_data) {

        $offerRequestApply = array();

        if (is_array($requests_data)
            && count($requests_data) > 0
            && is_array($offers_data)
            && count($offers_data) > 0
        ){
            //отсечение тех сущностей, для которых нет соответствий по связанным регионам
            //checkRequestAndOffersByRegion($requests_data, $offers_data);

            //подбор подходящих пар запрос-товар (с дополнительной проверкой запросов)
            $offerRequestApply = self::getLeads($offers_data, $requests_data, true);
        }

        if (is_array($offerRequestApply) && sizeof($offerRequestApply) > 0) {

            //сбор данных для уведомлений
//            $arData = array();
            $arFarmerId = array();
            $offer_prices = array();
            $best_offer_request = array();
            //добавление пар
            foreach ($offerRequestApply as $key => $item) {

                $clientData = array(
                    'CULTURE' => $item['REQUEST']['CULTURE_ID'],
                    'CLIENT' => $item['REQUEST']['CLIENT_ID'],
                    'REQUEST' => $item['REQUEST']['ID'],
                    'WH' => $item['REQUEST']['BEST_PRICE']['WH_ID'],
                    'CENTER' => $item['REQUEST']['BEST_PRICE']['CENTER'],
                );
                $farmerData = array(
                    'FARMER' => $item['OFFER']['FARMER_ID'],
                    'OFFER' => $item['OFFER']['ID'],
                    'WH' => $item['OFFER']['WH_ID'],
                    'NDS' => $item['OFFER']['USER_NDS']
                );
                $route = $item['REQUEST']['BEST_PRICE']['ROUTE'];
                $price = $item['REQUEST']['BEST_PRICE']['BC_DDP'];
                $price_csm = $item['REQUEST']['BEST_PRICE']['ACC_PRICE_CSM'];
                $price_base_contract = $item['REQUEST']['BEST_PRICE']['BASE_PRICE'];

                //если для данного запроса и данного поставщика еще не создавались соответствия, то создаем
                if(!isset($arFarmerId[$item['REQUEST']['ID']]['FARMER_IDS'][$item['OFFER']['FARMER_ID']])) {

                    //добавление данных о лучшей цене
                    $offer_prices[$farmerData['OFFER']] = $price_csm;
                    $best_offer_request[$farmerData['OFFER']] = $item['REQUEST']['BEST_PRICE']['REQUEST_ID'];

                    //создаем соответствие
                    lead::addLead($clientData, $farmerData, $route, $price, $price_csm, $price_base_contract);

                    //создаем встречные ВП для текущего запроса, если требуется
                    farmer::createNewCounterOfferByRequest($item['REQUEST']['ID'], $offerRequestApply);
                }

                //учитываем поставщиков и культуру для каждого запроса
                if(isset($arFarmerId[$item['REQUEST']['ID']]['FARMER_IDS'])){
                    $arFarmerId[$item['REQUEST']['ID']]['FARMER_IDS'][$item['OFFER']['FARMER_ID']] = true;
                }else{
                    $arFarmerId[$item['REQUEST']['ID']] = array(
                        'CNT' => 0,
                        'CULTURE' => $item['REQUEST']['CULTURE_ID'],
                        'FARMER_IDS' => array($item['OFFER']['FARMER_ID'] => true),
                        'BEST_CNT' => 0
                    );
                }
            }
            if(isset($clientData['CULTURE'])
                && is_numeric($clientData['CULTURE'])
            ){
                self::setBestPriceArr($clientData['CULTURE'], $offer_prices, $best_offer_request);
            }
            //обновляем количества получивших запросы поставщиков (и лучших цен) для каждого запроса
            /*if(count($arFarmerId) > 0) {
                foreach ($arFarmerId as $cur_request => $cur_data) {
                    $arFarmerId[$cur_request]['CNT'] = count($cur_data['FARMER_IDS']);

                    $arFarmerPrice = lead::getLeadList4bestPrice(array_keys($cur_data['FARMER_IDS']), $cur_request, $cur_data['CULTURE']);
                    $arFarmerBestPrice = array();

                    //для данного запроса определяем количество поставщиков, для которых обнаружилась лучшая цена
                    foreach ($offerRequestApply as $key => $item) {
                        //проверяем только текущий запрос
                        if ($cur_request == $item['REQUEST']['ID']) {
                            $price = $item['REQUEST']['BEST_PRICE']['BC_DDP'];
                            if (!isset($arFarmerPrice[$item['OFFER']['FARMER_ID']])) {
                                $arFarmerBestPrice[$item['OFFER']['FARMER_ID']] = true;
                            } elseif ($price > $arFarmerPrice[$item['OFFER']['FARMER_ID']]) {
                                $arFarmerBestPrice[$item['OFFER']['FARMER_ID']] = true;
                            }
                        }
                    }
                    $arFarmerId[$cur_request]['BEST_CNT'] = count($arFarmerBestPrice);
                }

                //обновляем количества в запросах (увеличиваем количества)
                $ib_id = rrsIblock::getIBlockId('client_request');
                CModule::IncludeModule('iblock');
                $res = CIBlockElement::GetList(
                    array('ID' => 'ASC'),
                    array(
                        'IBLOCK_ID' => $ib_id,
                        'ID' => array_keys($arFarmerId)
                    ),
                    false,
                    false,
                    array('ID', 'PROPERTY_F_NUM', 'PROPERTY_FARMER_BEST_PRICE_CNT')
                );
                while($data = $res->Fetch()){
                    if(isset($arFarmerId[$data['ID']])){
                        $new_cnt = intval($data['PROPERTY_F_NUM_VALUE']) + $arFarmerId[$data['ID']]['CNT'];
                        $new_best_cnt = intval($data['PROPERTY_FARMER_BEST_PRICE_CNT_VALUE']) + $arFarmerId[$data['ID']]['BEST_CNT'];

                        CIBlockElement::SetPropertyValuesEx($data['ID'], $ib_id, array('F_NUM' => $new_cnt, 'FARMER_BEST_PRICE_CNT' => $new_best_cnt));
                    }
                }
            }*/

            //Добавление в очередь рассылки уведомлений
            //notice::addNoticeLog('searchSuitableOffers', $request_id, $arData);

//            return $arResult;
        }
//        return 0;
    }

    /**
     * Поиск подходящих для товара запросов
     * @param  number $offer_id идентификатор товара
     * @return
     */
    public static function searchSuitableRequests($offer_id) {
        //получение информации о товаре поставщика
        $offer = farmer::getOfferById($offer_id);
        $arOffers[] = $offer;

        //поиск аналогов культуры
        $cultureList = culture::getAnalog($offer['CULTURE_ID']);

        $cultureList[] = $offer['CULTURE_ID'];

        if(isset($offer['WH_REGION'])
            && is_numeric($offer['WH_REGION'])
        ) {
            //получение всех активных запросов по культуре для связанных регионов с регионом товара
            $arRequests = client::getRequestListWithRegion($cultureList, $offer['WH_REGION']);

            if (is_array($arRequests) && sizeof($arRequests) > 0) {
                //подбор подходящих пар запрос-товар
                $offerRequestApply = self::getLeads($arOffers, $arRequests);
            }
            unset($arOffers, $arRequests);

            if (is_array($offerRequestApply) && sizeof($offerRequestApply) > 0) {
                //массив клиентов, которые добавили данного фермера в черным список
                $allClientsBL = BlackList::getClientsByFarmerBL($offer['FARMER_ID']);
                //данные личного черного списка поставщиков
                $blackListData = array_flip(farmer::getBlackListTotal($offer['FARMER_ID']));

                //сбор данных для уведомлений
                $arData = array();

                $offer_prices = array();
                $best_offer_request = array();
                //добавление пар
                foreach ($offerRequestApply as $key => $item) {
                    if (!isset($allClientsBL[$item['REQUEST']['CLIENT_ID']])
                        && !isset($blackListData[$item['REQUEST']['CLIENT_ID']])
                    ) {
                        //если не в черном списке, создаем пару
                        $clientData = array(
                            'CULTURE' => $item['REQUEST']['CULTURE_ID'],
                            'CLIENT' => $item['REQUEST']['CLIENT_ID'],
                            'REQUEST' => $item['REQUEST']['ID'],
                            'WH' => $item['REQUEST']['BEST_PRICE']['WH_ID'],
                            'CENTER' => $item['REQUEST']['BEST_PRICE']['CENTER'],
                        );
                        $farmerData = array(
                            'FARMER' => $item['OFFER']['FARMER_ID'],
                            'OFFER' => $item['OFFER']['ID'],
                            'WH' => $item['OFFER']['WH_ID'],
                            'NDS' => $item['OFFER']['USER_NDS']
                        );
                        $route = $item['REQUEST']['BEST_PRICE']['ROUTE'];
                        $price = $item['REQUEST']['BEST_PRICE']['BC_DDP'];
                        $price_csm = $item['REQUEST']['BEST_PRICE']['ACC_PRICE_CSM'];
                        $price_base_contract = $item['REQUEST']['BEST_PRICE']['BASE_PRICE'];

                        //добавление данных о лучшей цене
                        $offer_prices[$farmerData['OFFER']] = $price_csm;
                        $best_offer_request[$farmerData['OFFER']] = $item['REQUEST']['BEST_PRICE']['REQUEST_ID'];

                        lead::addLead($clientData, $farmerData, $route, $price, $price_csm, $price_base_contract);

                        $arData[] = array(
                            "offer" => intval($item['OFFER']['ID']),
                            "request" => intval($item['REQUEST']['ID']),
                            "farmer" => intval($item['OFFER']['FARMER_ID']),
                            "type" => ($item['REQUEST']['NEED_DELIVERY'] == 'Y') ? 'CPT' : 'FCA',
                            "culture" => $item['REQUEST']['CULTURE_NAME'],
                            "wh" => $item['OFFER']['WH_NAME'],
                            "price" => $item['REQUEST']['BEST_PRICE']['ACC_PRICE_CSM'],
                            "volume" => $item['REQUEST']['VOLUME']
                        );
                    }
                }

                if(isset($clientData['CULTURE'])
                    && is_numeric($clientData['CULTURE'])
                ){
                    self::setBestPriceArr($clientData['CULTURE'],$offer_prices, $best_offer_request);
                }

                //Добавление в очередь рассылки уведомлений
                notice::addNoticeLog('searchSuitableRequests', $offer_id, $arData);

                return sizeof($offerRequestApply);
            }
        }

        return 0;
    }

    /**
     * Получение информации о сделке для черного списка
     * @param $deal_id
     */
    public static function getInfo4BL($deal_id){
        $result = array();

        CModule::IncludeModule('iblock');
        $res = CIBlockElement::GetList(
            array('ID' => 'ASC'),
            array('IBLOCK_CODE' => 'deals_deals', 'ACTIVE' => 'Y', 'ID' => $deal_id),
            false,
            false,
            array('ID', 'NAME', 'PROPERTY_CULTURE', 'PROPERTY_CENTER')
        );
        if ($ob = $res->Fetch()) {
            $tmp = array(
                'NAME'          => $ob['NAME'],
                'CULTURE_ID'    => $ob['PROPERTY_CULTURE_VALUE'],
                'CENTER'        => $ob['PROPERTY_CENTER_VALUE']
            );
            $result = $tmp;
        }

        return $result;
    }

    /**
     * Получение списка статусов сделки
     * @param  int $deal_id идентификатор сделки
     * @return [] список статусов
     */
    public static function getStatus($deal_id) {
        $result = array();

        CModule::IncludeModule('iblock');
        $res = CIBlockElement::GetList(
            array('ID' => 'ASC'),
            array('IBLOCK_CODE' => 'deals_deals', 'ACTIVE' => 'Y', 'ID' => $deal_id),
            false,
            false,
            array('ID', 'NAME', 'PROPERTY_STAGE', 'PROPERTY_STATUS', 'PROPERTY_DELIVERY')
        );
        if ($ob = $res->Fetch()) {
            $tmp = array(
                'S' => $ob['PROPERTY_STAGE_ENUM_ID'] > 0 ? rrsIblock::getPropListId('deals_deals', 'STAGE', $ob['PROPERTY_STAGE_ENUM_ID']) : '',
                'DEAL' => $ob['PROPERTY_STATUS_ENUM_ID'] > 0 ? rrsIblock::getPropListId('deals_deals', 'STATUS', $ob['PROPERTY_STATUS_ENUM_ID']) : '',
                'DELIVERY' => $ob['PROPERTY_DELIVERY_ENUM_ID'] > 0 ? rrsIblock::getPropListId('deals_deals', 'DELIVERY', $ob['PROPERTY_DELIVERY_ENUM_ID']) : '',
            );
            $result = $tmp;
        }

        return $result;
    }

    /**
     * Установление статуса сделки
     * @param  int $deal_id идентификатор сделки
     *         [] $status массив пар код свойства-код статуса
     * @return bool
     */
    public static function setStatus ($deal_id, $status) {
        CModule::IncludeModule('iblock');
        $prop = array(
            'STAGE' => rrsIblock::getPropListKey('deals_deals', 'STAGE', $status),
            'DATE_STAGE' => date('d.m.Y H:i:s')
        );
        if ($status == 'search') {
            $prop['DATE_SEARCH'] = date('d.m.Y H:i:s');
        }
        if ($status == 'close') {
            $prop['STATUS'] = rrsIblock::getPropListKey('deals_deals', 'STATUS', 'close');
        }
        if ($status == 'reject') {
            $prop['STATUS'] = rrsIblock::getPropListKey('deals_deals', 'STATUS', 'cancel');
        }
        CIBlockElement::SetPropertyValuesEx(
            $deal_id,
            rrsIblock::getIBlockId('deals_deals'),
            $prop
        );
        $el = new CIBlockElement;
        $res = $el->Update($deal_id, array('NAME' => date('d.m.Y H:i:s')));
        return true;
    }

    /**
     * Получение информации для транспортной модели
     * @param  int $deal_id идентификатор сделки
     * @return []
     */
    public static function getInfo4Transport ($deal_id) {
        CModule::IncludeModule('iblock');
        $result = array();
        $res = CIBlockElement::GetList(
            array('ID' => 'DESC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('deals_deals'),
                'ACTIVE' => 'Y',
                'ID' => $deal_id
            ),
            false,
            false,
            array(
                'ID',
                'NAME',
                'PROPERTY_CENTER',
                'PROPERTY_ROUTE'
            )
        );
        if ($ob = $res->Fetch()) {
            $result['CENTER'] = $ob['PROPERTY_CENTER_VALUE'];
            $result['DAYS'] = model::getTarifDays($ob['PROPERTY_CENTER_VALUE'], $ob['PROPERTY_ROUTE_VALUE']);
        }

        return $result;
    }

    /**
     * Проверка на наличие документа
     * @param  int $deal_id идентификатор сделки
     *         string $docCode код документа
     * @return int идентификатор файла
     */
    public static function createDocument($deal_id, $templateDocCode, $docCode, $docName, $data, $profile = '') {
        CModule::IncludeModule('iblock');
        $commonTemplate = true;
        if (trim($profile) != '') {
            $resDeal = CIBlockElement::GetList(
                array('ID' => 'DESC'),
                array('IBLOCK_ID' => rrsIblock::getIBlockId('deals_deals'), 'ACTIVE' => 'Y', 'ID' => $deal_id),
                false,
                false,
                array('ID', 'PROPERTY_CLIENT')
            );
            if ($obDeal = $resDeal->Fetch()) {
                $templateText = '';
                $resProfile = CIBlockElement::GetList(
                    array('ID' => 'DESC'),
                    array('IBLOCK_ID' => rrsIblock::getIBlockId($profile), 'ACTIVE' => 'Y', 'PROPERTY_USER' => $obDeal['PROPERTY_CLIENT_VALUE']),
                    false,
                    false,
                    array('ID', 'PROPERTY_'.$templateDocCode)
                );
                if ($obProfile = $resProfile->Fetch()) {
                    $templateText = $obProfile['PROPERTY_'.strtoupper($templateDocCode).'_VALUE']['TEXT'];
                    if (trim($templateText) != '') {
                        $commonTemplate = false;
                    }
                }
            }
        }

        if ($commonTemplate) {
            $res = CIBlockElement::GetList(
                array('ID' => 'DESC'),
                array(
                    'IBLOCK_ID' => rrsIblock::getIBlockId('deals_docs_templates'), 'ACTIVE' => 'Y', 'CODE' => $templateDocCode),
                false,
                false,
                array('ID', 'NAME', 'CODE', 'DETAIL_TEXT')
            );
            if ($ob = $res->Fetch()) {
                $templateText = $ob['DETAIL_TEXT'];
            }
        }

        if (trim($templateText) != '') {
            $oElement = new CIBlockElement();
            $arUpdateValues = $arUpdatePropertyValues = array();

            $arUpdateValues['IBLOCK_ID'] = rrsIblock::getIBlockId('deals_docs');
            $arUpdateValues['ACTIVE'] = 'Y';
            $arUpdateValues['NAME'] = $docName;
            $arUpdateValues['CODE'] = $docCode;

            if ($ID = $oElement->Add($arUpdateValues)) {
                $arUpdatePropertyValues['DEAL'] = $deal_id;

                $template = str_replace(
                    array_keys($data),
                    $data,
                    $templateText
                );

                $template = str_replace(
                    array("#DOC_ID#"),
                    array($ID),
                    $template
                );

                $text = '<html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"></head><body style="width: 1024px; font-size: 10px;">';
                $text .= "<style>
                    ul{line-height: 18px; margin: 0; padding: 0;}
                    p{line-height: 18px; text-indent: 24px; margin: 0 !important; padding: 0 !important;}
                    p table {text-indent: 0;}
                    table tr td{text-align: left;}
                    .pay1 tr td{border: 1px solid #333333; color: #333333;}
                    .pay2{font-size: 9px;}
                    .pay2 .title td{text-align: center;}
                </style>";
                $text .= $template;
                $text .= '</body></html>';

                $file_html = $_SERVER["DOCUMENT_ROOT"]."/upload/tmp/".$deal_id."_".$docCode.'.html';

                $f = fopen($file_html, 'w+');
                fwrite($f, $text);
                fclose($f);

                $arUpdatePropertyValues['FILE_HTML'] = CFile::MakeFileArray($file_html);

                $pdf = new pdf();
                $pdf->HtmlToPDF($file_html, 'F', $_SERVER["DOCUMENT_ROOT"]."/upload/tmp/");

                $file_pdf = $_SERVER["DOCUMENT_ROOT"]."/upload/tmp/".$deal_id."_".$docCode.'.pdf';
                $arUpdatePropertyValues['FILE_PDF'] = CFile::MakeFileArray($file_pdf);

                $arUpdateValues['PROPERTY_VALUES'] = $arUpdatePropertyValues;

                if ($oElement->Update($ID, $arUpdateValues)) {
                    unlink($file_html);
                    unlink($file_pdf);

                    return $ID;
                }
            }
        }

        return false;
    }

    /**
     * Получение документа
     * @param  int $deal_id идентификатор сделки
     *         string $docCode код документа
     * @return []
     */
    public static function getDocument($deal_id, $docCode) {
        CModule::IncludeModule('iblock');
        $res = CIBlockElement::GetList(
            array('ID' => 'DESC'),
            array(
                'IBLOCK_CODE' => 'deals_docs',
                'ACTIVE' => 'Y',
                'PROPERTY_DEAL' => $deal_id,
                'CODE' => $docCode
            ),
            false,
            false,
            array(
                'ID',
                'NAME',
                'DATE_CREATE',
                'PROPERTY_FILE_PDF'
            )
        );
        if ($ob = $res->Fetch()) {
            $result = $ob;
        }

        return $result;
    }

    /**
     * Формирование таблиц значений качества со сбросами, с отклонениями для документов
     * @param  int $culture_id идентификатор культуры
     *         int $request_id идентификатор запроса покупателя
     * @return [] html
     */
    function formDumpTable($culture_id, $request_id) {
        $paramsInfo = culture::getParamsListByCultureId($culture_id);
        $requestParams = current(client::getParamsList(array($request_id)));

        $baseTable = $rangTable = $dumpTable = '<table border="1" cellpadding="1">';
        $baseTable .= '<tr><td style="text-align: center;">показатель</td><td style="text-align: center;">норматив</td></tr>';
        $rangTable .= '<tr><td style="text-align: center;">Показатели</td><td style="text-align: center;">Базисные</td><td style="text-align: center;">Диапазон допустимых значений</td></tr>';
        $dumpTable .= '<tr><td style="text-align: center;" width="30%">Показатели</td><td style="text-align: center;" width="20%">Базисные</td><td style="text-align: center;" width="20%">Диапазон допустимых значений</td><td style="text-align: center;" width="30%">Изменение цены в % за каждый % отклонения показателя от базисного</td></tr>';
        foreach ($paramsInfo as $param) {
            $info = $requestParams[$param['QUALITY_ID']];
            $baseTable .= '<tr><td>' . $param['QUALITY_NAME'] . '</td>';
            $rangTable .= '<tr><td>' . $param['QUALITY_NAME'] . '</td>';
            $dumpTable .= '<tr><td width="30%">' . $param['QUALITY_NAME'] . '</td>';
            if ($info['LBASE_ID'] > 0) {
                foreach ($param['LIST'] as $item) {
                    if ($info['LBASE_ID'] == $item['ID']) {
                        $baseTable .= '<td style="text-align: center;">' . $item['NAME'] . '</td>';
                        $rangTable .= '<td style="text-align: center;">' . $item['NAME'] . '</td><td>&nbsp;</td>';
                        $dumpTable .= '<td style="text-align: center;" width="20%">' . $item['NAME'] . '</td><td width="20%">&nbsp;</td><td width="30%">&nbsp;</td>';
                        break;
                    }
                }
            }
            else {
                $baseTable .= '<td style="text-align: center;">' . $info['BASE'] . '</td>';
                $rangTable .= '<td style="text-align: center;">' . $info['BASE'] . '</td><td style="text-align: center;">' . $info['MIN'] . '&nbsp;-&nbsp;' . $info['MAX'] . '</td>';
                $dumpTable .= '<td style="text-align: center;" width="20%">' . $info['BASE'] . '</td><td style="text-align: center;" width="20%">' . $info['MIN'] . '&nbsp;-&nbsp;' . $info['MAX'] . '</td><td  width="30%" style="text-align: center;">';
                if (sizeof($info['DUMPING']) > 0) {
                    foreach ($info['DUMPING'] as $dump) {
                        $dumpTable .= 'от ' . $dump['MN'] . ' до ' . $dump['MX'] . ' - ';
                        $dumpTable .= ($dump['DUMP'] > 0)?'плюс ':'минус ';
                        $dumpTable .=  number_format(abs($dump['DUMP']), 1, ',', ' ') . '%<br>';
                    }
                }
                else {
                    $dumpTable .= '&nbsp;';
                }
                $dumpTable .= '</td>';
            }
            $baseTable .= '</tr>';
            $rangTable .= '</tr>';
            $dumpTable .= '</tr>';
        }
        $baseTable .= '</table>';
        $rangTable .= '</table>';
        $dumpTable .= '</table>';

        return array('BASE_TABLE' => $baseTable, 'RANG_TABLE' => $rangTable, 'DUMP_TABLE' => $dumpTable);
    }

    /**
     * получение наличия активных сделок для выбранных АП и покупателей
     *
     * @param mixed $farmers_arr идентификатор или массив идентификаторов поставщиков
     * @param mixed $clients_arr идентификатор или массив идентификаторов покупателей
     *
     * @return [] массив со списком флагов наличия активных сделок по каждому поставщику
     */
    public static function getUsersActiveDeals($farmers_arr = false, $clients_arr = false) {
        $result = array();
        $check_farmer = array();
        $check_client = array();

        $arFilter = array(
            'ACTIVE'            => 'Y',
            'IBLOCK_ID'         => rrsIblock::getIBlockId('deals_deals'),
            'PROPERTY_STATUS'   => rrsIblock::getPropListKey('deals_deals', 'STATUS', 'open')
        );

        if(is_array($farmers_arr) && count($farmers_arr) > 0
            || !is_array($farmers_arr) && is_numeric($farmers_arr)
        ){
            $arFilter['PROPERTY_FARMER'] = $farmers_arr;
            if(is_array($farmers_arr)){
                $check_farmer = array_flip($farmers_arr);
            }else{
                $check_farmer[$farmers_arr] = true;
            }
        }

        if(is_array($clients_arr) && count($clients_arr) > 0
            || !is_array($clients_arr) && is_numeric($clients_arr)
        ){
            $arFilter['PROPERTY_CLIENT'] = $clients_arr;
            if(is_array($clients_arr)){
                $check_client = array_flip($clients_arr);
            }else{
                $check_client[$clients_arr] = true;
            }
        }

        if(isset($arFilter['PROPERTY_FARMER'])
            || isset($arFilter['PROPERTY_CLIENT'])
        ){
            $res = CIBlockElement::GetList(
                array('ID' => 'ASC'),
                $arFilter,
                false,
                false,
                array('PROPERTY_FARMER', 'PROPERTY_CLIENT')
            );
            while($data = $res->Fetch()){
                if(isset($data['PROPERTY_FARMER_VALUE'])
                    && is_numeric($data['PROPERTY_FARMER_VALUE'])
                    && isset($check_farmer[$data['PROPERTY_FARMER_VALUE']])
                ){
                    $result[$data['PROPERTY_FARMER_VALUE']] = true;
                }
                if(isset($data['PROPERTY_CLIENT_VALUE'])
                    && is_numeric($data['PROPERTY_CLIENT_VALUE'])
                    && isset($check_client[$data['PROPERTY_CLIENT_VALUE']])
                ){
                    $result[$data['PROPERTY_CLIENT_VALUE']] = true;
                }
            }
        }

        return $result;
    }

    /**
     * Получение прав на участие в сделках для поставщиков (например наличие загруженных документов)
     * @param mixed $farmersIds идентификатор или массив идентификаторов поставщиков
     * @return [] массив со списком элементов
     */
    function checkRights($farmersIds) {
        $result = array();

        if (!is_array($farmersIds) && is_numeric($farmersIds)){
            $farmersIds = array($farmersIds);
        }

        foreach($farmersIds as $cur_farmer){
            $result[$cur_farmer] = farmer::checkDealsRights($cur_farmer);
        }

        return $result;
    }

    /**
     * @param array $regions - массив ID регионов
     */
    static function getRegionFarmersWH($regions = array()){
        $farmersWHs = array();
        //получаем склады по выбранному региону
        $res = CIBlockElement::GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('farmer_warehouse'),
                'ACTIVE' => 'Y',
                'PROPERTY_REGION' => $regions,
            ),
            false,
            false,
            array('ID')
        );
        while ($ob = $res->Fetch()) {
            $farmersWHs[$ob['ID']] = 1;
        }
        return $farmersWHs;
    }


    /**
     * Получение средней цены, по массиву регионов и культуре, по сделкам
     * @param $regions      -   массив регионов
     * @param $cunture_id   -   ID культуры
     * @param $days         -   дней назад для поиска
     * @param $offer_id     -   id товара
     */
    static function getAveragePriceByRigionsAndCulture($regions, $culture_id, $days, $offer_id){
        $wh_array = self::getRegionFarmersWH($regions);
        $price_count = 0;
        $price_summ = 0;
        $average_sum = 0;
        $days++; //дней назад со вчерашнего
        if((sizeof($wh_array))&&(is_array($wh_array))){
            $el_obj = new CIBlockElement();
            $reqs_data = array();
            $res = $el_obj->GetList(
                array('ID' => 'ASC'),
                array(
                    'IBLOCK_ID' => rrsIblock::getIBlockId('deals_deals'),
                    'PROPERTY_FARMER_WAREHOUSE' => array_keys($wh_array),
                    'PROPERTY_CULTURE' => $culture_id,
                    '<DATE_CREATE' => date('d.m.Y ',strtotime('-1 day')).'23:59:59',
                    '>DATE_CREATE' => date('d.m.Y ',strtotime('-'.$days.' day')).'00:00:00'
                ),
                false,
                false,
                array('PROPERTY_REQUEST', 'PROPERTY_CENTER', 'PROPERTY_OFFER', 'PROPERTY_BASE_PRICE', 'PROPERTY_CLIENT_WAREHOUSE', 'PROPERTY_CLIENT_WAREHOUSE.PROPERTY_MAP')
            );
            if($res->SelectedRowsCount() > 0) {
                while($data = $res->Fetch()){
                    //берем все цены с места, для рассматриваемого запроса
                    $reqs_data[$data['PROPERTY_REQUEST_VALUE']][] = array(
                        'OFFER_ID' => $data['PROPERTY_OFFER_VALUE'],
                        'PRICE' => $data['PROPERTY_BASE_PRICE_VALUE'], //базисная цена, посчитанная для цены с места, указанной в предложении
                        'REQUEST_WH' => $data['PROPERTY_CLIENT_WAREHOUSE_VALUE'],
                        'REQUEST_WH_MAP' => $data['PROPERTY_CLIENT_WAREHOUSE_PROPERTY_MAP_VALUE'],
                        'CENTER' => $data['PROPERTY_CENTER_VALUE'],
                    );
                }

                //получение средней цены для запросов, участвующих в парах с учетом параметров товара
                if(count($reqs_data) > 0){
                    $average_sum = self::getDealsPricesWithOffer($offer_id, $reqs_data);
                }
            }
        }
        if((!empty($price_count))&&(!empty($price_summ))){
            $average_sum = round($price_summ/$price_count,0);
        }
        return $average_sum;
    }


    /**
     * Получение средней цены с места от цен пар, с учетом качества товара
     * @param $offer_id - id товара
     * @param $deal_requests_data - данные запросов
     * @return float средняя цена
     */
    static function getDealsPricesWithOffer($offer_id, $deal_requests_data){
        $result = 0;

        $price_sum = 0;
        $price_count = 0;

        $arAgrohelperTariffs = model::getAgrohelperTariffs();
        $arCulturesGroup = culture::getCulturesGroup();
        $offer = farmer::getOfferById($offer_id);
        $requests_data = client::getRequestListByIDs(array_keys($deal_requests_data), false, true);

        $routes = array();

        //обрабатываем цены с места пар
        foreach($requests_data as $cur_pos => $cur_item){
            if(isset($deal_requests_data[$cur_item['ID']])){
                //обрабатываем все цены с места для рассматриваемых пар (в котором участвует текущий запрос данного запроса)
                foreach($deal_requests_data[$cur_item['ID']] as $deal_data){
                    //определяем базисную цену для данного запроса по указанной цене с места

                    //определяем необходимые параметры для расчета цены (сброс по качеству и тариф на перевозку)
                    $dump = self::getDump($cur_item['PARAMS'], $offer['PARAMS']);
                    if(!isset($routes[$offer['WH_ID']][$deal_data['REQUEST_WH']])) {
                        //получаем расстояние между складами
                        $temp_route = log::getRouteCache($offer['WH_ID'], $deal_data['REQUEST_WH']);
                        if(isset($temp_route[$offer['WH_ID']][$deal_data['REQUEST_WH']])){
                            $routes[$offer['WH_ID']][$deal_data['REQUEST_WH']] = $temp_route[$offer['WH_ID']][$deal_data['REQUEST_WH']];
                        }else{
                            //добавляем расстояние между складами
                            $route = rrsIblock::getRoute($offer['WH_MAP'], $cur_item['REQUEST_WH_MAP']);
                            $routes[$offer['WH_ID']][$deal_data['REQUEST_WH']] = $route;
                            log::addRouteCacheItem($offer['WH_ID'], $deal_data['REQUEST_WH'], $route);
                        }

                    }
                    $tarif = client::getTarif($cur_item['CLIENT_ID'], $arCulturesGroup[$cur_item['CULTURE_ID']], 'fca', $deal_data['CENTER'], $routes[$offer['WH_ID']][$deal_data['REQUEST_WH']], $arAgrohelperTariffs);

                    //расчитываем цену
                    $csm_data = lead::makeCSMFromClientBase($deal_data['PRICE'], $cur_item['USER_NDS'] == 'yes', $offer['USER_NDS'] == 'yes', $dump, $tarif, array('delivery_type' => 'cpt')); //расчет идёт всегда по схеме dap (но тариф используется fca)

                    //считаем сумму цен
                    $price_sum += $csm_data['UF_CSM_PRICE'];
                    $price_count++;
                }
            }
        }

        if($price_count > 0){
            $result = $price_sum / $price_count;
        }

        return $result;
    }


    /**
     * Получение средней цены на основе сделок (с учетом качества товара)
     * @param $offer_id
     * @param $days - дней назад, начиная со вчерашнего
     * @param $linked_regions - признак того, что необходимо искать в связанных регионах (по умолчанию нет)
     */
    static function getAveragePrice($offer_id, $days, $linked_regions = false){
        $offer = farmer::getOfferById($offer_id);
        $average_price = 0;
        if((isset($offer['WH_REGION']))&&($offer['WH_REGION'])&&(!empty($offer['CULTURE_ID']))){
            //поиск рекомендации в рамках региона товара
            if(!$linked_regions){
                $average_price = self::getAveragePriceByRigionsAndCulture(array($offer['WH_REGION']), $offer['CULTURE_ID'], $days, $offer_id);
            }else{
                //поиск рекомендации в связанных регионах с регионом товара
                $linkRegions = getLinkedRegions($offer['WH_REGION']);
                $average_price = self::getAveragePriceByRigionsAndCulture($linkRegions,$offer['CULTURE_ID'],$days, $offer_id);
            }
        }
        return $average_price;
    }


    /**
     * Добавление записи о новой цене
     * @param $offer_id
     * @param $region_id
     * @param $culture_id
     * @param $price
     * @param int $region_link
     * @param int $iRequestId
     */
    static function addNewBestPrice($offer_id,$region_id,$culture_id,$price,$region_link,$iRequestId = 0){
        $hl_id = rrsIblock::HLgetIBlockId('BESTPRICES');
        global $DB;
        $data = array(
            'UF_DATE' => $DB->FormatDate(date("d.m.Y H:i:s"), "DD.MM.YYYY HH:MI:SS", FORMAT_DATETIME),
            'UF_OFFER_ID' => $offer_id,
            'UF_REGION_ID' => $region_id,
            'UF_CULTURE_ID' => $culture_id,
            'UF_PRICE' => $price,
            'UF_REGION_LINK' => $region_link,
            'UF_REQUEST_ID' => $iRequestId,
        );
        return log::_createEntity($hl_id, $data);
    }


    /**
     * Обновление цены
     * @param $price            - цена
     * @param int $iRequestId
     * @param $offer_id         - id товара
     * @param int $link_region  - признак соседний регион или нет
     * @param int $entId        - id записи в HL блоке, если он не указан, то будет произведен поиск по $offer_id и $link_region
     */
    static function updateBestPrice($id, $price,$iRequestId = 0){
        $hl_id = rrsIblock::HLgetIBlockId('BESTPRICES');
        $arFields = array(
            'UF_PRICE' => $price,
            'UF_REQUEST_ID' => $iRequestId,
        );
        $result = log::_updateEntity($hl_id, $id, $arFields);
       return $result;
    }

    /**
     * Обновление цен товаров и добавление новых для соседних регионов
     * @param $culture_id       - ID культуры
     * @param $offers_prices    - товар + цены
     * @param $offersData       - культуры и регионы товаров
     * @param array $arrBestOfferRequest - массив ID товаров и ID запросов, дающих для товара лушчую цену
     */
    static function SearchInLinkReg($culture_id,$offers_prices,$offersData, $arrBestOfferRequest = array()){
        $rec_prices = self::getBestPricesList($culture_id,1);
        $link_regions = array();
        $now = date('d.m.Y');
        $best_prices = array();
        //ищим лучшую цену в разрезе культуры и региона, среди найденных товаров
        //это нужно, чтобы даже в начале обхода и добавления новых элементов можно было узнать лучшую цену
        // и не делать повторный обход массива, после добавления новых элементов
        foreach ($offers_prices as $offer_id=>$price){
            $req_cult_key = $offersData[$offer_id]['CULTURE_ID'];
            if(isset($link_regions[$offersData[$offer_id]['WH_REGION']])){
                $link_reg = $link_regions[$offersData[$offer_id]['WH_REGION']];
            }else{
                $link_regions[$offersData[$offer_id]['WH_REGION']] = getLinkedRegions($offersData[$offer_id]['WH_REGION']);
                $link_reg = $link_regions[$offersData[$offer_id]['WH_REGION']];
            }
            if((sizeof($link_reg))&&(is_array($link_reg))){
                for($i=0,$c=sizeof($link_reg);$i<$c;$i++){
                    $link_key = $req_cult_key.'|'.$link_reg[$i];
                    if(isset($rec_prices[$link_key])){
                        if(!isset($rec_prices[$link_key][$offer_id])){
                            $rec_prices[$link_key][$offer_id] = array($now => array(
                                'UF_OFFER_ID' => $offer_id,
                                'UF_REQUEST_ID' => (isset($arrBestOfferRequest[$offer_id]) ? $arrBestOfferRequest[$offer_id] : 0),
                                'UF_DATE' => $now,
                                'UF_CULTURE_ID' => $offersData[$offer_id]['CULTURE_ID'],
                                'UF_REGION_ID' => $offersData[$offer_id]['WH_REGION'],
                                'UF_PRICE' => $price,
                                'UF_REGION_LINK' => 1,
                                'NEW' => 1
                            ));
                        }
                        foreach ($rec_prices[$link_key] as $sub_offer_id => $values) {
                            //если есть данные на текущую дату
                            if(isset($rec_prices[$link_key][$sub_offer_id][$now])){
                                if($price>$rec_prices[$link_key][$sub_offer_id][$now]['UF_PRICE']){
                                    $rec_prices[$link_key][$sub_offer_id][$now]['UF_PRICE'] = $price;
                                    if(!isset($rec_prices[$link_key][$sub_offer_id][$now]['NEW'])){
                                        $rec_prices[$link_key][$sub_offer_id][$now]['UPDATE'] = 1;
                                    }
                                }
                            }else{
                                //если нет, то добавляем в начало элемент с текущей датой
                                $ADD = array(
                                    $now => array(
                                        'UF_OFFER_ID' => $offer_id,
                                        'UF_REQUEST_ID' => (isset($arrBestOfferRequest[$offer_id]) ? $arrBestOfferRequest[$offer_id] : 0),
                                        'UF_DATE' => $now,
                                        'UF_CULTURE_ID' => $offersData[$offer_id]['CULTURE_ID'],
                                        'UF_REGION_ID' => $offersData[$offer_id]['WH_REGION'],
                                        'UF_PRICE' => $price,
                                        'UF_REGION_LINK' => 1,
                                        'NEW' => 1
                                    )
                                );
                                $rec_prices[$link_key][$sub_offer_id] = $ADD + $rec_prices[$link_key][$sub_offer_id];
                            }
                        }
                    }else{
                        $rec_prices[$link_key][$offer_id] = array($now => array(
                            'UF_OFFER_ID' => $offer_id,
                            'UF_REQUEST_ID' => (isset($arrBestOfferRequest[$offer_id]) ? $arrBestOfferRequest[$offer_id] : 0),
                            'UF_DATE' => $now,
                            'UF_CULTURE_ID' => $offersData[$offer_id]['CULTURE_ID'],
                            'UF_REGION_ID' => $offersData[$offer_id]['WH_REGION'],
                            'UF_PRICE' => $price,
                            'UF_REGION_LINK' => 1,
                            'NEW' => 1
                        ));
                    }

                }
            }
        }
        //объединяем массив с соседними регионами, с группировкой по исходному региону
        $rec_region_prices = array();
        foreach ($rec_prices as $key=>$val){
            foreach($val as $offer_id=>$values){
                foreach($values as $date=>$fields){
                    if(isset($rec_region_prices[$fields['UF_OFFER_ID']][$date])){
                        if($rec_region_prices[$fields['UF_OFFER_ID']][$date]['UF_PRICE']<$fields['UF_PRICE']){
                            $rec_region_prices[$fields['UF_OFFER_ID']][$date] = $fields;
                        }
                    }else{
                        $rec_region_prices[$fields['UF_OFFER_ID']][$date] = $fields;
                    }
                }
            }
        }
        //сохраняем данные в HL блок
        self::saveBestPrice($rec_region_prices);
    }


    /**
     * обновление цен и добавление новых товаров
     * @param $offers_prices    - товар + цены
     * @param $rec_prices       - данные полученные из HL блока
     * @param $offersData       - культуры и регион товаров
     * @param array $arrBestOfferRequest - массив ID товаров и ID запросов, дающих для товара лушчую цену (соответствует id товара в массиве $offers_prices)
     * @return mixed
     */
    static function SearchOffersPrices($offers_prices,$rec_prices,$offersData, $arrBestOfferRequest = array()){
        $now = date('d.m.Y');
        $best_prices = array();
        $best_prices_requests = array();
        //ищим лучшую цену в разрезе культуры и региона, среди найденных товаров
        //это нужно, чтобы даже в начале обхода и добавления новых элементов можно было узнать лучшую цену
        // и не делать повторный обход массива, после добавления новых элементов
        foreach ($offers_prices as $offer_id=>$price){
            $req_cult_key = $offersData[$offer_id]['CULTURE_ID'].'|'.$offersData[$offer_id]['WH_REGION'];
            if(!isset($best_prices[$req_cult_key])){
                $best_prices[$req_cult_key] = $price;
                //также запоминаем ID запроса, давшего цену
                if(isset($arrBestOfferRequest[$offer_id])) {
                    $best_prices_requests[$req_cult_key] = $arrBestOfferRequest[$offer_id];
                }
            }else{
                if($best_prices[$req_cult_key]<$price){
                    $best_prices[$req_cult_key] = $price;
                    //также запоминаем ID запроса, давшего цену
                    if(isset($arrBestOfferRequest[$offer_id])) {
                        $best_prices_requests[$req_cult_key] = $arrBestOfferRequest[$offer_id];
                    }
                }
            }
        }
        foreach ($offers_prices as $offer_id=>$price){
            $req_cult_key = $offersData[$offer_id]['CULTURE_ID'].'|'.$offersData[$offer_id]['WH_REGION'];
            if(isset($rec_prices[$req_cult_key])) {
                if(!isset($rec_prices[$req_cult_key][$offer_id])){
                    $rec_prices[$req_cult_key][$offer_id] = array($now => array(
                        'UF_OFFER_ID' => $offer_id,
                        'UF_DATE' => $now,
                        'UF_CULTURE_ID' => $offersData[$offer_id]['CULTURE_ID'],
                        'UF_REGION_ID' => $offersData[$offer_id]['WH_REGION'],
                        'UF_PRICE' => $best_prices[$req_cult_key],
                        'UF_REGION_LINK' => 0,
                        'UF_REQUEST_ID' => (isset($best_prices_requests[$req_cult_key]) ? $best_prices_requests[$req_cult_key] : 0),
                        'NEW' => 1
                    ));
                }
                foreach ($rec_prices[$req_cult_key] as $sub_offer_id => $values) {
                    //если есть данные на текущую дату
                    if (isset($values[$now])) {
                        if ($best_prices[$req_cult_key] > $rec_prices[$req_cult_key][$sub_offer_id][$now]['UF_PRICE']) {
                            $rec_prices[$req_cult_key][$sub_offer_id][$now]['UF_PRICE'] = $best_prices[$req_cult_key];
                            if (!isset($rec_prices[$req_cult_key][$sub_offer_id][$now]['NEW'])) {
                                $rec_prices[$req_cult_key][$sub_offer_id][$now]['UPDATE'] = 1;
                            }
                        }
                    } else {
                        //если нет, то добавляем в начало элемент с текущей датой
                        $ADD = array(
                            $now => array(
                                'UF_OFFER_ID' => $sub_offer_id,
                                'UF_DATE' => $now,
                                'UF_CULTURE_ID' => $offersData[$offer_id]['CULTURE_ID'],
                                'UF_REGION_ID' => $offersData[$offer_id]['WH_REGION'],
                                'UF_PRICE' => $best_prices[$req_cult_key],
                                'UF_REGION_LINK' => 0,
                                'UF_REQUEST_ID' => (isset($best_prices_requests[$req_cult_key]) ? $best_prices_requests[$req_cult_key] : 0),
                                'NEW' => 1
                            )
                        );
                        $rec_prices[$req_cult_key][$sub_offer_id] = $ADD + $rec_prices[$req_cult_key][$sub_offer_id];
                    }
                }
            }
            else{
                $rec_prices[$req_cult_key][$offer_id] = array($now => array(
                    'UF_OFFER_ID' => $offer_id,
                    'UF_DATE' => $now,
                    'UF_CULTURE_ID' => $offersData[$offer_id]['CULTURE_ID'],
                    'UF_REGION_ID' => $offersData[$offer_id]['WH_REGION'],
                    'UF_PRICE' => $best_prices[$req_cult_key],
                    'UF_REQUEST_ID' => (isset($best_prices_requests[$req_cult_key]) ? $best_prices_requests[$req_cult_key] : 0),
                    'UF_REGION_LINK' => 0,
                    'NEW' => 1
                ));
            }
        }
        return $rec_prices;
    }


    /**
     * Обновление цен товаров и добавление новых для своего региона товара
     * @param $culture_id       - ID культуры
     * @param $offers_prices    - товар + цены
     * @param $offersData       - культуры и регионы товаров
     * @param array $arrBestOfferRequest - массив ID товаров и ID запросов, дающих для товара лушчую цену
     */
    static function SearchInMainReg($culture_id,$offers_prices,$offersData, $arrBestOfferRequest = array()){
        //получаем данные из HL блока
        $rec_prices = self::getBestPricesList($culture_id);
        //проходим массив, найденный в HL блоке, добавляем новые товары, обновляем цены если нужно
        $rec_prices = self::SearchOffersPrices($offers_prices,$rec_prices,$offersData,$arrBestOfferRequest);
        $rec_region_prices = array();
        //подготавливаем массив для сохранения результатов
        foreach ($rec_prices as $key=>$params) {
            foreach ($params as $offer_id => $values) {
                $rec_region_prices[$offer_id] = $values;
            }
        }
        //сохраняем данные в HL блок
        self::saveBestPrice($rec_region_prices);
    }

    /**
     * Сохранение данных о лучших ценах в HL блок
     * @param $rec_region_prices
     */
    static function saveBestPrice($rec_region_prices){
        $hl_id = rrsIblock::HLgetIBlockId('BESTPRICES');
        foreach ($rec_region_prices as $offer_id=>$values){
            $i = 0;
            krsort($values);
            foreach ($values as $date=>$fields){
                $i++;
                if(isset($fields['NEW'])){
                    self::addNewBestPrice(
                        $fields['UF_OFFER_ID'],
                        $fields['UF_REGION_ID'],
                        $fields['UF_CULTURE_ID'],
                        $fields['UF_PRICE'],
                        $fields['UF_REGION_LINK'],
                        (isset($fields['UF_REQUEST_ID']) ? $fields['UF_REQUEST_ID'] : 0)
                    );
                }
                if(isset($fields['UPDATE'])){
                    self::updateBestPrice(
                        $fields['ID'],
                        $fields['UF_PRICE'],
                        (isset($fields['UF_REQUEST_ID']) ? $fields['UF_REQUEST_ID'] : 0)
                    );
                }
                if($i>2){
                    if($fields['ID']){
                        log::_deleteEntity($hl_id,$fields['ID']);
                    }
                }
            }
        }
    }

    /**
     * Обновление цен товаров и добавление новых товаров в HL блок
     * @param $culture_id    - ID культуры
     * @param $offers_prices - ID товаров и цена которая добавляется в LEADLIST
     * @param array $arrBestOfferRequest - массив ID товаров и ID запросов, дающих для товара лушчую цену
     */
    static function setBestPriceArr($culture_id, $offers_prices, $arrBestOfferRequest = array()){
        if((sizeof($offers_prices))&&(is_array($offers_prices))){
            //получаем культуры и регионы товаров
            $offersData = farmer::getOffersCulturesAndRegionsByIds(array_keys($offers_prices));
            //корректируем цены, с учетом СНО поставщика (в бд всегда храним цены без НДС)
            $nds_val = rrsIblock::getConst('nds');
            foreach($offers_prices as $cur_offer_id => $cur_price){
                if(isset($offersData[$cur_offer_id])
                    && $offersData[$cur_offer_id]['NDS']
                ){
                    //убираем НДС из цены
                    $offers_prices[$cur_offer_id] = round($offers_prices[$cur_offer_id] / (1 + 0.01 * $nds_val), 2);
                }
            }
            //обрабатываем данные по региону товаров
            self::SearchInMainReg($culture_id,$offers_prices,$offersData, $arrBestOfferRequest);
            //обрабатываем данные по соседним регионам
            self::SearchInLinkReg($culture_id,$offers_prices,$offersData, $arrBestOfferRequest);
        }
    }


    /**
     * Получение лучших цен, которые хранятся в HL блоке
     * @param $CULTURE_ID       - ID культуры
     * @param int $region_link  - признак того, брать товары по собственному региону или по соседним
     * @return array
     */
    static function getBestPricesList($CULTURE_ID,$region_link = 0){
        $hl_id = rrsIblock::HLgetIBlockId('BESTPRICES');
        $result = array();
        //проверяем есть ли данные по текущему региону и культуре
        $arFilter = array(
            'UF_CULTURE_ID' => $CULTURE_ID,
            'UF_REGION_LINK' => $region_link
        );
        $logObj = new log;
        $entityDataClass = $logObj->getEntityDataClass($hl_id);
        $el = new $entityDataClass;
        $rsData = $el->getList(array(
            'select' => array('ID', 'UF_OFFER_ID', 'UF_DATE', 'UF_CULTURE_ID', 'UF_REGION_ID', 'UF_PRICE', 'UF_REGION_LINK', 'UF_REQUEST_ID'),
            'filter' => $arFilter,
            'order'  => array('UF_DATE' => 'DESC')
        ));
        if($region_link == 1){
            $link_regions = array();
            while($res = $rsData->fetch()){
                $temp_data = explode(' ', $res['UF_DATE']->toString());
                $res['UF_DATE'] = $temp_data[0];
                $req_cult_key = $res['UF_CULTURE_ID'];
                if(isset($link_regions[$res['UF_REGION_ID']])){
                    $link_reg = $link_regions[$res['UF_REGION_ID']];
                }else{
                    $link_regions[$res['UF_REGION_ID']] = getLinkedRegions($res['UF_REGION_ID']);
                    $link_reg = $link_regions[$res['UF_REGION_ID']];
                }
                //подготавливаем ключи для соседних регионов
                if((sizeof($link_reg))&&(is_array($link_reg))){
                    for($i=0,$c=sizeof($link_reg);$i<$c;$i++){
                        $link_key = $req_cult_key.'|'.$link_reg[$i];
                        $result[$link_key][$res['UF_OFFER_ID']][$res['UF_DATE']] = $res;
                    }
                }
            }
        }else{
            while($res = $rsData->fetch()){
                $temp_data = explode(' ', $res['UF_DATE']->toString());
                $res['UF_DATE'] = $temp_data[0];
                $result[$res['UF_CULTURE_ID'].'|'.$res['UF_REGION_ID']][$res['UF_OFFER_ID']][$res['UF_DATE']] = $res;
            }
        }
        return $result;
    }


    /**
     * Получение лучшей цены товара
     * @param $offerId
     * @param string $farmer_nds - код СНО поставщика (необязательный, если не задан, то будет проверен внутри функции) (y|n)
     * @param int $iStepDays - допустимая разница между последней и предпоследней ценой (для новой логики расчета рекомендуемой цены) (необязательно)
     */
    static function getBestPrice($offerId, $farmer_nds = '', $iStepDays = 0){
        $hl_id = rrsIblock::HLgetIBlockId('BESTPRICES');
        $result = array();
        //проверяем есть ли данные по текущему региону и культуре
        $arFilter = array(
            'UF_OFFER_ID' => $offerId,
            'UF_REGION_LINK' => 0
        );
        if($farmer_nds == '') {
            $offerData = farmer::getOfferById($offerId);
            $farmer_nds = ($offerData['USER_NDS'] == 'yes');
        }else{
            $farmer_nds = ($farmer_nds == 'y');
        }
        $nds_val = rrsIblock::getConst('nds');
        $bSkip = false;
        $iTempTstmp = 0;
        $logObj = new log;
        $entityDataClass = $logObj->getEntityDataClass($hl_id);
        $el = new $entityDataClass;
        $rsData = $el->getList(array(
            'select' => array('ID', 'UF_PRICE', 'UF_DATE'),
            'filter' => $arFilter,
            'order'  => array('UF_DATE' => 'DESC'),
            'limit'  => 2
        ));
        while($res = $rsData->fetch()){
            $res['UF_DATE'] = $res['UF_DATE']->toString();

            //если нужно брать вторую дату не далее чем на несколько дней назад
            if($iStepDays > 0) {
                //если берем первую дату
                if($iTempTstmp == 0) {
                    $iTempTstmp = MakeTimeStamp($res['UF_DATE']);
                }
                //если нужно сверить даты
                elseif($iTempTstmp - MakeTimeStamp($res['UF_DATE']) > $iStepDays * 24 * 3600){
                    $bSkip = true;
                }
            }

            //если требуется пропустить другие даты
            if($bSkip){
                continue;
            }

            //учитываем СНО поставщика
            $temp_price = $res['UF_PRICE'];
            if($farmer_nds){
                $temp_price = $temp_price + $temp_price * 0.01 * $nds_val; //добавляем НДС к значению
            }

            $result[] = $temp_price;
        }
        if(sizeof($result)==0){
            //для смежных регионов
            $arFilter = array(
                'UF_OFFER_ID' => $offerId,
                'UF_REGION_LINK' => 1
            );
            $logObj = new log;
            $entityDataClass = $logObj->getEntityDataClass($hl_id);
            $el = new $entityDataClass;
            $rsData = $el->getList(array(
                'select' => array('ID', 'UF_PRICE', 'UF_DATE'),
                'filter' => $arFilter,
                'order'  => array('UF_DATE' => 'DESC')
            ));
            while($res = $rsData->fetch()){
                $temp_data = explode(' ', $res['UF_DATE']->toString());
                $res['UF_DATE'] = $temp_data[0];
                $result[] = $res['UF_PRICE'];
            }
        }
        return $result;
    }


    /**
     * Получение лучшей цены товара для цены рекомендации (измененнная логика)
     * @param $offerId
     * @param string $farmer_nds - код СНО поставщика (необязательный, если не задан, то будет проверен внутри функции) (y|n)
     * @param int $iStepDays - допустимая разница между сегодняшней и предыдущей ценой (для новой логики расчета рекомендуемой цены) (необязательно)
     */
    static function getBestPriceForRecommend($offerId, $farmer_nds = '', $iStepDays = 0){
        $result = array();

        //получаем данные для текущей даты (из таблицы BESTOFFERPRICES)
        $arFilter = array(
            'UF_OFFER_ID' => $offerId,
            'UF_DATE' => date('d.m.Y'),
        );
        $logObj = new log;
        $entityDataClass = $logObj->getEntityDataClass(rrsIblock::HLgetIBlockId('BESTOFFERPRICES'));
        $el = new $entityDataClass;
        $rsData = $el->getList(array(
            'select' => array('ID', 'UF_BEST_CSM_PRICE'),
            'filter' => $arFilter,
            'order'  => array('UF_DATE' => 'DESC'),
            'limit'  => 1
        ));
        if($res = $rsData->fetch()){
            $result[0] = $res['UF_BEST_CSM_PRICE'];
        }

        //проверяем есть ли данные по текущему региону и культуре для предыдущих дат (из таблицы BESTPRICES)
        $arFilter = array(
            'UF_OFFER_ID' => $offerId,
            'UF_REGION_LINK' => 0,
            '>=UF_DATE' => date('d.m.Y', strtotime('-' . $iStepDays . ' DAY')),
            '<UF_DATE' => date('d.m.Y'),
        );
        if($farmer_nds == '') {
            $offerData = farmer::getOfferById($offerId);
            $farmer_nds = ($offerData['USER_NDS'] == 'yes');
        }else{
            $farmer_nds = ($farmer_nds == 'y');
        }
        $nds_val = rrsIblock::getConst('nds');
        $logObj = new log;
        $entityDataClass = $logObj->getEntityDataClass(rrsIblock::HLgetIBlockId('BESTPRICES'));
        $el = new $entityDataClass;
        $rsData = $el->getList(array(
            'select' => array('ID', 'UF_PRICE', 'UF_DATE'),
            'filter' => $arFilter,
            'order'  => array('UF_DATE' => 'DESC'),
            'limit'  => 1
        ));
        if($res = $rsData->fetch()){
            $res['UF_DATE'] = $res['UF_DATE']->toString();

            //учитываем СНО поставщика
            $temp_price = $res['UF_PRICE'];
            if($farmer_nds){
                $temp_price = $temp_price + $temp_price * 0.01 * $nds_val; //добавляем НДС к значению
            }

            $result[1] = $temp_price;
        }
        return $result;
    }


    /**
     * Получение рекоммендованной цены для товара
     * @param int $offerId - ID товара
     * @param string $farmer_nds - код СНО поставщика (y | n, необязательный, если не задан, то будет проверен внутри получения данных) (y|n)
     * @param string $iCulture - ID культуры
     * @param array $arrPairPricesNoNds - массив с двумя лучшими ценами пар для региона товара (необязательно, в случае отсутствия значения будет браться из товара, цены указываются без НДС)
     */
    static function getRecommendedPrice($offerId, $farmer_nds = '', $iCulture = '', $arrPairPricesNoNds = array()){
        $result = 0;

        $iStepDays = 3; //расстояние между датами bp1 и bp2, и от текущей даты до ap2

        //устанавливаем тип НДС, если не задан
        if($farmer_nds == ''
            || $iCulture == ''
        ) {
            $offerData = farmer::getOfferById($offerId);
            $farmer_nds = ($offerData['USER_NDS'] == 'yes' ? 'y' : 'n');
            $iCulture = $offerData['CULTURE_ID'];
        }else{
            $farmer_nds = ($farmer_nds == 'y' ? $farmer_nds : 'n');
        }
        $arrPairPrices = array();

        //получаем цены из пар
        if(count($arrPairPricesNoNds) == 0){
            //если пары не были переданы в параметрах
            $arrSearchRegions = farmer::getRegionByOffer($offerId);
            $arrPairPrices = self::getPairPricesForRecommend($arrSearchRegions, $farmer_nds, $iCulture, $iStepDays);
        }
        else{
            if($farmer_nds == 'y'
                && (
                    !empty($arrPairPricesNoNds[0])
                    || !empty($arrPairPricesNoNds[1])
                )
            ){
            //если пары не был переданы в параметрах (без НДС)
                $nds_val = rrsIblock::getConst('nds');
                if (!empty($arrPairPricesNoNds[0])) {
                    //добавляем НДС к значению
                    $arrPairPrices[0] = $arrPairPricesNoNds[0] + $arrPairPricesNoNds[0] * 0.01 * $nds_val;
                }
                if (!empty($arrPairPricesNoNds[1])) {
                    //добавляем НДС к значению
                    $arrPairPrices[1] = $arrPairPricesNoNds[1] + $arrPairPricesNoNds[1] * 0.01 * $nds_val;
                }
            }
        }

        //получаем лучшие цены
        $best_price = self::getBestPriceForRecommend($offerId, $farmer_nds, $iStepDays);

        //производим расчет
        if(
            !empty($best_price[0])
            && !empty($arrPairPrices[0])
        ){
            //расчет b1 - 1: bp1 * 1,02
            if($best_price[0] > $arrPairPrices[0]){
                $result = $best_price[0] * 1.02;
            }
            //расчет b1 - 2: ap1 * 1,02
            else{
                $result = $arrPairPrices[0] * 1.02;
            }
        }elseif (
            !empty($best_price[0])
            && !empty($best_price[1])
            && empty($arrPairPrices[0])
            && !empty($arrPairPrices[1])
        ){
            //расчет b2: ap2 * bp1 / bp2
            $result = $arrPairPrices[1] * $best_price[0] / $best_price[1];
        }elseif(
            !empty($best_price[0])
            && empty($best_price[1])
            && empty($arrPairPrices[0])
            && !empty($arrPairPrices[1])
        ){
            //расчет b3 - 1: bp1 * 1,02
            if($best_price[0] > $arrPairPrices[1]){
                $result = $best_price[0] * 1.02;
            }
            //расчет b3 - 2: ap2 * 1,02
            else{
                $result = $arrPairPrices[1] * 1.02;
            }
        }elseif(!empty($best_price[0])){
            //расчет b4: bp1 * 1,02
            $result = $best_price[0] * 1.02;
        }
        //округляем цену рекомендации до 50 (<=25 округляется до 0, <=75 округляется до 50, >75 округляется до 100)
        $result = round($result);
        $iTempMod = $result % 100;
        $result = floor($result / 100) * 100 + ($iTempMod > 75 ? 100 : ($iTempMod > 25 ? 50 : 0));

        return round($result,0);
    }

    /**
     * Текст о рекумендованной цене, выводимый в шаблонах на добавление предложений
     * @param $offerId
     * @param $js - флаг того, что данные будут выводиться в js и что требуется экранизация одиночной кавычки
     * @param string $farmer_nds - код СНО поставщика (необязательный, если не задан, то будет проверен внутри получения данных) (y|n)
     * @param array $arrOnlyVals - массив с ключами передачи (если нужны только значения)
     */
    static function getRecommendedPriceText($offerId, $js = false, $farmer_nds = '', $arrOnlyVals = array()){
        $text = '';
        $n_date_m = getMonthName(date("n"));
        $n_date_d = date("j");

        $y_date_m = getMonthName(date("n",time() - 86400));
        $y_date_d = date("j",time() - 86400);

        $average_price = deal::getAveragePrice($offerId,7, false);
        $rec_price = deal::getRecommendedPrice($offerId, $farmer_nds);

        //если нужны только значения, то возвращаем их
        if(!empty($arrOnlyVals['rec_price'])){
            $arrResult['rec_price'] = $rec_price;
        }
        if(!empty($arrOnlyVals['average_price'])){
            $arrResult['average_price'] = $average_price;
        }
        if(count($arrOnlyVals) > 0){
            return (!empty($arrResult) ? $arrResult : array());
        }

        if(!empty($rec_price)){
            if(!empty($average_price)){
                $average_price = round($average_price, 0);
                if(!$js){
                    $text = '<div class="pr_1">Рынок: <div class="pr_val rowed">'.number_format($average_price, 0, '.', ' ').' руб/т</div></div>';
                }else{
                    $text = '<div class=\'pr_1\'>Рынок: <div class=\'pr_val rowed\'>'.number_format($average_price, 0, '.', ' ').' руб/т</div></div>';
                }
            }

            $rec_price = round($rec_price, 0);
            if(!$js){
                $text .= '<div class="pr_1">Рекомендация цены: <div class="pr_val_rec rowed"> '.number_format($rec_price, 0, '.', ' ').' руб/т</div></div>';
            }else{
                ob_start();
                $text .= ob_get_clean();
                $text .= '<div class=\'pr_1\'>Рекомендация цены: <div class=\'pr_val_rec rowed\'> '.number_format($rec_price, 0, '.', ' ').' руб/т</div></div>';
            }

            if($text != '')
            {
                if(!$js) {
                    $text = '<div class="r_price_block">' . $text . '</div>';
                }else{
                    $text = '<div class=\'r_price_block\'>' . $text . '</div>';
                }
            }
        }


        return $text;
    }

    /**
     * Получение и сохранение данных для пар, теряемых при удалении пользователя
     * @param array $deals_ids - массив ID пар
     */
    static function savePairDataByIds($deals_ids){
        $my_c = 0;
        $work_ids = array();
        if(is_numeric($deals_ids)){
            $work_ids[] = $deals_ids;
        }elseif(is_array($deals_ids)){
            $work_ids = $deals_ids;
        }

        //получаем те пары, из обновляемых
        if(count($work_ids) > 0){
            //дополнительные данные
            $clients_data = array();
            $farmers_data = array();
            $deals_data = array();

            $offers_ids = array();
            $quality_ids = array();

            CModule::IncludeModule('iblock');
            $el_obj = new CIBlockElement;
            $res = $el_obj->GetList(
                array('ID' => 'DESC'),
                array(
                    'IBLOCK_ID' => rrsIblock::getIBlockId('deals_deals'),
                    'ID' => $work_ids
                ),
                false,
                false,
                array(
                    'ID',
                    'PROPERTY_CULTURE',
                    'PROPERTY_CLIENT_WAREHOUSE.PROPERTY_ADDRESS',
                    'PROPERTY_FARMER_WAREHOUSE.PROPERTY_ADDRESS',
                    'PROPERTY_CLIENT',
                    'PROPERTY_FARMER',
                    'PROPERTY_DEL_CLIENT_REQUISITES',
                    'PROPERTY_DEL_CLIENT_WHADRESS',
                    'PROPERTY_DEL_FARMER_REQUISITES',
                    'PROPERTY_DEL_FARMER_WHADRESS',
                    'PROPERTY_DEL_PARAMS',
                    'PROPERTY_OFFER',
                )
            );
            while($data = $res->Fetch()){
                $deals_data[$data['ID']] = array(
                    'CLIENT' => $data['PROPERTY_CLIENT_VALUE'],
                    'FARMER' => $data['PROPERTY_FARMER_VALUE']
                );
                if(isset($data['PROPERTY_CLIENT_WAREHOUSE_PROPERTY_ADDRESS_VALUE'])
                    && trim($data['PROPERTY_CLIENT_WAREHOUSE_PROPERTY_ADDRESS_VALUE']) != ''
                ){
                    $deals_data[$data['ID']]['CLIENT_WAREHOUSE_ADDRESS'] = $data['PROPERTY_CLIENT_WAREHOUSE_PROPERTY_ADDRESS_VALUE'];
                }
                if(isset($data['PROPERTY_FARMER_WAREHOUSE_PROPERTY_ADDRESS_VALUE'])
                    && trim($data['PROPERTY_FARMER_WAREHOUSE_PROPERTY_ADDRESS_VALUE']) != ''
                ){
                    $deals_data[$data['ID']]['FARMER_WAREHOUSE_ADDRESS'] = $data['PROPERTY_FARMER_WAREHOUSE_PROPERTY_ADDRESS_VALUE'];
                }

                $clients_data[$data['PROPERTY_CLIENT_VALUE']] = array();
                $farmers_data[$data['PROPERTY_FARMER_VALUE']] = array();

                if(!empty($data['PROPERTY_CULTURE_VALUE'])){
                    $deals_data[$data['ID']]['CULTURE'] = $data['PROPERTY_CULTURE_VALUE'];
                }

                if(is_numeric($data['PROPERTY_OFFER_VALUE'])) {
                    $offers_ids[$data['PROPERTY_OFFER_VALUE']] = true;
                    $deals_data[$data['ID']]['OFFER'] = $data['PROPERTY_OFFER_VALUE'];
                }
            }

            $active_params_info = array();
            $res = $el_obj->GetList(
                array('ID' => 'DESC'),
                array('IBLOCK_ID' => rrsIblock::getIBlockId('characteristics'), 'ACTIVE' => 'Y'),
                false,
                false,
                array('ID', 'NAME', 'PROPERTY_CULTURE', 'PROPERTY_QUALITY', 'PROPERTY_QUALITY.NAME')
            );
            while ($data = $res->Fetch()) {
                $active_params_info[$data['PROPERTY_CULTURE_VALUE']][$data['PROPERTY_QUALITY_VALUE']] = array(
                    'ID' => $data['ID'],
                    'QUALITY_NAME' => $data['PROPERTY_QUALITY_NAME']
                );
            }

            //получение параметров
            $temp_offers_params = array();
            if(count($offers_ids) > 0) {
                $temp_offers_params = farmer::getParamsList(array_keys($offers_ids));
            }

            //дособираем данные пользователей
            if(count($clients_data) > 0
                || count($farmers_data) > 0
            ){
                //получение ФИО пользователей
                $res = CUser::GetList(
                    ($by = 'ID'), ($order = 'ASC'),
                    array(
                        'ID' => implode(' | ', array_merge(array_keys($clients_data), array_keys($farmers_data)))
                    ),
                    array(
                        'FIELDS' => array('ID', 'NAME', 'LAST_NAME', 'SECOND_NAME')
                    )
                );
                while($data = $res->Fetch()){
                    if(isset($clients_data[$data['ID']])){
                        $clients_data[$data['ID']]['FIO'] = trim($data['LAST_NAME'] . ' ' . $data['NAME'] . ' ' . $data['SECOND_NAME']);
                    }elseif(isset($farmers_data[$data['ID']])){
                        $farmers_data[$data['ID']]['FIO'] = trim($data['LAST_NAME'] . ' ' . $data['NAME'] . ' ' . $data['SECOND_NAME']);
                    }
                }

                if(count($clients_data) > 0) {
                    //получение данных профиля покупателя
                    $user_type_ip = rrsIblock::getPropListKey('client_profile', 'UL_TYPE', 'ip');
                    $res = $el_obj->GetList(
                        array('ID' => 'DESC'),
                        array(
                            'IBLOCK_ID' => rrsIblock::getIBlockId('client_profile'),
                            'PROPERTY_USER' => array_keys($clients_data)
                        ),
                        false,
                        false,
                        array(
                            'ID',
                            'PROPERTY_USER',
                            'PROPERTY_PHONE',
                            'PROPERTY_INN',
                            'PROPERTY_UL_TYPE',
                            'PROPERTY_FULL_COMPANY_NAME',
                            'PROPERTY_IP_FIO',
                            'PROPERTY_YUR_ADRESS',
                            'PROPERTY_POST_ADRESS',
                            'PROPERTY_KPP',
                            'PROPERTY_OGRN',
                            'PROPERTY_OKPO',
                        )
                    );
                    while ($data = $res->Fetch()) {
                        if (isset($clients_data[$data['PROPERTY_USER_VALUE']])) {
                            //получение названия организации
                            $temp_val = '';
                            if ($user_type_ip == $data['PROPERTY_UL_TYPE_VALUE']) {
                                $temp_val = 'ИП ' . trim($data['PROPERTY_IP_FIO_VALUE']);
                            } else {
                                $temp_val = trim($data['PROPERTY_FULL_COMPANY_NAME_VALUE']);
                            }
                            $clients_data[$data['PROPERTY_USER_VALUE']]['ORG_NAME'] = $temp_val;

                            //получение реквизитов
                            $temp_val = '';
                            if (trim($data['PROPERTY_INN_VALUE']) != '') {
                                $temp_val .= 'ИНН: ' . trim($data['PROPERTY_INN_VALUE']);
                            }
                            if (trim($data['PROPERTY_YUR_ADRESS_VALUE']) != '') {
                                $temp_val .= ($temp_val != '' ? ', ' : '') . 'Юридический адрес: ' . trim($data['PROPERTY_YUR_ADRESS_VALUE']);
                            }
                            if (trim($data['PROPERTY_POST_ADRESS_VALUE']) != '') {
                                $temp_val .= ($temp_val != '' ? ', ' : '') . 'Адрес для корреспонденции: ' . trim($data['PROPERTY_POST_ADRESS_VALUE']);
                            }
                            if (trim($data['PROPERTY_KPP_VALUE']) != '') {
                                $temp_val .= ($temp_val != '' ? ', ' : '') . 'КПП: ' . trim($data['PROPERTY_KPP_VALUE']);
                            }
                            if (trim($data['PROPERTY_OGRN_VALUE']) != '') {
                                $temp_val .= ($temp_val != '' ? ', ' : '') . 'ОГРН' . ($user_type_ip == $data['PROPERTY_UL_TYPE_VALUE'] ? ' ИП' : '') . ': ' . trim($data['PROPERTY_OGRN_VALUE']);
                            }
                            if (trim($data['PROPERTY_OKPO_VALUE']) != '') {
                                $temp_val .= ($temp_val != '' ? ', ' : '') . 'ОКПО: ' . trim($data['PROPERTY_OKPO_VALUE']);
                            }
                            $clients_data[$data['PROPERTY_USER_VALUE']]['DEL_CLIENT_REQUISITES'] = $temp_val;

                            //получение телефона
                            if (trim($data['PROPERTY_PHONE_VALUE']) != '') {
                                $clients_data[$data['PROPERTY_USER_VALUE']]['PHONE'] = trim($data['PROPERTY_PHONE_VALUE']);
                            }
                        }
                    }
                }

                if(count($farmers_data) > 0) {
                    //получение данных профиля поставщика
                    $user_type_ip = rrsIblock::getPropListKey('farmer_profile', 'UL_TYPE', 'ip');
                    $res = $el_obj->GetList(
                        array('ID' => 'DESC'),
                        array(
                            'IBLOCK_ID' => rrsIblock::getIBlockId('farmer_profile'),
                            'PROPERTY_USER' => array_keys($farmers_data)
                        ),
                        false,
                        false,
                        array(
                            'ID',
                            'PROPERTY_USER',
                            'PROPERTY_PHONE',
                            'PROPERTY_INN',
                            'PROPERTY_UL_TYPE',
                            'PROPERTY_FULL_COMPANY_NAME',
                            'PROPERTY_IP_FIO',
                            'PROPERTY_YUR_ADRESS',
                            'PROPERTY_POST_ADRESS',
                            'PROPERTY_KPP',
                            'PROPERTY_OGRN',
                            'PROPERTY_OKPO'
                        )
                    );
                    while ($data = $res->Fetch()) {
                        if (isset($farmers_data[$data['PROPERTY_USER_VALUE']])) {
                            //получение названия организации
                            $temp_val = '';
                            if ($user_type_ip == $data['PROPERTY_UL_TYPE_VALUE']) {
                                $temp_val = 'ИП ' . trim($data['PROPERTY_IP_FIO_VALUE']);
                            } else {
                                $temp_val = trim($data['PROPERTY_FULL_COMPANY_NAME_VALUE']);
                            }
                            $farmers_data[$data['PROPERTY_USER_VALUE']]['ORG_NAME'] = $temp_val;

                            //получение реквизитов
                            $temp_val = '';
                            if (trim($data['PROPERTY_INN_VALUE']) != '') {
                                $temp_val .= 'ИНН: ' . trim($data['PROPERTY_INN_VALUE']);
                            }
                            if (trim($data['PROPERTY_YUR_ADRESS_VALUE'])) {
                                $temp_val .= ($temp_val != '' ? ', ' : '') . 'Юридический адрес: ' . trim($data['PROPERTY_YUR_ADRESS_VALUE']);
                            }
                            if (trim($data['PROPERTY_POST_ADRESS_VALUE'])) {
                                $temp_val .= ($temp_val != '' ? ', ' : '') . 'Адрес для корреспонденции: ' . trim($data['PROPERTY_POST_ADRESS_VALUE']);
                            }
                            if (trim($data['PROPERTY_KPP_VALUE'])) {
                                $temp_val .= ($temp_val != '' ? ', ' : '') . 'КПП: ' . trim($data['PROPERTY_KPP_VALUE']);
                            }
                            if (trim($data['PROPERTY_OGRN_VALUE'])) {
                                $temp_val .= ($temp_val != '' ? ', ' : '') . 'ОГРН' . ($user_type_ip == $data['PROPERTY_UL_TYPE_VALUE'] ? ' ИП' : '') . ': ' . trim($data['PROPERTY_OGRN_VALUE']);
                            }
                            if (trim($data['PROPERTY_OKPO_VALUE'])) {
                                $temp_val .= ($temp_val != '' ? ', ' : '') . 'ОКПО: ' . trim($data['PROPERTY_OKPO_VALUE']);
                            }
                            $farmers_data[$data['PROPERTY_USER_VALUE']]['DEL_FARMER_REQUISITES'] = $temp_val;

                            //получение телефона
                            if (trim($data['PROPERTY_PHONE_VALUE']) != '') {
                                $farmers_data[$data['PROPERTY_USER_VALUE']]['PHONE'] = trim($data['PROPERTY_PHONE_VALUE']);
                            }
                        }
                    }
                }
            }

            //обновляем данные в парах
            $arProps = array();
            $deals_ib = rrsIblock::getIBlockId('deals_deals');
            foreach($deals_data as $cur_deal_id => $cur_deal_data){
                $arProps = array();
                //данные покупателя
                if(isset($clients_data[$cur_deal_data['CLIENT']])){
                    if(!empty($clients_data[$cur_deal_data['CLIENT']]['ORG_NAME'])) {
                        $arProps['DEL_CLIENT_ORG'] = $clients_data[$cur_deal_data['CLIENT']]['ORG_NAME'];
                    }
                    if(!empty($clients_data[$cur_deal_data['CLIENT']]['FIO'])) {
                        $arProps['DEL_CLIENT_FIO'] = $clients_data[$cur_deal_data['CLIENT']]['FIO'];
                    }
                    if(!empty($clients_data[$cur_deal_data['CLIENT']]['DEL_CLIENT_REQUISITES'])) {
                        $arProps['DEL_CLIENT_REQUISITES'] = $clients_data[$cur_deal_data['CLIENT']]['DEL_CLIENT_REQUISITES'];
                    }
                    if(!empty($clients_data[$cur_deal_data['CLIENT']]['PHONE'])) {
                        $arProps['DEL_CLIENT_PHONE'] = $clients_data[$cur_deal_data['CLIENT']]['PHONE'];
                    }
                    if(!empty($cur_deal_data['CLIENT_WAREHOUSE_ADDRESS'])) {
                        $arProps['DEL_CLIENT_WHADRESS'] = $cur_deal_data['CLIENT_WAREHOUSE_ADDRESS'];
                    }
                }
                //данные поставщика
                if(isset($farmers_data[$cur_deal_data['FARMER']])){
                    if(!empty($farmers_data[$cur_deal_data['FARMER']]['ORG_NAME'])) {
                        $arProps['DEL_FARMER_ORG'] = $farmers_data[$cur_deal_data['FARMER']]['ORG_NAME'];
                    }
                    if(!empty($farmers_data[$cur_deal_data['FARMER']]['FIO'])) {
                        $arProps['DEL_FARMER_FIO'] = $farmers_data[$cur_deal_data['FARMER']]['FIO'];
                    }
                    if(!empty($farmers_data[$cur_deal_data['FARMER']]['DEL_FARMER_REQUISITES'])) {
                        $arProps['DEL_FARMER_REQUISITES'] = $farmers_data[$cur_deal_data['FARMER']]['DEL_FARMER_REQUISITES'];
                    }
                    if(!empty($farmers_data[$cur_deal_data['FARMER']]['PHONE'])) {
                        $arProps['DEL_FARMER_PHONE'] = $farmers_data[$cur_deal_data['FARMER']]['PHONE'];
                    }
                    if(!empty($cur_deal_data['FARMER_WAREHOUSE_ADDRESS'])) {
                        $arProps['DEL_FARMER_WHADRESS'] = $cur_deal_data['FARMER_WAREHOUSE_ADDRESS'];
                    }
                }
                //данные параметров
                if(isset($cur_deal_data['OFFER'])
                    && isset($temp_offers_params[$cur_deal_data['OFFER']])){
                    foreach ($temp_offers_params[$cur_deal_data['OFFER']] as $param){
                        if(!isset($active_params_info[$cur_deal_data['CULTURE']][$param['QUALITY_ID']]['QUALITY_NAME'])){
                            continue;
                        }

                        $quality_ids[$param['QUALITY_ID']] = true;
                    }

                    //получаем наименования параметров качества
                    $unit_info = array();
                    $res = $el_obj->GetList(
                        array('SORT' => 'ASC', 'ID' => 'ASC'),
                        array(
                            'IBLOCK_ID' => rrsIblock::getIBlockId('quality'),
                            'ACTIVE' => 'Y',
                            'ID' => array_keys($quality_ids)
                        ),
                        false,
                        false,
                        array('ID', 'PROPERTY_UNIT')
                    );
                    while ($data = $res->Fetch()) {
                        $unit_info[$data['ID']] = $data['PROPERTY_UNIT_VALUE'];
                    }

                    $temp_params_text = '';
                    foreach ($temp_offers_params[$cur_deal_data['OFFER']] as $param){
                        if(!isset($active_params_info[$cur_deal_data['CULTURE']][$param['QUALITY_ID']]['QUALITY_NAME'])){
                            continue;
                        }

                        $quality_ids[$param['QUALITY_ID']] = true;

                        $temp_params_text .= '<div class="val_adress">' . $active_params_info[$cur_deal_data['CULTURE']][$param['QUALITY_ID']]['QUALITY_NAME'] . ': '
                            . '<b>' . $param['BASE'] . (isset($unit_info[$param['QUALITY_ID']]) && trim(isset($unit_info[$param['QUALITY_ID']])) != '' ? ' ' . $unit_info[$param['QUALITY_ID']] : '') . '</b>'
                            . '</div>';
                    }

                    if($temp_params_text != ''){
                        $arProps['DEL_PARAMS'] = $temp_params_text;
                    }
                }
                if(count($arProps) > 0){
                    $el_obj->SetPropertyValuesEx($cur_deal_id, $deals_ib, $arProps);
                    $my_c++;
                }
            }
        }
    }

    /**
     * Получение типа предложения на основе которого создана пара (платное или агентское)
     * @param $deal_id
     * @return string
     */
    static function getDealType($deal_id){
        CModule::IncludeModule('iblock');
        $result = 'c';
        $res = CIBlockElement::GetList(
            array('ID' => 'DESC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('deals_deals'),
                'ACTIVE' => 'Y',
                'ID' => $deal_id
            ),
            false,
            false,
            array(
                'ID',
                'PROPERTY_COFFER_TYPE',
            )
        );
        if ($ob = $res->Fetch()) {
            if(!empty($ob['PROPERTY_COFFER_TYPE_VALUE'])){
                $result = $ob['PROPERTY_COFFER_TYPE_VALUE'];
            }
        }
        return $result;
    }

    /**
     * Получение типа предложения на основе которого создана пара (платное или агентское)
     * @param int $iOfferId - ID товара
     * @param int $iRequestId - ID запроса
     * @return int - ID пары
     */
    static function getIdByRequestAndOffer($iOfferId, $iRequestId, $bSetGlobalClient = false){
        $iResult = 0;

        $obRes = CIBlockElement::GetList(
            array('ID' => 'DESC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('deals_deals'),
                'PROPERTY_OFFER' => $iOfferId,
                'PROPERTY_REQUEST' => $iRequestId,
            ),
            false,
            array('nTopCount' => 1),
            array(
                'ID', 'PROPERTY_CLIENT'
            )
        );
        if ($arData = $obRes->Fetch()) {
            $iResult = $arData['ID'];

            if($bSetGlobalClient){
                $GLOBALS['CLIENT_ID'] = $arData['PROPERTY_CLIENT_VALUE'];
            }
        }
        return $iResult;
    }

    /**
     * Получение данных пары
     * @param int $iDeal - ID ары
     * @return array - данные пары
     */
    static function getById($iDeal){
        $arResult = array();

        $obRes = CIBlockElement::GetList(
            array('ID' => 'DESC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('deals_deals'),
                'ID' => $iDeal,
            ),
            false,
            array('nTopCount' => 1),
            array(
                'PROPERTY_ACC_PRICE_CSM', 'PROPERTY_VOLUME', 'PROPERTY_IS_ADD_CERT', 'PROPERTY_IS_AGENT_SUPPORT'
            )
        );
        if ($arData = $obRes->Fetch()) {
            $arResult = array(
                'PRICE_CSM' => $arData['PROPERTY_ACC_PRICE_CSM_VALUE'],
                'VOLUME' => $arData['PROPERTY_VOLUME_VALUE'],
                'IS_ADD_CERT' => $arData['PROPERTY_IS_ADD_CERT_VALUE'],
                'IS_AGENT_SUPPORT' => $arData['PROPERTY_IS_AGENT_SUPPORT_VALUE'],
            );
        }
        return $arResult;
    }

    /**
     * Получение лучших цен из пар для расчета рекомендованной цены (ищутся цены за сегодня и не далее чем в $iStepDays дней назад)
     * @param array|int $arrRegions - массив ID регионов, в которых искать пары
     * @param string $sFarmerNds - НДС поставщика (y | n), для которого рассчитывается рекомендованная цена
     * @param string $iCulture - ID культуры
     * @param int $iStepDays - разрешенная разница в датах между лучшими ценами пар (необязательно)
     * @return array - массив с лучшими ценами (одна последняя цена или две цены, с разницей не более чем $iStepDays дней)
     */
    static function getPairPricesForRecommend($arrRegions, $sFarmerNds, $iCulture, $iStepDays){
        $arResult = array();

        //получаем ID складов поставщиков в рассматриваемых регионах
        $arrFarmerWHIds = farmer::getWhByRegions($arrRegions);

        $arrTemp = array();
        $obRes = CIBlockElement::GetList(
            array('ID' => 'DESC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('deals_deals'),
                'PROPERTY_FARMER_WAREHOUSE' => $arrFarmerWHIds,
                'PROPERTY_CULTURE' => $iCulture,
                '>ACTIVE_FROM' => ConvertTimeStamp(strtotime('-' . $iStepDays . ' DAYS'), 'SHORT')
            ),
            false,
            false,
            array(
                'ACTIVE_FROM', 'PROPERTY_ACC_PRICE_CSM', 'PROPERTY_B_NDS'
            )
        );
        while ($arrData = $obRes->Fetch()) {
            $nTempPrice = $arrData['PROPERTY_ACC_PRICE_CSM_VALUE'];

            //если нужно добавить НДС к цене
            if(
                $sFarmerNds == 'y'
                && $arrData['PROPERTY_B_NDS_VALUE'] != 'Y'
            ){
                $nds_val = rrsIblock::getConst('nds');
                $nTempPrice = $nTempPrice + $nTempPrice * 0.01 * $nds_val;
            }
            //если нужно вычесть НДС из цены
            elseif(
                $sFarmerNds == 'n'
                && $arrData['PROPERTY_B_NDS_VALUE'] != 'N'
            ){
                $nds_val = rrsIblock::getConst('nds');
                $nTempPrice = $nTempPrice / (1 + 0.01 * $nds_val);
            }

            //обработка даты
            $arrTempDate = explode(' ', $arrData['ACTIVE_FROM']);

            //работа с ценами
            if(!empty($arrTempDate[0])){
                //добавляем значние для даты, если его еще нет
                if(!isset($arrTemp[$arrTempDate[0]])){
                    $arrTemp[$arrTempDate[0]] = $nTempPrice;
                }elseif($arrTemp[$arrTempDate[0]] < $nTempPrice){
                    $arrTemp[$arrTempDate[0]] = $nTempPrice;
                }
            }
        }

        //получение лучшей цены за сегодня и первой предыдущей лучшей цены
        $sCheckDate = date('d.m.Y');
        if(isset($arrTemp[$sCheckDate])){
            $arResult[0] = $arrTemp[$sCheckDate];
        }
        for($i = 1; $i <= $iStepDays; $i++){
            $sCheckDate = date('d.m.Y', strtotime('-' . $i . ' DAYS'));
            if(isset($arrTemp[$sCheckDate])){
                $arResult[1] = $arrTemp[$sCheckDate];
                break;
            }
        }

        return $arResult;
    }
}