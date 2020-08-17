<?
class culture {
    /**
     * Получение списка культур группы
     * @param  int $group_id идентификатор группы
     *         string $sort поле сортировки
     *         bool $recache флаг очистки кеша
     * @return [] массив со списком культур
     */
    public static function getListByGroupId($group_id, $sort = 'SORT', $recache = false) {
        CModule::IncludeModule('iblock');
        $obCache = new CPHPCache;
        $life_time = 86400;
        $cache_id = 'getListByGroupId_' . $group_id . '_' . $sort;
        if ($obCache->InitCache($life_time, $cache_id, "/") && !$recache) {
            $vars = $obCache->GetVars();
            $result = $vars[$cache_id];
        }
        else {
            $res = CIBlockElement::GetList(
                array($sort => 'ASC'),
                array(
                    'IBLOCK_ID' => rrsIblock::getIBlockId('cultures'),
                    'ACTIVE' => 'Y',
                    'PROPERTY_GROUP' => $group_id
                ),
                false,
                false,
                array('ID', 'NAME', 'CODE', 'SORT')
            );
            while ($ob = $res->Fetch()) {
                $result[$ob['ID']] = $ob;
            }
        }
        if ($recache) $obCache->Clean($cache_id, "/", $basedir = "cache");
        if ($obCache->StartDataCache()) $obCache->EndDataCache(array($cache_id => $result));
        return $result;
    }

    /**
     * Получение списка характеристик культуры, базисных характеристик
     * @param  int $culture_id идентификатор культуры
     *         string $sort поле сортировки
     *         bool $recache флаг очистки кеша
     * @return [] массив со списком характеристик
     */
    public static function getParamsListByCultureId($culture_id, $sort = 'SORT', $recache = false) {
        CModule::IncludeModule('iblock');
        $obCache = new CPHPCache;
        $life_time = 3600;
        $cache_id = 'getParamsListByCultureId_' . $culture_id . '_' . $sort;
        if ($obCache->InitCache($life_time, $cache_id, "/") && !$recache) {
            $vars = $obCache->GetVars();
            $result = $vars[$cache_id];
        }
        else {
            $list = array();
            $res = CIBlockElement::GetList(
                array('SORT' => 'ASC', 'ID' => 'ASC'),
                array(
                    'IBLOCK_ID' => rrsIblock::getIBlockId('basis_values'),
                    'ACTIVE' => 'Y',
                    'PROPERTY_CULTURE' => $culture_id
                ),
                false,
                false,
                array('ID', 'NAME', 'PROPERTY_QUALITY')
            );
            while ($ob = $res->Fetch()) {
                $list[$ob['PROPERTY_QUALITY_VALUE']][] = $ob;
            }
            $res = CIBlockElement::GetList(
                array($sort => 'ASC'),
                array(
                    'IBLOCK_ID' => rrsIblock::getIBlockId('characteristics'),
                    'ACTIVE' => 'Y',
                    'PROPERTY_CULTURE' => $culture_id
                ),
                false,
                false,
                array(
                    'ID',
                    'NAME',
                    'PROPERTY_QUALITY',
                    'PROPERTY_QUALITY.NAME',
                    'PROPERTY_TYPE',
                    'PROPERTY_LBASE',
                    'PROPERTY_BASE',
                    'PROPERTY_MIN',
                    'PROPERTY_MAX',
                    'PROPERTY_STEP',
                    'PROPERTY_RESET_BELOW_BASIS',
                    'PROPERTY_RESET_MORE_BASIS',
                )
            );
            while ($ob = $res->Fetch()) {
                $tmp = array(
                    'ID' => $ob['ID'],
                    'NAME' => $ob['NAME'],
                    'QUALITY_ID' => $ob['PROPERTY_QUALITY_VALUE'],
                    'QUALITY_NAME' => $ob['PROPERTY_QUALITY_NAME'],
                    'TYPE_NAME' => $ob['PROPERTY_TYPE_VALUE'],
                    'TYPE_ID' => $ob['PROPERTY_TYPE_ENUM_ID'],
                    'LBASE_ID' => $ob['PROPERTY_LBASE_VALUE'],
                    'BASE' => $ob['PROPERTY_BASE_VALUE'],
                    'MIN' => $ob['PROPERTY_MIN_VALUE'],
                    'MAX' => $ob['PROPERTY_MAX_VALUE'],
                    'STEP' => $ob['PROPERTY_STEP_VALUE'],
                    'LIST' => $list[$ob['PROPERTY_QUALITY_VALUE']],
                    'RESET_BELOW_BASIS' => $ob['PROPERTY_RESET_BELOW_BASIS_VALUE'],
                    'RESET_MORE_BASIS'  => $ob['PROPERTY_RESET_MORE_BASIS_VALUE'],
                );
                $result[$ob['PROPERTY_QUALITY_VALUE']] = $tmp;
            }
        }
        if ($recache) $obCache->Clean($cache_id, "/", $basedir = "cache");
        if ($obCache->StartDataCache()) $obCache->EndDataCache(array($cache_id => $result));
        return $result;
    }

    /**
     * Получение названия культуры
     * @param  int $culture_id идентификатор культуры
     *         bool $recache флаг очистки кеша
     * @return array название культуры
     */
    public static function getName($culture_id, $recache = false) {
        CModule::IncludeModule('iblock');
        $obCache = new CPHPCache;
        $life_time = 86400*30;
        $cache_id = 'getName_' . $culture_id;
        if ($obCache->InitCache($life_time, $cache_id, "/") && !$recache) {
            $vars = $obCache->GetVars();
            $result = $vars[$cache_id];
        }
        else {
            $res = CIBlockElement::GetList(
                array('NAME' => 'ASC'),
                array(
                    'IBLOCK_ID' => rrsIblock::getIBlockId('cultures'),
                    'ACTIVE' => 'Y',
                    'ID' => $culture_id
                ),
                false,
                false,
                array('ID', 'NAME', 'PROPERTY_CHEGO')
            );
            if ($ob = $res->Fetch()) {
                $result = array('NAME' => $ob['NAME'], 'CHEGO' => $ob['PROPERTY_CHEGO_VALUE']);
            }
        }
        if ($recache) $obCache->Clean($cache_id, "/", $basedir = "cache");
        if ($obCache->StartDataCache()) $obCache->EndDataCache(array($cache_id => $result));
        return $result;
    }

    /**
     * Получение групп культур
     * @param  bool $recache флаг очистки кеша
     * @return [] массив со списком культур
     */
    public static function getCulturesGroup($recache = false) {
        CModule::IncludeModule('iblock');
        $obCache = new CPHPCache;
        $life_time = 86400;
        $cache_id = 'getCulturesGroup';
        if ($obCache->InitCache($life_time, $cache_id, "/") && !$recache) {
            $vars = $obCache->GetVars();
            $result = $vars[$cache_id];
        }
        else {
            $res = CIBlockElement::GetList(
                array('ID' => 'ASC'),
                array(
                    'IBLOCK_ID' => rrsIblock::getIBlockId('cultures'),
                    'ACTIVE' => 'Y'
                ),
                false,
                false,
                array('ID', 'NAME', 'PROPERTY_GROUP')
            );
            while ($ob = $res->Fetch()) {
                $result[$ob['ID']] = $ob['PROPERTY_GROUP_VALUE'];
            }
        }
        if ($recache) $obCache->Clean($cache_id, "/", $basedir = "cache");
        if ($obCache->StartDataCache()) $obCache->EndDataCache(array($cache_id => $result));
        return $result;
    }

    /**
     * Получение списка аналогов для культуры
     * @param  int $culture_id идентификатор группы
     *         bool $recache флаг очистки кеша
     * @return [] массив со списком культур
     */
    public static function getAnalog($culture_id, $recache = false) {
        CModule::IncludeModule('iblock');
        $result = array();

        $obCache = new CPHPCache;
        $life_time = 86400;
        $cache_id = 'getAnalog_' . $culture_id;
        if ($obCache->InitCache($life_time, $cache_id, "/") && !$recache) {
            $vars = $obCache->GetVars();
            $result = $vars[$cache_id];
        }
        else {
            $res = CIBlockElement::GetList(
                array('ID' => 'ASC'),
                array(
                    'IBLOCK_ID' => rrsIblock::getIBlockId('cultures'),
                    'ACTIVE' => 'Y',
                    'ID' => $culture_id
                ),
                false,
                false,
                array('ID', 'PROPERTY_ANALOG')
            );
            if ($ob = $res->Fetch()) {
                $result = $ob['PROPERTY_ANALOG_VALUE'];
            }
        }
        if ($recache) $obCache->Clean($cache_id, "/", $basedir = "cache");
        if ($obCache->StartDataCache()) $obCache->EndDataCache(array($cache_id => $result));
        return $result;
    }



    /**
     * Быстрая диактивация \автивация статусов для всех культур (характреистик) по Показателю (QUALITY)
     * @param $sQualityName - значение QUALITY - по имени(т.к нет CODE) из админки
     * @param bool $isActive - в какое значение перевести
     * @param bool $isWindowLog - лог в окно
     * @throws Exception
     *
     */

    public  static function changeActiveToCharByQuality($sQualityName, $isActive = true , $isWindowLog = false){

        if(!$sQualityName || empty($sQualityName)) {
            throw  new Exception("Не указан параметр \$sQualityName");
        }
        CModule::IncludeModule('iblock');
        $obIBlockElement    = new CIBlockElement();

        $sActive = $isActive ? 'Y' : 'N';

        $rsResult = $obIBlockElement->GetList(
            ['ID' => 'ASC'],
            [
                'IBLOCK_ID'              => rrsIblock::getIBlockId('characteristics'),
                '=PROPERTY_QUALITY.NAME' => $sQualityName
            ],
            false,
            false,
            ['ID', 'IBLOCK_ID', 'NAME', 'PROPERTY_QUALITY.NAME', 'PROPERTY_CULTURE.NAME']
        );

        while ($arRow = $rsResult->Fetch()){

            if(!$obIBlockElement->Update($arRow['ID'], ['ACTIVE'  => $sActive])){
                throw new Exception("Не удалось обновить элемент ID={$arRow['ID']}");
            }
            if($isWindowLog) {
                echo "<br> Обновлен Элемент ID = <i>{$arRow['ID']}</i> - Показатель <b>({$sQualityName})</b> , для культуры  [{$arRow['PROPERTY_CULTURE_NAME']}]";
                echo " Активность : <b>{$sActive}</b><br>";
            }
        }

    }

    /**
     * Получение названий культур
     * @param array $arrCultures - массив ID культур (необязательно)
     * @return array - массив, где ключи ID культур, значения - названия
     */
    public static function getNames($arrCultures = array()){
        $arrResult = array();

        $arrFilter = array(
            'IBLOCK_ID' => rrsIblock::getIBlockId('cultures'),
            'ACTIVE' => 'Y',
        );
        if(count($arrCultures) > 0){
            $arrFilter = $arrCultures;
        }

        $obRes = CIBlockElement::GetList(
            array('ID' => 'ASC'),
            $arrFilter,
            false,
            false,
            array('ID', 'NAME')
        );
        while ($arrData = $obRes->Fetch()){
            $arrResult[$arrData['ID']] = $arrData['NAME'];
        }

        return $arrResult;
    }


    /**
     * Увстановка сброса для всех культур (характреистик) по Показателю (QUALITY)
     * @param $sQualityName - значение QUALITY - по имени(т.к нет CODE) из админки
     * @param $sCultureName - значение CULTURE - по имени(т.к нет CODE) из админки
     * @param double $dMinReset - Сброс, ниже базисного значения
     * @param double $dMaxReset - Сброс, выше базисного значения
     * @param bool $isWindowLog - лог в окно
     * @throws Exception
     *
     */

    public  static function setResetToCharByQuality($sQualityName, $sCultureName = '', $dMinReset, $dMaxReset, $isWindowLog = false){

        if(
            (!$sQualityName || empty($sQualityName))
            || filter_var($dMinReset, FILTER_VALIDATE_FLOAT) === false
            || filter_var($dMaxReset, FILTER_VALIDATE_FLOAT) === false
        ) {
            throw  new Exception("Не указан параметр ");
        }
        CModule::IncludeModule('iblock');
        $obIBlockElement    = new CIBlockElement();

        $arFilter = [
            'IBLOCK_ID'              => rrsIblock::getIBlockId('characteristics'),
            '=PROPERTY_QUALITY.NAME' => $sQualityName
        ];
        // Если указан конкретная культура
        if($sCultureName != ''){
            $arFilter['=PROPERTY_CULTURE.NAME'] = $sCultureName;
        }

        $rsResult = $obIBlockElement->GetList(
            ['ID' => 'ASC'],
            $arFilter,
            false,
            false,
            ['ID', 'IBLOCK_ID', 'NAME', 'PROPERTY_QUALITY.NAME', 'PROPERTY_CULTURE.NAME']
        );

        while ($arRow = $rsResult->Fetch()){

            $arPropUpdate = [
                'RESET_BELOW_BASIS' => $dMinReset,
                'RESET_MORE_BASIS'  => $dMaxReset
            ];

            $obIBlockElement->SetPropertyValuesEx($arRow['ID'], $arRow['IBLOCK_ID'], $arPropUpdate);

            if($isWindowLog) {
                echo <<<TEXT
    Обновлен Элемент ID = <i>{$arRow['ID']}</i> <br>
    - Показатель <b>({$sQualityName})</b><br>
    для культуры  <b>[{$arRow['PROPERTY_CULTURE_NAME']}]</b><br>
    Сброс, ниже базисного значения {$dMinReset}<br>
    Сброс, выше базисного значения {$dMaxReset}<br><br>
    =========================================================================================<br><br><br>
TEXT;
            }
        }

    }
}