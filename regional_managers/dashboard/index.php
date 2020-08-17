<? require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Активность пользователей");
?>

<?
global $USER;
$APPLICATION->IncludeComponent(
    "rarus:regional_manager_dashboard",
    "",
    Array(
        "UID" => $USER->GetID(),
        "PARTNER_ID" => (isset($_GET['partner_id']) ? $_GET['partner_id'] : 0),
        "AGENT_ID" => (isset($_GET['agent_id']) ? $_GET['agent_id'] : 0),
        "DETAIL_URL" => '/regional_managers/dashboard/detail.php'
    )
);
?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>