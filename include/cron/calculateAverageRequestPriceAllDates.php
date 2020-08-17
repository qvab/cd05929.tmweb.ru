<?
//получение средней цены на год по дням активности запросов

if(empty($_SERVER['SHELL']))
    die();

set_time_limit(12000);
//боевой
$_SERVER["DOCUMENT_ROOT"] = "/home/bitrix/www";
//песочница
//$_SERVER["DOCUMENT_ROOT"] = "/www/unorganized_projects/agrohelper/sandboxes/bragev/www";
//$_SERVER['SERVER_NAME'] = 'bragev.agrohelper.old.rrsdev.ru';

define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS",true);

flush();
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
ob_end_flush();

CModule::IncludeModule('iblock');
CModule::IncludeModule('highloadblock');

global $DB;
$el = new CIBlockElement;

$nds_val = rrsIblock::getConst('nds');
$wh_regions = array();
$clients_nds = array();
$res = CIBlockElement::GetList(
    array('ID' => 'ASC'),
    array(
        'IBLOCK_ID' => rrsIblock::getIBlockId('client_warehouse'),
        'ACTIVE' => 'Y',
    ),
    false,
    false,
    array(
        'ID',
        'PROPERTY_REGION',
        'PROPERTY_CLIENT',
    )
);
while ($ob = $res->Fetch()) {
    $wh_regions[$ob['ID']] = $ob['PROPERTY_REGION_VALUE'];
    $clients_nds[$ob['PROPERTY_CLIENT_VALUE']] = false;
}

//получаем типы НДС пользователей
if(count($clients_nds) > 0){
    $res = $el->GetList(
        array('ID' => 'ASC'),
        array(
            'IBLOCK_ID' => rrsIblock::getIBlockId('client_profile'),
            'PROPERTY_USER' => array_keys($clients_nds)
        ),
        false,
        false,
        array('PROPERTY_USER', 'PROPERTY_NDS.CODE')
    );
    while($data = $res->Fetch()){
        if(isset($clients_nds[$data['PROPERTY_USER_VALUE']])){
            $clients_nds[$data['PROPERTY_USER_VALUE']] = ($data['PROPERTY_NDS_CODE'] == 'Y');
        }
    }
}

//получаем данные изменения запроса
$request_dates = array();
$res = $el->Getlist(
    array('ID' => 'ASC'),
    array(
        'IBLOCK_ID' => rrsIblock::getIBlockId('client_request'),
        //'ACTIVE' => 'Y',
        '>DATE_CREATE' => date('d.m.Y H:i:s', strtotime('-1 year')), //прошедший год
    ),
    false,
    false,
    array('ID','ACTIVE','DATE_CREATE', 'TIMESTAMP_X', 'PROPERTY_ACTIVE')
);


$active_yes = rrsIblock::getPropListKey('client_request', 'ACTIVE', 'yes');
$active_no = rrsIblock::getPropListKey('client_request', 'ACTIVE', 'no');
//$nds_yes = rrsIblock::getPropListKey('client_request', 'USER_NDS', 'yes');
//$nds_no = rrsIblock::getPropListKey('client_request', 'USER_NDS', 'no');
$now = date('d.m.Y');
while($tmp = $res->Fetch()){
    $nds = 0;
    if($tmp['PROPERTY_ACTIVE_ENUM_ID'] == $active_yes){
        $date_start = date('d.m.Y', strtotime($tmp['DATE_CREATE']));
        $date_end = $now;
    }elseif($tmp['PROPERTY_ACTIVE_ENUM_ID'] == $active_no){
        $date_start = date('d.m.Y', strtotime($tmp['DATE_CREATE']));
        $date_end = date('d.m.Y', strtotime($tmp['TIMESTAMP_X']));
    }
    /*if($tmp['PROPERTY_USER_NDS_ENUM_ID'] == $nds_yes){
        $nds = 1;
    }*/
    $request_dates[$tmp['ID']] = array(
        'date_start'=>$date_start,
        'date_end'=>$date_end,
        //'nds'=>$nds
    );
}

//p($request_dates);

//получаем данные стоимостей
$request_prices = array();
$res = $el->Getlist(
    array('ID' => 'ASC'),
    array(
        'IBLOCK_ID' => rrsIblock::getIBlockId('client_request_cost'),
        'ACTIVE' => 'Y',
        '>DATE_CREATE' => date('d.m.Y H:i:s', strtotime('-1 year')), //прошедший год
    ),
    false,
    false,
    array('DATE_CREATE', 'PROPERTY_WAREHOUSE', 'PROPERTY_WAREHOUSE.PROPERTY_CLIENT', 'PROPERTY_PRICE', 'PROPERTY_CULTURE', 'PROPERTY_REQUEST')
);
while($tmp = $res->Fetch()){
    if(isset($wh_regions[$tmp['PROPERTY_WAREHOUSE_VALUE']])
        && $wh_regions[$tmp['PROPERTY_WAREHOUSE_VALUE']]
        && isset($clients_nds[$tmp['PROPERTY_WAREHOUSE_PROPERTY_CLIENT_VALUE']])
    ){
        $date = date('d.m.Y', strtotime($tmp['DATE_CREATE']));
        if(array_key_exists($tmp['PROPERTY_REQUEST_VALUE'],$request_dates)){
            $dates = $request_dates[$tmp['PROPERTY_REQUEST_VALUE']];
            $tmp['date_start'] = $dates['date_start'];
            $tmp['date_end'] = $dates['date_end'];
            $tmp['nds'] = $clients_nds[$tmp['PROPERTY_WAREHOUSE_PROPERTY_CLIENT_VALUE']]; // true|false
            $startTime = strtotime($dates['date_start']);
            $endTime = strtotime($dates['date_end']);
            $date_array = array();
            while($startTime<=$endTime){
                $addDate = date('d.m.Y',$startTime);
                $request_prices[$addDate][$tmp['PROPERTY_CULTURE_VALUE']][$wh_regions[$tmp['PROPERTY_WAREHOUSE_VALUE']]][] = $tmp;
                $startTime+=86400;
            }
        }
    }
}

//p($request_prices);

$request_average_price = array();

//добавляем данные в HL блок
foreach ($request_prices as $date=>$cult_items){
    foreach ($cult_items as $culture_id=>$reg_items){
        foreach ($reg_items as $region_id=>$price_items){
            $price_count = 0;
            $price_all = 0;
            foreach ($price_items as $item){
                $price_count++;
                //храним всегда цену без НДС
                $tmp_price = $item['PROPERTY_PRICE_VALUE'];
                if($item['nds']){
                    //вычитаем НДС из цены
                    $tmp_price = $tmp_price / (1 + 0.01 * $nds_val);
                }

                $price_all+=$tmp_price;
            }
            if((!empty($price_count))&&(!empty($price_all))){
                $average_price = round($price_all/$price_count,2);
                $request_average_price[$date][] = array(
                    'culture_id' => $culture_id,
                    'region_id' => $region_id,
                    'price' => $average_price,
                    //'nds' => $item['nds']
                );
            }
        }
    }
}

$hlIblockId = log::getIdByName('REQUESTAVERAGEPRICES');
if((sizeof($request_average_price))&&(is_array($request_average_price))){
    foreach ($request_average_price as $date=>$items){
        foreach ($items as $item){
            $add = array(
                'UF_DATE' => $DB->FormatDate(date("d.m.Y", strtotime($date)), "DD.MM.YYYY", FORMAT_DATE),
                'UF_REGION_ID' => $item['region_id'],
                'UF_CULTURE_ID' => $item['culture_id'],
                'UF_PRICE' => $item['price'],
            );
            $res = log::_createEntity($hlIblockId, $add);
        }
    }
}
?>