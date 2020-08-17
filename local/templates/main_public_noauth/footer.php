<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
IncludeTemplateLangFile(__FILE__);
?>
                </div><!-- /public_content-->



                <div class="content-form policy_page public_form">
                    <div class="close" onclick="closePublicForm(this);"></div>
                    <div class="fields text_page_area">
                        <?$APPLICATION->IncludeComponent("bitrix:main.include", "",
                            array(
                                "AREA_FILE_SHOW" => "file",
                                "PATH" => "/include/policy.php"),
                            false
                        );?>
                    </div>
                </div>

                <div class="content-form agreement_page public_form" data-actionobj="agreement">
                    <div class="close" onclick="closePublicForm(this);"></div>
                    <div class="fields text_page_area">
                        <?$APPLICATION->IncludeComponent("bitrix:main.include", "",
                            array(
                                "AREA_FILE_SHOW" => "file",
                                "PATH" => "/include/agreement.php"),
                            false
                        );?>
                    </div>
                </div>

                <div class="content-form reglament_page public_form">
                    <div class="close" onclick="closePublicForm(this);"></div>
                    <div class="fields text_page_area">
                        <?$APPLICATION->IncludeComponent("bitrix:main.include", "",
                            array(
                                "AREA_FILE_SHOW" => "file",
                                "PATH" => "/include/reglament.php"),
                            false
                        );?>
                    </div>
                </div>

            </div><!-- /page_body -->

        </div><!-- /page_wrapper -->

    <div id="back_shad"></div>
    <div id="load_img"></div>

    <? /* //определяем тип устройства (мобильные, не мобильные) */?>
    <div style="display: block; position: absolute;" id="mobile_check"></div>
    <script type="text/javascript">
        if ("ontouchstart" in document.documentElement) {
            var devObj = document.getElementById('mobile_check');
            if(typeof devObj != 'undefined') {
                var widthVal = devObj.offsetWidth;
                if(widthVal === 2){
                    device_type = 'm';
                }
            }
        }
    </script>

    </body>
</html>