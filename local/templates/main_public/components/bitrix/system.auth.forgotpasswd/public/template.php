<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<div class="content-form forgot-form public_form <?if(isset($_POST['PUBLIC_FORM']) && $_POST['PUBLIC_FORM'] == 'RESTORE'){?>active<?}?>">
    <div class="close" onclick="closePublicForm(this);"></div>
    <div class="page_sub_title">Восстановление пароля</div>
    <div class="fields">
        <?
        if(isset($_POST['PUBLIC_FORM']) && $_POST['PUBLIC_FORM'] == 'RESTORE'
            && (isset($_POST['USER_EMAIL']) && check_email($_POST['USER_EMAIL']) || isset($_POST['USER_LOGIN']) && $_POST['USER_LOGIN'] != '')
        )
        {
            ?>
            <font class="notetext">Проверьте указанную почту</font>
        <?}

        ?>
        <form name="bform" method="post" action="" class="restore_form">
            <input type="hidden" name="PUBLIC_FORM" value="RESTORE"/>

            <input type="hidden" name="AUTH_FORM" value="Y"/>
            <input type="hidden" name="TYPE" value="SEND_PWD"/>
            <div class="field"><?=GetMessage("AUTH_FORGOT_PASSWORD_1")?></div>

            <div class="field row phone_sms">
                <label class="field-title">E-Mail или телефон</label>
                <div class="form-input"><input type="text" name="USER_EMAIL" maxlength="255" /></div>
            </div>

            <div class="field field-button"><input type="submit" class="input-submit" name="send_account_info" value="<?=GetMessage("AUTH_SEND")?>" /></div>
        </form>
    </div>
</div>