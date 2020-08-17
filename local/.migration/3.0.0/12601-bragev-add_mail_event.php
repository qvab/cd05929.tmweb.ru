<?php
/**
 * Добавление почтового шаблона для уведомления о "Деле"
 */

// Подключение Битрикса
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');
global $DB, $USER;
if (!$USER->IsAdmin()) {
    header('HTTP/1.0 403 Forbidden');
    exit;
}


set_time_limit(0);

/**
 * Сайты на которые создаются шаблоны и типы почтовых сообщений
 */
$arSiteLangIds = ['ru'];
$arSiteCodeIds = ['s1'];

$arEventTypes = [];

/**
 * Конфигурация типов почтовых сообщений
 */
$arEventTypes[] =
    [
        'LID'         => $arSiteLangIds,
        'EVENT_NAME'  => 'ADD_NEW_PAIR_DOP',
        'NAME'        => 'Изменение дополнительных опций пар',
        'DESCRIPTION' => implode(
            PHP_EOL,
            [
                'Дополнительные поля, которые можно использовать в шаблоне:',
                '#REQUEST_ID# - идентификатор запроса',
                '#LIST# - список доп опций',
                '#URL# - детальная страница предложения (для перехода)',
                '#EMAIL# - email покупателя',
                '#FIO# - кем было добавлен'
            ]
        ),
    ];



/**
 * Конфигурация шаблонов почтовых сообщений
 */
$arEventMessages = [];

$arEventMessages[] =
    [
        'ACTIVE'      => 'Y',
        'EVENT_NAME'  => 'ADD_NEW_PAIR_DOP',
        'LID'         => $arSiteCodeIds,
        //'LANGUAGE_ID' => 'ru',
        'EMAIL_FROM'  => '#DEFAULT_EMAIL_FROM#',
        'EMAIL_TO'    => '#EMAIL#',
        'SUBJECT'     => 'АГРОХЕЛПЕР: Изменение дополнительных опций пар',
        'BODY_TYPE'   => 'html',
        'MESSAGE'     => implode('<br />', [
            '<img width="251" src="https://agrohelper.ru/upload/medialibrary/62a/mail-logo.png" height="71">',
            '',
            'Здравствуйте!',
            '',
            'Пользователем <a href="#URL#">#FIO#</a>',
            'были изменены #LIST#',
            '',
            'С уважением,',
            'команда&nbsp;<a target="_blank" href="https://agrohelper.ru">АГРОХЕЛПЕР</a>',
            'Тел. +7 909 211-26-86',
            'Email:&nbsp;<a href="mailto:admin@agrohelper.ru">admin@agrohelper.ru</a>',
        ]),
    ];


// ---------------------------------------------------------------------------------------------------------------------

$obEventType    = new CEventType;
$obEventMessage = new CEventMessage;

$DB->StartTransaction();
try {

    /*
     * Создание/обновление типов
     */
    foreach ($arEventTypes AS $arType) {

        // Проходимся по каждому языку в которому должен быть привязан тип
        foreach ($arType['LID'] AS $sSiteLangId) {
            // Создаем копию и выставляем конкретный язык
            $arTypeX = $arType;
            $arTypeX['LID'] = $sSiteLangId;

            // Получаем текущий тип
            $arCurrent = $obEventType->getList(['TYPE_ID' => $arType['EVENT_NAME'], 'LID' => $sSiteLangId])->fetch();

            // Обновляем
            if ($arCurrent['ID']) {
                if (!$obEventType->Update(['ID' => $arCurrent['ID']], $arTypeX))
                    throw new RuntimeException("Ошибка! Невозможно обновить тип почтовых событий. Объект #{$arCurrent['ID']}: " . json_encode($arTypeX));
            } // Создаем
            else {
                if (!$obEventType->Add($arTypeX))
                    throw new RuntimeException('Ошибка! Невозможно добавить тип почтовых событий. Объект: ' . "\n" . json_encode($arTypeX));
            }
        }
    }

    /*
     * Создание/обновление почтовых шаблонов для типов
     */
    foreach ($arEventMessages AS $arMessage) {


        // Проходимся по кадому ИД сайта, к которому дожен быть привязан шаблон
        foreach ($arMessage['LID'] AS $sSiteId) {
            // Создаем копию и выставляем конкретный сайт
            $arMessageX = $arMessage;
            $arMessageX['LID'] = $sSiteId;

            //т.к. шаблона 2, то просто создаем по очереди

            if (!$obEventMessage->Add($arMessageX)){
                throw new RuntimeException('Ошибка! Невозможно добавить шаблон почтового события. Объект: ' . "\n" . json_encode($arMessageX));
            }


            // Получаем текущий тип
            $arCurrent = $obEventMessage->getList($foo = 'id', $bar = 'desc', ['TYPE_ID' => $arMessage['EVENT_NAME'], 'EMAIL_TO' =>$arMessage['EMAIL_TO'], 'LID' => $sSiteId])->fetch();

            // Обновляем
            if ($arCurrent) {
                if (!$obEventMessage->Update($arCurrent['ID'], $arMessageX)){
                    throw new RuntimeException("Ошибка! Невозможно обновить шаблон почтового события. Объект #{$arMessageEx['ID']}: " . json_encode($arMessageX));
                }
            } // Создаем
            else {
                if (!$obEventMessage->Add($arMessageX)){
                    throw new RuntimeException('Ошибка! Невозможно добавить шаблон почтового события. Объект: ' . "\n" . json_encode($arMessageX));
                }
            }

        }
    }

// Обработка ошибок
} catch (Exception $ex) {
    // Откат изменений
    $DB->Rollback();
    echo 'Завершено с ошибкой: <br /><pre>' . $ex->getMessage() . '</pre>';
    die();
}

// Сохранение данных
$DB->Commit();

// Выводим сообщение
echo 'Выполнено успешно! ' . date('(H:i:s)');
echo '<br>Файл миграции: ' . __FILE__;
