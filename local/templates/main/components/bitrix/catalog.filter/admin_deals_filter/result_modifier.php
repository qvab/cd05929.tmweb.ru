<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$obElement  = new CIBlockElement;

$stageIds = $transportIds = $partnerIds = $farmerIds = $clientIds = $cultureIds = array();

$res = $obElement->GetList(
    array('ID' => 'ASC'),
    array(
        'IBLOCK_ID' => rrsIblock::getIBlockId('deals_deals'),
        'ACTIVE' => 'Y',
        'PROPERTY_STATUS' => rrsIblock::getPropListKey('deals_deals', 'STATUS', 'open'),
    ),
    false,
    false,
    array(
        'ID',
        'PROPERTY_CULTURE',
        'PROPERTY_CLIENT',
        'PROPERTY_FARMER',
        'PROPERTY_PARTNER',
        'PROPERTY_TRANSPORT',
        'PROPERTY_STATUS',
        'PROPERTY_STAGE'
    )
);
while ($ob = $res->Fetch()) {
    if (intval($ob['PROPERTY_CULTURE_VALUE']) > 0) {
        $cultureIds[$ob['PROPERTY_CULTURE_VALUE']] = true;
    }

    if (!empty($ob['PROPERTY_CLIENT_VALUE'])) {
        $clientIds[$ob['PROPERTY_CLIENT_VALUE']] = true;
    }

    if (!empty($ob['PROPERTY_FARMER_VALUE'])) {
        $farmerIds[$ob['PROPERTY_FARMER_VALUE']] = true;
    }

    if (!empty($ob['PROPERTY_PARTNER_VALUE'])) {
        $partnerIds[$ob['PROPERTY_PARTNER_VALUE']] = true;
    }

    if (!empty($ob['PROPERTY_TRANSPORT_VALUE'])) {
        $transportIds[$ob['PROPERTY_TRANSPORT_VALUE']] = true;
    }

    if (intval($ob['PROPERTY_STAGE_ENUM_ID']) > 0) {
        $stageIds[$ob['PROPERTY_STAGE_ENUM_ID']] = true;
    }
}

//Культура
$arResult['CULTURE_LIST'] = array();
if (sizeof($cultureIds) > 0) {
    $res = $obElement->GetList(
        array('NAME' => 'ASC'),
        array(
            'IBLOCK_ID' => rrsIblock::getIBlockId('cultures'),
            'ID' => array_keys($cultureIds),
            'ACTIVE' => 'Y'
        ),
        false,
        false,
        array('ID', 'NAME')
    );

    while ($ob = $res->Fetch()) {
        $arResult['CULTURE_LIST'][$ob['ID']] = array(
            'ID'    => $ob['ID'],
            'NAME'  => $ob['NAME'],
        );
    }
}

//Список покупателей
$arResult['CLIENT_LIST'] = array();
if (sizeof($clientIds) > 0) {
    $res = $obElement->GetList(
        array('NAME' => 'ASC'),
        array(
            'IBLOCK_ID' => rrsIblock::getIBlockId('client_profile'),
            'ACTIVE' => 'Y',
            'PROPERTY_USER' => array_keys($clientIds),
        ),
        false,
        false,
        array(
            'ID',
            'PROPERTY_FULL_COMPANY_NAME',
            'PROPERTY_IP_FIO',
            'PROPERTY_USER'
        )
    );

    while ($ob = $res->Fetch()) {
        if (trim($ob['PROPERTY_FULL_COMPANY_NAME_VALUE']) != '') {
            $ob['COMPANY'] = $ob['PROPERTY_FULL_COMPANY_NAME_VALUE'];
        }
        elseif (trim($ob['PROPERTY_IP_FIO_VALUE']) != '') {
            $ob['COMPANY'] = 'ИП ' . $ob['PROPERTY_IP_FIO_VALUE'];
        }
        $arResult['CLIENT_LIST'][$ob['PROPERTY_USER_VALUE']] = $ob;
    }
}

//Список АП
$arResult['FARMER_LIST'] = array();
if (sizeof($farmerIds) > 0) {
    $res = $obElement->GetList(
        array('NAME' => 'ASC'),
        array(
            'IBLOCK_ID' => rrsIblock::getIBlockId('farmer_profile'),
            'ACTIVE' => 'Y',
            'PROPERTY_USER' => array_keys($farmerIds),
        ),
        false,
        false,
        array(
            'ID',
            'PROPERTY_FULL_COMPANY_NAME',
            'PROPERTY_IP_FIO',
            'PROPERTY_USER'
        )
    );

    while ($ob = $res->Fetch()) {
        if (trim($ob['PROPERTY_FULL_COMPANY_NAME_VALUE']) != '') {
            $ob['COMPANY'] = $ob['PROPERTY_FULL_COMPANY_NAME_VALUE'];
        }
        elseif (trim($ob['PROPERTY_IP_FIO_VALUE']) != '') {
            $ob['COMPANY'] = 'ИП ' . $ob['PROPERTY_IP_FIO_VALUE'];
        }
        $arResult['FARMER_LIST'][$ob['PROPERTY_USER_VALUE']] = $ob;
    }
}

//Список организаторов
$arResult['PARTNER_LIST'] = array();
if (sizeof($partnerIds) > 0) {
    $res = $obElement->GetList(
        array('NAME' => 'ASC'),
        array(
            'IBLOCK_ID' => rrsIblock::getIBlockId('partner_profile'),
            'ACTIVE' => 'Y',
            'PROPERTY_USER' => array_keys($partnerIds),
        ),
        false,
        false,
        array(
            'ID',
            'PROPERTY_FULL_COMPANY_NAME',
            'PROPERTY_IP_FIO',
            'PROPERTY_USER'
        )
    );

    while ($ob = $res->Fetch()) {
        if (trim($ob['PROPERTY_FULL_COMPANY_NAME_VALUE']) != '') {
            $ob['COMPANY'] = $ob['PROPERTY_FULL_COMPANY_NAME_VALUE'];
        }
        elseif (trim($ob['PROPERTY_IP_FIO_VALUE']) != '') {
            $ob['COMPANY'] = 'ИП ' . $ob['PROPERTY_IP_FIO_VALUE'];
        }
        $arResult['PARTNER_LIST'][$ob['PROPERTY_USER_VALUE']] = $ob;
    }
}

//Список ТК
$arResult['TRANSPORT_LIST'] = array();
if (sizeof($transportIds) > 0) {
    $res = $obElement->GetList(
        array('NAME' => 'ASC'),
        array(
            'IBLOCK_ID' => rrsIblock::getIBlockId('transport_profile'),
            'ACTIVE' => 'Y',
            'PROPERTY_USER' => array_keys($transportIds),
        ),
        false,
        false,
        array(
            'ID',
            'PROPERTY_FULL_COMPANY_NAME',
            'PROPERTY_IP_FIO',
            'PROPERTY_USER'
        )
    );

    while ($ob = $res->Fetch()) {
        if (trim($ob['PROPERTY_FULL_COMPANY_NAME_VALUE']) != '') {
            $ob['COMPANY'] = $ob['PROPERTY_FULL_COMPANY_NAME_VALUE'];
        }
        elseif (trim($ob['PROPERTY_IP_FIO_VALUE']) != '') {
            $ob['COMPANY'] = 'ИП ' . $ob['PROPERTY_IP_FIO_VALUE'];
        }
        $arResult['TRANSPORT_LIST'][$ob['PROPERTY_USER_VALUE']] = $ob;
    }
}

$arResult['STAGE_LIST'] = rrsIblock::getPropListKey('deals_deals', 'STAGE');

foreach ($arResult['STAGE_LIST'] as $key => $stage) {
    if (!in_array($stage['ID'], array_keys($stageIds))) {
        unset($arResult['STAGE_LIST'][$key]);
    }
}

$arResult['SHOW_FORM'] = (
    !empty($arResult['CULTURE_LIST'])           ||
    !empty($arResult['CLIENT_LIST'])            ||
    !empty($arResult['FARMER_LIST'])            ||
    !empty($arResult['PARTNER_LIST'])           ||
    !empty($arResult['TRANSPORT_LIST'])         ||
    !empty($arResult['STAGE_LIST'])
);
?>