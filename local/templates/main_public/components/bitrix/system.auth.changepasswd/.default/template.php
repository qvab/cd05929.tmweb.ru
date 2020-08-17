<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

//определяем тип восстановления (по телефону, по email, либо пользователь перешел по ссылке от агента)
if(!isset($arResult['WORK_MODE'])){
    $arResult['WORK_MODE'] = 'change';
}

$email_val = $arResult["EMAIL"];
if($email_val == ''
    && isset($_POST['USER_EMAIL'])
    && $_POST['USER_EMAIL'] != ''
){
    $email_val = $_POST['USER_EMAIL'];
}

$phone_added = '';
if(isset($_POST['USER_PHONE']) && $_POST['USER_PHONE'] != ''){
    $phone_added = $_POST['USER_PHONE'];
}

?>
<div class="content-form changepswd-form public_form <?if(defined('NEED_AUTH')){?>active<?}?>">
    <div class="close" onclick="document.location.href='/';"></div>
    <?if($arResult['WORK_MODE'] == 'by_agent'){?>
        <div class="page_sub_title">Авторизация пользователя</div>
        <?global $APPLICATION; $APPLICATION->SetTitle('Авторизация пользователя');?>
    <?}else{?>
        <div class="page_sub_title">Смена пароля</div>
    <?}?>
    <div class="fields">
        <?
        if($arResult['WORK_MODE'] != 'phone') {
            if(isset($arParams["~AUTH_RESULT"]['MESSAGE']) && $arParams["~AUTH_RESULT"]['MESSAGE'] != ''){
                if($arResult['WORK_MODE'] == 'by_agent'){
                    $err_text = trim(strip_tags($arParams["~AUTH_RESULT"]['MESSAGE']));
                    if($err_text != 'Неверное подтверждение пароля.'
                        && $err_text != 'Указанный email уже зарегистрирован в системе'
                    ) {
                        ShowMessage('Ссылка устарела');
                    }else{
                        ShowMessage($arParams["~AUTH_RESULT"]);
                    }
                }else{
                    ShowMessage($arParams["~AUTH_RESULT"]);
                }
            }
        }

        if(isset($arParams['AUTH_RESULT']['TYPE']) && $arParams['AUTH_RESULT']['TYPE'] == 'OK')
        {
            if($arResult['WORK_MODE'] == 'phone'){
                echo '<font class="notetext">Пароль успешно изменён. <a href="' . $GLOBALS['host'] . '/#action=auth">Войдите используя новые данные</a><br/><br/></font>';
            }else{
                echo '<font class="notetext"><br/>Для входа на сайта к перейдите к <a href="/#action=auth">форме авторизации</a>.<br/><br/></font>';
            }
        }
        ?>
        <form method="post" action="" name="bform">
            <input type="hidden" name="PUBLIC_FORM" value="CHANGE"/>

            <input type="hidden" name="AUTH_FORM" value="Y"/>
            <input type="hidden" name="TYPE" value="CHANGE_PWD"/>
            <?if($arResult['WORK_MODE'] == 'by_agent'){?>
                <input type="hidden" name="USER_LOGIN" maxlength="50" value="<?=$arResult["LAST_LOGIN"]?>" />
                <input type="hidden" name="USER_CHECKWORD" maxlength="50" value="<?=$arResult["USER_CHECKWORD"]?>" />

                <?if(isset($_GET['counter_offer'])
                    && is_numeric($_GET['counter_offer'])
                ){?>
                    <input type="hidden" name="COUNTER_OFFER_ID" value="<?=$_GET['counter_offer'];?>" />
                <?}?>

                <div class="row">
                    <div class="row_sub_head">Email</div>
                    <div class="row_val">
                        <input type="text" data-checkval="y" data-checktype="email" name="USER_EMAIL"  maxlength="50" value="<?=$email_val?>" />
                    </div>
                </div>
                <div class="row phone_sms<?=($phone_added != '' ? ' check_success' : '');?>">
                    <div class="row_sub_head">Телефон <span class="starrequired">*</span></div>
                    <div class="row_val">
                        <?if($phone_added != ''){?>
                            <input type="text" readonly="readonly" class="phone_msk" maxlength="50" value="<?=$phone_added;?>" />
                            <input type="hidden" name="USER_PHONE" value="<?=$phone_added;?>" />
                        <?}else{?>
                            <input type="text" name="USER_PHONE" class="phone_msk" maxlength="50" value="<?=$arResult["PHONE"];?>" />
                        <?}?>
                        <div class="success_ico"></div>
                        <div class="clear"></div>
                    </div>
                </div>
            <?}elseif($arResult['WORK_MODE'] == 'phone'){?>
                <input type="hidden" name="USER_LOGIN" maxlength="50" value="<?=$arResult["LAST_LOGIN"]?>" />
                <input type="hidden" name="USER_CHECKWORD" maxlength="50" value="<?=$arResult["USER_CHECKWORD"]?>" />
            <?}else{?>
                <input type="hidden" name="USER_LOGIN" maxlength="50" value="<?=$arResult["LAST_LOGIN"]?>" />
                <input type="hidden" name="USER_CHECKWORD" maxlength="50" value="<?=$arResult["USER_CHECKWORD"]?>" />
                <?/*
                <div class="row">
                    <div class="row_sub_head"><?=(isset($_GET['invite_by_agent']) && $_GET['invite_by_agent'] == 'y' ? GetMessage("AUTH_SPEC_LOGIN") : GetMessage("AUTH_LOGIN"));?><span class="starrequired">*</span></div>
                    <div class="row_val"><input type="text" name="USER_LOGIN" maxlength="50" value="<?=$arResult["LAST_LOGIN"]?>" /></div>
                </div>
                <div class="row">
                    <div class="row_sub_head"><?=GetMessage("AUTH_CHECKWORD")?><span class="starrequired">*</span></div>
                    <div class="row_val"><input type="text" name="USER_CHECKWORD" maxlength="50" value="<?=$arResult["USER_CHECKWORD"]?>" /></div>
                </div>
                */?>
            <?}?>
            <div class="row">
                <div class="row_sub_head">Задайте пароль <span class="starrequired">*</span></div>
                <div class="row_val"><input type="password" name="USER_PASSWORD" maxlength="50" value="<?=$arResult["USER_PASSWORD"]?>" /></div>
                <div class="description">&mdash; <?echo $arResult["GROUP_POLICY"]["PASSWORD_REQUIREMENTS"];?></div>
            </div>
            <div class="row">
                <div class="row_sub_head">Продублируйте пароль <span class="starrequired">*</span></div>
                <div class="row_val"><input type="password" name="USER_CONFIRM_PASSWORD" maxlength="50" value="<?=$arResult["USER_CONFIRM_PASSWORD"]?>"  /></div>
            </div>
            <?if($arResult['WORK_MODE'] == 'by_agent'){?>
                <div class="row policy_row">
                    <div class="field field-option radio_group">
                        <div class="radio_area">
                            <input type="checkbox" data-text="<?=GetMessage("AUTH_REGISTER_CONFIM");?>" name="AUTH_REG_CONFIM" value="Y" />
                        </div>
                    </div>
                </div>
                <div class="row reglament_row">
                    <div class="field field-option radio_group">
                        <div class="radio_area">
                            <input type="checkbox" data-text="<?=str_replace('"', '', GetMessage("AUTH_REGLAMENT_CONFIM"));?>" name="AUTH_REGLAMENT_CONFIM" value="Y" />
                        </div>
                    </div>
                </div>
            <?}?>
            <div class="row"><input type="submit" class="input-submit" name="change_pwd" value="<?=($arResult['WORK_MODE'] == 'by_agent' ? 'Авторизоваться' : GetMessage("AUTH_CHANGE"));?>" /></div>
        </form>
    </div>
</div>