<?php

/**
 * Для работы агентов полностью на кроне нужно сделать:
 * (ссылка: https://dev.1c-bitrix.ru/community/webdev/user/25773/blog/10059/)
 * (еще: http://epages.su/blog/transfer-all-bitrix-agents-to-cron.html)
 *
 * Не забываем сравнивать стандартный крон файл с этим после обновлений, могут появлятся дополнительные фичи.
 * (/bitrix/modules/main/tools/cron_events.php)
 *
 * 1) Проверьте, чтобы в файле dbconn.php не было установленных констант:
 ---------------------------------------------------------
    BX_CRONTAB           (не должно быть true или можно закомментировать полностью)
    BX_CRONTAB_SUPPORT   (не должно быть true или можно закомментировать полностью)
    NO_AGENT_CHECK
    DisableEventsCheck
 --------------------------------------------------------
 *
 * 2) Установите опцию, которая запрещает выполнение агента в прологе:
 ---------------------------------------------------------
    COption::SetOptionString("main", "check_agents", "N");
    echo COption::GetOptionString("main", "check_agents", "Y"); // должно вывестись N
 ---------------------------------------------------------
 *
 * 3) Опция, которая влияет на выбор агентов в функции CheckAgents, должна быть не определена или "N".
 ---------------------------------------------------------
    COption::SetOptionString("main", "agents_use_crontab", "N");
    echo COption::GetOptionString("main", "agents_use_crontab", "N"); // должно вывестись N
 ---------------------------------------------------------
 *
 * 4) Добавить этот скрипт в выполнение по крону (стандартный битриксовый нужно убрать, если он там есть)
 * Например:
 ---------------------------------------------------------
    * * * * * /usr/bin/php -f /home/bitrix/www/local/php_interface/cron_events.php > /dev/null 2>&1
 ---------------------------------------------------------
 *
 * @author Смагин АС <sinator5000@gmail.com>
 */

// Окружение
$_SERVER["DOCUMENT_ROOT"] = realpath(dirname(__FILE__)."/../..");
$DOCUMENT_ROOT = $_SERVER["DOCUMENT_ROOT"];

// Настраиваем константы
define("NO_KEEP_STATISTIC",       true);
define("NOT_CHECK_PERMISSIONS",   true);
define('BX_WITH_ON_AFTER_EPILOG', true);
define('BX_NO_ACCELERATOR_RESET', true); // Чтобы не глючило на VMBitrix 3.1 из-за Zend при отправке бэкапа в облако.

// Подключаем пролог Битрикса.
// Здесь никакой агент не выполнится. Потому что мы сделали COption::SetOptionString("main", "check_agents", "N");
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

// Снимаем лимиты на скрипт
@set_time_limit(0);
@ignore_user_abort(true);

// Запускаем агентов
CAgent::CheckAgents(); // а вот тут все агенты выполнятся. И периодические, и непериодические
CEvent::CheckEvents(); // почтовые события мы оставили выполняться на хитах. Но и тут они могут выполняться. Почему нет?


// BX_CRONTAB ставим сюда, т.к. инчае в прологе будет выставлена константа BX_CRONTAB_SUPPORT и выполнятся только непереодические кроны.
// (можно просто объявить BX_CRONTAB_SUPPORT - false конечно...)
define("BX_CRONTAB", true);


// Модуль рассылки
if (CModule::IncludeModule('subscribe')) {
    $cPosting = new CPosting();
    $cPosting->AutoSend();
}


// Модуль email-маркетинг
if(CModule::IncludeModule('sender')) {
    \Bitrix\Sender\MailingManager::checkPeriod(false);
    \Bitrix\Sender\MailingManager::checkSend();
}

// А еще есть файлик резервного копирования, который запустится, когда его время придет.
require($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/tools/backup.php");

// Заканчивает отработку вызывая события ("main", "OnAfterEpilog"), потом отключается от БД и запускает self::ForkActions();
CMain::FinalActions();

