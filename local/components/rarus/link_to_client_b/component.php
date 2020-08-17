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

//if partner link is choosed
if(isset($_GET['link_to_partner']) && is_numeric($_GET['link_to_partner']))
{
    $res = CIBlockElement::GetList(array('ID' => 'ASC'), array('IBLOCK_CODE' => 'farmer_profile', 'PROPERTY_USER' => $arParams['U_ID'], 'PROPERTY_PARTNER_ID' => false), false, array('nTopCount' => 1), array('ID', 'IBLOCK_ID'));
    if($data = $res->Fetch())
    {
        CIBlockElement::SetPropertyValuesEx($data['ID'], $data['IBLOCK_ID'], array('PARTNER_ID' => $_GET['link_to_partner'], 'PARTNER_ID_TIMESTAMP' => time()));
        LocalRedirect('/personal/link_to_partner/');
        exit;
    }
}

if(isset($_GET['link_to_partner']))
{//if partner link is choosed, but error found -> redirect to default page (or send error letter to admin)
    LocalRedirect('/personal/link_to_partner/');
    exit;
}

//check current link farmers
$farmers_ids = array();
$res = CIBlockElement::GetList(array('ID' => 'ASC'), array('IBLOCK_CODE' => 'farmer_profile', 'PROPERTY_PARTNER_ID' => $arParams['U_ID']), false, false, array('ID'));
if($data = $res->Fetch())
{
    if(isset($_GET['unlink_partner']) && $_GET['unlink_partner'] == 'y')
    {//if partner unlink is choosed
        CIBlockElement::SetPropertyValuesEx($data['ID'], $data['IBLOCK_ID'], array('PARTNER_ID' => false));
        LocalRedirect('/personal/link_to_partner/');
        exit;
    }
    $farmers_ids[$data['PROPERTY_PARTNER_ID_VALUE']] = true;
}

if(isset($_GET['unlink_partner']) && $_GET['unlink_partner'] == 'y')
{//if partner unlink is choosed, but no partner found -> redirect to default page (or send error letter to admin)
    LocalRedirect('/personal/link_to_partner/');
    exit;
}

//get farmers list
$res = CUser::GetList(($by="id"), ($order="asc"), array('GROUPS_ID' => 11, 'ACTIVE' => 'Y'), array('FIELDS' => array('ID', 'ACTIVE', 'NAME', 'LAST_NAME', 'LOGIN')));
while($data = $res->Fetch())
{
    if(isset($farmers_ids[$data['ID']]))
    {//list of linked farmers
        $arResult['LINKED_FARMERS_LIST'][] = array(
            'ID' => $data['ID'],
            'ACTIVE' => $data['ACTIVE'],
            'NAME' => $data['NAME'],
            'LAST_NAME' => $data['LAST_NAME'],
            'LOGIN' => $data['LOGIN']
        );
    }
    else
    {//list of not linked farmers
        $arResult['FARMERS_LIST'][] = array(
            'ID' => $data['ID'],
            'ACTIVE' => $data['ACTIVE'],
            'NAME' => $data['NAME'],
            'LAST_NAME' => $data['LAST_NAME'],
            'LOGIN' => $data['LOGIN']
        );
    }
}

$this->IncludeComponentTemplate();

unset($res, $data);