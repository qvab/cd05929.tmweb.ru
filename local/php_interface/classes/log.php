<?
use \Bitrix\Highloadblock\HighloadBlockTable;

class log {
    public static $hlParityPrice = 1;
    public static $hlR2 = 2;
    public static $hlRejectLeads = 3;
    public static $hlDealsLog = 4;
    public static $hlUserActivityLog = 7;
    public static $hlRouteCache = 10;

    function getEntityDataClass($iblock) {
        CModule::IncludeModule('highloadblock');

        if (empty($iblock) || $iblock < 1)
            return false;

        $hlblock = HighloadBlockTable::getById($iblock)->fetch();
        $entity = HighloadBlockTable::compileEntity($hlblock);
        $entityDataClass = $entity->getDataClass();
        return $entityDataClass;
    }

    public static function addParityPriceLog($center_id, $culture_id, $reason, $code, $priceData) {
        global $DB;
        $data = array(
            'UF_DATE' => $DB->FormatDate(date("d.m.Y H:i:s"), "DD.MM.YYYY HH:MI:SS", FORMAT_DATETIME),
            'UF_CENTER' => $center_id,
            'UF_CULTURE' => $culture_id,
            'UF_REASON' => $reason,
            'UF_CODE' => $code,
            'UF_PASSIVE_PRICE' => price($priceData['PRICE_PASSIVE']),
            'UF_STANDART_PRICE' => price($priceData['PRICE_STANDART']),
            'UF_ACTIVE_PRICE' => price($priceData['PRICE_ACTIVE']),
        );

        return self::_createEntity(self::$hlParityPrice, $data);
    }

    public static function getLastParityPriceLog($center_id, $culture_id) {
        $entityDataClass = self::getEntityDataClass(static::$hlParityPrice);
        $el = new $entityDataClass;

        $rsData = $el->getList(array(
            'select' => array('ID', 'UF_DATE'),
            'filter' => array(
                'UF_CENTER' => $center_id,
                'UF_CULTURE' => $culture_id,
                'UF_CODE' => 'mm'
            )
        ));
        if ($result = $rsData->fetch()) {
            return $result;
        }
    }

    public static function addR2Log($center_id, $culture_id, $n, $r2) {
        global $DB;
        $data = array(
            'UF_DATE' => $DB->FormatDate(date("d.m.Y H:i:s"), "DD.MM.YYYY HH:MI:SS", FORMAT_DATETIME),
            'UF_CENTER' => $center_id,
            'UF_CULTURE' => $culture_id,
            'UF_DEALS_NUM' => $n,
            'UF_R2' => $r2,
        );

        return self::_createEntity(self::$hlR2, $data);
    }

    public static function addRejectLead($user_id, $request_id, $offer_id) {
        global $DB;
        $data = array(
            'UF_DATE' => $DB->FormatDate(date("d.m.Y H:i:s"), "DD.MM.YYYY HH:MI:SS", FORMAT_DATETIME),
            'UF_FARMER_ID' => $user_id,
            'UF_REQUEST_ID' => $request_id,
            'UF_OFFER_ID' => $offer_id,
        );

        return self::_createEntity(self::$hlRejectLeads, $data);
    }

    public static function getRejectLeads($user_id) {
        $filter = array(
            'UF_FARMER_ID' => $user_id
        );
        $rejectLeadList = self::_getEntitiesList(self::$hlRejectLeads, $filter);
        $result = array();
        foreach ($rejectLeadList as $item) {
            $result[$item['UF_REQUEST_ID']][$item['UF_OFFER_ID']] = true;
        }

        return $result;
    }

    public static function addDealStatusLog($deal_id, $code, $text) {
        global $DB;
        $data = array(
            'UF_DATE' => $DB->FormatDate(date("d.m.Y H:i:s"), "DD.MM.YYYY HH:MI:SS", FORMAT_DATETIME),
            'UF_DEAL_ID' => $deal_id,
            'UF_STATUS_CODE' => $code,
            'UF_TEXT' => $text
        );

        return self::_createEntity(self::$hlDealsLog, $data);
    }

    public static function getDealStatusLog($deal_id) {
        $filter = array(
            'UF_DEAL_ID' => $deal_id
        );
        $result = self::_getEntitiesList(self::$hlDealsLog, $filter, 'UF_STATUS_CODE');
        return $result;
    }

    public static function getDealStatusLogList($dealIds) {
        $filter = array(
            'UF_DEAL_ID' => $dealIds
        );
        $arResult = self::_getEntitiesList(self::$hlDealsLog, $filter);
        foreach ($arResult as $item) {
            $result[$item['UF_DEAL_ID']][$item['UF_STATUS_CODE']] = $item;
        }
        return $result;
    }

    public static function addUserActivityLog($partner_id, $client_num, $client_no, $farmer_num, $farmer_no, $transport_num) {
        global $DB;
        $data = array(
            'UF_PARTNER_ID' => $partner_id,
            'UF_CLIENT_NUM' => $client_num,
            'UF_CLIENT_NUM_NO' => $client_no,
            'UF_FARMER_NUM' => $farmer_num,
            'UF_FARMER_NUM_NO' => $farmer_no,
            'UF_TRANSPORT_NUM' => $transport_num,
            'UF_DATE' => $DB->FormatDate(date("d.m.Y H:i:s"), "DD.MM.YYYY HH:MI:SS", FORMAT_DATETIME),
        );

        return self::_createEntity(self::$hlUserActivityLog, $data);
    }

    public static function getLastUserActivityLog() {
        $entityDataClass = self::getEntityDataClass(static::$hlUserActivityLog);
        $el = new $entityDataClass;

        $rsData = $el->getList(array(
            'select' => array('*'),
            'filter' => array(
                'UF_PARTNER_ID' => 1,
            ),
            'order' => array('ID'=>'DESC')
        ));
        if ($result = $rsData->fetch()) {
            return $result;
        }
    }

    public static function getRouteCache($farmerWHid, $clientWHid) {
        $entityDataClass = self::getEntityDataClass(static::$hlRouteCache);
        $el = new $entityDataClass;
        $result = array();

        $rsData = $el->getList(array(
            'select' => array('*'),
            'filter' => array(
                'UF_FARMER_WH_ID' => $farmerWHid,
                'UF_CLIENT_WH_ID' => $clientWHid
            ),
            'order' => array('ID'=>'DESC')
        ));
        while ($res = $rsData->fetch()) {
            $result[$res['UF_FARMER_WH_ID']][$res['UF_CLIENT_WH_ID']] = $res['UF_ROUTE'];
        }

        return $result;
    }

    /**
     * Получение расстояния между складами
     *
     * @access  public
     * @param   int $farmerWHid1 id скалада 1
     * @param   int $farmerWHid2 id скалада 2
     * @param   float $route величина расстояния
     *
     * @return  float величина расстояния
     */
    public static function getFarmerWHRouteCache($farmerWHid1, $farmerWHid2) {
        $entityDataClass = self::getEntityDataClass(rrsIblock::HLgetIBlockId('FMROUTESCACHE'));
        $el = new $entityDataClass;
        $result = 0;

        $rsData = $el->getList(array(
            'select' => array('*'),
            'filter' => array(
                'UF_FARMER_WH_ID1' => $farmerWHid1,
                'UF_FARMER_WH_ID2' => $farmerWHid2
            ),
            'order' => array('ID'=>'DESC')
        ));
        if($res = $rsData->fetch()) {
            $result = $res['UF_ROUTE'];
        }

        //получение обратного пути (если расстояние хранится не от $farmerWHid1 до $farmerWHid2, а от $farmerWHid2 до $farmerWHid1)
        $rsData = $el->getList(array(
            'select' => array('*'),
            'filter' => array(
                'UF_FARMER_WH_ID1' => $farmerWHid2,
                'UF_FARMER_WH_ID2' => $farmerWHid1
            ),
            'order' => array('ID'=>'DESC')
        ));
        if($res = $rsData->fetch()) {
            $result = $res['UF_ROUTE'];
        }

        return $result;
    }

    /**
     * Получение ID ВП для выбранного фильтра (обычно по ID запроса и товара)
     *
     * @access  public
     * @param   array $arFilter - фильтрация запроса
     *
     * @return int - ID записи
     */
    public static function getCounterRequestByFilter($arFilter) {
        $entityDataClass = self::getEntityDataClass(rrsIblock::HLgetIBlockId('COUNTEROFFERS'));
        $el = new $entityDataClass;
        $result = 0;

        $rsData = $el->getList(array(
            'select' => array('ID'),
            'filter' => $arFilter,
            'order' => array('ID'=>'ASC'),
            'limit' => 1
        ));
        if($res = $rsData->fetch()) {
            $result = $res['ID'];
        }

        return $result;
    }

    public static function addRouteCacheItem($farmerWHid, $clientWHid, $route) {
        global $DB;
        if (intval($farmerWHid) > 0 && intval($clientWHid) > 0) {
            $data = array(
                'UF_CREATE_DATE' => $DB->FormatDate(date("d.m.Y H:i:s"), "DD.MM.YYYY HH:MI:SS", FORMAT_DATETIME),
                'UF_FARMER_WH_ID' => $farmerWHid,
                'UF_CLIENT_WH_ID' => $clientWHid,
                'UF_ROUTE' => $route,
                'UF_TIMESTAMP' => strtotime('+30 day'),
            );
            return self::_createEntity(self::$hlRouteCache, $data);
        }

        return false;
    }

    /**
     * Добавление сущности в highload-инфоблок FMROUTESCACHE (расстояния между складами поставщиков)
     *
     * @access  public
     * @param   int $farmerWHid1 id скалада 1
     * @param   int $farmerWHid2 id скалада 2
     * @param   float $route величина расстояния
     *
     * @return  int идентификатор сущности
     */
    public static function addFarmerWHRouteCacheItem($farmerWHid1, $farmerWHid2, $route) {
        global $DB;
        if (intval($farmerWHid1) > 0 && intval($farmerWHid2) > 0) {
            $data = array(
                'UF_CREATE_DATE' => $DB->FormatDate(date("d.m.Y H:i:s"), "DD.MM.YYYY HH:MI:SS", FORMAT_DATETIME),
                'UF_FARMER_WH_ID1' => $farmerWHid1,
                'UF_FARMER_WH_ID2' => $farmerWHid2,
                'UF_ROUTE' => $route,
                'UF_TIMESTAMP' => strtotime('+30 day'),
            );
            return self::_createEntity(rrsIblock::HLgetIBlockId('FMROUTESCACHE'), $data);
        }

        return false;
    }

    public static function updateRouteCacheItem($id, $route) {
        global $DB;
        $data = array(
            'UF_CREATE_DATE' => $DB->FormatDate(date("d.m.Y H:i:s"), "DD.MM.YYYY HH:MI:SS", FORMAT_DATETIME),
            'UF_ROUTE' => $route,
            'UF_TIMESTAMP' => strtotime('+30 day'),
        );

        return self::_updateEntity(self::$hlRouteCache, $id, $data);
    }

    /**
     * Обновление данных сущности в highload-инфоблок FMROUTESCACHE (расстояния между складами поставщиков)
     *
     * @access  public
     * @param   int $id id записи в таблице
     * @param   float $route величина расстояния
     *
     * @return  int идентификатор сущности
     */
    public static function updateFarmerWHRouteCacheItem($id, $route) {
        global $DB;
        $data = array(
            'UF_CREATE_DATE' => $DB->FormatDate(date("d.m.Y H:i:s"), "DD.MM.YYYY HH:MI:SS", FORMAT_DATETIME),
            'UF_ROUTE' => $route,
            'UF_TIMESTAMP' => strtotime('+30 day'),
        );

        return self::_updateEntity(rrsIblock::HLgetIBlockId('FMROUTESCACHE'), $id, $data);
    }

    /**
     * Добавление сущности в highload-инфоблок
     *
     * @access  public
     * @param   int $hlBlockId идентификатор хайлоад-блока
     *          [] $data массив полей
     * @return  [] идентификатор сущности
     */
    public static function _createEntity($hlBlockId, $data) {
        $entityDataClass = self::getEntityDataClass($hlBlockId);
        $el = new $entityDataClass;

        $res = $el->add($data);
        if ($res->isSuccess())
            $result['ID'] = $res->getId();
        else
            return false;
        return $result;
    }

    /**
     * Изменение сущности в highload-инфоблок
     *
     * @access  public
     * @param   int $hlBlockId идентификатор хайлоад-блока
     *          int $entityId идентификатор сущности
     *          [] $data массив полей
     * @return  [] идентификатор сущности
     */
    public static function _updateEntity($hlBlockId, $entityId, $data) {
        $entityDataClass = self::getEntityDataClass($hlBlockId);
        $el = new $entityDataClass;
        $res = $el->update($entityId, $data);
        return $res;
    }

    /**
     * Получение списка сущностей в highload-инфоблоке
     *
     * @access  public
     * @param   [] $filter массив с полями для поиска
     * @return  [] массив со списком сущностей
     */
    public static function _getEntitiesList($hlBlockId, $filter, $key = false, $order = false) {
        $entityDataClass = self::getEntityDataClass($hlBlockId);
        $el = new $entityDataClass;

        $arFilter = array(
            'select' => array('*'),
            'filter' => $filter
        );

        if($order){
            $arFilter['order'] = $order;
        }

        $rsData = $el->getList($arFilter);
        if ($key) {
            while ($res = $rsData->fetch()) {
                $result[$res[$key]] = $res;
            }
        }
        else {
            while ($res = $rsData->fetch()) {
                $result[] = $res;
            }
        }
        return $result;
    }

    /**
     * Удаление сущности в highload-инфоблоке
     *
     * @access  public
     * @param   number $hlBlockId идентификатор хайлоад-блока
     *          number $entityId ID сущности
     * @return  bool
     */
    public static function _deleteEntity($hlBlockId, $entityId) {
        $entityDataClass = self::getEntityDataClass($hlBlockId);
        $el = new $entityDataClass;

        $res = $el->delete($entityId);
        if ($res->isSuccess())
            return true;
        else
            return false;
    }

    /*
     * Возвращает ID highload инфоблока по коду
     * @param string $code - код инфоблока
     *
     * @return int - ID инфоблока
     * */
    public static function getIdByName($code){
        $result = 0;

        try{
            $hldata = \Bitrix\Highloadblock\HighloadBlockTable::getList([
                'select' => ['ID'],
                'filter' => ['=NAME' => $code]
            ])->fetch();
            $result = $hldata['ID'];

        }catch (Exception $e){
            //возникли проблемы с получением id
            $result = -1;
        }

        return $result;
    }

    /*
     * Проверяет минимальную цену с места предложения для региона (с учетом культуры) и обновляет запись в случае необходлимости
     * @param float $price_csm - цена с места
     * @param boolean $is_nds - тип налогообложения поставщика (true - с НДС, false, - без НДС)
     * @param int $offer_id - id товара
     * @param int $region_id - id региона
     * @param int $culture_id - id культуры
     * */
    public static function checkLowerCounterOfferCSM($price_csm, $is_nds, $offer_id, $region_id, $culture_id){
        if(is_numeric($region_id)
            && is_numeric($culture_id)
        ) {
            $entityDataClass = self::getEntityDataClass(rrsIblock::HLgetIBlockId('REGIONCOFFERLOWERCSM'));
            $el = new $entityDataClass;
            $date_val = ConvertTimeStamp(false, 'SHORT');

            $filter = array(
                'UF_REGION' => $region_id,
                'UF_CULTURE' => $culture_id,
                'UF_DATE' => $date_val
            );

            $arFilter = array(
                'select' => array('ID', 'UF_PRICE_NO_NDS', 'UF_PRICE_NDS'),
                'filter' => $filter,
            );

            //проверяем есть ли запись минимальной цены с места
            $nds_val = rrsIblock::getConst('nds');
            $res = $el->getList($arFilter);
            if($data = $res->fetch()){
                //если старая цена есть, сравниваем с новой ценой
                $check_price = ($is_nds ? $data['UF_PRICE_NDS'] : $data['UF_PRICE_NO_NDS']);
                if($check_price > $price_csm){
                    //обновляем данные
                    $price_nds = 0;
                    $price_no_nds = 0;
                    if($is_nds){
                        $price_nds = $price_csm;
                        $price_no_nds = $price_csm / (1 + 0.01 * $nds_val); //вычитаем НДС из значения
                    }else{
                        $price_no_nds = $price_csm;
                        $price_nds = $price_csm + ($price_csm * 0.01 * $nds_val); //прибавляем НДС к значению
                    }

                    $arData = array(
                        'UF_OFFER_ID' => $offer_id,
                        'UF_PRICE_NDS' => $price_nds,
                        'UF_PRICE_NO_NDS' => $price_no_nds,
                    );
                    $el->Update($data['ID'], $arData);
                }
            }else{
                //нет старой цены, добавляем запись
                $price_nds = 0;
                $price_no_nds = 0;
                if($is_nds){
                    $price_nds = $price_csm;
                    $price_no_nds = $price_csm / (1 + 0.01 * $nds_val); //вычитаем НДС из значения
                }else{
                    $price_no_nds = $price_csm;
                    $price_nds = $price_csm + ($price_csm * 0.01 * $nds_val); //прибавляем НДС к значению
                }

                $arData = array(
                    'UF_DATE' => $date_val,
                    'UF_OFFER_ID' => $offer_id,
                    'UF_REGION' => $region_id,
                    'UF_CULTURE' => $culture_id,
                    'UF_PRICE_NDS' => $price_nds,
                    'UF_PRICE_NO_NDS' => $price_no_nds,
                );
                $el->Add($arData);
            }

        }
    }

    /*
     * Получает минимальную цену с места предложения для региона (с учетом культуры) по ID товара
     * @param int $offer_id - id товара
     * @return float мнимальную цену предложения
     * */
    public static function getLowerCounterOfferCSMByOffer($offerId){
        $result = 0;

        if(is_numeric($offerId)) {
            $offerData = farmer::getOfferById($offerId);

            $entityDataClass = self::getEntityDataClass(rrsIblock::HLgetIBlockId('REGIONCOFFERLOWERCSM'));
            $el = new $entityDataClass;
            $date_val = ConvertTimeStamp(strtotime('-7 DAYS'), 'SHORT');

            $filter = array(
                'UF_REGION' => $offerData['WH_REGION'],
                'UF_CULTURE' => $offerData['CULTURE_ID'],
                '>=UF_DATE' => $date_val
            );

            $arFilter = array(
                'order' => array('UF_DATE' => 'DESC'),
                'select' => array('ID', 'UF_PRICE_NO_NDS', 'UF_PRICE_NDS'),
                'filter' => $filter,
                'limit' => 1
            );
            $res = $el->getList($arFilter);
            if($data = $res->fetch()){
                if($offerData['USER_NDS'] == 'yes'){
                    $result = $data['UF_PRICE_NDS'];
                }else{
                    $result = $data['UF_PRICE_NO_NDS'];
                }
            }
        }

        return $result;
    }
}
?>