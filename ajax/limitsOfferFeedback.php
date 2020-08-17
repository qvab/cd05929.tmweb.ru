<?php
//обратная связь для ограничения количества запросов

header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header ("Cache-Control: no-cache, must-revalidate");
header ("Pragma: no-cache");
header ("content-type: application/x-javascript; charset=UTF-8");
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

//проверяем обязательные данные
if(!isset($_POST['email'])
    || !check_email($_POST['email'])
){
    //ошибка в email
    echo '2';
    exit;
}
if(!isset($_POST['type'])
    || (
        $_POST['type'] != 1
        && $_POST['type'] != 2
    )
){
    //ошибка в типе оплаты
    echo '3';
    exit;
}

//проверка значения заявки
$value_lim = rrsIblock::getConst('min_offer_limit_form');
if(!isset($_POST['value'])
    || !filter_var($_POST['value'], FILTER_VALIDATE_INT)
    || $_POST['value'] < $value_lim
){
    //ошибка в значении
    $_POST['value'] = $value_lim;
}
//проверка месяцев в заявке
$value_lim = rrsIblock::getConst('min_month_offer_limit');
if(!isset($_POST['month_value'])
    || !filter_var($_POST['month_value'], FILTER_VALIDATE_INT)
    || $_POST['month_value'] < $value_lim
){
    //ошибка в значении
    $_POST['month_value'] = $value_lim;
}

global $USER;
$arGroups = array_flip($USER->GetUserGroupArray());

//проверяем состоит ли пользователь в группе покупателей
if(is_array($arGroups)
    && isset($arGroups[11])
){
    $ib_id = rrsIblock::getIBlockId('farmer_offer_limit_feedback');
    if(is_numeric($ib_id)){
        $el_obj = new CIBlockElement;
        $user_type = (isset($_POST['type']) && $_POST['type'] == 1 ? 'Физ. лицо' : 'Юр. лицо / ИП');
        $arFields = array(
            'NAME' => 'Новое обращение от пользователя с id ' . $USER->GetID(),
            'IBLOCK_ID' => $ib_id,
            'ACTIVE' => 'Y',
            'PROPERTY_VALUES' => array(
                'EMAIL' => $_POST['email'],
                'USER' => $USER->GetID(),
                'TYPE' => $user_type,
                'QUANTITY' => $_POST['value'],
                'MONTH' => $_POST['month_value'],
                'COMMENT_TEXT' => $_POST['comment_text'],
            )
        );

        //получаем ИНН и название организации
        $company_name = '';
        $inn_val = '';
        $res = $el_obj->GetList(
            array('ID', 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('farmer_profile'),
                'PROPERTY_USER' => $USER->GetID()
            ),
            false,
            array('nTopCount' => 1),
            array('PROPERTY_FULL_COMPANY_NAME', 'PROPERTY_IP_FIO', 'PROPERTY_INN')
        );
        while($data = $res->Fetch()){
            if ($data['PROPERTY_FULL_COMPANY_NAME_VALUE'] != '') {
                $company_name = $data['PROPERTY_FULL_COMPANY_NAME_VALUE'];
            }
            else {
                $company_name = $data['PROPERTY_IP_FIO_VALUE'];
            }
            $inn_val = $data['PROPERTY_INN_VALUE'];
        }
        $arFields['PROPERTY_VALUES']['COMPANY_NAME'] = $company_name;
        $arFields['PROPERTY_VALUES']['INN'] = $inn_val;

        $new_id = $el_obj->Add($arFields);

        if(intval($new_id) > 0){
            echo '1';

            //отправка уведомлений админам
            $arFields = array(
                'URL' => $GLOBALS['host'] . '/bitrix/admin/user_edit.php?lang=ru&ID=' . $USER->GetID(),
                'FEEDBACK_URL' => $GLOBALS['host'] . '/bitrix/admin/iblock_element_edit.php?IBLOCK_ID=' . rrsIblock::getIBlockId('farmer_offer_limit_feedback') . '&type=farmer&ID=' . $new_id . '&lang=ru&find_section_section=0&WF=Y',
                'TEXT' => ''
            );

            //собираем данные пользователя
            $res = CUser::GetList(
                ($by = 'id'), ($order = 'asc'),
                array('ID' => $USER->GetID()),
                array('SELECT' => array('EMAIL', 'NAME', 'LAST_NAME', 'LOGIN'))
            );
            if($data = $res->Fetch()){
                $temp_val = '';
                if($data['EMAIL'] != '' && !checkEmailFromPhone($data['EMAIL'])){
                    $temp_val = trim($data['NAME'] . ' ' . $data['LAST_NAME'] . ' ' . $data['EMAIL']);
                }elseif($data['LOGIN'] != '' ){
                    $temp_val = trim($data['NAME'] . ' ' . $data['LAST_NAME'] . ' ' . $data['LOGIN']);
                }else{
                    $temp_val = trim($data['NAME'] . ' ' . $data['LAST_NAME']);
                }
                if($temp_val != '') {
                    $arFields['USER_INFO'] = '<a href="' . $arFields['URL'] . '">' . $temp_val . '</a>';
                }else{
                    //у пользователя нет email и нет фио -> берём телефон из профиля
                    $arFields['USER_INFO'] = '<a href="' . $arFields['URL'] . '">' . $USER->GetID() . '</a>';
                }
            }
            if($inn_val != ''){
                $inn_val  = '<br/>ИНН: ' . $inn_val;
            }
            if($company_name != ''){
                $company_name = '<br/>Название организации / Имя ИП: ' . $company_name;
            }

            $comment_text = '';
            if(!empty($_POST['comment_text'])){
                $comment_text = '<br/>Комментарий: '.$_POST['comment_text'];
            }

            $arFields['USER_INFO'] = $company_name . $inn_val . '<br/>Тип плательщика: ' . $user_type . '<br/>Количество товаров: ' . $_POST['value'] . '<br/>Количество месяцев: ' . $_POST['month_value'] . '<br/>Стоимость при подаче заявки: ' . ($_POST['value'] * $_POST['month_value'] * rrsIblock::getConst('offer_limit_price')) . ' руб.<br/>Ссылка на пользователя в администраторском разделе сайта: ' . $arFields['USER_INFO'].$comment_text;

            //отправляем данные администраторам
            $res = CUser::GetList(
                ($by = 'id'), ($order = 'asc'),
                array(
                    'GROUPS_ID' => 1,
                    'ACTIVE' => 'Y',
                ),
                array('SELECT' => array('EMAIL'))
            );
            while($data = $res->Fetch()){
                $arFields['EMAIL'] = $data['EMAIL'];
                CEvent::Send('OFFERLIMITSFEEDBACK', 's1', $arFields);
            }
        }
    }
}

exit;