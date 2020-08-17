<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php"); ?>


<?$APPLICATION->SetTitle("Личный кабинет администратора");?>
    <div class="main_page_area">
        <div class="block_area welcome_area">
            <div class="block_head">Добро пожаловать в Агрохелпер</div>
            <div class="clear"></div>
        </div>

        <?$APPLICATION->IncludeComponent(
            "rarus:user_main_page_workpanel",
            ".default",
            Array(
                'U_ID' => $USER->GetID()
            ),
            false
        );?>


    </div>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>