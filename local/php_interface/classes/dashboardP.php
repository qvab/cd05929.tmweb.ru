<?php

//Класс для работы с данными дашборда партнера
class dashboardP {

    const highload_id = 13;

    //возвращает данные для отображения на дашборде партнера
    function getDashboardData($filter_data = array()){
        $result = array(
            'FARMERS_DATA_MAIN' => array(
                'NOT_DEMO_TODAY'        => 0,
                'DEMO_TODAY'            => 0,
                'TOTAL_TODAY'           => 0,
                'NOT_DEMO_YESTERDAY'    => 0,
                'DEMO_YESTERDAY'        => 0,
                'TOTAL_YESTERDAY'       => 0,
                'NOT_DEMO_WEEK_AGO'     => 0,
                'DEMO_WEEK_AGO'         => 0,
                'TOTAL_WEEK_AGO'        => 0
            ),
            'FARMERS_DATA_NO_OFFERS' => array(
                'TODAY'     => 0,
                'YESTERDAY' => 0,
                'WEEK_AGO'  => 0
            ),
            'CLIENTS_DATA_MAIN' => array(
                'NOT_DEMO_TODAY'        => 0,
                'DEMO_TODAY'            => 0,
                'TOTAL_TODAY'           => 0,
                'NOT_DEMO_YESTERDAY'    => 0,
                'DEMO_YESTERDAY'        => 0,
                'TOTAL_YESTERDAY'       => 0,
                'NOT_DEMO_WEEK_AGO'     => 0,
                'DEMO_WEEK_AGO'         => 0,
                'TOTAL_WEEK_AGO'        => 0
            ),
            'CLIENTS_DATA_NO_REQUESTS' => array(
                'TODAY'     => 0,
                'YESTERDAY' => 0,
                'WEEK_AGO'  => 0
            ),
            'TRANSPORT_DATA' => array(
                'TODAY'     => 0,
                'YESTERDAY' => 0,
                'WEEK_AGO'  => 0
            )
        );

        $entityDataClass = log::getEntityDataClass(self::highload_id);
        $el = new $entityDataClass;

        $filter = array();
        if(isset($filter_data['PARTNER_ID'])
            && (is_numeric($filter_data['PARTNER_ID'])
                || is_array($filter_data['PARTNER_ID'])
                    && count($filter_data['PARTNER_ID']) > 0
            )
        ){
            if($filter_data['PARTNER_ID'] != 0)
                $filter['UF_PARTNER_ID'] = $filter_data['PARTNER_ID'];
        }

        if(isset($filter_data['AGENT_ID'])
            && is_numeric($filter_data['AGENT_ID'])
            && $filter_data['AGENT_ID'] > 0
        ){
            if($filter_data['AGENT_ID'] == 1){
                //только агенты
                $filter['!=UF_AGENT_ID'] = 0;
            }elseif($filter_data['AGENT_ID'] == 2){
                //только без агентов
                $filter['UF_AGENT_ID'] = 0;
            }else{
                //id агента
                $filter['UF_AGENT_ID'] = $filter_data['AGENT_ID'];
            }
        }

        //берём данные за сегодня
        $filter['UF_DATE'] = date('d.m.Y');
        $rsData = $el->getList(array(
            'select' => array('*'),
            'filter' => $filter,
            'order' => array('ID' => 'ASC')
        ));
        while($data = $rsData->fetch()){
            $result['CLIENTS_DATA_MAIN']['NOT_DEMO_TODAY']  += $data['UF_CLIENT_NUM_N_DEMO'];
            $result['CLIENTS_DATA_MAIN']['DEMO_TODAY']      += $data['UF_CLIENT_NUM_DEMO'];
            $result['CLIENTS_DATA_NO_REQUESTS']['TODAY']    += $data['UF_NO_REQ_CLIENT_CNT'];

            $result['FARMERS_DATA_MAIN']['NOT_DEMO_TODAY']  += $data['UF_FARMER_NUM_N_DEMO'];
            $result['FARMERS_DATA_MAIN']['DEMO_TODAY']      += $data['UF_FARMER_NUM_DEMO'];
            $result['FARMERS_DATA_NO_OFFERS']['TODAY']      += $data['UF_NO_OF_FARMERS_CNT'];

            $result['TRANSPORT_DATA']['TODAY']              += $data['UF_TRANSPORT_NUM'];
        }
        //подсчитываем общие данные
        $result['CLIENTS_DATA_MAIN']['TOTAL_TODAY'] = $result['CLIENTS_DATA_MAIN']['NOT_DEMO_TODAY'] +
            $result['CLIENTS_DATA_MAIN']['DEMO_TODAY'];
        $result['FARMERS_DATA_MAIN']['TOTAL_TODAY'] = $result['FARMERS_DATA_MAIN']['NOT_DEMO_TODAY'] +
            $result['FARMERS_DATA_MAIN']['DEMO_TODAY'];

        //берём данные за вчера
        $filter['UF_DATE'] = date('d.m.Y', strtotime('-1 DAYS'));
        $rsData = $el->getList(array(
            'select' => array('*'),
            'filter' => $filter,
            'order' => array('ID' => 'ASC')
        ));
        while($data = $rsData->fetch()){
            $result['CLIENTS_DATA_MAIN']['NOT_DEMO_YESTERDAY']  += $data['UF_CLIENT_NUM_N_DEMO'];
            $result['CLIENTS_DATA_MAIN']['DEMO_YESTERDAY']      += $data['UF_CLIENT_NUM_DEMO'];
            $result['CLIENTS_DATA_NO_REQUESTS']['YESTERDAY']    += $data['UF_NO_REQ_CLIENT_CNT'];

            $result['FARMERS_DATA_MAIN']['NOT_DEMO_YESTERDAY']  += $data['UF_FARMER_NUM_N_DEMO'];
            $result['FARMERS_DATA_MAIN']['DEMO_YESTERDAY']      += $data['UF_FARMER_NUM_DEMO'];
            $result['FARMERS_DATA_NO_OFFERS']['YESTERDAY']      += $data['UF_NO_OF_FARMERS_CNT'];

            $result['TRANSPORT_DATA']['YESTERDAY']              += $data['UF_TRANSPORT_NUM'];
        }
        //подсчитываем общие данные
        $result['CLIENTS_DATA_MAIN']['TOTAL_YESTERDAY'] = $result['CLIENTS_DATA_MAIN']['NOT_DEMO_YESTERDAY'] +
            $result['CLIENTS_DATA_MAIN']['DEMO_YESTERDAY'];
        $result['FARMERS_DATA_MAIN']['TOTAL_YESTERDAY'] = $result['FARMERS_DATA_MAIN']['NOT_DEMO_YESTERDAY'] +
            $result['FARMERS_DATA_MAIN']['DEMO_YESTERDAY'];

        //берём данные недельной давности
        $filter['UF_DATE'] = date('d.m.Y', strtotime('-7 DAYS'));
        $rsData = $el->getList(array(
            'select' => array('*'),
            'filter' => $filter,
            'order' => array('ID' => 'ASC')
        ));
        while($data = $rsData->fetch()){
            $result['CLIENTS_DATA_MAIN']['NOT_DEMO_WEEK_AGO']   += $data['UF_CLIENT_NUM_N_DEMO'];
            $result['CLIENTS_DATA_MAIN']['DEMO_WEEK_AGO']       += $data['UF_CLIENT_NUM_DEMO'];
            $result['CLIENTS_DATA_NO_REQUESTS']['WEEK_AGO']     += $data['UF_NO_REQ_CLIENT_CNT'];

            $result['FARMERS_DATA_MAIN']['NOT_DEMO_WEEK_AGO']   += $data['UF_FARMER_NUM_N_DEMO'];
            $result['FARMERS_DATA_MAIN']['DEMO_WEEK_AGO']       += $data['UF_FARMER_NUM_DEMO'];
            $result['FARMERS_DATA_NO_OFFERS']['WEEK_AGO']       += $data['UF_NO_OF_FARMERS_CNT'];

            $result['TRANSPORT_DATA']['WEEK_AGO']               += $data['UF_TRANSPORT_NUM'];
        }
        //подсчитываем общие данные
        $result['CLIENTS_DATA_MAIN']['TOTAL_WEEK_AGO'] = $result['CLIENTS_DATA_MAIN']['NOT_DEMO_WEEK_AGO'] +
            $result['CLIENTS_DATA_MAIN']['DEMO_WEEK_AGO'];
        $result['FARMERS_DATA_MAIN']['TOTAL_WEEK_AGO'] = $result['FARMERS_DATA_MAIN']['NOT_DEMO_WEEK_AGO'] +
            $result['FARMERS_DATA_MAIN']['DEMO_WEEK_AGO'];

        return $result;
    }

    /*
     * Получает данные для построения детальной страницы dashboard
     *
     * @param [] $filter_data - фильтр по агентам и партнерам
     * @param string $user_type - тип пользователя (client, farmer или transport)
     * @param string $show_type - тип отображаемых данных (пусто - демо и не демо пользователи, demo - демо-режим, not_demo - не демо-режим, no_data - без товаров или без запросов)
     *
     * @return [] массив со списком культур
     * */
    function getDetailPageData($filter_data = array(), $user_type, $show_type, $list_url){
        $result = array(
            'PARTNER_DATA'  => array(),
            'AGENT_DATA'    => array(),
            'USERS_DATA'    => array()
        );

        $select = array('UF_PARTNER_ID', 'UF_AGENT_ID');
        $filter = array();
        $users_names  = array();
        $users_data   = array();

        CModule::IncludeModule('iblock');
        $el_obj = new CIBlockElement;
        $u_obj  = new CUser;

        $entityDataClass = log::getEntityDataClass(self::highload_id);
        $el = new $entityDataClass;

        $filter['UF_DATE'] = $filter_data['UF_DATE'];
        if(isset($filter_data['PARTNER_ID'])){
            $filter['UF_PARTNER_ID'] = $filter_data['PARTNER_ID'];
        }
        if(isset($filter_data['AGENT_ID'])){
            if($filter_data['AGENT_ID'] == 1){
                //только агенты
                $filter['!=UF_AGENT_ID'] = 0;
            }elseif($filter_data['AGENT_ID'] == 2){
                //только без агента
                $filter['UF_AGENT_ID'] = 0;
            }else{
                //выбран агент
                $filter['UF_AGENT_ID'] = $filter_data['AGENT_ID'];
            }
        }

        switch($user_type){
            case 'farmer':
                if($show_type === 'not_demo'){
                    //получаем данные пользователей в полноценном режиме
                    $select[] = 'UF_F_NUM_N_DEMO_LIST';
                    $filter['!=UF_F_NUM_N_DEMO_LIST'] = '';
                    $rsData = $el->getList(array(
                        'select' => $select,
                        'filter' => $filter,
                        'order' => array('ID' => 'ASC')
                    ));
                    while($data = $rsData->fetch()){
                        $result['PARTNER_DATA'][$data['UF_PARTNER_ID']] = '';
                        if(is_numeric($data['UF_AGENT_ID'])
                            && $data['UF_AGENT_ID'] > 0
                        ){
                            $result['AGENT_DATA'][$data['UF_AGENT_ID']] = '';
                        }
                        $temp_arr = explode(';', $data['UF_F_NUM_N_DEMO_LIST']);
                        foreach($temp_arr as $cur_id)
                        {
                            $users_data[$cur_id] = array(
                                'NAME'      => '',
                                'EMAIL'     => '',
                                'AGENT'     => $data['UF_AGENT_ID'],
                                'PARTNER'   => $data['UF_PARTNER_ID']
                            );
                        }
                    }
                }elseif($show_type === 'demo'){
                    //получаем данные пользователей в демо-режиме
                    $select[] = 'UF_F_NUM_DEMO_LIST';
                    $filter['!=UF_F_NUM_DEMO_LIST'] = '';
                    $rsData = $el->getList(array(
                        'select' => $select,
                        'filter' => $filter,
                        'order' => array('ID' => 'ASC')
                    ));
                    while($data = $rsData->fetch()){
                        $result['PARTNER_DATA'][$data['UF_PARTNER_ID']] = '';
                        if(is_numeric($data['UF_AGENT_ID'])
                            && $data['UF_AGENT_ID'] > 0
                        ){
                            $result['AGENT_DATA'][$data['UF_AGENT_ID']] = '';
                        }
                        $temp_arr = explode(';', $data['UF_F_NUM_DEMO_LIST']);
                        foreach($temp_arr as $cur_id)
                        {
                            $users_data[$cur_id] = array(
                                'NAME'      => '',
                                'EMAIL'     => '',
                                'AGENT'     => $data['UF_AGENT_ID'],
                                'PARTNER'   => $data['UF_PARTNER_ID']
                            );
                        }
                    }
                }elseif($show_type === 'no_data'){
                    //получаем данные пользователей без товаров
                    $select[] = 'UF_NO_OFFER_FARMERS';
                    $filter['!=UF_NO_OFFER_FARMERS'] = '';
                    $rsData = $el->getList(array(
                        'select' => $select,
                        'filter' => $filter,
                        'order' => array('ID' => 'ASC')
                    ));
                    while($data = $rsData->fetch()){
                        $result['PARTNER_DATA'][$data['UF_PARTNER_ID']] = '';
                        if(is_numeric($data['UF_AGENT_ID'])
                            && $data['UF_AGENT_ID'] > 0
                        ){
                            $result['AGENT_DATA'][$data['UF_AGENT_ID']] = '';
                        }
                        $temp_arr = explode(';', $data['UF_NO_OFFER_FARMERS']);
                        foreach($temp_arr as $cur_id)
                        {
                            $users_data[$cur_id] = array(
                                'NAME'      => '',
                                'EMAIL'     => '',
                                'AGENT'     => $data['UF_AGENT_ID'],
                                'PARTNER'   => $data['UF_PARTNER_ID']
                            );
                        }
                    }
                }else{
                    //получаем даныне пользователей как в демо-режиме так и в полноценном режиме
                    $select[] = 'UF_F_NUM_N_DEMO_LIST';
                    $select[] = 'UF_F_NUM_DEMO_LIST';
                    $rsData = $el->getList(array(
                        'select' => $select,
                        'filter' => $filter,
                        'order' => array('ID' => 'ASC')
                    ));
                    while($data = $rsData->fetch()){
                        $result['PARTNER_DATA'][$data['UF_PARTNER_ID']] = '';
                        if(is_numeric($data['UF_AGENT_ID'])
                            && $data['UF_AGENT_ID'] > 0
                        ){
                            $result['AGENT_DATA'][$data['UF_AGENT_ID']] = '';
                        }
                        $list_data = $data['UF_F_NUM_N_DEMO_LIST'];
                        if($data['UF_F_NUM_N_DEMO_LIST'] != ''
                            && $data['UF_F_NUM_DEMO_LIST'] != ''
                        ){
                            $list_data .=  ';';
                        }
                        $list_data .=  $data['UF_F_NUM_DEMO_LIST'];

                        if($list_data != ''){
                            $temp_arr = explode(';', $list_data);
                            foreach($temp_arr as $cur_id)
                            {
                                $users_data[$cur_id] = array(
                                    'NAME'      => '',
                                    'EMAIL'     => '',
                                    'AGENT'     => $data['UF_AGENT_ID'],
                                    'PARTNER'   => $data['UF_PARTNER_ID']
                                );
                            }
                        }
                    }
                }

                //получаем данные компаний пользователей
                if(count($users_data) > 0){
                    $ip_value_id = rrsIblock::getPropListKey('farmer_profile', 'UL_TYPE', 'ip');
                    $res = $el_obj->GetList(
                        array('PROPERTY_FULL_COMPANY_NAME' => 'ASC', 'PROPERTY_IP_FIO' => 'ASC'),
                        array(
                            'IBLOCK_ID'     => rrsIblock::getIBlockId('farmer_profile'),
                            'PROPERTY_USER' => array_keys($users_data)
                        ),
                        false,
                        false,
                        array('PROPERTY_FULL_COMPANY_NAME', 'PROPERTY_IP_FIO', 'PROPERTY_UL_TYPE', 'PROPERTY_USER')
                    );
                    while($data = $res->Fetch()){
                        if($data['PROPERTY_UL_TYPE_ENUM_ID'] != $ip_value_id){
                            $users_data[$data['PROPERTY_USER_VALUE']]['NAME'] = $data['PROPERTY_FULL_COMPANY_NAME_VALUE'];
                        }else{
                            $users_data[$data['PROPERTY_USER_VALUE']]['NAME'] = $data['PROPERTY_IP_FIO_VALUE'];
                        }
                    }
                }
                break;

            case 'client':
                if($show_type === 'not_demo'){
                    //получаем данные пользователей в полноценном режиме
                    $select[] = 'UF_C_NUM_N_DEMO_LIST';
                    $filter['!=UF_C_NUM_N_DEMO_LIST'] = '';
                    $rsData = $el->getList(array(
                        'select' => $select,
                        'filter' => $filter,
                        'order' => array('ID' => 'ASC')
                    ));
                    while($data = $rsData->fetch()){
                        $result['PARTNER_DATA'][$data['UF_PARTNER_ID']] = '';
                        if(is_numeric($data['UF_AGENT_ID'])
                            && $data['UF_AGENT_ID'] > 0
                        ){
                            $result['AGENT_DATA'][$data['UF_AGENT_ID']] = '';
                        }
                        $temp_arr = explode(';', $data['UF_C_NUM_N_DEMO_LIST']);
                        foreach($temp_arr as $cur_id)
                        {
                            $users_data[$cur_id] = array(
                                'NAME'      => '',
                                'EMAIL'     => '',
                                'AGENT'     => $data['UF_AGENT_ID'],
                                'PARTNER'   => $data['UF_PARTNER_ID']
                            );
                        }
                    }
                }elseif($show_type === 'demo'){
                    //получаем данные пользователей в демо-режиме
                    $select[] = 'UF_C_NUM_DEMO_LIST';
                    $filter['!=UF_C_NUM_DEMO_LIST'] = '';
                    $rsData = $el->getList(array(
                        'select' => $select,
                        'filter' => $filter,
                        'order' => array('ID' => 'ASC')
                    ));
                    while($data = $rsData->fetch()){
                        $result['PARTNER_DATA'][$data['UF_PARTNER_ID']] = '';
                        if(is_numeric($data['UF_AGENT_ID'])
                            && $data['UF_AGENT_ID'] > 0
                        ){
                            $result['AGENT_DATA'][$data['UF_AGENT_ID']] = '';
                        }
                        $temp_arr = explode(';', $data['UF_C_NUM_DEMO_LIST']);
                        foreach($temp_arr as $cur_id)
                        {
                            $users_data[$cur_id] = array(
                                'NAME'      => '',
                                'EMAIL'     => '',
                                'AGENT'     => $data['UF_AGENT_ID'],
                                'PARTNER'   => $data['UF_PARTNER_ID']
                            );
                        }
                    }
                }elseif($show_type === 'no_data'){
                    //получаем данные пользователей без запросов
                    $select[] = 'UF_NO_REQ_CLIENTS';
                    $filter['!=UF_NO_REQ_CLIENTS'] = '';
                    $rsData = $el->getList(array(
                        'select' => $select,
                        'filter' => $filter,
                        'order' => array('ID' => 'ASC')
                    ));
                    while($data = $rsData->fetch()){
                        $result['PARTNER_DATA'][$data['UF_PARTNER_ID']] = '';
                        if(is_numeric($data['UF_AGENT_ID'])
                            && $data['UF_AGENT_ID'] > 0
                        ){
                            $result['AGENT_DATA'][$data['UF_AGENT_ID']] = '';
                        }
                        $temp_arr = explode(';', $data['UF_NO_REQ_CLIENTS']);
                        foreach($temp_arr as $cur_id)
                        {
                            $users_data[$cur_id] = array(
                                'NAME'      => '',
                                'EMAIL'     => '',
                                'AGENT'     => $data['UF_AGENT_ID'],
                                'PARTNER'   => $data['UF_PARTNER_ID']
                            );
                        }
                    }
                }else{
                    //получаем даныне пользователей как в демо-режиме так и в полноценном режиме
                    $select[] = 'UF_C_NUM_N_DEMO_LIST';
                    $select[] = 'UF_C_NUM_DEMO_LIST';
                    $rsData = $el->getList(array(
                        'select' => $select,
                        'filter' => $filter,
                        'order' => array('ID' => 'ASC')
                    ));
                    while($data = $rsData->fetch()){
                        $result['PARTNER_DATA'][$data['UF_PARTNER_ID']] = '';
                        if(is_numeric($data['UF_AGENT_ID'])
                            && $data['UF_AGENT_ID'] > 0
                        ){
                            $result['AGENT_DATA'][$data['UF_AGENT_ID']] = '';
                        }
                        $list_data = $data['UF_C_NUM_N_DEMO_LIST'];
                        if($data['UF_C_NUM_N_DEMO_LIST'] != ''
                            && $data['UF_C_NUM_DEMO_LIST'] != ''
                        ){
                            $list_data .=  ';';
                        }
                        $list_data .=  $data['UF_C_NUM_DEMO_LIST'];

                        if($list_data != ''){
                            $temp_arr = explode(';', $list_data);
                            foreach($temp_arr as $cur_id)
                            {
                                $users_data[$cur_id] = array(
                                    'NAME'      => '',
                                    'EMAIL'     => '',
                                    'AGENT'     => $data['UF_AGENT_ID'],
                                    'PARTNER'   => $data['UF_PARTNER_ID']
                                );
                            }
                        }
                    }
                }

                //получаем данные компаний пользователей
                if(count($users_data) > 0){
                    $ip_value_id = rrsIblock::getPropListKey('client_profile', 'UL_TYPE', 'ip');
                    $res = $el_obj->GetList(
                        array('PROPERTY_FULL_COMPANY_NAME' => 'ASC', 'PROPERTY_IP_FIO' => 'ASC'),
                        array(
                            'IBLOCK_ID'     => rrsIblock::getIBlockId('client_profile'),
                            'PROPERTY_USER' => array_keys($users_data)
                        ),
                        false,
                        false,
                        array('PROPERTY_FULL_COMPANY_NAME', 'PROPERTY_IP_FIO', 'PROPERTY_UL_TYPE', 'PROPERTY_USER')
                    );
                    while($data = $res->Fetch()){
                        if($data['PROPERTY_UL_TYPE_ENUM_ID'] != $ip_value_id){
                            $users_data[$data['PROPERTY_USER_VALUE']]['NAME'] = $data['PROPERTY_FULL_COMPANY_NAME_VALUE'];
                        }else{
                            $users_data[$data['PROPERTY_USER_VALUE']]['NAME'] = $data['PROPERTY_IP_FIO_VALUE'];
                        }
                    }
                }
                break;

            case 'transport':
                //получаем данные пользователей в полноценном режиме
                $select[] = 'UF_TRANSPORT_LIST';
                $filter['!=UF_TRANSPORT_LIST'] = '';
                $rsData = $el->getList(array(
                    'select' => $select,
                    'filter' => $filter,
                    'order' => array('ID' => 'ASC')
                ));
                while($data = $rsData->fetch()){
                    $result['PARTNER_DATA'][$data['UF_PARTNER_ID']] = '';
                    if(is_numeric($data['UF_AGENT_ID'])
                        && $data['UF_AGENT_ID'] > 0
                    ){
                        $result['AGENT_DATA'][$data['UF_AGENT_ID']] = '';
                    }
                    $temp_arr = explode(';', $data['UF_TRANSPORT_LIST']);
                    foreach($temp_arr as $cur_id)
                    {
                        $users_data[$cur_id] = array(
                            'NAME'      => '',
                            'EMAIL'     => '',
                            'AGENT'     => $data['UF_AGENT_ID'],
                            'PARTNER'   => $data['UF_PARTNER_ID']
                        );
                    }
                }

                //получаем данные компаний пользователей
                if(count($users_data) > 0){
                    $ip_value_id = rrsIblock::getPropListKey('transport_profile', 'UL_TYPE', 'ip');
                    $res = $el_obj->GetList(
                        array('PROPERTY_FULL_COMPANY_NAME' => 'ASC', 'PROPERTY_IP_FIO' => 'ASC'),
                        array(
                            'IBLOCK_ID'     => rrsIblock::getIBlockId('transport_profile'),
                            'PROPERTY_USER' => array_keys($users_data)
                        ),
                        false,
                        false,
                        array('PROPERTY_FULL_COMPANY_NAME', 'PROPERTY_IP_FIO', 'PROPERTY_UL_TYPE', 'PROPERTY_USER')
                    );
                    while($data = $res->Fetch()){
                        if($data['PROPERTY_UL_TYPE_ENUM_ID'] != $ip_value_id){
                            $users_data[$data['PROPERTY_USER_VALUE']]['NAME'] = $data['PROPERTY_FULL_COMPANY_NAME_VALUE'];
                        }else{
                            $users_data[$data['PROPERTY_USER_VALUE']]['NAME'] = $data['PROPERTY_IP_FIO_VALUE'];
                        }
                    }
                }
                break;

            default:
                //отправляем пользователя на основную страницу dashboard, если неверный параметр $user_type
                LocalRedirect($list_url);
                exit;
        }

        //получаем email пользователей и имена(для агентов и партнеров, либо если название компании пусто)
        //затем сортируем данные по полю имени и заполняем массив $result
        if(count($users_data) > 0){
            //данные для пользователей
            $res = $u_obj->GetList(
                ($by = 'NAME'), ($order = 'ASC'),
                array(
                    'ID' => implode(' | ', array_keys($users_data)),
                    'ACTIVE'        => 'Y'
                ),
                array('FIELDS' => array('ID', 'EMAIL', 'NAME', 'LAST_NAME'), 'SELECT' => array('UF_DEMO'))
            );
            while($data = $res->Fetch()){
                $users_data[$data['ID']]['EMAIL'] = $data['EMAIL'];
                if($users_data[$data['ID']]['NAME'] == ''){
                    $users_data[$data['ID']]['NAME'] = trim($data['NAME'] . ' ' . $data['LAST_NAME']);
                    if($users_data[$data['ID']]['NAME'] == ''){
                        $users_data[$data['ID']]['NAME'] = $data['EMAIL'];
                    }
                }
            }

            //данные для партнёров
            if(count($result['PARTNER_DATA']) > 0){
                $empty_name_flag = false;
                $ip_value_id = rrsIblock::getPropListKey('partner_profile', 'UL_TYPE', 'ip');
                $res = $el_obj->GetList(
                    array('PROPERTY_FULL_COMPANY_NAME' => 'ASC', 'PROPERTY_IP_FIO' => 'ASC'),
                    array(
                        'IBLOCK_ID'     => rrsIblock::getIBlockId('partner_profile'),
                        'PROPERTY_USER' => array_keys($result['PARTNER_DATA'])
                    ),
                    false,
                    false,
                    array('PROPERTY_FULL_COMPANY_NAME', 'PROPERTY_IP_FIO', 'PROPERTY_UL_TYPE', 'PROPERTY_USER')
                );
                while($data = $res->Fetch()){
                    if($data['PROPERTY_UL_TYPE_ENUM_ID'] != $ip_value_id){
                        $result['PARTNER_DATA'][$data['PROPERTY_USER_VALUE']] = $data['PROPERTY_FULL_COMPANY_NAME_VALUE'];
                    }else{
                        $result['PARTNER_DATA'][$data['PROPERTY_USER_VALUE']] = $data['PROPERTY_IP_FIO_VALUE'];
                    }

                    if($result['PARTNER_DATA'][$data['PROPERTY_USER_VALUE']] == ''){
                        $empty_name_flag = true;
                    }
                }
                if($empty_name_flag){
                    //получение данных для транспортных компаний у которых нет названия компании
                    $res = $u_obj->GetList(
                        ($by = 'ID'), ($order = 'ASC'),
                        array(
                            'ID' => implode(' | ', array_keys($result['PARTNER_DATA']))
                        ),
                        array('FIELDS' => array('ID', 'EMAIL'))
                    );
                    while($data = $res->Fetch()){
                        if($result['PARTNER_DATA']['NAME'] == ''){
                            $result['PARTNER_DATA']['NAME'] = $data['EMAIL'];
                        }
                    }
                }
            }

            //Данные для агентов
            if(count($result['AGENT_DATA']) > 0){
                $res = $u_obj->GetList(
                    ($by = 'ID'), ($order = 'ASC'),
                    array(
                        'ID' => implode(' | ', array_keys($result['AGENT_DATA']))
                    ),
                    array('FIELDS' => array('ID', 'EMAIL', 'NAME', 'LAST_NAME'))
                );
                while($data = $res->Fetch()){
                    $result['AGENT_DATA'][$data['ID']] = trim($data['NAME'] . ' ' . $data['LAST_NAME']);
                    if($result['AGENT_DATA'][$data['ID']] == ''){
                        $result['AGENT_DATA'][$data['ID']] = $data['EMAIL'];
                    }
                }
            }

            //получаем имена и сортируем из по алфавиту
            foreach($users_data as $cur_uid => $cur_data){
                $users_names[$cur_uid] = $cur_data['NAME'];
            }
            asort($users_names);
            foreach($users_names as $cur_uid => $cur_name){
                if($users_data[$cur_uid]['EMAIL'] != ''){
                    $result['USERS_DATA'][$cur_uid] = $users_data[$cur_uid];
                }
            }
        }

        return $result;
    }

    //получает данные для сохранения данных в БД
    // 1. Получение списка партнеров (далее рассматриваются только пользователи, привязанные к организаторам)
    // 2. Получение связанных агентов покупателей
    // 3. Получение связанных агентов АП
    // 4. Получение связанных АП
    // 5. Получение связанных покупателей
    // 6. Проверка того являются ли пользователи демо-пользователями
    // 7. Получение активных товаров
    // 8. Получение активных запросов
    // 9. Получение связанных транспортных компаний
    //10. структуризация данных для внесения в БД
    //11. Внесение данных в БД
    function createDashboardData(){
        CModule::IncludeModule('iblock');
        $el_obj = new CIBlockElement;
        $u_obj = new CUser;

        $partners_list = array(); //массив партнеров (внутри массивы ID привязанных агентов АП и агентов покупателей)
        $client_agents_by_partners_list = array(); //массив агентов покупателей (внутри ID привязанных покупателей)
        $farmer_agents_by_partners_list = array(); //массив агентов АП (внутри ID привязанных АП)

        $active_offers_list = array(); //ID активных товаров, для рассматриваемых АП
        $linked_farmers_list = array(); //связи партнеров и АП (не привязанных к агентам)
        $linked_farmers_by_partner_list = array(); //связи АП без агентов с партнерами
        $linked_farmers_to_agents_list = array(); //ID АП, привязанных к агентам
        $all_farmers_list = array(); //общий массив ID АП ($linked_farmers_list + $linked_farmers_to_agents_list)

        $active_requests_list = array();  //ID активных запросов, для рассматриваемых покупателей
        $linked_clients_list = array(); //связи партнеров и покупателей (не привязанных к агентам)
        $linked_clients_by_partner_list = array(); //покупателей без агентов с партнерами
        $linked_clients_to_agents_list = array(); //ID покупателей, привязанных к агентам
        $all_clients_list = array(); //общий массив ID покупателей ($linked_clients_list + $linked_clients_to_agents_list)

        $inactive_list = array();//список деактивированных пользователей
        $demo_users_ids = array();//список пользователей в режиме дето
        $linked_transports_list = array();
        $all_transport_list = array();

        // 1. Получение списка партнеров
        $res = $u_obj->GetList(($by = 'id'), ($order = 'asc'), array('GROUPS_ID' => 10, 'ACTIVE' => 'Y'), array('FIELDS' => array('ID')));
        while($data = $res->Fetch()){
            $partners_list[$data['ID']] = array(
                'CL_AGENTS' => array(),
                'FM_AGENTS' => array(),
            );
        }

        if(count($partners_list) == 0){
            exit;
        }

        // 2. Получение связанных с партнерами агентов покупателей
        $res = $el_obj->GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID'           => rrsIblock::getIBlockId('client_agent_partner_link'),
                'PROPERTY_PARTNER_ID' => array_keys($partners_list)
            ),
            false,
            false,
            array(
                'PROPERTY_PARTNER_ID',
                'PROPERTY_USER_ID'
            )
        );
        while($data = $res->Fetch()){
            $client_agents_by_partners_list[$data['PROPERTY_USER_ID_VALUE']] = array(
                'USERS'         => array()
            );
            $partners_list[$data['PROPERTY_PARTNER_ID_VALUE']]['CL_AGENTS'][] = $data['PROPERTY_USER_ID_VALUE'];
        }
        if(count($client_agents_by_partners_list) > 0){
            //получение связей агентов покупателей с покупателями
            $res = $el_obj->GetList(
                array('ID' => 'ASC'),
                array(
                    'IBLOCK_ID'         => rrsIblock::getIBlockId('client_agent_link'),
                    'PROPERTY_AGENT_ID' => array_keys($client_agents_by_partners_list)
                ),
                false,
                false,
                array(
                    'PROPERTY_AGENT_ID',
                    'PROPERTY_USER_ID'
                )
            );
            while($data = $res->Fetch()){
                if(isset($client_agents_by_partners_list[$data['PROPERTY_AGENT_ID_VALUE']])){
                    $client_agents_by_partners_list[$data['PROPERTY_AGENT_ID_VALUE']]['USERS'][] = $data['PROPERTY_USER_ID_VALUE'];
                    $linked_clients_to_agents_list[] = $data['PROPERTY_USER_ID_VALUE'];
                }
            }
        }

        // 3. Получение связанных агентов АП
        $res = $el_obj->GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID'           => rrsIblock::getIBlockId('agent_partner_link'),
                'PROPERTY_PARTNER_ID' => array_keys($partners_list)
            ),
            false,
            false,
            array(
                'PROPERTY_PARTNER_ID',
                'PROPERTY_USER_ID'
            )
        );
        while($data = $res->Fetch()){
            $farmer_agents_by_partners_list[$data['PROPERTY_USER_ID_VALUE']] = array(
                'USERS'         => array()
            );
            $partners_list[$data['PROPERTY_PARTNER_ID_VALUE']]['FM_AGENTS'][] = $data['PROPERTY_USER_ID_VALUE'];
        }
        if(count($farmer_agents_by_partners_list) > 0){
            //получение связей агентов АП с АП
            $res = $el_obj->GetList(
                array('ID' => 'ASC'),
                array(
                    'IBLOCK_ID'         => rrsIblock::getIBlockId('farmer_agent_link'),
                    'PROPERTY_AGENT_ID' => array_keys($farmer_agents_by_partners_list)
                ),
                false,
                false,
                array(
                    'PROPERTY_AGENT_ID',
                    'PROPERTY_USER_ID'
                )
            );
            while($data = $res->Fetch()){
                if(isset($farmer_agents_by_partners_list[$data['PROPERTY_AGENT_ID_VALUE']])){
                    $farmer_agents_by_partners_list[$data['PROPERTY_AGENT_ID_VALUE']]['USERS'][] = $data['PROPERTY_USER_ID_VALUE'];
                    $linked_farmers_to_agents_list[] = $data['PROPERTY_USER_ID_VALUE'];
                }
            }
        }

        // 4. Получение связанных АП (без тех, что привязаны к агентам)
        $res = $el_obj->GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('farmer_profile'),
                'PROPERTY_PARTNER_ID' => array_keys($partners_list),
                '!PROPERTY_USER' => $linked_farmers_to_agents_list
            ),
            false,
            false,
            array('PROPERTY_USER', 'PROPERTY_PARTNER_ID')
        );
        while($data = $res->Fetch()){
            $linked_farmers_list[] = $data['PROPERTY_USER_VALUE'];
            $linked_farmers_by_partner_list[$data['PROPERTY_PARTNER_ID_VALUE']][] = $data['PROPERTY_USER_VALUE'];
        }

        // 5. Получение связанных покупателей (без тех, что привязаны к агентам)
        $res = $el_obj->GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('client_profile'),
                'PROPERTY_PARTNER_ID' => array_keys($partners_list),
                '!PROPERTY_USER' => $linked_clients_to_agents_list
            ),
            false,
            false,
            array('PROPERTY_USER', 'PROPERTY_PARTNER_ID')
        );
        while($data = $res->Fetch()){
            $linked_clients_list[] = $data['PROPERTY_USER_VALUE'];
            $linked_clients_by_partner_list[$data['PROPERTY_PARTNER_ID_VALUE']][] = $data['PROPERTY_USER_VALUE'];
        }
        $res = $el_obj->GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('client_partner_link'),
                'PROPERTY_PARTNER_ID' => array_keys($partners_list),
                '!PROPERTY_USER_ID' => $linked_clients_to_agents_list
            ),
            false,
            false,
            array('PROPERTY_USER_ID', 'PROPERTY_PARTNER_ID')
        );
        while($data = $res->Fetch()){
            $linked_clients_list[] = $data['PROPERTY_USER_ID_VALUE'];
            $linked_clients_by_partner_list[$data['PROPERTY_PARTNER_ID_VALUE']][] = $data['PROPERTY_USER_ID_VALUE'];
        }

        //связываем АП привязанных к организатору с агентом и без агента (и то же для покупателей)
        $all_farmers_list = array_merge($linked_farmers_to_agents_list, $linked_farmers_list);
        $all_clients_list = array_merge($linked_clients_to_agents_list, $linked_clients_list);

        // 6. Проверка того являются ли пользователи демо-пользователями
        $res = $u_obj->GetList(($by = 'id'), ($order = 'asc'),
            array('UF_DEMO' => 1,
                'ID' => implode(' | ', array_merge($all_clients_list, $all_farmers_list)
                )
            ),
            array('FIELDS' => array('ID'))
        );
        while($data = $res->Fetch()){
            $demo_users_ids[$data['ID']] = 1; //являются демо пользователями
        }

        // 7. Получение активных товаров
        if(count($all_farmers_list) > 0){
            $res = $el_obj->GetList(
                array('ID' => 'ASC'),
                array(
                    'IBLOCK_ID' => rrsIblock::getIBlockId('farmer_offer'),
                    'PROPERTY_FARMER' => $all_farmers_list,
                    'PROPERTY_ACTIVE' => rrsIblock::getPropListKey('farmer_offer', 'ACTIVE', 'yes')
                ),
                false,
                false,
                array('ID', 'PROPERTY_FARMER')
            );
            while($data = $res->Fetch()){
                $active_offers_list[$data['PROPERTY_FARMER_VALUE']] = true;
            }
        }

        // 8. Получение активных запросов
        if(count($all_clients_list) > 0){
            $res = $el_obj->GetList(
                array('ID' => 'ASC'),
                array(
                    'IBLOCK_ID' => rrsIblock::getIBlockId('client_request'),
                    'PROPERTY_CLIENT' => $all_clients_list,
                    'PROPERTY_ACTIVE' => rrsIblock::getPropListKey('client_request', 'ACTIVE', 'yes')
                ),
                false,
                false,
                array('ID', 'PROPERTY_CLIENT')
            );
            while($data = $res->Fetch()){
                $active_requests_list[$data['PROPERTY_CLIENT_VALUE']] = true;
            }
        }

        // 9. Получение связанных транспортных компаний
        $res = $el_obj->GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('transport_profile'),
                'PROPERTY_PARTNER_ID' => array_keys($partners_list)
            ),
            false,
            false,
            array('PROPERTY_USER', 'PROPERTY_PARTNER_ID')
        );
        while($data = $res->Fetch()){
            if(is_numeric($data['PROPERTY_USER_VALUE'])){
                $linked_transports_list[$data['PROPERTY_PARTNER_ID_VALUE']][$data['PROPERTY_USER_VALUE']] = true;
                $all_transport_list[$data['PROPERTY_USER_VALUE']] = true;
            }
        }
        $res = $el_obj->GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('transport_partner_link'),
                'PROPERTY_PARTNER_ID' => array_keys($partners_list)
            ),
            false,
            false,
            array('PROPERTY_USER_ID', 'PROPERTY_PARTNER_ID')
        );
        while($data = $res->Fetch()){
            if(is_numeric($data['PROPERTY_USER_VALUE'])){
                $linked_transports_list[$data['PROPERTY_PARTNER_ID_VALUE']][$data['PROPERTY_USER_VALUE']] = true;
                $all_transport_list[$data['PROPERTY_USER_VALUE']] = true;
            }
        }

        //10. Получение неактивных пользователей покупателей, АП и транспортных компаний
        $res = $u_obj->GetList(($by = 'id'), ($order = 'asc'),
            array('ACTIVE' => 'N',
                'ID' => implode(' | ', array_merge($all_clients_list, $all_farmers_list, array_keys($all_transport_list))
                )
            ),
            array('FIELDS' => array('ID'))
        );
        while($data = $res->Fetch()){
            $inactive_list[$data['ID']] = 1; //деактивированная учётная щапись
        }

        //10. структуризация данных для внесения в БД
        $line_arr = array();
        foreach($partners_list as $cur_partner_id => $cur_partner_data){
            //работа с АП, привязанными к агентам
            foreach($cur_partner_data['FM_AGENTS'] as $cur_agent_id){
                $temp_arr = array(
                    'UF_PARTNER_ID'         => $cur_partner_id, //id партнера
                    'UF_AGENT_ID'           => $cur_agent_id, //id агента
                    'UF_AGENT_TYPE'         => 'f', //тип агента (c - клиент, f - АП)
                    'UF_FARMER_NUM_N_DEMO'  => 0, //количество АП не в демо режиме
                    'UF_F_NUM_N_DEMO_LIST'  => '', //список АП не в демо режиме
                    'UF_FARMER_NUM_DEMO'    => 0, //количество АП в демо режиме
                    'UF_F_NUM_DEMO_LIST'    => '', //список АП в демо режиме
                    'UF_NO_OF_FARMERS_CNT'  => 0, //количество АП без активных товаров
                    'UF_NO_OFFER_FARMERS'   => '', //перечень id АП без активных товаров, привязанных к данному агенту АП
                    'UF_CLIENT_NUM_N_DEMO'  => 0, //количество клиентов не в демо режиме
                    'UF_C_NUM_N_DEMO_LIST'  => '', //список клиентов не в демо режиме
                    'UF_CLIENT_NUM_DEMO'    => 0, //количество клиентов в демо режиме
                    'UF_C_NUM_DEMO_LIST'    => '', //список клиентов в демо режиме
                    'UF_NO_REQ_CLIENT_CNT'  => 0, //количество клиентов без активных запросов
                    'UF_NO_REQ_CLIENTS'     => '', //перечень id клиентов без активных запросов, привязанных к данному агенту АП
                    'UF_TRANSPORT_NUM'      => 0, //количество грузоперевозчиков, привязанных к данному агенту
                    'UF_TRANSPORT_LIST'     => '' //количество грузоперевозчиков, привязанных к данному агенту
                );

                if(isset($farmer_agents_by_partners_list[$cur_agent_id]['USERS'])){
                    foreach($farmer_agents_by_partners_list[$cur_agent_id]['USERS'] as $cur_uid){
                        if(isset($inactive_list[$cur_uid])){
                            continue; //пропускаем деактивированные учётные записи
                        }

                        if(isset($demo_users_ids[$cur_uid])){
                            $temp_arr['UF_FARMER_NUM_DEMO'] += 1;

                            if($temp_arr['UF_F_NUM_DEMO_LIST'] != ''){
                                $temp_arr['UF_F_NUM_DEMO_LIST'] .= ';' . $cur_uid;
                            }else{
                                $temp_arr['UF_F_NUM_DEMO_LIST'] = $cur_uid;
                            }
                        }else{
                            $temp_arr['UF_FARMER_NUM_N_DEMO'] += 1;

                            if($temp_arr['UF_F_NUM_N_DEMO_LIST'] != ''){
                                $temp_arr['UF_F_NUM_N_DEMO_LIST'] .= ';' . $cur_uid;
                            }else{
                                $temp_arr['UF_F_NUM_N_DEMO_LIST'] = $cur_uid;
                            }
                        }

                        if(!isset($active_offers_list[$cur_uid])){
                            $temp_arr['UF_NO_OF_FARMERS_CNT'] += 1;

                            if($temp_arr['UF_NO_OFFER_FARMERS'] != ''){
                                $temp_arr['UF_NO_OFFER_FARMERS'] .= ';' . $cur_uid;
                            }else{
                                $temp_arr['UF_NO_OFFER_FARMERS'] = $cur_uid;
                            }
                        }
                    }

                    $line_arr[] = $temp_arr;
                }
            }

            //работа с покупателями, привязанными к агентам
            foreach($cur_partner_data['CL_AGENTS'] as $cur_agent_id){
                $temp_arr = array(
                    'UF_PARTNER_ID'         => $cur_partner_id, //id партнера
                    'UF_AGENT_ID'           => $cur_agent_id, //id агента
                    'UF_AGENT_TYPE'         => 'c', //тип агента (c - клиент, f - АП)
                    'UF_FARMER_NUM_N_DEMO'  => 0, //количество АП не в демо режиме
                    'UF_F_NUM_N_DEMO_LIST'  => '', //список АП не в демо режиме
                    'UF_FARMER_NUM_DEMO'    => 0, //количество АП в демо режиме
                    'UF_F_NUM_DEMO_LIST'    => '', //список АП в демо режиме
                    'UF_NO_OF_FARMERS_CNT'  => 0, //количество АП без активных товаров
                    'UF_NO_OFFER_FARMERS'   => '', //перечень id АП без активных товаров, привязанных к данному агенту АП
                    'UF_CLIENT_NUM_N_DEMO'  => 0, //количество клиентов не в демо режиме
                    'UF_C_NUM_N_DEMO_LIST'  => '', //список клиентов не в демо режиме
                    'UF_CLIENT_NUM_DEMO'    => 0, //количество клиентов в демо режиме
                    'UF_C_NUM_DEMO_LIST'    => '', //список клиентов в демо режиме
                    'UF_NO_REQ_CLIENT_CNT'  => 0, //количество клиентов без активных запросов
                    'UF_NO_REQ_CLIENTS'     => '', //перечень id клиентов без активных запросов, привязанных к данному агенту АП
                    'UF_TRANSPORT_NUM'      => 0, //количество грузоперевозчиков, привязанных к данному агенту
                    'UF_TRANSPORT_LIST'     => '' //количество грузоперевозчиков, привязанных к данному агенту
                );

                if(isset($client_agents_by_partners_list[$cur_agent_id]['USERS'])){
                    foreach($client_agents_by_partners_list[$cur_agent_id]['USERS'] as $cur_uid){
                        if(isset($inactive_list[$cur_uid])){
                            continue; //пропускаем деактивированные учётные записи
                        }

                        if(isset($demo_users_ids[$cur_uid])){
                            $temp_arr['UF_CLIENT_NUM_DEMO'] += 1;

                            if($temp_arr['UF_C_NUM_DEMO_LIST'] != ''){
                                $temp_arr['UF_C_NUM_DEMO_LIST'] .= ';' . $cur_uid;
                            }else{
                                $temp_arr['UF_C_NUM_DEMO_LIST'] = $cur_uid;
                            }
                        }else{
                            $temp_arr['UF_CLIENT_NUM_N_DEMO'] += 1;

                            if($temp_arr['UF_C_NUM_N_DEMO_LIST'] != ''){
                                $temp_arr['UF_C_NUM_N_DEMO_LIST'] .= ';' . $cur_uid;
                            }else{
                                $temp_arr['UF_C_NUM_N_DEMO_LIST'] = $cur_uid;
                            }
                        }

                        if(!isset($active_requests_list[$cur_uid])){
                            $temp_arr['UF_NO_REQ_CLIENT_CNT'] += 1;

                            if($temp_arr['UF_NO_REQ_CLIENTS'] != ''){
                                $temp_arr['UF_NO_REQ_CLIENTS'] .= ';' . $cur_uid;
                            }else{
                                $temp_arr['UF_NO_REQ_CLIENTS'] .= $cur_uid;
                            }
                        }
                    }
                }

                $line_arr[] = $temp_arr;
            }

            //количество грузоперевозчиков и данные по непривязанным к агентам покупателям и АП
            $no_agent_data_flag = false;
            $temp_arr = array(
                'UF_PARTNER_ID'         => $cur_partner_id, //id партнера
                'UF_AGENT_ID'           => 0, //id агента
                'UF_AGENT_TYPE'         => '', //тип агента (c - клиент, f - АП)
                'UF_FARMER_NUM_N_DEMO'  => 0, //количество АП не в демо режиме
                'UF_F_NUM_N_DEMO_LIST'  => '', //список АП не в демо режиме
                'UF_FARMER_NUM_DEMO'    => 0, //количество АП в демо режиме
                'UF_F_NUM_DEMO_LIST'    => '', //список АП в демо режиме
                'UF_NO_OF_FARMERS_CNT'  => 0, //количество АП без активных товаров
                'UF_NO_OFFER_FARMERS'   => '', //перечень id АП без активных товаров, привязанных к данному агенту АП
                'UF_CLIENT_NUM_N_DEMO'  => 0, //количество клиентов не в демо режиме
                'UF_C_NUM_N_DEMO_LIST'  => '', //список клиентов не в демо режиме
                'UF_CLIENT_NUM_DEMO'    => 0, //количество клиентов в демо режиме
                'UF_C_NUM_DEMO_LIST'    => '', //список клиентов в демо режиме
                'UF_NO_REQ_CLIENT_CNT'  => 0, //количество клиентов без активных запросов
                'UF_NO_REQ_CLIENTS'     => '', //перечень id клиентов без активных запросов, привязанных к данному агенту АП
                'UF_TRANSPORT_NUM'      => 0, //количество грузоперевозчиков, привязанных к данному агенту
                'UF_TRANSPORT_LIST'     => '' //количество грузоперевозчиков, привязанных к данному агенту
            );
            if(isset($linked_transports_list[$cur_partner_id])){
                $no_agent_data_flag = true;
                foreach($linked_transports_list[$cur_partner_id] as $cur_uid => $cur_flag){
                    if(isset($inactive_list[$cur_uid])){
                        continue; //пропускаем деактивированные учётные записи
                    }

                    if($temp_arr['UF_TRANSPORT_LIST'] != ''){
                        $temp_arr['UF_TRANSPORT_LIST'] .= ';' . $cur_uid;
                    }else{
                        $temp_arr['UF_TRANSPORT_LIST'] = $cur_uid;
                    }

                    $temp_arr['UF_TRANSPORT_NUM'] += 1;
                }
            }

            //покупатели партнеров, не привязанные к агентам
            if(isset($linked_clients_by_partner_list[$cur_partner_id])
                && count($linked_clients_by_partner_list[$cur_partner_id]) > 0
            ){
                $no_agent_data_flag = true;

                foreach($linked_clients_by_partner_list[$cur_partner_id] as $cur_uid){
                    if(isset($inactive_list[$cur_uid])){
                        continue; //пропускаем деактивированные учётные записи
                    }

                    if(isset($demo_users_ids[$cur_uid])){
                        $temp_arr['UF_CLIENT_NUM_DEMO'] += 1;

                        if($temp_arr['UF_C_NUM_DEMO_LIST'] != ''){
                            $temp_arr['UF_C_NUM_DEMO_LIST'] .= ';' . $cur_uid;
                        }else{
                            $temp_arr['UF_C_NUM_DEMO_LIST'] = $cur_uid;
                        }
                    }else{
                        $temp_arr['UF_CLIENT_NUM_N_DEMO'] += 1;

                        if($temp_arr['UF_C_NUM_N_DEMO_LIST'] != ''){
                            $temp_arr['UF_C_NUM_N_DEMO_LIST'] .= ';' . $cur_uid;
                        }else{
                            $temp_arr['UF_C_NUM_N_DEMO_LIST'] = $cur_uid;
                        }
                    }

                    if(!isset($active_offers_list[$cur_uid])){
                        $temp_arr['UF_NO_REQ_CLIENT_CNT'] += 1;

                        if($temp_arr['UF_NO_REQ_CLIENTS'] != ''){
                            $temp_arr['UF_NO_REQ_CLIENTS'] .= ';' . $cur_uid;
                        }else{
                            $temp_arr['UF_NO_REQ_CLIENTS'] .= $cur_uid;
                        }
                    }
                }
            }

            //АП партнеров, не привязанные к агентам
            if(isset($linked_farmers_by_partner_list[$cur_partner_id])
                && count($linked_farmers_by_partner_list[$cur_partner_id]) > 0
            ){
                $no_agent_data_flag = true;

                foreach($linked_farmers_by_partner_list[$cur_partner_id] as $cur_uid){
                    if(isset($inactive_list[$cur_uid])){
                        continue; //пропускаем деактивированные учётные записи
                    }

                    if(isset($demo_users_ids[$cur_uid])){
                        $temp_arr['UF_FARMER_NUM_DEMO'] += 1;

                        if($temp_arr['UF_F_NUM_DEMO_LIST'] != ''){
                            $temp_arr['UF_F_NUM_DEMO_LIST'] .= ';' . $cur_uid;
                        }else{
                            $temp_arr['UF_F_NUM_DEMO_LIST'] = $cur_uid;
                        }
                    }else{
                        $temp_arr['UF_FARMER_NUM_N_DEMO'] += 1;

                        if($temp_arr['UF_F_NUM_N_DEMO_LIST'] != ''){
                            $temp_arr['UF_F_NUM_N_DEMO_LIST'] .= ';' . $cur_uid;
                        }else{
                            $temp_arr['UF_F_NUM_N_DEMO_LIST'] = $cur_uid;
                        }
                    }

                    if(!isset($active_offers_list[$cur_uid])){
                        $temp_arr['UF_NO_OF_FARMERS_CNT'] += 1;

                        if($temp_arr['UF_NO_OFFER_FARMERS'] != ''){
                            $temp_arr['UF_NO_OFFER_FARMERS'] .= ';' . $cur_uid;
                        }else{
                            $temp_arr['UF_NO_OFFER_FARMERS'] .= $cur_uid;
                        }
                    }
                }
            }

            if($no_agent_data_flag){
                $line_arr[] = $temp_arr;
            }
        }

        //11. Внесение данных в БД
        self::saveDashboardData($line_arr);
    }

    //сохранение данных в БД
    function saveDashboardData($save_data){
        $log_obj = new log();
        $entityDataClass = $log_obj->getEntityDataClass(self::highload_id);

        $el = new $entityDataClass;
        $c_date = date('d.m.Y');

        //проверка того, что нет данных за текущую дату
        $res = $el->getList(array(
            'select' => array('ID'),
            'filter' => array('UF_DATE' => $c_date),
            'limit' => 1
        ));
        if($res->getSelectedRowsCount() == 0){
            //за сегодня записей еще нет -> вносим данные
            foreach($save_data as $cur_elem){
                $cur_elem['UF_DATE'] = $c_date;
                log::_createEntity(self::highload_id, $cur_elem);
            }
        }
    }
}