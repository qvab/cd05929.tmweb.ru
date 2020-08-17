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

//проверка параметров
if(!isset($arParams['MODE'])){
    $APPLICATION->ThrowException('Не указан режим работы.');
    return false;
}
if(!isset($arParams['UID'])){
    $APPLICATION->ThrowException('Не указан ID пользователя.');
    return false;
}
if(!isset($arParams['USER_TYPE'])){
    $APPLICATION->ThrowException('Не указан тип пользователя.');
    return false;
}
if(!isset($arParams['SHOW_TYPE'])){
    $APPLICATION->ThrowException('Не указан режим отображения.');
    return false;
}
if(!isset($arParams['AGENT_ID'])){
    $APPLICATION->ThrowException('Должно быть задано значение параметра по умолчанию.');
    return false;
}
if(!isset($arParams['PARTNER_ID'])){
    $APPLICATION->ThrowException('Должно быть задано значение параметра по умолчанию.');
    return false;
}
if(!isset($arParams['LIST_URL'])){
    $APPLICATION->ThrowException('Должно быть задано значение параметра по умолчанию.');
    return false;
}

//ссылка на исходную страницу
$arResult['BACKURL'] = $arParams['LIST_URL'] . (isset($_GET['backurl']) ? urldecode($_GET['backurl']) : '');

$filter = array();

//установка фильтрации для getDetailPageData
if($arParams['MODE'] == 'admin'){
    if($arParams['PARTNER_ID'] != 0){
        $filter['PARTNER_ID'] = $arParams['PARTNER_ID'];
    }

    if($arParams['AGENT_ID'] != 0){
        $filter['AGENT_ID'] = $arParams['AGENT_ID'];
    }
}elseif($arParams['MODE'] == 'partner'){
    $filter['PARTNER_ID'] = $arParams['UID'];

    if($arParams['AGENT_ID'] != 0){
        $filter['AGENT_ID'] = $arParams['AGENT_ID'];
    }
}elseif($arParams['MODE'] == 'regional_manager'){
    $check_partners = array(); // список партнеров регионального менеджера

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
    while($data = $res->Fetch()){
        $check_partners[$data['PROPERTY_ORGANIZER_VALUE']] = true;
    }

    if($arParams['PARTNER_ID'] != 0){
        //проверка привязки регионального менеджера к партнеру

        if(isset($check_partners[$arParams['PARTNER_ID']])){
            $filter['PARTNER_ID'] = $arParams['PARTNER_ID'];
        }else{
            LocalRedrect($arParams['LIST_URL']);
            exit;
        }
    }elseif(count($check_partners) > 0){
        $filter['PARTNER_ID'] = array_keys($check_partners);
    }

    if($arParams['AGENT_ID'] != 0){
        $filter['AGENT_ID'] = $arParams['AGENT_ID'];
    }
}

$filter['UF_DATE'] = date('d.m.Y');

$d_obj = new dashboardP();
$arResult['DATA'] = $d_obj->getDetailPageData($filter, $arParams['USER_TYPE'], $arParams['SHOW_TYPE'], $arParams['LIST_URL']);

//установка заголовка в зависимости от параметров
$arResult['title'] = '&nbsp;';
switch($arParams['USER_TYPE']){
    case 'farmer':

        if($arParams['SHOW_TYPE'] === 'not_demo'){
            $arResult['title'] = 'Поставщики в полноценном режиме';
        }elseif($arParams['SHOW_TYPE'] === 'demo'){
            $arResult['title'] = 'Поставщика в демо-режиме';
        }elseif($arParams['SHOW_TYPE'] === 'no_data'){
            $arResult['title'] = 'Поставщики без активных товаров';
        }else{
            $arResult['title'] = 'Поставщики';
        }

        break;

    case 'client':

        if($arParams['SHOW_TYPE'] === 'not_demo'){
            $arResult['title'] = 'Покупатели в полноценном режиме';
        }elseif($arParams['SHOW_TYPE'] === 'demo'){
            $arResult['title'] = 'Покупатели в демо-режиме';
        }elseif($arParams['SHOW_TYPE'] === 'no_data'){
            $arResult['title'] = 'Покупатели без активных запросов';
        }else{
            $arResult['title'] = 'Покупатели';
        }

        break;

    case 'transport':

        $arResult['title'] = 'Транспортные компании';

        break;
}

$this->IncludeComponentTemplate();

unset($filter);