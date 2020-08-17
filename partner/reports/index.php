<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");?>
<?$APPLICATION->SetTitle("Отчет по вознаграждениям");?>
<?LocalRedirect('/partner/profile/'); exit; ?>
<?$APPLICATION->IncludeComponent(
    "rarus:report.remuneration",
    "",
    Array(),
    false
);?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>