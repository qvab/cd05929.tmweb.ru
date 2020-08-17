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
CPageOption::SetOptionString("main", "nav_page_in_session", "N");

$user_id = 0;
//проверка обязательных полей
if(isset($arParams['CLIENT_ID'])
    && filter_var($arParams['CLIENT_ID'], FILTER_VALIDATE_INT)
){
    $user_id = $arParams['CLIENT_ID'];
}else {
    $arResult['ERROR_MESSAGE'] = 'Не указан ID пользователя';
    return false;
}

$arResult['LIMITS'] = client::openerCountGet($user_id);

$this->includeComponentTemplate();