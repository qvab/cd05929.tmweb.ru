<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
if ($_POST) {
    $el = new CIBlockElement;
    //удаляем тарифы
    if (is_array($_POST['REMOVE_TARIFF']) && sizeof($_POST['REMOVE_TARIFF']) > 0) {
        foreach ($_POST['REMOVE_TARIFF'] as $tariffId) {
            $el->Delete($tariffId);
        }
    }

    //обновляем тарифы
    if (is_array($_POST['tariff']) && sizeof($_POST['tariff']) > 0) {
        foreach ($_POST['tariff'] as $tariffId => $data) {
            CIBlockElement::SetPropertyValuesEx(
                $tariffId,
                $arParams['IBLOCK_ID'],
                array(
                    'KM_FROM' => $data['from'],
                    'KM_TO' => $data['to'],
                    'DAYS' => $data['days'],
                    'TARIF_AH' => $data['val'],
                )
            );
        }
    }

    //добавляем тарифы
    if (is_array($_POST['new_tariff']) && sizeof($_POST['new_tariff']) > 0) {
        foreach ($_POST['new_tariff'] as $data) {
            $arLoadProductArray = Array(
                'IBLOCK_ID' => $arParams['IBLOCK_ID'],
                'NAME' => '['.$data['from'].' - '.$data['to'].']',
                'ACTIVE' => 'Y',
                'PROPERTY_VALUES' => array(
                    'KM_FROM' => $data['from'],
                    'KM_TO' => $data['to'],
                    'DAYS' => $data['days'],
                    'TARIF_AH' => $data['val'],
                ),
            );

            $el->Add($arLoadProductArray);
        }
    }

    global $CACHE_MANAGER;
    $CACHE_MANAGER->ClearByTag("iblock_id_".$arParams['IBLOCK_ID']);

    $arAgrohelperTariffs = model::getAgrohelperTariffs(true);

    LocalRedirect('/admin/tariff/');
}

if ($_GET['renew'] == 'y') {
    $el = new CIBlockElement;
    $ib = rrsIblock::getIBlockId('tariff');

    $arAgrohelperTariffs = model::getAgrohelperTariffs();

    //берем все региональные центры
    $arCenters = array();
    $res = CIBlockElement::GetList(
        array('ID' => 'ASC'),
        array(
            'IBLOCK_ID' => rrsIblock::getIBlockId('regions_centers'),
            'ACTIVE' => 'Y'
        ),
        false,
        false,
        array('ID', 'NAME')
    );
    while ($ob = $res->Fetch()) {
        $arCenters[$ob['ID']] = $ob;
    }

    //берем все действующие тарифы, рассчитанные по ММ
    $arTariffs = array();
    $res = CIBlockElement::GetList(
        array('ID' => 'ASC'),
        array(
            'IBLOCK_ID' => $ib,
        ),
        false,
        false,
        array('ID', 'PROPERTY_CENTER', 'PROPERTY_TARIF_ID')
    );
    while ($ob = $res->Fetch()) {
        if (!$ob['PROPERTY_CENTER_VALUE'] || !$ob['PROPERTY_TARIF_ID_VALUE']) {
            $el->Delete($ob['ID']);
        }
        else {
            $arTariffs[$ob['PROPERTY_CENTER_VALUE']][$ob['PROPERTY_TARIF_ID_VALUE']] = $ob;
        }
    }

    //удалим тарифы не существующих РЦ
    foreach ($arTariffs as $centerId => $arCenterTariffs) {
        if (!in_array($centerId, array_keys($arCenters))) {
            foreach ($arCenterTariffs as $tariff) {
                $el->Delete($tariff['ID']);
            }
        }
    }

    //удаляем тарифы РЦ, которых не существует в справочнике тарифов АХ
    foreach ($arTariffs as $centerId => $arCenterTariffs) {
        foreach ($arCenterTariffs as $tariffId => $tariff) {
            if (!in_array($tariffId, array_keys($arAgrohelperTariffs))) {
                $el->Delete($tariff['ID']);
                unset($arTariffs[$centerId][$tariffId]);
            }
        }
    }

    //добавляем/изменяем тарифы РЦ, которые есть в справочнике тарифов АХ
    foreach ($arCenters as $centerId => $arCenter) {
        $arCenterTariffs = $arTariffs[$centerId];
        foreach ($arAgrohelperTariffs as $tariffId => $agrohelperTariff) {
            if (in_array($tariffId, array_keys($arCenterTariffs))) {
                //редактируем тариф
                CIBlockElement::SetPropertyValuesEx(
                    $arCenterTariffs[$tariffId]['ID'],
                    $ib,
                    array(
                        'KM_FROM' => $agrohelperTariff['FROM'],
                        'KM_TO' => $agrohelperTariff['TO'],
                        'DAYS' => $agrohelperTariff['DAYS'],
                        'TARIF_AU' => $agrohelperTariff['TARIF'],
                        'TARIF' => $agrohelperTariff['TARIF'],

                    )
                );
            }
            else {
                //добавляем тариф
                $arLoadProductArray = Array(
                    'IBLOCK_ID' => $ib,
                    'NAME' => 'тариф',
                    'ACTIVE' => 'Y',
                    'PROPERTY_VALUES'=> array(
                        'CENTER' => $centerId,
                        'KM_FROM' => $agrohelperTariff['FROM'],
                        'KM_TO' => $agrohelperTariff['TO'],
                        'DAYS' => $agrohelperTariff['DAYS'],
                        'TARIF_AU' => $agrohelperTariff['TARIF'],
                        'TARIF' => $agrohelperTariff['TARIF'],
                        'TARIF_ID' => $tariffId
                    ),
                );

                $el->Add($arLoadProductArray);
            }
        }
    }

    global $CACHE_MANAGER;
    $CACHE_MANAGER->ClearByTag("iblock_id_".$ib);

    LocalRedirect('/admin/tariff/');
}
?>