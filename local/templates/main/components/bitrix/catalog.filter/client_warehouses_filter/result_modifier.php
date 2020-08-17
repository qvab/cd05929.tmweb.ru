<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
$statusList = rrsIblock::getPropListKey('client_warehouse', 'ACTIVE');
foreach ($statusList as $item) {
    $arResult['Q'][$item['XML_ID']] = 0;
    $status[$item['ID']] = $item['XML_ID'];
}
$arResult['Q']['all'] = 0;

$res = CIBlockElement::GetList(
    array('ID' => 'ASC'),
    array(
        'IBLOCK_ID' => rrsIblock::getIBlockId('client_warehouse'),
        'ACTIVE' => 'Y',
        'PROPERTY_CLIENT' => $USER->GetID()
    ),
    false,
    false,
    array(
        'ID',
        'PROPERTY_ACTIVE',
    )
);
while ($ob = $res->Fetch()) {
    $arResult['Q']['all']++;
    $arResult['Q'][$status[$ob['PROPERTY_ACTIVE_ENUM_ID']]]++;
}
?>