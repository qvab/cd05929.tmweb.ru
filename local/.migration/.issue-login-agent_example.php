<?php
/**
 * Описание: Описание добовляемого Агента.
 * Интервал: Раз в сутки
 * Периодический: Да
 * Время запуска: каждый день в 4 утра
 */

// Подключение Битрикса
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

// Проверка на админа
if(!$USER->IsAdmin()) die('WTF Bro!? Fuck off!');



/** Конвертор ошибки в исключение */
if(!function_exists('exception_error_handler')) {
    function exception_error_handler($errno, $errstr, $errfile, $errline ) {
        throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
    }
}
// Подвешиваем обработку ошибок
set_error_handler('exception_error_handler', E_RECOVERABLE_ERROR);



// Запуск транзакции
$DB->StartTransaction();



/*
 * Настройка агента(ов)
 */
$arAgents = [
    # Короткое описание агента
    [
        /*
        PHP строка для запуска функции-агента. (например:  CSomeClass::RunMyAgent();    или:  mySimplyAgent('param', 'value'); ) */
        'NAME'              => '',
        /*
        идентификатор модуля (необходим для подключения файлов модуля. (необязательный. по умолчанию пустой)) */
        'MODULE_ID'         => '',
        /*
        Периодический [Y|N] (по умолчанию N) (нормальное пояснение - https://dev.1c-bitrix.ru/community/webdev/user/25773/blog/10055/ )
            N - запуск от даты последнего завершения + интервал (игнорирует пропущенные запуски, если пропустили 10 раз, то запустит только 1),
            Y - запускается от даты начала агента до текущей даты столько раз, сколько раз "влезет" интервал (гарантирует кол-во запусков, даже если пропустили, а также гарантирует время запуска +\-)  */
        'IS_PERIOD'         => 'N',
        /*
        С какой периодичностью запускать агента (в секундах) */
        'AGENT_INTERVAL'    => 24 * 60 * 60,
        /*
        Дата первой проверки "не пора ли запустить агент" в формате текущего языка. (необязательный. по умолчанию - текущее время (false)) (вроде не работает) */
        'DATE_CHECK'        => date('d.m.Y H:i:s', mktime(4 +24, 0, 0)),  // Завтра в 4 утра
        /*
        Активность агента (Y|N). (необязательный. по умолчанию - "Y" (активен)) */
        'ACTIVE'            => 'Y',
        /*
        Дата первого запуска агента в формате текущего языка. (необязательный. по умолчанию - текущее время (false)) */
        'NEXT_EXEC'         => date('d.m.Y H:i:s', mktime(4 +24, 0, 0)),  // Завтра в 4 утра
        /*
        Индекс сортировки позволяющий указать порядок запуска данного агента относительно других агентов для которых подошло время запуска. (необязательный. по умолчанию - 100) */
        'SORT'              => 100,
        /*
        ID пользователя, от которого будет работать Агент (по умолчанию false) */
        'USER_ID'           => false,
        /*
        Возвращать ошибку, если такой агент уже существует (это не свойство Агента, а параметр функции добавления, по умолчанию true) */
        'ERROR'             => true,
    ],

    # ... следующий агент
];



try {
    $obAgentsController = new \CAgent();


    # Проходимся по каждому агенту и добавляем/обновляем
    foreach($arAgents AS $arAgent) {
        # Нормализуем данные
        $arAgent['NAME']           = $arAgent['NAME']?:'';
        $arAgent['MODULE_ID']      = $arAgent['MODULE_ID']?:'';
        $arAgent['IS_PERIOD']      = $arAgent['IS_PERIOD']?:'N';
        $arAgent['AGENT_INTERVAL'] = $arAgent['AGENT_INTERVAL']?:86400;
        $arAgent['DATE_CHECK']     = $arAgent['DATE_CHECK']?:date('d.m.Y H:i:s');
        $arAgent['ACTIVE']         = $arAgent['ACTIVE']?:'Y';
        $arAgent['NEXT_EXEC']      = $arAgent['NEXT_EXEC']?:date('d.m.Y H:i:s');
        $arAgent['SORT']           = $arAgent['SORT']?:100;
        $arAgent['USER_ID']        = $arAgent['USER_ID']?:false;
        $arAgent['ERROR']          = $arAgent['ERROR'] === false? false : true;

        // Проверяем
        if(!$obAgentsController->CheckFields($arAgent))
            throw new Exception('В данных агента "'. $arAgent['NAME'] .'". Причина: '. $APPLICATION->LAST_ERROR);


        # Ищем текущего агента
        $arCurrentAgent = $obAgentsController->GetList(
            ['ID' => 'DESC'],
            [
                'MODULE_ID' => $arAgent['MODULE'],
                'NAME'      => $arAgent['NAME']?:-1,
            ]
        )->Fetch();



        /**
         * Если агент еще не создан - создаем, иначе активируем
         */
        // Создаем
        if(!$arCurrentAgent) {
            $iAgentId = $obAgentsController->AddAgent(
                $arAgent['NAME'],
                $arAgent['MODULE_ID']?:'',
                $arAgent['IS_PERIOD']?:'N',
                $arAgent['AGENT_INTERVAL']?:86400,
                $arAgent['DATE_CHECK']?:'',
                $arAgent['ACTIVE']?:'Y',
                $arAgent['NEXT_EXEC']?:'',
                $arAgent['SORT']?:100,
                $arAgent['USER_ID']?:false,
                $arAgent['ERROR'] === false? false : true
            );
            if(!$iAgentId)
                throw new Exception('Ошибка добавления агента "'. $arAgent['NAME'] .'". Причина: '. $APPLICATION->LAST_ERROR);
        }

        // Обновляем
        else {
            if(!$obAgentsController->Update($arCurrentAgent['ID'], $arAgent))
                throw new Exception('Ошибка обновления агента "'.$arAgent['NAME'].'" [ID: '.$arCurrentAgent['ID'].']. Причина: '. $APPLICATION->LAST_ERROR);
        }

    }




    $DB->Commit();
}
catch(Exception $ex) {
    // Откат изменений
    $DB->Rollback();
    echo 'Завершено с ошибкой: ' . $ex->getMessage();
    die();
}


// Выводим сообщение
echo 'Выполнено успешно! '.date('(H:i:s)');
echo '<br>Файл миграции: '.__FILE__;
