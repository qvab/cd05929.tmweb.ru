<?php

/**
 *  Класс для работы с документами участников системы
 */
class CDocument {

    private $obIB   = null;
    private $obEl   = null;
    private $obFile = null;

    private $arDocumentsClient = [];
    private $arDocumentsFarmer = [];


    public function __construct() {

        // Подключаем необходимые модули
        if(!CModule::IncludeModule('iblock')) {
            throw new Exception('Не удалось подключить модуль "iblock"');
        }

        $this->obIB     = new CIBlock;
        $this->obEl     = new CIBlockElement;
        $this->obFile   = new CFile;
    }


    /**
     * Проверяет комплект документов участника системы
     * @param $iUserId
     * @param $sRoleType
     * @return mixed
     * @throws Exception
     */
    public function isFullSet($iUserId, $sRoleType) {

        $bResult = null;

        switch ($sRoleType) {
            case 'client':
                if(client::checkIsClient($iUserId)) {
                    $arDocuments = $this->getDocumentsClient($iUserId);
                    $bResult = $arDocuments['IS_FULL_SET'];
                }
                break;

            case 'farmer':
                if(farmer::checkIsFarmer($iUserId)) {
                    $arDocuments = $this->getDocumentsFarmer($iUserId);
                    $bResult = $arDocuments['IS_FULL_SET'];
                }
                break;
                // TODO! - При необходимости расширить свитч по другим ролям
            default:
                throw new Exception('Неизвестный тип роли пользователя');
        }

        return $bResult;
    }


    /**
     * Отдает документы поставщика
     * @param $iFarmerId
     * @return array
     */
    public function getDocumentsFarmer($iFarmerId) {


        if(!empty($this->arDocumentsFarmer[$iFarmerId])) {
            return $this->arDocumentsFarmer[$iFarmerId];
        }

        $arResult = [
            'DOCUMENTS'                 => [],      // Пакет документов
            'LIST_MISSING_DOCUMENTS'    => [],      // Список недостающих документов АП
            'IS_FULL_SET'               => true,    // Признак полного пакета документов
            'ERROR'                     => null,    // Ошибки
        ];

        try {

            // ИД ИБ профили АП
            $iIblockId = getIBlockID('farmer', 'farmer_profile');
            if(empty($iIblockId)) {
                throw new Exception('Не удалось получить ИД ИБ профилей АП');
            }

            // Поля выборки
            $arSelect = ['ID', 'PROPERTY_UL_TYPE', 'PROPERTY_NDS',];

            // Получаем св-ва типа файл и добавляем их в выборку
            $rs = $this->obIB->GetProperties($iIblockId, [], ['PROPERTY_TYPE' => 'F']);
            while ($arRow = $rs->Fetch()) {
                $arSelect[] = 'PROPERTY_' . $arRow['CODE'];
            }

            // Получаем поля АП из профиля
            $arFarmer = $this->obEl->GetList(
                [],
                [
                    'IBLOCK_ID'     => $iIblockId,
                    'PROPERTY_USER' => $iFarmerId,
                ],
                false,
                false,
                $arSelect
            )->Fetch();


            if(empty($arFarmer)) {
                throw new Exception('Не удалось получить профиль поставщика');
            }

            if(empty($arFarmer['PROPERTY_UL_TYPE_ENUM_ID'])) {
                throw new Exception('Не задан Тип ЮЛ для поставщика['.$iFarmerId.']');
            }

            if(empty($arFarmer['PROPERTY_NDS_VALUE'])) {
                throw new Exception('Не задан тип налогообложения для поставщика');
            }

            $arUlType = rrsIblock::getPropListId('farmer_profile', 'UL_TYPE');
            if(empty($arUlType) || empty($arUlType[$arFarmer['PROPERTY_UL_TYPE_ENUM_ID']]['XML_ID'])) {
                throw new Exception('Не удалось получить значения списка "Тип ЮЛ"');
            }

            // Получаем набор документов для АП
            $arListDocumentType = $this->getDocumentTypeByRole('farmer');
            if(empty($arListDocumentType)) {
                throw new Exception('Не удалось получить набор документов для поставщика');
            }

            // Формируем список документов в зависимости от параметров профиля АП
            foreach ($arListDocumentType as $sCodeDocument => $arDocumentType) {

                if($arDocumentType['UL_TYPE_XML_ID'] != $arUlType[$arFarmer['PROPERTY_UL_TYPE_ENUM_ID']]['XML_ID']) {
                    continue;
                }

                $arDocument = [
                    'NAME'          => $arDocumentType['NAME'],
                    'FULL_NAME'     => $arDocumentType['FULL_NAME'],
                    'PATH'          => null,
                    'IS_REQUIRED'   => false,
                ];

                // Если у документа задан тип НДС проверяем обязателен ли он для покупателя
                if(!empty($arDocumentType['NDS_TYPE']) && in_array($arFarmer['PROPERTY_NDS_VALUE'], $arDocumentType['NDS_TYPE'])) {
                    $arDocument['IS_REQUIRED'] = true;
                }

                $iFileId = intval($arFarmer['PROPERTY_' . $sCodeDocument . '_VALUE']);
                if(!empty($iFileId)) {
                    $arDocument['PATH'] = $this->obFile->GetPath($iFileId);
                }

                if($arDocument['IS_REQUIRED'] && empty($arDocument['PATH'])) {
                    $arResult['IS_FULL_SET'] = false;
                    $arResult['LIST_MISSING_DOCUMENTS'][$sCodeDocument] = $arDocument['NAME'];
                }

                $arResult['DOCUMENTS'][$sCodeDocument] = $arDocument;
            }
        } catch (Exception $e) {
            $arResult['ERROR'] = $e->getMessage();
        }

        $this->arDocumentsFarmer[$iFarmerId] =& $arResult;

        return $this->arDocumentsFarmer[$iFarmerId];
    }


    /**
     * Отдает документы покупателя
     * @param $iClientId
     * @return array
     */
    public function getDocumentsClient($iClientId) {

        if(!empty($this->arDocumentsClient[$iClientId])) {
            return $this->arDocumentsClient[$iClientId];
        }

        $arResult = [
            'DOCUMENTS'                 => [],      // Пакет документов
            'LIST_MISSING_DOCUMENTS'    => [],      // Список недостающих документов клиента
            'IS_FULL_SET'               => true,    // Признак полного пакета документов
            'ERROR'                     => null,    // Ошибки
        ];

        try {

            // ИД ИБ профили покупателей
            $iIblockId = getIBlockID('client', 'client_profile');
            if(empty($iIblockId)) {
                throw new Exception('Не удалось получить ИД ИБ профилей покупателя');
            }

            // Поля выборки
            $arSelect = ['ID', 'PROPERTY_UL_TYPE', 'PROPERTY_NDS',];

            // Получаем св-ва типа файл и добавляем их в выборку
            $rs = $this->obIB->GetProperties($iIblockId, [], ['PROPERTY_TYPE' => 'F']);
            while ($arRow = $rs->Fetch()) {
                $arSelect[] = 'PROPERTY_' . $arRow['CODE'];
            }

            // Получаем поля покупателя из профиля
            $arClient = $this->obEl->GetList(
                [],
                [
                    'IBLOCK_ID'     => $iIblockId,
                    'PROPERTY_USER' => $iClientId,
                ],
                false,
                false,
                $arSelect
            )->Fetch();

            if(empty($arClient)) {
                throw new Exception('Не удалось получить профиль покупателя');
            }

            if(empty($arClient['PROPERTY_UL_TYPE_ENUM_ID'])) {
                throw new Exception('Не задан Тип ЮЛ для покупателя['.$iClientId.']');
            }

            if(empty($arClient['PROPERTY_NDS_VALUE'])) {
                throw new Exception('Не задан тип налогообложения для клиента');
            }

            $arUlType = rrsIblock::getPropListId('client_profile', 'UL_TYPE');
            if(empty($arUlType) || empty($arUlType[$arClient['PROPERTY_UL_TYPE_ENUM_ID']]['XML_ID'])) {
                throw new Exception('Не удалось получить значения списка "Тип ЮЛ"');
            }

            // Получаем набор документов для покупателя
            $arListDocumentType = $this->getDocumentTypeByRole('client');
            if(empty($arListDocumentType)) {
                throw new Exception('Не удалось получить набор документов для покупателя');
            }

            // Формируем список документов в зависимости от параметров профиля клиента
            foreach ($arListDocumentType as $sCodeDocument => $arDocumentType) {

                if($arDocumentType['UL_TYPE_XML_ID'] != $arUlType[$arClient['PROPERTY_UL_TYPE_ENUM_ID']]['XML_ID']) {
                    continue;
                }

                $arDocument = [
                    'NAME'          => $arDocumentType['NAME'],
                    'FULL_NAME'     => $arDocumentType['FULL_NAME'],
                    'PATH'          => null,
                    'IS_REQUIRED'   => false,
                ];

                // Если у документа задан тип НДС проверяем обязателен ли он для покупателя
                if(!empty($arDocumentType['NDS_TYPE']) && in_array($arClient['PROPERTY_NDS_VALUE'], $arDocumentType['NDS_TYPE'])) {
                    $arDocument['IS_REQUIRED'] = true;
                }

                $iFileId = intval($arClient['PROPERTY_' . $sCodeDocument . '_VALUE']);
                if(!empty($iFileId)) {
                    $arDocument['PATH'] = $this->obFile->GetPath($iFileId);
                }

                if($arDocument['IS_REQUIRED'] && empty($arDocument['PATH'])) {
                    $arResult['IS_FULL_SET'] = false;
                    $arResult['LIST_MISSING_DOCUMENTS'][$sCodeDocument] = $arDocument['NAME'];
                }

                $arResult['DOCUMENTS'][$sCodeDocument] = $arDocument;
            }
        } catch (Exception $e) {
            $arResult['ERROR'] = $e->getMessage();
        }

        $this->arDocumentsClient[$iClientId] =& $arResult;

        return $this->arDocumentsClient[$iClientId];
    }


    /**
     * Отдает набор документов по роли
     * @param $sRoleType
     * @return array
     * @throws Exception
     */
    private function getDocumentTypeByRole($sRoleType) {

        // Значения списка "Тип ЮЛ"
        $arUlType = rrsIblock::getPropListId('user_docs', 'TYPE');
        if(empty($arUlType)) {
            throw new Exception('Не удалось значения списка "Тип ЮЛ"');
        }

        // Получаем набор документов по роли
        $rs = $this->obEl->GetList(
            [],
            [
                'IBLOCK_ID'     => getIBlockID('lists', 'user_docs'),
                'SECTION_CODE'  => $sRoleType,
                'ACTIVE'        => 'Y',
            ],
            false,
            false,
            ['ID', 'NAME', 'CODE', 'PROPERTY_TYPE', 'PROPERTY_NDS', 'PROPERTY_NAME']
        );

        $arResult = [];
        while ($arRow = $rs->Fetch()) {

            $arResult[$arRow['CODE']] = [
                'NAME'              => $arRow['NAME'],
                'FULL_NAME'         => $arRow['PROPERTY_NAME_VALUE'],
                'UL_TYPE_XML_ID'    => $arUlType[$arRow['PROPERTY_TYPE_ENUM_ID']]['XML_ID'],
                'NDS_TYPE'          => $arRow['PROPERTY_NDS_VALUE'],
            ];
        }

        return $arResult;
    }
}