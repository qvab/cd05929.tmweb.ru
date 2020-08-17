<?php

//получение данных для графиков для выбранного товара

//разрешенные режимы работы
$allowed_types = array(
    'year' => true,
    'month' => true,
    'week' => true,
);

if(isset($_POST['offer_id'])
    && is_numeric($_POST['offer_id'])
    && isset($_POST['type_code'])
    && isset($allowed_types[$_POST['type_code']])
){
    header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
    header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
    header ("Cache-Control: no-cache, must-revalidate");
    header ("Pragma: no-cache");
    header("content-type: application/x-javascript; charset=UTF-8");
    require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

    //получение метки времени в соответствии с выбранным режимом
    $get_time = strtotime('-1 ' . $_POST['type_code']);
    $cur_time = time();

    $offerData = farmer::getOfferById($_POST['offer_id']);

    //данные для работы
    $categories_data = '';
    $best_data = '';
    $my_prices_data = '';
    $wh_radwh_data = '';
    $deals_cur_reg_data = '';
    $deals_cur_reg_arr = array();
    $deals_linked_reg_data = '';
    $deals_linked_reg_arr = array();

    //получение данных для графика "Спрос"
    $cur_pos = 0;
    $current_offer = 0;
    $date = '';
    $temp_date = array();
    //$temp_arr_cur_min = array(10, 10, 3500);
    //$temp_arr_cur_max = array(0, 0, 0);
//    $temp_arr_new = array();
//    $temp_val = '';
    $max_data = '';
    $min_data = '';

    $data = log::_getEntitiesList(rrsIblock::HLgetIBlockId('BESTOFFERPRICES'), array(
        'UF_OFFER_ID'   => $_POST['offer_id'],
        '>UF_DATE'      => date('d.m.Y', $get_time),
        '>UF_BEST_CSM_PRICE' => 0
    ), false, array(
            'UF_OFFER_ID'   => 'ASC',
            'UF_DATE'       => 'ASC'
        )
    );

    $cur_pos = 0;
    foreach($data as $cur_data){
        $date = $cur_data['UF_DATE']->format('d.m.Y');
        if($date != '') {
            $temp_date = explode(' ', $date);

            if($cur_pos > 0){
                $best_data .= ';' . $temp_date[0] . ',' . $cur_data['UF_BEST_CSM_PRICE'];
            }else{
                $best_data = $temp_date[0] . ',' . $cur_data['UF_BEST_CSM_PRICE'];
            }

            //проверяем новые значения максимальной и минимальной дат
            /*$temp_arr_new = explode('.', $temp_date[0]);
            if(count($temp_arr_new) == 3){
                if(intval($temp_arr_new[2] . $temp_arr_new[1] . $temp_arr_new[0]) > intval($temp_arr_cur_max[2] . $temp_arr_cur_max[1] . $temp_arr_cur_max[0])){
                    //найдена новая большая дата
                    $max_data = $temp_date[0];
                    $temp_arr_cur_max = $temp_arr_new;
                }
                if(intval($temp_arr_new[2] . $temp_arr_new[1] . $temp_arr_new[0]) < intval($temp_arr_cur_min[2] . $temp_arr_cur_min[1] . $temp_arr_cur_min[0])){
                    //найдена новая меньшая дата
                    $min_data = $temp_date[0];
                    $temp_arr_cur_min = $temp_arr_new;
                }
            }*/

            $cur_pos++;
        }
    }

    //получение данных для графика "Мои цены"
    $cur_pos = 0;
    $data = log::_getEntitiesList(rrsIblock::HLgetIBlockId('COUNTEROFFERSLOG'), array(
        'UF_OFFER_ID'   => $_POST['offer_id'],
        '>UF_DATE'      => date('d.m.Y', $get_time)
    ), false, array(
            'UF_OFFER_ID'   => 'ASC',
            'UF_DATE'       => 'ASC'
        )
    );
    foreach($data as $cur_data){
        $date = $cur_data['UF_DATE']->format('d.m.Y');
        if($date != '') {
            $temp_date = explode(' ', $date);

            if ($cur_pos > 0) {
                $my_prices_data .= ';' . $temp_date[0] . ',' . $cur_data['UF_FARMER_PRICE'];
            } else {
                $my_prices_data = $temp_date[0] . ',' . $cur_data['UF_FARMER_PRICE'];
            }

            //проверяем новые значения максимальной и минимальной дат
            /*$temp_arr_new = explode('.', $temp_date[0]);
            if (count($temp_arr_new) == 3) {
                if (intval($temp_arr_new[2] . $temp_arr_new[1] . $temp_arr_new[0]) > intval($temp_arr_cur_max[2] . $temp_arr_cur_max[1] . $temp_arr_cur_max[0])) {
                    //найдена новая большая дата
                    $max_data = $temp_date[0];
                    $temp_arr_cur_max = $temp_arr_new;
                }
                if (intval($temp_arr_new[2] . $temp_arr_new[1] . $temp_arr_new[0]) < intval($temp_arr_cur_min[2] . $temp_arr_cur_min[1] . $temp_arr_cur_min[0])) {
                    //найдена новая меньшая дата
                    $min_data = $temp_date[0];
                    $temp_arr_cur_min = $temp_arr_new;
                }
            }*/
            $cur_pos++;
        }
    }

    //получение данных для графика "Рынок"
    $wh_list = array();
    $cultures_list = array();
    farmer::getWHAndCulturesByOffers($_POST['offer_id'], $wh_list, $cultures_list); //$wh_list и $cultures_list наполняются в этой функции

    //получение складов в текщих регионах и связанных регионах
    $wh_at_cur_regions = farmer::getWHAtCurrentRegion($wh_list);
    $wh_at_linked_regions = farmer::getWHAtLinkedRegions($wh_list);

    //получение списка окружающих складов (отдельно для текущих регионов, отдельно для связанных регионов, согласно задаче #12789)
    $wh_at_cur_regions_list = array();
    foreach ($wh_at_cur_regions as $cur_wh => $cur_arr){
        foreach ($cur_arr as $cur_wh_id){
            $wh_at_cur_regions_list[$cur_wh_id] = true;
        }
    }
    $wh_at_linked_regions_list = array();
    foreach ($wh_at_linked_regions as $cur_wh => $cur_arr){
        foreach ($cur_arr as $cur_wh_id){
            $wh_at_linked_regions_list[$cur_wh_id] = true;
        }
    }

    $cur_pos = 0;
    foreach($wh_at_linked_regions as $offer_wh => $rad_linked_list){
        foreach($rad_linked_list as $cur_linked_wh){
            if($cur_pos > 0){
                $wh_radwh_data .= ';' . (isset($wh_at_cur_regions_list[$cur_linked_wh]) ? 'n' : 'y') . ',' . $offer_wh . ',' . $cur_linked_wh;
            }else{
                $wh_radwh_data .= (isset($wh_at_cur_regions_list[$cur_linked_wh]) ? 'n' : 'y') . ',' . $offer_wh . ',' . $cur_linked_wh;
            }
            $cur_pos++;
        }
    }

    //получение средневзвешенных цен по складам с учетом культур и дат (в текущих регионах складов)
    $temp_deals_data = deal::getByWHAndCultures($wh_at_linked_regions_list, $cultures_list, $_POST['type_code'], $offerData['USER_NDS'] == 'yes', $_POST['offer_id']);
    unset($wh_list, $cultures_list, $wh_at_cur_regions, $wh_at_linked_regions, $wh_at_regions_list, $wh_at_linked_regions_list);

    //складываем полученные цены по датам (дата является уникальным ключом, т.к. культуры и склады у нас берутся только для выбранного товара)
    if(count($temp_deals_data) > 0){
        foreach ($temp_deals_data as $cur_warehouse => $cur_wh_data){
            //если склад находится в связанном регионе
            if(!isset($wh_at_cur_regions_list[$cur_warehouse ])) {
                foreach ($cur_wh_data as $cur_culture => $culture_data) {
                    foreach ($culture_data as $cur_date => $cur_price) {
                        $deals_linked_reg_arr[$cur_date][] = $cur_price;
                    }
                }
            }
            //если склад находится в текущем регионе
            else{
                foreach ($cur_wh_data as $cur_culture => $culture_data) {
                    foreach ($culture_data as $cur_date => $cur_price) {
                        $deals_cur_reg_arr[$cur_date][] = $cur_price;
                        $deals_linked_reg_arr[$cur_date][] = $cur_price;
                    }
                }
            }
        }
    }

    //высчитываем средневзвешенную цену для каждой даты
    if(count($deals_linked_reg_arr) > 0){
        $cur_pos = 0;
        foreach($deals_linked_reg_arr as $cur_date => $cur_prices){
            $temp_price = 0;
            foreach($cur_prices as $cur_price){
                $temp_price += $cur_price;
            }

            if(count($cur_prices) > 0) {
                if($cur_pos > 0) {
                    $deals_linked_reg_data .= ';' . $cur_date . ',' . round($temp_price / count($cur_prices));
                }else{
                    $deals_linked_reg_data = $cur_date . ',' . round($temp_price / count($cur_prices));
                }

                //проверяем новые значения максимальной и минимальной дат
                /*$temp_arr_new = explode('.', $cur_date);
                if (count($temp_arr_new) == 3) {
                    if (intval($temp_arr_new[2] . $temp_arr_new[1] . $temp_arr_new[0]) > intval($temp_arr_cur_max[2] . $temp_arr_cur_max[1] . $temp_arr_cur_max[0])) {
                        //найдена новая большая дата
                        $max_data = $cur_date;
                        $temp_arr_cur_max = $temp_arr_new;
                    }
                    if (intval($temp_arr_new[2] . $temp_arr_new[1] . $temp_arr_new[0]) < intval($temp_arr_cur_min[2] . $temp_arr_cur_min[1] . $temp_arr_cur_min[0])) {
                        //найдена новая меньшая дата
                        $min_data = $cur_date;
                        $temp_arr_cur_min = $temp_arr_new;
                    }
                }*/

                $cur_pos++;
            }
        }
        $cur_pos = 0;
        foreach($deals_cur_reg_arr as $cur_date => $cur_prices){
            $temp_price = 0;
            foreach($cur_prices as $cur_price){
                $temp_price += $cur_price;
            }

            if(count($cur_prices) > 0) {
                if($cur_pos > 0) {
                    $deals_cur_reg_data .= ';' . $cur_date . ',' . round($temp_price / count($cur_prices));
                }else{
                    $deals_cur_reg_data = $cur_date . ',' . round($temp_price / count($cur_prices));
                }

                //проверяем новые значения максимальной и минимальной дат
                /*$temp_arr_new = explode('.', $cur_date);
                if (count($temp_arr_new) == 3) {
                    if (intval($temp_arr_new[2] . $temp_arr_new[1] . $temp_arr_new[0]) > intval($temp_arr_cur_max[2] . $temp_arr_cur_max[1] . $temp_arr_cur_max[0])) {
                        //найдена новая большая дата
                        $max_data = $cur_date;
                        $temp_arr_cur_max = $temp_arr_new;
                    }
                    if (intval($temp_arr_new[2] . $temp_arr_new[1] . $temp_arr_new[0]) < intval($temp_arr_cur_min[2] . $temp_arr_cur_min[1] . $temp_arr_cur_min[0])) {
                        //найдена новая меньшая дата
                        $min_data = $cur_date;
                        $temp_arr_cur_min = $temp_arr_new;
                    }
                }*/

                $cur_pos++;
            }
        }
    }

    unset($deals_cur_reg_arr, $deals_linked_reg_arr);

    //получение набора дат от $min_data до $max_data
    //if($min_data != $max_data){
        $time_from = $get_time;
        $min_data = date('d.m.Y', $time_from);
        $time_to = $cur_time;
        $max_data = date('d.m.Y', $time_to);
        $days_val = ceil(($time_to - $time_from) / 86400) + 1; // + 1 для отображения на графике следующей даты

        $categories_data = $min_data . ';' . $max_data . ';' . $days_val . ';';

        $my_c = 0;
        for($i = 0; $i < $days_val; $i++){
            if($my_c > 0){
                $categories_data .= ',';
            }

            $categories_data .= date('d.m.Y', $time_from + $i * 86400);
            $my_c++;
        }
    //}

    echo json_encode(array(
        'cat_data' => $categories_data,
        'best_data' => $best_data,
        'my_prices_data' => $my_prices_data,
        'deals_cur' => $deals_cur_reg_data,
        'deals_linked' => $deals_linked_reg_data,
    ));
    exit;
}

echo 1; //ошибка получения данных
exit;