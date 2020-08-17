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
$arResult = array('AGENTS' => array(), 'PARTNERS' => array());

//проверка параметров
if(!isset($arParams['UID'])){
    $APPLICATION->ThrowException('Не указан ID пользователя.');
    return false;
}
if(!isset($arParams['PARTNER_ID'])){
    $APPLICATION->ThrowException('Должно быть задано значение параметра организатора по умолчанию.');
    return false;
}
if(!isset($arParams['AGENT_ID'])){
    $APPLICATION->ThrowException('Должно быть задано значение параметра агента по умолчанию.');
    return false;
}

//установка фильтрации по умолчанию для getDashboardData
$filter['PARTNER_ID'] = $arParams['PARTNER_ID'];
$filter['AGENT_ID'] = $arParams['AGENT_ID'];

//установка настроек для ссылок на детальную страницу
$arResult['URI_PARAMS'] = array();
if($arParams['PARTNER_ID'] != 0){
    $arResult['URI_PARAMS'][] = 'partner_id=' . $arParams['PARTNER_ID'];
}
if($arParams['AGENT_ID'] != 0){
    $arResult['URI_PARAMS'][] = 'agent_id=' . $arParams['AGENT_ID'];
}

//Получаем данные связанные с текущим пользователем
CModule::IncludeModule('iblock');
//получаем организаторов
$el_obj = new CIBlockElement;
$res = $el_obj->GetList(
    array('ID' => 'ASC'),
    array(
        'IBLOCK_ID'                    => rrsIblock::getIBlockId('BIND_REGIONAL_TO_ORGANIZERS'),
        'PROPERTY_REGIONAL_MANAGER'    => $arParams['UID']
    ),
    false,
    false,
    array('PROPERTY_ORGANIZER')
);
while($data = $res->Fetch()) {
    $arResult['PARTNERS'][$data['PROPERTY_ORGANIZER_VALUE']] = '';
}
//получаем имена организаторов
if(count($arResult['PARTNERS']) > 0){
    $user_obj = new CUser;
    $res = $user_obj->GetList(
        ($by = "ID"), ($order = "DESC"),
        array('ID' => implode(' | ', array_keys($arResult['PARTNERS']))),
        array('FIELDS' => array('ID', 'NAME', 'LAST_NAME', 'LOGIN'))
    );
    while ($data = $res->Fetch()) {
        $arResult['PARTNERS'][$data['ID']] = trim($data['NAME'] . ' ' . $data['LAST_NAME']);
        if($arResult['PARTNERS'][$data['ID']] == ''){
            $arResult['PARTNERS'][$data['ID']] = $data['LOGIN'];
        }
    }
}
//убираем вывод данных, если не найдено связанных партнеров или выбранный партнер не в списке привязанных
if($filter['PARTNER_ID'] == 0
    && count($arResult['PARTNERS']) == 0
    ||
    $filter['PARTNER_ID'] != 0
    && !isset($arResult['PARTNERS'][$filter['PARTNER_ID']])
){
    $filter['PARTNER_ID'] = -1;
}elseif($filter['PARTNER_ID'] == 0
    && count($arResult['PARTNERS']) > 0){
    $filter['PARTNER_ID'] = array_keys($arResult['PARTNERS']);
}

//получаем даныне из БД
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
    $arResult['DATA']['CLIENTS_DATA_NO_REQUESTS']['TODAY']
);
$arResult['PERCENTS']['CLIENTS_DATA_NO_REQUESTS']['WEEK_AGO'] = percentDiffSign(
    $arResult['DATA']['CLIENTS_DATA_NO_REQUESTS']['WEEK_AGO'],
    $arResult['DATA']['CLIENTS_DATA_NO_REQUESTS']['TODAY']
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

$this->IncludeComponentTemplate();

unset($filter);