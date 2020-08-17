<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
/**
 * Компонент для вывода списка дел у агента поставщика
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
/** @var CFreightTariffUpdate $this */


/**
 * Результирующий массив
 */
$arResult = [
    'USED_ID'       => null,
    'DATA_FILTER'   => [],
    'TYPES_AFFAIR'  => [],
    'DATA_AFFAIR'   => [],
    'DATE_FROM'     => null,
    'DATE_TO'       => null,
    'ERROR_MSG'     => null,
    'FILTER_USED'   => false,
];


/**
 * Параметры
 */
// Вывод фильтра по дате от
if(!empty($arParams['FILTER_FIELDS']['DATE_FROM']) && $arParams['FILTER_FIELDS']['DATE_FROM'] == 'Y') {
    $arParams['FILTER_FIELDS']['DATE_FROM'] = true;
} else {
    $arParams['FILTER_FIELDS']['DATE_FROM'] = false;
}

// Вывод фильтра по дате по
if(!empty($arParams['FILTER_FIELDS']['DATE_TO']) && $arParams['FILTER_FIELDS']['DATE_TO'] == 'Y') {
    $arParams['FILTER_FIELDS']['DATE_TO'] = true;
} else {
    $arParams['FILTER_FIELDS']['DATE_TO'] = false;
}

// Вывод фильтра по типу сущности
if(!empty($arParams['FILTER_FIELDS']['TYPE']) && $arParams['FILTER_FIELDS']['TYPE'] == 'Y') {
    $arParams['FILTER_FIELDS']['TYPE'] = true;
} else {
    $arParams['FILTER_FIELDS']['TYPE'] = false;
}

// Вывод фильтра по поставщику
if(!empty($arParams['FILTER_FIELDS']['FARMER']) && $arParams['FILTER_FIELDS']['FARMER'] == 'Y') {
    $arParams['FILTER_FIELDS']['FARMER'] = true;
} else {
    $arParams['FILTER_FIELDS']['FARMER'] = false;
}

// Вывод фильтра по покупателю
if(!empty($arParams['FILTER_FIELDS']['CLIENT']) && $arParams['FILTER_FIELDS']['CLIENT'] == 'Y') {
    $arParams['FILTER_FIELDS']['CLIENT'] = true;
} else {
    $arParams['FILTER_FIELDS']['CLIENT'] = false;
}

// Если задан АП не выводим фильтр по АП
$arParams['FARMER_ID'] = intval($arParams['FARMER_ID']);
if(!empty($arParams['FARMER_ID'])) {
    $arParams['FILTER_FIELDS']['FARMER'] = false;
}

// Выводить в описании Поставщика
if(!empty($arParams['SHOW_DESCRIPTION_FARMER']) && $arParams['SHOW_DESCRIPTION_FARMER'] == 'Y') {
    $arParams['SHOW_DESCRIPTION_FARMER'] = true;
} else {
    $arParams['SHOW_DESCRIPTION_FARMER'] = false;
}


/**
 * Обработка
 */
try {

    /**
     * Объекты
     */
    $obUser = new CUser;
    $obEl   = new CIBlockElement;

    // ИД Агента
    $arResult['USED_ID'] = $obUser->GetID();

    if(!agent::checkIsAgent($arResult['USED_ID'])) {
        throw new Exception('Текущий пользователь USER_ID['.$arResult['USED_ID'].'] не является агентом поставщика');
    }

    /**
     *  Данные для фильтра
     */

    // Типы дел
    $arTypeEnum = CAffair::GetTypes();
    $arResult['TYPES_AFFAIR']           =& $arTypeEnum['ITEMS'];
    $arResult['DATA_FILTER']['TYPE']    =& $arTypeEnum['BY_XML_ID'];

    // Участники (Поставщики/Покупатели)
    $arFilterType = [];
    foreach ($arTypeEnum['BY_XML_ID'] as $sCode => $arItem) {
        $arFilterType[] = $sCode;
    }

    // Данные для фильтра из "Дел"
    if($arParams['FILTER_FIELDS']['FARMER'] || $arParams['FILTER_FIELDS']['CLIENT'] || $arParams['SHOW_DESCRIPTION_FARMER']) {

        $arAffair = CAffair::GetList(
            $arFilterType,
            ['ID' => 'ASC'],
            ['UF_USER_AGENT' => $arResult['USED_ID']],
            [
                'ID',
                'UF_TYPE_AFFAIR',
                'UF_USER_PARTICIPANT',
            ]
        );

        $arFarmerId = []; // АП
        $arClientId = []; // Покупатели
        foreach ($arAffair['ITEMS'] as $arItem) {
            $sCodeType = $arTypeEnum['ITEMS'][$arItem['UF_TYPE_AFFAIR']]['XML_ID'];
            if($sCodeType == 'OFFER') {
                $arFarmerId[$arItem['UF_USER_PARTICIPANT']] = $arItem['UF_USER_PARTICIPANT'];
            } elseif ($sCodeType == 'REQUEST') {
                $arClientId[$arItem['UF_USER_PARTICIPANT']] = $arItem['UF_USER_PARTICIPANT'];
            }
        }

        /*if(!empty($arClientId)) {
            // Покупателей пока не делаем. В будущем планируется сделать дела по запросам.
        }*/

        // Поставщики
        if(!empty($arFarmerId)) {

            $arFarmerId = array_values($arFarmerId);

            // Поля пользователя
            $rs = $obUser->GetList(
                ($by='ID'),
                ($order='ASC'),
                [
                    'ID'=>implode(' | ', $arFarmerId),
                ],
                [
                    'FIELDS' => ['ID', 'NAME', 'LAST_NAME', 'SECOND_NAME' ,'EMAIL', 'LOGIN'],
                ]
            );

            $arFarmer = [];
            while ($arRow = $rs->Fetch()) {

                $bBracket   = false;
                $arUserItem = [];

                if(!empty($arRow['LAST_NAME'])) {
                    $arUserItem[]   = $arRow['LAST_NAME'];
                    $bBracket       = true;
                }

                if(!empty($arRow['NAME'])) {
                    $arUserItem[]   = $arRow['NAME'];
                    $bBracket       = true;
                }

                if(!empty($arRow['SECOND_NAME'])) {
                    $arUserItem[]   = $arRow['SECOND_NAME'];
                    $bBracket       = true;
                }

                if(!empty($arRow['EMAIL'])) {
                    if($bBracket)
                        $arRow['EMAIL'] = '(' . $arRow['EMAIL'] . ')';

                    $arUserItem[] = $arRow['EMAIL'];
                }

                $arRow['TITLE'] = implode(' ', $arUserItem);

                if(empty($arRow['TITLE'])) {
                    $arRow['TITLE'] = $arRow['LOGIN'];
                }

                $arFarmer[$arRow['ID']] = $arRow;
            }





            // Данные из ИБ "Поставщик"->"Привязка к агентам"
            $rs = $obEl->GetList(
                [],
                [
                    'ACTIVE'            => 'Y',
                    'IBLOCK_ID'         => getIBlockID('farmer', 'farmer_agent_link'),
                    'PROPERTY_AGENT_ID' => $arResult['USED_ID'],
                    'PROPERTY_USER_ID'  => $arFarmerId,
                ],
                false,
                false,
                ['PROPERTY_USER_ID', 'PROPERTY_FARMER_NICKNAME',]
            );

            $arDataFarmerAgentLink = [];
            while ($arRow = $rs->Fetch()) {

                $arDataFarmerAgentLink[$arRow['PROPERTY_USER_ID_VALUE']] = [
                    'NICKNAME' => $arRow['PROPERTY_FARMER_NICKNAME_VALUE'],
                ];
            }

            $arULType = rrsIblock::getPropListKey('farmer_profile', 'UL_TYPE');

            // Данные из профили
            $rs = $obEl->GetList(
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
                    'PROPERTY_UL_TYPE',
                    'PROPERTY_FULL_COMPANY_NAME',
                    'PROPERTY_IP_FIO',
                    'PROPERTY_USER',
                ]
            );

            while ($arRow = $rs->Fetch()) {

                $sTitle = null;
                // Никнейм
                $sNickname = $arDataFarmerAgentLink[$arRow['PROPERTY_USER_VALUE']]['NICKNAME'];

                if($arRow['PROPERTY_UL_TYPE_ENUM_ID'] == $arULType['ul']['ID']) {
                    $sTitle .= trim($arRow['PROPERTY_FULL_COMPANY_NAME_VALUE']);
                } elseif ($arRow['PROPERTY_UL_TYPE_ENUM_ID'] == $arULType['ip']['ID']) {
                    $sTitle = trim($arRow['PROPERTY_IP_FIO_VALUE']);

                    if(!empty($sTitle))
                        $sTitle .= 'ИП ' . $sTitle;
                }

                if(!empty($sTitle) && !empty($sNickname)) {
                    $sTitle .= ' (' . $sNickname . ')';
                } elseif(empty($sTitle) && !empty($sNickname)) {
                    $sTitle .= $sNickname;
                } elseif(empty($sTitle)) {
                    $sTitle = $arFarmer[$arRow['PROPERTY_USER_VALUE']]['TITLE'];
                }
                
                $arResult['DATA_FILTER']['FARMER'][$arRow['PROPERTY_USER_VALUE']] = [
                    'USER_ID'   => $arRow['PROPERTY_USER_VALUE'],
                    'TITLE'     => $sTitle,
                ];
            }
        }
    }


    /**
     * Собираем массив фильтрации дел
     */

    // Текущий агент
    $arFilter = ['UF_USER_AGENT' => $arResult['USED_ID']];

    // Тип
    if($arParams['FILTER_FIELDS']['TYPE']) {

        $_GET['TYPE_ID'] = intval($_GET['TYPE_ID']);

        if(!empty($_GET['TYPE_ID'])) {

            $sCodeType = $arTypeEnum['ITEMS'][$_GET['TYPE_ID']]['XML_ID'];
            if(empty($sCodeType)) {
                throw new Exception('Не удалось получить код типа');
            }

            $arFilterType = [$sCodeType];
            $arResult['FILTER_USED'] = true;
        }
    }


    // АП
    if(!empty($arParams['FARMER_ID'])) {

        $arFilter['UF_USER_PARTICIPANT'] = $arParams['FARMER_ID'];

    } elseif ($arParams['FILTER_FIELDS']['FARMER']) {

        $_GET['FARMER'] = intval($_GET['FARMER']);

        if(!empty($_GET['FARMER'])) {
            $arFilter['UF_USER_PARTICIPANT'] = $_GET['FARMER'];
            $arResult['FILTER_USED'] = true;
        }
    }


    // Дата с
    if($arParams['FILTER_FIELDS']['DATE_FROM']) {
        // Дата от
        $arResult['DATE_FROM'] = trim($_GET['DATE_FROM']);

        // Дата начала по умолчанию
        if(empty($arResult['DATE_FROM'])) {
            $arResult['DATE_FROM'] = date('d.m.Y');
        } else {
            $arResult['FILTER_USED'] = true;
        }
    }


    // Дата по
    if($arParams['FILTER_FIELDS']['DATE_TO']) {
        $arResult['DATE_TO'] = trim($_GET['DATE_TO']);
    }

    // Проверка дат
    if(!empty($arResult['DATE_FROM']) && !empty($arResult['DATE_TO'])) {

        $date1Wrong = $date2Wrong = $date2Less = null;

        CheckFilterDates($arResult['DATE_FROM'], $arResult['DATE_TO'], $date1Wrong, $date2Wrong, $date2Less);

        if ($date1Wrong == "Y") {
            throw new Exception('Неверный формат "Дата действия от"');
        }

        if ($date2Wrong == "Y") {
            throw new Exception('Неверный формат "Дата действия по"');
        }

        // Первая дата больше второй
        if ($date2Less == "Y") {
            // Меняем даты местами
            list($arResult['DATE_TO'], $arResult['DATE_FROM']) = [$arResult['DATE_FROM'], $arResult['DATE_TO']];
        }
    }

    // Фильтр от
    if(!empty($arResult['DATE_FROM'])) {
        $arFilter['>=UF_DATE_AFFAIR'] = $arResult['DATE_FROM'];
    }

    // Фильтр по
    if(!empty($arResult['DATE_TO'])) {
        $arFilter['<=UF_DATE_AFFAIR'] = $arResult['DATE_TO'];
        $arResult['FILTER_USED'] = true;
    }

    // Сортировка
    $arOrder = ['UF_DATE_AFFAIR' => 'ASC'];

    /**
     * Выборка дел
     */
    $arResult['DATA_AFFAIR'] = CAffair::GetList(
        $arFilterType,
        $arOrder,
        $arFilter,
        [
            'ID',
            'UF_TYPE_AFFAIR',
            'UF_USER_PARTICIPANT',
            'UF_DATE_AFFAIR',
            'UF_FARMER_VOLUME',
            'UF_EXPECTED_PRICE',
            'UF_DATE_CREATE',
            'UF_COMMENT',
            'UF_XML_ID',
        ],
        null,
        null,
        false,
        true
    );
    $arOfferIDs = [];
    foreach ($arResult['DATA_AFFAIR']['ITEMS'] as $iID => $arAffair) {
        $arOfferIDs[] = $arAffair['UF_XML_ID'];
    }
    $arResult['OFFERS'] = farmer::getOfferListByIDs($arOfferIDs);

} catch (Exception $e) {
    $arResult['ERROR_MSG'] = $e->getMessage();
}

$this->IncludeComponentTemplate();