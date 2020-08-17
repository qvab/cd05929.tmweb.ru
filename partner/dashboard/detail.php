<?
header('Location: /partner/');
exit;
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");?>
<?$APPLICATION->SetTitle("Активность пользователей");?>

<?
global $USER;
$APPLICATION->IncludeComponent(
    "rarus:dashboard_detail",
    "",
    Array(
        "MODE" => 'partner',
        "UID" => $USER->GetID(),
        "USER_TYPE" => (isset($_GET['user_type']) ? $_GET['user_type'] : 0),
        "SHOW_TYPE" => (isset($_GET['show_type']) ? $_GET['show_type'] : 0),
        "AGENT_ID" => (isset($_GET['agent_id']) ? $_GET['agent_id'] : 0),
        "PARTNER_ID" => 0,
        "LIST_URL" => '/partner/dashboard/'
    )
);
?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>