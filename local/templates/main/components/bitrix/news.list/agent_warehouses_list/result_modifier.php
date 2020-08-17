<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arResult['REGION_LIST'] = rrsIblock::getElementList(rrsIblock::getIBlockId('regions'));
$arResult['TRANSPORT_LIST'] = rrsIblock::getElementList(rrsIblock::getIBlockId('transport_type'));

$agentObj = new agent();
$arResult['FARMERS_DATA'] = $agentObj->getFarmersForSelect($arParams['UID']);
?>