<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");?>
<?$APPLICATION->SetTitle('Информация об агенте');?>
<h1 class="page_header"><?$APPLICATION->ShowTitle();?></h1>
<?
$arMenuParams = [
    "ROOT_MENU_TYPE"        => "profile",
    "MENU_CACHE_TYPE"       => "A",
    "MENU_CACHE_TIME"       => "36000000",
    "MENU_CACHE_USE_GROUPS" => "Y",
    "MENU_CACHE_GET_VARS"   => [],
    "MAX_LEVEL"             => "1",
    "CHILD_MENU_TYPE"       => "profile",
    "USE_EXT"               => "Y",
    "DELAY"                 => "N",
    "ALLOW_MULTI_SELECT"    => "N",
    "MENU_CACHE_USE_USERS"  => 'Y',
];

if ($GLOBALS['DEMO'] == 'Y') {
    $arMenuParams['ROOT_MENU_TYPE']     = 'profile_demo';
    $arMenuParams['CHILD_MENU_TYPE']    = 'profile_demo';
}
?>
<?$APPLICATION->IncludeComponent(
    "bitrix:menu",
    "profile",
    $arMenuParams,
    false
);?>

<?$APPLICATION->IncludeComponent(
    'rarus:link_to_agent',
    '.default',
    Array(
        "CACHE_TIME"    => "36000000",
        "CACHE_TYPE"    => "A",
        "FARMER_ID"     => CUser::GetID(),
    )
);?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>