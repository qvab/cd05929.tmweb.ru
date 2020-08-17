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
<?
$APPLICATION->IncludeComponent(
    "rarus:agent.farmer.affairs",
    "",
    Array(
        'FILTER_FIELDS' => [                        // Поля фильтра
            'DATE_FROM' => 'Y',
            'DATE_TO'   => 'Y',
            'FARMER'    => 'N',
            'TYPE'      => 'N',
        ],
        'FARMER_ID'     => $_GET['uid'],
        'HIDDEN_INPUTS' => ['uid' => $_GET['uid']], // [name=>value] - скрытые поля
        'SHOW_DESCRIPTION_FARMER' => 'N',           // Выводить в описании АП
        'ADD_NEW'       => 'Y'
    )
);
?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>