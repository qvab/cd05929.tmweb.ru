<? if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)die();
/**
 * @author: Vitaly Melnik <vitali@rarus.ru>
 */

/**
 * Результирующий массив
 */
$arAjax = [
    'result'        => false,
    'errorMessage'  => null,
    'htmlRowTariff' => null,
    'from'          => null,
    'to'            => null,
];

try {

    /**
     * Обработка обновления тарифа
     */
    if(!empty($_GET['UPDATE_TARIFF']) && $_GET['UPDATE_TARIFF'] == 'Y') {

        // Проверяем сессию
        if(!check_bitrix_sessid()) {
            throw new Exception('Ваша сессия истекла');
        }

        // Текущий интервал тарифа
        $sKeyFrom   = trim($_GET['KEY_FROM']);
        $sKeyTo     = trim($_GET['KEY_TO']);

        if(strlen($sKeyFrom) < 1) {
            throw new Exception('Не удалось определить текущее значение "Расстояние от"');
        }

        if(strlen($sKeyTo) < 1) {
            throw new Exception('Не удалось определить текущее значение "Расстояние до"');
        }

        // Новые значения тарифа
        $sFrom      = trim($_GET['FROM']);
        $sTo        = trim($_GET['TO']);
        $sDays      = trim($_GET['DAYS']);
        $sTariff    = trim($_GET['TARIFF']);

        if (strlen($sFrom) < 1) {
            throw new Exception('Не заполнено поле "Расстояние от"');
        }

        if (empty($sTo)) {
            throw new Exception('Не заполнено поле "Расстояние до"');
        }

        if (empty($sDays)) {
            throw new Exception('Не заполнено поле "Кол-во дней в рейсе"');
        }

        if (empty($sTariff)) {
            throw new Exception('Не заполнено поле "Тарифная ставка"');
        }

        // Обновляем
        if(!$this->persistTariff($sKeyFrom, $sKeyTo, $sFrom, $sTo, $sDays, $sTariff)) {
            throw new Exception('Ошибка обновления тарифа для расстояния "' . $sKeyFrom . '-' . $sKeyTo . '"');
        }

        $arAjax['htmlRowTariff'] = CFreightTariffUpdate::getHtmlRowTariff($sFrom, $sTo, $sDays, $sTariff);

        $arAjax['from'] = $sFrom;
        $arAjax['to'] = $sTo;

    }
    /**
     * Обработка добавления нового тарифа
     */
    elseif (!empty($_GET['ADD_TARIFF']) && $_GET['ADD_TARIFF'] == 'Y') {

        // Проверяем сессию
        if(!check_bitrix_sessid()) {
            throw new Exception('Ваша сессия истекла');
        }

        // Значения нового тарифа
        $sFrom      = trim($_GET['FROM']);
        $sTo        = trim($_GET['TO']);
        $sDays      = trim($_GET['DAYS']);
        $sTariff    = trim($_GET['TARIFF']);

        if (strlen($sFrom) < 1) {
            throw new Exception('Не заполнено поле "Расстояние от"');
        }

        if (empty($sTo)) {
            throw new Exception('Не заполнено поле "Расстояние до"');
        }

        if (empty($sDays)) {
            throw new Exception('Не заполнено поле "Кол-во дней в рейсе"');
        }

        if (empty($sTariff)) {
            throw new Exception('Не заполнено поле "Тарифная ставка"');
        }

        // Добавляем
        if(!$this->persistTariff($sFrom, $sFrom, $sFrom, $sTo, $sDays, $sTariff)) {
            throw new Exception('Ошибка добавления тарифа для расстояния "' . $sFrom . '-' . $sFrom . '"');
        }

        $arAjax['htmlRowTariff'] = CFreightTariffUpdate::getHtmlRowTariff($sFrom, $sTo, $sDays, $sTariff);

        $arAjax['from'] = $sFrom;
        $arAjax['to'] = $sTo;
    }


    $arAjax['result'] = true;

} catch (Exception $e) {
    $arAjax['result'] = false;
    $arAjax['errorMessage'] = $e->getMessage();
}

// Сбрасываем буфер
$GLOBALS['APPLICATION']->RestartBuffer();
// Очищаем остатки
while(@ob_end_clean());
// Заголовки json
header("Content-type: application/json; charset=utf-8");
echo json_encode($arAjax);
die();