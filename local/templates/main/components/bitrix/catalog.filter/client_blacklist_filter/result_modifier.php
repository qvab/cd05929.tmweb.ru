<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$obElement  = new CIBlockElement;
$obUser     = new CUser;


$REG_CENTER_LIST = array();
//получаем ригионы региональных центров
$res = CIBlockElement::GetList(
    array('ID' => 'ASC'),
    array(
        'IBLOCK_ID' => rrsIblock::getIBlockId('regions_centers'),
        'ACTIVE' => 'Y',
        'ID' => $center_id
    ),
    false,
    false,
    array(
        'ID',
        'PROPERTY_REGION',
    )
);
while ($ob = $res->Fetch()){
    $REG_CENTER_LIST[$ob['ID']] = $ob['PROPERTY_REGION_VALUE'];
}

$CULTURE_LIST = rrsIblock::getElementList(rrsIblock::getIBlockId('cultures'));
$REGION_LIST =  rrsIblock::getElementList(rrsIblock::getIBlockId('regions'));
//получение данных для фильтрации по причине
$arResult['REASON_LIST'] = getReasonsListForFilter('c');

$rs = $obElement->GetList(
    ['ID' => 'ASC'],
    [
        'IBLOCK_ID' => $arParams['IBLOCK_ID'],
        'ACTIVE' => 'Y',
    ],
    false,
    false,
    [
        'ID',
        'PROPERTY_DEAL',
    ]
);

$arResult['REGIONS_LIST'] = array();
$arResult['CULTURE_LIST'] = array();

while($arRow = $rs->Fetch()) {
    $deal_data = deal::getInfo4BL($arRow['PROPERTY_DEAL_VALUE']);
    $tmp['DEAL_NAME'] = $deal_data['NAME'];
    if(isset($REG_CENTER_LIST[$deal_data['CENTER']])){
        if($REGION_LIST[$REG_CENTER_LIST[$deal_data['CENTER']]]){
            $arResult['REGIONS_LIST'][$REGION_LIST[$REG_CENTER_LIST[$deal_data['CENTER']]]['ID']] = [
                'ID'    => $REGION_LIST[$REG_CENTER_LIST[$deal_data['CENTER']]]['ID'],
                'NAME'  => $REGION_LIST[$REG_CENTER_LIST[$deal_data['CENTER']]]['NAME'],
            ];

        }
    }
    if(isset($CULTURE_LIST[$deal_data['CULTURE_ID']])){
        $arResult['CULTURE_LIST'][$deal_data['CULTURE_ID']] = [
            'ID'    => $deal_data['CULTURE_ID'],
            'NAME'  => $CULTURE_LIST[$deal_data['CULTURE_ID']]['NAME'],
        ];
    }
}

unset($REG_CENTER_LIST,$CULTURE_LIST,$REGION_LIST);

$user_id = $obUser->GetID();

$filterName = $arParams['FILTER_NAME'];
$GLOBALS[$filterName] = array();
if(isset($_GET['region_id'])){
    $GLOBALS[$filterName]['REGION_ID'] = $_GET['region_id'];
}
if(isset($_GET['culture_id'])){
    $GLOBALS[$filterName]['CULTURE_ID'] = $_GET['culture_id'];
}
if(isset($_GET['reasond_id'])
    && is_numeric($_GET['reasond_id'])
    && $_GET['reasond_id'] > 0
){
    $GLOBALS[$filterName]['PROPERTY_ANSWERS'] = $_GET['reasond_id'];
}

$GLOBALS[$filterName]['PROPERTY_USER'] = $user_id;
