<?
class transport {
    /**
     * Получение профиля клиента C
     * @param  int $user_id идентификатор пользователя
     *         bool $profile возвращать ли информацию о пользователе
     * @return [] массив с полями профиля
     */
    public static function getProfile($user_id, $profile = false) {
        CModule::IncludeModule('iblock');
        $res = CIBlockElement::GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('transport_profile'),
                'ACTIVE' => 'Y',
                'PROPERTY_USER' => $user_id
            ),
            false,
            false,
            array(
                'ID',
                'NAME',
                'PROPERTY_FULL_COMPANY_NAME',
                'PROPERTY_IP_FIO',
                'PROPERTY_NOTICE',
                'PROPERTY_PHONE'
            )
        );
        if ($ob = $res->Fetch()) {
            if (trim($ob['PROPERTY_FULL_COMPANY_NAME_VALUE']) != '') {
                $ob['COMPANY'] = $ob['PROPERTY_FULL_COMPANY_NAME_VALUE'];
            }
            elseif (trim($ob['PROPERTY_IP_FIO_VALUE']) != '') {
                $ob['COMPANY'] = 'ИП ' . $ob['PROPERTY_IP_FIO_VALUE'];
            }
            $result = $ob;
        }

        if ($profile) {
            $result['USER'] = rrsIblock::getUserInfo($user_id);
        }

        return $result;
    }

    /**
     * Получение полной информации профиля ТК
     * @param  int $user_id идентификатор пользователя
     * @return [] массив с полями профиля
     */
    public static function getFullProfile($user_id) {
        CModule::IncludeModule('iblock');
        $res = CIBlockElement::GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('transport_profile'),
                'ACTIVE' => 'Y',
                'PROPERTY_USER' => $user_id
            ),
            false,
            false,
            array(
                'ID',
                'NAME',
                'PROPERTY_PHONE',
                'PROPERTY_UL_TYPE',
                'PROPERTY_INN',
                'PROPERTY_FULL_COMPANY_NAME',
                'PROPERTY_IP_FIO',
                'PROPERTY_YUR_ADRESS',
                'PROPERTY_POST_ADRESS',
                'PROPERTY_KPP',
                'PROPERTY_OGRN',
                'PROPERTY_OKPO',
                'PROPERTY_FIO_DIR',
                'PROPERTY_BANK',
                'PROPERTY_BIK',
                'PROPERTY_RASCH_SCHET',
                'PROPERTY_KOR_SCHET',
            )
        );
        if ($ob = $res->Fetch()) {
            if ($ob['PROPERTY_UL_TYPE_ENUM_ID'] > 0) {
                $ob['UL_TYPE'] = rrsIblock::getPropListId('transport_profile', 'UL_TYPE', $ob['PROPERTY_UL_TYPE_ENUM_ID']);
            }
            if (!$ob['UL_TYPE']) {
                $ob['UL_TYPE'] = 'ul';
            }

            if ($ob['UL_TYPE'] == 'ip') {
                $ob['COMPANY'] = 'Индивидуальный предприниматель ' . $ob['PROPERTY_IP_FIO_VALUE'];
                $ob['ADDRESS'] = $ob['PROPERTY_POST_ADRESS_VALUE'];
                $ob['KPP'] = '';
                $ob['OGRN'] = 'ОГРНИП ' . $ob['PROPERTY_OGRN_VALUE'];
            }
            else {
                $ob['COMPANY'] = $ob['PROPERTY_FULL_COMPANY_NAME_VALUE'];
                $ob['ADDRESS'] = $ob['PROPERTY_YUR_ADRESS_VALUE'];
                $ob['KPP'] = 'КПП ' . $ob['PROPERTY_KPP_VALUE'];
                $ob['OGRN'] = 'ОГРН ' . $ob['PROPERTY_OGRN_VALUE'];
            }

            $result = $ob;
        }

        $result['USER'] = rrsIblock::getUserInfo($user_id);

        return $result;
    }

    /**
     * Получение списка всех ТК, привязанных к организатору
     * @param  int $partner_id идентификатор пользователя
     * @return [] массив со списком элементов
     */
    public static function getLinkedTransportList($partner_id) {
        CModule::IncludeModule('iblock');
        $res = CIBlockElement::GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('transport_partner_link'),
                'ACTIVE' => 'Y',
                'PROPERTY_PARTNER_ID' => $partner_id,
            ),
            false,
            false,
            array(
                'ID',
                'NAME',
                'PROPERTY_USER_ID',
            )
        );
        while ($ob = $res->Fetch()) {
            $result[] = $ob['PROPERTY_USER_ID_VALUE'];
        }

        return $result;
    }

    /**
     * Получение списка всех ТК, привязанных к организатору
     * @param  int $partner_id идентификатор пользователя
     * @return [] массив со списком элементов
     */
    public static function getTransportList() {
        CModule::IncludeModule('iblock');
        $res = CIBlockElement::GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('transport_profile'),
                'ACTIVE' => 'Y'
            ),
            false,
            false,
            array(
                'ID',
                'NAME',
                'PROPERTY_USER',
            )
        );
        while ($ob = $res->Fetch()) {
            $result[] = $ob['PROPERTY_USER_VALUE'];
        }

        return $result;
    }


    /**
     * Отдает список ID подтвержденных ТК
     * @return array
     */
    public static function getListConfirmedTransportCompanies() {

        $arTransportCompaniesId = [];

        try {

            // ID значения "Да" св-ва "VERIFIED"
            $arEnumYes = CIBlockPropertyEnum::GetList(
                [],
                [
                    'IBLOCK_ID' => getIBlockID('transport_company', 'transport_partner_link'),
                    'CODE'      => 'VERIFIED',
                    'XML_ID'    => 'yes',
                ]
            )->Fetch();

            if(empty($arEnumYes['ID'])) {
                throw new Exception('Не удалось определить ID значения "Да" поля "VERIFIED"');
            }

            // Формируем список подтвержденных компаний
            $rs = CIBlockElement::GetList(
                ['ID' => 'ASC'],
                [
                    'IBLOCK_ID'         => getIBlockID('transport_company', 'transport_partner_link'),
                    'ACTIVE'            => 'Y',
                    'PROPERTY_VERIFIED' => $arEnumYes['ID'],
                ],
                false,
                false,
                ['PROPERTY_USER_ID']
            );

            while ($arRow = $rs->Fetch()) {
                $arTransportCompaniesId[$arRow['PROPERTY_USER_ID_VALUE']] = $arRow['PROPERTY_USER_ID_VALUE'];
            }

        } catch (Exception $e) {
            ShowError('Ошибка получения списка подтвержденных транспортных компаний! ' . $e->getMessage());
            die();
        }

        return array_values($arTransportCompaniesId);
    }

    /**
     * Получение списка всех баз ТК
     * @param  int $user_id идентификатор пользователя
     * @return [] массив со списком элементов
     */
    public static function getAutoparkList($user_id) {
        CModule::IncludeModule('iblock');
        $res = CIBlockElement::GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('transport_autopark'),
                'ACTIVE' => 'Y',
                'PROPERTY_TRANSPORT' => $user_id,
                'PROPERTY_ACTIVE' => rrsIblock::getPropListKey('transport_autopark', 'ACTIVE', 'yes'),
                '!PROPERTY_MAP' => false,
            ),
            false,
            false,
            array(
                'ID',
                'NAME',
                'SORT',
                'PROPERTY_ADDRESS',
                'PROPERTY_MAP',
            )
        );
        while ($ob = $res->Fetch()) {
            $tmp = array(
                'ID' => $ob['ID'],
                'NAME' => $ob['NAME'],
                'ADDRESS' => $ob['PROPERTY_ADDRESS_VALUE'],
                'MAP' => $ob['PROPERTY_MAP_VALUE']
            );
            $result[$ob['ID']] = $tmp;
        }

        return $result;
    }

    /*
     * @param int $uid - идентификатор пользователя
     * @return array - массив id организаторов, с которыми у транспортной компании с $uid есть загруженные договора (ключи массива - id организатора)
     */
    public static function checkDealsRightsIds($uid)
    {
        $answer = array(); //default is no rights

        CModule::IncludeModule('iblock');
        $res = CIBlockElement::GetList(
            array('ID' => 'DESC'),
            array(
                'IBLOCK_CODE' => 'transport_partner_link',
                'ACTIVE' => 'Y',
                'PROPERTY_USER_ID' => $uid
            ),
            false,
            false,
            array('PROPERTY_PARTNER_ID')
        );
        while($data = $res->Fetch())
        {
            if(is_numeric($data['PROPERTY_PARTNER_ID_VALUE']))
            {
                $answer[$data['PROPERTY_PARTNER_ID_VALUE']] = true;
            }
        }

        return $answer;
    }

    /*
     * Получение региона пользователя
     * @param int $user_id - идентификатор пользователя
     * @return int идентификатор региона
     */
    public static function getRegion($user_id, $recache = false) {
        CModule::IncludeModule('iblock');
        $obCache = new CPHPCache;
        $life_time = 3600;
        $cache_id = 'getRegion_' . $user_id;
        if ($obCache->InitCache($life_time, $cache_id, "/") && !$recache) {
            $vars = $obCache->GetVars();
            $result = $vars[$cache_id];
        }
        else {
            $res = CIBlockElement::GetList(
                array('ID' => 'DESC'),
                array(
                    'IBLOCK_ID' => rrsIblock::getIBlockId('transport_profile'),
                    'ACTIVE' => 'Y',
                    'PROPERTY_USER' => $user_id
                ),
                false,
                false,
                array('ID', 'PROPERTY_REGION')
            );
            if ($ob = $res->Fetch()) {
                $result = $ob['PROPERTY_REGION_VALUE'];
            }
        }
        if ($recache) $obCache->Clean($cache_id, "/", $basedir = "cache");
        if ($obCache->StartDataCache()) $obCache->EndDataCache(array($cache_id => $result));
        return $result;
    }

    /*
     * Получение списка всех документов пользователя
     * @param
     * @return [] спислк документов
     */
    public static function getAllDocuments($recache = false) {
        CModule::IncludeModule('iblock');
        $obCache = new CPHPCache;
        $life_time = 3600;
        $cache_id = 'getAllDocuments_transport';
        if ($obCache->InitCache($life_time, $cache_id, "/") && !$recache) {
            $vars = $obCache->GetVars();
            $result = $vars[$cache_id];
        }
        else {
            $res = CIBlockElement::GetList(
                array('SORT' => 'ASC'),
                array(
                    'IBLOCK_ID' => rrsIblock::getIBlockId('user_docs'),
                    'ACTIVE' => 'Y',
                    'SECTION_CODE' => 'transport',
                ),
                false,
                false,
                array('ID', 'NAME', 'CODE')
            );
            while ($ob = $res->Fetch()) {
                $result[$ob['CODE']] = $ob;
            }
        }
        if ($recache) $obCache->Clean($cache_id, "/", $basedir = "cache");
        if ($obCache->StartDataCache()) $obCache->EndDataCache(array($cache_id => $result));
        return $result;
    }

    /**
     * возвращает данные организатора, привязанного к пользователю (для рассылки при регистрации из приглашения)
     * @param $userId - id пользователя
     * @param $is_invited - признак того был ли приглашен пользователь или регистрируется самостоятельно
     * @return array - массив данных с ключами [EMAIL] - почта, [NAME] - ФИО или логин
     */
    public static function getPartnerEmailData($userId, $is_invited = false) {
        $result = array();

        if($is_invited){
            $res = CIBlockElement::GetList(
                array('ID' => 'ASC'),
                array(
                    'IBLOCK_ID' => rrsIblock::getIBlockId('transport_partner_link'),
                    'PROPERTY_USER_ID' => $userId
                ),
                false,
                array('nTopCount' => 1),
                array('PROPERTY_PARTNER_ID')
            );
        }else {
            $res = CIBlockElement::GetList(
                array('ID' => 'ASC'),
                array(
                    'IBLOCK_ID' => rrsIblock::getIBlockId('transport_profile'),
                    'PROPERTY_USER' => $userId
                ),
                false,
                array('nTopCount' => 1),
                array('PROPERTY_PARTNER_ID')
            );
        }
        if($data = $res->Fetch()){
            if(isset($data['PROPERTY_PARTNER_ID_VALUE'])
                && is_numeric($data['PROPERTY_PARTNER_ID_VALUE'])
            ) {
                $res = CUser::GetList(
                    ($by = 'id'), ($order = 'asc'),
                    array('ID' => $data['PROPERTY_PARTNER_ID_VALUE']),
                    array('FIELDS' => array('NAME', 'LAST_NAME', 'EMAIL', 'LOGIN'))
                );
                if($data = $res->Fetch()){
                    $result['EMAIL'] = $data['EMAIL'];

                    $temp_name = trim($data['NAME'] . ' ' . $data['LAST_NAME']);
                    if($temp_name == ''){
                        $temp_name = $data['LOGIN'];
                    }

                    $result['NAME'] = $temp_name;
                }
            }
        }

        return $result;
    }

    /**
     * Проверка сохранения фильтра на странице запросов транспортной компании
     *
     * @return array массив, где у ключа 'NEED_UPD' значение либо true либо false,
     * у ключа 'URL_UPD' значение адреса переадресации
     */
    public static function filterTransportRequestCheck(){
        $result = array(
            'NEED_UPD'  => false,
            'URL_UPD'   => ''
        );
        if(isset($_GET['id'])
            && $_GET['id'] > 0){
            return $result;
        }

        if(((isset($_GET['culture_id']))&&(!empty($_GET['culture_id'])))||
            ((isset($_GET['distance_id']))&&(!empty($_GET['distance_id'])))){
            return $result;
        }

        $page_need_update = false;
        $new_url_params = array();

        $culture_id_cookie = '';
        $distance_id_cookie = '';
        //проверка куки склада
        $cookie_name = 'transport_request_culture_id';
        if(isset($_COOKIE[$cookie_name])){
            $culture_id_cookie = $_COOKIE[$cookie_name];
            if((!isset($_GET['culture_id']) || $_GET['culture_id'] == '' || $_GET['culture_id'] == '0')
                && $culture_id_cookie != 0 && $culture_id_cookie != ''
            ){
                $result['NEED_UPD'] = true;
                $new_url_params[] = 'culture_id=' . $culture_id_cookie;
            }
        }
        //проверка куки культуры
        $cookie_name = 'transport_request_distance_id';
        if(isset($_COOKIE[trim($cookie_name)])){
            $distance_id_cookie = $_COOKIE[$cookie_name];
            if((!isset($_GET['distance_id']) || $_GET['distance_id'] == '' || $_GET['distance_id'] == '0')
                && $distance_id_cookie != 0 && $distance_id_cookie != ''
            ){
                $result['NEED_UPD'] = true;
                $new_url_params[] = 'distance_id=' . $distance_id_cookie;
            }
        }
        if($result['NEED_UPD']){
            $result['URL_UPD'] = '/transport/request/' . (count($new_url_params) > 0 ? '?' . implode('&', $new_url_params) : '');
        }
        return $result;
    }

}
?>