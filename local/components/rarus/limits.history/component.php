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
//проверка обязательных полей
if(isset($arParams['CLIENT_ID'])
    && filter_var($arParams['CLIENT_ID'], FILTER_VALIDATE_INT)
) {
    $user_id = $arParams['CLIENT_ID'];
}elseif(isset($arParams['FARMER_ID'])
    && filter_var($arParams['FARMER_ID'], FILTER_VALIDATE_INT)
){
    $user_id = $arParams['FARMER_ID'];
}else {
    $arResult['ERROR_MESSAGE'] = 'Не указан ID пользователя';
    return false;
}

//устанавливаем количество записей на странице
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

//проверка фильтрации по типу (для покупателя)
if(isset($arParams['CLIENT_ID'])) {
    if (isset($_GET['data_type'])
        && is_numeric($_GET['data_type'])
        && $_GET['data_type'] > 0
    ) {
        $params['data_type'] = array($_GET['data_type']);
    } else {
        //по умолчанию поулчаем данные из обоих вариантов
        $params['data_type'] = array(1, 2);
    }
}else{
    $params['data_type'] = array(3);
}

//получение данных для вывода
$items_data = client::limitsHistory($user_id, $params); //для всех типов получение идёт в одном месте
$arResult['ITEMS'] = (isset($items_data['ITEMS']) ? $items_data['ITEMS'] : array());
$arResult['CNT'] = (isset($items_data['CNT']) ? $items_data['CNT'] : 0);
unset($items_data, $params);

//дополнительно получаем комментарии к действиям в истории
if(count($arResult['ITEMS']) > 0){
    $elem_ids = array();
    foreach ($arResult['ITEMS'] as $cur_data){
        if($cur_data['ELEMENT_ID'] > 0) {
            $elem_ids[$cur_data['ELEMENT_ID']] = true;
        }
    }

    $ib_codes_arr = array();
    if(isset($arParams['CLIENT_ID'])){
        if(isset($params['data_type'])) {
            switch ($params['data_type']) {
                case 1:
                    //история принятий (комментарии)
                    $ib_codes_arr = array(
                        'counter_request_limits_changes',
                    );
                    break;

                case 2:
                    //история ограничений запросов (комментарии)
                    $ib_codes_arr = array(
                        'client_request_limits_changes',
                    );
                    break;
            }
        }

        //по умолчанию для покупателя берутся ограничения запросов и принятия (комментарии)
        if(count($ib_codes_arr) == 0){
            $ib_codes_arr = array(
                'counter_request_limits_changes',
                'client_request_limits_changes',
            );
        }
    }else{
        $ib_codes_arr = array(
            'farmer_offer_limits_changes',
        );
    }

    $arResult['COMMENTS'] = getHistoryLimitsComments(array_keys($elem_ids), false, $ib_codes_arr);
}

//пагинация
$nav = new \Bitrix\Main\UI\PageNavigation("page");
$nav->allowAllRecords(false)
    ->setPageSize($arParams['NEWS_COUNT'])
    ->initFromUri();
$nav->setRecordCount($arResult['CNT']);
$nav->setCurrentPage($current_page);
$arResult['NAV_OBJ'] = $nav;

//установка заголовка для страницы профиля
CModule::IncludeModule('iblock');
$res = CIBlockElement::GetList(
    array('ID' => 'ASC'),
    array(
        'IBLOCK_ID' => rrsIblock::getIBlockId('client_profile'),
        'ACTIVE' => 'Y',
        'PROPERTY_USER' => $user_id
    ),
    false, array('nTopCount' => 1),
    array('PROPERTY_FULL_COMPANY_NAME', 'PROPERTY_IP_FIO')
);
if ($data = $res->Fetch()) {
    if (isset($data['PROPERTY_FULL_COMPANY_NAME_VALUE']) && trim($data['PROPERTY_FULL_COMPANY_NAME_VALUE']) != '') {
        $APPLICATION->SetTitle($data['PROPERTY_FULL_COMPANY_NAME_VALUE']);
    }
    elseif (isset($data['PROPERTY_IP_FIO_VALUE']) && trim($data['PROPERTY_IP_FIO_VALUE']) != '') {
        $APPLICATION->SetTitle('ИП ' . $data['PROPERTY_IP_FIO_VALUE']);
    }
}

//получение почты пользователя
$res = CUser::GetList(
    ($by = 'id'), ($order = 'desc'),
    array('ID' => $user_id),
    array('SELECT' => array('EMAIL'))
);
while($data = $res->Fetch()){
    $arResult['EMAIL'] = $data['EMAIL'];
}

$this->includeComponentTemplate();