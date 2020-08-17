<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arResult['REGION_LIST'] = rrsIblock::getElementList(rrsIblock::getIBlockId('regions'));


$iUserId = CUser::GetID();

// Является ли текущий пользователь агентом
$arResult['IS_AGENT_CURRENT_USER']   = agent::checkIsAgent($iUserId);
// Является ли  пользователь профиля поставщиком
$arResult['IS_FARMER_PROFILE_USER']  = farmer::checkIsFarmer($arParams['U_ID']);

$arResult['IS_SHOW_ADD_LINK'] = false;
if($arResult['IS_AGENT_CURRENT_USER'] && $arResult['IS_FARMER_PROFILE_USER']) {
    $obAgent = new agent;
    $arResult['IS_SHOW_ADD_LINK'] = $obAgent->checkFarmerByAgent($arParams['U_ID'], $iUserId);
}

?>