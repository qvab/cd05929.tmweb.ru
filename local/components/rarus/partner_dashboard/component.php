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
global $APPLICATION;

$filter = array();
$arResult = array('AGENTS' => array());

//проверка параметров
if(!isset($arParams['UID'])){
    $APPLICATION->ThrowException('Не указан ID пользователя.');
    return false;
}
if(!isset($arParams['AGENT_ID'])){
    $APPLICATION->ThrowException('Должно быть задано значение параметра по умолчанию.');
    return false;
}

//установка фильтрации для getDashboardData
$filter['PARTNER_ID'] = $arParams['UID'];
$filter['AGENT_ID'] = $arParams['AGENT_ID'];

//установка настроек для ссылок на детальную страницу
$arResult['URI_PARAMS'] = array();
if($arParams['AGENT_ID'] != 0){
    $arResult['URI_PARAMS'][] = 'agent_id=' . $arParams['AGENT_ID'];
}

$d_obj = new dashboardP();
$arResult['DATA'] = $d_obj->getDashboardData($filter);

//Высчитываем проценты для вчера/неделю назад
//Для АП
$arResult['PERCENTS']['FARMERS_DATA_MAIN']['NOT_DEMO_YESTERDAY'] = percentDiffSign(
    $arResult['DATA']['FARMERS_DATA_MAIN']['NOT_DEMO_YESTERDAY'],
    $arResult['DATA']['FARMERS_DATA_MAIN']['NOT_DEMO_TODAY']
);
$arResult['PERCENTS']['FARMERS_DATA_MAIN']['NOT_DEMO_WEEK_AGO'] = percentDiffSign(
    $arResult['DATA']['FARMERS_DATA_MAIN']['NOT_DEMO_WEEK_AGO'],
    $arResult['DATA']['FARMERS_DATA_MAIN']['NOT_DEMO_TODAY']
);

$arResult['PERCENTS']['FARMERS_DATA_MAIN']['DEMO_YESTERDAY'] = percentDiffSign(
    $arResult['DATA']['FARMERS_DATA_MAIN']['DEMO_YESTERDAY'],
    $arResult['DATA']['FARMERS_DATA_MAIN']['DEMO_TODAY']
);
$arResult['PERCENTS']['FARMERS_DATA_MAIN']['DEMO_WEEK_AGO'] = percentDiffSign(
    $arResult['DATA']['FARMERS_DATA_MAIN']['DEMO_WEEK_AGO'],
    $arResult['DATA']['FARMERS_DATA_MAIN']['DEMO_TODAY']
);

$arResult['PERCENTS']['FARMERS_DATA_MAIN']['TOTAL_YESTERDAY'] = percentDiffSign(
    $arResult['DATA']['FARMERS_DATA_MAIN']['TOTAL_YESTERDAY'],
    $arResult['DATA']['FARMERS_DATA_MAIN']['TOTAL_TODAY']
);
$arResult['PERCENTS']['FARMERS_DATA_MAIN']['TOTAL_WEEK_AGO'] = percentDiffSign(
    $arResult['DATA']['FARMERS_DATA_MAIN']['TOTAL_WEEK_AGO'],
    $arResult['DATA']['FARMERS_DATA_MAIN']['TOTAL_TODAY']
);

$arResult['PERCENTS']['FARMERS_DATA_NO_OFFERS']['YESTERDAY'] = percentDiffSign(
    $arResult['DATA']['FARMERS_DATA_NO_OFFERS']['YESTERDAY'],
    $arResult['DATA']['FARMERS_DATA_NO_OFFERS']['TODAY']
);
$arResult['PERCENTS']['FARMERS_DATA_NO_OFFERS']['WEEK_AGO'] = percentDiffSign(
    $arResult['DATA']['FARMERS_DATA_NO_OFFERS']['WEEK_AGO'],
    $arResult['DATA']['FARMERS_DATA_NO_OFFERS']['TODAY']
);


//Для покупателей
$arResult['PERCENTS']['CLIENTS_DATA_MAIN']['NOT_DEMO_YESTERDAY'] = percentDiffSign(
    $arResult['DATA']['CLIENTS_DATA_MAIN']['NOT_DEMO_YESTERDAY'],
    $arResult['DATA']['CLIENTS_DATA_MAIN']['NOT_DEMO_TODAY']
);
$arResult['PERCENTS']['CLIENTS_DATA_MAIN']['NOT_DEMO_WEEK_AGO'] = percentDiffSign(
    $arResult['DATA']['CLIENTS_DATA_MAIN']['NOT_DEMO_WEEK_AGO'],
    $arResult['DATA']['CLIENTS_DATA_MAIN']['NOT_DEMO_TODAY']
);

$arResult['PERCENTS']['CLIENTS_DATA_MAIN']['DEMO_YESTERDAY'] = percentDiffSign(
    $arResult['DATA']['CLIENTS_DATA_MAIN']['DEMO_YESTERDAY'],
    $arResult['DATA']['CLIENTS_DATA_MAIN']['DEMO_TODAY']
);
$arResult['PERCENTS']['CLIENTS_DATA_MAIN']['DEMO_WEEK_AGO'] = percentDiffSign(
    $arResult['DATA']['CLIENTS_DATA_MAIN']['DEMO_WEEK_AGO'],
    $arResult['DATA']['CLIENTS_DATA_MAIN']['DEMO_TODAY']
);

$arResult['PERCENTS']['CLIENTS_DATA_MAIN']['TOTAL_YESTERDAY'] = percentDiffSign(
    $arResult['DATA']['CLIENTS_DATA_MAIN']['TOTAL_YESTERDAY'],
    $arResult['DATA']['CLIENTS_DATA_MAIN']['TOTAL_TODAY']
);
$arResult['PERCENTS']['CLIENTS_DATA_MAIN']['TOTAL_WEEK_AGO'] = percentDiffSign(
    $arResult['DATA']['CLIENTS_DATA_MAIN']['TOTAL_WEEK_AGO'],
    $arResult['DATA']['CLIENTS_DATA_MAIN']['TOTAL_TODAY']
);

$arResult['PERCENTS']['CLIENTS_DATA_NO_REQUESTS']['YESTERDAY'] = percentDiffSign(
    $arResult['DATA']['CLIENTS_DATA_NO_REQUESTS']['YESTERDAY'],
    $arResult['DATA']['CLIENTS_DATA_NO_REQUESTS']['TODAY'],
    'reverse_'
);
$arResult['PERCENTS']['CLIENTS_DATA_NO_REQUESTS']['WEEK_AGO'] = percentDiffSign(
    $arResult['DATA']['CLIENTS_DATA_NO_REQUESTS']['WEEK_AGO'],
    $arResult['DATA']['CLIENTS_DATA_NO_REQUESTS']['TODAY'],
    'reverse_'
);


//Для транспортных компаний
$arResult['PERCENTS']['TRANSPORT_DATA']['YESTERDAY'] = percentDiffSign(
    $arResult['DATA']['TRANSPORT_DATA']['YESTERDAY'],
    $arResult['DATA']['TRANSPORT_DATA']['TODAY']
);
$arResult['PERCENTS']['TRANSPORT_DATA']['WEEK_AGO'] = percentDiffSign(
    $arResult['DATA']['TRANSPORT_DATA']['WEEK_AGO'],
    $arResult['DATA']['TRANSPORT_DATA']['TODAY']
);


//получаем список агентов покупателей для фильтра
CModule::IncludeModule('iblock');
$el_obj = new CIBlockElement;
$res = $el_obj->GetList(
    array('ID' => 'ASC'),
    array(
        'IBLOCK_ID'             => rrsIblock::getIBlockId('client_agent_partner_link'),
        'ACTIVE'                => 'Y',
        'PROPERTY_PARTNER_ID'   => $arParams['UID']
    ),
    false,
    false,
    array('PROPERTY_USER_ID')
);
while($data = $res->Fetch()) {
    $arResult['AGENTS'][$data['PROPERTY_USER_ID_VALUE']] = '';
}
//получаем список агентов АП для фильтра
$res = $el_obj->GetList(
    array('ID' => 'ASC'),
    array(
        'IBLOCK_ID'             => rrsIblock::getIBlockId('agent_partner_link'),
        'ACTIVE'                => 'Y',
        'PROPERTY_PARTNER_ID'   => $arParams['UID']
    ),
    false,
    false,
    array('PROPERTY_USER_ID')
);
while($data = $res->Fetch()) {
    $arResult['AGENTS'][$data['PROPERTY_USER_ID_VALUE']] = '';
}
//получаем имена агентов
if(count($arResult['AGENTS']) > 0){
    $user_obj = new CUser;
    $res = $user_obj->GetList(
        ($by = "ID"), ($order = "DESC"),
        array('ID' => implode(' | ', array_keys($arResult['AGENTS']))),
        array('FIELDS' => array('ID', 'NAME', 'LAST_NAME', 'LOGIN'))
    );
    while ($data = $res->Fetch()) {
        if(isset($arResult['AGENTS'][$data['ID']])) {
            $arResult['AGENTS'][$data['ID']] = trim($data['NAME'] . ' ' . $data['LAST_NAME']);
            if($arResult['AGENTS'][$data['ID']] == ''){
                $arResult['AGENTS'][$data['ID']] = $data['LOGIN'];
            }
        }
    }
}

$this->IncludeComponentTemplate();

unset($filter);