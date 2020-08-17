<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$obElement  = new CIBlockElement;
$obUser     = new CUser;
$obGroup    = new CGroup;
$filterName = $arParams['FILTER_NAME'];
$GLOBALS[$filterName] = array();

$iUserId = $obUser->GetID();

$statusList = rrsIblock::getPropListKey('deals_deals', 'STATUS');
foreach ($statusList as $item) {
    $arResult['Q'][$item['XML_ID']] = 0;
    $status[$item['ID']] = $item['XML_ID'];
}
$arResult['Q']['all'] = 0;

/**
 * Обработка
 */
if (!isset($_REQUEST['status']) || (!in_array($_REQUEST['status'], array('new')))) {
    $_REQUEST['status'] = 'new';
}


// Группы
$rsGroup = $obGroup->GetList(
    $by = "c_sort",
    $order = "asc",
    ['STRING_ID' => 'partner']
);

$arGroup = [];
while ($arRow = $rsGroup->Fetch()) {
    $arGroup[$arRow['STRING_ID']] = $arRow['ID'];
}

// Группы пользователя
$arGroupUser = array_flip($obUser->GetUserGroup($iUserId));

// Роли
$arResult['IS_AGENTS']          = false;
$arResult['IS_CLIENT_AGENTS']   = false;

$agentObj = new agent();
$arResult['REGIONS_LIST'] = [];
$arResult['CLIENT_LIST'] = $agentObj->getClientsForSelect($arParams['AGENT_ID']);
$arResult['FARMER_LIST'] = $agentObj->getFarmersForSelect($arParams['AGENT_ID']);

$arFilterDeals = array(
    'IBLOCK_ID' => rrsIblock::getIBlockId('deals_deals'),
    'ACTIVE' => 'Y',
);

$bFoundLinkedUsers = false;
if(count($arResult['CLIENT_LIST']) > 0){
    $arFilterDeals['PROPERTY_CLIENT'] = array_keys($arResult['CLIENT_LIST']);
    $bFoundLinkedUsers = true;
}else{
    $arFilterDeals['ID'] = 0;
}
if(count($arResult['FARMER_LIST']) > 0){
    $arFilterDeals['PROPERTY_FARMER'] = array_keys($arResult['FARMER_LIST']);
    $bFoundLinkedUsers = true;
}else{
    $arFilterDeals['ID'] = 0;
}
if($bFoundLinkedUsers){
    unset($arFilterDeals['ID']);
}

//ставим условие "или", заданы и покупатели и поставщики
if(isset($arFilterDeals['PROPERTY_CLIENT'])
    && isset($arFilterDeals['PROPERTY_FARMER'])
){
    $arFilterDeals[] = array(
        'LOGIC' => 'OR',
        array('PROPERTY_CLIENT' => $arFilterDeals['PROPERTY_CLIENT']),
        array('PROPERTY_FARMER' => $arFilterDeals['PROPERTY_FARMER'])
    );
    unset($arFilterDeals['PROPERTY_CLIENT'], $arFilterDeals['PROPERTY_FARMER']);
}

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

$GLOBALS[$filterName]['PROPERTY_PAIR_STATUS'] = rrsIblock::getPropListKey('deals_deals', 'PAIR_STATUS', 'new');
//получаем данные пар
$rs = $obElement->GetList(
    ['ID' => 'ASC'],
    $arFilterDeals,
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
while ($arRow = $rs->Fetch()) {

    if(!empty($arRow['PROPERTY_CULTURE_VALUE'])) {
        $arCultureId[$arRow['PROPERTY_CULTURE_VALUE']] = $arRow['PROPERTY_CULTURE_VALUE'];
        $arDealsByCulture[$arRow['PROPERTY_CULTURE_VALUE']][] = $arRow['ID'];
    }

    if(!empty($arRow['PROPERTY_CLIENT_WAREHOUSE_VALUE'])) {
        $arClientWarehouseId[$arRow['PROPERTY_CLIENT_WAREHOUSE_VALUE']][] = $arRow['PROPERTY_CLIENT_WAREHOUSE_VALUE'];
        $arDealsByClientWarehouse[$arRow['PROPERTY_CLIENT_WAREHOUSE_VALUE']][] = $arRow['ID'];
    }

    if(!empty($arRow['PROPERTY_FARMER_WAREHOUSE_VALUE'])) {
        $arFarmerWarehouseId[$arRow['PROPERTY_FARMER_WAREHOUSE_VALUE']][] = $arRow['PROPERTY_FARMER_WAREHOUSE_VALUE'];
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

//фильтр по умолчанию
$bFoundLinkedUsers = false;
if(count($arResult['CLIENT_LIST']) > 0){
    //фильтр по покупателям
    $GLOBALS[$filterName]['PROPERTY_CLIENT'] = array_keys($arResult['CLIENT_LIST']);
    $bFoundLinkedUsers = true;
}else{
    $GLOBALS[$filterName]['ID'] = 0;
}
if(count($arResult['FARMER_LIST']) > 0){
    //фильтр по поставщикам
    $GLOBALS[$filterName]['PROPERTY_FARMER'] = array_keys($arResult['FARMER_LIST']);
    $bFoundLinkedUsers = true;
}else{
    $GLOBALS[$filterName]['ID'] = 0;
}
if($bFoundLinkedUsers){
    unset($GLOBALS[$filterName]['ID']);
}

if(count($arClientId) > 0){
    $CLIENT_LIST = array();
    foreach ($arClientId as $k=>$v){
        if(isset($arResult['CLIENT_LIST'][$k])){
            $CLIENT_LIST[$k] = $arResult['CLIENT_LIST'][$k];
        }
    }
    $arResult['CLIENT_LIST'] = $CLIENT_LIST;
    $GLOBALS[$filterName]['PROPERTY_CLIENT'] = array_keys($arResult['CLIENT_LIST']);
}else{
    $arResult['CLIENT_LIST'] = array();
}
$REGIONS = $agentObj->getAgentRegionsByWH($arClientWarehouseId);
$arResult['CL_REGION_TO_WH'] = array();
if((sizeof($REGIONS['REGIONS']))&&(is_array($REGIONS['REGIONS']))){
    $arResult['REGIONS_LIST'] = $REGIONS['REGIONS'];
}
if((sizeof($REGIONS['REGION_TO_WH']))&&(is_array($REGIONS['REGION_TO_WH']))){
    $arResult['CL_REGION_TO_WH'] = $REGIONS['REGION_TO_WH'];
}

if(count($arFarmerId) > 0){
    $FARMER_LIST = array();
    foreach ($arFarmerId as $k=>$v){
        if(isset($arResult['FARMER_LIST'][$k])){
            $FARMER_LIST[$k] = $arResult['FARMER_LIST'][$k];
        }
    }
    $arResult['FARMER_LIST'] = $FARMER_LIST;
    $GLOBALS[$filterName]['PROPERTY_FARMER'] = array_keys($arResult['FARMER_LIST']);
}else{
    $arResult['FARMER_LIST'] = array();
}
$REGIONS = $agentObj->getAgentRegionsByWH($arFarmerWarehouseId,'FARMER');
$arResult['FM_REGION_TO_WH'] = array();
if((sizeof($REGIONS['REGIONS']))&&(is_array($REGIONS['REGIONS']))){
    if(!isset($arResult['REGIONS_LIST']) || count($arResult['REGIONS_LIST']) == 0) {
        $arResult['REGIONS_LIST'] = $REGIONS['REGIONS'];
    }else{
        foreach($REGIONS['REGIONS'] as $cur_pos => $cur_data){
            $arResult['REGIONS_LIST'][$cur_pos] = $cur_data;
        }
    }
}
if((sizeof($REGIONS['REGION_TO_WH']))&&(is_array($REGIONS['REGION_TO_WH']))){
    if(!isset($arResult['REGION_TO_WH']) || count($arResult['REGION_TO_WH']) == 0) {
        $arResult['FM_REGION_TO_WH'] = $REGIONS['REGION_TO_WH'];
    }
}

//если заданы фильтры и по покупателю и по поставщику, то организоываем логику "ИЛИ"
if(isset($GLOBALS[$filterName]['PROPERTY_FARMER'])
    && count($GLOBALS[$filterName]['PROPERTY_FARMER']) > 0
    && isset($GLOBALS[$filterName]['PROPERTY_CLIENT'])
    && count($GLOBALS[$filterName]['PROPERTY_CLIENT']) > 0
){
    $GLOBALS[$filterName]['0'] = array(
        'LOGIC' => 'OR',
        array('PROPERTY_CLIENT' => $GLOBALS[$filterName]['PROPERTY_CLIENT']),
        array('PROPERTY_FARMER' => $GLOBALS[$filterName]['PROPERTY_FARMER'])
    );
    unset($GLOBALS[$filterName]['PROPERTY_FARMER'], $GLOBALS[$filterName]['PROPERTY_CLIENT']);
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
        ['ID', 'NAME']
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
if(count($arClientWarehouseId) > 0) {

    $rs = $obElement->GetList(
        ['NAME' => 'ASC'],
        [
            'IBLOCK_ID' => getIBlockID('client', 'client_warehouse'),
            'ID'        => array_keys($arClientWarehouseId),
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
if(count($arFarmerWarehouseId) > 0) {

    $rs = $obElement->GetList(
        ['NAME' => 'ASC'],
        [
            'IBLOCK_ID' => getIBlockID('farmer', 'farmer_warehouse'),
            'ID'        => array_keys($arFarmerWarehouseId),
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

$arResult['SHOW_FORM'] = (
    !empty($arResult['REGIONS_LIST'])           ||
    !empty($arResult['CULTURE_LIST'])           ||
    !empty($arResult['CLIENT_WAREHOUSE_LIST'])  ||
    !empty($arResult['FARMER_WAREHOUSE_LIST'])  ||
    !empty($arResult['CLIENT_LIST'])            ||
    !empty($arResult['FARMER_LIST'])
);

//Фильтрация по покупателю
if(!empty($_GET['client_id'])) {
    if(isset($GLOBALS[$filterName][0])){
        //убираем фильтр "или"
        unset($GLOBALS[$filterName][0]);
    }
    $GLOBALS[$filterName]['PROPERTY_CLIENT'] = $_GET['client_id'];
}
//Фильтрация по АП
if(!empty($_GET['farmer_id'])) {
    if(isset($GLOBALS[$filterName][0])){
        //убираем фильтр "или"
        unset($GLOBALS[$filterName][0]);
    }
    $GLOBALS[$filterName]['PROPERTY_FARMER'] = $_GET['farmer_id'];
}

//Фильтрация по региону
if(!empty($_GET['region_id'])){
    //если задан регион, то получает склады региона и по ним делаем фильтр
    if(isset($arResult['CL_REGION_TO_WH'][$_GET['region_id']])) {
        $GLOBALS[$filterName]['PROPERTY_CLIENT_WAREHOUSE'] = $arResult['CL_REGION_TO_WH'][$_GET['region_id']];
    }elseif(isset($arResult['FM_REGION_TO_WH'][$_GET['region_id']])) {
        $GLOBALS[$filterName]['PROPERTY_FARMER_WAREHOUSE'] = $arResult['FM_REGION_TO_WH'][$_GET['region_id']];
    }else{
        //если фильтр региона не относится ни к покупателям ни к поставщикам - "портим фильтр"
        $GLOBALS[$filterName]['PROPERTY_FARMER_WAREHOUSE'] = 0;
    }
}

//Фильтрация по культуре
if(!empty($_GET['culture_id'])) {
    $GLOBALS[$filterName]['PROPERTY_CULTURE'] = $_GET['culture_id'];
}

//Фильтрация по складу покупателя
if(!empty($_GET['client_warehouse_id'])) {
    $GLOBALS[$filterName]['PROPERTY_CLIENT_WAREHOUSE'] = $_GET['client_warehouse_id'];
}

//Фильтрация по складу АП
if(!empty($_GET['farmer_warehouse_id'])) {
    $GLOBALS[$filterName]['PROPERTY_FARMER_WAREHOUSE'] = $_GET['farmer_warehouse_id'];
}

$base_url = '/partner/pair/';

//получение у кого в черном списке состоит текущий пользователь для дополнительнйо фильтрации вывода
if($arResult['IS_CLIENT_AGENTS']){
    $opp_filter = client::getBlackListWhereOpponent($USER->GetID());
    if(count($opp_filter) > 0){
        $GLOBALS['arrFilter']['!PROPERTY_FARMER'] = $opp_filter;
    }

}elseif($arResult['IS_AGENTS']){
    $base_url = '/agent/pair/';
    $opp_filter = farmer::getBlackListWhereOpponent($USER->GetID());
    if(count($opp_filter) > 0){
        $GLOBALS['arrFilter']['!PROPERTY_CLIENT'] = $opp_filter;
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
                    $page =  ceil($data['RANK'] / 20);
                    $currentPage = 1;
                    if(isset($_GET['PAGEN_1'])){
                        $currentPage = $_GET['PAGEN_1'];
                    }
                    if($page>0){
                        if($currentPage != $page){
                            $url = $base_url.'?id='.$_GET['id'];
                            if($page>1) {
                                $url .= '&PAGEN_1=' . $page;
                            }
                            LocalRedirect($url);
                        }
                    }
                }

            }
        }
    }
}