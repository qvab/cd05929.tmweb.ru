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
$arResult['UNCOMPLETE_DEALS_IDS'] = array();

if (!isset($arParams['U_ID']) || !is_numeric($arParams['U_ID'])) {
    $arResult['ERROR'] = 'Y';
    $arResult['ERROR_MESSAGE'] = 'Не указан ID пользователя.';
}

if (!isset($arParams['SUB_HEAD']) || trim($arParams['SUB_HEAD']) == '') {
    $arResult['ERROR'] = 'Y';
    $arResult['ERROR_MESSAGE'] = 'Не указан подзаголовок страницы.';
}

if (!isset($arParams['IB_CODE']) || trim($arParams['IB_CODE']) == '') {
    $arResult['ERROR'] = 'Y';
    $arResult['ERROR_MESSAGE'] = 'Не указан код инфорблока с пользователями.';
}

//если требуется удалить/деактивировать пользователя
if(isset($_GET['deactivate']) && is_numeric($_GET['deactivate']) && $_GET['deactivate'] > 0){
    $_GET['delete'] = $_GET['deactivate'];
}
if(isset($_GET['delete']) && is_numeric($_GET['delete']) && $_GET['delete'] > 0)
{
    $deal_check = deal::getUsersActiveDeals($_GET['delete']);
    if(!isset($deal_check[$_GET['delete']])){ //проверяем наличие открытых сделок
        farmer::deleteFarmer($_GET['delete']);
    }
}

$el_obj = new CIBlockElement;
if ($arResult['ERROR'] != 'Y') {
    $u_id = $arParams['U_ID'];
    $user_obj = new CUser;
    $agentObj = new agent;
    $check_deals_ids = array(); //список id пользователей, которые нужно будет проверить на наличие незакрытых сделок
    $check_unclosed_deals_ids = array(); //user ids that were checked and have unclosed deals
    $arResult['USERS_LIST'] = array(); //список привязаных к организатору поставщиков
    $arResult['AGENTS_LIST'] = array(); //список агентов поставщиков, привязаных к партнеру
    $arResult['AGENTS_CONTROL_LIST'] = rrsIblock::getPropListId('farmer_agent_link', 'AGENT_RIGHTS');
    $check_agents_list = array();

    //получаем список агентов
    $res = $el_obj->GetList(
        array('ID' => 'ASC'),
        array(
            'IBLOCK_ID'             => rrsIblock::getIBlockId('agent_partner_link'),
            'ACTIVE'                => 'Y',
            'PROPERTY_PARTNER_ID'   => $u_id
        ),
        false,
        false,
        array('PROPERTY_USER_ID')
    );
    while($data = $res->Fetch()) {
        $check_agents_list[$data['PROPERTY_USER_ID_VALUE']] = '';
    }

    $arFilter = array(
        'IBLOCK_CODE'           => $arParams['IB_CODE'],
        'ACTIVE'                => 'Y',
        'PROPERTY_PARTNER_ID'   => $u_id
    );
    if(isset($_GET['agent_id'])
        && is_numeric($_GET['agent_id'])
        && $_GET['agent_id'] > 0
    ){
        $farmerslinkedtoagent = $agentObj->getFarmers($_GET['agent_id']);
        if(count($farmerslinkedtoagent) > 0){
            $arFilter['PROPERTY_USER'] = $farmerslinkedtoagent;
        }else{
            $arFilter['PROPERTY_USER'] = 0;
        }
    }
    $res = $el_obj->GetList(
        array('ID' => 'DESC'),
        $arFilter,
        false,
        false,
        array(
            'PROPERTY_USER',
            'ID',
            'IBLOCK_ID',
            'PROPERTY_PARTNER_VERIFIED',
            'PROPERTY_FULL_COMPANY_NAME',
            'PROPERTY_IP_FIO',
            'PROPERTY_VERIFIED'
        )
    );

    while ($data = $res->Fetch()) {
        $check_deals_ids[$data['PROPERTY_USER_VALUE']] = true;
        $company = $data['PROPERTY_FULL_COMPANY_NAME_VALUE'];
        if ($company == '')
            $company = $data['PROPERTY_IP_FIO_VALUE'];
        $arResult['USERS_LIST'][$data['PROPERTY_USER_VALUE']] = array(
            'IBLOCK_ID'     => $data['IBLOCK_ID'],
            'VERIFIED'      => (is_numeric($data['PROPERTY_VERIFIED_ENUM_ID']) && $data['PROPERTY_VERIFIED_ENUM_ID'] == rrsIblock::getPropListKey('farmer_profile', 'VERIFIED', 'yes')
                    ? 'y'
                    : 'n'),
            'COMPANY_NAME'  => $company,
            'AGENT_ID'      => '',
            'AGENT_CONTROL' => '',
            'EMAIL'         => '',
            'ACTIVE'        => '',
            'UF_DEMO'       => '',
            'LINK_ID'       => $data['ID']
        );
    }

    //получаем наличие открытых сделок (влияет на возможность деактивации/удаления пользователя)
    $farmers_deals = deal::getUsersActiveDeals(array_keys($arResult['USERS_LIST']));

    //получаем данные пользователей
    if(count($arResult['USERS_LIST']) + count($check_agents_list) > 0){
        $res = $user_obj->GetList(
            ($by = "ID"), ($order = "DESC"),
            array('ID' => implode(' | ', array_merge(array_keys($arResult['USERS_LIST']), array_keys($check_agents_list)))),
            array('FIELDS' => array('ID', 'EMAIL', 'ACTIVE', 'NAME', 'LAST_NAME', 'LOGIN'), 'SELECT' => array('UF_DEMO'))
        );
        while ($data = $res->Fetch()) {
            if (isset($arResult['USERS_LIST'][$data['ID']])) {
                $arResult['USERS_LIST'][$data['ID']]['EMAIL'] = $data['EMAIL'];
                $arResult['USERS_LIST'][$data['ID']]['ACTIVE'] = $data['ACTIVE'];
                $arResult['USERS_LIST'][$data['ID']]['UF_DEMO'] = $data['UF_DEMO'];

                if(isset($farmers_deals[$data['ID']])){
                    $arResult['USERS_LIST'][$data['ID']]['OPEN_DEALS'] = true;
                }
            }

            if(isset($check_agents_list[$data['ID']])) {
                $check_agents_list[$data['ID']] = trim($data['NAME'] . ' ' . $data['LAST_NAME']);
                if($check_agents_list[$data['ID']] == ''){
                    $check_agents_list[$data['ID']] = $data['LOGIN'];
                }
            }
        }
    }

    foreach($check_agents_list as $cur_id => $cur_data){
        if($cur_data != ''){
            $arResult['AGENTS_LIST'][$cur_id] = $cur_data;
        }
    }

    //get farmers to agents links
    $res = $el_obj->GetList(
        array('ID' => 'ASC'),
        array(
            'IBLOCK_ID' => rrsIblock::getIBlockId('farmer_agent_link'),
            'PROPERTY_USER_ID' => array_keys($arResult['USERS_LIST'])
        ),
        false,
        false,
        array('PROPERTY_USER_ID', 'PROPERTY_AGENT_ID', 'PROPERTY_AGENT_RIGHTS')
    );
    while($data = $res->Fetch())
    {
        if(isset($arResult['USERS_LIST'][$data['PROPERTY_USER_ID_VALUE']]))
        {
            $arResult['USERS_LIST'][$data['PROPERTY_USER_ID_VALUE']]['AGENT_ID'] = $data['PROPERTY_AGENT_ID_VALUE'];
            $arResult['USERS_LIST'][$data['PROPERTY_USER_ID_VALUE']]['AGENT_CONTROL'] = $data['PROPERTY_AGENT_RIGHTS_ENUM_ID'];
        }
    }

    //check if resend invite is need
    $arResult['MESS_STR'] = '';
    if(isset($_GET['resend']) && is_numeric($_GET['resend']) && isset($arResult['USERS_LIST'][$_GET['resend']]))
    {
        if($arResult['USERS_LIST'][$_GET['resend']]['ACTIVE'] == 'Y') {
            $arResult['MESS_STR'] = 'Аккаунт пользователя уже успешно активирован.';
        }
        else {
            $res = $user_obj->GetList(($by = "ID"), ($order = "DESC"), array('ID' => $_GET['resend']), array('FIELDS' => array('ID', 'EMAIL'), 'SELECT' => array('UF_HASH_INVITE')));
            if($data = $res->Fetch()) {
                if($data['ACTIVE'] == 'Y') {
                    $arResult['MESS_STR'] = 'Аккаунт пользователя уже успешно активирован.';
                }
                else {
                    $arEventFields = array(
                        'EMAIL' => $data['EMAIL'],
                        'HREF' => $GLOBALS['host'] . '/?reg_hash=' . $data['UF_HASH_INVITE'] . $arResult['USERS_LIST'][$_GET['resend']]['IBLOCK_ID'] . '#action=register',
                        'TO' => 'поставщика'
                    );
                    $res_val = CEvent::Send("AGRO_INVITE_USER", "s1", $arEventFields);
                    LocalRedirect($APPLICATION->GetCurDir(false) . '?resend_success=y');
                    exit;
                }
            }
        }
    }
    if(isset($_GET['resend_success']) && $_GET['resend_success'] == 'y') {
        $arResult['MESS_STR'] = 'Повторное письмо направлено пользователю';
    }

    if($arParams['IB_CODE'] == 'farmer_profile')
    {//check farmer uncomplete deals
        if(count($check_deals_ids) > 0)
        {
            $res = $el_obj->GetList(array('ID' => 'ASC'), array('IBLOCK_CODE' => 'deals_deals', 'PROPERTY_FARMER' => array_keys($check_deals_ids), 'PROPERTY_STATUS' => 52), false, false, array('PROPERTY_FARMER'));
            while($data = $res->Fetch())
            {
                $arResult['UNCOMPLETE_DEALS_IDS'][$data['PROPERTY_FARMER_VALUE']] = true;
            }
        }
    }
    elseif($arParams['IB_CODE'] == 'transport_profile')
    {//check transport uncomplete deals
        if(count($check_deals_ids) > 0)
        {
            $res = $el_obj->GetList(array('ID' => 'ASC'), array('IBLOCK_CODE' => 'deals_deals', 'PROPERTY_TRANSPORT' => array_keys($check_deals_ids), 'PROPERTY_STATUS' => 52), false, false, array('PROPERTY_TRANSPORT'));
            while($data = $res->Fetch())
            {
                $arResult['UNCOMPLETE_DEALS_IDS'][$data['PROPERTY_TRANSPORT_VALUE']] = true;
            }
        }
    }

    //check if there is unlink action
    if(isset($_GET['unlink_partner']) && is_numeric($_GET['unlink_partner'])
        && isset($arResult['USERS_LIST'][$_GET['unlink_partner']])
        && !isset($arResult['UNCOMPLETE_DEALS_IDS'][$_GET['unlink_partner']])
    )
    {//unlink user
        //get user properties element id
        $res = $el_obj->GetList(array('ID' => 'ASC'), array('IBLOCK_CODE' => $arParams['IB_CODE'], 'PROPERTY_USER' => $_GET['unlink_partner']), false, array('nTopCount' => 1), array('IBLOCK_ID', 'ID'));
        if($data = $res->Fetch())
        {
            $el_obj->SetPropertyValuesEx($data['ID'], $data['IBLOCK_ID'], array('PARTNER_ID' => false, 'VERIFIED' => rrsIblock::getPropListKey('farmer_profile', 'VERIFIED', 'no'), 'PARTNER_ID_TIMESTAMP' => 0));

            $noticeList = notice::getNoticeList();
            $farmerProfile = farmer::getProfile($_GET['unlink_partner'], true);
            $partnerProfile = partner::getProfile($arParams['U_ID']);

            $url = '/farmer/link_to_partner/';
            if (in_array($noticeList['e_l']['ID'], $farmerProfile['PROPERTY_NOTICE_VALUE'])) {
                $arEventFields = array(
                    'LINKED_URL' => $url,
                    'PARTNER_ID' => $_GET['unlink_partner'],
                    'EMAIL' => $farmerProfile['USER']['EMAIL'],
                    'PROFILE_LINK' => $GLOBALS['host'].'/profile/?uid='.$arParams['U_ID'],
                    'COMPANY_NAME' => $partnerProfile['PROPERTY_FULL_COMPANY_NAME_VALUE']
                );
                CEvent::Send('FARMER_UNLINK_FARMER', 's1', $arEventFields);
            }
            if (in_array($noticeList['c_l']['ID'], $farmerProfile['PROPERTY_NOTICE_VALUE'])) {
                notice::addNotice($farmerProfile['USER']['ID'], 'l', 'Открепление от организатора', $url, '#' . $_GET['unlink_partner']);
            }
            if (in_array($noticeList['s_l']['ID'], $farmerProfile['PROPERTY_NOTICE_VALUE']) && $farmerProfile['PROPERTY_PHONE_VALUE']) {
                $phone = str_replace(array('+', ' ', '(', ')', '-'), "", $farmerProfile['PROPERTY_PHONE_VALUE']);
                notice::sendNoticeSMS($phone, 'Открепление от организатора: '.$GLOBALS['host'].$url);
            }

            LocalRedirect($APPLICATION->GetCurDir());
            exit;
        }
    }
}

//set documents page title
if($arParams['SET_TITLE'] == 'Y') {
    $arProfile = $el_obj->GetList(
        array('ID' => 'ASC'),
        array('IBLOCK_CODE' => 'partner_profile', 'ACTIVE' => 'Y', 'PROPERTY_USER' => $arParams['U_ID']),
        false,
        array('nTopCount' => 1),
        array('PROPERTY_FULL_COMPANY_NAME')
    )->Fetch();
    if($arProfile && isset($arProfile['PROPERTY_FULL_COMPANY_NAME_VALUE']) && trim($arProfile['PROPERTY_FULL_COMPANY_NAME_VALUE']) != '') {
        $APPLICATION->SetTitle($arProfile['PROPERTY_FULL_COMPANY_NAME_VALUE']);
    }
}

$this->IncludeComponentTemplate();

unset($res, $data, $el_obj, $user_obj, $check_deals_ids, $check_unclosed_deals_ids);