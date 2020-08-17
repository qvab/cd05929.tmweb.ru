<?php

//изменение подтверждения качества товара

if(isset($_POST['offer_id'])
    && filter_var($_POST['offer_id'], FILTER_VALIDATE_INT)
    && isset($_POST['q_approved'])
    && filter_var($_POST['q_approved'], FILTER_VALIDATE_INT) !== false
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
            'Q_APPROVED' => ($_POST['q_approved'] == 1 ? 1 : 0),
            'Q_APPROVED_DATA' => $new_data,
            'Q_APPROVED_PARTNER_ID' => $USER->GetID()
        );
        $el_obj->SetPropertyValuesEx($_POST['offer_id'], $ib_id, $arProps);
        //обновление подтверждений в предложениях товара
        partner::changeCounterOfferQualityApproved($_POST['offer_id'], $arProps['Q_APPROVED'], $new_data);
        //сбрасываем кеш в иб, после обновления инфоблока
        global $CACHE_MANAGER;
        $CACHE_MANAGER->ClearByTag('iblock_id_' . rrsIblock::getIBlockId('farmer_offer'));
        $CACHE_MANAGER->ClearByTag('iblock_id_' . rrsIblock::getIBlockId('deals_deals'));

        if($arProps['Q_APPROVED']){
            $result .= ';' . $new_data;
        }

        usleep(500000); //задержка на полсекунды, чтобы было визуально комфортно пользователю
    }

    echo $result;
}
