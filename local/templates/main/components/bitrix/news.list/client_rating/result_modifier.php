<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
if (sizeof($arResult['ITEMS']) > 0) {
    $arItem = reset($arResult['ITEMS']);
    $arResult['RATING'] = array(
        'REC' => number_format($arItem['PROPERTIES']['REC']['VALUE'], 2, '.', ''),
        'LAB' => number_format($arItem['PROPERTIES']['LAB']['VALUE'], 2, '.', ''),
        'PAY' => number_format($arItem['PROPERTIES']['PAY']['VALUE'], 2, '.', ''),
        'RATE' => number_format($arItem['PROPERTIES']['RATING']['VALUE'], 2, '.', '')
    );
}
else {
    $arResult['RATING'] = array('REC' => '10.00', 'LAB' => '10.00', 'PAY' => '10.00', 'RATE' => '10.00');
}
?>