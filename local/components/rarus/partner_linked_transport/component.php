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
$verifiedStatusList = rrsIblock::getPropListKey('transport_partner_link', 'VERIFIED');
foreach ($verifiedStatusList as $item) {
    $verifiedStatus[$item['ID']] = $item['XML_ID'];
}

if ($arResult['ERROR'] != 'Y') {
    $u_id = $arParams['U_ID'];
    $user_obj = new CUser;
    $check_deals_ids = array(); //user ids to check on unclosed deals
    $arResult['USERS_LIST'] = array(); //list of linked users to show to partner

    //get activated users
    $res = $el_obj->GetList(
        array('ID' => 'DESC'),
        array(
            'IBLOCK_ID' => rrsIblock::getIBlockId('transport_partner_link'),
            'PROPERTY_PARTNER_ID' => $u_id
        ),
        false,
        false,
        array('ID','PROPERTY_USER_ID', 'IBLOCK_ID', 'PROPERTY_PARTNER_LINK_DATE','PROPERTY_VERIFIED')
    );
    while ($data = $res->Fetch()) {
        $check_deals_ids[$data['PROPERTY_USER_ID_VALUE']] = true;
        $arResult['USERS_LIST'][$data['PROPERTY_USER_ID_VALUE']] = array(
            'IBLOCK_ID' => $data['IBLOCK_ID'],
            'LINK_DATE' => $data['PROPERTY_PARTNER_LINK_DATE_VALUE'],
            'LINK_ID' => $data['ID']
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
        array('PROPERTY_USER', 'IBLOCK_ID', 'PROPERTY_FULL_COMPANY_NAME', 'PROPERTY_IP_FIO')
    );
    while ($data = $res->Fetch()) {
        if ($data['PROPERTY_IP_FIO_VALUE'] != '')
            $company = $data['PROPERTY_IP_FIO_VALUE'];
        else
            $company = $data['PROPERTY_FULL_COMPANY_NAME_VALUE'];

        if ($arResult['USERS_LIST'][$data['PROPERTY_USER_VALUE']]['IBLOCK_ID'] > 0) {
            $arResult['USERS_LIST'][$data['PROPERTY_USER_VALUE']]['COMPANY_NAME'] = $company;
        }
        else {
            $arResult['USERS_LIST'][$data['PROPERTY_USER_VALUE']] = array(
                'IBLOCK_ID' => $data['IBLOCK_ID'],
                'COMPANY_NAME' => $company
            );
        }
    }

    $userList = array_keys($arResult['USERS_LIST']);

    if (is_array($userList) && sizeof($userList) > 0) {
        $res = $user_obj->GetList(
            ($by = "ID"), ($order = "DESC"),
            array('ID' => implode(' | ', $userList)),
            array('FIELDS' => array('ID', 'EMAIL', 'ACTIVE', 'NAME', 'LAST_NAME'))
        );
        while ($data = $res->Fetch()) {
            if (isset($arResult['USERS_LIST'][$data['ID']])) {
                $arResult['USERS_LIST'][$data['ID']]['EMAIL'] = $data['EMAIL'];
                $arResult['USERS_LIST'][$data['ID']]['ACTIVE'] = $data['ACTIVE'];
            }
        }
    }



    /*//get inactivated users
    $res = $el_obj->GetList(
        array('ID' => 'DESC'),
        array(
            'IBLOCK_CODE' => 'transport_profile',
            'PROPERTY_PARTNER_ID' => $u_id),
        false,
        false,
        array('PROPERTY_USER', 'IBLOCK_ID')
    );
    while ($data = $res->Fetch()) {
        if (is_numeric($data['PROPERTY_USER_VALUE']) && $data['PROPERTY_USER_VALUE'] > 0) {
            $arResult['USERS_LIST'][$data['PROPERTY_USER_VALUE']] = array('IBLOCK_ID' => $data['IBLOCK_ID'], 'LINK_DOC' => 'n', 'LINK_DOC_NUM' => '', 'LINK_DOC_DATE' => '');
        }
    }*/

    /*//get activated users
    $res = $el_obj->GetList(
        array('ID' => 'DESC'),
        array('IBLOCK_CODE' => 'transport_partner_link', 'PROPERTY_PARTNER_ID' => $u_id),
        false,
        false,
        array('PROPERTY_USER_ID', 'IBLOCK_ID', 'PROPERTY_PARTNER_LINK_DOC', 'PROPERTY_PARTNER_LINK_DOC_NUM', 'PROPERTY_PARTNER_LINK_DOC_DATE')
    );
    while ($data = $res->Fetch()) {
        $check_deals_ids[$data['PROPERTY_USER_ID_VALUE']] = true;
        $arResult['USERS_LIST'][$data['PROPERTY_USER_ID_VALUE']] = array(
            'IBLOCK_ID' => $data['IBLOCK_ID'],
            'LINK_DOC' => (is_numeric($data['PROPERTY_PARTNER_LINK_DOC_VALUE']) ? $data['PROPERTY_PARTNER_LINK_DOC_VALUE'] : 'n'),
            'LINK_DOC_NUM' => $data['PROPERTY_PARTNER_LINK_DOC_NUM_VALUE'],
            'LINK_DOC_DATE' => $data['PROPERTY_PARTNER_LINK_DOC_DATE_VALUE']
        );
    }*/

    /*if (is_array($arResult['USERS_LIST']) && sizeof($arResult['USERS_LIST']) > 0) {
        $res = $el_obj->GetList(
            array('ID' => 'DESC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('transport_profile'),
                'PROPERTY_USER' => array_keys($arResult['USERS_LIST'])
            ),
            false,
            false,
            array('ID', 'NAME', 'PROPERTY_USER', 'PROPERTY_FULL_COMPANY_NAME', 'PROPERTY_IP_FIO')
        );
        while ($ob = $res->Fetch()) {
            if (!$ob['PROPERTY_FULL_COMPANY_NAME_VALUE'] && $ob['PROPERTY_IP_FIO_VALUE'] != '') {
                $arResult['USERS_LIST'][$ob['PROPERTY_USER_VALUE']]['COMPANY_NAME'] = $ob['PROPERTY_IP_FIO_VALUE'];
            }
            else {
                $arResult['USERS_LIST'][$ob['PROPERTY_USER_VALUE']]['COMPANY_NAME'] = $ob['PROPERTY_FULL_COMPANY_NAME_VALUE'];
            }
        }


        $res = $user_obj->GetList(($by = "ID"), ($order = "DESC"), array('ID' => implode(' | ', array_keys($arResult['USERS_LIST']))), array('FIELDS' => array('ID', 'EMAIL', 'ACTIVE')));
        while($data = $res->Fetch())
        {
            if(isset($arResult['USERS_LIST'][$data['ID']]))
            {
                $arResult['USERS_LIST'][$data['ID']]['EMAIL'] = $data['EMAIL'];
                $arResult['USERS_LIST'][$data['ID']]['ACTIVE'] = $data['ACTIVE'];
            }
        }
    }*/

    //check doc upload action
    /*if(isset($_POST['add_doc']) && $_POST['add_doc'] == 'y'
        && is_numeric($_POST['uid']) && isset($arResult['USERS_LIST'][$_POST['uid']])
        && isset($arResult['USERS_LIST'][$_POST['uid']]['LINK_DOC']) && $arResult['USERS_LIST'][$_POST['uid']]['LINK_DOC'] == 'n'
        && isset($_FILES['doc_val']['error']) && $_FILES['doc_val']['error'] == 0
        && isset($_POST['doc_num']) && trim($_POST['doc_num']) != ''
        && isset($_POST['doc_date']) && trim($_POST['doc_date']) != ''
    )
    {//if user temporary linked, but doc was not uploaded yet
        $res = $el_obj->Getlist(array('ID' => 'ASC'), array('IBLOCK_CODE' => 'transport_partner_link', 'PROPERTY_PARTNER_ID' => $u_id, 'PROPERTY_USER_ID' => $_POST['uid']), false, array('nTopCount' => 1), array('ID'));
        if($data = $res->Fetch())
        {
            $el_obj->SetPropertyValuesEx($data['ID'], $arResult['USERS_LIST'][$_POST['uid']]['IBLOCK_ID'], array('PARTNER_LINK_DOC' => $_FILES['doc_val'], 'PARTNER_ID_TIMESTAMP' => 0, 'PARTNER_LINK_DOC_NUM' => $_POST['doc_num'], 'PARTNER_LINK_DOC_DATE' => $_POST['doc_date']));

            $noticeList = notice::getNoticeList();
            $transportProfile = partner::getProfile($_POST['uid'], true);

            $url = '/transport/link_to_partner/';
            if (in_array($noticeList['e_l']['ID'], $transportProfile['PROPERTY_NOTICE_VALUE'])) {
                $arEventFields = array(
                    'LINKED_URL' => $GLOBALS['host'].$url,
                    'PARTNER_ID' => $u_id,
                    'EMAIL' => $transportProfile['USER']['EMAIL'],
                );
                CEvent::Send('TRANSPORT_ADD_DOC', 's1', $arEventFields);
            }
            if (in_array($noticeList['c_l']['ID'], $transportProfile['PROPERTY_NOTICE_VALUE'])) {
                notice::addNotice($transportProfile['USER']['ID'], 'l', 'Добавлен договор с организатором', $url, '#' . $u_id);
            }
            if (in_array($noticeList['s_l']['ID'], $transportProfile['PROPERTY_NOTICE_VALUE']) && $transportProfile['PROPERTY_PHONE_VALUE']) {
                $phone = str_replace(array('+', ' ', '(', ')', '-'), "", $transportProfile['PROPERTY_PHONE_VALUE']);
                notice::sendNoticeSMS($phone, 'Добавлен договор с организатором: '.$GLOBALS['host'].$url);
            }

            LocalRedirect($APPLICATION->GetCurDir(false));
            exit;
        }
        else
        {//error add doc to user

        }
    }*/

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
                        'TO' => 'перевозчика'
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
        //check transport uncomplete deals
        $res = $el_obj->GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('deals_deals'),
                'ACTIVE' => 'Y',
                'PROPERTY_TRANSPORT' => array_keys($check_deals_ids),
                'PROPERTY_STATUS' => rrsIblock::getPropListKey('deals_deals', 'STATUS', 'open')
            ),
            false,
            false,
            array('PROPERTY_TRANSPORT')
        );
        while ($data = $res->Fetch()) {
            $arResult['UNCOMPLETE_DEALS_IDS'][$data['PROPERTY_TRANSPORT_VALUE']] = true;
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
                'IBLOCK_ID' => rrsIblock::getIBlockId('transport_partner_link'),
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
            $transportProfile = transport::getProfile($_GET['unlink_partner'], true);
            $partnerProfile = partner::getProfile($arParams['U_ID']);

            $url = '/transport/link_to_partner/';
            if (in_array($noticeList['e_l']['ID'], $transportProfile['PROPERTY_NOTICE_VALUE'])) {
                $arEventFields = array(
                    'LINKED_URL' => $GLOBALS['host'].$url,
                    'PARTNER_ID' => $u_id,
                    'EMAIL' => $transportProfile['USER']['EMAIL'],
                    'PROFILE_LINK' => $GLOBALS['host'].'/profile/?uid='.$arParams['U_ID'],
                    'COMPANY_NAME' => $partnerProfile['PROPERTY_FULL_COMPANY_NAME_VALUE']
                );
                CEvent::Send('TRANSPORT_UNLINK_TRANSPORT', 's1', $arEventFields);
            }
            if (in_array($noticeList['c_l']['ID'], $transportProfile['PROPERTY_NOTICE_VALUE'])) {
                notice::addNotice($transportProfile['USER']['ID'], 'l', 'Открепление от организатора', $url, '#' . $_GET['unlink_partner']);
            }
            if (in_array($noticeList['s_l']['ID'], $transportProfile['PROPERTY_NOTICE_VALUE']) && $transportProfile['PROPERTY_PHONE_VALUE']) {
                $phone = str_replace(array('+', ' ', '(', ')', '-'), "", $transportProfile['PROPERTY_PHONE_VALUE']);
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

unset($res, $data, $el_obj, $user_obj, $check_deals_ids);
