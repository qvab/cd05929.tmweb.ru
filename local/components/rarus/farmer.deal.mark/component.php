<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

CModule::IncludeModule('iblock');

$assetInst = \Bitrix\Main\Page\Asset::getInstance();

if (!$arParams['DEAL_ID'] || !is_numeric($arParams['DEAL_ID'])) {
    ShowError(GetMessage("NO_DEAL_ID_ERROR"));
    return 0;
}

$res = CIBlockElement::GetList(
    array('ID' => 'DESC'),
    array(
        'IBLOCK_ID' => $arParams['DEALS_IBLOCK_ID'],
        'ACTIVE' => 'Y',
        'ID' => $arParams['DEAL_ID']
    ),
    false,
    false,
    array(
        'ID',
        'ACTIVE_FROM',
        'PROPERTY_CULTURE',
        'PROPERTY_CULTURE.NAME',
        'PROPERTY_CLIENT',
        'PROPERTY_REQUEST',
        'PROPERTY_FARMER',
        'PROPERTY_OFFER',
        'PROPERTY_PARTNER',
    )
);
if ($ob = $res->Fetch()) {
    $arResult['DEAL'] = $ob;
}

if (!$arResult['DEAL']['ID']) {
    ShowError(GetMessage("NO_DEAL_ERROR"));
    return 0;
}
if ($arResult['DEAL']['PROPERTY_FARMER_VALUE'] != $USER->GetID()) {
    ShowError(GetMessage("NO_PERMISSIONS_ERROR"));
    return 0;
}

$res = CIBlockElement::GetList(
    array('ID' => 'DESC'),
    array(
        'IBLOCK_ID' => rrsIblock::getIBlockId('client_marks'),
        'ACTIVE' => 'Y',
        'PROPERTY_DEAL' => $arResult['DEAL']['ID']
    ),
    false,
    false,
    array(
        'ID',
        'DATE_CREATE',
        'PROPERTY_CHECK_FARMER',
    )
);
if ($ob = $res->Fetch()) {
    $markElement = $ob;
}

if (!$markElement['ID']) {
    ShowError(GetMessage("NO_MARK_ELEMENT_ERROR"));
    return 0;
}
if ($markElement['PROPERTY_CHECK_FARMER_ENUM_ID'] > 0) {
    ShowError(GetMessage("EXIST_MARK_ERROR"));
    return 0;
}
if (strtotime(date("d.m.Y H:i:s")) - strtotime($markElement['DATE_CREATE']) > 86400) {
    ShowError(GetMessage("TIME_MARK_ERROR"));
    return 0;
}

if ($_REQUEST['save']) {
    CIBlockElement::SetPropertyValuesEx(
        $markElement['ID'],
        rrsIblock::getIBlockId('client_marks'),
        array(
            'REC_FARMER' => $_REQUEST['rec'],
            'LAB_FARMER' => $_REQUEST['lab'],
            'PAY_FARMER' => $_REQUEST['pay'],
            'CHECK_FARMER' => rrsIblock::getPropListKey('client_marks', 'CHECK_FARMER', 'yes'),
        )
    );
    LocalRedirect('/farmer/deals/'.$arResult['DEAL']['ID'].'/?mark=ok');
}

//$arResult['CLIENT'] = client::getProfile($arResult['DEAL']['PROPERTY_CLIENT_VALUE']);
//$arResult['FARMER'] = farmer::getProfile($arResult['DEAL']['PROPERTY_FARMER_VALUE']);

$this->includeComponentTemplate();

$APPLICATION->SetTitle("Сделка #".$arResult['DEAL']['ID']." от ".date('d.m.Y', strtotime($arResult['DEAL']['ACTIVE_FROM'])));
?>