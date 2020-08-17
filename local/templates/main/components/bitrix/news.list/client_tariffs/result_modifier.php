<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
$tariffs = model::getAgrohelperTariffs();
$arResult['GROUP_CULTURES'] = rrsIblock::getElementList(rrsIblock::getIBlockId('cultures_groups'));
$arResult['TARIFFS'] = array();

$arItems = array();
foreach ($arResult['ITEMS'] as $arItem) {
    if (intval($arItem['PROPERTIES']['TYPE']['VALUE']) > 0 && intval($arItem['PROPERTIES']['TARIF_ID']['VALUE']) > 0) {
        $arItems[$arItem['PROPERTIES']['TYPE']['VALUE']][$arItem['PROPERTIES']['TARIF_ID']['VALUE']] = array(
            'ID' => $arItem['ID'],
            'VALUE' => $arItem['PROPERTIES']['TARIF']['VALUE']
        );
    }
}

foreach ($arResult['GROUP_CULTURES'] as $group) {
    foreach ($tariffs as $key => $tariff) {
        if (isset($arItems[$group['ID']][$key])) {
            $id = $arItems[$group['ID']][$key]['ID'];
            $val = $arItems[$group['ID']][$key]['VALUE'];
        }
        else {
            $id = 0;
            $val = $tariff['TARIF'];
        }
        $arResult['TARIFFS'][$group['ID']][$key] = array(
            'ID' => $id,
            'NAME' => $tariff['NAME'],
            'VALUE' => $val,
            'MIN' => round($tariff['TARIF'] * (1. - 0.01 * $arParams['MIN_TARIFF'])),
            'MAX' => round($tariff['TARIF'] * (1. + 0.01 * $arParams['MAX_TARIFF'])),
        );
    }
}
?>