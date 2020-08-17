<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();?>
<?
$arResult = $templateData;
if (sizeof($arResult['ITEMS']) > 0 && $arParams['AUTO_READ']) {
    $el = new CIBlockElement;
    foreach ($arResult['ITEMS'] as $arItem) {
        if ($arItem['PROPERTIES']['READ']['VALUE'] == 'N') {
            CIBlockElement::SetPropertyValuesEx($arItem['ID'], $arParams['IBLOCK_ID'], array('READ' => 'Y'));
            $res = $el->Update($arItem['ID'], array('TIMESTAMP_X' => date('d.m.Y H:i:s')));
        }
    }
}
?>