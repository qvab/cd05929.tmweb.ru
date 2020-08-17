<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?>

<div id="public_reg_form" class="public_form <?if(isset($_POST['PUBLIC_FORM']) && $_POST['PUBLIC_FORM'] == 'REGISTER'){?>active<?}?>">
    <div class="close" onclick="closePublicForm(this);"></div>
    <div class="page_sub_title">Регистрация 2</div>
    <div class="reg-cntn">
        <?
        if ($arResult['INVITE_ERROR'] != '') {
            ShowMessage($arResult['INVITE_ERROR']);
        }
        else {
            if (isset($arResult['SUCCESS_MESSAGE']) && trim($arResult['SUCCESS_MESSAGE']) != '') {
            ?>
                <div class="success"><?=$arResult['SUCCESS_MESSAGE']?></div>
            <?
            }
            else {
                //if (!isset($arResult['INVITE_RESTRICTED_FORM'])) {
                //standart register form
                if (isset($_GET['reg_hash']) || (isset($_GET['hash']) && $_GET['reg'] == 'mobile')) {
                ?>
                    <link rel="stylesheet" type="text/css" href="<?=$templateFolder;?>/spec_registration_style.css" />
                    <script type="text/javascript" src="<?=$templateFolder;?>/spec_registration_script.js"></script>
                <?
                }
                ?>
                    <div class="tab_form reg_form_control_tabs<? if (isset($_GET['reg_hash']) || (isset($_GET['hash']) && $_GET['reg'] == 'mobile')) { ?> no_access<? } ?>">
                        <div class="item form_control_tab<?if($arResult['SHOW_TYPE'] == 'farmer'){?> active<?}?>" data-val="farmer">
                            <span>Поставщик</span>
                            <div class="ico"></div>
                        </div>
                        <div class="item form_control_tab<?if($arResult['SHOW_TYPE'] == 'client'){?> active<?}?>" data-val="client">
                            <span>Покупатель</span>
                            <div class="ico"></div>
                        </div>
                        <div class="clear"></div>
                    </div>

                    <div class="reg_form active">
                        <form class="auth-form" action="" method="post" enctype="multipart/form-data">
                            <?
                            if ($arResult['SHOW_ERRORS'] == 'Y' && $arResult['ERROR']) {
                                ShowMessage($arResult['ERROR_MESSAGE']);
                            }

                            foreach ($arResult["POST"] as $key => $value) {
                            ?>
                                <input type="hidden" name="<?=$key?>" value="<?=$value?>" />
                            <?
                            }
                            ?>

                            <input type="hidden" name="PUBLIC_FORM" value="REGISTER"/>
                            <input type="hidden" name="REG_FORM" value="Y" />
                            <input type="hidden" name="TYPE" value="<?
                            switch($arResult['SHOW_TYPE']){
                                case 'client': case 'farmer': case 'agent': case 'client_agent':
                                    echo $arResult['SHOW_TYPE'];
                                break;
                            }
                            ?>" />

                            <div class="row">
                                <div class="holder row_sub_head">Данные пользователя:</div>
                            </div>

                            <?if($arResult['SHOW_TYPE'] != 'agent'
                            && $arResult['SHOW_TYPE'] != 'client_agent'
                            ){?>
                                <div class="row">
                                    <div class="needItem">*</div>
                                    <div class="holder row_val">
                                        <input type="text" name="USER_LAST_NAME" value="<?=$arResult['USER_LAST_NAME']?>" placeholder="<?=GetMessage("USER_LAST_NAME")?>" />
                                    </div>
                                </div>
                            <?}?>

                            <div class="row">
                                <div class="needItem">*</div>
                                <div class="holder row_val">
                                    <input type="text" name="USER_NAME" value="<?=$arResult['USER_NAME']?>" placeholder="<?=GetMessage("USER_NAME")?>" />
                                </div>
                            </div>

                            <?if($arResult['SHOW_TYPE'] != 'agent'
                            && $arResult['SHOW_TYPE'] != 'client_agent'
                            ){?>
                                <div class="row">
                                    <div class="holder row_val">
                                        <input type="text" name="USER_SECOND_NAME" value="<?=$arResult['USER_SECOND_NAME']?>" placeholder="<?=GetMessage("USER_SECOND_NAME")?>" />
                                    </div>
                                </div>
                            <?}?>

                            <?
                            if (isset($_GET['reg_hash']) || (isset($_GET['hash']) && $_GET['reg'] == 'mobile')) {
                            ?>
                                <input type="hidden" name="USER_EMAIL" value="<?=$arResult['USER_EMAIL']?>">
                                <div class="row no_show_need">
                                    <div class="holder row_val">
                                        <input type="text" data-checkval="y" data-checktype="email" name="" value="<?=$arResult['USER_EMAIL']?>" disabled="disabled" placeholder="<?=GetMessage("AUTH_EMAIL")?>">
                                    </div>
                                </div>
                            <?
                            }
                            else {
                            ?>
                                <div class="row no_show_need">
                                    <div class="holder row_val">
                                        <input type="text" data-checkval="y" data-checktype="email" name="USER_EMAIL" value="<?=$arResult['USER_EMAIL']?>" placeholder="<?=GetMessage("AUTH_EMAIL")?>" />
                                    </div>
                                </div>
                            <?
                            }
                            ?>

                            <?
                            $check_sms = false;
                            if(isset($_POST['success_sms'])
                                && $_POST['success_sms'] == 'y'
                                && isset($_POST['PROP__PHONE'])
                                && isset($_SESSION['success_sms_' . str_replace(array('+', '-', '(',')', ' '), '', $_POST['PROP__PHONE'])])
                            ){
                                $check_sms = true;
                                ?>
                                <input name="success_sms" type="hidden" value="y" />
                            <?}?>

                            <div class="row phone_sms<?=(isset($_POST['PROP__PHONE']) && isset($_SESSION['success_sms_' . getPhoneDigits($_POST['PROP__PHONE'])]) ? ' check_success' : '');?>">
                                <div class="needItem">*</div>
                                <div class="holder row_val">
                                    <input type="text" class="phone_msk<?if($check_sms){?> check_success" readonly="readonly<?}?>" name="PROP__PHONE" data-checktype="phone" value="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['client_profile']['PHONE']['VALUE']?>" placeholder="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['client_profile']['PHONE']['NAME']?>" />
                                    <div class="success_ico"></div>
                                    <div class="clear"></div>
                                </div>
                            </div>

                            <?if($arResult['SHOW_TYPE'] != 'agent'
                                && $arResult['SHOW_TYPE'] != 'client_agent'
                            ){
//                                p($arResult['PROPERTIES_IBLOCK_ADDITIONAL']['client_profile']);
                                /*?>
                                <div class="row">
                                    <div class="holder row_sub_head">Данные компании:</div>
                                </div>
                                <?*/?>

                                <?if(isset($arResult['REGIONS'])
                                    && is_array($arResult['REGIONS'])
                                    && count($arResult['REGIONS']) > 0
                                ){?>
                                    <div class="row">
                                        <div class="needItem">*</div>
                                        <div class="holder row_val">
                                            <select name="PROP__REGION" data-search="y">
                                                <option value="">Регион</option>
                                                <?foreach($arResult['REGIONS'] as $cur_id => $cur_name) {
                                                    ?>
                                                    <option value="<?=$cur_id;?>" <?=(isset($_POST['PROP__REGION']) && $_POST['PROP__REGION'] == $cur_id ? 'selected="selected"' : '')?>><?=$cur_name;?></option>
                                                    <?
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                <?}?>

                                <div class="row inn_row<?=(isset($_POST['PROP__INN']) && isset($_SESSION['success_inn_' . $_POST['PROP__INN']]) ? ' check_success' : '');?>">
                                    <div class="needItem">*</div>
                                    <div class="holder row_val">
                                        <input type="text" class="<?if(isset($_POST['PROP__INN']) && isset($_SESSION['success_inn_' . $_POST['PROP__INN']]) ? ' check_success' : ''){?> check_success" readonly="readonly<?}?>" name="PROP__INN" data-checktype="pos_int_empty" data-checkval="y" value="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['client_profile']['INN']['VALUE']?>" placeholder="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['client_profile']['INN']['NAME']?>" />
                                        <?if(isset($_POST['PROP__INN']) && isset($_SESSION['success_inn_' . $_POST['PROP__INN']])){
                                            //при успешной проверке ИНН проверяем установленные значения связанных свойств
                                            $sPropFullCompanyName = htmlspecialcharsbx($_POST['PROP__FULL_COMPANY_NAME']);
                                            if(isset($sPropFullCompanyName) && $sPropFullCompanyName != ''){
                                                ?><input type="hidden" name="PROP__FULL_COMPANY_NAME" value="<?=str_replace('"', '&quot;', $sPropFullCompanyName);?>" /><?
                                            }

                                            $sPropYurAdress = htmlspecialcharsbx($_POST['PROP__YUR_ADRESS']);
                                            if(isset($sPropYurAdress) && $sPropYurAdress != ''){
                                                ?><input type="hidden" name="PROP__YUR_ADRESS" value="<?=$sPropYurAdress;?>" /><?
                                            }

                                            $sPropRegDate = htmlspecialcharsbx($_POST['PROP__REG_DATE']);
                                            if(isset($sPropRegDate) && $sPropRegDate != ''){
                                                ?><input type="hidden" name="PROP__REG_DATE" value="<?=$sPropRegDate;?>" /><?
                                            }

                                            $sPropIpFio = htmlspecialcharsbx($_POST['PROP__IP_FIO']);
                                            if(isset($sPropIpFio) && $sPropIpFio != ''){
                                                ?><input type="hidden" name="PROP__IP_FIO" value="<?=$sPropIpFio;?>" /><?
                                            }

                                            $sPropOgrn = htmlspecialcharsbx($_POST['PROP__OGRN']);
                                            if(isset($sPropOgrn) && $sPropOgrn != ''){
                                                ?><input type="hidden" name="PROP__OGRN" value="<?=$sPropOgrn;?>" /><?
                                            }

                                            $sPropOkpo = htmlspecialcharsbx($_POST['PROP__OKPO']);
                                            if(isset($sPropOkpo) && $sPropOkpo != ''){
                                                ?><input type="hidden" name="PROP__OKPO" value="<?=$sPropOkpo;?>" /><?
                                            }

                                            $sPropKpp = htmlspecialcharsbx($_POST['PROP__KPP']);
                                            if(isset($sPropKpp) && $sPropKpp != ''){
                                                ?><input type="hidden" name="PROP__KPP" value="<?=$sPropKpp;?>" /><?
                                            }

                                            $sPropFioDir = htmlspecialcharsbx($_POST['PROP__FIO_DIR']);
                                            if(isset($sPropFioDir) && $sPropFioDir != ''){
                                                ?><input type="hidden" name="PROP__FIO_DIR" value="<?=$sPropFioDir;?>" /><?
                                            }

                                            $sPropUlType = htmlspecialcharsbx($_POST['PROP__UL_TYPE']);
                                            if(isset($sPropUlType) && $sPropUlType != ''){
                                                ?><input type="hidden" name="PROP__UL_TYPE" value="<?=$sPropUlType;?>" /><?
                                            }
                                        }?>

                                        <div class="success_ico"></div>
                                        <div class="clear"></div>
                                            <input class="empty_but" data-val="Запросить данные по ИНН" type="button" onclick="uploadInnReg(this);" value="Проверка ИНН" title="Данные об организации заполняются автоматически по ИНН">
                                        <div class="clear"></div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="needItem">*</div>
                                    <div class="holder row_val">
                                        <select name="PROP__NDS">
                                            <option value=""><?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['client_profile']['NDS']['NAME']?></option>
                                            <?
                                            if (isset($arResult['PROPERTIES_LISTS_ADDITIONAL']['NDS'])) {
                                                foreach($arResult['PROPERTIES_LISTS_ADDITIONAL']['NDS'] as $cur_id => $cur_name) {
                                                ?>
                                                    <option value="<?=$cur_id;?>" <?=($arResult['PROPERTIES_IBLOCK_ADDITIONAL']['client_profile']['NDS']['VALUE'] == $cur_id ? 'selected="selected"' : '')?>><?=$cur_name;?></option>
                                                <?
                                                }
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                            <?}?>

                            <div class="row">
                                <div class="needItem">*</div>
                                <div class="holder row_val">
                                    <input type="password" name="USER_PASS" value="" placeholder="<?=GetMessage("AUTH_PASSWORD")?>" />
                                </div>
                            </div>

                            <div class="row">
                                <div class="needItem">*</div>
                                <div class="holder row_val">
                                    <input type="password" name="USER_PASS_CONFIRM" value="" placeholder="<?=GetMessage("AUTH_PASS_CONFIRM")?>" />
                                </div>
                            </div>

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

                            <div class="g-recaptcha g-relazy" id="recaptcha_c" data-callback="onRecSubmit" data-size="invisible"></div>

                            <div class="row">
                                <span class="rrs_btn_auth">
                                    <input class="submit-btn" type="submit" name="Login" value="<?=GetMessage("AUTH_REG_BUTTON")?>" data-role="none">
                                </span>
                            </div>

                        </form>
                    </div>
                <?
            }
        }
        ?>
    </div>
</div>
