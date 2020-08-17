<?
//check if restore password form
if(isset($_GET['change_password']) && $_GET['change_password'] == 'yes'
    && isset($_GET['USER_CHECKWORD']) && trim($_GET['USER_CHECKWORD']) != ''
    && isset($_GET['USER_LOGIN']) && trim($_GET['USER_LOGIN']) != ''
)
{
    define('NEED_AUTH', 'Y');
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

//устанавливаем индексацию главной страницы (остальные страницы скрыты от индексации)
$APPLICATION->SetPageProperty("robots", "index, follow");

$APPLICATION->SetTitle("АГРОХЕЛПЕР");?>

    <!-- Текстовка -->
    <section>
        <div class="content-block">
            <div class="content-left-box">
                <div class="page_sub_title">
                    <?$APPLICATION->IncludeComponent(
                        "bitrix:main.include",
                        "",
                        array(
                            "AREA_FILE_SHOW" => "file",
                            "PATH" => "/include/main_public/1_about_title.php"
                        ),
                        false
                    );?>
                </div>
                <div class="content-text">
                    <?$APPLICATION->IncludeComponent(
                        "bitrix:main.include",
                        "",
                        array(
                            "AREA_FILE_SHOW" => "file",
                            "PATH" => "/include/main_public/1_about_text.php"
                        ),
                        false
                    );?>
                </div>
            </div>
            <div class="content-right-box">
                <div class="content-media">
                    <?$APPLICATION->IncludeComponent(
                        "bitrix:main.include",
                        "",
                        array(
                            "AREA_FILE_SHOW" => "file",
                            "PATH" => "/include/main_public/1_about_video.php"
                        ),
                        false
                    );?>

                    <?if($_SESSION['SESS_INCLUDE_AREAS']):?>
                    <?else:?>
                    <?endif;?>
                </div>
            </div>
        </div>
    </section>


    <!-- Аккардион -->
    <section>
        <div class="accordions-list-block with-num big-titles">

            <!-- Строка (покупатель) -->
            <div class="accordion-block with-file active">
                <div class="accordion-title">
                    <div class="title-text">Покупателю</div>
                    <div class="indicator"></div>
                    <div class="clip"></div>
                </div>
                <div class="accordion-content-wrap">
                    <div class="accordion-content">
                        <!-- Большой экран -->
                        <div class="d-sm-none">
                            <!-- Строка -->
                            <div class="accordion-content-row">
                                <div class="accordion-content-row-columns-block">
                                    <div class="accordion-content-row-column">
                                        <!-- Фича -->
                                        <div class="features-wrap">
                                            <div class="content-icon">
                                                <?$APPLICATION->IncludeComponent(
                                                    "bitrix:main.include",
                                                    "",
                                                    array(
                                                        "AREA_FILE_SHOW" => "file",
                                                        "PATH" => "/include/main_public/buyer/1_icon.php"
                                                    ),
                                                    false
                                                );?>
                                            </div>
                                            <div class="content-title">
                                                <?$APPLICATION->IncludeComponent(
                                                    "bitrix:main.include",
                                                    "",
                                                    array(
                                                        "AREA_FILE_SHOW" => "file",
                                                        "PATH" => "/include/main_public/buyer/1_title.php"
                                                    ),
                                                    false
                                                );?>
                                            </div>
                                            <div class="content-text">
                                                <?$APPLICATION->IncludeComponent(
                                                    "bitrix:main.include",
                                                    "",
                                                    array(
                                                        "AREA_FILE_SHOW" => "file",
                                                        "PATH" => "/include/main_public/buyer/1_text.php"
                                                    ),
                                                    false
                                                );?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="accordion-content-row-column">
                                        <?$APPLICATION->IncludeComponent(
                                            "bitrix:main.include",
                                            "",
                                            array(
                                                "AREA_FILE_SHOW" => "file",
                                                "PATH" => "/include/main_public/buyer/paper.php"
                                            ),
                                            false
                                        );?>
                                    </div>
                                </div>
                            </div>
                            <!-- Строка -->
                            <div class="accordion-content-row">
                                <div class="accordion-content-row-columns-block">
                                    <div class="accordion-content-row-column">
                                        <!-- Фича -->
                                        <div class="features-wrap">
                                            <div class="content-icon">
                                                <?$APPLICATION->IncludeComponent(
                                                    "bitrix:main.include",
                                                    "",
                                                    array(
                                                        "AREA_FILE_SHOW" => "file",
                                                        "PATH" => "/include/main_public/buyer/2_icon.php"
                                                    ),
                                                    false
                                                );?>
                                            </div>
                                            <div class="content-title">
                                                <?$APPLICATION->IncludeComponent(
                                                    "bitrix:main.include",
                                                    "",
                                                    array(
                                                        "AREA_FILE_SHOW" => "file",
                                                        "PATH" => "/include/main_public/buyer/2_title.php"
                                                    ),
                                                    false
                                                );?>
                                            </div>
                                            <div class="content-text">
                                                <?$APPLICATION->IncludeComponent(
                                                    "bitrix:main.include",
                                                    "",
                                                    array(
                                                        "AREA_FILE_SHOW" => "file",
                                                        "PATH" => "/include/main_public/buyer/2_text.php"
                                                    ),
                                                    false
                                                );?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="accordion-content-row-column">
                                        <!-- Фича -->
                                        <div class="features-wrap">
                                            <div class="content-icon">
                                                <?$APPLICATION->IncludeComponent(
                                                    "bitrix:main.include",
                                                    "",
                                                    array(
                                                        "AREA_FILE_SHOW" => "file",
                                                        "PATH" => "/include/main_public/buyer/3_icon.php"
                                                    ),
                                                    false
                                                );?>
                                            </div>
                                            <div class="content-title">
                                                <?$APPLICATION->IncludeComponent(
                                                    "bitrix:main.include",
                                                    "",
                                                    array(
                                                        "AREA_FILE_SHOW" => "file",
                                                        "PATH" => "/include/main_public/buyer/3_title.php"
                                                    ),
                                                    false
                                                );?>
                                            </div>
                                            <div class="content-text">
                                                <?$APPLICATION->IncludeComponent(
                                                    "bitrix:main.include",
                                                    "",
                                                    array(
                                                        "AREA_FILE_SHOW" => "file",
                                                        "PATH" => "/include/main_public/buyer/3_text.php"
                                                    ),
                                                    false
                                                );?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Мобилка -->
                        <div class="d-sm-block d-none text-center">
                            <!-- Строка -->
                            <div class="accordion-content-row">
                                <div class="owl-carousel owl-theme">
                                    <!-- Фича -->
                                    <div class="features-wrap">
                                        <div class="content-icon">
                                            <?$APPLICATION->IncludeComponent(
                                                "bitrix:main.include",
                                                "",
                                                array(
                                                    "AREA_FILE_SHOW" => "file",
                                                    "PATH" => "/include/main_public/buyer/1_icon.php"
                                                ),
                                                false
                                            );?>
                                        </div>
                                        <div class="content-title">
                                            <?$APPLICATION->IncludeComponent(
                                                "bitrix:main.include",
                                                "",
                                                array(
                                                    "AREA_FILE_SHOW" => "file",
                                                    "PATH" => "/include/main_public/buyer/1_title.php"
                                                ),
                                                false
                                            );?>
                                        </div>
                                        <div class="content-text">
                                            <?$APPLICATION->IncludeComponent(
                                                "bitrix:main.include",
                                                "",
                                                array(
                                                    "AREA_FILE_SHOW" => "file",
                                                    "PATH" => "/include/main_public/buyer/1_text.php"
                                                ),
                                                false
                                            );?>
                                        </div>
                                    </div>
                                    <!-- Фича -->
                                    <div class="features-wrap">
                                        <div class="content-icon">
                                            <?$APPLICATION->IncludeComponent(
                                                "bitrix:main.include",
                                                "",
                                                array(
                                                    "AREA_FILE_SHOW" => "file",
                                                    "PATH" => "/include/main_public/buyer/2_icon.php"
                                                ),
                                                false
                                            );?>
                                        </div>
                                        <div class="content-title">
                                            <?$APPLICATION->IncludeComponent(
                                                "bitrix:main.include",
                                                "",
                                                array(
                                                    "AREA_FILE_SHOW" => "file",
                                                    "PATH" => "/include/main_public/buyer/2_title.php"
                                                ),
                                                false
                                            );?>
                                        </div>
                                        <div class="content-text">
                                            <?$APPLICATION->IncludeComponent(
                                                "bitrix:main.include",
                                                "",
                                                array(
                                                    "AREA_FILE_SHOW" => "file",
                                                    "PATH" => "/include/main_public/buyer/2_text.php"
                                                ),
                                                false
                                            );?>
                                        </div>
                                    </div>
                                    <!-- Фича -->
                                    <div class="features-wrap">
                                        <div class="content-icon">
                                            <?$APPLICATION->IncludeComponent(
                                                "bitrix:main.include",
                                                "",
                                                array(
                                                    "AREA_FILE_SHOW" => "file",
                                                    "PATH" => "/include/main_public/buyer/3_icon.php"
                                                ),
                                                false
                                            );?>
                                        </div>
                                        <div class="content-title">
                                            <?$APPLICATION->IncludeComponent(
                                                "bitrix:main.include",
                                                "",
                                                array(
                                                    "AREA_FILE_SHOW" => "file",
                                                    "PATH" => "/include/main_public/buyer/3_title.php"
                                                ),
                                                false
                                            );?>
                                        </div>
                                        <div class="content-text">
                                            <?$APPLICATION->IncludeComponent(
                                                "bitrix:main.include",
                                                "",
                                                array(
                                                    "AREA_FILE_SHOW" => "file",
                                                    "PATH" => "/include/main_public/buyer/3_text.php"
                                                ),
                                                false
                                            );?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Строка -->
                            <div class="accordion-content-row">
                                <?$APPLICATION->IncludeComponent(
                                    "bitrix:main.include",
                                    "",
                                    array(
                                        "AREA_FILE_SHOW" => "file",
                                        "PATH" => "/include/main_public/buyer/paper.php"
                                    ),
                                    false
                                );?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <!-- Строка (поставщик) -->
            <div class="accordion-block with-file active">
                <div class="accordion-title">
                    <div class="title-text">Поставщику</div>
                    <div class="indicator"></div>
                    <div class="clip"></div>
                </div>
                <div class="accordion-content-wrap">
                    <div class="accordion-content">
                        <!-- Большой экран -->
                        <div class="d-sm-none">
                            <!-- Строка -->
                            <div class="accordion-content-row">
                                <div class="accordion-content-row-columns-block">
                                    <div class="accordion-content-row-column">
                                        <!-- Фича -->
                                        <div class="features-wrap">
                                            <div class="content-icon">
                                                <?$APPLICATION->IncludeComponent(
                                                    "bitrix:main.include",
                                                    "",
                                                    array(
                                                        "AREA_FILE_SHOW" => "file",
                                                        "PATH" => "/include/main_public/agroproducer/1_icon.php"
                                                    ),
                                                    false
                                                );?>
                                            </div>
                                            <div class="content-title">
                                                <?$APPLICATION->IncludeComponent(
                                                    "bitrix:main.include",
                                                    "",
                                                    array(
                                                        "AREA_FILE_SHOW" => "file",
                                                        "PATH" => "/include/main_public/agroproducer/1_title.php"
                                                    ),
                                                    false
                                                );?>
                                            </div>
                                            <div class="content-text">
                                                <?$APPLICATION->IncludeComponent(
                                                    "bitrix:main.include",
                                                    "",
                                                    array(
                                                        "AREA_FILE_SHOW" => "file",
                                                        "PATH" => "/include/main_public/agroproducer/1_text.php"
                                                    ),
                                                    false
                                                );?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="accordion-content-row-column">
                                        <?$APPLICATION->IncludeComponent(
                                            "bitrix:main.include",
                                            "",
                                            array(
                                                "AREA_FILE_SHOW" => "file",
                                                "PATH" => "/include/main_public/agroproducer/paper.php"
                                            ),
                                            false
                                        );?>
                                    </div>
                                </div>
                            </div>
                            <!-- Строка -->
                            <div class="accordion-content-row">
                                <div class="accordion-content-row-columns-block">
                                    <div class="accordion-content-row-column">
                                        <!-- Фича -->
                                        <div class="features-wrap">
                                            <div class="content-icon">
                                                <?$APPLICATION->IncludeComponent(
                                                    "bitrix:main.include",
                                                    "",
                                                    array(
                                                        "AREA_FILE_SHOW" => "file",
                                                        "PATH" => "/include/main_public/agroproducer/2_icon.php"
                                                    ),
                                                    false
                                                );?>
                                            </div>
                                            <div class="content-title">
                                                <?$APPLICATION->IncludeComponent(
                                                    "bitrix:main.include",
                                                    "",
                                                    array(
                                                        "AREA_FILE_SHOW" => "file",
                                                        "PATH" => "/include/main_public/agroproducer/2_title.php"
                                                    ),
                                                    false
                                                );?>
                                            </div>
                                            <div class="content-text">
                                                <?$APPLICATION->IncludeComponent(
                                                    "bitrix:main.include",
                                                    "",
                                                    array(
                                                        "AREA_FILE_SHOW" => "file",
                                                        "PATH" => "/include/main_public/agroproducer/2_text.php"
                                                    ),
                                                    false
                                                );?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="accordion-content-row-column">
                                        <!-- Фича -->
                                        <div class="features-wrap">
                                            <div class="content-icon">
                                                <?$APPLICATION->IncludeComponent(
                                                    "bitrix:main.include",
                                                    "",
                                                    array(
                                                        "AREA_FILE_SHOW" => "file",
                                                        "PATH" => "/include/main_public/agroproducer/3_icon.php"
                                                    ),
                                                    false
                                                );?>
                                            </div>
                                            <div class="content-title">
                                                <?$APPLICATION->IncludeComponent(
                                                    "bitrix:main.include",
                                                    "",
                                                    array(
                                                        "AREA_FILE_SHOW" => "file",
                                                        "PATH" => "/include/main_public/agroproducer/3_title.php"
                                                    ),
                                                    false
                                                );?>
                                            </div>
                                            <div class="content-text">
                                                <?$APPLICATION->IncludeComponent(
                                                    "bitrix:main.include",
                                                    "",
                                                    array(
                                                        "AREA_FILE_SHOW" => "file",
                                                        "PATH" => "/include/main_public/agroproducer/3_text.php"
                                                    ),
                                                    false
                                                );?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Мобилка -->
                        <div class="d-sm-block d-none text-center">
                            <!-- Строка -->
                            <div class="accordion-content-row">
                                <div class="owl-carousel owl-theme">
                                    <!-- Фича -->
                                    <div class="features-wrap">
                                        <div class="content-icon">
                                            <?$APPLICATION->IncludeComponent(
                                                "bitrix:main.include",
                                                "",
                                                array(
                                                    "AREA_FILE_SHOW" => "file",
                                                    "PATH" => "/include/main_public/agroproducer/1_icon.php"
                                                ),
                                                false
                                            );?>
                                        </div>
                                        <div class="content-title">
                                            <?$APPLICATION->IncludeComponent(
                                                "bitrix:main.include",
                                                "",
                                                array(
                                                    "AREA_FILE_SHOW" => "file",
                                                    "PATH" => "/include/main_public/agroproducer/1_title.php"
                                                ),
                                                false
                                            );?>
                                        </div>
                                        <div class="content-text">
                                            <?$APPLICATION->IncludeComponent(
                                                "bitrix:main.include",
                                                "",
                                                array(
                                                    "AREA_FILE_SHOW" => "file",
                                                    "PATH" => "/include/main_public/agroproducer/1_text.php"
                                                ),
                                                false
                                            );?>
                                        </div>
                                    </div>
                                    <!-- Фича -->
                                    <div class="features-wrap">
                                        <div class="content-icon">
                                            <?$APPLICATION->IncludeComponent(
                                                "bitrix:main.include",
                                                "",
                                                array(
                                                    "AREA_FILE_SHOW" => "file",
                                                    "PATH" => "/include/main_public/agroproducer/2_icon.php"
                                                ),
                                                false
                                            );?>
                                        </div>
                                        <div class="content-title">
                                            <?$APPLICATION->IncludeComponent(
                                                "bitrix:main.include",
                                                "",
                                                array(
                                                    "AREA_FILE_SHOW" => "file",
                                                    "PATH" => "/include/main_public/agroproducer/2_title.php"
                                                ),
                                                false
                                            );?>
                                        </div>
                                        <div class="content-text">
                                            <?$APPLICATION->IncludeComponent(
                                                "bitrix:main.include",
                                                "",
                                                array(
                                                    "AREA_FILE_SHOW" => "file",
                                                    "PATH" => "/include/main_public/agroproducer/2_text.php"
                                                ),
                                                false
                                            );?>
                                        </div>
                                    </div>
                                    <!-- Фича -->
                                    <div class="features-wrap">
                                        <div class="content-icon">
                                            <?$APPLICATION->IncludeComponent(
                                                "bitrix:main.include",
                                                "",
                                                array(
                                                    "AREA_FILE_SHOW" => "file",
                                                    "PATH" => "/include/main_public/agroproducer/3_icon.php"
                                                ),
                                                false
                                            );?>
                                        </div>
                                        <div class="content-title">
                                            <?$APPLICATION->IncludeComponent(
                                                "bitrix:main.include",
                                                "",
                                                array(
                                                    "AREA_FILE_SHOW" => "file",
                                                    "PATH" => "/include/main_public/agroproducer/3_title.php"
                                                ),
                                                false
                                            );?>
                                        </div>
                                        <div class="content-text">
                                            <?$APPLICATION->IncludeComponent(
                                                "bitrix:main.include",
                                                "",
                                                array(
                                                    "AREA_FILE_SHOW" => "file",
                                                    "PATH" => "/include/main_public/agroproducer/3_text.php"
                                                ),
                                                false
                                            );?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Строка -->
                            <div class="accordion-content-row">
                                <?$APPLICATION->IncludeComponent(
                                    "bitrix:main.include",
                                    "",
                                    array(
                                        "AREA_FILE_SHOW" => "file",
                                        "PATH" => "/include/main_public/agroproducer/paper.php"
                                    ),
                                    false
                                );?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>


    <!-- Контакты -->
    <section>
        <div class="content-block contacts">
            <div class="content-left-box">
                <div class="page_sub_title">
                    <?$APPLICATION->IncludeComponent(
                        "bitrix:main.include",
                        "",
                        array(
                            "AREA_FILE_SHOW" => "file",
                            "PATH" => "/include/main_public/4_contacts_title.php"
                        ),
                        false
                    );?>
                </div>
                <div class="content-text">
                    <?$APPLICATION->IncludeComponent(
                        "bitrix:main.include",
                        "",
                        array(
                            "AREA_FILE_SHOW" => "file",
                            "PATH" => "/include/main_public/4_contacts_company.php"
                        ),
                        false
                    );?>
                    <div class="d-sm-none">
                        <?$APPLICATION->IncludeComponent(
                            "bitrix:main.include",
                            "",
                            array(
                                "AREA_FILE_SHOW" => "file",
                                "PATH" => "/include/main_public/4_contacts_text.php"
                            ),
                            false
                        );?>
                    </div>
                </div>
            </div>
            <div class="content-right-box">
                <div class="content-media">
                    <div id="this_yandex_map_area" style=""></div>

                    <script src="https://api-maps.yandex.ru/2.1/?lang=ru_RU" type="text/javascript"></script>
                    <script type="text/javascript">
                        function initWHMap(){
                            //старт отображения карты
                            mapObj = $('#this_yandex_map_area');
                            if(mapObj.length == 1){
                                gMap = new google.maps.Map(document.getElementById('this_yandex_map_area'), {
                                    center: {lat: 51.701658, lng: 39.161633},
                                    zoom: 11,
                                    streetViewControl: false,
                                    rotateControl: false,
                                    fullscreenControl: false
                                });
                                gMarker = new google.maps.Marker({
                                    position: {lat: 51.701658, lng: 39.161633},
                                    map: gMap,
                                    title: "АГРОХЕЛПЕР",
                                    icon: '/local/templates/main_public/images/public/baloon.png'
                                });
                            }
                        }
                    </script>
                </div>

                <div class="content-text d-sm-block d-none">
                    <?$APPLICATION->IncludeComponent(
                        "bitrix:main.include",
                        "",
                        array(
                            "AREA_FILE_SHOW" => "file",
                            "PATH" => "/include/main_public/4_contacts_text.php"
                        ),
                        false
                    );?>
                </div>
            </div>
        </div>
    </section>


    <!-- Ссылки -->
    <section>
        <div class="content-block legal-links">
            <div class="content-left-box">
                <div class="page_sub_title">
                    <?$APPLICATION->IncludeComponent(
                        "bitrix:main.include",
                        "",
                        array(
                            "AREA_FILE_SHOW" => "file",
                            "PATH" => "/include/main_public/5_links_title.php"
                        ),
                        false
                    );?>
                </div>
                <div class="content-text">
                    <?$APPLICATION->IncludeComponent(
                        "bitrix:main.include",
                        "",
                        array(
                            "AREA_FILE_SHOW" => "file",
                            "PATH" => "/include/main_public/5_links_text.php"
                        ),
                        false
                    );?>
                </div>
            </div>
            <div class="content-right-box"></div>
        </div>
    </section>

<script src="https://maps.googleapis.com/maps/api/js?key=<?=$GLOBALS['googleMapKey'];?>&callback=initWHMap&libraries=places&language=ru" async defer></script>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>