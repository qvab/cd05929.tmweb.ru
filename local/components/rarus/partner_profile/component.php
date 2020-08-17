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
$arResult['ERROR_TEXT'] = '';

if (!isset($arParams['U_ID']) || !is_numeric($arParams['U_ID'])) {
    $arResult['ERROR'] = 'Y';
    $arResult['ERROR_MESSAGE'] = 'Не указан ID пользователя.';
}

if (!isset($arParams['EDIT_PROPS_LIST']) || !is_array($arParams['EDIT_PROPS_LIST'])) {
    $arResult['ERROR'] = 'Y';
    $arResult['ERROR_MESSAGE'] = 'Не указан список свойств для редактирования.';
}

if ($arResult['ERROR'] != 'Y') {
    $u_obj = new CUser;
    $el_obj = new CIBlockElement;

    $res = CIBlockElement::GetList(
        array('SORT' => 'ASC'),
        array('IBLOCK_ID' => rrsIblock::getIBlockId('foundations'), 'ACTIVE' => 'Y'),
        false,
        false,
        array('ID', 'NAME', 'CODE', 'PROPERTY_CHEGO', 'PROPERTY_SHOW')
    );
    while ($ob = $res->Fetch()) {
        $arResult['FOUND'][$ob['CODE']] = $ob;
    }

    $arResult['SHOW_FIELDS'] = array();
    $arResult['SHOW_PROPS'] = array();
    $arResult['SHOW_PROPS_TYPE'] = array();
    $arResult['SHOW_PROPS_LIST_DATA'] = array();

    //get props type data
    $get_lists_ib_ids = array();
    $res = CIBlockProperty::GetList(
        array('SORT' => 'ASC', 'ID' => 'ASC'),
        array(
            'IBLOCK_ID' => rrsIblock::getIBlockId('partner_profile')
        )
    );
    while ($data = $res->Fetch()) {
        $arResult['SHOW_PROPS_TYPE'][$data['CODE']] = array(
            'ID' => $data['ID'],
            'CODE' => $data['CODE'],
            'PROPERTY_TYPE' => $data['PROPERTY_TYPE'],
            'NAME' => $data['NAME'],
            'LINK_IBLOCK_ID' => $data['LINK_IBLOCK_ID']
        );
        if ($data['PROPERTY_TYPE'] == 'E' && is_numeric($data['LINK_IBLOCK_ID'])) {
            $get_lists_ib_ids[$data['LINK_IBLOCK_ID']] = true;
        }
    }
    if (count($get_lists_ib_ids) > 0) {
        foreach ($get_lists_ib_ids as $cur_ib_id => $cur_flag) {
            $res = $el_obj->GetList(
                array('ID' => 'ASC'),
                array(
                    'IBLOCK_ID' => $cur_ib_id,
                    'ACTIVE' => 'Y'
                ),
                false,
                false,
                array('ID', 'NAME', 'IBLOCK_ID')
            );
            while ($data = $res->Fetch()) {
                $arResult['SHOW_PROPS_LIST_DATA'][$data['IBLOCK_ID']][$data['ID']] = $data['NAME'];
            }
        }
    }

    //check if exists update data
    if (isset($_POST['update']) && $_POST['update'] == 'y') {
        if ($arResult['ERROR_TEXT'] == '') {
            $arUpdateFields = array('UF_FIRST_PHONE' => 0);
            if (isset($_POST['NAME'])) {
                $arUpdateFields['NAME'] = $_POST['NAME'];
                $arUpdateFields['LAST_NAME'] = (isset($_POST['LAST_NAME']) ? $_POST['LAST_NAME'] : '');
                $arUpdateFields['SECOND_NAME'] = (isset($_POST['SECOND_NAME']) ? $_POST['SECOND_NAME'] : '');
            }

            //update user fields values
            if (count($arUpdateFields) > 0 && !$u_obj->Update($arParams['U_ID'], $arUpdateFields)) {
                //there is some error with updating profile
                $arResult['ERROR_TEXT'] = $u_obj->LAST_ERROR;
            }
        }

        $arUpdateProps = array();
        if ($arResult['ERROR_TEXT'] == '') {
            //check additional profile properties

            if (!isset($arParams['TYPE']) || $arParams['TYPE'] == 1) {
                foreach ($arParams['EDIT_PROPS_LIST'] as $cur_code) {
                    if (isset($_POST['PROP__' . $cur_code])) {
                        $arUpdateProps[$cur_code] = $_POST['PROP__' . $cur_code];
                    }
                    elseif ($_FILES['PROP__' . $cur_code]['tmp_name'] != ''
                        && $_FILES['PROP__' . $cur_code]['error'] == 0
                        && isset($arResult['SHOW_PROPS_TYPE'][$cur_code])
                        && $arResult['SHOW_PROPS_TYPE'][$cur_code]['PROPERTY_TYPE'] == 'F'
                    ) {
                        $arUpdateProps[$cur_code] = $_FILES['PROP__' . $cur_code];
                    }
                }

                $arUpdateProps['SIGNER'] = $_POST['signer'][$_POST['user_type']];
                $signCode = rrsIblock::getElementCodeById(rrsIblock::getIBlockId('signers'), $_POST['signer'][$_POST['user_type']]);

                $arUpdateProps['POST'] = $_POST['post'][$_POST['user_type']][$signCode];

                if ($signCode == 'sign') {
                    $arUpdateProps['FIO_SIGN'] = $_POST['fio'][$_POST['user_type']][$signCode];
                }
                elseif ($_POST['user_type'] == 'ul') {
                    $arUpdateProps['FIO_SIGN'] = $_POST['PROP__FIO_DIR'];
                }
                elseif ($_POST['user_type'] == 'ip') {
                    $arUpdateProps['FIO_SIGN'] = $_POST['PROP__IP_FIO'];
                }

                $arUpdateProps['FOUND'] = $_POST['found'][$_POST['user_type']][$signCode];
                $arUpdateProps['FOUND_NUM'] = $_POST['num'][$_POST['user_type']][$signCode];
                $arUpdateProps['FOUND_DATE'] = $_POST['date'][$_POST['user_type']][$signCode];

                $foundCode = rrsIblock::getElementCodeById(rrsIblock::getIBlockId('foundations'), $_POST['found'][$_POST['user_type']][$signCode]);

                $arUpdateProps['FOUNDATION'] = $arResult['FOUND'][$foundCode]['PROPERTY_CHEGO_VALUE'];
                if ($_POST['num'][$_POST['user_type']][$signCode] != '') {
                    $arUpdateProps['FOUNDATION'] .= ' № ' . $_POST['num'][$_POST['user_type']][$signCode];
                }
                if ($_POST['date'][$_POST['user_type']][$signCode] != '') {
                    $arUpdateProps['FOUNDATION'] .= ' от ' . $_POST['date'][$_POST['user_type']][$signCode] . ' г.';
                }
            }
            elseif ($arParams['TYPE'] == 2) {
                //update documents page
                //check all documents props
                $arCheck = array();
                $res = CIBlockProperty::GetList(
                    array('SORT' => 'ASC', 'ID' => 'ASC'),
                    array(
                        'IBLOCK_ID' => rrsIblock::getIBlockId('partner_profile'),
                        'PROPERTY_TYPE' => 'F'
                    )
                );
                while ($data = $res->Fetch()) {
                    $arCheck[$data['CODE']] = true;
                }
                if (count($arCheck) > 0) {
                    foreach ($arCheck as $cur_code => $cur_flag) {
                        if (isset($_FILES['PROP__' . $cur_code]) && $_FILES['PROP__' . $cur_code]['error'] == 0) {
                            $arUpdateProps[$cur_code] = $_FILES['PROP__' . $cur_code];
                        }
                    }
                }
            }

            //update user profile props values
            if (count($arUpdateProps) > 0) {
                //get profile id
                $res = $el_obj->GetList(
                    array('ID' => 'ASC'),
                    array(
                        'IBLOCK_ID' => rrsIblock::getIBlockId('partner_profile'),
                        'ACTIVE' => 'Y',
                        'PROPERTY_USER' => $arParams['U_ID']
                    ),
                    false,
                    array('nTopCount' => 1),
                    array('ID', 'IBLOCK_ID')
                );
                if ($data = $res->Fetch()) {
                    //update props values
                    $el_obj->SetPropertyValuesEx($data['ID'], $data['IBLOCK_ID'], $arUpdateProps);
                }
            }
        }

        if ($arResult['ERROR_TEXT'] == '') {
            LocalRedirect($GLOBALS['APPLICATION']->GetCurDir(false)."?success=ok");
            exit;
        }
    }

    //get standart fields values
    $res = $u_obj->GetList(($by="id"), ($order="asc"), array('ID' => $arParams['U_ID']), array('FIELDS' => array('NAME', 'LAST_NAME', 'SECOND_NAME', 'EMAIL'), 'SELECT' => array('UF_FIRST_PHONE')));
    if ($data = $res->Fetch()) {
        $arResult['SHOW_FIELDS']['EMAIL'] = $data['EMAIL'];
        $arResult['SHOW_FIELDS']['NAME'] = $data['NAME'];
        $arResult['SHOW_FIELDS']['LAST_NAME'] = $data['LAST_NAME'];
        $arResult['SHOW_FIELDS']['SECOND_NAME'] = $data['SECOND_NAME'];
        $arResult['SHOW_FIELDS']['PHONE_NEVER_APPROVED'] = $data['UF_FIRST_PHONE'];
    }

    //get props values
    $arSelect = $arParams['EDIT_PROPS_LIST'];
    array_walk($arSelect, 'addPropStrToVal', 'PROPERTY_');
    $arSelect[] = 'IBLOCK_ID';
    $res = $el_obj->GetList(
        array('ID' => 'ASC'),
        array(
            'IBLOCK_ID' => rrsIblock::getIBlockId('partner_profile'),
            'ACTIVE' => 'Y',
            'PROPERTY_USER' => $arParams['U_ID']
        ),
        false,
        array('nTopCount' => 1),
        $arSelect
    );
    if ($data = $res->Fetch()) {
        foreach ($arParams['EDIT_PROPS_LIST'] as $cur_code) {
            if ($cur_code == 'FULL_COMPANY_NAME' && isset($data['PROPERTY_' . $cur_code . '_VALUE']) && trim($data['PROPERTY_' . $cur_code . '_VALUE']) != '') {
                $APPLICATION->SetTitle($data['PROPERTY_' . $cur_code . '_VALUE']);
            }
            elseif ($cur_code == 'IP_FIO' && isset($data['PROPERTY_' . $cur_code . '_VALUE']) && trim($data['PROPERTY_' . $cur_code . '_VALUE']) != '') {
                $APPLICATION->SetTitle('ИП ' . $data['PROPERTY_' . $cur_code . '_VALUE']);
            }
            $arResult['SHOW_PROPS'][$cur_code] = (isset($data['PROPERTY_' . $cur_code . '_VALUE']) ? $data['PROPERTY_' . $cur_code . '_VALUE'] : '');

            if ($cur_code == 'UL_TYPE') {
                $arResult['SHOW_PROPS'][$cur_code] = (isset($data['PROPERTY_' . $cur_code . '_ENUM_ID']) ? $data['PROPERTY_' . $cur_code . '_ENUM_ID'] : '');
            }
        }
    }

    $ulType = rrsIblock::getPropListId('partner_profile', 'UL_TYPE', $arResult['SHOW_PROPS']['UL_TYPE']);
    if ($ulType == '' || is_array($ulType))
        $ulType = 'ul';

    $arResult['USER_UL_TYPE'] = $ulType;

    if ($arParams['TYPE'] == 2) {
        $arSelect = array();

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
            $arDocs['PROPERTY_' . $ob['CODE']] = $ob;
        }

        if (is_array($arDocs) && sizeof($arDocs) > 0) {
            $arSelect = array_keys($arDocs);
            $arResult['DOCS_LIST'] = $arDocs;
        }

        if (count($arSelect) > 0) {
            //get additional files props values
            $res = $el_obj->GetList(
                array('ID' => 'ASC'),
                array(
                    'IBLOCK_ID' => rrsIblock::getIBlockId('partner_profile'),
                    'ACTIVE' => 'Y',
                    'PROPERTY_USER' => $arParams['U_ID']
                ),
                false,
                array('nTopCount' => 1),
                $arSelect
            );
            array_walk($arSelect, 'delPropStrFromVal', 'PROPERTY_');
            if ($data = $res->Fetch()) {
                foreach ($arSelect as $cur_code) {
                    $arResult['SHOW_PROPS'][$cur_code] = (isset($data['PROPERTY_' . $cur_code . '_VALUE']) ? $data['PROPERTY_' . $cur_code . '_VALUE'] : '');
                }
            }

            //get additional files props types
            $new_arr = array();
            $arSelect = array_flip($arSelect);
            $res = CIBlockProperty::GetList(
                array('SORT' => 'ASC', 'ID' => 'ASC'),
                array(
                    'IBLOCK_ID' => rrsIblock::getIBlockId('partner_profile'),
                    'PROPERTY_TYPE' => 'F'
                )
            );
            while ($data = $res->Fetch()) {
                if (isset($arSelect[$data['CODE']])) {
                    $arResult['SHOW_PROPS_TYPE'][$data['CODE']] = array(
                        'ID' => $data['ID'],
                        'CODE' => $data['CODE'],
                        'PROPERTY_TYPE' => $data['PROPERTY_TYPE'],
                        'NAME' => $data['NAME'],
                        'LINK_IBLOCK_ID' => $data['LINK_IBLOCK_ID']
                    );
                }
            }
        }
    }
}

//set documents page title
if (isset($arParams['TYPE']) && $arParams['TYPE'] == 2) {
    $res = $el_obj->GetList(
        array('ID' => 'ASC'),
        array(
            'IBLOCK_ID' => rrsIblock::getIBlockId('partner_profile'),
            'ACTIVE' => 'Y',
            'PROPERTY_USER' => $arParams['U_ID']
        ),
        false,
        array('nTopCount' => 1),
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
}

if (!isset($arParams['TYPE']) || $arParams['TYPE'] == 1) {
    $arResult['SIGNERS']= getSignerList();
}

$this->IncludeComponentTemplate();

unset($res, $data, $el_obj, $u_obj, $get_lists_ib_ids, $arSelect, $arUpdateFields);