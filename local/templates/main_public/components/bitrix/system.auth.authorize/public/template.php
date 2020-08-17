<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?>
<div class="content-form login-form public_form <?if(isset($_POST['PUBLIC_FORM']) && $_POST['PUBLIC_FORM'] == 'AUTH'){?>active<?}?>">

    <div class="close" onclick="<?if(isset($_REQUEST['backurl']) && trim($_REQUEST['backurl']) != ''){?>document.location.href='/';<?}else{?>closePublicForm(this);<?}?>"></div>
    <div class="page_sub_title">Авторизация</div>

    <div class="fields">

    <?if(isset($_POST['PUBLIC_FORM']) && $_POST['PUBLIC_FORM'] == 'AUTH'){?>
        <div class="err_text auth_form">Неверный E-mail/телефон или пароль</div>
    <?}?>

    <form name="form_auth" method="post" target="_top" action="">
        <input type="hidden" name="PUBLIC_FORM" value="AUTH"/>

        <input type="hidden" name="AUTH_FORM" value="Y" />
        <input type="hidden" name="TYPE" value="AUTH" />

        <?if(isset($_REQUEST['backurl']) && trim($_REQUEST['backurl']) != ''){?>
            <input type="hidden" name="backurl" value="<?=$_REQUEST['backurl'];?>" />
        <?}?>

        <div class="field">
            <label class="field-title">E-mail или телефон</label>
            <div class="form-input"><input type="text" name="USER_LOGIN_S" maxlength="50" value="<?=(isset($_POST['USER_LOGIN_S']) && trim($_POST['USER_LOGIN_S']) != '' ? $_POST['USER_LOGIN_S'] : '');?>" class="input-field" /></div>
        </div>
        <div class="field">
            <label class="field-title"><?=GetMessage("AUTH_PASSWORD")?></label>
            <div class="form-input"><input type="password" name="USER_PASSWORD" maxlength="50" class="input-field" />
    <?if($arResult["SECURE_AUTH"]):?>
                    <span class="bx-auth-secure" id="bx_auth_secure" title="<?echo GetMessage("AUTH_SECURE_NOTE")?>" style="display:none">
                        <div class="bx-auth-secure-icon"></div>
                    </span>
                    <noscript>
                    <span class="bx-auth-secure" title="<?echo GetMessage("AUTH_NONSECURE_NOTE")?>">
                        <div class="bx-auth-secure-icon bx-auth-secure-unlock"></div>
                    </span>
                    </noscript>
    <script type="text/javascript">
    document.getElementById('bx_auth_secure').style.display = 'inline-block';
    </script>
    <?endif?>
            </div>
        </div>
        <?
        if ($arResult["STORE_PASSWORD"] == "Y")
        {
        ?>
            <div class="field field-option radio_group">
                <div class="radio_area">
                    <input type="checkbox" data-text="<?=GetMessage("AUTH_REMEMBER_ME")?>" id="USER_REMEMBER" name="USER_REMEMBER" value="Y" />
                </div>
            </div>
        <?
        }
        ?>
        <div class="field field-button">
            <input type="submit" name="Login" value="<?=GetMessage("AUTH_AUTHORIZE")?>" />
        </div>
    <?
    if ($arParams["NOT_SHOW_LINKS"] != "Y")
    {
    ?><noindex>
    <div class="field">
    <a href="javascript:void(0);" data-action="forgot_password" rel="nofollow" onclick="showPublicOtherForm('.forgot-form', this);"><b><?=GetMessage("AUTH_FORGOT_PASSWORD_2")?></b></a><br />
    </div>
    </noindex><?
    }
    ?>
    </form>

    </div>

</div>