<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");?>
<?$APPLICATION->SetTitle("Мои дела"); ?>
    <h1 class="page_header"><?$APPLICATION->ShowTitle()?></h1>
<? $APPLICATION->IncludeComponent(
    "rarus:agent.farmer.affairs",
    "",
    Array(
        'FILTER_FIELDS' => [    // Поля фильтра
            'DATE_FROM' => 'Y',
            'DATE_TO'   => 'Y',
            'FARMER'    => 'Y',
            'TYPE'      => 'N', // Пока скрываем этот фильтр, т.к. сейчас в системе только дела по товарам
        ],
        'SHOW_DESCRIPTION_FARMER' => 'Y',// Выводить в описании АП
    )
);
?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>