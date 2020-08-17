<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

global $USER;
CModule::IncludeModule('iblock');
$el_obj = new CIBlockElement;
$arResult['ITEMS'] = array();
$arResult['REGION_LIST'] = array();
$arResult['CULTURE_LIST'] = array();

$user_names = array();
//получение "названий" пользователей
$res = $el_obj->GetList(
    array('ID' => 'ASC'),
    array(
        'IBLOCK_ID' => rrsIblock::getIBlockId('farmer_agent_link'),
        'PROPERTY_AGENT_ID' => $USER->GetID(),
        '!PROPERTY_FARMER_NICKNAME' => false
    ),
    false,
    false,
    array('PROPERTY_USER_ID', 'PROPERTY_FARMER_NICKNAME')
);
while($data = $res->Fetch()){
    $user_names[$data['PROPERTY_USER_ID_VALUE']] = trim($data['PROPERTY_FARMER_NICKNAME_VALUE']);
}

//получение пользователей
$res = CUser::GetList(
    ($by = 'id'), ($order = 'desc'),
    array(
        'GROUPS_ID' => array(11),
        'ACTIVE' => 'Y'
    ),
    array('FIELDS' => array('ID', 'EMAIL', 'NAME', 'LAST_NAME', 'SECOND_NAME'))
);
while($data = $res->Fetch()){
    if(isset($user_names[$data['ID']])
        && $user_names[$data['ID']] != ''
    ){
        $arResult['ITEMS'][$data['ID']] = $user_names[$data['ID']];
    }else{
        $arResult['ITEMS'][$data['ID']] = trim($data['NAME'] . ' ' . $data['LAST_NAME'] . ' ' . $data['SECOND_NAME']);
    }

    if(!checkEmailFromPhone($data['EMAIL'])) {
        if ($arResult['ITEMS'][$data['ID']]) {
            $arResult['ITEMS'][$data['ID']] .= ' (' . $data['EMAIL'] . ')';
        } else {
            $arResult['ITEMS'][$data['ID']] .= ' (' . $data['EMAIL'] . ')';
        }
    }

    if(empty($arResult['ITEMS'][$data['ID']])){
        $arResult['ITEMS'][$data['ID']] = $data['ID'];
    }
}

//получение культур и складов
if(count($arResult['ITEMS']) > 0) {

    //получение регионов
    $culture_ids = array();
    $res = $el_obj->GetList(
        array('PROPERTY_REGION.SORT' => 'ASC', 'PROPERTY_REGION.NAME' => 'ASC'),
        array(
            'IBLOCK_ID' => rrsIblock::getIBlockId('farmer_warehouse'),
            'ACTIVE' => 'Y',
            'PROPERTY_ACTIVE' => rrsIblock::getPropListKey('farmer_warehouse', 'ACTIVE', 'yes'),
            '!PROPERTY_FARMER' => false,
            '!PROPERTY_REGION' => false,
        ),
        false,
        false,
        array('ID', 'NAME', 'PROPERTY_FARMER', 'PROPERTY_REGION', 'PROPERTY_REGION.NAME')
    );
    while($data = $res->Fetch()){
        if(isset($arResult['ITEMS'][$data['PROPERTY_FARMER_VALUE']])
            && is_numeric($data['PROPERTY_REGION_VALUE'])
            && $data['PROPERTY_REGION_VALUE'] > 0
        ){
            $arResult['REGION_LIST'][$data['PROPERTY_REGION_VALUE']] = $data['PROPERTY_REGION_NAME'];
        }
    }

    //получение культур
    $res = $el_obj->GetList(
        array('PROPERTY_CULTURE' => 'ASC'),
        array(
            'IBLOCK_ID' => rrsIblock::getIBlockId('farmer_offer'),
            'ACTIVE' => 'Y',
            'PROPERTY_FARMER' => array_keys($arResult['ITEMS']),
            'PROPERTY_ACTIVE' => rrsIblock::getPropListKey('farmer_offer', 'ACTIVE', 'yes'),
            '!PROPERTY_CULTURE' => false
        ),
        array('PROPERTY_CULTURE'),
        false
    );
    while($data = $res->Fetch()){
        if(isset($data['PROPERTY_CULTURE_VALUE'])
            && is_numeric($data['PROPERTY_CULTURE_VALUE'])
        ){
            $culture_ids[$data['PROPERTY_CULTURE_VALUE']] = true;
        }
    }

    if(count($culture_ids) > 0){
        $res = $el_obj->GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('cultures'),
                'ACTIVE' => 'Y',
                'ID' => array_keys($culture_ids)
            ),
            false,
            false,
            array('ID', 'NAME')
        );
        while($data = $res->Fetch()){
            $arResult['CULTURE_LIST'][$data['ID']] = $data['NAME'];
        }
    }
}

$filterName = $arParams['FILTER_NAME'];
$GLOBALS[$filterName] = array();

if(!empty($_GET['farmer_id'])) {
    $GLOBALS[$filterName]['ID'] = $_GET['farmer_id'];
}
if(!empty($_GET['is_linked'])) {
    $GLOBALS[$filterName]['LINKED_TYPE'] = $_GET['is_linked'];
}
if(!empty($_GET['region_id'])
    && isset($arResult['REGION_LIST'][$_GET['region_id']])
) {
    $GLOBALS[$filterName]['REGION'] = $_GET['region_id'];
}
if(!empty($_GET['culture_id'])
    && isset($arResult['CULTURE_LIST'][$_GET['culture_id']])
) {
    $GLOBALS[$filterName]['CULTURE'] = $_GET['culture_id'];
}