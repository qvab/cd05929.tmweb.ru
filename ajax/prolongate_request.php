<?php
//продлевает действие запроса покупателя и агента покупателя

if(isset($_POST['r_id'])
    && filter_var($_POST['r_id'], FILTER_VALIDATE_INT)
){
    require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

    global $USER;
    $uid = $USER->GetID();

    $user_groups = CUser::GetUserGroup($USER->GetID());
    if (in_array(9, $user_groups))
        $GLOBALS['rrs_user_perm_level'] = 'c';
    elseif (in_array(14, $user_groups))
        $GLOBALS['rrs_user_perm_level'] = 'agc'; //агент покупателя

    if(isset($GLOBALS['rrs_user_perm_level'])
        && ($GLOBALS['rrs_user_perm_level'] == 'c'
            ||
            $GLOBALS['rrs_user_perm_level'] == 'agc'
        )
    ){
        CModule::IncludeModule('iblock');

        $el_obj = new CIBlockElement;

        $wh_list = array();

        //проверка принадлежит ли запрос пользователю и соответвует ли окончание активности запроса условию
        //(+-6 часов окончание активности от текущей даты)
        $temp_uid = 0;
        $res = $el_obj->GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('client_request'),
                'ID'        => $_POST['r_id']
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

            if($GLOBALS['rrs_user_perm_level'] == 'agc'){
                //проверка привязки покупателя и агента покупателя
                $agentObj = new agent();
                if($agentObj->checkLinkWithClient($temp_uid, $uid)){
                    $uid = $temp_uid;
                }else{
                    //агент не привязан к владельцу запроса
                    exit;
                }
            }elseif($uid != $temp_uid){
                //покупатель не является владельцем запроса
                exit;
            }

            //проверка данных запроса и выполнения условия продления
            $tmstmp_diff = floor((strtotime($data['ACTIVE_TO']) - time())/3600);
            $check_can_be_prolongated = requestCanBePrologated(
                $tmstmp_diff,
                $data['PROPERTY_ACTIVE_ENUM_ID'] == rrsIblock::getPropListKey('client_request', 'ACTIVE', 'yes'),
                $data['PROPERTY_IS_PROLONGATED_ENUM_ID'] == rrsIblock::getPropListKey('client_request', 'IS_PROLONGATED', 'yes')
            );
            if($check_can_be_prolongated == 'ya'){
                $new_time = strtotime('+90 days');
                //активный запрос с возможностью продления
                $el_obj->Update($_POST['r_id'], array('ACTIVE_TO' => ConvertTimeStamp($new_time, 'FULL')));
                $json_result = array('result'=>$new_time, 'num'=>$data['PROPERTY_F_NUM_VALUE'], 'best'=>$data['PROPERTY_FARMER_BEST_PRICE_CNT_VALUE']);
                echo json_encode($json_result); exit;
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
                        'PROPERTY_REQUEST' => $_POST['r_id']
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
                    $json_result = array('result'=>0);
                    echo json_encode($json_result); exit;
                }

                $data = array(
                    'userAccID'     => $uid,
                    'request_id'    => $_POST['r_id'],
                    'urgency'       => '',
                    'volume'        => $data['PROPERTY_VOLUME_VALUE'],
                    'warehouse'     => $wh_data
                );
                $result = client::copyRequestApi($data);

                //отмечаем текущий запрос как продленный
                $el_obj->SetPropertyValuesEx($_POST['r_id'], rrsIblock::getIBlockId('client_request'), array('IS_PROLONGATED' => rrsIblock::getPropListKey('client_request', 'IS_PROLONGATED', 'yes')));

                $res = $el_obj->Getlist(
                    array(),
                    array(
                        'IBLOCK_ID' => rrsIblock::getIBlockId('client_request'),
                        'ID' => $result['id']
                    ),
                    false,
                    false,
                    array('PROPERTY_F_NUM', 'PROPERTY_FARMER_BEST_PRICE_CNT')
                );
                if ($ob = $res->Fetch()) {
                    $json_result = array('result'=>1, 'num'=>$ob['PROPERTY_F_NUM_VALUE'], 'best'=>$ob['PROPERTY_FARMER_BEST_PRICE_CNT_VALUE']);
                    echo json_encode($json_result); exit;
                }

                $json_result = array('result'=>1);
                echo json_encode($json_result); exit;
            }
        }
    }
}
