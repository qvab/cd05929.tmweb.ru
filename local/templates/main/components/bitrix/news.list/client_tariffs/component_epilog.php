<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
if ($_POST && intval($arParams['CLIENT_ID']) > 0) {
    $el = new CIBlockElement;
    foreach ($_POST['tariffs'] as $gr => $arTariffs) {
        foreach ($arTariffs as $id => $arVal) {
            $arLoadProductArray = Array(
                'IBLOCK_ID' => $arParams['IBLOCK_ID'],
                'NAME' => 'тариф',
                'ACTIVE' => 'Y',
                'PROPERTY_VALUES' => array(
                    'USER' => $arParams['CLIENT_ID'],
                    'TYPE' => $gr,
                    'TARIF_ID' => $id,
                    'TARIF' => $arVal['VALUE'],
                ),
            );
            if (intval($arVal['ID']) > 0) {
                $el->Update($arVal['ID'], $arLoadProductArray);
            }
            else {
                $el->Add($arLoadProductArray);
            }
        }
    }
    LocalRedirect($arParams['LIST_URL']);
}
?>