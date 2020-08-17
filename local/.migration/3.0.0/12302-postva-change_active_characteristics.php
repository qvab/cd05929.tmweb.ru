<?php

/**
 * диактивируем для характеристик культур  "Цвет", "Запах", "Зараженность вредителями" (из инфоблока не удалять)
 */

// Подключение Битрикса
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

// Проверка на админа
if(!$USER->IsAdmin()) {
    die();
}




/** Конвертор ошибки в исключение */
if(!function_exists('exception_error_handler')) {
    function exception_error_handler($errno, $errstr, $errfile, $errline ) {
        throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
    }
}

// Выставляем лимит
set_time_limit('300');



try {

    $DB->StartTransaction();

    culture::changeActiveToCharByQuality("Цвет", false, true);
    culture::changeActiveToCharByQuality("Запах", false, true);
    culture::changeActiveToCharByQuality("Зараженность вредителями", false, true);

    // Сохранение данных
    $DB->Commit();

}
// Обработка ошибок
catch( Exception $ex ) {
    // Откат изменений
    $DB->Rollback();
    echo 'Завершено с ошибкой: ' . $ex->getMessage();
    die();
}


// Выводим сообщение
echo 'Выполнено успешно! '.date('(H:i:s)');
echo '<br>Файл миграции: '.__FILE__;
