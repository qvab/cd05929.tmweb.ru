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
$arResult['FARMERS_LIST'] = array();

if(!isset($arParams['U_ID']) || !is_numeric($arParams['U_ID']))
{
    $arResult['ERROR'] = 'Y';
    $arResult['ERROR_MESSAGE'] = 'Не указан ID пользователя.';
}

if($arResult['ERROR'] != 'Y')
{
    $u_id = $arParams['U_ID'];
    $el_obj = new CIBlockElement;
    $user_obj = new CUser;
    $check_deals_ids = array(); //user ids to check on unclosed deals
    $check_unclosed_deals_ids = array(); //user ids that were checked and have unclosed deals
    $arResult['USERS_LIST'] = array(); //list of linked users to show to partner

    $res = $el_obj->GetList(array('ID' => 'DESC'), array('IBLOCK_CODE' => 'client_profile', 'PROPERTY_PARTNER_ID' => $u_id), false, false, array('PROPERTY_USER', 'IBLOCK_ID'));
    while($data = $res->Fetch())
    {
        $arResult['USERS_LIST'][$data['PROPERTY_USER_VALUE']] = array('TYPE' => 'Покупатель', 'IBLOCK_ID' => $data['IBLOCK_ID']);
    }
    $res = $el_obj->GetList(array('ID' => 'DESC'), array('IBLOCK_CODE' => 'farmer_profile', 'PROPERTY_PARTNER_ID' => $u_id), false, false, array('PROPERTY_USER', 'IBLOCK_ID', 'PROPERTY_PARTNER_LINK_DOC'));
    while($data = $res->Fetch())
    {
        $check_deals_ids[$data['PROPERTY_USER_VALUE']] = true;
        $arResult['USERS_LIST'][$data['PROPERTY_USER_VALUE']] = array('TYPE' => 'Поставщик', 'IBLOCK_ID' => $data['IBLOCK_ID'], 'LINK_DOC' => (is_numeric($data['PROPERTY_PARTNER_LINK_DOC_VALUE']) ? 'y' : 'n') );
    }
    $res = $el_obj->GetList(array('ID' => 'DESC'), array('IBLOCK_CODE' => 'transport_profile', 'PROPERTY_PARTNER_ID' => $u_id), false, false, array('PROPERTY_USER', 'IBLOCK_ID'));
    while($data = $res->Fetch())
    {
        $arResult['USERS_LIST'][$data['PROPERTY_USER_VALUE']] = array('TYPE' => 'Транспортная компания', 'IBLOCK_ID' => $data['IBLOCK_ID']);
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

    //check doc upload action
    if(isset($_POST['add_doc']) && $_POST['add_doc'] == 'y'
        && is_numeric($_POST['uid']) && isset($arResult['USERS_LIST'][$_POST['uid']])
        && isset($arResult['USERS_LIST'][$_POST['uid']]['LINK_DOC']) && $arResult['USERS_LIST'][$_POST['uid']]['LINK_DOC'] == 'n'
        && isset($_FILES['doc_val']['error']) && $_FILES['doc_val']['error'] == 0
    )
    {//if user temporary linked, but doc was not uploaded yet
        $res = $el_obj->GetList(array('ID' => 'ASC'), array('IBLOCK_CODE' => 'farmer_profile', 'PROPERTY_PARTNER_ID' => $u_id, 'PROPERTY_USER' => $_POST['uid']), false, array('nTopCount' => 1), array('ID'));
        if($data = $res->Fetch())
        {
            $el_obj->SetPropertyValuesEx($data['ID'], $arResult['USERS_LIST'][$_POST['uid']]['IBLOCK_ID'], array('PARTNER_LINK_DOC' => $_FILES['doc_val'], 'PARTNER_ID_TIMESTAMP' => 0));
            LocalRedirect('/personal/');
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
                    $arEventFields = array('EMAIL' => $data['EMAIL'], 'HREF' => $GLOBALS['host'] . '/register/?reg_hash=' . $data['UF_HASH_INVITE'] . $arResult['USERS_LIST'][$_GET['resend']]['IBLOCK_ID']);
                    $res_val = CEvent::Send("AGRO_INVITE_USER", "s1", $arEventFields);
                    LocalRedirect('/personal/?resend_success=y');
                    exit;
                }
            }
        }
    }
    if(isset($_GET['resend_success']) && $_GET['resend_success'] == 'y')
    {
        $arResult['MESS_STR'] = 'Повторное письмо направлено пользователю';
    }

    //check farmer uncomplete deals
    if(count($check_deals_ids) > 0)
    {
        $res = $el_obj->GetList(array('ID' => 'ASC'), array('IBLOCK_CODE' => 'deals_deals', 'PROPERTY_FARMER' => array_keys($check_deals_ids), 'PROPERTY_STATUS' => 52), false, false, array('PROPERTY_FARMER'));
        if($data = $res->Fetch())
        {
            $check_unclosed_deals_ids[$data['PROPERTY_FARMER_VALUE']] = true;
        }
    }
}

$this->IncludeComponentTemplate();

unset($res, $data, $el_obj, $user_obj, $check_deals_ids, $check_unclosed_deals_ids);