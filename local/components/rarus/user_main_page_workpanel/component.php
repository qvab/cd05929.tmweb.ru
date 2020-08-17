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

if (!isset($arParams['U_ID']) || !is_numeric($arParams['U_ID'])) {
    $arResult['ERROR'] = 'Y';
    $arResult['ERROR_MESSAGE'] = 'Не указан ID пользователя.';
}

$arResult['CHECK_DATA'] = array();
$arResult['HREFS'] = array();
if ($arResult['ERROR'] == '') {
    $u_obj = new CUser;
    $el_obj = new CIBlockElement;
    /* check data
     * client: available warehouses, requests, active deals
     * farmer: available warehouses, partners, partner docs, offers, required docs, active deals
     * partner: available farmers, transports, farmers docs, transports docs, active deals
     * transport: available warehouses, partners, partner docs, active deals
     * */
    switch ($GLOBALS['rrs_user_perm_level']) {
        case 'c': //client

            if ($GLOBALS['DEMO'] != 'Y') {
                //check notices
                //partner required docs
                $arSelect = array(
                    'PROPERTY_UL_TYPE',
                    'PROPERTY_NOTICE'
                );

                $arDocsList = array_keys(client::getAllDocuments());
                array_walk($arDocsList, 'addPropStrToVal', 'PROPERTY_');

                $arSelect = array_merge($arSelect, $arDocsList);

                $res = $el_obj->GetList(
                    array('ID' => 'ASC'),
                    array(
                        'IBLOCK_ID' => rrsIblock::getIBlockId('client_profile'),
                        'ACTIVE' => 'Y',
                        'PROPERTY_USER' => $arParams['U_ID']
                    ),
                    false,
                    false,
                    $arSelect
                );
                if ($data = $res->Fetch()) {
                    //check notice settings
                    if (!is_array($data['PROPERTY_NOTICE_VALUE']) || implode('', $data['PROPERTY_NOTICE_VALUE']) == '') {
                        $arResult['CHECK_DATA']['NOTICES'] = 'NO';
                        $arResult['HREFS']['NOTICES'] = '/client/center/settings/';
                    }

                    //partner required docs
                    /*$ulType = rrsIblock::getPropListId('client_profile', 'UL_TYPE', $data['PROPERTY_UL_TYPE_ENUM_ID']);
                    if (!$ulType)
                        $ulType = 'ul';

                    $arFilter = array(
                        'IBLOCK_ID' => rrsIblock::getIBlockId('user_docs'),
                        'ACYIVE' => 'Y',
                        'SECTION_CODE' => 'client',
                        'PROPERTY_TYPE' => rrsIblock::getPropListKey('user_docs', 'TYPE', $ulType)
                    );

                    $arDocs = array();
                    $res = CIBlockElement::GetList(
                        array('SORT' => 'ASC'),
                        $arFilter,
                        false,
                        false,
                        array('ID', 'NAME', 'CODE', 'PROPERTY_NAME')
                    );
                    while ($ob = $res->Fetch()) {
                        $arDocs[$ob['CODE']] = $ob;
                    }

                    if (is_array($arDocs) && sizeof($arDocs) > 0) {
                        foreach ($arDocs as $doc) {
                            if (!isset($data['PROPERTY_'.$doc['CODE'].'_VALUE']) || !is_numeric($data['PROPERTY_'.$doc['CODE'].'_VALUE'])) {
                                $arResult['CHECK_DATA']['REQUIRED_DOCS'] = 'NO';
                                $arResult['HREFS']['REQUIRED_DOCS'] = '/client/documents/';
                                break;
                            }
                        }
                    }*/
                }
            }

            //check warehouses
            $res = $el_obj->GetList(
                array('ID' => 'ASC'),
                array(
                    'IBLOCK_ID' => rrsIblock::getIBlockId('client_warehouse'),
                    'ACTIVE' => 'Y',
                    'PROPERTY_ACTIVE' => rrsIblock::getPropListKey('client_warehouse', 'ACTIVE', 'yes'),
                    'PROPERTY_CLIENT' => $arParams['U_ID']
                ),
                false,
                false,
                array('ID')
            );
            if ($res->SelectedRowsCount() == 0) {
                $arResult['CHECK_DATA']['WAREHOUSES'] = 'NO';
                $arResult['HREFS']['WAREHOUSES'] = '/client/warehouses/';
            }

            if ($GLOBALS['DEMO'] != 'Y') {
                //check requests
                $res = $el_obj->GetList(
                    array('ID' => 'ASC'),
                    array(
                        'IBLOCK_ID' => rrsIblock::getIBlockId('client_request'),
                        'ACTIVE' => 'Y',
                        'PROPERTY_CLIENT' => $arParams['U_ID'],
                        '!PROPERTY_ACTIVE' => rrsIblock::getPropListKey('client_request', 'ACTIVE', 'no')
                    ),
                    false,
                    false,
                    array('ID')
                );
                if ($res->SelectedRowsCount() == 0) {
                    $arResult['CHECK_DATA']['REQUESTS'] = 'NO';
                    $arResult['HREFS']['REQUESTS'] = '/client/request/';
                }

                //check deals
                /*
                $res = $el_obj->GetList(
                    array('ID' => 'ASC'),
                    array(
                        'IBLOCK_ID' => rrsIblock::getIBlockId('deals_deals'),
                        'ACTIVE' => 'Y',
                        'PROPERTY_CLIENT' => $arParams['U_ID'],
                        'PROPERTY_STATUS' => rrsIblock::getPropListKey('deals_deals', 'STATUS', 'open')
                    ),
                    false,
                    false,
                    array('ID')
                );
                if ($res->SelectedRowsCount() > 0) {
                    $arResult['CHECK_DATA']['DEALS'] = 'YES';
                    $arResult['HREFS']['DEALS'] = '/client/deals/';
                }*/

                //check notices
                $res = $el_obj->GetList(
                    array('ID' => 'ASC'),
                    array(
                        'IBLOCK_ID' => rrsIblock::getIBlockId('client_profile'),
                        'ACTIVE' => 'Y',
                        'PROPERTY_USER' => $arParams['U_ID'],
                        '!PROPERTY_NOTICE' => false
                    ),
                    false,
                    false,
                    array('ID')
                );
                if ($res->SelectedRowsCount() == 0) {
                    $arResult['CHECK_DATA']['NOTICES'] = 'NO';
                    $arResult['HREFS']['NOTICES'] = '/client/center/settings/';
                }

                //check partner`s link
                /*$res = $el_obj->GetList(
                    array('ID' => 'ASC'),
                    array(
                        'IBLOCK_ID' => rrsIblock::getIBlockId('client_partner_link'),
                        'ACTIVE' => 'Y',
                        'PROPERTY_USER_ID' => $arParams['U_ID'],
                        '!PROPERTY_PARTNER_ID' => false
                    ),
                    false,
                    false,
                    array('ID')
                );
                if ($res->SelectedRowsCount() == 0) {
                    $arResult['CHECK_DATA']['PARTNER_LINK'] = 'NO';
                    $arResult['HREFS']['PARTNER_LINK'] = '/client/link_to_partner/';
                }*/
            }

            break;

        case 'f':

            //check warehouses
            $res = $el_obj->GetList(
                array('ID' => 'ASC'),
                array(
                    'IBLOCK_ID' => rrsIblock::getIBlockId('farmer_warehouse'),
                    'ACTIVE' => 'Y',
                    'PROPERTY_ACTIVE' => rrsIblock::getPropListKey('farmer_warehouse', 'ACTIVE', 'yes'),
                    'PROPERTY_FARMER' => $arParams['U_ID']
                ),
                false,
                false,
                array('ID')
            );
            if ($res->SelectedRowsCount() == 0) {
                $arResult['CHECK_DATA']['WAREHOUSES'] = 'NO';
                $arResult['HREFS']['WAREHOUSES'] = '/farmer/warehouses/';
            }

            if ($GLOBALS['DEMO'] != 'Y') {

                //available linked partners & docs
                // & farmer required docs
                // & notice settings

                $arSelect = array(
                    'PROPERTY_PARTNER_ID',
                    'PROPERTY_VERIFIED',
                    'PROPERTY_UL_TYPE',
                    'PROPERTY_NDS',
                    'PROPERTY_NDS.CODE',
                    'PROPERTY_NOTICE'
                );

                $arDocsList = array_keys(farmer::getAllDocuments());
                array_walk($arDocsList, 'addPropStrToVal', 'PROPERTY_');

                $arSelect = array_merge($arSelect, $arDocsList);

                $res = $el_obj->GetList(
                    array('ID' => 'ASC'),
                    array(
                        'IBLOCK_ID' => rrsIblock::getIBlockId('farmer_profile'),
                        'ACTIVE' => 'Y',
                        'PROPERTY_USER' => $arParams['U_ID']
                    ),
                    false,
                    false,
                    $arSelect
                );
                if ($data = $res->Fetch()) {
                    //check notice settings
                    if (!is_array($data['PROPERTY_NOTICE_VALUE']) || implode('', $data['PROPERTY_NOTICE_VALUE']) == '') {
                        $arResult['CHECK_DATA']['NOTICES'] = 'NO';
                        $arResult['HREFS']['NOTICES'] = '/farmer/center/settings/';
                    }

                    //linked partners & docs
                    /*if (!isset($data['PROPERTY_PARTNER_ID_VALUE']) || !is_numeric($data['PROPERTY_PARTNER_ID_VALUE']) || $data['PROPERTY_PARTNER_ID_VALUE'] == 0) {
                        $arResult['CHECK_DATA']['PARTNERS'] = 'NO';
                        $arResult['HREFS']['PARTNERS'] = '/farmer/link_to_partner/';
                    }
                    elseif (!isset($data['PROPERTY_VERIFIED_ENUM_ID'])
                        || $data['PROPERTY_VERIFIED_ENUM_ID'] != rrsIblock::getPropListKey('farmer_profile', 'VERIFIED', 'yes')
                    ) {
                        $arResult['CHECK_DATA']['PARTNERS_VERIFY'] = 'NO';
                        $arResult['HREFS']['PARTNERS_VERIFY'] = '/farmer/link_to_partner/';
                    }*/

                    //farmer required docs
                    $check_arr = rrsIblock::getElementList(29);
                    if (!isset($data['PROPERTY_NDS_VALUE']) || !is_numeric($data['PROPERTY_NDS_VALUE']) || !isset($check_arr[$data['PROPERTY_NDS_VALUE']])) {
                        //no nds values is set
                        $arResult['CHECK_DATA']['REQUIRED_DOCS'] = 'NO_NDS';
                        $arResult['HREFS']['REQUIRED_DOCS'] = '/farmer/profile/';
                    }
                    else {
                        //check docs
                        /*$ulType = rrsIblock::getPropListId('farmer_profile', 'UL_TYPE', $data['PROPERTY_UL_TYPE_ENUM_ID']);
                        if (!$ulType)
                            $ulType = 'ul';

                        $arFilter = array(
                            'IBLOCK_ID' => rrsIblock::getIBlockId('user_docs'),
                            'ACYIVE' => 'Y',
                            'SECTION_CODE' => 'farmer',
                            'PROPERTY_TYPE' => rrsIblock::getPropListKey('user_docs', 'TYPE', $ulType)
                        );

                        if ($ulType == 'ul') {
                            $arFilter['PROPERTY_NDS'] = $data['PROPERTY_NDS_VALUE'];
                        }

                        $arDocs = array();
                        $res = CIBlockElement::GetList(
                            array('SORT' => 'ASC'),
                            $arFilter,
                            false,
                            false,
                            array('ID', 'NAME', 'CODE', 'PROPERTY_NAME')
                        );
                        while ($ob = $res->Fetch()) {
                            $arDocs[$ob['CODE']] = $ob;
                        }

                        if (is_array($arDocs) && sizeof($arDocs) > 0) {
                            foreach ($arDocs as $doc) {
                                if (!isset($data['PROPERTY_'.$doc['CODE'].'_VALUE']) || !is_numeric($data['PROPERTY_'.$doc['CODE'].'_VALUE'])) {
                                    $arResult['CHECK_DATA']['REQUIRED_DOCS'] = 'NO';
                                    $arResult['HREFS']['REQUIRED_DOCS'] = '/farmer/documents/';
                                    break;
                                }
                            }
                        }*/
                    }
                }
            }

            //check offers
            $res = $el_obj->GetList(
                array('ID' => 'ASC'),
                array(
                    'IBLOCK_ID' => rrsIblock::getIBlockId('farmer_offer'),
                    'ACTIVE' => 'Y',
                    'PROPERTY_FARMER' => $arParams['U_ID']
                ),
                false,
                false,
                array('ID')
            );
            if ($res->SelectedRowsCount() == 0) {
                $arResult['CHECK_DATA']['OFFERS'] = 'NO';
                $arResult['HREFS']['OFFERS'] = '/farmer/offer/';
            }

            if ($GLOBALS['DEMO'] != 'Y') {
                //check deals
                /*$res = $el_obj->GetList(
                    array('ID' => 'ASC'),
                    array(
                        'IBLOCK_ID' => rrsIblock::getIBlockId('deals_deals'),
                        'ACTIVE' => 'Y',
                        'PROPERTY_FARMER' => $arParams['U_ID'],
                        'PROPERTY_STATUS' => rrsIblock::getPropListKey('deals_deals', 'STATUS', 'open')
                    ),
                    false,
                    false,
                    array('ID')
                );
                if ($res->SelectedRowsCount() > 0) {
                    $arResult['CHECK_DATA']['DEALS'] = 'YES';
                    $arResult['HREFS']['DEALS'] = '/farmer/deals/';
                }*/
            }

            break;

        case 'p':

            //check notices
            //partner required docs
            $arSelect = array(
                'PROPERTY_UL_TYPE',
                'PROPERTY_NOTICE'
            );

            $arDocsList = array_keys(partner::getAllDocuments());
            array_walk($arDocsList, 'addPropStrToVal', 'PROPERTY_');

            $arSelect = array_merge($arSelect, $arDocsList);

            $res = $el_obj->GetList(
                array('ID' => 'ASC'),
                array(
                    'IBLOCK_ID' => rrsIblock::getIBlockId('partner_profile'),
                    'ACTIVE' => 'Y',
                    'PROPERTY_USER' => $arParams['U_ID']
                ),
                false,
                false,
                $arSelect
            );
            if ($data = $res->Fetch()) {
                //check notice settings
                if (!is_array($data['PROPERTY_NOTICE_VALUE']) || implode('', $data['PROPERTY_NOTICE_VALUE']) == '') {
                    $arResult['CHECK_DATA']['NOTICES'] = 'NO';
                    $arResult['HREFS']['NOTICES'] = '/partner/center/settings/';
                }

                //partner required docs
                $ulType = rrsIblock::getPropListId('partner_profile', 'UL_TYPE', $data['PROPERTY_UL_TYPE_ENUM_ID']);
                if (!$ulType)
                    $ulType = 'ul';

                $arFilter = array(
                    'IBLOCK_ID' => rrsIblock::getIBlockId('user_docs'),
                    'ACYIVE' => 'Y',
                    'SECTION_CODE' => 'partner',
                    'PROPERTY_TYPE' => rrsIblock::getPropListKey('user_docs', 'TYPE', $ulType)
                );

                $arDocs = array();
                $res = CIBlockElement::GetList(
                    array('SORT' => 'ASC'),
                    $arFilter,
                    false,
                    false,
                    array('ID', 'NAME', 'CODE', 'PROPERTY_NAME')
                );
                while ($ob = $res->Fetch()) {
                    $arDocs[$ob['CODE']] = $ob;
                }

                if (is_array($arDocs) && sizeof($arDocs) > 0) {
                    foreach ($arDocs as $doc) {
                        if (!isset($data['PROPERTY_'.$doc['CODE'].'_VALUE']) || !is_numeric($data['PROPERTY_'.$doc['CODE'].'_VALUE'])) {
                            $arResult['CHECK_DATA']['REQUIRED_DOCS'] = 'NO';
                            $arResult['HREFS']['REQUIRED_DOCS'] = '/partner/documents/';
                            break;
                        }
                    }
                }
            }

            //check farmers
            $temp_ids_arr = array();
            $res = $el_obj->GetList(
                array('ID' => 'ASC'),
                array(
                    'IBLOCK_ID' => rrsIblock::getIBlockId('farmer_profile'),
                    'ACTIVE' => 'Y',
                    'PROPERTY_PARTNER_ID' => $arParams['U_ID']
                ),
                false,
                false,
                array(
                    'ID',
                    'PROPERTY_USER',
                    'PROPERTY_VERIFIED',
                    'PROPERTY_PARTNER_ID_TIMESTAMP'
                )
            );
            if ($res->SelectedRowsCount() == 0) {
                $arResult['CHECK_DATA']['FARMERS'] = 'NO';
                $arResult['HREFS']['FARMERS'] = '/partner/users/invite/';
            }
            else {
                while ($data = $res->Fetch()) {
                    if (!isset($data['PROPERTY_VERIFIED_ENUM_ID'])
                        || $data['PROPERTY_VERIFIED_ENUM_ID'] != rrsIblock::getIBlockId('farmer_profile', 'VERIFIED', 'yes')
                    ) {
                        //found farmer with no docs
                        $arResult['CHECK_DATA']['FARMERS'] = 'NO_VERIFY';
                        $arResult['HREFS']['FARMERS'] = '/partner/users/linked_users/';
                        $temp_ids_arr[$data['PROPERTY_USER_VALUE']] = true;
                    }
                }
            }

            if ($arResult['CHECK_DATA']['FARMERS'] == 'NO_DOCS' && count($temp_ids_arr) > 0) {
                //check if all users without docs that are active
                $res = $u_obj->GetList(($by="ID"), ($order="DESC"), array('ID' => implode(' | ', array_keys($temp_ids_arr)), 'ACTIVE' => 'N'), array('FIELDS' => array('ID')));
                if ($res->SelectedRowsCount() > 0) {
                    $arResult['CHECK_DATA']['FARMERS_2'] = 'NOT_ACTIVE';
                    $arResult['HREFS']['FARMERS_2'] = '/partner/users/linked_users/';
                }
            }

            //check transport
            $temp_ids_arr = array();
            $res = $el_obj->GetList(
                array('ID' => 'ASC'),
                array(
                    'IBLOCK_ID' => rrsIblock::getIBlockId('transport_partner_link'),
                    'ACTIVE' => 'Y',
                    'PROPERTY_PARTNER_ID' => $arParams['U_ID']
                ),
                false,
                false,
                array(
                    'ID',
                    'PROPERTY_USER_ID',
                    'PROPERTY_PARTNER_LINK_DATE'
                )
            );
            if ($res->SelectedRowsCount() == 0) {
                $arResult['CHECK_DATA']['TRANSPORT'] = 'NO';
                $arResult['HREFS']['TRANSPORT'] = '/partner/users/invite/';
            }
            /*else {
                while ($data = $res->Fetch()) {
                    if (!isset($data['PROPERTY_PARTNER_LINK_DOC_VALUE']) || !is_numeric($data['PROPERTY_PARTNER_LINK_DOC_VALUE'])
                        || !isset($data['PROPERTY_PARTNER_LINK_DOC_NUM_VALUE']) || trim($data['PROPERTY_PARTNER_LINK_DOC_NUM_VALUE']) == ''
                        || !isset($data['PROPERTY_PARTNER_LINK_DOC_DATE_VALUE']) || trim($data['PROPERTY_PARTNER_LINK_DOC_DATE_VALUE']) == ''
                    ) {
                        //found farmer with no docs
                        $arResult['CHECK_DATA']['TRANSPORT'] = 'NO_DOCS';
                        $arResult['HREFS']['TRANSPORT'] = '/partner/users/linked_transport/';
                    }
                }
            }*/

            //check if all users without docs that are active
            $res = $el_obj->GetList(
                array('ID' => 'ASC'),
                array(
                    'IBLOCK_ID' => rrsIblock::getIBlockId('transport_profile'),
                    'ACTIVE' => 'Y',
                    'PROPERTY_PARTNER_ID' => $arParams['U_ID'],
                    'PARTNER_LINK_DOC' => false
                ),
                false,
                false,
                array(
                    'ID',
                    'PROPERTY_USER'
                )
            );
            while ($data = $res->Fetch()) {
                $temp_ids_arr[$data['PROPERTY_USER_VALUE']] = true;
            }
            if (count($temp_ids_arr) > 0) {
                $res = $u_obj->GetList(($by="ID"), ($order="DESC"), array('ID' => implode(' | ', array_keys($temp_ids_arr)), 'ACTIVE' => 'N'), array('FIELDS' => array('ID')));
                if ($res->SelectedRowsCount() > 0) {
                    $arResult['CHECK_DATA']['TRANSPORT_2'] = 'NOT_ACTIVE';
                    $arResult['HREFS']['TRANSPORT_2'] = '/partner/users/linked_transport/';
                }
            }

            //check deals
            /*$res = $el_obj->GetList(
                array('ID' => 'ASC'),
                array(
                    'IBLOCK_ID' => rrsIblock::getIBlockId('deals_deals'),
                    'ACTIVE' => 'Y',
                    'PROPERTY_PARTNER' => $arParams['U_ID']
                ),
                false,
                false,
                array('ID')
            );
            if ($res->SelectedRowsCount() > 0) {
                $arResult['CHECK_DATA']['DEALS'] = 'YES';
                $arResult['HREFS']['DEALS'] = '/partner/deals/';
            }*/

            break;

        case 't':

            //check notices
            //partner required docs
            $arSelect = array(
                'PROPERTY_UL_TYPE',
                'PROPERTY_NOTICE'
            );

            $arDocsList = array_keys(transport::getAllDocuments());
            array_walk($arDocsList, 'addPropStrToVal', 'PROPERTY_');

            $arSelect = array_merge($arSelect, $arDocsList);

            $res = $el_obj->GetList(
                array('ID' => 'ASC'),
                array(
                    'IBLOCK_ID' => rrsIblock::getIBlockId('transport_profile'),
                    'ACTIVE' => 'Y',
                    'PROPERTY_USER' => $arParams['U_ID']
                ),
                false,
                false,
                $arSelect
            );
            if ($data = $res->Fetch()) {
                //check notice settings
                if (!is_array($data['PROPERTY_NOTICE_VALUE']) || implode('', $data['PROPERTY_NOTICE_VALUE']) == '') {
                    $arResult['CHECK_DATA']['NOTICES'] = 'NO';
                    $arResult['HREFS']['NOTICES'] = '/transport/center/settings/';
                }

                //transport required docs
                $ulType = rrsIblock::getPropListId('transport_profile', 'UL_TYPE', $data['PROPERTY_UL_TYPE_ENUM_ID']);
                if (!$ulType)
                    $ulType = 'ul';

                $arFilter = array(
                    'IBLOCK_ID' => rrsIblock::getIBlockId('user_docs'),
                    'ACYIVE' => 'Y',
                    'SECTION_CODE' => 'transport',
                    'PROPERTY_TYPE' => rrsIblock::getPropListKey('user_docs', 'TYPE', $ulType)
                );

                $arDocs = array();
                $res = CIBlockElement::GetList(
                    array('SORT' => 'ASC'),
                    $arFilter,
                    false,
                    false,
                    array('ID', 'NAME', 'CODE', 'PROPERTY_NAME')
                );
                while ($ob = $res->Fetch()) {
                    $arDocs[$ob['CODE']] = $ob;
                }

                if (is_array($arDocs) && sizeof($arDocs) > 0) {
                    foreach ($arDocs as $doc) {
                        if (!isset($data['PROPERTY_'.$doc['CODE'].'_VALUE']) || !is_numeric($data['PROPERTY_'.$doc['CODE'].'_VALUE'])) {
                            $arResult['CHECK_DATA']['REQUIRED_DOCS'] = 'NO';
                            $arResult['HREFS']['REQUIRED_DOCS'] = '/transport/documents/';
                            break;
                        }
                    }
                }
            }

            //check warehouses
            $res = $el_obj->GetList(
                array('ID' => 'ASC'),
                array(
                    'IBLOCK_ID' => rrsIblock::getIBlockId('transport_autopark'),
                    'ACTIVE' => 'Y',
                    'PROPERTY_TRANSPORT' => $arParams['U_ID']
                ),
                false,
                false,
                array('ID')
            );
            if ($res->SelectedRowsCount() == 0) {
                $arResult['CHECK_DATA']['WAREHOUSES'] = 'NO';
                $arResult['HREFS']['WAREHOUSES'] = '/transport/autopark/';
            }

            //check partners
            $res = $el_obj->GetList(
                array('ID' => 'ASC'),
                array(
                    'IBLOCK_ID' => rrsIblock::getIBlockId('transport_partner_link'),
                    'ACTIVE' => 'Y',
                    'PROPERTY_USER_ID' => $arParams['U_ID']
                ),
                false,
                false,
                array('ID', 'PROPERTY_PARTNER_LINK_DATE')
            );
            if ($res->SelectedRowsCount() == 0) {
                $arResult['CHECK_DATA']['PARTNERS'] = 'NO';
                $arResult['HREFS']['PARTNERS'] = '/transport/link_to_partner/';
            }
            /*else {
                while ($data = $res->Fetch()) {
                    if (!isset($data['PROPERTY_PARTNER_LINK_DOC_VALUE']) || !is_numeric($data['PROPERTY_PARTNER_LINK_DOC_VALUE'])
                        || !isset($data['PROPERTY_PARTNER_LINK_DOC_NUM_VALUE']) || trim($data['PROPERTY_PARTNER_LINK_DOC_NUM_VALUE']) == ''
                        || !isset($data['PROPERTY_PARTNER_LINK_DOC_DATE_VALUE']) || trim($data['PROPERTY_PARTNER_LINK_DOC_DATE_VALUE']) == ''
                    ) {
                        //found farmer with no docs
                        $arResult['CHECK_DATA']['PARTNERS'] = 'NO_DOCS';
                        $arResult['HREFS']['PARTNERS'] = '/transport/link_to_partner/';
                        break;
                    }
                }
            }*/

            //check deals
            /*$res = $el_obj->GetList(
                array('ID' => 'ASC'),
                array(
                    'IBLOCK_ID' => rrsIblock::getIBlockId('deals_deals'),
                    'ACTIVE' => 'Y',
                    'PROPERTY_TRANSPORT' => $arParams['U_ID']
                ),
                false,
                false,
                array('ID')
            );
            if ($res->SelectedRowsCount() > 0) {
                $arResult['CHECK_DATA']['DEALS'] = 'YES';
                $arResult['HREFS']['DEALS'] = '/transport/deals/';
            }*/

            /*//check notices
            $res = $el_obj->GetList(
                array('ID' => 'ASC'),
                array(
                    'IBLOCK_ID' => rrsIblock::getIBlockId('transport_profile'),
                    'ACTIVE' => 'Y',
                    'PROPERTY_USER' => $arParams['U_ID'],
                    '!PROPERTY_NOTICE' => false
                ),
                false,
                false,
                array('ID')
            );
            if ($res->SelectedRowsCount() == 0) {
                $arResult['CHECK_DATA']['NOTICES'] = 'NO';
                $arResult['HREFS']['NOTICES'] = '/transport/center/settings/';
            }*/
            break;
    }
}

$this->IncludeComponentTemplate();