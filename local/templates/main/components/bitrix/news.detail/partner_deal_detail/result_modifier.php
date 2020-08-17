<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
$arResult['CLIENT'] = client::getProfile($arResult['PROPERTIES']['CLIENT']['VALUE']);
$arResult['FARMER'] = farmer::getProfile($arResult['PROPERTIES']['FARMER']['VALUE']);
$arResult['PARTNER'] = partner::getProfile($arResult['PROPERTIES']['PARTNER']['VALUE']);
$arResult['FARMER_PARTNER'] = partner::getProfile($arResult['FARMER']['PROPERTY_PARTNER_ID_VALUE'], true);
if(isset($arParams['USER_TYPE'])
    && $arParams['USER_TYPE'] == 'p'
){
    $agentObj = new agent();
    $arResult['FARMER_AGENT'] = rrsIblock::getUserInfo($agentObj->getAgentByFarmer($arResult['PROPERTIES']['FARMER']['VALUE']));
}

if ($arResult['PROPERTIES']['TRANSPORT']['VALUE']) {
    $arResult['TRANSPORT'] = transport::getProfile($arResult['PROPERTIES']['TRANSPORT']['VALUE']);
}

$arResult['CLIENT_WAREHOUSE'] = current(client::getWarehouseParamsList(array($arResult['PROPERTIES']['CLIENT_WAREHOUSE']['VALUE'])));
$arResult['FARMER_WAREHOUSE'] = current(farmer::getWarehouseParamsList(array($arResult['PROPERTIES']['FARMER_WAREHOUSE']['VALUE'])));

//устанавливаем имена организаторов и агента АП
$arResult['PARTNER']['PARTNER_NAME'] = $arResult['PARTNER']['PROPERTY_FULL_COMPANY_NAME_VALUE'];
if($arResult['PARTNER']['PARTNER_NAME'] == '') $arResult['PARTNER']['PARTNER_NAME'] = trim($arResult['PARTNER']['USER']['NAME'] . ' ' . $arResult['PARTNER']['USER']['LAST_NAME']);
if($arResult['PARTNER']['PARTNER_NAME'] == '') $arResult['PARTNER']['PARTNER_NAME'] = $arResult['PARTNER']['USER']['LOGIN'];

$arResult['FARMER_PARTNER']['PARTNER_NAME'] = $arResult['FARMER_PARTNER']['PROPERTY_FULL_COMPANY_NAME_VALUE'];
if($arResult['FARMER_PARTNER']['PARTNER_NAME'] == '') $arResult['FARMER_PARTNER']['PARTNER_NAME'] = trim($arResult['FARMER_PARTNER']['USER']['NAME'] . ' ' . $arResult['FARMER_PARTNER']['USER']['LAST_NAME']);
if($arResult['FARMER_PARTNER']['PARTNER_NAME'] == '') $arResult['FARMER_PARTNER']['PARTNER_NAME'] .= $arResult['FARMER_PARTNER']['LOGIN'];

if(isset($arResult['FARMER_AGENT'])){
    $arResult['FARMER_AGENT']['FULL_NAME'] = trim($arResult['FARMER_AGENT']['NAME'] . ' ' . $arResult['FARMER_AGENT']['LAST_NAME']);
    if($arResult['FARMER_AGENT']['FULL_NAME'] == ''){
        $arResult['FARMER_AGENT']['FULL_NAME'] .= $arResult['FARMER_AGENT']['LOGIN'];
    }
}

$arResult['COMMISSION'] = rrsIblock::getConst('commission');

$arResult['PARAMS_INFO'] = culture::getParamsListByCultureId($arResult['PROPERTIES']['CULTURE']['VALUE']);
$arResult['REQUEST_PARAMS'] = current(client::getParamsList(array($arResult['PROPERTIES']['REQUEST']['VALUE'])));

foreach ($arResult['PARAMS_INFO'] as $param) {
    $info = $arResult['REQUEST_PARAMS'][$param['QUALITY_ID']];
    if ($info['LBASE_ID'] > 0) {
        foreach ($param['LIST'] as $item) {
            if ($info['LBASE_ID'] == $item['ID']) {
                $arResult['REQUEST_PARAMS'][$param['QUALITY_ID']]['LBASE_NAME'] = $item['NAME'];
                break;
            }
        }
    }
}

$res = CIBlockElement::GetList(
    array('SORT' => 'ASC', 'ID' => 'ASC'),
    array('IBLOCK_ID' => rrsIblock::getIBlockId('quality'), 'ACTIVE' => 'Y'),
    false,
    false,
    array('ID', 'NAME', 'PROPERTY_UNIT')
);
while ($ob = $res->Fetch()) {
    $arResult['UNIT_INFO'][$ob['ID']] = $ob['PROPERTY_UNIT_VALUE'];
}

if ($arResult['PROPERTIES']['STAGE']['VALUE_XML_ID'] == 'new')
    $arResult['STATUS_MESSAGE'] = 'Новая сделка';
elseif ($arResult['PROPERTIES']['STAGE']['VALUE_XML_ID'] == 'reject')
    $arResult['STATUS_MESSAGE'] = 'Сделка аннулирована';
elseif ($arResult['PROPERTIES']['STAGE']['VALUE_XML_ID'] == 'search')
    $arResult['STATUS_MESSAGE'] = 'Идет поиск перевозчика';
elseif ($arResult['PROPERTIES']['STAGE']['VALUE_XML_ID'] == 'no_transport')
    $arResult['STATUS_MESSAGE'] = 'Перевозчик не найден. Сделка отменена';
elseif ($arResult['PROPERTIES']['STAGE']['VALUE_XML_ID'] == 'dkp')
    $arResult['STATUS_MESSAGE'] = 'Подписание договора купли-продажи';
elseif ($arResult['PROPERTIES']['STAGE']['VALUE_XML_ID'] == 'execution')
    $arResult['STATUS_MESSAGE'] = 'Идет выполнение заказа';
elseif ($arResult['PROPERTIES']['STAGE']['VALUE_XML_ID'] == 'complete')
    $arResult['STATUS_MESSAGE'] = 'Закрытие сделки. Осуществление взаиморасчетов';
elseif ($arResult['PROPERTIES']['STAGE']['VALUE_XML_ID'] == 'close')
    $arResult['STATUS_MESSAGE'] = 'Сделка завершена';


// Профиль покупателя
$arResult['CLIENT'] = client::getProfile($arResult['PROPERTIES']['CLIENT']['VALUE']);
// Профиль поставщика
$arResult['FARMER'] = farmer::getProfile($arResult['PROPERTIES']['FARMER']['VALUE']);