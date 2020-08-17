<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");?>
<?$APPLICATION->SetTitle("Активность пользователей");?>

<?
$APPLICATION->IncludeComponent(
    "rarus:admin_dashboard",
    "",
    Array(
        "PARTNER_ID" => (isset($_GET['partner_id']) ? $_GET['partner_id'] : 0),
        "AGENT_ID" => (isset($_GET['agent_id']) ? $_GET['agent_id'] : 0),
        "DETAIL_URL" => '/admin/dashboard/detail.php',
    )
);
?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>