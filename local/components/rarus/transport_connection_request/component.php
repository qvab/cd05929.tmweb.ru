<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
/** @var CBitrixComponent $this */
/** @var array $arParams */
/** @var array $arResult */
/** @var string $componentPath */
/** @var string $componentName */
/** @var string $componentTemplate */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

/** @global CIntranetToolbar $INTRANET_TOOLBAR */
global $INTRANET_TOOLBAR;

use Bitrix\Main\Context,
    Bitrix\Main\Type\DateTime,
    Bitrix\Main\Loader,
    Bitrix\Iblock;

$arResult = array();
$cache_time = false;

//проверяем отправленыне данные
if(isset($_POST['PUBLIC_FORM'])
    && $_POST['PUBLIC_FORM'] == 'TK_REQUEST'
    && !empty($_POST['fio'])
    && !empty($_POST['region'])
    && !empty($_POST['phone'])
    && !empty($_POST['email'])
){
    //проверка почты
    if(!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)){
        $arResult['ERROR_MESSAGE'] = 'Укажите пожалуйста корректный email.';
    }

    if(empty($arResult['ERROR_MESSAGE'])){
        //проверка рекаптчи
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
            $cache_time = 0;
        }
    }

    if(empty($arResult['ERROR_MESSAGE'])){
        //вносим данные в БД

        CModule::IncludeModule('iblock');
        $el_obj = new CIBlockElement;

        $arFields = array(
            'IBLOCK_ID' => rrsIblock::getIBlockId('transport_connection_request'),
            'ACTIVE' => 'Y',
            'NAME' => 'Новая заявка на подключение в качестве перевозчика от ' . $_POST['email'],
            'PROPERTY_VALUES' => array(
                'FIO' => trim($_POST['fio']),
                'REGION' => $_POST['region'],
                'PHONE' => $_POST['phone'],
                'EMAIL' => $_POST['email'],
                'COMMENT' => (!empty($_POST['comment']) ? trim($_POST['comment']) : ''),
            )
        );

        if($new_id = $el_obj->Add($arFields)){
            $regionName = '';
            $res = $el_obj->GetList(
                array('ID' => 'ASC'),
                array('IBLOCK_ID' => rrsIblock::getIBlockId('regions'), 'ID' => $_POST['region']),
                false,
                array('nTopCount' => 1),
                array('NAME')
            );
            if($data = $res->Fetch()){
                $regionName = $data['NAME'];
            }

            //заполняем доп данные к письмам
            $additional_message = "<div>ФИО: {$arFields['PROPERTY_VALUES']['FIO']}</div>";
            if($regionName != ''){
                $additional_message .= "<div>Регион: {$regionName}</div>";
            }
            $additional_message .= "<div>Email: {$arFields['PROPERTY_VALUES']['EMAIL']}</div>
                     <div>Телефон: {$arFields['PROPERTY_VALUES']['PHONE']}</div>";
            if($arFields['PROPERTY_VALUES']['COMMENT'] != ''){
                $additional_message .= "<div>Комментарий: <br/>{$arFields['PROPERTY_VALUES']['COMMENT']}</div>";
            }

            //рассылка уведомлений администраторам
            $email_arr = array();
            $u_obj = new CUser;
            $res = $u_obj->GetList(
                ($by = 'id'), ($order = 'asc'),
                array('GROUPS_ID' => 1),
                array('SELECT' => array('ID', 'EMAIL'))
            );
            while($data = $res->Fetch()){
                $email_arr[$data['ID']] = $data['EMAIL'];
            }
            if(count($email_arr) > 0) {
                CEvent::Send('TK_CONNECTION_AGROHELPER', 's1', array(
                    'EMAIL_LIST' => $email_arr,
                    'ADDITIONAL_DATA' => $additional_message
                ));
            }

            //рассылка уведомлений организаторам
            $admin_email_arr = array();
            $res = $u_obj->GetList(
                ($by = 'id'), ($order = 'asc'),
                array('GROUPS_ID' => 10),
                array('SELECT' => array('ID', 'EMAIL'))
            );
            while($data = $res->Fetch()){
                if(!isset($email_arr[$data['ID']])) {
                    $admin_email_arr[] = $data['EMAIL'];
                }
            }
            if(count($admin_email_arr) > 0) {
                CEvent::Send('PARTNER_TK_CONNECTION_AGROHELPER', 's1', array(
                    'EMAIL_LIST' => $admin_email_arr,
                    'ADDITIONAL_DATA' => $additional_message
                ));
            }

            //переадресация на страницу с сообщением об успешном добавлении записи
            setcookie('tk_connection_request_success', 'y', time() + 60);
            global $APPLICATION;
            LocalRedirect($APPLICATION->GetCurUri(false, false) . '#action=transport_connection');
            exit;
        }
    }
}

//устанавливаем кеширование
if ($this->StartResultCache($cache_time)) {
    $arResult['REGIONS'] = array();

    CModule::IncludeModule('iblock');
    $el_obj = new CIBlockElement;

    //получаем регионы
    $res = $el_obj->GetList(
        array('SORT' => 'ASC', 'ID' => 'ASC'),
        array(
            'IBLOCK_ID' => rrsIblock::getIBlockId('regions'),
            'ACTIVE' => 'Y'
        ),
        false,
        false,
        array('ID', 'NAME')
    );
    while($data = $res->Fetch()){
        $arResult['REGIONS'][$data['ID']] = $data['NAME'];
    }

    $this->SetResultCacheKeys(array("REGIONS"));

    $this->IncludeComponentTemplate();
}

unset($res, $data, $el_obj, $u_obj, $get_lists_ib_ids, $arSelect, $arUpdateFields);