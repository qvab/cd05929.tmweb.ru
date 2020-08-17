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
$el_obj = new CIBlockElement;

if (!isset($arParams['U_ID']) || !is_numeric($arParams['U_ID'])) {
    $arResult['ERROR'] = 'Y';
    $arResult['ERROR_MESSAGE'] = 'Не указан ID пользователя.';
}

if (!isset($arParams['SUB_HEAD']) || trim($arParams['SUB_HEAD']) == '') {
    $arResult['ERROR'] = 'Y';
    $arResult['ERROR_MESSAGE'] = 'Не указан подзаголовок страницы.';
}

//статусы свойства - Проверено
$verifiedStatusList = rrsIblock::getPropListKey('client_partner_link', 'VERIFIED');
foreach ($verifiedStatusList as $item) {
    $verifiedStatus[$item['ID']] = $item['XML_ID'];
}

//если требуется удалить/деактивировать пользователя
if(isset($_GET['deactivate']) && is_numeric($_GET['deactivate']) && $_GET['deactivate'] > 0){
    $_GET['delete'] = $_GET['deactivate'];
}
if(isset($_GET['delete']) && is_numeric($_GET['delete']) && $_GET['delete'] > 0)
{
    $deal_check = deal::getUsersActiveDeals(false, $_GET['delete']);
    if(!isset($deal_check[$_GET['delete']])){ //проверяем наличие открытых сделок
        client::deleteClient($_GET['delete']);
    }
}

if ($arResult['ERROR'] != 'Y') {
    $u_id = $arParams['U_ID'];
    $user_obj = new CUser;
    $agentObj = new agent();
    $check_deals_ids = array(); //список id пользователей, которые нужно будет проверить на наличие незакрытых сделок
    $arResult['USERS_LIST'] = array(); //список привязаных к организатору покупателей
    $arResult['AGENTS_LIST'] = array(); //список агентов покупателей, привязаных к партнеру
    $arResult['AGENTS_CONTROL_LIST'] = rrsIblock::getPropListId('client_agent_link', 'AGENT_RIGHTS');
    $check_agents_list = array();

    //получаем список агентов
    $res = $el_obj->GetList(
        array('ID' => 'ASC'),
        array(
            'IBLOCK_ID'             => rrsIblock::getIBlockId('client_agent_partner_link'),
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

    //получаем регион текущего организатора
    $res = CIBlockElement::GetList(
        array('ID' => 'ASC'),
        array(
            'IBLOCK_ID' => rrsIblock::getIBlockId('partner_profile'),
            'PROPERTY_USER' => $arParams['U_ID']
        ),
        false,
        array('nTopCount' => 1),
        array('ID', 'IBLOCK_ID', 'PROPERTY_REGION')
    );
    if ($data = $res->Fetch()) {
        if(is_numeric($data['PROPERTY_REGION_VALUE']))
            $region_id = $data['PROPERTY_REGION_VALUE'];
    }

    //получение всех покупателей в регионе текущего организатора
    $regionClients = array();
    if ($region_id > 0) {
        $res = CIBlockElement::GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId($arParams['IB_CODE']),
                'ACTIVE' => 'Y',
                'PROPERTY_REGION' => $region_id
            ),
            false,
            false,
            array('ID', 'PROPERTY_USER', 'PROPERTY_FULL_COMPANY_NAME')
        );
        while ($data = $res->Fetch()) {
            $regionClients[$data['PROPERTY_USER_VALUE']] = array('ID' => $data['ID'], 'COMPANY_NAME' => $data['PROPERTY_FULL_COMPANY_NAME_VALUE']);
        }

        //исключение из списка тех покупатеелй, у которых уже есть привязка к какому-либо организаотру
        if (is_array($regionClients) && sizeof($regionClients) > 0) {
            $res = CIBlockElement::GetList(
                array('ID' => 'ASC'),
                array(
                    'IBLOCK_ID' => rrsIblock::getIBlockId('client_partner_link'),
                    'ACTIVE' => 'Y',
                    'PROPERTY_USER_ID' => array_keys($regionClients)
                ),
                false,
                false,
                array('ID', 'PROPERTY_USER_ID', 'PROPERTY_PARTNER_ID')
            );
            while ($data = $res->Fetch()) {
                unset($regionClients[$data['PROPERTY_USER_ID_VALUE']]);
            }
        }
    }

    //проверяем на есть ли данный партнер в черном списке клиентов
    foreach($regionClients as $client_id=>$cl_data){
        if(BlackList::clientPartnerBLExists($client_id,$USER->getID())){
            unset($regionClients[$client_id]);
        }
    }
    $arResult['CLIENTS_LIST'] = $regionClients;

    //get activated clients
    $arFilter = array(
        'IBLOCK_ID'             => rrsIblock::getIBlockId('client_partner_link'),
        'ACTIVE'                => 'Y',
        'PROPERTY_PARTNER_ID'   => $u_id
    );
    if(isset($_GET['agent_id'])
        && is_numeric($_GET['agent_id'])
        && $_GET['agent_id'] > 0
    ){
        $clientslinkedtoagent = $agentObj->getClients($_GET['agent_id']);
        if(count($clientslinkedtoagent) > 0){
            $arFilter['PROPERTY_USER_ID'] = $clientslinkedtoagent;
        }else{
            $arFilter['PROPERTY_USER_ID'] = 0;
        }
    }
    $res = $el_obj->GetList(
        array('ID' => 'DESC'),
        $arFilter,
        false,
        false,
        array(
            'ID',
            'PROPERTY_USER_ID',
            'IBLOCK_ID',
            'PROPERTY_PARTNER_LINK_DATE',
            'PROPERTY_VERIFIED',
            'PROPERTY_PARTNER_LINK_DOC',
            'PROPERTY_PARTNER_LINK_DOC_NUM',
            'PROPERTY_PARTNER_LINK_DOC_DATE',
        )
    );
    while ($data = $res->Fetch()) {
        $check_deals_ids[$data['PROPERTY_USER_ID_VALUE']] = true;
        $arResult['USERS_LIST'][$data['PROPERTY_USER_ID_VALUE']] = array(
            'IBLOCK_ID' => $data['IBLOCK_ID'],
            'LINK_DATE' => $data['PROPERTY_PARTNER_LINK_DATE_VALUE'],
            'LINK_ID' => $data['ID'],
            'LINK_DOC'      => (is_numeric($data['PROPERTY_PARTNER_LINK_DOC_VALUE']) ? $data['PROPERTY_PARTNER_LINK_DOC_VALUE'] : 'n'),
            'LINK_DOC_NUM'  => $data['PROPERTY_PARTNER_LINK_DOC_NUM_VALUE'],
            'LINK_DOC_DATE' => $data['PROPERTY_PARTNER_LINK_DOC_DATE_VALUE'],
        );
        if(isset($verifiedStatus[$data['PROPERTY_VERIFIED_ENUM_ID']])){
            $arResult['USERS_LIST'][$data['PROPERTY_USER_ID_VALUE']]['VERIFIED'] = $verifiedStatus[$data['PROPERTY_VERIFIED_ENUM_ID']];
        }else{
            $arResult['USERS_LIST'][$data['PROPERTY_USER_ID_VALUE']]['VERIFIED'] = 'no';
        }

    }

    //get inactivated users
    $arFilter = array('IBLOCK_ID' => rrsIblock::getIBlockId($arParams['IB_CODE']), 'ACTIVE' => 'Y');
    if (is_array($check_deals_ids) && sizeof($check_deals_ids) > 0) {
        $arFilter[] = array(
            'LOGIC' => 'OR',
            0 => array('PROPERTY_PARTNER_ID' => $u_id, '!PROPERTY_USER' => false),
            1 => array('PROPERTY_USER' => array_keys($check_deals_ids))
        );
    }
    else {
        $arFilter['PROPERTY_PARTNER_ID'] = $u_id;
        $arFilter['!PROPERTY_USER'] = false;
    }

    $res = $el_obj->GetList(
        array('ID' => 'DESC'),
        $arFilter,
        false,
        false,
        array('PROPERTY_USER', 'IBLOCK_ID', 'PROPERTY_FULL_COMPANY_NAME')
    );
    while ($data = $res->Fetch()) {
        if ($arResult['USERS_LIST'][$data['PROPERTY_USER_VALUE']]['IBLOCK_ID'] > 0) {
            $arResult['USERS_LIST'][$data['PROPERTY_USER_VALUE']]['COMPANY_NAME'] = $data['PROPERTY_FULL_COMPANY_NAME_VALUE'];
        }
        else {
            $arResult['USERS_LIST'][$data['PROPERTY_USER_VALUE']] = array(
                'IBLOCK_ID' => $data['IBLOCK_ID'],
                'COMPANY_NAME' => $data['PROPERTY_FULL_COMPANY_NAME_VALUE']
            );
        }
    }

    //получаем наличие открытых сделок (влияет на возможность деактивации/удаления пользователя)
    $clients_deals = deal::getUsersActiveDeals(false, array_keys($arResult['USERS_LIST']));

    $userList = array_merge(array_keys($arResult['USERS_LIST']), array_keys($arResult['CLIENTS_LIST']), array_keys($check_agents_list));

    if (is_array($userList) && sizeof($userList) > 0) {
        $res = $user_obj->GetList(
            ($by = "ID"), ($order = "DESC"),
            array('ID' => implode(' | ', $userList)),
            array('FIELDS' => array('ID', 'EMAIL', 'ACTIVE', 'NAME', 'LAST_NAME', 'LOGIN'), 'SELECT' => array('UF_DEMO'))
        );
        while ($data = $res->Fetch()) {
            if (isset($arResult['USERS_LIST'][$data['ID']])) {
                $arResult['USERS_LIST'][$data['ID']]['EMAIL'] = $data['EMAIL'];
                $arResult['USERS_LIST'][$data['ID']]['ACTIVE'] = $data['ACTIVE'];
                $arResult['USERS_LIST'][$data['ID']]['UF_DEMO'] = $data['UF_DEMO'];

                if(isset($clients_deals[$data['ID']])){
                    $arResult['USERS_LIST'][$data['ID']]['OPEN_DEALS'] = true;
                }
            }
            if (isset($arResult['CLIENTS_LIST'][$data['ID']])) {
                $arResult['CLIENTS_LIST'][$data['ID']]['EMAIL'] = $data['EMAIL'];
                $arResult['CLIENTS_LIST'][$data['ID']]['ACTIVE'] = $data['ACTIVE'];
                $arResult['CLIENTS_LIST'][$data['ID']]['NAME'] = $data['NAME'];
                $arResult['CLIENTS_LIST'][$data['ID']]['LAST_NAME'] = $data['LAST_NAME'];
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

    //получение привязки покупателей и агентов
    $res = $el_obj->GetList(
        array('ID' => 'ASC'),
        array(
            'IBLOCK_ID' => rrsIblock::getIBlockId('client_agent_link'),
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

    //check doc upload action
    if(isset($_POST['add_doc']) && $_POST['add_doc'] == 'y'
        && is_numeric($_POST['uid']) && isset($arResult['USERS_LIST'][$_POST['uid']])
        && isset($arResult['USERS_LIST'][$_POST['uid']]['LINK_DOC']) && $arResult['USERS_LIST'][$_POST['uid']]['LINK_DOC'] == 'n'
        && isset($_FILES['doc_val']['error']) && $_FILES['doc_val']['error'] == 0
        && isset($_POST['doc_num']) && trim($_POST['doc_num']) != ''
        && isset($_POST['doc_date']) && trim($_POST['doc_date']) != ''
    )
    {//if user temporary linked, but doc was not uploaded yet

        $res = $el_obj->GetList(array('ID' => 'ASC'), array('IBLOCK_ID' => rrsIblock::getIBlockId('client_partner_link'), 'PROPERTY_PARTNER_ID' => $u_id, 'PROPERTY_USER_ID' => $_POST['uid']), false, array('nTopCount' => 1), array('ID'));
        if($data = $res->Fetch())
        {
            $el_obj->SetPropertyValuesEx($data['ID'], $arResult['USERS_LIST'][$_POST['uid']]['IBLOCK_ID'], array('PARTNER_LINK_DOC' => $_FILES['doc_val'], 'PARTNER_ID_TIMESTAMP' => 0, 'PARTNER_LINK_DOC_NUM' => $_POST['doc_num'], 'PARTNER_LINK_DOC_DATE' => $_POST['doc_date']));

            //отправка сообщения клиенту о том, что он подтверждён
            //получение названия для организатора
            $partner_profile = partner::getProfile($u_id);
            $arEventFields = array(
                'EMAIL' => $arResult['USERS_LIST'][$_POST['uid']]['EMAIL'],
                'COMPANY_NAME' => $partner_profile['PROPERTY_FULL_COMPANY_NAME_VALUE'],
                'LINKED_URL' => $GLOBALS['host'] . '/client/link_to_partner/',
                'PARTNER_ID' => $u_id,
            );

            /*$noticeList = notice::getNoticeList();
            $farmerProfile = partner::getProfile($_POST['uid'], true);

            $url = '/farmer/link_to_partner/';
            if (in_array($noticeList['e_l']['ID'], $farmerProfile['PROPERTY_NOTICE_VALUE'])) {
                $arEventFields = array(
                    'LINKED_URL' => $GLOBALS['host'].$url,
                    'PARTNER_ID' => $u_id,
                    'EMAIL' => $farmerProfile['USER']['EMAIL'],
                );
                CEvent::Send('FARMER_ADD_DOC', 's1', $arEventFields);
            }
            if (in_array($noticeList['c_l']['ID'], $farmerProfile['PROPERTY_NOTICE_VALUE'])) {
                notice::addNotice($farmerProfile['USER']['ID'], 'l', 'Добавлен договор', $url, '#' . $u_id);
            }
            if (in_array($noticeList['s_l']['ID'], $farmerProfile['PROPERTY_NOTICE_VALUE']) && $farmerProfile['PROPERTY_PHONE_VALUE']) {
                $phone = str_replace(array('+', ' ', '(', ')', '-'), "", $farmerProfile['PROPERTY_PHONE_VALUE']);
                notice::sendNoticeSMS($phone, 'Добавлен договор: '.$GLOBALS['host'].$url);
            }*/

            LocalRedirect($APPLICATION->GetCurDir(false));
            exit;
        }
        else
        {//error add doc to user

        }
    }

    //check if resend invite is need
    $arResult['MESS_STR'] = '';
    if (isset($_GET['resend']) && is_numeric($_GET['resend']) && isset($arResult['USERS_LIST'][$_GET['resend']])) {
        if ($arResult['USERS_LIST'][$_GET['resend']]['ACTIVE'] == 'Y') {
            $arResult['MESS_STR'] = 'Аккаунт пользователя уже успешно активирован.';
        }
        else {
            $res = $user_obj->GetList(($by = "ID"), ($order = "DESC"), array('ID' => $_GET['resend']), array('FIELDS' => array('ID', 'EMAIL'), 'SELECT' => array('UF_HASH_INVITE')));
            if ($data = $res->Fetch()) {
                if ($data['ACTIVE'] == 'Y') {
                    $arResult['MESS_STR'] = 'Аккаунт пользователя уже успешно активирован.';
                }
                else {
                    $arEventFields = array(
                        'EMAIL' => $data['EMAIL'],
                        'HREF' => $GLOBALS['host'] . '/?reg_hash=' . $data['UF_HASH_INVITE'] . $arResult['USERS_LIST'][$_GET['resend']]['IBLOCK_ID'] . '#action=register',
                        'TO' => 'покупателя'
                    );
                    $res_val = CEvent::Send("AGRO_INVITE_USER", "s1", $arEventFields);
                    LocalRedirect($APPLICATION->GetCurDir(false) . '?resend_success=y');
                    exit;
                }
            }
        }
    }
    if (isset($_GET['resend_success']) && $_GET['resend_success'] == 'y') {
        $arResult['MESS_STR'] = 'Повторное письмо направлено пользователю';
    }

    if (count($check_deals_ids) > 0) {
        //check client uncomplete deals
        $res = $el_obj->GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('deals_deals'),
                'ACTIVE' => 'Y',
                'PROPERTY_CLIENT' => array_keys($check_deals_ids),
                'PROPERTY_STATUS' => rrsIblock::getPropListKey('deals_deals', 'STATUS', 'open')
            ),
            false,
            false,
            array('ID', 'PROPERTY_CLIENT')
        );
        while ($data = $res->Fetch()) {
            $arResult['UNCOMPLETE_DEALS_IDS'][$data['PROPERTY_CLIENT_VALUE']] = true;
        }

        //check active requests
        $res = CIBlockElement::GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('client_request'),
                'ACTIVE' => 'Y',
                //'ACTIVE_DATE' => 'Y',
                'PROPERTY_ACTIVE' => rrsIblock::getPropListKey('client_request', 'ACTIVE', 'yes'),
                'PROPERTY_CLIENT' => array_keys($check_deals_ids)
            ),
            false,
            false,
            array('ID', 'PROPERTY_CLIENT')
        );
        if ($data = $res->Fetch()) {
            $arResult['UNCOMPLETE_DEALS_IDS'][$data['PROPERTY_CLIENT_VALUE']] = true;
        }
    }

    //check if there is unlink action
    if (isset($_GET['unlink_partner']) && is_numeric($_GET['unlink_partner'])
        && isset($arResult['USERS_LIST'][$_GET['unlink_partner']])
        && !isset($arResult['UNCOMPLETE_DEALS_IDS'][$_GET['unlink_partner']])
    ) {//unlink user
        //get user properties element id
        $res = $el_obj->GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('client_partner_link'),
                'PROPERTY_PARTNER_ID' => $u_id,
                'PROPERTY_USER_ID' => $_GET['unlink_partner']
            ),
            false,
            array('nTopCount' => 1),
            array('IBLOCK_ID', 'ID')
        );
        if ($data = $res->Fetch()) {
            $el_obj->Delete($data['ID']);

            $noticeList = notice::getNoticeList();
            $clientProfile = client::getProfile($_GET['unlink_partner'], true);
            $partnerProfile = partner::getProfile($arParams['U_ID']);

            $url = '/client/link_to_partner/';
            if (in_array($noticeList['e_l']['ID'], $clientProfile['PROPERTY_NOTICE_VALUE'])) {
                $arEventFields = array(
                    'LINKED_URL' => $GLOBALS['host'].$url,
                    'PARTNER_ID' => $u_id,
                    'EMAIL' => $clientProfile['USER']['EMAIL'],
                    'PROFILE_LINK' => $GLOBALS['host'].'/profile/?uid='.$arParams['U_ID'],
                    'COMPANY_NAME' => $partnerProfile['PROPERTY_FULL_COMPANY_NAME_VALUE']
                );
                CEvent::Send('CLIENT_UNLINK_CLIENT', 's1', $arEventFields);
            }
            if (in_array($noticeList['c_l']['ID'], $clientProfile['PROPERTY_NOTICE_VALUE'])) {
                notice::addNotice($clientProfile['USER']['ID'], 'l', 'Открепление от организатора', $url, '#' . $_GET['unlink_partner']);
            }
            if (in_array($noticeList['s_l']['ID'], $clientProfile['PROPERTY_NOTICE_VALUE']) && $clientProfile['PROPERTY_PHONE_VALUE']) {
                $phone = str_replace(array('+', ' ', '(', ')', '-'), "", $clientProfile['PROPERTY_PHONE_VALUE']);
                notice::sendNoticeSMS($phone, 'Открепление от организатора: '.$GLOBALS['host'].$url);
            }

            //открепление агента
            $res = $el_obj->GetList(
                array('ID' => 'ASC'),
                array(
                    'IBLOCK_ID' => rrsIblock::getIBlockId('client_agent_link'),
                    'PROPERTY_USER_ID' => $clientProfile['USER']['ID']
                ),
                false,
                array('nTopCount' => 1),
                array('IBLOCK_ID', 'ID')
            );
            if ($data = $res->Fetch()) {
                $el_obj->Delete($data['ID']);
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

unset($res, $data, $el_obj, $user_obj, $check_deals_ids);