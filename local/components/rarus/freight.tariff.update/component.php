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
    'TARIFF_LIST'   => [],
    'ERROR_MSG'     => null,
];


try {

    // Инициализация
    $this->init();


    if(!empty($_GET['AJAX']) && $_GET['AJAX'] == 'Y') {
        include ('ajax.php');
    }



    // Список возможных тарифов
    $arResult['TARIFF_LIST'] = $this->getListUniqueTariff();


    /**
     * Обработка всех изменений
     */
    if(!empty($_POST['SAVE_ALL_TARIFF']) && $_POST['SAVE_ALL_TARIFF'] == 'Y') {

        try {

            // Запуск транзакции
            $DB->StartTransaction();

            // Проверяем сессию
            if(!check_bitrix_sessid()) {
                throw new Exception('Ваша сессия истекла');
            }

            // Обходим тарифы
            foreach ($arResult['TARIFF_LIST'] as $arTariff) {

                $sKeyFrom = $arTariff['KM_FROM'];
                $sKeyTo = $arTariff['KM_TO'];

                if (isset($_POST['KM_FROM'][$sKeyFrom][$sKeyTo]) && isset($_POST['KM_TO'][$sKeyFrom][$sKeyTo])) {

                    // Валидация полей
                    $sFrom      = trim($_POST['KM_FROM'][$sKeyFrom][$sKeyTo]);
                    $sTo        = trim($_POST['KM_TO'][$sKeyFrom][$sKeyTo]);
                    $sDays      = trim($_POST['DAYS'][$sKeyFrom][$sKeyTo]);
                    $sTariff    = trim($_POST['TARIFF_AU'][$sKeyFrom][$sKeyTo]);

                    if (strlen($sFrom) < 1) {
                        throw new Exception('Не заполнено поле "Расстояние от" "' . $sKeyFrom . '-' . $sKeyTo . '"');
                    }

                    if (empty($sTo)) {
                        throw new Exception('Не заполнено поле "Расстояние до" "' . $sKeyFrom . '-' . $sKeyTo . '"');
                    }

                    /*if (empty($sDays)) {
                        throw new Exception('Не заполнено поле "Кол-во дней в рейсе" "' . $sKeyFrom . '-' . $sKeyTo . '"');
                    }*/

                    /*if (empty($sTariff)) {
                        throw new Exception('Не заполнено поле "Тарифная ставка" "' . $sKeyFrom . '-' . $sKeyTo . '"');
                    }*/

                    // Обновляем/Сохраняем тариф
                    if(!$this->persistTariff($sKeyFrom, $sKeyTo, $sFrom, $sTo, $sDays, $sTariff)) {
                        throw new Exception('Ошибка сохранения тарифов для расстояния "' . $sKeyFrom . '-' . $sKeyTo . '"');
                    }

                } elseif(!empty($_POST['REMOVE_TARIFF']) && in_array($sKeyFrom . '_' . $sKeyTo, $_POST['REMOVE_TARIFF'])) {

                    // Удаляем тариф
                    if(!$this->removeTariff($sKeyFrom, $sKeyTo)) {
                        throw new Exception('Ошибка удаления тарифов для расстояния "' . $sKeyFrom . '-' . $sKeyTo . '"');
                    }
                }
            }

            // Добавляем новые тарифы
            if(!empty($_POST['NEW_KM_FROM'])) {

                $iCountNewRows = 0;
                foreach ($_POST['NEW_KM_FROM'] as $sRow => $sKmFrom) {

                    $iCountNewRows++;

                    // Валидация полей
                    $sFrom      = trim($sKmFrom);
                    $sTo        = trim($_POST['NEW_KM_TO'][$sRow]);
                    $sDays      = trim($_POST['NEW_DAYS'][$sRow]);
                    $sTariff    = trim($_POST['NEW_TARIFF_AU'][$sRow]);

                    if (strlen($sFrom) < 1) {
                        throw new Exception('Не заполнено поле "Расстояние от" в строке "' . $iCountNewRows . '" нового тарифа');
                    }

                    if (empty($sTo)) {
                        throw new Exception('Не заполнено поле "Расстояние до" в строке "' . $iCountNewRows . '" нового тарифа');
                    }

                    if (empty($sDays)) {
                        throw new Exception('Не заполнено поле "Кол-во дней в рейсе" в строке "' . $iCountNewRows . '" нового тарифа');
                    }

                    /*if (empty($sTariff)) {
                        throw new Exception('Не заполнено поле "Тарифная ставка" в строке "' . $iCountNewRows . '" нового тарифа');
                    }*/

                    // Добавляем новый тариф
                    if(!$this->persistTariff($sFrom, $sTo, $sFrom, $sTo, $sDays, $sTariff)) {
                        throw new Exception('Ошибка добавления новых тарифов для расстояния "' . $sFrom . '-' . $sTo . '"');
                    }
                }
            }

            // Обновляем список тарифов после изменения
            $arResult['TARIFF_LIST'] = $this->getListUniqueTariff();

            // Сохранение данных
            $DB->Commit();

        } catch (Exception $ex) {
            // Откат изменений
            $DB->Rollback();
            // Прокидываем исключение выше
            throw $ex;
        }
    }

} catch (Exception $e) {
    $arResult['ERROR_MSG'] = $e->getMessage();
}


$this->IncludeComponentTemplate();

