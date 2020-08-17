<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
$arResult = $templateData;

$userId = $USER->GetID();

if ($GLOBALS['rrs_user_perm_level'] == 'c' && $userId != $arResult['DEAL']['PROPERTY_CLIENT_VALUE']) {
    LocalRedirect('/client/deals/');
}
elseif ($GLOBALS['rrs_user_perm_level'] == 'f' && $userId != $arResult['DEAL']['PROPERTY_FARMER_VALUE']) {
    LocalRedirect('/farmer/deals/');
}
elseif ($GLOBALS['rrs_user_perm_level'] == 'p' && $userId != $arResult['DEAL']['PROPERTY_PARTNER_VALUE']) {
    LocalRedirect('/partner/deals/');
}
elseif ($GLOBALS['rrs_user_perm_level'] == 't' && $userId != $arResult['DEAL']['PROPERTY_TRANSPORT_VALUE']) {
    LocalRedirect('/transport/deals/');
}
?>