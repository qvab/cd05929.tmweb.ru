<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
$arResult['FARMERS_DATA'] = array(); //дополнительный массив id пользователей для агента АП (для получения данных АП)
$arResult['FARMER_AGENT_RIGHTS'] = array(); //дополнительный массив прав агента с поставщиками
$arResult['CLIENTS_DATA'] = array(); //дополнительный массив id покупателей для агента (для получения данных покупателя)
$arResult['CLIENT_AGENT_RIGHTS'] = array(); //дополнительный массив прав агента с покупателями

$farmers_ids = array();
$clients_ids = array();
$userIds = array();

if ($arParams['USER_TYPE'] == 'FARMER') {
    $ibCode = 'client_profile';
    $userIds = array();
    foreach ($arResult['ITEMS'] as $arItem) {
        if ($arItem['PROPERTIES']['CLIENT']['VALUE'] > 0) {
            $userIds[$arItem['PROPERTIES']['CLIENT']['VALUE']] = true;
        }
    }
}
elseif (in_array($arParams['USER_TYPE'], array('CLIENT', 'PARTNER', 'TRANSPORT'))) {
    $ibCode = 'farmer_profile';
    $userIds = array();
    foreach ($arResult['ITEMS'] as $arItem) {

        if ($arItem['PROPERTIES']['FARMER']['VALUE'] > 0) {
            $userIds[$arItem['PROPERTIES']['FARMER']['VALUE']] = true;
        }
    }
}

if (is_array($userIds) && sizeof($userIds) > 0) {
    $res = CIBlockElement::GetList(
        array('ID' => 'ASC'),
        array(
            'IBLOCK_ID' => rrsIblock::getIBlockId($ibCode),
            'ACTIVE' => 'Y',
            'PROPERTY_USER' => array_keys($userIds)
        ),
        false,
        false,
        array('ID', 'NAME', 'LAST_NAME', 'EMAIL', 'PROPERTY_FULL_COMPANY_NAME', 'PROPERTY_IP_FIO', 'PROPERTY_UL_TYPE', 'PROPERTY_USER')
    );
    while ($ob = $res->Fetch()) {
        $ulType = rrsIblock::getPropListId($ibCode, 'UL_TYPE', $ob['PROPERTY_UL_TYPE_ENUM_ID']);
        if ($ulType == 'ip') {
            $name = 'ИП ' . $ob['PROPERTY_IP_FIO_VALUE'];
        }
        else {
            $name = $ob['PROPERTY_FULL_COMPANY_NAME_VALUE'];
        }
        $arResult['USER_LIST'][$ob['PROPERTY_USER_VALUE']] = $name;
    }
}

//получение данных для агента
if(isset($arParams['AGENT_USER'])
    && $arParams['AGENT_USER'] == 'Y'
){
    $agentObj = new agent();

    //получение данных АП для агента АП
    if($arParams['USER_TYPE'] == 'FARMER'){
        $arResult['FARMERS_DATA'] = $agentObj->getFarmersForSelect($USER->GetID());
        $arResult['FARMER_AGENT_RIGHTS'] = $agentObj->checkFarmerByAgentRights(array_keys($arResult['FARMERS_DATA']), $USER->GetID(), 'deals');
    }
    //получение данных покупателя для агента покупателя
    else{
        $arResult['CLIENTS_DATA'] = $agentObj->getClientsForSelect($USER->GetID(), false, true, true, true);
        $arResult['CLIENT_AGENT_RIGHTS'] = $agentObj->getClientDealsRightsForAgent($USER->GetID(), $arResult['CLIENTS_DATA'], true);
    }
}
?>