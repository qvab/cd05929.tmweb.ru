<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$obElement = new CIBlockElement;

$users_limits = array();
$arRequestId = array();
$user_id = array();
$arResult['MODE'] = 'client';
if(isset($arParams['CLIENT_ID'])
    && is_numeric($arParams['CLIENT_ID'])
){
    $user_id = $arParams['CLIENT_ID'];
}elseif(isset($arParams['AGENT_ID'])
    && is_numeric($arParams['AGENT_ID'])
){
    $arResult['MODE'] = 'agent';
    $agent_obj = new agent();
    $arResult['CLIENT_LIST'] = $agent_obj->getClientsForSelect($arParams['AGENT_ID']);
    if((sizeof($arResult['CLIENT_LIST']))&&(is_array($arResult['CLIENT_LIST']))){
        $arFilter = array(
            'IBLOCK_ID' => rrsIblock::getIBlockId('client_profile'),
            'ACTIVE' => 'Y',
            'PROPERTY_USER' => array_keys($arResult['CLIENT_LIST'])
        );
        $res = $obElement->GetList(
            array('ID' => 'ASC'),
            $arFilter,
            false,
            false,
            array('ID', 'PROPERTY_USER', 'PROPERTY_COUNTER_REQUEST_LIMIT', 'PROPERTY_PARTNER_CONTRACT')
        );
        while ($arRow = $res->Fetch()) {
            $limit = $arRow['PROPERTY_COUNTER_REQUEST_LIMIT_VALUE'];
            if(empty($limit)){
                $limit = 0;
            }
            $users_limits[$arRow['PROPERTY_USER_VALUE']] = $limit;
        }
    }
    if(count($arResult['CLIENT_LIST']) > 0){
        $user_id = array_keys($arResult['CLIENT_LIST']);
        foreach ($arResult['CLIENT_LIST'] as $id=>$fields){
            if(array_key_exists($id,$users_limits)){
                $arResult['CLIENT_LIST'][$id]['LIMIT'] = $users_limits[$id];
            }else{
                $arResult['CLIENT_LIST'][$id]['LIMIT'] = 0;
            }
        }
    }

    $arResult['AGENT_CONTRACT'] = partner::getUsersContractsForPartner(array_keys($arResult['CLIENT_LIST']));
}


$arResult['COUNTER_REGION_DATA_TOT'] = 0;
/*if(isset($arParams['CLIENT_ID'])
    && is_numeric($arParams['CLIENT_ID'])
) {
    //получение регионов
    $arResult['REGIONS_LIST'] = array();
    $REGIONS = client::getClientRegionsByCOUNTEROFFERS($arParams['CLIENT_ID']);
    $arResult['REGIONS_LIST'] = $REGIONS['REGIONS'];
    $arResult['COUNTER_REGION_DATA_TOT'] = $REGIONS['ALL_COUNT_CP'];

}else*/if(isset($arParams['AGENT_ID'])&&(sizeof($user_id))){
    $arResult['REGIONS_LIST'] = array();
    $REGIONS = client::getClientRegionsByCOUNTEROFFERS($user_id);

    $arResult['REGIONS_LIST'] = $REGIONS['REGIONS'];
    $arResult['COUNTER_REGION_DATA_TOT'] = $REGIONS['ALL_COUNT_CP'];
    $arResult['WH_TO_REGION'] = $REGIONS['WH_TO_REGION'];

    unset($REGIONS);
}


$arRequestList = client::getRequestListByUser($user_id);
$arRequestCultures = array(); //данные культуры для подсчета встречных предложений в фильтре
foreach($arRequestList as $cur_req){
    $arRequestId[] = $cur_req['request_id'];
    $arRequestCultures[$cur_req['request_id']] = $cur_req['culture_id'];
}

$arCultureId    = [];
$arWarehouseId  = [];

if(!empty($arRequestId)) {
    // ИД складов, ИД культур
    $rs = $obElement->GetList(
        array(),
        array(
            'IBLOCK_ID'         => rrsIblock::getIBlockId('client_request_cost'),
            'PROPERTY_REQUEST'  => $arRequestId,
        ),
        false,
        false,
        array(
            'ID',
            'PROPERTY_CULTURE',
            'PROPERTY_WAREHOUSE',
            'PROPERTY_REQUEST',
        )
    );

    while ($arRow = $rs->Fetch()) {

        if(!empty($arRow['PROPERTY_CULTURE_VALUE'])) {
            $arCultureId[$arRow['PROPERTY_CULTURE_VALUE']]          = $arRow['PROPERTY_CULTURE_VALUE'];
        }
    }
}


// Список культур
$arResult['CULTURE_LIST'] = [];
if(!empty($arCultureId)) {

    $re = $obElement->GetList(
        ['NAME' => 'ASC'],
        [
            'IBLOCK_ID' => rrsIblock::getIBlockId('cultures'),
            'ID'        => array_values($arCultureId),
        ],
        false,
        false,
        array('ID', 'NAME')
    );

    while($arRow = $re->Fetch()) {
        $arResult['CULTURE_LIST'][$arRow['ID']] = [
            'ID'    => $arRow['ID'],
            'NAME'  => $arRow['NAME'],
        ];
    }
}

// Список складов
$arResult['WAREHOUSE_LIST'] = [];

//данные для вывода количества встречных предложений
$arResult['COUNTER_CULTURE_DATA'] = array();
$arResult['COUNTER_USER_DATA_TOT'] = 0;
$arResult['COUNTER_CULTURE_DATA_TOT'] = 0;
$arResult['COUNTER_WH_DATA_TOT'] = 0;
$arResult['COUNTER_WH_DATA'] = array();
$arResult['COUNTER_USER_DATA'] = array();
$arResult['COUNTER_REGION_DATA'] = array();
$checked_region = (isset($_GET['region_id']) && is_numeric($_GET['region_id']) && $_GET['region_id'] > 0
    ? $_GET['region_id']
    : 0);
$checked_uid = (isset($_GET['client_id']) && is_numeric($_GET['client_id']) && $_GET['client_id'] > 0
    ? $_GET['client_id']
    : 0);
$checked_culture = (isset($_GET['culture_id']) && is_numeric($_GET['culture_id']) && $_GET['culture_id'] > 0
    ? $_GET['culture_id']
    : 0);
$checked_wh = (isset($_GET['warehouse_id']) && is_numeric($_GET['warehouse_id']) && $_GET['warehouse_id'] > 0
    ? $_GET['warehouse_id']
    : 0);

//if(!empty($arWarehouseId)) {

    $arFilterPart = array(
        'LOGIC' => 'OR',
        array(//корректное значение фильтра - активные склады
            'ACTIVE' => 'Y',
            'PROPERTY_ACTIVE' => rrsIblock::getPropListKey('client_warehouse', 'ACTIVE', 'yes')
        )
    );

    //временно ставим иную логику вывода складов - выводим активные склады, а также склады по которым есть встречные предложения
    $counter_wh_list = array();
    $counter_reqs = client::getCounterRequestData($user_id);

    foreach($counter_reqs as $cur_count_req){
        $counter_wh_list[$cur_count_req['UF_CLIENT_WH_ID']] = true;

        //расчёт количетсва ВП по культурам и складам
        if(isset($arRequestCultures[$cur_count_req['UF_REQUEST_ID']])) {

            if (isset($arResult['COUNTER_WH_DATA'][$cur_count_req['UF_CLIENT_WH_ID'] . '_' . $arRequestCultures[$cur_count_req['UF_REQUEST_ID']]])) {
                $arResult['COUNTER_WH_DATA'][$cur_count_req['UF_CLIENT_WH_ID'] . '_' . $arRequestCultures[$cur_count_req['UF_REQUEST_ID']]]['CNT'] += 1;
            } else {
                $arResult['COUNTER_WH_DATA'][$cur_count_req['UF_CLIENT_WH_ID'] . '_' . $arRequestCultures[$cur_count_req['UF_REQUEST_ID']]] = array(
                    'UID' => $cur_count_req['UF_CLIENT_ID'],
                    'CNT' => 1,
                    'WH' => $cur_count_req['UF_CLIENT_WH_ID'],
                    'CULTURE' => $arRequestCultures[$cur_count_req['UF_REQUEST_ID']],
                    'REGION' => ($arResult['WH_TO_REGION'][$cur_count_req['UF_CLIENT_WH_ID']] ? $arResult['WH_TO_REGION'][$cur_count_req['UF_CLIENT_WH_ID']] : 0)
                );
            }

            //подсчет общих количеств ВП для пользователей, культур и складов
            if ($checked_region == 0
                ||
                ($checked_region > 0
                    && isset($arResult['WH_TO_REGION'][$cur_count_req['UF_CLIENT_WH_ID']])
                    && $checked_region == $arResult['WH_TO_REGION'][$cur_count_req['UF_CLIENT_WH_ID']]
                )
            ) {
                if (isset($arResult['COUNTER_USER_DATA'][$cur_count_req['UF_CLIENT_ID']])) {
                    $arResult['COUNTER_USER_DATA'][$cur_count_req['UF_CLIENT_ID']] += 1;
                } else {
                    $arResult['COUNTER_USER_DATA'][$cur_count_req['UF_CLIENT_ID']] = 1;
                }
                //рассчет количества ВП для пользователей
                $arResult['COUNTER_USER_DATA_TOT']++;
                //рассчет количества ВП для культур и для складов
                if ($checked_uid == 0
                    ||
                    ($checked_uid > 0
                        && $checked_uid == $cur_count_req['UF_CLIENT_ID']
                    )
                ) {
                    $arResult['COUNTER_CULTURE_DATA_TOT']++;
                    if ($checked_culture == 0
                        ||
                        ($checked_culture > 0
                            && $checked_culture == $arRequestCultures[$cur_count_req['UF_REQUEST_ID']]
                        )
                    ) {
                        $arResult['COUNTER_WH_DATA_TOT']++;
                    }
                }
            }
        }
    }

    if(count($counter_wh_list) > 0){
        $arFilterPart[] = array('ID' => array_keys($counter_wh_list));
    }

    $re = $obElement->GetList(
        ['NAME' => 'ASC'],
        [
            'IBLOCK_ID' => rrsIblock::getIBlockId('client_warehouse'),
            'ID'        => array_values($arWarehouseId),
            'PROPERTY_CLIENT' => $user_id,
            //временно ставим иную логику вывода складов - активные склады, а также склады по которым есть встречные предложения
            $arFilterPart,
        ],
        false,
        false,
        array('ID', 'NAME')
    );
    while($arRow = $re->Fetch()) {
        $arResult['WAREHOUSE_LIST'][$arRow['ID']] = [
            'ID'    => $arRow['ID'],
            'NAME'  => $arRow['NAME'],
        ];
    }
//}

// Флаг вывода формы фильтра
$arResult['SHOW_FORM'] = (!empty($arResult['CULTURE_LIST']) || !empty($arResult['WAREHOUSE_LIST']));
if(isset($arParams['AGENT_ID'])
    && is_numeric($arParams['AGENT_ID'])
){
    if(empty($arResult['CLIENT_LIST'])){$arResult['SHOW_FORM'] = false;}
}

