<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();?>
<?
$arResult['CULTURE_LIST'] = rrsIblock::getElementList(rrsIblock::getIBlockId('cultures'));
$arResult['DELIVERY_LIST'] = rrsIblock::getPropListKey('farmer_offer', 'DELIVERY');
$arResult['DOCS_LIST'] = rrsIblock::getElementList(rrsIblock::getIBlockId('need_docs'));
$arResult['TRANSPORT_LIST'] = rrsIblock::getElementList(rrsIblock::getIBlockId('transport_type'));

$clientIds = array();
foreach ($arResult['ITEMS'] as $arItem) {
    if (intval($arItem['REQUEST']['CLIENT_ID']) > 0) {
        $clientIds[$arItem['REQUEST']['CLIENT_ID']] = true;
    }
}

$arResult['CLIENT_RATING'] = client::getRating(array_keys($clientIds));
?>