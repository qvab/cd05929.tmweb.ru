<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
/** @var CBitrixComponent $this */
/** @var array $arParams */
/** @var array $arResult */
/** @var string $componentPath */
/** @var string $componentName */
/** @var string $componentTemplate */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

/** @global CIntranetToolbar $INTRANET_TOOLBAR */
global $INTRANET_TOOLBAR;
CPageOption::SetOptionString("main", "nav_page_in_session", "N");

$arResult['ERROR_MESSAGE'] = '';

$user_id = 0;
if(isset($_GET['uid'])
    && filter_var($_GET['uid'], FILTER_VALIDATE_INT)
){
    $user_id = $_GET['uid'];
}

//устанавливаем количество запсией на странице
$arParams['NEWS_COUNT'] = (isset($arParams['NEWS_COUNT'])
    && filter_var($arParams['NEWS_COUNT'], FILTER_VALIDATE_INT) > 0
    && $arParams['NEWS_COUNT'] < 501
    ? $arParams['NEWS_COUNT']
    : 30);

$params = array();
$current_page = 1;
//показ нужной страницы пагинации
if(isset($_GET['page'])
    && filter_var($_GET['page'], FILTER_VALIDATE_INT) > 1
){
    $params['offset'] = ($_GET['page'] - 1) * $arParams['NEWS_COUNT'];
    $current_page = $_GET['page'];
}

$params['limit'] = $arParams['NEWS_COUNT'];
$params['order'] = array('UF_DATE' => 'DESC');
$params['by_admin'] = 'y';

//применение фильтра по дате
$date_from = '';
$date_to = '';
if(isset($_GET['date_from'])){
    $temp_val = explode('.', $_GET['date_from']);
    if(isset($temp_val['2'])
        && is_numeric($temp_val['2']) && mb_strlen($temp_val['2']) == 4
        && is_numeric($temp_val['1']) && mb_strlen($temp_val['1']) == 2
        && is_numeric($temp_val['0']) && mb_strlen($temp_val['0']) == 2
    ){
        $date_from = "{$temp_val['2']}.{$temp_val['1']}.{$temp_val['0']}";
    }
}
if(isset($_GET['date_to'])){
    $temp_val = explode('.', $_GET['date_to']);
    if(isset($temp_val['2'])
        && is_numeric($temp_val['2']) && mb_strlen($temp_val['2']) == 4
        && is_numeric($temp_val['1']) && mb_strlen($temp_val['1']) == 2
        && is_numeric($temp_val['0']) && mb_strlen($temp_val['0']) == 2
    ){
        $date_to= "{$temp_val['2']}.{$temp_val['1']}.{$temp_val['0']}";
    }
}
//проверка какая дата больше, если установлены обе и перепутаны, то меняем местами
if($date_from != ''
    && $date_to != ''
    && intval(str_replace('.', '', $date_from)) > intval(str_replace('.', '', $date_to))
){
    $temp_val = $date_from;
    $date_from = $date_to;
    $date_to = $temp_val;
}

if($date_from != '') {
    $params['filter']['>=UF_DATE'] = ConvertTimeStamp(MakeTimeStamp($date_from . ' 00:00:00', 'YYYY.MM.DD HH:MI:SS'), 'FULL');
}

if($date_to != '') {
    $params['filter']['<=UF_DATE'] = ConvertTimeStamp(MakeTimeStamp($date_to . ' 23:59:59', 'YYYY.MM.DD HH:MI:SS'), 'FULL');
}

//пока ставим админу только данные по принятиям
$params['data_type'] = array(1);

//получение данных для вывода
$items_data = client::limitsHistory($user_id, $params);
$arResult['ITEMS'] = (isset($items_data['ITEMS']) ? $items_data['ITEMS'] : array());
$arResult['CNT'] = (isset($items_data['CNT']) ? $items_data['CNT'] : 0);

//получение данных пользователей
if(count($arResult['ITEMS']) > 0){
    $uids = array();
    foreach($arResult['ITEMS'] as $cur_data){
        $uids[$cur_data['UID']] = true;
    }

    if(count($uids) > 0){
        $res = CUser::GetList(
            ($sort = 'id'), ($order = 'asc'),
            array('GROUPS_ID' => 9, 'ID' => implode(' | ', array_keys($uids)),
            array('SELECT' => array('ID', 'EMAIL', 'NAME', 'LAST_NAME', 'SECOND_NAME')))
        );
        $uids = array();
        while($data = $res->Fetch()) {
            if($data['EMAIL'] != '') {
                $arResult['USERS'][$data['ID']] = trim($data['EMAIL']) . ' [' . $data['ID'] . ']';
            }else{
                $temp_val = trim($data['NAME'] . ' ' . $data['LAST_NAME']);
                if($temp_val != ''){
                    $arResult['USERS'][$data['ID']] = $temp_val . ' [' . $data['ID'] . ']';
                }else {
                    //дополнительно получаем данные пользователей, у которых нет email и имени
                    $uids[$data['ID']] = true;
                }
            }
        }

        //дополнительно получаем данные пользователей, у которых нет email и имени
        if(count($uids) > 0){
            CModule::IncludeModule('iblock');
            $el_obj = new CIBlockElement();
            $res = $el_obj->GetList(
                array('ID' => 'ASC'),
                array(
                    'IBLOCK_ID' => rrsIblock::getIBlockId('client_profile'),
                    'PROPERTY_USER' => array_keys($uids),
                ),
                false,
                false,
                array('PROPERTY_USER', 'PROPERTY_PHONE', 'PROPERTY_FULL_COMPANY_NAME')
            );
            while($data = $res->Fetch()){
                $temp_val = trim($data['PROPERTY_FULL_COMPANY_NAME_VALUE']);
                if($temp_val != ''){
                    $arResult['USERS'][$data['PROPERTY_USER_VALUE']] = $temp_val . ' [' . $data['PROPERTY_USER_VALUE'] . ']';
                }else{
                    $temp_val = trim($data['PROPERTY_PHONE_VALUE']);
                    if($temp_val != ''){
                        $arResult['USERS'][$data['PROPERTY_USER_VALUE']] = $temp_val . ' [' . $data['PROPERTY_USER_VALUE'] . ']';
                    }else{
                        $arResult['USERS'][$data['PROPERTY_USER_VALUE']] = $data['PROPERTY_USER_VALUE'];
                    }
                }
            }
        }
    }
}

unset($items_data, $params, $uids);

//дополнительно получаем комментарии к действиям в истории
if(count($arResult['ITEMS']) > 0){
    $elem_ids = array();
    foreach ($arResult['ITEMS'] as $cur_data){
        $elem_ids[$cur_data['ELEMENT_ID']] = true;
    }

    $ib_codes_arr = array(
        'counter_request_limits_changes',
//        'client_request_limits_changes',
//        'farmer_offer_limit_feedback',
    );
    $arResult['COMMENTS'] = getHistoryLimitsComments(array_keys($elem_ids), 'admin', $ib_codes_arr);
}

//пагинация
$nav = new \Bitrix\Main\UI\PageNavigation("page");
$nav->allowAllRecords(false)
    ->setPageSize($arParams['NEWS_COUNT'])
    ->initFromUri();
$nav->setRecordCount($arResult['CNT']);
$nav->setCurrentPage($current_page);
$arResult['NAV_OBJ'] = $nav;

$this->includeComponentTemplate();