<?php

/*
 * Происходит расчет данных для копирования запроса через приложение (использование АПИ)
 * далее данные помещаются в таблицу и доступны по хеш-коду
 * */

if(isset($argv) && is_array($argv))
{
    parse_str(implode('&', array_slice($argv, 1)), $_GET);
}

$_SERVER['DOCUMENT_ROOT'] = __DIR__ . '/..';

if(!isset($_GET['hash_val'])
    || trim($_GET['hash_val']) == ''
    || !isset($_GET['userAccID'])
    || !is_numeric($_GET['userAccID'])
    || !isset($_GET['request_id'])
    || !is_numeric($_GET['request_id'])
)
{
    echo 2;
    exit;
}

require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

//проверяем есть ли пустая запись с хэшем
$hrh_obj = new HighloadRequestsHash(9);
if($hrh_obj->GetNoteData($_GET['hash_val']) != -1)
    exit;

//производим попытку пролонгации
$uid = $_GET['userAccID'];
CModule::IncludeModule('iblock');

$el_obj = new CIBlockElement;

$wh_list = array();

$result_data = array(
    'id'        => $_GET['request_id'],
    'message'   => '',
    'success'   => 1
);

//проверка принадлежит ли запрос пользователю и соответвует ли окончание активности запроса условию
//(+-6 часов окончание активности от текущей даты)
$temp_uid = 0;
$res = $el_obj->GetList(
    array('ID' => 'ASC'),
    array(
        'IBLOCK_ID' => rrsIblock::getIBlockId('client_request'),
        'ID'        => $_GET['request_id']
    ),
    false,
    array('nTopCount' => 1),
    array(
        'ID',
        'ACTIVE_TO',
        'PROPERTY_VOLUME',
        'PROPERTY_CLIENT',
        'PROPERTY_ACTIVE',
        'PROPERTY_IS_PROLONGATED',
        'PROPERTY_F_NUM',
        'PROPERTY_FARMER_BEST_PRICE_CNT'
    )
);
if($data = $res->Fetch()){

    if(isset($data['PROPERTY_CLIENT_VALUE'])
        && is_numeric($data['PROPERTY_CLIENT_VALUE'])
    ){
        $temp_uid = $data['PROPERTY_CLIENT_VALUE'];
    }

    if($uid != $temp_uid){
        //покупатель не является владельцем запроса
        exit;
    }

    //проверка данных запроса и выполнения условия продления
    $tmstmp_diff = floor((strtotime($data['ACTIVE_TO']) - time())/3600);
    $check_can_be_prolongated = requestCanBePrologated($tmstmp_diff,
        $data['PROPERTY_ACTIVE_ENUM_ID'] == rrsIblock::getPropListKey('client_request', 'ACTIVE', 'yes'),
        $data['PROPERTY_IS_PROLONGATED_ENUM_ID'] == rrsIblock::getPropListKey('client_request', 'IS_PROLONGATED', 'yes')
    );
    if($check_can_be_prolongated == 'ya'){
        //активный запрос с возможностью продления
        $new_time = strtotime('+90 days');
        $el_obj->Update($_GET['request_id'], array('ACTIVE_TO' => ConvertTimeStamp($new_time, 'FULL')));
        $result_data['message'] = 'Запрос продлён';
        if ($data['PROPERTY_F_NUM_VALUE'] > 0) {
            $result_data['message'] .= '. Ваш запрос ' . flex($data['PROPERTY_F_NUM_VALUE'])
                . ', для ' . $data['PROPERTY_FARMER_BEST_PRICE_CNT_VALUE'] . ' - лучшая цена';
        }
        else {
            $result_data['message'] .= '. На ваш запрос не найден ни один товар';
        }
    }elseif($check_can_be_prolongated == 'yn'){
        //неактивный запрос с возможностью продления

        //проверяем остались ли активные склады от старого запроса
        //берем активные склады покупателя
        $res = $el_obj->GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID'         => rrsIblock::getIBlockId('client_warehouse'),
                'PROPERTY_ACTIVE'   => rrsIblock::getPropListKey('client_warehouse', 'ACTIVE', 'yes'),
                'PROPERTY_CLIENT'   => $uid
            ),
            false,
            false,
            array('ID')
        );
        while($data2 = $res->Fetch()){
            $wh_list[$data2['ID']] = true;
        }

        //получаем данные запроса
        $wh_data = array();
        $res = $el_obj->Getlist(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('client_request_cost'),
                'ACTIVE' => 'Y',
                'PROPERTY_REQUEST' => $_GET['request_id']
            ),
            false,
            false,
            array('PROPERTY_WAREHOUSE', 'PROPERTY_PRICE')
        );
        while($data2 = $res->Fetch()){
            if(isset($wh_list[$data2['PROPERTY_WAREHOUSE_VALUE']])){
                $wh_data[$data2['PROPERTY_WAREHOUSE_VALUE']] = $data2['PROPERTY_PRICE_VALUE'];
            }
        }

        if(count($wh_data) == 0){
            //ошибка - не найден ни один склад
            $result_data['message'] = 'Не удалось продлить запрос. Склады запроса не активны или удалены. Перейти к редактированию складов';
            $result_data['warnings'] = array(
                'text'  => 'Не удалось продлить запрос. Склады запроса не активны или удалены. Перейти к редактированию складов'
            );
            $result_data['url'] = $GLOBALS['host'] . '/client/warehouses/';

            $arClient = CUser::GetByID($_GET['userAccID'])->Fetch();
            if ($arClient['UF_API_KEY'])
                $result_data['url'] .= '?dkey='.$arClient['UF_API_KEY'];

            $result_data['success'] = 0;
        }else{
            $data = array(
                'userAccID'     => $uid,
                'request_id'    => $_GET['request_id'],
                'urgency'       => '',
                'volume'        => $data['PROPERTY_VOLUME_VALUE'],
                'warehouse'     => $wh_data
            );

            client::copyRequestApi($data);

            //отмечаем текущий запрос как продленный
            $el_obj->SetPropertyValuesEx($_GET['request_id'], rrsIblock::getIBlockId('client_request'), array('IS_PROLONGATED' => rrsIblock::getPropListKey('client_request', 'IS_PROLONGATED', 'yes')));
            $result_data['message'] = 'Запрос продлён';
        }
    }

    if($result_data['message'] != ''){
        //кладем данные в таблицу
        $hrh_obj->UpdateNote($_GET['hash_val'], json_encode($result_data));
    }

    //удаляем временный файл (если нужно)
    if(strlen(preg_replace('/[0-9a-fA-F]/', '', $_GET['hash_val'])) == 0
        && file_exists($_SERVER['DOCUMENT_ROOT'] . '/upload/hash_temp/' . $_GET['hash_val'] . '.txt')
    ){
        unlink($_SERVER['DOCUMENT_ROOT'] . '/upload/hash_temp/' . $_GET['hash_val'] . '.txt');
    }
}