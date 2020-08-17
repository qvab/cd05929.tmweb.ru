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

$el_obj = new CIBlockElement;
if ($arResult['ERROR'] != 'Y') {
    $u_id = $arParams['U_ID'];
    $user_obj = new CUser;
    $check_deals_ids = array(); //user ids to check on unclosed deals
    $check_unclosed_deals_ids = array(); //user ids that were checked and have unclosed deals
    $arResult['USERS_LIST'] = array(); //list of linked users to show to partner

    $res = $el_obj->GetList(
        array('ID' => 'DESC'),
        array(
            'IBLOCK_CODE' => $arParams['IB_CODE'],
            'PROPERTY_PARTNER_ID' => $u_id
        ),
        false,
        false,
        array(
            'PROPERTY_USER',
            'IBLOCK_ID',
            'PROPERTY_PARTNER_LINK_DOC',
            'PROPERTY_PARTNER_LINK_DOC_NUM',
            'PROPERTY_PARTNER_LINK_DOC_DATE',
            'PROPERTY_FULL_COMPANY_NAME',
            'PROPERTY_IP_FIO'
        )
    );
    while ($data = $res->Fetch()) {
        $check_deals_ids[$data['PROPERTY_USER_VALUE']] = true;
        $company = $data['PROPERTY_FULL_COMPANY_NAME_VALUE'];
        if ($company == '')
            $company = $data['PROPERTY_IP_FIO_VALUE'];
        $arResult['USERS_LIST'][$data['PROPERTY_USER_VALUE']] = array(
            'IBLOCK_ID' => $data['IBLOCK_ID'],
            'LINK_DOC' => (is_numeric($data['PROPERTY_PARTNER_LINK_DOC_VALUE']) ? $data['PROPERTY_PARTNER_LINK_DOC_VALUE'] : 'n'),
            'LINK_DOC_NUM' => $data['PROPERTY_PARTNER_LINK_DOC_NUM_VALUE'],
            'LINK_DOC_DATE' => $data['PROPERTY_PARTNER_LINK_DOC_DATE_VALUE'],
            'COMPANY_NAME' => $company
        );
    }
    $res = $user_obj->GetList(
        ($by = "ID"), ($order = "DESC"),
        array('ID' => implode(' | ', array_keys($arResult['USERS_LIST']))),
        array('FIELDS' => array('ID', 'EMAIL', 'ACTIVE'), 'SELECT' => array('UF_DEMO'))
    );
    while ($data = $res->Fetch()) {
        if (isset($arResult['USERS_LIST'][$data['ID']])) {
            $arResult['USERS_LIST'][$data['ID']]['EMAIL'] = $data['EMAIL'];
            $arResult['USERS_LIST'][$data['ID']]['ACTIVE'] = $data['ACTIVE'];
            $arResult['USERS_LIST'][$data['ID']]['UF_DEMO'] = $data['UF_DEMO'];
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
        $res = $el_obj->GetList(array('ID' => 'ASC'), array('IBLOCK_CODE' => $arParams['IB_CODE'], 'PROPERTY_PARTNER_ID' => $u_id, 'PROPERTY_USER' => $_POST['uid']), false, array('nTopCount' => 1), array('ID'));
        if($data = $res->Fetch())
        {
            $el_obj->SetPropertyValuesEx($data['ID'], $arResult['USERS_LIST'][$_POST['uid']]['IBLOCK_ID'], array('PARTNER_LINK_DOC' => $_FILES['doc_val'], 'PARTNER_ID_TIMESTAMP' => 0, 'PARTNER_LINK_DOC_NUM' => $_POST['doc_num'], 'PARTNER_LINK_DOC_DATE' => $_POST['doc_date']));

            $noticeList = notice::getNoticeList();
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
            }

            LocalRedirect($APPLICATION->GetCurDir(false));
            exit;
        }
        else
        {//error add doc to user

        }
    }

    //check if resend invite is need
    $arResult['MESS_STR'] = '';
    if(isset($_GET['resend']) && is_numeric($_GET['resend']) && isset($arResult['USERS_LIST'][$_GET['resend']]))
    {
        if($arResult['USERS_LIST'][$_GET['resend']]['ACTIVE'] == 'Y')
        {
            $arResult['MESS_STR'] = 'Аккаунт пользователя уже успешно активирован.';
        }
        else
        {
            $res = $user_obj->GetList(($by = "ID"), ($order = "DESC"), array('ID' => $_GET['resend']), array('FIELDS' => array('ID', 'EMAIL'), 'SELECT' => array('UF_HASH_INVITE')));
            if($data = $res->Fetch())
            {
                if($data['ACTIVE'] == 'Y')
                {
                    $arResult['MESS_STR'] = 'Аккаунт пользователя уже успешно активирован.';
                }
                else
                {
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
    if(isset($_GET['resend_success']) && $_GET['resend_success'] == 'y')
    {
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
            $el_obj->SetPropertyValuesEx($data['ID'], $data['IBLOCK_ID'], array('PARTNER_ID' => false, 'PARTNER_LINK_DOC' => array('del' => 'Y'), 'PARTNER_ID_TIMESTAMP' => 0, 'PARTNER_LINK_DOC_NUM' => false, 'PARTNER_LINK_DOC_DATE' => false));

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
$res = $el_obj->GetList(array('ID' => 'ASC'), array('IBLOCK_CODE' => 'partner_profile', 'ACTIVE' => 'Y', 'PROPERTY_USER' => $arParams['U_ID']), false, array('nTopCount' => 1), array('PROPERTY_FULL_COMPANY_NAME'));
if($data = $res->Fetch())
{
    if(isset($data['PROPERTY_FULL_COMPANY_NAME_VALUE']) && trim($data['PROPERTY_FULL_COMPANY_NAME_VALUE']) != '')
    {
        $APPLICATION->SetTitle($data['PROPERTY_FULL_COMPANY_NAME_VALUE']);
    }
}

$this->IncludeComponentTemplate();

unset($res, $data, $el_obj, $user_obj, $check_deals_ids, $check_unclosed_deals_ids);