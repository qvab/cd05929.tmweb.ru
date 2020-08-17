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
$el_obj = new CIBlockElement;

$arResult['ERROR'] = '';
$arResult['ERROR_MESSAGE'] = '';
$arResult['PARTNERS_LIST'] = array();
//$arResult['CURRENT_PARTNER_DATA'] = array();

$partner_list_ids = array();
$partner_linked_list_data = array();
//$region_id = 0;

if (!isset($arParams['U_ID']) || !is_numeric($arParams['U_ID'])) {
    $arResult['ERROR'] = 'Y';
    $arResult['ERROR_MESSAGE'] = 'Не указан ID пользователя.';
}

if (!isset($arParams['IB_CODE']) || trim($arParams['IB_CODE']) == '') {
    $arResult['ERROR'] = 'Y';
    $arResult['ERROR_MESSAGE'] = 'Не указан код инфоблока со свойствами пользователя.';
}

//check uncomplete deals
$arFilter = array(
    'IBLOCK_ID' => rrsIblock::getIBlockId('deals_deals'),
    'ACTIVE' => 'Y',
    'PROPERTY_STATUS' => rrsIblock::getPropListKey('deals_deals', 'STATUS', 'open'),
    'PROPERTY_CLIENT' => $arParams['U_ID']
);

$res = CIBlockElement::GetList(array('ID' => 'ASC'), $arFilter, false, false, array('ID'));
if ($data = $res->Fetch()) {
    $arResult['UNCOMPLETE_DEALS'] = 'y';
}

//check active requests
$arFilter = array(
    'IBLOCK_ID' => rrsIblock::getIBlockId('client_request'),
    'ACTIVE' => 'Y',
    //'ACTIVE_DATE' => 'Y',
    'PROPERTY_ACTIVE' => rrsIblock::getPropListKey('client_request', 'ACTIVE', 'yes'),
    'PROPERTY_CLIENT' => $arParams['U_ID']
);

$res = CIBlockElement::GetList(array('ID' => 'ASC'), $arFilter, false, false, array('ID'));
if ($data = $res->Fetch()) {
    $arResult['UNCOMPLETE_DEALS'] = 'y';
}

/*$res = CIBlockElement::Getlist(
    array('ID' => 'ASC'),
    array(
        'IBLOCK_ID' => rrsIblock::getIBlockId($arParams['IB_CODE']),
        'PROPERTY_USER' => $arParams['U_ID']
    ),
    false,
    array('nTopCount' => 1),
    array('ID', 'IBLOCK_ID', 'PROPERTY_REGION', 'PROPERTY_FULL_COMPANY_NAME')
);
if ($data = $res->Fetch()) {
    if(is_numeric($data['PROPERTY_REGION_VALUE']))
        $region_id = $data['PROPERTY_REGION_VALUE'];
}*/

//if partner link is choosed
if (isset($_GET['link_to_partner']) && is_numeric($_GET['link_to_partner'])) {
    //check if not double
    $res = CIBlockElement::GetList(
        array('ID' => 'ASC'),
        array(
            'IBLOCK_ID' => rrsIblock::getIBlockId('client_partner_link'),
            'PROPERTY_USER_ID' => $arParams['U_ID'],
            'PROPERTY_PARTNER_ID' => $_GET['link_to_partner']
        ),
        false,
        false,
        array('ID')
    );
    if ($res->SelectedRowsCount() > 0) {
        $arResult['ERROR_MESSAGE'] = 'Данный организатор уже привязан (возможно деактивирован, свяжитесь с администрацией)';
    }

    if ($arResult['ERROR_MESSAGE'] == '') {
        //link user to partner
        $new_id = $el_obj->Add(
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('client_partner_link'),
                'ACTIVE' => 'Y',
                'NAME' => 'Привязка покупателя [' . $arParams['U_ID'] . '] к организатору [' . $_GET['link_to_partner'] . ']',
                'PROPERTY_VALUES' => array(
                    'USER_ID' => $arParams['U_ID'],
                    'PARTNER_ID' => $_GET['link_to_partner'],
                    'PARTNER_LINK_DATE' => date('d.m.Y H:i:s'),
                    'PARTNER_ID_TIMESTAMP' => time()
                )
            )
        );

        if (intval($new_id) > 0) {
            $noticeList = notice::getNoticeList();
            $partnerProfile = partner::getProfile($_GET['link_to_partner'], true);
            $clientProfile = client::getProfile($arParams['U_ID']);

            $url = '/partner/users/linked_clients/';
            if (in_array($noticeList['e_l']['ID'], $partnerProfile['PROPERTY_NOTICE_VALUE'])) {
                $arEventFields = array(
                    'LINKED_URL' => $GLOBALS['host'].$url,
                    'CLIENT_ID' => $arParams['U_ID'],
                    'EMAIL' => $partnerProfile['USER']['EMAIL'],
                    'PROFILE_LINK' => $GLOBALS['host'].'/profile/?uid='.$arParams['U_ID'],
                    'COMPANY_NAME' => $clientProfile['PROPERTY_FULL_COMPANY_NAME_VALUE']
                );
                CEvent::Send('PARTNER_LINK_CLIENT', 's1', $arEventFields);
            }
            if (in_array($noticeList['c_l']['ID'], $partnerProfile['PROPERTY_NOTICE_VALUE'])) {
                notice::addNotice($partnerProfile['USER']['ID'], 'l', 'Прикрепление покупателя', $url, '#' . $arParams['U_ID']);
            }
            if (in_array($noticeList['s_l']['ID'], $partnerProfile['PROPERTY_NOTICE_VALUE']) && $partnerProfile['PROPERTY_PHONE_VALUE']) {
                $phone = str_replace(array('+', ' ', '(', ')', '-'), "", $partnerProfile['PROPERTY_PHONE_VALUE']);
                notice::sendNoticeSMS($phone, 'Прикрепление покупателя: '.$GLOBALS['host'].$url);
            }

            LocalRedirect($APPLICATION->GetCurPage() . (isset($_GET['region_id']) && is_numeric($_GET['region_id']) ? '?region_id=' . $_GET['region_id'] : '') . '');
            exit;
        }
        else {
            //add error
            $arResult['ERROR_MESSAGE'] = $el_obj->LAST_ERROR;
        }
    }
}

if (isset($_GET['link_to_partner'])) {
    //if partner link is choosed, but error found -> redirect to default page (or send error letter to admin)
    LocalRedirect($APPLICATION->GetCurPage());
    exit;
}

//if partner unlink is choosed
if (isset($_GET['unlink_partner']) && is_numeric($_GET['unlink_partner'])) {
    //check if link exist
    $res = CIBlockElement::GetList(
        array('ID' => 'ASC'),
        array(
            'IBLOCK_ID' => rrsIblock::getIBlockId('client_partner_link'),
            'PROPERTY_USER_ID' => $arParams['U_ID'],
            'PROPERTY_PARTNER_ID' => $_GET['unlink_partner']
        ),
        false,
        false,
        array('ID')
    );
    if ($data = $res->Fetch()) {
        //unlink partner
        $el_obj->Delete($data['ID']);

        $noticeList = notice::getNoticeList();
        $partnerProfile = partner::getProfile($_GET['unlink_partner'], true);
        $clientProfile = client::getProfile($arParams['U_ID']);

        $url = '/partner/users/linked_clients/';
        if (in_array($noticeList['e_l']['ID'], $partnerProfile['PROPERTY_NOTICE_VALUE'])) {
            $arEventFields = array(
                'LINKED_URL' => $GLOBALS['host'].$url,
                'CLIENT_ID' => $arParams['U_ID'],
                'EMAIL' => $partnerProfile['USER']['EMAIL'],
                'PROFILE_LINK' => $GLOBALS['host'].'/profile/?uid='.$arParams['U_ID'],
                'COMPANY_NAME' => $clientProfile['PROPERTY_FULL_COMPANY_NAME_VALUE']
            );
            CEvent::Send('PARTNER_UNLINK_CLIENT', 's1', $arEventFields);
        }
        if (in_array($noticeList['c_l']['ID'], $partnerProfile['PROPERTY_NOTICE_VALUE'])) {
            notice::addNotice($partnerProfile['USER']['ID'], 'l', 'Открепление покупателя', $url, '#' . $arParams['U_ID']);
        }
        if (in_array($noticeList['s_l']['ID'], $partnerProfile['PROPERTY_NOTICE_VALUE']) && $partnerProfile['PROPERTY_PHONE_VALUE']) {
            $phone = str_replace(array('+', ' ', '(', ')', '-'), "", $partnerProfile['PROPERTY_PHONE_VALUE']);
            notice::sendNoticeSMS($phone, 'Открепление покупателя: '.$GLOBALS['host'].$url);
        }
    }

    LocalRedirect($APPLICATION->GetCurPage() . (isset($_GET['region_id']) && is_numeric($_GET['region_id']) ? '?region_id=' . $_GET['region_id'] : ''));
    exit;
}
if (isset($_GET['unlink_partner']) && $_GET['unlink_partner'] == 'y') {
    //if partner unlink is choosed, there is uncomplete deals -> redirect to default page (or send error letter to admin)
    LocalRedirect($APPLICATION->GetCurPage());
    exit;
}

//filter partners by region (if set)
if (is_numeric($arParams['REGION_ID'])) {
    $res = CIBlockElement::GetList(
        array('ID' => 'ASC'),
        array(
            'IBLOCK_ID' => rrsIblock::getIBlockId('partner_profile'),
            'PROPERTY_REGION' => $arParams['REGION_ID'],
            '!PROPERTY_USER' => false
        ),
        false,
        false,
        array('PROPERTY_USER')
    );
    while( $data = $res->Fetch()) {
        $partner_list_ids[$data['PROPERTY_USER_VALUE']] = true;
    }
}

//add current linked partners links
$res = $el_obj->GetList(
    array('ID' => 'ASC'),
    array(
        'IBLOCK_ID' => rrsIblock::getIBlockId('client_partner_link'),
        'PROPERTY_USER_ID' => $arParams['U_ID'],
        '!PROPERTY_PARTNER_ID' => false
    ),
    false,
    false,
    array(
        'PROPERTY_PARTNER_ID',
        'PROPERTY_PARTNER_LINK_DATE'
    )
);
while ($data = $res->Fetch()) {
    $partner_list_ids[$data['PROPERTY_PARTNER_ID_VALUE']] = true;
    $partner_linked_list_data[$data['PROPERTY_PARTNER_ID_VALUE']] = array(
        'PARTNER_LINK_DATE' => $data['PROPERTY_PARTNER_LINK_DATE_VALUE'],
    );

    if(!isset($partner_linked_list_data[$data['PROPERTY_PARTNER_ID_VALUE']]['PARTNER_LINK_DATE']))
        $partner_linked_list_data[$data['PROPERTY_PARTNER_ID_VALUE']] = true;
}

//get partners data
$arFilter = array('GROUPS_ID' => 10, 'ACTIVE' => 'Y');
if (count($partner_list_ids) > 0) {
    $arFilter['ID'] = implode(' | ', array_keys($partner_list_ids));
}
else {//if $partner_list_ids is empty
    $arFilter['ID'] = 0;
}
global $USER;
$res = CUser::GetList(($by="id"), ($order="asc"), $arFilter, array('FIELDS' => array('ID', 'ACTIVE', 'NAME', 'LAST_NAME', 'LOGIN')));
while ($data = $res->Fetch()) {
    if (isset($partner_linked_list_data[$data['ID']])) {
        //current linked partner
        $arResult['CURRENT_PARTNER_DATA'] = array(
            'ID' => $data['ID'],
            'ACTIVE' => $data['ACTIVE'],
            'NAME' => $data['NAME'],
            'LAST_NAME' => $data['LAST_NAME'],
            'LOGIN' => $data['LOGIN'],
            'DOCS' => (isset($partner_linked_list_data[$data['ID']]['PARTNER_LINK_DATE']) ? array(
                    'PARTNER_LINK_DATE' => $partner_linked_list_data[$data['ID']]['PARTNER_LINK_DATE'],
                ) : array())
        );
    }
    else {
        if(!BlackList::clientPartnerBLExists($USER->getID(),$data['ID'])){
            //list of partners (not linked)
            $arResult['PARTNERS_LIST'][] = array(
                'ID' => $data['ID'],
                'ACTIVE' => $data['ACTIVE'],
                'NAME' => $data['NAME'],
                'LAST_NAME' => $data['LAST_NAME'],
                'LOGIN' => $data['LOGIN']
            );
        }
    }
}

//set linked partners page title
$res = $el_obj->GetList(
    array('ID' => 'ASC'),
    array(
        'IBLOCK_ID' => rrsIblock::getIBlockId('client_profile'),
        'ACTIVE' => 'Y',
        'PROPERTY_USER' => $arParams['U_ID']
    ),
    false, array('nTopCount' => 1),
    array('PROPERTY_FULL_COMPANY_NAME', 'PROPERTY_IP_FIO')
);
if ($data = $res->Fetch()) {
    if (isset($data['PROPERTY_FULL_COMPANY_NAME_VALUE']) && trim($data['PROPERTY_FULL_COMPANY_NAME_VALUE']) != '') {
        $APPLICATION->SetTitle($data['PROPERTY_FULL_COMPANY_NAME_VALUE']);
    }
    elseif (isset($data['PROPERTY_IP_FIO_VALUE']) && trim($data['PROPERTY_IP_FIO_VALUE']) != '') {
        $APPLICATION->SetTitle('ИП ' . $data['PROPERTY_IP_FIO_VALUE']);
    }
}


$this->IncludeComponentTemplate();

unset($res, $data);