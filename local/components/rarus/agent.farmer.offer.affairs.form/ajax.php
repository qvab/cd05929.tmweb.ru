<? if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)die();
/**
 * @author: Vitaly Melnik <vitali@rarus.ru>
 */

/**
 * Результирующий массив
 */
$arAjax = [
    'result'    => false,
    'html'      => null,
    'data'      => [],
    'errorsMessage' => [
        'date-affair'       => null,
        'farmer-volume'     => null,
        'expected-price'    => null,
        'all'               => null,
    ],
];

try {

    /**
     * Обработка нового дела
     */
    if(!empty($_GET['ADD_AFFAIR']) && $_GET['ADD_AFFAIR'] == 'Y') {

        try {

            // Запуск транзакции
            $DB->StartTransaction();

            // Проверяем сессию
            if(!check_bitrix_sessid()) {
                throw new Exception('Ваша сессия истекла');
            }

            // ИД товара
            $iOfferId = intval($_GET['OFFER_ID']);
            if(empty($iOfferId)) {
                throw new Exception('Не удалось получить ИД товара');
            }

            // Дата действия
            $sDateAffair = trim($_GET['DATE_AFFAIR']);
            if(empty($sDateAffair)) {
                $arAjax['errorsMessage']['date-affair'] = 'Пожалуйста, выберите дату следующего действия';
                throw new Exception('Не указана дата действия');
            }

            // Текущая дата
            $obDateCurrent = new DateTime(date('Y-m-d'));
            $arDateAffair = explode('.', $sDateAffair);
            $obDateAffair = new DateTime($arDateAffair[2] . '-' . $arDateAffair[1] . '-'. $arDateAffair[0]);
            if($obDateCurrent > $obDateAffair) {
                $arAjax['errorsMessage']['date-affair'] = 'Дата действия меньше текущей';
                throw new Exception('Укажите правильную дату');
            }

            // Объем у фермера
            $sFarmerVolume = trim($_GET['FARMER_VOLUME']);
            if(empty($sFarmerVolume)) {
                $arAjax['errorsMessage']['farmer-volume'] = 'Пожалуйста, укажите объем продукции';
                throw new Exception('Не задан объем наличия у поставщика');
            }

            // Ожидаемая цена
            $sExpectedPrice = trim($_GET['EXPECTED_PRICE']);
            if(empty($sExpectedPrice)) {
                $arAjax['errorsMessage']['expected-price'] = 'Пожалуйста, укажите ожидаемую цену';
                throw new Exception('Не задана цена');
            }

            // Комментарий
            $sComment = trim($_GET['COMMENT']);

            $GLOBALS['APPLICATION']->RestartBuffer();
            
            // Добавляем
            global $USER;
            $iAffairId = CAffair::Add('OFFER', $iOfferId, $sDateAffair, $sFarmerVolume, $sExpectedPrice, $sComment, null, $USER->GetID());
            if(empty($iAffairId)) {
                throw new Exception('Ошибка записи дела');
            }

            // Сохранение данных
            $DB->Commit();

        } catch (Exception $ex) {
            // Откат изменений
            $DB->Rollback();
            // Прокидываем исключение выше
            throw $ex;
        }
    }
    $arAjax['href']   = '/profile/agent_affairs/?uid=' . $_GET['FARMER_ID'];
    $arAjax['result'] = true;

} catch (Exception $e) {
    $arAjax['result'] = false;
    $arAjax['errorsMessage']['all'] = $e->getMessage();
}


// Сбрасываем буфер
$GLOBALS['APPLICATION']->RestartBuffer();
// Очищаем остатки
while(@ob_end_clean());
// Заголовки json
header("Content-type: application/json; charset=utf-8");
echo json_encode($arAjax);
die();