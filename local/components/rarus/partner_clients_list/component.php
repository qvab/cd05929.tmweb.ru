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

use Bitrix\Main\Context,
    Bitrix\Main\Type\DateTime,
    Bitrix\Main\Loader,
    Bitrix\Iblock;

CModule::IncludeModule('iblock');

$arResult['ERROR'] = '';
$arResult['ERROR_MESSAGE'] = '';
$arResult['ITEMS'] = array();
$el_obj = new CIBlockElement;
$agentObj = new agent;
global $USER;

if(!isset($arParams['NEWS_COUNT'])){
    $arParams['NEWS_COUNT'] = 10;
}elseif($arParams['NEWS_COUNT'] > 100){
    $arParams['NEWS_COUNT'] = 100;
}
$arResult['page'] = 1;
if(isset($_GET['PAGEN_1'])
    && is_numeric($_GET['PAGEN_1'])
    && $_GET['PAGEN_1'] > 1
){
    $arResult['page'] = $_GET['page'];
}

$arNavParams = array(
    'nPageSize' => $arParams['NEWS_COUNT'],
    'iNumPage' => $arResult['page']
);

$arFilter = array(
    'GROUPS_ID' => array(9),
    'ACTIVE' => 'Y'
);

//фильтрация прямая
if(isset($GLOBALS['arrFilter']['ID'])){
    $arFilter['ID'] = $GLOBALS['arrFilter']['ID'];
}

//фильтрация по региону
$region_wh_ids = array(); //id складов при выбраном регионе, для возможной доп фильтрации при выбранной культуре
if(isset($GLOBALS['arrFilter']['REGION'])
    && is_numeric($GLOBALS['arrFilter']['REGION'])
){
    $check_ids = array();
    $res = $el_obj->GetList(
        array('ID' => 'ASC'),
        array(
            'IBLOCK_ID' => rrsIblock::getIBlockId('client_warehouse'),
            'PROPERTY_REGION' => $GLOBALS['arrFilter']['REGION'],
            'ACTIVE' => 'Y',
            'PROPERTY_ACTIVE' => rrsIblock::getPropListKey('client_warehouse', 'ACTIVE', 'yes'),
            '!PROPERTY_CLIENT' => false
        ),
        false,
        false,
        array('ID', 'PROPERTY_CLIENT')
    );
    while($data = $res->Fetch()){
        if(is_numeric($data['PROPERTY_CLIENT_VALUE'])){
            $check_ids[$data['PROPERTY_CLIENT_VALUE']] = true;
            $region_wh_ids[] = $data['ID'];
        }
    }
    if(count($check_ids) > 0) {
        $check_ids = array_keys($check_ids);

        //если уже был установлен фильтр по пользователю, то находим пересечение значений
        if (!isset($arFilter['ID'])) {
            $arFilter['ID'] = implode('|', $check_ids);
        } else {
            $found_id = false;
            foreach ($check_ids as $cur_id) {
                if ($arFilter['ID'] == $cur_id) {
                    $found_id = true;
                    break;
                }
            }
            if (!$found_id) {
                $arFilter['ID'] = 0;
            }
        }
    }
}

//фильтрация по культуре
if(isset($GLOBALS['arrFilter']['CULTURE'])
    && is_numeric($GLOBALS['arrFilter']['CULTURE'])
){
    $wh_check_ids = array();
    $req_ids = array();
    $res = $el_obj->GetList(
        array('ID' => 'ASC'),
        array(
            'IBLOCK_ID' => rrsIblock::getIBlockId('client_request'),
            'ACTIVE' => 'Y',
            'PROPERTY_ACTIVE' => rrsIblock::getPropListKey('client_request', 'ACTIVE', 'yes'),
            'PROPERTY_CULTURE' => $GLOBALS['arrFilter']['CULTURE']
        ),
        false,
        false,
        array('ID')
    );
    while($data = $res->Fetch()){
        $req_ids[] = $data['ID'];
    }
    if(count($req_ids) > 0) {
        $tempFilter = array(
            'IBLOCK_ID' => rrsIblock::getIBlockId('client_request_cost'),
            'PROPERTY_REQUEST' => $req_ids,
            '!PROPERTY_WAREHOUSE' => false
        );
        //дополнительно фильтруем по складам выбранного региона (если регион был выбран)
        if(count($region_wh_ids) > 0){
            $tempFilter['PROPERTY_WAREHOUSE'] = $region_wh_ids;
        }
        $res = $el_obj->GetList(
            array('PROPERTY_WAREHOUSE' => 'ASC'),
            $tempFilter,
            false,
            false,
            array('PROPERTY_WAREHOUSE')
        );
        while ($data = $res->Fetch()) {
            if (is_numeric($data['PROPERTY_WAREHOUSE_VALUE'])) {
                $wh_check_ids[$data['PROPERTY_WAREHOUSE_VALUE']] = true;
            }
        }
    }
    if(count($wh_check_ids) > 0){
        $users_check = array();
        $res = $el_obj->GetList(
            array('PROPERTY_CLIENT' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('client_warehouse'),
                'ID' => array_keys($wh_check_ids),
                'ACTIVE' => 'Y',
                'PROPERTY_ACTIVE' => rrsIblock::getPropListKey('client_warehouse', 'ACTIVE', 'yes'),
                '!PROPERTY_CLIENT' => false
            ),
            array('PROPERTY_CLIENT')
        );
        while($data = $res->Fetch()){
            $users_check[$data['PROPERTY_CLIENT_VALUE']] = true;
        }
        //устанавливаем данные в фильтрацию
        if(count($users_check) > 0) {
            $users_check = array_keys($users_check);
            if (!isset($arFilter['ID'])
                || $arFilter['ID'] == ''
            ){
                if (count($users_check) > 1){
                    $arFilter['ID'] = implode('|', $users_check);
                }else{
                    $arFilter['ID'] = reset($users_check);
                }
            } else {
                if(is_numeric($arFilter['ID'])){
                    //ищем пересечение массива $users_check и числа $arFilter['ID']
                    $found_id = false;
                    foreach($users_check as $cur_id){
                        if($arFilter['ID'] == $cur_id){
                            $found_id = true;
                            break;
                        }
                    }
                    if(!$found_id){
                        $arFilter['ID'] = 0;
                    }
                }else{
                    //ищем пересечение массива $users_check и набора чисел в $arFilter['ID']
                    $filter_check = explode('|', $arFilter['ID']);
                    $new_filter = array_intersect($users_check, $filter_check);
                    if(count($new_filter) > 0){
                        $arFilter['ID'] = implode('|', $new_filter);
                    }else{
                        $arFilter['ID'] = 0;
                    }
                }
            }
        }else{
            $arFilter['ID'] = 0;
        }
    }else{
        $arFilter['ID'] = 0;
    }
}

//фильтрация по типу привязки
if(isset($GLOBALS['arrFilter']['LINKED_TYPE'])
    && (
        $GLOBALS['arrFilter']['LINKED_TYPE'] == 1
        || $GLOBALS['arrFilter']['LINKED_TYPE'] == 2
    )
){
    $check_ids = array();
    if(isset($arFilter['ID'])
        && $arFilter['ID'] != ''
    ){
        if(is_numeric($arFilter['ID'])){
            $check_ids = array($arFilter['ID']);
        }else{
            $check_ids = explode('|', $arFilter['ID']);
        }
    }

    $filtered_ids = $agentObj->getClientsForSelect($USER->GetID(), $check_ids);

    if($GLOBALS['arrFilter']['LINKED_TYPE'] == 1){
        if(count($filtered_ids) > 0){
            $arFilter['ID'] = implode('|', array_keys($filtered_ids));
        }else{
            $arFilter['ID'] = 0;
        }
    }elseif($GLOBALS['arrFilter']['LINKED_TYPE'] == 2){
        if(count($filtered_ids) > 0){
            if(isset($arFilter['ID'])
                && $arFilter['ID'] != ''
            ){
                if(is_numeric($arFilter['ID'])){
                    $arFilter['ID'] = $arFilter['ID'] . '&' . '~' . implode('&~', array_keys($filtered_ids));
                }else{
                    $arFilter['ID'] = '(' . $arFilter['ID'] . ')&' . '~' . implode('&~', array_keys($filtered_ids));
                }
            }else{
                $arFilter['ID'] = '~'.implode('&~', array_keys($filtered_ids));
            }
        }
    }
}

//получение записей пользователей с пагинацией
$res = CUser::GetList(
    ($by = 'id'), ($order = 'desc'),
    $arFilter,
    array(
        'FIELDS' => array('ID', 'EMAIL', 'NAME', 'LAST_NAME', 'SECOND_NAME'),
        'SELECT' => array('UF_FIRST_PHONE', 'UF_FIRST_LOGIN'),
        'NAV_PARAMS' => $arNavParams
    )
);
$arResult['NAV_DATA'] = $res->GetPageNavStringEx($navComponentObject, '', 'rarus', 'N');
while($data = $res->Fetch()){
    $arResult['ITEMS'][$data['ID']] = array(
        'NAME' => trim($data['NAME'] . ' ' . $data['LAST_NAME'] . ' ' . $data['SECOND_NAME']),
        'EMAIL' => (!checkEmailFromPhone($data['EMAIL']) ? $data['EMAIL'] . '' : ''),
        'UF_FIRST_LOGIN' => (intval($data['UF_FIRST_LOGIN']) == 1 ? true : false),
        'UF_FIRST_PHONE' => (intval($data['UF_FIRST_PHONE']) == 1 ? true : false),
    );
}

//получение "названий" пользователей
//и данных пользователей
if(count($arResult['ITEMS']) > 0){
    //получение "названий" пользователей
    $res = $el_obj->GetList(
        array('ID' => 'ASC'),
        array(
            'IBLOCK_ID' => rrsIblock::getIBlockId('client_agent_link'),
            'PROPERTY_AGENT_ID' => $USER->GetID(),
            '!PROPERTY_CLIENT_NICKNAME' => false,
            'PROPERTY_USER_ID' => array_keys($arResult['ITEMS'])
        ),
        false,
        false,
        array('PROPERTY_USER_ID', 'PROPERTY_CLIENT_NICKNAME')
    );
    while($data = $res->Fetch()){
        if(trim($data['PROPERTY_CLIENT_NICKNAME_VALUE']) != ''
            && isset($arResult['ITEMS'][$data['PROPERTY_USER_ID_VALUE']])
        ){
            $arResult['ITEMS'][$data['PROPERTY_USER_ID_VALUE']]['NAME'] = trim($data['PROPERTY_CLIENT_NICKNAME_VALUE']);
        }
    }

    //получение данных пользователей
    //получение заполненности обязательных полей пользователей
    $arResult['CLIENTS_PROFILE_DONE'] = $agentObj->getClientsRegistrationRights(array_keys($arResult['ITEMS']));
    //получение связанных пользователей из выводимого списка (ключ - ID пользователя) и наличия агентского договора
    $arResult['LINKED_ITEMS'] = array();
    $temp_linked_elements = $agentObj->getClientsForSelect($USER->GetID(), array_keys($arResult['ITEMS']));
    foreach($temp_linked_elements as $cur_uid => $cur_data){
        $arResult['LINKED_ITEMS'][$cur_uid] = array(
            'CONTRACT' => (!empty($cur_data['CONTRACT']) ? $cur_data['CONTRACT'] : 0),
            'CONTRACT_DATE' => (!empty($cur_data['CONTRACT_DATE']) ? $cur_data['CONTRACT_DATE'] : ''),
            'CONTRACT_FILE' => (!empty($cur_data['CONTRACT_FILE']) ? $cur_data['CONTRACT_FILE'] : ''),
            'CONTRACT_LAST_DATE' => (!empty($cur_data['CONTRACT_LAST_DATE']) ? $cur_data['CONTRACT_LAST_DATE'] : ''),
        );
    }
}

$this->IncludeComponentTemplate();

unset($res, $data, $el_obj, $user_obj, $check_deals_ids);