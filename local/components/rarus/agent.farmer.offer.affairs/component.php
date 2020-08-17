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


if (!function_exists('getItemHtmlRow')) {
    /**
     * Отдает HTML ноды "Дела"
     * @param $sDateAffair
     * @param $sFarmerVolume
     * @param $sExpectedPrice
     * @param $sComment
     * @return string
     */
    function getItemHtmlRow($sDateAffair, $sFarmerVolume, $sExpectedPrice, $sComment) {

        $sHtml = '<div class="item">';
            $sHtml .= '<div class="field">Дата действия: <b>'.$sDateAffair->format('d.m.Y').'</b></div>';
            $sHtml .= '<div class="field">Объем в наличии у поставщика: <b>'.$sFarmerVolume.'</b></div>';
            $sHtml .= '<div class="field">Ожидаемая цена: <b>'.$sExpectedPrice.'</b></div>';

            if(!empty($sComment)) {
                $sHtml .= '<div class="field">Комментарии для следующего звонка:<div class="comment-content">'.$sComment.'</div></div>';
            }

        $sHtml .= '</div>';

        return $sHtml;
    }
}



try {

    // Проверяем параметры
    $arParams['OFFER_ID'] = intval($arParams['OFFER_ID']);
    if(empty($arParams['OFFER_ID'])) {
        throw new Exception('Не задан ИД товара');
    }

    // Текущая дата
    $arParams['CUR_DATE'] = new DateTime('now');

    // Лимит по умолчанию
    $arParams['LIMIT'] = 1;

    // Обработчик AJAX
    if(!empty($_GET['AJAX']) && $_GET['AJAX'] == 'Y') {
        include ('ajax.php');
    }

    $arOrder = ['UF_DATE_AFFAIR' => 'ASC',];

    $arFilter = [
        'UF_XML_ID'         => $arParams['OFFER_ID'],
        '>=UF_DATE_AFFAIR'  => $arParams['CUR_DATE']->format('d.m.Y'),
    ];

    $arSelect = ['ID', 'UF_DATE_AFFAIR', 'UF_FARMER_VOLUME', 'UF_EXPECTED_PRICE', 'UF_COMMENT',];

    $arResult['DATA'] = CAffair::GetList(['OFFER'], $arOrder, $arFilter, $arSelect, $arParams['LIMIT']);
    // Если нет дел в будущем находим ближайшее к текущей дате #11997
    if(empty($arResult['DATA']['CNT'])) {

        // По хорошему надо использовать в фильтре "LOGIC" => "OR", НО тогда непонятно как быть с сортировкой!
        $arFilter['<=UF_DATE_AFFAIR'] = $arParams['CUR_DATE']->format('d.m.Y');
        unset($arFilter['>=UF_DATE_AFFAIR']);
        // Меняем сортировку
        $arOrder = ['UF_DATE_AFFAIR' => 'DESC',];

        // Выборка прошлых дел
        $arResult['DATA'] = CAffair::GetList(['OFFER'], $arOrder, $arFilter, $arSelect, $arParams['LIMIT'], null, false);
    }

} catch (Exception $e) {
    $arResult['ERROR_MSG'] = $e->getMessage();
}

$this->IncludeComponentTemplate();