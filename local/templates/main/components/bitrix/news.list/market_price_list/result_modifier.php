<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
$arResult['CULTURE_LIST'] = rrsIblock::getElementList(rrsIblock::getIBlockId('cultures'), 'NAME');
$arResult['TYPE_LIST'] = rrsIblock::getElementList(rrsIblock::getIBlockId('company_type'), 'SORT');

$modelList = array();
foreach ($arResult['ITEMS'] as $arItem) {
    $modelList[$arItem['PROPERTIES']['CULTURE']['VALUE']][$arItem['PROPERTIES']['TYPE']['VALUE']][] = array(
        'ID' => $arItem['ID'],
        'NAME' => $arItem['NAME'],
        'PRICE' => $arItem['PROPERTIES']['MARKET_COST']['VALUE'],
        'DATE' => $arItem['TIMESTAMP_X']
    );
}

$arPrice = array();
foreach ($arResult['CULTURE_LIST'] as $culture) {
    if (is_array($modelList[$culture['ID']])) {
        $typeList = array();
        foreach ($arResult['TYPE_LIST'] as $type) {
            if (is_array($modelList[$culture['ID']][$type['ID']])) {
                $typeList[] = array(
                    'ID' => $type['ID'],
                    'NAME' => $type['NAME'],
                    'MODEL' => $modelList[$culture['ID']][$type['ID']]
                );
            }
        }
        $tmp = array(
            'ID' => $culture['ID'],
            'NAME' => $culture['NAME'],
            'TYPE' => $typeList
        );
        $arPrice[] = $tmp;
    }
}

$arResult['PRICE_LIST'] = $arPrice;

$obj = $this->getComponent();
$obj->SetResultCacheKeys(array('PRICE_LIST'));
?>