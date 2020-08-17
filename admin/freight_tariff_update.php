<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");?>
<?$APPLICATION->SetTitle("Обновление тарифов на перевозку");?>
<?
LocalRedirect('/admin/tariff/');
?>
<?$APPLICATION->IncludeComponent(
    "rarus:freight.tariff.update",
    "",
    Array(),
    false
);?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>