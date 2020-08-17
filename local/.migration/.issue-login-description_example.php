<?php

/*
 * Описание того, что будет происходить
 *
 * PS: Файл мозно дополнять, но только без говнокодинга!!!! (с) Смагин
 * PS2: Не забываем менять оба файла!
 * PS3: Не забываем прописывать новые настроки модулей и тд, если они используются.
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
$obIBlockType       = new CIBlockType;
$obIBlock           = new CIBlock();
$obIBlockProperty   = new CIBlockProperty();
$obIBlockSection    = new CIBlockSection();
$obIBlockElement    = new CIBlockElement();
$obUserTypeEntity   = new CUserTypeEntity();

/**
 * Список Типов ИБ с их свойствами
 * Не ассоциативный массив, где элемент массива является массивом со свойствами для создания Типа ИБ
 */
$arIBlockTypes = [
    // Имя типа ИБ
    [
        'CODE'      => 'КОД ТИПА ИБ',
        'DELETE'    => true,    // Удалить этот ИБ? (удален будет перед тем как создан, таким образом можно пересоздать ИБ)
        'PROPERTY'  => [
            'SECTIONS' => 'Y',
            'IN_RSS'   => 'N',
            'SORT'     => 500,
            'LANG'     => [
                'ru' => [
                    'NAME'          => 'Имя',
                    'SECTION_NAME'  => 'Разделы',
                    'ELEMENT_NAME'  => 'Элементы',
                ],
                'en' => [
                    'NAME'          => 'Name',
                    'SECTION_NAME'  => 'Sections',
                    'ELEMENT_NAME'  => 'Elements',
                ],
            ]
        ],
    ],
    //... следующий тип ИБ
];





/*
 * Массив свойст ИБ
 * Записываются здесь, а не в ИБ если они повторяются в разных ИБ
 */
$arPropsData = [
    'PROP_CODE1' => [
        'NAME'			=> 'Имя1',
        'ACTIVE' 		=> 'Y',
        'PROPERTY_TYPE' => 'S',
    ],
];



/**
 * Список ИБ с их свойствами
 * Не ассоциативный массив, где элемент массива является массивом со свойствами ИБ
 */
$arIBlocks = [
    // Имя типа ИБ -> Имя ИБ (что бы легко было найти и проверить в АДМИНКЕ)
    [
        'TYPE'      => 'ТИП ИБ',
        'CODE'      => 'КОД ИБ',
        'DELETE'    => true,    // Удалить этот ИБ? (удален будет перед тем как создан, таким образом можно пересоздать ИБ, в противном случаи будет обновлено)
        'CREATE'    => [   // Если это свойство задано, то если такого ИБ нет то он будет создан
            'VERSION' => 2,
            'SITE_ID' => ['en', 's1'],
            'WORKFLOW'=> 'N',
            'BIZPROC' => 'N',
            'ACTIVE'  => 'Y',
            'NAME'    => 'Имя ИБ',
            'GROUP_ID'=> ['1' => 'X','2' => 'R',],
            // и другие свойства для создания (список в низу файла, код и тип не указывать)
        ],
        'SECTIONS'   => [
            // TODO: Дополнить "IBLOCK_SECTION_ID" возможностью поиска по коду раздела в ИБ если он не создается в этом же ИБ
            // а так же дублеривание кодов разделов допустим в разных языках в одном ИБ
            // TODO: Функционал удаления и обновления.
            // TODO: Функционал проверки на создание дубликатов при повторном запуске.
            'SECT_CODE1' => [
                'ACTIVE'              => 'Y',
                'IBLOCK_SECTION_ID'   => false, // Указывается ID родителя (если не создается новый ИБ) или указывается его CODE (если этот раздел создается раньше в этом же скрипте)
                'NAME'                => 'Имя',
                'SORT'                => 500,
                //... и другие свойства, которые описаны ниже
            ],
        ],
        'PROPERTIES' => [ // Массив свойст ИБ
            'PROP_CODE1' => $arPropsData['PROP_CODE1'],
            'PROP_CODE2' => ['DELETE' => true] + $arPropsData['PROP_CODE1'],
            'PROP_CODE3' => [
                'DELETE'        => true, // Удалить это свойство? (удалено будет перед тем как создано, таким образом можно пересоздать свойсво)
                'UPDATE'        => true, // Обновить это свойство?
                'NAME'			=> 'Имя3',
                'ACTIVE' 		=> 'Y',
                'PROPERTY_TYPE' => 'S',
            ],
        ],
        'UF_FIELDS'  => [ // Массив пользовательских свойст ИБ (для секций)
            'PROP_CODE' => [ // без UF_
                'DELETE'        => true, // Удалить это свойство? (удалено будет перед тем как создано, таким образом можно пересоздать свойсво)
                'USER_TYPE_ID' 	=> 'boolean',
                'MULTIPLE' 		=> 'N',
                'MANDATORY' 	=> 'N',
                'SHOW_FILTER' 	=> 'N',
                'SHOW_IN_LIST' 	=> 'N',
                'EDIT_IN_LIST' 	=> 'Y',
                'IS_SEARCHABLE' => 'N',

                // Названия
                'EDIT_FORM_LABEL' 	=> ['ru' => 'Название', 'en' => 'Name'], // Подпись в форме редактирования
                'LIST_COLUMN_LABEL' => ['ru' => 'Название', 'en' => 'Name'], // Заголовок в списке
                'LIST_FILTER_LABEL' => ['ru' => 'Название', 'en' => 'Name'], // Подпись фильтра в списке
                'ERROR_MESSAGE'     => ['ru' => 'Название', 'en' => 'Name'], // Сообщение об ошибке (не обязательное)
                'HELP_MESSAGE'      => ['ru' => 'Название', 'en' => 'Name'], // Помощь

                // Дополнительные настройки поля (зависят от типа)
                'SETTINGS' 		=> [
                    // Да/Нет
                    'DEFAULT_VALUE' => 1,
                    'DISPLAY' 		=> 'CHECKBOX',
                ],
            ],
        ],
        'TABS'       => [
            'DELETE' => false,      // Удаляет кастумное отображение (игнорируется если есть UPDATE)
            'UPDATE' => true,       // Обновляет отображение вкладок, если свойство не задано, то садаёт отображение только при создании ИБ

            # Таб №1
            [
                'CODE'   => 'edit1',                // Конкретный код таба (если хочется использовать один из стандартных табов, список табов в комментах), для катумных казывается cedit*, где * - номер, при жэто поле не обязательное и будет автоматом сгенерированно
                'TITLE'  => 'Название таба',        // Если не задан или задан в true и указан CODE стандартного таба, то будет взято стандартное имя
                // Поля в табе
                'FIELDS' => [                       // Если вместо массива указать true или свойство вообще отсутсвует и указан CODE стандартного таба, то будет подставлен стандартный набор для этого раздлела, особенно актуально для SEO
                    'ACTIVE' => 'Активность',
                    'NAME'   => true,               // Если у стандартного поля указать имя true, то будет автоматом подставленно стандартное название
                    'edit1_csection1'   => '--Разделитель',     // Для заголовков блоков вначале нужно писать две черточки (--), а код должен быть уникальным для всех табов
                    //'PROPERTY_6'        => 'Новинка',           // Для свойств можно указать либо PROPERTY_*ID*, либо PROPERTY_*CODE*
                    //'PROPERTY_POST'     => true,                // Если указывается код, то допускается для автоподстановки имени указывать true
                ],
            ],
            # Таб №2 (SEO)
            [
                'CODE'   => 'edit14',
            ],
            # Таб №3
            [
                'TITLE'  => 'Новый таб',
                // Поля в табе
                'FIELDS' => [
                    'PROPERTY_PROP_CODE1'     => true,
                    'PROPERTY_PROP_CODE2'     => 'Кастумное название свойства',
                    'xxxxxxxxxxxxxx'          => '--Разделитель',
                    'PROPERTY_PROP_CODE3'     => true,
                ],
            ],
            # Таб №4
            [
                //...
            ],
        ],
    ],
    //... следующий ИБ
];


// Подвешиваем обработку ошибок
set_error_handler('exception_error_handler', E_RECOVERABLE_ERROR);

// Запуск транзакции
$DB->StartTransaction();

try {
    /*
     * Просто удобный сворачиватель
     * 0:)
     */
    try {

        /**
         * Проходимся по типам ИБ и обробатываем
         * ==================================================================
         */
        if(!empty($arIBlockTypes)) {
            foreach($arIBlockTypes AS &$arType) {
                // Ищем такой тип ИБ и получаем его свойства
                if($arRow = $obIBlockType->GetByID($arType['CODE'])->Fetch()) {
                    if(!empty($arType['DELETE'])) {
                        if(!$obIBlockType->Delete($arType['CODE']))
                            throw new Exception('Тип ИБ "'.$arType['LANG']['ru']['NAME'].' ['.$arType['CODE'].']" не удалось удалить.');
                    }
                }

                // Если нужно создать новый
                if(!empty($arType['PROPERTY'])) {
                    // Пополняем свойства ID (если оно задано, то наверно хотят сменить код ТИПА)
                    // TODO: можно дописать так, что бы при повторном запуске это учитывалось и не выдовало ошибку создания типа с одинаковым ID
                    if(empty($arType['PROPERTY']['ID']))
                        $arType['PROPERTY']['ID'] = $arType['CODE'];

                    // Если такой ИБ уже есть, то обновляем его
                    if($arRow && empty($arType['DELETE'])) {
                        if (!$obIBlockType->Update($arType['CODE'], $arType['PROPERTY']))
                            throw new Exception('Не удалось обновить тип ИБ "' . $arType['LANG']['ru']['NAME'] . ' [' . $arType['CODE'] . ']". Ошибка: ' . $obIBlockType->LAST_ERROR);
                    }
                    // Добавляем Тип заного
                    else {
                        if (!$obIBlockType->Add($arType['PROPERTY']))
                            throw new Exception('Не удалось добавить тип ИБ "' . $arType['LANG']['ru']['NAME'] . ' [' . $arType['CODE'] . ']". Ошибка: ' . $obIBlockType->LAST_ERROR);
                    }
                }

                // Очищаем
                unset($arRow);
            }
            unset($arType);

        } // Конец обработки типов ИБ




        /**
         * Проходимся по ИБ и обрабатываем
         * ==================================================================
         */
        if(!empty($arIBlocks)) {
            foreach($arIBlocks AS &$arIBlock) {
                try {
                    // Ищем такой ИБ и поулчаем его ID
                    if($arRow = $obIBlock->GetList([], ['=TYPE' => $arIBlock['TYPE'], '=CODE' => $arIBlock['CODE'], 'CHECK_PERMISSIONS' => 'N'])->Fetch()) {
                        // Дополняем массив
                        $arIBlock['ID']     = $arRow['ID'];
                        $arIBlock['NAME']   = $arRow['NAME'];

                        if(!empty($arIBlock['DELETE'])) {
                            if(!$obIBlock->Delete($arIBlock['ID']))
                                throw new Exception('Не удалось удалить.');

                            $arIBlock['DELETED']    = true;
                            $arIBlock['ID_DELETED'] = $arIBlock['ID'];
                            $arIBlock['ID']         = null;
                        }
                    }
                    unset($arRow);

                    /*
                     * Проверяем, нужно ли создать новый ИБ
                     * ------------------------------------------------------------------
                     */
                    if(!empty($arIBlock['CREATE'])) {
                        // Дополняем массив
                        $arIBlock['CREATE']['IBLOCK_TYPE_ID'] = $arIBlock['TYPE'];
                        // Проверяем, если код занят, то его наверно хотят сменить и не будем его тогда заменять
                        // TODO: учитывать это и обработать так, что бы не создавалось два ИБ с одинаковым кодом в одном типе при повторном запуске скрипта.
                        // TODO: Проверить возможность смены ID типа вообще :Р ибо в админке не меняется.
                        if(empty($arIBlock['CREATE']['CODE']))
                            $arIBlock['CREATE']['CODE'] = $arIBlock['CODE'];


                        // Если такой ИБ уже есть
                        if($arIBlock['ID']) {
                            // Обновляем его
                            if(!$obIBlock->Update($arIBlock['ID'], $arIBlock['CREATE']))
                                throw new Exception('Не удалось обновить. Причина: '. $obIBlock->LAST_ERROR);

                            // Дополняем массив
                            $arIBlock['NAME']    = $arIBlock['CREATE']['NAME']?:$arIBlock['NAME'];
                            $arIBlock['UPDATED'] = true;
                        }

                        // Создаем ИБ заного
                        else {
                            // Дополняем массив
                            $arIBlock['NAME']    = $arIBlock['CREATE']['NAME'];
                            $arIBlock['CREATED'] = true;

                            // Создаем
                            if(!$arIBlock['ID'] = $obIBlock->Add($arIBlock['CREATE']))
                                throw new Exception('Не удалось создать. Причина: '. $obIBlock->LAST_ERROR);
                        }
                    }

                } catch (Exception $ex) {
                    throw new Exception('Ошибка обработки ИБ "'.$arIBlock['NAME'].' ['.$arIBlock['TYPE'].' -> '.$arIBlock['CODE'].']"! <br>Ошибка: '. $ex->getMessage());
                }




                /*
                 * Проверяем, нужно ли создать пользовательские поля ИБ
                 * ------------------------------------------------------------------
                 */
                if(!empty($arIBlock['UF_FIELDS'])) {
                    // Проверяем ID ИБ
                    if(empty($arIBlock['ID']))
                        throw new Exception('ИБ "'.$arIBlock['TYPE'].' -> '.$arIBlock['CODE'].'" не существует.');


                    /*
                     * Проходимся по пользовательским полям ИБ
                     */
                    try {
                        foreach($arIBlock['UF_FIELDS'] AS $sCode => &$arField) {
                            // Пополняем данными о коде и ID ИБ
                            $arField['FIELD_NAME']   = 'UF_'.(preg_replace('/^UF_/i', '', $sCode));
                            $arField['ENTITY_ID']    = 'IBLOCK_'.$arIBlock['ID'].'_SECTION';

                            // Проверяем, а есть ли такое уже свойсто в этом ИБ
                            if($arRow = $obUserTypeEntity->GetList([], ['ENTITY_ID' => $arField['ENTITY_ID'], 'FIELD_NAME' => $arField['FIELD_NAME']])->Fetch()) {
                                // Дополняем массив
                                $arField['ID'] = $arRow['ID'];

                                // Проверяем, нужно ли удалить это свойсто
                                if(!empty($arField['DELETE'])) {
                                    unset($arField['ID']);

                                    if(!$obUserTypeEntity->Delete($arField['ID']))
                                        throw new Exception('Не удалось удалить.');
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
                                        throw new Exception('Не удалось добавить. Причина: '. $obUserTypeEntity->LAST_ERROR);
                                } else {
                                    // Обновить
                                    if(!$obUserTypeEntity->Update($arField['ID'], $arField))
                                        throw new Exception('Не удалось обновить. Причина: '. $obUserTypeEntity->LAST_ERROR);
                                }
                            }

                        }
                        unset($arField);
                    } catch (Exception $ex) {
                        throw new Exception('Ошибка обработки пользовательского поля "'.$arField['FIELD_NAME'].' ('.$arField['ENTITY_ID'].')" в ИБ "'.$arIBlock['TYPE'].' -> '.$arIBlock['CODE'].'"! <br>Ошибка: '. $ex->getMessage());
                    }

                } // Конец обхода пользовательских полей ИБ в цикле




                /*
                 * Проверяем, нужно ли создать свойства ИБ
                 * ------------------------------------------------------------------
                 */
                if(!empty($arIBlock['PROPERTIES'])) {
                    // Проверяем ID ИБ
                    if(empty($arIBlock['ID']))
                        throw new Exception('ИБ "'.$arIBlock['TYPE'].' -> '.$arIBlock['CODE'].'" не существует.');


                    /*
                     * Проходимся по свойствам ИБ
                     */
                    try {
                        foreach($arIBlock['PROPERTIES'] AS $sCode => &$arProperty) {
                            // Пополняем свойсво данными о коде и ID ИБ
                            $arProperty['CODE']      = $sCode;
                            $arProperty['IBLOCK_ID'] = $arIBlock['ID'];

                            // Проверяем, а есть ли такое уже свойсто в этом ИБ
                            if($arRow = $obIBlockProperty->GetList([], ['IBLOCK_ID' => $arIBlock['ID'], 'CODE' => $sCode])->Fetch()) {
                                // Дополняем массив
                                $arProperty['ID'] = $arRow['ID'];

                                // Проверяем, нужно ли удалить это свойсто
                                if(!empty($arProperty['DELETE'])) {
                                    if(!$obIBlockProperty->Delete($arProperty['ID']))
                                        throw new Exception('Не удалось удалить.');

                                    $arProperty['DELETED'] = true;
                                    unset($arProperty['ID']);
                                }

                                // Проверяем нужно ли обновить
                                elseif(!empty($arProperty['UPDATE'])) {
                                    // Очищаем массив от лишнего
                                    unset($arProperty['DELETE'], $arProperty['UPDATE']);

                                    if($arProperty['PROPERTY_TYPE'] == 'L') {
                                        $arEnumList  = getEnumList( $arProperty['IBLOCK_ID'], $sCode);
                                        $arNewValues = [];
                                        $iNewCount   = 0;
                                        foreach($arProperty['VALUES'] AS $arValue) {
                                            if($arEnumList[$arValue['XML_ID']])
                                                $arNewValues[$arEnumList[$arValue['XML_ID']]['ID']] = $arValue;
                                            else
                                                $arNewValues['n'. ++$iNewCount] = $arValue;
                                        }
                                        $arProperty['VALUES'] = $arNewValues;

                                        // Сохраняем список существующих свойств
                                        if($arProperty['LIST_TYPE'] == 'L' && $arProperty['SAVE_LIST']) {
                                            foreach ($arEnumList AS $arEnum) {
                                                if(!$arProperty['VALUES'][$arEnum['ID']])
                                                    $arProperty['VALUES'][$arEnum['ID']] = $arEnum;
                                            }

                                        }

                                        // Удаляем лишнее
                                        unset($arEnumList, $arNewValues, $iNewCount);

                                    }

                                    // Обновляем
                                    if(!$obIBlockProperty->Update($arProperty['ID'], $arProperty))
                                        throw new Exception($obIBlockProperty->LAST_ERROR);

                                    // Все ок, пропускаем добавление
                                    $arProperty['UPDATED'] = true;
                                    continue;
                                }

                                // Иначе проверяем, такое же ли было найдено свойство
                                elseif(
                                    $arProperty['PROPERTY_TYPE'] == $arRow['PROPERTY_TYPE']
                                    && $arProperty['USER_TYPE']  == $arRow['USER_TYPE']

                                ) {
                                    // Все ок, такое уже есть пропускаем добавление
                                    continue;
                                }
                                else
                                    throw new Exception('Данное свойсво уже существует, но у него другой тип. '
                                        .'['.$arProperty['PROPERTY_TYPE'] .':'. ($arProperty['USER_TYPE']?:'') .' => ' .$arRow['PROPERTY_TYPE'] .':'. ($arRow['USER_TYPE']?:'').']');
                            }
                            unset($arRow);

                            // Если нужно добавить и оно не было найдено или оно удалено, то добавляем
                            if($arProperty['NAME'] && !$arProperty['ID']) {
                                // Очищаем массив от лишнего
                                unset($arProperty['DELETE'], $arProperty['UPDATE']);

                                // Добавляем
                                if(!$arProperty['ID'] = $obIBlockProperty->Add($arProperty))
                                    throw new Exception($obIBlockProperty->LAST_ERROR);

                                $arProperty['CREATED'] = true;
                            }

                        }
                        unset($arProperty);
                    } catch (Exception $ex) {
                        throw new Exception('Ошибка обработки свойства "'.$arProperty['NAME'].' ('.$arProperty['CODE'].')" в ИБ "'.$arIBlock['TYPE'].' -> '.$arIBlock['CODE'].'"! <br>Ошибка: '. $ex->getMessage());
                    }

                } // Конец обхода свойств ИБ в цикле
                # Получаем все свойства ИБ, что бы можно было с ними потом работать, допустим в табах
                $rsResult = $obIBlockProperty->GetList(['SORT' => 'ASC'], ['IBLOCK_ID' => $arIBlock['ID']]);
                while($arRow = $rsResult->Fetch())
                    $arIBlock['PROPERTIES'][$arRow['CODE']] = ($arIBlock['PROPERTIES'][$arRow['CODE']]?:[]) + $arRow;
                unset($rsResult, $arRow);





                /*
                 * Проверяем, нужно ли создать разделы ИБ
                 * ------------------------------------------------------------------
                 */
                if(!empty($arIBlock['SECTIONS'])) {
                    // Проверяем ID ИБ
                    if(empty($arIBlock['ID']))
                        throw new Exception('ИБ "'.$arIBlock['TYPE'].' -> '.$arIBlock['CODE'].'" не существует.');


                    /*
                     * Проходимся по разделам ИБ
                     */
                    try {
                        foreach($arIBlock['SECTIONS'] AS $sCode => &$arSection) {
                            // Пополняем свойсво данными ID ИБ
                            $arSection['IBLOCK_ID'] = $arIBlock['ID'];
                            $arSection['CODE']      = $sCode;

                            // Проверяем раздел родителя, если это строка, то пробуем получить ID родителя которого мы уже добавляли
                            if(!empty($arSection['IBLOCK_SECTION_ID']) && $arSection['IBLOCK_SECTION_ID'] != (string)intval($arSection['IBLOCK_SECTION_ID'], 10)) {
                                // Проверяем ID раздела, если его нет, то ошибку выкидываем
                                if(empty($arIBlock['SECTIONS'][$arSection['IBLOCK_SECTION_ID']]['ID']))
                                    throw new Exception('ID раздела родителя не найден. ('.$arSection['IBLOCK_SECTION_ID'].')');

                                $arSection['IBLOCK_SECTION_ID'] = $arIBlock['SECTIONS'][$arSection['IBLOCK_SECTION_ID']]['ID'];
                            }

                            // Добавляем
                            if(!$arSection['ID'] = $obIBlockSection->Add($arSection))
                                throw new Exception($obIBlockSection->LAST_ERROR);

                        }
                        unset($arSection);
                    } catch (Exception $ex) {
                        throw new Exception('Ошибка обработки раздела "'.$arSection['NAME'].' ('.$arSection['CODE'].')" в ИБ "'.$arIBlock['TYPE'].' -> '.$arIBlock['CODE'].'"! <br>Ошибка: '. $ex->getMessage());
                    }

                } // Конец обхода разделов ИБ в цикле




                /*
                 * Проверяем, нужно ли создать табы отображения ИБ
                 * ------------------------------------------------------------------
                 */
                if(!empty($arIBlock['TABS'])) {
                    // Проверяем ID ИБ
                    if(empty($arIBlock['ID']))
                        throw new Exception('ИБ "'.$arIBlock['TYPE'].' -> '.$arIBlock['CODE'].'" не существует.');


                    /*
                     * Проходимся по табам ИБ
                     */
                    $bNeedUpdate   = $arIBlock['TABS']['UPDATE'] || ($arIBlock['CREATED'] && $arIBlock['TABS'][0]) ? true : false;
                    $bNeedDelete   = $arIBlock['TABS']['DELETE'] && !$bNeedUpdate         ? true : false;
                    $sCategory     = 'form';
                    $sOptionName   = "form_element_{$arIBlock['ID']}";
                    #PROPS_DEFAULT - дополняем тут и по этому хэштегу в описании ( не забываем про шаблоны файлов миграции )
                    $arTabsName    = [
                        'edit1'  => $arIBlock['CREATE']['ELEMENT_NAME']?:'Элемент',
                        'edit2'  => $arIBlock['CREATE']['SECTIONS_NAME']?:'Разделы',
                        'edit5'  => 'Анонс',
                        'edit6'  => 'Подробно',
                        'edit14' => 'SEO',
                        'edit9'  => 'Доступ',
                    ];
                    $arFieldsName  = [
                        'ID'                => 'ID',
                        'DATE_CREATE'       => 'Создан',
                        'TIMESTAMP_X'       => 'Изменен',
                        'ACTIVE'            => 'Активность',
                        'ACTIVE_FROM'       => 'Начало активности',
                        'ACTIVE_TO'         => 'Окончание активности',
                        'NAME'              => 'Название',
                        'CODE'              => 'Символьный код',
                        'XML_ID'            => 'Внешний код',
                        'SORT'              => 'Сортировка',
                        'PREVIEW_PICTURE'   => 'Картинка для анонса',
                        'PREVIEW_TEXT'      => 'Описание для анонса',
                        'DETAIL_PICTURE'    => 'Детальная картинка',
                        'DETAIL_TEXT'       => 'Детальное описание',
                        'IPROPERTY_TEMPLATES_ELEMENT_META_TITLE'       => 'Шаблон META TITLE',
                        'IPROPERTY_TEMPLATES_ELEMENT_META_KEYWORDS'    => 'Шаблон META KEYWORDS',
                        'IPROPERTY_TEMPLATES_ELEMENT_META_DESCRIPTION' => 'Шаблон META DESCRIPTION',
                        'IPROPERTY_TEMPLATES_ELEMENT_PAGE_TITLE'       => 'Заголовок элемента',
                        'IPROPERTY_TEMPLATES_ELEMENT_PREVIEW_PICTURE_FILE_ALT'    => 'Шаблон ALT',
                        'IPROPERTY_TEMPLATES_ELEMENT_PREVIEW_PICTURE_FILE_TITLE'  => 'Шаблон TITLE',
                        'IPROPERTY_TEMPLATES_ELEMENT_PREVIEW_PICTURE_FILE_NAME'   => 'Шаблон имени файла',
                        'IPROPERTY_TEMPLATES_ELEMENT_DETAIL_PICTURE_FILE_ALT'     => 'Шаблон ALT',
                        'IPROPERTY_TEMPLATES_ELEMENT_DETAIL_PICTURE_FILE_TITLE'   => 'Шаблон TITLE',
                        'IPROPERTY_TEMPLATES_ELEMENT_DETAIL_PICTURE_FILE_NAME'    => 'Шаблон имени файла',
                        'TAGS'              => 'Теги',
                        'SECTIONS'          => $arIBlock['CREATE']['SECTIONS_NAME']?:'Разделы',
                        'RIGHTS'            => 'Права доступа к элементу',
                        'CATALOG'           => 'Торговый каталог',
                    ];
                    $arDefaultTabsContent = [
                        // Элемент
                        'edit1' => [
                            'ID'            => true,
                            'DATE_CREATE'   => true,
                            'TIMESTAMP_X'   => true,
                            'ACTIVE'        => true,
                            'ACTIVE_FROM'   => true,
                            'ACTIVE_TO'     => true,
                            'NAME'          => true,
                            'CODE'          => true,
                            'XML_ID'        => true,
                            'SORT'          => true,
                        ] + (
                            !$arIBlock['PROPERTIES']? [] : ['IBLOCK_ELEMENT_PROP_VALUE'   => '--Значения свойств'] + array_combine(
                                array_map(function($v){return 'PROPERTY_'.$v['ID'];}, $arIBlock['PROPERTIES']),
                                array_map(function($v){return $v['NAME'];}, $arIBlock['PROPERTIES'])
                            )
                        ),
                        // Раздел
                        'edit2' => [
                            'SECTIONS' => true,
                        ],
                        // Анонс
                        'edit5' => [
                            'PREVIEW_PICTURE'   => true,
                            'PREVIEW_TEXT'      => true,
                        ],
                        // Подробно
                        'edit6' => [
                            'DETAIL_PICTURE'    => 'Детальная картинка',
                            'DETAIL_TEXT'       => 'Детальное описание',
                        ],
                        // SEO
                        'edit14' => [
                            'IPROPERTY_TEMPLATES_ELEMENT_META_TITLE'       => true,
                            'IPROPERTY_TEMPLATES_ELEMENT_META_KEYWORDS'    => true,
                            'IPROPERTY_TEMPLATES_ELEMENT_META_DESCRIPTION' => true,
                            'IPROPERTY_TEMPLATES_ELEMENT_PAGE_TITLE'       => true,
                            'IPROPERTY_TEMPLATES_ELEMENTS_PREVIEW_PICTURE'  => '--Настройки для картинок анонса элементов',
                            'IPROPERTY_TEMPLATES_ELEMENT_PREVIEW_PICTURE_FILE_ALT'    => true,
                            'IPROPERTY_TEMPLATES_ELEMENT_PREVIEW_PICTURE_FILE_TITLE'  => true,
                            'IPROPERTY_TEMPLATES_ELEMENT_PREVIEW_PICTURE_FILE_NAME'   => true,
                            'IPROPERTY_TEMPLATES_ELEMENTS_DETAIL_PICTURE'             => '--Настройки для детальных картинок элементов',
                            'IPROPERTY_TEMPLATES_ELEMENT_DETAIL_PICTURE_FILE_ALT'     => true,
                            'IPROPERTY_TEMPLATES_ELEMENT_DETAIL_PICTURE_FILE_TITLE'   => true,
                            'IPROPERTY_TEMPLATES_ELEMENT_DETAIL_PICTURE_FILE_NAME'    => true,
                            'SEO_ADDITIONAL' => '--Дополнительно',
                            'TAGS'           => true,
                        ],
                    ];


                    // Удаляем
                    if($bNeedDelete) {
                        if(!CUserOptions::DeleteOption($sCategory, $sOptionName, true, null))
                            throw new Exception('В ИБ "'.$arIBlock['TYPE'].' -> '.$arIBlock['CODE'].'" не удалось удалить настройку отображения табов.');
                    }

                    // Обновляем
                    if($bNeedUpdate) {
                        $arTabs = $arIBlock['TABS'];
                        unset($arTabs['DELETE'], $arTabs['UPDATE']);

                        if(count($arTabs)) {
                            $sTabsSettings = '';
                            $iCustomTabIndex = 0;
                            foreach($arTabs AS $arSection) {
                                // Определяем переменные для таба
                                $sTabCode  = $arSection['CODE']?: 'сedit'. (++$iCustomTabIndex);
                                $sTabTitle = ($arSection['TITLE'] && $arSection['TITLE'] !== true? $arSection['TITLE'] : ($arTabsName[$sTabCode]?: 'Вкладка '. $iCustomTabIndex));
                                $arFields  = ($arSection['FIELDS'] && $arSection['FIELDS'] !== true? $arSection['FIELDS'] : ($arDefaultTabsContent[$sTabCode]?: []));

                                // Начало Таба
                                $sTabsSettings .= "{$sTabCode}--#--{$sTabTitle}--";


                                // Наполнение таба
                                foreach($arFields AS $sCode => $sName) {
                                    // Если это стандартное поле
                                    if($arFieldsName[$sCode]) {
                                        // Автоматическая подстановка названия
                                        if($sName === true)
                                            $sName = $arFieldsName[$sCode];
                                    }
                                    // Если это свойство
                                    elseif (strpos($sCode, 'PROPERTY_') === 0) {
                                        $sPropCode = str_replace('PROPERTY_', '', $sCode);

                                        // Если есть такое свойство
                                        if($arIBlock['PROPERTIES'][$sPropCode]) {
                                            $sCode = 'PROPERTY_'. $arIBlock['PROPERTIES'][$sPropCode]['ID'];
                                            $sName = $sName !== true? $sName : $arIBlock['PROPERTIES'][$sPropCode]['NAME'];
                                        }
                                    }

                                    // Допником проверяем имя
                                    $sName = $sName !== true? $sName : $sCode;


                                    // Дополняем строку
                                    $sTabsSettings .= ",--{$sCode}--#--{$sName}--";
                                }


                                // Окончание таба
                                $sTabsSettings .= ';--';
                            }

                            // Добавляем
                            if(!CUserOptions::SetOption($sCategory, $sOptionName, ['tabs' => $sTabsSettings], true, null))
                                throw new Exception('В ИБ "'.$arIBlock['TYPE'].' -> '.$arIBlock['CODE'].'" не удалось добавить настройку отображения табов.');
                        }
                    }
                } // Конец обхода табов ИБ в цикле



            } // Конец обхода ИБ в цикле
            unset($arIBlock);

        } // Конец обработки ИБ

    }
    // Проброс ошибоки дальше
    catch( Exception $ex ) {
        throw $ex;
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









// Что бы можно было удобно сворачивать подсказки :)
if(0) {

    /**
     * ===================================================================
     * Перечень свойст у разных сущностей, можно дополнять
     *
     * --------------------------------------------
     * PS: НЕ ЗАБЫВАЕМ УДАЛЯТЬ ИЗ СКРИПТОВ МИГРАЦИИ
     * ===================================================================
     */

    /** =============== ИНФОБЛОКИ +++++++++++++++ */
    if (0) {

        /* Список полей для добавления -=: ТИПА ИБ :=- */
        /*

        ** Пример размещения в коде
        $arIBlockTypes = [
            ...
            [
                'CODE'      => 'КОД ТИПА ИБ',
                ...
                'PROPERTY'  => [
                    'SECTIONS' => 'Y',
                    'IN_RSS'   => 'N',
                    'SORT'     => 500,
                    'LANG'     => [
                        'ru' => [
                            'NAME'          => 'Имя',
                            'SECTION_NAME'  => 'Разделы',
                            'ELEMENT_NAME'  => 'Элементы',
                        ],
                        'en' => [
                            'NAME'          => 'Name',
                            'SECTION_NAME'  => 'Sections',
                            'ELEMENT_NAME'  => 'Elements',
                        ],
                    ]
                ],

                ...
            ],
            ...
        ];

        */


        /* Список полей для добавления -=: ИБ :=- */
        /*

        ** Пример размещения в коде
        $arIBlocks = [
            ...
            [
                'TYPE'      => 'ТИП ИБ',
                'CODE'      => 'КОД ИБ',
                ...

                'CREATE'       => [
                    'VERSION'           => 2,
                    'ACTIVE'            => 'Y', // (Y|N)
                    'NAME'              => 'Название',
                    'LIST_PAGE_URL'     => $LIST_PAGE_URL,
                    'SECTION_PAGE_URL'  => $SECTION_PAGE_URL,
                    'DETAIL_PAGE_URL'   => $DETAIL_PAGE_URL,
                    'INDEX_SECTION'     => 'Y',  // Индексировать разделы для модуля поиска (Y|N)
                    'INDEX_ELEMENT'     => 'Y', // Индексировать элементы для модуля поиска (Y|N)
                    'SITE_ID'           => ['en', 's1'],  // ОБЯЗАТЕЛЬНЫЙ - привязка к сайту
                    'LID'               => $LID,  // Еще одна привязка к сайту?
                    'SORT'              => $SORT,
                    'PICTURE'           => $arPICTURE,
                    'DESCRIPTION'       => $DESCRIPTION,
                    'DESCRIPTION_TYPE'  => $DESCRIPTION_TYPE,
                    'EDIT_FILE_BEFORE'  => $EDIT_FILE_BEFORE,
                    'EDIT_FILE_AFTER'   => $EDIT_FILE_AFTER,
                    'WORKFLOW'          => $WF_TYPE=='WF'? 'Y': 'N', // !!!!!!!!!!!!     по умолчанию Y
                    'BIZPROC'           => $WF_TYPE=='BP'? 'Y': 'N',
                    'SECTION_CHOOSER'   => $SECTION_CHOOSER,
                    'LIST_MODE'         => $LIST_MODE,
                    'RIGHTS_MODE'       => 'E',                            // Расширенный режим прав
                    'GROUP_ID'          => ['1' => 'X','2' => 'R',],       // Права доступа по группам (1 - Админ, 2 - Все юзеры; W - Изменение, Х - Полный доступ, R - Чтение, D - нет доступа)

                    // Названия для элементов
                    'ELEMENTS_NAME'     => 'Элементы',
                    'ELEMENT_NAME'      => 'Элемент',
                    'ELEMENT_ADD'       => 'Добавить элемент',
                    'ELEMENT_EDIT'      => 'Изменить элемент',
                    'ELEMENT_DELETE'    => 'Удалить элемент',

                    // Названия для разделов
                    'SECTIONS_NAME'     => 'Разделы',
                    'SECTION_NAME'      => 'Раздел',
                    'SECTION_ADD'       => 'Добавить раздел',
                    'SECTION_EDIT'      => 'Изменить раздел',
                    'SECTION_DELETE'    => 'Удалить раздел',


                    // Массив настроек стандартных свойст ИБ
                    'FIELDS'            => [

                        //
                        // ---------------------------
                        // Для Разделов
                        // ---------------------------
                        //
                        'SECTION_CODE'   => [
                            'IS_REQUIRED'   => 'N',
                            'DEFAULT_VALUE' => [
                                'UNIQUE'            => 'Y',     // Если код задан, то проверять на уникальность.
                                'TRANSLITERATION'   => 'Y',     // Транслитерировать из названия при добавлении элемента.
                                'TRANS_LEN'         => 100,         // Максимальная длина результата транслитерации
                                'TRANS_CASE'        => 'L',         // Приведение к регистру (L/U)
                                'TRANS_EAT'         => 'Y',         // Удалять лишние символы замены.
                                'TRANS_SPACE'       => '-',         // Замена для символа пробела
                                'TRANS_OTHER'       => '-',         // Замена для прочих символов
                                'USE_GOOGLE'        => '-',         // Использовать внешний сервис для перевода.
                            ]
                        ],
                        'SECTION_NAME'        => [
                            //'IS_REQUIRED'   => 'Y', // Y|N
                            'DEFAULT_VALUE' => '',
                        ],
                        'SECTION_XML_ID'  => [
                            'IS_REQUIRED'   => 'Y',    // Y|N
                            'DEFAULT_VALUE' => '',
                        ],
                        'SECTION_PICTURE' => [
                            'IS_REQUIRED'   => 'N', // Y|N
                            'DEFAULT_VALUE' => [
                                'FROM_DETAIL'        => 'Y',            // Создавать картинку анонса из детальной (если не задана).
                                'DELETE_WITH_DETAIL' => 'Y',            // Удалять картинку анонса, если удаляется детальная.
                                'UPDATE_WITH_DETAIL' => 'Y',            // Создавать картинку анонса из детальной даже если задана.
                                'SCALE'              => 'Y',            // Уменьшать если большая.
                                'WIDTH'              => 300,                // Максимальная ширина
                                'HEIGHT'             => 150,                // Максимальная высота
                                'IGNORE_ERRORS'      => 'N',                // Игнорировать ошибки масштабирования. (Y|N)
                                'METHOD'             => 'resample',         // Сохранять качество при масштабировании (требует больше ресурсов на сервере) (resample / '')
                                'COMPRESSION'        => 95,                 // Качество (только для JPEG, 1-100, по умолчанию около 75)
                                'USE_WATERMARK_FILE' => 'N',            // Наносить авторский знак в виде изображения. (Y|N)
                                'WATERMARK_FILE'     => '',                 // Путь к изображению с авторским знаком
                                'WATERMARK_FILE_ALPHA'      => '',          // Прозрачность авторского знака (%)
                                'WATERMARK_FILE_POSITION'   => '',          // Размещение авторского знака (tl \ tc \ tr \ ml \ mc \ mr \ bl \ bc \ br)
                                'USE_WATERMARK_TEXT' => '',             // Наносить авторский знак в виде текста
                                'WATERMARK_TEXT'            => 'N',         // Содержание надписи
                                'WATERMARK_TEXT_FONT'       => '',          // Путь к файлу шрифта
                                'WATERMARK_TEXT_COLOR'      => '',          // Цвет надписи (без #, например, FF00EE)
                                'WATERMARK_TEXT_SIZE'       => '',          // Размер (% от размера картинки)
                                'WATERMARK_TEXT_POSITION'   => '',          // Размещение авторского знака (tl \ tc \ tr \ ml \ mc \ mr \ bl \ bc \ br)
                            ]
                        ],
                        'SECTION_DETAIL_PICTURE' => [
                            'IS_REQUIRED'   => 'N', // Y|N
                            'DEFAULT_VALUE' => [
                                'SCALE'              => 'Y',            // Уменьшать если большая.
                                'WIDTH'              => 1280,               // Максимальная ширина
                                'HEIGHT'             => 720,                // Максимальная высота
                                'IGNORE_ERRORS'      => 'N',                // Игнорировать ошибки масштабирования. (Y|N)
                                'METHOD'             => 'resample',         // Сохранять качество при масштабировании (требует больше ресурсов на сервере) (resample / '')
                                'COMPRESSION'        => 95,                 // Качество (только для JPEG, 1-100, по умолчанию около 75)
                                'USE_WATERMARK_FILE' => 'N',            // Наносить авторский знак в виде изображения. (Y|N)
                                'WATERMARK_FILE'     => '',                 // Путь к изображению с авторским знаком
                                'WATERMARK_FILE_ALPHA'      => '',          // Прозрачность авторского знака (%)
                                'WATERMARK_FILE_POSITION'   => '',          // Размещение авторского знака (tl \ tc \ tr \ ml \ mc \ mr \ bl \ bc \ br)
                                'USE_WATERMARK_TEXT' => '',             // Наносить авторский знак в виде текста
                                'WATERMARK_TEXT'            => 'N',         // Содержание надписи
                                'WATERMARK_TEXT_FONT'       => '',          // Путь к файлу шрифта
                                'WATERMARK_TEXT_COLOR'      => '',          // Цвет надписи (без #, например, FF00EE)
                                'WATERMARK_TEXT_SIZE'       => '',          // Размер (% от размера картинки)
                                'WATERMARK_TEXT_POSITION'   => '',          // Размещение авторского знака (tl \ tc \ tr \ ml \ mc \ mr \ bl \ bc \ br)
                            ]
                        ],
                        'SECTION_DESCRIPTION_TYPE' => [
                            //'IS_REQUIRED'   => 'Y',  // Y|N
                            'DEFAULT_VALUE' => 'text', // text\html
                        ],
                        'SECTION_DESCRIPTION' => [
                            'IS_REQUIRED'   => 'Y',    // Y|N
                            'DEFAULT_VALUE' => '',
                        ],


                        //
                        // ---------------------------
                        // Для элементов
                        // ---------------------------
                        //
                        'IBLOCK_SECTION' => [
                            'IS_REQUIRED'   => 'N', // Y|N
                            'DEFAULT_VALUE' => [
                                'KEEP_IBLOCK_SECTION_ID' => 'N', // Y|N - Разрешить выбор основного раздела для привязки.
                            ]
                        ],
                        'ACTIVE'      => [
                            //'IS_REQUIRED'   => 'Y', // Y|N
                            'DEFAULT_VALUE' => 'Y', // Y|N
                        ],
                        'ACTIVE_FROM' => [
                            'IS_REQUIRED'   => 'N', // Y|N
                            'DEFAULT_VALUE' => '',  // =now \ =today \ ''
                        ],
                        'ACTIVE_TO'   => [
                            'IS_REQUIRED'   => 'N', // Y|N
                            'DEFAULT_VALUE' => '',  // Продолжительность активности элемента (дней)
                        ],
                        'SORT'        => [
                            'IS_REQUIRED'   => 'Y', // Y|N
                        ],
                        'NAME'        => [
                            //'IS_REQUIRED'   => 'Y', // Y|N
                            'DEFAULT_VALUE' => '',
                        ],
                        'PREVIEW_PICTURE' => [
                            'IS_REQUIRED'   => 'N', // Y|N
                            'DEFAULT_VALUE' => [
                                'FROM_DETAIL'        => 'Y',            // Создавать картинку анонса из детальной (если не задана).
                                'DELETE_WITH_DETAIL' => 'Y',            // Удалять картинку анонса, если удаляется детальная.
                                'UPDATE_WITH_DETAIL' => 'Y',            // Создавать картинку анонса из детальной даже если задана.
                                'SCALE'              => 'Y',            // Уменьшать если большая.
                                'WIDTH'              => 300,                // Максимальная ширина
                                'HEIGHT'             => 150,                // Максимальная высота
                                'IGNORE_ERRORS'      => 'N',                // Игнорировать ошибки масштабирования. (Y|N)
                                'METHOD'             => 'resample',         // Сохранять качество при масштабировании (требует больше ресурсов на сервере) (resample / '')
                                'COMPRESSION'        => 95,                 // Качество (только для JPEG, 1-100, по умолчанию около 75)
                                'USE_WATERMARK_FILE' => 'N',            // Наносить авторский знак в виде изображения. (Y|N)
                                'WATERMARK_FILE'     => '',                 // Путь к изображению с авторским знаком
                                'WATERMARK_FILE_ALPHA'      => '',          // Прозрачность авторского знака (%)
                                'WATERMARK_FILE_POSITION'   => '',          // Размещение авторского знака (tl \ tc \ tr \ ml \ mc \ mr \ bl \ bc \ br)
                                'USE_WATERMARK_TEXT' => '',             // Наносить авторский знак в виде текста
                                'WATERMARK_TEXT'            => 'N',         // Содержание надписи
                                'WATERMARK_TEXT_FONT'       => '',          // Путь к файлу шрифта
                                'WATERMARK_TEXT_COLOR'      => '',          // Цвет надписи (без #, например, FF00EE)
                                'WATERMARK_TEXT_SIZE'       => '',          // Размер (% от размера картинки)
                                'WATERMARK_TEXT_POSITION'   => '',          // Размещение авторского знака (tl \ tc \ tr \ ml \ mc \ mr \ bl \ bc \ br)
                            ]
                        ],
                        'DETAIL_PICTURE' => [
                            'IS_REQUIRED'   => 'N', // Y|N
                            'DEFAULT_VALUE' => [
                                'SCALE'              => 'Y',            // Уменьшать если большая.
                                'WIDTH'              => 1280,               // Максимальная ширина
                                'HEIGHT'             => 720,                // Максимальная высота
                                'IGNORE_ERRORS'      => 'N',                // Игнорировать ошибки масштабирования. (Y|N)
                                'METHOD'             => 'resample',         // Сохранять качество при масштабировании (требует больше ресурсов на сервере) (resample / '')
                                'COMPRESSION'        => 95,                 // Качество (только для JPEG, 1-100, по умолчанию около 75)
                                'USE_WATERMARK_FILE' => 'N',            // Наносить авторский знак в виде изображения. (Y|N)
                                'WATERMARK_FILE'     => '',                 // Путь к изображению с авторским знаком
                                'WATERMARK_FILE_ALPHA'      => '',          // Прозрачность авторского знака (%)
                                'WATERMARK_FILE_POSITION'   => '',          // Размещение авторского знака (tl \ tc \ tr \ ml \ mc \ mr \ bl \ bc \ br)
                                'USE_WATERMARK_TEXT' => '',             // Наносить авторский знак в виде текста
                                'WATERMARK_TEXT'            => 'N',         // Содержание надписи
                                'WATERMARK_TEXT_FONT'       => '',          // Путь к файлу шрифта
                                'WATERMARK_TEXT_COLOR'      => '',          // Цвет надписи (без #, например, FF00EE)
                                'WATERMARK_TEXT_SIZE'       => '',          // Размер (% от размера картинки)
                                'WATERMARK_TEXT_POSITION'   => '',          // Размещение авторского знака (tl \ tc \ tr \ ml \ mc \ mr \ bl \ bc \ br)
                            ]
                        ],
                        'PREVIEW_TEXT_TYPE' => [
                            //'IS_REQUIRED'   => 'Y',  // Y|N
                            'DEFAULT_VALUE' => 'text', // text\html
                        ],
                        'PREVIEW_TEXT_TYPE_ALLOW_CHANGE' => [
                            'DEFAULT_VALUE' => 'Y', // Y|N
                        ],
                        'PREVIEW_TEXT' => [
                            'IS_REQUIRED'   => 'Y',    // Y|N
                            'DEFAULT_VALUE' => '',
                        ],
                        'DETAIL_TEXT_TYPE'  => [
                            //'IS_REQUIRED'   => 'Y',  // Y|N
                            'DEFAULT_VALUE' => 'text', // text\html
                        ],
                        'DETAIL_TEXT_TYPE_ALLOW_CHANGE' => [
                            'DEFAULT_VALUE' => 'Y', // Y|N
                        ],
                        'DETAIL_TEXT' => [
                            'IS_REQUIRED'   => 'Y',    // Y|N
                            'DEFAULT_VALUE' => '',
                        ],
                        'XML_ID'  => [
                            'IS_REQUIRED'   => 'Y',    // Y|N
                            'DEFAULT_VALUE' => '',
                        ],
                        'TAGS'   => [
                            'IS_REQUIRED'   => 'Y',    // Y|N
                            'DEFAULT_VALUE' => '',
                        ],
                        'CODE'   => [
                            'IS_REQUIRED'   => 'N',
                            'DEFAULT_VALUE' => [
                                'UNIQUE'            => 'Y',     // Если код задан, то проверять на уникальность.
                                'TRANSLITERATION'   => 'Y',     // Транслитерировать из названия при добавлении элемента.
                                'TRANS_LEN'         => 100,         // Максимальная длина результата транслитерации
                                'TRANS_CASE'        => 'L',         // Приведение к регистру (L/U)
                                'TRANS_EAT'         => 'Y',         // Удалять лишние символы замены.
                                'TRANS_SPACE'       => '-',         // Замена для символа пробела
                                'TRANS_OTHER'       => '-',         // Замена для прочих символов
                                'USE_GOOGLE'        => '-',         // Использовать внешний сервис для перевода.
                            ]
                        ],

                    ],
                ],

                ...
            ],
            ...
        ];
        */



        /* Массив -=: СВОЙСТ РАЗДЕЛА :=- */
        /*

          $arFields = [
              "ACTIVE"              => $ACTIVE,
              "IBLOCK_SECTION_ID"   => $IBLOCK_SECTION_ID,
              "IBLOCK_ID"           => $IBLOCK_ID,
              "NAME"                => $NAME,
              "SORT"                => $SORT,
              "CODE"                => $_POST["CODE"],
              "PICTURE"             => $arPICTURE,
              "DETAIL_PICTURE"      => $arDETAIL_PICTURE,
              "DESCRIPTION"         => $DESCRIPTION,
              "DESCRIPTION_TYPE"    => $DESCRIPTION_TYPE,
         );
         */



        /* Перечень настроек для создания -=: СВОЙСТВА ИБ :=- */
        /*

         $arDefPropInfo = [
            'ID' => 0,
            //'IBLOCK_ID' => 0,
            'FILE_TYPE' => '',
            'LIST_TYPE'     => 'L',     // (C - флажки; L - список) ЗЫ: не воткните случайно русскую С!
            'SAVE_LIST'     => true,    // Сохраняет текущие значения списка, которых нет в VALUES. Значения из VALUES актуализируются (обновляются)
            'ROW_COUNT' => '1',
            'COL_COUNT' => '30',
            'LINK_IBLOCK_ID' => '0',
            'DEFAULT_VALUE' => '',
            'USER_TYPE_SETTINGS' => [
                // для HL
                'TABLE_NAME' => 'b_hl_club_features_table',
            ],
            'WITH_DESCRIPTION' => '',
            'SEARCHABLE' => '',
            'FILTRABLE' => '',
            'ACTIVE' => 'Y',
            'MULTIPLE_CNT' => '5',
            'XML_ID' => '',
            'PROPERTY_TYPE' => 'S',
            'NAME' => '',
            'HINT' => '',
            'USER_TYPE' => '',
            'MULTIPLE' => 'N',
            'IS_REQUIRED' => 'N',
            'SORT' => '500',
            'CODE' => '',
            'SHOW_DEL' => 'N',
            'VALUES' => [],
            'SECTION_PROPERTY' => 'Y',
            'SMART_FILTER' => 'N',
            'TABLE_NAME' => 'b_hl_club_features_table',
        );

        */



        /* Массив для списка (L) */
        /*
         'VALUES' => [
            [
                'XML_ID'    => '',
                'VALUE'     => '',
                'SORT'      => '',
                'DEF'       => 'N',
            ],
         ]
         */



        /* Типы свойст ИБ (PROPERTY_TYPE:USER_TYPE) */
        /*

        S               - Строка
        N               - Число
        L               - Список
        F               - Файл
        G               - Привязка к разделам
        E               - Привязка к элементам
        S:HTML          - HTML/текст
        S:video         - Видео
        S:Date          - Дата
        S:DateTime      - Дата/Время
        S:Money         - Деньги
        S:map_yandex    - Привязка к Яндекс.Карте
        S:map_google    - Привязка к карте Google Maps
        S:UserID        - Привязка к пользователю
        G:SectionAuto   - Привязка к разделам с автозаполнением
        S:employee      - Привязка к сотруднику
        S:TopicID       - Привязка к теме форума
        E:SKU           - Привязка к товарам (SKU)
        S:FileMan       - Привязка к файлу (на сервере)
        S:ECrm          - Привязка к элементам CRM
        E:EList         - Привязка к элементам в виде списка
        S:ElementXmlID  - Привязка к элементам по XML_ID
        E:EAutocomplete - Привязка к элементам с автозаполнением
        S:directory     - Справочник
        N:Sequence      - Счетчик



        */



        /* Настройка отображения табов (TABS) */
        /*
         * Позволяет настроить вкладки ИБ для отображения в админке
         *
         * TODO - дополнить тут список стандартными полями и табами, допустим из торговых предложений, а также в самом коде по хэштегу #PROPS_DEFAULT
         *

        ** Список стандартных вкладок:
         - 'edit1'  => 'Элемент',
         - 'edit2'  => 'Разделы',
         - 'edit5'  => 'Анонс',
         - 'edit6'  => 'Подробно',
         - 'edit14' => 'SEO',
         - 'edit9'  => 'Доступ',

        ** Список стандартных полей:
         - 'ID'                => 'ID',
         - 'DATE_CREATE'       => 'Создан',
         - 'TIMESTAMP_X'       => 'Изменен',
         - 'ACTIVE'            => 'Активность',
         - 'ACTIVE_FROM'       => 'Начало активности',
         - 'ACTIVE_TO'         => 'Окончание активности',
         - 'NAME'              => 'Название',
         - 'CODE'              => 'Символьный код',
         - 'XML_ID'            => 'Внешний код',
         - 'SORT'              => 'Сортировка',
         - 'PREVIEW_PICTURE'   => 'Картинка для анонса',
         - 'PREVIEW_TEXT'      => 'Описание для анонса',
         - 'DETAIL_PICTURE'    => 'Детальная картинка',
         - 'DETAIL_TEXT'       => 'Детальное описание',
         - 'IPROPERTY_TEMPLATES_ELEMENT_META_TITLE'       => 'Шаблон META TITLE',
         - 'IPROPERTY_TEMPLATES_ELEMENT_META_KEYWORDS'    => 'Шаблон META KEYWORDS',
         - 'IPROPERTY_TEMPLATES_ELEMENT_META_DESCRIPTION' => 'Шаблон META DESCRIPTION',
         - 'IPROPERTY_TEMPLATES_ELEMENT_PAGE_TITLE'       => 'Заголовок элемента',
         - 'IPROPERTY_TEMPLATES_ELEMENT_PREVIEW_PICTURE_FILE_ALT'    => 'Шаблон ALT',
         - 'IPROPERTY_TEMPLATES_ELEMENT_PREVIEW_PICTURE_FILE_TITLE'  => 'Шаблон TITLE',
         - 'IPROPERTY_TEMPLATES_ELEMENT_PREVIEW_PICTURE_FILE_NAME'   => 'Шаблон имени файла',
         - 'IPROPERTY_TEMPLATES_ELEMENT_DETAIL_PICTURE_FILE_ALT'     => 'Шаблон ALT',
         - 'IPROPERTY_TEMPLATES_ELEMENT_DETAIL_PICTURE_FILE_TITLE'   => 'Шаблон TITLE',
         - 'IPROPERTY_TEMPLATES_ELEMENT_DETAIL_PICTURE_FILE_NAME'    => 'Шаблон имени файла',
         - 'TAGS'              => 'Теги',
         - 'SECTIONS'          => $arIBlock['CREATE']['SECTIONS_NAME']?:'Разделы',
         - 'RIGHTS'            => 'Права доступа к элементу',
         - 'CATALOG'           => 'Торговый каталог',


        ** Пример размещения в коде
        $arIBlocks = [
            ...
            [
                'TYPE'      => 'ТИП ИБ',
                'CODE'      => 'КОД ИБ',
                ...

                'TABS'       => [
                    'DELETE' => true,       // Удаляет кастумное отображение (игнорируется если есть UPDATE)
                    'UPDATE' => true,       // Обновляет отображение вкладок, если свойство не задано, то садаёт отображение только при создании ИБ

                    # Таб №1
                    [
                        'CODE'   => 'edit1',                // Конкретный код таба (если хочется использовать один из стандартных табов, список табов в комментах), для катумных казывается cedit*, где * - номер, при жэто поле не обязательное и будет автоматом сгенерированно
                        'TITLE'  => 'Название таба',        // Если не задан или задан в true и указан CODE стандартного таба, то будет взято стандартное имя
                        // Поля в табе
                        'FIELDS' => [                       // Если вместо массива указать true или свойство вообще отсутсвует и указан CODE стандартного таба, то будет подставлен стандартный набор для этого раздлела, особенно актуально для SEO
                            'ACTIVE' => 'Активность',
                            'NAME'   => true,               // Если у стандартного поля указать имя true, то будет автоматом подставленно стандартное название
                            'edit1_csection1'   => '--Разделитель',     // Для заголовков блоков вначале нужно писать две черточки (--), а код должен быть уникальным для всех табов
                            'PROPERTY_6'        => 'Новинка',           // Для свойств можно указать либо PROPERTY_*ID*, либо PROPERTY_*CODE*
                            'PROPERTY_POST'     => true,                // Если указывается код, то допускается для автоподстановки имени указывать true
                        ],
                    ],
                    # Таб №2
                    [
                        //...
                    ],
                ],

                ...
            ],
            ...
        ];

         */


        /** =============== END: ИНФОБЛОКИ +++++++++++++++ */
    }





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

}