<?
header('Location: /partner/');
exit;
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");?>
<?$APPLICATION->SetTitle("Активность пользователей");?>

<?
global $USER;
$APPLICATION->IncludeComponent(
    "rarus:partner_dashboard",
    "",
    Array(
        "UID" => $USER->GetID(),
        "AGENT_ID" => (isset($_GET['agent_id']) ? $_GET['agent_id'] : 0),
        "DETAIL_URL" => '/partner/dashboard/detail.php'
    )
);
?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>