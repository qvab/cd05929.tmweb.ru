<?php

/*
 * Происходит расчет данных для копирования запроса через приложение (использование АПИ)
 * далее данные помещаются в таблицу и доступны по хеш-коду
 * */

if(isset($argv) && is_array($argv))
{
    parse_str(implode('&', array_slice($argv, 1)), $_GET);
    $_SERVER['DOCUMENT_ROOT'] = __DIR__ . '/..';
}

if(!isset($_GET['hash_val'])
    || trim($_GET['hash_val']) == ''
    || !isset($_GET['userAccID'])
    || !is_numeric($_GET['userAccID'])
    || !isset($_GET['request_id'])
    || !is_numeric($_GET['request_id'])
)
{
    exit;
}

require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

//проверяем есть ли пустая запись с хэшем
$hrh_obj = new HighloadRequestsHash(9);
if($hrh_obj->GetNoteData($_GET['hash_val']) != -1)
    exit;

$data = array(
    'userAccID' => $_GET['userAccID'],
    'request_id' => $_GET['request_id'],
);

//делаем расчёт
$result = client::getRequestCopyDataApi($data);

//кладем данные в таблицу
$hrh_obj->UpdateNote($_GET['hash_val'], json_encode($result));

//удаляем временный файл
if(strlen(preg_replace('/[0-9a-fA-F]/', '', $_GET['hash_val'])) == 0
    && file_exists($_SERVER['DOCUMENT_ROOT'] . '/upload/hash_temp/' . $_GET['hash_val'] . '.txt')
){
    unlink($_SERVER['DOCUMENT_ROOT'] . '/upload/hash_temp/' . $_GET['hash_val'] . '.txt');
}