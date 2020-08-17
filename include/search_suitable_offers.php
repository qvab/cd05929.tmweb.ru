<?php

if(isset($argv) && is_array($argv))
{
    parse_str(implode('&', array_slice($argv, 1)), $_GET);
    $_SERVER['DOCUMENT_ROOT'] = __DIR__ . '/..';
}
if(($_GET['new_request']>0)&&($_GET['key'] == md5(trim($_GET['new_request'])))){

    require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

    CModule::IncludeModule('iblock');

    //поиск подходящих для запроса товаров поставщиков
    $arSuitableOffers = deal::searchSuitableOffers($_GET['new_request']);

    CIBlockElement::SetPropertyValuesEx(
        $_GET['new_request'],
        rrsIblock::getIBlockId('client_request'),
        array(
            'F_NUM'                 => $arSuitableOffers['FARMER_CNT'],
            'FARMER_BEST_PRICE_CNT' => $arSuitableOffers['FARMER_BEST_PRICE_CNT'],
            'F_CALC'                => '1',
        )
    );
    global $CACHE_MANAGER;
    $CACHE_MANAGER->ClearByTag("iblock_id_".rrsIblock::getIBlockId('client_request'));
}

exit;
