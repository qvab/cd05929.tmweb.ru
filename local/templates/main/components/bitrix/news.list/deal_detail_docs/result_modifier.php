<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

foreach ($arResult["ITEMS"] as $key => $arItem) {
    if ($arItem['CODE'] == 'reestr') {
        $arResult['DOCS'][$arItem['CODE']][] = array(
            'NAME' => $arItem['NAME'],
            'DATE_CREATE' => $arItem['DATE_CREATE'],
            'FILE' => $arItem['DISPLAY_PROPERTIES']['FILE_PDF']['FILE_VALUE']
        );
    }
    else {
        $arResult['DOCS'][$arItem['CODE']] = array(
            'NAME' => $arItem['NAME'],
            'DATE_CREATE' => $arItem['DATE_CREATE'],
            'FILE' => $arItem['DISPLAY_PROPERTIES']['FILE_PDF']['FILE_VALUE']
        );
    }
}

$res = CIBlockElement::GetList(
    array('ID' => 'DESC'),
    array('IBLOCK_ID' => rrsIblock::getIBlockId('deals_deals'), 'ACTIVE' => 'Y', 'ID' => $_REQUEST['ELEMENT_ID']),
    false,
    false,
    array(
        'ID',
        'NAME',
        'ACTIVE_FROM',
        'PROPERTY_CULTURE.NAME',
        'PROPERTY_CLIENT',
        'PROPERTY_FARMER',
        'PROPERTY_PARTNER',
        'PROPERTY_TRANSPORT',
    )
);
if ($ob = $res->Fetch()) {
    $arResult['DEAL'] = $ob;
}


// Профиль покупателя
$arResult['CLIENT'] = client::getProfile($arResult['DEAL']['PROPERTY_CLIENT_VALUE']);
// Профиль поставщика
$arResult['FARMER'] = farmer::getProfile($arResult['DEAL']['PROPERTY_FARMER_VALUE']);

if(!empty($arResult['DEAL']['PROPERTY_PARTNER_VALUE'])) {
    $arResult['PARTNER'] = partner::getProfile($arResult['DEAL']['PROPERTY_PARTNER_VALUE']);
}