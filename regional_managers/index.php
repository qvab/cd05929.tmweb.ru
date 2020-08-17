<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");?>
<?$APPLICATION->SetTitle("Личный кабинет регионального менеджера");
?>

    <div class="main_page_area">
        <div class="block_area welcome_area">
            <div class="block_head">Добро пожаловать в Агрохелпер</div>

            <div class="video">
                <video width="370" height="210" preload="none" controls="controls" poster="/local/templates/main_public/images/public/farmer_video_back.jpg">
                    <source src="/local/templates/main_public/images/public/farmer_video.mp4" type='video/mp4; codecs="avc1.42E01E, mp4a.40.2"'>
                </video>
            </div>
            <div class="block_data">
                Пожалуйста, ознакомьтесь с инструкцией по использованию системы АГРОХЕЛПЕР<br/>
                <a href="/upload/docs/instruction_partner.pdf" download="instruction_partner.pdf" >Скачать инструкцию</a>

                <?
                $info = log::getLastUserActivityLog();
                if ($info['ID'] > 0) {
                    ?>
                    <div class="user_info">
                        <div class="c_icon">Активных покупателей: <?=$info['UF_CLIENT_NUM']?></div>
                        <div class="f_icon">Активных поставщиков: <?=$info['UF_FARMER_NUM']?></div>
                    </div>
                <?
                }
                ?>
            </div>
            <div class="clear"></div>
        </div>
    </div>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>