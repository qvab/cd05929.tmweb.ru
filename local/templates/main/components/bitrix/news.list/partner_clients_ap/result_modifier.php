<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
//получаем список поставщиков
$userFilter = array(
    'ACTIVE' => 'Y',
    'GROUPS_ID' => 9
);
$rsUsers = CUser::GetList(
    ($by = "ID"), ($order = "desc"), $userFilter, array('FIELDS'=>array('ID','LOGIN','NAME','EMAIL'))
);

$USERS = array();
while ($arUser = $rsUsers->Fetch()) {
    $USERS[$arUser['ID']] = $arUser;
}

$ITEMS = array();
if (sizeof($arResult['ITEMS']) > 0) {
    foreach($arResult['ITEMS'] as $i => $arrData){
        if((isset($arResult['ITEMS'][$i]['PROPERTIES']['USER_ID']))&&(isset($arResult['ITEMS'][$i]['PROPERTIES']['PARTNER_ID']))){
            if(array_key_exists($arResult['ITEMS'][$i]['PROPERTIES']['USER_ID']['VALUE'],$USERS)){
                $farmer_exists = BlackList::ClientFarmerBLExists($arResult['ITEMS'][$i]['PROPERTIES']['USER_ID']['VALUE'],$arParams['U_ID']);
                $ITEMS[] = array(
                    'ELEMENT_ID' => $arResult['ITEMS'][$i]['ID'],
                    'CLIENT_ID' => $arResult['ITEMS'][$i]['PROPERTIES']['USER_ID']['VALUE'],
                    'PARTNER_ID' => $arResult['ITEMS'][$i]['PROPERTIES']['PARTNER_ID']['VALUE'],
                    'CLIENT_NAME' => $USERS[$arResult['ITEMS'][$i]['PROPERTIES']['USER_ID']['VALUE']]['NAME'],
                    'CLIENT_LOGIN' => (checkEmailFromPhone($USERS[$arResult['ITEMS'][$i]['PROPERTIES']['USER_ID']['VALUE']]['LOGIN']) ? '' : $USERS[$arResult['ITEMS'][$i]['PROPERTIES']['USER_ID']['VALUE']]['LOGIN']),
                    'CLIENT_EMAIL' => $USERS[$arResult['ITEMS'][$i]['PROPERTIES']['USER_ID']['VALUE']]['EMAIL'],
                    'BL_EXISTS' => $farmer_exists
                );
            }
        }
    }
}
$arResult['ITEMS'] = $ITEMS;
$arResult['FARMER_ID'] = $arParams['U_ID'];
unset($ITEMS,$USERS);
?>