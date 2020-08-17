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

$arParamsToRequest = array(
	"USER_EMAIL",
	"USER_NAME",
);

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

        foreach ($_POST as $key => $val) {
            $arResult[$key] = $val;
        }
        foreach ($arParamsToRequest as $val) {
            if (trim($_POST[$val]) == '') {
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

        if ($arResult['ERROR_MESSAGE'] <> '')
            $arResult['ERROR'] = true;

        //нет ошибок
        if (!$arResult['ERROR']) {
            //ищем пользователя по email
            $arFilter = array("EMAIL" => trim($_POST['USER_EMAIL']));
            $rsUsers = $user_obj->GetList(
                ($by="id"), ($order="desc"),
                $arFilter,
                array(
                    'FIELDS' => array('ID', 'EMAIL', 'ACTIVE'),
                    'SELECT' => array('UF_HASH_INVITE', 'UF_HASH')
                )
            );

            while ($us = $rsUsers->GetNext()) {
                if ($us['EMAIL'] == trim($_POST['USER_EMAIL'])) {
                    $userInfo = $us;
                }
            }

            if (intval($userInfo['ID']) > 0) {
                if ($userInfo['ACTIVE'] == 'Y') {
                    $arResult['ERROR_MESSAGE'] = 'Данный электронный адрес уже зарегистрирован. Измените электронный адрес или авторизуйтесь';
                }
                else {
                    if (!isset($_GET['reg_hash'])
                        && (isset($_GET['reg_hash']) && strlen($_GET['reg_hash']) != 10)
                        && ($_GET['reg'] == 'mobile' && !isset($_GET['hash']))
                    ) {
                        $arResult['ERROR_MESSAGE'] = 'Данный электронный адрес уже зарегистрирован. Измените электронный адрес или авторизуйтесь';
                    }
                }
            }

            /*if (intval($userInfo['ID']) > 0 && (!isset($_GET['reg_hash']) || strlen($_GET['reg_hash']) != 10)) {
                //такой email уже есть

                //additionally check if invite was sent to user and usertype is 'pokupatel' (if so, then let user register & update invite temporary profile)
                $invited_user = false;
                if ($userInfo['ACTIVE'] == 'N') {
                    $res = $el_obj->GetList(array('ID' => 'ASC'), array('IBLOCK_CODE' => 'client_profile', 'PROPERTY_USER' => $userInfo['ID']), false, array('nTopCount' => 1), array('ID', 'IBLOCK_ID'));
                    if($data = $res->Fetch())
                    {
                        $invited_user = true;
                        //emulate get parameter
                        $_GET['reg_hash'] = $userInfo['UF_HASH_INVITE'] . $data['IBLOCK_ID'];
                    }
                }

                if(!$invited_user)
                    $arResult['ERROR_MESSAGE'] = 'Данный электронный адрес уже зарегистрирован. Измените электронный адрес или авторизуйтесь';
            }*/

            if (!isset($arResult['ERROR_MESSAGE']) || $arResult['ERROR_MESSAGE'] == '') {
                //email отсутствует, можно создать нового пользователя
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

                        case 'partner':
                            $arGroups[] = 10;
                            $ib_code = 'partner_profile';
                            $uCode = 'p';
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

                        case 'transport':
                            $arGroups[] = 12;
                            $ib_code = 'transport_profile';
                            $uCode = 't';
                            //check if user comes with email invite href
                            if ($arUser['ID'] > 0) {
                                $res = $el_obj->GetList(
                                    array('ID' => 'DESC'),
                                    array(
                                        'IBLOCK_ID' => rrsIblock::getIBlockId('transport_profile'),
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

                        case 'agent':
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
                            break;
                    }
                }
                else {
                    $arResult['ERROR_MESSAGE'] = 'Не указана роль пользователя';
                }

                $password = hashPass(4).'-'.hashPass(12);
                if ($no_need_confirmation) {
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
                }
                else {
                    //usual register
                    //check recaptcha
                    $recaptcha_response = '';
                    $recaptcha_key = '6LeDzmAUAAAAACcr90tdCuygA__v1xHsEIs37DFe';
                    $captcha_error = true;
                    if (isset($_POST['g-recaptcha-response'])) {
                        if (!empty($_POST['g-recaptcha-response'])) {
                            $recaptcha_response = $_POST['g-recaptcha-response'];
                            $data = array(
                                'secret' => $recaptcha_key,
                                'response' => $recaptcha_response,
                                'remoteip' => $_SERVER['REMOTE_ADDR']
                            );
                            $verify = curl_init();
                            curl_setopt($verify, CURLOPT_URL, "https://www.google.com/recaptcha/api/siteverify");
                            curl_setopt($verify, CURLOPT_POST, true);
                            curl_setopt($verify, CURLOPT_POSTFIELDS, http_build_query($data));
                            curl_setopt($verify, CURLOPT_SSL_VERIFYPEER, false);
                            curl_setopt($verify, CURLOPT_RETURNTRANSFER, true);
                            $response = curl_exec($verify);
                            $response_arr = json_decode($response, true);
                            if (!isset($response_arr['success']) || $response_arr['success'] != true) {
                                $captcha_error = true;
                            } else {
                                $captcha_error = false;
                            }
                        }
                    }
                    if ($captcha_error) {
                        $arResult['ERROR_MESSAGE'] = 'Ошибка google-каптчи. Обновите страницу и попробуйте заполнить форму заново. В случае повторения ошибки напишите администрации через форму обраной связи.';
                    }

                    //проверка подтверждения телефона по смс
                    if(isset($_POST['PROP__PHONE'])
                        && !isset($_SESSION['success_sms_' . str_replace(array('+', '-', '(',')', ' '), '', $_POST['PROP__PHONE'])])
                    ){
                        $arResult['ERROR_MESSAGE'] = 'Ошибка подтверждения телефона по смс. Обновите страницу и попробуйте заполнить форму заново. В случае повторения ошибки напишите администрации через форму обраной связи.';
                    }
                }

                if (!isset($arResult['ERROR_MESSAGE']) || $arResult['ERROR_MESSAGE'] == '') {
                  $json = json_encode($_POST, JSON_UNESCAPED_UNICODE);
                  $f = fopen($_SERVER["DOCUMENT_ROOT"]."/_____response2.json", "w");
                  $response = fwrite($f, $json);
                  fclose($f);
                    $arFields = array(
                        "NAME"                  => trim($_POST['USER_NAME']),
                        "LAST_NAME"             => trim($_POST['USER_LAST_NAME']),
                        "EMAIL"                 => trim($_POST['USER_EMAIL']),
                        "LOGIN"                 => trim($_POST['USER_EMAIL']),
                        "ACTIVE"                => "N",
                        "GROUP_ID"              => $arGroups,
                        "PASSWORD"              => $password,
                        "CONFIRM_PASSWORD"      => $password,
                        "UF_PRIV_POLICY_CONF"   => $_POST['AUTH_REG_CONFIM'],
                        "UF_REGLAMENT_CONF"     => $_POST['AUTH_REGLAMENT_CONFIM'],
                        "UF_FIRST_PHONE"        => 0,
                        "UF_FIRST_LOGIN"        => 1
                    );

                    if ($no_need_confirmation) {
                        $arFields['ACTIVE'] = 'Y';
                        $arFields['UF_HASH'] = '';
                        $arFields['UF_HASH_INVITE'] = '';
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

                            if(isset($_SESSION['success_sms_' . str_replace(array('+', '-', '(',')', ' '), '', $_POST['PROP__PHONE'])])){
                                //удаляем переменную, если подтверждение телефона по смс уже не нужно
                                unset($_SESSION['success_sms_' . str_replace(array('+', '-', '(',')', ' '), '', $_POST['PROP__PHONE'])]);
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

                            $arProps['UL_TYPE'] = rrsIblock::getPropListKey($ib_code, 'UL_TYPE', $_POST['PROP__UL_TYPE']);

                            if ($_POST['PROP__UL_TYPE'] == 'ul') {
                                unset($arProps['IP_FIO']);
                            }
                            elseif ($_POST['PROP__UL_TYPE'] == 'ip') {
                                unset($arProps['FULL_COMPANY_NAME'], $arProps['YUR_ADRESS'], $arProps['KPP'], $arProps['FIO_DIR']);
                            }

                            if ($_POST['TYPE'] == 'partner' || $_POST['TYPE'] == 'transport') {
                                $arProps['SIGNER'] = $_POST['signer'][$_POST['PROP__UL_TYPE']];
                                $signCode = rrsIblock::getElementCodeById(rrsIblock::getIBlockId('signers'), $_POST['signer'][$_POST['PROP__UL_TYPE']]);

                                $arProps['POST'] = $_POST['post'][$_POST['PROP__UL_TYPE']][$signCode];

                                if ($signCode == 'sign') {
                                    $arProps['FIO_SIGN'] = $_POST['fio'][$_POST['PROP__UL_TYPE']][$signCode];
                                }
                                elseif ($_POST['PROP__UL_TYPE'] == 'ul') {
                                    $arProps['FIO_SIGN'] = $_POST['PROP__FIO_DIR'];
                                }
                                elseif ($_POST['PROP__UL_TYPE'] == 'ip') {
                                    $arProps['FIO_SIGN'] = $_POST['PROP__IP_FIO'];
                                }

                                $arProps['FOUND'] = $_POST['found'][$_POST['PROP__UL_TYPE']][$signCode];
                                $arProps['FOUND_NUM'] = $_POST['num'][$_POST['PROP__UL_TYPE']][$signCode];
                                $arProps['FOUND_DATE'] = $_POST['date'][$_POST['PROP__UL_TYPE']][$signCode];

                                $foundCode = rrsIblock::getElementCodeById(rrsIblock::getIBlockId('foundations'), $_POST['found'][$_POST['PROP__UL_TYPE']][$signCode]);

                                $arProps['FOUNDATION'] = $arResult['FOUND'][$foundCode]['PROPERTY_CHEGO_VALUE'];
                                if ($_POST['num'][$_POST['PROP__UL_TYPE']][$signCode] != '') {
                                    $arProps['FOUNDATION'] .= ' № ' . $_POST['num'][$_POST['PROP__UL_TYPE']][$signCode];
                                }
                                if ($_POST['date'][$_POST['PROP__UL_TYPE']][$signCode] != '') {
                                    $arProps['FOUNDATION'] .= ' от ' . $_POST['date'][$_POST['PROP__UL_TYPE']][$signCode] . ' г.';
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
                                        elseif ($ib_code == 'transport_profile') {
                                            $arProps['PARTNER_ID_TIMESTAMP'] = time();
                                            $arProps['PARTNER_ID'] = '';
                                            $el_obj->Add(
                                                array(
                                                    'IBLOCK_ID' => rrsIblock::getIBlockId('transport_partner_link'),
                                                    'ACTIVE' => 'Y',
                                                    'NAME' => 'Привязка транспортной компании [' . $ID . '] к организатору [' . $arProfile['PROPERTY_PARTNER_ID_VALUE'] . ']',
                                                    'PROPERTY_VALUES' => array(
                                                        'USER_ID' => $ID,
                                                        'PARTNER_ID' => $arProfile['PROPERTY_PARTNER_ID_VALUE'],
                                                        'PARTNER_LINK_DATE' => date('d.m.Y H:i:s')
                                                    )
                                                )
                                            );
                                        }
                                        elseif ($ib_code == 'agent_profile') {
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
                                        }
                                        elseif ($ib_code == 'farmer_profile') {
                                            $arProps['PARTNER_ID_TIMESTAMP'] = 0;
                                        }
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
                            }
                        }

                        if ($no_need_confirmation == false) {

                            if ($uCode == 'p') {
                                $arEventFields = array(
                                    "EMAIL" => trim($_POST['USER_EMAIL']),
                                );
                                $res_val = CEvent::Send("REG_HASH_PASSWORD_PARTNER", "s1", $arEventFields);

                                $ib = rrsIblock::getIBlockId('partner_profile');
                                $arEventFields = array(
                                    "PARTNER_PROFILE" => $GLOBALS['host'].'/bitrix/admin/iblock_element_edit.php?IBLOCK_ID='.$ib.'&type=partner&ID='.$id_val.'&lang=ru&WF=Y&find_section_section=0',
                                    "USER_PROFILE" => $GLOBALS['host'].'/bitrix/admin/user_edit.php?lang=ru&ID='.$ID
                                );
                                //$res_val = CEvent::Send("NEW_PARTNER_REGISTRATION", "s1", $arEventFields);

                                //уведомление администраторов о регистрации организатора
                                $user_data = 'Пользователь роли организатор зарегистрирован ' . date('Y.m.d H:i') . '<br/><br/>'; //данные для рассылки
                                $arSendFields = array();
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
                                foreach($email_list as $cur_data){
                                    $arSendFields['RECIPIENT_DATA'] = $cur_data['NAME'];
                                    $arSendFields['EMAIL_LIST'] = $cur_data['EMAIL'];
                                    $arSendFields['USER_DATA'] = $user_data . 'Email: ' . $_POST['USER_EMAIL'];
                                    CEvent::Send("NEW_USER_ADD", "s1", $arSendFields);
                                }
                            }
                            else {
                                $res_val = CEvent::Send("REG_HASH_PASSWORD", "s1", $arEventFields);
                            }
                        }

                        if ($no_need_confirmation) {

                            //уведомление администраторов и того, кто пригласил
                            $user_data = 'Пользователь роли '; //данные для рассылки
                            $arSendFields = array();

                            $invite_user_info = array();
                            switch($uCode){
                                case 'c':
                                    $user_data .= 'покупатель';
                                    //получение данных того, кто пригласил для уведомления
                                    $invite_user_info = client::getPartnerEmailData($ID, true);
                                    break;

                                case 'f':
                                    $user_data .= 'поставщик';
                                    //получение данных того, кто пригласил для уведомления
                                    $invite_user_info = farmer::getPartnerEmailData($ID);
                                    break;

                                case 'ag':
                                    $user_data .= 'агент поставщика';
                                    //получение данных того, кто пригласил для уведомления
                                    $invite_user_info = agent::getPartnerEmailDataF($ID, true);
                                    break;

                                case 'agc':
                                    //получение данных того, кто пригласил для уведомления
                                    $invite_user_info = agent::getPartnerEmailDataCL($ID, true);
                                    $user_data .= 'агент покупателя';
                                    break;

                                case 't':
                                    $user_data .= 'транспортная компания';
                                    //получение данных того, кто пригласил для уведомления
                                    $invite_user_info = transport::getPartnerEmailData($ID, true);
                                    break;
                            }
                            $user_data .= ' зарегистрирован ' . date('Y.m.d H:i') . '<br/><br/>';
                            $email_list = array($invite_user_info);
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
                            foreach($email_list as $cur_data){
                                $arSendFields['RECIPIENT_DATA'] = $cur_data['NAME'];
                                $arSendFields['EMAIL_LIST'] = $cur_data['EMAIL'];
                                $arSendFields['USER_DATA'] = $user_data . 'Email: ' . $_POST['USER_EMAIL'];
                                CEvent::Send("NEW_USER_ADD", "s1", $arSendFields);
                            }

                            $user_obj->Authorize($ID);
                            LocalRedirect('/');
                            exit;
                            //$arResult['SUCCESS_MESSAGE'] = 'Регистрация прошла успешно! Вы можете авторизоваться, используя ваши логин и пароль.';
                        }
                        else {
                            if ($uCode == 'p') {
                                $arResult['SUCCESS_MESSAGE'] = 'Регистрация прошла успешно. Дождитесь подтверждения регистрации со стороны оператора АХ';
                            }
                            else {
                                $arResult['SUCCESS_MESSAGE'] = 'Регистрация прошла успешно. Для завершения регистрации перейдите по ссылке в письме, направленном на вашу электронную почту';
                            }
                        }
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
                        if (sizeof(array_intersect($user_groups, array(11, 9))) > 0) {
                            $arFields['UF_DEMO'] = true;
                        }

                        $user_obj->Update($data['ID'], $arFields, false);

                        $user_obj->Authorize($data['ID']);
                        $arResult['SUCCESS_MESSAGE'] = 'Ваша регистрация завершена';

                        //уведомление администраторов и организаторов
                        $user_data = 'Пользователь роли '; //данные для рассылки
                        $arSendFields = array();
                        $user_groups = array_flip($user_groups);
                        if(isset($user_groups[9])){
                            $user_data .= 'покупатель';
                        }elseif(isset($user_groups[11])){
                            $user_data .= 'поставщик';
                        }elseif(isset($user_groups[12])){
                            $user_data .= 'транспортная компания';
                        }elseif(isset($user_groups[13])){
                            $user_data .= 'агент поставщика';
                        }elseif(isset($user_groups[14])){
                            $user_data .= 'агент покупателя';
                        }
                        $user_data .= ' зарегистрирован ' . date('Y.m.d H:i') . '<br/><br/>';
                        $email_list = array();
                        $res = $user_obj->GetList(
                            ($by = 'id'), ($order = 'asc'),
                            array('GROUPS_ID' => array(1, 10)),
                            array('FIELDS' => array('EMAIL', 'NAME', 'LAST_NAME', 'LOGIN'))
                        );
                        while($data = $res->Fetch()){
                            $temp_name = trim($data['NAME'] . ' ' . $data['LAST_NAME']);
                            if($temp_name == ''){
                                $temp_name = $data['LOGIN'];
                            }
                            $email_list[] = array('EMAIL' => $data['EMAIL'], 'NAME' => $temp_name);
                        }
                        foreach($email_list as $cur_data){
                            $arSendFields['RECIPIENT_DATA'] = $cur_data['NAME'];
                            $arSendFields['EMAIL_LIST'] = $cur_data['EMAIL'];
                            $arSendFields['USER_DATA'] = $user_data . 'Email: ' . $user_email;
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
    $res = $ib_obj->GetList(array('ID' => 'ASC'), array('CODE' => array('client_profile', 'partner_profile', 'farmer_profile', 'transport_profile')));
    while ($data = $res->Fetch()) {
        $iblocks_arr[$data['CODE']] = $data['ID'];
    }
    //2.
    $codes_arr = array(
        'NDS'=> false,
        'FULL_COMPANY_NAME' => true,
        'IP_FIO' => true,
        'YUR_ADRESS' => true,
        'POST_ADRESS' => false,
        'PHONE' => false,
        'EMAIL' => false,
        'INN' => false,
        'KPP' => true,
        'OGRN' => true,
        'OKPO' => true,
        'FIO_DIR' => false,
        'OSNOVANIE_PRAVA_PODPISI_FILE' => false,
        'BANK' => false,
        'BIK' => false,
        'RASCH_SCHET' => false,
        'KOR_SCHET' => false,
        'REGION' => false

    ); //form props & autocomplete flag
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
                rrsIblock::getIBlockId('agent_profile'),
                rrsIblock::getIBlockId('client_agent_profile')
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
                elseif ($ib_id == rrsIblock::getIBlockId('transport_profile')) {
                    $arResult['SHOW_TYPE'] = 'transport';
                    //$arResult['INVITE_RESTRICTED_FORM'] = 'transport';
                }
                elseif ($ib_id == rrsIblock::getIBlockId('agent_profile')) {
                    $arResult['SHOW_TYPE'] = 'agent';
                    //$arResult['INVITE_RESTRICTED_FORM'] = 'transport';
                }
                elseif ($ib_id == rrsIblock::getIBlockId('client_agent_profile')) {
                    $arResult['SHOW_TYPE'] = 'client_agent';
                    //$arResult['INVITE_RESTRICTED_FORM'] = 'transport';
                }
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
                        $err_val = 'Ошибка в ссылке. Проверьте её правильность или обратитесь к администратору.';
                    }
                }
                else {
                    $err_val = 'Ошибка в ссылке. Проверьте её правильность или обратитесь к администратору.';
                }
            }
            else {
                $err_val = 'Ошибка в ссылке. Проверьте её правильность или обратитесь к администратору.';
            }
        }
        else {
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


