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


// Обработчик AJAX
if(!empty($_GET['AJAX']) && $_GET['AJAX'] == 'Y') {
    include ('ajax.php');
}


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

if ($arResult['ERROR'] != 'Y') {
    $u_id = $arParams['U_ID'];

    $agentObj = new agent();

    $arResult['ITEMS'] = $agentObj->getAgentsOfPartner($u_id);

    //check if resend invite is need
    $arResult['MESS_STR'] = '';
    if (isset($_GET['resend']) && is_numeric($_GET['resend']) && isset($arResult['ITEMS'][$_GET['resend']])) {
        if ($arResult['ITEMS'][$_GET['resend']]['ACTIVE'] == 'Y') {
            $arResult['MESS_STR'] = 'Аккаунт пользователя уже успешно активирован.';
        }
        else {
            $user_obj = new CUser;
            $res = $user_obj->GetList(($by = "ID"), ($order = "DESC"), array('ID' => $_GET['resend']), array('FIELDS' => array('ID', 'EMAIL'), 'SELECT' => array('UF_HASH_INVITE')));
            if ($data = $res->Fetch()) {
                if ($data['ACTIVE'] == 'Y') {
                    $arResult['MESS_STR'] = 'Аккаунт пользователя уже успешно активирован.';
                }
                else {
                    $arEventFields = array(
                        'EMAIL' => $data['EMAIL'],
                        'HREF'  => $GLOBALS['host'] . '/?reg_hash=' . $data['UF_HASH_INVITE'] . rrsIblock::getIBlockId('agent_profile') . '#action=register',
                        'TO'    => 'агента'
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

    //check if delete agent is need
    if (isset($_GET['unlink_agent']) && is_numeric($_GET['unlink_agent']) && isset($arResult['ITEMS'][$_GET['unlink_agent']]))
    {
        if($agentObj->deleteAgent($_GET['unlink_agent'], $u_id))
        {
            $arResult['MESS_STR'] = 'Агент удален из системы';
            unset($arResult['ITEMS'][$_GET['unlink_agent']]);
        }
    }

    //unlink_partner_del
    //check if there is unlink action
    if (isset($_GET['unlink_partner_del']) && is_numeric($_GET['unlink_partner_del']) && isset($arResult['ITEMS'][$_GET['unlink_partner_del']])) {//delete user
        $agentObj->deleteAgent($_GET['unlink_partner_del'], $u_id);
        partner::deleteNotRespUser($_GET['unlink_partner_del']);
        LocalRedirect($APPLICATION->GetCurDir());
        exit;
    }

    //проверка загрузки договора организатором
    if(isset($_POST['add_doc']) && $_POST['add_doc'] == 'y'
        && is_numeric($_POST['uid']) && isset($arResult['ITEMS'][$_POST['uid']])
        && isset($arResult['ITEMS'][$_POST['uid']]['LINK_DOC']) && $arResult['ITEMS'][$_POST['uid']]['LINK_DOC'] == 'n'
        && isset($_FILES['doc_val']['error']) && $_FILES['doc_val']['error'] == 0
        && isset($_POST['doc_num']) && trim($_POST['doc_num']) != ''
        && isset($_POST['doc_date']) && trim($_POST['doc_date']) != ''
    )
    {
        $el_obj = new CIBlockElement;
        $res = $el_obj->GetList(array('ID' => 'ASC'), array('IBLOCK_ID' => rrsIblock::getIBlockId('agent_partner_link'), 'PROPERTY_PARTNER_ID' => $u_id, 'PROPERTY_USER_ID' => $_POST['uid']), false, array('nTopCount' => 1), array('ID', 'IBLOCK_ID'));
        if($data = $res->Fetch())
        {
            $el_obj->SetPropertyValuesEx($data['ID'], $data['IBLOCK_ID'], array('PARTNER_LINK_DOC' => $_FILES['doc_val'], 'PARTNER_LINK_DOC_NUM' => $_POST['doc_num'], 'PARTNER_LINK_DOC_DATE' => $_POST['doc_date']));

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