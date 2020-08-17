<?php

//изменение доступности товара
if(isset($_POST['offer_id'])
    && filter_var($_POST['offer_id'], FILTER_VALIDATE_INT)
    && isset($_POST['stat_id'])
    && filter_var($_POST['stat_id'], FILTER_VALIDATE_INT)
){
    $result = '1';

    header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
    header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
    header ("Cache-Control: no-cache, must-revalidate");
    header ("Pragma: no-cache");
    header ("content-type: application/x-javascript; charset=UTF-8");
    require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

    global $USER;
    $user_groups = CUser::GetUserGroup($USER->GetID());
    $ib_id = rrsIblock::getIBlockId('farmer_offer');
    if(in_array(getGroupIdByRole('p'), $user_groups))
    {
        CModule::IncludeModule('iblock');
        $el_obj = new CIBlockElement;

        $new_data = ConvertTimeStamp(false, 'FULL');
        $arProps = array(
            'STATUS_AVAILABLE' => $_POST['stat_id'],
            'STATUS_AVAILABLE_DATE' => $new_data,
            'STATUS_AVAILABLE_USER' => $USER->GetID()
        );
        $el_obj->SetPropertyValuesEx($_POST['offer_id'], $ib_id, $arProps);

        //сбрасываем кеш в иб, после обновления записи инфоблока
        global $CACHE_MANAGER;
        $CACHE_MANAGER->ClearByTag('iblock_id_' . rrsIblock::getIBlockId('farmer_offer'));

        //вовзращаем дату
        echo date('d.m.Y H:i:s');
        exit;
    }

    echo $result;
}
