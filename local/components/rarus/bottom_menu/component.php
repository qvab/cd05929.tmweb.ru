<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
/** @var CBitrixComponent $this */
/** @var array $arParams */
/** @var array $arResult */
/** @var string $componentPath */
/** @var string $componentName */
/** @var string $componentTemplate */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

// Результирующий массив
$arResult = [
    'URL_PROFILE'       => null,
    'URL_HELP'          => null,
    'bActiveProfile'    => false,
    'bActiveHelp'       => false,
];


// Определяем урл для разных ролей
switch ($arParams['PERM_LEVEL']) {
    case 'c':

        $arResult['URL_PROFILE']    = '/client/profile/';
        $arResult['URL_HELP']       = '/client/help/';

        $arResult['URL_SUB_PROFILE'] = [
            '/client/profile/change_password/',
            '/client/documents/',
            '/client/link_to_partner/',
        ];

        break;
    case 'f':

        $arResult['URL_PROFILE']    = '/farmer/profile/';
        $arResult['URL_HELP']       = '/farmer/help/';

        $arResult['URL_SUB_PROFILE'] = [
            '/farmer/profile/change_password/',
            '/farmer/documents/',
            '/farmer/link_to_partner/',
            '/farmer/agent_info/'
        ];

        break;
    case 't':

        $arResult['URL_PROFILE']    = '/transport/profile/';
        $arResult['URL_HELP']       = '/transport/help/';

        $arResult['URL_SUB_PROFILE'] = [
            '/transport/profile/change_password/',
            '/transport/documents/',
            '/transport/link_to_partner/',
        ];

        break;
    case 'p':

        $arResult['URL_PROFILE']    = '/partner/profile/';
        $arResult['URL_HELP']       = '/partner/help/';

        $arResult['URL_SUB_PROFILE'] = [
            '/partner/profile/change_password/',
            '/partner/documents/',
        ];

        break;
    case 'ag':

        $arResult['URL_PROFILE']    = '/agent/profile/';
        $arResult['URL_HELP']       = '/agent/help/';

        $arResult['URL_SUB_PROFILE'] = [
            '/agent/profile/change_password/',
        ];

        break;

    case 'agc':

        $arResult['URL_PROFILE']    = '/client_agent/profile/';
        $arResult['URL_HELP']       = '/client_agent/help/';

        $arResult['URL_SUB_PROFILE'] = [
            '/client_agent/profile/change_password/',
        ];

        break;
}

// Определяем активность ссылки
$sCurDir = $APPLICATION->GetCurDir();
if($arResult['URL_PROFILE'] && ($arResult['URL_PROFILE'] == $sCurDir || in_array($sCurDir, $arResult['URL_SUB_PROFILE']))) {
    $arResult['bActiveProfile'] = true;
} elseif ($arResult['URL_HELP'] && $arResult['URL_HELP'] == $sCurDir) {
    $arResult['bActiveHelp'] = true;
}

$this->IncludeComponentTemplate();