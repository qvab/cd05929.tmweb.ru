<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();?>
<?
$arFilter = array('IBLOCK_CODE' => 'deals_docs', 'ACTIVE' => 'Y', 'PROPERTY_DEAL' => $arResult['ID']);
$arSelect = array('ID', 'NAME', 'PROPERTY_FILE_PDF', 'CODE', 'DATE_CREATE');
$res = CIBlockElement::GetList(array("DATE_CREATE" => "ASC"), $arFilter, false, false, $arSelect);
while ($ob = $res->Fetch()) {
    if (intval($ob['PROPERTY_FILE_PDF_VALUE']) > 0) {
        $ob['FILE'] = CFile::GetFileArray($ob['PROPERTY_FILE_PDF_VALUE']);
        if ($ob['CODE'] == 'reestr') {
            $arResult['DOCS'][$ob['CODE']][] = $ob;
        }
        else {
            $arResult['DOCS'][$ob['CODE']] = $ob;
        }
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
    array('ID', 'DATE_CREATE', 'PROPERTY_CAR', 'PROPERTY_WEIGHT', 'PROPERTY_DUMP', 'PROPERTY_RC', 'PROPERTY_COST')
);
while ($ob = $res->GetNext()) {
    $arResult['VI'][] = array(
        'ID' => $ob['ID'],
        'DATE_CREATE' => $ob['DATE_CREATE'],
        'CAR' => $ob['PROPERTY_CAR_VALUE'],
        'WEIGHT' => $ob['PROPERTY_WEIGHT_VALUE'],
        'DUMP' => $ob['PROPERTY_DUMP_VALUE'],
        'RC' => $ob['PROPERTY_RC_VALUE'],
        'COST' => $ob['PROPERTY_COST_VALUE'],
    );

    $weight += $ob['PROPERTY_WEIGHT_VALUE'];
    $fullCost += $ob['PROPERTY_COST_VALUE'];
}

$rc_ = $fullCost / $weight;

$arResult['VI_SUMMARY']['WEIGHT'] = 0.001 * $weight;
$arResult['VI_SUMMARY']['RC'] = 1000 * $rc_;
$arResult['VI_SUMMARY']['COST'] = $fullCost;
?>