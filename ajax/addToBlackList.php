<?php
header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header ("Cache-Control: no-cache, must-revalidate");
header ("Pragma: no-cache");
header("content-type: application/x-javascript; charset=UTF-8");
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

//добавление/удаление покупателей и поставщиков из личных черных списков пользователей

global $USER;
if($USER->IsAuthorized()
    && isset($_POST['user_type'])
    && isset($_POST['user_id'])
    && is_numeric($_POST['user_id'])
    && isset($_POST['deal_id'])
    && is_numeric($_POST['deal_id'])
){
    CModule::IncludeModule('iblock');
    $el_obj = new CIBlockElement;
    $ib_id = ($_POST['user_type'] == 'f' ? rrsIblock::getIBlockId('farmer_black_list') : rrsIblock::getIBlockId('client_black_list'));
    $user_id = $USER->GetID();


    //проверка на роль (если организатор)
    $user_groups = CUser::GetUserGroup($USER->GetID());
    if (in_array(getGroupIdByRole('p'), $user_groups)){
        //получение пользователя по паре
        $res = $el_obj->GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('deals_deals'),
                'ID' => $_POST['deal_id']
            ),
            false,
            array('nTopCount' => 1),
            array('ID', 'PROPERTY_CLIENT', 'PROPERTY_FARMER')
        );
        if($data = $res->Fetch()){
            $user_id = ($_POST['user_type'] == 'f' ? $data['PROPERTY_FARMER_VALUE'] : $data['PROPERTY_CLIENT_VALUE']);
        }
    }

    //проверка на дубли
    $res = $el_obj->GetList(
        array('ID' => 'ASC'),
        array(
            'IBLOCK_ID' => $ib_id,
            'ACTIVE' => 'Y',
            'PROPERTY_USER' => $user_id,
            'PROPERTY_OPPONENT' => $_POST['user_id']
        ),
        false,
        array('nTopCount' => 1),
        array('ID')
    );
    if($res->SelectedRowsCount() > 0){
        //активная запись уже имеется (возможно повторная отправка при подвисшей странице)
        echo 2;
    }else{
        //добавление записи в инфоблок
        echo addBlackListElement($user_id, $_POST['user_type'], $_POST['user_id'], $_POST['deal_id'], (isset($_POST['anket']) ? $_POST['anket'] : array()), (isset($_POST['other_text']) ? $_POST['other_text'] : ''));
    }
}

exit;