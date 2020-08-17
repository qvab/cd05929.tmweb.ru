<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");?>
<?$APPLICATION->SetTitle("Активность пользователей");?>

<?
global $USER;
$APPLICATION->IncludeComponent(
    "rarus:dashboard_detail",
    "",
    Array(
        "MODE" => 'regional_manager',
        "UID" => $USER->GetID(),
        "USER_TYPE" => (isset($_GET['user_type']) ? $_GET['user_type'] : 0),
        "SHOW_TYPE" => (isset($_GET['show_type']) ? $_GET['show_type'] : 0),
        "AGENT_ID" => (isset($_GET['agent_id']) ? $_GET['agent_id'] : 0),
        "PARTNER_ID" => (isset($_GET['partner_id']) ? $_GET['partner_id'] : 0),
        "LIST_URL" => '/regional_managers/dashboard/'
    )
);
?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>