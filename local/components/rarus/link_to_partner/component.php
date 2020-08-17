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
$arResult['PARTNERS_LIST'] = array();
$arResult['CUR_PARTNER_DATA'] = array();

$partner_list_ids = array();
//$region_id = 0;

if (!isset($arParams['U_ID']) || !is_numeric($arParams['U_ID'])) {
    $arResult['ERROR'] = 'Y';
    $arResult['ERROR_MESSAGE'] = 'Не указан ID пользователя.';
}

if (!isset($arParams['IB_CODE']) || trim($arParams['IB_CODE']) == '') {
    $arResult['ERROR'] = 'Y';
    $arResult['ERROR_MESSAGE'] = 'Не указан код инфоблока со свойствами пользователя.';
}

//if partner link is choosed
if (isset($_GET['link_to_partner']) && is_numeric($_GET['link_to_partner'])) {
    $farmerProfile = farmer::getProfile($arParams['U_ID'], true);

    $res = CIBlockElement::GetList(
        array('ID' => 'ASC'),
        array(
            'IBLOCK_ID' => rrsIblock::getIBlockId($arParams['IB_CODE']),
            'PROPERTY_USER' => $arParams['U_ID'],
            'PROPERTY_PARTNER_ID' => false
        ),
        false,
        array('nTopCount' => 1),
        array('ID', 'IBLOCK_ID')
    );
    if ($data = $res->Fetch()) {
        $prop = array(
            'PARTNER_ID' => $_GET['link_to_partner']
        );
        if ($farmerProfile['USER']['UF_DEMO']) {
            $prop['PARTNER_ID_TIMESTAMP'] = 0;
        }
        else {
            $prop['PARTNER_ID_TIMESTAMP'] = time();
        }

        CIBlockElement::SetPropertyValuesEx($data['ID'], $data['IBLOCK_ID'], $prop);

        $noticeList = notice::getNoticeList();
        $partnerProfile = partner::getProfile($_GET['link_to_partner'], true);
        //$farmerProfile = farmer::getProfile($arParams['U_ID']);

        $url = '/partner/users/linked_users/?u_id=' . $arParams['U_ID'];
        if (in_array($noticeList['e_l']['ID'], $partnerProfile['PROPERTY_NOTICE_VALUE'])) {
            $arEventFields = array(
                'LINKED_URL' => $GLOBALS['host'].$url,
                'FARMER_ID' => $arParams['U_ID'],
                'EMAIL' => $partnerProfile['USER']['EMAIL'],
                'PROFILE_LINK' => $GLOBALS['host'].'/profile/?uid='.$arParams['U_ID'],
                'COMPANY_NAME' => $farmerProfile['PROPERTY_FULL_COMPANY_NAME_VALUE']
            );
            CEvent::Send('PARTNER_LINK_FARMER', 's1', $arEventFields);
        }
        if (in_array($noticeList['c_l']['ID'], $partnerProfile['PROPERTY_NOTICE_VALUE'])) {
            notice::addNotice($partnerProfile['USER']['ID'], 'l', 'Прикрепление поставщика', $url, '#' . $arParams['U_ID']);
        }
        if (in_array($noticeList['s_l']['ID'], $partnerProfile['PROPERTY_NOTICE_VALUE']) && $partnerProfile['PROPERTY_PHONE_VALUE']) {
            $phone = str_replace(array('+', ' ', '(', ')', '-'), "", $partnerProfile['PROPERTY_PHONE_VALUE']);
            notice::sendNoticeSMS($phone, 'Прикрепление поставщика: '.$GLOBALS['host'].$url);
        }

        LocalRedirect($APPLICATION->GetCurPage());
        exit;
    }
}

if (isset($_GET['link_to_partner'])) {
    //if partner link is choosed, but error found -> redirect to default page (or send error letter to admin)
    LocalRedirect($APPLICATION->GetCurPage());
    exit;
}

//check current link partner
$partner_id = 0;
$res = CIBlockElement::GetList(array('ID' => 'ASC'), array('IBLOCK_CODE' => $arParams['IB_CODE'], 'PROPERTY_USER' => $arParams['U_ID']), false, array('nTopCount' => 1), array('ID', 'IBLOCK_ID', 'PROPERTY_PARTNER_ID', 'PROPERTY_REGION', 'PROPERTY_VERIFIED', 'PROPERTY_FULL_COMPANY_NAME'));
if ($data = $res->Fetch()) {
    if (isset($data['PROPERTY_FULL_COMPANY_NAME_VALUE']) && trim($data['PROPERTY_FULL_COMPANY_NAME_VALUE']) != '') {
        //set title to partners link page
        $APPLICATION->SetTitle($data['PROPERTY_FULL_COMPANY_NAME_VALUE']);
    }

    if (isset($_GET['unlink_partner']) && $_GET['unlink_partner'] == 'y') {
        //if partner unlink is choosed
        CIBlockElement::SetPropertyValuesEx($data['ID'], $data['IBLOCK_ID'], array('PARTNER_ID' => false, 'VERIFIED' => rrsIblock::getPropListKey('farmer_profile', 'VERIFIED', 'no'), 'PARTNER_ID_TIMESTAMP' => 0));
        //unlink agent
        $agentObj = new agent();
        $agentObj->dropLinkWithFarmer($arParams['U_ID'], $data['PROPERTY_PARTNER_ID_VALUE']);

        $noticeList = notice::getNoticeList();
        $partnerProfile = partner::getProfile($data['PROPERTY_PARTNER_ID_VALUE'], true);
        $farmerProfile = farmer::getProfile($arParams['U_ID']);

        $url = '/partner/users/linked_users/';
        if (in_array($noticeList['e_l']['ID'], $partnerProfile['PROPERTY_NOTICE_VALUE'])) {
            $arEventFields = array(
                'LINKED_URL' => $GLOBALS['host'].$url,
                'FARMER_ID' => $arParams['U_ID'],
                'EMAIL' => $partnerProfile['USER']['EMAIL'],
                'PROFILE_LINK' => $GLOBALS['host'].'/profile/?uid='.$arParams['U_ID'],
                'COMPANY_NAME' => $farmerProfile['PROPERTY_FULL_COMPANY_NAME_VALUE']
            );
            CEvent::Send('PARTNER_UNLINK_FARMER', 's1', $arEventFields);
        }
        if (in_array($noticeList['c_l']['ID'], $partnerProfile['PROPERTY_NOTICE_VALUE'])) {
            notice::addNotice($partnerProfile['USER']['ID'], 'l', 'Открепление поставщика', $url, '#' . $arParams['U_ID']);
        }
        if (in_array($noticeList['s_l']['ID'], $partnerProfile['PROPERTY_NOTICE_VALUE']) && $partnerProfile['PROPERTY_PHONE_VALUE']) {
            $phone = str_replace(array('+', ' ', '(', ')', '-'), "", $partnerProfile['PROPERTY_PHONE_VALUE']);
            notice::sendNoticeSMS($phone, 'Открепление поставщика: '.$GLOBALS['host'].$url);
        }

        LocalRedirect($APPLICATION->GetCurPage());
        exit;
    }

    if (is_numeric($data['PROPERTY_PARTNER_ID_VALUE'])) {
        $partner_id = $data['PROPERTY_PARTNER_ID_VALUE'];
        if (isset($data['PROPERTY_VERIFIED_ENUM_ID'])
            && $data['PROPERTY_VERIFIED_ENUM_ID'] == rrsIblock::getPropListKey('farmer_profile', 'VERIFIED', 'yes')
        ) {
            $arResult['CUR_PARTNER_DATA'] = array(
                'VERIFIED' => $data['PROPERTY_VERIFIED_ENUM_ID']
            );
        }
    }

    /*if(is_numeric($data['PROPERTY_REGION_VALUE']))
        $region_id = $data['PROPERTY_REGION_VALUE'];*/
}

//check uncomplete deals
$arFilter = array('IBLOCK_CODE' => 'deals_deals', 'PROPERTY_STATUS' => 52);
if ($arParams['IB_CODE'] == 'farmer_profile') {
    $arFilter['PROPERTY_FARMER'] = $arParams['U_ID'];
}
elseif ($arParams['IB_CODE'] == 'transport_profile') {
    $arFilter['PROPERTY_TRANSPORT'] = $arParams['U_ID'];
}
$res = CIBlockElement::GetList(array('ID' => 'ASC'), $arFilter, false, array('nTopCount' => 1), array('ID'));
if ($data = $res->Fetch()) {
    $arResult['UNCOMPLETE_DEALS'] = 'y';
}

if ((!isset($arResult['UNCOMPLETE_DEALS']) || $arResult['UNCOMPLETE_DEALS'] != 'y') && isset($_GET['unlink_partner']) && $_GET['unlink_partner'] == 'y') {
    //if partner unlink is choosed, but no partner found -> redirect to default page (or send error letter to admin)
    LocalRedirect($APPLICATION->GetCurPage());
    exit;
}

//filter partners by regions (if set)
if (is_numeric($arParams['REGION_ID'])) {
    $res = CIBlockElement::GetList(
        array('ID' => 'ASC'),
        array(
            'IBLOCK_ID' => rrsIblock::getIBlockId('partner_profile'),
            'PROPERTY_REGION' => $arParams['REGION_ID'],
            '!PROPERTY_USER' => false),
        false,
        false,
        array('PROPERTY_USER', 'PROPERTY_REGION')
    );
    while ($data = $res->Fetch()) {
        $partner_list_ids[$data['PROPERTY_USER_VALUE']] = true;
    }
}
if ($partner_id != 0 && !isset($partner_list_ids[$partner_id])) {
    $partner_list_ids[$partner_id] = true;
}

//get partners list
$arFilter = array('GROUPS_ID' => 10, 'ACTIVE' => 'Y');
if ($arParams['REGION_ID'] > 0) {
    //set region partners filter
    if (count($partner_list_ids) > 0) {
        $arFilter['ID'] = implode(' | ', array_keys($partner_list_ids));
    }
    else {
        //if $partner_list_ids is empty
        $arFilter['ID'] = 0;
    }
}
$res = CUser::GetList(($by="id"), ($order="asc"), $arFilter, array('FIELDS' => array('ID', 'ACTIVE', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'LOGIN')));
while ($data = $res->Fetch()) {
    if($partner_id != 0 && $partner_id == $data['ID']) {
        //current linked partner
        $arResult['CURRENT_PARTNER_DATA'] = array(
            'ID' => $data['ID'],
            'ACTIVE' => $data['ACTIVE'],
            'NAME' => $data['NAME'],
            'LAST_NAME' => $data['LAST_NAME'],
            'SECOND_NAME' => $data['SECOND_NAME'],
            'LOGIN' => $data['LOGIN']
        );
    }
    else {
        //list of partners (not linked)
        $arResult['PARTNERS_LIST'][] = array(
            'ID' => $data['ID'],
            'ACTIVE' => $data['ACTIVE'],
            'NAME' => $data['NAME'],
            'LAST_NAME' => $data['LAST_NAME'],
            'SECOND_NAME' => $data['SECOND_NAME'],
            'LOGIN' => $data['LOGIN']
        );
    }
}

$this->IncludeComponentTemplate();

unset($res, $data);