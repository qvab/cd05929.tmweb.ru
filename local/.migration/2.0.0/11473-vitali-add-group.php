<?php

/**
 * Создает группу "Региональные менеджеры"
 * Присваиваем администратору символьный код
 */

// Подключение Битрикса
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

// Проверка на админа
if(!$USER->IsAdmin()) {
    die();
}


/*
 * Описываем группы
 */
$arGroups = [
    'REGIONAL_MANAGERS' => [
        "ACTIVE"        => "Y",
        'C_SORT'        => 390,
        "NAME"          => "Региональные менеджеры",
        "DESCRIPTION"   => "Роль региональный менеджер",
    ],
];



/** Конвертор ошибки в исключение */
if(!function_exists('exception_error_handler')) {
    function exception_error_handler($errno, $errstr, $errfile, $errline ) {
        throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
    }
}

// Выставляем лимит
set_time_limit('300');


// Создаем объекты
$obUser     = new CUser;
$obGroup    = new CGroup;

try {

    $DB->StartTransaction();

    /*
     * Создаем группу
     */
    foreach($arGroups as $sGroupCode => $arGroup) {

        $arGroup['STRING_ID'] = $sGroupCode;

        $arCurGroup = $obGroup->GetList(
            $foo='id',
            $bar='asc',
            array('STRING_ID' => $sGroupCode)
        )->Fetch();


        /**
         * Добавляем/Обновляем
         */
        if(!empty($arCurGroup['ID'])) {
            if(!$obGroup->Update($arCurGroup['ID'], $arGroup)) {
                throw new Exception('Не удалось обновить группу "'.$arGroup['NAME'].'" ['.$sGroupCode.']. Причина: '. $obGroup->LAST_ERROR);
            }
        } else {
            if(!$obGroup->Add($arGroup)) {
                throw new Exception('Не удалось добавить группу "'.$arGroup['NAME'].'" ['.$sGroupCode.']. Причина: '. $obGroup->LAST_ERROR);
            }
        }
    }

    // Присваиваем администратору символьный код
    $arCurGroupAdmin = $obGroup->GetList(
        $foo='id',
        $bar='asc',
        array('ID' => 1,)
    )->Fetch();

    $arCurGroupAdmin['STRING_ID'] = 'ADMIN';

    if(!$obGroup->Update(1, $arCurGroupAdmin)) {
        throw new Exception('Не удалось обновить группу "'.$arCurGroupAdmin['NAME'].'" Причина: '. $obGroup->LAST_ERROR);
    }

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
