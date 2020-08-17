<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
$arResult['SHOW_FORM'] = true;
$agentObj = new agent();
$arResult['CLIENT_LIST'] = $agentObj->getClientsForSelect($arParams['AGENT_ID'], false, false, false, true);
if(empty($arResult['CLIENT_LIST'])){$arResult['SHOW_FORM'] = false;}
?>