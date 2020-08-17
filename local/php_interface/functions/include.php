<?
/**
 * В этом файле подключаем все функции
 * @author: Vitaly Melnik <vitali@rarus.ru>
 */

/* Отдает ИД ИБ по типу и коду */
require_once(__DIR__.'/Utils/getIBlockID.php');
/*Функиции работы с датами*/
require_once(__DIR__.'/Utils/dateFunctions.php');
/* Функция вывод информации о переменной */
require_once(__DIR__.'/Utils/pre.php');
/* Идентификаторы группы*/
require_once(__DIR__.'/Utils/getGroupIdByRole.php');
/* Дамп переменной в файл*/
require_once(__DIR__.'/Utils/debugLog.php');
/* Дамп переменной в файл*/
require_once(__DIR__.'/Utils/CrmIntegration.php');
?>