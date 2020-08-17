<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
$obElement  = new CIBlockElement;

$clientIds = $farmerIds = $partnerIds = $transportIds = array();

foreach ($arResult['ITEMS'] as $arItem) {
    if (!empty($arItem['PROPERTIES']['CLIENT']['VALUE'])) {
        $clientIds[$arItem['PROPERTIES']['CLIENT']['VALUE']] = true;
    }

    if (!empty($arItem['PROPERTIES']['FARMER']['VALUE'])) {
        $farmerIds[$arItem['PROPERTIES']['FARMER']['VALUE']] = true;
    }

    if (!empty($arItem['PROPERTIES']['PARTNER']['VALUE'])) {
        $partnerIds[$arItem['PROPERTIES']['PARTNER']['VALUE']] = true;
    }

    if (!empty($arItem['PROPERTIES']['TRANSPORT']['VALUE'])) {
        $transportIds[$arItem['PROPERTIES']['TRANSPORT']['VALUE']] = true;
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
?>