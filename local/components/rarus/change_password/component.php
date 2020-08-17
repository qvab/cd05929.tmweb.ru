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
$arResult['MESS_STR'] = '';

if(!isset($arParams['U_ID']) || !is_numeric($arParams['U_ID']))
{
    $arResult['ERROR'] = 'Y';
    $arResult['ERROR_MESSAGE'] = 'Не указан ID пользователя.';
}

if(!isset($arParams['IB_CODE']) || trim($arParams['IB_CODE']) == '')
{
    $arResult['ERROR'] = 'Y';
    $arResult['ERROR_MESSAGE'] = 'Не указан код инфоблока.';
}

if($arResult['ERROR'] != 'Y' && isset($_POST['change']) && $_POST['change'] == 'y')
{//if change password form send

    //check send data
    if(!isset($_POST['new_passw']) || trim($_POST['new_passw']) == '' || mb_strlen($_POST['new_passw']) < 6)
    {
        $arResult['MESS_STR'] = 'Пароль не может быть короче 6 символов.';
    }

    if($arResult['MESS_STR'] == '' && (!isset($_POST['new_passw_conf']) || $_POST['new_passw_conf'] != $_POST['new_passw']))
    {
        $arResult['MESS_STR'] = 'Пароль и подтверждение пароля не совпадают.';
    }

    if($arResult['MESS_STR'] == '')
    {
        $user_obj = new CUser;
        $user_obj->Update($arParams['U_ID'], array(
            'PASSWORD' => $_POST['new_passw'],
            'CONFIRM_PASSWORD' => $_POST['new_passw']
        ));

        LocalRedirect($APPLICATION->GetCurDir() . '?success=ok');
        exit;
    }
}

//set linked partners page title
CModule::IncludeModule('iblock');
$res = CIBlockElement::GetList(
    array('ID' => 'ASC'),
    array(
        'IBLOCK_ID' => rrsIblock::getIBlockId($arParams['IB_CODE']),
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