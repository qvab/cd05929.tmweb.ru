<?
class rrsIblock {
    /**
     * Получение идентификатора инфоблока по коду
     * @param  string $code код инфоблока
     *         bool $recache флаг очистки кеша
     * @return number идентификатор инфоблока
     */
    public static function getIBlockId($code, $recache = false) {
        $result = '';

        CModule::IncludeModule('iblock');
        $obCache = new CPHPCache;
        $life_time = 86400*30;
        $cache_id = 'getIBlockId_' . $code;
        if ($obCache->InitCache($life_time, $cache_id, "/") && !$recache) {
            $vars = $obCache->GetVars();
            $result = $vars[$cache_id];
        }
        else {
            $res = CIBlock::GetList(
                array('ID' => 'ASC'),
                array('CODE' => $code, 'ACTIVE' => 'Y')
            );
            if ($ob = $res->Fetch()) {
                $result = $ob['ID'];
            }
        }
        if ($recache) $obCache->Clean($cache_id, "/", $basedir = "cache");
        //если запущено без сброса кеша и нет результата - запускаем повторно со сбросом (возможно закешировался пустой результат)
        elseif($result == ''){ self::getIBlockId($code, true); }
        if ($obCache->StartDataCache()) $obCache->EndDataCache(array($cache_id => $result));
        return $result;
    }

    /**
     * Получение идентификатора highload инфоблока по коду
     * @param  string $code код highload инфоблока
     *         bool $recache флаг очистки кеша
     * @return number идентификатор highload инфоблока
     */
    public static function HLgetIBlockId($code, $recache = false) {
        $result = '';

        $obCache = new CPHPCache;
        $life_time = 86400*30;
        $cache_id = 'HLgetIBlockId_' . $code;
        if ($obCache->InitCache($life_time, $cache_id, "/") && !$recache) {
            $vars = $obCache->GetVars();
            $result = $vars[$cache_id];
        }
        else {
            CModule::IncludeModule('highloadblock');
            $res = \Bitrix\Highloadblock\HighloadBlockTable::getList(array(
                'select' => array('ID'),
                'filter' => array('=NAME' => $code)
            ));
            if($data = $res->fetch()){
                $result = $data['ID'];
            }
        }
        if ($recache) $obCache->Clean($cache_id, "/", $basedir = "cache");
        //если запущено без сброса кеша и нет результата - запускаем повторно со сбросом (возможно закешировался пустой результат)
        elseif($result == ''){ self::HLgetIBlockId($code, true); }

        if ($obCache->StartDataCache()) $obCache->EndDataCache(array($cache_id => $result));
        return $result;
    }

    /**
     * Получение списка элементов инфоблока
     * @param  int $ib id инфоблока
     *         string $sort поле сортировки
     *         bool $recache флаг очистки кеша
     * @return [] массив со списком элементов
     */
    public static function getElementList($ib, $sort = 'SORT', $recache = false) {
        CModule::IncludeModule('iblock');
        $obCache = new CPHPCache;
        $life_time = 3600;
        $cache_id = 'getElementList_' . $ib . '_' . $sort;
        if ($obCache->InitCache($life_time, $cache_id, "/") && !$recache) {
            $vars = $obCache->GetVars();
            $result = $vars[$cache_id];
        }
        else {
            $res = CIBlockElement::GetList(
                array($sort => 'ASC'),
                array('IBLOCK_ID' => $ib, 'ACTIVE' => 'Y'),
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
     * Получение символьного кода по идентификатору
     * @param  int $ib идентификатор инфоблока
     *         int $id идентификатор элемента
     *         bool $recache флаг очистки кеша
     * @return string значение символьного кода
     */
    public static function getElementCodeById($ib, $id, $recache = false) {
        CModule::IncludeModule('iblock');
        $obCache = new CPHPCache;
        $life_time = 86400;
        $cache_id = 'getElementCodeById_' . $ib . '_' . $id;
        if ($obCache->InitCache($life_time, $cache_id, "/") && !$recache) {
            $vars = $obCache->GetVars();
            $result = $vars[$cache_id];
        }
        else {
            $res = CIBlockElement::GetList(
                array('ID' => 'ASC'),
                array('IBLOCK_ID'=>$ib, 'ACTIVE' => 'Y', 'ID' => $id),
                false,
                false,
                array('ID', 'NAME', 'CODE')
            );
            if ($ob = $res->Fetch()) {
                $result = $ob['CODE'];
            }
        }
        if ($recache) $obCache->Clean($cache_id, "/", $basedir = "cache");
        if ($obCache->StartDataCache()) $obCache->EndDataCache(array($cache_id => $result));
        return $result;
    }

    /**
     * Получение значения переменной сайта
     * @param  string $code символьный код переменной
     * @return string значение переменной сайта
     */
    public static function getConst($code, $recache = false) {
        $result = '';

        CModule::IncludeModule('iblock');
        $obCache = new CPHPCache;
        $life_time = 86400*30;
        $cache_id = 'getConst_' . $code;
        if ($obCache->InitCache($life_time, $cache_id, "/") && !$recache) {
            $vars = $obCache->GetVars();
            $result = $vars[$cache_id];
        }
        else {
            $res = CIBlockElement::GetList(
                array('ID' => 'ASC'),
                array(
                    'IBLOCK_ID' => rrsIblock::getIBlockId('data'),
                    'ACTIVE' => 'Y',
                    'CODE' => $code
                ),
                false,
                false,
                array('ID', 'NAME', 'CODE', 'PROPERTY_VALUE')
            );
            if ($ob = $res->Fetch()) {
                $result = $ob['PROPERTY_VALUE_VALUE'];
            }
        }
        if ($recache) $obCache->Clean($cache_id, "/", $basedir = "cache");
        if ($obCache->StartDataCache()) $obCache->EndDataCache(array($cache_id => $result));

        return $result;
    }

    /**
     * Получение значения переменной сайта
     * @param  string $iblock_code символьный код инфоблока
     * @param  string $property_code символьный код свойства
     * @param  boolean $recache флаг сброса кеша
     * @return id значение переменной сайта
     */
    public static function getIBlockPropertyID($iblock_code, $property_code, $recache = false) {
        $result = 0;

        $obCache = new CPHPCache;
        $life_time = 86400*30;
        $cache_id = 'ib_prop_code_' . $iblock_code . '&' . $property_code;
        if ($obCache->InitCache($life_time, $cache_id, "/") && !$recache) {
            $vars = $obCache->GetVars();
            $result = $vars[$cache_id];
        }
        else {
            CModule::IncludeModule('iblock');
            $ib_id = rrsIblock::getIBlockId($iblock_code);
            if(is_numeric($ib_id)
                && $ib_id > 0
                && trim($property_code) != ''
            ){
                $res = CIBlock::GetProperties(
                    $ib_id,
                    array('ID' => 'ASC'),
                    array(
                        'CODE' => $property_code
                    )
                );
                if ($ob = $res->Fetch()) {
                    $result = $ob['ID'];
                }
            }
        }
        if ($recache) $obCache->Clean($cache_id, "/", $basedir = "cache");
        //если запущено без сброса кеша и нет результата - запускаем повторно со сбросом (возможно закешировался пустой результат)
        elseif($result == 0){ self::getIBlockPropertyID($iblock_code, $property_code, true); }

        if ($obCache->StartDataCache()) $obCache->EndDataCache(array($cache_id => $result));

        return $result;
    }

    /**
     * Получение списка элементов инфоблока
     * @param  int $ib id инфоблока
     * @return [] массив со списком элементов
     */
    public static function getElementsInfo($ib, $ids) {
        CModule::IncludeModule('iblock');
        $res = CIBlockElement::GetList(
            array('ID' => 'ASC'),
            array('IBLOCK_ID' => $ib, 'ACTIVE' => 'Y', 'ID' => $ids),
            false,
            false,
            array('ID', 'NAME')
        );
        while ($ob = $res->Fetch()) {
            $result[$ob['ID']] = $ob;
        }

        return $result;
    }

    /**
     * Получение вариантов значений свойств типа "список"
     * @param  string $iblockCode код инфоблока
     *         string $propCode код свойства
     *         string $elemCode код элемента списка
     * @return [] массив со списком элементов/id элемента списка
     */
    public static function getPropListKey($iblockCode, $propCode, $elemCode = false) {
        $obCache = new CPHPCache;
        $life_time = 86400*30;
        $cache_id = 'getPropListKey_' . $iblockCode . '_' . $propCode . '_' . $elemCode;
        if ($obCache->InitCache($life_time, $cache_id, "/")) {
            $vars = $obCache->GetVars();
            $result = $vars[$cache_id];
        }
        else {
            CModule::IncludeModule('iblock');
            $result = array();

            $property_enums = CIBlockPropertyEnum::GetList(
                array('SORT' => 'ASC'),
                array('IBLOCK_ID' => rrsIblock::getIBlockId($iblockCode), 'CODE' => $propCode)
            );
            while ($enum_fields = $property_enums->Fetch()) {
                $result[$enum_fields['XML_ID']] = $enum_fields;
            }

            if ($elemCode) {
                $result = $result[$elemCode]['ID'];
            }
        }
        if ($obCache->StartDataCache()) $obCache->EndDataCache(array($cache_id => $result));
        return $result;
    }

    /**
     * Получение вариантов значений свойств типа "список"
     * @param  string $iblockCode код инфоблока
     *         string $propCode код свойства
     *         string $elem_id идентификатор элемента списка
     * @return [] массив со списком элементов/id элемента списка
     */
    public static function getPropListId($iblockCode, $propCode, $elem_id = false) {
        $obCache = new CPHPCache;
        $life_time = 3600;
        $cache_id = 'getPropListId_' . $iblockCode . '_' . $propCode . '_' . $elem_id;
        if ($obCache->InitCache($life_time, $cache_id, "/")) {
            $vars = $obCache->GetVars();
            $result = $vars[$cache_id];
        }
        else {
            CModule::IncludeModule('iblock');
            $result = array();

            $property_enums = CIBlockPropertyEnum::GetList(
                array('SORT' => 'ASC'),
                array('IBLOCK_ID' => rrsIblock::getIBlockId($iblockCode), 'CODE'=> $propCode)
            );
            while ($enum_fields = $property_enums->Fetch()) {
                $result[$enum_fields['ID']] = $enum_fields;
            }

            if ($elem_id) {
                $result = $result[$elem_id]['XML_ID'];
            }
        }
        if ($obCache->StartDataCache()) $obCache->EndDataCache(array($cache_id => $result));
        return $result;
    }

    /**
    * Получение информации о пользователе сайта по идентификатору
    * @param  int $user_id идентификатор пользователя
    * @return [] массив с полями пользователя
    */
    public static function getUserInfo($user_id) {
        $result = array();
        $rsUsers = CUser::GetList(($by="id"), ($order="desc"), array("ID" => $user_id), array("SELECT"=>array("UF_*")));
        if ($ar = $rsUsers->Fetch()) {
            //$ar["GROUPS"] = CUser::GetUserGroup($ar["ID"]);
            $result = $ar;
        }
        return $result;
    }

    /**
     * Получение информации о пользователях сайта по идентификаторам
     * @param  int $users_id идентификаторы пользователей
     * @return [] массив с полями пользователей
     */
    public static function getUsersInfo($users_id,$fields = array(),$select = array()) {
        $result = array();
        $rsUsers = CUser::GetList(($by="id"), ($order="desc"), array("ID" => implode(' | ', $users_id)), array("SELECT"=>$select,'FIELDS'=>$fields));
        while ($ar = $rsUsers->Fetch()) {
            $result[$ar['ID']] = $ar;
        }
        return $result;
    }

    /**
     * Получение Email пользователя
     * @param $user_id - ID пользователя
     * @return string
     */
    public static function getEmail($user_id){
        $result = '';
        $rsUsers = CUser::GetList(($by="id"), ($order="desc"), array("ID" => $user_id, array('FIELDS'=>'EMAIL')));
        while ($ar = $rsUsers->Fetch()) {
            $result = $ar['EMAIL'];
        }
        return $result;
    }

    /**
     * Получение матрицы расстояний
     * @param  [] $pArray1 массив точек
     *         [] $pArray2 массив точек
     * @return [] массив расстояний
     */
    public static function getRouteMatrix($pArray1, $pArray2) {
        $result = array();
        foreach ($pArray1 as $ow) {
            foreach ($pArray2 as $rw) {
                $result[$ow['ID']][$rw['ID']] = rrsIblock::getRoute($ow['MAP'], $rw['MAP']);
            }
        }
        return $result;
    }

    /**
     * Получение расстояния между точками
     * @param  string $p1 координаты точки
     *         string $p2 координаты точки
     * @return number расстояние между точками в км
     */
    public static function getRoute($p1, $p2) {
        $apiKey = $GLOBALS['googleMapKey'];
        $distance = json_decode(
            file_get_contents("https://maps.googleapis.com/maps/api/directions/json?origin=".$p1."&destination=".$p2."&key=".$apiKey),
            true
        );
        return ceil(0.001*$distance['routes'][0]['legs'][0]['distance']['value']);
    }

    /**
     * Получение последнего курса доллара
     * @return number значение курса доллара
     */
    public static function getCurrencyRate() {
        CModule::IncludeModule('currency');
        $result = array();

        $arFilter = array(
            'CURRENCY' => 'USD'
        );
        $by = "date";
        $order = "desc";

        $db_rate = CCurrencyRates::GetList($by, $order, $arFilter);
        if ($ar_rate = $db_rate->Fetch()) {
            $result = $ar_rate['RATE'];
        }

        return $result;
    }

    /**
     * @param string $point_data coordinates point like "53.201458330832594,45.11858215553855"
     * @return int region center ID
     */
    public static function getNearestRegCenterID($point_data) {
        $answer = 0; //0 is for error result
        $check_val = explode(',', $point_data);
        $min = 100000; //large initial value in kilometers
        if(isset($check_val[1]) && is_numeric($check_val[0]) && is_numeric($check_val[1]))
        {
            $res = CIBlockElement::GetList(
                array('ID' => 'ASC'),
                array(
                    'IBLOCK_ID' => rrsIblock::getIBlockId('regions_centers'),
                    'ACTIVE' => 'Y',
                    '!PROPERTY_MAP' => false),
                false,
                false,
                array('ID', 'PROPERTY_MAP')
            );
            while ($data = $res->Fetch()) {
                $route_val = self::getRoute($point_data, $data['PROPERTY_MAP_VALUE']);
                if ($min > $route_val) {
                    $min = $route_val;
                    $answer = $data['ID'];
                }
            }
        }

        return $answer;
    }

    public static function isUserPassword($userId, $password) {
        $userData = CUser::GetByID($userId)->Fetch();
        $salt = substr($userData['PASSWORD'], 0, (strlen($userData['PASSWORD']) - 32));
        $realPassword = substr($userData['PASSWORD'], -32);
        $password = md5($salt.$password);
        return ($password == $realPassword);
    }

    public static function sq($x1, $y1, $x2, $y2) {
        //(x1, y1) - точка из параметров запроса и товара
        //(x2, y2) - диапазон из таблицы сбросов

        if ($x1 >= $y1 || $x2 >= $y2 || $y2 < $x1 || $y1 < $x2)
            return 0;

        if ($x1 <= $x2 && $y2 <= $y1)
            return $y2 - $x2;

        if ($x1 <= $x2 && $y1 <= $y2)
            return $y1 - $x2;

        if ($x2 <= $x1 && $y2 <= $y1)
            return $y2 - $x1;

        if ($x2 <= $x1 && $y1 <= $y2)
            return $y1 - $x1;

        return 0;
    }
}
?>