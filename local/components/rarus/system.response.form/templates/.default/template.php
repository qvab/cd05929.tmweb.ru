<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?>
<div id="public_response_form" class="public_form">
    <div class="close" onclick="closePublicForm(this);"></div>
    <div class="page_sub_title">Обратная связь</div>
    <div class="reg-cntn">
        <?php
        if (isset($arResult['SUCCESS_MESSAGE']) && trim($arResult['SUCCESS_MESSAGE']) != '') {
            ?>
            <div class="success"><?=htmlspecialcharsbx($arResult['SUCCESS_MESSAGE'])?></div>
            <?
        }else{
            ?>
            <div class="response_form active" data-val="s1">
                <form class="response-form" action="" method="post" enctype="multipart/form-data">
                    <?
                    if ($arResult['SHOW_ERRORS'] == 'Y' && $arResult['ERROR']) {
                        ShowMessage($arResult['ERROR_MESSAGE']);
                    }
                    foreach($arResult["POST"] as $key => $value) {
                        ?>
                        <input type="hidden" name="<?=$key?>" value="<?=$value?>" />
                        <?
                    }
                    ?>
                    <input type="hidden" name="PUBLIC_FORM" value="RESPONSE"/>
                    <input type="hidden" name="RESPONSE_FORM" value="Y" />
                    <div class="row">
                        <div class="help_text">Если у вас есть вопросы, или возникли какие-либо проблемы, пожалуйста, воспользуйтесь формой обратной связи. В ближайшее время с вами свяжется оператор АГРОХЕЛПЕР.</div>
                    </div>
                    <div class="row">
                        <div class="needItem">*</div>
                        <div class="holder row_val">
                            <input type="text" name="USER_NAME" value="<?=htmlspecialcharsbx($arResult['USER_NAME'])?>" placeholder="<?=GetMessage("USER_NAME")?>" />
                        </div>
                    </div>
                    <div class="row">
                        <div class="holder row_val">
                            <input type="text" name="USER_PHONE" value="<?=htmlspecialcharsbx($arResult['USER_PHONE'])?>" placeholder="<?=GetMessage("USER_PHONE")?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="needItem">*</div>
                        <div class="holder row_val">
                            <input type="text" name="USER_EMAIL" value="<?=htmlspecialcharsbx($arResult['USER_EMAIL'])?>" placeholder="<?=GetMessage("USER_EMAIL")?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="holder row_val">
                            <input type="text" name="RESP_THEME" value="<?=htmlspecialcharsbx($arResult['RESP_THEME'])?>" placeholder="<?=GetMessage("RESP_THEME")?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="needItem">*</div>
                        <div class="holder row_val">
                            <textarea title="<?=GetMessage("RESP_QUESTION")?>" name="RESP_QUESTION" placeholder="<?=GetMessage("RESP_QUESTION")?>"><?=htmlspecialcharsbx($arResult['RESP_QUESTION'])?></textarea>
                        </div>
                    </div>
                    <div class="row">
                        <div class="field field-option radio_group">
                            <div class="radio_area">
                                <input type="checkbox" data-text="<?=GetMessage("RESP_CONFIM")?>" id="RESP_CONFIM" name="RESP_CONFIM" value="Y" />
                            </div>
                        </div>
                    </div>

                    <div id="recaptcha" class="g-recaptcha g-relazy"
                         data-sitekey="6LeDzmAUAAAAABZV4UNfOq9SzwqDqtWJXvtDPb5G"
                         data-callback="onRecSubmit"
                         data-size="invisible"></div>
                    <div class="row">
                                <span class="rrs_btn_auth">
                                    <!--<button id="responseSubmit" class="submit-btn g-recaptcha" data-sitekey="6LeDzmAUAAAAABZV4UNfOq9SzwqDqtWJXvtDPb5G" data-callback="responseSubmit"
                                            name="Login"><?=GetMessage("RESP_SUBMIT")?></button>-->
                                    <button id="resp_submit" class="submit-btn" name="Login"><?=GetMessage("RESP_SUBMIT")?></button>
                                </span>

                    </div>

                </form>
            </div>
            <?
        }
        ?>
    </div>
</div>