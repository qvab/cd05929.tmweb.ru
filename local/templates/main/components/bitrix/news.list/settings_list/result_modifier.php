<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
$arResult['NOTICE_LIST_SOURCE'] = rrsIblock::getPropListId('notice', 'SOURCE');
$arResult['NOTICE_LIST_TYPE'] = rrsIblock::getPropListId('notice', 'TYPE');

$arResult['VIEW'] = array();

$res = CIBlockElement::GetList(
    array('SORT' => 'ASC', 'ID' => 'ASC'),
    array('IBLOCK_ID' => rrsIblock::getIBlockId('notice'), 'ACTIVE' => 'Y', 'PROPERTY_USER_GROUP' => $arParams['USER_TYPE']),
    false,
    false,
    array('ID', 'NAME', 'CODE', 'PROPERTY_USER_GROUP', 'PROPERTY_CAN_CHANGE', 'PROPERTY_SOURCE', 'PROPERTY_TYPE')
);
while ($ob = $res->Fetch()) {
    $sourceCode = $arResult['NOTICE_LIST_SOURCE'][$ob['PROPERTY_SOURCE_ENUM_ID']]['XML_ID'];
    $typeCode = $arResult['NOTICE_LIST_TYPE'][$ob['PROPERTY_TYPE_ENUM_ID']]['XML_ID'];

    $arResult['VIEW'][$sourceCode] = true;
    $arResult['VIEW'][$typeCode] = true;
    $canChange = false;
    if (in_array($arParams['USER_TYPE'], $ob['PROPERTY_CAN_CHANGE_VALUE']))
        $canChange = true;
    $arResult['USER_NOTICE_LIST'][$sourceCode][$typeCode] = array(
        'ID' => $ob['ID'],
        'NAME' => $ob['NAME'],
        'CHANGE' => $canChange
    );
}

$arrTemp = reset($arResult['ITEMS']);
if(!empty($arrTemp['PROPERTIES']['NOTICE']['VALUE'])) {
    $arResult['INFO'] = $arrTemp['PROPERTIES']['NOTICE']['VALUE'];
}