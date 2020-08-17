<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");?>
<?$APPLICATION->SetTitle("Личный кабинет транспортной компании");?>

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
                <a href="/upload/docs/instruction_transport.pdf" download="instruction_transport.pdf" >Скачать инструкцию</a>

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


        <?
        $APPLICATION->IncludeComponent("rarus:user_main_page_workpanel", ".default", Array(
                'U_ID' => $USER->GetID()
            ),
            false
        );
        ?>

        <div class="block_area">
            <div class="block_head">Последние уведомления</div>
            <div class="block_data">
                <?
                $GLOBALS['arrFilter'] = array();
                $GLOBALS['arrFilter']['PROPERTY_USER'] = $USER->GetID();

                $APPLICATION->IncludeComponent(
                    "bitrix:news.list",
                    "notice_list",
                    Array(
                        "ACTIVE_DATE_FORMAT" => "d.m.Y",
                        "ADD_SECTIONS_CHAIN" => "N",
                        "AJAX_MODE" => "N",
                        "AJAX_OPTION_ADDITIONAL" => "",
                        "AJAX_OPTION_HISTORY" => "N",
                        "AJAX_OPTION_JUMP" => "N",
                        "AJAX_OPTION_STYLE" => "Y",
                        "CACHE_FILTER" => "N",
                        "CACHE_GROUPS" => "Y",
                        "CACHE_TIME" => "36000000",
                        "CACHE_TYPE" => "A",
                        "CHECK_DATES" => "Y",
                        "DETAIL_URL" => "",
                        "DISPLAY_BOTTOM_PAGER" => "N",
                        "DISPLAY_DATE" => "Y",
                        "DISPLAY_NAME" => "Y",
                        "DISPLAY_PICTURE" => "N",
                        "DISPLAY_PREVIEW_TEXT" => "N",
                        "DISPLAY_TOP_PAGER" => "N",
                        "FIELD_CODE" => array("NAME","DATE_CREATE"),
                        "FILTER_NAME" => "arrFilter",
                        "HIDE_LINK_WHEN_NO_DETAIL" => "N",
                        "IBLOCK_ID" => "51",
                        "IBLOCK_TYPE" => "services",
                        "INCLUDE_IBLOCK_INTO_CHAIN" => "N",
                        "INCLUDE_SUBSECTIONS" => "Y",
                        "MESSAGE_404" => "",
                        "NEWS_COUNT" => "5",
                        "PAGER_BASE_LINK_ENABLE" => "N",
                        "PAGER_DESC_NUMBERING" => "N",
                        "PAGER_DESC_NUMBERING_CACHE_TIME" => "36000",
                        "PAGER_SHOW_ALL" => "N",
                        "PAGER_SHOW_ALWAYS" => "N",
                        "PAGER_TEMPLATE" => "rarus",
                        "PAGER_TITLE" => "Новости",
                        "PARENT_SECTION" => "",
                        "PARENT_SECTION_CODE" => "",
                        "PREVIEW_TRUNCATE_LEN" => "",
                        "PROPERTY_CODE" => array("USER","TYPE","LINK_HREF","LINK_NAME","READ",""),
                        "SET_BROWSER_TITLE" => "N",
                        "SET_LAST_MODIFIED" => "N",
                        "SET_META_DESCRIPTION" => "N",
                        "SET_META_KEYWORDS" => "N",
                        "SET_STATUS_404" => "N",
                        "SET_TITLE" => "N",
                        "SHOW_404" => "N",
                        "SORT_BY1" => "ACTIVE_FROM",
                        "SORT_BY2" => "SORT",
                        "SORT_ORDER1" => "DESC",
                        "SORT_ORDER2" => "ASC",
                        "AUTO_READ" => false
                    )
                );?>
            </div>
        </div>

    </div>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>