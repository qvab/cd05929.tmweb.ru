<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$obElement = new CIBlockElement;

$statusList = rrsIblock::getPropListKey('client_request', 'ACTIVE');
foreach ($statusList as $item) {
    $arResult['Q'][$item['XML_ID']] = 0;
    $status[$item['ID']] = $item['XML_ID'];
}
$arResult['Q']['all'] = 0;

$res = $obElement->GetList(
    array('ID' => 'ASC'),
    array(
        'IBLOCK_ID' => rrsIblock::getIBlockId('client_request'),
        'ACTIVE' => 'Y',
        'PROPERTY_CLIENT' => $USER->GetID()
    ),
    false,
    false,
    array(
        'ID',
        'PROPERTY_ACTIVE',
    )
);

$arRequestId = [];
while ($ob = $res->Fetch()) {
    $arResult['Q']['all']++;
    $arResult['Q'][$status[$ob['PROPERTY_ACTIVE_ENUM_ID']]]++;
    $arRequestId[] = $ob['ID'];
}


$arCultureId    = [];
$arWarehouseId  = [];
$arRequestByCulture     = [];
$arRequestByWarehouse   = [];

if(!empty($arRequestId)) {

    // ИД складов, ИД культур
    $rs = $obElement->GetList(
        [],
        [
            'IBLOCK_ID'         => getIBlockID('client', 'client_request_cost'),
            'ACTIVE' => 'Y',
            'PROPERTY_REQUEST'  => $arRequestId,
        ],
        false,
        false,
        [
            'ID',
            'PROPERTY_CULTURE',
            'PROPERTY_WAREHOUSE',
            'PROPERTY_REQUEST',
        ]
    );

    while ($arRow = $rs->Fetch()) {

        if(!empty($arRow['PROPERTY_CULTURE_VALUE'])) {
            $arCultureId[$arRow['PROPERTY_CULTURE_VALUE']]          = $arRow['PROPERTY_CULTURE_VALUE'];
            $arRequestByCulture[$arRow['PROPERTY_CULTURE_VALUE']][] = $arRow['PROPERTY_REQUEST_VALUE'];
        }

        if(!empty($arRow['PROPERTY_WAREHOUSE_VALUE'])) {
            $arWarehouseId[$arRow['PROPERTY_WAREHOUSE_VALUE']]          = $arRow['PROPERTY_WAREHOUSE_VALUE'];
            $arRequestByWarehouse[$arRow['PROPERTY_WAREHOUSE_VALUE']][] = $arRow['PROPERTY_REQUEST_VALUE'];
        }
    }
}


// Список культур
$arResult['CULTURE_LIST'] = [];
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

// Список складов
$arResult['WAREHOUSE_LIST'] = [];
if(!empty($arWarehouseId)) {

    $re = $obElement->GetList(
        ['NAME' => 'ASC'],
        [
            'IBLOCK_ID' => getIBlockID('client', 'client_warehouse'),
            'ID'        => array_values($arWarehouseId),
        ],
        false,
        false,
        array('ID', 'NAME',)
    );

    while($arRow = $re->Fetch()) {
        $arResult['WAREHOUSE_LIST'][$arRow['ID']] = [
            'ID'    => $arRow['ID'],
            'NAME'  => $arRow['NAME'],
        ];
    }
}

// Флаг вывода формы фильтра
$arResult['SHOW_FORM'] = (!empty($arResult['CULTURE_LIST']) || !empty($arResult['WAREHOUSE_LIST']));


/**
 * Обработка
 */
if (!isset($_REQUEST['status']) || (!in_array($_REQUEST['status'], array('yes', 'no', 'all')))) {
    $_REQUEST['status'] = 'yes';
}

$filterName = $arParams['FILTER_NAME'];
$GLOBALS[$filterName] = array();
$GLOBALS[$filterName]['PROPERTY_CLIENT'] = $USER->GetID();
if (in_array($_REQUEST['status'], array('yes', 'no'))) {
    $GLOBALS[$filterName]['PROPERTY_ACTIVE'] = rrsIblock::getPropListKey('client_request', 'ACTIVE', $_REQUEST['status']);
}

// По складу
$_GET['warehouse_id'] = trim($_GET['warehouse_id']);
if(!empty($_GET['warehouse_id'])) {
    $GLOBALS[$filterName]['ID'] = $arRequestByWarehouse[$_GET['warehouse_id']];
}

// По культуре
$_GET['culture_id'] = trim($_GET['culture_id']);
if(!empty($_GET['culture_id'])) {
    if(!empty($GLOBALS[$filterName]['ID'])) {
        $GLOBALS[$filterName]['ID'] = array_intersect($GLOBALS[$filterName]['ID'], $arRequestByCulture[$_GET['culture_id']]);
        if(count($GLOBALS[$filterName]['ID']) == 0){
            $GLOBALS[$filterName]['ID'] = 0;
        }
    } else {
        $GLOBALS[$filterName]['ID'] = $arRequestByCulture[$_GET['culture_id']];
    }
}