<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
//получаем список поставщиков
$userFilter = array(
    'ACTIVE' => 'Y',
    'GROUPS_ID' => 11
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
    foreach ($arResult['ITEMS'] as $i => $arrData) {
        if ((isset($arResult['ITEMS'][$i]['PROPERTIES']['USER_ID'])) && (isset($arResult['ITEMS'][$i]['PROPERTIES']['FARMER_ID']))) {
            if (array_key_exists($arResult['ITEMS'][$i]['PROPERTIES']['FARMER_ID']['VALUE'], $USERS)) {
                $ITEMS[] = array(
                    'ELEMENT_ID' => $arResult['ITEMS'][$i]['ID'],
                    'CLIENT_ID' => $arResult['ITEMS'][$i]['PROPERTIES']['USER_ID']['VALUE'],
                    'FARMER_ID' => $arResult['ITEMS'][$i]['PROPERTIES']['FARMER_ID']['VALUE'],
                    'FARMER_NAME' => $USERS[$arResult['ITEMS'][$i]['PROPERTIES']['FARMER_ID']['VALUE']]['NAME'],
                    'FARMER_LOGIN' => $USERS[$arResult['ITEMS'][$i]['PROPERTIES']['FARMER_ID']['VALUE']]['LOGIN'],
                    'FARMER_EMAIL' => $USERS[$arResult['ITEMS'][$i]['PROPERTIES']['FARMER_ID']['VALUE']]['EMAIL']
                );
            }
        }
    }
}
$arResult['ITEMS'] = $ITEMS;
unset($ITEMS,$USERS);
?>