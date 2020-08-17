<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/**
 * Заглушки
 */
$arResult = [
    'STAGE_ID'                  => null,
    'ITEMS'                     => [],
    'CULTURE_LIST'              => [],
    'DISTANCE_LIST'             => [],
    'LINKED_PARTNERS'           => [],
    'ROUTE_MAP'                 => [],
    'MIN_ROUTE'                 => [],
    'SHOW_FORM'                 => false,
    'DEALS_BY_CULTURE'          => [],
    'DEALS_BY_CLIENT_WAREHOUSE' => [],
    'DEALS_BY_FARMER_WAREHOUSE' => [],
    'ERROR_MSG'                 => null,
];

if(empty($arParams['FILTER_NAME'])) {
    $arParams['FILTER_NAME'] = 'arrFilter';
}

if(empty($arParams['CACHE_TIME'])) {
    $arParams['CACHE_TIME'] = 60*60;
}

if(empty($arParams['CACHE_TYPE'])) {
    $arParams['CACHE_TYPE'] = 'N';
}

if(empty($arParams['USER_ID'])) {
    $obUser = new CUser;
    $arParams['USER_ID'] = $obUser->GetID();
}

/**
 * Переменная фильтра
 */
$sFilterName = $arParams['FILTER_NAME'];
$GLOBALS[$sFilterName] = [
    '>PROPERTY_TARIF' => 0,
    'PROPERTY_TRANSPORT' => false,
];

$sCacheId = md5(serialize($arParams));
$sCachePath = '/'.SITE_ID.'/'.$sCacheId;

$obCache = new CPHPCache();

try {

    if(($arParams['CACHE_TYPE'] != 'N') && $obCache->InitCache($arParams['CACHE_TIME'], $sCacheId, $sCachePath)) {
        $vars = $obCache->GetVars();
        $arResult = $vars['arResult'];
        unset($vars);
    } elseif($obCache->StartDataCache()  ) {

        // Объекты
        $obElement  = new CIBlockElement;

        // ИД этапа "Поиск перевозчика"
        $arResult['STAGE_ID'] = rrsIblock::getPropListKey('deals_deals', 'STAGE', 'search');
        if(empty($arResult['STAGE_ID'])) {
            throw new Exception('Не удалось получить ИД этапа "Поиск перевозчика"');
        }

        if(empty($arParams['USER_ID'])) {
            throw new Exception('Не удалось получить ИД пользователя');
        }

        // Автопарк ТК
        $arResult['CAR_PARK'] = transport::getAutoparkList($arParams['USER_ID']);
        if(empty($arResult['CAR_PARK'])) {
            throw new Exception('Список автопарков не заполнен');
        }


        $rs = $obElement->GetList(
            ['ID' => 'ASC'],
            [
                'IBLOCK_ID'             => rrsIblock::getIBlockId('deals_deals'),
                'PROPERTY_STAGE'        => $arResult['STAGE_ID'],
                '>PROPERTY_TARIF'       => 0,
                'PROPERTY_TRANSPORT'    => false,
            ],
            false,
            false,
            [
                'ID',
                'PROPERTY_CULTURE',
                'PROPERTY_PARTNER',
                'PROPERTY_CLIENT_WAREHOUSE',
                'PROPERTY_FARMER_WAREHOUSE',
                'PROPERTY_FARMER_WAREHOUSE',
            ]
        );


        $arPartnersId = [];
        $arClientWarehouseId = [];
        $arFarmerWarehouseId = [];
        while ($arRow = $rs->Fetch()) {

            $arResult['ITEMS'][$arRow['ID']] = $arRow;

            $iClientWarehouseId = intval($arRow['PROPERTY_CLIENT_WAREHOUSE_VALUE']);
            if(!empty($iClientWarehouseId)) {
                $arClientWarehouseId[$iClientWarehouseId] = $iClientWarehouseId;
            }


            $iFarmerWarehouseId = intval($arRow['PROPERTY_FARMER_WAREHOUSE_VALUE']);
            if(!empty($iFarmerWarehouseId)) {
                $arFarmerWarehouseId[$iFarmerWarehouseId] = $iFarmerWarehouseId;
            }


            $iPartnerId = intval($arRow['PROPERTY_PARTNER_VALUE']);
            if(!empty($iPartnerId)) {
                $arPartnersId[$iPartnerId] = $iPartnerId;
            }
        }


        if(!empty($iPartnerId)) {

            $rs = $obElement->GetList(
                ['ID' => 'ASC'],
                [
                    'IBLOCK_ID'             => rrsIblock::getIBlockId('transport_partner_link'),
                    'ACTIVE'                => 'Y',
                    'PROPERTY_PARTNER_ID'   => array_values($iPartnerId),
                    'PROPERTY_VERIFIED'     => rrsIblock::getPropListKey('transport_partner_link', 'VERIFIED', 'yes'),
                    'PROPERTY_USER_ID'      => $arParams['USER_ID'],
                ],
                false,
                false,
                [
                    'ID',
                    'PROPERTY_PARTNER_ID',
                ]
            );

            while ($arRow = $rs->Fetch()) {
                $arResult['LINKED_PARTNERS'][$arRow['PROPERTY_PARTNER_ID_VALUE']] = $arRow['PROPERTY_PARTNER_ID_VALUE'];
            }
        }


        // Параметры складов АП
        $arFarmerWarehouseIdParams  = [];
        if(!empty($arFarmerWarehouseId)) {
            $arFarmerWarehouseIdParams = farmer::getWarehouseParamsList(array_values($arFarmerWarehouseId));
        }

        foreach ($arFarmerWarehouseIdParams as $arWarehouse) {
            foreach ($arResult['CAR_PARK'] as $arCarPark) {

                $fRoute = rrsIblock::getRoute($arCarPark['MAP'], $arWarehouse['MAP']);
                if(empty($fRoute)) {
                    throw new Exception('Не удалось получить расстояние между точками');
                }
                $arResult['ROUTE_MAP'][$arWarehouse['ID']][$arCarPark['ID']] = $fRoute;
            }
        }

        foreach ($arResult['ROUTE_MAP'] as $iWarehouseId => $arWarehouse) {

            $min = 10000;
            foreach ($arWarehouse as $key => $distance) {
                if ($distance < $min) {
                    $arResult['MIN_ROUTE'][$iWarehouseId] = array('AP_ID' => $key, 'ROUTE' => $distance);
                    $min = $distance;
                }
            }
        }

        $limit = rrsIblock::getConst('limit_transport');


        $arCultureId = [];
        $arClientWarehouseId = [];
        $arFarmerWarehouseId = [];
        foreach ($arResult['ITEMS'] as $iItemId => $arItem) {

            $iFarmerWarehouseId = intval($arItem['PROPERTY_FARMER_WAREHOUSE_VALUE']);
            $iPartnerId         = intval($arItem['PROPERTY_PARTNER_VALUE']);

            if(empty($arResult['LINKED_PARTNERS'][$iPartnerId]) || $arResult['MIN_ROUTE'][$iFarmerWarehouseId]['ROUTE'] > $limit) {
                unset($arResult['ITEMS'][$iItemId]);
                continue;
            }

            $iCultureId = intval($arItem['PROPERTY_CULTURE_VALUE']);
            if(!empty($iCultureId)) {
                $arCultureId[$iCultureId] = $iCultureId;
                $arResult['DEALS_BY_CULTURE'][$iCultureId][] = $arItem['ID'];
            }
        }


        // Культура
        if(!empty($arCultureId)) {

            $re = $obElement->GetList(
                ['NAME' => 'ASC'],
                [
                    'IBLOCK_ID' => getIBlockID('directories', 'cultures'),
                    'ID'        => array_values($arCultureId),
                ],
                false,
                false,
                array('ID', 'NAME',)
            );

            while($arRow = $re->Fetch()) {
                $arResult['CULTURE_LIST'][$arRow['ID']] = [
                    'ID'    => $arRow['ID'],
                    'NAME'  => $arRow['NAME'],
                ];
            }
        }

        // Расстояния
        $rs = $obElement->GetList(
            ['SORT' => 'ASC'],
            [
                'IBLOCK_ID' => getIBlockID('directories', 'distance'),
                'ACTIVE'    => 'Y',
            ],
            false,
            false,
            [
                'ID',
                'NAME',
                'PROPERTY_MIN',
                'PROPERTY_MAX',
            ]
        );

        while($arRow = $rs->Fetch()) {
            $arResult['DISTANCE_LIST'][$arRow['ID']] = [
                'ID'    => $arRow['ID'],
                'NAME'  => $arRow['NAME'],
                'DISTANCE_MIN' => intval($arRow['PROPERTY_MIN_VALUE']),
                'DISTANCE_MAX' => intval($arRow['PROPERTY_MAX_VALUE']),
            ];
        }


        $arResult['SHOW_FORM'] = (!empty($arResult['CULTURE_LIST']) || !empty($arResult['DISTANCE_LIST']));

        $obCache->EndDataCache(array('arResult' => $arResult));
    }
} catch (Exception $e) {
    $obCache->Clean($sCacheId, $sCachePath);
    $arResult['ERROR_MSG'] = 'Ошибка фильтра! ' . $e->getMessage();
}


/**
 * Обработка фильтра
 */

$GLOBALS[$sFilterName]['PROPERTY_STAGE'] = $arResult['STAGE_ID'];


// По культуре
$_GET['culture_id'] = trim($_GET['culture_id']);
if(!empty($_GET['culture_id'])) {
    $GLOBALS[$sFilterName]['ID'] = $arResult['DEALS_BY_CULTURE'][$_GET['culture_id']];
}

// По расстоянию
$_GET['distance_id'] = intval($_GET['distance_id']);
if(!empty($_GET['distance_id'])) {

    $iDistanceMin = $arResult['DISTANCE_LIST'][$_GET['distance_id']]['DISTANCE_MIN'];
    if(!empty($iDistanceMin)) {
        $GLOBALS[$sFilterName]['>PROPERTY_ROUTE'] = $iDistanceMin;
    }

    $iDistanceMax = $arResult['DISTANCE_LIST'][$_GET['distance_id']]['DISTANCE_MAX'];
    if(!empty($iDistanceMax)) {
        $GLOBALS[$sFilterName]['<=PROPERTY_ROUTE'] = $iDistanceMax;
    }
}