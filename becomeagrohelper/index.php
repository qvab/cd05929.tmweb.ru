<?php
/**
 * Created by 1C-Rarus
 *
 * @author Постников Василий <postva@rarus.ru>
 *
 * @var CUser $USER
 */
define('PUBLIC_AREA', 'Y');
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

if($USER->IsAuthorized()){
    LocalRedirect('/');
    exit;
}
$APPLICATION->SetTitle("АГРОХЕЛПЕР");

?>
<a class="close" href="/"></a>

<div class="page_sub_title">Стать партнером АГРОХЕЛПЕР</div>

<div class="accordions-list-block inner with-num big-titles">
        <!-- Строка (поставщик) -->
        <div class="accordion-block with-file  active">
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
                                                    "PATH" => "/include/main_public/becomeagrohelper/1_icon.php"
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
                                                    "PATH" => "/include/main_public/becomeagrohelper/1_title.php"
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
                                                    "PATH" => "/include/main_public/becomeagrohelper/1_text.php"
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
                                                    "PATH" => "/include/main_public/becomeagrohelper/2_icon.php"
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
                                                    "PATH" => "/include/main_public/becomeagrohelper/2_title.php"
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
                                                    "PATH" => "/include/main_public/becomeagrohelper/2_text.php"
                                                ),
                                                false
                                            );?>
                                        </div>
                                    </div>
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
                                                    "PATH" => "/include/main_public/becomeagrohelper/3_icon.php"
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
                                                    "PATH" => "/include/main_public/becomeagrohelper/3_title.php"
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
                                                    "PATH" => "/include/main_public/becomeagrohelper/3_text.php"
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
                                                    "PATH" => "/include/main_public/becomeagrohelper/4_icon.php"
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
                                                    "PATH" => "/include/main_public/becomeagrohelper/4_title.php"
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
                                                    "PATH" => "/include/main_public/becomeagrohelper/4_text.php"
                                                ),
                                                false
                                            );?>
                                        </div>
                                    </div>
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
                                                    "PATH" => "/include/main_public/becomeagrohelper/5_icon.php"
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
                                                    "PATH" => "/include/main_public/becomeagrohelper/5_title.php"
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
                                                    "PATH" => "/include/main_public/becomeagrohelper/5_text.php"
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
                                                    "PATH" => "/include/main_public/becomeagrohelper/6_icon.php"
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
                                                    "PATH" => "/include/main_public/becomeagrohelper/6_title.php"
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
                                                    "PATH" => "/include/main_public/becomeagrohelper/6_text.php"
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
                                                "PATH" => "/include/main_public/becomeagrohelper/1_icon.php"
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
                                                "PATH" => "/include/main_public/becomeagrohelper/1_title.php"
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
                                                "PATH" => "/include/main_public/becomeagrohelper/1_text.php"
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
                                                "PATH" => "/include/main_public/becomeagrohelper/2_icon.php"
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
                                                "PATH" => "/include/main_public/becomeagrohelper/2_title.php"
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
                                                "PATH" => "/include/main_public/becomeagrohelper/2_text.php"
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
                                                "PATH" => "/include/main_public/becomeagrohelper/3_icon.php"
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
                                                "PATH" => "/include/main_public/becomeagrohelper/3_title.php"
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
                                                "PATH" => "/include/main_public/becomeagrohelper/3_text.php"
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
                                                "PATH" => "/include/main_public/becomeagrohelper/4_icon.php"
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
                                                "PATH" => "/include/main_public/becomeagrohelper/4_title.php"
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
                                                "PATH" => "/include/main_public/becomeagrohelper/4_text.php"
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
                                                "PATH" => "/include/main_public/becomeagrohelper/5_icon.php"
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
                                                "PATH" => "/include/main_public/becomeagrohelper/5_title.php"
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
                                                "PATH" => "/include/main_public/becomeagrohelper/5_text.php"
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
                                                "PATH" => "/include/main_public/becomeagrohelper/6_icon.php"
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
                                                "PATH" => "/include/main_public/becomeagrohelper/6_title.php"
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
                                                "PATH" => "/include/main_public/becomeagrohelper/6_text.php"
                                            ),
                                            false
                                        );?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<div class="page_sub_title">Заполните анкету</div>

<?$APPLICATION->IncludeComponent(
    "rarus:become_agrohelper",
    "",
    Array(
        "CACHE_TIME"  =>  0,
        "CACHE_TYPE"  =>  'Y',
    )
);?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>