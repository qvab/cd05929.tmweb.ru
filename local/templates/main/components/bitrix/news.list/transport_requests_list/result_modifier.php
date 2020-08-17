<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();?>
<?
$arResult['AUTOPARK'] = transport::getAutoparkList($USER->GetID());
if (is_array($arResult['AUTOPARK']) && sizeof($arResult['AUTOPARK']) > 0) {
    $arResult['CULTURE_LIST'] = rrsIblock::getElementList(rrsIblock::getIBlockId('cultures'));
    $arResult['TRANSPORT_LIST'] = rrsIblock::getElementList(rrsIblock::getIBlockId('transport_type'));

    $arResult['COMMISSION'] = rrsIblock::getConst('commission_transport');

    $requestWarehouseIds = $offerWarehouseIds = array();
    foreach ($arResult["ITEMS"] as $arItem) {
        $requestWarehouseIds[$arItem['PROPERTIES']['CLIENT_WAREHOUSE']['VALUE']] = true;
        $offerWarehouseIds[$arItem['PROPERTIES']['FARMER_WAREHOUSE']['VALUE']] = true;
    }

    if (is_array($requestWarehouseIds) && sizeof($requestWarehouseIds) > 0) {
        $arResult['CLIENT_WAREHOUSES_LIST'] = client::getWarehouseParamsList(array_keys($requestWarehouseIds));
    }
    if (is_array($offerWarehouseIds) && sizeof($offerWarehouseIds) > 0) {
        $arResult['FARMER_WAREHOUSES_LIST'] = farmer::getWarehouseParamsList(array_keys($offerWarehouseIds));
    }

    $arResult['MATRIX'] = array();
    if (is_array($arResult['FARMER_WAREHOUSES_LIST']) && sizeof($arResult['FARMER_WAREHOUSES_LIST']) > 0) {
        foreach ($arResult['FARMER_WAREHOUSES_LIST'] as $wh) {
            foreach ($arResult['AUTOPARK'] as $ap) {
                $arResult['MATRIX'][$wh['ID']][$ap['ID']] = rrsIblock::getRoute($ap['MAP'], $wh['MAP']);
            }
        }

        $arResult['MIN_ROUTE'] = array();
        foreach ($arResult['MATRIX'] as $whKey => $arWH) {
            $min = 10000;
            foreach ($arWH as $key => $distanse) {
                if ($distanse < $min) {
                    $arResult['MIN_ROUTE'][$whKey] = array('AP_ID' => $key, 'ROUTE' => $distanse);
                    $min = $distanse;
                }
            }
        }

        $limit = rrsIblock::getConst('limit_transport');

        foreach ($arResult["ITEMS"] as $key => $arItem) {
            if ($arResult['MIN_ROUTE'][$arItem['PROPERTIES']['FARMER_WAREHOUSE']['VALUE']]['ROUTE'] > $limit) {
                unset($arResult["ITEMS"][$key]);
            }
        }

        if (sizeof($arResult["ITEMS"]) > 0) {
            foreach ($arResult["ITEMS"] as $arItem) {
                $cwh = $arItem['PROPERTIES']['CLIENT_WAREHOUSE']['VALUE'];
                $fwh = $arItem['PROPERTIES']['FARMER_WAREHOUSE']['VALUE'];
                $tap = $arResult['MIN_ROUTE'][$fwh]['AP_ID'];
                $arResult['MIN_ROUTE'][$cwh]['ROUTE'] = rrsIblock::getRoute($arResult['AUTOPARK'][$tap]['MAP'], $arResult['CLIENT_WAREHOUSES_LIST'][$cwh]['MAP']);
            }
        }
    }
}

$partnerIds = array();
foreach ($arResult["ITEMS"] as $arItem) {
    if (intval($arItem['PROPERTIES']['PARTNER']['VALUE']) > 0) {
        $partnerIds[$arItem['PROPERTIES']['PARTNER']['VALUE']] = true;
    }
}

if (is_array($partnerIds) && sizeof($partnerIds) > 0) {
    $res = CIBlockElement::GetList(
        array('ID' => 'ASC'),
        array(
            'IBLOCK_ID' => rrsIblock::getIBlockId('partner_profile'),
            'ACTIVE' => 'Y',
            'PROPERTY_USER' => array_keys($partnerIds),
        ),
        false,
        false,
        array(
            'ID',
            'NAME',
            'PROPERTY_USER',
            'PROPERTY_FULL_COMPANY_NAME',
        )
    );
    while ($ob = $res->Fetch()) {
        $arResult['PARTNERS'][$ob['PROPERTY_USER_VALUE']] = $ob;
    }

    $res = CIBlockElement::GetList(
        array('ID' => 'ASC'),
        array(
            'IBLOCK_ID' => rrsIblock::getIBlockId('transport_partner_link'),
            'ACTIVE' => 'Y',
            'PROPERTY_PARTNER_ID' => array_keys($partnerIds),
            'PROPERTY_VERIFIED' => rrsIblock::getPropListKey('transport_partner_link', 'VERIFIED', 'yes'),
            'PROPERTY_USER_ID' => $USER->GetID()
        ),
        false,
        false,
        array(
            'ID',
            'NAME',
            'PROPERTY_PARTNER_ID',
            //'PROPERTY_PARTNER_LINK_DOC',
        )
    );
    while ($ob = $res->Fetch()) {
        $arResult['LINKED_PARTNERS'][$ob['PROPERTY_PARTNER_ID_VALUE']] = $ob;
    }
}
?>