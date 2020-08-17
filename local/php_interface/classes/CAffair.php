<?php

use Bitrix\Highloadblock\HighloadBlockTable as HlTab;
use Bitrix\Main\Entity;


/**
 * Модель для работы с "Делами"
 * @author: Vitaly Melnik <vitali@rarus.ru>
 */

class CAffair {

    const IB_NAME           = 'AFFAIRS';

    private $iHLId          = null;
    private $sCodeHL        = 'HLBLOCK_';
    private $arTypesAffair  = [];
    public  $nPageSize      = 10;

    private $obEnum = null;
    private $obElem = null;

    private static $instance = null;

    private function __clone() {}


    /*
     * Вызов конструктора через синглтон
     */
    public static function getInstance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }


    /**
     * CReservations constructor.
     * @throws Exception
     */
    private function __construct() {

        // Подключение модуля
        if(!CModule::IncludeModule('iblock')) {
            throw new \Exception('Ошибка подключения модуля "iblock"');
        }

        if(!CModule::IncludeModule('highloadblock')) {
            throw new VFException('Ошибка подключения модуля "highloadblock"');
        }

        // Получаем идентификатор highloadblock
        $arData = HlTab::getList(array('filter'=>array('NAME'=>self::IB_NAME)))->Fetch();

        if(!$arData['ID']) {
            throw new \Exception('Не найден ID Highload блока "'.self::IB_NAME.'"');
        } else {
            $hlblock = HlTab::getById($arData['ID'])->fetch();
            $this->obAffair = HlTab::compileEntity($hlblock);
            $this->sCodeHL .= $arData['ID'];
        }

        $this->iHLId    = $arData['ID'];
        $this->obEnum   = new CUserFieldEnum;
        $this->obElem   = new CIBlockElement;
    }


    /**
     * Отдает список значений поля "Тип дела"
     * @return array
     * @throws Exception
     */
    public static function GetTypes() {

        // Вызываем конструктор объекта
        $_ob = self::getInstance();

        if(empty($_ob->arTypesAffair)) {

            $_ob->arTypesAffair = [
                'BY_XML_ID' => [],
                'ITEMS'     => [],
            ];

            global $USER_FIELD_MANAGER;
            // Код списка
            $sKeyField = 'UF_TYPE_AFFAIR';

            // Фильтр
            $arFields = $USER_FIELD_MANAGER->GetUserFields($_ob->sCodeHL);
            if(empty($arFields[$sKeyField]['ID'])) {
                throw new Exception('Не удалось получить идентификатор поля "'.$sKeyField.'" в HL['.$_ob->iHLId.']');
            }

            $rs = $_ob->obEnum->GetList(
                ['SORT' => 'ASC',],
                ["USER_FIELD_ID" => $arFields[$sKeyField]['ID'],]
            );

            while ($arRow = $rs->Fetch()) {

                $_ob->arTypesAffair['ITEMS'][$arRow['ID']] = [
                    'ID'        => $arRow['ID'],
                    'TITLE'     => $arRow['VALUE'],
                    'SORT'      => $arRow['SORT'],
                    'XML_ID'    => $arRow['XML_ID'],
                ];

                $_ob->arTypesAffair['BY_XML_ID'][$arRow['XML_ID']] = &$_ob->arTypesAffair['ITEMS'][$arRow['ID']];
            }
        }

        return $_ob->arTypesAffair;
    }


    /**
     * Добавляет дело
     * @param $sType                    - Тип дела
     * @param $iEntityId                - Идентификатор сущности
     * @param $sDate                    - Дата действия
     * @param $sFarmerVolume            - Объем в наличии у поставщика
     * @param $sExpectedPrice           - Ожидаемая цена
     * @param $sComment                 - Комментарий
     * @param $iParticipantUserId       - Идентификатор пользователя участника (АП/Покупатель)
     * @param $iAgentUserId             - Идентификатор пользователя (Агент АП/Агент покупателя)
     * @throws Exception
     */
    public static function Add($sType, $iEntityId, $sDate, $sFarmerVolume, $sExpectedPrice, $sComment, $iParticipantUserId = null, $iAgentUserId = null) {

        // Вызываем конструктор объекта
        $_ob = self::getInstance();

        // Список возможных типов
        $arTypes = $_ob->GetTypes();

        // Проверка полей
        $iTypeId = $arTypes['BY_XML_ID'][$sType]['ID'];
        if(empty($iTypeId)) {
            throw new Exception('Неизвестный тип');
        }

        $iEntityId = intval($iEntityId);
        if(empty($iEntityId)) {
            throw new Exception('Не задан идентификатор сущности');
        }

        // Если не задан идентификатор агента или покупателя тянем его из сущности (товар/запрос)
        if(empty($iParticipantUserId)) {

            $arFilterParticipant = [
                'ID' => $iEntityId,
            ];

            $sFieldParticipant = 'PROPERTY_';

            switch ($sType) {
                case 'OFFER':
                    $arFilterParticipant['IBLOCK_ID'] = getIBlockID('farmer', 'farmer_offer');
                    $sFieldParticipant .= 'FARMER';
                    break;
                case 'REQUEST':
                    $arFilterParticipant['IBLOCK_ID'] = getIBlockID('client', 'client_request');
                    $sFieldParticipant .= 'CLIENT';
                    break;
                default:
                    throw new Exception('Неизвестный тип поиска участника (АП/Покупатель)');
            }

            $arParticipant = $_ob->obElem->GetList(
                [],
                $arFilterParticipant,
                false,
                false,
                ['ID', $sFieldParticipant]
            )->Fetch();

            $iParticipantUserId = $arParticipant[$sFieldParticipant . '_VALUE'];
        }

        $iParticipantUserId = intval($iParticipantUserId);
        if(empty($iParticipantUserId)) {
            throw new Exception('Не задан идентификатор участника');
        }

        // Если не задан ИД агента находим его
        if(empty($iAgentUserId)) {

            $arFilterAgentLink = [
                'ACTIVE'            => 'Y',
                'PROPERTY_USER_ID'  => $iParticipantUserId,
                'AGENT_ID' => $iAgentUserId
            ];

            switch ($sType) {
                case 'OFFER':
                    $arFilterAgentLink['IBLOCK_ID'] = getIBlockID('farmer', 'farmer_agent_link');
                    break;
                case 'REQUEST':
                    $arFilterAgentLink['IBLOCK_ID'] = getIBlockID('client', 'client_agent_link');
                    break;
                default:
                    throw new Exception('Неизвестный тип для определения агента)');
            }

            $arAgentLink = $_ob->obElem->GetList(
                [],
                $arFilterAgentLink,
                false,
                false,
                ['ID', 'PROPERTY_AGENT_ID']
            )->Fetch();

            $iAgentUserId = $arAgentLink['PROPERTY_AGENT_ID_VALUE'];
        }

        $iAgentUserId = intval($iAgentUserId);
        if(empty($iAgentUserId)) {
            // TODO! - Уточнить у заказчика: Может ли быть "дело" у сущности "Товар/Запрос" если у участника(АП/Клиент) нет привязки к агенту. В текущей задаче #11997 "Дела" есть только у агентов!
            throw new Exception('Не задан идентификатор агента');
        }

        // Поля "Дела"
        $arData = [
            'UF_TYPE_AFFAIR'        => $iTypeId,
            'UF_XML_ID'             => $iEntityId,
            'UF_DATE_AFFAIR'        => $sDate,
            'UF_USER_PARTICIPANT'   => $iParticipantUserId,
            'UF_USER_AGENT'         => $iAgentUserId,
            'UF_FARMER_VOLUME'      => trim($sFarmerVolume),
            'UF_EXPECTED_PRICE'     => trim($sExpectedPrice),
            'UF_COMMENT'            => trim($sComment),
            'UF_DATE_CREATE'        => date("d.m.Y H:i:s"),
            'UF_CODE'               => $sType . '_' . $iEntityId,
            'UF_SORT'               => 500,
        ];

        // Класс дела
        $sClass = $_ob->obAffair->getDataClass();

        // Добавляем дело
        $result = $sClass::add($arData);

        if($result->isSuccess()) {

            // Сбрасываем кэш сущности к которой привязано дело
            switch ($sType) {
                case 'OFFER':
                    $GLOBALS['CACHE_MANAGER']->ClearByTag('iblock_id_' . getIBlockID('farmer', 'farmer_offer'));
                    break;
                case 'REQUEST':
                    $GLOBALS['CACHE_MANAGER']->ClearByTag('iblock_id_' . getIBlockID('client', 'client_request'));
                    break;
            }

            return $result->getId();
        
        } else {
            throw new Exception('Ошибка добавления записи дела: ' . implode(PHP_EOL, $result->getErrorMessages()));
        }
    }


    /**
     * Отдает список дел по фильтру
     * @param $arType               - Тип дела
     * @param array $arOrder        - Сортировка
     * @param array $arFilter       - Фильтр
     * @param array $arSelect       - Поля выборки
     * @param integer $iLimit       - Количество записей
     * @param integer $iOffset      - Смещение для limit
     * @param boolean $bCnt         - Флаг подсчета записей по заданному фильтру
     * @param boolean $bNavString   - Флаг использования постраничной навигации
     * @return array
     * @throws Exception
     */
    public static function GetList(array $arType, array $arOrder = [], array $arFilter = [], array $arSelect = ['*'], $iLimit = null, $iOffset = null, $bCnt = true, $bNavString = false) {

        $arResult = [
            'ITEMS' => [],
            'CNT'   => 0 ,
        ];

        // Вызываем конструктор объекта
        $_ob = self::getInstance();

        // Список возможных типов
        $arTypesEnum = $_ob->GetTypes();

        foreach ($arType as $sCodeType) {
            // Проверка полей
            $iTypeId = $arTypesEnum['BY_XML_ID'][$sCodeType]['ID'];
            if(empty($iTypeId)) {
                throw new Exception('Неизвестный тип дела "'.$sCodeType.'"');
            }
            $arFilter['UF_TYPE_AFFAIR'][] = $iTypeId;
        }

        // Класс дела
        $sClass = $_ob->obAffair->getDataClass();

        $main_query = new Entity\Query($sClass);
        $main_query->setFilter($arFilter);
        $main_query->setOrder($arOrder);
        $main_query->setSelect($arSelect);

        if(!empty($iLimit) && !$bNavString) {
            $main_query->setLimit($iLimit);
        }

        if(!empty($iOffset) && !$bNavString) {
            $main_query->setOffset($iOffset);
        }

        $result = $main_query->exec();

        $result = new CDBResult($result);
 // Пагинация
        if($bNavString) {

            // Параметры пагинации
            $nPageSize  = $_ob->nPageSize;
            $iNumPage   = isset($_GET['PAGEN_1']) ? $_GET['PAGEN_1'] : 1;

            $result->NavStart($nPageSize, true, $iNumPage);
            $main_query->setOffset(($iNumPage-1) * $nPageSize);

            $arResult["NAV_STRING"] = $result->GetPageNavString('', 'rarus');
            $arResult["NAV_PARAMS"] = $result->GetNavParams();
            $arResult["NAV_NUM"]    = $result->NavNum;
        }

        while($arRow = $result->fetch()){
            $arResult['ITEMS'][$arRow['ID']] = $arRow;
        }

        if($bCnt) {
            // Всего записей по фильтру
            $arCnt = $sClass::getList(
                [
                    'filter'    => $arFilter,
                    'select'    => ['CNT',],
                    'runtime'   => [new Bitrix\Main\Entity\ExpressionField('CNT', 'COUNT(*)'),],
                ]
            )->Fetch();

            $arResult['CNT'] = intval($arCnt['CNT']);
        }

        return $arResult;
    }


    /**
     * Количество элементов на странице
     * @param $iSize
     */
    public static function setNPageSize($iSize) {
        $_ob = self::getInstance();
        $_ob->nPageSize = $iSize;
    }


    /**
     * Агент оповещения о дате наступления "Дела"
     */
    public static function AgentSendMailScheduled() {

        $arAffairs          = [];
        $arUserId           = [];
        $arUsers            = [];

        $arFarmer           = [];
        $arClient           = [];

        $arAgentFarmer      = [];
        $arAgentClient      = [];

        $arFarmerId         = [];
        $arClientId         = [];

        $arAgentFarmerId    = [];
        $arAgentClientId    = [];


        // Вызываем конструктор объекта
        $_ob = self::getInstance();


        // Список возможных типов
        $arTypesEnum = $_ob->GetTypes();

        // Класс дел
        $sClass = $_ob->obAffair->getDataClass();

        /**
         * Выборка дел
         */
        $rs = $sClass::getList(
            [
                'filter'    => [
                    '=UF_DATE_AFFAIR' => date('d.m.Y'),
                ],
                'select' => [
                    'ID',
                    'UF_XML_ID',
                    'UF_CODE',
                    'UF_TYPE_AFFAIR',
                    'UF_USER_AGENT',
                    'UF_USER_PARTICIPANT',
                    'UF_FARMER_VOLUME',
                    'UF_EXPECTED_PRICE',
                    'UF_COMMENT',
                    'UF_DATE_AFFAIR',
                    'UF_DATE_CREATE',
                ],
            ]
        );
        while ($arRow = $rs->Fetch())  {

            $aCodeType = $arTypesEnum['ITEMS'][$arRow['UF_TYPE_AFFAIR']]['XML_ID'];

            if($aCodeType == 'OFFER') {
                // ИД АП
                $arFarmerId[$arRow['UF_USER_PARTICIPANT']]  = $arRow['UF_USER_PARTICIPANT'];

                // Ссылка на профиль АП
                $arRow['PARTICIPANT'] =& $arFarmer[$arRow['UF_USER_PARTICIPANT']];

                // ИД агента АП
                $arAgentFarmerId[$arRow['UF_USER_AGENT']] = $arRow['UF_USER_AGENT'];
                // ССылка на профиль агента АП
                $arRow['AGENT'] =& $arAgentFarmer[$arRow['UF_USER_AGENT']];

            } elseif ($aCodeType == 'REQUEST') {

                continue;

                /*
                 * Покупателей пока не делаем. В будущем планируется сделать дела по запросам.
                 *
                // ИД Покупателя
                $arClientId[$arRow['UF_USER_PARTICIPANT']]  = $arRow['UF_USER_PARTICIPANT'];
                // Ссылка на профиль покупателя
                $arRow['PARTICIPANT'] =& $arClient[$arRow['UF_USER_PARTICIPANT']];

                // ИД Агента покупателя
                $arAgentClientId[$arRow['UF_USER_AGENT']]   = $arRow['UF_USER_AGENT'];
                // Ссылка на профиль агента покупателя
                $arRow['AGENT'] =& $arAgentClient[$arRow['UF_USER_AGENT']];
                */
            } else {
                continue; // Неизвестный тип, пока не ясно как как это захочет обрабатывать заказчик
            }

            // ИД пользователей
            $arUserId[$arRow['UF_USER_PARTICIPANT']] = $arRow['UF_USER_PARTICIPANT'];
            $arUserId[$arRow['UF_USER_AGENT']] = $arRow['UF_USER_AGENT'];

            // Дела
            $arAffairs[$arRow['ID']] = $arRow;
        }

        //получение культур товаров
        $arOffersCultures = array();
        $arOffersToAffairsLinks = array();
        $arAffairsCulturesNames = array();
        foreach($arAffairs as $iCurId => $arItemData){
            $arTemp = explode('_', $arItemData['UF_CODE']);
            if(
                isset($arTemp[0])
                && $arTemp[0] == 'OFFER'
                && isset($arTemp[1])
                && filter_var($arTemp[1], FILTER_VALIDATE_INT)
            ){
                $arOffersToAffairsLinks[$arTemp[1]] = $iCurId;
                $arOffersCultures[$arTemp[1]] = '';
            }
        }

        //перенос названий культур в массив с ID дел
        if(count($arOffersCultures) > 0){
            $arOffersCultures = farmer::getCultureNamesByOffers(array_keys($arOffersCultures));
            foreach($arOffersCultures as $iCurOfer => $sCurName){
                if(isset($arOffersToAffairsLinks[$iCurOfer])){
                    $arAffairsCulturesNames[$arOffersToAffairsLinks[$iCurOfer]] = $sCurName;
                }
            }
            unset($arOffersToAffairsLinks, $arOffersCultures);
        }

        /**
         * Пользователи
         */
        if(!empty($arUserId)) {

            $arUserFilter = ['ID' => implode(' | ', $arUserId)];

            $rsUsers = CUser::GetList(
                ($by='id'),
                ($order='desc'),
                $arUserFilter,
                [
                    'FIELDS' => ['ID', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'EMAIL',],
                ]
            );

            while ($arRow = $rsUsers->Fetch()) {
                $arUsers[$arRow['ID']] = $arRow;
            }
        }


        /**
         * Профили АП
         */
        if(!empty($arFarmerId)) {

            $arFarmerId = array_values($arFarmerId);

            $rs = $_ob->obElem->GetList(
                [
                    'PROPERTY_FULL_COMPANY_NAME' => 'ASC',
                    'PROPERTY_IP_FIO' => 'ASC',
                ],
                [
                    'ACTIVE'        => 'Y',
                    'IBLOCK_ID'     => getIBlockID('farmer', 'farmer_profile'),
                    'PROPERTY_USER' => $arFarmerId,
                ],
                false,
                false,
                [
                    'ID',
                    'PROPERTY_FULL_COMPANY_NAME',
                    'PROPERTY_IP_FIO',
                    'PROPERTY_USER',
                    'PROPERTY_UL_TYPE',
                    'PROPERTY_USER'
                ]
            );

            while ($arRow = $rs->Fetch()) {
                $ul_type = rrsIblock::getPropListId('farmer_profile', 'UL_TYPE', $arRow['PROPERTY_UL_TYPE_ENUM_ID']);
                if($ul_type=='ip'){
                    if(!empty($arRow['PROPERTY_IP_FIO_VALUE'])){
                        $sNameCompany = 'ИП ' . trim($arRow['PROPERTY_IP_FIO_VALUE']);
                    }else{
                        if(!empty($arRow['PROPERTY_USER_VALUE'])){
                            $rsUser = CUser::GetByID($arRow['PROPERTY_USER_VALUE']);
                            $arUser = $rsUser->Fetch();
                            $sNameCompany = 'ИП';
                            if(!empty($arUser['LAST_NAME'])){
                                $sNameCompany.= ' '.trim($arUser['LAST_NAME']);
                                if(!empty($arUser['NAME'])){
                                    $sNameCompany.= ' '.trim($arUser['NAME']);
                                }
                            }else{
                                if(!empty($arUser['NAME'])){
                                    $sNameCompany.= ' '.trim($arUser['NAME']);
                                }
                            }
                        }
                    }
                }else{
                    $sNameCompany = trim($arRow['PROPERTY_FULL_COMPANY_NAME_VALUE']);
                }
                $arFarmer[$arRow['PROPERTY_USER_VALUE']] = [
                    'USER_ID'   => $arRow['PROPERTY_USER_VALUE'],
                    'TITLE'     => $sNameCompany,
                    'ROLE_NAME' => 'Поставщик',
                    'DATA'      =>&$arUsers[ $arRow['PROPERTY_USER_VALUE']],
                ];
            }
        }

        // Профили агентов АП
        if(!empty($arAgentFarmerId)) {

            $rs = $_ob->obElem->GetList(
                ['ID' => 'ASC'],
                [
                    'IBLOCK_ID' => getIBlockID('partner', 'partner_profile'),
                    'ACTIVE' => 'Y',
                    'PROPERTY_USER' => array_values($arAgentFarmerId),
                ],
                false,
                false,
                [
                    'ID',
                    'PROPERTY_USER',
                    'PROPERTY_NOTICE',
                ]
            );

            while ($arRow = $rs->Fetch()) {

                $sTitle = $arUsers[ $arRow['PROPERTY_USER_VALUE']]['LAST_NAME'] . ' ' . $arUsers[ $arRow['PROPERTY_USER_VALUE']]['NAME'];

                $arAgentFarmer[$arRow['PROPERTY_USER_VALUE']] = [
                    'USER_ID'   => $arRow['PROPERTY_USER_VALUE'],
                    'TITLE'     => $sTitle,
                    'ROLE_NAME' => 'Агент покупателя',
                    'DATA'      =>&$arUsers[ $arRow['PROPERTY_USER_VALUE']],
                ];
            }
        }



        /**
         * Пробегаем по всем "делам"
         *
         */

        foreach ($arAffairs as $arAffairItem) {
            /**
             * Email уведомление
             */
            if((sizeof($arAffairItem['AGENT']['DATA']))&&(is_array($arAffairItem['AGENT']['DATA']))){

                $sDescription = null;

                $sDescription .= '<b>'.$arAffairItem['PARTICIPANT']['ROLE_NAME'].':</b>' .
                    ' <a href="' . $GLOBALS['host'] . '/profile/?uid='.$arAffairItem['UF_USER_PARTICIPANT'].'">' . $arAffairItem['PARTICIPANT']['TITLE'] . '</a><br />';

                if(isset($arAffairsCulturesNames[$arAffairItem['ID']])){
                    $sDescription .= '<b>Товар:</b> ' . $arAffairsCulturesNames[$arAffairItem['ID']] . '<br />';
                }
                $sDescription .= '<b>Ожидаемая цена:</b> ' . $arAffairItem['UF_EXPECTED_PRICE'] . '<br />';
                $sDescription .= '<b>Объем в наличии у поставщика:</b> ' . $arAffairItem['UF_FARMER_VOLUME']. '<br />';
                $sDescription .= '<b>Комментарии для следующего звонка:</b><br />' . $arAffairItem['UF_COMMENT'];

                //если есть EMAIL отправляем уведомление по почте
                if(!empty($arAffairItem['AGENT']['DATA']['EMAIL'])){
                    $arEventFields = [
                        'ID_AFFAIR'     => $arAffairItem['ID'],                                     // ID дела
                        'EMAIL_AGENT'   => $arAffairItem['AGENT']['DATA']['EMAIL'],                 // Email агента',
                        'DATE_AFFAIR'   => $arAffairItem['UF_DATE_AFFAIR']->format('d.m.Y'),        // Дата действия
                        'DATE_CREATE'   => $arAffairItem['UF_DATE_CREATE']->format('d.m.Y H:i:s'),  // Дата создания действия
                        'DESCRIPTION'   => $sDescription,                                           // Описание
                    ];
                    // Отправка
                    CEvent::Send('SCHEDULED_AFFAIRS', 's1', $arEventFields);
                }
                /*
                 * Отправлеяем уведомление в центр уведомлений
                 * Новое дело по товару "Товар" на складе "Склад"
                 * */
                if(!empty($arAffairItem['UF_XML_ID'])){
                    $offerData = farmer::getOfferById($arAffairItem['UF_XML_ID']);
                    if((sizeof($offerData))&&(is_array($offerData))){
                        $a_href = 'https://agrohelper.ru/partner/offer/?id='.$arAffairItem['UF_XML_ID'];
                        $message = 'Новое дело по товару "'.$offerData['CULTURE_NAME'].'" на складе "'.$offerData['WH_NAME'].'"';
                        notice::addNotice($arAffairItem['UF_USER_AGENT'], 'd', $message, $a_href, '#' . $arAffairItem['UF_XML_ID']);
                    }
                }


            }
        }

        return __METHOD__ . '();';
    }

}