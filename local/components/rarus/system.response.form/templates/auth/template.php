<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?>
<h1>Помощь</h1>
<div id="public_response_form" class="public_form <?if(isset($arParams['GROUP_ID']) && is_numeric($arParams['GROUP_ID'])){?> active<?}?>">
    <div class="close" onclick="closePublicForm(this);"></div>
    <div class="reg-cntn">
        <?php
        if (isset($arResult['SUCCESS_MESSAGE']) && trim($arResult['SUCCESS_MESSAGE']) != '') {
            ?>
            <div class="success"><?=$arResult['SUCCESS_MESSAGE']?></div>
            <?
        }else{
            ?>
            <div class="response_form active" data-val="s1">
                <form class="response-auth-form" action="" method="post" enctype="multipart/form-data">
                    <?
                    if ($arResult['SHOW_ERRORS'] == 'Y' && $arResult['ERROR']) {
                        ShowMessage($arResult['ERROR_MESSAGE']);
                    }
                    ?>
                    <input type="hidden" name="PUBLIC_FORM" value="RESPONSE_AUTH"/>
                    <input type="hidden" name="RESPONSE_AUTH_FORM" value="Y" />
                    <div class="row">
                        <div class="help_text">Если у вас есть вопросы, или возникли какие-либо проблемы, пожалуйста, воспользуйтесь формой обратной связи. В ближайшее время с вами свяжется оператор АГРОХЕЛПЕР.</div>
                    </div>
                    <div class="row">
                        <div class="holder row_val">
                            <input type="text" name="RESP_THEME" value="<?=$arResult['RESP_THEME']?>" placeholder="<?=GetMessage("RESP_THEME")?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="needItem">*</div>
                        <div class="holder row_val">
                            <textarea title="<?=GetMessage("RESP_QUESTION")?>" name="RESP_QUESTION" placeholder="<?=GetMessage("RESP_QUESTION")?>"><?=$arResult['RESP_QUESTION']?></textarea>
                        </div>
                    </div>
                    <div class="row">
                                <span class="rrs_btn_auth">
                                    <input class="submit-btn" type="submit" name="Login" value="<?=GetMessage("RESP_SUBMIT")?>" data-role="none">
                                </span>
                    </div>

                </form>
            </div>
            <?
        }
        ?>
    </div>
</div>