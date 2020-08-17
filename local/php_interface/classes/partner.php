<?
class partner {
    /**
     * Получение профиля организатора
     * @param  int $user_id идентификатор пользователя
     *         bool $profile возвращать ли информацию о пользователе
     * @return [] массив с полями профиля
     */
    public static function getProfile($user_id, $profile = false) {
        CModule::IncludeModule('iblock');
        $res = CIBlockElement::GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('partner_profile'),
                'ACTIVE' => 'Y',
                'PROPERTY_USER' => $user_id
            ),
            false,
            false,
            array(
                'ID',
                'NAME',
                'PROPERTY_FULL_COMPANY_NAME',
                'PROPERTY_NOTICE',
                'PROPERTY_PHONE'
            )
        );
        if ($ob = $res->Fetch()) {
            $result = $ob;
        }

        if ($profile) {
            $result['USER'] = rrsIblock::getUserInfo($user_id);
        }

        return $result;
    }

    /**
     * Получение полной информации профиля организатора
     * @param  int $user_id идентификатор пользователя
     * @return [] массив с полями профиля
     */
    public static function getFullProfile($user_id) {
        CModule::IncludeModule('iblock');
        $res = CIBlockElement::GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('partner_profile'),
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
                'PROPERTY_POST',
                'PROPERTY_FIO_SIGN',
                'PROPERTY_FOUNDATION',
                'PROPERTY_BANK',
                'PROPERTY_BIK',
                'PROPERTY_RASCH_SCHET',
                'PROPERTY_KOR_SCHET',
            )
        );
        if ($ob = $res->Fetch()) {
            if ($ob['PROPERTY_UL_TYPE_ENUM_ID'] > 0) {
                $ob['UL_TYPE'] = rrsIblock::getPropListId('partner_profile', 'UL_TYPE', $ob['PROPERTY_UL_TYPE_ENUM_ID']);
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

    public function getPublicProfileMenu($user_mode){
        $result = '';
        if($user_mode == 'f' && $GLOBALS['rrs_user_perm_level'] == 'p'
        ){
            switch($user_mode){
                case 'f':
                    $result = array(
                        'warehouses' => 'Склады',
                        'offers' => 'Товары',
                        'requests' => 'Запросы'
                    );
                    break;
            }
        }
        return $result;
    }

    public function getPublicProfileData($user_mode, $tab_data = ''){
        $result = array();
        switch($user_mode){
            case 'f':
                switch($tab_data){
                    case 'warehouses':
                        break;
                    case 'offers':
                        break;
                    case 'requests':
                        break;
                    default:
                }
                break;
        }
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
        $cache_id = 'getAllDocuments_partner';
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
                    'SECTION_CODE' => 'partner',
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

    /*
     * Обновление подтверждения качества товара в предложениях по товару
     * @param int $offer_id - ID товара
     * @param int $quality_approved - подтвердение качества установлено/снято
     * @param string $approved_data - дата установления подтвердения качества
     * @return [] спислк документов
     */
    public static function changeCounterOfferQualityApproved($offer_id, $quality_approved, $approved_data = '') {

        if(filter_var($quality_approved, FILTER_VALIDATE_INT) !== false
            && filter_var($offer_id, FILTER_VALIDATE_INT) !== false
        ) {
            CModule::IncludeModule('highloadblock');
            $arIds = array();

            $log_obj = new log;
            $entityDataClass = $log_obj->getEntityDataClass(rrsIblock::HLgetIBlockId('COUNTEROFFERS'));
            $el = new $entityDataClass;

            //получаем ID предложений по товару
            $arFilter = array(
                'select' => array('ID'),
                'filter' => array('UF_OFFER_ID' => $offer_id ),
                'order' => array('ID' => 'ASC')
            );
            $res = $el->getList($arFilter);
            while($data = $res->fetch()) {
                $arIds[] = $data['ID'];
            }
            if(count($arIds) > 0){
                $arFields = array(
                    'UF_PARTNER_Q_APRVD' => $quality_approved,
                );
                if($approved_data){
                    $arFields['UF_PARTNER_Q_APRVD_D'] = $approved_data;
                }

                foreach($arIds as $o_id){
                    $el->update($o_id, $arFields);
                }
            }
        }
    }

    /*
     * Получение данных о подтверждении товаров
     * @param [] $arrOfferIds - ID товаров
     * @return [] массив данных о подтверждении товаров, где ключами являются ID товаров
     */
    public static function getOffersApproves($arrOfferIds) {
        $arResult = array();

        CModule::IncludeModule('iblock');
        $obRes = CIBlockElement::GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('farmer_offer'),
                'ID' => $arrOfferIds,
                'PROPERTY_Q_APPROVED' => 1
            ),
            false,
            false,
            array(
                'ID',
                'PROPERTY_Q_APPROVED',
                'PROPERTY_Q_APPROVED_DATA',
                'PROPERTY_Q_APPROVED_PARTNER_ID',
            )
        );

        while($arData = $obRes->Fetch()){
            $arResult[$arData['ID']] = array(
                'Q_APPROVED'    => $arData['PROPERTY_Q_APPROVED_VALUE'],
                'Q_APPROVED_DATA'    => $arData['PROPERTY_Q_APPROVED_DATA_VALUE'],
                'Q_APPROVED_PARTNER_ID'    => $arData['PROPERTY_Q_APPROVED_PARTNER_ID_VALUE'],
            );
        }

        return $arResult;
    }

    /**
     * Получение информации о ДОУ организатора и покупателя
     * @param  int $partner_id идентификатор организатора
     *         int $client_id идентификатор покупателя
     * @return [] массив с информацией
     */
    public static function getClientDouInfo($partner_id, $client_id) {
        CModule::IncludeModule('iblock');
        $result = array();
        $res = CIBlockElement::GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('partner_client_dou'),
                'ACTIVE' => 'Y',
                'PROPERTY_PARTNER' => $partner_id,
                'PROPERTY_CLIENT' => $client_id
            ),
            false,
            false,
            array(
                'ID',
                'PROPERTY_DOU_NUM',
                'PROPERTY_DOU_DATE'
            )
        );
        if ($ob = $res->Fetch()) {
            $result = $ob;
        }

        return $result;
    }

    /**
     * Получение информации о ДОУ организатора и перевозчика
     * @param  int $partner_id идентификатор организатора
     *         int $transport_id идентификатор перевозчика
     * @return [] массив с информацией
     */
    public static function getTransportDouInfo($partner_id, $transport_id) {
        CModule::IncludeModule('iblock');
        $result = array();
        $res = CIBlockElement::GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('partner_transport_dou'),
                'ACTIVE' => 'Y',
                'PROPERTY_PARTNER' => $partner_id,
                'PROPERTY_TRANSPORT' => $transport_id
            ),
            false,
            false,
            array(
                'ID',
                'PROPERTY_DOU_NUM',
                'PROPERTY_DOU_DATE'
            )
        );
        if ($ob = $res->Fetch()) {
            $result = $ob;
        }

        return $result;
    }

    /**
     * @param $uid
     * @return bool
     */
    public static function deleteNotRespUser($uid){
        $result = false;
        $u_obj  = new CUser;
        $rsUser = $u_obj::GetByID($uid);
        $arUser = $rsUser->Fetch();
        if($arUser['ACTIVE'] == 'N'){
            //отмечаем флаг, который разрешит нам удаление пользователя
            $u_obj->Update($uid, array('UF_NOT_RESP' => 'Y'));
            $result = $u_obj->Delete($uid);
        }
        return $result;
    }

    /**
     * Возврашает массив ID поставщиков для выбранного организатора
     * @param int $partner_id
     * @return array
     */
    public static function getFarmers($partner_id, $checked_farmers_ids = array())
    {
        $result = array();

        if(is_numeric($partner_id))
        {
            $filerArr = array(
                'IBLOCK_ID'         => rrsIblock::getIBlockId('farmer_agent_link'),
                'ACTIVE'            => 'Y',
                'PROPERTY_AGENT_ID' => $partner_id
            );

            if(is_array($checked_farmers_ids) && count($checked_farmers_ids) > 0)
            {
                $filerArr['PROPERTY_USER_ID'] = $checked_farmers_ids;
            }

            $el_obj = new CIBlockElement();
            $res = $el_obj->GetList(
                array('ID' => 'ASC'),
                $filerArr,
                false,
                false,
                array('PROPERTY_USER_ID')
            );
            while($data = $res->Fetch())
            {
                $result[] = $data['PROPERTY_USER_ID_VALUE'];
            }
        }

        return $result;
    }

    /**
     * Возврашает массив ID покупателей для выбранного организатора
     * @param int $partner_id
     * @param array $checked_clients_ids - массив с ID покупателей для ограничения проверки
     * @return array - массив ID привязанных покупателей
     */
    public static function getClients($partner_id, $checked_clients_ids = array())
    {
        $result = array();

        if(is_numeric($partner_id))
        {
            $filerArr = array(
                'IBLOCK_ID'         => rrsIblock::getIBlockId('client_agent_link'),
                'ACTIVE'            => 'Y',
                'PROPERTY_AGENT_ID' => $partner_id
            );

            if(is_array($checked_clients_ids) && count($checked_clients_ids) > 0)
            {
                $filerArr['PROPERTY_USER_ID'] = $checked_clients_ids;
            }

            $el_obj = new CIBlockElement();
            $res = $el_obj->GetList(
                array('ID' => 'ASC'),
                $filerArr,
                false,
                false,
                array('PROPERTY_USER_ID')
            );
            while($data = $res->Fetch())
            {
                $result[] = $data['PROPERTY_USER_ID_VALUE'];
            }
        }

        return $result;
    }

    /**
     * Возврашает массив ID покупателей для выбранного организатора (с данными покупателя)
     * @param int $partner_id - ID организатора
     * @param int $checked_clients_ids - массив ID покупателей, для сужения выборки
     * @param boolean $get_demo - признак выборки доп поля UF_DEMO
     * @param boolean $get_agent_rights - признак выборки доп поля с правами агента на управление
     * @return array
     */
    static function getClientsForSelect($partner_id, $checked_clients_ids = array(), $get_demo = false, $get_agent_rights = false, $get_partner_rights = false)
    {
        $result = array();

        if(is_numeric($partner_id)){
            $user_ids = array();
            $filerArr = array(
                'IBLOCK_ID'         => rrsIblock::getIBlockId('client_agent_link'),
                'ACTIVE'            => 'Y',
                'PROPERTY_AGENT_ID' => $partner_id
            );

            if(is_array($checked_clients_ids) && count($checked_clients_ids) > 0){
                $filerArr['PROPERTY_USER_ID'] = $checked_clients_ids;
            }elseif(is_numeric($checked_clients_ids) && $checked_clients_ids > 0){
                $filerArr['PROPERTY_USER_ID'] = array($checked_clients_ids);
            }

            $arSelect = array('PROPERTY_USER_ID', 'PROPERTY_CLIENT_NICKNAME');
            if($get_agent_rights){
                $arSelect[] = 'PROPERTY_AGENT_RIGHTS';
            }
            $el_obj = new CIBlockElement();
            $res = $el_obj->GetList(
                array('ID' => 'DESC'),
                $filerArr,
                false,
                false,
                $arSelect
            );
            while($data = $res->Fetch()){
                $user_ids[$data['PROPERTY_USER_ID_VALUE']] = array(
                    'NICK' => trim($data['PROPERTY_CLIENT_NICKNAME_VALUE'])
                );
                if($get_agent_rights){
                    $user_ids[$data['PROPERTY_USER_ID_VALUE']]['AGENT_RIGHTS'] = $data['PROPERTY_AGENT_RIGHTS_ENUM_ID'];
                }
            }

            if(count($user_ids) > 0){
                $u_obj = new CUser;
                $arSelect = array(
                    'FIELDS' => array('ID', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'EMAIL')
                );
                if($get_demo){
                    $arSelect['SELECT'] = array('UF_DEMO');
                }
                $res = $u_obj->GetList(
                    ($by = 'email'),
                    ($order = 'asc'),
                    array(
                        'ID'        => implode(' | ', array_keys($user_ids)),
                        'ACTIVE'    => 'Y'
                    ),
                    $arSelect
                );
                while($data = $res->Fetch()){
                    $result[$data['ID']] = array(
                        'NAME'  => trim($data['LAST_NAME'].' '.$data['NAME'].' '.$data['SECOND_NAME']),
                        'EMAIL' => $data['EMAIL'],
                        'NICK'  => ''
                    );
                    if(isset($user_ids[$data['ID']]['NICK']) && $user_ids[$data['ID']]['NICK'] != ''){
                        $result[$data['ID']]['NICK'] = $user_ids[$data['ID']]['NICK'];
                    }
                    if($get_demo){
                        $result[$data['ID']]['UF_DEMO'] = $data['UF_DEMO'];
                    }
                    if($get_agent_rights){
                        $result[$data['ID']]['AGENT_RIGHTS'] = $user_ids[$data['ID']]['AGENT_RIGHTS'];
                    }
                }

                //получение подтверждений для покупателей
                if($get_partner_rights){
                    $res = $el_obj->GetList(
                        array('ID' => 'ASC'),
                        array(
                            'IBLOCK_ID' => rrsIblock::getIBlockId('client_partner_link'),
                            'ACTIVE' => 'Y',
                            'PROPERTY_USER_ID' => array_keys($user_ids)
                        ),
                        false,
                        false,
                        array('PROPERTY_VERIFIED', 'PROPERTY_USER_ID', 'PROPERTY_PARTNER_LINK_DOC')
                    );
                    while($data = $res->Fetch()){
                        if(isset($result[$data['PROPERTY_USER_ID_VALUE']])){
                            if(isset($data['PROPERTY_VERIFIED_ENUM_ID'])
                                && $data['PROPERTY_VERIFIED_ENUM_ID'] == rrsIblock::getPropListKey('client_partner_link', 'VERIFIED', 'yes')
                            ){
                                $result[$data['PROPERTY_USER_ID_VALUE']]['VERIFIED'] = 'Y';
                            }

                            if(isset($data['PROPERTY_PARTNER_LINK_DOC_VALUE'])
                                && $data['PROPERTY_PARTNER_LINK_DOC_VALUE'] != ''
                            ){
                                $result[$data['PROPERTY_USER_ID_VALUE']]['LINK_DOC'] = 'Y';
                            }
                        }
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Получение id покупателя по id склада
     * @param int $warehouse_id - ID склада
     * @return int - ID покупателя
     */
    static function getClientByWarehouse($warehouse_id){
        $result = 0;

        $res = CIBlockElement::GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('client_warehouse'),
                'ACTIVE' => 'Y',
                'ID' => $warehouse_id
            ),
            false,
            array('nTopCount' => 1),
            array('PROPERTY_CLIENT')
        );
        if($data = $res->Fetch()){
            if(isset($data['PROPERTY_CLIENT_VALUE'])
                && is_numeric($data['PROPERTY_CLIENT_VALUE'])
            ){
                $result = $data['PROPERTY_CLIENT_VALUE'];
            }
        }

        return $result;
    }

    /**
     * Возврашает массив ID поставщиков для выбранного организатора (с данными поставщика)
     * @param int $partner_id - id организатора
     * @param array $checked_farmers_ids - массив ID поставщиков
     * @param boolean $get_demo - признак того возвращать ли св-во UF_DEMO пользователя
     * @param boolean $get_first_authorize - признак того возвращать ли св-во UF_FIRST_LOGIN пользователя
     * @return array
     */
    function getFarmersForSelect($partner_id, $checked_farmers_ids = array(), $get_demo = false, $get_first_authorize = false)
    {
        $result = array();

        if(is_numeric($partner_id)){
            $user_ids = array();
            $filerArr = array(
                'IBLOCK_ID'         => rrsIblock::getIBlockId('farmer_agent_link'),
                'ACTIVE'            => 'Y',
                'PROPERTY_AGENT_ID' => $partner_id
            );

            if(is_array($checked_farmers_ids) && count($checked_farmers_ids) > 0){
                $filerArr['PROPERTY_USER_ID'] = $checked_farmers_ids;
            }

            $el_obj = new CIBlockElement();
            $res = $el_obj->GetList(
                array('ID' => 'DESC'),
                $filerArr,
                false,
                false,
                array('PROPERTY_USER_ID', 'PROPERTY_FARMER_NICKNAME')
            );
            while($data = $res->Fetch()){
                $user_ids[$data['PROPERTY_USER_ID_VALUE']] = trim($data['PROPERTY_FARMER_NICKNAME_VALUE']);
            }

            if(count($user_ids) > 0){
                $u_obj = new CUser;
                $arSelect = array(
                    'FIELDS' => array('ID', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'EMAIL')
                );
                if($get_demo){
                    $arSelect['SELECT'] = array('UF_DEMO');
                }
                if($get_first_authorize){
                    $arSelect['SELECT'] = array('UF_FIRST_LOGIN');
                }
                $res = $u_obj->GetList(
                    ($by = 'email'),
                    ($order = 'asc'),
                    array(
                        'ID'        => implode(' | ', array_keys($user_ids)),
                        'ACTIVE'    => 'Y'
                    ),
                    $arSelect
                );
                while($data = $res->Fetch()){
                    $result[$data['ID']] = array(
                        'NAME'  => trim($data['LAST_NAME'].' '.$data['NAME'].' '.$data['SECOND_NAME']),
                        'EMAIL' => $data['EMAIL'],
                        'NICK'  => ''
                    );
                    if(isset($user_ids[$data['ID']]) && $user_ids[$data['ID']] != ''){
                        $result[$data['ID']]['NICK'] = $user_ids[$data['ID']];
                    }
                    if($get_demo){
                        $result[$data['ID']]['UF_DEMO'] = $data['UF_DEMO'];
                    }
                    if($get_first_authorize){
                        $result[$data['ID']]['UF_FIRST_LOGIN'] = $data['UF_FIRST_LOGIN'];
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Привязка пользователя к организатору
     * @param int $user_id - id организатора
     * @param int $partner_id - id организатора
     * @param string $user_type - тип пользователя (f/c)
     * @return boolean флаг успеха привязки
     */
    public static function linkUserToPartner($user_id, $partner_id, $user_type){
        $result = false;

        if(is_numeric($partner_id)
            && is_numeric($user_id)
        ){
            CModule::IncludeModule('iblock');
            $el_obj = new CIBlockElement;
            $ib_id = 0;

            if($user_type == 'f'){
                $ib_id = rrsIblock::getIBlockId('farmer_agent_link');
            }elseif($user_type == 'c'){
                $ib_id = rrsIblock::getIBlockId('client_agent_link');
            }

            if($ib_id > 0) {
                //проверка на дублирование привязки
                $res = $el_obj->GetList(
                    array('ID' => 'ASC'),
                    array(
                        'IBLOCK_ID' => $ib_id,
                        'PROPERTY_USER_ID' => $user_id,
                        'PROPERTY_AGENT_ID' => $partner_id,
                    ),
                    false,
                    array('nTopCount' => 1),
                    array('ID')
                );
                if ($res->SelectedRowsCount() == 0) {
                    //дублей нет, добавляем привязку
                    $arFields = array(
                        'IBLOCK_ID' => $ib_id,
                        'NAME' => "Привязка " . ($user_type == 'f' ? 'поставщика' : 'покупателя') . " [{$user_id}] к организатору [{$partner_id}]",
                        'ACTIVE' => 'Y',
                        'PROPERTY_VALUES' => array(
                            'USER_ID' => $user_id,
                            'AGENT_ID' => $partner_id,
                        )
                    );
                    $el_obj->Add($arFields);

                    //добавляем привязку в отдельном инфоблоке (для покупателя)
                    if($user_type == 'c') {
                        //проверка на дублирование
                        $res = $el_obj->GetList(
                            array('ID' => 'ASC'),
                            array(
                                'IBLOCK_ID' => rrsIblock::getIBlockId('client_partner_link'),
                                'PROPERTY_USER_ID' => $user_id,
                                'PROPERTY_PARTNER_ID' => $partner_id,
                            ),
                            false,
                            array('nTopCount' => 1),
                            array('ID')
                        );
                        if($res->SelectedRowsCount() == 0){
                            $arFields = array(
                                'IBLOCK_ID' => rrsIblock::getIBlockId('client_partner_link'),
                                'ACTIVE' => 'Y',
                                'NAME' => "Привязка покупателя [{$user_id}] к организатору [{$partner_id}]",
                                'PROPERTY_VALUES' => array(
                                    'USER_ID' => $user_id,
                                    'PARTNER_ID' => $partner_id,
                                )
                            );
                            $el_obj->Add($arFields);
                        }
                    }

                    $result = true;
                }
            }
        }

        return $result;
    }

    /**
     * Проверка сохранения фильтра на странице со списком покупателей
     *
     * @return array массив, где у ключа 'NEED_UPD' значение либо true либо false,
     * у ключа 'URL_UPD' значение адреса переадресации
     */
    public static function filterPartnerClientsCheck() {
        $result = array(
            'NEED_UPD'  => false,
            'URL_UPD'   => ''
        );

        $new_url_params = array();

        //проверка куки пользователя
        $cookie_name = 'partner_client_list_client';
        if(isset($_COOKIE[$cookie_name])){
            $cookie_value = $_COOKIE[$cookie_name];
            if((!isset($_GET['client_id'])
                    || $_GET['client_id'] == ''
                )
                && $cookie_value != 0
                || isset($_GET['client_id'])
                && $_GET['client_id'] != ''
                && $_GET['client_id'] != $cookie_value
            ){
                $result['NEED_UPD'] = true;
                $new_url_params[] = 'client_id=' . $cookie_value;
            }
        }

        //проверка куки типа привязки
        $cookie_name = 'partner_client_list_link_type';
        if(isset($_COOKIE[$cookie_name])){
            $cookie_value = $_COOKIE[$cookie_name];
            if((!isset($_GET['is_linked'])
                    || $_GET['is_linked'] == ''
                )
                && $cookie_value != 0
                || isset($_GET['is_linked'])
                && $_GET['is_linked'] != ''
                && $_GET['is_linked'] != $cookie_value
            ){
                $result['NEED_UPD'] = true;
                $new_url_params[] = 'is_linked=' . $cookie_value;
            }
        }

        //проверка куки региона
        $cookie_name = 'partner_client_list_region';
        if(isset($_COOKIE[$cookie_name])){
            $cookie_value = $_COOKIE[$cookie_name];
            if((!isset($_GET['region_id'])
                    || $_GET['region_id'] == ''
                )
                && $cookie_value != 0
                || isset($_GET['region_id'])
                && $_GET['region_id'] != ''
                && $_GET['region_id'] != $cookie_value
            ){
                $result['NEED_UPD'] = true;
                $new_url_params[] = 'region_id=' . $cookie_value;
            }
        }

        //проверка куки культуры
        $cookie_name = 'partner_client_list_culture';
        if(isset($_COOKIE[$cookie_name])){
            $cookie_value = $_COOKIE[$cookie_name];
            if((!isset($_GET['culture_id'])
                    || $_GET['culture_id'] == ''
                )
                && $cookie_value != 0
                || isset($_GET['culture_id'])
                && $_GET['culture_id'] != ''
                && $_GET['culture_id'] != $cookie_value
            ){
                $result['NEED_UPD'] = true;
                $new_url_params[] = 'culture_id=' . $cookie_value;
            }
        }

        if($result['NEED_UPD']){
            $result['URL_UPD'] = '/partner/users/linked_clients/' . (count($new_url_params) > 0 ? '?' . implode('&', $new_url_params) : '');
        }

        return $result;
    }

    /**
     * Проверка сохранения фильтра на странице со списком покупателей
     *
     * @return array массив, где у ключа 'NEED_UPD' значение либо true либо false,
     * у ключа 'URL_UPD' значение адреса переадресации
     */
    public static function filterPartnerFarmersCheck() {
        $result = array(
            'NEED_UPD'  => false,
            'URL_UPD'   => ''
        );

        $new_url_params = array();

        //проверка куки пользователя
        $cookie_name = 'partner_farmer_list_farmer';
        if(isset($_COOKIE[$cookie_name])){
            $cookie_value = $_COOKIE[$cookie_name];
            if((!isset($_GET['farmer_id'])
                    || $_GET['farmer_id'] == ''
                )
                && $cookie_value != 0
                || isset($_GET['farmer_id'])
                && $_GET['farmer_id'] != ''
                && $_GET['farmer_id'] != $cookie_value
            ){
                $result['NEED_UPD'] = true;
                $new_url_params[] = 'farmer_id=' . $cookie_value;
            }
        }

        //проверка куки типа привязки
        $cookie_name = 'partner_farmer_list_link_type';
        if(isset($_COOKIE[$cookie_name])){
            $cookie_value = $_COOKIE[$cookie_name];
            if((!isset($_GET['is_linked'])
                    || $_GET['is_linked'] == ''
                )
                && $cookie_value != 0
                || isset($_GET['is_linked'])
                && $_GET['is_linked'] != ''
                && $_GET['is_linked'] != $cookie_value
            ){
                $result['NEED_UPD'] = true;
                $new_url_params[] = 'is_linked=' . $cookie_value;
            }
        }

        //проверка куки региона
        $cookie_name = 'partner_farmer_list_region';
        if(isset($_COOKIE[$cookie_name])){
            $cookie_value = $_COOKIE[$cookie_name];
            if((!isset($_GET['region_id'])
                    || $_GET['region_id'] == ''
                )
                && $cookie_value != 0
                || isset($_GET['region_id'])
                && $_GET['region_id'] != ''
                && $_GET['region_id'] != $cookie_value
            ){
                $result['NEED_UPD'] = true;
                $new_url_params[] = 'region_id=' . $cookie_value;
            }
        }

        //проверка куки культуры
        $cookie_name = 'partner_farmer_list_culture';
        if(isset($_COOKIE[$cookie_name])){
            $cookie_value = $_COOKIE[$cookie_name];
            if((!isset($_GET['culture_id'])
                    || $_GET['culture_id'] == ''
                )
                && $cookie_value != 0
                || isset($_GET['culture_id'])
                && $_GET['culture_id'] != ''
                && $_GET['culture_id'] != $cookie_value
            ){
                $result['NEED_UPD'] = true;
                $new_url_params[] = 'culture_id=' . $cookie_value;
            }
        }

        if($result['NEED_UPD']){
            $result['URL_UPD'] = '/partner/users/linked_users/' . (count($new_url_params) > 0 ? '?' . implode('&', $new_url_params) : '');
        }

        return $result;
    }

    /**
     * Отвязка пользователя от организатора
     * @param int $user_id - id организатора
     * @param int $partner_id - id организатора
     * @param string $user_type - тип пользователя (f/c)
     * @return boolean флаг успеха отвязки
     */
    public static function unlinkUserFromPartner($user_id, $partner_id, $user_type)
    {
        $result = false;

        if(is_numeric($partner_id)
            && is_numeric($user_id)
        ){
            CModule::IncludeModule('iblock');
            $el_obj = new CIBlockElement;
            $ib_id = 0;

            if($user_type == 'f'){
                $ib_id = rrsIblock::getIBlockId('farmer_agent_link');
            }elseif($user_type == 'c'){
                $ib_id = rrsIblock::getIBlockId('client_agent_link');
            }

            if($ib_id > 0) {
                //проверка на дублирование привязки
                $res = $el_obj->GetList(
                    array('ID' => 'ASC'),
                    array(
                        'IBLOCK_ID' => $ib_id,
                        'PROPERTY_USER_ID' => $user_id,
                        'PROPERTY_AGENT_ID' => $partner_id,
                    ),
                    false,
                    array('nTopCount' => 1),
                    array('ID')
                );
                if($data = $res->Fetch()) {
                    $el_obj->Delete($data['ID']);

                    if($user_type == 'c') {
                        //удаляем привязку в отдельном инфоблоке (для покупателя)
                        $res = $el_obj->GetList(
                            array('ID' => 'ASC'),
                            array(
                                'IBLOCK_ID' => rrsIblock::getIBlockId('client_partner_link'),
                                'PROPERTY_USER_ID' => $user_id,
                                'PROPERTY_PARTNER_ID' => $partner_id,
                            ),
                            false,
                            array('nTopCount' => 1),
                            array('ID')
                        );
                        if ($data = $res->Fetch()) {
                            $el_obj->Delete($data['ID']);
                        }
                    }

                    $result = true;
                }
            }
        }

        return $result;
    }

    /**
     * Получает данные о наличии агентского договора между организатором и покупателями
     * @param array $user_ids - ID покупателей
     * @return array - массив, где ключи - ID покупателей, значение - true
     */
    public static function getUsersContractsForPartner($user_ids){
        $result = array();

        if(!is_array($user_ids)
            || count($user_ids) == 0
        ){
            return $result;
        }

        CModule::IncludeModule('iblock');
        $res = CIBlockElement::GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('client_profile'),
                'PROPERTY_USER' => $user_ids,
                '!PROPERTY_PARTNER_CONTRACT_SET' => false,
//                '!PROPERTY_PARTNER_CONTRACT_DATA' => false,
//                '!PROPERTY_PARTNER_CONTRACT_FILE' => false,
            ),
            false,
            false,
            array('PROPERTY_USER', 'PROPERTY_PARTNER_CONTRACT_SET')
        );
        while($data = $res->Fetch()){
            if(intval($data['PROPERTY_PARTNER_CONTRACT_SET_VALUE']) > 0) {
                $result[$data['PROPERTY_USER_VALUE']] = true;
            }
        }

        return $result;
    }

    /**
     * Получает текст для попапа уточнения цены запроса организатором
     * @param int $iRequestId - ID запроса
     * @return string - строка с текстом (по умолчанию пустая)
     */
    public static function getClarifyCounterRequestPrice($iRequestId){
        $sResult = '';

        if(filter_var($iRequestId, FILTER_VALIDATE_INT)) {


            $iUid = 0;
            $sDeliveryType = 'CPT';
            $sPrice = '';
            $sWarehouseName = '';
            $sCultureName = '';
            $bEmailFromPhone = true;
            $sNdsText = 'Без НДС';

            $arNdsTypes = rrsIblock::getPropListKey('client_request', 'USER_NDS');

            //получаем потребность в доставке (CPT/FCA)
            $obRes = CIBlockElement::GetList(
                array('ID' => 'ASC'),
                array(
                    'IBLOCK_ID' => rrsIblock::getIBlockId('client_request'),
                    'ID' => $iRequestId,
                ),
                false,
                array('nTopCount' => 1),
                array('PROPERTY_DELIVERY.CODE', 'PROPERTY_CLIENT', 'PROPERTY_USER_NDS')
            );
            if ($arData = $obRes->Fetch()) {
//                if($arData['PROPERTY_DELIVERY_CODE'] == 'N'){
//                    $sDeliveryType = 'FCA';
//                }

                if($arData['PROPERTY_USER_NDS_ENUM_ID'] == $arNdsTypes['yes']['ID']){
                    $sNdsText = 'С НДС';
                }

                if(filter_var($arData['PROPERTY_CLIENT_VALUE'], FILTER_VALIDATE_INT)) {
                    $iUid = $arData['PROPERTY_CLIENT_VALUE'];
                }
            }

            //получаем почту пользователя (для проверки не создана ли она из почты)
            $obRes = CUser::GetList(
                ($by = 'ID'), ($order = 'ASC'),
                array('ID' => $iUid),
                array('FIELDS' => array('EMAIL'))
            );
            if($arData = $obRes->Fetch()){
                if(
                    $arData['EMAIL']
                    && !checkEmailFromPhone($arData['EMAIL'])
                ){
                    $bEmailFromPhone = false;
                }
            }

            //получаем остальные данные
            $obRes = CIBlockElement::GetList(
                array('ID' => 'ASC'),
                array(
                    'IBLOCK_ID' => rrsIblock::getIBlockId('client_request_cost'),
                    'PROPERTY_REQUEST' => $iRequestId,
                ),
                false,
                array('nTopCount' => 1),
                array('PROPERTY_PRICE', 'PROPERTY_WAREHOUSE.NAME', 'PROPERTY_CULTURE.NAME')
            );
            if ($arData = $obRes->Fetch()) {
                $sPrice = number_format($arData['PROPERTY_PRICE_VALUE'], 0, '.', ' ');
                $sWarehouseName = $arData['PROPERTY_WAREHOUSE_NAME'];
                $sCultureName = $arData['PROPERTY_CULTURE_NAME'];
            }

            if($iUid) {
                $sResult = "<div class=\"row\"><div class=\"agent_counter_href_value\">На складе {$sWarehouseName} по товару \"{$sCultureName}\" установлена цена {$sDeliveryType} {$sPrice} руб/т ({$sNdsText}).<br/>Просьба уточнить цену на сегодня?</div></div>";
                if(!$bEmailFromPhone) {
                    $sResult .= "<input class=\"submit-btn b_left\" type=\"button\" onclick=\"sendTextLinkToEmail(this,{$iUid},'client_price_clarify');\" value=\"Отправить по email\">";
                }
                $sResult .= "<input class=\"submit-btn b_right\" type=\"button\" onclick=\"copyLinkText(this,0,'client_price_clarify');\" value=\"Копировать\">";
            }
        }

        return $sResult;
    }

    /**
     * Отмена встречного предложения
     * @param int $iOfferId - ID товара
     * @param int $iPartnerId - ID организатора
     * @param boolean $bFromFarmer - отменяет поставщик (по умолчанию нет)
     * @return boolean - флаг успешности отмены встречного предложения
     */
    public static function cancelCounterOffers($iOfferId, $iPartnerId = 0, $bFromFarmer = false){
        $bResult = true;

        $log_obj = new log;
        $entityDataClass = $log_obj->getEntityDataClass(rrsIblock::HLgetIBlockId('COUNTEROFFERS'));
        $el = new $entityDataClass;

        $arFilter = array(
            'select' => array('ID', 'UF_BY_PARTNER_REAL'),
            'filter' => array('UF_OFFER_ID' => $iOfferId),
            'order' => array('ID' => 'ASC')
        );
        $obRes = $el->getList($arFilter);

        if($obRes->getSelectedRowsCount() > 0){
            $bResult = true;

            //если встречные предложения есть
            $iCounter = 0;
            //при этом запрещаем организаторам отменять встречные предложения других организаторов
            if(
                !$bFromFarmer
                && $iPartnerId == 0
            ) {
                global $USER;
                $user_groups = CUser::GetUserGroup($USER->GetID());
                if (in_array(getGroupIdByRole('p'), $user_groups)) {
                    $iPartnerId = $USER->GetID();
                }
            }
            while ($arData = $obRes->fetch()) {
                //запрещаем организаторам отменять встречные предложения других организаторов
                if(
                    !$bFromFarmer
                    && $iPartnerId > 0
                    && $iPartnerId != $arData['UF_BY_PARTNER_REAL']
                ){
                    $bResult = false;
                    return $bResult;
                }
                $el->delete($arData['ID']);
                $iCounter++;
            }
        }

        return $bResult;
    }

    /**
     * Получение данных формы создания предложения для товара
     * @param int $iOfferId - ID товара
     * @param boolean $bFromPublic - если форма нужна для публичной страницы (по умолчанию нет)
     * @return string - флаг успешности отмены встречного предложения
     */
    public static function counterOfferFormAfterDeleting($iOfferId, $bFromPublic = false){
        $sResult = '';

        $sNdsVal = 'n';

        //получение наличия встречных запросов для товаров (в этом случае нельзя создавать повторные встречные предложения)
        $counter_requests_data = farmer::getCounterRequestsData($iOfferId);

        //получение начальной цены и ограничений для ввода (работа с данными соответствий)
        //получаем соответствия для товаров
        $arLeads = lead::getLeadList(array('UF_OFFER_ID' => $iOfferId), ['UF_CSM_PRICE' => 'DESC']);
        $offerRequestApply = lead::createLeadList($arLeads);

        //получаем максимальную (+10% к максимальной цене) и минимальную цены (-10% от минимальной цены) для ограничений, а также цену для установления по умолчанию (берется максимальная из имеющихся цен в $offerRequestApply)
        $temp_val = 0;
        $min_val = 0;
        $max_val = 0;
        $set_val = 0;
        $req_ids = array();

        foreach ($offerRequestApply as $cur_data) {
            if ($cur_data['OFFER']['ID'] == $iOfferId) {
                $arReqsCsmCounted[$cur_data['OFFER']['ID']][$cur_data['REQUEST']['ID']] = $cur_data['REQUEST']['BEST_PRICE']['ACC_PRICE_CSM'];
                $temp_val = round($cur_data['REQUEST']['BEST_PRICE']['ACC_PRICE_CSM']);
                if ($min_val > $temp_val || $min_val == 0) {
                    $min_val = $temp_val;
                }
                if ($max_val < $temp_val || $max_val == 0) {
                    $max_val = $temp_val;
                }

                $req_ids[$cur_data['REQUEST']['ID']] = true;

                if ($cur_data['OFFER']['USER_NDS'] == 'yes') {
                    $sNdsVal = 'y';
                }
            }
        }
        $set_val = round($max_val);
        $max_val = round($max_val + $max_val * 0.2);
        $min_val = round($min_val - $min_val * 0.2);

        $arrOnlyVals = array();
        if($bFromPublic){
            $arrOnlyVals = array('rec_price' => true);
        }

        $rec_text = deal::getRecommendedPriceText($iOfferId, true, $sNdsVal, $arrOnlyVals);

        //выводим данные в форму (эти данные вставятся в форму в script.js)
        $sResult = partner::getCounterOfferFormData($iOfferId, $set_val, $min_val, $max_val, $rec_text, (count($req_ids) > 0 ? array_keys($req_ids) : array()), $bFromPublic, (!empty($rec_text['rec_price']) ? $rec_text['rec_price'] : 0));

        return $sResult;
    }

    /**
     * Получение данных формы создания предложения для товара
     * @param int $iOfferId - ID товара
     * @param int $iSetVal - устанавливаемая цена по умолчанию
     * @param int $iMinVal - ограничение цены снизу
     * @param int $iMaxVal - ограничение цены сверху
     * @param string $sRecText - текст с рекомендованой ценой
     * @param array $arRequestIds - массив ID запросов
     * @param boolean $bFromPublic - если форма нужна для публичной страницы (по умолчанию нет)
     * @param int $iRecPrice - рекомендуемая цена (значение, необязательно)
     * @return string - html формы создания предложения
     */
    public static function getCounterOfferFormData($iOfferId, $iSetVal, $iMinVal, $iMaxVal, $sRecText, $arRequestIds, $bFromPublic = false, $iRecPrice = 0){
        $sResult = '';

        ob_start();
        if(count($arRequestIds) > 0) {
            if($bFromPublic){?>
                <div class="prop_area adress_val counter_data">
                    <div class="val_adress">
                        <div class="counter_request_additional_data">
                            <div class="row first_row">
                                <div>
                                    <div class="r_price_block">
                                        <div class="pr_1">Рекомендация цены: <div class="pr_val_rec rowed"><span class="val_span"><?= number_format($iRecPrice, 0, '.', ' '); ?></span> руб/т</div></div>
                                    </div>
                                </div>
                                <div class="flex-row"><div class="row_head">Моя цена "с места":</div><div class="row_val min_max_val"><div class="min_price"><?= number_format($iMinVal, 0, '.', ' '); ?><span>min</span></div><span class="minus minus_bg" data-step="50" onclick="farmerClickCounterMinPrice(this);" data-min="<?=$iMinVal?>"></span><input type="text" name="price" placeholder="" value=""><span class="plus plus_bg" data-step="50" onclick="farmerClickCounterMaxPrice(this);" data-max="<?=$iMaxVal?>"></span><div class="max_price"><?= number_format($iMaxVal, 0, '.', ' '); ?><span>max</span></div></div></div>
                                <div class="clear no_line"></div>
                            </div>
                            <div class="row">
                                <div class="flex-row">
                                    <div class="row_head">Указать количество тонн:</div>
                                    <div class="row_val"><input type="text" data-checkval="y" data-checktype="pos_int" name="volume" placeholder="" value=""><span class="ton_pos">т.</span></div>
                                </div>
                                <div class="clear no_line"></div>
                            </div>
                            <div class="row">
                                <div class="flex-row">
                                    <div class="row_head">Стоимость услуги, руб:</div>
                                    <div class="row_val"><input type="text" readonly="readonly" placeholder="" name="serv_price" value="0"><div class="partner_price_part"><span class="val">0</span> руб/т</div></div>
                                </div>
                                <div class="clear no_line"></div>
                            </div>
                            <input type="button" name="save" value="Отправить предложение" class="submit-btn counter_request_submit"><div class="refinement_text">Срок действия предложения - 7 дней.</div>
                        </div>
                    </div>
                </div>
                <?
            }else {
                ?>
                <div class="prop_area adress_val counter_data">
                    <div class="adress">Отправка/создание предложения:</div>
                    <div class="val_adress">
                        <div class="counter_request_additional_data">
                            <div class="row first_row">
                                <div class="row_val">
                                    <input type="text" data-checkval="y" data-checktype="pos_int" name="volume"
                                           placeholder="Указать количество тонн" value=""><span
                                            class="ton_pos">т.</span>
                                </div>
                            </div>
                            <div class="row">
                                <div>
                                    <?= ($sRecText ?: ''); ?>
                                </div>
                                <div class="flex-row">
                                    <div class="row_head">Моя цена "с места":</div>
                                    <div class="row_val min_max_val">
                                        <div class="min_price"><?= number_format($iMinVal, 0, '.', ' '); ?>
                                            <span>min</span>
                                        </div>
                                        <span class="minus minus_bg" data-step="50"
                                              onclick="farmerClickCounterMinPrice(this);"
                                              data-min="<?= $iMinVal; ?>"></span>
                                        <input type="text" name="price" placeholder=""
                                               value="<?= number_format($iSetVal, 0, '.', ' '); ?>">
                                        <span class="plus plus_bg" data-step="50"
                                              onclick="farmerClickCounterMaxPrice(this);"
                                              data-max="<?= $iMaxVal ?>"></span>
                                        <div class="max_price"><?= number_format($iMaxVal, 0, '.', ' '); ?>
                                            <span>max</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?/*<div class="row two_lines_checkbox">
                            <div class="row_val">
                                <div class="radio_group fst">
                                    <div class="radio_area"><input type="checkbox" name="can_deliver" value="1"
                                                                   data-text="МОГУ ОТВЕЗТИ <br/>за прибавку в цене"
                                                                   class="customized">
                                        <div class="custom_input checkbox" data-name="can_deliver">
                                            <div class="ico"></div>
                                            МОГУ ОТВЕЗТИ <br>за прибавку в цене
                                        </div>
                                    </div>
                                </div>
                                <div class="radio_group">
                                    <div class="radio_area"><input type="checkbox" name="lab_trust" value="1"
                                                                   data-text="ДОВЕРЮСЬ <br/>лаборатории покупателя"
                                                                   class="customized">
                                        <div class="custom_input checkbox" data-name="lab_trust">
                                            <div class="ico"></div>
                                            ДОВЕРЮСЬ <br>лаборатории покупателя
                                        </div>
                                    </div>
                                </div>
                                <div class="clear"></div>
                            </div>
                        </div>

                        <div class="prop_area adress_val additional_label">
                            <div class="radio_group fst">
                                <div class="radio_area"><input type="checkbox" onclick="toggleAdditional(this);"
                                                               class="customized" name="is_partner_offer"
                                                               value="partner" data-text="Услуги Агрохелпера">
                                    <div class="custom_input checkbox" data-name="can_deliver">
                                        <div class="ico"></div>
                                        Услуги Агрохелпера
                                    </div>
                                </div>
                            </div>
                        </div>*/
                            ?>

                            <div class="prop_area adress_val additional_options active">
                                <?/*<span class="minus minus_bg" data-step="50"
                                  onclick="partnerClickServiceMinusPrice(this);"></span>
                            <span class="plus plus_bg" data-step="50"
                                  onclick="partnerClickServicePlusPrice(this);"></span>*/
                                ?>
                                <?/*<div class="adress">Услуги Агрохелпера:</div>*/
                                ?>

                                <div class="val_adress slide-description">
                                    <div class="radio_group">
                                        <div class="radio_area">
                                            <input type="checkbox"
                                                   data-text="<div class=\'custom_data_text\'><span class=\'option-name\' onclick=\'showAdditOptions(this);\'>Сопровождение сделки<i></i></span></div>"
                                                   name="IS_AGENT_SUPPORT" value="Y" class="customized">
                                            <div class="custom_input checkbox" data-name="IS_AGENT_SUPPORT">
                                                <div class="ico"></div>
                                                <div class="custom_data_text"><span class="option-name"
                                                                                    onclick="showAdditOptions(this);">Сопровождение сделки<i></i></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="option-description">
                                        <p>Помощь в поиске информации, необходимой для совершения сделки. Ваш консьерж в
                                            исполнение сделки.</p>
                                        <?//убрано в рамках #13211
                                        /*
                                        <p>После заказа вы договариваетесь с партнером об агентских условиях (комиссия ±0,5%
                                            от суммы сделки, потребность финансирования и т.д.), заключаете агентский
                                            договор, и агент исполняет, сопровождает сделку.</p>
                                        <p><a href="/upload/docs/Договор оказания услуг Агента (на ЭТП).docx"
                                              download="Договор оказания услуг Агента (на ЭТП).docx">Договор оказания услуг
                                                Агента (на ЭТП)</a></p>
                                        <p><a href="/upload/docs/Договор оказания услуг Агента (без ЭТП).docx"
                                              download="Договор оказания услуг Агента (без ЭТП).docx">Договор оказания услуг
                                                Агента (без ЭТП)</a></p>*/
                                        ?>
                                    </div>
                                </div>

                                <div class="partner_price_part"><span class="val"></span> руб/т</div>
                                <div class="adress partner_price">Стоимость услуги, руб: <input type="text"
                                                                                                readonly="readonly"
                                                                                                name="partner_service_price"
                                                                                                value="0"/></div>

                                <?/*<div class="val_adress slide-description">
                                <div class="radio_group">
                                    <div class="radio_area">
                                        <input type="checkbox" checked="checked" readonly="readonly" disabled="disabled"
                                               data-text="<div class=\'custom_data_text\'><span class=\'option-name\' onclick=\'showAdditOptions(this);\'>Заключение договора<i></i></span></div>"
                                               name="IS_SECURE_DEAL" value="Y" class="customized">
                                        <div class="custom_input checkbox checked" data-name="IS_AGENT_SERVICE">
                                            <div class="ico"></div>
                                            <div class="custom_data_text"><span class="option-name"
                                                                                onclick="showAdditOptions(this);">Заключение договора<i></i></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="option-description">
                                    <p>После заказа услуги, мы заключаем договор с агропроизводителем в вашей редакции и
                                        от вашего имени.</p>
                                </div>
                            </div>

                            <div class="val_adress slide-description">
                                <div class="radio_group">
                                    <div class="radio_area">
                                        <input type="checkbox"
                                               data-text="<div class=\'custom_data_text\'><span class=\'option-name\' >Отбор проб и лабораторная диагностика<i></i></span></div>"
                                               name="IS_ADD_CERT" value="Y" class="customized">
                                        <div class="custom_input checkbox" data-name="IS_ADD_CERT">
                                            <div class="ico"></div>
                                            <div class="custom_data_text"><span class="option-name"
                                                                                onclick="showAdditOptions(this);">Отбор проб и лабораторная диагностика<i></i></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="option-description">
                                    <p>Данная услуга позволяет определить качество, с помощью независимой лаборатории -
                                        партнера АГРОХЕЛПЕР, для предварительной оценки и принятия решения о
                                        покупке. </p>
                                    <p>После заказа услуги вы заключаете договор с нашим партнером, оплачиваете, и он
                                        исполняет услугу.</p>
                                    <p><a href="/upload/docs/Договор на получение результатов исследований.doc"
                                          download="Договор на получение результатов исследований.doc">Договор на
                                            получение результатов исследований</a></p>
                                    <p>
                                        <a href="/upload/docs/Договор на проведение исследований и испытаний образцов продукции.docx"
                                           download="Договор на проведение исследований и испытаний образцов продукции.docx">Договор
                                            на проведение исследований и испытаний образцов продукции</a></p>
                                    <p><a href="/upload/docs/Пример карточки анализа.pdf"
                                          download="Пример карточки анализа.pdf">Пример карточки анализа</a></p>
                                </div>
                            </div>
                            <div class="val_adress slide-description">
                                <div><span class="option-name" onclick="showAdditOptions(this);">Сопроводительные документы, в т.ч:<i></i></span><br><br>
                                </div>
                                <div class="option-description">
                                    <p>После заказа вы заключаете договор с нашим партнёром, оплачиваете, и он исполняет
                                        сделку.</p>
                                </div>
                                <br>
                                <div class="radio_group">
                                    <div class="radio_area">
                                        <input type="checkbox" data-text="Карантинное свидетельство"
                                               name="IS_BILL_OF_HEALTH" value="Y" class="customized">
                                        <div class="custom_input checkbox" data-name="IS_BILL_OF_HEALTH">
                                            <div class="ico"></div>
                                            Карантинное свидетельство
                                        </div>
                                    </div>
                                    <div class="radio_area">
                                        <input type="checkbox" data-text="Ветеринарные свидетельства" name="IS_VET_CERT"
                                               value="Y" class="customized">
                                        <div class="custom_input checkbox" data-name="IS_VET_CERT">
                                            <div class="ico"></div>
                                            Ветеринарные свидетельства
                                        </div>
                                    </div>
                                    <div class="radio_area">
                                        <input type="checkbox" data-text="Декларация о соответствии"
                                               name="IS_QUALITY_CERT" value="Y" class="customized">
                                        <div class="custom_input checkbox" data-name="IS_QUALITY_CERT">
                                            <div class="ico"></div>
                                            Декларация о соответствии
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="val_adress slide-description">
                                <div class="radio_group">
                                    <div class="radio_area">
                                        <input type="checkbox"
                                               data-text="<div class=\'custom_data_text\'><span class=\'option-name\' onclick=\'showAdditOptions(this);\'>Транспортировка<i></i></span></div>"
                                               name="IS_TRANSFER" value="Y" class="customized">
                                        <div class="custom_input checkbox" data-name="IS_TRANSFER">
                                            <div class="ico"></div>
                                            <div class="custom_data_text"><span class="option-name"
                                                                                onclick="showAdditOptions(this);">Транспортировка<i></i></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="option-description">
                                    <p>После заказа вы заключаете договор с нашим партнёром, оплачиваете, и он исполняет
                                        сделку.</p>
                                    <p>Тариф по договоренности, но стартовым будет тот, который учтен в расчете базисной
                                        цены данного товара.</p>
                                    <p><a href="/upload/docs/Договор на перевозку грузов автомобильным транспортом.docx"
                                          download="Договор на перевозку грузов автомобильным транспортом.docx">Договор
                                            на перевозку грузов автомобильным транспортом</a></p>
                                </div>
                            </div>
                            <div class="val_adress slide-description">
                                <div class="radio_group">
                                    <div class="radio_area">
                                        <input type="checkbox"
                                               data-text="<div class=\'custom_data_text\'><span class=\'option-name\' onclick=\'showAdditOptions(this);\'>Безопасная сделка<i></i></span></div>"
                                               name="IS_SECURE_DEAL" value="Y" class="customized">
                                        <div class="custom_input checkbox" data-name="IS_SECURE_DEAL">
                                            <div class="ico"></div>
                                            <div class="custom_data_text"><span class="option-name"
                                                                                onclick="showAdditOptions(this);">Безопасная сделка<i></i></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="option-description">
                                    <p>Данная услуга позволяет вам сделать безопасную предоплату.</p>
                                    <p>Предоплата перечисляется на специальный счет, который разблокируется после
                                        приемки товара.</p>
                                    <p>
                                        <a href="/upload/docs/Договор купли-продажи (расчеты через номинальный счет).docx"
                                           download="Договор купли-продажи (расчеты через номинальный счет).docx">Договор
                                            купли-продажи (расчеты через номинальный счет)</a></p>
                                </div>
                            </div>*/
                                ?>
                            </div>

                            <input type="button" name="save" value="Отправить предложение"
                                   class="submit-btn counter_request_submit">
                        </div>
                    </div>
                    <div class=" refinement_text"><br>
                        <?/*Сделайте предложение, чтобы покупатель увидел ваши намерения и связался с вами в случае
                    заинтересованности.*/
                        ?>
                        Срок действия предложения - 7 дней.
                    </div>
                </div>
                <?
            }
        }else{

        }
        $sResult = ob_get_clean();

        return $sResult;
    }

    /**
     * Получение имени и телефона организатора
     * @param int $iUserId - ID пользователя
     * @param boolean $bShort - флаг того, что нужно брать короткое имя (если есть имя, иначе фамилия)
     * @return array - массив с данными пользователя
     */
    public static function getPartnerInfo($iUserId, $bShort = false){
        $arResult = array(
            'NAME' => '',
            'PHONE' => '',
        );

        $obRes = CUser::GetList(
            ($by = 'ID'),($order = 'ASC'),
            array('ID' => $iUserId),
            array('FIELDS' => array('NAME', 'LAST_NAME', 'SECOND_NAME'))
        );
        if($arData = $obRes->Fetch()){
            if($bShort){
                if($arData['NAME']){
                    $arResult['NAME'] = trim($arData['NAME']);
                }elseif($arData['LAST_NAME']){
                    $arResult['NAME'] = trim($arData['LAST_NAME']);
                }
            }else{
                $arResult['NAME'] = trim($arData['NAME'] . ' ' . $arData['LAST_NAME'] . ' ' . $arData['SECOND_NAME']);
            }
        }

        $obRes = CIBlockElement::GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('partner_profile'),
                'PROPERTY_USER' => $iUserId,
            ),
            false,
            array('nTopCount' => 1),
            array('PROPERTY_PHONE')
        );
        if($arData = $obRes->Fetch()){
            if(!empty($arData['PROPERTY_PHONE_VALUE'])){
                $arResult['PHONE'] = $arData['PROPERTY_PHONE_VALUE'];
            }
        }

        return $arResult;
    }

    /**
     * Расчет стоимости услуг организатора
     * @param int $iCsmPrice - цена с места, от которой производится расчет
     * @param int $iVolume - объем
     * @param boolean $bLabChecked - требуется ли добавление стоимости услуг лаборатории
     * @param boolean $bSupportChecked - требуется ли добавление стоимости сопровождения сделки
     * @param int $iConstOptionContract - константа counter_option_contract
     * @param int $iConstOptionLab - константа counter_option_lab
     * @param int $iConstOptionSupport - константа counter_option_support
     * @return int - стоимость агентских услуг
     */
    public static function countCounterOfferPartnerPrice($iCsmPrice, $iVolume, $bLabChecked, $bSupportChecked, $iConstOptionContract, $iConstOptionLab, $iConstOptionSupport){
        $iResult = 0;

        //расчет стоимости агентского договора
        if(
            filter_var($iCsmPrice, FILTER_VALIDATE_INT)
            && filter_var($iVolume, FILTER_VALIDATE_INT)
            && filter_var($iConstOptionContract, FILTER_VALIDATE_INT)
        ){
            $iResult += partner::countCounterOfferPartnerPriceDogovor($iCsmPrice, $iVolume, $iConstOptionContract);

            //добавление стоимости услуг лаборатории
            if (
                $bLabChecked
                && filter_var($iConstOptionLab, FILTER_VALIDATE_INT)
            ) {
                $iResult += $iConstOptionLab;
            }

            //добавление стоимости сопровождения сделки
            if (
                $bSupportChecked
                && filter_var($iConstOptionSupport, FILTER_VALIDATE_INT)
            ) {
                $iResult += partner::countCounterOfferPartnerPriceSupport($iVolume, $iConstOptionSupport);
            }
        }

        return $iResult;
    }

    /**
     * Расчет части стоимости услуг организатора (заключение договора)
     * @param int $iCsmPrice - цена с места, от которой производится расчет
     * @param int $iVolume - объем
     * @param int $iConstOptionContract - константа counter_option_contract
     * @return int - стоимость части агентских услуг
     */
    public static function countCounterOfferPartnerPriceDogovor($iCsmPrice, $iVolume, $iConstOptionContract){
        $iResult = 0;

        $iResult = round(($iConstOptionContract / 10000.0) * $iCsmPrice * $iVolume);

        return $iResult;
    }

    /**
     * Расчет части стоимости услуг организатора (сопровождения сделки)
     * @param int $iCsmPrice - цена с места, от которой производится расчет
     * @param int $iVolume - объем
     * @param int $iConstOptionSupport - константа counter_option_support
     * @return int - стоимость части агентских услуг
     */
    public static function countCounterOfferPartnerPriceSupport($iVolume, $iConstOptionSupport){
        $iResult = 0;

        $iResult = $iConstOptionSupport * $iVolume;

        return $iResult;
    }

    /**
     * Получение данных для рассылки писем организаторам, и отправка писем
     * (функция предполагается к запуску каждый день, письма отправляются каждый понедельник, если нет изменения цены рынка и один раз в день, если есть изменение цены рынка)
     */
    public static function dailyPartnersOfferMailing(){
        $arrFarmersIds = array();
        $arrPartnersIds = array();
        $arrPartnersData = array();
        $arrOffers = array();
        $arrCounterOffersData = array();
        $arrLinkedPartners = array();
        $arrFarmersNames = array();
        $arrFarmersPhones = array();
        $arrCultureIds = array();
        $arrCultureList = array();
        $iOfferIbId = 0;
        $bCheckMarketDiff = true;
        $bCheckSprosDiff = true;
        $sSubject = $sEmail = $sPhone = $href_url = $straight_href = $result_html = $sTemp = '';
        $sTemp = date('w');
        $arrOffersSpros = array();

        //устанавливаем проверку на разницу цены для всех дней недели кроме понедельника
        if($sTemp == 1){
            $bCheckMarketDiff = false;
            $bCheckSprosDiff = false;
        }

        //получаем активные товары в наличии
        $arrOffers = farmer::getActiveAvailableOffers();

        //получаем ID товаров, для которых действуют предложения
        if(count($arrOffers) > 0){
            $arrCounterOffersData = farmer::getCounterRequestsData(array_keys($arrOffers));
        }

        //оставляем те из товаров, для которых нет предложений
        foreach($arrCounterOffersData as $iOffer => $arrData){
            if(isset($arrOffers[$iOffer])){
                unset($arrOffers[$iOffer]);
            }
        }

        //получаем привязанных организаторов для поставщиков
        foreach ($arrOffers as $iOffer => $arrData){
            $arrFarmersIds[$arrData['FARMER']] = true;
        }
        if(count($arrFarmersIds) > 0) {
            $arrLinkedPartners = farmer::getLinkedPartnersListForFarmers(array_keys($arrFarmersIds));
        }

        //получаем данные для товаров и отправляем организаторам
        if(count($arrLinkedPartners) > 0) {
            //получаем имена и телефоны поставщиков
            $arrFarmersNames = getUserName(array_keys($arrFarmersIds));
            $arrFarmersPhones = farmer::getPhoneList(array_keys($arrFarmersIds));

            //получаем email организаторов
            foreach($arrOffers as $iOffer => $arrData){
                if(isset($arrLinkedPartners[$arrData['FARMER']])){
                    foreach($arrLinkedPartners[$arrData['FARMER']] as $iPartner){
                        $arrPartnersIds[$iPartner] = true;
                    }
                    $arrCultureIds[$arrData['CULTURE']] = true;
                }
            }
            if(count($arrPartnersIds) > 0){
                $arrPartnersData = getUsersEmail(array_keys($arrPartnersIds));
            }

            //получаем названия культур
            if(count($arrCultureIds) > 0){
                $arrCultureList = culture::getNames(array_keys($arrCultureIds));
            }

            //получаем даныне спроса для товаров
            $arrOffersSpros = farmer::getOfferSprosLast(array_keys($arrOffers));

            //получаем и отправляем данные по email, если нужно
            $iOfferIbId = rrsIblock::getIBlockId('farmer_offer');
            foreach($arrOffers as $iOffer => $arrData){
                if(
                    isset($arrLinkedPartners[$arrData['FARMER']])
                    && isset($arrFarmersNames[$arrData['FARMER']])
                    && isset($arrCultureList[$arrData['CULTURE']])
                ){
                    $GLOBALS['MARKET_DIFF'] = 0;
                    $href_url = '/partner_offer_page/?offer_id=' . $iOffer;

                    $sSubject = $arrFarmersNames[$arrData['FARMER']] . ', ' . $arrCultureList[$arrData['CULTURE']];
                    $sPhone = '';
                    $sText = $sSubject;
                    if(!empty($arrFarmersPhones[$arrData['FARMER']])){
                        $sPhone = 'Телефон: ' . $arrFarmersPhones[$arrData['FARMER']];
                        $sText .= '<br/>' . $sPhone;
                    }

                    $arrFields = array(
                        'SUBJECT' => $sSubject,
                    );

                    //выбираем спрос товара из общего списка
                    $arrCurOfferSpros = (array_key_exists($iOffer, $arrOffersSpros) ? $arrOffersSpros[$iOffer] : array());
                    if(
                        !empty($arrCurOfferSpros['BY'])
                        && $arrCurOfferSpros['BY'] != $arrCurOfferSpros['Y']
                    ){
                        $tmpVal = round($arrCurOfferSpros['Y'] - $arrCurOfferSpros['BY']);
                        if($tmpVal != 0) {
                            $arrCurOfferSpros['CH'] = $tmpVal;
                        }
                    }

                    foreach($arrLinkedPartners[$arrData['FARMER']] as $iPartner){
                        if(isset($arrPartnersData[$iPartner])){
                            $straight_href = generateStraightHref($iPartner, $arrData['FARMER'], 'f', $iOffer, $iOfferIbId, 'ib', $href_url, '/partner_offer_page/');
                            //оформление ссылки в html-тег
                            $straight_href = '<a href="' . $straight_href . '">' . $straight_href . '</a>';
                            $result_html = farmer::partnerCreateCounterRequestText($iOffer, $straight_href, true, $bCheckMarketDiff, true, false, $arrCurOfferSpros);

                            //проверяем - изменилась ли цена для данного товара (если требуется проверка изменения цены и она не изменилась, то переходим к следующему товару)
                            //также проверяем - есть ли изменение спроса
                            if(
                                $bCheckMarketDiff
                                && $GLOBALS['MARKET_DIFF'] == 0
                                && $bCheckSprosDiff
                                && empty($arrCurOfferSpros['CH'])
                            ){
                                break;
                            }

                            $arrFields['TEXT'] = $sText . '<br/>_<br/>' . $result_html . '<br/>_';
                            $arrFields['EMAIL'] = $arrPartnersData[$iPartner];
                            CEvent::Send('SEND_PARTNER_DAILY_OFFERS', 's1', $arrFields);
                        }
                    }
                }
            }
        }
    }

    /**
     * Получение ников поставщиков для организаторов
     * @param array $arrFarmersIds - массив ID поставщиков
     * @return array - массив названий для организаторов
     */
    public static function getFarmersNicks($arrFarmersIds){
        $arrResult = array();

        if(
            is_array($arrFarmersIds)
            && count($arrFarmersIds) > 0
        )
        $obRes = CIBlockElement::GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('farmer_agent_link'),
                'PROPERTY_USER_ID' => $arrFarmersIds
            ),
            false,
            false,
            array('PROPERTY_USER_ID', 'PROPERTY_AGENT_ID', 'PROPERTY_FARMER_NICKNAME')
        );
        while($arrData = $obRes->Fetch()){
            if(
                !empty($arrData['PROPERTY_USER_ID_VALUE'])
                && !empty($arrData['PROPERTY_AGENT_ID_VALUE'])
                && !empty($arrData['PROPERTY_FARMER_NICKNAME_VALUE'])
            ){
                $arrResult[$arrData['PROPERTY_USER_ID_VALUE']][$arrData['PROPERTY_AGENT_ID_VALUE']] = $arrData['PROPERTY_FARMER_NICKNAME_VALUE'];
            }
        }

        return $arrResult;
    }

    /**
     * Ищем дубль ИНН в профилях поставщиков и покупателей
     * @param int $iInn - значение ИНН
     * @return boolean - признак найденного дубля
     */
    public static function isDoubleProfileInn($iInn){
        $bResult = false;

        if(filter_var($iInn, FILTER_VALIDATE_INT)) {
            $arFilter = array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('farmer_profile'),
                'ACYIVE' => 'Y',
                'PROPERTY_INN' => $iInn
            );
            $res = CIBlockElement::GetList(
                array('SORT' => 'ASC'),
                $arFilter,
                false,
                array('nTopCount' => 1),
                array('ID')
            );
            if ($res->SelectedRowsCount() > 0) {
                $bResult = true;
            }
            if (!$bResult) {
                $arFilter = array(
                    'IBLOCK_ID' => rrsIblock::getIBlockId('client_profile'),
                    'ACYIVE' => 'Y',
                    'PROPERTY_INN' => $iInn
                );
                $res = CIBlockElement::GetList(
                    array('SORT' => 'ASC'),
                    $arFilter,
                    false,
                    array('nTopCount' => 1),
                    array('ID')
                );
                if ($res->SelectedRowsCount() > 0) {
                    $bResult = true;
                }
            }
        }

        return false;
    }
}
?>