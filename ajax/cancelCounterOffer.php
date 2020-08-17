<?php

//отмена предложения
if(
    isset($_POST['offer_id'])
    && filter_var($_POST['offer_id'], FILTER_VALIDATE_INT)
){
    require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

    CModule::IncludeModule('highloadblock');

    $bCounterOffersCanceled = partner::cancelCounterOffers($_POST['offer_id'], (!empty($_POST['partner_id']) ? $_POST['partner_id'] : 0), (!empty($_POST['from_farmer'])));

    //получаем форму для отправки нового встречного предложения
    if($bCounterOffersCanceled){
        CModule::IncludeModule('iblock');
        if(!empty($_POST['from_farmer'])) {
            //если отменяется поставщиком с публичной страницы
            echo farmer::counterOfferFormAfterDeleting($_POST['offer_id'], (!empty($_POST['volume']) ? $_POST['volume'] : 0));
        }else{
            echo partner::counterOfferFormAfterDeleting($_POST['offer_id'], !empty($_POST['partner_id']));
        }
        exit;
    }else{
        //если нет прав на отмену
        echo 2;
        exit;
    }
}

echo 1;
exit;