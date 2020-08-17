<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
/** @var CBitrixComponent $this */
/** @var array $arParams */
/** @var array $arResult */
/** @var string $componentPath */
/** @var string $componentName */
/** @var string $componentTemplate */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

/** @global CIntranetToolbar $INTRANET_TOOLBAR */
global $INTRANET_TOOLBAR;

use Bitrix\Main\Context,
	Bitrix\Main\Type\DateTime,
	Bitrix\Main\Loader,
	Bitrix\Iblock;

CPageOption::SetOptionString("main", "nav_page_in_session", "N");


//Объекты
$oElement = new CIBlockElement();
$agentObj = new agent();

$arResult['OFFER_ID'] = 0;
if(isset($_GET['offer_id'])
    && is_numeric($_GET['offer_id'])
){
    $arResult['OFFER_ID'] = $_GET['offer_id'];
    if($arParams['TYPE'] == 'agent'){
        $arParams['FARMER_ID'] = farmer::getOfferFarmer($arResult['OFFER_ID']);
    }


}else{
    LocalRedirect($arParams['REQUEST_LIST_URL']);
    exit;
}



if($arParams['TYPE'] == 'farmer'){
    $arResult['USER_RIGHTS'] = farmer::checkRights('counter_request', $arParams['FARMER_ID'], array('CHECK_BY_OFFER' => $arResult['OFFER_ID']));
//проверка прав
    if(!isset($arResult['USER_RIGHTS']['REQUEST_RIGHT'])
        || $arResult['USER_RIGHTS']['REQUEST_RIGHT'] != 'Y'
    ){
        LocalRedirect($arParams['REQUEST_LIST_URL']);
        exit;
    }
}



//создание встречных предложений
if (isset($_REQUEST['send_data'])
    && $_REQUEST['send_data'] == 'y'
    && isset($_REQUEST['offer_id'])
    && isset($_REQUEST['selected_requests'])
    && is_array($_REQUEST['selected_requests'])
    && count($_REQUEST['selected_requests']) > 0
    && isset($_REQUEST['volume'])
    && trim($_REQUEST['volume']) != ''
    && isset($_REQUEST['price'])
    && trim($_REQUEST['price']) != ''
) {
    global $USER;

    if($arParams['TYPE'] == 'agent'){
        $farmer_id = farmer::getOfferFarmer($_POST['offer_id']);
    }else{
        $farmer_id = $USER->GetID();
    }


    $sendData = array(
        'offer_id'          => $_REQUEST['offer_id'],
        'selected_requests' => $_REQUEST['selected_requests'],
        'price'             => $_REQUEST['price'],
        'volume'            => $_REQUEST['volume'],
        'type'              => 'c', //"counter"
        'farmer_id'         => $farmer_id,
        'delivery'          => 'exw' //if !$_REQUEST['can_deliver'] && !$_REQUEST['lab_trust'] -> exw
    );

    if(isset($_REQUEST['can_deliver'])
        && $_REQUEST['can_deliver'] == 1
    ){
        //if $_REQUEST['can_deliver'] || $_REQUEST['can_deliver'] && $_REQUEST['lab_trust'] -> dap
        $sendData['delivery'] = 'cpt';
    }elseif(isset($_REQUEST['lab_trust'])
        && $_REQUEST['lab_trust'] == 1
    ){
        //if $_REQUEST['lab_trust'] -> fca
        $sendData['delivery'] = 'fca';
    }

    farmer::addCounterRequest($sendData,$arParams['TYPE']);
}

//получение пар запрос-товар
$arFilter = array(
    'UF_FARMER_ID'  => $arParams['FARMER_ID'],
    'UF_OFFER_ID'   => $arResult['OFFER_ID']
);

if (intval($_GET['culture']) > 0) {
    $arFilter['UF_CULTURE_ID'] = intval($_GET['culture']);
    $arOffer = farmer::getOfferById($arResult['OFFER_ID']);
}
if (intval($_GET['wh']) > 0) {
    $arFilter['UF_FARMER_WH_ID'] = intval($_GET['wh']);
}


if(intval($_GET['type_nds']) > 0) {
    switch ($_GET['type_nds']) {
        case 1:
            $arFilter['UF_NDS'] = 'yes';
            break;
        case 2:
            $arFilter['UF_NDS'] = 'no';
            break;
    }
}


$arLeads = lead::getLeadList($arFilter);

if (sizeof($arLeads) < 1) {
    $arResult['ERROR'] = "Ни одного запроса не найдено";
}

if (!$arResult['ERROR']) {
    $offerRequestApply = lead::createLeadList($arLeads);

    //сортировка запросов по культурам и по цене
    usort($offerRequestApply, "orderRcPrice");
    //$offerRequestApply = deal::leadsSort($offerRequestApply);
    $offerRequestApply = deal::leadsSort($offerRequestApply, true);

    $arGroupItems   = [];
    $arFarmerId     = [];
    $arCultureId    = [];
    $arClientsId    = [];
    $arRequestWarehouseId = [];

    $arFarmerId[] = $arParams['FARMER_ID'];

    foreach ($arFarmerId as $iFarmerId) {
        if (empty($offerRequestApply[$iFarmerId])) {
            continue;
        }

        foreach ($offerRequestApply[$iFarmerId] as $arItem) {
            $iCultureId = $arItem['OFFER']['CULTURE_ID'];
            $arCultureId[$iCultureId] = $iCultureId;

            // Группируем по культуре и по АП
            $arGroupItems[$iCultureId][$iFarmerId][] = $arItem;

            if (!empty($arItem['REQUEST']['CLIENT_ID'])) {
                $arClientsId[$arItem['REQUEST']['CLIENT_ID']] = true;
            }

            if(!empty($arItem['REQUEST']['BEST_PRICE']['WH_ID'])) {
                $arRequestWarehouseId[$arItem['REQUEST']['BEST_PRICE']['WH_ID']] = true;
            }
        }
    }
    unset($offerRequestApply, $iCultureId, $iFarmerId);

    // Склады
    if (!empty($arRequestWarehouseId)) {
        $arRequestWarehouseId = array_keys($arRequestWarehouseId);
        $arResult['REQUEST_WAREHOUSES_LIST'] = client::getWarehouseParamsList($arRequestWarehouseId);
    }

    /**
     * Разбиваем группировку на блоки [АП - Цена по каждой культуре]
     */
    $nBlockNum = 0;
    $arResult['ITEMS'] = [];

    loopStart:
    $bGoTo = false;
    foreach ($arFarmerId as $iFarmerId) {

        foreach ($arCultureId as $iCultureId) {

            if(empty($arGroupItems[$iCultureId][$iFarmerId])) {
                continue;
            }

            $arResult['ITEMS'][$nBlockNum][] = array_shift($arGroupItems[$iCultureId][$iFarmerId]);
            if(!empty($arGroupItems[$iCultureId][$iFarmerId])) {
                $bGoTo = true;
            }
        }
        $nBlockNum++;
    }

    if($bGoTo) {
        goto loopStart;
    }

    //получаем максимальную и минимальную цены
    $min_val = 0;
    $max_val = 0;
    foreach($arResult['ITEMS'] as $cur_block){
        foreach ($cur_block as $arItem){
            $temp_val = round($arItem['REQUEST']['BEST_PRICE']['ACC_PRICE_CSM']);
            if($min_val > $temp_val || $min_val == 0){
                $min_val = $temp_val;
            }
            if($max_val < $temp_val || $max_val == 0){
                $max_val = $temp_val;
            }
        }
    }
    $arResult['SET_VALUE'] = round($max_val);
    $arResult['MAX_PRICE'] = round($max_val + $max_val * 0.1);
    $arResult['MIN_PRICE'] = round($min_val - $min_val * 0.1);
}

if (!is_array($arResult["ITEMS"]) && sizeof($arResult["ITEMS"]) == 0){
    LocalRedirect($arParams['REQUEST_LIST_URL']);
    exit;
}

$this->includeComponentTemplate();