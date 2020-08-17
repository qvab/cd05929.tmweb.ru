<?php

//сокрытие названия, если находиммся в добавлении записи счетчика принятий
//или на странцие добавления записи ограничения сущности (запроса или товара)
if($APPLICATION->GetCurPage() == '/bitrix/admin/iblock_element_edit.php'
    && isset($_GET['IBLOCK_ID'])
    &&(
        $_GET['IBLOCK_ID'] == rrsIblock::getIBlockId('counter_request_limits_changes')
        || $_GET['IBLOCK_ID'] == rrsIblock::getIBlockId('client_request_limits_changes')
        || $_GET['IBLOCK_ID'] == rrsIblock::getIBlockId('farmer_offer_limits_changes')
    )
){
    CJSCore::Init(array("jquery"));
    ?><script type="text/javascript">
        $('document').ready(function(){
            $('#tr_NAME').css('display', 'none');
        });
    </script><?
}
//дополнение страницы HL инфоблока предложений (COUNTEROFFERS) кнопкой выгрузки (в админке)
elseif (
    $APPLICATION->GetCurPage() == '/bitrix/admin/highloadblock_rows_list.php'
    && !empty($_GET['ENTITY_ID'])
    && $_GET['ENTITY_ID'] == rrsIblock::HLgetIBlockId('COUNTEROFFERS')
){
    admin::showDownloadCounterOffersButton();
}