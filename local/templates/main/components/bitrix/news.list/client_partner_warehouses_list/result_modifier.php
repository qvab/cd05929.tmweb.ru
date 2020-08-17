<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arResult['REGION_LIST'] = rrsIblock::getElementList(rrsIblock::getIBlockId('regions'));
$arResult['TRANSPORT_LIST'] = rrsIblock::getElementList(rrsIblock::getIBlockId('transport_type'));
$arResult['RIGHTS_LIST'] = rrsIblock::getPropListKey('client_agent_link', 'AGENT_RIGHTS', 'control_w_deals');

$arResult['CLIENTS_DATA'] = partner::getClientsForSelect($arParams['UID'], array_keys($user_ids), false, true, true);
?>