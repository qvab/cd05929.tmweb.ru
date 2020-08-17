<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
$arResult['CULTURE_LIST'] = rrsIblock::getElementList(rrsIblock::getIBlockId('cultures'), 'NAME');

if ($arParams['FARMER_ID'] > 0) {
    $arResult['WH_LIST'] = farmer::getWarehouseList($arParams['FARMER_ID']);
}

//получение пар запрос-товар
$arFilter = array(
    'UF_FARMER_ID' => $arParams['FARMER_ID']
);
$arLeads = lead::getLeadList($arFilter);


$cultureList = culture::getAnalog($request['CULTURE_ID']);

$cultureList[] = $request['CULTURE_ID'];

$offers_cultures = farmer::getFermerOffersCultures($arParams['FARMER_ID']);

foreach ($arLeads as $arItem) {
    if(isset($offers_cultures[$arItem['UF_OFFER_ID']]) ){
        $cultures[$offers_cultures[$arItem['UF_OFFER_ID']]] = true;
    }

    $whs[$arItem['UF_FARMER_WH_ID']] = true;
}

$cultures = array_keys($cultures);
$whs = array_keys($whs);

foreach ($arResult['CULTURE_LIST'] as $key => $arItem) {
    if (!in_array($key, $cultures)) {
        unset($arResult['CULTURE_LIST'][$key]);
    }
}
foreach ($arResult['WH_LIST'] as $key => $arItem) {
    if (!in_array($key, $whs)) {
        unset($arResult['WH_LIST'][$key]);
    }
}
?>