<?php

/*
 * Происходит копирование запроса через приложение (использование АПИ)
 * далее ответ для приложения помещается в таблицу и доступен по хеш-коду
 * */

if(isset($argv) && is_array($argv))
{
    parse_str(implode('&', array_slice($argv, 1)), $_GET);
    $_SERVER['DOCUMENT_ROOT'] = __DIR__ . '/..';
}

require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');
CModule::IncludeModule('iblock');

if(!isset($_GET['hash_val'])
    || trim($_GET['hash_val']) == ''
    || !isset($_GET['userAccID'])
    || !is_numeric($_GET['userAccID'])
//    || !isset($_GET['reqID'])
//    || !filter_var($_GET['reqID'], FILTER_VALIDATE_INT)
    || !isset($_GET['request_id'])
    || !is_numeric($_GET['request_id'])
    || !isset($_GET['event'])
    //|| !isset($_GET['urgency'])
    //|| !is_numeric($_GET['urgency'])
    || !isset($_GET['volume'])
    || !is_numeric($_GET['volume'])
    || !isset($_GET['warehouse'])
    || !client::checkCountedWarehouses($_GET['warehouse'])
    //Убираем для задачи #12302 (Изменение логики добавления/копирования запроса покупателя) //привязка к партнеру
    //|| client::getLinkedPartner($_GET['userAccID']) == 0
)
{
    exit;
}

//проверяем есть ли пустая запись с хэшем
$hrh_obj = new HighloadRequestsHash(9);
if($hrh_obj->GetNoteData($_GET['hash_val']) != -1)
    exit;

$data = array(
    'userAccID'     => $_GET['userAccID'],
    'request_id'    => $_GET['request_id'],
    'urgency'       => '',
    'volume'        => $_GET['volume'],
    'warehouse'     => $_GET['warehouse']
);

//производим копирование
$result = client::copyRequestApi($data);

//кладем данные в таблицу
if(count($result) > 0)
{
    $hrh_obj->UpdateNote($_GET['hash_val'], json_encode($result));
}
//$q = deal::searchSuitableOffers($_GET['reqID']);
//CIBlockElement::SetPropertyValuesEx($_GET['reqID'], rrsIblock::getIBlockId('client_request'), array('F_NUM' => $q));

//удаляем временный файл
if(strlen(preg_replace('/[0-9a-fA-F]/', '', $_GET['hash_val'])) == 0
    && file_exists($_SERVER['DOCUMENT_ROOT'] . '/upload/hash_temp/' . $_GET['hash_val'] . '.txt')
){
    unlink($_SERVER['DOCUMENT_ROOT'] . '/upload/hash_temp/' . $_GET['hash_val'] . '.txt');
}