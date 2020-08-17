<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");?>
<?$APPLICATION->SetTitle("Отчет по перевозкам");?>
<?$APPLICATION->IncludeComponent(
    "rarus:report.remuneration",
    "transportation",
    Array(),
    false
);?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>