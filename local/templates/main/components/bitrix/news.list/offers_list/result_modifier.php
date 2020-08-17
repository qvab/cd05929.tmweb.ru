<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
    <?
    $arResult['CULTURE_LIST'] = rrsIblock::getElementList(rrsIblock::getIBlockId('cultures'));
    $arResult['OFFER_PARAMS'] = farmer::getParamsList($arResult['ELEMENTS']);
    $arResult['GRAPH_DATA'] = array();

    $res = CIBlockElement::GetList(
        array('SORT' => 'ASC', 'ID' => 'ASC'),
        array('IBLOCK_ID' => rrsIblock::getIBlockId('quality'), 'ACTIVE' => 'Y'),
        false,
        false,
        array('ID', 'NAME', 'PROPERTY_UNIT')
    );
    while ($ob = $res->Fetch()) {
        $arResult['UNIT_INFO'][$ob['ID']] = $ob['PROPERTY_UNIT_VALUE'];
    }

    $res = CIBlockElement::GetList(
        array('SORT' => 'ASC', 'ID' => 'ASC'),
        array('IBLOCK_ID' => rrsIblock::getIBlockId('basis_values'), 'ACTIVE' => 'Y'),
        false,
        false,
        array('ID', 'NAME', 'PROPERTY_QUALITY', 'PROPERTY_CULTURE')
    );
    while ($ob = $res->Fetch()) {
        foreach ($ob['PROPERTY_CULTURE_VALUE'] as $culture_id) {
            $arResult['LBASE_INFO'][$culture_id][$ob['PROPERTY_QUALITY_VALUE']][$ob['ID']] = $ob['NAME'];
        }
    }

    $res = CIBlockElement::GetList(
        array('ID' => 'DESC'),
        array('IBLOCK_ID' => rrsIblock::getIBlockId('characteristics'), 'ACTIVE' => 'Y'),
        false,
        false,
        array('ID', 'NAME', 'PROPERTY_CULTURE', 'PROPERTY_QUALITY', 'PROPERTY_QUALITY.NAME')
    );
    while ($ob = $res->Fetch()) {
        $arResult['PARAMS_INFO'][$ob['PROPERTY_CULTURE_VALUE']][$ob['PROPERTY_QUALITY_VALUE']] = array(
            'ID' => $ob['ID'],
            'QUALITY_NAME' => $ob['PROPERTY_QUALITY_NAME']
        );
    }

    $warehousesIds = array();
    foreach ($arResult["ITEMS"] as $arItem) {
        $warehousesIds[$arItem['PROPERTIES']['WAREHOUSE']['VALUE']] = true;
    }
    $arResult['WAREHOUSES_LIST'] = farmer::getWarehouseParamsList(array_keys($warehousesIds));

    $res = CIBlockElement::GetList(
        array('ID' => 'DESC'),
        array('IBLOCK_ID' => rrsIblock::getIBlockId('deals_deals'), 'ACTIVE' => 'Y', 'PROPERTY_OFFER' => $arResult['ELEMENTS']),
        false,
        false,
        array('ID', 'NAME', 'DATE_CREATE', 'PROPERTY_VOLUME', 'PROPERTY_STATUS', 'PROPERTY_OFFER')
    );
    while ($ob = $res->Fetch()) {
        $arResult['DEALS'][$ob['PROPERTY_OFFER_VALUE']][] = array(
            'ID' => $ob['ID'],
            'DATE_CREATE' => $ob['DATE_CREATE'],
            'VOLUME' => $ob['PROPERTY_VOLUME_VALUE'],
            'STATUS' => $ob['PROPERTY_STATUS_VALUE']
        );
    }

    //отдельно сохраняем данные складов и культур для передачи имен складов в component_epilog
    $this->__component->arResult["CACHED_WAREHOUSES_LIST"] = array();
    foreach($arResult['WAREHOUSES_LIST'] as $cur_id => $cur_data){
        $this->__component->arResult["CACHED_WAREHOUSES_LIST"][$cur_id] = $cur_data['NAME'];
    }
    $this->__component->SetResultCacheKeys(array("CACHED_WAREHOUSES_LIST"));
    $this->__component->arResult["CACHED_CULTURES_LIST"] = array();
    foreach($arResult['CULTURE_LIST'] as $cur_id => $cur_data){
        $this->__component->arResult["CACHED_CULTURES_LIST"][$cur_id] = $cur_data['NAME'];
    }
    $this->__component->SetResultCacheKeys(array("CACHED_CULTURES_LIST"));

    unset($warehousesIds, $res, $ob);
    $arResult['COUNTER_REQUEST'] = farmer::getCounterRequestsData($arResult['ELEMENTS']);
?>