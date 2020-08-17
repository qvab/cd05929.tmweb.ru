<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
$statusList = rrsIblock::getPropListKey('deals_deals', 'STATUS');
foreach ($statusList as $item) {
    $arResult['Q'][$item['XML_ID']] = 0;
    $status[$item['ID']] = $item['XML_ID'];
}
$arResult['Q']['all'] = 0;

$agentObj = new agent();
$arResult['FARMERS_LIST'] = $agentObj->getFarmersForSelect($USER->GetID());

$res = CIBlockElement::GetList(
    array('ID' => 'ASC'),
    array(
        'IBLOCK_ID' => rrsIblock::getIBlockId('deals_deals'),
        'ACTIVE' => 'Y',
        'PROPERTY_'.$arParams['USER_TYPE'] => (count($arResult['FARMERS_LIST']) > 0 ? array_keys($arResult['FARMERS_LIST']) : 0)
    ),
    false,
    false,
    array(
        'ID',
        'PROPERTY_STATUS'
    )
);
while ($ob = $res->Fetch()) {
    $arResult['Q']['all']++;
    $arResult['Q'][$status[$ob['PROPERTY_STATUS_ENUM_ID']]]++;
}
?>