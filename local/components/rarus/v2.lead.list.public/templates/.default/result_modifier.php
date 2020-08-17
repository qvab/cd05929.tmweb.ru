<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();?>
<?
$arResult['CULTURE_LIST'] = rrsIblock::getElementList(rrsIblock::getIBlockId('cultures'));
$arResult['DELIVERY_LIST'] = rrsIblock::getPropListKey('farmer_offer', 'DELIVERY');
$arResult['DOCS_LIST'] = rrsIblock::getElementList(rrsIblock::getIBlockId('need_docs'));
$arResult['TRANSPORT_LIST'] = rrsIblock::getElementList(rrsIblock::getIBlockId('transport_type'));
?>