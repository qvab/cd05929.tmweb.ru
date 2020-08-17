<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<div id="public_reg_form" class="public_form <?if(isset($_POST['PUBLIC_FORM']) && $_POST['PUBLIC_FORM'] == 'REGISTER'){?>active<?}?>">
    <div class="close" onclick="closePublicForm(this);"></div>
    <div class="page_sub_title">Регистрация</div>
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
                        <div class="item form_control_tab<?if($arResult['SHOW_TYPE'] == 'farmer'){?> active<?}?>" data-val="s3" type="farmer">
                            <span>Поставщик</span>
                            <div class="ico"></div>
                        </div>
                        <div class="item form_control_tab<?if($arResult['SHOW_TYPE'] == 'client'){?> active<?}?>" data-val="s2" type="client">
                            <span>Покупатель</span>
                            <div class="ico"></div>
                        </div>
                        <?/*<div class="item form_control_tab<?if($arResult['SHOW_TYPE'] == 'partner'){?> active<?}?>" data-val="s1" type="organizer">
                            <span>Организатор</span>
                            <div class="ico"></div>
                        </div>*/?>
                        <?
                        if ($arResult['SHOW_TYPE'] == 'transport') {
                        ?>
                            <div class="item form_control_tab active" data-val="s4" type="transport">
                                <span>Транспортная компания</span>
                                <div class="ico"></div>
                            </div>
                        <?
                        }
                        elseif ($arResult['SHOW_TYPE'] == 'agent') {
                        ?>
                            <div class="item form_control_tab active" data-val="s5" type="agent">
                                <span>Агент поставщика</span>
                                <div class="ico"></div>
                            </div>
                        <?
                        }
                        elseif ($arResult['SHOW_TYPE'] == 'client_agent') {
                        ?>
                            <div class="item form_control_tab active" data-val="s5" type="client_agent">
                                <span>Агент покупателя</span>
                                <div class="ico"></div>
                            </div>
                        <?
                        }
                        ?>
                        <div class="clear"></div>
                    </div>

                    <div class="reg_form<?if($arResult['SHOW_TYPE'] == 'partner'){?> active<?}?>" data-val="s1">
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
                            <input type="hidden" name="TYPE" value="partner" />

                            <div class="row">
                                <div class="holder row_sub_head">Данные пользователя:</div>
                            </div>

                            <div class="row">
                                <div class="needItem">*</div>
                                <div class="holder row_val">
                                    <input type="text" name="USER_NAME" value="<?=$arResult['USER_NAME']?>" placeholder="<?=GetMessage("USER_NAME")?>" />
                                </div>
                            </div>

                            <div class="row">
                                <div class="needItem">*</div>
                                <div class="holder row_val">
                                    <input type="text" name="USER_LAST_NAME" value="<?=$arResult['USER_LAST_NAME']?>" placeholder="<?=GetMessage("USER_LAST_NAME")?>">
                                </div>
                            </div>

                            <?
                            if (isset($_GET['reg_hash']) || (isset($_GET['hash']) && $_GET['reg'] == 'mobile')) {
                            ?>
                                <input type="hidden" name="USER_EMAIL" value="<?=$arResult['USER_EMAIL']?>">
                                <div class="row">
                                    <div class="needItem">*</div>
                                    <div class="holder row_val">
                                        <input type="text" data-checkval="y" data-checktype="email" name="" value="<?=$arResult['USER_EMAIL']?>" disabled="disabled" placeholder="<?=GetMessage("AUTH_EMAIL")?>">
                                    </div>
                                </div>
                            <?
                            }
                            else {
                            ?>
                                <div class="row">
                                    <div class="needItem">*</div>
                                    <div class="holder row_val">
                                        <input type="text" name="USER_EMAIL" data-checkval="y" data-checktype="email" value="<?=$arResult['USER_EMAIL']?>" placeholder="<?=GetMessage("AUTH_EMAIL")?>">
                                    </div>
                                </div>
                            <?
                            }
                            ?>

                            <div class="row phone_sms">
                                <div class="needItem">*</div>
                                <div class="holder row_val">
                                    <input type="text" class="phone_msk" name="PROP__PHONE" data-checktype="phone" value="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['partner_profile']['PHONE']['VALUE']?>" placeholder="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['partner_profile']['PHONE']['NAME']?>" />
                                    <div class="send_button" data-again="Отправить повторно" data-first="Отправить смс">Отправить смс</div>
                                    <div class="success_ico"></div>
                                    <div class="clear"></div>
                                </div>

                                <div class="sms_confirmation">
                                    <div class="needItem">*</div>
                                    <div class="row_val">
                                        <input type="text" name="sms_code" placeholder="Введите код" />
                                        <div class="submit_sms_button">Подтвердить</div>
                                        <div class="clear"></div>
                                    </div>
                                </div>

                            </div>

                            <?
                            if (isset($_GET['reg_hash']) || (isset($_GET['hash']) && $_GET['reg'] == 'mobile')) {
                            ?>
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
                            <?
                            }
                            ?>

                            <div class="row">
                                <div class="holder row_sub_head"><?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['partner_profile']['REGION']['NAME']?>:</div>
                                <div class="needItem">*</div>
                                <div class="holder row_val">
                                    <select data-search="y" name="PROP__REGION">
                                        <option value=""></option>
                                        <?
                                        if (isset($arResult['PROPERTIES_LISTS_ADDITIONAL']['REGION'])) {
                                            foreach($arResult['PROPERTIES_LISTS_ADDITIONAL']['REGION'] as $cur_id => $cur_name) {
                                            ?>
                                                <option value="<?=$cur_id;?>" <?=($arResult['PROPERTIES_IBLOCK_ADDITIONAL']['partner_profile']['REGION']['VALUE'] == $cur_id ? 'selected="selected"' : '')?>><?=$cur_name;?></option>
                                            <?
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="holder row_sub_head">Данные компании:</div>
                            </div>

                            <input type="hidden" name="PROP__UL_TYPE" value="">

                            <div class="row">
                                <div class="needItem">*</div>
                                <div class="holder row_val">
                                    <input type="text" name="PROP__INN" value="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['partner_profile']['INN']['VALUE']?>" placeholder="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['partner_profile']['INN']['NAME']?>" />
                                    <input class="empty_but" data-val="Запросить данные по ИНН" type="button" onclick="uploadInn(this);" value="Запросить данные по ИНН" title="Данные об организации заполняются автоматически по ИНН" />
                                </div>
                            </div>

                            <div class="row">
                                <div class="holder row_val">
                                    <input type="hidden" name="PROP__FULL_COMPANY_NAME" value="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['partner_profile']['FULL_COMPANY_NAME']['VALUE']?>" />
                                    <textarea title="Заполняется автоматически после валидации ИНН" name="" class="disabled" readonly="readonly" placeholder="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['partner_profile']['FULL_COMPANY_NAME']['NAME']?>"><?
                                        echo ($arResult['PROPERTIES_IBLOCK_ADDITIONAL']['partner_profile']['FULL_COMPANY_NAME']['VALUE'] != '' ? $arResult['PROPERTIES_IBLOCK_ADDITIONAL']['partner_profile']['FULL_COMPANY_NAME']['NAME'] . ': ' . $arResult['PROPERTIES_IBLOCK_ADDITIONAL']['partner_profile']['FULL_COMPANY_NAME']['VALUE'] : '');
                                    ?></textarea>
                                </div>
                            </div>

                            <div class="row" style="display: none;">
                                <div class="holder row_val">
                                    <input type="hidden" name="PROP__IP_FIO" value="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['partner_profile']['IP_FIO']['VALUE']?>" />
                                    <textarea title="Заполняется автоматически после валидации ИНН" name="" class="disabled" readonly="readonly" placeholder="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['partner_profile']['IP_FIO']['NAME']?>"><?
                                        echo ($arResult['PROPERTIES_IBLOCK_ADDITIONAL']['partner_profile']['IP_FIO']['VALUE'] != '' ? $arResult['PROPERTIES_IBLOCK_ADDITIONAL']['partner_profile']['IP_FIO']['NAME'] . ': ' . $arResult['PROPERTIES_IBLOCK_ADDITIONAL']['partner_profile']['IP_FIO']['VALUE'] : '');
                                        ?></textarea>
                                </div>
                            </div>

                            <input type="hidden" name="PROP__REG_DATE" value="">

                            <div class="row">
                                <div class="holder row_val">
                                    <input type="hidden" name="PROP__YUR_ADRESS" value="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['partner_profile']['YUR_ADRESS']['VALUE']?>" />
                                    <textarea title="Заполняется автоматически после валидации ИНН" name="" class="disabled" readonly="readonly" placeholder="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['partner_profile']['YUR_ADRESS']['NAME']?>"><?
                                        echo ($arResult['PROPERTIES_IBLOCK_ADDITIONAL']['partner_profile']['YUR_ADRESS']['VALUE'] != '' ? $arResult['PROPERTIES_IBLOCK_ADDITIONAL']['partner_profile']['YUR_ADRESS']['NAME'] . ': ' . $arResult['PROPERTIES_IBLOCK_ADDITIONAL']['partner_profile']['YUR_ADRESS']['VALUE'] : '');
                                    ?></textarea>
                                </div>
                            </div>

                            <div class="row">
                                <div class="needItem">*</div>
                                <div class="holder row_val">
                                    <input type="text" name="PROP__POST_ADRESS" value="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['partner_profile']['POST_ADRESS']['VALUE']?>" placeholder="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['partner_profile']['POST_ADRESS']['NAME']?>" />
                                </div>
                            </div>

                            <div class="row">
                                <div class="radio_group">
                                    <div class="radio_area">
                                        <input type="checkbox" data-text="соответствует юридическому адресу" name="" id="is_yur_adres_p" class="is_yur_adres" value="">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="holder row_val">
                                    <input type="hidden" name="PROP__KPP" value="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['partner_profile']['KPP']['VALUE']?>" />
                                    <input type="text" title="Заполняется автоматически после валидации ИНН" name="" class="disabled" readonly="readonly" value="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['partner_profile']['KPP']['VALUE']?>" placeholder="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['partner_profile']['KPP']['NAME']?>" />
                                </div>
                            </div>

                            <div class="row">
                                <div class="holder row_val">
                                    <input type="hidden" name="PROP__OGRN" value="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['partner_profile']['OGRN']['VALUE']?>" />
                                    <input type="text" title="Заполняется автоматически после валидации ИНН" name="" class="disabled" readonly="readonly" value="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['partner_profile']['OGRN']['VALUE']?>" placeholder="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['partner_profile']['OGRN']['NAME']?>" />
                                </div>
                            </div>

                            <div class="row">
                                <div class="holder row_val">
                                    <input type="hidden" name="PROP__OKPO" value="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['partner_profile']['OKPO']['VALUE']?>" />
                                    <input type="text" title="Заполняется автоматически после валидации ИНН" name="" class="disabled" readonly="readonly" value="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['partner_profile']['OKPO']['VALUE']?>" placeholder="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['partner_profile']['OKPO']['NAME']?>" />
                                </div>
                            </div>

                            <div class="row">
                                <div class="holder row_val">
                                    <input type="text" name="PROP__FIO_DIR" value="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['partner_profile']['FIO_DIR']['VALUE']?>" placeholder="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['partner_profile']['FIO_DIR']['NAME']?>" />
                                </div>
                            </div>

                            <div class="signer_ul">
                                <?
                                if (sizeof($arResult['SIGNERS']['ul']) > 0) {
                                ?>
                                    <div class="row">
                                        <div class="holder row_sub_head">Подписант:</div>
                                    </div>

                                    <div class="row_val">
                                        <div class="radio_group">
                                            <?
                                            $k = 0;
                                            foreach ($arResult['SIGNERS']['ul'] as $signer) {
                                            ?>
                                                <div class="radio_area">
                                                    <input type="radio" class="ch_signer" name="signer[ul]" data-text="<?=$signer['NAME']?>" data-code="ul_<?=$signer['CODE']?>" id="s1<?=$signer['ID']?>" value="<?=$signer['ID']?>" <? if ($k == 0) { ?>checked="checked"<? } ?>>
                                                </div>
                                                <?
                                                $k++;
                                            }
                                            ?>
                                        </div>
                                    </div>

                                    <div class="sign_block signer_ul_dir">
                                        <input type="hidden" name="post[ul][dir]" value="директор">
                                        <input type="hidden" name="num[ul][dir]" value="">
                                        <input type="hidden" name="date[ul][dir]" value="">
                                        <div class="row">
                                            <div class="holder row_sub_head">Действует на основании (название документа):</div>
                                            <div class="needItem">*</div>
                                            <div class="holder row_val">
                                                <select name="found[ul][dir]">
                                                    <?
                                                    foreach ($arResult['FOUND'] as $item) {
                                                        if ($item['PROPERTY_SHOW_ENUM_ID'] > 0) {
                                                        ?>
                                                            <option value="<?=$item['ID']?>"><?=$item['NAME']?></option>
                                                        <?
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="sign_block no_active signer_ul_gendir">
                                        <input type="hidden" name="post[ul][gendir]" value="генеральный директор">
                                        <input type="hidden" name="num[ul][gendir]" value="">
                                        <input type="hidden" name="date[ul][gendir]" value="">
                                        <div class="row">
                                            <div class="holder row_sub_head">Действует на основании (название документа):</div>
                                            <div class="needItem">*</div>
                                            <div class="holder row_val">
                                                <select name="found[ul][gendir]">
                                                    <?
                                                    foreach ($arResult['FOUND'] as $item) {
                                                        if ($item['PROPERTY_SHOW_ENUM_ID'] > 0) {
                                                        ?>
                                                            <option value="<?=$item['ID']?>"><?=$item['NAME']?></option>
                                                        <?
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="sign_block no_active signer_ul_sign">
                                        <div class="row">
                                            <div class="needItem">*</div>
                                            <div class="holder row_val">
                                                <input type="text" name="fio[ul][sign]" value="" placeholder="ФИО">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="holder row_val">
                                                <input type="text" name="post[ul][sign]" value="" placeholder="Должность">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <input type="hidden" name="found[ul][sign]" value="<?=$arResult['FOUND']['doverennost']['ID']?>">
                                            <div class="holder row_sub_head">Действует на основании <?=$arResult['FOUND']['doverennost']['PROPERTY_CHEGO_VALUE']?></div>
                                        </div>
                                        <div class="row">
                                            <div class="needItem">*</div>
                                            <div class="holder row_val">
                                                №<input type="text" class="signer_found_num" name="num[ul][sign]" value="" placeholder="" />
                                                от<input type="text" class="signer_found_date" autocomplete="off" name="date[ul][sign]" onclick="var tmp_val = this.value; this.value = ''; BX.calendar({value: tmp_val, node: this, field: this, bTime: false, callback_after: goTriggerChange(this)}); changeCalendar();" placeholder="" />
                                            </div>
                                        </div>
                                    </div>
                                <?
                                }
                                ?>
                            </div>

                            <div class="signer_ip">
                                <?
                                if (sizeof($arResult['SIGNERS']['ip']) > 0) {
                                ?>
                                    <div class="row">
                                        <div class="holder row_sub_head">Подписант:</div>
                                    </div>

                                    <div class="row_val">
                                        <div class="radio_group">
                                            <?
                                            $k = 0;
                                            foreach ($arResult['SIGNERS']['ip'] as $signer) {
                                            ?>
                                                <div class="radio_area">
                                                    <input type="radio" class="ch_signer" name="signer[ip]" data-text="<?=$signer['NAME']?>" data-code="ip_<?=$signer['CODE']?>" id="s2<?=$signer['ID']?>" value="<?=$signer['ID']?>" <? if ($k == 0) { ?>checked="checked"<? } ?>>
                                                </div>
                                                <?
                                                $k++;
                                            }
                                            ?>
                                        </div>
                                    </div>

                                    <div class="sign_block signer_ip_ip">
                                        <input type="hidden" name="post[ip][ip]" value="">
                                        <input type="hidden" name="num[ip][ip]" value="">
                                        <input type="hidden" name="date[ip][ip]" value="">
                                        <div class="row">
                                            <input type="hidden" name="found[ip][ip]" value="<?=$arResult['FOUND']['svidetelstvo']['ID']?>">
                                            <div class="holder row_sub_head">Действует на основании <?=$arResult['FOUND']['svidetelstvo']['PROPERTY_CHEGO_VALUE']?></div>
                                        </div>
                                    </div>

                                    <div class="sign_block no_active signer_ip_sign">
                                        <div class="row">
                                            <div class="needItem">*</div>
                                            <div class="holder row_val">
                                                <input type="text" name="fio[ip][sign]" value="" placeholder="ФИО">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="holder row_val">
                                                <input type="text" name="post[ip][sign]" value="" placeholder="Должность">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <input type="hidden" name="found[ip][sign]" value="<?=$arResult['FOUND']['doverennost']['ID']?>">
                                            <div class="holder row_sub_head">Действует на основании <?=$arResult['FOUND']['doverennost']['PROPERTY_CHEGO_VALUE']?></div>
                                        </div>
                                        <div class="row">
                                            <div class="needItem">*</div>
                                            <div class="holder row_val">
                                                №<input type="text" class="signer_found_num" name="num[ip][sign]" value="" placeholder="" />
                                                от<input type="text" class="signer_found_date" autocomplete="off" name="date[ip][sign]" onclick="var tmp_val = this.value; this.value = ''; BX.calendar({value: tmp_val, node: this, field: this, bTime: false, callback_after: goTriggerChange(this)}); changeCalendar();" placeholder="" />
                                            </div>
                                        </div>
                                    </div>
                                <?
                                }
                                ?>
                            </div>

                            <div class="row">
                                <div class="holder row_sub_head">
                                    <?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['partner_profile']['OSNOVANIE_PRAVA_PODPISI_FILE']['NAME']?>:
                                </div>
                                <div class="holder row_val">
                                    <input type="file" name="PROP__OSNOVANIE_PRAVA_PODPISI_FILE" class="needFile" />
                                </div>
                            </div>

                            <div class="row">
                                <div class="holder row_sub_head">Банковские реквизиты компании:</div>
                            </div>

                            <div class="row">
                                <div class="needItem">*</div>
                                <div class="holder row_val">
                                    <input type="text" name="PROP__BIK" value="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['partner_profile']['BIK']['VALUE']?>" placeholder="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['partner_profile']['BIK']['NAME']?>" />
                                    <input class="empty_but" data-val="Запросить данные по БИК" type="button" onclick="uploadBic(this);" value="Запросить данные по БИК" title="Данные о банковской организации заполняются автоматически по БИК" />
                                </div>
                            </div>

                            <div class="row">
                                <div class="needItem">*</div>
                                <div class="holder row_val">
                                    <input type="text" name="PROP__BANK" value="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['partner_profile']['BANK']['VALUE']?>" placeholder="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['partner_profile']['BANK']['NAME']?>" />
                                </div>
                            </div>

                            <div class="row">
                                <div class="needItem">*</div>
                                <div class="holder row_val">
                                    <input type="text" name="PROP__RASCH_SCHET" value="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['partner_profile']['RASCH_SCHET']['VALUE']?>" placeholder="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['partner_profile']['RASCH_SCHET']['NAME']?>" />
                                </div>
                            </div>

                            <div class="row">
                                <div class="needItem">*</div>
                                <div class="holder row_val">
                                    <input type="text" name="PROP__KOR_SCHET" value="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['partner_profile']['KOR_SCHET']['VALUE']?>" placeholder="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['partner_profile']['KOR_SCHET']['NAME']?>" />
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

                            <div class="g-recaptcha g-relazy" id="recaptcha_p" data-callback="onRecSubmit" data-size="invisible"></div>

                            <div class="row">
                                <span class="rrs_btn_auth">
                                    <input class="submit-btn" type="submit" name="Login" value="<?=GetMessage("AUTH_REG_BUTTON")?>" data-role="none">
                                </span>
                            </div>

                        </form>
                    </div>

                    <div class="reg_form<?if($arResult['SHOW_TYPE'] == 'client'){?> active<?}?>" data-val="s2">
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
                            <input type="hidden" name="TYPE" value="client" />

                            <div class="row">
                                <div class="holder row_sub_head">Данные пользователя:</div>
                            </div>

                            <div class="row">
                                <div class="needItem">*</div>
                                <div class="holder row_val">
                                    <input type="text" name="USER_NAME" value="<?=$arResult['USER_NAME']?>" placeholder="<?=GetMessage("USER_NAME")?>" />
                                </div>
                            </div>

                            <?/*<div class="row">
                                <div class="needItem">*</div>
                                <div class="holder row_val">
                                    <input type="text" name="USER_LAST_NAME" value="<?=$arResult['USER_LAST_NAME']?>" placeholder="<?=GetMessage("USER_LAST_NAME")?>" />
                                </div>
                            </div>*/?>

                            <?
                            if (isset($_GET['reg_hash']) || (isset($_GET['hash']) && $_GET['reg'] == 'mobile')) {
                            ?>
                                <input type="hidden" name="USER_EMAIL" value="<?=$arResult['USER_EMAIL']?>">
                                <div class="row">
                                    <div class="needItem">*</div>
                                    <div class="holder row_val">
                                        <input type="text" data-checkval="y" data-checktype="email" name="" value="<?=$arResult['USER_EMAIL']?>" disabled="disabled" placeholder="<?=GetMessage("AUTH_EMAIL")?>">
                                    </div>
                                </div>
                            <?
                            }
                            else {
                            ?>
                                <div class="row">
                                    <div class="needItem">*</div>
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

                            <div class="row phone_sms">
                                <div class="needItem">*</div>
                                <div class="holder row_val">
                                    <input type="text" class="phone_msk<?if($check_sms){?> check_success<?}?>" name="PROP__PHONE" data-checktype="phone" value="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['partner_profile']['PHONE']['VALUE']?>" placeholder="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['partner_profile']['PHONE']['NAME']?>" />
                                    <!--<div class="send_button" data-again="Отправить повторно" data-first="Отправить смс">Отправить смс</div>-->
                                    <div class="success_ico"></div>
                                    <div class="clear"></div>
                                </div>

                                <?/*<div class="sms_confirmation">
                                    <div class="needItem">*</div>
                                    <div class="row_val">
                                        <input type="text" name="sms_code" placeholder="Введите код" />
                                        <div class="submit_sms_button">Подтвердить</div>
                                        <div class="clear"></div>
                                    </div>
                                </div>*/?>
                            </div>

                            <?
                            if (isset($_GET['reg_hash']) || (isset($_GET['hash']) && $_GET['reg'] == 'mobile')) {
                            ?>
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
                            <?
                            }
                            ?>

                            <?/*<div class="row">
                                <div class="holder row_sub_head"><?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['client_profile']['REGION']['NAME']?>:</div>
                                <div class="needItem">*</div>
                                <div class="holder row_val">
                                    <select data-search="y" name="PROP__REGION">
                                        <option value=""></option>
                                        <?
                                        if (isset($arResult['PROPERTIES_LISTS_ADDITIONAL']['REGION'])) {
                                            foreach($arResult['PROPERTIES_LISTS_ADDITIONAL']['REGION'] as $cur_id => $cur_name) {
                                            ?>
                                                <option value="<?=$cur_id;?>" <?=($arResult['PROPERTIES_IBLOCK_ADDITIONAL']['client_profile']['REGION']['VALUE'] == $cur_id ? 'selected="selected"' : '')?>><?=$cur_name;?></option>
                                            <?
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>*/?>

                            <div class="row">
                                <div class="holder row_sub_head">Данные компании:</div>
                            </div>

                            <?/*<input type="hidden" name="PROP__UL_TYPE" value="ul">

                            <div class="row">
                                <div class="needItem">*</div>
                                <div class="holder row_val">
                                    <input type="text" name="PROP__INN" value="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['client_profile']['INN']['VALUE'];?>" placeholder="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['client_profile']['INN']['NAME'];?>" />
                                    <input class="empty_but" data-val="Запросить данные по ИНН" type="button" onclick="uploadInn(this);" value="Запросить данные по ИНН" title="Данные об организации заполняются автоматически по ИНН" />
                                </div>
                            </div>

                            <div class="row">
                                <div class="holder row_val">
                                    <input type="hidden" name="PROP__FULL_COMPANY_NAME" value="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['client_profile']['FULL_COMPANY_NAME']['VALUE'];?>" />
                                    <textarea title="Заполняется автоматически после валидации ИНН" name="" class="disabled" readonly="readonly" placeholder="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['client_profile']['FULL_COMPANY_NAME']['NAME']?>"><?
                                        echo ($arResult['PROPERTIES_IBLOCK_ADDITIONAL']['client_profile']['FULL_COMPANY_NAME']['VALUE'] != '' ? $arResult['PROPERTIES_IBLOCK_ADDITIONAL']['client_profile']['FULL_COMPANY_NAME']['NAME'] . ': ' . $arResult['PROPERTIES_IBLOCK_ADDITIONAL']['client_profile']['FULL_COMPANY_NAME']['VALUE'] : '');
                                    ?></textarea>
                                </div>
                            </div>

                            <div class="row" style="display: none;">
                                <div class="holder row_val">
                                    <input type="hidden" name="PROP__IP_FIO" value="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['client_profile']['IP_FIO']['VALUE']?>" />
                                    <textarea title="Заполняется автоматически после валидации ИНН" name="" class="disabled" readonly="readonly" placeholder="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['client_profile']['IP_FIO']['NAME']?>"><?
                                        echo ($arResult['PROPERTIES_IBLOCK_ADDITIONAL']['client_profile']['IP_FIO']['VALUE'] != '' ? $arResult['PROPERTIES_IBLOCK_ADDITIONAL']['client_profile']['IP_FIO']['NAME'] . ': ' . $arResult['PROPERTIES_IBLOCK_ADDITIONAL']['client_profile']['IP_FIO']['VALUE'] : '');
                                        ?></textarea>
                                </div>
                            </div>

                            <input type="hidden" name="PROP__REG_DATE" value="">

                            <div class="row">
                                <div class="holder row_val">
                                    <input type="hidden" name="PROP__YUR_ADRESS" value="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['client_profile']['YUR_ADRESS']['VALUE'];?>" />
                                    <textarea title="Заполняется автоматически после валидации ИНН" name="" class="disabled" readonly="readonly" placeholder="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['client_profile']['YUR_ADRESS']['NAME']?>"><?
                                        echo ($arResult['PROPERTIES_IBLOCK_ADDITIONAL']['client_profile']['YUR_ADRESS']['VALUE'] != '' ? $arResult['PROPERTIES_IBLOCK_ADDITIONAL']['client_profile']['YUR_ADRESS']['NAME'] . ': ' . $arResult['PROPERTIES_IBLOCK_ADDITIONAL']['client_profile']['YUR_ADRESS']['VALUE'] : '');
                                    ?></textarea>
                                </div>
                            </div>

                            <div class="row">
                                <div class="needItem">*</div>
                                <div class="holder row_val">
                                    <input type="text" name="PROP__POST_ADRESS" value="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['client_profile']['POST_ADRESS']['VALUE'];?>" placeholder="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['client_profile']['POST_ADRESS']['NAME'];?>" />
                                </div>
                            </div>

                            <div class="row">
                                <div class="radio_group">
                                    <div class="radio_area">
                                        <input type="checkbox" data-text="соответствует юридическому адресу" name="" id="is_yur_adres_c" class="is_yur_adres" value="">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="holder row_val">
                                    <input type="hidden" name="PROP__KPP" value="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['client_profile']['KPP']['VALUE']?>" />
                                    <input type="text" title="Заполняется автоматически после валидации ИНН" name="" class="disabled" readonly="readonly" value="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['client_profile']['KPP']['VALUE']?>" placeholder="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['client_profile']['KPP']['NAME']?>" />
                                </div>
                            </div>

                            <div class="row">
                                <div class="holder row_val">
                                    <input type="hidden" name="PROP__OGRN" value="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['client_profile']['OGRN']['VALUE']?>" />
                                    <input type="text" title="Заполняется автоматически после валидации ИНН" name="" class="disabled" readonly="readonly" value="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['client_profile']['OGRN']['VALUE']?>" placeholder="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['client_profile']['OGRN']['NAME']?>" />
                                </div>
                            </div>

                            <div class="row">
                                <div class="holder row_val">
                                    <input type="hidden" name="PROP__OKPO" value="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['client_profile']['OKPO']['VALUE']?>" />
                                    <input type="text" title="Заполняется автоматически после валидации ИНН" name="" class="disabled" readonly="readonly" value="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['client_profile']['OKPO']['VALUE']?>" placeholder="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['client_profile']['OKPO']['NAME']?>" />
                                </div>
                            </div>

                            <div class="row">
                                <div class="holder row_val">
                                    <input type="text" name="PROP__FIO_DIR" value="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['client_profile']['FIO_DIR']['VALUE']?>" placeholder="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['client_profile']['FIO_DIR']['NAME']?>" />
                                </div>
                            </div>

                            <div class="row">
                                <div class="holder row_sub_head">
                                    <?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['client_profile']['OSNOVANIE_PRAVA_PODPISI_FILE']['NAME']?>:
                                </div>
                                <div class="holder row_val">
                                    <input type="file" name="PROP__OSNOVANIE_PRAVA_PODPISI_FILE" />
                                </div>
                            </div>*/?>

                            <div class="row">
                                <div class="holder row_sub_head"><?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['client_profile']['NDS']['NAME']?>:</div>
                                <div class="needItem">*</div>
                                <div class="holder row_val">
                                    <select name="PROP__NDS">
                                        <option value=""></option>
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

                            <?/*<div class="row">
                                <div class="holder row_sub_head">Банковские реквизиты компании:</div>
                            </div>

                            <div class="row">
                                <div class="needItem">*</div>
                                <div class="holder row_val">
                                    <input type="text" name="PROP__BIK" value="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['client_profile']['BIK']['VALUE']?>" placeholder="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['client_profile']['BIK']['NAME']?>" />
                                    <input class="empty_but" data-val="Запросить данные по БИК" type="button" onclick="uploadBic(this);" value="Запросить данные по БИК" title="Данные о банковской организации заполняются автоматически по БИК" />
                                </div>
                            </div>

                            <div class="row">
                                <div class="needItem">*</div>
                                <div class="holder row_val">
                                    <input type="text" name="PROP__BANK" value="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['client_profile']['BANK']['VALUE']?>" placeholder="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['client_profile']['BANK']['NAME']?>" />
                                </div>
                            </div>

                            <div class="row">
                                <div class="needItem">*</div>
                                <div class="holder row_val">
                                    <input type="text" name="PROP__RASCH_SCHET" value="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['client_profile']['RASCH_SCHET']['VALUE']?>" placeholder="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['client_profile']['RASCH_SCHET']['NAME']?>" />
                                </div>
                            </div>

                            <div class="row">
                                <div class="needItem">*</div>
                                <div class="holder row_val">
                                    <input type="text" name="PROP__KOR_SCHET" value="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['client_profile']['KOR_SCHET']['VALUE']?>" placeholder="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['client_profile']['KOR_SCHET']['NAME']?>" />
                                </div>
                            </div>*/?>

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

                    <div class="reg_form<?if($arResult['SHOW_TYPE'] == 'farmer'){?> active<?}?>" data-val="s3">
                        <form class="auth-form farmer" action="" method="post" enctype="multipart/form-data">
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

                            <input type="hidden" name="PUBLIC_FORM value="REGISTER"/>
                            <input type="hidden" name="REG_FORM" value="Y" />
                            <input type="hidden" name="TYPE" value="farmer" />

                            <div class="row">
                                <div class="holder row_sub_head">Данные пользователя:</div>
                            </div>

                            <div class="row">
                                <div class="needItem">*</div>
                                <div class="holder row_val">
                                    <input type="text" name="USER_NAME" value="<?=$arResult['USER_NAME']?>" placeholder="<?=GetMessage("USER_NAME")?>" />
                                </div>
                            </div>

                            <?/*<div class="row">
                                <div class="needItem">*</div>
                                <div class="holder row_val">
                                    <input type="text" name="USER_LAST_NAME" value="<?=$arResult['USER_LAST_NAME']?>" placeholder="<?=GetMessage("USER_LAST_NAME")?>" />
                                </div>
                            </div>*/?>

                            <?
                            if (isset($_GET['reg_hash']) || (isset($_GET['hash']) && $_GET['reg'] == 'mobile')) {
                            ?>
                                <input type="hidden" name="USER_EMAIL" value="<?=$arResult['USER_EMAIL']?>">
                                <div class="row">
                                    <div class="needItem">*</div>
                                    <div class="holder row_val">
                                        <input type="text" data-checkval="y" data-checktype="email" name="" value="<?=$arResult['USER_EMAIL']?>" disabled="disabled" placeholder="<?=GetMessage("AUTH_EMAIL")?>">
                                    </div>
                                </div>
                            <?
                            }
                            else {
                            ?>
                                <div class="row">
                                    <div class="needItem">*</div>
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

                            <div class="row phone_sms<?if($check_sms){?> check_success<?}?>">
                                <div class="needItem">*</div>
                                <div class="holder row_val">
                                    <input type="text" class="phone_msk" name="PROP__PHONE" data-checktype="phone" value="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['farmer_profile']['PHONE']['VALUE'];?>" placeholder="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['farmer_profile']['PHONE']['NAME'];?>" />
                                    <!--<div class="send_button" data-again="Отправить повторно" data-first="Отправить смс">Отправить смс</div>-->
                                    <div class="success_ico"></div>
                                    <div class="clear"></div>
                                </div>

                                <?/*<div class="sms_confirmation">
                                    <div class="needItem">*</div>
                                    <div class="row_val">
                                        <input type="text" name="sms_code" placeholder="Введите код" />
                                        <div class="submit_sms_button">Подтвердить</div>
                                        <div class="clear"></div>
                                    </div>
                                </div>*/?>
                            </div>

                            <?
                            if (isset($_GET['reg_hash']) || (isset($_GET['hash']) && $_GET['reg'] == 'mobile')) {
                            ?>
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
                            <?
                            }
                            ?>

                            <?/*<div class="row">
                                <div class="holder row_sub_head"><?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['farmer_profile']['REGION']['NAME']?>:</div>
                                <div class="needItem">*</div>
                                <div class="holder row_val">
                                    <select data-search="y" name="PROP__REGION">
                                        <option value=""></option>
                                        <?
                                        if (isset($arResult['PROPERTIES_LISTS_ADDITIONAL']['REGION'])) {
                                            foreach($arResult['PROPERTIES_LISTS_ADDITIONAL']['REGION'] as $cur_id => $cur_name) {
                                            ?>
                                                <option value="<?=$cur_id;?>" <?=($arResult['PROPERTIES_IBLOCK_ADDITIONAL']['farmer_profile']['REGION']['VALUE'] == $cur_id ? 'selected="selected"' : '')?>><?=$cur_name;?></option>
                                            <?
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>*/?>

                            <div class="row">
                                <div class="holder row_sub_head">Данные компании:</div>
                            </div>

                            <?/*<input type="hidden" name="PROP__UL_TYPE" value="ul">

                            <div class="row">
                                <div class="needItem">*</div>
                                <div class="holder row_val">
                                    <input type="text" name="PROP__INN" value="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['farmer_profile']['INN']['VALUE'];?>" placeholder="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['farmer_profile']['INN']['NAME'];?>" />
                                    <input class="empty_but" data-val="Запросить данные по ИНН" type="button" onclick="uploadInn(this);" value="Запросить данные по ИНН" title="Данные об организации заполняются автоматически по ИНН" />
                                </div>
                            </div>

                            <div class="row">
                                <div class="holder row_val">
                                    <input type="hidden" name="PROP__FULL_COMPANY_NAME" value="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['farmer_profile']['FULL_COMPANY_NAME']['VALUE'];?>" />
                                    <textarea title="Заполняется автоматически после валидации ИНН" name="" class="disabled" readonly="readonly" placeholder="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['farmer_profile']['FULL_COMPANY_NAME']['NAME']?>"><?
                                        echo ($arResult['PROPERTIES_IBLOCK_ADDITIONAL']['farmer_profile']['FULL_COMPANY_NAME']['VALUE'] != '' ? $arResult['PROPERTIES_IBLOCK_ADDITIONAL']['farmer_profile']['FULL_COMPANY_NAME']['NAME'] . ': ' . $arResult['PROPERTIES_IBLOCK_ADDITIONAL']['farmer_profile']['FULL_COMPANY_NAME']['VALUE'] : '');
                                    ?></textarea>
                                </div>
                            </div>

                            <div class="row" style="display: none;">
                                <div class="holder row_val">
                                    <input type="hidden" name="PROP__IP_FIO" value="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['farmer_profile']['IP_FIO']['VALUE']?>" />
                                    <textarea title="Заполняется автоматически после валидации ИНН" name="" class="disabled" readonly="readonly" placeholder="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['farmer_profile']['IP_FIO']['NAME']?>"><?
                                        echo ($arResult['PROPERTIES_IBLOCK_ADDITIONAL']['farmer_profile']['IP_FIO']['VALUE'] != '' ? $arResult['PROPERTIES_IBLOCK_ADDITIONAL']['farmer_profile']['IP_FIO']['NAME'] . ': ' . $arResult['PROPERTIES_IBLOCK_ADDITIONAL']['farmer_profile']['IP_FIO']['VALUE'] : '');
                                        ?></textarea>
                                </div>
                            </div>

                            <input type="hidden" name="PROP__REG_DATE" value="">

                            <div class="row">
                                <div class="holder row_val">
                                    <input type="hidden" name="PROP__YUR_ADRESS" value="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['farmer_profile']['YUR_ADRESS']['VALUE'];?>" />
                                    <textarea title="Заполняется автоматически после валидации ИНН" name="" class="disabled" readonly="readonly" placeholder="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['farmer_profile']['YUR_ADRESS']['NAME']?>"><?
                                        echo ($arResult['PROPERTIES_IBLOCK_ADDITIONAL']['farmer_profile']['YUR_ADRESS']['VALUE'] != '' ? $arResult['PROPERTIES_IBLOCK_ADDITIONAL']['farmer_profile']['YUR_ADRESS']['NAME'] . ': ' . $arResult['PROPERTIES_IBLOCK_ADDITIONAL']['farmer_profile']['YUR_ADRESS']['VALUE'] : '');
                                    ?></textarea>
                                </div>
                            </div>

                            <div class="row">
                                <div class="needItem">*</div>
                                <div class="holder row_val">
                                    <input type="text" name="PROP__POST_ADRESS" value="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['farmer_profile']['POST_ADRESS']['VALUE'];?>" placeholder="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['farmer_profile']['POST_ADRESS']['NAME'];?>" />
                                </div>
                            </div>

                            <div class="row">
                                <div class="radio_group">
                                    <div class="radio_area">
                                        <input type="checkbox" data-text="соответствует юридическому адресу" name="" id="is_yur_adres_f" class="is_yur_adres" value="">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="holder row_val">
                                    <input type="hidden" name="PROP__KPP" value="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['farmer_profile']['KPP']['VALUE']?>" />
                                    <input type="text" title="Заполняется автоматически после валидации ИНН" name="" class="disabled" readonly="readonly" value="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['farmer_profile']['KPP']['VALUE']?>" placeholder="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['farmer_profile']['KPP']['NAME']?>" />
                                </div>
                            </div>

                            <div class="row">
                                <div class="holder row_val">
                                    <input type="hidden" name="PROP__OGRN" value="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['farmer_profile']['OGRN']['VALUE']?>" />
                                    <input type="text" title="Заполняется автоматически после валидации ИНН" name="" class="disabled" readonly="readonly" value="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['farmer_profile']['OGRN']['VALUE']?>" placeholder="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['farmer_profile']['OGRN']['NAME']?>" />
                                </div>
                            </div>

                            <div class="row">
                                <div class="holder row_val">
                                    <input type="hidden" name="PROP__OKPO" value="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['farmer_profile']['OKPO']['VALUE']?>" />
                                    <input type="text" title="Заполняется автоматически после валидации ИНН" name="" class="disabled" readonly="readonly" value="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['farmer_profile']['OKPO']['VALUE']?>" placeholder="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['farmer_profile']['OKPO']['NAME']?>" />
                                </div>
                            </div>

                            <div class="row">
                                <div class="holder row_val">
                                    <input type="text" name="PROP__FIO_DIR" value="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['farmer_profile']['FIO_DIR']['VALUE']?>" placeholder="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['farmer_profile']['FIO_DIR']['NAME']?>" />
                                </div>
                            </div>

                            <div class="row">
                                <div class="holder row_sub_head">
                                    <?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['farmer_profile']['OSNOVANIE_PRAVA_PODPISI_FILE']['NAME']?>:
                                </div>
                                <div class="holder row_val">
                                    <input type="file" name="PROP__OSNOVANIE_PRAVA_PODPISI_FILE" />
                                </div>
                            </div>*/?>

                            <div class="row">
                                <div class="holder row_sub_head"><?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['farmer_profile']['NDS']['NAME']?>:</div>
                                <div class="needItem">*</div>
                                <div class="holder row_val">
                                    <select name="PROP__NDS">
                                        <option value=""></option>
                                        <?
                                        if (isset($arResult['PROPERTIES_LISTS_ADDITIONAL']['NDS'])) {
                                            foreach($arResult['PROPERTIES_LISTS_ADDITIONAL']['NDS'] as $cur_id => $cur_name) {
                                            ?>
                                                <option value="<?=$cur_id;?>" <?=($arResult['PROPERTIES_IBLOCK_ADDITIONAL']['farmer_profile']['NDS']['VALUE'] == $cur_id ? 'selected="selected"' : '')?>><?=$cur_name;?></option>
                                            <?
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>

                            <?/*<div class="row">
                                <div class="holder row_sub_head">Банковские реквизиты компании:</div>
                            </div>

                            <div class="row">
                                <div class="needItem">*</div>
                                <div class="holder row_val">
                                    <input type="text" name="PROP__BIK" value="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['farmer_profile']['BIK']['VALUE']?>" placeholder="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['farmer_profile']['BIK']['NAME']?>" />
                                    <input class="empty_but" data-val="Запросить данные по БИК" type="button" onclick="uploadBic(this);" value="Запросить данные по БИК" title="Данные о банковской организации заполняются автоматически по БИК" />
                                </div>
                            </div>

                            <div class="row">
                                <div class="needItem">*</div>
                                <div class="holder row_val">
                                    <input type="text" name="PROP__BANK" value="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['farmer_profile']['BANK']['VALUE']?>" placeholder="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['farmer_profile']['BANK']['NAME']?>" />
                                </div>
                            </div>

                            <div class="row">
                                <div class="needItem">*</div>
                                <div class="holder row_val">
                                    <input type="text" name="PROP__RASCH_SCHET" value="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['farmer_profile']['RASCH_SCHET']['VALUE']?>" placeholder="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['farmer_profile']['RASCH_SCHET']['NAME']?>" />
                                </div>
                            </div>

                            <div class="row">
                                <div class="needItem">*</div>
                                <div class="holder row_val">
                                    <input type="text" name="PROP__KOR_SCHET" value="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['farmer_profile']['KOR_SCHET']['VALUE']?>" placeholder="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['farmer_profile']['KOR_SCHET']['NAME']?>" />
                                </div>
                            </div>*/?>

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

                            <div class="row">
                                <span class="rrs_btn_auth">
                                    <input class="submit-btn" type="submit" name="Login" value="<?=GetMessage("AUTH_REG_BUTTON")?>" data-role="none">
                                </span>
                            </div>

                            <div class="g-recaptcha g-relazy" id="recaptcha_f" data-callback="onRecSubmit" data-size="invisible"></div>

                        </form>
                    </div>

                    <div class="reg_form<?if($arResult['SHOW_TYPE'] == 'transport'){?> active<?}?>" data-val="s4">
                        <form class="auth-form transport" action="" method="post" enctype="multipart/form-data">
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
                            <input type="hidden" name="TYPE" value="transport" />

                            <div class="row">
                                <div class="holder row_sub_head">Данные пользователя:</div>
                            </div>

                            <div class="row">
                                <div class="needItem">*</div>
                                <div class="holder row_val">
                                    <input type="text" name="USER_NAME" value="<?=$arResult['USER_NAME']?>" placeholder="<?=GetMessage("USER_NAME")?>" />
                                </div>
                            </div>

                            <div class="row">
                                <div class="needItem">*</div>
                                <div class="holder row_val">
                                    <input type="text" name="USER_LAST_NAME" value="<?=$arResult['USER_LAST_NAME']?>" placeholder="<?=GetMessage("USER_LAST_NAME")?>">
                                </div>
                            </div>

                            <?
                            if (isset($_GET['reg_hash']) || (isset($_GET['hash']) && $_GET['reg'] == 'mobile')) {
                            ?>
                                <input type="hidden" name="USER_EMAIL" value="<?=$arResult['USER_EMAIL']?>">
                                <div class="row">
                                    <div class="needItem">*</div>
                                    <div class="holder row_val">
                                        <input type="text" data-checkval="y" data-checktype="email" name="" value="<?=$arResult['USER_EMAIL']?>" disabled="disabled" placeholder="<?=GetMessage("AUTH_EMAIL")?>">
                                    </div>
                                </div>
                            <?
                            }
                            else {
                            ?>
                                <div class="row">
                                    <div class="needItem">*</div>
                                    <div class="holder row_val">
                                        <input type="text" data-checkval="y" data-checktype="email" name="USER_EMAIL" value="<?=$arResult['USER_EMAIL']?>" placeholder="<?=GetMessage("AUTH_EMAIL")?>">
                                    </div>
                                </div>
                            <?
                            }
                            ?>

                            <div class="row phone_sms">
                                <div class="needItem">*</div>
                                <div class="holder row_val">
                                    <input type="text" class="phone_msk" name="PROP__PHONE" data-checktype="phone" value="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['transport_profile']['PHONE']['VALUE'];?>" placeholder="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['transport_profile']['PHONE']['NAME'];?>" />
                                    <div class="send_button" data-again="Отправить повторно" data-first="Отправить смс">Отправить смс</div>
                                    <div class="success_ico"></div>
                                    <div class="clear"></div>
                                </div>

                                <div class="sms_confirmation">
                                    <div class="needItem">*</div>
                                    <div class="row_val">
                                        <input type="text" name="sms_code" placeholder="Введите код" />
                                        <div class="submit_sms_button">Подтвердить</div>
                                        <div class="clear"></div>
                                    </div>
                                </div>
                            </div>

                            <?
                            if (isset($_GET['reg_hash']) || (isset($_GET['hash']) && $_GET['reg'] == 'mobile')) {
                            ?>
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
                            <?
                            }
                            ?>

                            <div class="row">
                                <div class="holder row_sub_head"><?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['transport_profile']['REGION']['NAME']?>:</div>
                                <div class="needItem">*</div>
                                <div class="holder row_val">
                                    <select data-search="y" name="PROP__REGION">
                                        <option value=""></option>
                                        <?
                                        if (isset($arResult['PROPERTIES_LISTS_ADDITIONAL']['REGION'])) {
                                            foreach($arResult['PROPERTIES_LISTS_ADDITIONAL']['REGION'] as $cur_id => $cur_name) {
                                            ?>
                                                <option value="<?=$cur_id;?>" <?=($arResult['PROPERTIES_IBLOCK_ADDITIONAL']['transport_profile']['REGION']['VALUE'] == $cur_id ? 'selected="selected"' : '')?>><?=$cur_name;?></option>
                                            <?
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="holder row_sub_head">Данные компании:</div>
                            </div>

                            <input type="hidden" name="PROP__UL_TYPE" value="ul">

                            <div class="row">
                                <div class="needItem">*</div>
                                <div class="holder row_val">
                                    <input type="text" name="PROP__INN" value="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['transport_profile']['INN']['VALUE']?>" placeholder="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['transport_profile']['INN']['NAME']?>" />
                                    <input class="empty_but" data-val="Запросить данные по ИНН" type="button" onclick="uploadInn(this);" value="Запросить данные по ИНН" title="Данные об организации заполняются автоматически по ИНН" />
                                </div>
                            </div>

                            <div class="row">
                                <div class="holder row_val">
                                    <input type="hidden" name="PROP__FULL_COMPANY_NAME" value="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['transport_profile']['FULL_COMPANY_NAME']['VALUE']?>" />
                                    <textarea title="Заполняется автоматически после валидации ИНН" name="" class="disabled" readonly="readonly" placeholder="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['transport_profile']['FULL_COMPANY_NAME']['NAME']?>"><?
                                        echo ($arResult['PROPERTIES_IBLOCK_ADDITIONAL']['transport_profile']['FULL_COMPANY_NAME']['VALUE'] != '' ? $arResult['PROPERTIES_IBLOCK_ADDITIONAL']['transport_profile']['FULL_COMPANY_NAME']['NAME'] . ': ' . $arResult['PROPERTIES_IBLOCK_ADDITIONAL']['transport_profile']['FULL_COMPANY_NAME']['VALUE'] : '');
                                        ?></textarea>
                                </div>
                            </div>

                            <div class="row" style="display: none;">
                                <div class="holder row_val">
                                    <input type="hidden" name="PROP__IP_FIO" value="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['transport_profile']['IP_FIO']['VALUE']?>" />
                                    <textarea title="Заполняется автоматически после валидации ИНН" name="" class="disabled" readonly="readonly" placeholder="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['transport_profile']['IP_FIO']['NAME']?>"><?
                                        echo ($arResult['PROPERTIES_IBLOCK_ADDITIONAL']['transport_profile']['IP_FIO']['VALUE'] != '' ? $arResult['PROPERTIES_IBLOCK_ADDITIONAL']['transport_profile']['IP_FIO']['NAME'] . ': ' . $arResult['PROPERTIES_IBLOCK_ADDITIONAL']['transport_profile']['IP_FIO']['VALUE'] : '');
                                        ?></textarea>
                                </div>
                            </div>

                            <input type="hidden" name="PROP__REG_DATE" value="">

                            <div class="row">
                                <div class="holder row_val">
                                    <input type="hidden" name="PROP__YUR_ADRESS" value="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['transport_profile']['YUR_ADRESS']['VALUE']?>" />
                                    <textarea title="Заполняется автоматически после валидации ИНН" name="" class="disabled" readonly="readonly" placeholder="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['transport_profile']['YUR_ADRESS']['NAME']?>"><?
                                        echo ($arResult['PROPERTIES_IBLOCK_ADDITIONAL']['transport_profile']['YUR_ADRESS']['VALUE'] != '' ? $arResult['PROPERTIES_IBLOCK_ADDITIONAL']['transport_profile']['YUR_ADRESS']['NAME'] . ': ' . $arResult['PROPERTIES_IBLOCK_ADDITIONAL']['transport_profile']['YUR_ADRESS']['VALUE'] : '');
                                        ?></textarea>
                                </div>
                            </div>

                            <div class="row">
                                <div class="needItem">*</div>
                                <div class="holder row_val">
                                    <input type="text" name="PROP__POST_ADRESS" value="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['transport_profile']['POST_ADRESS']['VALUE']?>" placeholder="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['transport_profile']['POST_ADRESS']['NAME']?>" />
                                </div>
                            </div>

                            <div class="row">
                                <div class="radio_group">
                                    <div class="radio_area">
                                        <input type="checkbox" data-text="соответствует юридическому адресу" name="" id="is_yur_adres_t" class="is_yur_adres" value="">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="holder row_val">
                                    <input type="hidden" name="PROP__KPP" value="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['transport_profile']['KPP']['VALUE']?>" />
                                    <input type="text" title="Заполняется автоматически после валидации ИНН" name="" class="disabled" readonly="readonly" value="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['transport_profile']['KPP']['VALUE']?>" placeholder="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['transport_profile']['KPP']['NAME']?>" />
                                </div>
                            </div>

                            <div class="row">
                                <div class="holder row_val">
                                    <input type="hidden" name="PROP__OGRN" value="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['transport_profile']['OGRN']['VALUE']?>" />
                                    <input type="text" title="Заполняется автоматически после валидации ИНН" name="" class="disabled" readonly="readonly" value="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['transport_profile']['OGRN']['VALUE']?>" placeholder="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['transport_profile']['OGRN']['NAME']?>" />
                                </div>
                            </div>

                            <div class="row">
                                <div class="holder row_val">
                                    <input type="hidden" name="PROP__OKPO" value="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['transport_profile']['OKPO']['VALUE']?>" />
                                    <input type="text" title="Заполняется автоматически после валидации ИНН" name="" class="disabled" readonly="readonly" value="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['transport_profile']['OKPO']['VALUE']?>" placeholder="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['transport_profile']['OKPO']['NAME']?>" />
                                </div>
                            </div>

                            <div class="row">
                                <div class="holder row_val">
                                    <input type="text" name="PROP__FIO_DIR" value="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['transport_profile']['FIO_DIR']['VALUE']?>" placeholder="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['transport_profile']['FIO_DIR']['NAME']?>" />
                                </div>
                            </div>

                            <div class="signer_ul">
                                <?
                                if (sizeof($arResult['SIGNERS']['ul']) > 0) {
                                ?>
                                    <div class="row">
                                        <div class="holder row_sub_head">Подписант:</div>
                                    </div>

                                    <div class="row_val">
                                        <div class="radio_group">
                                            <?
                                            $k = 0;
                                            foreach ($arResult['SIGNERS']['ul'] as $signer) {
                                            ?>
                                                <div class="radio_area">
                                                    <input type="radio" class="ch_signer" name="signer[ul]" data-text="<?=$signer['NAME']?>" data-code="ul_<?=$signer['CODE']?>" id="s3<?=$signer['ID']?>" value="<?=$signer['ID']?>" <? if ($k == 0) { ?>checked="checked"<? } ?>>
                                                </div>
                                                <?
                                                $k++;
                                            }
                                            ?>
                                        </div>
                                    </div>

                                    <div class="sign_block signer_ul_dir">
                                        <input type="hidden" name="post[ul][dir]" value="директор">
                                        <input type="hidden" name="num[ul][dir]" value="">
                                        <input type="hidden" name="date[ul][dir]" value="">
                                        <div class="row">
                                            <div class="holder row_sub_head">Действует на основании (название документа):</div>
                                            <div class="needItem">*</div>
                                            <div class="holder row_val">
                                                <select name="found[ul][dir]">
                                                    <?
                                                    foreach ($arResult['FOUND'] as $item) {
                                                        if ($item['PROPERTY_SHOW_ENUM_ID'] > 0) {
                                                        ?>
                                                            <option value="<?=$item['ID']?>"><?=$item['NAME']?></option>
                                                        <?
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="sign_block no_active signer_ul_gendir">
                                        <input type="hidden" name="post[ul][gendir]" value="генеральный директор">
                                        <input type="hidden" name="num[ul][gendir]" value="">
                                        <input type="hidden" name="date[ul][gendir]" value="">
                                        <div class="row">
                                            <div class="holder row_sub_head">Действует на основании (название документа):</div>
                                            <div class="needItem">*</div>
                                            <div class="holder row_val">
                                                <select name="found[ul][gendir]">
                                                    <?
                                                    foreach ($arResult['FOUND'] as $item) {
                                                        if ($item['PROPERTY_SHOW_ENUM_ID'] > 0) {
                                                        ?>
                                                            <option value="<?=$item['ID']?>"><?=$item['NAME']?></option>
                                                        <?
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="sign_block no_active signer_ul_sign">
                                        <div class="row">
                                            <div class="needItem">*</div>
                                            <div class="holder row_val">
                                                <input type="text" name="fio[ul][sign]" value="" placeholder="ФИО">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="holder row_val">
                                                <input type="text" name="post[ul][sign]" value="" placeholder="Должность">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <input type="hidden" name="found[ul][sign]" value="<?=$arResult['FOUND']['doverennost']['ID']?>">
                                            <div class="holder row_sub_head">Действует на основании <?=$arResult['FOUND']['doverennost']['PROPERTY_CHEGO_VALUE']?></div>
                                        </div>
                                        <div class="row">
                                            <div class="needItem">*</div>
                                            <div class="holder row_val">
                                                №<input type="text" class="signer_found_num" name="num[ul][sign]" value="" placeholder="" />
                                                от<input type="text" class="signer_found_date" autocomplete="off" name="date[ul][sign]" onclick="var tmp_val = this.value; this.value = ''; BX.calendar({value: tmp_val, node: this, field: this, bTime: false, callback_after: goTriggerChange(this)}); changeCalendar();" placeholder="" />
                                            </div>
                                        </div>
                                    </div>
                                <?
                                }
                                ?>
                            </div>

                            <div class="signer_ip">
                                <?
                                if (sizeof($arResult['SIGNERS']['ip']) > 0) {
                                ?>
                                    <div class="row">
                                        <div class="holder row_sub_head">Подписант:</div>
                                    </div>

                                    <div class="row_val">
                                        <div class="radio_group">
                                            <?
                                            $k = 0;
                                            foreach ($arResult['SIGNERS']['ip'] as $signer) {
                                                ?>
                                                <div class="radio_area">
                                                    <input type="radio" class="ch_signer" name="signer[ip]" data-text="<?=$signer['NAME']?>" data-code="ip_<?=$signer['CODE']?>" id="s4<?=$signer['ID']?>" value="<?=$signer['ID']?>" <? if ($k == 0) { ?>checked="checked"<? } ?>>
                                                </div>
                                                <?
                                                $k++;
                                            }
                                            ?>
                                        </div>
                                    </div>

                                    <div class="sign_block signer_ip_ip">
                                        <input type="hidden" name="post[ip][ip]" value="">
                                        <input type="hidden" name="num[ip][ip]" value="">
                                        <input type="hidden" name="date[ip][ip]" value="">
                                        <div class="row">
                                            <input type="hidden" name="found[ip][ip]" value="<?=$arResult['FOUND']['svidetelstvo']['ID']?>">
                                            <div class="holder row_sub_head">Действует на основании <?=$arResult['FOUND']['svidetelstvo']['PROPERTY_CHEGO_VALUE']?></div>
                                        </div>
                                    </div>

                                    <div class="sign_block no_active signer_ip_sign">
                                        <div class="row">
                                            <div class="needItem">*</div>
                                            <div class="holder row_val">
                                                <input type="text" name="fio[ip][sign]" value="" placeholder="ФИО">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="holder row_val">
                                                <input type="text" name="post[ip][sign]" value="" placeholder="Должность">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <input type="hidden" name="found[ip][sign]" value="<?=$arResult['FOUND']['doverennost']['ID']?>">
                                            <div class="holder row_sub_head">Действует на основании <?=$arResult['FOUND']['doverennost']['PROPERTY_CHEGO_VALUE']?></div>
                                        </div>
                                        <div class="row">
                                            <div class="needItem">*</div>
                                            <div class="holder row_val">
                                                №<input type="text" class="signer_found_num" name="num[ip][sign]" value="" placeholder="" />
                                                от<input type="text" class="signer_found_date" autocomplete="off" name="date[ip][sign]" onclick="var tmp_val = this.value; this.value = ''; BX.calendar({value: tmp_val, node: this, field: this, bTime: false, callback_after: goTriggerChange(this)}); changeCalendar();" placeholder="" />
                                            </div>
                                        </div>
                                    </div>
                                <?
                                }
                                ?>
                            </div>

                            <div class="row">
                                <div class="holder row_sub_head">
                                    <?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['transport_profile']['OSNOVANIE_PRAVA_PODPISI_FILE']['NAME']?>:
                                </div>
                                <div class="holder row_val">
                                    <input type="file" name="PROP__OSNOVANIE_PRAVA_PODPISI_FILE" class="needFile" />
                                </div>
                            </div>

                            <div class="row">
                                <div class="holder row_sub_head">Банковские реквизиты компании:</div>
                            </div>

                            <div class="row">
                                <div class="needItem">*</div>
                                <div class="holder row_val">
                                    <input type="text" name="PROP__BIK" value="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['transport_profile']['BIK']['VALUE']?>" placeholder="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['transport_profile']['BIK']['NAME']?>" />
                                    <input class="empty_but" data-val="Запросить данные по БИК" type="button" onclick="uploadBic(this);" value="Запросить данные по БИК" title="Данные о банковской организации заполняются автоматически по БИК" />
                                </div>
                            </div>

                            <div class="row">
                                <div class="needItem">*</div>
                                <div class="holder row_val">
                                    <input type="text" name="PROP__BANK" value="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['transport_profile']['BANK']['VALUE']?>" placeholder="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['transport_profile']['BANK']['NAME']?>" />
                                </div>
                            </div>

                            <div class="row">
                                <div class="needItem">*</div>
                                <div class="holder row_val">
                                    <input type="text" name="PROP__RASCH_SCHET" value="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['transport_profile']['RASCH_SCHET']['VALUE']?>" placeholder="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['transport_profile']['RASCH_SCHET']['NAME']?>" />
                                </div>
                            </div>

                            <div class="row">
                                <div class="needItem">*</div>
                                <div class="holder row_val">
                                    <input type="text" name="PROP__KOR_SCHET" value="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['transport_profile']['KOR_SCHET']['VALUE']?>" placeholder="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['transport_profile']['KOR_SCHET']['NAME']?>" />
                                </div>
                            </div>

                            <div class="row policy_row">
                                <div class="field field-option radio_group">
                                    <div class="radio_area">
                                        <input type="checkbox" data-text="<?=str_replace('"', '', GetMessage("AUTH_REGISTER_CONFIM"));?>" name="AUTH_REG_CONFIM" value="Y" />
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

                            <div class="row">
                                <span class="rrs_btn_auth">
                                    <input class="submit-btn" type="submit" name="Login" value="<?=GetMessage("AUTH_REG_BUTTON")?>" data-role="none">
                                </span>
                            </div>

                            <div class="g-recaptcha g-relazy" id="recaptcha_t" data-callback="onRecSubmit" data-size="invisible"></div>


                        </form>
                    </div>

                    <div class="reg_form<?if($arResult['SHOW_TYPE'] == 'agent'){?> active<?}?>" data-val="s5">
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
                        <input type="hidden" name="TYPE" value="agent" />

                        <div class="row">
                            <div class="holder row_sub_head">Данные пользователя:</div>
                        </div>

                        <div class="row">
                            <div class="needItem">*</div>
                            <div class="holder row_val">
                                <input type="text" name="USER_NAME" value="<?=$arResult['USER_NAME']?>" placeholder="<?=GetMessage("USER_NAME")?>" />
                            </div>
                        </div>

                        <div class="row">
                            <div class="needItem">*</div>
                            <div class="holder row_val">
                                <input type="text" name="USER_LAST_NAME" value="<?=$arResult['USER_LAST_NAME']?>" placeholder="<?=GetMessage("USER_LAST_NAME")?>">
                            </div>
                        </div>

                        <?
                        if (isset($_GET['reg_hash']) || (isset($_GET['hash']) && $_GET['reg'] == 'mobile')) {
                            ?>
                            <input type="hidden" name="USER_EMAIL" value="<?=$arResult['USER_EMAIL']?>">
                            <div class="row">
                                <div class="needItem">*</div>
                                <div class="holder row_val">
                                    <input type="text" data-checkval="y" data-checktype="email" name="" value="<?=$arResult['USER_EMAIL']?>" disabled="disabled" placeholder="<?=GetMessage("AUTH_EMAIL")?>">
                                </div>
                            </div>
                        <?
                        }
                        else {
                            ?>
                            <div class="row">
                                <div class="needItem">*</div>
                                <div class="holder row_val">
                                    <input type="text" data-checkval="y" data-checktype="email" name="USER_EMAIL" value="<?=$arResult['USER_EMAIL']?>" placeholder="<?=GetMessage("AUTH_EMAIL")?>" />
                                </div>
                            </div>
                        <?
                        }
                        ?>

                        <div class="row phone_sms">
                            <div class="needItem">*</div>
                            <div class="holder row_val">
                                <input type="text" class="phone_msk" name="PROP__PHONE" data-checktype="phone" value="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['farmer_profile']['PHONE']['VALUE'];?>" placeholder="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['farmer_profile']['PHONE']['NAME'];?>" />
                                <div class="send_button" data-again="Отправить повторно" data-first="Отправить смс">Отправить смс</div>
                                <div class="success_ico"></div>
                                <div class="clear"></div>
                            </div>

                            <div class="sms_confirmation">
                                <div class="needItem">*</div>
                                <div class="row_val">
                                    <input type="text" name="sms_code" placeholder="Введите код" />
                                    <div class="submit_sms_button">Подтвердить</div>
                                    <div class="clear"></div>
                                </div>
                            </div>
                        </div>

                        <?
                        if (isset($_GET['reg_hash']) || (isset($_GET['hash']) && $_GET['reg'] == 'mobile')) {
                            ?>
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
                        <?
                        }
                        ?>

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

                        <div class="g-recaptcha g-relazy" id="recaptcha_ag" data-callback="onRecSubmit" data-size="invisible"></div>

                        <div class="row">
                            <span class="rrs_btn_auth">
                                <input class="submit-btn" type="submit" name="Login" value="<?=GetMessage("AUTH_REG_BUTTON")?>" data-role="none">
                            </span>
                        </div>

                        </form>
                    </div>

                    <div class="reg_form<?if($arResult['SHOW_TYPE'] == 'client_agent'){?> active<?}?>" data-val="s6">
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
                            <input type="hidden" name="TYPE" value="client_agent" />

                            <div class="row">
                                <div class="holder row_sub_head">Данные пользователя:</div>
                            </div>

                            <div class="row">
                                <div class="needItem">*</div>
                                <div class="holder row_val">
                                    <input type="text" name="USER_NAME" value="<?=$arResult['USER_NAME']?>" placeholder="<?=GetMessage("USER_NAME")?>" />
                                </div>
                            </div>

                            <div class="row">
                                <div class="needItem">*</div>
                                <div class="holder row_val">
                                    <input type="text" name="USER_LAST_NAME" value="<?=$arResult['USER_LAST_NAME']?>" placeholder="<?=GetMessage("USER_LAST_NAME")?>">
                                </div>
                            </div>

                            <?
                            if (isset($_GET['reg_hash']) || (isset($_GET['hash']) && $_GET['reg'] == 'mobile')) {
                                ?>
                                <input type="hidden" name="USER_EMAIL" value="<?=$arResult['USER_EMAIL']?>">
                                <div class="row">
                                    <div class="needItem">*</div>
                                    <div class="holder row_val">
                                        <input type="text" data-checkval="y" data-checktype="email" name="" value="<?=$arResult['USER_EMAIL']?>" disabled="disabled" placeholder="<?=GetMessage("AUTH_EMAIL")?>">
                                    </div>
                                </div>
                            <?
                            }
                            else {
                                ?>
                                <div class="row">
                                    <div class="needItem">*</div>
                                    <div class="holder row_val">
                                        <input type="text" data-checkval="y" data-checktype="email" name="USER_EMAIL" value="<?=$arResult['USER_EMAIL']?>" placeholder="<?=GetMessage("AUTH_EMAIL")?>" />
                                    </div>
                                </div>
                            <?
                            }
                            ?>

                            <div class="row phone_sms">
                                <div class="needItem">*</div>
                                <div class="holder row_val">
                                    <input type="text" class="phone_msk" name="PROP__PHONE" data-checktype="phone" value="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['client_profile']['PHONE']['VALUE'];?>" placeholder="<?=$arResult['PROPERTIES_IBLOCK_ADDITIONAL']['client_profile']['PHONE']['NAME'];?>" />
                                    <div class="send_button" data-again="Отправить повторно" data-first="Отправить смс">Отправить смс</div>
                                    <div class="success_ico"></div>
                                    <div class="clear"></div>
                                </div>

                                <div class="sms_confirmation">
                                    <div class="needItem">*</div>
                                    <div class="row_val">
                                        <input type="text" name="sms_code" placeholder="Введите код" />
                                        <div class="submit_sms_button">Подтвердить</div>
                                        <div class="clear"></div>
                                    </div>
                                </div>
                            </div>

                            <?
                            if (isset($_GET['reg_hash']) || (isset($_GET['hash']) && $_GET['reg'] == 'mobile')) {
                                ?>
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
                            <?
                            }
                            ?>

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

                            <div class="g-recaptcha g-relazy" id="recaptcha_agc" data-callback="onRecSubmit" data-size="invisible"></div>

                            <div class="row">
                                <span class="rrs_btn_auth">
                                    <input class="submit-btn" type="submit" name="Login" value="<?=GetMessage("AUTH_REG_BUTTON")?>" data-role="none" />
                                </span>
                            </div>

                        </form>
                    </div>
                    <!-- <div class="g-recaptcha-sms" id="recaptcha_sms" data-callback="regRecaptchaPhoneSubmit" data-size="invisible"></div> -->
                <?
            }
        }
        ?>
    </div>
</div>
