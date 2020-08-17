<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
/**
 * @author: Vitaly Melnik <vitali@rarus.ru>
 */
/** @var CBitrixComponent $this */
/** @var array $arParams */
/** @var array $arResult */
/** @var string $componentPath */
/** @var string $componentName */
/** @var string $componentTemplate */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */
/** @var CReportRemuneration $this */


/**
 * Результирующий массив
 */
$arResult = [
    'ERROR_MSG'     => null,
];


try {

    // Инициализация
    $this->init();

    // Заглушка
    $arResult = [
        'ORGANIZER_LIST'    => [],
        'STATUSES_DEALS'    => [],
        'CLIENTS'           => [],
        'TRANSPORTS'        => [],
        'FARMERS'           => [],
        'PARTNERS'          => [],
        'AGENT_AP'          => [],
        'AGENT_CLIENTS'     => [],
        'DATA_REPORT'       => [],
        'TOTAL_DATA_REPORT' => [],
        'PROCESSING_REPORT' => false,
    ];


    /**
     * Параметры
     */

    // С
    $arParams['DATE_FROM'] = trim($_POST['DATE_FROM']);
    if(empty($arParams['DATE_FROM'])) {
        $d = new DateTime('NOW');
        $arParams['DATE_FROM'] = $d->modify('- 10 day')->format('d.m.Y');
    }

    // По
    $arParams['DATE_TO'] = trim($_POST['DATE_TO']);
    if(empty($arParams['DATE_TO'])) {
        $d = new DateTime('NOW');
        $arParams['DATE_TO'] = $d->format('d.m.Y');
    }


    // Все группы пользователя
    $arUserGroups = $this->obUser->GetUserGroup($this->obUser->GetID());

    $arParams['IS_SHOW_FILTER_ORGANIZER'] = false;
    if(in_array(getGroupIdByRole('a'), $arUserGroups)) {            // Админ

        $arParams['IS_SHOW_FILTER_ORGANIZER'] = true;
        $arParams['ORGANIZER']      = intval($_POST['ORGANIZER']);
        $arResult['ORGANIZER_LIST'] = $this->getListOrganizers();

    } elseif (in_array(getGroupIdByRole('p'), $arUserGroups)) {     // Организатор

        $arParams['ORGANIZER'] = $this->obUser->GetID();
    } else {                                                        // Кто-то левый
        throw new Exception('Доступ к странице запрещен');
    }


    $arFilter = [];

    /**
     * Обработка формирования отчетов
     */
    if(
        (!empty($_POST['GET_REPORT']) && $_POST['GET_REPORT'] == 'Y') ||
        (!empty($_POST['GET_REPORT_TRANSPORTATION']) && $_POST['GET_REPORT_TRANSPORTATION'] == 'Y')
    ) {

        if(!check_bitrix_sessid()) {
            throw new Exception('Ваша сессия истекла');
        }

        $arResult['PROCESSING_REPORT'] = true;

        // Проверка дат
        $date1Wrong = $date2Wrong = $date2Less = null;
        CheckFilterDates($arParams['DATE_FROM'], $arParams['DATE_TO'], $date1Wrong, $date2Wrong, $date2Less);

        if ($date1Wrong == "Y") {
            throw new Exception('Неверный формат "Сделка от"');
        }

        if ($date2Wrong == "Y") {
            throw new Exception('Неверный формат "Сделка по"');
        }

        // Первая дата больше второй
        if ($date2Less == "Y") {
            // Меняем даты местами
            list($arParams['DATE_TO'], $arParams['DATE_FROM']) = [$arParams['DATE_FROM'], $arParams['DATE_TO']];
        }


        // Формируем фильтр по сделкам
        $arFilter = [
            'ACTIVE'    => 'Y',
            'IBLOCK_ID' => getIBlockID('deals', 'deals_deals'),
        ];

        // Статусы сделки
        $arResult['STATUSES_DEALS'] = $this->getStatusDeals();

        if(empty($arResult['STATUSES_DEALS']['close']['ID'])) {
            throw new Exception('Не удалось определить ИД закрытого статуса сделки');
        }

        // Формируем отчет по закрытым сделкам
        $arFilter['PROPERTY_STATUS'] = $arResult['STATUSES_DEALS']['close']['ID'];

        $arFilter['>=PROPERTY_DATE_STAGE'] = DateTime::createFromFormat('d.m.Y', $arParams['DATE_FROM'])->format('Y-m-d') . ' 00:00:01';
        $arFilter['<=PROPERTY_DATE_STAGE'] = DateTime::createFromFormat('d.m.Y', $arParams['DATE_TO'])->format('Y-m-d') . ' 23:59:59';

        if(!empty($arParams['ORGANIZER'])) {
            $arFilter['PROPERTY_PARTNER'] = $arParams['ORGANIZER'];
        }
    }





    /**
     * Отчет по вознаграждениям
     */
    if(!empty($_POST['GET_REPORT']) && $_POST['GET_REPORT'] == 'Y') {

        // Сделки
        $rs = $this->obElement->GetList(
            ['id' => 'asc'],
            $arFilter,
            false,
            false,
            [
                'ID',
                'PROPERTY_FARMER',
                'PROPERTY_CLIENT',
                'PROPERTY_PARTNER',
                'PROPERTY_AGENT_USER',
                'PROPERTY_AGENT_CLIENT_USER',
                'PROPERTY_REWARD_PERCENT_AGENT',
                'PROPERTY_REWARD_PERCENT_OPERATOR_AH',
                'PROPERTY_REWARD_PERCENT_ORGANIZER',
            ]
        );

        $arDealsId          = [];
        $arClientsId        = [];
        $arFarmerId         = [];
        $arPartnerId        = [];
        $arAgentId          = [];
        $arAgentClientsId   = [];

        // Параметры по умолчанию
        $nParamRewardPercentOperatorAH = rrsIblock::getConst('REWARD_PERCENT_OPERATOR_AH');
        if(empty($nParamRewardPercentOperatorAH)) {
            throw new Exception('Не удалось получить параметр "Вознаграждение оператора АХ от организатора АП [%]"');
        }

        $nParamRewardPercentOrganizer = rrsIblock::getConst('REWARD_PERCENT_ORGANIZER');
        if(empty($nParamRewardPercentOrganizer)) {
            throw new Exception('Не удалось получить параметр "Вознаграждение организатору покупателя от вознаграждения организатора АП (если они разные) [%]"');
        }

        $nCommission = rrsIblock::getConst('commission');
        if(empty($nCommission)) {
            throw new Exception('Не удалось получить параметр "Комиссия партнера, %"');
        }

        
        while ($arRow = $rs->Fetch()) {

            $iClientId = intval($arRow['PROPERTY_CLIENT_VALUE']);
            if(!empty($iClientId)) {
                $arClientsId[$iClientId] = $iClientId;
            }

            $iFarmerId = intval($arRow['PROPERTY_FARMER_VALUE']);
            if(!empty($iFarmerId)) {
                $arFarmerId[$iFarmerId] = $iFarmerId;
            }

            $iPartnerId = intval($arRow['PROPERTY_PARTNER_VALUE']);
            if(!empty($iPartnerId)) {
                $arPartnerId[$iPartnerId] = $iPartnerId;
            }

            $iAgentId = intval($arRow['PROPERTY_AGENT_USER_VALUE']);
            if(!empty($iAgentId)) {
                $arAgentId[$iAgentId] = $iAgentId;
            }

            $iAgentClientId = intval($arRow['PROPERTY_AGENT_CLIENT_USER_VALUE']);
            if(!empty($iAgentClientId)) {
                $arAgentClientsId[$iAgentClientId] = $iAgentClientId;
            }

            $arDealsId[] = $arRow['ID'];

            // Если не задано поле "Процент вознаграждения оператора АХ от организатора АП" берем дефолтное
            if(!empty($arRow['PROPERTY_REWARD_PERCENT_OPERATOR_AH_VALUE'])) {
                $nRewardPercentOperatorAH = $arRow['PROPERTY_REWARD_PERCENT_OPERATOR_AH_VALUE'];
            } else {
                $nRewardPercentOperatorAH = $nParamRewardPercentOperatorAH;
            }

            // Если не задано поле "Процент вознаграждения организатору покупателя от вознаграждения организатора АП (если они разные)" берем дефолтное
            if(!empty($arRow['PROPERTY_REWARD_PERCENT_ORGANIZER_VALUE'])) {
                $nRewardPercentOrganizer = $arRow['PROPERTY_REWARD_PERCENT_ORGANIZER_VALUE'];
            } else {
                $nRewardPercentOrganizer = $nParamRewardPercentOrganizer;
            }



            // Результирующий массив отчета
            $arResult['DATA_REPORT'][$arRow['ID']] = [
                'DKP' => null,

                'FARMER_ID'     => $arRow['PROPERTY_FARMER_VALUE'],
                'FARMER_NAME'   => null,

                'CLIENT_ID'     => $arRow['PROPERTY_CLIENT_VALUE'],
                'CLIENT_NAME'   => null,

                'COST_DEALS'    => null,

                'PARTNER_ID'    => $arRow['PROPERTY_PARTNER_VALUE'],
                'PARTNER_NAME'  => null,

                'REMUNERATION_ORGANIZER_AP' => null,

                'AGENT_ID'              => $arRow['PROPERTY_AGENT_USER_VALUE'],
                'AGENT_NAME'            => null,
                'AGENT_REWARD_PERCENT'  => $arRow['PROPERTY_REWARD_PERCENT_AGENT_VALUE'],
                'AGENT_PAYMENTS'        => null,

                'REWARD_PERCENT_OPERATOR_AH'    => $nRewardPercentOperatorAH,
                'PAYMENTS_OPERATOR_AH'          => null,

                'AGENT_CLIENT_ID'   => $arRow['PROPERTY_AGENT_CLIENT_USER_VALUE'],

                'CLIENT_PARTNER_ID'             => null,
                'CLIENT_PARTNER_NAME'           => null,
                'CLIENT_PARTNER_REWARD_PERCENT' => $nRewardPercentOrganizer,
                'PAYMENTS_CLIENT_PARTNER'       => null,
            ];
        }


        $arLinkPartnerClients  = [];

        // Покупатели
        if(!empty($arClientsId)) {
            $arClientsId = array_values($arClientsId);
            $arResult['CLIENTS'] = $this->getClientsById($arClientsId);

            // Линки покупателя на организатора
            $arLinkPartnerClients = $this->getLinkPartnerClientsByClientsId($arClientsId);

            // Дополняем массив ИД организаторов
            foreach ($arLinkPartnerClients as $iPartnerId) {
                if(!empty($iPartnerId)) {
                    $arPartnerId[$iPartnerId] = $iPartnerId;
                }
            }
        }

        // АП
        if(!empty($arFarmerId)) {
            $arFarmerId = array_values($arFarmerId);
            $arResult['FARMERS'] = $this->getFarmerById($arFarmerId);
        }


        // Агенты АП
        if(!empty($arAgentId)) {
            $arResult['AGENT_AP'] = $this->getAgentAPById($arAgentId);
        }


        // Агенты покупателя
        if(!empty($arAgentClientsId)) {
            $arResult['AGENT_CLIENTS'] = $this->getAgentClientsById($arAgentClientsId);
        }

        // Документы по сделкам
        $arResult['DOCUMENTS'] = $this->getDocumentByDealsId($arDealsId);


        // Ведомости исполнения
        $arResult['DATA_STATEMENTS'] = $this->getDataStatements($arDealsId);


        // Организаторы
        if(!empty($arPartnerId)) {
            $arPartnerId = array_values($arPartnerId);
            $arResult['PARTNERS'] = $this->getPartnerById($arPartnerId);
        }

        // Итоговые поля отчета
        $arResult['TOTAL_DATA_REPORT'] = [
            'COST_DEALS'                => 0,
            'REMUNERATION_ORGANIZER_AP' => 0,
            'AGENT_PAYMENTS'            => 0,
            'PAYMENTS_OPERATOR_AH'      => 0,
            'PAYMENTS_CLIENT_PARTNER'   => 0,
        ];

        /**
         * Дополняем отчет данными
         */
        foreach ($arResult['DATA_REPORT'] as $iDealId => &$arItem) {

            // ДКП
            $sDKP = null;
            if(!empty($arResult['DOCUMENTS'][$iDealId]['dkp'])) {
                $sDKP .= 'ДКП №' . $arResult['DOCUMENTS'][$iDealId]['dkp']['ID'];
                $sDKP .= ' от ' . DateTime::createFromFormat('d.m.Y H:i:s', $arResult['DOCUMENTS'][$iDealId]['dkp']['DATE_CREATE'])->format('d.m.Y');
            }
            $arItem['DKP'] = $sDKP;

            // Поставщик
            $arItem['FARMER_NAME'] = $arResult['FARMERS'][$arItem['FARMER_ID']]['TITLE'];

            // Покупатель
            $arItem['CLIENT_NAME'] = $arResult['CLIENTS'][$arItem['CLIENT_ID']]['TITLE'];

            // Сумма сделки
            $arItem['COST_DEALS'] = $arResult['DATA_STATEMENTS'][$iDealId]['COST_DEALS'];

            // Организатор АП
            $arItem['PARTNER_NAME'] = $arResult['PARTNERS'][$arItem['PARTNER_ID']]['TITLE'];

            // Вознаграждение Организатору АП
            if(!empty($arItem['COST_DEALS'])) {
                $arItem['REMUNERATION_ORGANIZER_AP'] = $nCommission * $arItem['COST_DEALS'] / (100 - $nCommission);
            }

            // Агент
            if(!empty($arItem['AGENT_ID'])) {

                $arItem['AGENT_NAME'] = $arResult['AGENT_AP'][$arItem['AGENT_ID']]['TITLE'];

                // Если в сделке нет % вознаграждения АП, тянем % из профиля
                /*if(empty($arItem['AGENT_REWARD_PERCENT'])) {
                    $arItem['AGENT_REWARD_PERCENT'] = $arResult['AGENT_AP'][$arItem['AGENT_ID']]['REWARD_PERCENT'];
                }*/

                // Выплаты Агенту
                if(!empty($arItem['COST_DEALS']) && !empty($arItem['AGENT_REWARD_PERCENT'])) {
                    $arItem['AGENT_PAYMENTS'] = ($arItem['COST_DEALS'] / 100) * $arItem['AGENT_REWARD_PERCENT'];
                }
            }

            // Выплаты Оператору (АХ)
            if(!empty($arItem['REMUNERATION_ORGANIZER_AP'])) {
                $arItem['PAYMENTS_OPERATOR_AH'] = ($arItem['REMUNERATION_ORGANIZER_AP'] / 100) * $arItem['REWARD_PERCENT_OPERATOR_AH'];
            }

            // Организатор Покупателя
            $arItem['CLIENT_PARTNER_ID'] = $arLinkPartnerClients[$arItem['CLIENT_ID']];
            if(!empty($arItem['CLIENT_PARTNER_ID'])) {

                if(empty($arResult['PARTNERS'][$arItem['CLIENT_PARTNER_ID']])) {
                    throw new Exception('Не удалось получить профиль организатора ИД['.$arItem['CLIENT_PARTNER_ID'].']');
                }

                $arItem['CLIENT_PARTNER_NAME'] = $arResult['PARTNERS'][$arItem['CLIENT_PARTNER_ID']]['TITLE'];

                if(($arItem['CLIENT_PARTNER_ID'] != $arItem['PARTNER_ID']) && !empty($arItem['REMUNERATION_ORGANIZER_AP'])) {
                    $arItem['PAYMENTS_CLIENT_PARTNER'] = ($arItem['REMUNERATION_ORGANIZER_AP'] / 100) * $arItem['CLIENT_PARTNER_REWARD_PERCENT'];
                }
            }



            $arResult['TOTAL_DATA_REPORT']['COST_DEALS']                += $arItem['COST_DEALS'];
            $arResult['TOTAL_DATA_REPORT']['AGENT_PAYMENTS']            += $arItem['AGENT_PAYMENTS'];
            $arResult['TOTAL_DATA_REPORT']['PAYMENTS_OPERATOR_AH']      += $arItem['PAYMENTS_OPERATOR_AH'];
            $arResult['TOTAL_DATA_REPORT']['PAYMENTS_CLIENT_PARTNER']   += $arItem['PAYMENTS_CLIENT_PARTNER'];
            $arResult['TOTAL_DATA_REPORT']['REMUNERATION_ORGANIZER_AP'] += $arItem['REMUNERATION_ORGANIZER_AP'];


            $arItem['COST_DEALS']                   = $this->getNumberFormat($arItem['COST_DEALS']);
            $arItem['AGENT_PAYMENTS']               = $this->getNumberFormat($arItem['AGENT_PAYMENTS']);
            $arItem['PAYMENTS_OPERATOR_AH']         = $this->getNumberFormat($arItem['PAYMENTS_OPERATOR_AH']);
            $arItem['PAYMENTS_CLIENT_PARTNER']      = $this->getNumberFormat($arItem['PAYMENTS_CLIENT_PARTNER']);
            $arItem['REMUNERATION_ORGANIZER_AP']    = $this->getNumberFormat($arItem['REMUNERATION_ORGANIZER_AP']);

            if(!empty($arItem['AGENT_REWARD_PERCENT'])) {
                $arItem['AGENT_REWARD_PERCENT'] = $this->getNumberFormat($arItem['AGENT_REWARD_PERCENT']);
            }



        }
        unset($arItem);

        foreach ($arResult['TOTAL_DATA_REPORT'] as  &$nValue) {
            $nValue = $this->getNumberFormat($nValue);
        }
        unset($nValue);
    }
    /**
     * Отчет по перевозкам
     */
    elseif (!empty($_POST['GET_REPORT_TRANSPORTATION']) && $_POST['GET_REPORT_TRANSPORTATION'] == 'Y') {

        // Интересуют сделки с участием ТК
        $arFilter['!=PROPERTY_TRANSPORT'] = false;

        // Сделки
        $rs = $this->obElement->GetList(
            ['id' => 'asc'],
            $arFilter,
            false,
            false,
            [
                'ID',
                'PROPERTY_FARMER',
                'PROPERTY_TRANSPORT',
                'PROPERTY_PARTNER',
                'PROPERTY_REWARD_PERCENT_TRANSPORTATION_AGENT',
                'PROPERTY_AGENT_USER',
                'PROPERTY_REWARD_PERCENT_TRANSPORTATION_OPERATOR_AH'
            ]
        );

        // Параметры по умолчанию
        $nCommissionTransport = rrsIblock::getConst('commission_transport');
        if(empty($nCommissionTransport)) {
            throw new Exception('Не удалось получить параметр "Комиссия партнера за транспортировку, %"');
        }

        $nRewardPercentTransportationOperatorAH = rrsIblock::getConst('REWARD_PERCENT_TRANSPORTATION_OPERATOR_AH');
        if(empty($nRewardPercentTransportationOperatorAH)) {
            throw new Exception('Не удалось получить параметр "Вознаграждение оператору АХ от вознаграждения организатора АП [%]"');
        }


        $arDealsId      = [];
        $arFarmerId     = [];
        $arTransportId  = [];
        $arPartnerId    = [];
        $arAgentId      = [];


        while ($arRow = $rs->Fetch()) {

            // Если не задано поле "Процент вознаграждения за транспортировк оператору АХ от вознаграждения организатора АП" берем дефолтное
            if(!empty($arRow['PROPERTY_REWARD_PERCENT_TRANSPORTATION_OPERATOR_AH_VALUE'])) {
                $nRewardPercentOperatorAH = $arRow['PROPERTY_REWARD_PERCENT_TRANSPORTATION_OPERATOR_AH_VALUE'];
            } else {
                $nRewardPercentOperatorAH = $nRewardPercentTransportationOperatorAH;
            }

            $arResult['DATA_REPORT'][$arRow['ID']] = [

                'ID'    => $arRow['ID'],
                'DTR'   => null,

                'TRANSPORT_ID'      => $arRow['PROPERTY_TRANSPORT_VALUE'],
                'TRANSPORT_NAME'    => null,

                'FARMER_ID'     => $arRow['PROPERTY_FARMER_VALUE'],
                'FARMER_NAME'   => null,

                'COST_DEALS'    => null,

                'PARTNER_ID'    => $arRow['PROPERTY_PARTNER_VALUE'],
                'PARTNER_NAME'  => null,

                'AGENT_ID'              => $arRow['PROPERTY_AGENT_USER_VALUE'],
                'AGENT_NAME'            => null,
                'AGENT_REWARD_PERCENT'  => $arRow['PROPERTY_REWARD_PERCENT_TRANSPORTATION_AGENT_VALUE'],
                'AGENT_PAYMENTS'        => null,

                'REWARD_PERCENT_OPERATOR_AH'    => $nRewardPercentOperatorAH,
                'PAYMENTS_OPERATOR_AH'          => null,
            ];

            $iFarmerId = intval($arRow['PROPERTY_FARMER_VALUE']);
            if(!empty($iFarmerId)) {
                $arFarmerId[$iFarmerId] = $iFarmerId;
            }

            $iTransportId = intval($arRow['PROPERTY_TRANSPORT_VALUE']);
            if(!empty($iTransportId)) {
                $arTransportId[$iTransportId] = $iTransportId;
            }

            $iPartnerId = intval($arRow['PROPERTY_PARTNER_VALUE']);
            if(!empty($iPartnerId)) {
                $arPartnerId[$iPartnerId] = $iPartnerId;
            }

            $iAgentId = intval($arRow['PROPERTY_AGENT_USER_VALUE']);
            if(!empty($iAgentId)) {
                $arAgentId[$iAgentId] = $iAgentId;
            }

            $arDealsId[] = $arRow['ID'];
        }


        $arResult['DOCUMENTS'] = $this->getDocumentByDealsId($arDealsId);

        // АП
        if(!empty($arFarmerId)) {
            $arFarmerId = array_values($arFarmerId);
            $arResult['FARMERS'] = $this->getFarmerById($arFarmerId);
        }

        // ТК
        if(!empty($arTransportId)) {
            $arTransportId = array_values($arTransportId);
            $arResult['TRANSPORTS'] = $this->getTransportsById($arTransportId);
        }

        // Ведомости исполнения
        $arResult['DATA_STATEMENTS'] = $this->getDataStatements($arDealsId);


        // Организаторы
        if(!empty($arPartnerId)) {
            $arPartnerId = array_values($arPartnerId);
            $arResult['PARTNERS'] = $this->getPartnerById($arPartnerId);
        }

        // Агенты АП
        if(!empty($arAgentId)) {
            $arResult['AGENT_AP'] = $this->getAgentAPById($arAgentId);
        }


        foreach ($arResult['DATA_REPORT'] as $iDealId => &$arItem) {

            $sDTR = null;
            if(!empty($arResult['DOCUMENTS'][$iDealId]['dtr'])) {
                $sDTR .= 'ДТР №' . $arResult['DOCUMENTS'][$iDealId]['dtr']['ID'];
                $sDTR .= ' от ' . DateTime::createFromFormat('d.m.Y H:i:s', $arResult['DOCUMENTS'][$iDealId]['dtr']['DATE_CREATE'])->format('d.m.Y');
            }
            $arItem['DTR'] = $sDTR;

            // Исполнитель
            $arItem['TRANSPORT_NAME'] = $arResult['TRANSPORTS'][$arItem['TRANSPORT_ID']]['TITLE'];

            // Заказчик
            $arItem['FARMER_NAME'] = $arResult['FARMERS'][$arItem['FARMER_ID']]['TITLE'];

            // Сумма сделки
            $arItem['COST_DEALS'] = $arResult['DATA_STATEMENTS'][$iDealId]['COST_DEALS'];

            // Организатор АП
            $arItem['PARTNER_NAME'] = $arResult['PARTNERS'][$arItem['PARTNER_ID']]['TITLE'];


            // Вознаграждение Организатору АП
            if(!empty($arItem['COST_DEALS'])) {
                $arItem['REMUNERATION_ORGANIZER_AP'] = ($arItem['COST_DEALS'] / 100) * $nCommissionTransport;
            }


            // Агент
            if(!empty($arItem['AGENT_ID'])) {

                $arItem['AGENT_NAME'] = $arResult['AGENT_AP'][$arItem['AGENT_ID']]['TITLE'];

                // Выплаты Агенту
                if(!empty($arItem['COST_DEALS']) && !empty($arItem['AGENT_REWARD_PERCENT'])) {
                    $arItem['AGENT_PAYMENTS'] = ($arItem['COST_DEALS'] / 100) * $arItem['AGENT_REWARD_PERCENT'];
                }
            }

            // Выплаты Оператору (АХ)
            if(!empty($arItem['REMUNERATION_ORGANIZER_AP'])) {
                $arItem['PAYMENTS_OPERATOR_AH'] = ($arItem['REMUNERATION_ORGANIZER_AP'] / 100) * $arItem['REWARD_PERCENT_OPERATOR_AH'];
            }


            // Формат чисел
            $arItem['COST_DEALS'] = $this->getNumberFormat($arItem['COST_DEALS']);
            $arItem['REMUNERATION_ORGANIZER_AP'] = $this->getNumberFormat($arItem['REMUNERATION_ORGANIZER_AP']);
            $arItem['AGENT_PAYMENTS'] = $this->getNumberFormat($arItem['AGENT_PAYMENTS']);
            $arItem['PAYMENTS_OPERATOR_AH'] = $this->getNumberFormat($arItem['PAYMENTS_OPERATOR_AH']);

        }
    }


} catch (Exception $e) {
    $arResult['ERROR_MSG'] = $e->getMessage();
}


$this->IncludeComponentTemplate();