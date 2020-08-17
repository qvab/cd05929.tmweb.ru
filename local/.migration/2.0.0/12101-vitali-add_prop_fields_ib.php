<?php
/*
 * Добавляет свойство "Минимальная удаленность, км в ИБ запросов"
 */

// Подключение Битрикса
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

// Проверка на админа
if(!$USER->IsAdmin())
    die();

// Подключаем необходимые модули
if(!CModule::IncludeModule("iblock"))
    die('Module "IBlock" not found!');


/** Конвертор ошибки в исключение */
if(!function_exists('exception_error_handler')) {
    function exception_error_handler($errno, $errstr, $errfile, $errline ) {
        throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
    }
}

// Создаем объекты
$obIBlockType       = new CIBlockType;
$obIBlock           = new CIBlock();
$obIBlockProperty   = new CIBlockProperty();
$obIBlockSection    = new CIBlockSection();
$obUserTypeEntity   = new CUserTypeEntity();

/**
 * Список Типов ИБ с их свойствами
 * Не ассоциативный массив, где элемент массива является массивом со свойствами для создания Типа ИБ
 */
$arIBlockTypes = array();


/*
 * Массив свойст ИБ
 * Записываются здесь, а не в ИБ если они повторяются в разных ИБ
 */
$arPropsData = array();



/**
 * Список ИБ с их свойствами
 * Не ассоциативный массив, где элемент массива является массивом со свойствами ИБ
 */
$arIBlocks = array(
    array(
        'TYPE'       => 'client',
        'CODE'       => 'client_request',
        'PROPERTIES' => array( // Массив свойст ИБ
            'MIN_REMOTENESS' => array(
                'UPDATE'        => true,
                'NAME'			=> 'Минимальная удаленность, км',
                'ACTIVE' 		=> 'Y',
                'PROPERTY_TYPE' => 'S',
                'HINT'			=> 'Минимальная удаленность доставки, км',
                'MULTIPLE'      => 'N',
                'IS_REQUIRED'   => 'N',
                'SORT'          => 650,
            ),
        ),
    ),
);




// Подвешиваем обработку ошибок
set_error_handler('exception_error_handler', E_RECOVERABLE_ERROR);

// Запуск транзакции
$DB->StartTransaction();

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
                    if($obIBlockType->Delete($arType['CODE']))
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

    } // Конец обработки типов ИБ




    /**
     * Проходимся по ИБ и обрабатываем
     * ==================================================================
     */
    if(!empty($arIBlocks)) {
        foreach($arIBlocks AS &$arIBlock) {
            try {
                // Ищем такой ИБ и поулчаем его ID
                if($arRow = $obIBlock->GetList(array(), array('TYPE' => $arIBlock['TYPE'], 'CODE' => $arIBlock['CODE']))->Fetch()) {
                    // Дополняем массив
                    $arIBlock['ID']     = $arRow['ID'];
                    $arIBlock['NAME']   = $arRow['NAME'];

                    if(!empty($arIBlock['DELETE'])) {
                        if($obIBlock->Delete($arIBlock['ID']))
                            throw new Exception('Не удалось удалить.');
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
                    if($arIBlock['ID'] && empty($arIBlock['DELETE'])) {
                        // Обновляем его
                        if(!$obIBlock->Update($arIBlock['ID'], $arIBlock['CREATE']))
                            throw new Exception('Не удалось обновить. Причина: '. $obIBlock->LAST_ERROR);

                        // Дополняем массив
                        $arIBlock['NAME'] = $arIBlock['CREATE']['NAME']?:$arIBlock['NAME'];
                    }

                    // Создаем ИБ заного
                    else {
                        // Дополняем массив
                        $arIBlock['NAME'] = $arIBlock['CREATE']['NAME'];

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
                        if($arRow = $obUserTypeEntity->GetList(array(), array('ENTITY_ID' => $arField['ENTITY_ID'], 'FIELD_NAME' => $arField['FIELD_NAME']))->Fetch()) {
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
                        if($arRow = $obIBlockProperty->GetList(array(), array('IBLOCK_ID' => $arIBlock['ID'], 'CODE' => $sCode))->Fetch()) {
                            // Дополняем массив
                            $arProperty['ID'] = $arRow['ID'];

                            // Проверяем, нужно ли удалить это свойсто
                            if(!empty($arProperty['DELETE'])) {
                                unset($arProperty['ID']);

                                if(!$obIBlockProperty->Delete($arProperty['ID']))
                                    throw new Exception('Не удалось удалить.');
                            }

                            // Проверяем нужно ли обновить
                            elseif(!empty($arProperty['UPDATE'])) {
                                // Очищаем массив от лишнего
                                unset($arProperty['DELETE'], $arProperty['UPDATE']);

                                // Обновляем
                                if(!$obIBlockProperty->Update($arProperty['ID'], $arProperty))
                                    throw new Exception($obIBlockProperty->LAST_ERROR);

                                // Все ок, пропускаем добавление
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
                        }

                    }
                } catch (Exception $ex) {
                    throw new Exception('Ошибка обработки свойства "'.$arProperty['NAME'].' ('.$arProperty['CODE'].')" в ИБ "'.$arIBlock['TYPE'].' -> '.$arIBlock['CODE'].'"! <br>Ошибка: '. $ex->getMessage());
                }

            } // Конец обхода свойств ИБ в цикле




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
                } catch (Exception $ex) {
                    throw new Exception('Ошибка обработки раздела "'.$arSection['NAME'].' ('.$arSection['CODE'].')" в ИБ "'.$arIBlock['TYPE'].' -> '.$arIBlock['CODE'].'"! <br>Ошибка: '. $ex->getMessage());
                }

            } // Конец обхода разделов ИБ в цикле


        } // Конец обхода ИБ в цикле

    } // Конец обработки ИБ



    /**
     * ==================================================================
     * Дополнительная логика миграции
     * Создание Элементов, парс данных, перенос чего нибудь - куда нибудь...
     * ==================================================================
     */






}
// Обработка ошибок
catch( Exception $ex ) {
    // Откат изменений
    $DB->Rollback();
    echo 'Завершено с ошибкой: ' . $ex->getMessage();
    die();
}


// Сохранение данных
$DB->Commit();

// Выводим сообщение
echo 'Выполнено успешно! '.date('(H:i:s)');
echo '<br>Файл миграции: '.__FILE__;

