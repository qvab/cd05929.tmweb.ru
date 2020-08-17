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

/**
 * Конфигурация типов почтовых сообщений
 */
$arEventTypes = [
    [
        'LID'         => $arSiteLangIds,
        'EVENT_NAME'  => 'SCHEDULED_AFFAIRS',
        'NAME'        => 'Уведомление о наступлении даты запланированного "Дела"',
        'DESCRIPTION' => implode(
            PHP_EOL,
            [
                'Дополнительные поля, которые можно использовать в шаблоне:',
                '#ID_AFFAIR#    - ID Дела',
                '#EMAIL_AGENT#  - Email агента',
                '#DATE_AFFAIR#  - Дата действия',
                '#DATE_CREATE#  - Дата создания действия',
                '#DESCRIPTION#  - Описание',
            ]
        ),
    ],
];

/**
 * Конфигурация шаблонов почтовых сообщений
 */
$arEventMessages = [
    [
        'ACTIVE'      => 'Y',
        'EVENT_NAME'  => 'SCHEDULED_AFFAIRS',
        'LID'         => $arSiteCodeIds,
        //'LANGUAGE_ID' => 'ru',
        'EMAIL_FROM'  => '#DEFAULT_EMAIL_FROM#',
        'EMAIL_TO'    => '#EMAIL_AGENT#',
        'SUBJECT'     => 'Уведомление о наступлении даты запланированного "Дела"',
        'BODY_TYPE'   => 'html',
        'MESSAGE'     => implode('<br />', [
            'Уведомление о наступлении даты запланированного "Дела"',
            '',
            '<b>ID Дела:</b>                 #ID_AFFAIR#',
            '<b>Дата действия:</b>           #DATE_AFFAIR#',
            '<b>Дата создания дела:</b>  #DATE_CREATE#',
            '<b>Описание:</b>',
            '#DESCRIPTION#',
            '',
            '--',
            'Письмо сгенерированно автоматически. Пожалуйста не отвечайте на него.',
        ]),
    ],
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

            // Получаем текущий тип
            $arCurrent = $obEventMessage->getList($foo = 'id', $bar = 'desc', ['TYPE_ID' => $arMessage['EVENT_NAME'], 'LID' => $sSiteId])->fetch();

            // Обновляем
            if ($arCurrent) {
                if (!$obEventMessage->Update($arCurrent['ID'], $arMessageX))
                    throw new RuntimeException("Ошибка! Невозможно обновить шаблон почтового события. Объект #{$arMessageEx['ID']}: " . json_encode($arMessageX));
            } // Создаем
            else {
                if (!$obEventMessage->Add($arMessageX))
                    throw new RuntimeException('Ошибка! Невозможно добавить шаблон почтового события. Объект: ' . "\n" . json_encode($arMessageX));
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
