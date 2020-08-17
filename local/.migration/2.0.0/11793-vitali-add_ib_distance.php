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
$arIBlockTypes = [];





/*
 * Массив свойст ИБ
 * Записываются здесь, а не в ИБ если они повторяются в разных ИБ
 */
$arPropsData = [];



/**
 * Список ИБ с их свойствами
 * Не ассоциативный массив, где элемент массива является массивом со свойствами ИБ
 */
$arIBlocks = [
    // Имя типа ИБ -> Имя ИБ (что бы легко было найти и проверить в АДМИНКЕ)
    [
        'TYPE'      => 'directories',
        'CODE'      => 'distance',
        'CREATE'    => [   // Если это свойство задано, то если такого ИБ нет то он будет создан
            'VERSION' => 2,
            'SITE_ID' => ['s1'],
            'WORKFLOW'=> 'N',
            'BIZPROC' => 'N',
            'ACTIVE'  => 'Y',
            'NAME'    => 'Расстояния',
            'GROUP_ID'=> ['1' => 'X','2' => 'R',],
            // и другие свойства для создания (список в низу файла, код и тип не указывать)
        ],
        'PROPERTIES' => [ // Массив свойст ИБ
            'MIN' => [
                'UPDATE'        => true, // Обновить это свойство?
                'NAME'			=> 'Минимальное значение км.',
                'ACTIVE' 		=> 'Y',
                'PROPERTY_TYPE' => 'N',
                'IS_REQUIRED'   => 'Y',
            ],
            'MAX' => [
                'UPDATE'        => true, // Обновить это свойство?
                'NAME'			=> 'Максимальное значение км.',
                'ACTIVE' 		=> 'Y',
                'PROPERTY_TYPE' => 'N',
                'IS_REQUIRED'   => 'Y',
            ],
        ],
        'TABS'       => [
            'DELETE' => false,      // Удаляет кастумное отображение (игнорируется если есть UPDATE)
            'UPDATE' => true,       // Обновляет отображение вкладок, если свойство не задано, то садаёт отображение только при создании ИБ

            # Таб №1
            [
                'CODE'   => 'edit1',                // Конкретный код таба (если хочется использовать один из стандартных табов, список табов в комментах), для катумных казывается cedit*, где * - номер, при жэто поле не обязательное и будет автоматом сгенерированно
                'TITLE'  => 'Расстояние',        // Если не задан или задан в true и указан CODE стандартного таба, то будет взято стандартное имя
                // Поля в табе
                'FIELDS' => [                       // Если вместо массива указать true или свойство вообще отсутсвует и указан CODE стандартного таба, то будет подставлен стандартный набор для этого раздлела, особенно актуально для SEO
                    'ACTIVE' => 'Активность',
                    'NAME'   => true,               // Если у стандартного поля указать имя true, то будет автоматом подставленно стандартное название
                    'edit1_csection1'   => '--Интервал',     // Для заголовков блоков вначале нужно писать две черточки (--), а код должен быть уникальным для всех табов
                    'PROPERTY_MIN'        => true,           // Для свойств можно указать либо PROPERTY_*ID*, либо PROPERTY_*CODE*
                    'PROPERTY_MAX'     => true,                // Если указывается код, то допускается для автоподстановки имени указывать true
                ],
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


    $iSort = 0;
    $arElement = [
        [
            'NAME'  => 'До 200',
            'MIN'   => '0',
            'MAX'   => '200',
            'SORT'  => ($iSort +=10),
        ],
        [
            'NAME'  => 'От 200 до 500',
            'MIN'   => '200',
            'MAX'   => '500',
            'SORT'  => ($iSort +=10),
        ],
        [
            'NAME'  => 'От 500 до 750',
            'MIN'   => '500',
            'MAX'   => '750',
            'SORT'  => ($iSort +=10),
        ],
        [
            'NAME'  => 'От 750 до 1000',
            'MIN'   => '750',
            'MAX'   => '1000',
            'SORT'  => ($iSort +=10),
        ],
        [
            'NAME'  => 'Более 1000',
            'MIN'   => '1000',
            'MAX'   => '0',
            'SORT'  => ($iSort +=10),
        ],
    ];

    foreach ($arElement as $arElementItem) {

        $arEl = $obIBlockElement->GetList(
            [],
            [
                'IBLOCK_ID' => getIBlockID('directories', 'distance'),
                'NAME'      => $arElementItem['NAME'],
            ],
            false,
            false,
            ['ID',]
        )->Fetch();


        $arFields = [
            'NAME'      => $arElementItem['NAME'],
            'ACTIVE'    => 'Y',
            'IBLOCK_ID' => getIBlockID('directories', 'distance'),
            'SORT'      => $arElementItem['SORT'],
            'PROPERTY_VALUES' => [
                'MIN' => $arElementItem['MIN'],
                'MAX' => $arElementItem['MAX'],
            ],
        ];

        if(empty($arEl['ID'])) {

            if(!$obIBlockElement->Add($arFields)) {
                throw new Exception('Ошибка добавления элемента. ' . $obIBlockElement->LAST_ERROR);
            }
        } else {
            if(!$obIBlockElement->Update($arEl['ID'], $arFields)) {
                throw new Exception('Ошибка обновления элемента. ' . $obIBlockElement->LAST_ERROR);
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