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
    $obAgent = new agent;

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
            $arUpdateFields == array();
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

            foreach ($arParams['EDIT_PROPS_LIST'] as $cur_code) {
                if (isset($_POST['PROP__' . $cur_code])) {
                    $arUpdateProps[$cur_code] = $_POST['PROP__' . $cur_code];
                }
                elseif (isset($_FILES['PROP__' . $cur_code]) && isset($arResult['SHOW_PROPS_TYPE'][$cur_code]) && $arResult['SHOW_PROPS_TYPE'][$cur_code]['PROPERTY_TYPE'] == 'F') {
                    $arUpdateProps[$cur_code] = $_FILES['PROP__' . $cur_code];
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
    $res = $u_obj->GetList(($by="id"), ($order="asc"), array('ID' => $arParams['U_ID']), array('FIELDS' => array('NAME', 'LAST_NAME', 'SECOND_NAME', 'EMAIL')));
    if ($data = $res->Fetch()) {
        $arResult['SHOW_FIELDS']['EMAIL'] = $data['EMAIL'];
        $arResult['SHOW_FIELDS']['NAME'] = $data['NAME'];
        $arResult['SHOW_FIELDS']['LAST_NAME'] = $data['LAST_NAME'];
        $arResult['SHOW_FIELDS']['SECOND_NAME'] = $data['SECOND_NAME'];
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
            $arResult['SHOW_PROPS'][$cur_code] = (isset($data['PROPERTY_' . $cur_code . '_VALUE']) ? $data['PROPERTY_' . $cur_code . '_VALUE'] : '');
        }
    }


    /**
     * Организатор агента клиента
     */
    $arResult['PARTNER_NAME']   = null;
    $arResult['PARTNER_ID']     = $obAgent->getPartnerByClientAgent($arParams['U_ID']);

    if(!empty($arResult['PARTNER_ID'])) {
        $arPartner = partner::getFullProfile($arResult['PARTNER_ID']);
        $arResult['PARTNER_NAME'] = $arPartner['COMPANY'];
        unset($arPartner);
    }
    unset($obAgent);
}

$this->IncludeComponentTemplate();

unset($res, $data, $el_obj, $u_obj, $get_lists_ib_ids, $arSelect, $arUpdateFields);