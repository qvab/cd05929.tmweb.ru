<?php
/*
 *  Добавляет агент для удаления уведомлений
 */

// Подключение Битрикса
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

// Проверка на админа
if(!$USER->IsAdmin())
    die();

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


$obAgent = new CAgent();


$arAgents = [
    [
        'NAME'          => 'notice::AgentCleanMsg(7);',
        'MODULE_ID'     => '',
        'PERIOD'        => 'N',
        'INTERVAL'      => 60*60*24,
        'DATE_CHECK'    => null,
        'ACTIVE'        => 'Y',
        'NEXT_EXEC'     => null,
        'SORT'          => 10,
    ],
];


try {

    foreach ($arAgents as $arAgentItem) {

        $arAgent = $obAgent->GetList(
            ['ID' => 'DESC'],
            [
                'NAME'      => $arAgentItem['NAME'],
                'MODULE_ID' => $arAgentItem['MODULE_ID'],
            ]
        )->Fetch();

        if(empty($arAgent['ID'])) {

            if(!$obAgent->AddAgent(
                    $arAgentItem['NAME'],
                    $arAgentItem['MODULE_ID'],
                    $arAgentItem['PERIOD'],
                    $arAgentItem['INTERVAL'],
                    $arAgentItem['DATE_CHECK'],
                    $arAgentItem['ACTIVE'],
                    $arAgentItem['NEXT_EXEC'],
                    $arAgentItem['SORT']
            )) {
                throw new Exception('Ошибка добавления агента');
            }

        } else {

            if(!$obAgent->Update(
                $arAgent['ID'],
                array(
                    'MODULE_ID'         => $arAgentItem['MODULE_ID'],
                    'ACTIVE'            => $arAgentItem['ACTIVE'],
                    'AGENT_INTERVAL'    => $arAgentItem['INTERVAL'],
                    'SORT'              => $arAgentItem['SORT'],
                )
            )) {
                throw new Exception('Ошибка обновления агента');
            }
        }
    }


    $DB->Commit();
}
catch( Exception $ex ) {
    // Откат изменений
    $DB->Rollback();
    echo 'Завершено с ошибкой: ' . $ex->getMessage();
    die();
}


// Выводим сообщение
echo 'Выполнено успешно! '.date('(H:i:s)');
echo '<br>Файл миграции: '.__FILE__;
