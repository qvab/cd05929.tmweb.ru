<?php
/**
 * Created by PhpStorm.
 * User: bragev
 * Date: 06.04.2018
 * Time: 14:07
 */


if(isset($_POST['search_query'])){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, 'https://geocode-maps.yandex.ru/1.x/?format=json&geocode='.urlencode($_POST['search_query']));
    $content = curl_exec($ch);
    echo $content;

    //регистрируем количество запросов к апи яндекс карт
    $cur_cnt = 0;
    $cur_date = date('Y-m-d H:i:s');
    $cur_day = explode(' ', $cur_date);
    //получаем старую запись
    if(file_exists($_SERVER['DOCUMENT_ROOT'] . '/farmer/warehouses/yandex_maps_today.txt')){
        $file_data = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/farmer/warehouses/yandex_maps_today.txt');
        $file_cnt = explode(':', $file_data, 2);
        if(isset($file_cnt[0])
            && filter_var($file_cnt[0], FILTER_VALIDATE_INT)
            && $file_cnt[0] > 0
        ){
            $cur_cnt = $file_cnt[0];
        }

        //проверяем если наступил новый день, то записываем старые данные в отчетный файл
        $check_day = $file_cnt[1];
        if($cur_day[0] != $check_day){
            file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/farmer/warehouses/yandex_maps_daily.txt', "{$cur_cnt}:{$check_day}\n", FILE_APPEND); //лог кликов для каждого дня
            file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/farmer/warehouses/yandex_maps_today.txt', ''); //счетчик за сегодня
            file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/farmer/warehouses/yandex_maps_today_list.txt', ''); //данные за сегодня
            $cur_cnt = 0;
        }
    }

    //записываем новые данные
    $cur_cnt++;
    file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/farmer/warehouses/yandex_maps_today.txt', "{$cur_cnt}:{$cur_day[0]}");
    file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/farmer/warehouses/yandex_maps_today_list.txt', "{$cur_cnt}:{$cur_date}\n", FILE_APPEND);
}


