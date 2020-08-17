<?
class farmer {

    /**
     * Получение товара
     * @param int $id идентификатор товара
     * @param bool $params  - возвращать ли параметры
     * @param bool $profile - возвращат ли данные профиля фермера
     * @return [] массив с информацией о товаре
     */
    public static function getOfferById($id,$params = true, $profile = true) {
        CModule::IncludeModule('iblock');
        $result = array();
        $res = CIBlockElement::GetList(
            array('ID' => 'DESC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('farmer_offer'),
                'ACTIVE' => 'Y',
                'PROPERTY_ACTIVE' => rrsIblock::getPropListKey('farmer_offer', 'ACTIVE', 'yes'),
                'ID' => $id
            ),
            false,
            false,
            array(
                'ID',
                'NAME',
                'DATE_CREATE',
                'PROPERTY_FARMER',
                'PROPERTY_USER_NDS',
                'PROPERTY_CULTURE',
                'PROPERTY_CULTURE.NAME',
                'PROPERTY_WAREHOUSE',
                'PROPERTY_WAREHOUSE.PROPERTY_ADDRESS',
                'PROPERTY_WAREHOUSE.PROPERTY_MAP',
                'PROPERTY_WAREHOUSE.PROPERTY_REGION',
                'PROPERTY_WAREHOUSE.NAME',
                'PROPERTY_Q_APPROVED',
                'PROPERTY_Q_APPROVED_DATA',
            )
        );
        if ($ob = $res->Fetch()) {
            $tmp = array(
                'ID' => $ob['ID'],
                'NAME' => $ob['NAME'],
                'DATE_CREATE' => $ob['DATE_CREATE'],
                'FARMER_ID' => $ob['PROPERTY_FARMER_VALUE'],
                'USER_NDS' => rrsIblock::getPropListId('farmer_offer', 'USER_NDS', $ob['PROPERTY_USER_NDS_ENUM_ID']),
                'CULTURE_ID' => $ob['PROPERTY_CULTURE_VALUE'],
                'CULTURE_NAME' => $ob['PROPERTY_CULTURE_NAME'],
                'WH_NAME' => $ob['PROPERTY_WAREHOUSE_NAME'],
                'WH_ID' => $ob['PROPERTY_WAREHOUSE_VALUE'],
                'WH_ADDRESS' => $ob['PROPERTY_WAREHOUSE_PROPERTY_ADDRESS_VALUE'],
                'WH_MAP' => $ob['PROPERTY_WAREHOUSE_PROPERTY_MAP_VALUE'],
                'WH_REGION' => $ob['PROPERTY_WAREHOUSE_PROPERTY_REGION_VALUE'],
                'Q_APPROVED' => $ob['PROPERTY_Q_APPROVED_VALUE'],
                'Q_APPROVED_DATA' => $ob['PROPERTY_Q_APPROVED_DATA_VALUE'],
            );
            $result = $tmp;
            if($params === true){
                $result['PARAMS'] = current(self::getParamsList(array($id)));
            }
            if($profile === true){
                $result['PROFILE'] = self::getProfile($result['FARMER_ID'], true);
            }

        }

        return $result;
    }

    /**
     * Получить ID поставщика по ID товара
     * @param $offer_id
     */
    public static function getOfferFarmer($offer_id){
        CModule::IncludeModule('iblock');
        $result = 0;
        $res = CIBlockElement::GetList(
            array('ID' => 'DESC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('farmer_offer'),
                'ACTIVE' => 'Y',
                'PROPERTY_ACTIVE' => rrsIblock::getPropListKey('farmer_offer', 'ACTIVE', 'yes'),
                'ID' => $offer_id
            ),
            false,
            false,
            array(
                'ID',
                'PROPERTY_FARMER',
            )
        );
        if ($ob = $res->Fetch()) {
            $result = $ob['PROPERTY_FARMER_VALUE'];
        }
        return $result;
    }

    /**
     * Получить названия культур для выбранных товаров
     * @param array $arOffersIds
     * @return array названий культур товаров
     */
    public static function getCultureNamesByOffers($arOffersIds){
        $arResult = array();

        if(
            is_array($arOffersIds)
            && count($arOffersIds) > 0
        ) {
            $obRes = CIBlockElement::GetList(
                array('ID' => 'DESC'),
                array(
                    'IBLOCK_ID' => rrsIblock::getIBlockId('farmer_offer'),
                    'ACTIVE' => 'Y',
                    'PROPERTY_ACTIVE' => rrsIblock::getPropListKey('farmer_offer', 'ACTIVE', 'yes'),
                    'ID' => $arOffersIds
                ),
                false,
                false,
                array(
                    'ID',
                    'PROPERTY_CULTURE.NAME',
                )
            );
            while ($arData = $obRes->Fetch()) {
                if($arData['PROPERTY_CULTURE_NAME']) {
                    $arResult[$arData['ID']] = $arData['PROPERTY_CULTURE_NAME'];
                }
            }
        }

        return $arResult;
    }

    /**
     * Получение списка товаров, отобранных по критерию "культура"
     * @param  [] $cultureIds список идентификаторов культур
     * @return [] массив со списком товаров
     */
    public static function getOfferList($cultureIds) {
        CModule::IncludeModule('iblock');
        $result = array();
        $res = CIBlockElement::GetList(
            array('ID' => 'DESC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('farmer_offer'),
                'ACTIVE' => 'Y',
                'PROPERTY_ACTIVE' => rrsIblock::getPropListKey('farmer_offer', 'ACTIVE', 'yes'),
                'PROPERTY_CULTURE' => $cultureIds
            ),
            false,
            false,
            array(
                'ID',
                'NAME',
                'DATE_CREATE',
                'PROPERTY_FARMER',
                'PROPERTY_USER_NDS',
                'PROPERTY_CULTURE',
                'PROPERTY_WAREHOUSE',
                'PROPERTY_WAREHOUSE.PROPERTY_ADDRESS',
                'PROPERTY_WAREHOUSE.PROPERTY_MAP',
                'PROPERTY_WAREHOUSE.NAME'
            )
        );
        while ($ob = $res->Fetch()) {
            $tmp = array(
                'ID' => $ob['ID'],
                'NAME' => $ob['NAME'],
                'DATE_CREATE' => $ob['DATE_CREATE'],
                'FARMER_ID' => $ob['PROPERTY_FARMER_VALUE'],
                'USER_NDS' => rrsIblock::getPropListId('farmer_offer', 'USER_NDS', $ob['PROPERTY_USER_NDS_ENUM_ID']),
                'CULTURE_ID' => $ob['PROPERTY_CULTURE_VALUE'],
                'WH_NAME' => $ob['PROPERTY_WAREHOUSE_NAME'],
                'WH_ID' => $ob['PROPERTY_WAREHOUSE_VALUE'],
                'WH_ADDRESS' => $ob['PROPERTY_WAREHOUSE_PROPERTY_ADDRESS_VALUE'],
                'WH_MAP' => $ob['PROPERTY_WAREHOUSE_PROPERTY_MAP_VALUE'],
            );
            $result[$ob['ID']] = $tmp;
        }

        $offerParams = farmer::getParamsList(array_keys($result));
        foreach ($result as $key => $offer) {
            $result[$key]['PARAMS'] = $offerParams[$key];
        }

        return $result;
    }

    /**
     * Получение списка товаров, отобранных по критерию "культура" и в регионах связанных с выбранным регионом
     * @param  [] $cultureIds список идентификаторов культур
     * @param  mixed $regionId id рассматриваемого региона/регионов
     * @param  boolean $search_linked_regions признак того нужно ли искать в связанных регионах (по умолчанию - да)
     * @return [] массив со списком товаров
     */
    public static function getOfferListWithRegion($cultureIds, $regionId, $search_linked_regions = true) {
        $result = array();

        //получение складов товаров для связанных регионов с регионом запроса (для FCA ищем без связанных регионов, т.к. все нужные регионы уже были выбраны в форме создания запроса)
        $linkedRegionWHIds = array();
        if($search_linked_regions) {
            $linkedRegionWHIds = getWHListForRegions(getLinkedRegions($regionId), 'f');
        }else{
            $linkedRegionWHIds = getWHListForRegions(array($regionId), 'f');
        }

        if(count($linkedRegionWHIds) > 0) {
            $res = CIBlockElement::GetList(
                array('ID' => 'DESC'),
                array(
                    'IBLOCK_ID' => rrsIblock::getIBlockId('farmer_offer'),
                    'ACTIVE' => 'Y',
                    'PROPERTY_ACTIVE' => rrsIblock::getPropListKey('farmer_offer', 'ACTIVE', 'yes'),
                    'PROPERTY_CULTURE' => $cultureIds,
                    'PROPERTY_WAREHOUSE' => $linkedRegionWHIds
                ),
                false,
                false,
                array(
                    'ID',
                    'NAME',
                    'DATE_CREATE',
                    'PROPERTY_FARMER',
                    'PROPERTY_USER_NDS',
                    'PROPERTY_CULTURE',
                    'PROPERTY_WAREHOUSE',
                    'PROPERTY_WAREHOUSE.PROPERTY_ADDRESS',
                    'PROPERTY_WAREHOUSE.PROPERTY_MAP',
                    'PROPERTY_WAREHOUSE.NAME'
                )
            );
            while ($ob = $res->Fetch()) {
                $tmp = array(
                    'ID' => $ob['ID'],
                    'NAME' => $ob['NAME'],
                    'DATE_CREATE' => $ob['DATE_CREATE'],
                    'FARMER_ID' => $ob['PROPERTY_FARMER_VALUE'],
                    'USER_NDS' => rrsIblock::getPropListId('farmer_offer', 'USER_NDS', $ob['PROPERTY_USER_NDS_ENUM_ID']),
                    'CULTURE_ID' => $ob['PROPERTY_CULTURE_VALUE'],
                    'WH_NAME' => $ob['PROPERTY_WAREHOUSE_NAME'],
                    'WH_ID' => $ob['PROPERTY_WAREHOUSE_VALUE'],
                    'WH_ADDRESS' => $ob['PROPERTY_WAREHOUSE_PROPERTY_ADDRESS_VALUE'],
                    'WH_MAP' => $ob['PROPERTY_WAREHOUSE_PROPERTY_MAP_VALUE'],
                );
                $result[$ob['ID']] = $tmp;
            }

            $offerParams = farmer::getParamsList(array_keys($result));
            foreach ($result as $key => $offer) {
                $result[$key]['PARAMS'] = $offerParams[$key];
            }
        }

        return $result;
    }

    /**
     * Получение списка предложений, отобранных по критерию "культура"
     * @param array $parameters - параметры для запроса БД
     * @return array массив данных
     */
    public static function getLeadsForFilterCount($parameters) {
        $result = array();
        if(isset($parameters['UF_FARMER_ID'])
            && (
                filter_var($parameters['UF_FARMER_ID'], FILTER_VALIDATE_INT)
                || (
                    is_array($parameters['UF_FARMER_ID'])
                    && count($parameters['UF_FARMER_ID']) > 0
                )
            )
        ){
            CModule::IncludeModule('highloadblock');

            $arFilter = array(
                'UF_FARMER_ID' => $parameters['UF_FARMER_ID']
            );

            //подготовка данных для запроса к БД
            $hlblock = \Bitrix\Highloadblock\HighloadBlockTable::getById(rrsIblock::HLgetIBlockId('LEADLIST'))->fetch();
            $entity = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlblock);
            $entityDataClass = $entity->getDataClass();
            $el_obj = new $entityDataClass;

            //получение данных из HL инфоблока
            $res = $el_obj->getList(
                array(
                    'order' => array('ID'=>'ASC'),
                    'select' => array('UF_REQUEST_ID', 'UF_FARMER_ID', 'UF_CULTURE_ID', 'UF_FARMER_WH_ID', 'UF_NDS', 'UF_OFFER_ID','UF_CLIENT_ID'),
                    'filter' => $arFilter,
                )
            );
            while($data = $res->fetch()) {
                $result[] = $data;
            }
        }

        return $result;
    }

    /**
     * Массив товаров с данными по региону и культуре
     * @param $ids - массив ID товаров
     * @return array
     */
    public static function getOffersCulturesAndRegionsByIds($ids) {
        CModule::IncludeModule('iblock');
        $result = array();
        $res = CIBlockElement::GetList(
            array('ID' => 'DESC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('farmer_offer'),
                'ACTIVE' => 'Y',
                'PROPERTY_ACTIVE' => rrsIblock::getPropListKey('farmer_offer', 'ACTIVE', 'yes'),
                'ID' => $ids
            ),
            false,
            false,
            array(
                'ID',
                'PROPERTY_CULTURE',
                'PROPERTY_WAREHOUSE.PROPERTY_REGION',
                'PROPERTY_USER_NDS',
            )
        );
        $nds_code_val = rrsIblock::getPropListKey('farmer_offer', 'USER_NDS', 'yes');
        while ($ob = $res->Fetch()) {
            $result[$ob['ID']] = array(
                'ID' => $ob['ID'],
                'CULTURE_ID' => $ob['PROPERTY_CULTURE_VALUE'],
                'WH_REGION' => $ob['PROPERTY_WAREHOUSE_PROPERTY_REGION_VALUE'],
                'NDS' => ($ob['PROPERTY_USER_NDS_ENUM_ID'] == $nds_code_val),
            );
        }

        return $result;
    }


    /**
     * Получение складов и культур по ID товаров (для графика в товарах поставщика)
     * @param  array $id_arr список идентификаторов товаров
     * @param  array &$wh_list сюда будут сложены ID складов (ключ - товар, значение - склад)
     * @param  array &$cultures_list сюда будут сложены ID культур (ID кульутры являтся ключом, а значением является флаг true)
     */
    public static function getWHAndCulturesByOffers($id_arr, &$wh_list, &$cultures_list){
        CModule::IncludeModule('iblock');

        $res = CIBlockElement::GetList(
            array('ID' => 'DESC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('farmer_offer'),
                'ID' => $id_arr,
            ),
            false,
            false,
            array(
                'ID',
                'PROPERTY_WAREHOUSE',
                'PROPERTY_CULTURE'
            )
        );
        while ($data = $res->Fetch()) {
            if(is_numeric($data['PROPERTY_WAREHOUSE_VALUE'])) {
                $wh_list[$data['ID']] = $data['PROPERTY_WAREHOUSE_VALUE'];
            }
            if(is_numeric($data['PROPERTY_CULTURE_VALUE'])) {
                $cultures_list[$data['PROPERTY_CULTURE_VALUE']] = true;
            }
        }
    }

    /**
     * Получение складов других поставщиков в радиусе 300 км
     * @param  array $wh_arr список идентификаторов складов
     * @return array массив соответствий ID товаров и складов (каждый товар привязан к одному складу)
     */
    public static function getWHAtRadius($wh_arr){
        $result = array();

        if(count($wh_arr) > 0) {
            $log_obj = new log;
            $entityDataClass = $log_obj->getEntityDataClass(rrsIblock::HLgetIBlockId('FMROUTESCACHE'));
            $el = new $entityDataClass;

            //получение записей, где нужные склады стоят на первой позиции
            $res = $el->getList(array(
                'select' => array('UF_FARMER_WH_ID1', 'UF_FARMER_WH_ID2'),
                'filter' => array(
                    'UF_FARMER_WH_ID1' => $wh_arr,
                    '<UF_ROUTE' => 301
                ),
                'order' => array('ID'=>'DESC')
            ));
            while($data = $res->fetch()) {
                $result[$data['UF_FARMER_WH_ID1']][] = $data['UF_FARMER_WH_ID2'];
            }
            //получение записей, где нужные склады стоят на второй позиции
            $res = $el->getList(array(
                'select' => array('UF_FARMER_WH_ID1', 'UF_FARMER_WH_ID2'),
                'filter' => array(
                    'UF_FARMER_WH_ID2' => $wh_arr,
                    '<UF_ROUTE' => 301
                ),
                'order' => array('ID'=>'DESC')
            ));
            while($data = $res->fetch()) {
                $result[$data['UF_FARMER_WH_ID2']][] = $data['UF_FARMER_WH_ID1'];
            }
        }

        return $result;
    }

    /**
     * Получаем склады поставщиков, находящиеся в связанных регионах с регионами указанных складов
     * @param array $wh_arr список идентификаторов складов
     * @return array массив соответствий ID складов и массиваа ID складов из связанных регионов
     */
    public static function getWHAtLinkedRegions($wh_arr){
        $result = array();
        $wh_to_reg = array();
        $regions_ids = array();

        if(count($wh_arr) > 0) {
            $ib_id = rrsIblock::getIBlockId('farmer_warehouse');
            //получаем регионы складов
            CModule::IncludeModule('iblock');
            $res = CIBlockElement::GetList(
                array('ID' => 'DESC'),
                array(
                    'IBLOCK_ID' => $ib_id,
                    'ACTIVE' => 'Y',
                    'PROPERTY_ACTIVE' => rrsIblock::getPropListKey('farmer_warehouse', 'ACTIVE', 'yes'),
                    'ID' => $wh_arr
                ),
                false,
                false,
                array(
                    'ID', 'PROPERTY_REGION',
                )
            );
            while ($data = $res->Fetch()) {
                $wh_to_reg[$data['PROPERTY_REGION_VALUE']][] = $data['ID'];
            }

            //получаем связанные регионы
            if(count($wh_to_reg) > 0){
                $res = CIBlockElement::GetList(
                    array('ID' => 'DESC'),
                    array(
                        'IBLOCK_ID' => rrsIblock::getIBlockId('linked_regions'),
                        'ACTIVE' => 'Y',
                        'PROPERTY_REGION' => array_keys($wh_to_reg)
                    ),
                    false,
                    false,
                    array(
                        'PROPERTY_LINKED', 'PROPERTY_REGION',
                    )
                );
                while($data = $res->Fetch()){
                    foreach($data['PROPERTY_LINKED_VALUE'] as $cur_reg){
                        $regions_ids[$cur_reg][] = $data['PROPERTY_REGION_VALUE'];
                    }
                }
                foreach($wh_to_reg as $cur_region => $cur_data){
                    $regions_ids[$cur_region][] = $cur_region;
                }
            }

            //получаем склады по связанным регионам и привязываем их к исходным складам
            if(count($regions_ids) > 0){
                $res = CIBlockElement::GetList(
                    array('ID' => 'DESC'),
                    array(
                        'IBLOCK_ID' => $ib_id,
                        'PROPERTY_REGION' => array_keys($regions_ids)
                    ),
                    false,
                    false,
                    array(
                        'ID', 'PROPERTY_REGION',
                    )
                );
                while($data = $res->Fetch()){
                    //если есть головные регионы, к которым привязан текущий регион склада, то пробегаемся по ним и связываем текущий склад с головным регионом
                    if(isset($regions_ids[$data['PROPERTY_REGION_VALUE']])) {
                        foreach ($regions_ids[$data['PROPERTY_REGION_VALUE']] as $parent_region){
                            if (isset($wh_to_reg[$parent_region])) {
                                foreach ($wh_to_reg[$parent_region] as $cur_wh) {
                                    if ($cur_wh != $data['ID']) {
                                        $result[$cur_wh][] = $data['ID'];
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Получаем склады поставщиков, находящиеся в регионе указанного склада
     * @param array $wh_arr список идентификаторов складов
     * @return array массив соответствий ID складов и массиваа ID складов из регионов переданных складов
     */
    public static function getWHAtCurrentRegion($wh_arr){
        $result = array();
        $wh_to_reg = array();
        $regions_ids = array();

        if(count($wh_arr) > 0) {
            $ib_id = rrsIblock::getIBlockId('farmer_warehouse');
            //получаем регионы складов
            CModule::IncludeModule('iblock');
            $res = CIBlockElement::GetList(
                array('ID' => 'DESC'),
                array(
                    'IBLOCK_ID' => $ib_id,
                    'ACTIVE' => 'Y',
                    'PROPERTY_ACTIVE' => rrsIblock::getPropListKey('farmer_warehouse', 'ACTIVE', 'yes'),
                    'ID' => $wh_arr
                ),
                false,
                false,
                array(
                    'ID', 'PROPERTY_REGION',
                )
            );
            while ($data = $res->Fetch()) {
                $wh_to_reg[$data['PROPERTY_REGION_VALUE']][] = $data['ID'];
            }

            //получаем склады по найденным регионам
            if(count($wh_to_reg) > 0){
                $res = CIBlockElement::GetList(
                    array('ID' => 'DESC'),
                    array(
                        'IBLOCK_ID' => $ib_id,
                        'PROPERTY_REGION' => array_keys($wh_to_reg)
                    ),
                    false,
                    false,
                    array(
                        'ID', 'PROPERTY_REGION',
                    )
                );
                while($data = $res->Fetch()){
                    if(isset($wh_to_reg[$data['PROPERTY_REGION_VALUE']])) {
                        foreach ($wh_to_reg[$data['PROPERTY_REGION_VALUE']] as $cur_wh) {
                            if ($cur_wh != $data['ID']) {
                                $result[$cur_wh][] = $data['ID'];
                            }
                        }
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Получение только тех складов в радусе 300 км, по которым есть сделки по нужной культуре
     * @param  array $id_arr список идентификаторов складов
     * @return array массив соответствий ID товаров и складов (каждый товар привязан к одному складу)
     */
    public static function getWHByCultureAtRadius($wh_arr){
        $result = array();

        if(count($wh_arr) > 0) {
            $log_obj = new log;
            $entityDataClass = $log_obj->getEntityDataClass(rrsIblock::HLgetIBlockId('FMROUTESCACHE'));
            $el = new $entityDataClass;

            //получение записей, где нужные склады стоят на первой позиции
            $res = $el->getList(array(
                'select' => array('UF_FARMER_WH_ID1', 'UF_FARMER_WH_ID2'),
                'filter' => array(
                    'UF_FARMER_WH_ID1' => $wh_arr,
                    '<UF_ROUTE' => 301
                ),
                'order' => array('ID'=>'DESC')
            ));
            while($data = $res->fetch()) {
                $result[$data['UF_FARMER_WH_ID1']][] = $data['UF_FARMER_WH_ID2'];
            }
            //получение записей, где нужные склады стоят на второй позиции
            $res = $el->getList(array(
                'select' => array('UF_FARMER_WH_ID1', 'UF_FARMER_WH_ID2'),
                'filter' => array(
                    'UF_FARMER_WH_ID2' => $wh_arr,
                    '<UF_ROUTE' => 301
                ),
                'order' => array('ID'=>'DESC')
            ));
            while($data = $res->fetch()) {
                $result[$data['UF_FARMER_WH_ID2']][] = $data['UF_FARMER_WH_ID1'];
            }
        }

        return $result;
    }

    /**
     * Получение списка товаров, отобранных по идентификаторам
     * @param  [] $IDs список идентификаторов товаров
     * @return [] массив со списком товаров
     */
    public static function getOfferListByIDs($IDs) {
        CModule::IncludeModule('iblock');
        $result = array();
        $res = CIBlockElement::GetList(
            array('ID' => 'DESC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('farmer_offer'),
                'ACTIVE' => 'Y',
                'PROPERTY_ACTIVE' => rrsIblock::getPropListKey('farmer_offer', 'ACTIVE', 'yes'),
                'ID' => $IDs
            ),
            false,
            false,
            array(
                'ID',
                'NAME',
                'DATE_CREATE',
                'PROPERTY_FARMER',
                'PROPERTY_USER_NDS',
                'PROPERTY_CULTURE',
                'PROPERTY_CULTURE.NAME',
                'PROPERTY_WAREHOUSE',
                'PROPERTY_WAREHOUSE.PROPERTY_ADDRESS',
                'PROPERTY_WAREHOUSE.PROPERTY_MAP',
                'PROPERTY_WAREHOUSE.NAME'
            )
        );
        while ($ob = $res->Fetch()) {
            $tmp = array(
                'ID' => $ob['ID'],
                'NAME' => $ob['NAME'],
                'DATE_CREATE' => $ob['DATE_CREATE'],
                'FARMER_ID' => $ob['PROPERTY_FARMER_VALUE'],
                'USER_NDS' => rrsIblock::getPropListId('farmer_offer', 'USER_NDS', $ob['PROPERTY_USER_NDS_ENUM_ID']),
                'CULTURE_ID' => $ob['PROPERTY_CULTURE_VALUE'],
                'CULTURE_NAME' => $ob['PROPERTY_CULTURE_NAME'],
                'WH_NAME' => $ob['PROPERTY_WAREHOUSE_NAME'],
                'WH_ID' => $ob['PROPERTY_WAREHOUSE_VALUE'],
                'WH_ADDRESS' => $ob['PROPERTY_WAREHOUSE_PROPERTY_ADDRESS_VALUE'],
                'WH_MAP' => $ob['PROPERTY_WAREHOUSE_PROPERTY_MAP_VALUE'],
            );
            $result[$ob['ID']] = $tmp;
        }

        $offerParams = farmer::getParamsList(array_keys($result));
        foreach ($result as $key => $offer) {
            $result[$key]['PARAMS'] = $offerParams[$key];
        }

        return $result;
    }

    /**
     * Деактивация товаров склада
     * @param number $wh_id идентификатор склада
     * @return number число деактивированных товаров
     */
    public static function setWHOfferDeactivation($wh_id){
        CModule::IncludeModule('iblock');
        $result = 0;
        $res = CIBlockElement::GetList(
            array('ID' => 'DESC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('farmer_offer'),
                'ACTIVE' => 'Y',
                'PROPERTY_ACTIVE' => rrsIblock::getPropListKey('farmer_offer', 'ACTIVE', 'yes'),
                'PROPERTY_WAREHOUSE' => $wh_id
            ),
            false,
            false,
            array(
                'ID'
            )
        );
        $whIds = array();
        while ($ob = $res->Fetch()) {
            $prop = array(
                'ACTIVE' => rrsIblock::getPropListKey('farmer_offer', 'ACTIVE', 'no')
            );
            CIBlockElement::SetPropertyValuesEx($ob['ID'], rrsIblock::getIBlockId('farmer_offer'), $prop);
            $whIds[] = $ob['ID'];

            $result++;
        }

        if (sizeof($whIds) > 0) {
            $filter = array(
                'UF_OFFER_ID' => $whIds
            );
            $arLeads = lead::getLeadList($filter);
            if (is_array($arLeads) && sizeof($arLeads) > 0) {
                lead::deleteLeads($arLeads);
            }

            //удаление встречных предложений
            self::removeCountersByWhID($wh_id);
        }

        return $result;
    }

    /**
     * Получение списка всех товаров пользователя
     * @param  number $user_id идентификатор пользователя
     * @return [] массив со списком товаров
     */
    public static function getOfferListByUser($user_id, $recache = false) {
        CModule::IncludeModule('iblock');
        $obCache = new CPHPCache;
        $life_time = 1;
        $cache_id = 'getOfferListByUser_' . $user_id;
        $result = array();

        if (!$recache && $obCache->InitCache($life_time, $cache_id, "/")) {
            $vars = $obCache->GetVars();
            $result = $vars[$cache_id];
        }
        else {
            $res = CIBlockElement::GetList(
                array('ID' => 'DESC'),
                array(
                    'IBLOCK_ID' => rrsIblock::getIBlockId('farmer_offer'),
                    'ACTIVE' => 'Y',
                    'PROPERTY_ACTIVE' => rrsIblock::getPropListKey('farmer_offer', 'ACTIVE', 'yes'),
                    'PROPERTY_FARMER' => $user_id
                ),
                false,
                false,
                array(
                    'ID',
                    'NAME',
                    'DATE_CREATE',
                    'PROPERTY_FARMER',
                    'PROPERTY_USER_NDS',
                    'PROPERTY_CULTURE',
                    'PROPERTY_CULTURE.NAME',
                    'PROPERTY_WAREHOUSE',
                    'PROPERTY_WAREHOUSE.PROPERTY_ADDRESS',
                    'PROPERTY_WAREHOUSE.PROPERTY_MAP',
                    'PROPERTY_WAREHOUSE.PROPERTY_REGION',
                    'PROPERTY_WAREHOUSE.NAME'
                )
            );
            while ($ob = $res->Fetch()) {
                $tmp = array(
                    'ID' => $ob['ID'],
                    'DATE_CREATE' => $ob['DATE_CREATE'],
                    'FARMER_ID' => $ob['PROPERTY_FARMER_VALUE'],
                    'USER_NDS' => rrsIblock::getPropListId('farmer_offer', 'USER_NDS', $ob['PROPERTY_USER_NDS_ENUM_ID']),
                    'CULTURE_ID' => $ob['PROPERTY_CULTURE_VALUE'],
                    'CULTURE_NAME' => $ob['PROPERTY_CULTURE_NAME'],
                    'WH_NAME' => $ob['PROPERTY_WAREHOUSE_NAME'],
                    'WH_ID' => $ob['PROPERTY_WAREHOUSE_VALUE'],
                    'WH_ADDRESS' => $ob['PROPERTY_WAREHOUSE_PROPERTY_ADDRESS_VALUE'],
                    'WH_MAP' => $ob['PROPERTY_WAREHOUSE_PROPERTY_MAP_VALUE'],
                    'WH_REGION' => $ob['PROPERTY_WAREHOUSE_PROPERTY_REGION_VALUE'],
                );
                $result[$ob['ID']] = $tmp;
            }

            $offerParams = farmer::getParamsList(array_keys($result));
            foreach ($result as $key => $offer) {
                $result[$key]['PARAMS'] = $offerParams[$key];
            }
        }
        if ($recache) $obCache->Clean($cache_id, "/", $basedir = "cache");
        //если с кешем имеем пустой результат, то запускаем еще раз без кеширования
        elseif(!$recache //оставляем условие для наглядности
            && (!$result
                ||
                count($result) == 0
            )
        ){
            self::getOfferListByUser($user_id, true);
        }
        if ($obCache->StartDataCache()) $obCache->EndDataCache(array($cache_id => $result));

        return $result;
    }

    /**
     * Получение списка товаров, отобранных по поставщику
     * @param  [] $user_id поставщик
     * @return [] массив со списком товаров
     */
    public static function getFermerOffersCultures($user_id,$cult_group = false){
        CModule::IncludeModule('iblock');
        $result = array();
        $res = CIBlockElement::GetList(
            array('ID' => 'DESC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('farmer_offer'),
                'ACTIVE' => 'Y',
                'PROPERTY_ACTIVE' => rrsIblock::getPropListKey('farmer_offer', 'ACTIVE', 'yes'),
                'PROPERTY_FARMER' => $user_id,
            ),
            false,
            false,
            array(
                'ID',
                'PROPERTY_CULTURE',
            )
        );
        while ($ob = $res->Fetch()) {
            if($cult_group){
                $result[$ob['PROPERTY_CULTURE_VALUE']] = 1;
            }else{
                $result[$ob['ID']] = $ob['PROPERTY_CULTURE_VALUE'];
            }
        }

        return $result;
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
                    'IBLOCK_ID' => rrsIblock::getIBlockId('farmer_warehouse'),
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
                'IBLOCK_ID' => rrsIblock::getIBlockId('farmer_profile'),
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
     * Получение профиля клиента B
     * @param  int $user_id идентификатор пользователя
     *         bool $profile возвращать ли информацию о пользователе
     * @return [] массив с полями профиля
     */
    public static function getProfile($user_id, $profile = false) {
        CModule::IncludeModule('iblock');
        $res = CIBlockElement::GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('farmer_profile'),
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
                'PROPERTY_PHONE',
                'PROPERTY_PARTNER_ID'
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
     * Получение полной информации профиля клиента B
     * @param  int $user_id идентификатор пользователя
     * @return [] массив с полями профиля
     */
    public static function getFullProfile($user_id) {
        CModule::IncludeModule('iblock');
        $res = CIBlockElement::GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('farmer_profile'),
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
                $ob['UL_TYPE'] = rrsIblock::getPropListId('farmer_profile', 'UL_TYPE', $ob['PROPERTY_UL_TYPE_ENUM_ID']);
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
                $ob['KPP'] = 'КПП ' . $ob['PROPERTY_KPP_VALUE'] . '';
                $ob['OGRN'] = 'ОГРН ' . $ob['PROPERTY_OGRN_VALUE'];
            }

            $result = $ob;
        }

        $result['USER'] = rrsIblock::getUserInfo($user_id);

        return $result;
    }

    /**
     * Получение списка параметров товаров
     * @param  [] $offerIds список идентификаторов товаров
     * @return [] массив со списком параметров
     */
    public static function getParamsList($offerIds) {
        CModule::IncludeModule('iblock');
        $res = CIBlockElement::GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('farmer_offer_chars'),
                'ACTIVE' => 'Y',
                'PROPERTY_OFFER' => $offerIds
            ),
            false,
            false,
            array(
                'ID',
                'NAME',
                'PROPERTY_OFFER',
                'PROPERTY_QUALITY',
                'PROPERTY_LBASE',
                'PROPERTY_BASE',
            )
        );
        while ($ob = $res->Fetch()) {
            $tmp = array(
                'ID' => $ob['ID'],
                'NAME' => $ob['NAME'],
                'OFFER_ID' => $ob['PROPERTY_OFFER_VALUE'],
                'QUALITY_ID' => $ob['PROPERTY_QUALITY_VALUE'],
                'LBASE_ID' => $ob['PROPERTY_LBASE_VALUE'],
                'BASE' => $ob['PROPERTY_BASE_VALUE']
            );
            $result[$ob['PROPERTY_OFFER_VALUE']][$ob['PROPERTY_QUALITY_VALUE']] = $tmp;
        }

        return $result;
    }

    /**
     * Получение списка всех складов колхозника
     * @param mixed $user_id идентификатор пользователя, или массив идентификаторов
     * @return [] массив со списком элементов
     */
    public static function getWarehouseList($user_id) {
        CModule::IncludeModule('iblock');
        $res = CIBlockElement::GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('farmer_warehouse'),
                'ACTIVE' => 'Y',
                'PROPERTY_FARMER' => $user_id,
                'PROPERTY_ACTIVE' => rrsIblock::getPropListKey('farmer_warehouse', 'ACTIVE', 'yes')
            ),
            false,
            false,
            array(
                'ID',
                'NAME',
                'SORT',
                'PROPERTY_ADDRESS',
                'PROPERTY_MAP',
            )
        );
        while ($ob = $res->Fetch()) {
            $tmp = array(
                'ID' => $ob['ID'],
                'NAME' => $ob['NAME'],
                'ADDRESS' => $ob['PROPERTY_ADDRESS_VALUE'],
                'MAP' => $ob['PROPERTY_MAP_VALUE']
            );
            $result[$ob['ID']] = $tmp;
        }

        return $result;
    }

    /**
     * Получение списка параметров складов поставщика
     * @param  [] $ids список идентификаторов складов поставщиков
     * @return [] массив со списком параметров
     */
    public static function getWarehouseParamsList($ids) {
        CModule::IncludeModule('iblock');
        $res = CIBlockElement::GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('farmer_warehouse'),
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
                'PROPERTY_REGION.NAME',
            )
        );
        while ($ob = $res->Fetch()) {
            $tmp = array(
                'ID' => $ob['ID'],
                'NAME' => $ob['NAME'],
                'ADDRESS' => $ob['PROPERTY_ADDRESS_VALUE'],
                'MAP' => $ob['PROPERTY_MAP_VALUE'],
                'REGION_NAME' => $ob['PROPERTY_REGION_NAME'],
            );
            $result[$ob['ID']] = $tmp;
        }

        return $result;
    }

    /**
     * Получение идентификатора организатора, привязанного к колхознику
     * @param  int $user_id идентификатор пользователя
     * @return [] массив со списком параметров
     */
    public static function getPartnerIdByFarmer($user_id) {
        $result = 0;

        CModule::IncludeModule('iblock');
        $res = CIBlockElement::GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('farmer_profile'),
                'ACTIVE' => 'Y',
                'PROPERTY_USER' => $user_id
            ),
            false,
            false,
            array(
                'ID',
                'NAME',
                'PROPERTY_PARTNER_ID'
            )
        );
        if ($ob = $res->Fetch()) {
            $result = $ob['PROPERTY_PARTNER_ID_VALUE'];
        }

        return $result;
    }

    /*
     * @param int $uid - идентификатор пользователя
     * @return string - тип прав пользователя на заключение сделок в зависимости от типа налогообложения и загруженных документов
     * (
     *      n1 - нет прав на заключение сделки, потому как не привязан организатор
     *      n2 - нет прав на заключение сделки, потому как привязанный организатор не загрузил договор
     *      n3 - нет прав на заключение сделки, не загружен перечень необходимых документов
     *      no_p - есть права на заключение сделок только без условия предоплаты
     *      a - есть права на заключенеи сделок,
     * )
     */
    public static function checkDealsRights($uid) {
        $answer = array('partner' => 'n1'); //default is no rights

        $arFarmer = rrsIblock::getUserInfo($uid);
        if ($arFarmer['UF_DEMO']) {
            $answer = array('demo' => 'n0');
            return $answer;
        }

        CModule::IncludeModule('iblock');

        $arSelect = array(
            'PROPERTY_PARTNER_ID',
            'PROPERTY_VERIFIED',
            'PROPERTY_UL_TYPE',
            'PROPERTY_NDS',
            'PROPERTY_NDS.CODE',
        );

        $arDocsList = array_keys(farmer::getAllDocuments());
        array_walk($arDocsList, 'addPropStrToVal', 'PROPERTY_');

        $arSelect = array_merge($arSelect, $arDocsList);

        $res = CIBlockElement::GetList(
            array('ID' => 'DESC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('farmer_profile'),
                'ACTIVE' => 'Y',
                'PROPERTY_USER' => $uid
            ),
            false,
            false,
            $arSelect
        );
        if ($data = $res->Fetch()) {
            if (is_numeric($data['PROPERTY_PARTNER_ID_VALUE'])) {
                //check if partner exists & active
                $res2 = CUser::GetList(($by="id"), ($order="desc"), array('ID' => $data['PROPERTY_PARTNER_ID_VALUE'], 'ACTIVE' => 'Y', 'GROUPS_ID' => 10));
                if ($res2->SelectedRowsCount() == 1) {
                    //check if partner verified link with farmer
                    if (!isset($data['PROPERTY_VERIFIED_ENUM_ID'])
                        || $data['PROPERTY_VERIFIED_ENUM_ID'] != rrsIblock::getPropListKey('farmer_profile', 'VERIFIED', 'yes')
                    ) {
                        //partner document not uploaded
                        $answer['partner'] = 'n2';
                    }
                    else {
                        unset($answer['partner']); //remove default error n1
                    }

                    //default docs check
                    $ulType = rrsIblock::getPropListId('farmer_profile', 'UL_TYPE', $data['PROPERTY_UL_TYPE_ENUM_ID']);
                    if (!$ulType)
                        $ulType = 'ul';

                    $arFilter = array(
                        'IBLOCK_ID' => rrsIblock::getIBlockId('user_docs'),
                        'ACYIVE' => 'Y',
                        'SECTION_CODE' => 'farmer',
                        'PROPERTY_TYPE' => rrsIblock::getPropListKey('user_docs', 'TYPE', $ulType)
                    );

                    if ($ulType == 'ul') {
                        $arFilter['PROPERTY_NDS'] = $data['PROPERTY_NDS_VALUE'];
                    }

                    $arDocs = array();
                    $res = CIBlockElement::GetList(
                        array('SORT' => 'ASC'),
                        $arFilter,
                        false,
                        false,
                        array('ID', 'NAME', 'CODE', 'PROPERTY_NAME')
                    );
                    while ($ob = $res->Fetch()) {
                        $arDocs[$ob['CODE']] = $ob;
                    }

                    if (is_array($arDocs) && sizeof($arDocs) > 0) {
                        foreach ($arDocs as $doc) {
                            if (!isset($data['PROPERTY_'.$doc['CODE'].'_VALUE']) || !is_numeric($data['PROPERTY_'.$doc['CODE'].'_VALUE'])) {
                                $answer['docs'] = 'n3';
                                break;
                            }
                        }
                    }

                    if (in_array('FD_FIN_RESULTS_VALUE', array_keys($arDocs)) && !is_numeric($data['PROPERTY_FD_FIN_RESULTS_VALUE'])) {
                        $answer['fin'] = 'no_p';
                    }
                }
            }
        }

        return $answer;
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
                    'IBLOCK_ID' => rrsIblock::getIBlockId('farmer_profile'),
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
        $cache_id = 'getAllDocuments_farmer';
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
                    'SECTION_CODE' => 'farmer',
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
     * Функция деактивирует товар
     * @param int $offer_id - id товара
     * @param boolean $need_redirect - флаг необходимости редиректа после опреации (по умолчанию - нужен редирект)
     *
     */
    public static function deactivateOffer($offer_id, $need_redirect = true){
        CModule::IncludeModule('iblock');

        //установка флага неактивности запроса
        CIBlockElement::SetPropertyValuesEx(
            $offer_id,
            rrsIblock::getIBlockId('farmer_offer'),
            array(
                'ACTIVE' => rrsIblock::getPropListKey('farmer_offer', 'ACTIVE', 'no'),
                'STATUS_AVAILABLE' => rrsIblock::getPropListKey('farmer_offer', 'STATUS_AVAILABLE', 'no'),
            )
        );

        $filter = array(
            'UF_OFFER_ID' => $offer_id
        );
        $arLeads = lead::getLeadList($filter);
        if (is_array($arLeads) && sizeof($arLeads) > 0) {
            lead::deleteLeads($arLeads);
        }

        $el = new CIBlockElement;
        $res = $el->Update($offer_id, array('NAME' => date('d.m.Y H:i:s')));

        /**
         * Удаляем все встречные предложения по ID товара
         */
        farmer::removeCountersByOfferID($offer_id);

        if($need_redirect) {
            global $APPLICATION;
            LocalRedirect($APPLICATION->GetCurPageParam(null, ['offer',]));
        }
    }

    /*
     * Расчет цены для поставщика (прямой расчет)
     * @param [] массив с данными
     * @return []
     */
    public static function bestPriceCalculation($data) {
        $result = array();
        $result['WH_ID'] = $data['CLIENT_WH_ID'];
        $result['CENTER'] = $data['CENTER'];
        $result['ROUTE'] = $data['ROUTE'];
        $result['TYPE'] = $data['TYPE'];
        $result['BC_DDP'] = $data['DDP_PRICE_CLIENT'];
        $result['SBROS_PERSENT'] = $data['DUMP'];

//        $nds = rrsIblock::getConst('nds');
//        $commissionVal = rrsIblock::getConst('commission');
//        $trCommissionVal = rrsIblock::getConst('commission_transport');

        //всегда берем тариф на перевозку как при fca (т.е. из настроек пользователя - из ИБ "Тарифы покупателя") и далее рассчитываем по схеме dap
        $tarif = client::getTarif($data['CLIENT_ID'], $data['CULTURE_GROUP_ID'], 'fca', $data['CENTER'], $data['ROUTE'], $data['TARIFF_LIST']);
        $result['TARIFF_VAL'] = $tarif;

        //расчет цены с места из базисной цены покупателя
        $csm_data = lead::makeCSMFromClientBase($data['DDP_PRICE_CLIENT'], $data['CLIENT_NDS'] == 'yes', $data['FARMER_NDS'] == 'yes', $data['DUMP'], $tarif, array('delivery_type' => 'cpt')); //расчет идёт всегда по схеме cpt (но тариф используется fca)
        $result['COMMISSION'] = $csm_data['COMMISSION'];
        $result['BASE_PRICE'] = $csm_data['UF_BASE_CONTR_PRICE'];
        $result['SBROS_RUB'] = $csm_data['SBROS_RUB'];
        $result['ACC_PRICE'] = $csm_data['RC_PRICE'];
        $result['ACC_PRICE_CSM'] = $csm_data['UF_CSM_PRICE'];

        return $result;
    }


    /**
     * Проверка пользователя на принадлежность к группе "Поставщик"
     * @param $iUserId
     * @return bool
     */
    public static function checkIsFarmer($iUserId) {

        $obUser     = new CUser;
        $obGroup    = new CGroup;

        // Группа "Поставщик"
        $arGroupFarmer = $obGroup->GetList(
            $by = "c_sort",
            $order = "asc",
            ['STRING_ID' => 'farmer']
        )->Fetch();

        // Группы пользователя
        $arGroupUser = $obUser->GetUserGroup($iUserId);

        return in_array($arGroupFarmer['ID'], $arGroupUser);
    }

    /**
     * Деактивация АП агентом
     * 1) удаление привязки к агенту (если есть)
     *
     * @param int $uid - ID поставщика
     *
     * @return bool - флаг
     */
    public static function deactivateFarmer($uid) {
        $result = false;

        $linked_user = false;

        global $USER;

        $agent_obj = new agent();
        if($agent_obj->checkFarmerByAgent($uid, $USER->GetID())){
            $linked_user = true;
        }

        if($linked_user){

            // 2) удаление привязки к агенту (если есть)
            $result = self::deleteAgentLink($uid);


        }

        return $result;
    }

    /**
     * Обновление расстояний между складами поставщиков
     */
    public static function updateFarmersWHRoutes() {
        $hl_ib_id = rrsIblock::HLgetIBlockId('FMROUTESCACHE');
        $entityDataClass = log::getEntityDataClass($hl_ib_id);
        $el = new $entityDataClass;

        $result = array();
        $farmerWHId = array(); //массив id складов (для удаления записей несуществующих складов)
        $farmerWH = array(); //массив данных путей

        //получаем просроченные записи (UF_TIMESTAMP = дата обновления + 30 дней)
        $rsData = $el->getList(array(
            'select' => array('ID', 'UF_FARMER_WH_ID1', 'UF_FARMER_WH_ID2', 'UF_ROUTE'),
            'filter' => array('<UF_TIMESTAMP' => time()),
            'order' => array('ID' => 'ASC')
        ));
        while ($res = $rsData->fetch()) {
            $result[] = $res;
            $farmerWHId[$res['UF_FARMER_WH_ID1']] = true;
            $farmerWHId[$res['UF_FARMER_WH_ID2']] = true;
        }

        if (is_array($result) && count($result) > 0) {
            if (is_array($farmerWHId) && sizeof($farmerWHId) > 0) {
                $res = CIBlockElement::GetList(
                    array('ID' => 'ASC'),
                    array(
                        'IBLOCK_ID' => rrsIblock::getIBlockId('farmer_warehouse'),
                        'ID' => array_keys($farmerWHId),
                    ),
                    false,
                    false,
                    array('ID', 'PROPERTY_MAP')
                );
                while ($data = $res->Fetch()) {
                    $farmerWH[$data['ID']] = $data['PROPERTY_MAP_VALUE'];
                }
            }

            $my_c = 0;
            foreach ($result as $item){
                if(!isset($farmerWH[$item['UF_FARMER_WH_ID1']])
                    || !isset($farmerWH[$item['UF_FARMER_WH_ID2']])
                ){
                    //один из складов был удален - удаляем соответствующее расстояние
                    log::_deleteEntity($hl_ib_id, $item['ID']);
                }else{
                    $route = rrsIblock::getRoute($farmerWH[$item['UF_FARMER_WH_ID1']], $farmerWH[$item['UF_FARMER_WH_ID2']]);
                    log::updateFarmerWHRouteCacheItem($item['ID'], $route);
                }
                $my_c++;
            }
            echo $my_c;
        }
    }

    /**
     * Генерация новых расстояний между складами поставщиков
     */
    public static function generateNewFarmersWHRoutes() {
        $hl_ib_id = rrsIblock::HLgetIBlockId('FMROUTESCACHE');
        $entityDataClass = log::getEntityDataClass($hl_ib_id);
        $el = new $entityDataClass;

        $result = array();
        $exist_pair = array(); //массив существующих пар
        $worked_pair = array(); //защита от дублей
        $farmerWHId = array(); //массив id складов

        //получаем id и координаты существующих складов
        $res = CIBlockElement::GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('farmer_warehouse'),
            ),
            false,
            false,
            array('ID', 'PROPERTY_MAP')
        );
        while ($data = $res->Fetch()) {
            $farmerWHId[$data['ID']] = $data['PROPERTY_MAP_VALUE'];
        }

        //получаем id всех складов, для которых есть записи расстояний
        $rsData = $el->getList(array(
            'select' => array('UF_FARMER_WH_ID1', 'UF_FARMER_WH_ID2'),
            'filter' => array(),
            'order' => array('ID' => 'ASC')
        ));
        while ($res = $rsData->fetch()) {
            if(!isset($exist_pair[$res['UF_FARMER_WH_ID1']][$res['UF_FARMER_WH_ID2']])
                && !isset($exist_pair[$res['UF_FARMER_WH_ID2']][$res['UF_FARMER_WH_ID1']])
            ){
                $exist_pair[$res['UF_FARMER_WH_ID1']][$res['UF_FARMER_WH_ID2']] = true;
            }
        }

        //генерация расстояний для тех складов, для которых не найдено расстояние
        $my_c = 0;
        foreach($farmerWHId as $cur_id1 => $cur_flag1){
            foreach($farmerWHId as $cur_id2 => $cur_flag2){
                if($cur_id1 != $cur_id2
                    && !isset($exist_pair[$cur_id1][$cur_id2])
                    && !isset($exist_pair[$cur_id2][$cur_id1])
                    && !isset($worked_pair[$cur_id1][$cur_id2])
                    && !isset($worked_pair[$cur_id2][$cur_id1])
                    && isset($farmerWHId[$cur_id1])
                    && isset($farmerWHId[$cur_id2])
                ){
                    //генерируем новые данные
                    $route = rrsIblock::getRoute($farmerWHId[$cur_id1], $farmerWHId[$cur_id2]);
                    log::addFarmerWHRouteCacheItem($cur_id1, $cur_id2, $route);

                    //отмечаем данные склады как обработанные
                    $worked_pair[$cur_id1][$cur_id2] = true;

                    //ограничение количества записей, создаваемых за один цикл
                    if($my_c > 20000){
                        break(2);
                    }
                    $my_c++;
                }
            }
        }
    }

    /**
     * Удаление (деактивация) АП агентом или организатором (удаление, если пользователь в демо-режиме)
     * 1) деактивация товаров
     * 3) удаление привязки к агенту (если есть)
     * 3) деактивация складов
     * 4) удаление пар по товарам АП
     * 5) удаление закешированных расстояний
     * 6) деактивация профиля учётной записи пользователя
     * 7) отправка увеломлений АП и (если требуется) организатору
     *
     * @param int $uid - ID поставщика
     * @param boolean $not_partner - флаг является ли отвязывающий пользователь организатором или агентом (для упрощения
     * проверки привязки к пользователю)
     *
     * @return bool - флаг удаления
     */
    public static function deleteFarmer($uid, $not_partner = false) {
        $result = false;

        $linked_user = false;

        global $USER;

        if($not_partner){
            $agent_obj = new agent();
            if($agent_obj->checkFarmerByAgent($uid, $USER->GetID())){
                $linked_user = true;
            }
        }elseif(self::checkLinkWithPartner($uid, $USER->GetID())){
            $linked_user = true;
        }

        if($linked_user){
            $demo_user = self::checkIfDemo($uid);

            // 1) деактивация/удаление товаров
            self::deactivateDeleteOffersByFarmer($uid, $demo_user);

            // 2) удаление привязки к агенту (если есть)
            self::deleteAgentLink($uid);

            // 3) деактивация/удаление складов
            $wh_list = self::deactivateDeleteWarehousesByFarmer($uid, $demo_user);

            // 4) удаление пар с участием АП
            self::deletePairs($uid);

            // 5) удаление закешированных расстояний
            if(count($wh_list > 0)){
                self::deleteCachedRoutes($wh_list);
            }

            // 6) деактивация/удаление профиля и учётной записи пользователя
            self::deactivateDeleteProfile($uid, $demo_user);

            // 7) отправка увеломлений АП
            $profile_data = farmer::getProfile($uid, true);
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
                $partner_id = farmer::getPartnerIdByFarmer($uid);
                if($partner_id > 0){
                    $agent_obj = new agent();
                    $sender_data = $agent_obj->getProfile($USER->GetID()); // отправитель - агент
                    $sender_name = trim($sender_data['USER']['LAST_NAME'].' '.$sender_data['USER']['NAME'].' '.$sender_data['USER']['SECOND_NAME']);
                    if($sender_name != ''){
                        $sender_name .= ' (' . $sender_data['USER']['EMAIL'] . ')';
                    }else{
                        $sender_name = $sender_data['USER']['EMAIL'];
                    }

                    // отправка увеломлений организатору
                    $partner_data = partner::getProfile($partner_id);
                    $ev_obj->Send('PARTNER_NOTIFY_FARMER_DEACTIVATE', 's1', array('EMAIL' => $partner_data['USER']['EMAIL'], 'BY' => $sender_name, 'USER_DATA' => $profile_name));
                }
            }else{
                $sender_data = partner::getProfile($USER->GetID(), true); // отправитель - организатор
                if(trim($sender_data['PROPERTY_FULL_COMPANY_NAME_VALUE']) != ''){
                    $sender_name .= trim($sender_data['PROPERTY_FULL_COMPANY_NAME_VALUE']) . ' (' . $sender_data['USER']['EMAIL'] . ')';
                }else{
                    $sender_name = $sender_data['USER']['EMAIL'];
                }
            }

            $ev_obj->Send('FARMER_DEACTIVATE', 's1', array('EMAIL' => $profile_data['USER']['EMAIL'], 'BY' => $sender_name));
        }

        return $result;
    }


    /**
     * Проверка привязан ли АП к организатору
     *
     * @param int $farmer_id - ID поставщика
     * @param int $partner_id - ID организатораю)
     *
     * @return bool - признак привязки
     */
    public static function checkLinkWithPartner($farmer_id, $partner_id) {
        $result = false;

        if(is_numeric($partner_id)
            && $partner_id > 0
        ){
            $res = CIBlockElement::GetList(
                array('ID' => 'ASC'),
                array(
                    'IBLOCK_ID'             => rrsIblock::getIBlockId('farmer_profile'),
                    'ACTIVE'                => 'Y',
                    'PROPERTY_USER'         => $farmer_id,
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

        return $result;
    }

    /**
     * Деактивация складов поставщика
     *
     * @param int $farmer_id - ID поставщика
     *
     * @return [] - массив ID складов
     */
    public static function deactivateDeleteWarehousesByFarmer($farmer_id, $delete_flag = false) {
        $result = array();

        if(is_numeric($farmer_id)
            && $farmer_id > 0
        ){
            $warehouses_list = array();
            $el_obj = new CIBlockElement;

            $res = $el_obj->GetList(
                array('ID' => 'ASC'),
                array(
                    'IBLOCK_ID'         => rrsIblock::getIBlockId('farmer_warehouse'),
                    'ACTIVE'            => 'Y',
                    'PROPERTY_FARMER'   => $farmer_id
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
                                'ACTIVE' => rrsIblock::getPropListKey('farmer_warehouse', 'ACTIVE', 'no')
                            )
                        );
                }

                $result = array_keys($warehouses_list);
            }
        }

        return $result;
    }

    /**
     * Деактивация товаров поставщика
     *
     * @param int $farmer_id - ID поставщика
     *
     * @return bool - флаг успеха операции
     */
    public static function deactivateDeleteOffersByFarmer($farmer_id, $delete_flag = false) {
        $result = false;

        if(is_numeric($farmer_id)
            && $farmer_id > 0
        ){
            $offers_list = array();
            $el_obj = new CIBlockElement;

            $res = $el_obj->GetList(
                array('ID' => 'ASC'),
                array(
                    'IBLOCK_ID'         => rrsIblock::getIBlockId('farmer_offer'),
                    //'ACTIVE'            => 'Y',
                    'PROPERTY_ACTIVE'   => rrsIblock::getPropListKey('farmer_offer', 'ACTIVE', 'yes'),
                    'PROPERTY_FARMER'   => $farmer_id
                ),
                false,
                false,
                array('ID', 'IBLOCK_ID')
            );
            while($data = $res->Fetch()){
                $offers_list[$data['ID']] = $data['IBLOCK_ID'];
            }

            if(count($offers_list) > 0){
                foreach($offers_list as $cur_id => $cur_ib){
//                    if($delete_flag)
//                        $el_obj->Delete($cur_id);
//                    else
                        $el_obj->SetPropertyValuesEx(
                            $cur_id,
                            $cur_ib,
                            array(
                                'ACTIVE' => rrsIblock::getPropListKey('farmer_offer', 'ACTIVE', 'no')
                        ));
                }
                $result = true;
            }
        }

        return $result;
    }

    /**
     * Удаление связи поставщика с его агентом
     *
     * @param int $farmer_id - ID поставщика
     *
     * @return bool - флаг успеха операции
     */
    public static function deleteAgentLink($farmer_id) {
        $result = false;

        if(is_numeric($farmer_id)
            && $farmer_id > 0
        ){
            $el_obj = new CIBlockElement;

            $res = $el_obj->GetList(
                array('ID' => 'ASC'),
                array(
                    'IBLOCK_ID'         => rrsIblock::getIBlockId('farmer_agent_link'),
                    'PROPERTY_USER_ID'  => $farmer_id
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
     * Удаление пар по товарам поставщика
     *
     * @param int $farmer_id - ID поставщика
     *
     * @return bool - флаг успеха операции
     */
    public static function deletePairs($farmer_id) {
        $result = false;

        if(is_numeric($farmer_id)
            && $farmer_id > 0
        ){
            $leadList = lead::getLeadList(array('UF_FARMER_ID' => $farmer_id));

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
     * @param int $farmer_id - ID поставщика
     *
     * @return bool - флаг успеха операции
     */
    public static function deactivateDeleteProfile($farmer_id, $delete_flag = false) {
        $result = false;

        if(is_numeric($farmer_id)
            && $farmer_id > 0
        ){
            $el_obj = new CIBlockElement;
            $u_obj  = new CUser;

            $res = $el_obj->GetList(
                array('ID' => 'ASC'),
                array(
                    'IBLOCK_ID'     => rrsIblock::getIBlockId('farmer_profile'),
                    'ACTIVE'        => 'Y',
                    'PROPERTY_USER' => $farmer_id
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
//                $u_obj->Delete($farmer_id);
//            else
                $u_obj->Update($farmer_id, array('ACTIVE' => 'N'));

            $result = true;
        }

        return $result;
    }

    /**
     * Удаление пар по товарам поставщика
     *
     * @param [] $warehouses_list - массив ID поставщиков
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
                    'UF_FARMER_WH_ID' => $warehouses_list
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
                    'IBLOCK_ID'     => rrsIblock::getIBlockId('farmer_profile'),
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
     * Проверяет есть ли возможность изменить НДС у АП
     * @param $iFarmerId
     * @return array
     */
    public static function isChangeNDS($iFarmerId) {

        $arResult = [
            'LOCK'  => false,
            'MSG'   => null,
        ];

        try {

            // Проверяем есть ли активные товары
            $arOffer = self::getOfferListByUser($iFarmerId, true);
            if(!empty($arOffer)) {
                throw new Exception('В системе есть активные товары');
            }

            // Проверяем есть ли активные сделки
            $arDeal = deal::getUsersActiveDeals($iFarmerId, false);
            if($arDeal[$iFarmerId]) {
                throw new Exception('В системе есть активные сделки');
            }

            // На всякий проверим может у пользователя и не может быть товаров и сделок
            if(!self::checkIsFarmer($iFarmerId)) {
                throw new Exception('Вы не являетесь поставщиком');
            }

        } catch (Exception $e) {
            $arResult['LOCK']   = true;
            $arResult['MSG']    = $e->getMessage();
        }

        return $arResult;
    }

    /**
     * возвращает данные организатора, привязанного к пользователю (для рассылки при регистрации из приглашения)
     * @param $userId - id пользователя
     * @return array - массив данных с ключами [EMAIL] - почта, [NAME] - ФИО или логин
     */
    public static function getPartnerEmailData($userId) {
        $result = array();

        $res = CIBlockElement::GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('farmer_profile'),
                'PROPERTY_USER' => $userId
            ),
            false,
            array('nTopCount' => 1),
            array('PROPERTY_PARTNER_ID')
        );
        if($data = $res->Fetch()){
            if(isset($data['PROPERTY_PARTNER_ID_VALUE'])
                && is_numeric($data['PROPERTY_PARTNER_ID_VALUE'])
            ) {
                $res = CUser::GetList(
                    ($by = 'id'), ($order = 'asc'),
                    array('ID' => $data['PROPERTY_PARTNER_ID_VALUE']),
                    array('FIELDS' => array('NAME', 'LAST_NAME', 'SECOND_NAME', 'EMAIL', 'LOGIN'))
                );
                if($data = $res->Fetch()){
                    $result['EMAIL'] = $data['EMAIL'];

                    $temp_name = trim($data['LAST_NAME'].' '.$data['NAME'].' '.$data['SECOND_NAME']);
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
     * создание встречных предложений поставщика
     * @param array $arData - данные для создания предложений
     * обязательные ключи $arData:
     * farmer_id - id АП
     * offer_id - id АП
     * selected_requests - массив ID запросов
     *
     * для 'type' = 'c' необходимо дополнительно передать ключи:
     * price - цена установленная ап
     * volume - объем установленный ап
     *
     * @return int - id созданной записи
     */
    public static function addCounterRequest($arData, $user_type = 'farmer', $arOfferData = array(), $arRequestData = array()) {
        //проверка переданных данных
        if(isset($arData['offer_id'])
            && isset($arData['selected_requests'])
            && isset($arData['farmer_id'])
        ){
            global $DB;
            $date = $DB->FormatDate(date("d.m.Y H:i:s"), "DD.MM.YYYY HH:MI:SS", FORMAT_DATETIME);

            $volume_val = (isset($arData['volume']) ? $arData['volume'] : 0);
            $nds = rrsIblock::getConst('nds');
            $commissionVal = rrsIblock::getConst('commission');

            //составляем массив дополнительных данных, если нужно
            $additional_fields_arr = array();
            if($arData['coffer_type'] == 'p'){
                $additional_fields_arr['IS_ADD_CERT'] = $arData['addit_is_add_cert'];
                $additional_fields_arr['IS_BILL_OF_HEALTH'] = $arData['addit_is_bill_of_health'];
                $additional_fields_arr['IS_VET_CERT'] = $arData['addit_is_vet_cert'];
                $additional_fields_arr['IS_QUALITY_CERT'] = $arData['addit_is_quality_cert'];
                $additional_fields_arr['IS_TRANSFER'] = $arData['addit_is_transfer'];
                $additional_fields_arr['IS_SECURE_DEAL'] = $arData['addit_is_secure_deal'];
                $additional_fields_arr['IS_AGENT_SUPPORT'] = $arData['addit_is_agent_support'];
            }

            //общие данные для встречных предложений по всем запросам
            $arFieldsCommon = array(
                'UF_OFFER_ID' => $arData['offer_id'],
                'UF_DATE' => $date,
                'UF_FARMER_PRICE' => (isset($arData['price']) ? $arData['price'] : 0),
                'UF_VOLUME' => $volume_val,
                'UF_TYPE' => (isset($arData['type']) && $arData['type'] == 'c' ? 'c' : 'a'), //counter or accepted type
                'UF_FARMER_ID' => $arData['farmer_id'],
                'UF_VOLUME_OFFER' => $volume_val,
                'UF_VOLUME_REMAINS' => $volume_val,
                'UF_COFFER_TYPE' => $arData['coffer_type'],
                'UF_PARTNER_PRICE' => (!empty($arData['addit_partner_price']) ? $arData['addit_partner_price'] : '0'),
                'UF_CREATE_BY_PARTNER' => (!empty($arData['addit_partner_id']) ? $arData['addit_partner_id'] : '0'),
                'UF_ADDIT_FIELDS' => (count($additional_fields_arr) > 0 ? json_encode($additional_fields_arr) : ''),
                'UF_PARTNER_Q_APRVD' => (!empty($arData['partner_quality_approved']) ? $arData['partner_quality_approved'] : '0'),
                'UF_PARTNER_Q_APRVD_D' => (!empty($arData['partner_quality_approved_d']) ? $arData['partner_quality_approved_d'] : ''),
                'UF_BY_PARTNER_REAL' => (!empty($arData['real_partner_id']) ? $arData['real_partner_id'] : 0),
            );

            if(isset($arData['delivery'])){
                $arFieldsCommon['UF_DELIVERY'] = $arData['delivery'];
            }

            if(!is_array($arData['selected_requests'])){
                $arData['selected_requests'] = array($arData['selected_requests']);
            }

            $requests_data = $arRequestData;
            if(count($requests_data) == 0) {
                $requests_data = client::getRequestListByIDs($arData['selected_requests']);
            }
            $offer_data = $arOfferData;
            if(count($offer_data) == 0) {
                $offer_data = farmer::getOfferById($arData['offer_id']);
            }

            $arCulturesGroup = culture::getCulturesGroup();
            $culture_id = 0;
            $client_to_requests = array(); //link between clients & requests
            $counter_request_logged = false;

            foreach($requests_data as $cur_request_data){
                $csm_val = 0;
                $arFields = $arFieldsCommon;

                $arFields['UF_VOLUME'] = min($arFields['UF_VOLUME'], $cur_request_data['VOLUME']);
                $arFields['UF_NDS_CLIENT'] = $cur_request_data['USER_NDS'];
                $arFields['UF_NDS_FARMER'] = $offer_data['USER_NDS'];
                $arFields['UF_REQUEST_ID'] = $cur_request_data['ID'];

                //доставка будет изменена в дальнейшем
                if(!isset($arFields['UF_DELIVERY'])){
                    if($cur_request_data['NEED_DELIVERY'] == 'Y'){
                        $arFields['UF_DELIVERY'] = 'cpt';
                    }else{
                        $arFields['UF_DELIVERY'] = 'fca';
                    }
                }

                $arLead = lead::getLead($arData['farmer_id'], $cur_request_data['ID'], $arData['offer_id']);

                $arFields['UF_ROUTE'] = $arLead['UF_ROUTE'];
                $arFields['UF_CLIENT_ID'] = $arLead['UF_CLIENT_ID'];
                $arFields['UF_CLIENT_WH_ID'] = $arLead['UF_CLIENT_WH_ID'];
                $arFields['UF_FARMER_WH_ID'] = $arLead['UF_FARMER_WH_ID'];

                $discount = deal::getDump($cur_request_data['PARAMS'], $offer_data['PARAMS']);
                //тариф всегда берем как fca, расчет всегда делаем как dap
                $tarif_val = client::getTarif($arLead['UF_CLIENT_ID'], $arCulturesGroup[$cur_request_data['CULTURE_ID']], 'fca', $arLead['UF_CENTER_ID'], $arLead['UF_ROUTE'], model::getAgrohelperTariffs());
                if($arFieldsCommon['UF_TYPE'] != 'c'){
                    //если принят запрос, то цена с места пересчитывается (т.к. система налогообложения АП могла измениться и данные из колонки UF_BASE_CONTR_PRICE HL ИБ LEADLIST могли устареть) из базисной цены (учитывающей НДС покупателя, т.е. колонки UF_BASE_PRICE HL ИБ LEADLIST),
                    $csm_data = lead::makeCSMFromClientBase($arLead['UF_BASE_PRICE'], $arFields['UF_NDS_CLIENT'] == 'yes', $arFields['UF_NDS_FARMER'] == 'yes', $discount, $tarif_val, array('delivery_type' => 'cpt'));
                    $arFields['UF_FARMER_PRICE'] = $csm_data['UF_CSM_PRICE'];
                }

                //получаем базисную цену для покупателя из указанной/принятой цены с места АП (также получаем цену с места для налогообложения покупателя)
                $base_price_data = lead::makeBaseFromCSM($arFields['UF_FARMER_PRICE'], $arFields['UF_NDS_CLIENT'] == 'yes', $arFields['UF_NDS_FARMER'] == 'yes', $discount, $tarif_val);
                $arFields['UF_BASE_CONTR_PRICE'] = $base_price_data['BASE_CONTR_PRICE'];

                //добавление данных в БД
                $counter_request_id = log::_createEntity(log::getIdByName('COUNTEROFFERS'), $arFields);

                if($culture_id == 0){
                    $culture_id = $cur_request_data['CULTURE_ID'];
                }

                //группируем запросы по покупателям для удобства отправки уведомлений
                $client_to_requests[$cur_request_data['CLIENT_ID']][] = array(
                    'REQ_ID' => $cur_request_data['ID'],
                    'CON_ID' => $counter_request_id['ID'],
                    'CLIENT_WH_ID' => $arLead['UF_CLIENT_WH_ID'],
                    'CULTURE_ID' => $cur_request_data['CULTURE_ID']
                );

                //вносим данные о встречном предложении в лог (т.к. используется цена с места и одно предложение, то для всех ВП делаем одну запись)
                if(!$counter_request_logged
                    && $counter_request_id
                ){
                    log::_createEntity(log::getIdByName('COUNTEROFFERSLOG'), array(
                        'UF_DATE' => $arFieldsCommon['UF_DATE'],
                        'UF_FARMER_ID' => $arFieldsCommon['UF_FARMER_ID'],
                        'UF_OFFER_ID' => $arFieldsCommon['UF_OFFER_ID'],
                        'UF_FARMER_PRICE' => $arFieldsCommon['UF_FARMER_PRICE'],
                        'UF_REQUEST_ID' => $cur_request_data['ID'], //запоминаем ID запроса на всякий случай
                        'UF_CULTURE' => $cur_request_data['CULTURE_ID'],
                        'UF_FARMER_WH_ID' => $arLead['UF_FARMER_WH_ID'],
                    ));
                    $counter_request_logged = true;

                    //проверяем цену предложения на минимальную величину за сегодня (обновляем данные в случае необходимости)
                    log::checkLowerCounterOfferCSM($arFieldsCommon['UF_FARMER_PRICE'], $arFields['UF_NDS_FARMER'] == 'yes', $arFieldsCommon['UF_OFFER_ID'], reset(farmer::getRegionsByWhs($arLead['UF_FARMER_WH_ID'])), $cur_request_data['CULTURE_ID']);
                }
            }

            //отправка уведомлений о встречных предложениях (покупателю, агенту покупателя, организатору)
            //$noticeList = notice::getNoticeList();
            $culture = culture::getName($culture_id);

            //уведомления покупателям
            $url = '/client/exclusive_offers/';
            foreach($client_to_requests as $cur_client_id => $cur_requests){
                $clientProfile = client::getProfile($cur_client_id, true);

                foreach($cur_requests as $cur_request){
                    $cur_url = $url . '?warehouse_id=' . $cur_request['CLIENT_WH_ID']
                        . '&culture_id=' . $cur_request['CULTURE_ID']
                        . '&o=' . $arData['offer_id']
                        . '&r=' . $cur_request['REQ_ID'];

                    //if (in_array($noticeList['e_d']['ID'], $clientProfile['PROPERTY_NOTICE_VALUE'])) {
                        $arEventFields = array(
                            'REQUEST_ID' => $cur_request['REQ_ID'],
                            'CULTURE' => $culture['NAME'],
                            'VOLUME' => $arFieldsCommon['UF_VOLUME'],
                            'ID' => $cur_request['CON_ID'],
                            'URL' => $GLOBALS['host'] . $cur_url,
                            'EMAIL' => $clientProfile['USER']['EMAIL'],
                        );
                        CEvent::Send('CLIENT_COUNTER_REQUEST_ADD', 's1', $arEventFields);
                    //}
//                    if (in_array($noticeList['c_d']['ID'], $clientProfile['PROPERTY_NOTICE_VALUE'])) {
//                        notice::addNotice($clientProfile['USER']['ID'], 'd', 'Направлено встречное предложение', $cur_url, '#' . $cur_request['CON_ID']);
//                    }
//                    if (in_array($noticeList['s_d']['ID'], $clientProfile['PROPERTY_NOTICE_VALUE']) && $clientProfile['PROPERTY_PHONE_VALUE']) {
//                        $phone = str_replace(array('+', ' ', '(', ')', '-'), "", $clientProfile['PROPERTY_PHONE_VALUE']);
//                        notice::sendNoticeSMS($phone, 'Направлено встречное предложение: ' . $GLOBALS['host'] . $cur_url);
//                    }
                }
            }

            /*
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
            }*/

            if($arFieldsCommon['UF_TYPE'] == 'c'
                && !isset($_POST['send_counter_offer_ajax'])
            ) {
                //сохранение куки для вывода текста об успешной отправки
                setcookie('send_counter_requests_success', 'y', time() + 60, "/");

                if($user_type == 'farmer'){
                    //переход к списку запросов
                    LocalRedirect('/farmer/request/');
                }elseif($user_type == 'agent'){
                    LocalRedirect('/partner/farmer_request/');
                }
                exit;
            }
        }
    }

    /**
     * создание встречных предложений поставщика - в случае добавления нового запроса, на основе старого предложения
     * @param array $arData - данные для создания предложений
     * обязательные ключи $arData:
     * farmer_id - id АП
     * offer_id - id АП
     * selected_requests - массив ID запросов
     * date - дата встречного предложения
     *
     * для 'type' = 'c' необходимо дополнительно передать ключи:
     * price - цена установленная ап
     *
     * volume_offer - объем с старого ВП
     * volume_remains - объем с старого ВП
     *
     * @return int - id созданной записи
     */
    public static function addCounterRequestA($arData) {
        //проверка переданных данных
        if(isset($arData['offer_id'])
            && isset($arData['selected_requests'])
            && isset($arData['farmer_id'])
            && isset($arData['date'])
            && isset($arData['price'])
            && isset($arData['volume_offer'])
            && isset($arData['volume_remains'])
        ){
            global $DB;
            $date = $arData['date'];

            $volume_val = (isset($arData['volume']) ? $arData['volume'] : 0);
            $volume_offer = (isset($arData['volume_offer']) ? $arData['volume_offer'] : 0);
            $volume_remains = (isset($arData['volume_remains']) ? $arData['volume_remains'] : 0);
            $nds = rrsIblock::getConst('nds');
            $commissionVal = rrsIblock::getConst('commission');
            //если объем остатка больше 0
            if($volume_remains>0){
                //общие данные для встречных предложений по всем запросам
                $arFieldsCommon = array(
                    'UF_OFFER_ID' => $arData['offer_id'],
                    'UF_DATE' => $date,
                    'UF_FARMER_PRICE' => (isset($arData['price']) ? $arData['price'] : 0),
                    'UF_VOLUME' => $volume_val,
                    'UF_TYPE' => (isset($arData['type']) && $arData['type'] == 'c' ? 'c' : 'a'), //counter or accepted type
                    'UF_FARMER_ID' => $arData['farmer_id'],
                    'UF_VOLUME_OFFER' => $volume_offer,
                    'UF_VOLUME_REMAINS' => $volume_remains,
                    'UF_COFFER_TYPE' => (!empty($arData['UF_COFFER_TYPE']) ? $arData['UF_COFFER_TYPE'] : 'c'),
                    'UF_PARTNER_PRICE' => (!empty($arData['UF_PARTNER_PRICE']) ? $arData['UF_PARTNER_PRICE'] : '0'),
                    'UF_CREATE_BY_PARTNER' => (!empty($arData['UF_CREATE_BY_PARTNER']) ? $arData['UF_CREATE_BY_PARTNER'] : '0'),
                    'UF_ADDIT_FIELDS' => (!empty($arData['UF_ADDIT_FIELDS']) ? $arData['UF_ADDIT_FIELDS'] : ''),
                    'UF_PARTNER_Q_APRVD' => $arData['UF_PARTNER_Q_APRVD'],
                    'UF_PARTNER_Q_APRVD_D' => $arData['UF_PARTNER_Q_APRVD_D'],
                    'UF_BY_PARTNER_REAL' => (!empty($arData['UF_BY_PARTNER_REAL']) ? $arData['UF_BY_PARTNER_REAL'] : 0),
                );

                if(isset($arData['delivery'])){
                    $arFieldsCommon['UF_DELIVERY'] = $arData['delivery'];
                }

                if(!empty($arData['UF_DELIVERY'])){
                    $arFieldsCommon['UF_DELIVERY'] = $arData['UF_DELIVERY'];
                }

                if(!is_array($arData['selected_requests'])){
                    $arData['selected_requests'] = array($arData['selected_requests']);
                }

                $requests_data = client::getRequestListByIDs($arData['selected_requests']);
                $offer_data = farmer::getOfferById($arData['offer_id']);

                $arCulturesGroup = culture::getCulturesGroup();
                $culture_id = 0;
                $client_to_requests = array(); //link between clients & requests
                $counter_request_logged = false;
                $counter_request_volume = array(); //массив с объемами создаваемых предложений

                foreach($requests_data as $cur_request_data){
                    $csm_val = 0;
                    $arFields = $arFieldsCommon;

                    $arFields['UF_VOLUME'] = min($arFields['UF_VOLUME_REMAINS'], $cur_request_data['REMAINS']);
                    $arFields['UF_NDS_CLIENT'] = $cur_request_data['USER_NDS'];
                    $arFields['UF_NDS_FARMER'] = $offer_data['USER_NDS'];
                    $arFields['UF_REQUEST_ID'] = $cur_request_data['ID'];

                    $counter_request_volume[$cur_request_data['ID']] = $arFields['UF_VOLUME'];
                    //если для запроса получается нулевой объем, то пропускаем создание предложения
                    if($arFields['UF_VOLUME'] == 0){
                        continue;
                    }

                    //доставка будет изменена в дальнейшем
                    if(!isset($arFields['UF_DELIVERY'])){
                        if($cur_request_data['NEED_DELIVERY'] == 'Y'){
                            $arFields['UF_DELIVERY'] = 'cpt';
                        }else{
                            $arFields['UF_DELIVERY'] = 'fca';
                        }
                    }

                    $arLead = lead::getLead($arData['farmer_id'], $cur_request_data['ID'], $arData['offer_id']);

                    $arFields['UF_ROUTE'] = $arLead['UF_ROUTE'];
                    $arFields['UF_CLIENT_ID'] = $arLead['UF_CLIENT_ID'];
                    $arFields['UF_CLIENT_WH_ID'] = $arLead['UF_CLIENT_WH_ID'];
                    $arFields['UF_FARMER_WH_ID'] = $arLead['UF_FARMER_WH_ID'];

                    $discount = deal::getDump($cur_request_data['PARAMS'], $offer_data['PARAMS']);
                    //тариф всегда берем как fca, расчет всегда делаем как dap
                    $tarif_val = client::getTarif($arLead['UF_CLIENT_ID'], $arCulturesGroup[$cur_request_data['CULTURE_ID']], 'fca', $arLead['UF_CENTER_ID'], $arLead['UF_ROUTE'], model::getAgrohelperTariffs());
                    if($arFieldsCommon['UF_TYPE'] != 'c'){
                        //если принят запрос, то цена с места пересчитывается (т.к. система налогообложения АП могла измениться и данные из колонки UF_BASE_CONTR_PRICE HL ИБ LEADLIST могли устареть) из базисной цены (учитывающей НДС покупателя, т.е. колонки UF_BASE_PRICE HL ИБ LEADLIST),
                        $csm_data = lead::makeCSMFromClientBase($arLead['UF_BASE_PRICE'], $arFields['UF_NDS_CLIENT'] == 'yes', $arFields['UF_NDS_FARMER'] == 'yes', $discount, $tarif_val, array('delivery_type' => 'cpt'));
                        $arFields['UF_FARMER_PRICE'] = $csm_data['UF_CSM_PRICE'];
                    }

                    //получаем базисную цену для покупателя из указанной/принятой цены с места АП (также получаем цену с места для налогообложения покупателя)
                    $base_price_data = lead::makeBaseFromCSM($arFields['UF_FARMER_PRICE'], $arFields['UF_NDS_CLIENT'] == 'yes', $arFields['UF_NDS_FARMER'] == 'yes', $discount, $tarif_val);
                    $arFields['UF_BASE_CONTR_PRICE'] = $base_price_data['BASE_CONTR_PRICE'];

                    //добавление данных в БД
                    $counter_request_id = log::_createEntity(log::getIdByName('COUNTEROFFERS'), $arFields);

                    if($culture_id == 0){
                        $culture_id = $cur_request_data['CULTURE_ID'];
                    }

                    //группируем запросы по покупателям для удобства отправки уведомлений
                    $client_to_requests[$cur_request_data['CLIENT_ID']][] = array(
                        'REQ_ID' => $cur_request_data['ID'],
                        'CON_ID' => $counter_request_id['ID'],
                        'CLIENT_WH_ID' => $arLead['UF_CLIENT_WH_ID'],
                        'CULTURE_ID' => $cur_request_data['CULTURE_ID'],
                    );

                    //вносим данные о встречном предложении в лог (т.к. используется цена с места и одно предложение, то для всех ВП делаем одну запись)
                    if(!$counter_request_logged
                        && $counter_request_id
                    ){
                        log::_createEntity(log::getIdByName('COUNTEROFFERSLOG'), array(
                            'UF_DATE' => $arFieldsCommon['UF_DATE'],
                            'UF_FARMER_ID' => $arFieldsCommon['UF_FARMER_ID'],
                            'UF_OFFER_ID' => $arFieldsCommon['UF_OFFER_ID'],
                            'UF_FARMER_PRICE' => $arFieldsCommon['UF_FARMER_PRICE'],
                            'UF_REQUEST_ID' => $cur_request_data['ID'], //запоминаем ID запроса на всякий случай
                            'UF_CULTURE' => $cur_request_data['CULTURE_ID'],
                            'UF_FARMER_WH_ID' => $arLead['UF_FARMER_WH_ID'],
                        ));
                        $counter_request_logged = true;
                    }
                }

                //отправка уведомлений о встречных предложениях (покупателю, агенту покупателя, организатору)
                //$noticeList = notice::getNoticeList();
                $culture = culture::getName($culture_id);

                //уведомления покупателям
                 $url = '/client/exclusive_offers/';
                 foreach($client_to_requests as $cur_client_id => $cur_requests){
                     $clientProfile = client::getProfile($cur_client_id, true);

                     foreach($cur_requests as $cur_request){
                         //отправляем письма только п тем предложениям, у которых не нулевой объём
                         if(isset($counter_request_volume[$cur_request])
                            && $counter_request_volume[$cur_request] > 0
                         ) {
                             $cur_url = $url . '?warehouse_id=' . $cur_request['CLIENT_WH_ID']
                                 . '&culture_id=' . $cur_request['CULTURE_ID']
                                 . '&o=' . $arData['offer_id']
                                 . '&r=' . $cur_request['REQ_ID'];

                             $arEventFields = array(
                                 'REQUEST_ID' => $cur_request['REQ_ID'],
                                 'CULTURE' => $culture['NAME'],
                                 'VOLUME' => $arFieldsCommon['UF_VOLUME'],
                                 'ID' => $cur_request['CON_ID'],
                                 'URL' => $GLOBALS['host'] . $cur_url,
                                 'EMAIL' => $clientProfile['USER']['EMAIL'],
                             );
                             CEvent::Send('CLIENT_COUNTER_REQUEST_ADD', 's1', $arEventFields);
                         }
                     }
                 }
            }
        }
    }

    /**
     * Создение новых встречных предложений для созданного запроса для тех товаров, по которым уже были созданы аналогичные ВП
     *
     * @param $request_id - ID запроса
     * @param bool $offerRequestApply - подбор подходящих пар запрос-товар (если false, то подбор будет сделан внутри метода)
     */
    public static function createNewCounterOfferByRequest($request_id,$offerRequestApply = false){

        //если подбор пар еще не был сделан, то делаем его
        if($offerRequestApply === false){
            //получение информации о запросе покупателя
            $request = client::getRequestById($request_id);
            $arRequests[] = $request;

            //поиск аналогов культуры
            $cultureList = culture::getAnalog($request['CULTURE_ID']);

            $cultureList[] = $request['CULTURE_ID'];

            //получение всех активных товаров по культуре
            $arOffers = farmer::getOfferList($cultureList);

            if (is_array($arOffers) && sizeof($arOffers) > 0) {
                //подбор подходящих пар запрос-товар
                $offerRequestApply = self::getLeads($arOffers, $arRequests);
            }
            unset($arOffers, $arRequests);
        }

        if((sizeof($offerRequestApply)>0)&&(is_array($offerRequestApply))){
            //если пары найдены то проверям есть у товаров уже созданные и активные Предложения (ВП)

            $offersIds = array();
            foreach($offerRequestApply as $i=>$item){
                $offersIds[$item['OFFER']['ID']] = 1;
            }
            //получаем данные по предложениям (ВП)
            $counterOffers = self::getCounterRequestsDataOrderId(array_keys($offersIds));

            if((sizeof($counterOffers)>0)&&(is_array($counterOffers))){
                foreach ($counterOffers as $offer_id=>$cur_data){
                    $sendData = array(
                        'offer_id' => $offer_id,
                        'selected_requests' => $request_id,
                        'farmer_id' => $cur_data['UF_FARMER_ID'],
                        'type' => $cur_data['UF_TYPE'],
                        'date' => $cur_data['UF_DATE'],
                        'price' => $cur_data['UF_FARMER_PRICE'],
                        'volume_offer' => $cur_data['UF_VOLUME_OFFER'],
                        'volume_remains' => $cur_data['UF_VOLUME_REMAINS'],
                        'UF_DELIVERY' => $cur_data['UF_DELIVERY'],
                        'UF_ADDIT_FIELDS' => $cur_data['UF_ADDIT_FIELDS'],
                        'UF_COFFER_TYPE' => $cur_data['UF_COFFER_TYPE'],
                        'UF_PARTNER_PRICE' => $cur_data['UF_PARTNER_PRICE'],
                        'UF_CREATE_BY_PARTNER' => $cur_data['UF_CREATE_BY_PARTNER'],
                        'UF_PARTNER_Q_APRVD' => $cur_data['UF_PARTNER_Q_APRVD'],
                        'UF_PARTNER_Q_APRVD_D' => $cur_data['UF_PARTNER_Q_APRVD_D'],
                        'UF_BY_PARTNER_REAL' => $cur_data['UF_BY_PARTNER_REAL'],
                    );
                    self::addCounterRequestA($sendData);
                }
            }
        }
    }

    /*
     * Общая функция проверки прав поставщика
     *
     * @param string $type - тип проверки прав (offer/request/warehouse/...)
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
                       'IBLOCK_ID' => rrsIblock::getIBlockId('farmer_profile'),
                       'PROPERTY_USER' => $id,
                       '!PROPERTY_INN' => false
                    ),
                    false,
                    false,
                    array('PROPERTY_USER', 'PROPERTY_INN')
                );
                if(is_numeric($id)){
                    //для конкретного пользователя
                    if($res->SelectedRowsCount() > 0) {
                        $result['REQUEST_RIGHT'] = 'Y';
                    }
                }else{
                    //для группы пользователей
                    while($data = $res->Fetch()){
                        $result[$data['PROPERTY_USER_VALUE']]['REQUEST_RIGHT'] = 'Y';
                    }
                }

                //проверка для конкретного встречного предложения, если требуется
                if(isset($result['REQUEST_RIGHT'])
                    && $result['REQUEST_RIGHT'] == 'Y'
                    && isset($additional_args['CHECK_BY_OFFER'])
                    && is_numeric($additional_args['CHECK_BY_OFFER'])
                ){
                    CModule::IncludeModule('highloadblock');
                    $arFilter = array('UF_OFFER_ID' => $additional_args['CHECK_BY_OFFER']);
                    $counter_request_data = log::_getEntitiesList(log::getIdByName('COUNTEROFFERS'), $arFilter);
                    if(count($counter_request_data) > 0){
                        //уже есть встречные предложения по данному предложению -> нет прав на создание повторного предложения
                        unset($result['REQUEST_RIGHT']);
                    }
                }

               break;
        }

        return $result;
    }

    /*
         * Получение отправленных встречных предложений
         *
         * @param int | array $offer_arr - массив товаров либо id
         * @param int | array $request_arr - массив запросов либо id (необязательный)
         *
         * @return array - массив данных по встречным предложениям
         * */
    public static function getCounterRequestsDataOrderId($offer_arr, $request_arr = array()){
        $result = array();

        CModule::IncludeModule('highloadblock');
        $offer_filter = $offer_arr;
        if(is_numeric($offer_filter)){
            $offer_filter = array($offer_filter);
        }
        $request_filter = $request_arr;
        if(is_numeric($request_filter)){
            $request_filter = array($request_filter);
        }

        if(count($offer_filter) == 0){
            $offer_filter = array(0);
        }

        $arFilter = array('UF_OFFER_ID' => $offer_filter);
        if(count($request_filter) > 0){
            $arFilter['UF_REQUEST_ID'] = $request_filter;
        }

        $counter_request_data = log::_getEntitiesList(log::getIdByName('COUNTEROFFERS'), $arFilter, 'ID');
        foreach ($counter_request_data as $item) {
            $result[$item['UF_OFFER_ID']] = array(
                'ID' => $item['ID'],
                'UF_DATE' => substr($item['UF_DATE']->toString(), 0, 19),
                'UF_VOLUME' => $item['UF_VOLUME'],
                'UF_TYPE' => $item['UF_TYPE'],
                'UF_DELIVERY' => $item['UF_DELIVERY'],
                'UF_FARMER_ID' => $item['UF_FARMER_ID'],
                'UF_FARMER_PRICE' => $item['UF_FARMER_PRICE'],
                'UF_REQUEST_ID' => $item['UF_REQUEST_ID'],
                'UF_FARMER_WH_ID' => $item['UF_FARMER_WH_ID'],
                'UF_VOLUME_OFFER' => $item['UF_VOLUME_OFFER'],
                'UF_VOLUME_REMAINS' => $item['UF_VOLUME_REMAINS'],
                'UF_ADDIT_FIELDS' => $item['UF_ADDIT_FIELDS'],
                'UF_COFFER_TYPE' => $item['UF_COFFER_TYPE'],
                'UF_PARTNER_PRICE' => $item['UF_PARTNER_PRICE'],
                'UF_CREATE_BY_PARTNER' => $item['UF_CREATE_BY_PARTNER'],
                'UF_PARTNER_Q_APRVD' => $item['UF_PARTNER_Q_APRVD'],
                'UF_PARTNER_Q_APRVD_D' => ($item['UF_PARTNER_Q_APRVD_D'] ? $item['UF_PARTNER_Q_APRVD_D']->toString() : ''),
                'UF_BY_PARTNER_REAL' => $item['UF_BY_PARTNER_REAL'],
            );
        }

        return $result;
    }

    /*
     * Получение отправленных встречных предложений
     *
     * @param int | array $offer_arr - массив товаров либо id
     * @param int | array $request_arr - массив запросов либо id (необязательный)
     *
     * @return array - массив данных по встречным предложениям
     * */
    public static function getCounterRequestsData($offer_arr, $request_arr = array()){
        $result = array();

        CModule::IncludeModule('highloadblock');
        $offer_filter = $offer_arr;
        if(is_numeric($offer_filter)){
            $offer_filter = array($offer_filter);
        }
        $request_filter = $request_arr;
        if(is_numeric($request_filter)){
            $request_filter = array($request_filter);
        }

        if(count($offer_filter) == 0){
            $offer_filter = array(0);
        }

        $arFilter = array('UF_OFFER_ID' => $offer_filter);
        if(count($request_filter) > 0){
            $arFilter['UF_REQUEST_ID'] = $request_filter;
        }

        $counter_request_data = log::_getEntitiesList(log::getIdByName('COUNTEROFFERS'), $arFilter);
        foreach($counter_request_data as $item){
            $result[$item['UF_OFFER_ID']] = array(
                'UF_DATE' => substr($item['UF_DATE']->toString(), 0, 16),
                'UF_VOLUME' => $item['UF_VOLUME'],
                'UF_TYPE' => $item['UF_TYPE'],
                'UF_DELIVERY' => $item['UF_DELIVERY'],
                'UF_FARMER_PRICE' => $item['UF_FARMER_PRICE'],
                'UF_FARMER_ID' => $item['UF_FARMER_ID'],
                'UF_REQUEST_ID' => $item['UF_REQUEST_ID'],
                'UF_FARMER_WH_ID' => $item['UF_FARMER_WH_ID'],
                'UF_VOLUME_OFFER' => $item['UF_VOLUME_OFFER'],
                'UF_VOLUME_REMAINS' => $item['UF_VOLUME_REMAINS'],
                'UF_COFFER_TYPE' => $item['UF_COFFER_TYPE'],
                'UF_PARTNER_PRICE' => $item['UF_PARTNER_PRICE'],
                'UF_ADDIT_FIELDS' => $item['UF_ADDIT_FIELDS'],
                'UF_PARTNER_Q_APRVD' => $item['UF_PARTNER_Q_APRVD'],
                'UF_PARTNER_Q_APRVD_D' => $item['UF_PARTNER_Q_APRVD_D'],
                'UF_BY_PARTNER_REAL' => $item['UF_BY_PARTNER_REAL'],
            );
        }

        return $result;
    }

    /**
     * Удаляет все встречные запросы АП по ID склада АП (удаление из HL)
     * @param $wh_id - ID склада/складов АП
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
            'UF_FARMER_WH_ID' => $wh_id
        ];
        $iHL        = log::getIdByName('COUNTEROFFERS');
        $arCounters = log::_getEntitiesList($iHL, $arFilter);


        foreach ($arCounters as $arCounterRequest) {
            log::_deleteEntity($iHL, $arCounterRequest['ID']);
        }
    }


    /**
     * Удаляет все встречные запросы АП по ID товара АП (удаление из HL)
     * @param $iOfferID - ID товара АП
     * @throws Exception
     */
    public static function removeCountersByOfferID($iOfferID) {
        // filter_var без проверки на false т.к 0 тоже не нужен
        if(!filter_var($iOfferID, FILTER_VALIDATE_INT)) {
            throw new Exception('Не передан ID товара');
        }
        CModule::IncludeModule('highloadblock');
        $arFilter   = [
            'UF_OFFER_ID' => $iOfferID
        ];
        $iHL        = log::getIdByName('COUNTEROFFERS');
        $arCounters = log::_getEntitiesList($iHL, $arFilter);
        foreach ($arCounters as $arCounterRequest) {
            log::_deleteEntity($iHL, $arCounterRequest['ID']);
        }
    }

    /**
     * Проверка сохранения фильтра на странице товаров поставщика
     *
     * @return array массив, где у ключа 'NEED_UPD' значение либо true либо false,
     * у ключа 'URL_UPD' значение адреса переадресации
     */
    public static function filterFarmerOffersCheck() {
        $result = array(
            'NEED_UPD'  => false,
            'URL_UPD'   => ''
        );
        if(isset($_GET['id'])
            && $_GET['id'] > 0){
            return $result;
        }

        if(((isset($_GET['warehouse_id']))&&(!empty($_GET['warehouse_id'])))||
            ((isset($_GET['culture_id']))&&(!empty($_GET['culture_id'])))){
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
        $warehouse_cookie = '';
        $culture_cookie = '';
        //проверка куки склада
        $cookie_name = 'farmer_offer_' . $tabFilterSuf . '_warehouse_id';
        if(isset($_COOKIE[$cookie_name])){
            $warehouse_cookie = $_COOKIE[$cookie_name];
            if((!isset($_GET['warehouse_id']) || $_GET['warehouse_id'] == '' || $_GET['warehouse_id'] == '0')
                && $warehouse_cookie != 0 && $warehouse_cookie != ''
            ){
                $result['NEED_UPD'] = true;
                $new_url_params[] = 'warehouse_id=' . $warehouse_cookie;
            }
        }
        //проверка куки культуры
        $cookie_name = 'farmer_offer_' . $tabFilterSuf . '_culture_id';
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
            $result['URL_UPD'] = '/farmer/offer/' . (count($new_url_params) > 0 ? '?' . implode('&', $new_url_params) : '');
        }
        return $result;
    }

    /**
     * Проверка сохранения фильтра на странице товаров поставщика (для организатора)
     *
     * @return array массив, где у ключа 'NEED_UPD' значение либо true либо false,
     * у ключа 'URL_UPD' значение адреса переадресации
     */
    public static function filterPartnerFarmerWHCheck() {
        $result = array(
            'NEED_UPD'  => false,
            'URL_UPD'   => ''
        );
        if(isset($_GET['id'])
            && $_GET['id'] > 0){
            return $result;
        }

        if(isset($_GET['farmer_id'][0])&&!empty($_GET['farmer_id'][0])){
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
        $cookie_name = 'partner_farmer_wh_' . $tabFilterSuf . '_farmer_id';
        if(isset($_COOKIE[$cookie_name])){
            $cookie_val = $_COOKIE[$cookie_name];
            if((!isset($_GET['farmer_id'][0]) || $_GET['farmer_id'][0] == '' || $_GET['farmer_id'][0] == '0')
                && $cookie_val != 0 && $cookie_val != ''
            ){
                $result['NEED_UPD'] = true;
                $new_url_params[] = 'farmer_id[]=' . $cookie_val;
            }
        }
        if($result['NEED_UPD']){
            $result['URL_UPD'] = '/partner/farmer_warehouses/' . (count($new_url_params) > 0 ? '?' . implode('&', $new_url_params) : '');
        }
        return $result;
    }

    /**
     * Проверка сохранения фильтра на странице запросов поставщика
     *
     * @return array массив, где у ключа 'NEED_UPD' значение либо true либо false,
     * у ключа 'URL_UPD' значение адреса переадресации
     */
    public static function filterFarmerRequestCheck(){
        $result = array(
            'NEED_UPD'  => false,
            'URL_UPD'   => ''
        );
        if(isset($_GET['r'])
            && $_GET['r'] > 0){
            return $result;
        }

        if(((isset($_GET['wh']))&&(!empty($_GET['wh'])))||
            ((isset($_GET['culture']))&&(!empty($_GET['culture'])))){
            return $result;
        }

        $page_need_update = false;
        $new_url_params = array();

        $warehouse_cookie = '';
        $culture_cookie = '';
        //проверка куки склада
        $cookie_name = 'farmer_requests_wh';
        if(isset($_COOKIE[$cookie_name])){
            $warehouse_cookie = $_COOKIE[$cookie_name];
            if((!isset($_GET['wh']) || $_GET['wh'] == '' || $_GET['wh'] == '0')
                && $warehouse_cookie != 0 && $warehouse_cookie != ''
            ){
                $result['NEED_UPD'] = true;
                $new_url_params[] = 'wh=' . $warehouse_cookie;
            }
        }
        //проверка куки культуры
        $cookie_name = 'farmer_requests_culture';
        if(isset($_COOKIE[trim($cookie_name)])){
            $culture_cookie = $_COOKIE[$cookie_name];
            if((!isset($_GET['culture']) || $_GET['culture'] == '' || $_GET['culture'] == '0')
                && $culture_cookie != 0 && $culture_cookie != ''
            ){
                $result['NEED_UPD'] = true;
                $new_url_params[] = 'culture=' . $culture_cookie;
            }
        }
        if($result['NEED_UPD']){
            $result['URL_UPD'] = '/farmer/request/' . (count($new_url_params) > 0 ? '?' . implode('&', $new_url_params) : '');
        }
        return $result;
    }

    /**
     * Проверка сохранения фильтра на странице пар фермеров
     *
     * @return array массив, где у ключа 'NEED_UPD' значение либо true либо false,
     * у ключа 'URL_UPD' значение адреса переадресации
     */
    public static function filterFarmerPairCheck(){
        $result = array(
            'NEED_UPD'  => false,
            'URL_UPD'   => ''
        );
        if(isset($_GET['id'])
            && $_GET['id'] > 0){
            return $result;
        }

        if(((isset($_GET['farmer_warehouse_id']))&&(!empty($_GET['farmer_warehouse_id'])))||
            ((isset($_GET['culture_id']))&&(!empty($_GET['culture_id'])))){
            return $result;
        }

        $page_need_update = false;
        $new_url_params = array();

        $warehouse_cookie = '';
        $culture_cookie = '';
        //проверка куки склада
        $cookie_name = 'farmer_deals_filter_farmer_warehouse_id';
        if(isset($_COOKIE[$cookie_name])){
            $warehouse_cookie = $_COOKIE[$cookie_name];
            if((!isset($_GET['farmer_warehouse_id']) || $_GET['farmer_warehouse_id'] == '' || $_GET['farmer_warehouse_id'] == '0')
                && $warehouse_cookie != 0 && $warehouse_cookie != ''
            ){
                $result['NEED_UPD'] = true;
                $new_url_params[] = 'farmer_warehouse_id=' . $warehouse_cookie;
            }
        }
        //проверка куки культуры
        $cookie_name = 'farmer_deals_filter_culture_id';
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
            $result['URL_UPD'] = '/farmer/pair/' . (count($new_url_params) > 0 ? '?' . implode('&', $new_url_params) : '');
        }
        return $result;
    }


    /**
     * Проверка сохранения фильтра на странице черного списка фермеров
     *
     * @return array массив, где у ключа 'NEED_UPD' значение либо true либо false,
     * у ключа 'URL_UPD' значение адреса переадресации
     */
    public static function filterFarmerBLCheck(){
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
            if((!isset($_GET['reasond_id']) || $_GET['reasond_id'] == '' || $_GET['reasond_id'] == '0')
                && $reason_id_cookie != 0 && $reason_id_cookie != ''
            ){
                $result['NEED_UPD'] = true;
                $new_url_params[] = 'reasond_id=' . $reason_id_cookie;
            }
        }
        if($result['NEED_UPD']){
            $result['URL_UPD'] = '/farmer/blacklist/' . (count($new_url_params) > 0 ? '?' . implode('&', $new_url_params) : '');
        }
        return $result;
    }


    /*
    * Получение записи черного списка покупателей для поставщика
    * @param int $user_id - id поставщика
    *
    * @return array - массив данных с id покупателей из черного списка поставщика
    * */
    public static function getUserBlackList($user_id){
        $result = array();

        $el_obj = new CIBlockElement;
        $res = $el_obj->GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('farmer_black_list'),
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

    /**
     * Получение покупателей у которых в черном списке состоит указанный поставщик
     * @param int $user_id ID пользователь
     * @return array массив ID покупателей
     */
    public static function getBlackListWhereOpponent($user_id){
        $result = array();

        CModule::IncludeModule('iblock');
        $el_obj = new CIBlockElement;
        $res = $el_obj->GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('client_black_list'),
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
     * Получение списка покупателей которые в черном списке выбранного поставщика или у которых в черном списке состоит указанный поставщик
     * @param int $user_id ID пользователь
     * @return array массив ID покупателей
     */
    public static function getBlackListTotal($user_id){
        $result = array();

        CModule::IncludeModule('iblock');
        $el_obj = new CIBlockElement;

        //получение данных тех, кто в ЧС пользователя
        $res = $el_obj->GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('farmer_black_list'),
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
                'IBLOCK_ID' => rrsIblock::getIBlockId('client_black_list'),
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
     * Получение данных поставщика
     * @param int $user_id ID поставщика
     * @return string данные поставщика
     */
    public static function getUserData($user_id){
        $result = '';

        CModule::IncludeModule('iblock');
        $res = CIBlockElement::GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('farmer_profile'),
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
     * Получение данных поставщиков (названий компаний, фио, телефонов)
     * @param array $arrUsers ID поставщиков
     * @return array массив с данными
     */
    public static function getUserListData($arrUsers){
        $arrResult = array();

        $res = CIBlockElement::GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('farmer_profile'),
                'PROPERTY_USER' => $arrUsers
            ),
            false,
            false,
            array('PROPERTY_USER', 'PROPERTY_FULL_COMPANY_NAME', 'PROPERTY_IP_FIO', 'PROPERTY_PHONE')
        );
        while($data = $res->Fetch()) {
            if (!empty($data['PROPERTY_USER_VALUE'])) {
                if (trim($data['PROPERTY_FULL_COMPANY_NAME_VALUE']) != '') {
                    $arrResult[$data['PROPERTY_USER_VALUE']]['FIO'] = trim($data['PROPERTY_FULL_COMPANY_NAME_VALUE']);
                } else {
                    $arrResult[$data['PROPERTY_USER_VALUE']]['FIO'] = trim($data['PROPERTY_IP_FIO_VALUE']);
                }
            }

            if (!empty($data['PROPERTY_PHONE_VALUE'])) {
                $arrResult[$data['PROPERTY_USER_VALUE']]['PHONE'] = trim($data['PROPERTY_PHONE_VALUE']);
            }
        }

        if(count($arrResult) > 0){
            $res = CUser::GetList(
                ($by = 'id'), ($order = 'asc'),
                array('ID' => implode(' | ', array_keys($arrResult))),
                array('FIELDS' => array('ID', 'EMAIL', 'NAME', 'LAST_NAME'))
            );
            while($data = $res->Fetch()){
                if($arrResult[$data['ID']]['FIO'] == '') {
                    $arrResult[$data['ID']]['FIO'] = trim($data['NAME'] . ' ' . $data['LAST_NAME']);
                    if ($arrResult[$data['ID']]['FIO'] == '') {
                        if (!checkEmailFromPhone($data['EMAIL'])) {
                            $arrResult[$data['ID']]['FIO'] = $data['EMAIL'];
                        } else {
                            $arrResult[$data['ID']]['FIO'] = $data['ID'];
                        }
                    }
                }
            }
        }

        return $arrResult;
    }

    /**
     * Проверка лимита на доступные товары
     * @param $user_id - ID покупателя
     * @return array - массив, где CNT - общее количество разрешенных поставщику товаров, REMAINS - оставшееся разрешенное количество
     */
    public static function checkAvailableOfferLimit($user_id){
        $result = array('CNT' => 0, 'REMAINS' => 0);

        if(!is_numeric($user_id)){
            return $result;
        }

        CModule::IncludeModule('iblock');
        $el_obj = new CIBlockElement;

        //получение константы ограничения
        $current_const = intval(rrsIblock::getConst('min_offer_limit'));
        if($current_const > 0){
            $result['CNT'] = $result['CNT'] + $current_const;
        }

        //индивидуальное дополнительное к общему ограничению
        $res = $el_obj->GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('farmer_profile'),
                'PROPERTY_USER' => $user_id
            ),
            false,
            false,
            array('PROPERTY_OFFER_LIMIT')
        );
        if($data = $res->Fetch()){
            $temp_val = intval($data['PROPERTY_OFFER_LIMIT_VALUE']);
            if($temp_val > 0){
                $result['CNT'] = $result['CNT'] + $temp_val;
            }
        }

        //проверка наличия текущих активных товаров у поставщиков
        $res = $el_obj->GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('farmer_offer'),
                'ACTIVE' => 'Y',
                'PROPERTY_FARMER' => $user_id,
                'PROPERTY_ACTIVE' => rrsIblock::getPropListKey('farmer_offer', 'ACTIVE', 'yes'),
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

    /*
     * Возвращает текст для истории изменения ограничения товаров
     * @param string $action_code - код действия
     * @param int $number - величина изменения (отрицательная при уменьшении)
     *
     * @return string - текст
     * */
    public static function offerLimitDefaultText($action_code, $number){
        $result = '-';

        switch($action_code){
            case 'change':
                //для изменения значения (+/-)
                if($number > 0){
                    $result = 'Увеличение лимита товаров (' . $number . ')';
                }else{
                    $result = 'Уменьшение лимита товаров (' . $number . ')';
                }
                break;

            case 'set':
                //для установления значения
                $result = 'Установление лимита товаров (' . $number . ')';
                break;
        }

        return $result;
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
    public static function offerLimitQuantityChange($action_type, $number, $uids, $element_id = 0, $comment_text = ''){
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
                        'IBLOCK_ID' => rrsIblock::getIBlockId('farmer_profile')
                    );
                    if(count($uids) > 0){
                        $arFilter['PROPERTY_USER'] = $uids;
                    }

                    $res = $el_obj->GetList(
                        array('ID' => 'ASC'),
                        $arFilter,
                        false,
                        false,
                        array('ID', 'PROPERTY_USER', 'PROPERTY_OFFER_LIMIT')
                    );
                    while($data = $res->Fetch()){
                        //установление указанного значения пользователя
                        $cur_val = (isset($data['PROPERTY_OFFER_LIMIT_VALUE']) ? intval($data['PROPERTY_OFFER_LIMIT_VALUE']) : 0);
                        $new_val = $cur_val + $number;
                        if($new_val < 0)
                            $new_val = 0;
                        CIBlockElement::SetPropertyValuesEx($data['ID'], $arFilter['IBLOCK_ID'], array('OFFER_LIMIT' => $new_val));

                        //запись в hl iblock
                        self::offerLimitChangeLog($data['PROPERTY_USER_VALUE'], $action_type, $cur_date, $created_by, $number, $cur_val, $new_val, $element_id);
                    }

                    //отправка уведомления покупателям на email (только активным пользователям с почтами не из телефона)
                    $arFilter = array(
                        'ACTIVE' => 'Y',
                        'GROUPS_ID' => 11
                    );
                    if(count($uids) > 0){
                        $arFilter['ID'] = implode('|', $uids);
                    }
                    $mailFields = array(
                        'URL' => $GLOBALS['host'] . '/farmer/profile/limits_history/',
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
                            CEvent::Send('OFFERLIMITCHANGEUSER', 's1', $mailFields);
                        }
                    }

                    //получаем данные пользователей, у которых появились "лишние" товары после изменения ограничения
                    self::checkOfferOverLimitAfterUpdate($uids);

                    break;

                case 'set':
                    //установление указанного значения для пользователя
                    $arFilter = array(
                        'IBLOCK_ID' => rrsIblock::getIBlockId('farmer_profile')
                    );
                    if(count($uids) > 0){
                        $arFilter['PROPERTY_USER'] = $uids;
                    }

                    $res = $el_obj->GetList(
                        array('ID' => 'ASC'),
                        $arFilter,
                        false,
                        false,
                        array('ID', 'PROPERTY_USER', 'PROPERTY_OFFER_LIMIT')
                    );
                    while($data = $res->Fetch()){
                        //установление указанного значения пользователя
                        $cur_val = (isset($data['PROPERTY_OFFER_LIMIT_VALUE']) ? intval($data['PROPERTY_OFFER_LIMIT_VALUE']) : 0);
                        $new_val = $number;
                        CIBlockElement::SetPropertyValuesEx($data['ID'], $arFilter['IBLOCK_ID'], array('OFFER_LIMIT' => $new_val));

                        //запись в hl iblock
                        self::offerLimitChangeLog($data['PROPERTY_USER_VALUE'], $action_type, $cur_date, $created_by, $number, $cur_val, $new_val, $element_id);
                    }

                    //отправка уведомления покупателям на email (только активным пользователям с почтами не из телефона)
                    $arFilter = array(
                        'ACTIVE' => 'Y',
                        'GROUPS_ID' => 11
                    );
                    if(count($uids) > 0){
                        $arFilter['ID'] = implode('|', $uids);
                    }
                    $mailFields = array(
                        'URL' => $GLOBALS['host'] . '/farmer/profile/limits_history/',
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
                            CEvent::Send('OFFERLIMITCHANGEUSER', 's1', $mailFields);
                        }
                    }

                    //получаем данные пользователей, у которых появились "лишние" товары после изменения ограничения
                    self::checkOfferOverLimitAfterUpdate($uids);

                    break;
            }
        }
    }

    /*
     * Запись события в лог ограничения товаров (отдельная функция на случай разделения хранения истории)
     * @param int $uid - ID пользователя
     * @param string $action - код типа действия
     * @param string $date - дата создания записи
     * @param int $created_by - ID пользователя, создавшего запись
     * @param int $num_work - величина изменения ограничения
     * @param int $num_before - значение ограничений до изменения
     * @param int $num_after - значение ограничений после изменения
     * @param int $elem_id - ID связанного элемента в ИБ "Ограничение количества товаров" (farmer_offer_limits_changes), необязательный параметр
     *
     * @return boolean - флаг успешности записи в лог
     * */
    public static function offerLimitChangeLog($uid, $action, $date, $created_by, $num_work, $num_before, $num_after, $elem_id = 0){
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
                'UF_BEFORE' => $num_before +  intval(rrsIblock::getConst('min_offer_limit')),
                'UF_AFTER' => $num_after + intval(rrsIblock::getConst('min_offer_limit')),
                'UF_ELEMENT_ID' => $elem_id,
                'UF_ENTITY_TYPE' => 'farmer_offer_limit'
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
     * Проверка указанных пользователей на превышение лимита товаров
     * @param array $user_id - массив ID поставщиков (проверить всех активных, если параметр пуст)
     */
    public static function checkOfferOverLimitAfterUpdate($user_id){

        $check_users = array();
        if(is_array($user_id)
            && count($user_id) > 0
        ){
            $check_users = $user_id;
        }
        else{
            //получаем всех активных поставщиков
            $res = CUser::GetList(
                ($by = 'id'), ($order = 'asc'),
                array(
                    'ACTIVE' => 'Y',
                    'GROUPS_ID' => array(11)
                ),
                array('FIELDS' => array('ID'))
            );
            while($data = $res->Fetch()){
                $check_users[] = $data['ID'];
            }
        }

        if(count($check_users) > 0){
            //проверяем остатки товаров и ограничения пользователей
            $check_arr = agent::checkAvailableOfferLimit($check_users, true);
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
                                'IBLOCK_ID' => rrsIblock::getIBlockId('farmer_offer'),
                                'ACTIVE' => 'Y',
                                'PROPERTY_FARMER' => $cur_user_id,
                                'PROPERTY_ACTIVE' => rrsIblock::getPropListKey('farmer_offer', 'ACTIVE', 'yes'),
                            ),
                            false,
                            array('nTopCount' => $cur_data['OVERLIM']),
                            array('ID')
                        );
                        while($data = $res->Fetch()){
                            self::deactivateOffer($data['ID'], false);
                        }
                    }
                }
            }
        }
    }

    /**
     * Получение страницы для переадресации, если текущая страница со списком товаров не содержит требуемый
     * @param int $off_id id товара
     * @param int $page_size количество элементов на странице
     * @param int $page_value номер текущей страницы
     * @return string адрес страницы
     */
    public static function getOfferListRedirectById($off_id, $page_size, $page_value = 1) {
        $result = '';
        CModule::IncludeModule('iblock');
        $ib_id = rrsIblock::getIBlockId('farmer_offer');
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
        $arFilter = array(
            'IBLOCK_ID' => $ib_id
        );
        if(isset($GLOBALS['arrFilter']['PROPERTY_FARMER'])
            && is_array($GLOBALS['arrFilter']['PROPERTY_FARMER'])
            && count($GLOBALS['arrFilter']['PROPERTY_FARMER']) > 0
        ){
            $arFilter['PROPERTY_FARMER'] = $GLOBALS['arrFilter']['PROPERTY_FARMER'];
        }
        if(isset($GLOBALS['arrFilter']['PROPERTY_ACTIVE'])
            && filter_var($GLOBALS['arrFilter']['PROPERTY_ACTIVE'], FILTER_VALIDATE_INT)
        ){
            $arFilter['PROPERTY_ACTIVE'] = $GLOBALS['arrFilter']['PROPERTY_ACTIVE'];
        }
        if(isset($GLOBALS['arrFilter']['PROPERTY_CULTURE'])
            && filter_var($GLOBALS['arrFilter']['PROPERTY_CULTURE'], FILTER_VALIDATE_INT)
        ){
            $arFilter['PROPERTY_CULTURE'] = $GLOBALS['arrFilter']['PROPERTY_CULTURE'];
        }
//        p($GLOBALS['arrFilter']);
//        p($arFilter);
        $res = $el_obj->GetList(
            array(
                'ID' => 'DESC',
                'SORT' => 'ASC'
            ),
            $arFilter,
            false,
            array(
                'nElementID' => $off_id,
                'nPageSize' => 0
            )
        );
        if($data = $res->Fetch()){
            if(isset($data['RANK'])
                && filter_var($data['RANK'], FILTER_VALIDATE_INT)
            ){
                $new_page = ceil($data['RANK'] / $page_size);

                if($page_value != $new_page) {
                    global $APPLICATION;
                    if ($new_page > 1) {
                        $result = $APPLICATION->GetCurPageParam('PAGEN_1=' . $new_page, array('PAGEN_1'));
                    } else {
                        $result = $APPLICATION->GetCurPageParam('', array('PAGEN_1'));
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Генерация текста для создание предложения от организатора поставщику
     * @param int $offer_id - id товара
     * @param string $straight_href - прямая ссылка (текст или оформленный html тег)
     * @param boolean $no_best_price - не возвращать лучшую цену (по умолчанию возвращать)
     * @param boolean $set_global_difference - возвращать ли в глобальной переменной наличие разницы в цене (нужно для рассылки писем организаторам) (по умолчанию не возвращать)
     * @param boolean $bRoundRecommendPrice - округлять ли рекомендуемую цену до 50 (по умолчанию нет)
     * @param boolean $bIsPopup - флаг того куда отправляется текст (в рассылку или попап) (по умолчанию - рассылка)
     * @param array $arrSpros - данные спроса для товара за последние два дня (позавчера и вчера)
     *
     * @return string текст для отображения поставщику
     */
    public static function partnerCreateCounterRequestText($offer_id, $straight_href, $no_best_price = false, $set_global_difference = false, $bRoundRecommendPrice = false, $bIsPopup = false, $arrSpros = array()) {
        $result = '';

        CModule::IncludeModule('iblock');
        CModule::IncludeModule('highloadblock');
        $warehouse_name = '';
        $culture_name = '';
        $best_price = 0;
        $market_price_before_yest = 0;
        $market_price_yest = 0;
        $market_diff = 0;
        $recomend_price = 0;

        //получаем необходимые данные
        //получаем название склада и культуры
        $offerData = farmer::getOfferById($offer_id);
        $warehouse_name = $offerData['WH_NAME'];
        $culture_name = $offerData['CULTURE_NAME'];

        //получаем рынок за вчера/позавчера
        $before_yest_date = ConvertTimeStamp(strtotime('-2 days'));
        $yest_date = ConvertTimeStamp(strtotime('-1 day'));
        $wh_list = array();
        $cultures_list = array();
        $deals_cur_reg_arr = array();
        $deals_linked_reg_arr = array();
        farmer::getWHAndCulturesByOffers($offer_id, $wh_list, $cultures_list); //$wh_list и $cultures_list наполняются в этой функции
        //получение складов в текщих регионах и связанных регионах
        $wh_at_cur_regions = farmer::getWHAtCurrentRegion($wh_list);
        $wh_at_linked_regions = farmer::getWHAtLinkedRegions($wh_list);
        //получение списка окружающих складов (отдельно для текущих регионов, отдельно для связанных регионов, согласно задаче #12789)
        $wh_at_cur_regions_list = array();
        foreach ($wh_at_cur_regions as $cur_wh => $cur_arr){
            foreach ($cur_arr as $cur_wh_id){
                $wh_at_cur_regions_list[$cur_wh_id] = true;
            }
        }
        $wh_at_linked_regions_list = array();
        foreach ($wh_at_linked_regions as $cur_wh => $cur_arr){
            foreach ($cur_arr as $cur_wh_id){
                $wh_at_linked_regions_list[$cur_wh_id] = true;
            }
        }
        //получение средневзвешенных цен по складам с учетом культур и дат (в текущих регионах складов)
        $temp_deals_data = deal::getByWHAndCultures($wh_at_linked_regions_list, $cultures_list, '-2 days', $offerData['USER_NDS'] == 'yes');
        unset($wh_list, $cultures_list, $wh_at_cur_regions, $wh_at_linked_regions,  $wh_at_linked_regions_list);
        //складываем полученные цены по датам (дата является уникальным ключом, т.к. культуры и склады у нас берутся только для выбранного товара)
        if(count($temp_deals_data) > 0){
            foreach ($temp_deals_data as $cur_warehouse => $cur_wh_data){
                //если склад находится в связанном регионе
                if(!isset($wh_at_cur_regions_list[$cur_warehouse ])) {
                    foreach ($cur_wh_data as $cur_culture => $culture_data) {
                        foreach ($culture_data as $cur_date => $cur_price) {
                            $deals_linked_reg_arr[$cur_date][] = $cur_price;
                        }
                    }
                }
                //если склад находится в текущем регионе
                else{
                    foreach ($cur_wh_data as $cur_culture => $culture_data) {
                        foreach ($culture_data as $cur_date => $cur_price) {
                            $deals_cur_reg_arr[$cur_date][] = $cur_price;
                            $deals_linked_reg_arr[$cur_date][] = $cur_price;
                        }
                    }
                }
            }
        }
        //высчитываем средневзвешенную цену для каждой даты
        if(count($deals_linked_reg_arr) > 0){ //если есть данные за связанные регионы, то возможно есть и за регион товара
            foreach($deals_cur_reg_arr as $cur_date => $cur_prices){
                $temp_price = 0;
                foreach($cur_prices as $cur_price){
                    $temp_price += $cur_price;
                }
                if(count($cur_prices) > 0) {
                    if($cur_date == $before_yest_date){
                        $market_price_before_yest = round($temp_price / count($cur_prices));
                    }elseif($cur_date == $yest_date){
                        $market_price_yest = round($temp_price / count($cur_prices));
                    }
                }
            }
            if($market_price_before_yest < 1
                ||
                $market_price_yest < 1
            ){ //данных для региона нет, проверяем данные для связанных регионов
                $market_price_yest = 0;
                $market_price_before_yest = 0;
                foreach($deals_linked_reg_arr as $cur_date => $cur_prices){
                    $temp_price = 0;
                    foreach($cur_prices as $cur_price){
                        $temp_price += $cur_price;
                    }
                    if(count($cur_prices) > 0) {
                        if($cur_date == $before_yest_date){
                            $market_price_before_yest = round($temp_price / count($cur_prices));
                        }elseif($cur_date == $yest_date){
                            $market_price_yest = round($temp_price / count($cur_prices));
                        }
                    }
                }
            }
        }
        $market_diff = round($market_price_yest - $market_price_before_yest);
        unset($temp_deals_data, $deals_cur_reg_arr, $deals_linked_reg_arr);

        //получаем лучшую цену с места (за вчера)
        if(!$no_best_price) {
            $best_prices_ib = rrsIblock::HLgetIBlockId('BESTOFFERPRICES');
            $log_obj = new log();
            $entity_class = $log_obj->getEntityDataClass($best_prices_ib);
            $hl_el = new $entity_class;
            $res = $hl_el->getList(array(
                'select' => array('UF_BEST_CSM_PRICE', 'UF_DATE'),
                'filter' => array(
                    'UF_OFFER_ID' => $offer_id,
                    '>=UF_DATE' => $yest_date,
                    '>UF_BEST_CSM_PRICE' => 0
                ),
                'order' => array('UF_DATE' => 'ASC')
            ));
            while ($data = $res->fetch()) {
                $temp_date = $data['UF_DATE']->toString();
                if ($temp_date == $yest_date) {
                    $best_price = $data['UF_BEST_CSM_PRICE'];
                }
            }
        }

        //получаем рекомендованную цену
        $recomend_price = deal::getRecommendedPrice($offer_id, ($offerData['USER_NDS'] == 'yes' ? 'y' : 'n'));

        //составляем текст

        $result = "На Вашем складе \"{$warehouse_name}\" по товару \"{$culture_name}\":<br><br>";
        $sMarketText = '';
        if(
            $market_diff != 0
            && $market_price_yest > 0
        ){
            if($market_diff > 0){
                $market_diff = '+' . number_format($market_diff, 0, ',', ' ');
            }else{
                $market_diff = number_format($market_diff, 0, ',', ' ');
            }

            if($market_price_before_yest > 0)
            {
                //если нужно, то возвращаем разницу на рынке в глобальной переменной
                if($set_global_difference){
                    $GLOBALS['MARKET_DIFF'] = $market_diff;
                }

                //$market_price_yest = number_format($market_price_yest, 0, ',', ' ');
                //$result .= "Рынок: {$market_price_yest} руб/т" . ($market_price_before_yest > 0 ? " ({$market_diff} руб/т)" : "") . "<br><br>";
                //$result .= "Рынок: {$market_diff} руб/т<br><br>";
                $sMarketText .= "Рынок: {$market_diff} руб/т<br><br>";
            }
        }
        $result .= $sMarketText;
        if($best_price > 0){
            $best_price = number_format($best_price, 0, ',', ' ');
            $result .= "Лучшая цена \"с места\": {$best_price} руб/т<br><br>";
        }
        //получаем спрос, если нужно
        $sSprosText = '';
        if(
            is_array($arrSpros)
            && count($arrSpros) > 0
        ){
            if(!empty($arrSpros['Y'])){
                $sSprosText = 'Спрос: ' . number_format($arrSpros['Y'], 0, ',', ' ') . ' руб/т';
                //если есть изменение, добавляем
                if(!empty($arrSpros['CH'])){
                    $sSprosText = $sSprosText . ' ('
                        . ($arrSpros['CH'] > 0 ? '+' : '')
                        . number_format($arrSpros['CH'], 0, ',', ' ') . ' руб/т)';
                }
                $sSprosText = $sSprosText . '<br>';
            }
        }
        if($recomend_price > 0){
            //округление до 50, если нужно (<=25 округляется до 0, <=75 округляется до 50, >75 округляется до 100)
            if($bRoundRecommendPrice){
                $iTempMod = $recomend_price % 100;
                $recomend_price = floor($recomend_price / 100) * 100 + ($iTempMod > 75 ? 100 : ($iTempMod > 25 ? 50 : 0));
            }

            $recomend_price = number_format($recomend_price, 0, ',', ' ');
            $result .= "Рекомендуем продать \"с места\" по: {$recomend_price} руб/т (" . ($offerData['USER_NDS'] == 'yes' ? 'с' : 'без') . " НДС)<br>";
        }
        if($sSprosText != ''){
            $result .= '<br>' . $sSprosText;
        }

        $result .= "<br>Предложите свою цену по ссылке: {$straight_href}";

        $sTemplateText = '';
        if($bIsPopup){ 
            $sTemplateText = popupTemplates::getOrgCounterRequestFarmerPopupTemplate();
        }else{
            $sTemplateText = popupTemplates::getOrgCounterRequestFarmerTemplate();
        }
        //если есть шаблон, то отправляем по нему, иначе отправляем по старому
        if($sTemplateText != ''){
            $result = str_replace(
                array(
                    '#ORG_CREQ_WH_NAME#',
                    '#ORG_CREQ_CULTURE#',
                    '#ORG_CREQ_CSMPRICE#',
                    '#ORG_CREQ_NDS#',
                    '#ORG_CREQ_MARKETPRICE#',
                    '#ORG_CREQ_SPROSPRICE#',
                    '#ORG_CREQ_HREF#',
                ),
                array(
                    $warehouse_name,
                    $culture_name,
                    $recomend_price,
                    ($offerData['USER_NDS'] == 'yes' ? 'с НДС' : 'без  НДС'),
                    $sMarketText,
                    $sSprosText,
                    $straight_href
                ),
                $sTemplateText
            );
        }

        return $result;
    }

    /**
     * Получение привязанных к поставщику организаторов
     * @param int $user_id идентификатор поставщика
     * @param boolean $bGetLast - получить ID только одного последнего организатора
     * @return array идентификаторов организатора
     */
    public static function getLinkedPartnerList($user_id, $bGetLast = false) {
        $result = array();
        CModule::IncludeModule('iblock');
        $arOrder = array('ID' => ($bGetLast ? 'DESC' : 'ASC')); //по умолчанию ID возрастает, но если нужно взять только последнего, то ставим сортировку по убиванию
        $arAddit = ($bGetLast ? array('nTopCount' => 1) : false);

        $res = CIBlockElement::GetList(
            $arOrder,
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('farmer_agent_link'),
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
     * Получение имени склада поставщика по его ID
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
     * Является ли пользователь поставщиком организатора
     * @param $farmerId     - ID фермера
     * @param $partnerId    - ID партнера
     */
    public static function isFarmerPartner($farmerId,$partnerId){
        CModule::IncludeModule('iblock');
        if((!empty($farmerId))&&(!empty($partnerId))){
            $res = CIBlockElement::GetList(
                array('ID' => 'ASC'),
                array(
                    'IBLOCK_ID' => rrsIblock::getIBlockId('farmer_agent_link'),
                    'ACTIVE' => 'Y',
                    'PROPERTY_USER_ID' => $farmerId,
                    'PROPERTY_AGENT_ID' => $partnerId
                ),
                false,
                false,
                array('ID')
            );
            if($ob = $res->Fetch()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Получение регионов и связанных регионов по списку товаров, сгруппированных по товарам
     * @param array $offer_ids - массив ID товаров
     * @return array - регионов и связанные регионы, сгруппированные по товарам
     */
    public static function getLinkedRegionsForOffers($offer_ids){
        $result = array();

        if(filter_var($offer_ids, FILTER_VALIDATE_INT)){
            $offer_ids = array($offer_ids);
        }

        if(is_array($offer_ids)
            && count($offer_ids) > 0
        ){
            CModule::IncludeModule('iblock');
            //получаем регионы товаров
            $temp_arr = array();
            $region_ids = array();
            $res = CIBlockElement::GetList(
                array('ID' => 'ASC'),
                array(
                    'IBLOCK_ID' => rrsIblock::getIBlockId('farmer_offer'),
                    'ID' => $offer_ids
                ),
                false,
                false,
                array('ID', 'PROPERTY_WAREHOUSE.PROPERTY_REGION')
            );
            while($data = $res->Fetch()){
                $temp_arr[$data['PROPERTY_WAREHOUSE_PROPERTY_REGION_VALUE']][] = $data['ID'];
            }

            //получаем связанные регионы
            if(count($temp_arr) > 0) {
                $res = CIBlockElement::GetList(
                    array('ID' => 'ASC'),
                    array(
                        'IBLOCK_ID' => rrsIblock::getIBlockId('linked_regions'),
                        'PROPERTY_REGION' => array_keys($temp_arr)
                    ),
                    false,
                    false,
                    array('PROPERTY_REGION', 'PROPERTY_LINKED')
                );
                while ($data = $res->Fetch()) {
                    //группируем регионы и связанные регионы по товарам
                    if(isset($temp_arr[$data['PROPERTY_REGION_VALUE']])){
                        foreach($temp_arr[$data['PROPERTY_REGION_VALUE']] as $cur_offer){
                            $result[$cur_offer][] = $data['PROPERTY_REGION_VALUE'];
                            foreach($data['PROPERTY_LINKED_VALUE'] as $cur_linked_reg){
                                $result[$cur_offer][] = $cur_linked_reg;
                            }
                        }
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Возвращает массив ID запросов из $arRequests, сгруппированных по товарам и соответенным по регионам товаров (т.е. это запросы, сгруппированные по товарам, которым они подходят по регионам товаров и связанными регионами)
     * @param array $offer_with_linked_regs - массив ID товаров и их регионов (с учётом связанных регионов)
     * @param array $requests_ids - массив ID рассматриваемых запросов
     * @return array - массив ID запросов из $arRequests, сгруппированных по товарам и соответенным по регионам товаров
     */
    public static function checkRequestsAllowForOffersByRegions($offer_with_linked_regs, $requests_ids){
        $result = array();

        if(count($offer_with_linked_regs) > 0
            && count($requests_ids) > 0
        ) {

            $temp_arr = array();

            //соотносим регионы с товарами
            foreach ($offer_with_linked_regs as $cur_offer => $cur_regions) {
                foreach ($cur_regions as $cur_reg) {
                    $temp_arr[$cur_reg][] = $cur_offer;
                }
            }

            //для последующей фильтрации получаем склады стоимостей выбранных запросов
            $used_wh_ids = array();
            CModule::IncludeModule('iblock');
            $res = CIBlockElement::GetList(
                array('ID' => 'ASC'),
                array(
                    'IBLOCK_ID' => rrsIblock::getIBlockId('client_request_cost'),
                    'PROPERTY_REQUEST' => $requests_ids
                ),
                false,
                false,
                array('PROPERTY_WAREHOUSE')
            );
            while ($data = $res->Fetch())
            {
                $used_wh_ids[$data['PROPERTY_WAREHOUSE_VALUE']] = true;
            }

            if(count($used_wh_ids) > 0) {
                //получаем запросы, по выбранным регионам через стоимости и склады
                $wh_data = array();
                $res = CIBlockElement::GetList(
                    array('ID' => 'ASC'),
                    array(
                        'IBLOCK_ID' => rrsIblock::getIBlockId('client_warehouse'),
                        'PROPERTY_REGION' => array_keys($temp_arr),
                        'ID' => array_keys($used_wh_ids)
                    ),
                    false,
                    false,
                    array('ID', 'PROPERTY_REGION')
                );
                while ($data = $res->Fetch()) {
                    $wh_data[$data['ID']] = $data['PROPERTY_REGION_VALUE'];
                }

                //получаем запросы из стоимостей
                if (count($wh_data) > 0) {
                    $res = CIBlockElement::GetList(
                        array('ID' => 'ASC'),
                        array(
                            'IBLOCK_ID' => rrsIblock::getIBlockId('client_request_cost'),
                            'PROPERTY_WAREHOUSE' => array_keys($wh_data)
                        ),
                        false,
                        false,
                        array('PROPERTY_WAREHOUSE', 'PROPERTY_REQUEST')
                    );
                    while ($data = $res->Fetch()) {
                        if(isset($wh_data[$data['PROPERTY_WAREHOUSE_VALUE']])
                            && isset($temp_arr[$wh_data[$data['PROPERTY_WAREHOUSE_VALUE']]])
                        ){
                            foreach($temp_arr[$wh_data[$data['PROPERTY_WAREHOUSE_VALUE']]] as $cur_offer){
                                $result[$cur_offer][$data['PROPERTY_REQUEST_VALUE']] = true;
                            }
                        }
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Получение данных формы создания предложения для товара
     * @param int $iOfferId - ID товара
     * @param int $iVolume - предустановленный объем
     * @return string - флаг успешности отмены встречного предложения
     */
    public static function counterOfferFormAfterDeleting($iOfferId, $iVolume = 0){
        $sResult = '';

        $sNdsVal = 'n';

        //получение наличия встречных запросов для товаров (в этом случае нельзя создавать повторные встречные предложения)
        $counter_requests_data = farmer::getCounterRequestsData($iOfferId);

        //получение начальной цены и ограничений для ввода (работа с данными соответствий)
        //получаем соответствия для товаров
        $arLeads = lead::getLeadList(array('UF_OFFER_ID' => $iOfferId), ['UF_CSM_PRICE' => 'DESC']);
        $offerRequestApply = lead::createLeadList($arLeads);

        //получаем максимальную (+10% к максимальной цене) и минимальную цены (-10% от минимальной цены) для ограничений, а также цену для установления по умолчанию (берется максимальная из имеющихся цен в $offerRequestApply)
        $temp_val = 0;
        $min_val = 0;
        $max_val = 0;
        $set_val = 0;
        $req_ids = array();

        foreach ($offerRequestApply as $cur_data) {
            if ($cur_data['OFFER']['ID'] == $iOfferId) {
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
                    $sNdsVal = 'y';
                }
            }
        }
        $set_val = round($max_val);
        $max_val = round($max_val + $max_val * 0.2);
        $min_val = round($min_val - $min_val * 0.2);

        $rec_value = deal::getRecommendedPriceText($iOfferId, true, $sNdsVal, array('rec_price' => true));

        //выводим данные в форму (эти данные вставятся в форму в script.js)
        $sResult = farmer::getCounterOfferFormData($iOfferId, $set_val, $min_val, $max_val, (!empty($rec_value['rec_price']) ? $rec_value['rec_price'] : 0), (count($req_ids) > 0 ? array_keys($req_ids) : array()), $iVolume);

        return $sResult;
    }

    /**
     * Получение данных формы создания предложения для товара
     * @param int $iOfferId - ID товара
     * @param int $iSetVal - устанавливаемая цена по умолчанию
     * @param int $iMinVal - ограничение цены снизу
     * @param int $iMaxVal - ограничение цены сверху
     * @param int $rec_value - рекомендованая цена
     * @param array $arRequestIds - массив ID запросов
     * @return string - html формы создания предложения
     */
    public static function getCounterOfferFormData($iOfferId, $iSetVal, $iMinVal, $iMaxVal, $rec_value, $arRequestIds, $iVolume = 0){
        $sResult = '';

        ob_start();
        if(count($arRequestIds) > 0) {
            ?>
            <div class="prop_area adress_val counter_data">
                <div class="val_adress">
                    <div class="counter_request_additional_data">
                        <div class="row first_row">
                            <?if(!empty($rec_value)){?>
                            <div>
                                <div class="r_price_block">
                                    <div class="pr_1">Рекомендация цены: <div class="pr_val_rec rowed"><span class="val_span"><?=number_format($rec_value, 0, '.', ' ');?></span> руб/т</div></div>
                                </div>
                            </div>
                            <?}?>
                            <div class="flex-row"><div class="row_head">Моя цена "с места":</div><div class="row_val min_max_val"><div class="min_price"><?=number_format($iMinVal, 0, '.', ' ');?><span>min</span></div><span class="minus minus_bg" data-step="50" onclick="farmerClickCounterMinPrice(this);" data-min="<?=$iMinVal?>"></span><input type="text" name="price" placeholder="" value=""><span class="plus plus_bg" data-step="50" onclick="farmerClickCounterMaxPrice(this);" data-max="<?=$iMaxVal?>"></span><div class="max_price"><?=number_format($iMaxVal, 0, '.', ' ');?><span>max</span></div></div></div>
                            <div class="clear no_line"></div>
                        </div>
                        <div class="row">
                            <div class="flex-row">
                                <div class="row_head">Указать количество тонн:</div>
                                <div class="row_val"><input type="text" data-checkval="y" data-checktype="pos_int" name="volume" placeholder="" value="<?=($iVolume > 0 ? $iVolume : '')?>"><span class="ton_pos">т.</span></div>
                            </div>
                            <div class="clear no_line"></div>
                        </div>
                        <input type="button" name="save" value="Отправить предложение" class="submit-btn counter_request_submit"><div class="refinement_text">Срок действия предложения - 7 дней.</div>
                    </div>
                </div>
            </div>
            <?
        }else{

        }
        $sResult = ob_get_clean();

        return $sResult;
    }

    /*
     * Получение/генерирование ссылки на главную страницу с авторизацией пользователя
     * @param int $iFarmertId - ID поставщика
     * @return string - ссылка на главную страницу с авторизацией пользователя
    */
    public static function getStraightHrefMain($iFarmertId){
        $sResult = '';
        $sTargetPage = '/farmer/';

        $obRes = CIBlockElement::GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('straight_href'),
                'PROPERTY_TARGET_USER' => $iFarmertId,
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
            $sResult = generateStraightHref($iAuthorId, $iFarmertId, 'f', 0, 0, '', $sTargetPage);
        }

        return $sResult;
    }

    /*
    * Получение массива активных товаров в наличии (для всех поставщиков)
     * @return array - массив данных, где ключи - ID товара, данные товара
    */
    public static function getActiveAvailableOffers(){
        $arrResult = array();

        $obRes = CIBlockElement::GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('farmer_offer'),
                'ACTIVE' => 'Y',
                'PROPERTY_ACTIVE' => rrsIblock::getPropListKey('farmer_offer', 'ACTIVE', 'yes'),
                '!PROPERTY_STATUS_AVAILABLE' => rrsIblock::getPropListKey('farmer_offer', 'STATUS_AVAILABLE', 'no'),
            ),
            false,
            false,
            array('ID', 'PROPERTY_FARMER', 'PROPERTY_CULTURE', 'PROPERTY_WAREHOUSE.PROPERTY_REGION', 'PROPERTY_USER_NDS')
        );
        while($arrData = $obRes->Fetch()){
            $arrResult[$arrData['ID']] = array(
                'FARMER' => $arrData['PROPERTY_FARMER_VALUE'],
                'CULTURE' => $arrData['PROPERTY_CULTURE_VALUE'],
            );
        }

        return $arrResult;
    }

    /*
    * Получение списка привязанных к поставщикам организаторов
     * @param array $arrFarmers - массив ID поставщиков
     * @return array - массив данных, где ключи - ID поставщиков, а значениями массив ID привязанных организаторов
    */
    public static function getLinkedPartnersListForFarmers($arrFarmers){
        $arrResult = array();

        if(
            is_array($arrFarmers)
            && count($arrFarmers) > 0
        ){
            $obRes = CIBlockElement::GetList(
                array('ID' => 'ASC'),
                array(
                    'IBLOCK_ID' => rrsIblock::getIBlockId('farmer_agent_link'),
                    'ACTIVE' => 'Y',
                    'PROPERTY_USER_ID' => $arrFarmers
                ),
                false,
                false,
                array('PROPERTY_USER_ID', 'PROPERTY_AGENT_ID')
            );
            while($arrData = $obRes->Fetch()){
                $arrResult[$arrData['PROPERTY_USER_ID_VALUE']][] = $arrData['PROPERTY_AGENT_ID_VALUE'];
            }
        }

        return $arrResult;
    }

    /*
    * Получение списка телефонов для выбранных поставщиков
     * @param array $arrFarmers - массив ID поставщиков
     * @return array - массив данных, где ключи - ID поставщиков, а значения массив телефонов
    */
    public static function getPhoneList($arrFarmers){
        $arrResult = array();

        if(
            is_array($arrFarmers)
            && count($arrFarmers) > 0
        ){
            $obRes = CIBlockElement::GetList(
                array('ID' => 'ASC'),
                array(
                    'IBLOCK_ID' => rrsIblock::getIBlockId('farmer_profile'),
                    'ACTIVE' => 'Y',
                    'PROPERTY_USER' => $arrFarmers,
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


    /*
    * Получение массива ID товаров для выбранных поставщиков
     * @param array $arrIds - массив ID поставщиков
     * @return array - массив ID товара
    */
    public static function getFarmersOffers($arrIds){
        $arrResult = array();

        if(
            is_array($arrIds)
            && count($arrIds) > 0
        ){
            $obRes = CIBlockElement::GetList(
                array('ID' => 'ASC'),
                array(
                    'IBLOCK_ID' => rrsIblock::getIBlockId('farmer_offer'),
                    'ACTIVE' => 'Y',
                    'PROPERTY_ACTIVE' => rrsIblock::getPropListKey('farmer_offer', 'ACTIVE', 'yes'),
                    'PROPERTY_FARMER' => $arrIds,
                ),
                false,
                false,
                array('ID')
            );
            while($arrData = $obRes->Fetch()){
                $arrResult[] = $arrData['ID'];
            }
        }

        return $arrResult;
    }


    /*
    * Получение региона по ID товара
     * @param int $iOffer - ID товара
     * @return int - ID региона
    */
    public static function getRegionByOffer($iOffer){
        $iResult = 0;

        if(filter_var($iOffer, FILTER_VALIDATE_INT)){
            $obRes = CIBlockElement::GetList(
                array('ID' => 'ASC'),
                array(
                    'IBLOCK_ID' => rrsIblock::getIBlockId('farmer_offer'),
                    'ACTIVE' => 'Y',
                    'PROPERTY_ACTIVE' => rrsIblock::getPropListKey('farmer_offer', 'ACTIVE', 'yes'),
                    'ID' => $iOffer,
                ),
                false,
                array('nTopCount' => 1),
                array('PROPERTY_WAREHOUSE.PROPERTY_REGION')
            );
            if($arrData = $obRes->Fetch()){
                if(!empty($arrData['PROPERTY_WAREHOUSE_PROPERTY_REGION_VALUE'])) {
                    $iResult = $arrData['PROPERTY_WAREHOUSE_PROPERTY_REGION_VALUE'];
                }
            }
        }

        return $iResult;
    }


    /*
    * Получение складов поставщиков по ID регионов
     * @param int | array $arrRegions - ID регионов
     * @return array - ID складов
    */
    public static function getWhByRegions($arrRegions){
        $arrResult = array();

        if(
            is_array($arrRegions)
            && count($arrRegions) > 0
            || filter_var($arrRegions, FILTER_VALIDATE_INT)
        ){
            $obRes = CIBlockElement::GetList(
                array('ID' => 'ASC'),
                array(
                    'IBLOCK_ID' => rrsIblock::getIBlockId('farmer_warehouse'),
                    'ACTIVE' => 'Y',
                    'PROPERTY_ACTIVE' => rrsIblock::getPropListKey('farmer_warehouse', 'ACTIVE', 'yes'),
                    'PROPERTY_REGION' => $arrRegions,
                ),
                false,
                false,
                array('ID')
            );
            while($arrData = $obRes->Fetch()){
                $arrResult[] = $arrData['ID'];
            }
        }

        return $arrResult;
    }


    /*
    * Копирование данных графика "Спрос" для товара (используется при копировании)
    * @param int $iOfferOld - ID товара, от которого копируются данные "Спрос"
    * @param int $iOfferNew - ID товара, которому копируются данные "Спрос"
    */
    public static function copyGraphSprosDataForOffer($iOfferOld, $iOfferNew){
        if(
            filter_var($iOfferOld, FILTER_VALIDATE_INT)
            && filter_var($iOfferNew, FILTER_VALIDATE_INT)
        ){
            $log_obj = new log;
            $entityDataClass = $log_obj->getEntityDataClass(rrsIblock::HLgetIBlockId('BESTOFFERPRICES'));
            $el = new $entityDataClass;

            $arrFilter = array(
                'UF_OFFER_ID' => $iOfferOld,
            );

            //определение - нужно ли брать данные текущего дня (если сегодня уже не будет запущен пересчёт, который запускается каждые 15 мин)
            $arrNextDate = explode('_', date('d_m_Y', strtotime('+1 day')));
            $iNextTimestamp = mktime(0, 0, 0, $arrNextDate[1], $arrNextDate[0], $arrNextDate[2]);
            if($iNextTimestamp - time() > 60 * 15){
                //не берем текущую дату, т.к. сегодня еще будет пересчёт
                $arrFilter['<UF_DATE'] = date('d.m.Y');
            }else{
                //берем текущую дату, т.к. следующий пересчет будет уже в следующий день
                $arrFilter['<UF_DATE'] = date('d.m.Y', strtotime('+1 day'));
            }

            //получение и копирование записей
            $res = $el->getList(array(
                'select' => array('*'),
                'filter' => $arrFilter,
                'order' => array('UF_DATE' => 'DESC')
            ));
            while($arrData = $res->fetch()) {
                //обрабатываем даты, убираем поле ID, меняем ID товара в массиве
                if(isset($arrData['UF_DATE_UPDATE'])){
                    $arrData['UF_DATE_UPDATE'] = $arrData['UF_DATE_UPDATE']->toString();
                }
                if(isset($arrData['UF_DATE'])){
                    $arrData['UF_DATE'] = $arrData['UF_DATE']->toString();
                }
                if(isset($arrData['ID'])) {
                    unset($arrData['ID']);
                }
                if(isset($arrData['UF_OFFER_ID'])) {
                    $arrData['UF_OFFER_ID'] = $iOfferNew;
                }
                if(array_key_exists('UF_COPIED_OFFER', $arrData)) {
                    $arrData['UF_COPIED_OFFER'] = $iOfferOld;
                }

                //добавляем запись
                $el->add($arrData);
            }
        }
    }


    /*
    * Получение данных "спроса" для товаров за последние два дня (позавчера и вчера)
    * @param array $arrOffers - ID товаров, для которых получаем данные "Спрос"
     *
    * @return array - данные спроса товаров, где ключи ID товаров, а значения - массив [Y] => [<price 1>], [BY] => [<price 2>], где <price 1> - цена за вчера, а <price 2> - цена за позавчера
    */
    public static function getOfferSprosLast($arrOffers){
        $arrResult = array();

        if(
            is_array($arrOffers)
            && count($arrOffers)
        ){
            $log_obj = new log;
            $entityDataClass = $log_obj->getEntityDataClass(rrsIblock::HLgetIBlockId('BESTOFFERPRICES'));
            $el = new $entityDataClass;

            $arrFilter = array(
                'UF_OFFER_ID' => $arrOffers,
                '>UF_DATE' => date('d.m.Y', strtotime('-3 day')),
                '<UF_DATE' => date('d.m.Y'),
            );

            $sYesterday = date('d.m.Y', strtotime('-1 day'));
            $sBeforeYesterday = date('d.m.Y', strtotime('-2 day'));

            //получение и записей
            $res = $el->getList(array(
                'select' => array('UF_OFFER_ID', 'UF_BEST_CSM_PRICE', 'UF_DATE'),
                'filter' => $arrFilter,
                'order' => array('UF_DATE' => 'DESC') //порядок помогает определить каким является первое значение
            ));
            while($arrData = $res->fetch()) {
                $sDate = $arrData['UF_DATE']->toString();

                //если значение за вчера
                if($sDate == $sYesterday){
                    $arrResult[$arrData['UF_OFFER_ID']]['Y'] = $arrData['UF_BEST_CSM_PRICE'];
                }
                //если значение за позавчера
                elseif($sDate == $sBeforeYesterday){
                    $arrResult[$arrData['UF_OFFER_ID']]['BY'] = $arrData['UF_BEST_CSM_PRICE'];
                }
            }
        }

        return $arrResult;
    }
}
?>