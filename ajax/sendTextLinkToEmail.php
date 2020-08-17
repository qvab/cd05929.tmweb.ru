<?php
header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header ("Cache-Control: no-cache, must-revalidate");
header ("Pragma: no-cache");
header ("content-type: application/x-javascript; charset=UTF-8");
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

$result = array('result' => 0);

if((isset($_POST['text']))&&(isset($_POST['uid'])&&(isset($_POST['mode'])))){
    switch ($_POST['mode']){
        case 'farmer':
            $user_data = rrsIblock::getUserInfo($_POST['uid']);
            if(!empty($user_data['EMAIL'])){
                $arFields = array(
                    'EMAIL' => $user_data['EMAIL'],
                    'TEXT' => $_POST['text'],
                    'THEME' => 'Ссылка для создания предложения'
                );
                CEvent::Send('SEND_TEXT_LINK', 's1', $arFields);
                $result = array('result' => 1);
            }
            break;
        case 'client':
            $user_data = rrsIblock::getUserInfo($_POST['uid']);
            if(!empty($user_data['EMAIL'])){
                $arFields = array(
                    'EMAIL' => $user_data['EMAIL'],
                    'TEXT' => $_POST['text'],
                    'THEME' => 'Ссылка на предложение покупателю'
                );
                CEvent::Send('SEND_TEXT_LINK', 's1', $arFields);
                $result = array('result' => 1);
            }
        case 'page_client':
            $user_data = rrsIblock::getUserInfo($_POST['uid']);
            if(!empty($user_data['EMAIL'])){
                $arFields = array(
                    'EMAIL' => $user_data['EMAIL'],
                    'TEXT' => $_POST['text'],
                    'THEME' => 'Рассмотрите предложения'
                );
                CEvent::Send('SEND_TEXT_LINK', 's1', $arFields);
                $result = array('result' => 1);
            }
            break;
        case 'client_graph_href':
            $user_data = rrsIblock::getUserInfo($_POST['uid']);
            if(!empty($user_data['EMAIL'])){
                $arFields = array(
                    'EMAIL' => $user_data['EMAIL'],
                    'TEXT' => $_POST['text'],
                );
                CEvent::Send('SEND_GRAPH_DATA_CLIENT_LINK', 's1', $arFields);
                $result = array('result' => 1);
            }
            break;
        case 'notice_farmer':
            $user_data = rrsIblock::getUserInfo($_POST['uid']);
            $text = $_POST['text'];
            if(!empty($user_data['EMAIL'])){
                $arFields = array(
                    'EMAIL' => $user_data['EMAIL'],
                    'TEXT' => $text,
                    'THEME' => 'Найден покупатель на ваше предложение'
                );
                CEvent::Send('SEND_TEXT_LINK', 's1', $arFields);
                $result = array('result' => 1);
            }
            break;
        case 'farmer_offer_graph':
            $user_data = rrsIblock::getUserInfo($_POST['uid']);
            $text = $_POST['text'];
            if(!empty($user_data['EMAIL'])){
                $arFields = array(
                    'EMAIL' => $user_data['EMAIL'],
                    'TEXT' => $text,
                    'THEME' => 'Данные спроса по товару'
                );
                CEvent::Send('SEND_TEXT_LINK', 's1', $arFields);
                $result = array('result' => 1);
            }
            break;
        case 'client_price_clarify':
            $user_data = rrsIblock::getUserInfo($_POST['uid']);
            $text = $_POST['text'];
            if(!empty($user_data['EMAIL'])){
                $arFields = array(
                    'EMAIL' => $user_data['EMAIL'],
                    'TEXT' => $text,
                    'THEME' => 'Уточнение цены покупателя'
                );
                CEvent::Send('SEND_TEXT_LINK', 's1', $arFields);
                $result = array('result' => 1);
            }
            break;
    }
}
echo json_encode($result);

