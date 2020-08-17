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

session_start();


/*
 * - Имя* (только для неавт.);
- Контактный телефон (только для неавт., с проверкой);
- E-mail* (только для неавт., с проверкой);
- Тема;
- Вопрос*;
- Каптча (только для неавт., скрытый вариант);
- Чекбокс "Я принимаю условия Политики конфиденциальности и даю согласие на обработку своих персональных данных"* (только для неавт., по умолчанию неакт.);
 * */

$arResult['SHOW_ERRORS'] = (array_key_exists('SHOW_ERRORS', $arParams) && $arParams['SHOW_ERRORS'] == 'Y'? 'Y' : 'N');
if(array_key_exists('GROUP_ID', $arParams)){
    $arResult['GROUP_ID'] = $arParams['GROUP_ID'];
}else{
    $arResult['GROUP_ID'] = 0;
}
$arResult['ERROR_MESSAGE'] = '';
CModule::IncludeModule('iblock');
$user_obj = new CUser;

    if (!$USER->IsAuthorized()) {
        //неавторизрованный пользователь
        $arParamsToRequest = array(
            "USER_NAME" => 1,
            "USER_PHONE" => 0,
            "USER_EMAIL" => 1,
            "RESP_THEME" => 0,
            "RESP_QUESTION" => 1,
            "RESP_CONFIM" => 1
        );


        $ok = false;

        if((isset($_GET['ok']))&&(isset($_SESSION['token']))){
            if($_GET['ok'] === $_SESSION['token']){
                $ok = true;
                $_SESSION['token'] = '';
            }
        }
        if($ok === true) {
            $arResult['SUCCESS_MESSAGE'] = 'Спасибо за обращение, в ближайшее время с вами свяжется оператор АГРОХЕЛПЕР.';
        }else{
            $recaptcha_response = '';
            $recaptcha_key = '6LeDzmAUAAAAACcr90tdCuygA__v1xHsEIs37DFe';
            $captcha_error = true;

            if ($_POST['RESPONSE_FORM'] == 'Y') {
                if(!isset($_POST['RESP_CONFIM']) || $_POST['RESP_CONFIM'] != 'Y')
                {
                    $arResult['ERROR_MESSAGE'] = 'Для отправки формы необходимо дать согласие на обработку персональных данных.';
                }

                if($arResult['ERROR_MESSAGE'] == '')
                {
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
                        $arResult['ERROR_MESSAGE'] = 'Неверная каптча';
                    }
                    if (!isset($arResult['ERROR_MESSAGE']) || $arResult['ERROR_MESSAGE'] == '') {
                        //создаем основную запись
                        $PROP = array();
                        $PROP['USER_NAME'] = '';
                        $PROP['USER_PHONE'] = '';
                        $PROP['USER_EMAIL'] = '';
                        $PROP['RESP_THEME'] = '';
                        $PROP['RESP_QUESTION'] = '';
                        $PROP['PERSONAL_CONFIRM'] = '';
                        $PROP['USER_ID'] = 0;       // ID пользователя
                        $PROP['USER_GROUP'] = 0;    //группа пользователя

                        //Проверям полученные параметры
                        foreach ($arParamsToRequest as $field => $req) {
                            if ($req) {
                                if (isset($_POST[$field])) {
                                    if (!empty($_POST[$field])) {
                                        $PROP[$field] = $_POST[$field];
                                        $arResult[$field] = $_POST[$field];
                                    } else {
                                        $arResult['ERROR_MESSAGE'] = 'Не заполнено поле: ' . $field;
                                    }
                                }
                            } else {
                                if (isset($_POST[$field])) {
                                    if (!empty($_POST[$field])) {
                                        $PROP[$field] = $_POST[$field];
                                        $arResult[$field] = $_POST[$field];
                                    }
                                }
                            }
                        }
                        $confim = false;
                        if (isset($_POST['RESP_CONFIM'])) {
                            if ($_POST['RESP_CONFIM'] == 'Y') {
                                $confim = true;
                            }
                        }
                        if ($confim !== true) {
                            $arResult['ERROR_MESSAGE'] = 'Необходимо принять Политику конфиденциальности';
                        }

                        if (!isset($arResult['ERROR_MESSAGE']) || $arResult['ERROR_MESSAGE'] == '') {
                            $el = new CIBlockElement;
                            $arFields = Array(
                                "MODIFIED_BY" => $USER->GetID(),    // элемент изменен текущим пользователем
                                "IBLOCK_SECTION_ID" => false,          // элемент лежит в корне раздела
                                "IBLOCK_ID" => 52,
                                "PROPERTY_VALUES" => $PROP,
                                "NAME" => "Форма [" . $PROP['USER_NAME'] . "] " . date("Y-m-d H:i:s"),
                                "ACTIVE" => "Y",            // активен
                            );
                            if ($RESP_ID = $el->Add($arFields)) {
                                //отправляем письмо
                                $arEventFields = array(
                                    'DEFAULT_EMAIL_FROM' => COption::GetOptionString("main", "email_from"), // email админа в настройках главного модуля,
                                    'EMAIL' => 'admin@agrohelper.ru',
                                    //'EMAIL' => $arFields['PROPERTY_VALUES']['USER_EMAIL'],
                                    'THEME' => $arFields['PROPERTY_VALUES']['RESP_THEME'],
                                    'THEME_UP' => $arFields['PROPERTY_VALUES']['RESP_THEME'],
                                    'AUTH_TYPE' => 'незарегистрированного',
                                    'USER_TYPE' => '-',
                                    'USER_NAME' => $arFields['PROPERTY_VALUES']['USER_NAME'],
                                    'USER_PHONE' => $arFields['PROPERTY_VALUES']['USER_PHONE'],
                                    'USER_EMAIL' => $arFields['PROPERTY_VALUES']['USER_EMAIL'],
                                    'QUEST' => $arFields['PROPERTY_VALUES']['RESP_QUESTION'],
                                    'USER_ADMIN_LINK' => '-'
                                );
                                CEvent::Send("RESPONSE_HELP", SITE_ID, $arEventFields);
                                $arResult['USER_NAME'] = '';
                                $arResult['USER_PHONE'] = '';
                                $arResult['USER_EMAIL'] = '';
                                $arResult['RESP_THEME'] = '';
                                $arResult['RESP_QUESTION'] = '';
                                $arResult['SUCCESS_MESSAGE'] = 'Спасибо за обращение, в ближайшее время с вами свяжется оператор АГРОХЕЛПЕР.';
                                $_SESSION['token'] = md5(time());
                                LocalRedirect($APPLICATION->GetCurDir() . '?ok=' . $_SESSION['token']);
                            }
                        }
                    }
                }
                else
                {
                    $arResult['USER_NAME'] = (isset($_POST['USER_NAME']) && $_POST['USER_NAME'] != '' ? $_POST['USER_NAME'] : '');
                    $arResult['USER_PHONE'] = (isset($_POST['USER_PHONE']) && $_POST['USER_PHONE'] != '' ? $_POST['USER_PHONE'] : '');
                    $arResult['USER_EMAIL'] = (isset($_POST['USER_EMAIL']) && $_POST['USER_EMAIL'] != '' ? $_POST['USER_EMAIL'] : '');
                    $arResult['RESP_THEME'] = (isset($_POST['RESP_THEME']) && $_POST['RESP_THEME'] != '' ? $_POST['RESP_THEME'] : '');
                    $arResult['RESP_QUESTION'] = (isset($_POST['RESP_QUESTION']) && $_POST['RESP_QUESTION'] != '' ? $_POST['RESP_QUESTION'] : '');
                }
            }
        }



        //сохранение неавторизованного пользователя

        /**
         *  (13, 19, 32, 33, там идет привязка к пользователю).
         * 13 - Профили покупателей
         * 19 - Профили Фермеров
         * 32 - Профили организаторов
         * 33 - Профили транспортных компаний
         */

        if ($arResult['ERROR_MESSAGE'] <> '')
            $arResult['ERROR'] = true;

        $this->IncludeComponentTemplate();
    } else {
        $arParamsToRequest = array(
            "RESP_THEME" => 0,
            "RESP_QUESTION" => 1
        );
        $userType = '';

        if ($arResult['ERROR_MESSAGE'] <> '')
            $arResult['ERROR'] = true;

        $ok = false;

        if((isset($_GET['ok']))&&(isset($_SESSION['token']))){
            if($_GET['ok'] === $_SESSION['token']){
                $ok = true;
                $_SESSION['token'] = '';
            }
        }
        if($ok === true) {
            $arResult['SUCCESS_MESSAGE'] = 'Спасибо за обращение, в ближайшее время с вами свяжется оператор АГРОХЕЛПЕР.';
        }else{
            if ($_POST['RESPONSE_AUTH_FORM'] == 'Y') {
                //создаем основную запись
                $PROP = array();
                $UID = $user_obj::GetID();
                $arGroups = CUser::GetUserGroup($UID);
                $userRes = CUser::GetByID($UID);
                $userData = $userRes->Fetch();

                //определяем какой пользователь сейчас, с учетом того что одновременно он может быть либо Покупатель[9]|Организатор[10]|Фермер[11]|Транспортные компании[12]

                $groupName = '';
                switch ($arResult['GROUP_ID']) {
                    case 9:
                        $groupName = 'Покупатели';
                        $userType = 'Покупатель';
                        break;
                    case 10:
                        $groupName = 'Организаторы';
                        $userType = 'Организатор';
                        break;
                    case 11:
                        $groupName = 'Поставщики';
                        $userType = 'Поставщик';
                        break;
                    case 12:
                        $groupName = 'Транспортные компании';
                        $userType = 'Транспортная компания';
                        break;
                    case 13:
                        $groupName = 'Агенты поставщиков';
                        $userType = 'Агент поставщика';
                        break;
                    case 14:
                        $groupName = 'Агенты покупателей';
                        $userType = 'Агент покупателей';
                        break;
                }
                if ((sizeof($arGroups)) && (is_array($arGroups))) {
                    $profileData = array();
                    if ((in_array(9, $arGroups)) && ($arResult['GROUP_ID'] == 9)) {
                        //если это Покупатель
                        $PROP['USER_ID'] = $UID;  // ID пользователя
                        $PROP['USER_GROUP'] = $arResult['GROUP_ID']; //группа пользователя
                        $profileData = client::getFullProfile($UID);
                    } elseif ((in_array(10, $arGroups)) && ($arResult['GROUP_ID'] == 10)) {
                        //если это Организатор
                        $PROP['USER_ID'] = $UID;  // ID пользователя
                        $PROP['USER_GROUP'] = $arResult['GROUP_ID']; //группа пользователя
                        $profileData = partner::getFullProfile($UID);
                    } elseif ((in_array(11, $arGroups)) && ($arResult['GROUP_ID'] == 11)) {
                        //если это Фермер
                        $PROP['USER_ID'] = $UID;  // ID пользователя
                        $PROP['USER_GROUP'] = $arResult['GROUP_ID']; //группа пользователя
                        $profileData = farmer::getFullProfile($UID);
                    } elseif ((in_array(12, $arGroups)) && ($arResult['GROUP_ID'] == 12)) {
                        //если это Транспортные компании
                        $PROP['USER_ID'] = $UID;  // ID пользователя
                        $PROP['USER_GROUP'] = $arResult['GROUP_ID']; //группа пользователя
                        $profileData = transport::getFullProfile($UID);
                    } elseif ((in_array(13, $arGroups)) && ($arResult['GROUP_ID'] == 13)) {
                        //если это Агенты
                        $PROP['USER_ID'] = $UID;  // ID пользователя
                        $PROP['USER_GROUP'] = $arResult['GROUP_ID']; //группа пользователя
                        $agentOb = new agent();
                        $profileData = $agentOb->getProfile($UID);
                    }elseif ((in_array(14, $arGroups)) && ($arResult['GROUP_ID'] == 14)) {
                        //если это Агенты
                        $PROP['USER_ID'] = $UID;  // ID пользователя
                        $PROP['USER_GROUP'] = $arResult['GROUP_ID']; //группа пользователя
                        $agentOb = new agent();
                        $profileData = $agentOb->getClientAgentProfile($UID);
                    }
                    //получаем вначале данные пользователя
                    if (isset($userData['NAME'])) {
                        $PROP['USER_NAME'] = $userData['NAME'];
                    }
                    if (isset($userData['EMAIL'])) {
                        $PROP['USER_EMAIL'] = $userData['EMAIL'];
                    }
                    if (isset($userData['PERSONAL_PHONE'])) {
                        $PROP['USER_PHONE'] = $userData['PERSONAL_PHONE'];
                    }
                    //проверяем заполнены ли данные у профиля клиенат, если да, то берем их
                    if ((sizeof($profileData)) && (is_array($profileData))) {
                        if (isset($profileData['PROPERTY_PHONE_VALUE'])) {
                            $PROP['USER_PHONE'] = $profileData['PROPERTY_PHONE_VALUE'];
                        }
                    }
                }

                $PROP['RESP_THEME'] = '';
                $PROP['RESP_QUESTION'] = '';
                $PROP['RESP_CONFIM'] = '';

                //Проверям полученные параметры
                foreach ($arParamsToRequest as $field => $req) {
                    if ($req) {
                        if (isset($_POST[$field])) {
                            if (!empty($_POST[$field])) {
                                $PROP[$field] = $_POST[$field];
                                $arResult[$field] = $_POST[$field];
                            } else {
                                $arResult['ERROR_MESSAGE'] = 'Не заполнено поле: ' . $field;
                            }
                        }
                    } else {
                        if (isset($_POST[$field])) {
                            if (!empty($_POST[$field])) {
                                $PROP[$field] = $_POST[$field];
                                $arResult[$field] = $_POST[$field];
                            }
                        }
                    }
                }
                if (empty($PROP['USER_ID'])) {
                    $arResult['ERROR_MESSAGE'] = 'Текущий пользователь не относится к группе ' . $groupName;
                }

                if (!isset($arResult['ERROR_MESSAGE']) || $arResult['ERROR_MESSAGE'] == '') {
                    $el = new CIBlockElement;
                    $arFields = Array(
                        "MODIFIED_BY" => $UID,    // элемент изменен текущим пользователем
                        "IBLOCK_SECTION_ID" => false,          // элемент лежит в корне раздела
                        "IBLOCK_ID" => 52,
                        "PROPERTY_VALUES" => $PROP,
                        "NAME" => "Форма [" . $userType . ":" . $PROP['USER_NAME'] . "][" . $UID . "] " . date("Y-m-d H:i:s"),
                        "ACTIVE" => "Y",            // активен
                    );
                    $admin_link = 'https://' . $_SERVER['SERVER_NAME'] . '/bitrix/admin/user_edit.php?lang=ru&ID=' . $UID;
                    if ($RESP_ID = $el->Add($arFields)) {
                        //отправляем письмо
                        $arEventFields = array(
                            'DEFAULT_EMAIL_FROM' => COption::GetOptionString("main", "email_from"), // email админа в настройках главного модуля,
                            'EMAIL' => 'admin@agrohelper.ru',
                            'THEME' => $arFields['PROPERTY_VALUES']['RESP_THEME'],
                            'THEME_UP' => $arFields['PROPERTY_VALUES']['RESP_THEME'],
                            'AUTH_TYPE' => 'зарегистрированного',
                            'USER_TYPE' => $userType,
                            'USER_NAME' => $arFields['PROPERTY_VALUES']['USER_NAME'],
                            'USER_PHONE' => $arFields['PROPERTY_VALUES']['USER_PHONE'],
                            'USER_EMAIL' => $arFields['PROPERTY_VALUES']['USER_EMAIL'],
                            'QUEST' => $arFields['PROPERTY_VALUES']['RESP_QUESTION'],
                            'USER_ADMIN_LINK' => '<a href="' . $admin_link . '" target="blank_">' . $admin_link . '</a>'
                        );
                        CEvent::Send("RESPONSE_HELP", SITE_ID, $arEventFields);
                        $arResult['USER_NAME'] = '';
                        $arResult['USER_PHONE'] = '';
                        $arResult['USER_EMAIL'] = '';
                        $arResult['RESP_THEME'] = '';
                        $arResult['RESP_QUESTION'] = '';
                        $arResult['SUCCESS_MESSAGE'] = 'Спасибо за обращение, в ближайшее время с вами свяжется оператор АГРОХЕЛПЕР.';
                        $_SESSION['token'] = md5(time());
                        LocalRedirect($APPLICATION->GetCurDir() . '?ok=' . $_SESSION['token']);
                    }
                }
            }
        }


        if ($arResult['ERROR_MESSAGE'] <> '')
            $arResult['ERROR'] = true;

        //сохранение неавторизованного пользователя
        $this->IncludeComponentTemplate();
    }



