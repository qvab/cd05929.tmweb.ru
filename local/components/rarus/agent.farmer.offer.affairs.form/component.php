<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
/**
 * @author: Vitaly Melnik <vitali@rarus.ru>
 */
/** @var CBitrixComponent $this */
/** @var array $arParams */
/** @var array $arResult */
/** @var string $componentPath */
/** @var string $componentName */
/** @var string $componentTemplate */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */
/** @var CFreightTariffUpdate $this */


/**
 * Результирующий массив
 */
$arResult = [
    'ITEMS'     => [],
    'ERROR_MSG' => null,
];




try {

    // Проверяем параметры
    $arParams['FARMER_ID'] = intval($arParams['FARMER_ID']);
    if(empty($arParams['FARMER_ID'])) {
        throw new Exception('Не задан поставщик');
    }

    // Текущая дата
    $arParams['CUR_DATE'] = new DateTime('now');


    // Обработчик AJAX
    if(!empty($_GET['AJAX']) && $_GET['AJAX'] == 'Y') {
        include ('ajax.php');
    }

    $arResult['OFFERS'] = farmer::getOfferListByUser($arParams['FARMER_ID']);


} catch (Exception $e) {
    $arResult['ERROR_MSG'] = $e->getMessage();
}

$this->IncludeComponentTemplate();