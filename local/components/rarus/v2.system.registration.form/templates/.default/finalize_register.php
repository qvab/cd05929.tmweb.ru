<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$APPLICATION->SetTitle('Регистрация. Завершающий шаг.');
?>
<div id="public_reg_form" class="public_form finilize_reg <?if(isset($_POST['PUBLIC_FORM']) && $_POST['PUBLIC_FORM'] == 'REGISTER'){?>active<?}?>">
    <div class="close" onclick="document.location.href='/';"></div>
    <div class="page_sub_title">Регистрация. Завершающий шаг.</div>
    <div class="reg-cntn">
        <?
        if(isset($arResult['SUCCESS_MESSAGE']) && trim($arResult['SUCCESS_MESSAGE']) != '') {
            ?>
            <div class="success"><?=$arResult['SUCCESS_MESSAGE']?></div>
        <?
        }
        else {
            ?>

            <div class="reg_form active" data-val="s1">
                <?
                if ($arResult['SHOW_ERRORS'] == 'Y' && $arResult['ERROR']) {
                    ShowMessage($arResult['ERROR_MESSAGE']);
                }

                if(isset($arResult['FINALIZE_REGISTER']) && $arResult['FINALIZE_REGISTER'] == 'Y')
                {
                    ?>
                    <form class="finalize_reg_form" action="" method="post">

                        <input type="hidden" name="reg" value="<?=htmlspecialcharsbx($_REQUEST['reg']);?>" />
                        <input type="hidden" name="hash" value="<?=htmlspecialcharsbx($_REQUEST['hash']);?>" />

                        <div class="row">
                            <div class="needItem">*</div>
                            <div class="holder row_val">
                                <input type="password" name="pass" value="<?=(isset($_POST['pass']) ? $_POST['pass'] : '');?>" placeholder="Пароль (не менее 6-ти символов)" />
                            </div>
                        </div>

                        <div class="row">
                            <div class="needItem">*</div>
                            <div class="holder row_val">
                                <input type="password" name="confirm_pass" value="<?=(isset($_POST['confirm_pass']) ? $_POST['confirm_pass'] : '');?>" placeholder="Подтвердить пароль" />
                            </div>
                        </div>

                        <div class="row">
                            <div class="row_val">
                                <input class="submit-btn inactive" type="submit" value="<?=GetMessage("AUTH_REG_BUTTON_CONFIRM")?>" data-role="none" />
                            </div>
                        </div>

                    </form>
                <?}?>
            </div>
        <?
        }
        ?>
    </div>
</div>