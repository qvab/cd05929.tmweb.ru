<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
$arResult['CULTURE_LIST'] = rrsIblock::getElementList(rrsIblock::getIBlockId('cultures'));
$arResult['DELIVERY_LIST'] = rrsIblock::getElementList(rrsIblock::getIBlockId('delivery4client'));
//$arResult['MARGIN_LIST'] = rrsIblock::getElementList(rrsIblock::getIBlockId('urgency'));
$arResult['REQUEST_COST'] = client::getCostList($arResult['ELEMENTS']);
$arResult['REQUEST_PARAMS'] = client::getParamsList($arResult['ELEMENTS']);

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

$res = CIBlockElement::GetList(
    array('SORT' => 'ASC', 'ID' => 'ASC'),
    array('IBLOCK_ID' => rrsIblock::getIBlockId('basis_values'), 'ACTIVE' => 'Y'),
    false,
    false,
    array('ID', 'NAME', 'PROPERTY_QUALITY', 'PROPERTY_CULTURE')
);
while ($ob = $res->Fetch()) {
    foreach ($ob['PROPERTY_CULTURE_VALUE'] as $culture_id) {
        $arResult['LBASE_INFO'][$culture_id][$ob['PROPERTY_QUALITY_VALUE']][$ob['ID']] = $ob['NAME'];
    }
}

$res = CIBlockElement::GetList(
    array('ID' => 'DESC'),
    array('IBLOCK_ID' => rrsIblock::getIBlockId('characteristics'), 'ACTIVE' => 'Y'),
    false,
    false,
    array('ID', 'NAME', 'PROPERTY_CULTURE', 'PROPERTY_QUALITY', 'PROPERTY_QUALITY.NAME')
);
while ($ob = $res->Fetch()) {
    $arResult['PARAMS_INFO'][$ob['PROPERTY_CULTURE_VALUE']][$ob['PROPERTY_QUALITY_VALUE']] = array(
        'ID' => $ob['ID'],
        'QUALITY_NAME' => $ob['PROPERTY_QUALITY_NAME']
    );
}

$warehousesIds = array();
foreach ($arResult["REQUEST_COST"] as $arCost) {
    foreach ($arCost as $arItem) {
        $warehousesIds[$arItem['WH_ID']] = true;
    }
}
$arResult['WAREHOUSES_LIST'] = client::getWarehouseParamsList(array_keys($warehousesIds));

$res = CIBlockElement::GetList(
    array('ID' => 'DESC'),
    array('IBLOCK_ID' => rrsIblock::getIBlockId('deals_deals'), 'ACTIVE' => 'Y', 'PROPERTY_REQUEST' => $arResult['ELEMENTS']),
    false,
    false,
    array('ID', 'NAME', 'DATE_CREATE', 'PROPERTY_VOLUME', 'PROPERTY_STATUS', 'PROPERTY_REQUEST')
);
while ($ob = $res->Fetch()) {
    $arResult['DEALS'][$ob['PROPERTY_REQUEST_VALUE']][] = array(
        'ID' => $ob['ID'],
        'DATE_CREATE' => $ob['DATE_CREATE'],
        'VOLUME' => $ob['PROPERTY_VOLUME_VALUE'],
        'STATUS' => $ob['PROPERTY_STATUS_VALUE']
    );
}

//получаем те запросу у которых нет активных складов (запрещаем продление таких запросов)
$arResult['CHECK_PROLONG_ITEMS'] = array();
$check_ids = array();
$active_prop = rrsIblock::getPropListKey('client_request', 'ACTIVE', 'yes');
foreach($arResult["ITEMS"] as $cur_req){
    if($arItem['PROPERTIES']['ACTIVE']['VALUE_XML_ID'] != 'yes'
        && $arItem['PROPERTIES']['IS_PROLONGATED']['VALUE_XML_ID'] != 'yes'
    ){
        $check_ids[] = $cur_req['ID'];
    }
}
if(count($check_ids) > 0){
    $arResult['CHECK_PROLONG_ITEMS'] = $check_ids;
}
//отправляем данные в component_epilog
$this->__component->SetResultCacheKeys(array("CHECK_PROLONG_ITEMS"));

unset($warehousesIds, $res, $ob);
?>