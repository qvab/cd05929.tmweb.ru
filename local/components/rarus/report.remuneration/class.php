<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
/**
 * @author: Vitaly Melnik <vitali@rarus.ru>
 */
class CReportRemuneration extends CBitrixComponent {

    public $obElement = null;
    public $obUser = null;

    /**
     * Инициализация
     */
    public function init() {

        // Подключаем модули
        $obModule = new CModule;

        // IB
        if(!$obModule->IncludeModule('iblock')) {
            throw Exception('Не удалось подключить модуль "iblock"');
        }
        unset($obModule);

        // Объекты
        $this->obUser       = new CUser;
        $this->obElement    = new CIBlockElement;
        $this->obPropEnum   = new CIBlockPropertyEnum;
    }


    /**
     * Отдает список всех организаторов
     * @return array
     * @throws Exception
     */
    public function getListOrganizers() {

        $arResult = [];

        $iGroupId = getGroupIdByRole('p');

        if(empty($iGroupId)) {
            throw new Exception('Не удалось получить идентификатор группы "Организатор"');
        }

        // Пользователи
        $arUsersId  = [];
        $rs = $this->obUser->GetList(
            ($by="id"),
            ($order="asc"),
            ['GROUPS_ID' => $iGroupId],
            [
                'FIELDS' => ['ID', 'NAME', 'LAST_NAME', 'EMAIL',]
            ]
        );

        while ($arRow = $rs->Fetch()) {

            $arTitle = [];
            if(!empty($arRow['NAME'])) {
                $arTitle[] = $arRow['NAME'];
            }

            if(!empty($arRow['LAST_NAME'])) {
                $arTitle[] = $arRow['LAST_NAME'];
            }

            if(!empty($arTitle)) {
                $arTitle[] = '(' . $arRow['EMAIL'] . ')';
            } else {
                $arTitle[] = $arRow['EMAIL'];
            }

            $arResult[$arRow['ID']] = implode(' ', $arTitle);

            $arUsersId[] = $arRow['ID'];
        }

        if(empty($arUsersId)) {
            return $arResult;
        }


        // Данные из профиля
        $rs = $this->obElement->GetList(
            ['PROPERTY_FULL_COMPANY_NAME' => 'ASC',],
            [
                'ACTIVE'        => 'Y',
                'IBLOCK_ID'     => getIBlockID('partner', 'partner_profile'),
                'PROPERTY_USER' => $arUsersId,
            ],
            false,
            false,
            [
                'PROPERTY_FULL_COMPANY_NAME',
                'PROPERTY_USER',
            ]
        );

        while ($arRow = $rs->Fetch()) {

            if(!empty($arRow['PROPERTY_FULL_COMPANY_NAME_VALUE'])) {
                $arResult[$arRow['PROPERTY_USER_VALUE']] = $arRow['PROPERTY_FULL_COMPANY_NAME_VALUE'];
            }
        }

        // Сортируем
        asort($arResult);

        return $arResult;
    }



    /**
     * Отдает статусы сделки
     */
    public function getStatusDeals() {

        $arResult = [];

        $rs = $this->obPropEnum->GetList(
            ['SORT' => 'ASC'],
            [
                'IBLOCK_ID' => getIBlockID('deals', 'deals_deals'),
                'CODE'      => 'STATUS',
            ]
        );

        while ($arRow = $rs->Fetch()) {
            $arResult[$arRow['XML_ID']] = $arRow;
        }

        return $arResult;
    }



    /**
     * Отдает поля покупателя
     * @param $arClientsId
     * @return array
     */
    public function getClientsById($arClientsId) {

        $arResult = [];

        if(empty($arClientsId)) {
            return $arResult;
        }

        // Пользователи
        $rs = $this->obUser->GetList(
            ($by="id"),
            ($order="asc"),
            ['ID' => implode(' | ', $arClientsId)],
            [
                'FIELDS' => ['ID', 'NAME', 'LAST_NAME', 'EMAIL',]
            ]
        );

        while ($arRow = $rs->Fetch()) {

            $arTitle = [];
            if(!empty($arRow['NAME'])) {
                $arTitle[] = $arRow['NAME'];
            }

            if(!empty($arRow['LAST_NAME'])) {
                $arTitle[] = $arRow['LAST_NAME'];
            }

            if(!empty($arTitle)) {
                $arTitle[] = '(' . $arRow['EMAIL'] . ')';
            } else {
                $arTitle[] = $arRow['EMAIL'];
            }

            $arResult[$arRow['ID']] = [
                'TITLE'         => implode(' ', $arTitle),
                'PARTNER_ID'    => null,
            ];
        }

        // Данные из профиля
        $rs = $this->obElement->GetList(
            ['ID' => 'ASC',],
            [
                'ACTIVE'        => 'Y',
                'IBLOCK_ID'     => getIBlockID('client', 'client_profile'),
                'PROPERTY_USER' => $arClientsId,
            ],
            false,
            false,
            [
                'PROPERTY_FULL_COMPANY_NAME',
                'PROPERTY_USER',
                'PROPERTY_IP_FIO',
                'PROPERTY_UL_TYPE',
                'PROPERTY_PARTNER_ID',
            ]
        );

        // Тип ЮЛ
        $arULType = rrsIblock::getPropListKey('client_profile', 'UL_TYPE');

        while ($arRow = $rs->Fetch()) {

            $sTitle = null;
            if($arRow['PROPERTY_UL_TYPE_ENUM_ID'] == $arULType['ul']['ID']) {
                $sTitle .= trim($arRow['PROPERTY_FULL_COMPANY_NAME_VALUE']);
            } elseif ($arRow['PROPERTY_UL_TYPE_ENUM_ID'] == $arULType['ip']['ID']) {
                $sTitle .= trim($arRow['PROPERTY_IP_FIO_VALUE']);

                if(!empty($sTitle))
                    $sTitle = 'ИП ' . $sTitle;
            }

            $arResult[$arRow['PROPERTY_USER_VALUE']]['TITLE'] = $sTitle;
            $arResult[$arRow['PROPERTY_USER_VALUE']]['PARTNER_ID'] = $arRow['PROPERTY_PARTNER_ID_VALUE'];
        }

        return $arResult;
    }


    /**
     * Отдает поля АП
     * @param $arFarmerId
     * @return array
     */
    public function getFarmerById($arFarmerId) {

        $arResult = [];

        if(empty($arFarmerId)) {
            return $arResult;
        }


        // Пользователи
        $rs = $this->obUser->GetList(
            ($by="id"),
            ($order="asc"),
            ['ID' => implode(' | ', $arFarmerId)],
            [
                'FIELDS' => ['ID', 'NAME', 'LAST_NAME', 'EMAIL',]
            ]
        );

        while ($arRow = $rs->Fetch()) {

            $arTitle = [];
            if(!empty($arRow['NAME'])) {
                $arTitle[] = $arRow['NAME'];
            }

            if(!empty($arRow['LAST_NAME'])) {
                $arTitle[] = $arRow['LAST_NAME'];
            }

            if(!empty($arTitle)) {
                $arTitle[] = '(' . $arRow['EMAIL'] . ')';
            } else {
                $arTitle[] = $arRow['EMAIL'];
            }

            $arResult[$arRow['ID']] = ['TITLE' => implode(' ', $arTitle)];
        }


        // Данные из профиля
        $rs = $this->obElement->GetList(
            ['ID' => 'ASC',],
            [
                'ACTIVE'        => 'Y',
                'IBLOCK_ID'     => getIBlockID('farmer', 'farmer_profile'),
                'PROPERTY_USER' => $arFarmerId,
            ],
            false,
            false,
            [
                'PROPERTY_FULL_COMPANY_NAME',
                'PROPERTY_USER',
                'PROPERTY_IP_FIO',
                'PROPERTY_UL_TYPE',
            ]
        );

        $arULType = rrsIblock::getPropListKey('farmer_profile', 'UL_TYPE');

        while ($arRow = $rs->Fetch()) {

            $sTitle = null;
            if($arRow['PROPERTY_UL_TYPE_ENUM_ID'] == $arULType['ul']['ID']) {
                $sTitle .= trim($arRow['PROPERTY_FULL_COMPANY_NAME_VALUE']);
            } elseif ($arRow['PROPERTY_UL_TYPE_ENUM_ID'] == $arULType['ip']['ID']) {
                $sTitle .= trim($arRow['PROPERTY_IP_FIO_VALUE']);

                if(!empty($sTitle))
                    $sTitle = 'ИП ' . $sTitle;
            }

            $arResult[$arRow['PROPERTY_USER_VALUE']]['TITLE'] = $sTitle;
        }

        return $arResult;
    }


    /**
     * Отдает поля организатора
     * @param $arPartnerId
     * @return array
     */
    public function getPartnerById($arPartnerId) {

        $arResult = [];

        if(empty($arPartnerId)) {
            return $arResult;
        }

        // Пользователи
        $rs = $this->obUser->GetList(
            ($by="id"),
            ($order="asc"),
            ['ID' => implode(' | ', $arPartnerId)],
            [
                'FIELDS' => ['ID', 'NAME', 'LAST_NAME', 'EMAIL',]
            ]
        );

        while ($arRow = $rs->Fetch()) {

            $arTitle = [];
            if(!empty($arRow['NAME'])) {
                $arTitle[] = $arRow['NAME'];
            }

            if(!empty($arRow['LAST_NAME'])) {
                $arTitle[] = $arRow['LAST_NAME'];
            }

            if(!empty($arTitle)) {
                $arTitle[] = '(' . $arRow['EMAIL'] . ')';
            } else {
                $arTitle[] = $arRow['EMAIL'];
            }

            $arResult[$arRow['ID']] = ['TITLE' => implode(' ', $arTitle)];
        }

        // Данные из профиля
        $rs = $this->obElement->GetList(
            ['ID' => 'ASC',],
            [
                'ACTIVE'        => 'Y',
                'IBLOCK_ID'     => getIBlockID('partner', 'partner_profile'),
                'PROPERTY_USER' => $arPartnerId,
            ],
            false,
            false,
            [
                'PROPERTY_FULL_COMPANY_NAME',
                'PROPERTY_USER',
                'PROPERTY_IP_FIO',
                'PROPERTY_UL_TYPE',
            ]
        );

        $arULType = rrsIblock::getPropListKey('partner_profile', 'UL_TYPE');

        while ($arRow = $rs->Fetch()) {


            $sTitle = null;
            if($arRow['PROPERTY_UL_TYPE_ENUM_ID'] == $arULType['ul']['ID']) {
                $sTitle .= trim($arRow['PROPERTY_FULL_COMPANY_NAME_VALUE']);
            } elseif ($arRow['PROPERTY_UL_TYPE_ENUM_ID'] == $arULType['ip']['ID']) {
                $sTitle .= trim($arRow['PROPERTY_IP_FIO_VALUE']);

                if(!empty($sTitle))
                    $sTitle = 'ИП ' . $sTitle;
            }

            $arResult[$arRow['PROPERTY_USER_VALUE']]['TITLE'] = $sTitle;
        }

        return $arResult;
    }


    /**
     * Отдает линки организаторов покупателя
     * @param $arClientsId
     * @return array
     * @throws Exception
     */
    public function getLinkPartnerClientsByClientsId($arClientsId) {

        $arResult = [];

        if(empty($arClientsId)) {
            return $arResult;
        }

        // "Покупатель"-"Привязка к организаторам"
        $rs = $this->obElement->GetList(
            ['ID' => 'ASC',],
            [
                'ACTIVE'            => 'Y',
                'IBLOCK_ID'         => getIBlockID('client', 'client_partner_link'),
                'PROPERTY_USER_ID'  => $arClientsId,
            ],
            false,
            false,
            [
                'ID',
                'PROPERTY_USER_ID',
                'PROPERTY_PARTNER_ID',
            ]
        );

        while ($arRow = $rs->Fetch()) {

            if(empty($arRow['PROPERTY_USER_ID_VALUE']) || empty($arRow['PROPERTY_PARTNER_ID_VALUE'])) {
                throw new Exception('Нет линковки клиента на партнера в ИБ "Покупатель"-"Привязка к организаторам" ИД['.$arRow['ID'].']');
            }
            $arResult[$arRow['PROPERTY_USER_ID_VALUE']] = $arRow['PROPERTY_PARTNER_ID_VALUE'];
        }

        return $arResult;
    }


    /**
     * Документы по сделке
     * @param $arDealsId
     * @return array
     */
    public function getDocumentByDealsId($arDealsId) {

        $arResult = [];

        if(empty($arDealsId)) {
            return $arResult;
        }

        $rs = $this->obElement->GetList(
            [],
            [
                'ACTIVE'        => 'Y',
                'IBLOCK_ID'     => getIBlockID('deals', 'deals_docs'),
                'PROPERTY_DEAL' => $arDealsId,
            ],
            false,
            false,
            ['ID', 'NAME', 'CODE', 'DATE_CREATE', 'PROPERTY_DEAL',]
        );

        while ($arRow = $rs->Fetch()) {

            $arResult[$arRow['PROPERTY_DEAL_VALUE']][$arRow['CODE']] = [
                'ID'            => $arRow['ID'],
                'DATE_CREATE'   => $arRow['DATE_CREATE'],
            ];
        }

        return $arResult;

    }


    /**
     * Отдает данные из "Ведомости исполнения"
     * @param $arDealsId
     * @return array
     */
    public function getDataStatements($arDealsId) {

        $arResult = [];

        if(empty($arDealsId)) {
            return $arResult;
        }

        $rs = $this->obElement->GetList(
            [],
            [
                'ACTIVE'        => 'Y',
                'IBLOCK_ID'     => getIBlockID('deals', 'deals_exe_docs'),
                'PROPERTY_DEAL' => $arDealsId,
            ],
            false,
            false,
            ['ID', 'PROPERTY_DEAL', 'PROPERTY_COST',]
        );

        while ($arRow = $rs->Fetch()) {

            if(empty($arResult[$arRow['PROPERTY_DEAL_VALUE']])) {
                $arResult[$arRow['PROPERTY_DEAL_VALUE']] = [
                    'COST_DEALS' => 0,
                ];
            }

            $arResult[$arRow['PROPERTY_DEAL_VALUE']]['COST_DEALS'] += $arRow['PROPERTY_COST_VALUE'];
        }

        return $arResult;
    }


    /**
     * Формат числа
     * @param $n
     * @return string
     */
    public function getNumberFormat($n) {
        return number_format( $n, 2, '.', ' ');
    }


    /**
     * Отдает Агентов АП
     * @param $arAgentAPId
     * @return array
     */
    public function getAgentAPById($arAgentAPId) {

        $arResult = [];

        if(empty($arAgentAPId)) {
            return $arResult;
        }

        // Пользователи
        $rs = $this->obUser->GetList(
            ($by="id"),
            ($order="asc"),
            ['ID' => implode(' | ', $arAgentAPId)],
            [
                'FIELDS' => ['ID', 'NAME', 'LAST_NAME', 'EMAIL',]
            ]
        );

        // Процент вознаграждения по умолчанию
        $nRewardPercent                 = rrsIblock::getConst('REWARD_PERCENT_AGENT');
        $nRewardPercentTransportation   = rrsIblock::getConst('REWARD_PERCENT_TRANSPORTATION_AGENT');


        while ($arRow = $rs->Fetch()) {

            $arTitle = [];
            if(!empty($arRow['NAME'])) {
                $arTitle[] = $arRow['NAME'];
            }

            if(!empty($arRow['LAST_NAME'])) {
                $arTitle[] = $arRow['LAST_NAME'];
            }

            if(!empty($arTitle)) {
                $arTitle[] = '(' . $arRow['EMAIL'] . ')';
            } else {
                $arTitle[] = $arRow['EMAIL'];
            }

            $arResult[$arRow['ID']] = [
                'TITLE'                         => implode(' ', $arTitle),
                'REWARD_PERCENT'                => $nRewardPercent,
                'REWARD_PERCENT_TRANSPORTATION' => $nRewardPercentTransportation,
                'PARTNER_ID'                    => null,
            ];
        }

        // Данные из профиля
        $rs = $this->obElement->GetList(
            ['ID' => 'ASC',],
            [
                'ACTIVE'        => 'Y',
                'IBLOCK_ID'     => getIBlockID('agent', 'agent_profile'),
                'PROPERTY_USER' => $arAgentAPId,
            ],
            false,
            false,
            [
                'PROPERTY_USER',
                'PROPERTY_PARTNER_ID',
                'PROPERTY_REWARD_PERCENT',
                'PROPERTY_PERCENT_TRANSPORTATION',
            ]
        );

        while ($arRow = $rs->Fetch()) {
            $arResult[$arRow['PROPERTY_USER_VALUE']]['REWARD_PERCENT']                  = $arRow['PROPERTY_REWARD_PERCENT_VALUE'];
            $arResult[$arRow['PROPERTY_USER_VALUE']]['REWARD_PERCENT_TRANSPORTATION']   = $arRow['PROPERTY_PERCENT_TRANSPORTATION_VALUE'];

            if(!empty($arRow['PROPERTY_PARTNER_ID_VALUE'])) {
                $arResult[$arRow['PROPERTY_USER_VALUE']]['PARTNER_ID'] = $arRow['PROPERTY_PARTNER_ID_VALUE'];
            }
        }

        return $arResult;
    }



    /**
     * Отдает Агентов покупателя
     * @param $arAgentClientsId
     * @return array
     */
    public function getAgentClientsById($arAgentClientsId) {

        $arResult = [];

        if(empty($arAgentClientsId)) {
            return $arResult;
        }

        // Пользователи
        $rs = $this->obUser->GetList(
            ($by="id"),
            ($order="asc"),
            ['ID' => implode(' | ', $arAgentClientsId)],
            [
                'FIELDS' => ['ID', 'NAME', 'LAST_NAME', 'EMAIL',]
            ]
        );

        while ($arRow = $rs->Fetch()) {

            $arTitle = [];
            if(!empty($arRow['NAME'])) {
                $arTitle[] = $arRow['NAME'];
            }

            if(!empty($arRow['LAST_NAME'])) {
                $arTitle[] = $arRow['LAST_NAME'];
            }

            if(!empty($arTitle)) {
                $arTitle[] = '(' . $arRow['EMAIL'] . ')';
            } else {
                $arTitle[] = $arRow['EMAIL'];
            }

            $arResult[$arRow['ID']] = [
                'TITLE'             => implode(' ', $arTitle),
                'REWARD_PERCENT'    => null,
                'PARTNER_ID'        => null,
            ];
        }

        // Данные из профиля
        $rs = $this->obElement->GetList(
            ['ID' => 'ASC',],
            [
                'ACTIVE'        => 'Y',
                'IBLOCK_ID'     => getIBlockID('agent', 'client_agent_profile'),
                'PROPERTY_USER' => $arAgentClientsId,
            ],
            false,
            false,
            [
                'PROPERTY_USER',
                'PROPERTY_PARTNER_ID',
                'PROPERTY_REWARD_PERCENT',
            ]
        );

        while ($arRow = $rs->Fetch()) {
            $arResult[$arRow['PROPERTY_USER_VALUE']]['REWARD_PERCENT'] = $arRow['PROPERTY_REWARD_PERCENT_VALUE'];
            if(!empty($arRow['PROPERTY_PARTNER_ID_VALUE'])) {
                $arResult[$arRow['PROPERTY_USER_VALUE']]['PARTNER_ID'] = $arRow['PROPERTY_PARTNER_ID_VALUE'];
            }
        }

        return $arResult;
    }


    /**
     * Отдает ТК
     * @param $arTransportId
     * @return array
     */
    public function getTransportsById($arTransportId) {

        $arResult = [];
        if(empty($arTransportId)) {
            return $arResult;
        }

        // Пользователи
        $rs = $this->obUser->GetList(
            ($by="id"),
            ($order="asc"),
            ['ID' => implode(' | ', $arTransportId)],
            [
                'FIELDS' => ['ID', 'NAME', 'LAST_NAME', 'EMAIL',]
            ]
        );

        while ($arRow = $rs->Fetch()) {

            $arTitle = [];
            if(!empty($arRow['NAME'])) {
                $arTitle[] = $arRow['NAME'];
            }

            if(!empty($arRow['LAST_NAME'])) {
                $arTitle[] = $arRow['LAST_NAME'];
            }

            if(!empty($arTitle)) {
                $arTitle[] = '(' . $arRow['EMAIL'] . ')';
            } else {
                $arTitle[] = $arRow['EMAIL'];
            }

            $arResult[$arRow['ID']] = [
                'TITLE' => implode(' ', $arTitle),
            ];
        }


        // Данные из профиля
        $rs = $this->obElement->GetList(
            ['ID' => 'ASC',],
            [
                'ACTIVE'        => 'Y',
                'IBLOCK_ID'     => getIBlockID('transport_company', 'transport_profile'),
                'PROPERTY_USER' => $arTransportId,
            ],
            false,
            false,
            [
                'PROPERTY_FULL_COMPANY_NAME',
                'PROPERTY_USER',
                'PROPERTY_IP_FIO',
                'PROPERTY_UL_TYPE',
            ]
        );

        $arULType = rrsIblock::getPropListKey('transport_profile', 'UL_TYPE');

        while ($arRow = $rs->Fetch()) {


            $sTitle = null;
            if($arRow['PROPERTY_UL_TYPE_ENUM_ID'] == $arULType['ul']['ID']) {
                $sTitle .= trim($arRow['PROPERTY_FULL_COMPANY_NAME_VALUE']);
            } elseif ($arRow['PROPERTY_UL_TYPE_ENUM_ID'] == $arULType['ip']['ID']) {
                $sTitle .= trim($arRow['PROPERTY_IP_FIO_VALUE']);

                if(!empty($sTitle))
                    $sTitle = 'ИП ' . $sTitle;
            }

            $arResult[$arRow['PROPERTY_USER_VALUE']]['TITLE'] = $sTitle;
        }

        return $arResult;
    }
}