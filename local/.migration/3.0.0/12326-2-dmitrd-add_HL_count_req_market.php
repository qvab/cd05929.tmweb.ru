<?php
/**
 * Добавляет HL "Рынок" для хранения данных запросов покупателей (для построения графика) - Хранилище
 * @author: dmitrd <dmitrd@rarus.ru>
 */

use Bitrix\Highloadblock\HighloadBlockTable as HlTab;
use Bitrix\Highloadblock\HighloadBlockLangTable as HlLangTab;

// Подключение Битрикса
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

// Проверка на админа
if (!$USER->IsAdmin())
    die();

// Подключаем необходимые модули
if (!CModule::IncludeModule("highloadblock")) {
    die('Module "highloadblock" not found!');
}


/** Конвертор ошибки в исключение */
if (!function_exists('exception_error_handler')) {
    function exception_error_handler($errno, $errstr, $errfile, $errline) {
        throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
    }
}

// Выставляем лимит
set_time_limit('300');


$iSort = 0;
/**
 * Массив описывающий хайлоад блоки
 */
$arHighBlocks = array(
    array(
        // Имя таблицы
        'TABLE_NAME' => 'rrs_count_req_market',
        // Название сущности
        'ENTITY_NAME' => 'CONTREQMARKET',
        // Языкозависимые названия
        'LANGS' => array('ru' => 'Рынок (для построения графиков для предложений)',),
        // Параметры Highloadblock
        'OPTIONS_HIGHLOADBLOCK' => array(
            'FIELDS'=> array(
                'UF_DATE' => array(
                    'SORT'              => $iSort += 10,
                    'MANDATORY'         => 'N',
                    'USER_TYPE_ID'      => 'datetime',
                    'MULTIPLE'          => 'S',
                    'SHOW_FILTER'       => 'N',
                    'SHOW_IN_LIST'      => 'Y',
                    'EDIT_IN_LIST'      => 'Y',
                    'IS_SEARCHABLE'     => 'N',
                    'EDIT_FORM_LABEL'   => array('ru' => 'Дата создания',),
                    'LIST_COLUMN_LABEL' => array('ru' => 'Дата создания',),
                    'SETTINGS' => array(
                        'DEFAULT_VALUE' => ['TYPE' => 'NOW'],
                    )
                ),
                'UF_CENTER' => array(
                    'SORT'              => $iSort += 10,
                    'MANDATORY'         => 'N',
                    'USER_TYPE_ID'      => 'integer',
                    'MULTIPLE'          => 'N',
                    'SHOW_FILTER'       => 'I',
                    'SHOW_IN_LIST'      => 'Y',
                    'EDIT_IN_LIST'      => 'Y',
                    'IS_SEARCHABLE'     => 'N',
                    'EDIT_FORM_LABEL'   => array('ru' => 'Региональный центр',),
                    'LIST_COLUMN_LABEL' => array('ru' => 'Региональный центр',),
                ),
                'UF_CSM_PRICE' => array(
                    'SORT'              => $iSort += 10,
                    'MANDATORY'         => 'N',
                    'USER_TYPE_ID'      => 'double',
                    'MULTIPLE'          => 'N',
                    'SHOW_FILTER'       => 'N',
                    'SHOW_IN_LIST'      => 'Y',
                    'EDIT_IN_LIST'      => 'Y',
                    'IS_SEARCHABLE'     => 'N',
                    'EDIT_FORM_LABEL'   => array('ru' => 'Цена (средневзвешанная)',),
                    'LIST_COLUMN_LABEL' => array('ru' => 'Цена (средневзвешанная)',),
                ),
                'UF_CULTURE' => array(
                    'SORT'              => $iSort += 10,
                    'MANDATORY'         => 'N',
                    'USER_TYPE_ID'      => 'integer',
                    'MULTIPLE'          => 'N',
                    'SHOW_FILTER'       => 'I',
                    'SHOW_IN_LIST'      => 'Y',
                    'EDIT_IN_LIST'      => 'Y',
                    'IS_SEARCHABLE'     => 'N',
                    'EDIT_FORM_LABEL'   => array('ru' => 'Культура',),
                    'LIST_COLUMN_LABEL' => array('ru' => 'Культура',),
                ),
            ),
        ),
    ),
    // ... Следующий Highloadblock
);


/**
 * Объекты
 */
$obUserTypeEntity   = new CUserTypeEntity;
$obEnum             = new CUserFieldEnum;

/**
 * Добавляет/Обновляет хайлоад блок
 * @param $sTableName
 * @param $sHighBlockName
 * @param array $arOptions
 * @param array $arLangs
 * @return mixed
 * @throws Exception
 */
function addHighLoadBlock($sTableName, $sHighBlockName, array $arOptions, array $arLangs = array()) {

    global $obUserTypeEntity, $obEnum, $APPLICATION;

    $arLogs = array();

    $connection = \Bitrix\Main\Application::getConnection();
    $sqlHelper = $connection->getSqlHelper();

    $arHlblock = HlTab::getList(
        array(
            'filter' => array(
                'TABLE_NAME' => $sTableName,
            ),
        )
    )->fetch();

    if (!$arHlblock) {

        $sNameHighBlock = $sHighBlockName;

        $sHighBlockName = preg_replace('/([^A-Za-z0-9]+)/', '', trim($sHighBlockName));
        if ($sHighBlockName == '') {
            throw new Exception('Неверное имя "' . $sNameHighBlock . '" для хайлоад блока');
        }

        $sHighBlockName = strtoupper(substr($sHighBlockName, 0, 1)) . substr($sHighBlockName, 1);

        $result = HlTab::add(
            array(
                'NAME' => $sHighBlockName,
                'TABLE_NAME' => $sTableName,
            )
        );

        if ($result->isSuccess()) {

            $iHighBlockID = $result->getId();

            /*if (!empty($arLangs)) {

                foreach ($arLangs as $sLid => $sName) {
                    $addLang = HlLangTab::add(
                        array(
                            'ID'    => $iHighBlockID,
                            'LID'   => $sLid,
                            'NAME'  => $sName
                        )
                    );

                    if (!$addLang->getId()) {
                        throw new Exception('Не удалось добавить локализацию');
                    }
                }
            }*/

            $arLogs[] = 'Хайлоад "' . $sHighBlockName . '"[' . $iHighBlockID . '] добавлен';

        } else {
            throw new Exception('Ошибка добавления Highload блока "' . $sNameHighBlock . '": ' . $result->getErrorMessages());
        }

    } else {
        $iHighBlockID = $arHlblock['ID'];
    }

    // Обработка полей
    foreach ($arOptions['FIELDS'] as $sFieldName => $arFieldParam) {

        $arUserField = array(
            'ENTITY_ID' => 'HLBLOCK_' . $iHighBlockID,
            'FIELD_NAME' => $sFieldName,
        );

        foreach ($arFieldParam as $sKeyParam => $mixValParam) {
            $arUserField[$sKeyParam] = $mixValParam;
        }

        $resProperty = CUserTypeEntity::GetList(
            array(),
            array(
                'ENTITY_ID' => $arUserField['ENTITY_ID'],
                'FIELD_NAME' => $arUserField['FIELD_NAME'],
            )
        );

        if ($arUserHasField = $resProperty->Fetch()) {

            $iUserTypePropId = $arUserHasField['ID'];

            if ($obUserTypeEntity->Update($iUserTypePropId, $arUserField)) {
                $arLogs[] = 'Пользовательское свойство "' . $arUserHasField['FIELD_NAME'] . '[' . $arUserHasField['ENTITY_ID'] . ']" успешно обновлено';
            } else {

                $ex = $APPLICATION->GetException();
                throw new Exception('Ошибка обновления пользовательского свойства "' . $arUserHasField['FIELD_NAME'] . '[' . $arUserHasField['ENTITY_ID'] . ']":' . $ex->GetString());
            }
        } else {

            if ($iUserTypePropId = $obUserTypeEntity->Add($arUserField)) {
                $arLogs[] = 'Пользовательское свойство "' . $arUserField['FIELD_NAME'] . '[' . $arUserField['ENTITY_ID'] . ']" успешно добавлено';
            } else {

                $ex = $APPLICATION->GetException();
                throw new Exception('Ошибка добавления пользовательского свойства "' . $arUserField['FIELD_NAME'] . '[' . $arUserField['ENTITY_ID'] . ']":' . $ex->GetString());
            }
        }


        // Обработка значений св-ва типа список
        if($arUserField['USER_TYPE_ID'] == 'enumeration') {

            $rsEnum = $obEnum->GetList(array(), array(
                "USER_FIELD_ID" => $iUserTypePropId,
            ));

            if(!$arEnum = $rsEnum->GetNext()) {

                // Удаляем значения списка
                $obEnum->DeleteFieldEnum($iUserTypePropId);

                if(!is_array($arUserField['VALUES'])) {
                    $arUserField['VALUES'] = array();
                }

                $i = 0;
                $arEnumValue = array();
                foreach ($arUserField['VALUES'] as $arEnum) {
                    $arEnumValue['n' . $i++] = $arEnum;
                }

                if(!empty($arEnumValue)) {

                    if(!$obEnum->SetEnumValues($iUserTypePropId, $arEnumValue)) {
                        throw new Exception('Не удалось задать значение списка свойству "'.$sFieldName.'"');
                    }
                }
            }
        }
    }


    // Доп логика работы с
    $hlEntity = HlTab::compileEntity(
        HlTab::getRowById($iHighBlockID)
    );

    if (isset($arOptions['ALTER']) && is_array($arOptions['ALTER'])) {

        try {

            foreach ($arOptions['ALTER'] as $sAlter) {

                $sSQL = str_replace(
                    '#TABLE_NAME#',
                    $sqlHelper->quote($hlEntity->getDBTableName()),
                    $sAlter
                );

                if ($connection->query($sSQL)) {
                    $arLogs[] = 'ALTER TABLE выполнен SQL запрос: "' . $sSQL . '"';
                }
            }

        } catch (\Bitrix\Main\DB\SqlQueryException $sqlEx) {
            $arLogs[] = '<span style="color:red">Не удалось выполнить SQL: ' . $sSQL . ' . Ошибка: ' . $sqlEx->getMessage() . '</span>';
        }
    }

    if (isset($arOptions['INDEXES']) && is_array($arOptions['INDEXES'])) {

        try {

            foreach ($arOptions['INDEXES'] as $indexData) {

                $iResult = $connection->createIndex(
                    str_replace('#TABLE_NAME#', $hlEntity->getDBTableName(), $indexData[0]),
                    str_replace('#TABLE_NAME#', $hlEntity->getDBTableName(), $indexData[1]),
                    $indexData[2]
                );

                $sIndexName = str_replace('#TABLE_NAME#', $hlEntity->getDBTableName(), $indexData[1]);

                if ($iResult) {
                    $arLogs[] = 'Индекс "' . $sIndexName . '" к таблице "' . $hlEntity->getDBTableName() . '" успешно добавлен';
                }
            }

        } catch (\Bitrix\Main\DB\SqlQueryException $sqlEx) {
            $arLogs[] = '<span style="color:red">Не удалось добавить индекс. Ошибка: ' . $sqlEx->getMessage() . '</span>';
        }
    }

    return array('ID' => $iHighBlockID, 'LOGS' => $arLogs);
}


// Подвешиваем обработку ошибок
set_error_handler('exception_error_handler', E_RECOVERABLE_ERROR);

// Логи
$arLogs = array();

// Запуск транзакции
$DB->StartTransaction();

// Обработка
try {

    foreach ($arHighBlocks as $arHighLoad) {

        $arResultCreate = addHighLoadBlock($arHighLoad['TABLE_NAME'], $arHighLoad['ENTITY_NAME'], $arHighLoad['OPTIONS_HIGHLOADBLOCK'], $arHighLoad['LANGS']);

        if(!empty($arResultCreate['LOGS'])) {
            $arLogs[] = '=================================';
            $arLogs = array_merge($arLogs, $arResultCreate['LOGS']);
        }
    }

    /**
     * ==================================================================
     * ###########--------------------------------------------###########
     *
     *                  Дополнительная логика миграции
     * Создание Элементов, парс данных, перенос чего нибудь - куда нибудь...
     *
     * ###########--------------------------------------------###########
     * ==================================================================
     */


    // Сохранение данных
    $DB->Commit();

} // Обработка ошибок
catch (Exception $ex) {
    // Откат изменений
    $DB->Rollback();
    echo 'Завершено с ошибкой: ' . $ex->getMessage();
    die();
}

// Выводим сообщение
echo 'Выполнено успешно! ' . date('(H:i:s)');
echo '<br />Файл миграции: ' . __FILE__;
echo '<br /><br />Лог выполнения:';
echo '<br />' . implode("<br />", $arLogs);



/*
 *      Типы свойст HL "USER_TYPE_ID"
 *
        webdav_element          - Документ из библиотеки документов
        crm                     - Привязка к элементам CRM
        crm_status              - Привязка к справочникам CRM
        money                   - Деньги
        video                   - Видео
        hlblock                 - Привязка к элементам highload - блоков
        employee                - Привязка к сотруднику
        webdav_element_history  - Документ истории из библиотеки документов
        string                  - Строка
        integer                 - Целое число
        double                  - Число
        datetime                - Дата со временем
        date                    - Дата
        boolean                 - Да / Нет
        address                 - Адрес
        url                     - Ссылка
        file                    - Файл
        enumeration             - Список
        iblock_section          - Привязка к разделам инф . блоков
        iblock_element          - Привязка к элементам инф . блоков
        string_formatted        - Шаблон
        vote                    - Опрос

        // Список для "enumeration"
        'VALUES' => array(
            array(
                'XML_ID'    => '',
                'VALUE'     => '',
                'SORT'      => '100',
                'DEF'       => 'N',
            ),
         );

 *
 *
*/

