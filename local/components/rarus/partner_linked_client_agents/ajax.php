<? if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)die();
/**
 * @author: Vitaly Melnik <vitali@rarus.ru>
 */

/**
 * Результирующий массив
 */
$arAjax = [
    'result'    => false,
    'data'      => [],
    'errorMsg'  => null,
];

// Объекты

$obEl = new CIBlockElement;

try {

    /**
     * Обработка нового дела
     */
    if(!empty($_GET['SAVE_OPTIONS']) && $_GET['SAVE_OPTIONS'] == 'Y') {

        try {

            // Запуск транзакции
            $DB->StartTransaction();

            // Проверяем сессию
            if(!check_bitrix_sessid()) {
                throw new Exception('Ваша сессия истекла');
            }

            $iAgentId = intval($_GET['AGENT_ID']);
            if(empty($iAgentId)) {
                throw new Exception('Не задан ИД агента');
            }

            // Находим элемент профиля агента
            $arProfile = $obEl->GetList(
                ['ID' => 'ASC'],
                [
                    'ACTIVE'        => 'Y',
                    'IBLOCK_ID'     => getIBlockID('agent', 'client_agent_profile'),
                    'PROPERTY_USER' => $iAgentId,
                ],
                false,
                ['nTopCount' => 1,],
                ['ID',]
            )->Fetch();

            if(empty($arProfile['ID'])) {
                throw new Exception('Не удалось получить профиль "Агента покупателя" пользователя['.$iAgentId.']');
            }

            $nPercent = trim($_GET['PERCENT']);
            if(strlen($nPercent) > 0) {
                $nPercent = floatval($_GET['PERCENT']);
            }

            // Обновляем значение св-ва
            $obEl->SetPropertyValuesEx($arProfile['ID'], getIBlockID('agent', 'client_agent_profile'), array('REWARD_PERCENT' => $nPercent));

            $arData = $obEl->GetList(
                [],
                [
                    'ID'        => $arProfile['ID'],
                    'IBLOCK_ID' => getIBlockID('agent', 'client_agent_profile'),
                ],
                false,
                false,
                ['ID', 'PROPERTY_REWARD_PERCENT']
            )->Fetch();

            if($arData['PROPERTY_REWARD_PERCENT_VALUE'] != $nPercent) {
                throw new Exception('Ошибка записи значения поля "Процент вознаграждения" текущее значение = "'.$arData['PROPERTY_REWARD_PERCENT_VALUE'].'", ожидаемое "'.$nPercent.'"');
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

    $arAjax['result'] = true;

} catch (Exception $e) {
    $arAjax['result'] = false;
    $arAjax['errorMsg'] = $e->getMessage();
}


// Сбрасываем буфер
$GLOBALS['APPLICATION']->RestartBuffer();
// Очищаем остатки
while(@ob_end_clean());
// Заголовки json
header("Content-type: application/json; charset=utf-8");
echo json_encode($arAjax);
die();