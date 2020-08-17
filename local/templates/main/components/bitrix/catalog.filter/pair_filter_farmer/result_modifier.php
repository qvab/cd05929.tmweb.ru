<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$obElement  = new CIBlockElement;
$obUser     = new CUser;
$obGroup    = new CGroup;

if($arParams['PROFILE'] == 'Y'){
    $iUserId = $_GET['uid'];
}else{
    $iUserId = $obUser->GetID();
}


$statusList = rrsIblock::getPropListKey('deals_deals', 'STATUS');
foreach ($statusList as $item) {
    $arResult['Q'][$item['XML_ID']] = 0;
    $status[$item['ID']] = $item['XML_ID'];
}
$arResult['Q']['all'] = 0;

// Группы
$rsGroup = $obGroup->GetList(
    $by = "c_sort",
    $order = "asc",
    ['STRING_ID' => 'client|farmer|partner|trade_companies']
);

$arGroup = [];
while ($arRow = $rsGroup->Fetch()) {
    $arGroup[$arRow['STRING_ID']] = $arRow;
}

// Группы пользователя
$arGroupUser = $obUser->GetUserGroup($iUserId);

// Роли
$arResult['IS_CLIENT']          = false;
$arResult['IS_FARMER']          = false;
$arResult['IS_PARTNER']         = false;
$arResult['IS_TRADE_COMPANIES'] = false;

if(in_array($arGroup['client']['ID'], $arGroupUser)) {
    $arResult['IS_CLIENT'] = true;
} elseif (in_array($arGroup['farmer']['ID'], $arGroupUser)) {
    $arResult['IS_FARMER'] = true;
} elseif (in_array($arGroup['partner']['ID'], $arGroupUser)) {
    $arResult['IS_PARTNER'] = true;
} elseif (in_array($arGroup['trade_companies']['ID'], $arGroupUser)) {
    $arResult['IS_TRADE_COMPANIES'] = true;
}

$rs = $obElement->GetList(
    ['ID' => 'ASC'],
    [
        'IBLOCK_ID' => rrsIblock::getIBlockId('deals_deals'),
        'ACTIVE' => 'Y',
        'PROPERTY_'.$arParams['USER_TYPE'] => $iUserId,
    ],
    false,
    false,
    [
        'ID',
        'PROPERTY_STATUS',
        'PROPERTY_CULTURE',
        'PROPERTY_CLIENT_WAREHOUSE',
        'PROPERTY_FARMER_WAREHOUSE',
        'PROPERTY_CLIENT',
        'PROPERTY_FARMER',
    ]
);

$arCultureId = [];
$arClientWarehouseId = [];
$arFarmerWarehouseId = [];

$arDealsByCulture = [];
$arDealsByClientWarehouse = [];
$arDealsByFarmerWarehouse = [];

$arClientId = [];
$arFarmerId = [];
$arDealsByClient = [];
$arDealsByFarmer = [];

while ($arRow = $rs->Fetch()) {

    if(!empty($arRow['PROPERTY_CULTURE_VALUE'])) {
        $arCultureId[$arRow['PROPERTY_CULTURE_VALUE']] = $arRow['PROPERTY_CULTURE_VALUE'];
        $arDealsByCulture[$arRow['PROPERTY_CULTURE_VALUE']][] = $arRow['ID'];
    }

    if(!empty($arRow['PROPERTY_CLIENT_WAREHOUSE_VALUE'])) {
        $arClientWarehouseId[$arRow['PROPERTY_CLIENT_WAREHOUSE_VALUE']] = $arRow['PROPERTY_CLIENT_WAREHOUSE_VALUE'];
        $arDealsByClientWarehouse[$arRow['PROPERTY_CLIENT_WAREHOUSE_VALUE']][] = $arRow['ID'];
    }

    if(!empty($arRow['PROPERTY_FARMER_WAREHOUSE_VALUE'])) {
        $arFarmerWarehouseId[$arRow['PROPERTY_FARMER_WAREHOUSE_VALUE']] = $arRow['PROPERTY_FARMER_WAREHOUSE_VALUE'];
        $arDealsByFarmerWarehouse[$arRow['PROPERTY_FARMER_WAREHOUSE_VALUE']][] = $arRow['ID'];
    }

    if(!empty($arRow['PROPERTY_CLIENT_VALUE'])) {
        $arClientId[$arRow['PROPERTY_CLIENT_VALUE']] = $arRow['PROPERTY_CLIENT_VALUE'];
        $arDealsByClient[$arRow['PROPERTY_CLIENT_VALUE']][] = $arRow['ID'];
    }

    if(!empty($arRow['PROPERTY_FARMER_VALUE'])) {
        $arFarmerId[$arRow['PROPERTY_FARMER_VALUE']] = $arRow['PROPERTY_FARMER_VALUE'];
        $arDealsByFarmer[$arRow['PROPERTY_FARMER_VALUE']][] = $arRow['ID'];
    }

    $arResult['Q']['all']++;
    $arResult['Q'][$status[$arRow['PROPERTY_STATUS_ENUM_ID']]]++;
}


/**
 * Культура
 */
$arResult['CULTURE_LIST'] = [];
if(!empty($arCultureId)) {

    $rs = $obElement->GetList(
        ['NAME' => 'ASC'],
        [
            'IBLOCK_ID' => getIBlockID('directories', 'cultures'),
            'ID'        => array_values($arCultureId),
        ],
        false,
        false,
        ['ID', 'NAME',]
    );

    while($arRow = $rs->Fetch()) {
        $arResult['CULTURE_LIST'][$arRow['ID']] = [
            'ID'    => $arRow['ID'],
            'NAME'  => $arRow['NAME'],
        ];
    }
}

/**
 * Склады покупателя
 */
$arResult['CLIENT_WAREHOUSE_LIST'] = [];
if($arResult['IS_CLIENT'] && !empty($arClientWarehouseId)) {

    $rs = $obElement->GetList(
        ['NAME' => 'ASC'],
        [
            'IBLOCK_ID' => getIBlockID('client', 'client_warehouse'),
            'ID'        => array_values($arClientWarehouseId),
        ],
        false,
        false,
        ['ID', 'NAME',]
    );

    while($arRow = $rs->Fetch()) {
        $arResult['CLIENT_WAREHOUSE_LIST'][$arRow['ID']] = [
            'ID'    => $arRow['ID'],
            'NAME'  => $arRow['NAME'],
        ];
    }
}

/**
 * Склады АП
 */
$arResult['FARMER_WAREHOUSE_LIST'] = [];
if($arResult['IS_FARMER'] && !empty($arFarmerWarehouseId)) {

    $rs = $obElement->GetList(
        ['NAME' => 'ASC'],
        [
            'IBLOCK_ID' => getIBlockID('farmer', 'farmer_warehouse'),
            'ID'        => array_values($arFarmerWarehouseId),
        ],
        false,
        false,
        ['ID', 'NAME',]
    );

    while($arRow = $rs->Fetch()) {
        $arResult['FARMER_WAREHOUSE_LIST'][$arRow['ID']] = [
            'ID'    => $arRow['ID'],
            'NAME'  => $arRow['NAME'],
        ];
    }
}

/**
 * Список поупателей
 */
$arResult['CLIENT_LIST'] = [];
if($arResult['IS_PARTNER'] && $arClientId) {
    foreach ($arClientId as $iClientId) {
        $arResult['CLIENT_LIST'][$iClientId] = client::getProfile($iClientId);
    }
}

/**
 * Список АП
 */
$arResult['FARMER_LIST'] = [];
if($arResult['IS_PARTNER'] && $arFarmerId) {
    foreach ($arFarmerId as $iFarmerId) {
        $arResult['FARMER_LIST'][$iFarmerId] = farmer::getProfile($iFarmerId);
    }
}

$arResult['DISTANCE_LIST'] = [];
if($arResult['IS_TRADE_COMPANIES']) {

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
}

$arResult['SHOW_FORM'] = (
    !empty($arResult['CULTURE_LIST'])           ||
    !empty($arResult['CLIENT_WAREHOUSE_LIST'])  ||
    !empty($arResult['FARMER_WAREHOUSE_LIST'])  ||
    !empty($arResult['CLIENT_LIST'])            ||
    !empty($arResult['FARMER_LIST'])            ||
    !empty($arResult['DISTANCE_LIST'])
);


/**
 * Обработка
 */
if (!isset($_REQUEST['status']) || (!in_array($_REQUEST['status'], array('new')))) {
    $_REQUEST['status'] = 'new';
}

$filterName = $arParams['FILTER_NAME'];
$GLOBALS[$filterName] = array();
$GLOBALS[$filterName]['PROPERTY_'.$arParams['USER_TYPE']] = $iUserId;
if (in_array($_REQUEST['status'], array('new'))) {
    $GLOBALS[$filterName]['PROPERTY_PAIR_STATUS'] = rrsIblock::getPropListKey('deals_deals', 'PAIR_STATUS', $_REQUEST['status']);
}
/*
// По культуре
//$_GET['culture_id'] = intval($_GET['culture_id']);
if(!empty($_GET['culture_id'])) {
    $GLOBALS[$filterName]['ID'] = $arDealsByCulture[$_GET['culture_id']];
}

// По складу покупателя
//$_GET['client_warehouse_id'] = intval($_GET['client_warehouse_id']);
if(!empty($_GET['client_warehouse_id'])) {
    if(!empty($GLOBALS[$filterName]['ID'])) {
        $GLOBALS[$filterName]['ID'] = array_intersect($GLOBALS[$filterName]['ID'], $arDealsByClientWarehouse[$_GET['client_warehouse_id']]);
    } else {
        $GLOBALS[$filterName]['ID'] = $arDealsByClientWarehouse[$_GET['client_warehouse_id']];
    }
}

// По складу АП
//$_GET['farmer_warehouse_id'] = intval($_GET['farmer_warehouse_id']);
if(!empty($_GET['farmer_warehouse_id'])) {
    if(!empty($GLOBALS[$filterName]['ID'])) {
        $GLOBALS[$filterName]['ID'] = array_intersect($GLOBALS[$filterName]['ID'], $arDealsByFarmerWarehouse[$_GET['farmer_warehouse_id']]);
    } else {
        $GLOBALS[$filterName]['ID'] = $arDealsByFarmerWarehouse[$_GET['farmer_warehouse_id']];
    }
}
*/

// По культуре
if(!empty($_GET['culture_id'])) {
    $GLOBALS[$filterName]['PROPERTY_CULTURE'] = $_GET['culture_id'];
}

// По складу покупателя
if(!empty($_GET['client_warehouse_id'])) {
    $GLOBALS[$filterName]['PROPERTY_CLIENT_WAREHOUSE'] = $_GET['client_warehouse_id'];
}

// По складу АП
if(!empty($_GET['farmer_warehouse_id'])) {
    $GLOBALS[$filterName]['PROPERTY_FARMER_WAREHOUSE'] = $_GET['farmer_warehouse_id'];
}

// По покупателю
//  $_GET['client_id'] = intval($_GET['client_id']);
if(!empty($_GET['client_id'])) {
    if(!empty($GLOBALS[$filterName]['ID'])) {
        $GLOBALS[$filterName]['ID'] = array_intersect($GLOBALS[$filterName]['ID'], $arDealsByClient[$_GET['client_id']]);
    } else {
        $GLOBALS[$filterName]['ID'] = $arDealsByClient[$_GET['client_id']];
    }
}

// По АП
//$_GET['farmer_id'] = intval($_GET['farmer_id']);
if(!empty($_GET['farmer_id'])) {
    if(!empty($GLOBALS[$filterName]['ID'])) {
        $GLOBALS[$filterName]['ID'] = array_intersect($GLOBALS[$filterName]['ID'], $arDealsByFarmer[$_GET['farmer_id']]);
    } else {
        $GLOBALS[$filterName]['ID'] = $arDealsByFarmer[$_GET['farmer_id']];
    }
}

// По расстоянию
//$_GET['distance_id'] = intval($_GET['distance_id']);
if(!empty($_GET['distance_id'])) {

    $iDistanceMin = $arResult['DISTANCE_LIST'][$_GET['distance_id']]['DISTANCE_MIN'];
    if(!empty($iDistanceMin)) {
        $GLOBALS[$filterName]['>PROPERTY_ROUTE'] = $iDistanceMin;
    }

    $iDistanceMax = $arResult['DISTANCE_LIST'][$_GET['distance_id']]['DISTANCE_MAX'];
    if(!empty($iDistanceMax)) {
        $GLOBALS[$filterName]['<=PROPERTY_ROUTE'] = $iDistanceMax;
    }
}

//редирект на страницу пагинации на которой находится пара с id в GET
if(isset($_GET['id'])) {
    $arFieldsP = $GLOBALS['arrFilter'];
    $arFieldsP['IBLOCK_ID'] = rrsIblock::getIBlockId('deals_deals');
    $arFieldsP['ACTIVE'] = 'Y';
    CModule::IncludeModule('iblock');
    global $USER;
    $el_obj = new CIBlockElement;
    $res = $el_obj->GetList(
        array('DATE_CREATE' => 'DESC', 'PROPERTY_PAIR_STATUS' => 'ASC'),
        $arFieldsP,
        false,
        array('nElementID' => $_GET['id']),
        array('ID')
    );
    $page = 0;
    if ($res->SelectedRowsCount() > 0) {
        $item_search = false;
        if ($data = $res->Fetch()) {
            if(isset($data['RANK'])){
                if($data['RANK']>0){
                    $page =  ceil($data['RANK'] / 5);
                    $currentPage = 1;
                    if(isset($_GET['PAGEN_1'])){
                        $currentPage = $_GET['PAGEN_1'];
                    }
                    if($page>0){
                        if($currentPage != $page){
                            if($arParams['PROFILE'] == 'Y'){
                                $url = '/profile/pair/?uid='.$_GET['uid'].'&id='.$_GET['id'];
                                if($page>1) {
                                    $url .= '&PAGEN_1=' . $page;
                                }
                            }else{
                                $url = '/farmer/pair/?id='.$_GET['id'];
                                if($page>1) {
                                    $url .= '&PAGEN_1=' . $page;
                                }
                            }
                            LocalRedirect($url);
                        }
                    }
                }

            }
        }
    }
}