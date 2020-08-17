<?php

/*
 * Добавляет новые значения параметров в ИБ "Списки"->"Переменные данные"
 */


// Подключение Битрикса
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

// Проверка на админа
if(!$USER->IsAdmin())
    die('You have no access!');

// Подключаем необходимые модули
if(!CModule::IncludeModule("iblock"))
    die('Module "IBlock" not found!');


/** Конвертор ошибки в исключение */
if(!function_exists('exception_error_handler')) {
    function exception_error_handler($errno, $errstr, $errfile, $errline ) {
        throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
    }
}

// Выставляем лимит
set_time_limit('300');


// Создаем объекты
$obIBlockElement = new CIBlockElement();


// Подвешиваем обработку ошибок
set_error_handler('exception_error_handler', E_RECOVERABLE_ERROR);

// Запуск транзакции
$DB->StartTransaction();


$arElements = [
    'REWARD_PERCENT_AGENT' => [
        'NAME'  => 'Вознаграждение агенту АП от суммы вознаграждения организатора АП [%]',
        'VALUE' => 0.3,
    ],
    'REWARD_PERCENT_OPERATOR_AH' => [
        'NAME'  => 'Вознаграждение оператора АХ от организатора АП [%]',
        'VALUE' => 15,
    ],
    'REWARD_PERCENT_ORGANIZER' => [
        'NAME'  => 'Вознаграждение организатору покупателя от вознаграждения организатора АП (если они разные) [%]',
        'VALUE' => 25,
    ],
    'REWARD_PERCENT_TRANSPORTATION_AGENT' => [
        'NAME'  => 'Вознаграждение агенту АП от вознаграждения организатора АП [%]',
        'VALUE' => 2,
    ],
    'REWARD_PERCENT_TRANSPORTATION_OPERATOR_AH' => [
        'NAME'  => 'Вознаграждение оператору АХ от вознаграждения организатора АП [%]',
        'VALUE' => 25,
    ],
];



try {

    foreach ($arElements as $sCode => $arItem) {

        $arEl = $obIBlockElement->GetList(
            [],
            [
                'IBLOCK_ID' => getIBlockID('lists', 'data'),
                'CODE'      => $sCode,
            ],
            false,
            ['nTopCount' => 1],
            ['ID',]
        )->Fetch();

        $arLoad = [
            'IBLOCK_ID' => getIBlockID('lists', 'data'),
            'NAME'      => $arItem['NAME'],
            'CODE'      => $sCode,
            'PROPERTY_VALUES' => [
                'VALUE' => $arItem['VALUE'],
            ]
        ];

        if(empty($arEl['ID'])) {

            $ID = $obIBlockElement->Add($arLoad);
            if(!$ID) {
                throw new Exception('Ошибка добавления элемента: ' . $obIBlockElement->LAST_ERROR);
            }
        } else {

            $ID = $obIBlockElement->Update($arEl['ID'], $arLoad);
            if(!$ID) {
                throw new Exception('Ошибка обновления элемента: ' . $obIBlockElement->LAST_ERROR);
            }
        }
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