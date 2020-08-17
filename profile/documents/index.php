<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

if (!$USER->IsAuthorized()
    || !isset($_GET['uid'])
    || !is_numeric($_GET['uid'])
) {
    LocalRedirect('/');
    exit;
}

$group = getUserType($_GET['uid']);
?>
<h1><?$APPLICATION->ShowTitle();?></h1>

<?$linked_user = $APPLICATION->IncludeComponent(
    'rarus:public_profile_menu',
    '',
    Array(
        'U_ID' => $_GET['uid'],
        'TYPE' => $group['TYPE'],
        'TAB' => 'documents'
    )
);?>
<?
if (!$linked_user || !isset($GLOBALS['linked_with_doc'])) {
    LocalRedirect('/profile/?uid=' . $_GET['uid']);
    exit;
}
?>
<?
switch($group['TYPE']){
    case 'f':
        $APPLICATION->IncludeComponent(
            "rarus:farmer_profile",
            ".default",
            Array(
                'U_ID' => $_GET['uid'],
                'EDIT_PROPS_LIST' => array(
                    'UL_TYPE',
                    'NDS'
                ),
                'TYPE' => 2,
                'BY_AGENT' => 'Y'
            ),
            false
        );

        break;

    case 'c':
        $APPLICATION->IncludeComponent(
            "rarus:client_profile",
            ".default",
            Array(
                'U_ID' => $_GET['uid'],
                'EDIT_PROPS_LIST' => array(
                    'UL_TYPE',
                    'NDS'
                ),
                'TYPE' => 2,
                'BY_AGENT' => 'Y'
            ),
            false
        );

        break;
}?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>