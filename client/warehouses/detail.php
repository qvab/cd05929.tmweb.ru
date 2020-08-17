<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

//если у нас предположительно есть ID склада, пробуем получить его данные
$APPLICATION->SetAdditionalCSS("/client/warehouses/styles.css", true);
$APPLICATION->SetTitle('Добавление склада');
?>
<h1 class="page_header"><?$APPLICATION->ShowTitle()?></h1>
<?$APPLICATION->IncludeComponent(
    "rarus:warehouse_add_form",
    "",
    Array(
        "USER_ID" => $USER->GetID(),
        "USER_TYPE" => "client",
    )
);?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>