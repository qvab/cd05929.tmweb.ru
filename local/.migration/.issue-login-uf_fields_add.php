<?

/*
 * Описание скрипта
 */

// Подключение Битрикса
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

// Проверка на админа
if(!$USER->IsAdmin())
    die('WTF Bro!? Fuck off!');

// Подключаем необходимые модули
if(!CModule::IncludeModule("iblock"))
    die('Module "IBlock" not found!');
if(!CModule::IncludeModule("intranet"))
    die('Module "Intranet" not found!');


/** Конвертор ошибки в исключение */
if(!function_exists('exception_error_handler')) {
    function exception_error_handler($errno, $errstr, $errfile, $errline ) {
        throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
    }
}

// Выставляем лимит
set_time_limit('300');


// Создаем объекты
$obUserTypeEntity   = new CUserTypeEntity();


// Список UF полей для добавления/обновления
$arFields = [
    // Описание UF поля
    [
        'FIELD_NAME'    => 'UF_FIELD_NAME',
        'ENTITY_ID'     => 'BLOG_POST',
        'USER_TYPE_ID' 	=> 'integer',
        'MULTIPLE' 		=> 'N',
        'MANDATORY' 	=> 'N',
        'SHOW_FILTER' 	=> 'N',
        'SHOW_IN_LIST' 	=> 'N',
        'EDIT_IN_LIST' 	=> 'N',
        'IS_SEARCHABLE' => 'N',

        // Названия
        'EDIT_FORM_LABEL' 	=> ['ru' => 'Название1', 'en' => ''], // Подпись в форме редактирования
        'LIST_COLUMN_LABEL' => ['ru' => '', 'en' => ''], // Заголовок в списке
        'LIST_FILTER_LABEL' => ['ru' => '', 'en' => ''], // Подпись фильтра в списке
        'ERROR_MESSAGE'     => ['ru' => '', 'en' => ''], // Сообщение об ошибке (не обязательное)
        'HELP_MESSAGE'      => ['ru' => '', 'en' => ''], // Помощь

        // Дополнительные настройки поля (зависят от типа)
        'SETTINGS' 		=> [
            // Целое число
            'DEFAULT_VALUE' => '0',              // Значение по умолчанию
            'SIZE'          => 20,              // Размер поля ввода для отображения
            'MIN_VALUE' 	=> 0,               // Минимальне значение (0 - не проверять)
            'MAX_VALUE' 	=> 0,               // Максимальное значение (0 - не проверять)
        ],
    ],
    // Еще одно поле
    [
        'FIELD_NAME'    => 'UF_FIELD_NAME2',
        'ENTITY_ID'     => 'BLOG_POST',
        'USER_TYPE_ID' 	=> 'integer',
        'MULTIPLE' 		=> 'N',
        'MANDATORY' 	=> 'N',
        'SHOW_FILTER' 	=> 'N',
        'SHOW_IN_LIST' 	=> 'N',
        'EDIT_IN_LIST' 	=> 'N',
        'IS_SEARCHABLE' => 'N',

        // Названия
        'EDIT_FORM_LABEL' 	=> ['ru' => 'Название2', 'en' => ''], // Подпись в форме редактирования
        'LIST_COLUMN_LABEL' => ['ru' => '', 'en' => ''], // Заголовок в списке
        'LIST_FILTER_LABEL' => ['ru' => '', 'en' => ''], // Подпись фильтра в списке
        'ERROR_MESSAGE'     => ['ru' => '', 'en' => ''], // Сообщение об ошибке (не обязательное)
        'HELP_MESSAGE'      => ['ru' => '', 'en' => ''], // Помощь

        // Дополнительные настройки поля (зависят от типа)
        'SETTINGS' 		=> [
            // Целое число
            'DEFAULT_VALUE' => '0',              // Значение по умолчанию
            'SIZE'          => 20,              // Размер поля ввода для отображения
            'MIN_VALUE' 	=> 0,               // Минимальне значение (0 - не проверять)
            'MAX_VALUE' 	=> 0,               // Максимальное значение (0 - не проверять)
        ],
    ],
];


try {

    foreach($arFields AS $arField) {

        // Проверяем, а есть ли такое уже свойсто в этом ИБ
        if($arRow = $obUserTypeEntity->GetList([], ['ENTITY_ID' => $arField['ENTITY_ID'], 'FIELD_NAME' => $arField['FIELD_NAME']])->Fetch()) {
            // Дополняем массив
            $arField['ID'] = $arRow['ID'];

            // Проверяем, нужно ли удалить это свойсто
            if(!empty($arField['DELETE'])) {
                if(!$obUserTypeEntity->Delete($arField['ID']))
                    throw new Exception('Не удалось удалить.');

                unset($arField['ID']);
            }
            // Иначе проверяем тип
            elseif(!empty($arField['USER_TYPE_ID']) && $arField['USER_TYPE_ID'] != $arRow['USER_TYPE_ID'])
                throw new Exception('Данное свойсво уже существует и у него другой тип');

            // Иначе проверяем множественность
            elseif(!empty($arField['MULTIPLE']) && $arField['MULTIPLE'] != $arRow['MULTIPLE'])
                throw new Exception('Данное свойсво уже существует и у него не совпадает свойство множественности');

        }
        unset($arRow);



        /*
         * Пробуем добавить или обновить
         */
        // Очищаем массив от лишнего
        unset($arField['DELETE'], $arField['UPDATE']);
        if(count($arField) > 1) {
            if(empty($arField['ID'])) {
                // Добавляем
                if(!$arField['ID'] = $obUserTypeEntity->Add($arField))
                    throw new Exception('Не удалось добавить "'.$arField['FIELD_NAME'].'". Причина: '. $obUserTypeEntity->LAST_ERROR);
            } else {
                // Обновить
                if(!$obUserTypeEntity->Update($arField['ID'], $arField))
                    throw new Exception('Не удалось обновить "'.$arField['FIELD_NAME'].'". Причина: '. $obUserTypeEntity->LAST_ERROR);
            }
        }
    }

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









/** =============== Пользовательские поля +++++++++++++++ */
if (0) {
    /*
     * Список свойств для добавления пользовательского поля
     * -------------------------------------------------

    $arFields = [
        'USER_TYPE_ID' 	=> 'iblock_element',	// Тип пользовательского поля (список ниже)
        'ENTITY_ID' 	=> 'USER',				// Объект, которому принадлежит пользовательское поле.  (список ниже)
        'FIELD_NAME' 	=> 'UF_DEPARTMENT',	    // Название поля (UF_*****) (максимальная длина 20 символов)
        'XML_ID' 		=> '',				    // Внешний код
        'SORT' 			=> 100,					// Сортировка
        'MULTIPLE' 		=> 'N',					// Множественное    (Y|N)
        'MANDATORY' 	=> 'N',					// Обязательное		(Y|N)
        'SHOW_FILTER' 	=> 'N',					// Показывать в фильтре списка (N - не показывать; I - точное совпадение; E - поиск по маске; S - поиск по подстроке)
        'SHOW_IN_LIST' 	=> 'Y',					// Показывать в списке  (Y|N)
        'EDIT_IN_LIST' 	=> 'Y',					// Не разрешать редактирование пользователем (Y|N)
        'IS_SEARCHABLE' => 'N',					// Значения поля участвуют в поиске (Y|N)

    // Названия
        'EDIT_FORM_LABEL' 	=> ['ru' => 'Название ПОЛЯ', 'en' => 'Field Name'),    // Подпись в форме редактирования
        'LIST_COLUMN_LABEL' => ['ru' => null, 'en' => null],                       // Заголовок в списке
        'LIST_FILTER_LABEL' => ['ru' => null, 'en' => null],                       // Подпись фильтра в списке
        'ERROR_MESSAGE' 	=> ['ru' => null, 'en' => null],                       // Сообщение об ошибке (не обязательное)
        'HELP_MESSAGE' 		=> ['ru' => null, 'en' => null],                       // Помощь

    // Дополнительные настройки поля (зависят от типа, список типов ниже)
        'SETTINGS' 		=> [

            // Видео
            'BUFFER_LENGTH' => 10,              // Размер буфера в секундах
            'CONTROLBAR'    => 'bottom',        // Расположение панели управления (bottom - внизу; none - не показывать)
            'AUTOSTART'     => 'N',             // Автоматически начать проигрывать (Y|N)
            'VOLUME'        => 1,               // Уровень громкости в процентах от максимального

            // Строка
            'DEFAULT_VALUE' => '',              // Значение по умолчанию
            'SIZE'          => 20,              // Размер поля ввода для отображения
            'ROWS'          => 1,               // Количество строчек поля ввода
            'MIN_LENGTH'    => 0,               // Минимальная длина строки (0 - не проверять)
            'MAX_LENGTH'    => 0,               // Максимальная длина строки (0 - не проверять)
            'REGEXP'        => '',              // Регулярное выражение для проверки

            // Целое число
            'DEFAULT_VALUE' => '',              // Значение по умолчанию
            'SIZE'          => 20,              // Размер поля ввода для отображения
            'MIN_VALUE' 	=> 0,               // Минимальне значение (0 - не проверять)
            'MAX_VALUE' 	=> 0,               // Максимальное значение (0 - не проверять)

            // Число
            'DEFAULT_VALUE' => '',              // Значение по умолчанию
            'SIZE'          => 20,              // Размер поля ввода для отображения
            'PRECISION'     => 4,               // Точность (количество знаков после запятой)
            'MIN_VALUE' 	=> 0,               // Минимальне значение (0 - не проверять)
            'MAX_VALUE' 	=> 0,               // Максимальное значение (0 - не проверять)

            // Дата/Время
            'DEFAULT_VALUE' => [           // Значение по умолчанию
                'TYPE'  => 'NONE',              // ТИП (NONE - нет; NOW - текущая; FIXED - фиксированная)
                'VALUE' => '31.10.2014',        // Значение если "Фиксированное"
            ],

            // Да/Нет
            'DEFAULT_VALUE' => 1,               // Значение по умолчанию (1|0 - Да|Нет)
            'DISPLAY' 		=> 'CHECKBOX',      // Внешний вид (CHECKBOX - Флажок; RADIO - Радиокнопки; DROPDOWN - Выпадающий список)

            // Файл
            'SIZE'          => 20,              // Размер поля ввода для отображения
            'LIST_WIDTH'    => 0,               // Максимальные ширина для отображения в списке
            'LIST_HEIGHT'   => 0,               // Максимальные высота для отображения в списке
            'MAX_SHOW_SIZE' => 0,               // Максимально допустимый размер для показа в списке (0 - не ограничивать)
            'MAX_ALLOWED_SIZE' => 0,            // Максимально допустимый размер файла для загрузки (0 - не проверять)
            'EXTENSIONS' 	=> '',              // Расширения (через пробел или запятую??? с точкой или без??)

            // Список
            'DISPLAY'       => 'LIST',          // Внешний вид (LIST - Список; CHECKBOX - Флажки)
            'LIST_HEIGHT'   => 5,               // Высота списка
            'CAPTION_NO_VALUE' => '',           // Подпись при отсутствии значения

            // Настройки привязки к разделам инфоблока
               'IBLOCK_ID' 	=> 123,             // Инфоблок
            'DEFAULT_VALUE' => '',              // Значение по умолчанию
            'DISPLAY' 		=> 'LIST',          // Внешний вид (LIST - Список; CHECKBOX - Флажки)
            'LIST_HEIGHT' 	=> 5,               // Высота списка
            'ACTIVE_FILTER' => 'N'              // Показывать только активные элементы (Y|N)

            // Настройки привязки к элементам инфоблока
               'IBLOCK_ID' 	=> 123,             // Инфоблок
            'DEFAULT_VALUE' => '',              // Значение по умолчанию
            'DISPLAY' 		=> 'LIST',          // Внешний вид (LIST - Список; CHECKBOX - Флажки)
            'LIST_HEIGHT' 	=> 5,               // Высота списка
            'ACTIVE_FILTER' => 'N'              // Показывать только активные элементы (Y|N)

            // Шаблон
               'PATTERN' 	=> '#VALUE#',           // Шаблон вывода (#VALUE# - значение)
            'DEFAULT_VALUE' => '',              // Значение по умолчанию
            'SIZE'          => 20,              // Размер поля ввода для отображения
            'ROWS'          => 1,               // Количество строчек поля ввода
            'MIN_LENGTH'    => 0,               // Минимальная длина строки (0 - не проверять)
            'MAX_LENGTH'    => 0,               // Максимальная длина строки (0 - не проверять)
            'REGEXP'        => '',              // Регулярное выражение для проверки
        ],
    ];

    */


    /*
     * Варианты объектов UF_ полей
     *
    USER                   - Пользователь
    IBLOCK_*****_SECTION   - Разделы в ИБ с ID *****
    BLOG_POST              - Посты в Блоге

    ... еще типы, дополняем... не стесняемся
     */


    /*
     * Типы UF_ полей
     *
    video               - Видео
    string              - Строка
    integer             - Целое число
    double              - Число
    datetime            - Дата/Время
    boolean             - Да/Нет
    file                - Файл
    enumeration         - Список
    iblock_section      - Привязка к разделам инф. блоков
    iblock_element      - Привязка к элементам инф. блоков
    string_formatted    - Шаблон

    ... еще типы, дополняем... не стесняемся

     */


    /** =============== END: Пользовательские поля +++++++++++++++ */
}