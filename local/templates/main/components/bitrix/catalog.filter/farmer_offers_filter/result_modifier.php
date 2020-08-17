<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
$statusList = rrsIblock::getPropListKey('farmer_offer', 'ACTIVE');
foreach ($statusList as $item) {
    $arResult['Q'][$item['XML_ID']] = 0;
    $status[$item['ID']] = $item['XML_ID'];
}
$arResult['Q']['all'] = 0;
$res = CIBlockElement::GetList(
    array('ID' => 'ASC'),
    array(
        'IBLOCK_ID' => rrsIblock::getIBlockId('farmer_offer'),
        'ACTIVE' => 'Y',
        'PROPERTY_FARMER' => $USER->GetID()
    ),
    false,
    false,
    array(
        'ID',
        'PROPERTY_ACTIVE',
        'PROPERTY_CULTURE',
        'PROPERTY_WAREHOUSE',
    )
);

$arCultureId = [];
$arWarehouseId = [];

$arOfferByCulture = [];
$arOfferByWarehouse = [];
while ($ob = $res->Fetch()) {
    $arResult['Q']['all']++;
    $arResult['Q'][$status[$ob['PROPERTY_ACTIVE_ENUM_ID']]]++;

    if(!empty($ob['PROPERTY_CULTURE_VALUE'])) {
        $arCultureId[$ob['PROPERTY_CULTURE_VALUE']] = $ob['PROPERTY_CULTURE_VALUE'];
        $arOfferByCulture[$ob['PROPERTY_CULTURE_VALUE']][] = $ob['ID'];
    }

    if(!empty($ob['PROPERTY_WAREHOUSE_VALUE'])) {
        $arWarehouseId[$ob['PROPERTY_WAREHOUSE_VALUE']] = $ob['PROPERTY_WAREHOUSE_VALUE'];
        $arOfferByWarehouse[$ob['PROPERTY_WAREHOUSE_VALUE']][] = $ob['ID'];
    }
}



// Список культур
$arResult['CULTURE_LIST'] = [];
if(!empty($arCultureId)) {

    $re = CIBlockElement::GetList(
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

    $re = CIBlockElement::GetList(
        ['NAME' => 'ASC'],
        [
            'IBLOCK_ID' => getIBlockID('farmer', 'farmer_warehouse'),
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
$GLOBALS[$filterName]['PROPERTY_FARMER'] = $USER->GetID();
if (in_array($_REQUEST['status'], array('yes', 'no'))) {
    $GLOBALS[$filterName]['PROPERTY_ACTIVE'] = rrsIblock::getPropListKey('farmer_offer', 'ACTIVE', $_REQUEST['status']);
}

// По складу
if(isset($_GET['warehouse_id'])) {
    $_GET['warehouse_id'] = trim($_GET['warehouse_id']);
    if (!empty($_GET['warehouse_id'])) {
        $GLOBALS[$filterName]['PROPERTY_WAREHOUSE'] = $_GET['warehouse_id'];
        //$GLOBALS[$filterName]['ID'] = $arOfferByWarehouse[$_GET['warehouse_id']];
    }
}

// По культуре
if(isset($_GET['culture_id'])){
    $_GET['culture_id'] = trim($_GET['culture_id']);
    if(!empty($_GET['culture_id'])) {
        $GLOBALS[$filterName]['PROPERTY_CULTURE'] = $_GET['culture_id'];
        /*if(!empty($GLOBALS[$filterName]['ID'])) {
            $GLOBALS[$filterName]['ID'] = array_intersect($GLOBALS[$filterName]['ID'], $arOfferByCulture[$_GET['culture_id']]);
        } else {
            $GLOBALS[$filterName]['ID'] = $arOfferByCulture[$_GET['culture_id']];
        }*/
    }
}
