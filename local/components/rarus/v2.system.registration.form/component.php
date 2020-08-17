<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2013 Bitrix
 */

/**
 * Bitrix vars
 * @global CUser $USER
 * @global CMain $APPLICATION
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponent $this
 */

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Security\Mfa;

/*
Authorization form (for prolog)
Params:
	REGISTER_URL => path to page with authorization script (component?)
	PROFILE_URL => path to page with profile component
*/

CJSCore::Init(array('date'));

if (!$USER->IsAuthorized()) {
    $arResult['USER'] = array();
    $arResult['ERROR'] = false;
    $arResult['SHOW_ERRORS'] = (array_key_exists('SHOW_ERRORS', $arParams) && $arParams['SHOW_ERRORS'] == 'Y'? 'Y' : 'N');
    $arResult['INVITE_ERROR'] = '';
    $arResult['INVITE_USER_MASTER'] = '';
    $arResult['SHOW_TYPE'] = (isset($_POST['PUBLIC_FORM']) && $_POST['PUBLIC_FORM'] == 'REGISTER' && isset($_POST['TYPE']) ? $_POST['TYPE'] : 'farmer');

    CModule::IncludeModule('iblock');
    $el_obj = new CIBlockElement;
    $ib_obj = new CIBlock;
    $user_obj = new CUser;

    $res = CIBlockElement::GetList(
        array('SORT' => 'ASC'),
        array('IBLOCK_ID' => rrsIblock::getIBlockId('foundations'), 'ACTIVE' => 'Y'),
        false,
        false,
        array('ID', 'NAME', 'CODE', 'PROPERTY_CHEGO', 'PROPERTY_SHOW')
    );
    while ($ob = $res->Fetch()) {
        $arResult['FOUND'][$ob['CODE']] = $ob;
    }

    if ($_POST['REG_FORM'] == 'Y')  {
        //самостоятельная регистрация

        $register_mode = 'phone'; //тип регистрации (email, телефон), телефон приоритетнее
        $check_phone = false;
        $check_email = false;

        foreach ($_POST as $key => $val) {
            $arResult[$key] = $val;
        }

        //проверка заполненных полей
        if(!isset($_POST['TYPE'])
            || (
                $_POST['TYPE'] != 'farmer'
                && $_POST['TYPE'] != 'client'
//                && $_POST['TYPE'] != 'agent'
//                && $_POST['TYPE'] != 'client_agent'
            )
        ){
            $arResult['ERROR_MESSAGE'] = 'Не все обязательные поля формы заполнены';
        }

        if($arResult['ERROR_MESSAGE'] == '' &&
            (!isset($_POST['PROP__NDS'])
                || trim($_POST['PROP__NDS']) == ''
            )
            && $_POST['TYPE'] != 'agent'
            && $_POST['TYPE'] != 'client_agent'
        ){
            $arResult['ERROR_MESSAGE'] = 'Не все обязательные поля формы заполнены';
        }
        if($arResult['ERROR_MESSAGE'] == '' &&
            (!isset($_POST['USER_NAME'])
                || trim($_POST['USER_NAME']) == ''
            )
        ){
            $arResult['ERROR_MESSAGE'] = 'Не все обязательные поля формы заполнены';
        }
        if($arResult['ERROR_MESSAGE'] == '' &&
            (!isset($_POST['USER_NAME'])
                || trim($_POST['USER_NAME']) == ''
            )
        ){
            $arResult['ERROR_MESSAGE'] = 'Не все обязательные поля формы заполнены';
        }
        if($arResult['ERROR_MESSAGE'] == ''){
            if(isset($_POST['PROP__PHONE'])){
                $phove_val = str_replace(array('+', ' ', '(', ')', '-'), '', $_POST['PROP__PHONE']);
                if(preg_replace('/[0-9]/s', '', $phove_val) == ''){
                    if(strlen($phove_val) == 11)
                    {
                        $phove_val = '7' . substr($phove_val, 1, strlen($phove_val) - 1);
                    }
                    elseif(strlen($phove_val) == 10){
                        $phove_val = '7' . $phove_val;
                    }

                    if(strlen($phove_val) == 11){
                        //проверка телефона на дубль

                        $is_double = false;
                        $check_doubles = profilePhoneDoubles($phove_val);
                        if(is_numeric($check_doubles)){
                            //проверяем был ли подтвержден телефон (если нет, то можно использовать номер)
                            $res = CUser::GetList(
                                ($by = 'id'), ($order = 'asc'),
                                array(
                                    'ID' => $check_doubles,
                                    '!UF_FIRST_PHONE' => 1
                                ),
                                array('FIELDS' => array('ID'))
                            );
                            if($res->SelectedRowsCount() > 0) {
                                $is_double = true;
                            }
                        }

                        if($is_double){
                            $arResult['ERROR_MESSAGE'] = 'Данный телефон уже зарегистрирован в системе';
                        }else{
                            $check_phone = true;
                        }
                    }
                }
            }
            if(isset($_POST['USER_EMAIL'])
                && filter_var($_POST['USER_EMAIL'], FILTER_VALIDATE_EMAIL)
            ){
                $check_email = true;
                if(!$check_phone){
                    $register_mode = 'email';
                }
            }

            if(!$check_phone
                && !$check_email
            ){
                $arResult['ERROR_MESSAGE'] = 'Не все обязательные поля формы заполнены';
            }
        }

        //Personal data computing agreement check
        if (!isset($_POST['AUTH_REG_CONFIM']) || $_POST['AUTH_REG_CONFIM'] != 'Y') {
            $arResult['ERROR_MESSAGE'] = 'Установите галочку согласия обработки персональных данных';
        }

        //Personal data computing agreement check
        if (!isset($_POST['AUTH_REGLAMENT_CONFIM']) || $_POST['AUTH_REGLAMENT_CONFIM'] != 'Y') {
            $arResult['ERROR_MESSAGE'] = 'Установите галочку согласия с регламентом системы АГРОХЕЛПЕР';
        }

        if ($arResult['ERROR_MESSAGE'] != '')
            $arResult['ERROR'] = true;

        //нет ошибок
        if (!$arResult['ERROR']) {

            $phone_email = '';
            if($check_email) {
                //ищем пользователя по email (проверяем на дубли)
                $arFilter = array("EMAIL" => trim($_POST['USER_EMAIL']));
                $rsUsers = $user_obj->GetList(
                    ($by = "id"), ($order = "desc"),
                    $arFilter,
                    array(
                        'FIELDS' => array('ID', 'EMAIL', 'ACTIVE'),
                        'SELECT' => array('UF_HASH_INVITE', 'UF_HASH')
                    )
                );
                if ($us = $rsUsers->GetNext()) {
                    $userInfo = $us;
                }

                if (intval($userInfo['ID']) > 0) {
                    if ($userInfo['ACTIVE'] == 'Y') {
                        $arResult['ERROR_MESSAGE'] = 'Данный электронный адрес уже зарегистрирован. Измените электронный адрес или авторизуйтесь';
                    } else {
                        if (!isset($_GET['reg_hash'])
                            && (isset($_GET['reg_hash']) && strlen($_GET['reg_hash']) != 10)
                            && ($_GET['reg'] == 'mobile' && !isset($_GET['hash']))
                        ) {
                            $arResult['ERROR_MESSAGE'] = 'Данный электронный адрес уже зарегистрирован. Измените электронный адрес или авторизуйтесь';
                        }
                    }
                }
            }elseif($register_mode == 'phone'){
                //генерируем почту из телефона и проверяем на дубли
                $phone_email = makeEmailFromPhone($_POST['PROP__PHONE']);
                $arFilter = array("EMAIL" => $phone_email);
                $rsUsers = $user_obj->GetList(
                    ($by="id"), ($order="desc"),
                    $arFilter,
                    array(
                        'FIELDS' => array('ID', 'EMAIL', 'ACTIVE'),
                        'SELECT' => array('UF_HASH_INVITE', 'UF_HASH')
                    )
                );

                if ($us = $rsUsers->GetNext()) {
                    $userInfo = $us;
                }

                if (intval($userInfo['ID']) > 0) {
                    if ($userInfo['ACTIVE'] == 'Y') {
                        $arResult['ERROR_MESSAGE'] = 'Данный телефон уже зарегистрирован. Воспользуйтесь формой восстановления пароля';
                    }
                    else {
                        if(!isset($_GET['reg_hash'])
                            && (isset($_GET['reg_hash']) && strlen($_GET['reg_hash']) != 10)
                            && ($_GET['reg'] == 'mobile' && !isset($_GET['hash']))
                        ){
                            $arResult['ERROR_MESSAGE'] = 'Данный телефон уже зарегистрирован. Воспользуйтесь формой восстановления пароля';
                        }
                    }
                }
            }

            if(!isset($arResult['ERROR_MESSAGE']) || $arResult['ERROR_MESSAGE'] == ''){
                //ошибки отсутствуют, можно создать нового пользователя
                CModule::IncludeModule("main");
                $arGroups = array(2);
                $no_need_confirmation = false;
                $ib_code = '';
                $ib_id = '';
                if (isset($_POST['TYPE'])) {
                    if (isset($_GET['reg_hash']) && trim($_GET['reg_hash']) != '') {
                        $check_ib_id = substr($_GET['reg_hash'], 8, 2);
                        $check_hash_val = substr($_GET['reg_hash'], 0, 8);
                        $res = $user_obj->GetList(
                            ($by="id"), ($order="asc"),
                            array('ACTIVE' => 'N', 'UF_HASH_INVITE' => $check_hash_val),
                            array('FIELDS' => array('ID', 'EMAIL'))
                        );
                        if ($data = $res->Fetch()) {
                            $arUser = $data;
                        }
                    }
                    if ($_GET['reg'] == 'mobile' && isset($_GET['hash']) && trim($_GET['hash']) != '') {
                        $check_hash_val = trim($_GET['hash']);
                        $res = $user_obj->GetList(
                            ($by="id"), ($order="asc"),
                            array('ACTIVE' => 'N', 'UF_HASH' => $check_hash_val),
                            array('FIELDS' => array('ID', 'EMAIL'))
                        );
                        if ($data = $res->Fetch()) {
                            $arUser = $data;
                        }
                    }

                    switch ($_POST['TYPE']) {
                        case 'client':
                            $arGroups[] = 9;
                            $ib_code = 'client_profile';
                            $uCode = 'c';
                            //check if user comes with email invite href
                            if ($arUser['ID'] > 0) {
                                $res = $el_obj->GetList(
                                    array('ID' => 'DESC'),
                                    array(
                                        'IBLOCK_ID' => rrsIblock::getIBlockId('client_profile'),
                                        'PROPERTY_USER' => $arUser['ID']
                                    ),
                                    false,
                                    array('nTopCount' => 1),
                                    array('ID', 'IBLOCK_ID', 'PROPERTY_PARTNER_ID')
                                );
                                if ($ob = $res->Fetch()) {
                                    $arProfile = $ob;
                                    $no_need_confirmation = true;
                                }
                            }
                            break;

                        case 'farmer':
                            $arGroups[] = 11;
                            $ib_code = 'farmer_profile';
                            $uCode = 'f';
                            //check if user comes with email invite href
                            if ($arUser['ID'] > 0) {
                                $res = $el_obj->GetList(
                                    array('ID' => 'DESC'),
                                    array(
                                        'IBLOCK_ID' => rrsIblock::getIBlockId('farmer_profile'),
                                        'PROPERTY_USER' => $arUser['ID']
                                    ),
                                    false,
                                    array('nTopCount' => 1),
                                    array('ID', 'IBLOCK_ID', 'PROPERTY_PARTNER_ID')
                                );
                                if ($ob = $res->Fetch()) {
                                    $arProfile = $ob;
                                    $no_need_confirmation = true;
                                }
                            }
                            break;

                        /*case 'agent':
                            $arGroups[] = 13;
                            $ib_code = 'agent_profile';
                            $uCode = 'ag';
                            //check if user comes with email invite href
                            if ($arUser['ID'] > 0) {
                                $res = $el_obj->GetList(
                                    array('ID' => 'DESC'),
                                    array(
                                        'IBLOCK_ID' => rrsIblock::getIBlockId('agent_profile'),
                                        'PROPERTY_USER' => $arUser['ID']
                                    ),
                                    false,
                                    array('nTopCount' => 1),
                                    array('ID', 'IBLOCK_ID', 'PROPERTY_PARTNER_ID')
                                );
                                if ($ob = $res->Fetch()) {
                                    $arProfile = $ob;
                                    $no_need_confirmation = true;
                                }
                            }
                            break;

                        case 'client_agent':
                            $arGroups[] = 14;
                            $ib_code = 'client_agent_profile';
                            $uCode = 'agc';
                            //check if user comes with email invite href
                            if ($arUser['ID'] > 0) {
                                $res = $el_obj->GetList(
                                    array('ID' => 'DESC'),
                                    array(
                                        'IBLOCK_ID' => rrsIblock::getIBlockId('client_agent_profile'),
                                        'PROPERTY_USER' => $arUser['ID']
                                    ),
                                    false,
                                    array('nTopCount' => 1),
                                    array('ID', 'IBLOCK_ID', 'PROPERTY_PARTNER_ID')
                                );
                                if ($ob = $res->Fetch()) {
                                    $arProfile = $ob;
                                    $no_need_confirmation = true;
                                }
                            }
                            break;*/
                    }
                }
                else {
                    $arResult['ERROR_MESSAGE'] = 'Не указана роль пользователя';
                }

                //if no need confirmation -> there is an password value
                if (!isset($_POST['USER_PASS']) || trim($_POST['USER_PASS']) == '') {
                    $arResult['ERROR_MESSAGE'] = 'Указан некорректный пароль';
                }
                elseif (!isset($_POST['USER_PASS_CONFIRM']) || trim($_POST['USER_PASS_CONFIRM']) == '') {
                    $arResult['ERROR_MESSAGE'] = 'Указано некорректное подтверждение пароля';
                }
                elseif (isset($_POST['USER_PASS']) && trim($_POST['USER_PASS']) != ''
                    && isset($_POST['USER_PASS_CONFIRM']) && trim($_POST['USER_PASS_CONFIRM']) != ''
                ) {
                    if ($_POST['USER_PASS'] != $_POST['USER_PASS_CONFIRM']) {
                        $arResult['ERROR_MESSAGE'] = 'Указанные пароли не совпадают';
                    }
                    else {
                        $password = $_POST['USER_PASS'];
                    }
                }

                if ($no_need_confirmation == false)
                {
                    //usual register
                    //check recaptcha
                    $recaptcha_response = '';
                    $recaptcha_key = '6LeDzmAUAAAAACcr90tdCuygA__v1xHsEIs37DFe';
                    $captcha_error = false;
                    if ($captcha_error) {
                        $arResult['ERROR_MESSAGE'] = 'Ошибка google-каптчи. Обновите страницу и попробуйте заполнить форму заново. В случае повторения ошибки напишите администрации через форму обраной связи.';
                    }

                    //проверка подтверждения телефона по смс
                    if($register_mode == 'phone'
                        && isset($_POST['PROP__PHONE'])
                        && !isset($_SESSION['success_sms_' . getPhoneDigits($_POST['PROP__PHONE'])])
                    ){
                        $arResult['ERROR_MESSAGE'] = 'Ошибка подтверждения телефона по смс. Обновите страницу и попробуйте заполнить форму заново. В случае повторения ошибки напишите администрации через форму обраной связи.';
                    }
                }

                if(!empty($_POST['PROP__INN'])){
                   if(partner::isDoubleProfileInn($_POST['PROP__INN'])){
                       $arResult['ERROR_MESSAGE'] = 'Данный ИНН уже зарегистрирован в системе.';

                       //снимаем запомненное подветрждение ИНН
                       if(isset($_SESSION['success_inn_' . $_POST['PROP__INN']])){
                           unset($_SESSION['success_inn_' . $_POST['PROP__INN']]);
                       }
                   }
                }

                if (!isset($arResult['ERROR_MESSAGE']) || $arResult['ERROR_MESSAGE'] == '') {
                    $email_val = ($phone_email != '' ? $phone_email : trim($_POST['USER_EMAIL']));
                    $arFields = array(
                        "NAME"                  => trim($_POST['USER_NAME']),
                        "LAST_NAME"             => trim($_POST['USER_LAST_NAME']),
                        "EMAIL"                 => $email_val,
                        "LOGIN"                 => $email_val,
                        "ACTIVE"                => "Y",
                        "GROUP_ID"              => $arGroups,
                        "PASSWORD"              => $password,
                        "CONFIRM_PASSWORD"      => $password,
                        "UF_PRIV_POLICY_CONF"   => $_POST['AUTH_REG_CONFIM'],
                        "UF_REGLAMENT_CONF"     => $_POST['AUTH_REGLAMENT_CONFIM'],
                        "UF_FIRST_PHONE"        => 0,
                        "UF_FIRST_LOGIN"        => 1,
                        // new fields
                        "PERSONAL_PHONE"        => $_POST["PROP__PHONE"],
                      // todo инн
                      // todo регион
                    );

                    //поставщик требует ручной активации администратором
                    if(isset($uCode)
                        && $uCode == 'f'
                    ){
                        $arFields['ACTIVE'] = 'N';
                    }

                    if($no_need_confirmation) {
                        $arFields['UF_HASH'] = '';
                        $arFields['UF_HASH_INVITE'] = '';
                    }
                    if($arResult['SHOW_TYPE'] != 'agent'
                        && $arResult['SHOW_TYPE'] != 'client_agent'
                    ){
                        //при регистрации покупателя или поставщика указываются также: отчество(необяз.), ИНН
                        $arFields['SECOND_NAME'] = trim($_POST['USER_SECOND_NAME']);
                    }

                    $ID = 0;
                    if ($arUser['ID'] > 0) {
                        $ID = $arUser['ID'];
                        if (!$user_obj->Update($ID, $arFields, false)) {
                            //update error
                            $ID = 0;
                        }
                    }
                    else {
                        $ID = $user_obj->Add($arFields);
                    }

                    if (intval($ID) > 0) {
                        if ($no_need_confirmation == false) {
                            //пользователь добавлен
                            //формируем токен
                            $hash = hashPass();

                            $fields = array(
                                "UF_HASH" => $hash,
                                "UF_HASH_INVITE" => '',
                            );
                            $user_obj->Update($ID, $fields);

                            $arEventFields = array(
                                "EMAIL" => trim($_POST['USER_EMAIL']),
                                "HREF" => $GLOBALS['host'] . '/?reg=yes&hash=' . $hash . '#action=register',
                            );

                            //удаляем переменную, если подтверждение телефона по смс уже не нужно
                            if(isset($_SESSION['success_sms_' . str_replace(array('+', '-', '(',')', ' '), '', $_POST['PROP__PHONE'])])){
                                unset($_SESSION['success_sms_' . str_replace(array('+', '-', '(',')', ' '), '', $_POST['PROP__PHONE'])]);
                            }

                            //удаляем переменную, если проверка инн уже не нужна
                            if(isset($_SESSION['success_inn_' . $_POST['PROP__INN']])){
                                unset($_SESSION['success_inn_' . $_POST['PROP__INN']]);
                            }
                        }

                        if ($ib_code != '') {
                            $ib_id = rrsIblock::getIBlockId($ib_code);

                            $arProps = array();
                            foreach ($_POST as $cur_key => $cur_val) {
                                if (strlen($cur_key) != strlen(str_replace('PROP__', '', $cur_key))) {
                                    $arProps[str_replace('PROP__', '', $cur_key)] = $cur_val;
                                }
                            }

                            if (count($arProps) > 0) {
                                $arProps['USER'] = $ID;
                                if (is_array($_FILES) && sizeof($_FILES) > 0) {
                                    foreach ($_FILES as $key => $file) {
                                        if ($file['error'] < 1 && isset($file['tmp_name'])) {
                                            $arProps[str_replace('PROP__', '', $key)] = $file;
                                        }
                                    }
                                }

                                if ($uCode != '') {
                                    $noticeList = notice::getNoticeListByUserType($uCode);
                                    if (is_array($noticeList) && sizeof($noticeList) > 0) {
                                        $n = 0;
                                        foreach ($noticeList as $item) {
                                            if (in_array($uCode, $item['PROPERTY_CAN_CHANGE_VALUE'])) {
                                                $arProps['NOTICE']["n".$n] = array("VALUE" => $item['ID']);
                                                $n++;
                                            }
                                        }
                                    }
                                }

                                //обработка типа плательщика - юр/физ. лицо (замена кода из списка на ID)
                                if(isset($arProps['UL_TYPE'])){
                                    $types_arr = rrsIblock::getPropListKey($ib_code, 'UL_TYPE');
                                    if(isset($types_arr[$arProps['UL_TYPE']])){
                                        $arProps['UL_TYPE'] = $types_arr[$arProps['UL_TYPE']]['ID'];
                                    }
                                }

                                $arFields = array(
                                    'IBLOCK_ID' => $ib_id,
                                    'NAME' => 'Свойства пользователя ' . $_POST['USER_EMAIL'] . ' с ID [' . $ID . ']',
                                    'ACTIVE' => 'Y'
                                );
                                if ($no_need_confirmation) {
                                    if (isset($arProfile['PROPERTY_PARTNER_ID_VALUE']) && is_numeric($arProfile['PROPERTY_PARTNER_ID_VALUE'])) {
                                        if ($ib_code == 'client_profile') {
                                            $arProps['PARTNER_ID_TIMESTAMP'] = time();
                                            $arProps['PARTNER_ID'] = '';
                                            $arProps['DKPLINK'] = $GLOBALS['host'].'/admin/genpdf/?id='.$arProfile['ID'];
                                            $el_obj->Add(
                                                array(
                                                    'IBLOCK_ID' => rrsIblock::getIBlockId('client_partner_link'),
                                                    'ACTIVE' => 'Y',
                                                    'NAME' => 'Привязка покупателя [' . $ID . '] к организатору [' . $arProfile['PROPERTY_PARTNER_ID_VALUE'] . ']',
                                                    'PROPERTY_VALUES' => array(
                                                        'USER_ID' => $ID,
                                                        'PARTNER_ID' => $arProfile['PROPERTY_PARTNER_ID_VALUE'],
                                                        'PARTNER_LINK_DATE' => date('d.m.Y H:i:s')
                                                    )
                                                )
                                            );
                                        }
                                        /*elseif ($ib_code == 'agent_profile') {
                                            $arProps['PARTNER_ID_TIMESTAMP'] = time();
                                            $arProps['PARTNER_ID'] = '';
                                            $el_obj->Add(
                                                array(
                                                    'IBLOCK_ID' => rrsIblock::getIBlockId('agent_partner_link'),
                                                    'ACTIVE' => 'Y',
                                                    'NAME' => 'Привязка агента [' . $ID . '] к организатору [' . $arProfile['PROPERTY_PARTNER_ID_VALUE'] . ']',
                                                    'PROPERTY_VALUES' => array(
                                                        'USER_ID' => $ID,
                                                        'PARTNER_ID' => $arProfile['PROPERTY_PARTNER_ID_VALUE'],
                                                        'PARTNER_LINK_DATE' => date('d.m.Y H:i:s')
                                                    )
                                                )
                                            );
                                        }
                                        elseif ($ib_code == 'client_agent_profile') {
                                            $arProps['PARTNER_ID_TIMESTAMP'] = time();
                                            $arProps['PARTNER_ID'] = '';
                                            $el_obj->Add(
                                                array(
                                                    'IBLOCK_ID' => rrsIblock::getIBlockId('client_agent_partner_link'),
                                                    'ACTIVE' => 'Y',
                                                    'NAME' => 'Привязка агента [' . $ID . '] к организатору [' . $arProfile['PROPERTY_PARTNER_ID_VALUE'] . ']',
                                                    'PROPERTY_VALUES' => array(
                                                        'USER_ID' => $ID,
                                                        'PARTNER_ID' => $arProfile['PROPERTY_PARTNER_ID_VALUE'],
                                                        'PARTNER_LINK_DATE' => date('d.m.Y H:i:s')
                                                    )
                                                )
                                            );
                                        }*/
                                        elseif ($ib_code == 'farmer_profile') {
                                            $arProps['PARTNER_ID_TIMESTAMP'] = 0;
                                        }
                                    }

                                    if(isset($_POST['PROP__PHONE']) && $_POST['PROP__PHONE'] != ''){
                                        $arProps['PHONE'] = makeCorrectPhone($_POST['PROP__PHONE']);
                                    }

                                    $el_obj->Update($arProfile['ID'], $arFields);
                                    $el_obj->SetPropertyValuesEx($arProfile['ID'], $arProfile['IBLOCK_ID'], $arProps);
                                }
                                else {
                                    $arFields['PROPERTY_VALUES'] = $arProps;
                                    $id_val = $el_obj->Add($arFields);
                                    if ($id_val > 0 && $ib_code == 'client_profile') {
                                        $el_obj->SetPropertyValuesEx(
                                            $id_val,
                                            rrsIblock::getIBlockId('client_profile'),
                                            array('DKPLINK' => $GLOBALS['host'].'/admin/genpdf/?id='.$id_val)
                                        );
                                    }
                                }

                              $f = fopen($_SERVER["DOCUMENT_ROOT"]."/__addRegister.json", "w");
                              fwrite($f, json_encode($arProps, JSON_UNESCAPED_UNICODE));
                              fclose($f);
                            }
                        }

                        //сейчас регистрация только по телефону -> комментируем блок
                        /*if ($no_need_confirmation == false
                            && $register_mode == 'email'
                        ) {

                            //направляем письмо на почту, если пользователь регистрируется посредством почты
                            $res_val = CEvent::Send("REG_HASH_PASSWORD", "s1", $arEventFields);
                        }*/

                        //уведомление администраторов и того, кто пригласил
                        $user_data = 'Пользователь роли '; //данные для рассылки
                        $arSendFields = array();

                        $invite_user_info = array();
                        switch($uCode){
                            case 'c':
                                $user_data .= 'покупатель';
                                //получение данных того, кто пригласил для уведомления
                                if ($no_need_confirmation) {
                                    $invite_user_info = client::getPartnerEmailData($ID, true);
                                }
                                break;

                            case 'f':
                                $user_data .= 'поставщик';
                                //получение данных того, кто пригласил для уведомления
                                if ($no_need_confirmation) {
                                    $invite_user_info = farmer::getPartnerEmailData($ID);
                                }
                                break;

                            /*case 'ag':
                                $user_data .= 'агент поставщика';
                                //получение данных того, кто пригласил для уведомления
                                if ($no_need_confirmation) {
                                    $invite_user_info = agent::getPartnerEmailDataF($ID, true);
                                }
                                break;

                            case 'agc':
                                $user_data .= 'агент покупателя';
                                //получение данных того, кто пригласил для уведомления
                                if ($no_need_confirmation) {
                                    $invite_user_info = agent::getPartnerEmailDataCL($ID, true);
                                }
                                break;*/

                            case 't':
                                $user_data .= 'транспортная компания';
                                //получение данных того, кто пригласил для уведомления
                                if ($no_need_confirmation) {
                                    $invite_user_info = transport::getPartnerEmailData($ID, true);
                                }
                                break;
                        }
                        $user_data .= ' зарегистрирован ' . date('Y.m.d H:i') . '<br/><br/>';
                        $email_list = array($invite_user_info);
                        //добираем email администраторов
                        $res = $user_obj->GetList(
                            ($by = 'id'), ($order = 'asc'),
                            array('GROUPS_ID' => array(1)),
                            array('FIELDS' => array('EMAIL', 'NAME', 'LAST_NAME', 'LOGIN'))
                        );
                        while($data = $res->Fetch()){
                            $temp_name = trim($data['NAME'] . ' ' . $data['LAST_NAME']);
                            if($temp_name == ''){
                                $temp_name = $data['LOGIN'];
                            }
                            $email_list[] = array('EMAIL' => $data['EMAIL'], 'NAME' => $temp_name);
                        }
                        if(
                            !empty($_POST['USER_EMAIL'])
                            && !checkEmailFromPhone($_POST['USER_EMAIL'])
                        ){
                            $user_data .= 'Email: ' . $_POST['USER_EMAIL'] . '<br/>';
                        }
                        if(!empty($_POST['PROP__PHONE'])){
                            $user_data .= 'Телефон: ' . $_POST['PROP__PHONE'] . '<br/>';
                        }
                        foreach($email_list as $cur_data){
                            $arSendFields['RECIPIENT_DATA'] = $cur_data['NAME'];
                            $arSendFields['EMAIL_LIST'] = $cur_data['EMAIL'];
                            $arSendFields['USER_DATA'] = $user_data;
                            if(isset($uCode)
                                && $uCode == 'f'
                            ){
                                $arSendFields['USER_DATA'] .= ' <br/>Пользователя необходимо активировать (<a href="' . $GLOBALS['host'] . '/bitrix/admin/user_edit.php?lang=ru&ID=' . $ID . '">ссылка</a>)';
                            }
                            CEvent::Send("NEW_USER_ADD", "s1", $arSendFields);
                        }

                        if(isset($uCode)
                            && $uCode == 'f'
                        ){
                            $arResult['SUCCESS_MESSAGE'] = 'Ваша регистрация завершена</br>В ближаешее время ваш аккаунт будет активирован, ожидайте SMS на указанный вами телефон';
                        }else {
                            $user_obj->Authorize($ID);
                            LocalRedirect('/');
                            exit;
                        }
                        //$arResult['SUCCESS_MESSAGE'] = 'Регистрация прошла успешно! Вы можете авторизоваться, используя ваши логин и пароль.';
                    }
                    else {
                        $arResult['ERROR_MESSAGE'] = $user_obj->LAST_ERROR;
                    }
                }
            }
        }
    }
    else {
        if (isset($_REQUEST['reg']) && $_REQUEST['reg'] == 'yes'
            && isset($_REQUEST['hash']) && trim($_REQUEST['hash']) != ''
        ) {
            if(isset($_POST['pass']) && isset($_POST['confirm_pass']) &&
                (trim($_POST['pass']) == '' || trim($_POST['confirm_pass']) == ''
                || trim($_POST['pass']) != trim($_POST['confirm_pass'])
                )
            ) {
                $arResult['ERROR'] = 'Y';
                $arResult['ERROR_MESSAGE'] = 'Указанные пароли не совпадают.';
                $arResult['FINALIZE_REGISTER'] = 'Y';
            }
            else {
                $hash_val = trim($_REQUEST['hash']);
                $res = $user_obj->GetList(($by="id"), ($order="asc"), array('ACTIVE' => 'N', 'UF_HASH' => $hash_val), array('FIELDS' => array('ID', 'EMAIL')));
                if ($data = $res->Fetch()) {
                    if (isset($_POST['pass']) && trim($_POST['pass']) != '') {
                        $arFields = array(
                            'UF_HASH' => '',
                            'ACTIVE' => 'Y',
                            'PASSWORD' => trim($_POST['pass']),
                            'CONFIRM_PASSWORD' => trim($_POST['pass'])
                        );

                        $user_email = $data['EMAIL'];

                        $user_groups = CUser::GetUserGroup($data['ID']);

                        $user_obj->Update($data['ID'], $arFields, false);

                        $user_obj->Authorize($data['ID']);
                        $arResult['SUCCESS_MESSAGE'] = 'Ваша регистрация завершена';

                        //уведомление администраторов и организаторов
                        $user_data = 'Пользователь роли '; //данные для рассылки
                        $arSendFields = array();
                        $user_groups = array_flip($user_groups);
                        $prof_id = 0;
                        if(isset($user_groups[9])){
                            $user_data .= 'покупатель';
                            $prof_id = rrsIblock::getIBlockId('client_profile');
                        }elseif(isset($user_groups[11])){
                            $user_data .= 'поставщик';
                            $prof_id = rrsIblock::getIBlockId('farmer_profile');
                        }
                        $user_data .= ' зарегистрирован ' . date('Y.m.d H:i') . '<br/><br/>';
                        $email_list = array();
                        $res = $user_obj->GetList(
                            ($by = 'id'), ($order = 'asc'),
                            array('GROUPS_ID' => array(1)),
                            array('FIELDS' => array('EMAIL', 'NAME', 'LAST_NAME', 'LOGIN'))
                        );
                        while($data = $res->Fetch()){
                            $temp_name = trim($data['NAME'] . ' ' . $data['LAST_NAME']);
                            if($temp_name == ''){
                                $temp_name = $data['LOGIN'];
                            }
                            $email_list[] = array('EMAIL' => $data['EMAIL'], 'NAME' => $temp_name);
                        }

                        if(
                            !empty($user_email)
                            && !checkEmailFromPhone($user_email)
                        ){
                            $user_data .= 'Email: ' . $user_email . '<br/>';
                        }
                        $sPhone = getUserPhone($prof_id, $data['ID']);
                        if(!empty($sPhone)){
                            $user_data .= 'Телефон: ' . $sPhone . '<br/>';
                        }

                        foreach($email_list as $cur_data){
                            $arSendFields['RECIPIENT_DATA'] = $cur_data['NAME'];
                            $arSendFields['EMAIL_LIST'] = $cur_data['EMAIL'];
                            $arSendFields['USER_DATA'] = $user_data;
                            CEvent::Send("NEW_USER_ADD", "s1", $arSendFields);
                        }

                        LocalRedirect('/');
                        exit;
                    }
                    else {
                        $arResult['FINALIZE_REGISTER'] = 'Y';
                    }
                }
                else
                {
                    $arResult['ERROR'] = 'Y';
                    $arResult['ERROR_MESSAGE'] = 'Код подтверждения неверен, либо устарел.';
                }
            }
            $this->IncludeComponentTemplate('finalize_register');
            return true;
        }
    }

	if($arResult['ERROR_MESSAGE'] <> '')
		$arResult['ERROR'] = true;

    //get users properties
    //1. Get iblocks ids
    //2. Get iblocks values (check if user comes from email invite)
    $iblocks_arr = array();
    $arResult['PROPERTIES_IBLOCK_ADDITIONAL'] = array();
    $arResult['PROPERTIES_LISTS_ADDITIONAL'] = array();
    //1.
    $res = $ib_obj->GetList(array('ID' => 'ASC'), array('CODE' => array('client_profile', 'farmer_profile')));
    while ($data = $res->Fetch()) {
        $iblocks_arr[$data['CODE']] = $data['ID'];
    }
    //2.
    $codes_arr = array(
        'INN'=> false,
        'NDS'=> false,
        'PHONE' => false,
        'EMAIL' => false
    ); //form props & autocomplete flag
    //получаем данные для выбора региона
    $res = $el_obj->GetList(
        array('ID' => 'ASC'),
        array(
            'IBLOCK_ID' => rrsIblock::getIBlockId('regions'),
            'ACTIVE' => 'Y'
        ),
        false,
        false,
        array('ID', 'NAME', 'IBLOCK_ID')
    );
    while ($data = $res->Fetch()) {
        $arResult['REGIONS'][$data['ID']] = $data['NAME'];
    }

    //check if user comes from email invite
    if ((!isset($arResult['SUCCESS_MESSAGE']) || $arResult['SUCCESS_MESSAGE'] == '') && isset($_GET['reg_hash'])) {
        if (strlen($_GET['reg_hash']) == 10) {
            $ib_id = substr($_GET['reg_hash'], 8, 2);
            $hash_val = substr($_GET['reg_hash'], 0, 8);
            $err_val = '';
            $check_arr = array(
                rrsIblock::getIBlockId('client_profile'),
                rrsIblock::getIBlockId('farmer_profile'),
                rrsIblock::getIBlockId('transport_profile'),
//                rrsIblock::getIBlockId('agent_profile'),
//                rrsIblock::getIBlockId('client_agent_profile')
            );
            if (in_array($ib_id, $check_arr)) {
                //href is ok (client_profile, farmer_profile or transport_profile) -> get user data

                if ($ib_id == rrsIblock::getIBlockId('client_profile')) {
                    $arResult['SHOW_TYPE'] = 'client';
                    //$arResult['INVITE_RESTRICTED_FORM'] = 'client';
                }
                elseif ($ib_id == rrsIblock::getIBlockId('farmer_profile')) {
                    $arResult['SHOW_TYPE'] = 'farmer';
                    //$arResult['INVITE_RESTRICTED_FORM'] = 'farmer';
                }
                /*elseif ($ib_id == rrsIblock::getIBlockId('agent_profile')) {
                    $arResult['SHOW_TYPE'] = 'agent';
                    //$arResult['INVITE_RESTRICTED_FORM'] = 'transport';
                }
                elseif ($ib_id == rrsIblock::getIBlockId('client_agent_profile')) {
                    $arResult['SHOW_TYPE'] = 'client_agent';
                    //$arResult['INVITE_RESTRICTED_FORM'] = 'transport';
                }*/
                $res = $user_obj->GetList(($by="id"), ($order="asc"), array('ACTIVE' => 'N', 'UF_HASH_INVITE' => $hash_val), array('FIELDS' => array('ID', 'EMAIL')));
                if ($data = $res->Fetch()) {
                    $arResult['USER_EMAIL'] = $data['EMAIL'];
                    $arResult['INVITE_USER_MASTER'] = $data['ID'];
                    $res = $el_obj->GetList(array('ID' => 'DESC'), array('IBLOCK_ID' => $ib_id, 'PROPERTY_USER' => $data['ID']), false, array('nTopCount' => 1), array('PROPERTY_*'));
                    if ($data2 = $res->GetNextElement()) {
                        $p = $data2->GetProperties();
                        foreach ($p as $cur_code => $cur_data) {
                            if (isset($cur_data['VALUE']) && !is_array($cur_data['VALUE']) && trim($cur_data['VALUE'] != '')) {
                                if (!isset($_REQUEST['PROP__' . $cur_code])) {
                                    $_REQUEST['PROP__' . $cur_code] = $cur_data['VALUE'];
                                }
                            }
                        }
                    }
                    else {
                        //не найден профиль пользователя
                        $err_val = 'Ошибка в ссылке. Проверьте её правильность или обратитесь к администратору.';
                    }
                }
                else {
                    //не найден хэш код приглашения
                    $err_val = 'Ошибка в ссылке. Проверьте её правильность или обратитесь к администратору.';
                }
            }
            else {
                //не найден код инфоблока зашифрованный в ссылке
                $err_val = 'Ошибка в ссылке. Проверьте её правильность или обратитесь к администратору.';
            }
        }
        else {
            //неверная длина ссылки
            $err_val = 'Ошибка в ссылке. Проверьте её правильность или обратитесь к администратору.';
        }
    }

    //check if user comes from mobile
    if ((!isset($arResult['SUCCESS_MESSAGE']) || $arResult['SUCCESS_MESSAGE'] == '') && isset($_GET['hash']) && $_GET['reg'] == 'mobile') {
        $hash_val = trim($_GET['hash']);
        $res = $user_obj->GetList(($by="id"), ($order="asc"), array('ACTIVE' => 'N', 'UF_HASH' => $hash_val), array('FIELDS' => array('ID', 'EMAIL', 'NAME')));
        if ($data = $res->Fetch()) {
            $arGroups = CUser::GetUserGroup($data['ID']);
            if (in_array(10, $arGroups)) {
                $arResult['SHOW_TYPE'] = 'partner';
                //$arResult['INVITE_RESTRICTED_FORM'] = 'partner';
            }
            elseif (in_array(11, $arGroups)) {
                $arResult['SHOW_TYPE'] = 'farmer';
                //$arResult['INVITE_RESTRICTED_FORM'] = 'farmer';
            }
            elseif (in_array(9, $arGroups)) {
                $arResult['SHOW_TYPE'] = 'client';
                //$arResult['INVITE_RESTRICTED_FORM'] = 'client';
            }
            elseif (in_array(12, $arGroups)) {
                $arResult['SHOW_TYPE'] = 'transport';
                //$arResult['INVITE_RESTRICTED_FORM'] = 'transport';
            }

            $arResult['USER_NAME'] = $data['NAME'];
            $arResult['USER_EMAIL'] = $data['EMAIL'];
        }
        else {
            $err_val = 'Ошибка в ссылке. Проверьте её правильность или обратитесь к администратору.';
        }
    }
    $arResult['INVITE_ERROR'] = $err_val;

    foreach ($iblocks_arr as $cur_code => $cur_id) {
        $res = CIBlockProperty::GetList(array('SORT' => 'ASC', 'ID' => 'ASC'), array('ACTIVE' => 'Y', 'IBLOCK_ID' => $cur_id, '!CODE' => false));
        while ($data = $res->Fetch()) {
            //skip other properties
            if (!isset($codes_arr[$data['CODE']]))
                continue;

            $arResult['PROPERTIES_IBLOCK_ADDITIONAL'][$cur_code][$data['CODE']] = array(
                'NAME' => $data['NAME'],
                'PROPERTY_TYPE' => $data['PROPERTY_TYPE'],
                'VALUE' => (isset($_REQUEST['PROP__' . $data['CODE']]) ? $_REQUEST['PROP__' . $data['CODE']] : ''),
                'AUTO_COMPLETE' => $codes_arr[$data['CODE']]
            );

            //get list values
            if ($data['PROPERTY_TYPE'] == 'E' && isset($data['LINK_IBLOCK_ID']) && is_numeric($data['LINK_IBLOCK_ID'])) {
                $res2 = $el_obj->GetList(array('SORT' => 'ASC', 'ID' => 'ASC'), array('IBLOCK_ID' => $data['LINK_IBLOCK_ID'], 'ACTIVE' => 'Y'), false, false, array('NAME', 'ID'));
                while ($data2 = $res2->Fetch()) {
                    $arResult['PROPERTIES_LISTS_ADDITIONAL'][$data['CODE']][$data2['ID']] = $data2['NAME'];
                }
            }
        }
    }

    $arResult['SIGNERS']= getSignerList();

    $this->IncludeComponentTemplate();
}
else {
    LocalRedirect('/personal/');
}


