<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
$this->setFrameMode(true);

if($arResult['ERROR'] == 'Y')
{
    echo '<div class="err_text row">' . $arResult['ERROR_MESSAGE'] . '</div>';
}
else
{
    if (is_array($arResult['success_text']) && sizeof($arResult['success_text']) > 0){
        foreach ($arResult['success_text'] as $val) {
        ?>
            <div class="success_text" style="padding: 10px 0; color: #00c100"><?=$val?></div>
        <?
        }
    }
    else {
    ?>
        <form class="invite_form" action="" method="POST">
            <input type="hidden" name="send_invite" value="y" />
            <div class="form_area">
                    <?if($arResult['error_text'] != ''){?>
                    <div class="err_text row">
                        <?=$arResult['error_text'];?>
                    </div>
                <?}?>
                <div class="line row">
                    <div class="row_head"><span class="required" style="color: #c10000">*</span> Тип пользователя:</div>
                    <div class="row_val">
                        <div class="radio_group">
                            <div class="radio_area">
                                <input type="radio" data-text="Покупатель" name="user_type" id="user_type1" value="9" <?if(isset($_POST['user_type']) && $_POST['user_type'] == 9) echo 'checked="checked"';?> style="vertical-align: " />
                            </div>
                            <div class="radio_area">
                                <input type="radio" data-text="Поставщик" name="user_type" id="user_type2" value="11" <?if(isset($_POST['user_type']) && $_POST['user_type'] == 11) echo 'checked="checked"';?> />
                            </div>
                            <div class="radio_area">
                                <input type="radio" data-text="Транспортная компания" name="user_type" id="user_type3" value="12" <?if(isset($_POST['user_type']) && $_POST['user_type'] == 12) echo 'checked="checked"';?> />
                            </div>
                            <div class="radio_area">
                                <input type="radio" data-text="Агент покупателя" name="user_type" id="user_type5" value="14" <?if(isset($_POST['user_type']) && $_POST['user_type'] == 14) echo 'checked="checked"';?> />
                            </div>
                            <div class="radio_area">
                                <input type="radio" data-text="Агент поставщика" name="user_type" id="user_type4" value="13" <?if(isset($_POST['user_type']) && $_POST['user_type'] == 13) echo 'checked="checked"';?> />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="line row">
                    <div class="row_head"><span class="required" style="color: #c10000">*</span> Email</div>
                    <div class="row_val one_line">
                        <input placeholder="Email" type="text" data-checkval="y" data-checktype="email" name="email" value="<?=(isset($_POST['email']) ? $_POST['email'] : '');?>" />
                    </div>
                </div>
                <?/*<div class="line row">
                    <div class="row_head">ИНН</div>
                    <div class="row_val">
                        <input placeholder="ИНН" data-checkval="y" data-checktype="pos_int_empty" type="text" name="inn" value="<?=(isset($_POST['inn']) ? $_POST['inn'] : '');?>" />
                    </div>
                </div>*/?>
                <div class="line row">
                    <div class="row_head">Телефон</div>
                    <div class="row_val">
                        <input placeholder="Телефон" class="phone_msk" data-checktype="phone" type="text" name="phone" value="<?=(isset($_POST['phone']) ? $_POST['phone'] : '');?>" />
                    </div>
                </div>
                <div class="line row">
                    <div class="row_head">Отправлять приглашение:</div>
                    <div class="row_val">
                        <div class="radio_group">
                            <div class="radio_area">
                                <input type="checkbox" data-text="В смс-сообщении" name="send_sms" id="send_type1" value="Y" <? if (($_POST['send_invite'] == 'y' && $_POST['send_sms'] == 'Y') || !isset($_POST['send_invite'])) { ?>checked="checked"<? } ?> style="vertical-align: " />
                            </div>
                            <div class="radio_area">
                                <input type="checkbox" data-text="В e-mail сообщении" name="send_email" id="send_type2" value="Y" <? if (($_POST['send_invite'] == 'y' && $_POST['send_email'] == 'Y') || !isset($_POST['send_invite'])) { ?>checked="checked"<? } ?> />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row policy_row">
                    <div class="field field-option radio_group">
                        <div class="radio_area">
                            <input type="checkbox" data-text="<?=GetMessage("AUTH_REGISTER_CONFIM_BY_PARTNER");?>" name="AUTH_REG_CONFIM_BY_PARTNER" value="Y" />
                        </div>
                    </div>
                </div>

                <div class="line row">
                    <input class="submit-btn inactive" type="submit" value="Отправить" />
                </div>
            </div>
        </form>

        <script type="text/javascript">
            $(document).ready(function(){
                //check errors
                $('form.invite_form').on('submit', function(e){
                    var err = '';
                    var error_flag = false;
                    var err_scroll_top = 0;
                    var rowObj;

                    //check user type
                    var checkField = $(this).find('input[name="user_type"]:checked');
                    if(checkField.length == 0)
                    {
                        err = 'Не выбран тип пользователя';
                        error_flag = true;
                        rowObj = $(this).find('input[name="user_type"]:first').parents('.row');
                        rowObj.addClass('error');
                        if(rowObj.find('.row_err').length == 0)
                        {
                            rowObj.find('.row_val').append('<div class="row_err">' + err +  '</div>');
                        }
                        else
                        {
                            rowObj.find('.row_err').text(err);
                        }
                        err_scroll_top = rowObj.offset().top - 100;
                        err = '';
                    }

                    //check email
                    checkField = $(this).find('input[name="email"]');
                    rowObj = checkField.parents('.row');
                    if(checkField.val().toString().replace(/ /g, '') == '')
                    {
                        err = 'Укажите email';
                        error_flag = true;
                        if(err_scroll_top == 0)
                        {
                            err_scroll_top = rowObj.offset().top - 100;
                        }
                    }
                    else if(!checkEmailRfc(checkField.val()))
                    {
                        err = 'Укажите корректный email';
                        error_flag = true;
                        if(err_scroll_top == 0)
                        {
                            err_scroll_top = rowObj.offset().top - 100;
                        }
                    }
                    if(err != '')
                    {
                        rowObj = checkField.parents('.row');
                        rowObj.addClass('error');
                        if(rowObj.find('.row_err').length == 0)
                        {
                            rowObj.find('.row_val').append('<div class="row_err">' + err +  '</div>');
                        }
                        else
                        {
                            rowObj.find('.row_err').text(err);
                        }
                        if(err_scroll_top == 0)
                        {
                            err_scroll_top = rowObj.offset().top - 100;
                        }
                        err = '';
                    }

                    if(error_flag != false)
                    {
                        $('form.invite_form .submit-btn').addClass('inactive');
                        e.preventDefault();
                    }
                });

                //remove error message after change
                $('.row_val input[type="text"], .row_val input[type="radio"], .radio_area input[type="checkbox"]').on('change', function(){
                    var err_obj = $(this).parents('.row.error');
                    if(err_obj.length == 1)
                    {
                        err_obj.removeClass('error');
                    }

                    //remove inactive class
                    if($('form.invite_form .row.error').length == 0)
                    {
                        $('form.invite_form .submit-btn').removeClass('inactive');
                    }
                });
            });
        </script>
    <?}
}
?>