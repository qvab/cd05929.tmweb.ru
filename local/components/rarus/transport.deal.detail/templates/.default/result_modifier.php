<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();?>
<?
$arFilter = array('IBLOCK_CODE' => 'deals_docs', 'ACTIVE' => 'Y', 'PROPERTY_DEAL' => $arResult['ID']);
$arSelect = array('ID', 'NAME', 'PROPERTY_FILE_PDF', 'CODE');
$res = CIBlockElement::GetList(array("ID" => "DESC"), $arFilter, false, false, $arSelect);
while ($ob = $res->Fetch()) {
    if (intval($ob['PROPERTY_FILE_PDF_VALUE']) > 0) {
        $ob['FILE'] = CFile::GetFileArray($ob['PROPERTY_FILE_PDF_VALUE']);
        $arResult['DOCS'][$ob['CODE']] = $ob;
    }
}

$res = CIBlockElement::GetList(
    array('ID' => 'DESC'),
    array(
        'IBLOCK_ID' => rrsIblock::getIBlockId('client_request'),
        'ACTIVE' => 'Y',
        'ID' => $arResult['PROPERTIES']['REQUEST']['VALUE']
    )
);
if ($ob = $res->GetNextElement()) {
    $arProps = $ob->GetProperties();
    $arResult['REQUEST'] = array(
        'PAYMENT' => $arProps['PAYMENT']['VALUE_XML_ID']
    );
}

$weight = $fullCost = $fullTransportCost = 0;
$res = CIBlockElement::GetList(
    array('ID' => 'ASC'),
    array(
        'IBLOCK_ID' => rrsIblock::getIBlockId('deals_exe_docs'),
        'ACTIVE' => 'Y',
        'PROPERTY_DEAL' => $arResult['ID']
    ),
    false,
    false,
    array('ID', 'DATE_CREATE', 'PROPERTY_CAR', 'PROPERTY_WEIGHT', 'PROPERTY_DUMP', 'PROPERTY_RC', 'PROPERTY_COST', 'PROPERTY_TRANSPORT_COST')
);
while ($ob = $res->GetNext()) {
    $arResult['VI'][] = array(
        'ID' => $ob['ID'],
        'DATE_CREATE' => $ob['DATE_CREATE'],
        'CAR' => $ob['PROPERTY_CAR_VALUE'],
        'WEIGHT' => $ob['PROPERTY_WEIGHT_VALUE'],
        'TRANSPORT_COST' => $ob['PROPERTY_TRANSPORT_COST_VALUE'],
    );

    $weight += $ob['PROPERTY_WEIGHT_VALUE'];
    $fullTransportCost += $ob['PROPERTY_TRANSPORT_COST_VALUE'];
}

$arResult['VI_SUMMARY']['WEIGHT'] = 0.001 * $weight;
$arResult['VI_SUMMARY']['TRANSPORT_COST'] = $fullTransportCost;

$res = CIBlockElement::GetList(
    array('SORT' => 'ASC'),
    array('IBLOCK_ID' => rrsIblock::getIBlockId('notice'), 'ACTIVE' => 'Y'),
    false,
    false,
    array('ID', 'NAME', 'CODE', 'SORT')
);
while ($ob = $res->Fetch()) {
    $arResult['NOTICE_LIST'][$ob['CODE']] = $ob;
}
?>