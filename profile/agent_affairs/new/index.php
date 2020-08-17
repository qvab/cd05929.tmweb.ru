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
        'TAB' => 'agent_affairs'
    )
);
if (!$linked_user) {
    LocalRedirect('/profile/?uid=' . $_GET['uid']);
    exit;
}?>
<?$APPLICATION->IncludeComponent(
    "rarus:agent.farmer.offer.affairs.form",
    "",
    array(
            'FARMER_ID' => $_GET['uid']
    )
);?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>