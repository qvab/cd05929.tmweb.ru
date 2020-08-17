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

$arParams['CUR_ID'] = intval($USER->GetID());

//проверка параметров
if ($arParams['CUR_ID'] == 0) {
    ShowError('Укажите корректный ID пользователя');
    return false;
}

if (!isset($arParams['U_ID']) || !is_numeric($arParams['U_ID'])) {
    ShowError('Укажите корректный ID пользователя');
    return false;
}

if (!isset($arParams['TYPE']) || trim($arParams['TYPE']) == '') {
    ShowError('Укажите тип пользователя для проверки прав');
    return false;
}

$u_obj  = new CUser;
$el_obj = new CIBlockElement;

//если не индекс то ставим заголовок страницы
if($arParams['TAB'] != ''){
    $title_val = '';
    $arFilter = array('PROPERTY_USER' => $arParams['U_ID']);
    $arSelect = array('PROPERTY_FULL_COMPANY_NAME', 'PROPERTY_IP_FIO');

    if ($arParams['TYPE'] == 'p') {
        $title_val = 'Профиль организатора';
        $arFilter['IBLOCK_ID'] = rrsIblock::getIBlockId('partner_profile');
    }
    elseif ($arParams['TYPE'] == 't') {
        $title_val = 'Профиль транспортной компании';
        $arFilter['IBLOCK_ID'] = rrsIblock::getIBlockId('transport_profile');
    }
    elseif ($arParams['TYPE'] == 'f') {
        $title_val = 'Профиль поставщика';
        $arFilter['IBLOCK_ID'] = rrsIblock::getIBlockId('farmer_profile');
    }
    elseif ($arParams['TYPE'] == 'c') {
        $title_val = 'Профиль клиента';
        $arFilter['IBLOCK_ID'] = rrsIblock::getIBlockId('client_profile');
    }
    $APPLICATION->SetTitle($title_val);

    $res = $el_obj->GetList(array('ID' => 'ASC'), $arFilter, false, array('nTopCount' => 1), $arSelect);
    while($data = $res->Fetch()){
        if(isset($data['PROPERTY_FULL_COMPANY_NAME_VALUE']) && ($data['PROPERTY_FULL_COMPANY_NAME_VALUE']) != ''){
            $APPLICATION->SetTitle($data['PROPERTY_FULL_COMPANY_NAME_VALUE']);
        }elseif(isset($data['PROPERTY_IP_FIO_VALUE']) && ($data['PROPERTY_IP_FIO_VALUE']) != ''){
            $APPLICATION->SetTitle($data['PROPERTY_IP_FIO_VALUE']);
        }
    }
}

//получаем данные для отображения
$arResult['MENU_LIST'] = array();

$linked = false;
global $USER;

if($GLOBALS['rrs_user_perm_level'] == 'p'){//проверка является ли текущий пользователь партнёром
    $agentObj = new agent();

    //проверяем привязан ли пользователь к текущему и загружен ли партнёрский договор
    if($arParams['TYPE'] == 'f'){

        $linked = true;
        $arResult['MENU_LIST'] = array(
            'partner_clients_blacklist' => 'Черный список'
        );

        //проверяем привязан ли поставщик к текущему агенту и заполенны ли все обязательные поля профиля (нужно для работы с документами)
        if($agentObj->checkFarmerByAgent($_GET['uid'], $arParams['CUR_ID'])) {
            $GLOBALS['linked_with_partner'] = true;

            if (!$agentObj->getFarmersRegistrationRights($_GET['uid'])) {
                $arResult['MENU_LIST'] = array(
                    'make_full_mode' => 'Изменить профиль'
                );
            } else {
                $GLOBALS['linked_with_doc'] = true;
                $arResult['MENU_LIST'] = array(
                    'make_full_mode' => 'Изменить профиль',
                    //'documents' => 'Работа с документами поставщика',
                );
            }

            $arResult['MENU_LIST']['warehouses'] = 'Склады';
            $arResult['MENU_LIST']['offers'] = 'Товары';
            $arResult['MENU_LIST']['pair'] = 'Пары';
            //$arResult['MENU_LIST']['requests'] = 'Запросы';
            $arResult['MENU_LIST']['agent_affairs'] = 'Дела';
            $arResult['MENU_LIST']['partner_clients_blacklist'] = 'Черный список';
        }
    }elseif ($arParams['TYPE'] == 'c') {//просматривается профиль покупателя)
        //формируем меню для партнера на основе отдельно файла меню
        require_once($_SERVER['DOCUMENT_ROOT'] . '/profile/.partner.menu.php');
        if(isset($aMenuLinks)){
            if((sizeof($aMenuLinks))&&(is_array($aMenuLinks))){
                $arResult['MENU_LIST'] = array();
                foreach($aMenuLinks as $item){
                    //проверяем имеет ли доступ партнер к текущему пункту меню
                    if(isset($item[3])){
                        if((sizeof($item[3]))&&(is_array($item[3]))){
                            if(isset($item[3]['permission'])){
                                switch($item[3]['permission']){
                                    case 'partner_client':
                                        //только если клиент связан с текущий партнером
                                        if($agentObj->checkLinkWithClient($_GET['uid'], $arParams['CUR_ID'])){
                                            $arResult['MENU_LIST'][str_replace('/','',$item[1])] = $item[0];
                                        }
                                        break;
                                    case 'all_client':
                                        //для всех клиентов
                                        $arResult['MENU_LIST'][str_replace('/','',$item[1])] = $item[0];
                                        break;
                                }
                            }
                        }
                    }
                }
            }
        }

        //проверяем привязан ли покупатель к текущему агенту и заполенны ли все обязательные поля профиля (нужно для работы с документами)
        if($agentObj->checkLinkWithClient($_GET['uid'], $arParams['CUR_ID'])) {
            $linked = true;

            if (!$agentObj->getClientsRegistrationRights($_GET['uid'])) {
                $arResult['MENU_LIST'] = array(
                    'make_full_mode' => 'Изменить профиль'
                );
            } else {
                $GLOBALS['linked_with_doc'] = true;
                $arResult['MENU_LIST'] = array(
                    'make_full_mode' => 'Изменить профиль',
                    'documents' => 'Работа с документами покупателя',
                );
            }

//            $arResult['MENU_LIST']['warehouses']    = 'Склады';
//            $arResult['MENU_LIST']['requests']      = 'Запросы';
        }
    }
}
elseif($GLOBALS['rrs_user_perm_level'] == 'ag'){//проверка является ли текущий пользователь агентом АП
    //проверяем привязан ли пользователь к текущему агенту
    if($arParams['TYPE'] == 'f'){
        //проверяем привязан ли поставщик к текущему агенту и заполенны ли все обязательные поля профиля (нужно для работы с документами)
        $agentObj = new agent();
        if($agentObj->checkFarmerByAgent($_GET['uid'], $arParams['CUR_ID'])) {
            $linked = true;

            if (!$agentObj->getFarmersRegistrationRights($_GET['uid'])) {
                $arResult['MENU_LIST'] = array(
                    'make_full_mode' => 'Изменить профиль'
                );
            } else {
                $GLOBALS['linked_with_doc'] = true;
                $arResult['MENU_LIST'] = array(
                    'make_full_mode' => 'Изменить профиль',
                    'documents' => 'Работа с документами поставщика',
                );
            }

            $arResult['MENU_LIST']['warehouses'] = 'Склады';
            $arResult['MENU_LIST']['offers'] = 'Товары';
            $arResult['MENU_LIST']['agent_affairs'] = 'Дела';
        }

    }
}elseif($GLOBALS['rrs_user_perm_level'] == 'agc'){//проверка является ли текущий пользователь агентом покупателя
    //проверяем привязан ли покупатель к текущему агенту
    if($arParams['TYPE'] == 'c'){
        //проверяем привязан ли покупатель к текущему агенту и заполенны ли все обязательные поля профиля (нужно для работы с документами)
        $agentObj = new agent();
        if($agentObj->checkLinkWithClient($_GET['uid'], $arParams['CUR_ID'])) {
            $linked = true;

            if (!$agentObj->getClientsRegistrationRights($_GET['uid'])) {
                $arResult['MENU_LIST'] = array(
                    'make_full_mode' => 'Изменить профиль'
                );
            } else {
                $GLOBALS['linked_with_doc'] = true;
                $arResult['MENU_LIST'] = array(
                    'make_full_mode' => 'Изменить профиль',
                    'documents' => 'Работа с документами поставщика',
                );
            }

//            $arResult['MENU_LIST']['warehouses']    = 'Склады';
//            $arResult['MENU_LIST']['requests']      = 'Запросы';
        }
    }
}
if($linked){
    $this->IncludeComponentTemplate();
}

return $linked;