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

CJSCore::Init(array('date'));

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
            'IBLOCK_ID' => rrsIblock::getIBlockId('client_profile')
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
    // Проверяем возможность изменить НДС
    $arResult['CHANGE_NDS'] = client::isChangeNDS($arParams['U_ID']);

    //check if exists update data
    if (isset($_POST['update']) && $_POST['update'] == 'y') {
        if(isset($arParams['BY_AGENT']) && $arParams['BY_AGENT'] == 'Y'){
            if(!isset($_POST['AUTH_REG_CONFIM_BY_AGENT']) || $_POST['AUTH_REG_CONFIM_BY_AGENT'] != 'Y'){
                $arResult['ERROR_TEXT'] = 'Вы не отметили подтверждение, что предоставление персональных данных на третьих лиц производится с их согласия';
            }
        }else{
            //Personal data computing agreement check
            if (!isset($_POST['AUTH_REG_CONFIM']) || $_POST['AUTH_REG_CONFIM'] != 'Y') {
                $arResult['ERROR_TEXT'] = 'Установите галочку согласия обработки персональных данных';
            }

            //Personal data computing agreement check
            if (!isset($_POST['AUTH_REGLAMENT_CONFIM']) || $_POST['AUTH_REGLAMENT_CONFIM'] != 'Y') {
                $arResult['ERROR_TEXT'] = 'Установите галочку согласия с регламентом системы АГРОХЕЛПЕР';
            }

            if (!$_POST['PASSWORD']) {
                $arResult['ERROR_TEXT'] = 'Вы не указали ваш текущий пароль';
            }
            elseif (!rrsIblock::isUserPassword($arParams['U_ID'], trim($_POST['PASSWORD']))) {
                $arResult['ERROR_TEXT'] = 'Вы указали неверный пароль';
            }
        }

        if (trim($_POST['EMAIL']) != ''
            && trim($_POST['EMAIL']) != trim($_POST['USER_EMAIL'])) {
            $rsUsers = CUser::GetList(
                ($by="id"), ($order="desc"),
                array(
                    '!ID' => $arParams['U_ID'],
                    'EMAIL' => trim($_POST['EMAIL']),
                ),
                array('FIELDS' => array('ID'))
            );
            if ($arRes = $rsUsers->Fetch()) {
                $arResult['ERROR_TEXT'] = 'Указанный вами почтовый адрес уже используется';
            }
        }elseif (trim($_POST['EMAIL']) == ''
            && checkEmailFromPhone($_POST['USER_EMAIL'])
        ){
            $_POST['EMAIL'] = trim($_POST['USER_EMAIL']);
        }

        $IBLOCK_IDs = array(
            0 => rrsIblock::getIBlockId('client_profile'),
            1 => rrsIblock::getIBlockId('farmer_profile'),
            2 => rrsIblock::getIBlockId('partner_profile'),
            3 => rrsIblock::getIBlockId('agent_profile'),
            4 => rrsIblock::getIBlockId('transport_profile'),
            5 => rrsIblock::getIBlockId('client_agent_profile'),
        );

        if (trim($_POST['PROP__PHONE']) != trim($_POST['USER_PHONE'])) {
            foreach($IBLOCK_IDs as $Id){
                $res = $el_obj->GetList(
                    array('ID' => 'ASC'),array('IBLOCK_ID' => $Id,'ACTIVE' => 'Y', 'PROPERTY_PHONE' => trim($_POST['PROP__PHONE']),),
                    false, array(), array('ID','IBLOCK_ID')
                );
                if ($res->SelectedRowsCount() > 0) {
                    $arResult['ERROR_TEXT'] = 'Указанный вами телефон уже используется';
                    break;
                }
            }
        }

        if ($arResult['ERROR_TEXT'] == '') {
            $arUpdateFields == array();
            if (isset($_POST['NAME'])) {
                $arUpdateFields['LOGIN'] = trim($_POST['EMAIL']);
                $arUpdateFields['EMAIL'] = trim($_POST['EMAIL']);
                $arUpdateFields['NAME'] = $_POST['NAME'];
                $arUpdateFields['LAST_NAME'] = (isset($_POST['LAST_NAME']) ? $_POST['LAST_NAME'] : '');
                $arUpdateFields['SECOND_NAME'] = (isset($_POST['SECOND_NAME']) ? $_POST['SECOND_NAME'] : '');
                if(!isset($arParams['BY_AGENT']) || $arParams['BY_AGENT'] != 'Y'){
                    $arUpdateFields['UF_FIRST_PHONE'] = false;
                }
                $arUpdateFields['UF_PRIV_POLICY_CONF'] = 'Y'; //Согласие на обработку персональных данных
                $arUpdateFields['UF_REGLAMENT_CONF'] = 'Y'; //Согласие с регламентом
                if(!isset($arParams['BY_AGENT']) || $arParams['BY_AGENT'] != 'Y'){
                    $arUpdateFields['UF_API_KEY'] = Agrohelper::hashApiKey(trim($_POST['EMAIL']), sha1($_POST['PASSWORD']));

                    if(isset($_POST['PROP__PHONE'])
                        && trim($_POST['PROP__PHONE'] != '')
                    ){
                        $arUpdateFields['UF_API_KEY_M'] = Agrohelper::hashApiKey(getPhoneDigits($_POST['PROP__PHONE']), sha1($_POST['PASSWORD']));
                    }
                }else {
                    $arUpdateFields['UF_REGLAMENT_CONF'] = 'Y';
                }
            }
            if(isset($arParams['BY_AGENT']) && $arParams['BY_AGENT'] == 'Y'){
                $arUpdateFields['UF_THIRD_PARTY_CONS'] = 'Y'; //Согласие третьих лиц
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

            $arUpdateProps['UL_TYPE'] = rrsIblock::getPropListKey('client_profile', 'UL_TYPE', $_POST['PROP__UL_TYPE']);

            if ($_POST['PROP__UL_TYPE'] == 'ul') {
                unset($arUpdateProps['IP_FIO']);
                $companyName = $arUpdateProps['FULL_COMPANY_NAME'];
            }
            elseif ($_POST['PROP__UL_TYPE'] == 'ip') {
                unset($arUpdateProps['FULL_COMPANY_NAME'], $arProps['YUR_ADRESS'], $arProps['KPP'], $arProps['FIO_DIR']);
                $companyName = $arUpdateProps['IP_FIO'];
            }

            $arUpdateProps['SIGNER'] = $_POST['signer'][$_POST['PROP__UL_TYPE']];
            $signCode = rrsIblock::getElementCodeById(rrsIblock::getIBlockId('signers'), $_POST['signer'][$_POST['PROP__UL_TYPE']]);

            $arUpdateProps['POST'] = $_POST['post'][$_POST['PROP__UL_TYPE']][$signCode];

            if ($signCode == 'sign') {
                $arUpdateProps['FIO_SIGN'] = $_POST['fio'][$_POST['PROP__UL_TYPE']][$signCode];
            }
            elseif ($_POST['PROP__UL_TYPE'] == 'ul') {
                $arUpdateProps['FIO_SIGN'] = $_POST['PROP__FIO_DIR'];
            }
            elseif ($_POST['PROP__UL_TYPE'] == 'ip') {
                $arUpdateProps['FIO_SIGN'] = $_POST['PROP__IP_FIO'];
            }

            $arUpdateProps['FOUND'] = $_POST['found'][$_POST['PROP__UL_TYPE']][$signCode];
            $arUpdateProps['FOUND_NUM'] = $_POST['num'][$_POST['PROP__UL_TYPE']][$signCode];
            $arUpdateProps['FOUND_DATE'] = $_POST['date'][$_POST['PROP__UL_TYPE']][$signCode];

            $foundCode = rrsIblock::getElementCodeById(rrsIblock::getIBlockId('foundations'), $_POST['found'][$_POST['PROP__UL_TYPE']][$signCode]);

            $arUpdateProps['FOUNDATION'] = $arResult['FOUND'][$foundCode]['PROPERTY_CHEGO_VALUE'];
            if ($_POST['num'][$_POST['PROP__UL_TYPE']][$signCode] != '') {
                $arUpdateProps['FOUNDATION'] .= ' № ' . $_POST['num'][$_POST['PROP__UL_TYPE']][$signCode];
            }
            if ($_POST['date'][$_POST['PROP__UL_TYPE']][$signCode] != '') {
                $arUpdateProps['FOUNDATION'] .= ' от ' . $_POST['date'][$_POST['PROP__UL_TYPE']][$signCode] . ' г.';
            }

            // Изменяем тип налогообложения
            $_POST['TYPE_NDS'] = intval($_POST['TYPE_NDS']);
            if(!empty($_POST['TYPE_NDS'])) {

                // ИД типов НДС
                $arNDSTypes = array_keys($arResult['SHOW_PROPS_LIST_DATA'][$arResult['SHOW_PROPS_TYPE']['NDS']['LINK_IBLOCK_ID']]);

                // Проверяем наличие ИД нового типа в списке
                if(!in_array($_POST['TYPE_NDS'], $arNDSTypes)) {
                    $arResult['ERROR'] = 'Y';
                    $arResult['ERROR_MESSAGE'] = 'Неизвестный тип налогообложения';
                }

                if($arResult['CHANGE_NDS']['LOCK']) {
                    $arResult['ERROR'] = 'Y';
                    $arResult['ERROR_MESSAGE'] = 'Нет возможности изменить тип НДС: "'.$arResult['CHANGE_NDS']['MSG'].'"';
                } else {
                    $arUpdateProps['NDS'] = $_POST['TYPE_NDS'];
                }
            }


            //update user profile props values
            if (count($arUpdateProps) > 0) {
                //get profile id
                $res = $el_obj->GetList(
                    array('ID' => 'ASC'),
                    array(
                        'IBLOCK_ID' => rrsIblock::getIBlockId('client_profile'),
                        'ACTIVE' => 'Y',
                        'PROPERTY_USER' => $arParams['U_ID']
                    ),
                    false,
                    array('nTopCount' => 1),
                    array('ID', 'IBLOCK_ID')
                );
                if ($data = $res->Fetch()) {
                    $linkedPartner = client::getLinkedPartner($arParams['U_ID']);

                    //update props values
                    $el_obj->SetPropertyValuesEx($data['ID'], $data['IBLOCK_ID'], $arUpdateProps);
                }
            }
        }

        if ($arResult['ERROR_TEXT'] == '') {
            $success_url = $GLOBALS['APPLICATION']->GetCurDir()."?success=ok";
            if(isset($arParams['BY_AGENT']) && $arParams['BY_AGENT'] == 'Y'){
                $success_url = $GLOBALS['APPLICATION']->GetCurPageParam('', array('success'))."&success=by";
            }
            LocalRedirect($success_url);
            exit;
        }
    }

    //get standart fields values
    $res = $u_obj->GetList(($by="id"), ($order="asc"), array('ID' => $arParams['U_ID']), array('FIELDS' => array('NAME', 'LAST_NAME', 'SECOND_NAME', 'EMAIL'), 'SELECT' => array('UF_FIRST_PHONE')));
    if ($data = $res->Fetch()) {
        $arResult['USER_EMAIL'] = $data['EMAIL'];
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
            'IBLOCK_ID' => rrsIblock::getIBlockId('client_profile'),
            'ACTIVE' => 'Y',
            'PROPERTY_USER' => $arParams['U_ID']
        ),
        false,
        array('nTopCount' => 1),
        $arSelect
    );
    if ($data = $res->Fetch()) {
        foreach ($arParams['EDIT_PROPS_LIST'] as $cur_code) {
            /*if ($cur_code == 'FULL_COMPANY_NAME' && isset($data['PROPERTY_' . $cur_code . '_VALUE']) && trim($data['PROPERTY_' . $cur_code . '_VALUE']) != '') {
                $APPLICATION->SetTitle($data['PROPERTY_' . $cur_code . '_VALUE']);
            }
            elseif ($cur_code == 'IP_FIO' && isset($data['PROPERTY_' . $cur_code . '_VALUE']) && trim($data['PROPERTY_' . $cur_code . '_VALUE']) != '') {
                $APPLICATION->SetTitle('ИП ' . $data['PROPERTY_' . $cur_code . '_VALUE']);
            }*/
            $arResult['SHOW_PROPS'][$cur_code] = (isset($data['PROPERTY_' . $cur_code . '_VALUE']) ? $data['PROPERTY_' . $cur_code . '_VALUE'] : '');

            if ($cur_code == 'UL_TYPE') {
                $arResult['SHOW_PROPS'][$cur_code] = (isset($data['PROPERTY_' . $cur_code . '_ENUM_ID']) ? $data['PROPERTY_' . $cur_code . '_ENUM_ID'] : '');
            }
        }
    }

    /*$ulType = rrsIblock::getPropListId('client_profile', 'UL_TYPE', $arResult['SHOW_PROPS']['UL_TYPE']);
    if ($ulType == '' || is_array($ulType))
        $ulType = 'ul';

    $arResult['USER_UL_TYPE'] = $ulType;*/
}

$arResult['SIGNERS']= getSignerList();

$this->IncludeComponentTemplate();

unset($res, $data, $el_obj, $u_obj, $get_lists_ib_ids, $arSelect, $arUpdateFields);