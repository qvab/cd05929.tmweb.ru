<?
use \Bitrix\Highloadblock\HighloadBlockTable;

class lead {
    public static $hlLeadList = 8;

    public static function addLead($clientData, $farmerData, $route, $price, $csm_price = '', $price_base_contract = '') {
        global $DB;
        $data = array(
            'UF_DATE' => $DB->FormatDate(date("d.m.Y H:i:s"), "DD.MM.YYYY HH:MI:SS", FORMAT_DATETIME),
            'UF_CULTURE_ID' => $clientData['CULTURE'],
            'UF_CLIENT_ID' => $clientData['CLIENT'],
            'UF_REQUEST_ID' => $clientData['REQUEST'],
            'UF_CLIENT_WH_ID' => $clientData['WH'],
            'UF_CENTER_ID' => $clientData['CENTER'],
            'UF_FARMER_ID' => $farmerData['FARMER'],
            'UF_OFFER_ID' => $farmerData['OFFER'],
            'UF_FARMER_WH_ID' => $farmerData['WH'],
            'UF_ROUTE' => $route,
            'UF_BASE_PRICE' => $price,
            'UF_CSM_PRICE' => $csm_price,
            'UF_BASE_CONTR_PRICE' => $price_base_contract,
            'UF_NDS' => $farmerData['NDS'],
        );

        return log::_createEntity(self::$hlLeadList, $data);
    }

    public static function getLeadList($filter, $arOrder = []) {
        $entityDataClass = log::getEntityDataClass(self::$hlLeadList);
        $el = new $entityDataClass;

        $result = array();

        $rsData = $el->getList(array(
            'select' => array('*'),
            'filter' => $filter,
            'order'  => $arOrder
        ));
        while ($res = $rsData->fetch()) {
            $result[] = $res;
        }

        return $result;
    }

    public static function getLead($user_id, $r, $o) {
        $entityDataClass = log::getEntityDataClass(self::$hlLeadList);
        $el = new $entityDataClass;

        $result = array();

        $rsData = $el->getList(array(
            'select' => array('*'),
            'filter' => array(
                'UF_FARMER_ID' => $user_id,
                'UF_REQUEST_ID' => $r,
                'UF_OFFER_ID' => $o
            )
        ));
        if ($res = $rsData->fetch()) {
            $result = $res;
        }

        return $result;
    }

    /*
     * Получение ID соответствия по ID запроса и товара
     * @param int $o_id - id товара
     * @param int $r_id - id запроса
     * @return int id соответствия
     * */
    public static function getIDByOfferAndRequest($o_id, $r_id) {
        $result = 0;

        $entityDataClass = log::getEntityDataClass(self::$hlLeadList);
        $el = new $entityDataClass;

        $rsData = $el->getList(array(
            'select' => array('ID'),
            'filter' => array(
                'UF_REQUEST_ID' => $r_id,
                'UF_OFFER_ID' => $o_id
            ),
            'order' => array('ID' => 'DESC'),
            'limit' => 1
        ));
        if ($res = $rsData->fetch()) {
            $result = $res['ID'];
        }

        return $result;
    }

    /*
     * Организация списка соответствий
     * @param array $arLeads - массив соответствий
     *
     * @return array - массив соответствий, дополненный данными
     * */
    public static function createLeadList($arLeads) {
        $requestIds = $offerIds = $arRequests = $arOffers = array();
        foreach ($arLeads as $lead) {
            $requestIds[$lead['UF_REQUEST_ID']] = true;
            $offerIds[$lead['UF_OFFER_ID']] = true;
        }

        if (is_array($requestIds) && sizeof($requestIds) > 0) {
            $arRequests = client::getRequestListByIDs(array_keys($requestIds));
        }

        if (is_array($offerIds) && sizeof($offerIds) > 0) {
            $arOffers = farmer::getOfferListByIDs(array_keys($offerIds));
        }

        $offerRequestApply = array();

        if (sizeof($arRequests) > 0 && sizeof($arOffers) > 0) {
            $arAgrohelperTariffs = model::getAgrohelperTariffs();
            $arCulturesGroup = culture::getCulturesGroup();

            foreach ($arLeads as $lead) {
                $arOffer = $arOffers[$lead['UF_OFFER_ID']];
                $arRequest = $arRequests[$lead['UF_REQUEST_ID']];
                if (isset($arOffer['ID']) && $arRequest['ID']){

                    $discount = deal::getDump($arRequest['PARAMS'], $arOffer['PARAMS']);
                    if ($arRequest['NEED_DELIVERY'] == 'N')
                        $type = 'fca';
                    else
                        $type = 'cpt';

                    $arRequest['BEST_PRICE'] = farmer::bestPriceCalculation(
                        array(
                            'CLIENT_ID' => $arRequest['CLIENT_ID'],
                            'CLIENT_WH_ID' => $lead['UF_CLIENT_WH_ID'],
                            'CENTER' => $lead['UF_CENTER_ID'],
                            'ROUTE' => $lead['UF_ROUTE'],
                            'DDP_PRICE_CLIENT' => $lead['UF_BASE_PRICE'],
                            'CLIENT_NDS' => $arRequest['USER_NDS'],
                            'FARMER_NDS' => $arOffer['USER_NDS'],
                            'TYPE' => $type,
                            'DUMP' => $discount,
                            'TARIFF_LIST' => $arAgrohelperTariffs,
                            'CULTURE_GROUP_ID' => $arCulturesGroup[$arRequest['CULTURE_ID']]
                        )
                    );

                    unset($arRequest['PARAMS']);
                    unset($arOffer['PARAMS']);

                    $offerRequestApply[] = array(
                        'OFFER' => $arOffer,
                        'REQUEST' => $arRequest,
                        'LEAD' => $lead
                    );
                }else{
                    //не удается найти либо активного запроса, либо активного предложения -> соответствие некорректно и подлежит удалению
                    lead::deleteLeads(array($lead));
                }
            }
        }else{
            //не удалось найти либо активных запросов, либо активных предложений -> данные соответствия некорректны и подлежит удалению
            lead::deleteLeads($arLeads);
        }

        return $offerRequestApply;
    }

    public static function deleteLeads($arLeads) {
        $hl_counter_requests = rrsIblock::HLgetIBlockId('COUNTEROFFERS');
        $ar_cr_filter = array();

        foreach ($arLeads as $lead) {
            if(is_numeric($lead['ID'])) {
                log::_deleteEntity(self::$hlLeadList, $lead['ID']);

                //также удаляем ВП, если запись есть
                $ar_cr_filter['UF_REQUEST_ID'] = $lead['UF_REQUEST_ID'];
                $ar_cr_filter['UF_OFFER_ID'] = $lead['UF_OFFER_ID'];
                $cr_id = log::getCounterRequestByFilter($ar_cr_filter);
                if($cr_id > 0) {
                    log::_deleteEntity($hl_counter_requests, $cr_id);
                }
            }
        }
    }

    public static function getLeadList4bestPrice($farmerIds, $request_id, $culture_id) {
        $entityDataClass = log::getEntityDataClass(self::$hlLeadList);
        $el = new $entityDataClass;

        $result = array();

        $rsData = $el->getList(array(
            'select' => array('*'),
            'filter' => array(
                'UF_FARMER_ID' => $farmerIds,
                '!UF_REQUEST_ID' => $request_id,
                'UF_CULTURE_ID' => $culture_id
            ),
            'order' => array('UF_BASE_PRICE' => 'ASC')
        ));
        while ($res = $rsData->fetch()) {
            $result[$res['UF_FARMER_ID']] = $res['UF_BASE_PRICE'];
        }

        return $result;
    }

    /*
     * Рассчитывает Цену с места на основании базисной цены, учитывающей НДС покупателя (прямой расчет)
     * @param string $base_price - базисная цена (учитывает тип НДС покупателя) - БЦ
     * @param boolean $is_client_with_nds - тип НДС покупателя
     * @param boolean $is_farmer_with_nds - тип НДС АП
     * @param integer $dump - сброс/прибавка в процентах
     * @param integer $tarif - тариф
     * @param integer $additional_data - необязательный массив (используется когда makeCSMFromClientBase вызывается в цикле, чтобы избежать множественного получения констант из БД; содержит данные:
     *      float nds - процент НДС
     *      float comissionVal - величина комиссии
     *      )
     *
     * @return array - массив с данными:
     * UF_BASE_CONTR_PRICE (базисная цена контракта, т.е c учётом типа НДС АП) - БЦ или БЦСМ или БЦ контракта
     * UF_CSM_PRICE - цена с места (c учётом типа НДС АП) - ЦСМ или ЦМ
     * RC_PRICE - расчетная цена - РЦ
     * NDS_RUB - значение НДС в рублях
     * SBROS_RUB - значение сброса/прибавки в рублях
     * COMMISSION - величина комиссии
     * */
    public static function makeCSMFromClientBase($base_price, $is_client_with_nds, $is_farmer_with_nds, $dump, $tarif, $additional_data = array()){
        $result = array();

        $base_price = str_replace(' ', '', $base_price);

        $nds = (isset($additional_data['nds']) ? $additional_data['nds'] : rrsIblock::getConst('nds'));
        $commissionVal = (isset($additional_data['commission']) ? $additional_data['commission'] : rrsIblock::getConst('commission'));
        //$ndsValue = 0;
        $base_price_nds = 0;
        $delivery_type = ((isset($additional_data['delivery_type'])) ? $additional_data['delivery_type'] : 'cpt');

        if(!$is_farmer_with_nds && $is_client_with_nds){
            //если у АП и покупателя разные системы налогообложения (у АП - без НДС, у покупателя - с НДС)

            //расчет величины НДС, если нужна отладка: $ndsValue = 0.01 * $base_price * $nds / (1. + 0.01 * $nds);
            $base_price_nds = $base_price / (1 + 0.01 * $nds); //пример $base_price=100, $nds=15%, $base_price_nds=100/115=0,869565; обратно (проверяем) 0,869565 + 0,869565*15% = 100
        }elseif($is_farmer_with_nds && !$is_client_with_nds){
            //если у АП и покупателя разные системы налогообложения (у АП - с НДС, у покупателя - без НДС)

            //расчет величины НДС, если нужна отладка: $ndsValue = 0.01 * $base_price * $nds / (0.01 * $nds - 1.);
            $base_price_nds = $base_price / (1 - 0.01 * $nds); //пример $base_price=100, $nds=15%, $base_price_nds=100/0,85=117,647; обратно (проверяем) 117,647 - 117,647*15% = 100
        }
        else{
            $base_price_nds = $base_price;
        }

        if($delivery_type != 'cpt'){
            $base_price_nds = $base_price_nds - $tarif;
            //здесь же обработка комиссии если не dap
            //$kom_val = 0.01 * $kom_percents * $base_price_nds
            //$base_price_nds = $base_price_nds - $kom_val
        }

        $result['COMMISSION'] = 0.01 * $commissionVal * $base_price_nds;
        $result['UF_BASE_CONTR_PRICE'] = round($base_price_nds - $result['COMMISSION'], 2);
        $result['SBROS_RUB'] = round(0.01 * $base_price_nds * $dump, 2);
        $result['RC_PRICE'] = round($base_price_nds + $result['SBROS_RUB'], 2);

        $result['UF_CSM_PRICE'] = $result['RC_PRICE'];
        if($delivery_type == 'cpt'){
            $result['UF_CSM_PRICE'] = $result['UF_CSM_PRICE'] - $tarif;
        }
        $result['UF_CSM_PRICE'] = round($result['UF_CSM_PRICE'], 2);

        return $result;
    }

    /*
     * Рассчитывает базисную цену из цены с места (учитывающую НДС покупателя)(обратный расчет)
     * @param string $csm_price - цена с места (учитывает тип НДС АП) - ЦСМ или ЦМ
     * @param boolean $is_client_with_nds - тип НДС покупателя
     * @param boolean $is_farmer_with_nds - тип НДС АП
     * @param integer $dump - сброс/прибавка в процентах
     * @param integer $tarif - тариф
     * @param array $base_data - данные для получения базисной цены, учитывающей НДС покупателя (содержит данные:
     *      boolean get_base_client - получать ли BASE_PRICE (т.е. базисную цену с учетом налогообложения покупателя)
     *      float nds - процент НДС
     *      float comissionVal - величина комиссии
     *      )
     * @param boolean $get_csm_from_client - признак того нужно ли получать цену с места для налогообложения покупателя
     *
     * @return array - массив с данными:
     * BASE_CONTR_PRICE (базисная цена, c учётом типа НДС АП) - БЦ или БЦ контракта
     * BASE_PRICE (базисная цена, c учётом типа НДС покупателя) - БЦ или БЦ контракта
     * RC_PRICE - расчетная цена - РЦ
     * */
    public static function makeBaseFromCSM($csm_price, $is_client_with_nds, $is_farmer_with_nds, $dump, $tarif, $base_data = array(), $get_csm_from_client = false){
        $result = array();

        $csm_price = str_replace(' ', '', $csm_price);

        $result['RC_PRICE'] = $csm_price;
        $delivery_type = (isset($base_data['delivery_type']) && $base_data['delivery_type'] == 'fca' ? 'fca' : 'cpt');
        if($delivery_type == 'cpt'){
            $result['RC_PRICE'] = $result['RC_PRICE'] + $tarif;
        }
        $result['BASE_CONTR_PRICE'] = (100 * $result['RC_PRICE']) / (100 + $dump);
        $result['BASE_PRICE'] = 0;

        //получаем также базинсую цену с учетом НДС покупателя (добавляем также в вывод цену с места с учетом налогообложения покупателя)
        if(isset($base_data['get_base_client'])
            && $base_data['get_base_client'] == true
        ){
            //учёт комиссии и тарифа при fca
            if($delivery_type != 'cpt'){
                $result['BASE_PRICE'] = $result['BASE_CONTR_PRICE'] + $base_data['comissionVal'];
                $result['BASE_PRICE'] = $result['BASE_PRICE'] + $tarif;
            }
            else{
                $result['BASE_PRICE'] = $result['BASE_CONTR_PRICE'];
            }

            if(!$is_farmer_with_nds && $is_client_with_nds){
                //если у АП и покупателя разные системы налогообложения (у АП - без НДС, у покупателя - с НДС)

                //просто прибавляем процент НДС от значения базисной цены
                $result['BASE_PRICE'] = $result['BASE_PRICE'] + $result['BASE_PRICE'] * $base_data['nds'] / 100;
            }elseif($is_farmer_with_nds && !$is_client_with_nds){
                //если у АП и покупателя разные системы налогообложения (у АП - с НДС, у покупателя - без НДС)

                //просто вычитываем процент НДС от значения базисной цены
                $result['BASE_PRICE'] = $result['BASE_PRICE'] - $result['BASE_PRICE'] * $base_data['nds'] / 100;
            }
            $result['DUMP_RUB'] = $result['RC_PRICE'] - $result['BASE_CONTR_PRICE'];

            if($get_csm_from_client){
                //для получения цены с места в налогообложении покупателя - вызываем расчет с места, куда вместо типа НДС АП передаем тип НДС покупателя
                $result['CSM_FOR_CLIENT'] = self::makeCSMFromClientBase($result['BASE_PRICE'], $is_client_with_nds, $is_client_with_nds, $dump, $tarif, array('commission' => $base_data['comissionVal'], 'nds' => $base_data['nds']));
            }
        }

        return $result;
    }

    /*
     * Восстановление соответствий между пользователями (и отправка ВП для запросов)
     * @param int $farmer_id - id поставщика
     * @param int $client_id - id покупателя
     *
     * */
    public static function restoreLeadsFromBlacklist($farmer_id, $client_id){
        //$result = array();
        //Порядок выполнения
        //0. Проверяем, что пользователи не в черном списке организатора
        //1. Получаем активные запросы покупателя
        //2. Получаем активные товары поставщика
        //3.a. Строим пары между найденными запросами и товарами
        //3.b. Строим ВП между найденными запросами и товарами, если у товаров есть подходящие ВП

        //подготавиливаем данные для работы
        CModule::IncludeModule('highloadblock');
        $el_obj = new CIBlockElement;
        $requests_arr = array();
        $offers_arr = array();

        //0. Проверяем, что пользователи не в черном списке организатора
        $allFarmersBlackList = BlackList::getClientFarmersBL($client_id);
        if(isset($allFarmersBlackList[$farmer_id])){
            //пользователь в черном списке организатора
            return false;
        }

        //1. Получаем данные активных запросов покупателя
        $requests_arr = client::getRequestListDataByUser($client_id);

        //2. Получаем данные активных товаров поставщика
        $offers_arr = farmer::getOfferListByUser($farmer_id);

        //3. Строим пары между найденными запросами и товарами
        //и строим ВП между найденными запросами и товарами, если у товаров есть подходящие ВП
        if(count($requests_arr) > 0
            && count($offers_arr) > 0
        ){
            deal::searchSuitableLeads($requests_arr, $offers_arr);
        }

        //return $result;
    }
}
?>