<?
class client {
    /**
     * Получение запроса
     * @param  int $id идентификатор запроса
     * @return [] массив с информацией о запросе
     */
    public static function getRequestById($id) {
        CModule::IncludeModule('iblock');
        $result = array();
        $res = CIBlockElement::GetList(
            array('ID' => 'DESC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('client_request'),
                'ACTIVE' => 'Y',
                //'ACTIVE_DATE' => 'Y',
                'PROPERTY_ACTIVE' => rrsIblock::getPropListKey('client_request', 'ACTIVE', 'yes'),
                'ID' => $id
            ),
            false,
            false,
            array(
                'ID',
                'NAME',
                'DATE_CREATE',
                'DATE_ACTIVE_TO',
                'PROPERTY_CLIENT',
                'PROPERTY_USER_NDS',
                'PROPERTY_CULTURE',
                'PROPERTY_CULTURE.NAME',
                'PROPERTY_DELIVERY.CODE',
                'PROPERTY_MIN_REMOTENESS',
                'PROPERTY_REMOTENESS',
                'PROPERTY_USE_REGIONS',
                'PROPERTY_VOLUME',
                'PROPERTY_REMAINS',
                'PROPERTY_PAYMENT',
                'PROPERTY_PERCENT',
                'PROPERTY_DELAY',
                'PROPERTY_DOCS',
                'PROPERTY_NDS',
            )
        );
        if ($ob = $res->Fetch()) {
            $tmp = array(
                'ID' => $ob['ID'],
                'NAME' => $ob['NAME'],
                'DATE_CREATE' => $ob['DATE_CREATE'],
                'DATE_ACTIVE_TO' => $ob['DATE_ACTIVE_TO'],
                'CLIENT_ID' => $ob['PROPERTY_CLIENT_VALUE'],
                'USER_NDS' => rrsIblock::getPropListId('client_request', 'USER_NDS', $ob['PROPERTY_USER_NDS_ENUM_ID']),
                'CULTURE_ID' => $ob['PROPERTY_CULTURE_VALUE'],
                'CULTURE_NAME' => $ob['PROPERTY_CULTURE_NAME'],
                'NEED_DELIVERY' => $ob['PROPERTY_DELIVERY_CODE'],
                'REMOTENESS' => $ob['PROPERTY_REMOTENESS_VALUE'],
                'MIN_REMOTENESS' => $ob['PROPERTY_MIN_REMOTENESS_VALUE'],
                'USE_REGIONS' => $ob['PROPERTY_USE_REGIONS_VALUE'],
                'VOLUME' => $ob['PROPERTY_VOLUME_VALUE'],
                'REMAINS' => $ob['PROPERTY_REMAINS_VALUE'],
                'PAYMENT' => rrsIblock::getPropListId('client_request', 'PAYMENT', $ob['PROPERTY_PAYMENT_ENUM_ID']),
                'PERCENT' => $ob['PROPERTY_PERCENT_VALUE'],
                'DELAY' => $ob['PROPERTY_DELAY_VALUE'],
                'DOCS' => $ob['PROPERTY_DOCS_VALUE'],
                'NDS' => $ob['PROPERTY_NDS_VALUE'],
            );
            $result = $tmp;
            $result['PARAMS'] = current(self::getParamsList(array($id)));
            $result['COST'] = current(self::getCostList(array($id)));
        }

        return $result;
    }

    /**
     * Даты активности запросов
     * @param $ids - массив ID запросов
     * @return array
     */
    public static function getRequestsActions($ids){
        CModule::IncludeModule('iblock');
        $result = array();
        $res = CIBlockElement::GetList(
            array('ID' => 'DESC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('client_request'),
                'ID' => $ids
            ),
            false,
            false,
            array(
                'ID',
                'DATE_CREATE',
                'TIMESTAMP_X',
                'PROPERTY_ACTIVE'
            )
        );
        $yes = rrsIblock::getPropListKey('client_request', 'ACTIVE', 'yes');
        $no = rrsIblock::getPropListKey('client_request', 'ACTIVE', 'no');
        $cTime = date('d.m.Y');
        while ($ob = $res->Fetch()) {
            $date_create = date('d.m.Y',strtotime($ob['DATE_CREATE']));
            $date_timestamp_x = date('d.m.Y',strtotime($ob['TIMESTAMP_X']));
            if($ob['PROPERTY_ACTIVE_ENUM_ID'] == $yes){
                $tmp = array(
                    'DATE_CREATE' => $date_create,
                    'TIMESTAMP_X' => $cTime,
                );
            }else{
                if(strtotime($cTime) == strtotime($date_timestamp_x)){
                    $date_timestamp_x = date('d.m.Y',strtotime($date_timestamp_x) - 86400);
                }
                $tmp = array(
                    'DATE_CREATE' => $date_create,
                    'TIMESTAMP_X' => $date_timestamp_x,
                );
            }
            $result[$ob['ID']] = $tmp;
        }
        return $result;
    }

    /**
     * Получение условия оплаты
     * @param  int $id идентификатор запроса
     * @return [] массив с информацией о запросе
     */
    public static function getPaymentRequestById($id) {
        CModule::IncludeModule('iblock');
        $result = array();
        $res = CIBlockElement::GetList(
            array('ID' => 'DESC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('client_request'),
                'ID' => $id
            ),
            false,
            false,
            array(
                'PROPERTY_PAYMENT',
                'PROPERTY_PERCENT',
                'PROPERTY_DELAY',
            )
        );
        if ($ob = $res->Fetch()) {
            $tmp = array(
                'PAYMENT_TYPE' => [
                    'NAME'  => $ob['PROPERTY_PAYMENT_VALUE'],
                    'VALUE' => rrsIblock::getPropListId('client_request', 'PAYMENT', $ob['PROPERTY_PAYMENT_ENUM_ID'])
                ],
                'PERCENT' => $ob['PROPERTY_PERCENT_VALUE'],
                'DELAY' => $ob['PROPERTY_DELAY_VALUE'],
            );
            $result = $tmp;
        }
        return $result;
    }

    /**
     * Получение списка запросов, отобранных по критерию "культура"
     * @param  [] $cultureIds список идентификаторов культур
     * @return [] массив со списком запросов
     */
    public static function getRequestList($cultureIds) {
        CModule::IncludeModule('iblock');
        $result = array();
        $res = CIBlockElement::GetList(
            array('ID' => 'DESC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('client_request'),
                'ACTIVE' => 'Y',
                //'ACTIVE_DATE' => 'Y',
                'PROPERTY_ACTIVE' => rrsIblock::getPropListKey('client_request', 'ACTIVE', 'yes'),
                '>PROPERTY_REMAINS' => 0,
                'PROPERTY_CULTURE' => $cultureIds
            ),
            false,
            false,
            array(
                'ID',
                'NAME',
                'DATE_CREATE',
                'DATE_ACTIVE_TO',
                'PROPERTY_CLIENT',
                'PROPERTY_USER_NDS',
                'PROPERTY_CULTURE',
                'PROPERTY_CULTURE.NAME',
                'PROPERTY_DELIVERY.CODE',
                'PROPERTY_REMOTENESS',
                'PROPERTY_VOLUME',
                'PROPERTY_REMAINS',
                'PROPERTY_PAYMENT',
                'PROPERTY_PERCENT',
                'PROPERTY_DELAY',
                'PROPERTY_DOCS',
                'PROPERTY_NDS'
            )
        );
        while ($ob = $res->Fetch()) {
            $tmp = array(
                'ID' => $ob['ID'],
                'DATE_CREATE' => $ob['DATE_CREATE'],
                'DATE_ACTIVE_TO' => $ob['DATE_ACTIVE_TO'],
                'DATE_DIFF' => strtotime($ob['DATE_ACTIVE_TO'])-time(),
                'CLIENT_ID' => $ob['PROPERTY_CLIENT_VALUE'],
                'USER_NDS' => rrsIblock::getPropListId('client_request', 'USER_NDS', $ob['PROPERTY_USER_NDS_ENUM_ID']),
                'CULTURE_ID' => $ob['PROPERTY_CULTURE_VALUE'],
                'CULTURE_NAME' => $ob['PROPERTY_CULTURE_NAME'],
                'NEED_DELIVERY' => $ob['PROPERTY_DELIVERY_CODE'],
                'REMOTENESS' => $ob['PROPERTY_REMOTENESS_VALUE'],
                'VOLUME' => $ob['PROPERTY_VOLUME_VALUE'],
                'REMAINS' => $ob['PROPERTY_REMAINS_VALUE'],
                'PAYMENT' => rrsIblock::getPropListId('client_request', 'PAYMENT', $ob['PROPERTY_PAYMENT_ENUM_ID']),
                'PERCENT' => $ob['PROPERTY_PERCENT_VALUE'],
                'DELAY' => $ob['PROPERTY_DELAY_VALUE'],
                'DOCS' => $ob['PROPERTY_DOCS_VALUE'],
                'NDS' => $ob['PROPERTY_NDS_VALUE'],
            );
            $result[$ob['ID']] = $tmp;
        }

        $requestParams = client::getParamsList(array_keys($result));
        $requestCostList = client::getCostList(array_keys($result));
        foreach ($result as $key => $request) {
            $result[$key]['PARAMS'] = $requestParams[$key];
            $result[$key]['COST'] = $requestCostList[$key];
        }

        return $result;
    }

    /**
     * Получение списка запросов, отобранных по критерию "культура" и в регионах связанных с выбранным регионом
     * @param  [] $cultureIds список идентификаторов культур
     * @param  mixed $regionId id рассматриваемого региона/регионов
     * @return [] массив со списком запросов
     */
    public static function getRequestListWithRegion($cultureIds, $regionId){
        $result = array();

        $searchRegions = getLinkedRegions($regionId);
        //получение складов товаров для связанных регионов с регионом товара
        $linkedRegionWHIds = getWHListForRegions($searchRegions, 'c');
        $regionReqIds = array();

        //получение запросов из складов
        if(count($linkedRegionWHIds) > 0) {
            //получаем ID запросов по ID складов через записи стоимостей
            $regionReqIds = client::getRequestListByWh($linkedRegionWHIds);
        }

        if(count($regionReqIds) > 0) {

            $delivery_fca_elem_id = getFCAItemID();

            $res = CIBlockElement::GetList(
                array('ID' => 'DESC'),
                array(
                    'IBLOCK_ID' => rrsIblock::getIBlockId('client_request'),
                    'ACTIVE' => 'Y',
                    'ACTIVE_DATE' => 'Y',
                    'PROPERTY_ACTIVE' => rrsIblock::getPropListKey('client_request', 'ACTIVE', 'yes'),
                    '>PROPERTY_REMAINS' => 0,
                    'PROPERTY_CULTURE' => $cultureIds,
                    'ID' => $regionReqIds,
                    //добавляем сложную фильтрацию, для выбранных складов, если запрос FCA (по #13130 пользователь выбирает регионы)
                    array(
                        "LOGIC" => "OR",
                        array("PROPERTY_DELIVERY" => $delivery_fca_elem_id, "PROPERTY_USE_REGIONS" => $regionId),
                        array("!PROPERTY_DELIVERY" => $delivery_fca_elem_id),
                    ),
                ),
                false,
                false,
                array(
                    'ID',
                    'NAME',
                    'DATE_CREATE',
                    'DATE_ACTIVE_TO',
                    'PROPERTY_CLIENT',
                    'PROPERTY_USER_NDS',
                    'PROPERTY_CULTURE',
                    'PROPERTY_CULTURE.NAME',
                    'PROPERTY_DELIVERY.CODE',
                    'PROPERTY_REMOTENESS',
                    'PROPERTY_USE_REGIONS',
                    'PROPERTY_VOLUME',
                    'PROPERTY_REMAINS',
                    'PROPERTY_PAYMENT',
                    'PROPERTY_PERCENT',
                    'PROPERTY_DELAY',
                    'PROPERTY_DOCS',
                    'PROPERTY_NDS'
                )
            );
            while ($ob = $res->Fetch()) {
                $tmp = array(
                    'ID' => $ob['ID'],
                    'DATE_CREATE' => $ob['DATE_CREATE'],
                    'DATE_ACTIVE_TO' => $ob['DATE_ACTIVE_TO'],
                    'DATE_DIFF' => strtotime($ob['DATE_ACTIVE_TO']) - time(),
                    'CLIENT_ID' => $ob['PROPERTY_CLIENT_VALUE'],
                    'USER_NDS' => rrsIblock::getPropListId('client_request', 'USER_NDS', $ob['PROPERTY_USER_NDS_ENUM_ID']),
                    'CULTURE_ID' => $ob['PROPERTY_CULTURE_VALUE'],
                    'CULTURE_NAME' => $ob['PROPERTY_CULTURE_NAME'],
                    'NEED_DELIVERY' => $ob['PROPERTY_DELIVERY_CODE'],
                    'REMOTENESS' => $ob['PROPERTY_REMOTENESS_VALUE'],
                    'USE_REGIONS' => $ob['PROPERTY_USE_REGIONS_VALUE'],
                    'VOLUME' => $ob['PROPERTY_VOLUME_VALUE'],
                    'REMAINS' => $ob['PROPERTY_REMAINS_VALUE'],
                    'PAYMENT' => rrsIblock::getPropListId('client_request', 'PAYMENT', $ob['PROPERTY_PAYMENT_ENUM_ID']),
                    'PERCENT' => $ob['PROPERTY_PERCENT_VALUE'],
                    'DELAY' => $ob['PROPERTY_DELAY_VALUE'],
                    'DOCS' => $ob['PROPERTY_DOCS_VALUE'],
                    'NDS' => $ob['PROPERTY_NDS_VALUE'],
                );
                $result[$ob['ID']] = $tmp;
            }

            $requestParams = client::getParamsList(array_keys($result));
            $requestCostList = client::getCostList(array_keys($result));
            foreach ($result as $key => $request) {
                $result[$key]['PARAMS'] = $requestParams[$key];
                $result[$key]['COST'] = $requestCostList[$key];
            }
        }

        return $result;
    }

    /**
     * Получение id покупателя по id склада
     * @param  integer $wh_id - id склада
     * @return integer - id покупателя
     */
    public static function getClientByWH($wh_id) {
        CModule::IncludeModule('iblock');
        $result = 0;

        if(filter_var($wh_id, FILTER_VALIDATE_INT)) {
            $res = CIBlockElement::GetList(
                array('ID' => 'DESC'),
                array(
                    'IBLOCK_ID' => rrsIblock::getIBlockId('client_warehouse'),
                    'ID' => $wh_id,
                    '!PROPERTY_CLIENT_VALUE' => false
                ),
                false,
                array('nTopCount' => 1),
                array('PROPERTY_CLIENT')
            );
        }
        while ($data = $res->Fetch()) {
            $result = $data['PROPERTY_CLIENT_VALUE'];
        }

        return $result;
    }

    /**
     * Получение списка запросов, отобранных по идентификаторам
     * @param  [] $IDs список идентификаторов запросов
     * @param  bool $bActiveDate активность по дате (для пар)
     * @return [] массив со списком запросов
     */
    public static function getRequestListByIDs($IDs, $bActiveDate = true, $get_costs = false) {
        CModule::IncludeModule('iblock');
        $result = array();
        $arFilter = array(
            'IBLOCK_ID' => rrsIblock::getIBlockId('client_request'),
            'ACTIVE' => 'Y',
            'PROPERTY_ACTIVE' => rrsIblock::getPropListKey('client_request', 'ACTIVE', 'yes'),
            '>PROPERTY_REMAINS' => 0,
            'ID' => $IDs
        );
        /*if($bActiveDate){
            $arFilter['ACTIVE_DATE'] = 'Y';
        }*/
        $res = CIBlockElement::GetList(
            array('ID' => 'DESC'),
            $arFilter,
            false,
            false,
            array(
                'ID',
                'NAME',
                'DATE_CREATE',
                'DATE_ACTIVE_TO',
                'PROPERTY_CLIENT',
                'PROPERTY_USER_NDS',
                'PROPERTY_CULTURE',
                'PROPERTY_CULTURE.NAME',
                'PROPERTY_DELIVERY.CODE',
                'PROPERTY_REMOTENESS',
                'PROPERTY_VOLUME',
                'PROPERTY_REMAINS',
                'PROPERTY_PAYMENT',
                'PROPERTY_PERCENT',
                'PROPERTY_DELAY',
                'PROPERTY_DOCS',
                'PROPERTY_NDS'
            )
        );
        while ($ob = $res->Fetch()) {
            $tmp = array(
                'ID' => $ob['ID'],
                'DATE_CREATE' => $ob['DATE_CREATE'],
                'DATE_ACTIVE_TO' => $ob['DATE_ACTIVE_TO'],
                'DATE_DIFF' => strtotime($ob['DATE_ACTIVE_TO'])-time(),
                'CLIENT_ID' => $ob['PROPERTY_CLIENT_VALUE'],
                'USER_NDS' => rrsIblock::getPropListId('client_request', 'USER_NDS', $ob['PROPERTY_USER_NDS_ENUM_ID']),
                'CULTURE_ID' => $ob['PROPERTY_CULTURE_VALUE'],
                'CULTURE_NAME' => $ob['PROPERTY_CULTURE_NAME'],
                'NEED_DELIVERY' => $ob['PROPERTY_DELIVERY_CODE'],
                'REMOTENESS' => $ob['PROPERTY_REMOTENESS_VALUE'],
                'VOLUME' => $ob['PROPERTY_VOLUME_VALUE'],
                'REMAINS' => $ob['PROPERTY_REMAINS_VALUE'],
                'PAYMENT' => rrsIblock::getPropListId('client_request', 'PAYMENT', $ob['PROPERTY_PAYMENT_ENUM_ID']),
                'PERCENT' => $ob['PROPERTY_PERCENT_VALUE'],
                'DELAY' => $ob['PROPERTY_DELAY_VALUE'],
                'DOCS' => $ob['PROPERTY_DOCS_VALUE'],
                'NDS' => $ob['PROPERTY_NDS_VALUE'],
            );
            $result[$ob['ID']] = $tmp;
        }

        $requestParams = client::getParamsList(array_keys($result));
        if($get_costs){
            $requestCostList = client::getCostList(array_keys($result));
        }
        foreach ($result as $key => $request) {
            $result[$key]['PARAMS'] = $requestParams[$key];
            if($get_costs) {
                $result[$key]['COST'] = $requestCostList[$key];
            }
        }

        return $result;
    }

    /**
     * Проверка принадлежат ли указанные запросы проверяемому клиенту (если хотя бы один из переданных id запросов не принадлежит клиенту, то вернется false)
     * @param mixed $req_id - список идентификаторов запросов, либо id запроса
     * @param int $client_id идентивифактор пользователя клиента
     * @return [] массив со списком запросов
     */
    public static function getCompareRequestListWithClient($req_id, $client_id) {
        $result = true;

        //проверка на корректность переданных параметров
        if( !filter_var($req_id, FILTER_VALIDATE_INT) && (!is_array($req_id) || count($req_id) == 0)
            || $req_id == 0
            || !filter_var($client_id, FILTER_VALIDATE_INT) || $client_id == 0
        ){
            return false;
        }

        $check_req_arr = array();
        $get_list = array();
        if(filter_var($req_id, FILTER_VALIDATE_INT))
            $check_req_arr = array($req_id => true);
        else
            $check_req_arr = array_flip($req_id);

        CModule::IncludeModule('iblock');
        $res = CIBlockElement::GetList(
            array('ID' => 'DESC'),
            array(
                'IBLOCK_ID'         => rrsIblock::getIBlockId('client_request'),
                'ID'                => $req_id,
                'PROPERTY_CLIENT'   => $client_id
            ),
            false,
            false,
            array(
                'ID',
            )
        );
        if($res->SelectedRowsCount() == 0){
            $result = false;
        }
        else{
            while($ob = $res->Fetch()){
                $get_list[$ob['ID']] = true;
            }

            foreach($check_req_arr as $cur_id => $cur_val)
            {
                if(!isset($get_list[$cur_id]))
                {
                    $result = false;
                    break;
                }
            }
        }

        return $result;
    }

    /**
     * Получение НДС покупателя
     * @param  int $user_id идентификатор пользователя
     * @return string НДС покупателя (Y/N)
     */
    public static function getNds($user_id) {

        CModule::IncludeModule('iblock');

        $arEl = CIBlockElement::GetList(
            array('PROPERTY_USER' => 'ASC'),
            array(
                'IBLOCK_ID'     => rrsIblock::getIBlockId('client_profile'),
                'ACTIVE'        => 'Y',
                'PROPERTY_USER' => $user_id
            ),
            false,
            false,
            array('ID', 'PROPERTY_NDS.CODE',)
        )->Fetch();

        return $arEl['PROPERTY_NDS_CODE'];
    }


    /**
     * Получение наименование организации/имя пользователя
     *
     * Получение компании пользователя ИП или ООО
     * если данных нет, то получаение ФИО пользователя,
     * если данных нет, то EMAIL пользователя,
     * если EMAIL от телефона, то ID пользователя
     *
     * @param $user_ids - массив идентификаторов пользователей
     * @param bool $profile - возвращать ли информацию о пользователе
     *
     */
    public static function getUserCompanyNames($user_ids){
        CModule::IncludeModule('iblock');
        $res = CIBlockElement::GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('client_profile'),
                'ACTIVE' => 'Y',
                'PROPERTY_USER' => $user_ids
            ),
            false,
            false,
            array(
                'ID',
                'NAME',
                'PROPERTY_USER',
                'PROPERTY_FULL_COMPANY_NAME',
                'PROPERTY_IP_FIO',
            )
        );
        while ($ob = $res->Fetch()) {
            $name = '';
            if (trim($ob['PROPERTY_FULL_COMPANY_NAME_VALUE']) != '') {
                $name = $ob['PROPERTY_FULL_COMPANY_NAME_VALUE'];
            }
            elseif (trim($ob['PROPERTY_IP_FIO_VALUE']) != '') {
                $name = 'ИП ' . $ob['PROPERTY_IP_FIO_VALUE'];
            }
            $user_data = rrsIblock::getUserInfo($ob['PROPERTY_USER_VALUE']);
            if(empty($name)){
                $user_data = rrsIblock::getUserInfo($ob['PROPERTY_USER_VALUE']);
                $name = trim($user_data['LAST_NAME'].' '.$user_data['NAME'].' '.$user_data['SECOND_NAME']);
                if(empty($name)){
                    if(!checkEmailFromPhone($user_data['EMAIL'])){
                        $name = $user_data['EMAIL'];
                    }else{
                        $name = 'ID пользователя: '.$ob['PROPERTY_USER_VALUE'];
                    }
                }
            }
            $result[$ob['PROPERTY_USER_VALUE']] = $name;
        }
        return $result;
    }


    /**
     * Получение профиля клиента А
     * @param  int $user_id идентификатор пользователя
     *         bool $profile возвращать ли информацию о пользователе
     * @return [] массив с полями профиля
     */
    public static function getProfile($user_id, $profile = false) {
        CModule::IncludeModule('iblock');
        $res = CIBlockElement::GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('client_profile'),
                'ACTIVE' => 'Y',
                'PROPERTY_USER' => $user_id
            ),
            false,
            false,
            array(
                'ID',
                'NAME',
                'PROPERTY_NDS.CODE',
                'PROPERTY_FULL_COMPANY_NAME',
                'PROPERTY_IP_FIO',
                'PROPERTY_NOTICE',
                'PROPERTY_PHONE'
            )
        );
        if ($ob = $res->Fetch()) {
            if (trim($ob['PROPERTY_FULL_COMPANY_NAME_VALUE']) != '') {
                $ob['COMPANY'] = $ob['PROPERTY_FULL_COMPANY_NAME_VALUE'];
            }
            elseif (trim($ob['PROPERTY_IP_FIO_VALUE']) != '') {
                $ob['COMPANY'] = 'ИП ' . $ob['PROPERTY_IP_FIO_VALUE'];
            }
            $result = $ob;
        }

        if ($profile) {
            $result['USER'] = rrsIblock::getUserInfo($user_id);
        }

        return $result;
    }

    /**
     * Получение полной информации профиля клиента А
     * @param  int $user_id идентификатор пользователя
     * @return [] массив с полями профиля
     */
    public static function getFullProfile($user_id) {
        CModule::IncludeModule('iblock');
        $res = CIBlockElement::GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('client_profile'),
                'ACTIVE' => 'Y',
                'PROPERTY_USER' => $user_id
            ),
            false,
            false,
            array(
                'ID',
                'NAME',
                'PROPERTY_PHONE',
                'PROPERTY_UL_TYPE',
                'PROPERTY_INN',
                'PROPERTY_FULL_COMPANY_NAME',
                'PROPERTY_IP_FIO',
                'PROPERTY_YUR_ADRESS',
                'PROPERTY_POST_ADRESS',
                'PROPERTY_KPP',
                'PROPERTY_OGRN',
                'PROPERTY_OKPO',
                'PROPERTY_FIO_DIR',
                'PROPERTY_NDS.CODE',
                'PROPERTY_POST',
                'PROPERTY_FIO_SIGN',
                'PROPERTY_FOUNDATION',
                'PROPERTY_BANK',
                'PROPERTY_BIK',
                'PROPERTY_RASCH_SCHET',
                'PROPERTY_KOR_SCHET',
            )
        );
        if ($ob = $res->Fetch()) {
            if ($ob['PROPERTY_UL_TYPE_ENUM_ID'] > 0) {
                $ob['UL_TYPE'] = rrsIblock::getPropListId('client_profile', 'UL_TYPE', $ob['PROPERTY_UL_TYPE_ENUM_ID']);
            }
            if (!$ob['UL_TYPE']) {
                $ob['UL_TYPE'] = 'ul';
            }

            if ($ob['UL_TYPE'] == 'ip') {
                $ob['COMPANY'] = 'Индивидуальный предприниматель ' . $ob['PROPERTY_IP_FIO_VALUE'];
                $ob['ADDRESS'] = $ob['PROPERTY_POST_ADRESS_VALUE'];
                $ob['KPP'] = '';
                $ob['OGRN'] = 'ОГРНИП ' . $ob['PROPERTY_OGRN_VALUE'];
            }
            else {
                $ob['COMPANY'] = $ob['PROPERTY_FULL_COMPANY_NAME_VALUE'];
                $ob['ADDRESS'] = $ob['PROPERTY_YUR_ADRESS_VALUE'];
                $ob['KPP'] = 'КПП ' . $ob['PROPERTY_KPP_VALUE'];
                $ob['OGRN'] = 'ОГРН ' . $ob['PROPERTY_OGRN_VALUE'];
            }

            $result = $ob;
        }

        $result['USER'] = rrsIblock::getUserInfo($user_id);

        return $result;
    }

    /**
     * Получение списка параметров запросов
     * @param  [] $offerIds список идентификаторов запросов
     * @return [] массив со списком параметров
     */
    public static function getParamsList($requestIds) {
        CModule::IncludeModule('iblock');
        $res = CIBlockElement::GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('client_request_chars'),
                'ACTIVE' => 'Y',
                'PROPERTY_REQUEST' => $requestIds
            ),
            false,
            false,
            array(
                'ID',
                'NAME',
                'PROPERTY_REQUEST',
                'PROPERTY_QUALITY',
                'PROPERTY_LBASE',
                'PROPERTY_BASE',
                'PROPERTY_MIN',
                'PROPERTY_MAX',
                'PROPERTY_DUMPING',
            )
        );
        while ($ob = $res->Fetch()) {
            $ob['DUMPING'] = array();
            foreach ($ob['PROPERTY_DUMPING_VALUE'] as $d) {
                $delta = explode(";", str_replace(array("[", "]", ":"), array("", "", ";"), $d));
                $ob['DUMPING'][] = array('MN' => $delta[0], 'MX' => $delta[1], 'DUMP' => $delta[2]);
            }
            $tmp = array(
                'ID' => $ob['ID'],
                'NAME' => $ob['NAME'],
                'REQUEST_ID' => $ob['PROPERTY_REQUEST_VALUE'],
                'QUALITY_ID' => $ob['PROPERTY_QUALITY_VALUE'],
                'LBASE_ID' => $ob['PROPERTY_LBASE_VALUE'],
                'BASE' => $ob['PROPERTY_BASE_VALUE'],
                'MIN' => $ob['PROPERTY_MIN_VALUE'],
                'MAX' => $ob['PROPERTY_MAX_VALUE'],
                'DUMPING' => $ob['DUMPING'],
            );
            $result[$ob['PROPERTY_REQUEST_VALUE']][$ob['PROPERTY_QUALITY_VALUE']] = $tmp;
        }

        return $result;
    }

    /**
     * Получение списка стоимостей запросов
     * @param  [] $requestIds список идентификаторов запросов
     * @return [] массив со списком стоимостей
     */
    public static function getCostList($requestIds) {
        CModule::IncludeModule('iblock');
        $res = CIBlockElement::GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('client_request_cost'),
                'ACTIVE' => 'Y',
                'PROPERTY_REQUEST' => $requestIds
            ),
            false,
            false,
            array(
                'ID',
                'NAME',
                'PROPERTY_REQUEST',
                'PROPERTY_WAREHOUSE',
                'PROPERTY_WAREHOUSE.PROPERTY_ADDRESS',
                'PROPERTY_WAREHOUSE.PROPERTY_MAP',
                'PROPERTY_WAREHOUSE.NAME',
                'PROPERTY_WAREHOUSE.PROPERTY_REGION',
                'PROPERTY_CENTER',
                'PROPERTY_PRICE',
                'PROPERTY_PARITY_PRICE'
            )
        );
        while ($ob = $res->Fetch()) {
            $tmp = array(
                'ID' => $ob['ID'],
                //'REQUEST_ID' => $ob['PROPERTY_REQUEST_VALUE'],
                'WH_ID' => $ob['PROPERTY_WAREHOUSE_VALUE'],
                'WH_ADDRESS' => $ob['PROPERTY_WAREHOUSE_PROPERTY_ADDRESS_VALUE'],
                'WH_MAP' => $ob['PROPERTY_WAREHOUSE_PROPERTY_MAP_VALUE'],
                'WH_NAME' => $ob['PROPERTY_WAREHOUSE_NAME'],
                'WH_REGION' => $ob['PROPERTY_WAREHOUSE_PROPERTY_REGION_VALUE'],
                'CENTER' => $ob['PROPERTY_CENTER_VALUE'],
                'DDP_PRICE_CLIENT' => $ob['PROPERTY_PRICE_VALUE'],
                'PARITY_PRICE' => $ob['PROPERTY_PARITY_PRICE_VALUE']
            );
            $result[$ob['PROPERTY_REQUEST_VALUE']][$ob['PROPERTY_WAREHOUSE_VALUE']] = $tmp;
        }

        return $result;
    }


    /**
     * Получение имен складов запроса
     * @param $requestId идентификатор запроса
     * @return str именя складов через запятую
     */
    public static function getCostWHNames($requestId){
        $wh_names = '';
        $selected_warehouses = array();
        //получение связанных с запросами данных (адреса складов и цены)
        $res_wh = CIBlockElement::GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('client_request_cost'),
                'ACTIVE' => 'Y',
                'PROPERTY_REQUEST' => $requestId
            ),
            false,
            false,
            array('PROPERTY_WAREHOUSE.NAME')
        );
        while($ob_wh = $res_wh->Fetch()){
            $selected_warehouses[] = $ob_wh['PROPERTY_WAREHOUSE_NAME'];
        }
        if((sizeof($selected_warehouses))&&(is_array($selected_warehouses))){
            $wh_names = implode(', ',$selected_warehouses);
        }
        return $wh_names;
    }

    /**
     * Получение имени склада покупателя по его ID
     * @param int $wh_id идентификатор склада
     * @return string имя склада
     */
    public static function getWHNameById($wh_id){
        CModule::IncludeModule('iblock');
        $result = '';
        $res = CIBlockElement::GetByID($wh_id);
        if($ar_res = $res->GetNext())
            $result = $ar_res['NAME'];
        return $result;
    }


    /**
     * Получение списка всех складов покупателя
     * @param  int $user_id идентификатор пользователя
     * @return [] массив со списком элементов
     */
    public static function getWarehouseList($user_id) {
        CModule::IncludeModule('iblock');
        $res = CIBlockElement::GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('client_warehouse'),
                'ACTIVE' => 'Y',
                'PROPERTY_CLIENT' => $user_id,
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
        while ($ob = $res->Fetch()) {
            $tmp = array(
                'ID' => $ob['ID'],
                'NAME' => $ob['NAME'],
                'REGION_ID' => $ob['PROPERTY_REGION_VALUE'],
                'ADDRESS' => $ob['PROPERTY_ADDRESS_VALUE'],
                'MAP' => $ob['PROPERTY_MAP_VALUE'],
            );
            $result[$ob['ID']] = $tmp;
        }

        return $result;
    }

    /**
     * Получение списка параметров складов
     * @param  [] $ids список идентификаторов складов покупателя
     * @return [] массив со списком параметров
     */
    public static function getWarehouseParamsList($ids) {
        CModule::IncludeModule('iblock');
        $res = CIBlockElement::GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('client_warehouse'),
                'ACTIVE' => 'Y',
                'ID' => $ids
            ),
            false,
            false,
            array(
                'ID',
                'NAME',
                'PROPERTY_ADDRESS',
                'PROPERTY_MAP',
                'PROPERTY_TRANSPORT'
            )
        );
        while ($ob = $res->Fetch()) {
            $tmp = array(
                'ID' => $ob['ID'],
                'NAME' => $ob['NAME'],
                'ADDRESS' => $ob['PROPERTY_ADDRESS_VALUE'],
                'MAP' => $ob['PROPERTY_MAP_VALUE'],
                //'CENTER_ID' => $ob['PROPERTY_CENTER_VALUE'],
                'TRANSPORT' => $ob['PROPERTY_TRANSPORT_VALUE']
            );
            $result[$ob['ID']] = $tmp;
        }

        return $result;
    }

    /**
     * Получение списка региональных центров по складам пользователя и по культуре
     * @param  int $culture_id идентификатор культуры
     *         [] $ids список идентификаторов складов покупателя
     * @return [] массив со списком параметров
     */
    public static function getCentersByWH($culture_id, $whIds) {
        CModule::IncludeModule('iblock');
        $res = CIBlockElement::GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('client_warehouse_rc'),
                'ACTIVE' => 'Y',
                'PROPERTY_CULTURE' => $culture_id,
                'PROPERTY_WAREHOUSE' => $whIds
            ),
            false,
            false,
            array(
                'ID',
                'NAME',
                'PROPERTY_CULTURE',
                'PROPERTY_WAREHOUSE',
                'PROPERTY_CENTER'
            )
        );
        while ($ob = $res->Fetch()) {
            $result[$ob['PROPERTY_WAREHOUSE_VALUE']] = $ob['PROPERTY_CENTER_VALUE'];
        }

        return $result;
    }

    /**
     * Получение списка региональных центров по складам пользователей
     * @param  [] $ids список идентификаторов складов покупателей
     * @return [] массив со списком параметров
     */
    public static function getCenters($whIds) {
        CModule::IncludeModule('iblock');
        $res = CIBlockElement::GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('client_warehouse_rc'),
                'ACTIVE' => 'Y',
                'PROPERTY_WAREHOUSE' => $whIds
            ),
            false,
            false,
            array(
                'ID',
                'NAME',
                'PROPERTY_CULTURE',
                'PROPERTY_WAREHOUSE',
                'PROPERTY_CENTER'
            )
        );
        while ($ob = $res->Fetch()) {
            $result[$ob['PROPERTY_CULTURE_VALUE']][$ob['PROPERTY_WAREHOUSE_VALUE']] = $ob['PROPERTY_CENTER_VALUE'];
        }

        return $result;
    }

    /**
     * Получение списка всех запросов пользователя
     * @param mixed $user_id идентификатор(идентификаторы) пользователя
     * @param boolean $recache флаг сброса кеша
     * @return [] массив со списком параметров
     */
    public static function getRequestListByUser($user_id, $recache = false) {
        $result = array();
        CModule::IncludeModule('iblock');

        $request_list = array();
        $price_list = array();
        $active_prop_list = array();
        $ib_id = rrsIblock::getIBlockId('client_request');
        $el_obj = new CIBlockElement;

        //получение данных свойства типа "список" (свойство активности)
        $res = CIBlockPropertyEnum::GetList(array('PROPERTY_ACTIVE' => 'ASC', 'ACTIVE_FROM' => 'DESC'), array(
            'IBLOCK_ID' => $ib_id,
            'CODE' => 'ACTIVE'
        ));
        while($ob = $res->Fetch()){
            $active_prop_list[$ob['ID']] = $ob['XML_ID'];
        }

        //получение запросов
        $res = $el_obj->GetList(
            array('PROPERTY_ACTIVE' => 'ASC', 'ACTIVE_TO' => 'DESC'),
            array(
                'IBLOCK_ID' => $ib_id,
                'ACTIVE' => 'Y',
                'PROPERTY_CLIENT' => $user_id
            ),
            false,
            false,
            array(
                'ID',
                'DATE_ACTIVE_FROM',
                'DATE_ACTIVE_TO',
                'PROPERTY_CULTURE.NAME',
                'PROPERTY_CULTURE',
                'PROPERTY_ACTIVE',
                'PROPERTY_REMAINS',
                'PROPERTY_DELIVERY.CODE'
            )
        );
        while ($ob = $res->Fetch()) {
            $request_list[$ob['ID']] = array(
                'request_id' => $ob['ID'],
                'request_date' => MakeTimeStamp($ob['DATE_ACTIVE_FROM']),
                'date_act' => MakeTimeStamp($ob['DATE_ACTIVE_TO']),
                'culture' => $ob['PROPERTY_CULTURE_NAME'],
                'culture_id' => $ob['PROPERTY_CULTURE_VALUE'],
                'delivery' => ($ob['PROPERTY_DELIVERY_CODE'] == 'Y' ? 'CPT' : 'FCA'),
                'active' => (isset($active_prop_list[$ob['PROPERTY_ACTIVE_ENUM_ID']])
                    && $active_prop_list[$ob['PROPERTY_ACTIVE_ENUM_ID']] == 'yes'
                        ? 1
                        : 0),
                'volume' => number_format($ob['PROPERTY_REMAINS_VALUE'], 0, ',', ' ') . ' т.',
                'prices' => array()
            );
        }

        //получение связанных с запросами данных (адреса складов и цены)
        $res = $el_obj->GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('client_request_cost'),
                'ACTIVE' => 'Y',
                'PROPERTY_REQUEST' => array_keys($request_list)
            ),
            false,
            false,
            array('PROPERTY_PRICE', 'PROPERTY_REQUEST', 'PROPERTY_WAREHOUSE.NAME')
        );
        while($ob = $res->Fetch()){
            $price_list[$ob['PROPERTY_REQUEST_VALUE']][] = array(
                'value' => number_format($ob['PROPERTY_PRICE_VALUE'], 0, ',', ' ') . ' руб/т',
                'name' => $ob['PROPERTY_WAREHOUSE_NAME']
            );
        }

        //заполнение массива для выдачи
        foreach($request_list as $cur_req_id => $cur_val){
            $temp_arr = $cur_val;
            if(isset($price_list[$cur_req_id])
                && is_array($price_list[$cur_req_id])
                && count($price_list[$cur_req_id]) > 0
            ){
                $temp_arr['prices'] = $price_list[$cur_req_id];
            }

            $result[] = $temp_arr;
        }

        return $result;
    }

    /**
     * Получение активных запросов пользователя и их данных
     * @param integer $user_id идентификатор пользователя
     *
     * @return [] массив с данными запросов
     */
    public static function getRequestListDataByUser($user_id) {
        CModule::IncludeModule('iblock');
        $result = array();
        $arFilter = array(
            'IBLOCK_ID' => rrsIblock::getIBlockId('client_request'),
            'ACTIVE' => 'Y',
            'PROPERTY_ACTIVE' => rrsIblock::getPropListKey('client_request', 'ACTIVE', 'yes'),
            '>PROPERTY_REMAINS' => 0,
            'PROPERTY_CLIENT' => $user_id
        );
        $res = CIBlockElement::GetList(
            array('ID' => 'DESC'),
            $arFilter,
            false,
            false,
            array(
                'ID',
                'NAME',
                'DATE_CREATE',
                'DATE_ACTIVE_TO',
                'PROPERTY_CLIENT',
                'PROPERTY_USER_NDS',
                'PROPERTY_CULTURE',
                'PROPERTY_CULTURE.NAME',
                'PROPERTY_DELIVERY.CODE',
                'PROPERTY_REMOTENESS',
                'PROPERTY_USE_REGIONS',
                'PROPERTY_VOLUME',
                'PROPERTY_REMAINS',
                'PROPERTY_PAYMENT',
                'PROPERTY_PERCENT',
                'PROPERTY_DELAY',
                'PROPERTY_DOCS',
                'PROPERTY_NDS'
            )
        );
        while ($ob = $res->Fetch()) {
            $tmp = array(
                'ID' => $ob['ID'],
                'DATE_CREATE' => $ob['DATE_CREATE'],
                'DATE_ACTIVE_TO' => $ob['DATE_ACTIVE_TO'],
                'DATE_DIFF' => strtotime($ob['DATE_ACTIVE_TO'])-time(),
                'CLIENT_ID' => $ob['PROPERTY_CLIENT_VALUE'],
                'USER_NDS' => rrsIblock::getPropListId('client_request', 'USER_NDS', $ob['PROPERTY_USER_NDS_ENUM_ID']),
                'CULTURE_ID' => $ob['PROPERTY_CULTURE_VALUE'],
                'CULTURE_NAME' => $ob['PROPERTY_CULTURE_NAME'],
                'NEED_DELIVERY' => $ob['PROPERTY_DELIVERY_CODE'],
                'REMOTENESS' => $ob['PROPERTY_REMOTENESS_VALUE'],
                'USE_REGIONS' => $ob['PROPERTY_USE_REGIONS_VALUE'],
                'VOLUME' => $ob['PROPERTY_VOLUME_VALUE'],
                'REMAINS' => $ob['PROPERTY_REMAINS_VALUE'],
                'PAYMENT' => rrsIblock::getPropListId('client_request', 'PAYMENT', $ob['PROPERTY_PAYMENT_ENUM_ID']),
                'PERCENT' => $ob['PROPERTY_PERCENT_VALUE'],
                'DELAY' => $ob['PROPERTY_DELAY_VALUE'],
                'DOCS' => $ob['PROPERTY_DOCS_VALUE'],
                'NDS' => $ob['PROPERTY_NDS_VALUE'],
            );
            $result[$ob['ID']] = $tmp;
        }

        $requestParams = client::getParamsList(array_keys($result));
        $requestCostList = client::getCostList(array_keys($result));

        foreach ($result as $key => $request) {
            $result[$key]['PARAMS'] = $requestParams[$key];
            $result[$key]['COST'] = $requestCostList[$key];
        }

        return $result;
    }

    /**
     * Получение списка ближайших региональных центров по культуре для складов покупателя
     * @param  int $culture_id идентификатор культуры
     *         [] $ids список идентификаторов складов покупателя
     * @return [] массив со списком параметров
     */
    public static function getNearestRegCenterIDByCulture($culture_id, $whIds) {
        CModule::IncludeModule('iblock');
        $centersList = array();
        $result = array();

        $res = CIBlockElement::GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('reg_center_leader'),
                'ACTIVE' => 'Y',
                'PROPERTY_CULTURE' => $culture_id
            ),
            false,
            false,
            array('ID', 'PROPERTY_CENTER', 'PROPERTY_CENTER.PROPERTY_MAP')
        );
        while ($ob = $res->Fetch()) {
            $centersList[] = $ob;
        }

        foreach ($whIds as $key => $map) {
            $min = 10000;
            unset($cntr);
            foreach ($centersList as $center) {
                $route_val = rrsIblock::getRoute($map, $center['PROPERTY_CENTER_PROPERTY_MAP_VALUE']);
                if ($route_val < $min) {
                    $min = $route_val;
                    $cntr = $center['PROPERTY_CENTER_VALUE'];
                }
            }

            $result[$key] = $cntr;
        }

        return $result;
    }

    /**
     * Добавление привязки регионального центра к складу покупателя
     * @param  int $user_id идентификатор покупателя
     *         int $culture_id идентификатор культуры
     *         int $wh_id идентификатор склада
     *         int $center_id идентификатор рег. центра
     * @return [] массив со списком параметров
     */
    public static function addCenterToWH($user_id, $culture_id, $wh_id, $center_id) {
        CModule::IncludeModule('iblock');
        $el = new CIBlockElement;

        $PROP = array();
        $PROP['CULTURE'] = $culture_id;
        $PROP['WAREHOUSE'] = $wh_id;
        $PROP['CENTER'] = $center_id;
        $PROP['USER'] = $user_id;

        $fieldArray = Array(
            'IBLOCK_ID' => rrsIblock::getIBlockId('client_warehouse_rc'),
            'ACTIVE' => 'Y',
            'NAME' => 'рц',
            "PROPERTY_VALUES"=> $PROP,
        );

        if ($ID = $el->Add($fieldArray)) {
            return $ID;
        }
        else {
            return false;
        }
    }

    /**
     * Получение привязанного к покупателю организатора
     * @param  int $user_id идентификатор покупателя
     * @return int идентификатор организатора
     */
    public static function getLinkedPartner($user_id) {
        $res = CIBlockElement::GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('client_partner_link'),
                'ACTIVE' => 'Y',
                'PROPERTY_USER_ID' => $user_id
            ),
            false,
            false,
            array('ID', 'PROPERTY_PARTNER_ID')
        );
        if ($ob = $res->Fetch()) {
            return $ob['PROPERTY_PARTNER_ID_VALUE'];
        }

        return 0;
    }

    /**
     * Получение привязанных к покупателю организаторов
     * @param  int $user_id идентификатор покупателя
     * @return array идентификаторов организатора
     */
    public static function getLinkedPartnerList($user_id, $bGetLast = false) {
        $result = array();
        $arAddit = ($bGetLast ? array('nTopCount' => 1) : false);
        $res = CIBlockElement::GetList(
            array('DATE_CREATE' => 'DESC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('client_agent_link'),
                'ACTIVE' => 'Y',
                'PROPERTY_USER_ID' => $user_id
            ),
            false,
            $arAddit,
            array('ID', 'PROPERTY_AGENT_ID')
        );
        while ($ob = $res->Fetch()) {
            $result[$ob['PROPERTY_AGENT_ID_VALUE']] = 1;
        }
        return array_keys($result);
    }

    /**
     * Получение списка токенов для отправки push-уведомлений
     * @param  [] $userIds идентификаторы пользователей
     * @return [] массив со списком токенов
     */
    public static function getPushTokens($userIds) {
        $result = array();
        $filter = array('UF_USER' => $userIds);
        $arUserDevice = UsersDevices::_getEntitiesList($filter);
        foreach ($arUserDevice as $item) {
            if ($item['UF_TOKEN']) {
                $result[$item['UF_USER']][] = $item['UF_TOKEN'];
            }
        }

        return $result;
    }

    /**
     * Проверка корректности массива цен для складов (функция сразу же поправляет "плохие" значения - например цены с пробелами между разрядов)
     * @param array $warArr идентификатор покупателя
     * @return bool флаг корректности массива $warArr
     */
    public static function checkCountedWarehouses(&$warArr) {
        $result = true;

        if(!is_array($warArr) || count($warArr) == 0){

            $result = false;
        }
        else{
            foreach($warArr as $cur_id => $cur_price){
                $temp_val = str_replace(' ', '', $cur_price);
                if(!is_numeric($temp_val) || $temp_val == 0){
                    $result = false;
                }
                else
                {
                    $warArr[$cur_id] = $temp_val;
                }
            }
        }

        return $result;
    }

    /**
     * Копирование запроса покупателя (АПИ)
     * @param  array $data данные для копирования
     * @return array идентификатор новой записи и количество поставщиков, которым подошёл запрос
     */
    public static function copyRequestApi($data) {
        $result = array();
        //$result = 0;

        CModule::IncludeModule('iblock');
        $el_obj = new CIBlockElement;

        $arFields   = array();
        $arProps    = array();

        //получаем данные профиля

        //получаем данные текущего запроса
        $res = $el_obj->GetList(
            array('ID' => 'ASC'),
            array(
                'ID' => $data['request_id'],
                'IBLOCK_ID' => rrsIblock::getIBlockId('client_request')),
            false,
            array('nTopCount' => 1),
            array(
                'PROPERTY_GROUP',
                'PROPERTY_CULTURE',
                'PROPERTY_DELIVERY',
                'PROPERTY_REMOTENESS',
                'PROPERTY_PAYMENT',
                'PROPERTY_DELIVERY',
                'PROPERTY_DELAY',
                'PROPERTY_NDS',
                //'PROPERTY_USER_NDS',
                'PROPERTY_PERCENT',
                'PROPERTY_DELAY',
                'PROPERTY_DOCS',
                'IBLOCK_ID'
            )
        );
        if($ob = $res->Fetch())
        {
            $date_a_from    = date("d.m.Y H:i:s");
            $date_a_to      = date("d.m.Y H:i:s", strtotime('+90 days'));

            $arFields['IBLOCK_ID']      = $ob['IBLOCK_ID'];
            $arFields['ACTIVE']         = 'Y';
            $arFields['MODIFIED_BY']    = $data['userAccID'];
            $arFields['ACTIVE_FROM']    = $date_a_from;
            $arFields['ACTIVE_TO']      = $date_a_to;
            $arFields['NAME']           = $arFields  ['ACTIVE_FROM'];
            $arProps['CLIENT']          = $data['userAccID'];
            $arProps['VOLUME']          = $data['volume'];
            $arProps['REMAINS']         = $arProps['VOLUME'];
            $arProps['USER_NDS']        = $ob['PROPERTY_USER_NDS_ENUM_ID'];
            $arProps['GROUP']           = $ob['PROPERTY_GROUP_VALUE'];
            $arProps['CULTURE']         = $ob['PROPERTY_CULTURE_VALUE'];
            $arProps['DELIVERY']        = $ob['PROPERTY_DELIVERY_VALUE'];
            $arProps['REMOTENESS']      = $ob["PROPERTY_REMOTENESS_VALUE"];

            if (sizeof($ob['PROPERTY_DOCS_VALUE']) > 0) {
                $n = 0;
                foreach ($ob['PROPERTY_DOCS_VALUE'] as $val) {
                    $arProps['DOCS']['n'.$n] = array('VALUE' => $val);
                    $n++;
                }
            }

            $arProps['PAYMENT']         = $ob['PROPERTY_PAYMENT_ENUM_ID'];
            $arProps['PERCENT']         = $ob['PROPERTY_PERCENT_VALUE'];
            $arProps['DELAY']           = $ob['PROPERTY_DELAY_VALUE'];

            if (sizeof($ob['PROPERTY_NDS_PROPERTY_VALUE_ID']) > 0) {
                $n = 0;
                foreach ($ob['PROPERTY_NDS_VALUE'] as $key => $val) {
                    $arProps['NDS']['n' . $n] = array('VALUE' => $key);
                    $n++;
                }
            }

            //$arProps['URGENCY']  = $data["urgency"];
            $arProps['ACTIVE']   = rrsIblock::getPropListKey('client_request', 'ACTIVE', 'yes');


            // Получаем значения списка НДС
            $arNDSEnum = [
                'BY_ID'     => rrsIblock::getPropListId('client_request', 'USER_NDS'),
                'BY_XML'    => [],
            ];
            foreach ($arNDSEnum['BY_ID'] as $iId => $arEnumItem) {
                $arNDSEnum['BY_XML'][$arEnumItem['XML_ID']] = &$arNDSEnum['BY_ID'][$iId];
            }

            // Проверяем использует ли покупатель НДС
            $sIsUseNDS = self::getNds($data['userAccID']);
            if($sIsUseNDS == 'Y') {
                $arProps['USER_NDS'] = $arNDSEnum['BY_XML']['yes']['ID'];
            } elseif ($sIsUseNDS == 'N') {
                $arProps['USER_NDS'] = $arNDSEnum['BY_XML']['no']['ID'];
            }

            $arFields["PROPERTY_VALUES"]  = $arProps;

            $ID = $el_obj->Add($arFields);
            if(intval($ID) > 0){
                $p_ib_id = rrsIblock::getIBlockId('client_request_chars');

                //сохранение параметров качества запроса
                $params_arr = current(self::getParamsList(array($data['request_id'])));
                foreach($params_arr as $cur_id => $cur_val){
                    $arFields                      = array();
                    $arProps              = array();

                    $arFields['NAME']              = $date_a_from;
                    $arFields['IBLOCK_ID']         = $p_ib_id;
                    $arProps['REQUEST']   = $ID;
                    $arProps['CULTURE']   = $ob['PROPERTY_CULTURE_VALUE'];
                    $arProps['QUALITY']   = $cur_val['QUALITY_ID'];
                    if ($cur_val['LBASE_ID'] > 0) {
                        $arProps['LBASE'] = $cur_val['LBASE_ID'];
                    }
                    else {
                        $arProps['BASE']  = $cur_val['BASE'];
                        $arProps['MIN']   = $cur_val['MIN'];
                        $arProps['MAX']   = $cur_val['MAX'];
                    }

                    //прямых сбросов нет
                    if(is_array($cur_val['DUMPING'])) {
                        $n = 0;
                        foreach ($cur_val['DUMPING'] as $d_val) {
                            $arProps['DUMPING']["n".$n] = array("VALUE" => "[{$d_val['MN']};{$d_val['MX']}]:{$d_val['DUMP']}");
                            $n++;
                        }
                    }

                    $arFields["PROPERTY_VALUES"] = $arProps;

                    $pID = $el_obj->Add($arFields);
                }

                //сохранение стоимостей
                if (is_array($data["warehouse"]) && count($data["warehouse"]) > 0) {
                    $arPrices = array();
                    $centerList = client::getCentersByWH($ob['PROPERTY_CULTURE_VALUE'], array_keys($data["warehouse"]));

                    if (intval($data['urgency']) > 0) {
                        foreach ($centerList as $key => $center) {
                            $arPrices[$center] = model::getParityPrice($center, $ob['PROPERTY_CULTURE_VALUE']);
                        }

                        $urCode = rrsIblock::getElementCodeById(
                            rrsIblock::getIBlockId('urgency'),
                            $data["urgency"]
                        );
                    }
                    else {
                        $clientProfile = client::getProfile($data['userAccID']);
                    }
                    $nds = rrsIblock::getConst('nds');

                    foreach ($data["warehouse"] as $key => $store) {
                        if (intval($data['urgency']) > 0) {
                            $parityPrice = $arPrices[$centerList[$key]]['PRICE_'.strtoupper($urCode)];
                        }
                        else {
                            if ($clientProfile['PROPERTY_NDS_CODE'] == 'N') {
                                $parityPrice = round($store * (1. + 0.01 * $nds), 0);
                            }
                            else {
                                $parityPrice = round($store, 0);
                            }
                        }

                        $arFields   = array();
                        $arProps    = array();

                        $arFields["NAME"]      = date("d.m.Y H:i:s");
                        $arFields["IBLOCK_ID"] = rrsIblock::getIBlockId('client_request_cost');

                        $arProps['REQUEST']       = $ID;
                        $arProps['CULTURE']       = $ob['PROPERTY_CULTURE_VALUE'];
                        $arProps['WAREHOUSE']     = $key;
                        $arProps['CENTER']        = $centerList[$key];
                        $arProps['PRICE']         = str_replace(' ', '', $store);
                        //$arProps['PARITY_PRICE']  = $arPrices[$centerList[$key]]['PRICE_'.strtoupper($urCode)];
                        $arProps['PARITY_PRICE'] = $parityPrice;

                        $arFields["PROPERTY_VALUES"] = $arProps;

                        $wID = $el_obj->Add($arFields);
                        if(intval($wID) == 0)
                        {
                            $result['ERROR'] = Agrohelper::getErrorMessage('CopyRequestError2');
                            //$result = -1;
                        }
                    }
                }

                //поиск подходящих для запроса товаров поставщиков
                /*$arSuitableOffers = deal::searchSuitableOffers($ID);

                $arSuitableOffers['FARMER_CNT']             = intval($arSuitableOffers['FARMER_CNT']);
                $arSuitableOffers['FARMER_BEST_PRICE_CNT']  = intval($arSuitableOffers['FARMER_BEST_PRICE_CNT']);

                $message = null;
                if($arSuitableOffers['FARMER_CNT'] > 0) {
                    $message .= 'Ваш запрос ' . morph($arSuitableOffers['FARMER_CNT'], 'получил ', 'получило ', 'получило ') . ' ';
                    $message .= $arSuitableOffers['FARMER_CNT'] . morph($arSuitableOffers['FARMER_CNT'], ' поставщик', ' поставщика', ' поставщиков');
                    $message .= ', для ' . $arSuitableOffers['FARMER_BEST_PRICE_CNT'] . ' - лучшая цена';
                } else {
                    $message .= 'На ваш запрос не найден ни один товар';
                }

                CIBlockElement::SetPropertyValuesEx(
                    $ID,
                    rrsIblock::getIBlockId('client_request'),
                    array(
                        'F_NUM'                 => $arSuitableOffers['FARMER_CNT'],
                        'FARMER_BEST_PRICE_CNT' => $arSuitableOffers['FARMER_BEST_PRICE_CNT'],
                    )
                );

                */

                $message = 'Запрос успешно создан';

                $result = array(
                    'id'        => $ID,
                    'num'       => 0,
                    'message'   => $message,
                    'success'   => 1
                );


//                if($result != -1){
//                    $result = $ID;
//                }
            }
            else
            {
                $result['ERROR'] = Agrohelper::getErrorMessage('CopyRequestError');
                //$result = -2;
            }
        }

        return $result;
    }

    /**
     * Получение привязанного к покупателю организатора (подтвержденного)
     * @param  int $user_id идентификатор покупателя
     * @return int идентификатор организатора
     */
    public static function getLinkedPartnerVerified($user_id) {
        $res = CIBlockElement::GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('client_partner_link'),
                'ACTIVE' => 'Y',
                'PROPERTY_VERIFIED' => rrsIblock::getPropListKey('client_partner_link', 'VERIFIED', 'yes'),
                'PROPERTY_USER_ID' => $user_id
            ),
            false,
            false,
            array('ID', 'PROPERTY_PARTNER_ID')
        );
        if ($ob = $res->Fetch()) {
            return $ob['PROPERTY_PARTNER_ID_VALUE'];
        }

        return 0;
    }
    
    /*
     * Получение региона пользователя
     * @param int $user_id - идентификатор пользователя
     * @return int идентификатор региона
     */
    public static function getRegion($user_id, $recache = false) {
        CModule::IncludeModule('iblock');
        $obCache = new CPHPCache;
        $life_time = 3600;
        $cache_id = 'getRegion_' . $user_id;
        if ($obCache->InitCache($life_time, $cache_id, "/") && !$recache) {
            $vars = $obCache->GetVars();
            $result = $vars[$cache_id];
        }
        else {
            $res = CIBlockElement::GetList(
                array('ID' => 'DESC'),
                array(
                    'IBLOCK_ID' => rrsIblock::getIBlockId('client_profile'),
                    'ACTIVE' => 'Y',
                    'PROPERTY_USER' => $user_id
                ),
                false,
                false,
                array('ID', 'PROPERTY_REGION')
            );
            if ($ob = $res->Fetch()) {
                $result = $ob['PROPERTY_REGION_VALUE'];
            }
        }
        if ($recache) $obCache->Clean($cache_id, "/", $basedir = "cache");
        if ($obCache->StartDataCache()) $obCache->EndDataCache(array($cache_id => $result));
        return $result;
    }

    /*
     * Получение списка всех документов пользователя
     * @param
     * @return [] спислк документов
     */
    public static function getAllDocuments($recache = false) {
        CModule::IncludeModule('iblock');
        $obCache = new CPHPCache;
        $life_time = 3600;
        $cache_id = 'getAllDocuments_client';
        if ($obCache->InitCache($life_time, $cache_id, "/") && !$recache) {
            $vars = $obCache->GetVars();
            $result = $vars[$cache_id];
        }
        else {
            $res = CIBlockElement::GetList(
                array('SORT' => 'ASC'),
                array(
                    'IBLOCK_ID' => rrsIblock::getIBlockId('user_docs'),
                    'ACTIVE' => 'Y',
                    'SECTION_CODE' => 'client',
                ),
                false,
                false,
                array('ID', 'NAME', 'CODE')
            );
            while ($ob = $res->Fetch()) {
                $result[$ob['CODE']] = $ob;
            }
        }
        if ($recache) $obCache->Clean($cache_id, "/", $basedir = "cache");
        if ($obCache->StartDataCache()) $obCache->EndDataCache(array($cache_id => $result));
        return $result;
    }

    /*
     * Деактивация запроса (api)
     * @param array $data - массив, содержащий параметры запроса (в том числе id пользователя и id запроса)
     * @return [] флаг успеха/данные ошибки
     */
    public static function deactivateRequestApi($data) {
        CModule::IncludeModule('iblock');

        $active_prop_list = array();

        //получение данных свойства типа "список" (свойство активности)
        $res = CIBlockPropertyEnum::GetList(array('ID' => 'ASC'), array(
            'IBLOCK_ID' => rrsIblock::getIBlockId('client_request'),
            'CODE' => array('ACTIVE')
        ));
        while($ob = $res->Fetch()){
            $active_prop_list[$ob['XML_ID']] = $ob['ID'];
        }

        if(!isset($active_prop_list['yes']) || !isset($active_prop_list['no']))
        {
            return array('ERROR' => Agrohelper::getErrorMessage('DeactivateRequestActPChanged'));
        }

        //проверка данных запроса
        $el_obj = new CIBlockElement;
        $res = $el_obj->GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('client_request'),
                'ID' => $data['request_id'],
                'PROPERTY_CLIENT' => $data['userAccID']
            ),
            false,
            false,
            array('PROPERTY_ACTIVE', 'IBLOCK_ID', 'ID')
        );
        if($ob = $res->Fetch()){
            if($ob['PROPERTY_ACTIVE_ENUM_ID'] == $active_prop_list['yes']){
                //Деактивация запроса
                $el_obj->SetPropertyValuesEx($ob['ID'], $ob['IBLOCK_ID'], array('ACTIVE' => $active_prop_list['no']));
                logRequestDeactivating($ob['ID']); //пишем лог о деактивации запроса

                //Удаление пар запрос-товар
                $filter = array(
                    'UF_REQUEST_ID' => $ob['ID']
                );
                $arLeads = lead::getLeadList($filter);
                if (is_array($arLeads) && sizeof($arLeads) > 0) {
                    lead::deleteLeads($arLeads);
                }

                //удаление встречных предложений
                self::removeCountersByRequestID($ob['ID']);
            }
            else{
                //запрос уже деактивирован
                return array('ERROR' => Agrohelper::getErrorMessage('DeactivateRequestDeactivated'));
            }
        }
        else{
            //запрос не найден или не соответсвует пользователю
            return array('ERROR' => Agrohelper::getErrorMessage('DeactivateRequestNotFind'));
        }

        return true;
    }

    /*
     * Получение детальной информации по запросу (api)
     * @param array $data - массив, содержащий параметры запроса (в том числе id пользователя и id запроса)
     * @return [] данные запроса
     */
    public static function getRequestDataApi($data) {
        $result = array();

        CModule::IncludeModule('iblock');
        $active_prop_list = array();
        $payment_prop_list = array();
        $price_list = array();
        $docs_ids = array();

        //получение данных свойства типа "список" (свойства активности и типа оплаты)
        $res = CIBlockPropertyEnum::GetList(array('ID' => 'ASC'), array(
            'IBLOCK_ID' => rrsIblock::getIBlockId('client_request'),
            'CODE' => array('ACTIVE', 'PAYMENT')
        ));
        while($ob = $res->Fetch()){
            if($ob['PROPERTY_CODE'] == 'ACTIVE')
                $active_prop_list[$ob['ID']] = $ob['XML_ID'];
            elseif($ob['PROPERTY_CODE'] == 'PAYMENT')
                $payment_prop_list[$ob['ID']] = $ob['VALUE'];
        }

        //получение данных запроса
        $el_obj = new CIBlockElement;
        $res = $el_obj->GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('client_request'),
                'PROPERTY_CLIENT' => $data['userAccID'],
                'ACTIVE' => 'Y',
                'ID' => $data['request_id']),
            false,
            array('nTopCount' => 1),
            array(
                'DATE_ACTIVE_FROM',
                'DATE_ACTIVE_TO',
                'PROPERTY_CULTURE.NAME',
                'PROPERTY_ACTIVE',
                'PROPERTY_VOLUME',
                'PROPERTY_PAYMENT',
                'PROPERTY_PERCENT',
                'PROPERTY_REMOTENESS',
                'PROPERTY_DELAY',
                'PROPERTY_URGENCY.NAME',
                'PROPERTY_DOCS',
                'PROPERTY_DELIVERY.CODE',
                'PROPERTY_F_NUM',
                'PROPERTY_IS_PROLONGATED',
                'PROPERTY_FARMER_BEST_PRICE_CNT',
            )
        );
        if($ob = $res->Fetch()){
            if(is_array($ob['PROPERTY_DOCS_VALUE']))
                $docs_ids = $ob['PROPERTY_DOCS_VALUE'];

            if (!$ob['PROPERTY_F_NUM_VALUE'])
                $ob['PROPERTY_F_NUM_VALUE'] = 0;

            if (!$ob['PROPERTY_URGENCY_NAME'])
                $ob['PROPERTY_URGENCY_NAME'] = 'Стандартная закупка';

            $result['request'] = array(
                'request_id'    => $ob['ID'],
                'request_date'  => MakeTimeStamp($ob['DATE_ACTIVE_FROM']),
                'request_url'   => $GLOBALS['host'] . '/client/request/?id=' . $ob['ID'],
                'date_act'      => MakeTimeStamp($ob['DATE_ACTIVE_TO']),
                'culture'       => $ob['PROPERTY_CULTURE_NAME'],
                'active'        => (isset($active_prop_list[$ob['PROPERTY_ACTIVE_ENUM_ID']])
                    && $active_prop_list[$ob['PROPERTY_ACTIVE_ENUM_ID']] == 'yes'
                    ? 1
                    : 0),
                'volume'        => number_format($ob['PROPERTY_VOLUME_VALUE'], 0, ',', ' ') . ' т.',
                'delivery'      => ($ob['PROPERTY_DELIVERY_CODE'] == 'Y' ? 'CPT' : 'FCA'),
                'docs'          => array(),
                'payment'       => (isset($payment_prop_list[$ob['PROPERTY_PAYMENT_ENUM_ID']])
                    ? $payment_prop_list[$ob['PROPERTY_PAYMENT_ENUM_ID']]
                    : ''),
                'urgency'       => $ob['PROPERTY_URGENCY_NAME'],
                'farmer_num'    => $ob['PROPERTY_F_NUM_VALUE'],
                'prices'        => array(),
                'best_num'      => $ob['PROPERTY_FARMER_BEST_PRICE_CNT_VALUE'],
                'prolong'       => (ClientRequests::RequestCanBePrologated($data) ? 1 : 0)
            );

            //заполение данных зависящих от типа доставки
            if($result['request']['delivery'] == 'FCA')
            {
                $result['request']['remoteness'] = 'Удалённость: ' . $ob['PROPERTY_REMOTENESS_VALUE'] . ' км';
            }
            else
            {
                $result['request']['remoteness'] = '';
            }

            //заполнение данных, зависящих от типа оплаты
            if($result['request']['payment'] == 'Предоплата')
            {
                $result['request']['payment']   = "Предоплата";
                $result['request']['delay']     = '';
                $result['request']['percent']   = '';
            }
            else
            {
                $result['request']['percent']   = '';
                $result['request']['delay']     = '';
                $result['request']['payment']   = "Постоплата";
            }

            //получение связанных с запросом данных (наименования требуемых документов)
            if(count($docs_ids) > 0)
            {
                $res = $el_obj->GetList(
                    array('ID' => 'ASC'),
                    array(
                        'IBLOCK_ID' => rrsIblock::getIBlockId('need_docs'),
                        'ACTIVE' => 'Y',
                        'ID' => $docs_ids
                    ),
                    false,
                    false,
                    array('NAME')
                );
                while($ob = $res->Fetch())
                {
                    $result['request']['docs'][] = $ob['NAME'];
                }
            }
            else {
                $result['request']['docs'][] = 'Нет';
            }

            //получение связанных с запросом данных (адреса складов и цены)
            $res = $el_obj->GetList(
                array('ID' => 'ASC'),
                array(
                    'IBLOCK_ID' => rrsIblock::getIBlockId('client_request_cost'),
                    'ACTIVE' => 'Y',
                    'PROPERTY_REQUEST' => $data['request_id']
                ),
                false,
                false,
                array('PROPERTY_PRICE', 'PROPERTY_REQUEST', 'PROPERTY_WAREHOUSE.NAME', 'PROPERTY_WAREHOUSE.PROPERTY_ADDRESS')
            );
            while($ob = $res->Fetch()){
                $price_list[] = array(
                    'value' => number_format($ob['PROPERTY_PRICE_VALUE'], 0, ',', ' ') . ' руб/т',
                    'name'      => $ob['PROPERTY_WAREHOUSE_NAME'],
                    'address'   => $ob['PROPERTY_WAREHOUSE_PROPERTY_ADDRESS_VALUE']
                );
            }
            $result['request']['prices'] = $price_list;
        }
        else{
            //Запрос не найден (либо не принадлежит данному клиенту)
            $result['ERROR'] = Agrohelper::getErrorMessage('GetRequestError');
        }

        return $result;
    }

    /*
     * Получение детальной информации по запросу (api)
     * @param array $data - массив, содержащий параметры запроса (в том числе id пользователя и id запроса)
     * @return [] данные запроса
     */
    public static function getRequestCopyDataApi($data) {
        $result = array();

        CModule::IncludeModule('iblock');
        $el_obj = new CIBlockElement;

        $active_prop_list = array();
        $payment_prop_list = array();
        $nds_prop_list = array();
        $docs_ids = array();
        $urgency_codes = array();
        $warhouses_params = array();
        $warhouses_list = array();
        $culture_id = 0;

        //получение данных свойства типа "список" (свойства активности и типа оплаты)
        $res = CIBlockPropertyEnum::GetList(array('ID' => 'ASC'), array(
            'IBLOCK_ID' => rrsIblock::getIBlockId('client_request'),
            'CODE' => array('ACTIVE', 'PAYMENT', 'NDS')
        ));
        while($ob = $res->Fetch()){
            if($ob['PROPERTY_CODE'] == 'ACTIVE')
                $active_prop_list[$ob['ID']] = $ob['XML_ID'];
            elseif($ob['PROPERTY_CODE'] == 'PAYMENT')
                $payment_prop_list[$ob['ID']] = $ob['VALUE'];
            elseif($ob['PROPERTY_CODE'] == 'NDS')
                $nds_prop_list[$ob['ID']] = $ob['VALUE'];
        }

        //получение данных запроса
        $res = $el_obj->GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('client_request'),
                'PROPERTY_CLIENT' => $data['userAccID'],
                'ACTIVE' => 'Y',
                'ID' => $data['request_id']),
            false,
            array('nTopCount' => 1),
            array(
                'PROPERTY_CULTURE',
                'PROPERTY_CULTURE.NAME',
                'PROPERTY_ACTIVE',
                'PROPERTY_VOLUME',
                'PROPERTY_PAYMENT',
                'PROPERTY_GROUP.NAME',
                'PROPERTY_URGENCY',
                'PROPERTY_DOCS',
                'PROPERTY_REMOTENESS',
                'PROPERTY_PERCENT',
                'PROPERTY_NDS',
                'PROPERTY_DELAY',
                'PROPERTY_DELIVERY.CODE')
        );
        if($ob = $res->Fetch()){
            if(is_array($ob['PROPERTY_DOCS_VALUE']))
                $docs_ids = $ob['PROPERTY_DOCS_VALUE'];

            if (!$ob['PROPERTY_URGENCY_VALUE'])
                $ob['PROPERTY_URGENCY_VALUE'] = 243;

            $result['request'] = array(
                'request_id'    => $ob['ID'],
                'request_url'   => $GLOBALS['host'] . '/client/request/new/?id=' . $ob['ID'],
                'group'         => $ob['PROPERTY_GROUP_NAME'],
                'culture'       => $ob['PROPERTY_CULTURE_NAME'],
                'volume'        => $ob['PROPERTY_VOLUME_VALUE'],
                'delivery'      => ($ob['PROPERTY_DELIVERY_CODE'] == 'Y' ? 'CPT' : 'FCA'),
                'docs'          => array(),
                'payment'       => (isset($payment_prop_list[$ob['PROPERTY_PAYMENT_ENUM_ID']])
                    ? $payment_prop_list[$ob['PROPERTY_PAYMENT_ENUM_ID']]
                    : ''),
                'nds'           => array(),
                'urgency_id'    => $ob['PROPERTY_URGENCY_VALUE'],
                'warehouses_list' => array()
            );

            //заполение данных зависящих от типа доставки
            if($result['request']['delivery'] == 'FCA')
            {
                $result['request']['remoteness'] = 'Удалённость: ' . $ob['PROPERTY_REMOTENESS_VALUE'] . ' км';
            }
            else
            {
                $result['request']['remoteness'] = '';
            }

            //заполнение данных, зависящих от типа оплаты
            if($result['request']['payment'] == 'Предоплата')
            {
                $result['request']['payment']   = "Предоплата";
                $result['request']['delay']     = '';
                $result['request']['percent']   = '';
            }
            else
            {
                $result['request']['percent']   = '';
                $result['request']['delay']     = '';
                $result['request']['payment']   = "Постоплата";
            }

            $culture_id = $ob['PROPERTY_CULTURE_VALUE'];

            //заполенение данных nds
            foreach($ob['PROPERTY_NDS_VALUE'] as $cur_id => $cur_data)
            {
                if(isset($nds_prop_list[$cur_id]))
                {
                    $result['request']['nds'][] = $nds_prop_list[$cur_id];
                }
            }

            //получение данных для свойства URGENCY
            $res = $el_obj->GetList(
                array('ID' => 'ASC'),
                array(
                    'IBLOCK_ID' => rrsIblock::getIBlockId('urgency'),
                ),
                false,
                false,
                array('ID', 'NAME', 'CODE')
            );
            while($ob = $res->Fetch())
            {
                $result['urgency_list'][$ob['ID']] = $ob['NAME'];
                $urgency_codes[strtoupper($ob['CODE'])] = $ob['ID'];
            }

            //получение связанных с запросом данных (наименования требуемых документов)
            if(count($docs_ids) > 0)
            {
                $res = $el_obj->GetList(
                    array('ID' => 'ASC'),
                    array(
                        'IBLOCK_ID' => rrsIblock::getIBlockId('need_docs'),
                        'ACTIVE'    => 'Y',
                        'ID'        => $docs_ids
                    ),
                    false,
                    false,
                    array('NAME')
                );
                while($ob = $res->Fetch())
                {
                    $result['request']['docs'][] = $ob['NAME'];
                }
            }
            else {
                $result['request']['docs'][] = 'Нет';
            }

            //получение связанных с запросом данных (адреса складов и расчёт цен)
            //получение выбранных складов для текущего запроса
            $selected_warehouses = array();
            $res = $el_obj->GetList(
                array('ID' => 'ASC'),
                array(
                    'IBLOCK_ID'         => rrsIblock::getIBlockId('client_request_cost'),
                    'ACTIVE'            => 'Y',
                    'PROPERTY_REQUEST'  => $data['request_id']
                ),
                false,
                false,
                array('PROPERTY_WAREHOUSE')
            );
            while($ob = $res->Fetch())
            {
                $selected_warehouses[$ob['PROPERTY_WAREHOUSE_VALUE']] = true;
            }

            $warhouses_params = current(self::getParamsList(array($data['request_id'])));
            $warhouses_list = client::basePriceCalculation($data['userAccID'], $culture_id, $warhouses_params);
            foreach($warhouses_list['WAREHOUSES'] as $cur_data)
            {
                $temp_w = array(
                    'id'        => $cur_data['ID'],
                    'name'      => $cur_data['NAME'],
                    'address'   => $cur_data['ADDRESS'],
                    'selected'  => (isset($selected_warehouses[$cur_data['ID']]) ? 1 : 0),
                    'prices'    => array()
                );
                if(isset($cur_data['CENTER_ID'])
                    && is_numeric($cur_data['CENTER_ID'])
                    && isset($warhouses_list['PRICES'][$cur_data['CENTER_ID']])
                )
                {
                    $temp_w['min'] = round($warhouses_list['PRICES'][$cur_data['CENTER_ID']]['BASE_PRICE_PASSIVE'], 0);
                    $temp_w['max'] = round($warhouses_list['PRICES'][$cur_data['CENTER_ID']]['BASE_PRICE_ACTIVE'], 0);
                    $temp_w['price'] = round($warhouses_list['PRICES'][$cur_data['CENTER_ID']]['BASE_PRICE_STANDART'], 0);
                    $temp_w['step'] = 50;

                    foreach($urgency_codes as $cur_p_code => $cur_p_id)
                    {
                        if(isset($warhouses_list['PRICES'][$cur_data['CENTER_ID']]['BASE_PRICE_' . $cur_p_code]))
                        {
                            $temp_w['prices'][$cur_p_id] = number_format($warhouses_list['PRICES'][$cur_data['CENTER_ID']]['BASE_PRICE_' . $cur_p_code], 0, ',', ' ');
                        }
                    }
                }
                $result['request']['warehouses_list'][] = $temp_w;
            }

            //добавление параметра безшовной авторизации к ссылке, если таковой установлен
            $arClient = CUser::GetByID($data['userAccID'])->Fetch();

            if($arClient['UF_API_KEY'])
            {
                $result['request']['request_url'] = $result['request']['request_url'] . "&dkey=".$arClient['UF_API_KEY'];
            }
        }
        else{
            //Запрос не найден (либо не принадлежит данному клиенту)
            $result['ERROR'] = Agrohelper::getErrorMessage('GetRequestError');
        }

        return $result;
    }

    /*
     * Получение списка базисных цен по складам покупателя
     * @param int $user_id - идентификатор пользователя
     * @return int идентификатор региона
     */
    public static function basePriceCalculation($user_id, $culture_id, $params) {
        $result = array();

        //получение списка складов покупателя
        $warehouses = client::getWarehouseList($user_id);

        if (is_array($warehouses) && sizeof($warehouses) > 0) {
            //получение региональных центров в привязке к складу и культуре
            $centerList = client::getCentersByWH($culture_id, array_keys($warehouses));
            $newWHlist = array();

            foreach ($warehouses as $key => $wh) {
                if (in_array($wh['ID'], array_keys($centerList))) {
                    $warehouses[$key]['CENTER_ID'] = $centerList[$wh['ID']];
                }
                else {
                    $newWHlist[] = $wh['ID'];
                }
            }

            if (is_array($newWHlist) && sizeof($newWHlist) > 0) {
                $WHcoords = array();
                foreach ($newWHlist as $val) {
                    if ($warehouses[$val]['MAP']) {
                        $WHcoords[$val] = $warehouses[$val]['MAP'];
                    }
                }

                $res = client::getNearestRegCenterIDByCulture($culture_id, $WHcoords);

                if (is_array($res) && sizeof($res) > 0) {
                    foreach ($res as $key => $val) {
                        $warehouses[$key]['CENTER_ID'] = $val;
                        client::addCenterToWH($user_id, $culture_id, $key, $val);
                    }
                }
            }

            //получение списка ближайших рег. центров всех складов
            $centerList = array();
            foreach ($warehouses as $warehouse) {
                $centerList[$warehouse['CENTER_ID']] = true;
            }

            //получение профиля клиента А
            $userWithNDS = client::getNds($user_id);

            //НДС
            $nds = rrsIblock::getConst('nds');

            //Сброс, р/кг
            //Получение списка характеристик культуры
            /* убрано в рамках hotfix 11579
             * $charsList = culture::getParamsListByCultureId($culture_id);
            foreach ($params as $key => $param) {
                if (is_array($param['DUMP']) && sizeof($param['DUMP']) > 0) {
                    $params[$key]['DUMPING'] = array();
                    foreach ($param['DUMP']['DISCOUNT'] as $i => $item) {
                        $params[$key]['DUMPING'][] = array(
                            'MN' => $param['DUMP']['MIN'][$i],
                            'MX' => $param['DUMP']['MAX'][$i],
                            'DUMP' => $item
                        );
                    }
                }
            }

            $dumping = deal::getDump($params, $charsList);*/

            //вычисление цены для каждого регионального центра
            foreach ($centerList as $center_id => $r) {
                $result['PRICES'][$center_id] = model::getParityPrice($center_id, $culture_id);

                foreach ($result['PRICES'][$center_id] as $key => $price) {
                    $ndsValue = 0;
                    if ($userWithNDS == 'N') {
                        //БЦ(DPP) , р/тн с учетом вычета НДС = Цена, если клиент А работает без НДС
                        $ndsValue = $price * 0.01 * $nds / (1. + 0.01 * $nds);
                    }
                    $price -= $ndsValue;

                    //учет таблицы сбросов
                    /* убрано в рамках hotfix 11579
                     * $dumpingValue = 0.01 * $price * $dumping;
                    $price += $dumpingValue;*/

                    $result['PRICES'][$center_id]['BASE_'.$key] = round($price, 0);
                }
            }
        }

        $result['WAREHOUSES'] = $warehouses;

        return $result;
    }

    /**
     * Получение признака права клиента создавать запросы и принимать участие в сделке
     * (признак определяется наличием договора с организатором)
     * @return boolean признака наличия права
     */
    public static function getAddRights($uid)
    {
        $result = 'N';
        if(!is_numeric($uid)){
            return $result;
        }

        $el = new CIBlockElement;
        $res = $el->GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID'                     => rrsIblock::getIBlockId('client_partner_link'),
                'PROPERTY_USER_ID'              => $uid,
                '!PROPERTY_PARTNER_ID'          => false,
                '!PROPERTY_PARTNER_LINK_DOC'    => false
            ),
            false,
            false,
            array('ID')
        );

        if($res->SelectedRowsCount() > 0){
            $result = 'Y';
        }

        return $result;
    }

    /**
     * Получение рейтинга покупателей
     * @param [] $userIds - массив идентификаторов покупателей
     * @return [] массив с информацией о рейтинге
     */
    public static function getRating($userIds) {
        $result = array();
        if (is_array($userIds) && sizeof($userIds) > 0) {
            $res = CIBlockElement::GetList(
                array('ID' => 'DESC'),
                array(
                    'IBLOCK_ID' => rrsIblock::getIBlockId('client_rating'),
                    'ACTIVE' => 'Y',
                    'PROPERTY_USER' => $userIds,
                ),
                false,
                false,
                array('ID', 'PROPERTY_USER', 'PROPERTY_REC', 'PROPERTY_LAB', 'PROPERTY_PAY', 'PROPERTY_RATING')
            );
            while ($ob = $res->GetNext()) {
                if ($ob['PROPERTY_RATING_VALUE'] == 0) {
                    $result[$ob['PROPERTY_USER_VALUE']] = array('REC' => '10.00', 'LAB' => '10.00', 'PAY' => '10.00', 'RATE' => '10.00');
                }
                else {
                    $result[$ob['PROPERTY_USER_VALUE']] = array(
                        'REC' => number_format($ob['PROPERTY_REC_VALUE'], 2, '.', ''),
                        'LAB' => number_format($ob['PROPERTY_LAB_VALUE'], 2, '.', ''),
                        'PAY' => number_format($ob['PROPERTY_PAY_VALUE'], 2, '.', ''),
                        'RATE' => number_format($ob['PROPERTY_RATING_VALUE'], 2, '.', '')
                    );
                }
            }

            foreach($userIds as $id) {
                if (!isset($result[$id])) {
                    $result[$id] = array('REC' => '10.00', 'LAB' => '10.00', 'PAY' => '10.00', 'RATE' => '10.00');
                }
            }
        }

        return $result;
    }

    /**
     * Деактивация запросов склада (при деактивации склада)
     * Логика работы:
     * 1) получаем по деактивируемым скаладам все активные связанные запросы
     * 2) из запросов деактивируем те, у которых все стоимости относятся к неактивным складам
     *
     *
     * @param number $wh_id идентификатор склада
     * @param int $uid идентификатор покупателя
     * @return number число деактивированных товаров
     */
    public static function setWHRequestDeactivation($wh_id, $uid){
        $result = 0;

        CModule::IncludeModule('iblock');
        $el_obj = new CIBlockElement;
        $client_request_cost_ib = rrsIblock::getIBlockId('client_request_cost');

        if(!is_numeric($wh_id)
            && (!is_array($wh_id) || count($wh_id) == 0)
        ){
            //плохой параметр $wh_id
            return $result;
        }

        //получаем стоимости по складам
        $req_to_wh_list = array(); //массив соотнесения складов, стоимостей и запросов
        $wh_active_id_list = array(); //массив активных складов, по наличию которых будем определять стоит ли деактивировать запрос
        $req_ids = array(); //id запросов, стоимости которых относятся к деактивруемым складам
        $req_ids_deact = array(); //id запросов, которые нужно деактивровать (у которых нет ни одной стоимости, которая относятся к активным складам)
        $cost_ids_deact = array(); //id стоимостей, которые нужно деактивровать (которые относятся к деактивированным складам)
        $res = $el_obj->GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => $client_request_cost_ib,
                'PROPERTY_CLIENT' => $uid,
                'PROPERTY_WAREHOUSE' => $wh_id,
            ),
            false,
            false,
            array('ID', 'PROPERTY_REQUEST', 'PROPERTY_WAREHOUSE')
        );
        while($data = $res->Fetch()){
            $req_ids[] = $data['PROPERTY_REQUEST_VALUE'];
            $cost_ids_deact[] = $data['ID'];
        }

        //деактивируем стоимости
        if(count($cost_ids_deact) > 0){
            $arFields = array('ACTIVE' => 'N');
            foreach($cost_ids_deact as $cur_cost_id){
                $el_obj->Update($cur_cost_id, $arFields, false, false);
            }
        }

        if(count($req_ids) > 0){
            //получаем затронутые активные запросы
            $res = $el_obj->GetList(
                array('ID' => 'DESC'),
                array(
                    'IBLOCK_ID' => rrsIblock::getIBlockId('client_request'),
                    'ID' => $req_ids,
                    'PROPERTY_CLIENT' => $uid,
                    'PROPERTY_ACTIVE' => rrsIblock::getPropListKey('client_request', 'ACTIVE', 'yes'),
                ),
                false,
                false,
                array(
                    'ID'
                )
            );
            $req_ids = array(); //массив активных запросов, связанных с деактивируемыми складами
            while($data = $res->Fetch()){
                $req_ids[$data['ID']] = true;
            }
            if(count($req_ids) > 0){
                //получаем все стоимости затронутых активных запросов
                $res = $el_obj->GetList(
                    array('ID' => 'ASC'),
                    array(
                        'IBLOCK_ID' => $client_request_cost_ib,
                        'PROPERTY_CLIENT' => $uid,
                        'ACTIVE' => 'Y',
                        'PROPERTY_REQUEST' => array_keys($req_ids),
                    ),
                    false,
                    false,
                    array('ID', 'PROPERTY_REQUEST', 'PROPERTY_WAREHOUSE')
                );
                while($data = $res->Fetch()){
                    $wh_active_id_list[$data['PROPERTY_WAREHOUSE_VALUE']] = true;
                    $req_to_wh_list[$data['PROPERTY_REQUEST_VALUE']][$data['ID']] = $data['PROPERTY_WAREHOUSE_VALUE'];
                }

                //получаем активные склады для рассматриваемых запросов
                if(count($wh_active_id_list) > 0) {
                    $res = $el_obj->GetList(
                        array('ID' => 'ASC'),
                        array(
                            'IBLOCK_ID' => rrsIblock::getIBlockId('client_warehouse'),
                            'ID' => array_keys($wh_active_id_list),
                            'PROPERTY_ACTIVE' => rrsIblock::getPropListKey('client_warehouse', 'ACTIVE', 'yes'),
                        ),
                        false,
                        false,
                        array('ID')
                    );
                    $wh_active_id_list = array(); //очищаем список складов, чтобы хранить в нем только те активные склады, что требуется для проверки
                    while($data = $res->Fetch()){
                        $wh_active_id_list[$data['ID']] = true;
                    }
                }else{
                    $wh_active_id_list = array(); //очищаем список складов (не оказалось складов в стоимостях - где-то в админке некорректно поудаляли)
                }

                $found_active_wh = false; //признак того, что для запроса имеется хотя бы один запрос с активным складом
                foreach($req_ids as $cur_check_request => $cur_flag){
                    if(isset($req_to_wh_list[$cur_check_request])){
                        $found_active_wh = false;
                        foreach($req_to_wh_list[$cur_check_request] as $cur_cost_id => $cur_wh){
                            if(isset($wh_active_id_list[$cur_wh])){
                                //найдена стоимость с активным складом -> не отмечаем запрос на деактивацию
                                $found_active_wh = true;
                                break;
                            }
                        }
                        if(!$found_active_wh){
                            //для запроса не найдено стоимостей с активными складами -> отмечаем его для деактивации
                            $req_ids_deact[$cur_check_request] = true;
                        }
                    }else{
                        //у активного запроса нет активных стоимостей - отмечаем его для деактивации
                        $req_ids_deact[$cur_check_request] = true;
                    }
                }

                //деактивируем запросы
                if(count($req_ids_deact) > 0){
                    $req_ib_id = rrsIblock::getIBlockId('client_request');
                    $req_active_set_value = rrsIblock::getPropListKey('client_request', 'ACTIVE', 'no');
                    foreach($req_ids_deact as $cur_req_id => $cur_flag){
                        $prop = array(
                            'ACTIVE' => $req_active_set_value
                        );
                        $el_obj->SetPropertyValuesEx($cur_req_id, $req_ib_id, $prop);
                        logRequestDeactivating($cur_req_id); //пишем лог о деактивации запроса
                        $result++;
                    }
                }
            }
        }

        //удаление пар
        $filter = array(
            'UF_CLIENT_WH_ID' => $wh_id
        );
        $arLeads = lead::getLeadList($filter);
        if (is_array($arLeads) && sizeof($arLeads) > 0) {
            lead::deleteLeads($arLeads);
        }

        //удаление встречных предложений
        self::removeCountersByWhID($wh_id);

        return $result;
    }

    /**
     * Проверка запросов - есть ли у них стоимости с активными складами (в ином случае их нельзя продлевать)
     * Логика работы:
     * 1) получаем по деактивируемым скаладам все активные связанные запросы
     * 2) из запросов деактивируем те, у которых все стоимости относятся к неактивным складам
     *
     *
     * @param array $req_ids массив идентификаторов запросов
     * @return number число деактивированных товаров
     */
    public static function checkRequestDeactivatedWH($req_ids){
        $result = array();

        CModule::IncludeModule('iblock');
        $el_obj = new CIBlockElement;
        $client_request_cost_ib = rrsIblock::getIBlockId('client_request_cost');

        if(!is_array($req_ids) || count($req_ids) == 0
        ){
            return $result;
        }

        //получаем стоимости по складам
        $req_to_wh_list = array(); //массив соотнесения складов, стоимостей и запросов
        $wh_active_id_list = array(); //массив активных складов, по наличию которых будем определять что делать с запросом

        //получаем все активные стоимости затронутых активных запросов
        $res = $el_obj->GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => $client_request_cost_ib,
                'PROPERTY_REQUEST' => $req_ids,
                'ACTIVE' => 'Y'
            ),
            false,
            false,
            array('ID', 'PROPERTY_REQUEST', 'PROPERTY_WAREHOUSE')
        );
        while($data = $res->Fetch()){
            $wh_active_id_list[$data['PROPERTY_WAREHOUSE_VALUE']] = true;
            $req_to_wh_list[$data['PROPERTY_REQUEST_VALUE']][$data['ID']] = $data['PROPERTY_WAREHOUSE_VALUE'];
        }

        //получаем активные склады для рассматриваемых запросов
        if(count($wh_active_id_list) > 0) {
            $res = $el_obj->GetList(
                array('ID' => 'ASC'),
                array(
                    'IBLOCK_ID' => rrsIblock::getIBlockId('client_warehouse'),
                    'ID' => array_keys($wh_active_id_list),
                    'PROPERTY_ACTIVE' => rrsIblock::getPropListKey('client_warehouse', 'ACTIVE', 'yes'),
                ),
                false,
                false,
                array('ID')
            );
            $wh_active_id_list = array(); //очищаем список складов, чтобы хранить в нем только те активные склады, что требуется для проверки
            while($data = $res->Fetch()){
                $wh_active_id_list[$data['ID']] = true;
            }
        }else{
            $wh_active_id_list = array(); //очищаем список складов (не оказалось складов в стоимостях - где-то в админке некорректно поудаляли)
        }

        $found_active_wh = false; //признак того, что для запроса имеется хотя бы один запрос с активным складом
        foreach($req_ids as $cur_check_request){
            if(isset($req_to_wh_list[$cur_check_request])){
                $found_active_wh = false;
                foreach($req_to_wh_list[$cur_check_request] as $cur_cost_id => $cur_wh){
                    if(isset($wh_active_id_list[$cur_wh])){
                        //найдена стоимость с активным складом -> не отмечаем запрос на деактивацию
                        $found_active_wh = true;
                        break;
                    }
                }
                if(!$found_active_wh){
                    //для запроса не найдено стоимостей с активными складами -> отмечаем его
                    $result[$cur_check_request] = true;
                }
            }else{
                //у активного запроса нет активных стоимостей - отмечаем его
                $result[$cur_check_request] = true;
            }
        }

        return $result;
    }

    /**
     * Деактивация покупателя агентом
     *
     * @param int $uid - ID покупателя
     *
     * @return bool - флаг
     */
    public static function deactivateClient($uid) {
        $result = false;

        $linked_user = false;

        global $USER;

        $agent_obj = new agent();
        if($agent_obj->checkClientByAgent($uid, $USER->GetID())){
            $linked_user = true;
        }

        if($linked_user){

            // 1) удаление привязки к агенту (если есть)
            $result = self::deleteAgentLink($uid);

        }

        return $result;
    }

    /**
     * Деактивация запроса покупателя
     * @param int $request_id - ID запроса
     */
    public static function deactivateRequestByID($request_id) {

        if(is_numeric($request_id)){
            CModule::IncludeModule('iblock');
            $el_obj = new CIBlockElement;

            //1. деактивация запроса
            $el_obj->SetPropertyValuesEx(
                $request_id,
                rrsIblock::getIBlockId('client_request'),
                array(
                    'ACTIVE' => rrsIblock::getPropListKey('client_request', 'ACTIVE', 'no')
                )
            );
            logRequestDeactivating($request_id); //пишем лог о деактивации запроса

            //2. удаление соответствий (также удаляются и встречные предложения)
            $filter = array(
                'UF_REQUEST_ID' => $request_id
            );
            $arLeads = lead::getLeadList($filter);
            if (is_array($arLeads) && sizeof($arLeads) > 0) {
                lead::deleteLeads($arLeads);
            }

            //3. меняем название запроса (взято из оригинальной деактивации)
            $el_obj->Update($request_id, array('NAME' => date('d.m.Y H:i:s')));
        }
    }

    /**
     * Удаление (деактивация) покупателя агентом или организатором (удаление, если пользователь в демо-режиме)
     * 1) деактивация запросов
     * 3) удаление привязки к агенту (если есть)
     * 3) деактивация складов
     * 4) удаление пар с участием покупателя
     * 5) удаление закешированных расстояний
     * 6) деактивация привязки покупателя к организаторам
     * 7) деактивация профиля учётной записи пользователя
     * 8) отправка увеломлений покупателю и (если требуется) организатору
     *
     * @param int $uid - ID покупателя
     * @param boolean $not_partner - флаг является ли отвязывающий пользователь организатором или агентом (для упрощения
     * проверки привязки к пользователю)
     *
     * @return bool - флаг удаления
     */
    public static function deleteClient($uid, $not_partner = false) {
        $result = false;

        $linked_user = false;

        global $USER;

        if($not_partner){
            $agent_obj = new agent();
            if($agent_obj->checkClientByAgent($uid, $USER->GetID())){
                $linked_user = true;
            }
        }elseif(self::checkLinkWithPartner($uid, $USER->GetID())){
            $linked_user = true;
        }

        if($linked_user){
            $demo_user = self::checkIfDemo($uid);

            // 1) деактивация/удаление запросов
            self::deactivateDeleteRequestsByClient($uid, $demo_user);

            // 2) удаление привязки к агенту (если есть)
            self::deleteAgentLink($uid);

            // 3) деактивация/удаление складов
            $wh_list = self::deactivateDeleteWarehousesByClient($uid, $demo_user);

            // 4) удаление пар с участием покупателя
            self::deletePairs($uid);

            // 5) удаление закешированных расстояний
            if(count($wh_list > 0)){
                self::deleteCachedRoutes($wh_list);
            }

            // 6) деактивация/удаление привязки покупателя к организаторам
            self::deactivateDeletePartnerLink($uid, $demo_user);

            // 7) деактивация/удаление профиля и учётной записи пользователя
            self::deactivateDeleteProfile($uid, $demo_user);

            // 8) отправка уведомлений покупателю
            $profile_data = client::getProfile($uid, 1);
            $profile_name = '';

            $sender_data = array();
            $sender_name = '';

            $ev_obj = new CEvent;

            if(trim($profile_data['PROPERTY_FULL_COMPANY_NAME_VALUE']) != ''){
                $profile_name = trim($profile_data['PROPERTY_FULL_COMPANY_NAME_VALUE']) . ' (' . $profile_data['USER']['EMAIL'] . ')';
            }else{
                $profile_name = $profile_data['USER']['EMAIL'];
            }

            if($not_partner){
                $partner_id = client::getLinkedPartner($uid);
                if($partner_id > 0){
                    $agent_obj = new agent();
                    $sender_data = $agent_obj->getClientAgentProfile($USER->GetID()); // отправитель - агент
                    $sender_name = trim($sender_data['USER']['NAME'] . ' ' . $sender_data['USER']['LAST_NAME']);
                    if($sender_name != ''){
                        $sender_name .= ' (' . $sender_data['USER']['EMAIL'] . ')';
                    }else{
                        $sender_name = $sender_data['USER']['EMAIL'];
                    }

                    // отправка увеломлений организатору
                    $partner_data = partner::getProfile($partner_id);
                    $ev_obj->Send('PARTNER_NOTIFY_CLIENT_DEACTIVATE', 's1', array('EMAIL' => $partner_data['USER']['EMAIL'], 'BY' => $sender_name, 'USER_DATA' => $profile_name));
                }
            }else{
                $sender_data = partner::getProfile($USER->GetID(), true); // отправитель - организатор
                if(trim($sender_data['PROPERTY_FULL_COMPANY_NAME_VALUE']) != ''){
                    $sender_name .= trim($sender_data['PROPERTY_FULL_COMPANY_NAME_VALUE']) . ' (' . $sender_data['USER']['EMAIL'] . ')';
                }else{
                    $sender_name = $sender_data['USER']['EMAIL'];
                }
            }

            $ev_obj->Send('CLIENT_DEACTIVATE', 's1', array('EMAIL' => $profile_data['USER']['EMAIL'], 'BY' => $sender_name));
        }

        return $result;
    }

    /**
     * Проверка привязан ли покупатель к организатору
     *
     * @param int $client_id - ID покупателя
     * @param int $partner_id - ID организатораю)
     *
     * @return bool - признак привязки
     */
    public static function checkLinkWithPartner($client_id, $partner_id) {
        $result = false;

        if(is_numeric($partner_id)
            && $partner_id > 0
        ){
            //полученеи привязки к организатору (пользователь в демо режиме)
            $res = CIBlockElement::GetList(
                array('ID' => 'ASC'),
                array(
                    'IBLOCK_ID'             => rrsIblock::getIBlockId('client_profile'),
                    'ACTIVE'                => 'Y',
                    'PROPERTY_USER'         => $client_id,
                    'PROPERTY_PARTNER_ID'   => $partner_id
                ),
                false,
                array('nTopCount' => 1),
                array('ID')
            );
            if($res->SelectedRowsCount() > 0){
                $result = true;
            }else{
                //полученеи привязки к организатору (пользователь в полноценном режиме)
                $res = CIBlockElement::GetList(
                    array('ID' => 'ASC'),
                    array(
                        'IBLOCK_ID'             => rrsIblock::getIBlockId('client_partner_link'),
                        'ACTIVE'                => 'Y',
                        'PROPERTY_USER_ID'      => $client_id,
                        'PROPERTY_PARTNER_ID'   => $partner_id
                    ),
                    false,
                    array('nTopCount' => 1),
                    array('ID')
                );
                if($res->SelectedRowsCount() > 0){
                    $result = true;
                }
            }
        }

        return $result;
    }

    /**
     * Деактивация складов покупателя
     *
     * @param int $client_id - ID покупателя
     *
     * @return [] - массив ID складов
     */
    public static function deactivateDeleteWarehousesByClient($client_id, $delete_flag = false) {
        $result = array();

        if(is_numeric($client_id)
            && $client_id > 0
        ){
            $warehouses_list = array();
            $el_obj = new CIBlockElement;

            $res = $el_obj->GetList(
                array('ID' => 'ASC'),
                array(
                    'IBLOCK_ID'         => rrsIblock::getIBlockId('client_warehouse'),
                    'ACTIVE'            => 'Y',
                    'PROPERTY_CLIENT'   => $client_id
                ),
                false,
                false,
                array('ID', 'IBLOCK_ID')
            );
            while($data = $res->Fetch()){
                $warehouses_list[$data['ID']] = $data['IBLOCK_ID'];
            }

            if(count($warehouses_list) > 0){
                foreach($warehouses_list as $cur_id => $cur_ib){
//                    if($delete_flag)
//                        $el_obj->Delete($cur_id);
//                    else
                        $el_obj->SetPropertyValuesEx(
                            $cur_id,
                            $cur_ib,
                            array(
                                'ACTIVE' => rrsIblock::getPropListKey('client_warehouse', 'ACTIVE', 'no')
                            )
                        );
                }

                $result = array_keys($warehouses_list);
            }
        }

        return $result;
    }

    /**
     * Деактивация запросов покупателя
     *
     * @param int $client_id - ID покупателя
     *
     * @return bool - флаг успеха операции
     */
    public static function deactivateDeleteRequestsByClient($client_id, $delete_flag = false) {
        $result = false;

        if(is_numeric($client_id)
            && $client_id > 0
        ){
            $requests_list = array();
            $el_obj = new CIBlockElement;

            $res = $el_obj->GetList(
                array('ID' => 'ASC'),
                array(
                    'IBLOCK_ID'         => rrsIblock::getIBlockId('client_request'),
                    //'ACTIVE'            => 'Y',
                    'PROPERTY_ACTIVE'   => rrsIblock::getPropListKey('client_request', 'ACTIVE', 'yes'),
                    'PROPERTY_CLIENT'   => $client_id
                ),
                false,
                false,
                array('ID', 'IBLOCK_ID')
            );
            while($data = $res->Fetch()){
                $requests_list[$data['ID']] = $data['IBLOCK_ID'];
            }

            if(count($requests_list) > 0){
                foreach($requests_list as $cur_id => $cur_ib){
//                    if($delete_flag)
//                        $el_obj->Delete($cur_id);
//                    else{
                        $el_obj->SetPropertyValuesEx(
                            $cur_id,
                            $cur_ib,
                            array(
                                'ACTIVE' => rrsIblock::getPropListKey('client_request', 'ACTIVE', 'no')
                            ));
                        logRequestDeactivating($cur_id); //пишем лог о деактивации запроса
//                    }
                }
                $result = true;
            }
        }

        return $result;
    }

    /**
     * Удаление связи покупателя с его агентом
     *
     * @param int $client_id - ID покупателя
     *
     * @return bool - флаг успеха операции
     */
    public static function deleteAgentLink($client_id) {
        $result = false;

        if(is_numeric($client_id)
            && $client_id > 0
        ){
            $el_obj = new CIBlockElement;

            $res = $el_obj->GetList(
                array('ID' => 'ASC'),
                array(
                    'IBLOCK_ID'         => rrsIblock::getIBlockId('client_agent_link'),
                    'PROPERTY_USER_ID'  => $client_id
                ),
                false,
                false,
                array('ID')
            );
            if($data = $res->Fetch()){
                $el_obj->Delete($data['ID']);
                $result = true;
            }
        }

        return $result;
    }

    /**
     * Удаление пар по товарам покупателя
     *
     * @param int $client_id - ID покупателя
     *
     * @return bool - флаг успеха операции
     */
    public static function deletePairs($client_id) {
        $result = false;

        if(is_numeric($client_id)
            && $client_id > 0
        ){
            $leadList = lead::getLeadList(array('UF_CLIENT_ID' => $client_id));

            if(is_array($leadList)
                && count($leadList) > 0
            ){
                lead::deleteLeads($leadList);
                $result = true;
            }
        }

        return $result;
    }

    /**
     * Деактивация/удаление профиля и учётной записи пользователя
     *
     * @param int $client_id - ID поставщика
     *
     * @return bool - флаг успеха операции
     */
    public static function deactivateDeletePartnerLink($client_id, $delete_flag = false) {
        $result = false;

        $link_list = array();

        if(is_numeric($client_id)
            && $client_id > 0
        ){
            $el_obj = new CIBlockElement;

            $res = $el_obj->GetList(
                array('ID' => 'ASC'),
                array(
                    'IBLOCK_ID'     => rrsIblock::getIBlockId('client_partner_link'),
                    'ACTIVE'        => 'Y',
                    'PROPERTY_USER_ID' => $client_id
                ),
                false,
                false,
                array('ID')
            );
            while($data = $res->Fetch()){
                $link_list[$data['ID']] = true;
            }
            if(count($link_list) > 0){
                foreach($link_list as $cur_id => $cur_flag){
//                    if($delete_flag)
//                        $el_obj->Delete($cur_id);
//                    else
                        $el_obj->Update($cur_id, array('ACTIVE' => 'N'));
                }
                $result = true;
            }

            $result = true;
        }

        return $result;
    }

    /**
     * Деактивация/удаление профиля и учётной записи пользователя
     *
     * @param int $client_id - ID поставщика
     *
     * @return bool - флаг успеха операции
     */
    public static function deactivateDeleteProfile($client_id, $delete_flag = false) {
        $result = false;

        if(is_numeric($client_id)
            && $client_id > 0
        ){
            $el_obj = new CIBlockElement;
            $u_obj  = new CUser;

            $res = $el_obj->GetList(
                array('ID' => 'ASC'),
                array(
                    'IBLOCK_ID'     => rrsIblock::getIBlockId('client_profile'),
                    'ACTIVE'        => 'Y',
                    'PROPERTY_USER' => $client_id
                ),
                false,
                false,
                array('ID')
            );
            if($data = $res->Fetch()){
//                if($delete_flag)
//                    $el_obj->Delete($data['ID']);
//                else
                    $el_obj->Update($data['ID'], array('ACTIVE' => 'N'));
            }

//            if($delete_flag)
//                $u_obj->Delete($client_id);
//            else
                $u_obj->Update($client_id, array('ACTIVE' => 'N'));

            $result = true;
        }

        return $result;
    }

    /**
     * Удаление пар по товарам клиента
     *
     * @param [] $warehouses_list - массив ID клиента
     *
     * @return bool - флаг успеха операции
     */
    public static function deleteCachedRoutes($warehouses_list) {
        $result = false;

        $routes_hl_block_id = 10;

        if(is_array($warehouses_list)
            && count($warehouses_list) > 0
        ){
            $routes_list = array();
            $entityDataClass = log::getEntityDataClass($routes_hl_block_id);
            $el = new $entityDataClass;

            $rsData = $el->getList(array(
                'select' => array('ID'),
                'filter' => array(
                    'UF_CLIENT_WH_ID' => $warehouses_list
                )
            ));
            while ($res = $rsData->fetch()) {
                $routes_list[$res['ID']] = true;
            }

            foreach($routes_list as $cur_id => $cur_flag){
                log::_deleteEntity($routes_hl_block_id, $cur_id);
            }

            $result = true;
        }

        return $result;
    }

    /**
     * Проверка - находится ли пользователь в демо режиме
     *
     * @param int $uid - ID пользователя
     *
     * @return bool - флаг успеха операции
     */
    public static function checkIfDemo($uid) {
        $result = false;

        $routes_hl_block_id = 10;

        if(is_numeric($uid)
            && $uid > 0
        ){
            $u_ogj = new CUser;

            $res = $u_ogj->GetList(
                ($by = 'id'),
                ($order = 'desc'),
                array('ID' => $uid),
                array('FIELDS' => array('ID'), 'SELECT' => array('UF_DEMO'))
            );
            if($data = $res->Fetch()){
                if(isset($data['UF_DEMO'])
                    && $data['UF_DEMO']
                ){
                    $result = true;
                }
            }

            $result = true;
        }

        return $result;
    }

    /**
     * Проверка - заполняется ли профиль первый раз
     *
     * @param int $uid - ID пользователя
     *
     * @return bool - флаг того первый ли раз заполняется профиль
     */
    public static function checkIfFirstProfile($uid) {
        $result = false;

        if(is_numeric($uid)
            && $uid > 0
        ){
            $el_obj = new CIBlockElement;

            $res = $el_obj->GetList(
                array('ID' => 'ASC'),
                array(
                    'IBLOCK_ID'     => rrsIblock::getIBlockId('client_profile'),
                    'PROPERTY_USER' => $uid,
                    'PROPERTY_INN' => false
                ),
                false,
                array('nTopCount' => 1),
                array('ID')
            );
            if($res->SelectedRowsCount() > 0){
                $result = true;
            }
        }

        return $result;
    }

    /**
     * Получение записей истории операций с принятиями и ограничениями сущностей для пользователя (покупателя и поставщика)
     * @param int $uid - ID пользователя
     * @param array $params - дополнительные параметры
     *
     * @return array - данные истории операций с принятиями
     */
    public static function limitsHistory($uid, $params) {
        $result = array();

        $arFilter = array();
        $filter = array();
        $order = array();
        $select = array();
        $limit = array();

        //типы данных (принятия/ограничения запросов/ограничения товаров - соответствует полю UF_ENTITY_TYPE в БД)
        $types_arr = array(
            1 => 'client_counter_request',
            2 => 'client_request_limit',
            3 => 'farmer_offer_limit'
        );
        $selected_types = array();

        //устанавливаем данные для выборки
        if(isset($params['filter'])){
            $filter = $params['filter'];
        }

        $filter['UF_USER_ID'] = $uid;
        if($uid == 0
            && isset($params['by_admin'])
        ){
            //администратор получает данные для всех пользователей
            unset($filter['UF_USER_ID']);
        }

        //проверка фильтрации по типу данных
        if(isset($params['data_type'])
            && is_array($params['data_type'])
            && count($params['data_type']) > 0
        ){
            foreach($params['data_type'] as $cur_index){
                if(isset($types_arr[$cur_index])){
                    $selected_types[] = $types_arr[$cur_index];
                }
            }
        }
        if(count($selected_types) > 0){
            if(count($selected_types) == 1){
                $filter['UF_ENTITY_TYPE'] = $selected_types[0];
            }else{
                $filter['?UF_ENTITY_TYPE'] = '(' . implode('||', $selected_types) . ')';
            }
        }else{
            //если типы данных не заданы, то убираем данные из вывода
            $filter['ID'] = 0;
        }

        if(isset($params['order'])){
            $order = $params['order'];
        }else{
            $order = array('ID' => 'DESC');
        }

        if(isset($params['select'])){
            $select = $params['select'];
        }else{
            $select = array('*');
        }

        if(isset($params['limit'])){
            $limit = $params['limit'];
        }else{
            $limit = 50;
        }

        if(isset($params['offset'])){
            $offset = $params['offset'];
        }else{
            $offset = 0;
        }

        $arFilter = array(
            'count_total' => true,
            'order' => $order,
            'select' => $select,
            'filter' => $filter,
            'limit' => $limit,
            'offset' => $offset
        );

        //подгатавливаем объекты для запроса
        CModule::IncludeModule('highloadblock');
        $hlblock = \Bitrix\Highloadblock\HighloadBlockTable::getById(rrsIblock::HLgetIBlockId('CONTREQLIMITSLOG'))->fetch();
        $entity = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlblock);
        $entityDataClass = $entity->getDataClass();
        $el_obj = new $entityDataClass;

        //получаем данные
        $comments_data = array();
        $res = $el_obj->getList($arFilter);
        if($res->getCount() > 0) {
            $result['CNT'] = $res->getCount();
            while ($data = $res->fetch()) {
                $result['ITEMS'][$data['ID']] = array(
                    'ACTION' => $data['UF_ACTION'],
                    'DATE' => $data['UF_DATE']->ToString(),
                    'NUMBER' => intval($data['UF_NUMBER']),
                    'BEFORE' => intval($data['UF_BEFORE']),
                    'AFTER' => intval($data['UF_AFTER']),
                    'ELEMENT_ID' => $data['UF_ELEMENT_ID'],
                    'ENTITY_TYPE' => $data['UF_ENTITY_TYPE'],
                );

                if(isset($params['by_admin'])){
                    $result['ITEMS'][$data['ID']]['UID'] = $data['UF_USER_ID'];
                }
            }
        }

        return $result;
    }

    /**
     * Получение пользовательского тарифа на перевозку
     * @param  [] параметры для определения тарифа
     * @return number тариф
     */
    public static function getTarif($client_id, $group_id, $type, $center_id, $km, $arTariffs) {
        if (!is_array($arTariffs) || sizeof($arTariffs) < 1)
            return 0;

        foreach ($arTariffs as $key => $arTariff) {
            if ($km >= $arTariff['FROM']) {
                $tariffId = $key;
            }
            if ($km < $arTariff['TO']) {
                break;
            }
        }

        if (!$tariffId)
            return 0;

        CModule::IncludeModule('iblock');
        if ($type == 'cpt') {
            $ib = rrsIblock::getIBlockId('tariff');
            $arFilter = array('IBLOCK_ID' => $ib, 'ACTIVE' => 'Y', 'PROPERTY_CENTER' => $center_id, 'PROPERTY_TARIF_ID' => $tariffId);
        }
        elseif ($type == 'fca') {
            $ib = rrsIblock::getIBlockId('client_tariffs');
            $arFilter = array('IBLOCK_ID' => $ib, 'ACTIVE' => 'Y', 'PROPERTY_USER' => $client_id, 'PROPERTY_TYPE' => $group_id, 'PROPERTY_TARIF_ID' => $tariffId);
        }
        else {
            return 0;
        }

        $res = CIBlockElement::GetList(array('ID' => 'ASC'), $arFilter, false, false, array('ID', 'NAME', 'PROPERTY_TARIF'));
        if ($ob = $res->Fetch()) {
            $result = round($ob['PROPERTY_TARIF_VALUE'], 0);
        }

        if (!$result) {
            $result = round($arTariffs[$tariffId]['TARIF'], 0);
        }

        return $result;
    }

    /**
     * Получение пользовательского тарифа на перевозку в виде Диапазона
     * @param  [] параметры для определения тарифа
     * @return number тариф
     */
    public static function getTariffRange($iKM, $arTariffs) {
        if (!is_array($arTariffs) || sizeof($arTariffs) < 1)
            return 0;

        foreach ($arTariffs as $key => $arTariff) {
            if ($iKM >= $arTariff['FROM']) {
                $iTariffId = $key;
            }
            if ($iKM < $arTariff['TO']) {
                break;
            }
        }

        if (!$iTariffId)
            return 0;

        return $arTariffs[$iTariffId];
    }
    /**
     * Получение страницы для переадресации, если текущая страница со списком запросов не содержит требуемый
     * @param int $req_id id запроса
     * @param int $page_size количество элементов на странице
     * @param int $page_value номер текущей страницы
     * @return string адрес страницы
     */
    public static function getRequestListRedirectById($req_id, $page_size, $page_value = 1) {
        $result = '';
        CModule::IncludeModule('iblock');
        $ib_id = rrsIblock::getIBlockId('client_request');
        $el_obj = new CIBlockElement;

        if(filter_var($page_value, FILTER_VALIDATE_INT) === false
            || $page_value < 1
        ){
            $page_value = 1;
        }
        if(filter_var($page_size, FILTER_VALIDATE_INT) === false
            || $page_size < 1
        ){
            $page_size = 1;
        }

        //получение номера страницы элемента
        $tabFilterSuf = 'all';
        $arFilter = array(
            'IBLOCK_ID' => $ib_id
        );
        if(isset($GLOBALS['arrFilter']['PROPERTY_ACTIVE'])
            && filter_var($GLOBALS['arrFilter']['PROPERTY_ACTIVE'], FILTER_VALIDATE_INT)
        ){
            $arFilter['PROPERTY_ACTIVE'] = $GLOBALS['arrFilter']['PROPERTY_ACTIVE'];
            $active_prop_list = rrsIblock::getPropListId('client_request', 'ACTIVE');
            if(isset($active_prop_list[$arFilter['PROPERTY_ACTIVE']]['XML_ID'])){
                if($active_prop_list[$arFilter['PROPERTY_ACTIVE']]['XML_ID'] == 'yes'){
                    $tabFilterSuf = 'yes';
                }elseif($active_prop_list[$arFilter['PROPERTY_ACTIVE']]['XML_ID'] == 'no'){
                    $tabFilterSuf = 'no';
                }
            }
        }
        if(isset($GLOBALS['arrFilter']['PROPERTY_CLIENT'])
            && is_array($GLOBALS['arrFilter']['PROPERTY_CLIENT'])
            && count($GLOBALS['arrFilter']['PROPERTY_CLIENT']) > 0
        ){
            $arFilter['PROPERTY_CLIENT'] = $GLOBALS['arrFilter']['PROPERTY_CLIENT'];
        }
        if(isset($GLOBALS['arrFilter']['PROPERTY_CULTURE'])
            && filter_var($GLOBALS['arrFilter']['PROPERTY_CULTURE'], FILTER_VALIDATE_INT)
        ){
            $arFilter['PROPERTY_CULTURE'] = $GLOBALS['arrFilter']['PROPERTY_CULTURE'];
        }
        if(!empty($GLOBALS['arrFilter']['ID'])){
            $arFilter['ID'] = $GLOBALS['arrFilter']['ID'];
        }

        $res = $el_obj->GetList(
            array(
                'PROPERTY_ACTIVE' => 'ASC',
                'ACTIVE_TO' => 'DESC'
            ),
            $arFilter,
            false,
            array(
                'nElementID' => $req_id,
                'nPageSize' => 0
            )
        );
        if($data = $res->Fetch()){
            if(isset($data['RANK'])
                && filter_var($data['RANK'], FILTER_VALIDATE_INT)
            ){
                $new_page = ceil($data['RANK'] / $page_size);

                if($page_value != $new_page
                    ||
                    //если пользователь пришел из графика, то переадресуем в любом случае
                    (isset($_GET['from_graph'])
                        && $_GET['from_graph'] == 'y'
                    )
                ) {
                    global $APPLICATION;

                    $page_str = '';
                    if ($new_page > 1) {
                        $page_str = 'PAGEN_1=' . $new_page;
                    }

                    if(isset($_GET['from_graph'])
                        && $_GET['from_graph'] == 'y'
                    ){
                        $page_str .= ($page_str != '' ? '&' : '') . 'culture=' . $GLOBALS['arrFilter']['PROPERTY_CULTURE'];
                        if($GLOBALS['rrs_user_perm_level'] == 'p'){
                            //запоминаем выбор фильтра
                            setcookie('client_ag_request_' . $tabFilterSuf . '_culture', $GLOBALS['arrFilter']['PROPERTY_CULTURE'], 0, '/');

                            if(!empty($GLOBALS['arrFilter']['PROPERTY_CLIENT'])) {
                                $page_str .= '&client_id[]=' . $GLOBALS['arrFilter']['PROPERTY_CLIENT'];
                                //запоминаем выбор фильтра
                                setcookie('client_ag_request_' . $tabFilterSuf . '_user', $GLOBALS['arrFilter']['PROPERTY_CLIENT'], 0, '/');
                            }
                        }else{
                            $page_str = 'culture_id=' . $GLOBALS['arrFilter']['PROPERTY_CULTURE'];
                            //запоминаем выбор фильтра
                            setcookie('client_request_' . $tabFilterSuf . '_culture', $GLOBALS['arrFilter']['PROPERTY_CULTURE'], 0, '/');
                            if(!empty($GLOBALS['arrFilter']['PROPERTY_WAREHOUSE'])) {
                                $page_str .= '&warehouse_id=' . $GLOBALS['arrFilter']['PROPERTY_WAREHOUSE'];
                                //запоминаем выбор фильтра
                                setcookie('client_request_' . $tabFilterSuf . '_wh', $GLOBALS['arrFilter']['PROPERTY_WAREHOUSE'], 0, '/');
                            }
                        }

                        $result = $APPLICATION->GetCurPageParam($page_str, array('PAGEN_1', 'from_graph', 'warehouse_id', 'culture_id', 'culture', 'client_id[]'));
                    }
                    else {
                        $result = $APPLICATION->GetCurPageParam($page_str, array('PAGEN_1', 'from_graph'));
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Проверка сохранения фильтра на странице запросов покупателя
     *
     * @return array массив, где у ключа 'NEED_UPD' значение либо true либо false,
     * у ключа 'URL_UPD' значение адреса переадресации
     */
    public static function filterRequestCheck() {
        $result = array(
            'NEED_UPD'  => false,
            'URL_UPD'   => ''
        );

        if(isset($_GET['request_id'])
            && $_GET['request_id'] > 0
            ||!empty($_GET['warehouse_id'])
            ||!empty($_GET['culture_id'])
        ){
            return $result;
        }

        $tabFilterSuf = '';
        $page_need_update = false;
        $new_url_params = array();

        switch($_REQUEST['status']){
            case 'yes':
                $tabFilterSuf = 'yes';
                break;
            case 'no':
                $tabFilterSuf = 'no';
                $new_url_params[] = 'status=no';
                break;
            default:
                $tabFilterSuf = 'all';
                $new_url_params[] = 'status=all';
        }

        $wh_cookie = '';
        $culture_cookie = '';
        //проверка куки склада
        $cookie_name = 'client_request_' . $tabFilterSuf . '_wh';
        if(isset($_COOKIE[$cookie_name])){
            $wh_cookie = $_COOKIE[$cookie_name];
            if((!isset($_GET['warehouse_id'])
                    || $_GET['warehouse_id'] == ''
                )
                && $wh_cookie != 0
                || isset($_GET['warehouse_id'])
                && $_GET['warehouse_id'] != ''
                && $_GET['warehouse_id'] != $wh_cookie
            ){
                $result['NEED_UPD'] = true;
                $new_url_params[] = 'warehouse_id=' . $wh_cookie;
            }
        }

        //проверка куки культуры
        $cookie_name = 'client_request_' . $tabFilterSuf . '_culture';
        if(isset($_COOKIE[$cookie_name])){
            $culture_cookie = $_COOKIE[$cookie_name];
            if((!isset($_GET['culture_id'])
                    || $_GET['culture_id'] == ''
                )
                && $culture_cookie != 0
                || isset($_GET['culture_id'])
                && $_GET['culture_id'] != ''
                && $_GET['culture_id'] != $culture_cookie
            ){
                $result['NEED_UPD'] = true;
                $new_url_params[] = 'culture_id=' . $culture_cookie;
            }
        }

        if($result['NEED_UPD']){
            $result['URL_UPD'] = '/client/request/' . (count($new_url_params) > 0 ? '?' . implode('&', $new_url_params) : '');
        }

        return $result;
    }

    /**
     * Проверка сохранения фильтра на странице запросов покупателя
     * @param boolean $is_agent - признак агента
     *
     * @return array массив, где у ключа 'NEED_UPD' значение либо true либо false,
     * у ключа 'URL_UPD' значение адреса переадресации
     */
    public static function filterCounterRequestCheck($is_agent = false) {
        $result = array(
            'NEED_UPD'  => false,
            'URL_UPD'   => ''
        );

        if(
            ((isset($_GET['warehouse_id']))&&(!empty($_GET['warehouse_id'])))
            || ((isset($_GET['culture_id']))&&(!empty($_GET['culture_id'])))
            || ((isset($_GET['region_id']))&&(!empty($_GET['region_id'])))
            || ((isset($_GET['client_id']))&&(!empty($_GET['client_id'])))
            || !empty($_GET['page'])
        ){
            return $result;
        }

        $new_url_params = array();

        //проверка куки склада
        $cookie_name = 'count_req_filter_warehouse';
        if(isset($_COOKIE[$cookie_name])){
            $cookie_value = $_COOKIE[$cookie_name];
            if($cookie_value != 0 &&
                (
                    (!isset($_GET['warehouse_id'])
                        || $_GET['warehouse_id'] == ''
                    )
                    || isset($_GET['warehouse_id'])
                    && $_GET['warehouse_id'] != ''
                    && $_GET['warehouse_id'] != $cookie_value
                )
            ){
                $result['NEED_UPD'] = true;
                $new_url_params[] = 'warehouse_id=' . $cookie_value;
            }
        }

        //проверка куки культуры
        $cookie_name = 'count_req_filter_culture';
        if(isset($_COOKIE[$cookie_name])){
            $cookie_value = $_COOKIE[$cookie_name];
            if($cookie_value != 0 &&
                (
                    (!isset($_GET['culture_id'])
                        || $_GET['culture_id'] == ''
                    )
                    || isset($_GET['culture_id'])
                    && $_GET['culture_id'] != ''
                    && $_GET['culture_id'] != $cookie_value
                )
            ){
                $result['NEED_UPD'] = true;
                $new_url_params[] = 'culture_id=' . $cookie_value;
            }
        }

        //проверка куки региона
        $cookie_name = 'count_req_filter_region';
        if(isset($_COOKIE[$cookie_name])){
            $cookie_value = $_COOKIE[$cookie_name];
            if($cookie_value != 0 &&
                (
                    (!isset($_GET['region_id'])
                        || $_GET['region_id'] == ''
                    )
                    || isset($_GET['region_id'])
                    && $_GET['region_id'] != ''
                    && $_GET['region_id'] != $cookie_value
                )
            ){
                $result['NEED_UPD'] = true;
                $new_url_params[] = 'region_id=' . $cookie_value;
            }
        }

        //проверка куки покупателей
        $cookie_name = 'count_req_filter_client';
        if(isset($_COOKIE[$cookie_name])){
            $cookie_value = $_COOKIE[$cookie_name];
            if($cookie_value != 0 &&
                (
                    (!isset($_GET['client_id'])
                        || $_GET['client_id'] == ''
                    )
                    || isset($_GET['client_id'])
                    && $_GET['client_id'] != ''
                    && $_GET['client_id'] != $cookie_value
                )
            ){
                $result['NEED_UPD'] = true;
                $new_url_params[] = 'client_id=' . $cookie_value;
            }
        }

        if($result['NEED_UPD']){
            $result['URL_UPD'] = '/' . ($is_agent ? 'partner/client_' : 'client/') . 'exclusive_offers/' . (count($new_url_params) > 0 ? '?' . implode('&', $new_url_params) : '');
        }

        return $result;
    }

    /**
     * Проверка пользователя на принадлежность к группе "Покупатель"
     * @param $iUserId
     * @return bool
     */
    public static function checkIsClient($iUserId) {

        $obUser     = new CUser;
        $obGroup    = new CGroup;

        // Группа "Поставщик"
        $arGroupClient = $obGroup->GetList(
            $by = "c_sort",
            $order = "asc",
            ['STRING_ID' => 'client']
        )->Fetch();

        // Группы пользователя
        $arGroupUser = $obUser->GetUserGroup($iUserId);

        unset($obUser, $obGroup);

        return in_array($arGroupClient['ID'], $arGroupUser);
    }


    /**
     * Проверяет есть ли возможность изменить НДС у "Покупателя"
     * @param $iClientId
     * @return array
     */
    public static function isChangeNDS($iClientId) {

        $arResult = [
            'LOCK'  => false,
            'MSG'   => null,
        ];

        try {

            // Проверяем есть ли активные запросы
            $arRequest = self::isSetActiveRequest($iClientId);

            if(!empty($arRequest)) {
                throw new Exception('В системе есть активные запросы');
            }

            // Проверяем есть ли активные сделки
            $arDeal = deal::getUsersActiveDeals(false, $iClientId);
            if($arDeal[$iClientId]) {
                throw new Exception('В системе есть активные сделки');
            }

            // На всякий проверим может у пользователя и не может быть запросов и сделок
            if(!self::checkIsClient($iClientId)) {
                throw new Exception('Вы не являетесь покупателем');
            }

        } catch (Exception $e) {
            $arResult['LOCK']   = true;
            $arResult['MSG']    = $e->getMessage();
        }

        return $arResult;
    }


    /**
     * Проверяет есть ли у покупателя активные запросы
     * @param $iClientId
     * @return bool
     */
    public static function isSetActiveRequest($iClientId) {

        // Значения списка активности
        $arListActive['BY_ID'] = rrsIblock::getPropListId('client_request', 'ACTIVE');
        $arListActive['BY_XML_ID'] = [];
        foreach ($arListActive['BY_ID'] as $iId => $arItemList) {
            $arListActive['BY_XML_ID'][$arItemList['XML_ID']] = &$arListActive['BY_ID'][$iId];
        }

        // Выборка активного запроса
        $arRequest = CIBlockElement::GetList(
            [],
            [
                'ACTIVE'            => 'Y',
                'IBLOCK_ID'         => getIBlockID('client', 'client_request'),
                'PROPERTY_CLIENT'   => $iClientId,
                'PROPERTY_ACTIVE'   => $arListActive['BY_XML_ID']['yes']['ID'],
            ],
            false,
            false,
            ['ID',]
        )->Fetch();

        unset($arListActive);

        return !empty($arRequest['ID']);
    }


    /**
     * возвращает данные организатора, привязанного к пользователю (для рассылки при регистрации из приглашения)
     * @param $userId - id пользователя
     * @param $is_invited - признак того был ли приглашен пользователь или регистрируется самостоятельно
     * @return array - массив данных с ключами [EMAIL] - почта, [NAME] - ФИО или логин
     */
    public static function getPartnerEmailData($userId, $is_invited = false) {
        $result = array();

        if($is_invited){
            $res = CIBlockElement::GetList(
                array('ID' => 'ASC'),
                array(
                    'IBLOCK_ID' => rrsIblock::getIBlockId('client_partner_link'),
                    'PROPERTY_USER_ID' => $userId
                ),
                false,
                array('nTopCount' => 1),
                array('PROPERTY_PARTNER_ID')
            );
        }else{
            $res = CIBlockElement::GetList(
                array('ID' => 'ASC'),
                array(
                    'IBLOCK_ID' => rrsIblock::getIBlockId('client_profile'),
                    'PROPERTY_USER' => $userId
                ),
                false,
                array('nTopCount' => 1),
                array('PROPERTY_PARTNER_ID')
            );
        }

        if($data = $res->Fetch()){
            if(isset($data['PROPERTY_PARTNER_ID_VALUE'])
                && is_numeric($data['PROPERTY_PARTNER_ID_VALUE'])
            ) {
                $res = CUser::GetList(
                    ($by = 'id'), ($order = 'asc'),
                    array('ID' => $data['PROPERTY_PARTNER_ID_VALUE']),
                    array('FIELDS' => array('NAME', 'LAST_NAME', 'EMAIL', 'LOGIN'))
                );
                if($data = $res->Fetch()){
                    $result['EMAIL'] = $data['EMAIL'];

                    $temp_name = trim($data['NAME'] . ' ' . $data['LAST_NAME']);
                    if($temp_name == ''){
                        $temp_name = $data['LOGIN'];
                    }

                    $result['NAME'] = $temp_name;
                }
            }
        }

        return $result;
    }

    /**
     * Удаляет все встречные предложения АП по ID запроса Клиента (удаление из HL)
     * @param $iRequestID - ID запроса пользователя
     * @throws Exception
     */
    public static function removeCountersByRequestID($iRequestID) {
        // filter_var без проверки на false т.к 0 тоже не нужен
        if(!filter_var($iRequestID, FILTER_VALIDATE_INT)
            && (!is_array($iRequestID) || count($iRequestID) == 0)
        ) {
            throw new Exception('Не передан ID запроса');
        }

        CModule::IncludeModule('highloadblock');

        $arFilter   = [
            'UF_REQUEST_ID' => $iRequestID
        ];
        $iHL        = log::getIdByName('COUNTEROFFERS');
        $arCounters = log::_getEntitiesList($iHL, $arFilter);


        foreach ($arCounters as $arCounterRequest) {
            log::_deleteEntity($iHL, $arCounterRequest['ID']);
        }
    }

    /**
     * Удаляет все встречные запросы АП по ID склада Клиента (удаление из HL)
     * @param $wh_id - ID склада/складов покупателя
     * @throws Exception
     */
    public static function removeCountersByWhID($wh_id) {
        // filter_var без проверки на false т.к 0 тоже не нужен
        if(!filter_var($wh_id, FILTER_VALIDATE_INT)
            && (!is_array($wh_id) || count($wh_id) == 0)
        ) {
            throw new Exception('Не передан ID склада');
        }

        CModule::IncludeModule('highloadblock');

        $arFilter   = [
            'UF_CLIENT_WH_ID' => $wh_id
        ];
        $iHL        = log::getIdByName('COUNTEROFFERS');
        $arCounters = log::_getEntitiesList($iHL, $arFilter);


        foreach ($arCounters as $arCounterRequest) {
            log::_deleteEntity($iHL, $arCounterRequest['ID']);
        }
    }

    /**
     * Возвращает данные встречных предложений покупателя
     * @param mixed $userId - id покупателя (покупателей)
     * @param array $additional_filter - дополнительная фильтрация выборки
     * @param array &$filters_count - массив уникальных фильтров с кол-вом предложений по ним
     * @param array $get_filter_count - флаг того, что нужно получить массив фильтров
     * @return array - массив данных предложений [EMAIL] - почта, [NAME] - ФИО или логин
     */
    public static function getCounterRequestData($userId = false, $additional_filter = array(),&$filters_count = array(),$get_filter_count = false) {
        $result = array();

        $arFilter = array();

        if(is_numeric($userId)
            || is_array($userId) && count($userId) > 0
        ){
            $arFilter['UF_CLIENT_ID'] = $userId;
        }
        $request_cultures = array();
        if(isset($additional_filter['culture_id'])
            && is_numeric($additional_filter['culture_id'])
        ){
            //получение запросов
            CModule::IncludeModule('iblock');
            $res = CIBlockElement::GetList(
                array('ID' => 'ASC'),
                array('IBLOCK_ID' => rrsIblock::getIBlockId('client_request'), 'PROPERTY_CLIENT' => $userId, 'PROPERTY_CULTURE' => $additional_filter['culture_id']),
                false,
                false,
                array('ID')
            );
            while($data = $res->Fetch()){
                $arFilter['UF_REQUEST_ID'][] = $data['ID'];
            }
            if($res->SelectedRowsCount() == 0){
                $arFilter['UF_REQUEST_ID'] = 0;
            }
        }elseif($get_filter_count === true){
            //получение запросов
            CModule::IncludeModule('iblock');
            $res = CIBlockElement::GetList(
                array('ID' => 'ASC'),
                array('IBLOCK_ID' => rrsIblock::getIBlockId('client_request'), 'PROPERTY_CLIENT' => $userId),
                false,
                false,
                array('ID','PROPERTY_CULTURE')
            );
            while($data = $res->Fetch()){
                $request_cultures[$data['ID']] = $data['PROPERTY_CULTURE_VALUE'];
            }
        }
        if(isset($additional_filter['warehouse_id'])
            && is_numeric($additional_filter['warehouse_id'])
        ){
            $arFilter['UF_CLIENT_WH_ID'] = $additional_filter['warehouse_id'];
        }

        if(isset($additional_filter['UF_REQUEST_ID'])
            && is_numeric($additional_filter['UF_REQUEST_ID'])
        ){
            $arFilter['UF_REQUEST_ID'] = $additional_filter['UF_REQUEST_ID'];
        }

        if((isset($additional_filter['UF_CLIENT_WH_ID'])
            && is_numeric($additional_filter['UF_CLIENT_WH_ID'])
        )||(isset($additional_filter['UF_CLIENT_WH_ID'])
                && is_array($additional_filter['UF_CLIENT_WH_ID']))){
            $arFilter['UF_CLIENT_WH_ID'] = $additional_filter['UF_CLIENT_WH_ID'];
        }

        if(isset($additional_filter['UF_OFFER_ID'])
            && is_numeric($additional_filter['UF_OFFER_ID'])
        ){
            $arFilter['UF_OFFER_ID'] = $additional_filter['UF_OFFER_ID'];
        }

        if(isset($additional_filter['UF_FARMER_WH_ID'])
            && is_numeric($additional_filter['UF_FARMER_WH_ID'])
        ){
            $arFilter['UF_FARMER_WH_ID'] = $additional_filter['UF_FARMER_WH_ID'];
        }

        //подготовка данных для получения информации из БД
//        $hlblock = \Bitrix\Highloadblock\HighloadBlockTable::getById(rrsIblock::HLgetIBlockId('COUNTEROFFERS'))->fetch();
//        $entity = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlblock);
//        $entityDataClass = $entity->getDataClass();
//        $el_obj = new $entityDataClass;
//        $el_filter = array(
//            'select' => array('*'),
//            'filter' => $arFilter,
//            'order' => array('ID')
//        );
//        $data_arr = array();


        $data_arr = log::_getEntitiesList(log::getIdByName('COUNTEROFFERS'), $arFilter);
        foreach ($data_arr as $cur_data){
            $temp_data = explode(' ', $cur_data['UF_DATE']->toString());
            $result[] = array(
                'ID' => $cur_data['ID'],
                'UF_OFFER_ID'       => $cur_data['UF_OFFER_ID'],
                'UF_REQUEST_ID'     => $cur_data['UF_REQUEST_ID'],
                'UF_CLIENT_ID'     => $cur_data['UF_CLIENT_ID'],
                'UF_NDS_CLIENT'     => $cur_data['UF_NDS_CLIENT'],
                'UF_NDS_FARMER'     => $cur_data['UF_NDS_FARMER'],
                'UF_TYPE'           => $cur_data['UF_TYPE'],
                'UF_DELIVERY'       => $cur_data['UF_DELIVERY'],
                'UF_VOLUME'         => $cur_data['UF_VOLUME'],
                'UF_BASE_CONTR_PRICE' => $cur_data['UF_BASE_CONTR_PRICE'], //базисная цена (контракта)
                'UF_FARMER_PRICE'   => $cur_data['UF_FARMER_PRICE'],  //цена с места (ЦСМ) от АП
                'UF_CLIENT_WH_ID'   => $cur_data['UF_CLIENT_WH_ID'],
                'UF_FARMER_WH_ID'   => $cur_data['UF_FARMER_WH_ID'],
                'UF_VOLUME_OFFER'   => $cur_data['UF_VOLUME_OFFER'],
                'UF_VOLUME_REMAINS' => $cur_data['UF_VOLUME_REMAINS'],
                'UF_DATE'           => $temp_data[0],
                'UF_ADDIT_FIELDS'   => $cur_data['UF_ADDIT_FIELDS'],
                'UF_COFFER_TYPE'    => $cur_data['UF_COFFER_TYPE'],
                'UF_PARTNER_PRICE'  => $cur_data['UF_PARTNER_PRICE'],
                'UF_CREATE_BY_PARTNER'   => $cur_data['UF_CREATE_BY_PARTNER'],
                'UF_PARTNER_Q_APRVD'   => $cur_data['UF_PARTNER_Q_APRVD'],
                'UF_PARTNER_Q_APRVD_D'   => $cur_data['UF_PARTNER_Q_APRVD_D'],
            );
            if(isset($additional_filter['culture_id'])
                && is_numeric($additional_filter['culture_id'])
            ){
                $culture_id = $additional_filter['culture_id'];
            }else{
                if(isset($request_cultures[$cur_data['UF_REQUEST_ID']])){
                    $culture_id = $request_cultures[$cur_data['UF_REQUEST_ID']];
                }
            }
            if(!empty($culture_id)){
                $filters_count[$cur_data['UF_CLIENT_ID'].'|'.$culture_id.'|'.$cur_data['UF_CLIENT_WH_ID']]+=1;
            }
        }
        return $result;
    }


    /**
     * обновляет объемы у запросов, по которому принято встречное предложение
     * @param $offer_id - id товара АП
     * @param $remails_volume - остаток от указанного в объеме товара
     */
    public static function counterRequestsRecountVolume($offer_id, $remains_volume, $culture_id, $culture_name) {
        $arFilter = array(
            'UF_OFFER_ID' => $offer_id
        );
        $hl_id = log::getIdByName('COUNTEROFFERS');

        $data_arr = log::_getEntitiesList($hl_id, $arFilter);
        if(count($data_arr) > 0){
            $users_email = array();
            $updateFields = array();

            //обновляем данные
            foreach($data_arr as $cur_data){
                $users_email[$cur_data['UF_CLIENT_ID']] = '';
            }
            if(count($users_email) > 0){
                $res = CUser::GetList(
                    ($by = 'id'), ($order = 'asc'),
                    array(
                        'ID' => implode(' | ', array_keys($users_email)),
                        'ACTIVE' => 'Y'
                    ),
                    array('FIELDS' => array('ID', 'EMAIL'))
                );
                while($data = $res->Fetch()){
                    $users_email[$data['ID']] = $data['EMAIL'];
                }
            }

            foreach($data_arr as $cur_data){
                $updateFields = array(
                    'UF_VOLUME' => min($cur_data['UF_VOLUME'], $remains_volume),
                    'UF_VOLUME_REMAINS' => $remains_volume
                );
                log::_updateEntity($hl_id, $cur_data['ID'], $updateFields);

                if($updateFields['UF_VOLUME'] != $cur_data['UF_VOLUME']){
                    if($users_email[$cur_data['UF_CLIENT_ID']] != ''){
                        //изменился объём встречного предложения - оповещаем покупателя
                        $arEventFields = array(
                            'COUNTER_REQUEST_ID' => $cur_data['ID'],
                            'EMAIL' => $users_email[$cur_data['UF_CLIENT_ID']],
                            'REQUEST_DATA' => '<br/>Старый объём: ' . $cur_data['UF_VOLUME']
                                . '<br/>Новый объём: ' . $updateFields['UF_VOLUME']
                                . '<br/>Культура: ' . $culture_name
                                . '<br/>Цена предложения: ' . $cur_data['UF_BASE_CONTR_PRICE'] . ' руб/т<br/>',
                            'URL' => $GLOBALS['host'].'/client/exclusive_offers/?warehouse_id='
                                . $cur_data['UF_CLIENT_WH_ID'] . '&culture_id=' . $culture_id . '&o=' . $offer_id . '&r=' . $cur_data['UF_REQUEST_ID'],
                        );
                        CEvent::Send('CLIENT_COUNTER_REQUEST_CHANGE', SITE_ID, $arEventFields);
                    }
                }
            }
        }
    }

    /**
     * Возвращает привязки культур к запросам
     * @param $requests_ids - массив ID запросов
     * @return array - массив привязок запросов к культуре
     */
    public static function getCulturesByRequests($requests_ids) {
        $result = array();

        if(count($requests_ids) > 0) {
            CModule::IncludeModule('iblock');
            $el_obj = new CIBlockElement;
            $res = $el_obj->GetList(
                array('ID' => 'ASC'),
                array(
                    'ID' => $requests_ids,
                    'IBLOCK_ID' => rrsIblock::getIBlockId("client_request")
                ),
                false,
                false,
                array('ID', 'PROPERTY_CLIENT', 'PROPERTY_CULTURE')
            );
            while ($data = $res->Fetch()) {
                $result[$data['ID']] = array(
                    'user_id' => $data['PROPERTY_CLIENT_VALUE'],
                    'culture' => $data['PROPERTY_CULTURE_VALUE']
                );
            }
        }

        return $result;
    }

    /**
     * Возвращает признак того, что пользователь ни разу не авторизовывался на сайте
     * @param $user_ids - массив ID покупателей
     * @return array - массив признаков того, что пользователь ни разу не авторизовывался на сайте (ID пользотваеля является ключом)
     */
    public static function getUsersFirstEntrances($user_ids) {
        $result = array();

        if(count($user_ids) > 0) {
            $u_obj = new CUser;
            $res = $u_obj->GetList(
                ($by = 'id'), ($order = 'asc'),
                array(
                    'ID' => $user_ids,
                    'UF_FIRST_LOGIN' => 1
                ),
                array('FIELDS' => array('ID'))
            );
            while ($data = $res->Fetch()) {
                $result[$data['ID']] = true;
            }
        }

        return $result;
    }

    /*
     * Общая функция проверки прав покупателя
     *
     * @param string $type - тип проверки прав (request/counter request/warehouse/...)
     * @param int | array $id - id пользователя | пользователей
     * @param array $additional_args - массив дополнительных параметров
     *
     * @return array - массив данных с правами
     * (если $id - число, то возвращается массив прав для одного пользователя,
     * если $id массив, то возвращаются массивы прав для каждого пользователя)
     * */
    public static function checkRights($type, $id, $additional_args = array()){
        $result = array();
        $el_obj = new CIBlockElement;

        switch($type){
            case 'counter_request':

                //ап не может принимать запросы/отправлять встречные предложения, если у него не заполнен ИНН
                $res = $el_obj->GetList(
                    array('ID' => 'ASC'),
                    array(
                        'IBLOCK_ID' => rrsIblock::getIBlockId('client_profile'),
                        'PROPERTY_USER' => $id,
                        '!PROPERTY_INN' => false
                    ),
                    false,
                    false,
                    array('PROPERTY_USER', 'PROPERTY_INN', 'PROPERTY_COUNTER_REQUEST_LIMIT')
                );
                if(is_numeric($id)){
                    //для конкретного пользователя
                    if($data = $res->Fetch()) {
                        //проверяем наличие принятий
                        if(isset($data['PROPERTY_COUNTER_REQUEST_LIMIT_VALUE'])
                            && intval($data['PROPERTY_COUNTER_REQUEST_LIMIT_VALUE']) > 0
                        ){
                            $result['REQUEST_RIGHT'] = 'Y';
                        }else{
                            $result['REQUEST_RIGHT'] = 'LIM'; //кончились принятия
                        }
                    }
                }else{
                    //для группы пользователей
                    while($data = $res->Fetch()){
                        //проверяем наличие принятий
                        if(isset($data['PROPERTY_COUNTER_REQUEST_LIMIT_VALUE'])
                            && intval($data['PROPERTY_COUNTER_REQUEST_LIMIT_VALUE']) > 0
                        ) {
                            $result[$data['PROPERTY_USER_VALUE']]['REQUEST_RIGHT'] = 'Y';
                        }else{
                            $result[$data['PROPERTY_USER_VALUE']]['REQUEST_RIGHT'] = 'LIM'; //кончились принятия
                        }
                    }
                }

                break;
        }

        return $result;
    }

    /*
     * Получение записи черного списка поставщиков для покупателя
     * @param int $user_id - id покупателя
     *
     * @return array - массив данных с id поставщиков из черного списка покупателя
     * */
    public static function getUserBlackList($user_id){
        $result = array();

        $el_obj = new CIBlockElement;
        $res = $el_obj->GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('client_black_list'),
                'ACTIVE' => 'Y',
                'PROPERTY_USER' => $user_id,
            ),
            false,
            false,
            array('PROPERTY_OPPONENT')
        );
        while($data = $res->Fetch()){
            $result[$data['PROPERTY_OPPONENT_VALUE']] = true;
        }

        return array_keys($result);
    }

    /*
     * Изменение количества принятий у пользователей
     *
     * @param string $action_type - код типа действия
     * @param int $number - количество принятий (может быть отрицательным числом)
     * @param array $uids - массив id пользователей (может быть пустым)
     * @param array $element_id - ID записи действия админа (если есть)
     * */
    public static function counterReqLimitQuantityChange($action_type, $number, $uids, $element_id = 0){
        if(filter_var($number, FILTER_VALIDATE_INT) !== false) {
            //общие данные для лога в highload инфоблоке
            global $USER;
            $cur_date = ConvertTimeStamp(false, 'FULL');
            $created_by = $USER->GetID();

            CModule::IncludeModule('iblock');
            $el_obj = new CIBlockElement;

            //вносим изменения в зависимости от типа действия
            switch ($action_type) {
                case 'change':
                    //изменение значения (+/- к текущему значению)
                    $arFilter = array(
                        'IBLOCK_ID' => rrsIblock::getIBlockId('client_profile')
                    );
                    if(count($uids) > 0){
                        $arFilter['PROPERTY_USER'] = $uids;
                    }

                    $res = $el_obj->GetList(
                        array('ID' => 'ASC'),
                        $arFilter,
                        false,
                        false,
                        array('ID', 'PROPERTY_USER', 'PROPERTY_COUNTER_REQUEST_LIMIT')
                    );
                    while($data = $res->Fetch()){
                        //установление указанного значения пользователя
                        $cur_val = (isset($data['PROPERTY_COUNTER_REQUEST_LIMIT_VALUE']) ? intval($data['PROPERTY_COUNTER_REQUEST_LIMIT_VALUE']) : 0);
                        $new_val = $cur_val + $number;
                        if($new_val < 0)
                            $new_val = 0;
                        CIBlockElement::SetPropertyValuesEx($data['ID'], $arFilter['IBLOCK_ID'], array('COUNTER_REQUEST_LIMIT' => $new_val));

                        //запись в hl iblock
                        self::counterReqLimitChangeLog($data['PROPERTY_USER_VALUE'], $action_type, $cur_date, $created_by, $number, $cur_val, $new_val, $element_id);
                    }

                    break;

                case 'set':
                    //установление указанного значения для пользователя
                    $arFilter = array(
                        'IBLOCK_ID' => rrsIblock::getIBlockId('client_profile')
                    );
                    if(count($uids) > 0){
                        $arFilter['PROPERTY_USER'] = $uids;
                    }

                    $res = $el_obj->GetList(
                        array('ID' => 'ASC'),
                        $arFilter,
                        false,
                        false,
                        array('ID', 'PROPERTY_USER', 'PROPERTY_COUNTER_REQUEST_LIMIT')
                    );
                    while($data = $res->Fetch()){
                        //установление указанного значения пользователя
                        $cur_val = (isset($data['PROPERTY_COUNTER_REQUEST_LIMIT_VALUE']) ? intval($data['PROPERTY_COUNTER_REQUEST_LIMIT_VALUE']) : 0);
                        $new_val = $number;
                        CIBlockElement::SetPropertyValuesEx($data['ID'], $arFilter['IBLOCK_ID'], array('COUNTER_REQUEST_LIMIT' => $new_val));

                        //запись в hl iblock
                        self::counterReqLimitChangeLog($data['PROPERTY_USER_VALUE'], $action_type, $cur_date, $created_by, $number, $cur_val, $new_val, $element_id);
                    }

                    break;

                case 'use':
                    //списание принятия при использовании пользователем
                    if(filter_var($uids, FILTER_VALIDATE_INT)) {
                        $arFilter = array(
                            'IBLOCK_ID' => rrsIblock::getIBlockId('client_profile'),
                            'PROPERTY_USER' => $uids
                        );
                        $res = $el_obj->GetList(
                            array('ID' => 'ASC'),
                            $arFilter,
                            false,
                            false,
                            array('ID', 'PROPERTY_USER', 'PROPERTY_COUNTER_REQUEST_LIMIT')
                        );
                        while ($profile_data = $res->Fetch()) {
                            //установление указанного значения пользователя
                            $cur_val = (isset($profile_data['PROPERTY_COUNTER_REQUEST_LIMIT_VALUE']) ? intval($profile_data['PROPERTY_COUNTER_REQUEST_LIMIT_VALUE']) : 0);
                            $new_val = $cur_val + $number;
                            if($new_val < 0){
                                $new_val = 0;
                            }

                            CIBlockElement::SetPropertyValuesEx($profile_data['ID'], $arFilter['IBLOCK_ID'], array('COUNTER_REQUEST_LIMIT' => $new_val));

                            //запись в hl iblock
                            self::counterReqLimitChangeLog($profile_data['PROPERTY_USER_VALUE'], $action_type, $cur_date, $created_by, $number, $cur_val, $new_val, $element_id);

                            //если израсходованы все принятия, то отправляем письмо администраторам
                            if($new_val == 0){
                                $arFields = array(
                                    'URL' => $GLOBALS['host'] . '/bitrix/admin/user_edit.php?lang=ru&ID=' . $uids
                                );
                                $user_email = '';

                                //собираем данные пользователя
                                $res = CUser::GetList(
                                    ($by = 'id'), ($order = 'asc'),
                                    array('ID' => $uids),
                                    array('SELECT' => array('EMAIL', 'NAME', 'LAST_NAME', 'LOGIN'))
                                );
                                if($data = $res->Fetch()){
                                    $temp_val = '';
                                    if($data['EMAIL'] != '' && !checkEmailFromPhone($data['EMAIL'])){
                                        $temp_val = trim($data['NAME'] . ' ' . $data['LAST_NAME'] . ' ' . $data['EMAIL']);
                                        $user_email = $data['EMAIL'];
                                    }elseif($data['LOGIN'] != '' ){
                                        $temp_val = trim($data['NAME'] . ' ' . $data['LAST_NAME'] . ' ' . $data['LOGIN']);
                                    }else{
                                        $temp_val = trim($data['NAME'] . ' ' . $data['LAST_NAME']);
                                    }
                                    if($temp_val != '') {
                                        $arFields['USER_INFO'] = '<a href="' . $arFields['URL'] . '">' . $temp_val . '</a>';
                                    }else{
                                        //у пользователя нет email и нет фио -> берём телефон из профиля
                                        $arFields['USER_INFO'] = '<a href="' . $arFields['URL'] . '">' . $uids . '</a>';
                                    }
                                }

                                //отправляем данные администраторам
                                $res = CUser::GetList(
                                    ($by = 'id'), ($order = 'asc'),
                                    array(
                                        'GROUPS_ID' => 1,
                                        'ACTIVE' => 'Y'
                                    ),
                                    array('SELECT' => array('EMAIL'))
                                );
                                while($data = $res->Fetch()){
                                    $arFields['EMAIL'] = $data['EMAIL'];
                                    CEvent::Send('COUNTERREQLIMSRUNOUT', 's1', $arFields);
                                }
                                /**
                                 * отправляем уведомление
                                 * Предложение по товару "Культура" на складе "Склад" не было принято
                                 * Получатели: покупатели, связанные организаторы
                                 */
                                $message = 'Израсходованы принятия';
                                $client_id = $profile_data['PROPERTY_USER_VALUE'];
                                $client_href = '/client/profile/counter_limits_history/?cr_form=1';
                                $partner_href = '/profile/?uid='.$client_id;
                                notice::addNotice($client_id, 'd', $message, $client_href, '#' . $client_id);  //покупателю
                                $partner_ids = client::getLinkedPartnerList($client_id);
                                if((sizeof($partner_ids))&&(is_array($partner_ids))){
                                    foreach ($partner_ids as $partner_id){
                                        notice::addNotice($partner_id, 'd', $message, $partner_href, '#' . $partner_id);  //организатору
                                    }

                                }


                                //отправляем сообщение пользователю
                                if($user_email != '') {
                                    //отправляем сообщение на почту
                                    $arFields['EMAIL'] = $user_email;
                                    $arFields['URL'] = $GLOBALS['host'] . '/client/profile/counter_limits_history/';
                                    CEvent::Send('COUNTERREQLIMSRUNOUT_U', 's1', $arFields);
                                }else{
                                    //email не задан, либо создан из телефона -> отправляем сообщение на телефон
                                    $res = $el_obj->GetList(
                                        array('ID' => "ASC"),
                                        array(
                                            'IBLOCK_ID' => rrsIblock::getIBlockId('client_profile'),
                                            'PROPERTY_USER' => $uids,
                                            '!PROPERTY_PHONE' => false
                                        ),
                                        false,
                                        array('nTopCount' => 1),
                                        array('PROPERTY_PHONE')
                                    );
                                    if($data = $res->Fetch()){
                                        $phone_val = makeCorrectPhone($data['PROPERTY_PHONE_VAL']);
                                        if($phone_val != ''){
                                            notice::sendNoticeSMS(getPhoneDigits($phone_val), 'Вы исчерпали лимит принятий. Для пополения воспользуйтесь формой обратной связи на странице истории операций в ЛК.');
                                        }
                                    }
                                }
                            }
                        }
                    }

                    break;

                case 'return':
                    //восстановление одного принятия (при добавлении поставщика покупателем в чёрный список)
                    if(filter_var($uids, FILTER_VALIDATE_INT)) {
                        $arFilter = array(
                            'IBLOCK_ID' => rrsIblock::getIBlockId('client_profile'),
                            'PROPERTY_USER' => $uids
                        );
                        $res = $el_obj->GetList(
                            array('ID' => 'ASC'),
                            $arFilter,
                            false,
                            false,
                            array('ID', 'PROPERTY_USER', 'PROPERTY_COUNTER_REQUEST_LIMIT')
                        );
                        while ($data = $res->Fetch()) {
                            //установление указанного значения пользователя
                            $cur_val = (isset($data['PROPERTY_COUNTER_REQUEST_LIMIT_VALUE']) ? intval($data['PROPERTY_COUNTER_REQUEST_LIMIT_VALUE']) : 0);
                            $new_val = $cur_val + $number;
                            if ($new_val < 0) {
                                $new_val = 0;
                            }

                            CIBlockElement::SetPropertyValuesEx($data['ID'], $arFilter['IBLOCK_ID'], array('COUNTER_REQUEST_LIMIT' => $new_val));

                            //запись в hl iblock
                            self::counterReqLimitChangeLog($data['PROPERTY_USER_VALUE'], $action_type, $cur_date, $created_by, $number, $cur_val, $new_val, $element_id);
                        }
                    }

                    break;
                case 'bl_del':
                    //снятие одного принятия при исключения поставщика их черного списка покупателя
                    if(filter_var($uids, FILTER_VALIDATE_INT)) {
                        $arFilter = array(
                            'IBLOCK_ID' => rrsIblock::getIBlockId('client_profile'),
                            'PROPERTY_USER' => $uids
                        );
                        $res = $el_obj->GetList(
                            array('ID' => 'ASC'),
                            $arFilter,
                            false,
                            false,
                            array('ID', 'PROPERTY_USER', 'PROPERTY_COUNTER_REQUEST_LIMIT')
                        );
                        while ($data = $res->Fetch()) {
                            //установление указанного значения пользователя
                            $cur_val = (isset($data['PROPERTY_COUNTER_REQUEST_LIMIT_VALUE']) ? intval($data['PROPERTY_COUNTER_REQUEST_LIMIT_VALUE']) : 0);
                            $new_val = $cur_val + $number;
                            if ($new_val < 0) {
                                $new_val = 0;
                            }

                            CIBlockElement::SetPropertyValuesEx($data['ID'], $arFilter['IBLOCK_ID'], array('COUNTER_REQUEST_LIMIT' => $new_val));

                            //запись в hl iblock
                            self::counterReqLimitChangeLog($data['PROPERTY_USER_VALUE'], $action_type, $cur_date, $created_by, $number, $cur_val, $new_val, $element_id);
                        }
                    }

                    break;
            }
        }
    }

    /*
     * Изменение ограничения запросов в профиле покупателей
     *
     * @param string $action_type - код типа действия
     * @param int $number - количество принятий (может быть отрицательным числом)
     * @param array $uids - массив id пользователей (может быть пустым)
     * @param array $element_id - ID записи действия админа (если есть)
     * @param string $comment_text - текст комментария
     * */
    public static function requestLimitQuantityChange($action_type, $number, $uids, $element_id = 0, $comment_text = ''){
        if(filter_var($number, FILTER_VALIDATE_INT) !== false) {
            //общие данные для лога в highload инфоблоке
            global $USER;
            $cur_date = ConvertTimeStamp(false, 'FULL');
            $created_by = $USER->GetID();

            CModule::IncludeModule('iblock');
            $el_obj = new CIBlockElement;

            //вносим изменения в зависимости от типа действия
            switch ($action_type) {
                case 'change':
                    //изменение значения (+/- к текущему значению)
                    $arFilter = array(
                        'IBLOCK_ID' => rrsIblock::getIBlockId('client_profile')
                    );
                    if(count($uids) > 0){
                        $arFilter['PROPERTY_USER'] = $uids;
                    }

                    $res = $el_obj->GetList(
                        array('ID' => 'ASC'),
                        $arFilter,
                        false,
                        false,
                        array('ID', 'PROPERTY_USER', 'PROPERTY_REQUEST_LIMIT')
                    );
                    while($data = $res->Fetch()){
                        //установление указанного значения пользователя
                        $cur_val = (isset($data['PROPERTY_REQUEST_LIMIT_VALUE']) ? intval($data['PROPERTY_REQUEST_LIMIT_VALUE']) : 0);
                        $new_val = $cur_val + $number;
                        if($new_val < 0)
                            $new_val = 0;
                        CIBlockElement::SetPropertyValuesEx($data['ID'], $arFilter['IBLOCK_ID'], array('REQUEST_LIMIT' => $new_val));

                        //запись в hl iblock
                        self::requestLimitChangeLog($data['PROPERTY_USER_VALUE'], $action_type, $cur_date, $created_by, $number, $cur_val, $new_val, $element_id);
                    }

                    //отправка уведомления покупателям на email (только активным пользователям с почтами не из телефона)
                    $arFilter = array(
                        'ACTIVE' => 'Y',
                        'GROUPS_ID' => 9
                    );
                    if(count($uids) > 0){
                        $arFilter['ID'] = implode('|', $uids);
                    }
                    $mailFields = array(
                        'URL' => $GLOBALS['host'] . '/client/profile/counter_limits_history/?data_type=2',
                        'TEXT' => $comment_text
                    );

                    $res = CUser::GetList(
                        ($by = 'id'), ($order = 'desc'),
                        $arFilter,
                        array('SELECT' => array('ID', 'EMAIL'))
                    );
                    while($data = $res->Fetch()){
                        //если телефон создан не из почты, то отправляем email
                        $mailFields['EMAIL'] = $data['EMAIL'];
                        if(!checkEmailFromPhone($data['EMAIL'])){
                            CEvent::Send('REQUESTLIMITCHANGEUSER', 's1', $mailFields);
                        }
                    }

                    //получаем данные пользователей, у которых появились "лишние" запросы после изменения ограничения
                    self::checkRequestOverLimitAfterUpdate($uids);

                    break;

                case 'set':
                    //установление указанного значения для пользователя
                    $arFilter = array(
                        'IBLOCK_ID' => rrsIblock::getIBlockId('client_profile')
                    );
                    if(count($uids) > 0){
                        $arFilter['PROPERTY_USER'] = $uids;
                    }

                    $res = $el_obj->GetList(
                        array('ID' => 'ASC'),
                        $arFilter,
                        false,
                        false,
                        array('ID', 'PROPERTY_USER', 'PROPERTY_REQUEST_LIMIT')
                    );
                    while($data = $res->Fetch()){
                        //установление указанного значения пользователя
                        $cur_val = (isset($data['PROPERTY_REQUEST_LIMIT_VALUE']) ? intval($data['PROPERTY_REQUEST_LIMIT_VALUE']) : 0);
                        $new_val = $number;
                        CIBlockElement::SetPropertyValuesEx($data['ID'], $arFilter['IBLOCK_ID'], array('REQUEST_LIMIT' => $new_val));

                        //запись в hl iblock
                        self::requestLimitChangeLog($data['PROPERTY_USER_VALUE'], $action_type, $cur_date, $created_by, $number, $cur_val, $new_val, $element_id);
                    }

                    //отправка уведомления покупателям на email (только активным пользователям с почтами не из телефона)
                    $arFilter = array(
                        'ACTIVE' => 'Y',
                        'GROUPS_ID' => 9
                    );
                    if(count($uids) > 0){
                        $arFilter['ID'] = implode('|', $uids);
                    }
                    $mailFields = array(
                        'URL' => $GLOBALS['host'] . '/client/profile/counter_limits_history/?data_type=2',
                        'TEXT' => $comment_text
                    );

                    $res = CUser::GetList(
                        ($by = 'id'), ($order = 'desc'),
                        $arFilter,
                        array('SELECT' => array('ID', 'EMAIL'))
                    );
                    while($data = $res->Fetch()){
                        //если телефон создан не из почты, то отправляем email
                        $mailFields['EMAIL'] = $data['EMAIL'];
                        if(!checkEmailFromPhone($data['EMAIL'])){
                            CEvent::Send('REQUESTLIMITCHANGEUSER', 's1', $mailFields);
                        }
                    }

                    //получаем данные пользователей, у которых появились "лишние" запросы после изменения ограничения
                    self::checkRequestOverLimitAfterUpdate($uids);

                    break;
            }
        }
    }

    /*
     * Запись события в лог счётчика принятий
     * @param int $uid - ID пользователя
     * @param string $action - код типа действия
     * @param string $date - дата создания записи
     * @param int $created_by - ID пользователя, создавшего запись
     * @param int $num_work - величина изменения принятий
     * @param int $num_before - значение принятий до изменения
     * @param int $num_after - значение принятий после изменения
     * @param int $elem_id - ID связанного элемента в ИБ "Лог для счетчика принятий ВП покупателя" (counter_request_limits_changes), необязательный параметр
     *
     * @return boolean - флаг успешности записи в лог
     * */
    public static function counterReqLimitChangeLog($uid, $action, $date, $created_by, $num_work, $num_before, $num_after, $elem_id = 0){
        $result = false;
        //проверка обязательных параметров
        if(is_numeric($uid)
            && $uid > 0
            && trim($action) != ''
            && trim($date) != ''
            && is_numeric($created_by)
            && $created_by > 0
            && is_numeric($num_work)
            && is_numeric($num_before)
            && is_numeric($num_after)
            && is_numeric($elem_id)
        ){
            CModule::IncludeModule('highloadblock');
            $hlblock = \Bitrix\Highloadblock\HighloadBlockTable::getById(rrsIblock::HLgetIBlockId('CONTREQLIMITSLOG'))->fetch();
            $entity = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlblock);
            $entityDataClass = $entity->getDataClass();
            $arFields = array(
                'UF_USER_ID' => $uid,
                'UF_ACTION' => $action,
                'UF_DATE' => $date,
                'UF_CREATED_BY' => $created_by,
                'UF_NUMBER' => $num_work,
                'UF_BEFORE' => $num_before,
                'UF_AFTER' => $num_after,
                'UF_ELEMENT_ID' => $elem_id,
                'UF_ENTITY_TYPE' => 'client_counter_request'
            );
            $el = new $entityDataClass;
            $res = $el->add($arFields);
            if($res->isSuccess()){
                $result = true;
            }else{
                //p($res->getErrorMessages());
            }
        }

        return $result;
    }

    /**
     * Проверка сохранения фильтра на странице запросов покупателя (для организатора)
     *
     * @return array массив, где у ключа 'NEED_UPD' значение либо true либо false,
     * у ключа 'URL_UPD' значение адреса переадресации
     */
    public static function filterPartnerClientWHCheck() {
        $result = array(
            'NEED_UPD'  => false,
            'URL_UPD'   => ''
        );
        if(isset($_GET['id'])
            && $_GET['id'] > 0){
            return $result;
        }

        if(isset($_GET['client_id'][0])&&!empty($_GET['client_id'][0])){
            return $result;
        }

        $tabFilterSuf = '';
        $page_need_update = false;
        $new_url_params = array();

        switch($_REQUEST['status']){
            case 'yes':
                $tabFilterSuf = 'yes';
                break;
            case 'no':
                $tabFilterSuf = 'no';
                $new_url_params[] = 'status=no';
                break;
            default:
                $tabFilterSuf = 'all';
                $new_url_params[] = 'status=all';
        }

        //проверка куки склада
        $cookie_name = 'partner_client_wh_' . $tabFilterSuf . '_client_id';
        if(isset($_COOKIE[$cookie_name])){
            $cookie_val = $_COOKIE[$cookie_name];
            if((!isset($_GET['farmer_id'][0]) || $_GET['farmer_id'][0] == '' || $_GET['farmer_id'][0] == '0')
                && $cookie_val != 0 && $cookie_val != ''
            ){
                $result['NEED_UPD'] = true;
                $new_url_params[] = 'client_id[]=' . $cookie_val;
            }
        }
        if($result['NEED_UPD']){
            $result['URL_UPD'] = '/partner/client_warehouses/' . (count($new_url_params) > 0 ? '?' . implode('&', $new_url_params) : '');
        }
        return $result;
    }

    /*
     * Запись события в лог ограничения запросов (отдельная функция на случай разделения хранения истории)
     * @param int $uid - ID пользователя
     * @param string $action - код типа действия
     * @param string $date - дата создания записи
     * @param int $created_by - ID пользователя, создавшего запись
     * @param int $num_work - величина изменения ограничения
     * @param int $num_before - значение ограничений до изменения
     * @param int $num_after - значение ограничений после изменения
     * @param int $elem_id - ID связанного элемента в ИБ "Ограничение количества запросов" (client_request_limits_changes), необязательный параметр
     *
     * @return boolean - флаг успешности записи в лог
     * */
    public static function requestLimitChangeLog($uid, $action, $date, $created_by, $num_work, $num_before, $num_after, $elem_id = 0){
        $result = false;
        //проверка обязательных параметров
        if(is_numeric($uid)
            && $uid > 0
            && trim($action) != ''
            && trim($date) != ''
            && is_numeric($created_by)
            && $created_by > 0
            && is_numeric($num_work)
            && is_numeric($num_before)
            && is_numeric($num_after)
            && is_numeric($elem_id)
        ){
            CModule::IncludeModule('highloadblock');
            $hlblock = \Bitrix\Highloadblock\HighloadBlockTable::getById(rrsIblock::HLgetIBlockId('CONTREQLIMITSLOG'))->fetch();
            $entity = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlblock);
            $entityDataClass = $entity->getDataClass();
            $arFields = array(
                'UF_USER_ID' => $uid,
                'UF_ACTION' => $action,
                'UF_DATE' => $date,
                'UF_CREATED_BY' => $created_by,
                'UF_NUMBER' => $num_work,
                'UF_BEFORE' => $num_before +  intval(rrsIblock::getConst('min_request_limit')),
                'UF_AFTER' => $num_after +  intval(rrsIblock::getConst('min_request_limit')),
                'UF_ELEMENT_ID' => $elem_id,
                'UF_ENTITY_TYPE' => 'client_request_limit'
            );
            $el = new $entityDataClass;
            $res = $el->add($arFields);
            if($res->isSuccess()){
                $result = true;
            }else{
                //p($res->getErrorMessages());
            }
        }

        return $result;
    }

    /*
     * Возвращает текст для истории принятий
     * @param string $action_code - код действия
     * @param int $number - величина изменения (отрицательная при уменьшении)
     *
     * @return string - текст
     * */
    public static function counterRequestOpenerDefaultText($action_code, $number){
        $result = '-';

        switch($action_code){
            case 'change':
                //для изменения значения (+/-)
                if($number > 0){
                    $result = 'Начисление принятий (' . $number . ')';
                }else{
                    $result = 'Списание принятий (' . $number . ')';
                }
                break;

            case 'set':
                //для установления значения
                $result = 'Установление принятий (' . $number . ')';
                break;

            case 'use':
                //для использования пользователем принятия
                $result = 'Использование принятия (' . $number . ')';
                break;

            case 'return':
                //для возврата принятия при добавлении покупаиелем поставщика в ЧС
                $result = 'Возврат за добавление в ЧС (' . $number . ')';
                break;

            case 'bl_del':
                //для возврата принятия при добавлении покупаиелем поставщика в ЧС
                $result = 'Списание за удаление из ЧС (' . $number . ')';
                break;
        }

        return $result;
    }

    /* @param int $num_after - значение принятий после изменения
     * @param int $elem_id - ID связанного элемента в ИБ "Лог для счетчика принятий ВП покупателя" (counter_request_limits_changes), необязательный параметр
     *
     * @return boolean - флаг успешности записи в лог
     * */
    public static function requestLimitDefaultText($action_code, $number){
        $result = '-';

        switch($action_code){
            case 'change':
                //для изменения значения (+/-)
                if($number > 0){
                    $result = 'Увеличение лимита запросов (' . $number . ')';
                }else{
                    $result = 'Уменьшение лимита запросов (' . $number . ')';
                }
                break;

            case 'set':
                //для установления значения
                $result = 'Установление лимита запросов (' . $number . ')';
                break;
        }

        return $result;
    }

    /*
     * расчёт цены для хранения в инфоблоке deals_deals (обратный расчёт)
     * */
    public static function pairPriceCalculation($data, $get_parity = false, $get_csm_for_client_nds = false){
        $result = array();

        $result['WH_ID'] = $data['CLIENT_WH_ID'];
        $result['CENTER'] = $data['CENTER'];
        $result['ROUTE'] = $data['ROUTE'];
        $result['TYPE'] = $data['TYPE'];

        $nds = rrsIblock::getConst('nds');
        $commissionVal = rrsIblock::getConst('commission');
//        $trCommissionVal = rrsIblock::getConst('commission_transport');
        $ndsVal = 0;

        //получаем Расчетную Цену (РЦ)
        //тариф всегда берем как fca, расчет ведем как dap
        $tarif = client::getTarif($data['CLIENT_ID'], $data['CULTURE_GROUP_ID'], 'fca', $data['CENTER'], $data['ROUTE'], $data['TARIFF_LIST']);
        $result['TARIF'] = $tarif;

        $base_price_data = lead::makeBaseFromCSM($data['RCSM'], $data['CLIENT_NDS'] == 'yes', $data['FARMER_NDS'] == 'yes', $data['DUMP'], $tarif, array('get_base_client' => true, 'nds' => $nds, 'comissionVal' => $commissionVal), $get_csm_for_client_nds);

        //получаем Паритетную Цену
        //dap (считаем всегда как dap)
        if($get_parity) {
            $pc = (100 * $base_price_data['BASE_PRICE']) / (100 - $commissionVal);
            if($data['CLIENT_NDS'] == 'no'){
                $ndsVal = $pc * 0.01 * $nds;
                $pc = $pc + $ndsVal;
            }
            $result['PARITY_PRICE'] = round($pc, 0);
        }

        //Заполняем старые данные
        $result['NDS_RUB'] = round($ndsVal, 2);
        $result['BASE_PRICE'] = round($base_price_data['BASE_PRICE'], 2);
        $result['ACC_PRICE'] = round($base_price_data['RC_PRICE'], 2);
        if(isset($base_price_data['DUMP_RUB'])){
            $result['DUMP_RUB'] = $base_price_data['DUMP_RUB'];
        }

        if(isset($base_price_data['CSM_FOR_CLIENT'])){
            $result['CSM_FOR_CLIENT'] = $base_price_data['CSM_FOR_CLIENT'];
        }

        return $result;
    }

    /*
     * получает данные для построения графиков во встречных предложениях покупателя (за последние 32 дня)
     * @param array $filter - фильтрация для получения данных (по культуре, складу, пользователю)
     * цены запросов дублируются каждый день (для графика)
     * @param boolean $user_nds_type - признак того, что пользователь работает с ндс (необязательный, по умолчанию - без ндс)
     *
     * @return array - массив данных для построения графика
     */
    public static function getCounterRequestsGraphsDataAll($filter, $user_nds_type = false){
        $hl_myr_id = rrsIblock::HLgetIBlockId('CONTREQMYPRICES');
        $hl_market_id = rrsIblock::HLgetIBlockId('CONTREQMARKET');
        $hl_myd_id = rrsIblock::HLgetIBlockId('CONTREQMYDEALS');
        $hl_av_price_id = rrsIblock::HLgetIBlockId('REQUESTAVERAGEPRICES');

        $result = array(
            'USER_REQUESTS' => array(),
            'MARKET_DATA' => array(),
            'USER_DEALS' => array(),
            'AVERAGE_PRICES' => array()
        );
        $get_date = ConvertTimeStamp(strtotime('-1 year'), 'SHORT', 's1');

        //отображаем графики только для конркетного пользователя
        if(!isset($filter['UF_USER_ID'])
            || $filter['UF_USER_ID'] == 0
        ){
            return $result;
        }

        if(isset($filter['UF_CENTER'])
            && is_numeric($filter['UF_CENTER'])
        ){
            //получение данных для графика "Мои цены"
            $arFilter = array();
            if(isset($filter['UF_USER_ID'])
                && is_numeric($filter['UF_USER_ID'])
            ){
                $arFilter = $filter;
            }
            $arFilter['>UF_DATE'] = $get_date;
            $arFilter['>UF_PRICE'] = 0;
            $logObj = new log;
            $entityDataClass = $logObj->getEntityDataClass($hl_myr_id);
            $el = new $entityDataClass;
            $rsData = $el->getList(array(
                'select' => array('UF_REQUEST', 'UF_DATE', 'UF_PRICE', 'UF_CENTER', 'UF_USER_ID'),
                'filter' => $arFilter,
                'order'  => array('UF_DATE' => 'ASC', 'ID' => 'ASC')
            ));
            $requests = array();
            $REQ_DATA = array();
            while($res = $rsData->fetch()){
                $temp_data = explode(' ', $res['UF_DATE']->toString());
                $requests[$res['UF_REQUEST']] = 1;
                $REQ_DATA[$res['UF_REQUEST']][$temp_data[0]] = array(
                    'UF_DATE' => $temp_data[0],
                    'UF_PRICE' => $res['UF_PRICE'],
                    'UF_CENTER' => $res['UF_CENTER'],
                    'UF_REQUEST' => $res['UF_REQUEST']
                );
            }
            $reqData = array();
            if((sizeof($requests))&&(is_array($requests))){
                $req_array = array();
                foreach ($requests as $reqId=>$v){
                    $req_array[] = $reqId;
                }
                $reqData = self::getRequestsActions($req_array);
            }
            /*
             * формируем массив по всем датам, начиная с даты создания запроса и повторяя актуальные цены
             * в последующие даты
             */
            $cTime = strtotime(date('d.m.Y'));
            foreach ($REQ_DATA as $reqId => $reqArr){
                if(array_key_exists($reqId,$reqData)){
                    $cTime = strtotime($reqData[$reqId]['TIMESTAMP_X']);
                }
                foreach ($reqArr as $date=>$opt){
                    $itemTime = strtotime($date)+86400;
                    while($itemTime<=$cTime){
                        if(!isset($REQ_DATA[$reqId][date('d.m.Y',$itemTime)])){
                            $opt['UF_DATE'] = date('d.m.Y',$itemTime);
                            $REQ_DATA[$reqId][date('d.m.Y',$itemTime)] = $opt;
                        }else{
                           break;
                        }
                        $itemTime+=86400;
                    }
                }
            }

            $NEW_REQUESTS = array();
            $minDate = strtotime(date('d.m.Y'));
            $maxDate = strtotime(date('d.m.Y'));
            foreach ($REQ_DATA as $reqId => $reqArr){
                foreach ($reqArr as $date=>$opt){
                    if(strtotime($opt['UF_DATE'])>$maxDate){
                        $maxDate = strtotime($opt['UF_DATE']);
                    }
                    if(strtotime($opt['UF_DATE'])<$minDate){
                        $minDate = strtotime($opt['UF_DATE']);
                    }
                    $NEW_REQUESTS[] = $opt;
                }
            }

            $result['REQ_MIN_DATE'] = date('d.m.Y',$minDate);
            $result['REQ_MAX_DATE'] = date('d.m.Y',$maxDate);

            $result['USER_REQUESTS'] = $NEW_REQUESTS;
            //получение данных для графика "Рынок"
            $arFilter = array();
            $arFilter['>UF_BASE_PRICE'] = 0;
            $arFilter['>UF_DATE'] = $get_date;
            if(isset($filter['UF_CENTER'])
                && is_numeric($filter['UF_CENTER'])
            ){
                $arFilter['UF_CENTER'] = $filter['UF_CENTER'];
            }
            if(isset($filter['UF_CULTURE'])
                && is_numeric($filter['UF_CULTURE'])
            ){
                $arFilter['UF_CULTURE'] = $filter['UF_CULTURE'];
            }
            $logObj = new log;
            $entityDataClass = $logObj->getEntityDataClass($hl_market_id);
            $el = new $entityDataClass;
            $rsData = $el->getList(array(
                'select' => array('UF_DATE', 'UF_BASE_PRICE_NDS', 'UF_BASE_PRICE', 'UF_CENTER'),
                'filter' => $arFilter,
                'order'  => array('UF_DATE' => 'ASC', 'ID' => 'ASC')
            ));
            while($res = $rsData->fetch()){
                $temp_data = explode(' ', $res['UF_DATE']->toString());
                $result['MARKET_DATA'][] = array(
                    'UF_DATE' => $temp_data[0],
                    'UF_BASE_PRICE' => ($user_nds_type ? $res['UF_BASE_PRICE_NDS'] : $res['UF_BASE_PRICE']),
                    'UF_CENTER' => $res['UF_CENTER'],
                );
            }

            //получение данных для графика "Мои сделки"
            $arFilter = array();
            $arFilter['>UF_DATE'] = $get_date;
            $arFilter['>UF_BASE_PRICE'] = 0;
            if(isset($filter['UF_USER_ID'])
                && is_numeric($filter['UF_USER_ID'])
            ){
                $arFilter = $filter;
                $arFilter['>UF_DATE'] = $get_date;
            }
            if(isset($filter['UF_CENTER'])
                && is_numeric($filter['UF_CENTER'])
            ){
                $arFilter['UF_CENTER'] = $filter['UF_CENTER'];
            }
            if(isset($filter['UF_CULTURE'])
                && is_numeric($filter['UF_CULTURE'])
            ){
                $arFilter['UF_CULTURE'] = $filter['UF_CULTURE'];
            }
            $logObj = new log;
            $entityDataClass = $logObj->getEntityDataClass($hl_myd_id);
            $el = new $entityDataClass;
            unset($arFilter['UF_WAREHOUSE']);
            $rsData = $el->getList(array(
                'select' => array('UF_DATE', 'UF_BASE_PRICE', 'UF_BASE_CONTR_PRICE', 'UF_CENTER'),
                'filter' => $arFilter,
                'order'  => array('UF_DATE' => 'ASC', 'ID' => 'ASC')
            ));
            while($res = $rsData->fetch()){
                $temp_data = explode(' ', $res['UF_DATE']->toString());
                $result['USER_DEALS'][] = array(
                    'UF_DATE' => $temp_data[0],
                    'UF_BASE_PRICE' => ($user_nds_type ? $res['UF_BASE_CONTR_PRICE'] : $res['UF_BASE_PRICE']),
                    'UF_CENTER' => $res['UF_CENTER'],
                );
            }

            //получение данных для графика "Спрос"
            $wh_reg = 0;
            if(isset($filter['UF_WAREHOUSE'])&&$filter['UF_WAREHOUSE']){
                //получаем регион выбранного склада
                $wh_reg = client::getRegionsByWhs(array($filter['UF_WAREHOUSE']));
                if(isset($wh_reg[$filter['UF_WAREHOUSE']])){
                    $wh_reg = $wh_reg[$filter['UF_WAREHOUSE']];
                }else{
                    $wh_reg = 0;
                }
            }
            if((isset($filter['UF_CULTURE'])
                && is_numeric($filter['UF_CULTURE']))&&(!empty($wh_reg))){
                $arFilter = array();
                $arFilter['>UF_DATE'] = $get_date;
                $arFilter['UF_REGION_ID'] = $wh_reg;
                $arFilter['UF_CULTURE_ID'] = $filter['UF_CULTURE'];
                $logObj = new log;
                $entityDataClass = $logObj->getEntityDataClass($hl_av_price_id);
                $el = new $entityDataClass;
                $rsData = $el->getList(array(
                    'select' => array('UF_DATE', 'UF_PRICE'),
                    'filter' => $arFilter,
                    'order'  => array('UF_DATE' => 'ASC', 'ID' => 'ASC')
                ));
                while($res = $rsData->fetch()){
                    $temp_data = explode(' ', $res['UF_DATE']->toString());
                    $result['AVERAGE_PRICES'][] = array(
                        'UF_DATE' => $temp_data[0],
                        'UF_PRICE' => $res['UF_PRICE'],
                    );
                }
            }


        }
        return $result;
    }

    /*
     * Выводит данные для построения графиков во встречных предложениях покупателя (в компоненте и для ajax)
     * @param array $data - массив с данными для графика
     * @param float $nds_val - значение "константы" nds
     * @param boolean $user_nds_type - флаг типа ндс получаемых данных
     * @param int $user_id - ID пользователя
     * @param boolean $is_ajax - флаг типа получения данных (в аякс запросе выводятся не все данные)
     *
     * @return array - массив данных для построения графика
     */
    public static function showCounterRequestsGraphsDataAll($data, $nds_val, $user_nds_type, $user_id, $is_ajax = false){
        $year_ymd = date('Y.m.d', strtotime('-1 year'));
        $month_ymd = date('Y.m.d', strtotime('-1 month'));
        $week_ymd = date('Y.m.d', strtotime('-1 week'));
        $cat_data = array(); //минимальная и максимальная даты (для отметок под графиком)

        $user_profile = client::getProfile($user_id);
        $user_nds = ($user_profile['PROPERTY_NDS_CODE'] == 'Y'); // тип НДС пользователя, к которому относятся получаемые данные
        unset($user_profile);

        if(!$is_ajax) {
            echo '<div id="date_restrictions" data-year="' . $year_ymd . '" data-month="' . $month_ymd . '" data-week="' . $week_ymd . '"></div>';
        }

        //данные графика "Мои цены"
        if (count($data['USER_REQUESTS']) > 0) {
            ?>
            <div id="graph_my_req<?=($user_nds_type ? '_nds' : '_no_nds');?>" data-nds="<?=($nds_val ? 'y' : 'n');?>" data-val="<?
            $my_c = 0;
            foreach($data['USER_REQUESTS'] as $cur_pos => $cur_data){
                if ($my_c > 0){
                    echo ';';
                }
                $tmp_price = $cur_data['UF_PRICE'];
                if($user_nds && !$user_nds_type){
                    //вычитаем НДС из цены
                    $tmp_price = $tmp_price / (1 + 0.01 * $nds_val);
                }elseif(!$user_nds && $user_nds_type){
                    //добавляем НДС в цену
                    $tmp_price = $tmp_price + $tmp_price * 0.01 * $nds_val;
                }

                echo $cur_data['UF_DATE'] . ',' . $tmp_price . ',' . $cur_data['UF_REQUEST'];
                $my_c++;
            }
            $cat_data['0'] = $data['REQ_MIN_DATE'];
            $cat_data['1'] = $data['REQ_MAX_DATE'];


            ?>"></div>
            <?
        }
        //данные графика "Рынок"
        if (count($data['MARKET_DATA']) > 1) {
            ?>
            <div id="graph_my_market<?=($user_nds_type ? '_nds' : '_no_nds');?>" data-val="<?
            $my_c = 0;
            foreach ($data['MARKET_DATA'] as $cur_pos => $cur_data) {
                if ($my_c > 0) {
                    echo ';';
                }

                echo $cur_data['UF_DATE'] . ',' . $cur_data['UF_BASE_PRICE'];
                $my_c++;
                //проверяем нет ли новых минимальных и максимальных дат среди значений
                if ($cur_pos == 0) {
                    if (isset($cat_data['0'])) {
                        $temp_cur = explode('.', $cat_data['0']);
                        $temp_new = explode('.', $cur_data['UF_DATE']);

                        if (count($temp_cur) == 3
                            && count($temp_new) == 3
                            && intval($temp_cur[2] . $temp_cur[1] . $temp_cur[0]) > intval($temp_new[2] . $temp_new[1] . $temp_new[0])
                        ) {
                            //найдена меньшая дата чем $cat_data['0']
                            $cat_data['0'] = $cur_data['UF_DATE'];
                        }
                    } else {
                        $cat_data['0'] = $cur_data['UF_DATE'];
                    }
                } elseif ($cur_pos == count($data['MARKET_DATA']) - 1) {
                    if (isset($cat_data[1])) {
                        $temp_cur = explode('.', $cat_data['1']);
                        $temp_new = explode('.', $cur_data['UF_DATE']);
                        if (count($temp_cur) == 3
                            && count($temp_new) == 3
                            && intval($temp_cur[2] . $temp_cur[1] . $temp_cur[0]) < intval($temp_new[2] . $temp_new[1] . $temp_new[0])
                        ) {
                            //найдена большая дата чем $cat_data['1']
                            $cat_data['1'] = $cur_data['UF_DATE'];
                        }
                    } else {
                        $cat_data['1'] = $cur_data['UF_DATE'];
                    }
                }
            }
            ?>"></div>
            <?
        }
        //данные графика "Мои сделки"
        if (count($data['USER_DEALS']) > 0) {
            ?>
            <div id="graph_my_deals<?=($user_nds_type ? '_nds' : '_no_nds');?>" data-val="<?
            $my_c = 0;
            foreach ($data['USER_DEALS'] as $cur_pos => $cur_data) {
                if ($my_c > 0) {
                    echo ';';
                }
                $tmp_price = $cur_data['UF_BASE_PRICE'];
                if($user_nds && !$user_nds_type){
                    //вычитаем НДС из цены
                    $tmp_price = $tmp_price / (1 + 0.01 * $nds_val);
                }elseif(!$user_nds && $user_nds_type){
                    //добавляем НДС в цену
                    $tmp_price = $tmp_price + $tmp_price * 0.01 * $nds_val;
                }

                echo $cur_data['UF_DATE'] . ',' . $tmp_price;
                $my_c++;
                //проверяем нет ли новых минимальных и максимальных дат среди значений
                if ($cur_pos == 0) {
                    if (isset($cat_data['0'])) {
                        $temp_cur = explode('.', $cat_data['0']);
                        $temp_new = explode('.', $cur_data['UF_DATE']);
                        if (count($temp_cur) == 3
                            && count($temp_new) == 3
                            && intval($temp_cur[2] . $temp_cur[1] . $temp_cur[0]) > intval($temp_new[2] . $temp_new[1] . $temp_new[0])
                        ) {
                            //найдена меньшая дата чем $cat_data['0']
                            $cat_data['0'] = $cur_data['UF_DATE'];
                        }
                    } else {
                        $cat_data['0'] = $cur_data['UF_DATE'];
                    }
                } elseif ($cur_pos == count($data['USER_DEALS']) - 1) {
                    if (isset($cat_data[1])) {
                        $temp_cur = explode('.', $cat_data['1']);
                        $temp_new = explode('.', $cur_data['UF_DATE']);
                        if (count($temp_cur) == 3
                            && count($temp_new) == 3
                            && intval($temp_cur[2] . $temp_cur[1] . $temp_cur[0]) < intval($temp_new[2] . $temp_new[1] . $temp_new[0])
                        ) {
                            //найдена большая дата чем $cat_data['1']
                            $cat_data['1'] = $cur_data['UF_DATE'];
                        }
                    } else {
                        $cat_data['1'] = $cur_data['UF_DATE'];
                    }
                }
            }
            ?>"></div>
            <?
        }
        //данные графика "Спрос"
        if (count($data['AVERAGE_PRICES']) > 0) {
            ?>
            <div id="graph_average_price<?=($user_nds_type ? '_nds' : '_no_nds');?>" data-val="<?
            $my_c = 0;
            foreach ($data['AVERAGE_PRICES'] as $cur_pos => $cur_data) {
                $tmp_price = $cur_data['UF_PRICE'];
                //учитываем НДС пользователя
                if($user_nds_type){
                    //добавляем НДС к значению
                    $tmp_price = $tmp_price + $tmp_price * 0.01 * $nds_val;
                }

                if ($my_c > 0) {
                    echo ';';
                }

                echo $cur_data['UF_DATE'] . ',' . round($tmp_price);
                $my_c++;
                //проверяем нет ли новых минимальных и максимальных дат среди значений
                if ($cur_pos == 0) {
                    if (isset($cat_data['0'])) {
                        $temp_cur = explode('.', $cat_data['0']);
                        $temp_new = explode('.', $cur_data['UF_DATE']);
                        if (count($temp_cur) == 3
                            && count($temp_new) == 3
                            && intval($temp_cur[2] . $temp_cur[1] . $temp_cur[0]) > intval($temp_new[2] . $temp_new[1] . $temp_new[0])
                        ) {
                            //найдена меньшая дата чем $cat_data['0']
                            $cat_data['0'] = $cur_data['UF_DATE'];
                        }
                    } else {
                        $cat_data['0'] = $cur_data['UF_DATE'];
                    }
                } elseif ($cur_pos == count($data['AVERAGE_PRICES']) - 1) {
                    if (isset($cat_data[1])) {
                        $temp_cur = explode('.', $cat_data['1']);
                        $temp_new = explode('.', $cur_data['UF_DATE']);
                        if (count($temp_cur) == 3
                            && count($temp_new) == 3
                            && intval($temp_cur[2] . $temp_cur[1] . $temp_cur[0]) < intval($temp_new[2] . $temp_new[1] . $temp_new[0])
                        ) {
                            //найдена большая дата чем $cat_data['1']
                            $cat_data['1'] = $cur_data['UF_DATE'];
                        }
                    } else {
                        $cat_data['1'] = $cur_data['UF_DATE'];
                    }
                }
            }
            ?>"></div>
            <?
        }
        if (count($cat_data) == 2) {
            $temp_from = explode('.', $cat_data['0']);
            $temp_to = explode('.', $cat_data['1']);
            $time_from = mktime(5, 0, 0, $temp_from[1], $temp_from[0], $temp_from[2]);
            $time_to = mktime(5, 1, 0, $temp_to[1], $temp_to[0], $temp_to[2]);
            $days_val = ceil(($time_to - $time_from) / 86400) + 1; // + 1 для отображения на графике следующей даты
            ?>
            <div id="graph_my_categories<?=($user_nds_type ? '_nds' : '_no_nds');?>" data-check="<?= $cat_data['0']; ?>" data-check2="<?= $cat_data['1']; ?>"
                 data-days="<?= $days_val; ?>" data-val="<?
            $my_c = 0;
            for ($i = 0; $i < $days_val; $i++) {
                if ($my_c > 0) {
                    echo ';';
                }

                echo date('d.m.Y', $time_from + $i * 86400);
                $my_c++;
            }
            ?>"></div>
            <?
        }
    }



    /**
     * Получение регионов, которые встречаются в ВП клиентов
     * @param $client_id - ID клиента (клиентов)
     * @return array
     */
    static function getClientRegionsByCOUNTEROFFERS($client_id){
        $result = array();

        $arClRegions = array();
        //получаем склады клиентов которые есть во встречных предложениях
        $arFilter = array();
        $arFilter['UF_CLIENT_ID'] = $client_id;
        $client_whs = array();
        $data_arr = log::_getEntitiesList(log::getIdByName('COUNTEROFFERS'), $arFilter);
        $allCount = 0;
        foreach ($data_arr as $cur_data){
            $client_whs[$cur_data['UF_CLIENT_WH_ID']][] = $cur_data['UF_CLIENT_WH_ID'];
            $allCount++;
        }
        foreach ($client_whs as $k=>$wh){
            $client_whs[$k] = count($wh);
        }
        //получаем все регионы
        $arRegions = array();
        $res = CIBlockElement::GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('regions'),
                'ACTIVE' => 'Y',
            ),
            false,
            false,
            array('ID', 'NAME')
        );
        while ($ob = $res->Fetch()) {
            $arRegions[$ob['ID']] = $ob;
        }
        //получаем регионы из складов
        $res = CIBlockElement::GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('client_warehouse'),
                'ACTIVE' => 'Y',
                'ID' => array_keys($client_whs),
            ),
            false,
            false,
            array('ID','PROPERTY_REGION')
        );
        while ($ob = $res->Fetch()) {
            if(array_key_exists($ob['PROPERTY_REGION_VALUE'],$arRegions)){
                $cp_count = 0;
                if(isset($client_whs[$ob['ID']])){
                    $cp_count = $client_whs[$ob['ID']];
                }
                if(isset($arClRegions[$ob['PROPERTY_REGION_VALUE']])){
                    $arClRegions[$ob['PROPERTY_REGION_VALUE']]['COUNT'] = $arClRegions[$ob['PROPERTY_REGION_VALUE']]['COUNT']+$cp_count;
                }else{
                    $arClRegions[$ob['PROPERTY_REGION_VALUE']] = $arRegions[$ob['PROPERTY_REGION_VALUE']];
                    $arClRegions[$ob['PROPERTY_REGION_VALUE']]['COUNT'] = $cp_count;
                }
            }
            //отдельно кладём принадлежность склада к региону
            $result['WH_TO_REGION'][$ob['ID']] = $ob['PROPERTY_REGION_VALUE'];
        }

        $result['REGIONS'] = $arClRegions;
        $result['ALL_COUNT_CP'] = $allCount;

        return $result;
    }

    /*
     * получает данные для построения графиков во встречных предложениях покупателя (за последние 32 дня) (устаревшая функция)
     * @param array $filter - фильтрация для получения данных (по культуре, складу, пользователю)
     *
     * @return array - массив данных для построения графика
     */
    public static function getCounterRequestsGraphsData($filter){

        $hl_myr_id = rrsIblock::HLgetIBlockId('CONTREQMYPRICES');
        $hl_market_id = rrsIblock::HLgetIBlockId('CONTREQMARKET');
        $hl_myd_id = rrsIblock::HLgetIBlockId('CONTREQMYDEALS');
        $result = array(
            'USER_REQUESTS' => array(),
            'MARKET_DATA' => array(),
            'USER_DEALS' => array()
        );
        $get_date = ConvertTimeStamp(strtotime('-1 year'), 'SHORT', 's1');

        //отображаем графики только для конркетного пользователя
        if(!isset($filter['UF_USER_ID'])
            || $filter['UF_USER_ID'] == 0
        ){
            return $result;
        }

        if(isset($filter['UF_CENTER'])
            && is_numeric($filter['UF_CENTER'])
        ){
            //получение данных для графика "Мои цены"
            $arFilter = array();
            if(isset($filter['UF_USER_ID'])
                && is_numeric($filter['UF_USER_ID'])
            ){
                $arFilter = $filter;
            }
            $arFilter['>UF_DATE'] = $get_date;
            $arFilter['>UF_PRICE'] = 0;
            $logObj = new log;
            $entityDataClass = $logObj->getEntityDataClass($hl_myr_id);
            $el = new $entityDataClass;
            $rsData = $el->getList(array(
                'select' => array('UF_REQUEST', 'UF_DATE', 'UF_PRICE', 'UF_CENTER', 'UF_USER_ID'),
                'filter' => $arFilter,
                'order'  => array('UF_DATE' => 'ASC', 'ID' => 'ASC')
            ));
            while($res = $rsData->fetch()){
                $temp_data = explode(' ', $res['UF_DATE']->toString());
                $result['USER_REQUESTS'][] = array(
                    'UF_DATE' => $temp_data[0],
                    'UF_PRICE' => $res['UF_PRICE'],
                    'UF_CENTER' => $res['UF_CENTER'],
                    'UF_REQUEST' => $res['UF_REQUEST']
                );
            }

            //получение данных для графика "Рынок"
            $arFilter = array();
            $arFilter['>UF_BASE_PRICE'] = 0;
            $arFilter['>UF_DATE'] = $get_date;
            if(isset($filter['UF_CENTER'])
                && is_numeric($filter['UF_CENTER'])
            ){
                $arFilter['UF_CENTER'] = $filter['UF_CENTER'];
            }
            if(isset($filter['UF_CULTURE'])
                && is_numeric($filter['UF_CULTURE'])
            ){
                $arFilter['UF_CULTURE'] = $filter['UF_CULTURE'];
            }
            $logObj = new log;
            $entityDataClass = $logObj->getEntityDataClass($hl_market_id);
            $el = new $entityDataClass;
            $rsData = $el->getList(array(
                'select' => array('UF_DATE', 'UF_BASE_PRICE', 'UF_CENTER'),
                'filter' => $arFilter,
                'order'  => array('UF_DATE' => 'ASC', 'ID' => 'ASC')
            ));
            while($res = $rsData->fetch()){
                $temp_data = explode(' ', $res['UF_DATE']->toString());
                $result['MARKET_DATA'][] = array(
                    'UF_DATE' => $temp_data[0],
                    'UF_BASE_PRICE' => $res['UF_BASE_PRICE'],
                    'UF_CENTER' => $res['UF_CENTER'],
                );
            }

            //получение данных для графика "Мои сделки"
            $arFilter = array();
            $arFilter['>UF_DATE'] = $get_date;
            $arFilter['>UF_BASE_PRICE'] = 0;
            if(isset($filter['UF_USER_ID'])
                && is_numeric($filter['UF_USER_ID'])
            ){
                $arFilter = $filter;
                $arFilter['>UF_DATE'] = $get_date;
            }
            if(isset($filter['UF_CENTER'])
                && is_numeric($filter['UF_CENTER'])
            ){
                $arFilter['UF_CENTER'] = $filter['UF_CENTER'];
            }
            if(isset($filter['UF_CULTURE'])
                && is_numeric($filter['UF_CULTURE'])
            ){
                $arFilter['UF_CULTURE'] = $filter['UF_CULTURE'];
            }
            $logObj = new log;
            $entityDataClass = $logObj->getEntityDataClass($hl_myd_id);
            $el = new $entityDataClass;
            $rsData = $el->getList(array(
                'select' => array('UF_DATE', 'UF_BASE_PRICE', 'UF_CENTER'),
                'filter' => $arFilter,
                'order'  => array('UF_DATE' => 'ASC', 'ID' => 'ASC')
            ));
            while($res = $rsData->fetch()){
                $temp_data = explode(' ', $res['UF_DATE']->toString());
                $result['USER_DEALS'][] = array(
                    'UF_DATE' => $temp_data[0],
                    'UF_BASE_PRICE' => $res['UF_BASE_PRICE'],
                    'UF_CENTER' => $res['UF_CENTER'],
                );
            }
        }
        return $result;
    }

    /**
     * Удаление дублируемые строк с одинаковыми данными
     */
    public static function getDoubleCounterRequest(){
        global $DB;
        $hl_myprices_id = rrsIblock::HLgetIBlockId('CONTREQMYPRICES');
        $results = $DB->Query("SELECT `UF_DATE`, DATE_FORMAT(`UF_DATE`, '%Y-%m-%d') as `UD_DATE_F`, `UF_USER_ID`, `UF_WAREHOUSE`, `UF_CULTURE`, `UF_CENTER`, `UF_PRICE`, `UF_REQUEST`, COUNT(*) as 'COUNT'
                              FROM rrs_count_req_my_prices
                              GROUP BY DATE_FORMAT(`UF_DATE`, '%Y-%m-%d'), `UF_USER_ID`, `UF_WAREHOUSE`, `UF_CULTURE`, `UF_CENTER`, `UF_PRICE`, `UF_REQUEST`
                              HAVING COUNT(*) > 1");
        while ($row = $results->Fetch()) {
            $sql = "SELECT `ID` FROM rrs_count_req_my_prices
                              WHERE DATE_FORMAT(`UF_DATE`, '%Y-%m-%d')='" . $row['UD_DATE_F'] . "'
                                    AND `UF_USER_ID`='" . $row['UF_USER_ID'] . "'
                                    AND `UF_WAREHOUSE`='" . $row['UF_WAREHOUSE'] . "'
                                    AND `UF_CULTURE`='" . $row['UF_CULTURE'] . "'
                                    AND `UF_CENTER`='" . $row['UF_CENTER'] . "'
                                    AND `UF_PRICE`='" . $row['UF_PRICE'] . "'
                                    AND `UF_REQUEST`='" . $row['UF_REQUEST'] . "'
            ";
            $doubleRes = $DB->Query($sql);
            $doubleArr = array();
            while ($drow = $doubleRes->Fetch()) {
                $doubleArr[] = $drow;
            }
            if ((sizeof($doubleArr) > 1) && (is_array($doubleArr))) {
                for ($i = 1, $c = sizeof($doubleArr); $i<$c;$i++){
                    log::_deleteEntity($hl_myprices_id, $doubleArr[$i]['ID']);
                }
            }
        }
    }


    /*
     * Проверка может ли пользователь принимать встречные предложения (проверяется количество доступных принятий)
     * @param array $uid - id пользователя
     *
     * @return boolean - флаг разрешения (количество принятий больше 0)
     */
    public static function openerCountCheck($uid){
        $result = false;

        if(filter_var($uid, FILTER_VALIDATE_INT) !== false) {
            CModule::IncludeModule('iblock');
            $el_obj = new CIBlockElement;
            $res = $el_obj->GetList(
                array('ID' => 'ASC'),
                array(
                    'IBLOCK_ID' => rrsIblock::getIBlockId('client_profile'),
                    'PROPERTY_USER' => $uid,
                    '>PROPERTY_COUNTER_REQUEST_LIMIT' => 0,
                    false,
                    array('nTopCount' => 1),
                    array('ID')
                )
            );
            if ($res->SelectedRowsCount() == 1) {
                $result = true;
            }
        }

        return $result;
    }


    /*
     * Возвращает количество доступных принятий для пользователя
     * @param int $uid - id пользователя
     *
     * @return int - количество доступных принятий
     */
    public static function openerCountGet($uid){
        $result = 0;

        if(filter_var($uid, FILTER_VALIDATE_INT) !== false) {
            CModule::IncludeModule('iblock');
            $el_obj = new CIBlockElement;
            $res = $el_obj->GetList(
                array('ID' => 'ASC'),
                array(
                    'IBLOCK_ID' => rrsIblock::getIBlockId('client_profile'),
                    'PROPERTY_USER' => $uid,
                    '>PROPERTY_COUNTER_REQUEST_LIMIT' => 0
                ),
                false,
                array('nTopCount' => 1),
                array('PROPERTY_COUNTER_REQUEST_LIMIT')
            );
            if ($data = $res->Fetch()) {
                $result = intval($data['PROPERTY_COUNTER_REQUEST_LIMIT_VALUE']);
            }
        }

        return $result;
    }


    /**
     * Сохранение данных по выбранному запросу для графика цен
     * @param int $reqId - ID запроса
     */
    public static function saveCounterRiquestPrices($reqId){
        if(!is_numeric($reqId)){
            return false;
        }

        $hl_myprices_id = rrsIblock::HLgetIBlockId('CONTREQMYPRICES');
        $el_obj = new CIBlockElement;

        $filter = array(
            'IBLOCK_ID' => rrsIblock::getIBlockId('client_request'),
            'ID' => $reqId,
        );

        $req_ids = array();

        //получаем активные запросы (активных обычно меньше 100, в то время как "стоимости" из инфоблока не удаляются)
        $res = $el_obj->GetList(
            array('ID' => 'ASC'),
            $filter,
            false,
            array('nTopCount' => 1),
            array('ID', 'PROPERTY_CLIENT')
        );
        while($data = $res->Fetch()){
            $req_ids[$data['ID']] = $data['PROPERTY_CLIENT_VALUE'];
        }

        $date_stmp = time();
        $date_full = ConvertTimeStamp($date_stmp, 'FULL', 's1');

        $el_obj = new CIBlockElement;

        if(count($req_ids) > 0) {
            $res = $el_obj->GetList(
                array('PROPERTY_WAREHOUSE' => 'ASC', 'PROPERTY_REQUEST' => 'ASC'),
                array(
                    'IBLOCK_ID' => rrsIblock::getIBlockId('client_request_cost'),
                    'ACTIVE' => 'Y',
                    'PROPERTY_REQUEST' => array_keys($req_ids)
                ),
                false,
                false,
                array('PROPERTY_WAREHOUSE', 'PROPERTY_REQUEST', 'PROPERTY_CULTURE', 'PROPERTY_CENTER', 'PROPERTY_PRICE')
            );
            while ($data = $res->Fetch()) {
                if (isset($req_ids[$data['PROPERTY_REQUEST_VALUE']])) {
                    //сохраняем в БД
                    $arFields = array(
                        'UF_DATE' => $date_full,
                        'UF_USER_ID' => $req_ids[$data['PROPERTY_REQUEST_VALUE']],
                        'UF_WAREHOUSE' => $data['PROPERTY_WAREHOUSE_VALUE'],
                        'UF_CULTURE' => $data['PROPERTY_CULTURE_VALUE'],
                        'UF_CENTER' => $data['PROPERTY_CENTER_VALUE'],
                        'UF_PRICE' => $data['PROPERTY_PRICE_VALUE'],
                        'UF_REQUEST' => $data['PROPERTY_REQUEST_VALUE']
                    );
                    log::_createEntity($hl_myprices_id, $arFields);
                }
            }
        }
    }


    /*
     * сохраняет данные для построения графиков во встречных предложениях покупателя (на графике - "Мои цены", "Рынок" и "Мои сделки")
     * @param array $counterRequests - массив данных встречных предложений для которых нужно построить график
     *
     * @return array - массив данных для построения графика
     */
    public static function saveCounterRequestsGraphsData(){
        $req_ids = array();
        $deals_arr = array();
        $market_arr = array();

//        $start_memory = memory_get_usage();
        $date_stmp = strtotime('-12 hours');
        $date_short = ConvertTimeStamp($date_stmp, 'SHORT', 's1');
        $date_val = $date_short . ' 14:00:00';
        $nds_val = rrsIblock::getConst('nds');
//        p($date_val);
//        exit;
        $date_from = $date_short . ' 00:00:00';
        $date_to = $date_short . ' 23:59:59';

        $el_obj = new CIBlockElement;

        //получаем активные запросы (активных обычно меньше 100, в то время как "стоимости" из инфоблока не удаляются)
        $res = $el_obj->GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('client_request'),
                '>=DATE_CREATE' => $date_from,
                '<=DATE_CREATE' => $date_to
            ),
            false,
            false,
            array('ID', 'PROPERTY_CLIENT')
        );
        while($data = $res->Fetch()){
            $req_ids[$data['ID']] = $data['PROPERTY_CLIENT_VALUE'];
        }

        $hl_myprices_id = rrsIblock::HLgetIBlockId('CONTREQMYPRICES');
        $hl_market = rrsIblock::HLgetIBlockId('CONTREQMARKET');
        $hl_my_deals = rrsIblock::HLgetIBlockId('CONTREQMYDEALS');

        //добавляем данные для графика "Мои цены"
        if(intval($hl_myprices_id) > 0
            && intval($hl_market) > 0
            && intval($hl_my_deals) > 0
        ){
            if(count($req_ids) > 0) {
                $res = $el_obj->GetList(
                    array('PROPERTY_WAREHOUSE' => 'ASC', 'PROPERTY_REQUEST' => 'ASC'),
                    array(
                        'IBLOCK_ID' => rrsIblock::getIBlockId('client_request_cost'),
                        'ACTIVE' => 'Y',
                        'PROPERTY_REQUEST' => array_keys($req_ids),
                        '>=DATE_CREATE' => $date_from,
                        '<=DATE_CREATE' => $date_to
                    ),
                    false,
                    false,
                    array('PROPERTY_WAREHOUSE', 'PROPERTY_REQUEST', 'PROPERTY_CULTURE', 'PROPERTY_CENTER', 'PROPERTY_PRICE')
                );
                while ($data = $res->Fetch()) {
                    if (isset($req_ids[$data['PROPERTY_REQUEST_VALUE']])) {
                        //сохраняем в БД
                        $arFields = array(
                            'UF_DATE' => $date_val,
                            'UF_USER_ID' => $req_ids[$data['PROPERTY_REQUEST_VALUE']],
                            'UF_WAREHOUSE' => $data['PROPERTY_WAREHOUSE_VALUE'],
                            'UF_CULTURE' => $data['PROPERTY_CULTURE_VALUE'],
                            'UF_CENTER' => $data['PROPERTY_CENTER_VALUE'],
                            'UF_PRICE' => $data['PROPERTY_PRICE_VALUE'],
                            'UF_REQUEST' => $data['PROPERTY_REQUEST_VALUE']
                        );

                        log::_createEntity($hl_myprices_id, $arFields);
                    }
                }
            }
            //находим полностью одинаковые записи в HL блоке и удаляем дубликаты
            client::getDoubleCounterRequest();

            //добавляем данные для графиков "Рынок" и "Мои сделки"
            $res = $el_obj->GetList(
                array('PROPERTY_CLIENT_WAREHOUSE' => 'ASC', 'PROPERTY_REQUEST' => 'ASC'),
                array(
                    'IBLOCK_ID' => rrsIblock::getIBlockId('deals_deals'),
                    '>PROPERTY_BASE_CONTR_PRICE' => 0,
                    '!PROPERTY_CLIENT_VALUE' => false,
                    '>=DATE_CREATE' => $date_from,
                    '<=DATE_CREATE' => $date_to
                ),
                false,
                false,
                array(
                    'ID', 'DATE_CREATE',
                    'PROPERTY_CULTURE',
                    'PROPERTY_CENTER',
                    'PROPERTY_CLIENT',
                    'PROPERTY_VOLUME',
                    'PROPERTY_ACC_PRICE_CSM',
                    'PROPERTY_BASE_PRICE',
                    'PROPERTY_BASE_CONTR_PRICE',
                    'PROPERTY_B_NDS',
                )
            );
            while($data = $res->Fetch()){
                if(is_numeric($data['PROPERTY_CLIENT_VALUE'])) {
                    $deals_arr[$data['PROPERTY_CLIENT_VALUE']][$data['PROPERTY_CENTER_VALUE']][] = array(
                        'VOLUME' => $data['PROPERTY_VOLUME_VALUE'],
                        'CSM_PRICE' => $data['PROPERTY_ACC_PRICE_CSM_VALUE'],
                        'BASE_PRICE' => $data['PROPERTY_BASE_PRICE_VALUE'],
                        'BASE_CONTR_PRICE' => $data['PROPERTY_BASE_CONTR_PRICE_VALUE'],
                        'CULTURE' => $data['PROPERTY_CULTURE_VALUE'],
                        'NDS' => ($data['PROPERTY_B_NDS_VALUE'] == 'Y'),
                        'ID' => $data['ID'],
                    );
                }
            }

            //пробегаем по всем центрам и сохраняем данные для пользователей и для центров
            foreach($deals_arr as $cur_client => $cur_centers_data){
                if(count($cur_centers_data) > 0){
                    foreach($cur_centers_data as $center_id => $cur_datas){
                        $cur_csm_price = 0;
                        $cur_csm_price_no_nds = 0;
                        $cur_csm_price_nds = 0;
                        $cur_base_price = 0;
                        $cur_base_price_no_nds = 0;
                        $cur_base_price_nds = 0;
                        $cur_base_contr_price = 0;
                        $cur_volume = 0;
                        $cur_culture = 0;

                        foreach($cur_datas as $cur_data){
                            $cur_csm_price_vol = $cur_data['CSM_PRICE'] * $cur_data['VOLUME'];
                            $cur_base_price_vol = $cur_data['BASE_PRICE'] * $cur_data['VOLUME'];

                            $cur_csm_price += $cur_csm_price_vol;
                            $cur_base_price += $cur_base_price_vol;
                            $cur_base_contr_price += $cur_data['BASE_CONTR_PRICE'] * $cur_data['VOLUME'];
                            $cur_volume += $cur_data['VOLUME'];
                            $cur_culture = $cur_data['CULTURE'];

                            //учет НДС для графика "рынок"
                            if($cur_data['NDS']){
                                //текущая цена с места содержит НДС
                                $cur_csm_price_nds += $cur_csm_price_vol;
                                $cur_base_price_nds += $cur_base_price_vol;
                            }else{
                                //текущая цена с места рассчитана без НДС
                                $cur_csm_price_nds += $cur_csm_price_vol + ($cur_csm_price_vol * 0.01 * $nds_val); //добавляем НДС
                                $cur_base_price_nds += $cur_base_price_vol + ($cur_base_price_vol * 0.01 * $nds_val); //добавляем НДС
                            }
                        }

                        $cur_csm_price_no_nds = $cur_csm_price_nds / (1 + 0.01 * $nds_val); //вычитаем НДС
                        $cur_base_price_no_nds = $cur_base_price_nds / (1 + 0.01 * $nds_val); //вычитаем НДС

                        if($cur_csm_price > 0
                            && $cur_volume > 0
                        ){
                            $market_arr[$center_id][] = array(
                                'VOLUME' => $cur_volume,
                                'CSM_PRICE_NDS' => $cur_csm_price_nds,
                                'CSM_PRICE_NO_NDS' => $cur_csm_price_no_nds,
                                'BASE_PRICE_NDS' => $cur_base_price_nds,
                                'BASE_PRICE_NO_NDS' => $cur_base_price_no_nds,
                                'BASE_CONTR_PRICE' => $cur_base_contr_price,
                                'CULTURE' => $cur_culture,
                            );

                            $arFields = array(
                                'UF_DATE'       => $date_val,
                                'UF_USER_ID'    => $cur_client,
                                'UF_CULTURE'    => $cur_culture,
                                'UF_CENTER'     => $center_id,
                                'UF_CSM_PRICE'  => number_format($cur_csm_price / $cur_volume, 2, '.', ''),
                                'UF_BASE_PRICE'  => number_format($cur_base_price / $cur_volume, 2, '.', ''),
                                'UF_BASE_CONTR_PRICE'  => number_format($cur_base_contr_price / $cur_volume, 2, '.', '')
                            );

                            //записываем в БД
                            log::_createEntity($hl_my_deals, $arFields);
                        }
                    }
                }
            }

            foreach($market_arr as $cur_center => $cur_datas){
                $cur_csm_price_no_nds = 0;
                $cur_csm_price_nds = 0;
                $cur_base_price_nds = 0;
                $cur_base_price_no_nds = 0;
                $cur_base_contr_price = 0;
                $cur_volume = 0;
                $cur_culture = 0;
                foreach($cur_datas as $cur_data){
                    $cur_csm_price_nds += $cur_data['CSM_PRICE_NDS'];
                    $cur_csm_price_no_nds += $cur_data['CSM_PRICE_NO_NDS'];
                    $cur_base_price_nds += $cur_data['BASE_PRICE_NDS'];
                    $cur_base_price_no_nds += $cur_data['BASE_PRICE_NO_NDS'];
                    $cur_base_contr_price += $cur_base_price_nds;
                    $cur_volume += $cur_data['VOLUME'];
                    $cur_culture = $cur_data['CULTURE'];
                }

                //записываем в БД
                if($cur_csm_price_nds > 0
                    && $cur_volume > 0
                ){
                    $arFields = array(
                        'UF_DATE'       => $date_val,
                        'UF_CULTURE'    => $cur_culture,
                        'UF_CENTER'     => $cur_center,
                        'UF_CSM_PRICE'  => number_format($cur_csm_price_no_nds / $cur_volume, 2, '.', ''),
                        'UF_CSM_PRICE_NDS'  => number_format($cur_csm_price_nds / $cur_volume, 2, '.', ''),
                        'UF_BASE_PRICE'  => number_format($cur_base_price_no_nds / $cur_volume, 2, '.', ''),
                        'UF_BASE_PRICE_NDS'  => number_format($cur_base_price_nds / $cur_volume, 2, '.', ''),
                        'UF_BASE_CONTR_PRICE'  => number_format($cur_base_contr_price / $cur_volume, 2, '.', '')
                    );

                    log::_createEntity($hl_market, $arFields);
                }
            }
        }
//        $end_memory = memory_get_usage() - $start_memory;
//        echo 'memory usage: ' . round($end_memory / 1024, 2) . ' KB';
        exit;
    }


    /**
     * Проверка сохранения фильтра на странице пар клиентов
     *
     * @return array массив, где у ключа 'NEED_UPD' значение либо true либо false,
     * у ключа 'URL_UPD' значение адреса переадресации
     */
    public static function filterClientPairCheck(){
        $result = array(
            'NEED_UPD'  => false,
            'URL_UPD'   => ''
        );
        if(isset($_GET['id'])
            && $_GET['id'] > 0){
            return $result;
        }

        if(((isset($_GET['client_warehouse_id']))&&(!empty($_GET['client_warehouse_id'])))||
            ((isset($_GET['culture_id']))&&(!empty($_GET['culture_id'])))
            || (
                isset($_POST['send_ajax'])
                && $_POST['send_ajax'] == 'y'
            )
        ){
            return $result;
        }

        $page_need_update = false;
        $new_url_params = array();

        $warehouse_cookie = '';
        $culture_cookie = '';
        //проверка куки склада
        $cookie_name = 'deals_filter_client_warehouse_id';
        if(isset($_COOKIE[$cookie_name])){
            $warehouse_cookie = $_COOKIE[$cookie_name];
            if((!isset($_GET['client_warehouse_id']) || $_GET['client_warehouse_id'] == '' || $_GET['client_warehouse_id'] == '0')
                && $warehouse_cookie != 0 && $warehouse_cookie != ''
            ){
                $result['NEED_UPD'] = true;
                $new_url_params[] = 'client_warehouse_id=' . $warehouse_cookie;
            }
        }
        //проверка куки культуры
        $cookie_name = 'deals_filter_culture_id';
        if(isset($_COOKIE[trim($cookie_name)])){
            $culture_cookie = $_COOKIE[$cookie_name];
            if((!isset($_GET['culture_id']) || $_GET['culture_id'] == '' || $_GET['culture_id'] == '0')
                && $culture_cookie != 0 && $culture_cookie != ''
            ){
                $result['NEED_UPD'] = true;
                $new_url_params[] = 'culture_id=' . $culture_cookie;
            }
        }
        if($result['NEED_UPD']){
            $result['URL_UPD'] = '/client/pair/' . (count($new_url_params) > 0 ? '?' . implode('&', $new_url_params) : '');
        }
        return $result;
    }

    /**
     * Получение поставщиков из черного списка покупателя
     * @param int $user_id ID пользователь
     * @return array массив ID поставщиков
     */
    public static function getBlackListOpponents($user_id){
        $result = array();

        CModule::IncludeModule('iblock');
        $el_obj = new CIBlockElement;
        $res = $el_obj->GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('client_black_list'),
                'ACTIVE' => 'Y',
                'PROPERTY_USER' => $user_id
            ),
            false,
            false,
            array('PROPERTY_OPPONENT')
        );
        while($data = $res->Fetch()){
            $result[$data['PROPERTY_OPPONENT_VALUE']] = true;
        }

        if(count($result) > 0){
            $result = array_keys($result);
        }

        return $result;
    }

    /**
     * Получение поставщиков у которых в черном списке состоит указанный покупатель
     * @param int $user_id ID пользователь
     * @return array массив ID поставщиков
     */
    public static function getBlackListWhereOpponent($user_id){
        $result = array();

        CModule::IncludeModule('iblock');
        $el_obj = new CIBlockElement;
        $res = $el_obj->GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('farmer_black_list'),
                'ACTIVE' => 'Y',
                'PROPERTY_OPPONENT' => $user_id
            ),
            false,
            false,
            array('PROPERTY_USER')
        );
        while($data = $res->Fetch()){
            $result[$data['PROPERTY_USER_VALUE']] = true;
        }

        if(count($result) > 0){
            $result = array_keys($result);
        }

        return $result;
    }

    /**
     * Получение списка поставщиков которые в черном списке выбранного покупателя или у которых в черном списке состоит указанный покупатель
     * @param int $user_id ID пользователь
     * @return array массив ID поставщиков
     */
    public static function getBlackListTotal($user_id){
        $result = array();

        CModule::IncludeModule('iblock');
        $el_obj = new CIBlockElement;

        //получение данных тех, кто в ЧС пользователя
        $res = $el_obj->GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('client_black_list'),
                'ACTIVE' => 'Y',
                'PROPERTY_USER' => $user_id
            ),
            false,
            false,
            array('PROPERTY_OPPONENT')
        );
        while($data = $res->Fetch()){
            $result[$data['PROPERTY_OPPONENT_VALUE']] = true;
        }

        //получение данных тех, у кого пользователь в ЧС
        $res = $el_obj->GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('farmer_black_list'),
                'ACTIVE' => 'Y',
                'PROPERTY_OPPONENT' => $user_id
            ),
            false,
            false,
            array('PROPERTY_USER')
        );
        while($data = $res->Fetch()){
            $result[$data['PROPERTY_USER_VALUE']] = true;
        }

        if(count($result) > 0){
            $result = array_keys($result);
        }

        return $result;
    }

    /**
     * Получение данных покупателя
     * @param int $user_id ID покупателя
     * @return string данные покупателя
     */
    public static function getUserData($user_id){
        $result = '';

        CModule::IncludeModule('iblock');
        $res = CIBlockElement::GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('client_profile'),
                'PROPERTY_USER' => $user_id
            ),
            false,
            array('nTopCount' => 1),
            array('PROPERTY_FULL_COMPANY_NAME', 'PROPERTY_IP_FIO')
        );
        if($data = $res->Fetch()){
            if(trim($data['PROPERTY_FULL_COMPANY_NAME_VALUE']) != ''){
                $result = trim($data['PROPERTY_FULL_COMPANY_NAME_VALUE']);
            }else{
                $result = trim($data['PROPERTY_IP_FIO_VALUE']);
            }
        }

        if($result == ''){
            $res = CUser::GetList(
                ($by = 'id'), ($order = 'asc'),
                array('ID' => $user_id),
                array('FIELDS' => array('ID', 'EMAIL', 'NAME', 'LAST_NAME'))
            );
            while($data = $res->Fetch()){
                $result = trim($data['NAME'] . ' ' . $data['LAST_NAME']);
                if($result == ''){
                    if(!checkEmailFromPhone($data['EMAIL'])){
                        $result = $data['EMAIL'];
                    }else{
                        $result = $data['ID'];
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Получение названия организации покупателей
     * @param array $user_ids ID покупателей
     * @return array массив названий организаций
     */
    public static function getUsersCompanyList($user_ids){
        $result = array();

        if(!empty($user_ids)
            && is_array($user_ids)
        ){
            CModule::IncludeModule('iblock');

            $user_type_ip = rrsIblock::getPropListKey('client_profile', 'UL_TYPE', 'ip');
            $res = CIBlockElement::GetList(
                array('ID' => 'ASC'),
                array(
                    'IBLOCK_ID' => rrsIblock::getIBlockId('client_profile'),
                    'PROPERTY_USER' => $user_ids
                ),
                false,
                false,
                array('PROPERTY_USER', 'PROPERTY_UL_TYPE', 'PROPERTY_FULL_COMPANY_NAME', 'PROPERTY_IP_FIO')
            );
            while($data = $res->Fetch()){
                if ($user_type_ip == $data['PROPERTY_UL_TYPE_ENUM_ID']) {
                    $result[$data['PROPERTY_USER_VALUE']] = 'ИП ' . trim($data['PROPERTY_IP_FIO_VALUE']);
                } else {
                    $result[$data['PROPERTY_USER_VALUE']] = trim($data['PROPERTY_FULL_COMPANY_NAME_VALUE']);
                }
            }
        }

        return $result;
    }
    
    /**
     * Проверка сохранения фильтра на странице черного списка клиентов
     *
     * @return array массив, где у ключа 'NEED_UPD' значение либо true либо false,
     * у ключа 'URL_UPD' значение адреса переадресации
     */
    public static function filterClientBLCheck(){
        $result = array(
            'NEED_UPD'  => false,
            'URL_UPD'   => ''
        );
        if(isset($_GET['id'])
            && $_GET['id'] > 0){
            return $result;
        }

        if(((isset($_GET['region_id']))&&(!empty($_GET['region_id'])))||
            ((isset($_GET['culture_id']))&&(!empty($_GET['culture_id'])))||
            ((isset($_GET['reasond_id']))&&(!empty($_GET['reasond_id'])))
        ){
            return $result;
        }

        $page_need_update = false;
        $new_url_params = array();

        $region_id_cookie = '';
        $culture_cookie = '';
        //проверка куки склада
        $cookie_name = 'blacklist_filter_region_id';
        if(isset($_COOKIE[$cookie_name])){
            $region_id_cookie = $_COOKIE[$cookie_name];
            if((!isset($_GET['region_id']) || $_GET['region_id'] == '' || $_GET['region_id'] == '0')
                && $region_id_cookie != 0 && $region_id_cookie != ''
            ){
                $result['NEED_UPD'] = true;
                $new_url_params[] = 'region_id=' . $region_id_cookie;
            }
        }
        //проверка куки культуры
        $cookie_name = 'blacklist_filter_culture_id';
        if(isset($_COOKIE[trim($cookie_name)])){
            $culture_cookie = $_COOKIE[$cookie_name];
            if((!isset($_GET['culture_id']) || $_GET['culture_id'] == '' || $_GET['culture_id'] == '0')
                && $culture_cookie != 0 && $culture_cookie != ''
            ){
                $result['NEED_UPD'] = true;
                $new_url_params[] = 'culture_id=' . $culture_cookie;
            }
        }
        //проверка куки причины
        $cookie_name = 'blacklist_filter_reasond_id';
        if(isset($_COOKIE[trim($cookie_name)])){
            $reason_id_cookie = $_COOKIE[$cookie_name];
            if((!isset($_GET['reasond_id']) || $_GET['culture_id'] == '' || $_GET['reasond_id'] == '0')
                && $reason_id_cookie != 0 && $reason_id_cookie != ''
            ){
                $result['NEED_UPD'] = true;
                $new_url_params[] = 'reasond_id=' . $reason_id_cookie;
            }
        }
        if($result['NEED_UPD']){
            $result['URL_UPD'] = '/client/blacklist/' . (count($new_url_params) > 0 ? '?' . implode('&', $new_url_params) : '');
        }
        return $result;
    }

    /**
     * Получение ID запросов по ID складов через записи стоимостей
     * @param  array $whIds - массив ID складов
     * @return array массив ID запросов
     */
    public static function getRequestListByWh($whIds){
        $result = array();

        CModule::IncludeModule('iblock');
        $res = CIBlockElement::GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('client_request_cost'),
                'PROPERTY_WAREHOUSE' => $whIds,
                'ACTIVE' => 'Y',
                '!PROPERTY_REQUEST' => false
            ),
            false,
            false,
            array('PROPERTY_REQUEST')
        );
        while($data = $res->Fetch()){
            $result[$data['PROPERTY_REQUEST_VALUE']] = true;
        }

        if(count($result) > 0){
            $result = array_keys($result);
        }

        return $result;
    }

    /**
     * Проверка лимита на доступные запросы
     * @param $user_id - ID покупателя
     * @return array - массив, где CNT - общее количество разрешенных покупателю запросов, REMAINS - оставшееся разрешенное количество
     */
    public static function checkAvailableRequestLimit($user_id){
        $result = array('CNT' => 0, 'REMAINS' => 0);

        if(!is_numeric($user_id)){
            return $result;
        }

        CModule::IncludeModule('iblock');
        $el_obj = new CIBlockElement;

        //получение константы ограничения
        $current_const = intval(rrsIblock::getConst('min_request_limit'));
        if($current_const > 0){
            $result['CNT'] = $result['CNT'] + $current_const;
        }

        //индивидуальное дополнительное к общему ограничению
        $res = $el_obj->GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('client_profile'),
                'PROPERTY_USER' => $user_id
            ),
            false,
            false,
            array('PROPERTY_REQUEST_LIMIT')
        );
        if($data = $res->Fetch()){
            $temp_val = intval($data['PROPERTY_REQUEST_LIMIT_VALUE']);
            if($temp_val > 0){
                $result['CNT'] = $result['CNT'] + $temp_val;
            }
        }

        //проверка наличия текущих активных запросов у пользователя
        $res = $el_obj->GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('client_request'),
                'ACTIVE' => 'Y',
                'PROPERTY_CLIENT' => $user_id,
                'PROPERTY_ACTIVE' => rrsIblock::getPropListKey('client_request', 'ACTIVE', 'yes'),
            ),
            false,
            false,
            array('ID')
        );

        $result['REMAINS'] = $result['CNT'] - $res->SelectedRowsCount();

        if($result['CNT'] < 0)
            $result['CNT'] = 0;

        if($result['REMAINS'] < 0)
            $result['REMAINS'] = 0;

        return $result;
    }

    /**
     * Проверка указанных пользователей на превышение лимита запросов
     * @param array $user_id - массив ID покупателей (проверить всех активных, если параметр пуст)
     */
    public static function checkRequestOverLimitAfterUpdate($user_id){

        $check_users = array();
        if(is_array($user_id)
            && count($user_id) > 0
        ){
            $check_users = $user_id;
        }
        else{
            //получаем всех активных покупателей
            $res = CUser::GetList(
                ($by = 'id'), ($order = 'asc'),
                array(
                    'ACTIVE' => 'Y',
                    'GROUPS_ID' => array(9)
                ),
                array('FIELDS' => array('ID'))
            );
            while($data = $res->Fetch()){
                $check_users[] = $data['ID'];
            }
        }

        if(count($check_users) > 0){
            //проверяем остатки запросов и ограничения пользователей
            $check_arr = agent::checkAvailableRequestLimit($check_users, true);
            if(isset($check_arr['OVERLIM'])
                && $check_arr['OVERLIM'] > 0
            ){
                foreach($check_arr['USERS'] as $cur_user_id => $cur_data){
                    if(isset($cur_data['OVERLIM'])
                        && filter_var($cur_data['OVERLIM'], FILTER_VALIDATE_INT)
                        && $cur_data['OVERLIM'] > 0
                    ){
                        //есть превышение лимита, удаляем лишние запросы для пользователя (сначала самые старые)
                        $res = CIBlockElement::GetList(
                            array('DATE_CREATE' => 'ASC'),
                            array(
                                'IBLOCK_ID' => rrsIblock::getIBlockId('client_request'),
                                'ACTIVE' => 'Y',
                                'PROPERTY_CLIENT' => $cur_user_id,
                                'PROPERTY_ACTIVE' => rrsIblock::getPropListKey('client_request', 'ACTIVE', 'yes'),
                            ),
                            false,
                            array('nTopCount' => $cur_data['OVERLIM']),
                            array('ID')
                        );
                        while($data = $res->Fetch()){
                            self::deactivateRequestByID($data['ID']);
                        }
                    }
                }
            }
        }
    }

    /**
     * Получение привязки складов к регионам
     * @param array $wh_ids идентификаторы складов
     * @return array массив, где ключи - id складов, а значения id регионов
     */
    public static function getRegionsByWhs($wh_ids) {
        $result = array();

        if(is_numeric($wh_ids)
            || (
                is_array($wh_ids)
                && count($wh_ids) > 0
            )
        ){
            CModule::IncludeModule('iblock');
            $elObj = new CIBlockElement;
            $res = $elObj->GetList(
                array('ID' => 'ASC'),
                array(
                    'IBLOCK_ID' => rrsIblock::getIBlockId('client_warehouse'),
                    'ID' => $wh_ids
                ),
                false,
                false,
                array(
                    'ID',
                    'PROPERTY_REGION'
                )
            );
            while($data = $res->Fetch()){
                $result[$data['ID']] = $data['PROPERTY_REGION_VALUE'];
            }
        }

        return $result;
    }

    /**
     * Получение складов по связанным регионам к рассматриваемому складу
     * @param int $wh_id идентификатора
     * @return array массив id складов
     */
    public static function getLinkedRegionsWhsByWhID($wh_id) {
        $result = array();

        $region_id = 0;
        $linked_regions_id = array();
        CModule::IncludeModule('iblock');
        $elObj = new CIBlockElement;

        //получаем регион склада
        if(is_numeric($wh_id)){
            $res = $elObj->GetList(
                array('ID' => 'ASC'),
                array(
                    'IBLOCK_ID' => rrsIblock::getIBlockId('client_warehouse'),
                    'ID' => $wh_id
                ),
                false,
                false,
                array(
                    'ID',
                    'PROPERTY_REGION'
                )
            );
            while($data = $res->Fetch()){
                $region_id = $data['PROPERTY_REGION_VALUE'];
            }
        }

        //получаем связанные регионы и склады по ним
        if(!empty($region_id)){
            $linked_regions_id = getLinkedRegions($region_id);
            if(count($linked_regions_id) > 0){
                $res = $elObj->GetList(
                    array('ID' => 'ASC'),
                    array(
                        'IBLOCK_ID' => rrsIblock::getIBlockId('client_warehouse'),
                        'PROPERTY_REGION' => $linked_regions_id
                    ),
                    false,
                    false,
                    array(
                        'ID'
                    )
                );
                while($data = $res->Fetch()){
                    $result[] = $data['ID'];
                }
            }
        }

        return $result;
    }


    /**
     * Получение региона склада
     * @param $WhId
     * @return int
     * @throws Exception
     */
    public static function getRegionByWh($WhId){
        $regionId = 0;
        $res = CIBlockElement::GetList(
            ['NAME' => 'ASC'],
            [
                'IBLOCK_ID' => getIBlockID('client', 'client_warehouse'),
                'ID'        => $WhId,
            ],
            false,
            false,
            ['ID', 'PROPERTY_REGION']
        );

        if($ob = $res->Fetch()) {
            $regionId = $ob['PROPERTY_REGION_VALUE'];
        }
        return $regionId;
    }


    /**
     * Получание регионального цента по культуре и складу
     * @param $cultureId    - ID культуры
     * @param $WhId         - ID склада
     * @return int
     */
    public static function getRegCenterByCultureAndWh($cultureId,$WhId){
        $regCenterId = 0;
        $res = CIBlockElement::GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('client_warehouse_rc'),
                'ACTIVE' => 'Y',
                'PROPERTY_WAREHOUSE' => $WhId,
                'PROPERTY_CULTURE' => $cultureId,
            ),
            false,
            false,
            array('ID')
        );
        if ($ob = $res->Fetch()) {
            $regCenterId = $ob['ID'];
        }
        return $regCenterId;
    }


    /**
     * Получение региональных центов по культуре и массива складов
     * @param $cultureId    - ID культуры
     * @param $WhIds        - массив ID складов
     * @return array
     */
    public static function getRegCenterByCultureAndWhArr($cultureId, $WhIds){
        $result = array();
        $res = CIBlockElement::GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('client_warehouse_rc'),
                'ACTIVE' => 'Y',
                'PROPERTY_WAREHOUSE' => $WhIds,
                'PROPERTY_CULTURE' => $cultureId,
            ),
            false,
            false,
            array('ID')
        );
        while($ob = $res->Fetch()){
            $result[] = $ob['ID'];
        }
        return $result;
    }


    /**
     * Получение цены спроса
     * @param $cultureId
     * @param mixed $region_ids - id региона или массив id
     * @param boolean $nds - с НДС/без НДС
     * @param boolean $get_linked_regions - получить данные в связанных регионах
     */
    public static function getReqAveragePrices($cultureId, $region_ids, $nds, $get_linked_regions = false){
        $hl_id = rrsIblock::HLgetIBlockId('REQUESTAVERAGEPRICES');
        $averagePrices = array();
        $tempPrices = array();
        if(!empty($region_ids)) {
            $arFilter = array();
            $arFilter['<=UF_DATE'] = date('d.m.Y', strtotime('-1 day'));
            $arFilter['>=UF_DATE'] = date('d.m.Y', strtotime('-2 day'));
            $arFilter['UF_REGION_ID'] = $region_ids;
            $arFilter['UF_CULTURE_ID'] = $cultureId;
            $nds_val = rrsIblock::getConst('nds');
            $logObj = new log;
            $entityDataClass = $logObj->getEntityDataClass($hl_id);
            $el = new $entityDataClass;
            $rsData = $el->getList(array(
                'select' => array('UF_DATE', 'UF_PRICE'),
                'filter' => $arFilter,
                'order' => array('UF_DATE' => 'ASC', 'ID' => 'ASC')
            ));
            while ($res = $rsData->fetch()) {
                $temp_data = explode(' ', $res['UF_DATE']->toString());
                $price = $res['UF_PRICE'];
                if ($nds) {
                    $price = $price + $price * 0.01 * $nds_val; //добавляем НДС
                }
                if (!$get_linked_regions) {
                    //внутри региона используем значение
                    $averagePrices[$temp_data[0]] = round($price);
                } else {
                    //для связанных регионов получаем массив значений для дальнейшей обработки
                    $tempPrices[$temp_data[0]][] = $price;
                }
            }
            //если данные по связанным регионам, то получаем среднее значение
            if($get_linked_regions){
                foreach($tempPrices as $cur_date => $cur_prices){
                    $temp_price = 0;
                    if(count($cur_prices) > 0) {
                        foreach ($cur_prices as $cur_price) {
                            $temp_price += $cur_price;
                        }
                        $averagePrices[$cur_date] = round($temp_price / count($cur_prices));
                    }
                }
            }
        }
        return $averagePrices;
    }


    /**
     * Получение цены рынка за 2 предыдущие даты
     * @param $cultureId
     * @param int $WhId - ID склада
     * @param boolean $nds - с НДС/без НДС
     * @param boolean $get_linked_regions - получить данные в связанных регионах
     */
    public static function getMarketCulturesPrices($cultureId, $WhId, $nds, $get_linked_regions = false){
        $hl_id = rrsIblock::HLgetIBlockId('CONTREQMARKET');
        if($get_linked_regions){
            //получаем id складов в связанных регионах
            $wh_ids = self::getLinkedRegionsWhsByWhID($WhId);
            $regCenterId = self::getRegCenterByCultureAndWhArr($cultureId, $wh_ids);
        }else {
            $regCenterId = self::getRegCenterByCultureAndWh($cultureId, $WhId);
        }
        $marketPrices = array();
        $tempPrices = array();
        if(!empty($regCenterId)){
            $arFilter = array();
            $arFilter['<=UF_DATE'] = date('d.m.Y', strtotime('-1 day')).' 23:59:59';
            $arFilter['>=UF_DATE'] = date('d.m.Y', strtotime('-2 day')).' 00:00:00';
            $arFilter['UF_CENTER'] = $regCenterId;
            $arFilter['UF_CULTURE'] = $cultureId;
            $logObj = new log;
            $entityDataClass = $logObj->getEntityDataClass($hl_id);
            $el = new $entityDataClass;
            $rsData = $el->getList(array(
                'select' => array('UF_DATE', 'UF_BASE_PRICE', 'UF_BASE_PRICE_NDS'),
                'filter' => $arFilter,
                'order'  => array('UF_DATE' => 'ASC', 'ID' => 'ASC')
            ));
            while($res = $rsData->fetch()){
                $temp_data = explode(' ', $res['UF_DATE']->toString());
                if(!$get_linked_regions){
                    //внутри региона используем значение
                    if ($nds) {
                        $marketPrices[$temp_data[0]] = round($res['UF_BASE_PRICE_NDS']);
                    } else {
                        $marketPrices[$temp_data[0]] = round($res['UF_BASE_PRICE']);
                    }
                }else{
                    //для связанных регионов получаем массив занчений для дальнейшей обработки
                    if ($nds) {
                        $tempPrices[$temp_data[0]][] = $res['UF_BASE_PRICE_NDS'];
                    } else {
                        $tempPrices[$temp_data[0]][] = $res['UF_BASE_PRICE'];
                    }
                }
            }
            //если данные по связанным регионам, то получаем среднее значение
            if($get_linked_regions){
                foreach($tempPrices as $cur_date => $cur_prices){
                    $temp_price = 0;
                    if(count($cur_prices) > 0) {
                        foreach ($cur_prices as $cur_price) {
                            $temp_price += $cur_price;
                        }
                        $marketPrices[$cur_date] = round($temp_price / count($cur_prices));
                    }
                }
            }
        }
        return $marketPrices;
    }

    /**
     * Получение цены предложения
     * @param $reqId    - ID запроса
     * @param $offerId  - ID предложения
     * @paran $nds      - с НДС/без НДС
     */
    public static function getOfferPrices($reqId,$offerId, $nds){
        $hl_id = rrsIblock::HLgetIBlockId('COUNTEROFFERS');
        $price = 0;
        $arFilter = array();
        $arFilter['UF_OFFER_ID'] = $offerId;
        $arFilter['UF_REQUEST_ID'] = $reqId;
        $logObj = new log;
        $entityDataClass = $logObj->getEntityDataClass($hl_id);
        $el = new $entityDataClass;
        $rsData = $el->getList(array(
                'select' => array('UF_FARMER_PRICE', 'UF_NDS_FARMER', 'UF_NDS_CLIENT', 'UF_OFFER_ID', 'UF_REQUEST_ID', 'UF_CLIENT_ID', 'UF_ROUTE', 'UF_CLIENT_WH_ID'),
                'filter' => $arFilter,
                'order'  => array('UF_DATE' => 'ASC', 'ID' => 'ASC')
        ));
        if($res = $rsData->fetch()){
            $offerData = farmer::getOfferById($res['UF_OFFER_ID']);
            $requestData = client::getRequestById($res['UF_REQUEST_ID']);
            $dumpValue = deal::getDump($requestData['PARAMS'], $offerData['PARAMS']);
            $arCulturesGroup = culture::getCulturesGroup();
            $reg_center = client::getRegCenterByCultureAndWh($requestData['CULTURE_ID'], $res['UF_CLIENT_WH_ID']);

            $tarif = client::getTarif($res['UF_CLIENT_ID'], $arCulturesGroup[$requestData['CULTURE_ID']], 'fca', $reg_center, $res['UF_ROUTE'], model::getAgrohelperTariffs());

            $price_tmp = lead::makeBaseFromCSM($res['UF_FARMER_PRICE'], $res['UF_NDS_CLIENT'] == 'yes', $res['UF_NDS_FARMER'] == 'yes', $dumpValue, $tarif);

            $price = $price_tmp['BASE_CONTR_PRICE'];
        }
        return round($price);
    }

    /*
     * Генерация текста для ссылки покупателю
     *
        На складе [склад] по товару [товар]:
        Рынок: [цена рынка], руб/тн (+ / - [значение изм. рынка]) (если есть данные)
        Средний спрос на рынке: [цена спроса], руб/тн (+ / - [значение изм. рынка]) (если есть данные)
        Предложение: [цена] руб/т, [без/с НДС]
        Рассмотреть предложение и аналитику по ссылке [ссылка]
     *  @param int $clientId - ID клиента
     *  @param int $reqId - ID запроса
     *  @param int $offerId - ID товара
     *  @param int $cultureId - ID культуры
     *  @param int $WhId - ID склада
     *  @param string $link - ссылка
     *  @param array $additional_value - массив дополнительных параметров
     *  @param boolean $bMakeHref - оформить ли ссылку в html тег (не стоит ставить для коппирования в мессенджеры)
     *  @param boolean $bIsOtherOffer - флаг того, что нужно использовать шаблон для публичной страницы создания пары, когда отображается ссылка на другое предложение
     *  @return string
    */
    public static function getClientTextToCOfferLink($clientId, $reqId, $offerId, $cultureId, $WhId, $link, $additional_value = array(), $bMakeHref = false, $bIsOtherOffer = false){
        $text = '';
        $counter_req_data = farmer::getCounterRequestsData($offerId, $reqId);
        $yesterday = date('d.m.Y', strtotime('-1 day')); //вчера
        $before_yesterday = date('d.m.Y', strtotime('-2 day')); //позавчера
        $wh_name = self::getWHNameById($WhId);  //имя склада покупателя
        $culture_name = culture::getName($cultureId);   //наименование культуры

        //проверяем корректно ли предложение
        if(
            !isset($counter_req_data[$offerId]['UF_VOLUME'])
            || empty($counter_req_data[$offerId]['UF_VOLUME'])
        ){
            echo 1;
            exit;
        }

//        $nds = self::getNds($clientId);  //получить НДС покупателя
        /*$offerData = farmer::getOfferById($offerId);
        $nds = ($offerData['USER_NDS'] == 'yes'); //НДС берется всегда поставщика
        $check_region = self::getRegionByWh($WhId);
        $check_linked_regions = getLinkedRegions($check_region);
        $market_prices = self::getMarketCulturesPrices($cultureId, $WhId, $nds);
        if(!isset($market_prices[$yesterday])
            && count($check_linked_regions) > 0
        ){
            //если нет данных по региону, получаем данные по связанным регионам средние значение
            $market_prices = self::getMarketCulturesPrices($cultureId, $WhId, $nds, true);
        }
        $average_prices = self::getReqAveragePrices($cultureId, $check_region, $nds);
        if(!isset($average_prices[$yesterday])
            && count($check_linked_regions) > 0
        ){
            //если нет данных по региону, получаем данные по связанным регионам средние значение
            $average_prices = self::getReqAveragePrices($cultureId, $check_linked_regions, $nds, true);
        }*/
        //$offerPrice = self::getOfferPrices($reqId,$offerId,$nds);
        $text .= $culture_name['NAME'] . ', ' . $counter_req_data[$offerId]['UF_VOLUME'] . ' т (' . ($additional_value['offer_nds'] == 'y' ? 'с' : 'без') . ' НДС):<br/><br/>';
        $text .= 'Цена "с места" (FCA): ' . $additional_value['offer_csmprice'] . ' руб/т<br/><br/>';

        //параметры качества
        $offer_unit_info = array();
        $offer_params_info = array();
        $offer_lbase_info = array();
        $offer_params = farmer::getParamsList($offerId);
        $res = CIBlockElement::GetList(
            array('SORT' => 'ASC', 'ID' => 'ASC'),
            array('IBLOCK_ID' => rrsIblock::getIBlockId('quality'), 'ACTIVE' => 'Y'),
            false,
            false,
            array('ID', 'NAME', 'PROPERTY_UNIT')
        );
        while ($ob = $res->Fetch()) {
            $offer_unit_info[$ob['ID']] = $ob['PROPERTY_UNIT_VALUE'];
        }
        $res = CIBlockElement::GetList(
            array('ID' => 'DESC'),
            array('IBLOCK_ID' => rrsIblock::getIBlockId('characteristics'), 'ACTIVE' => 'Y'),
            false,
            false,
            array('ID', 'PROPERTY_CULTURE', 'PROPERTY_QUALITY', 'PROPERTY_QUALITY.NAME')
        );
        while ($ob = $res->Fetch()){
            $offer_params_info[$ob['PROPERTY_CULTURE_VALUE']][$ob['PROPERTY_QUALITY_VALUE']] = array(
                'ID' => $ob['ID'],
                'QUALITY_NAME' => $ob['PROPERTY_QUALITY_NAME']
            );
        }
        $res = CIBlockElement::GetList(
            array('SORT' => 'ASC', 'ID' => 'ASC'),
            array('IBLOCK_ID' => rrsIblock::getIBlockId('basis_values'), 'ACTIVE' => 'Y'),
            false,
            false,
            array('ID', 'NAME', 'PROPERTY_QUALITY', 'PROPERTY_CULTURE')
        );
        while ($ob = $res->Fetch()) {
            foreach ($ob['PROPERTY_CULTURE_VALUE'] as $cur_culture) {
                $offer_lbase_info[$cur_culture][$ob['PROPERTY_QUALITY_VALUE']][$ob['ID']] = $ob['NAME'];
            }
        }

        //if($counter_req_data[$offerId]['UF_PARTNER_Q_APRVD']) {
            $text .= 'Качество' . ($counter_req_data[$offerId]['UF_PARTNER_Q_APRVD'] ? ' (подтверждено лабораторией)' : '') . ':<br/>';
        //}
        $sParamsText = '';
        foreach ($offer_params[$offerId] as $param) {
            if(isset($offer_params_info[$cultureId][$param['QUALITY_ID']]['QUALITY_NAME'])){
                //$text .= ' ' . $offer_params_info[$cultureId][$param['QUALITY_ID']]['QUALITY_NAME'] . ': ';
                $sParamsText .= ' &nbsp; ' . $offer_params_info[$cultureId][$param['QUALITY_ID']]['QUALITY_NAME'] . ': ';
                if($param['LBASE_ID'] > 0){
                    //$text .= $offer_lbase_info[$cultureId][$param['QUALITY_ID']][$param['LBASE_ID']] . '<br/>';
                    $sParamsText .= $offer_lbase_info[$cultureId][$param['QUALITY_ID']][$param['LBASE_ID']] . '<br/>';
                }else{
                    //$text .= $param['BASE'] . ($offer_unit_info[$param['QUALITY_ID']] != '' ? ' ' . $offer_unit_info[$param['QUALITY_ID']] : '') . '<br/>';
                    $sParamsText .= $param['BASE'] . ($offer_unit_info[$param['QUALITY_ID']] != '' ? ' ' . $offer_unit_info[$param['QUALITY_ID']] : '') . '<br/>';
                }
            }
        }
        $text .= $sParamsText . '<br/>';

        $text .= 'До склада ' . str_replace(array('\'', '""'), '&quot;', $wh_name)
            . ' (' . $additional_value['offer_tarif_distance'] . ') за ' . $additional_value['offer_tarif'] . ' руб/т'
            . (
                $additional_value['offer_dump']
                    ? (
                        $additional_value['offer_dump'] > 0
                            ? ',<br/>прибавка за качество ' . $additional_value['offer_dump'] . ' руб/т '
                            : ',<br/>сброс за качество ' . abs($additional_value['offer_dump']) . ' руб/т '
                    )
                        . '→ прогноз CPT: ' . $additional_value['offer_base_price'] . ' руб/т<br/><br/>'
                    : '<br/>→ прогноз CPT: ' . $additional_value['offer_base_price'] . ' руб/т<br/><br/>'
            );
        $sServices = '';
        if($counter_req_data[$offerId]['UF_COFFER_TYPE'] == 'p'){
            //если агентское предложение, то также выводим выбранные опции
            $agent_ops = json_decode($counter_req_data[$offerId]['UF_ADDIT_FIELDS'], true);
            $agent_ops_names = array(
                'IS_ADD_CERT' => 'Отбор проб и лабораторная диагностика',
                'IS_BILL_OF_HEALTH' => 'Карантинное свидетельство',
                'IS_VET_CERT' => 'Ветеринарные свидетельства',
                'IS_QUALITY_CERT' => 'Декларация о соответствии',
                'IS_TRANSFER' => 'Транспортировка',
                'IS_SECURE_DEAL' => 'Безопасная сделка',
                'IS_AGENT_SUPPORT' => 'Сопровождение сделки'
            );

            $text .= 'Услуги Агрохелпера (Заключение договора';
            foreach($agent_ops as $cur_code => $cur_val){
                if($cur_val
                    && isset($agent_ops_names[$cur_code])
                ){
                    $sServices .= ', ' . $agent_ops_names[$cur_code];
                    //$text .= ', ' . $agent_ops_names[$cur_code];
                }
            }
            $text .= $sServices;
            $text .= ') - ' . $counter_req_data[$offerId]['UF_PARTNER_PRICE'] . ' руб (' . round($counter_req_data[$offerId]['UF_PARTNER_PRICE'] / $counter_req_data[$offerId]['UF_VOLUME']) . ' руб/т)<br/>'
                . ($bIsOtherOffer ? 'Рассмотрите другое предложение ниже: ' : 'Если интересно, примите пару по ссылке: ')
                . ($bMakeHref ? '<a href="' . $link . '">' . $link . '</a>' : $link);
        }else{
            $counter_opens = client::openerCountGet($clientId);
            if($counter_opens > 0) {
                $text .= 'Контакт агропроизводителя, готового продать<br/>'
                    . 'Получить: ' . ($bMakeHref ? '<a href="' . $link . '">' . $link . '</a>' : $link);
            }else{
                $text .= 'Контакт агропроизводителя, готового продать - ' . rrsIblock::getConst('counter_req_price') . ' руб.<br/>'
                    . ($bIsOtherOffer ? 'Рассмотрите другое предложение ниже: ' : 'Если интересно, примите пару по ссылке: ')
                    . ($bMakeHref ? '<a href="' . $link . '">' . $link . '</a>' : $link);
            }
        }

        //проверяем есть ли данные по рынку
        /*if(isset($market_prices[$yesterday])){
            //если есть данные за вчерашний день
            $text.='Рынок: '.number_format($market_prices[$yesterday], 0, ',', ' ').' руб/т';
            if(isset($market_prices[$before_yesterday])){
                $diff = $market_prices[$yesterday] - $market_prices[$before_yesterday];
                if($diff>0){
                    $text.=', (+'.number_format($diff, 0, ',', ' ').' руб/т)';
                }elseif($diff<0){
                    $text.=', ('.number_format($diff, 0, ',', ' ').' руб/т)';
                }
            }
            $text.="<br>";
        }
        if(isset($average_prices[$yesterday])){
            //если есть данные за вчерашний день
            $text.='Средний спрос на рынке: '.number_format($average_prices[$yesterday], 0, ',', ' ').' руб/т';
            if(isset($average_prices[$before_yesterday])){
                $diff = $average_prices[$yesterday] - $average_prices[$before_yesterday];
                if($diff>0){
                    $text.=', (+'.number_format($diff, 0, ',', ' ').' руб/т)';
                }elseif($diff<0){
                    $text.=', ('.number_format($diff, 0, ',', ' ').' руб/т)';
                }
            }
            $text.="<br><br>";
        }*/

        $sTemplateText = '';
        if($bIsOtherOffer){
            $sTemplateText = popupTemplates::getOrgOtherCounterRequestClientTemplate();
        }else{
            $sTemplateText = popupTemplates::getOrgCounterRequestClientTemplate();
        }
        //если есть шаблон, то берем данные по нему, иначе на всякий случай возвращаем данные в старом виде
        if($sTemplateText != ''){
            $text = str_replace(array(
                '#ORG_CREQ_CULTURE#',
                '#ORG_CREQ_VOL#',
                '#ORG_CREQ_NDS#',
                '#ORG_CREQ_CSMPRICE#',
                '#ORG_CREQ_QUALITY_APPROVED#',
                '#ORG_CREQ_PARAM_LIST#',
                '#ORG_CREQ_WH_NAME#',
                '#ORG_CREQ_WH_ROUTE#',
                '#ORG_CREQ_WH_ROUTE_TARIF#',
                '#ORG_CREQ_SBROS#',
                '#ORG_CREQ_BASEPRICE#',
                '#ORG_CREQ_SERVICES#',
                '#ORG_CREQ_HREF#',
                '#ORG_CREQ_AGENTPRICE#',
                '#ORG_CREQ_AGENTPRICE_PER_TON#',
            ), array(
                $culture_name['NAME'],
                $counter_req_data[$offerId]['UF_VOLUME'],
                ($additional_value['offer_nds'] == 'y' ? 'с НДС' : 'без НДС'),
                $additional_value['offer_csmprice'],
                ($counter_req_data[$offerId]['UF_PARTNER_Q_APRVD'] ? ' (подтверждено лабораторией)' : ''),
                $sParamsText,
                str_replace(array('\'', '""'), '&quot;', $wh_name),
                $additional_value['offer_tarif_distance'],
                $additional_value['offer_tarif'],
                ($additional_value['offer_dump']
                    ? (
                    $additional_value['offer_dump'] > 0
                        ? ',<br/>прибавка за качество ' . $additional_value['offer_dump'] . ' руб/т '
                        : ',<br/>сброс за качество ' . abs($additional_value['offer_dump']) . ' руб/т '
                    )
                    : '<br/>'
                ),
                $additional_value['offer_base_price'],
                $sServices,
                ($bMakeHref ? '<a href="' . $link . '">' . $link . '</a>' : $link),
                $counter_req_data[$offerId]['UF_PARTNER_PRICE'],
                round($counter_req_data[$offerId]['UF_PARTNER_PRICE'] / $counter_req_data[$offerId]['UF_VOLUME'])
            ), $sTemplateText);
        }

        return $text;
    }

    /*
     * Получение связи регионов и складов пользователя (с учетом связанных регионов)
     * @param mixed $clientId - ID клиента/клиентов
     * @return array - ID регионов складов и связанных регионов
    */
    public static function getAllRegionsWithWHsLink($clientId){
        $result = array();

        CModule::IncludeModule('iblock');
        $wh_regions = array();
        $res = CIBlockElement::GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('client_warehouse'),
                'ACTIVE' => 'Y',
                'PROPERTY_ACTIVE' => rrsIblock::getPropListKey('client_warehouse', 'ACTIVE', 'yes'),
                'PROPERTY_CLIENT' => $clientId,
                '!PROPERTY_REGION' => false
            ),
            false,
            false,
            array('ID', 'PROPERTY_REGION')
        );
        while($data = $res->Fetch()){
            $wh_regions[$data['ID']] = $data['PROPERTY_REGION_VALUE'];
        }

        //получаем связанные регионы для выбранных регионов
        if(count($wh_regions) >  0){
            $res = CIBlockElement::GetList(
                array('ID' => 'ASC'),
                array(
                    'IBLOCK_ID' => rrsIblock::getIBlockId('linked_regions'),
                    'ACTIVE' => 'Y',
                    'PROPERTY_REGION' => $wh_regions
                ),
                false,
                false,
                array('PROPERTY_REGION', 'PROPERTY_LINKED')
            );
            while($data = $res->Fetch()){
                if(!empty($data['PROPERTY_LINKED_VALUE'])){
                    foreach ($wh_regions as $cur_wh_id => $cur_reg_id){
                        if($cur_reg_id == $data['PROPERTY_REGION_VALUE']){
                            $result[$cur_reg_id][] = $cur_wh_id;
                            foreach($data['PROPERTY_LINKED_VALUE'] as $cur_linked_reg_id){
                                $result[$cur_linked_reg_id][] = $cur_wh_id;
                            }
                        }
                    }
                }
            }
        }

        return $result;
    }

    /*
     * Проверяем наличие предложений у покупателя|покупателей (множ. для организатора)
     * @param int $clientId - ID покупателя
     * @return Boolean - признак наличия предложений
    */
    public static function checlAvailableOffers($clientId){
        $result = false;

        CModule::IncludeModule('highloadblock');
        $hlblock = \Bitrix\Highloadblock\HighloadBlockTable::getById(rrsIblock::HLgetIBlockId('COUNTEROFFERS'))->fetch();
        $entity = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlblock);
        $entityDataClass = $entity->getDataClass();
        $el_obj = new $entityDataClass;

        $arFilter = array(
            'count_total' => true,
            'order' => array('ID' => 'ASC'),
            'select' => array('ID'),
            'filter' => array('UF_CLIENT_ID' => $clientId),
            'limit' => 1
        );

        $res = $el_obj->getList($arFilter);
        if($res->getCount() > 0){
            $result = true;
        }

        return $result;
    }

    /*
     * Получение/генерирование ссылки на главную страницу с авторизацией пользователя
     * @param int $iClientId - ID покупателя
     * @return string - ссылка на главную страницу с авторизацией пользователя
    */
    public static function getStraightHrefMain($iClientId){
        $sResult = '';
        $sTargetPage = '/client/';

        $obRes = CIBlockElement::GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('straight_href'),
                'PROPERTY_TARGET_USER' => $iClientId,
                'PROPERTY_TARGET_URL' => $sTargetPage,
            ),
            false,
            array('nTopCount' => 1),
            array('PROPERTY_CHECK_CODE')
        );
        if($arData = $obRes->Fetch()){
            if(!empty($arData['PROPERTY_CHECK_CODE_VALUE'])) {
                $sResult = $GLOBALS['host'] . '?spec_href=' . $arData['PROPERTY_CHECK_CODE_VALUE'];
            }
        }

        //если готовой ссылки нет - создаем её
        if($sResult == ''){
            $iAuthorId = 0;
            global $USER;
            if($USER->IsAuthorized()){
                $iAuthorId = $USER->GetID();
            }
            $sResult = generateStraightHref($iAuthorId, $iClientId, 'c', 0, 0, '', $sTargetPage);
        }

        return $sResult;
    }

    /*
     * Получение другого предложения для запроса (лучшего из предложенных) - текст со ссылкой на другую страницу
     * @param int $iRequestId - ID запроса
     * @return string - текст со ссылкой на другую страницу
    */
    public static function getOtherCounterRequestHrefByRequest($iRequestId){
        $sResult = '';

        $arItems = array();
        $arAdditionalFilter = array();
        $arRequestData = client::getRequestById($iRequestId);
        $iUserId = $arRequestData['CLIENT_ID'];

        //получаем данные для фильтрования
        $arAdditionalFilter['culture_id'] = $arRequestData['CULTURE_ID'];
        if(
            isset($arRequestData['COST'])
            && is_array($arRequestData['COST'])
        ){
            $arTemp = array_keys($arRequestData['COST']);
            if(isset($arTemp[0])){
                $arAdditionalFilter['warehouse_id'] = $arTemp[0];
            }
        }

        //получаем предложения
        $arItems = client::getCounterRequestData($iUserId, $arAdditionalFilter);
        $arAdditionalData = array();

        //сортируем по отклонению от цены запроса
        $arFilter = array();
        foreach($arItems as $cur_counter_request) {
            $arFilter['UF_OFFER_ID'][] = $cur_counter_request['UF_OFFER_ID'];
            $arFilter['UF_REQUEST_ID'][] = $cur_counter_request['UF_REQUEST_ID'];
        }
        if(count($arFilter) > 0){
            $arLeads = lead::getLeadList($arFilter);
            if(count($arLeads) > 0){
                $arLeadsKeys = array();
                foreach($arLeads as $cur_pos => $curLead){
                    $arLeadsKeys[$curLead['UF_OFFER_ID'] . '_' . $curLead['UF_FARMER_WH_ID'] . '_' . $curLead['UF_REQUEST_ID'] . '_' . $curLead['UF_CLIENT_WH_ID']] = $cur_pos;
                }

                $offers_data = farmer::getOfferListByIDs($arFilter['UF_OFFER_ID']);
                $requests_data = client::getRequestListByIDs($arFilter['UF_REQUEST_ID'], false, true);
                if(count($offers_data) > 0
                    && count($requests_data) > 0
                ){
                    $arCulturesGroup = culture::getCulturesGroup();
                    $arAgrohelperTariffs = model::getAgrohelperTariffs();
                    $nds = rrsIblock::getConst('nds');
                    $commissionVal = rrsIblock::getConst('commission');

                    //параметры товара (примеси, стекловидность и т.д.)
                    $arResult['OFFER_PARAMS'] = farmer::getParamsList($arFilter['UF_OFFER_ID']);

                    //прогноз сброса, прогноз тарифа
                    foreach($arItems as $cur_pos => $cur_counter_request){
                        $lead_key = $cur_counter_request['UF_OFFER_ID'] . '_' . $cur_counter_request['UF_FARMER_WH_ID']
                            . '_' . $cur_counter_request['UF_REQUEST_ID'] . '_' . $cur_counter_request['UF_CLIENT_WH_ID'];

                        if(isset($arLeadsKeys[$lead_key])
                            && isset($offers_data[$cur_counter_request['UF_OFFER_ID']])
                            && isset($requests_data[$cur_counter_request['UF_REQUEST_ID']])
                        ) {
                            $lead = $arLeads[$arLeadsKeys[$lead_key]];
                            $discount = deal::getDump($requests_data[$cur_counter_request['UF_REQUEST_ID']]['PARAMS'], $offers_data[$cur_counter_request['UF_OFFER_ID']]['PARAMS']);

                            //тариф всегда берется как fca, расчет всегда идет как dap
                            $tarif = client::getTarif($requests_data[$cur_counter_request['UF_REQUEST_ID']]['CLIENT_ID'], $arCulturesGroup[$requests_data[$cur_counter_request['UF_REQUEST_ID']]['CULTURE_ID']], 'fca', $lead['UF_CENTER_ID'], $lead['UF_ROUTE'], $arAgrohelperTariffs);
                            $arTariffRange = client::getTariffRange($lead['UF_ROUTE'], $arAgrohelperTariffs);

                            $best_price_data = lead::makeBaseFromCSM($cur_counter_request['UF_FARMER_PRICE'], $requests_data[$cur_counter_request['UF_REQUEST_ID']]['USER_NDS'] == 'yes', $offers_data[$cur_counter_request['UF_OFFER_ID']]['USER_NDS'] == 'yes', $discount, $tarif, array('delivery_type' => 'cpt', 'get_base_client' => true, 'nds' => $nds, 'comissionVal' => $commissionVal), true);

                            if (isset($best_price_data['BASE_PRICE'])) {
                                $arAdditionalData[$cur_pos]['BASE_PRICE'] = $best_price_data['BASE_PRICE'];

                                if (isset($requests_data[$cur_counter_request['UF_REQUEST_ID']]['COST'][$cur_counter_request['UF_CLIENT_WH_ID']]['DDP_PRICE_CLIENT'])) {
                                    $arAdditionalData[$cur_pos]['CLIENT_BASE_PRICE'] = $requests_data[$cur_counter_request['UF_REQUEST_ID']]['COST'][$cur_counter_request['UF_CLIENT_WH_ID']]['DDP_PRICE_CLIENT'];
                                    $temp_client_base_price = $arAdditionalData[$cur_pos]['CLIENT_BASE_PRICE'];
                                    $temp_farmer_base_price = $arAdditionalData[$cur_pos]['BASE_PRICE'];

                                    if ($cur_counter_request['UF_NDS_CLIENT'] == 'no'
                                        && $cur_counter_request['UF_NDS_FARMER'] == 'yes'
                                    ) {
                                        //добавляем НДС к цене
                                        $temp_client_base_price = $temp_client_base_price + ($temp_client_base_price * 0.01 * $nds);
                                        $temp_farmer_base_price = $best_price_data['BASE_CONTR_PRICE'];
                                    } elseif ($cur_counter_request['UF_NDS_CLIENT'] == 'yes'
                                        && $cur_counter_request['UF_NDS_FARMER'] == 'no'
                                    ) {
                                        //вычитаем НДС из цены
                                        $temp_client_base_price = $temp_client_base_price / (1 + 0.01 * $nds);
                                        $temp_farmer_base_price = $best_price_data['BASE_CONTR_PRICE'];
                                    }
                                    $arAdditionalData[$cur_pos]['DIFFERENCE'] = round($temp_farmer_base_price) - round($temp_client_base_price);
                                }

                                $arAdditionalData[$cur_pos]['TARIF'] = $tarif;
                                $arAdditionalData[$cur_pos]['TARIFF_RANGE'] = ($arTariffRange['FROM'] > 0 ? $arTariffRange['FROM'] . '-' : 'до ')
                                    . $arTariffRange['TO'] . ' км';
                            }

                            //если типы НДС поставщика и покупателя разнятся, то сохраняем также данные для поставщика
                            $arAdditionalData[$cur_pos]['FARMER_DUMP_RUB'] = -1 * intval($best_price_data['DUMP_RUB']);
                            $arAdditionalData[$cur_pos]['FARMER_BASE_CONTR_PRICE'] = $best_price_data['BASE_CONTR_PRICE'];
                        }
                    }
                }
            }
        }
        $arItemsDiff = array();
        foreach ($arItems as $iPos => $arItem){
            if($arAdditionalData[$iPos]['DIFFERENCE']){
                $arItemsDiff[$iPos] = $arAdditionalData[$iPos]['DIFFERENCE'];
            }else{
                $arItemsDiff[$iPos] = 0;
            }
        }
        asort($arItemsDiff);
        $arrNewAdditionalData = array();
        $arrNewItems = array();
        $i = 0;
        foreach($arItemsDiff as $iPos => $iDif){
            if(isset($arItems[$iPos])){
                $arrNewItems[$i] = $arItems[$iPos];
            }
            if(isset($arAdditionalData[$iPos])){
                $arrNewAdditionalData[$i] = $arAdditionalData[$iPos];
            }
            $i++;
        }
        if(
            count($arrNewAdditionalData) == count($arAdditionalData)
            && count($arrNewItems) == count($arItems)
        ){
            $arItems = $arrNewItems;
            $arAdditionalData = $arrNewAdditionalData;
        }

        if(isset($arItems[0]['UF_OFFER_ID'])){
            $arrAdditional = array();
            $arrAdditional['offer_csmprice'] = $arItems[0]['UF_FARMER_PRICE'];
            $arrAdditional['offer_nds'] = ($arItems[0]['UF_NDS_FARMER'] == 'yes' ? 'y' : 'n');
            $arrAdditional['offer_csm_addittext'] = '"с места"/(FCA)';
            $arrAdditional['offer_tarif'] = trim($arAdditionalData[0]['TARIF']);
            $arrAdditional['offer_tarif_distance'] = trim($arAdditionalData[0]['TARIFF_RANGE']);
            $arrAdditional['offer_dump'] = $arAdditionalData[0]['FARMER_DUMP_RUB'];
            $arrAdditional['offer_base_price'] = $arAdditionalData[0]['FARMER_BASE_CONTR_PRICE'];
            $arrAdditional['offer_delivery_type'] = 'CPT';
            $arrAdditional['offer_agentfullprice'] = $arItems[0]['UF_PARTNER_PRICE'];
            $arrAdditional['offer_agentprice'] = round($arItems[0]['UF_PARTNER_PRICE'] / $arItems[0]['UF_VOLUME']);

            //генерируем текст для отображения на странице
            $sTargetUrl = '/client/exclusive_offers/?warehouse_id=' . $arItems[0]['UF_CLIENT_WH_ID']
                . '&culture_id=' . $arRequestData['CULTURE_ID']
                . '&r=' . $arRequestData['ID']
                . '&o=' . $arItems[0]['UF_OFFER_ID']
                . '&cid=' . $arItems[0]['ID'];

            $iUid = 0;
            global $USER;
            if($USER->IsAuthorized()){
                $iUid = $USER->GetID();
            }
            $sHrefVal = generateStraightHref($iUid, $arRequestData['CLIENT_ID'], 'c', '', '', '', $sTargetUrl, '/pair_page/');
            if($sHrefVal != ''){
                $sResult = client::getClientTextToCOfferLink($arRequestData['CLIENT_ID'], $arRequestData['ID'], $arItems[0]['UF_OFFER_ID'], $arRequestData['CULTURE_ID'], $arItems[0]['UF_CLIENT_WH_ID'], $sHrefVal, $arrAdditional, true, true);
            }
        }

        return $sResult;
    }

    /*
     * Получение ID покупателя по ID запроса
     * @param int $iRequest - ID запроса
     * @return int - ID покупателя
    */
    public static function getUserIdByRequest($iRequest){
        $iResult = 0;

        $obRes = CIBlockElement::GetList(
            array('ID' => 'DESC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('client_request'),
                'ID' => $iRequest
            ),
            false,
            array('nTopcCount' => 1),
            array('PROPERTY_CLIENT')
        );
        if($arData = $obRes->Fetch()){
            if(!empty($arData['PROPERTY_CLIENT_VALUE'])) {
                $iResult = $arData['PROPERTY_CLIENT_VALUE'];
            }
        }

        return $iResult;
    }

    /*
     * Проверка наличия хотя бы одного предложения для пользователей
     * @param array | int $arrUsers - ID покупателей
     * @return boolean
    */
    public static function isUsersCounterRequestsAvailable($arrUsers){
        $bResult = false;

        if(filter_var($arrUsers, FILTER_VALIDATE_INT)){
            $arrUsers = array($arrUsers);
        }
        if(
            is_array($arrUsers)
            && count($arrUsers) > 0
        ){
            $log_obj = new log;
            $entityDataClass = $log_obj->getEntityDataClass(rrsIblock::HLgetIBlockId('COUNTEROFFERS'));
            $el = new $entityDataClass;

            //получение записей, где нужные склады стоят на первой позиции
            $res = $el->getList(array(
                'select' => array('ID'),
                'filter' => array(
                    'UF_CLIENT_ID' => $arrUsers,
                ),
                'order' => array('ID'=>'ASC'),
                'limit' => 1
            ));
            if($data = $res->fetch()) {
                $bResult = true;
            }
        }

        return $bResult;
    }

    /*
    * Получение списка телефонов для выбранных покупателей
     * @param array $arrClients - массив ID покупателей
     * @return array - массив данных, где ключи - ID покупателей, а значения -  массив телефонов
    */
    public static function getPhoneList($arrClients){
        $arrResult = array();

        if(
            is_array($arrClients)
            && count($arrClients) > 0
        ){
            $obRes = CIBlockElement::GetList(
                array('ID' => 'ASC'),
                array(
                    'IBLOCK_ID' => rrsIblock::getIBlockId('client_profile'),
                    'ACTIVE' => 'Y',
                    'PROPERTY_USER' => $arrClients,
                    '!PROPERTY_PHONE' => false,
                ),
                false,
                false,
                array('PROPERTY_PHONE', 'PROPERTY_USER')
            );
            while($arrData = $obRes->Fetch()){
                $arrResult[$arrData['PROPERTY_USER_VALUE']] = $arrData['PROPERTY_PHONE_VALUE'];
            }
        }

        return $arrResult;
    }
}