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

if ($arResult['ERROR'] == 'Y') {
    echo '<div class="err_text row">' . $arResult['ERROR_MESSAGE'] . '</div>';
}
else {
    if ($arResult['ERROR_TEXT'] != '') {
        echo '<div class="err_text row">' . $arResult['ERROR_TEXT'] . '</div>';
    }

    if ($_GET['success'] == 'ok') {
        echo '<div class="success">Ваши данные успешно сохранены</div>';
    }

    if ($_GET['success'] == 'by') {
        echo '<div class="success">Данные пользователя успешно сохранены</div>';
    }
    ?>
    <div class="reg_form demo_form active">
        <form method="post" name="profile_form" action="" enctype="multipart/form-data">
            <input type="hidden" name="TYPE" value="farmer" />
            <input type="hidden" name="USER_EMAIL" value="<?=$arResult['USER_EMAIL'];?>">
            <input type="hidden" name="USER_PHONE" value="<?=$arResult['SHOW_PROPS']['PHONE']?>">
            <input type="hidden" name="update" value="y" />
            <div class="content-form profile-form">
                <div class="fields">
                    <?if(isset($arParams['BY_AGENT']) && $arParams['BY_AGENT'] == 'Y'){?>
                        <div class="row">
                            <div class="holder row_sub_head">Данные поставщика:</div>
                        </div>
                    <?}else{?>
                        <div class="row">
                            <div class="holder row_sub_head">Данные пользователя:</div>
                        </div>
                    <?}?>

                    <div class="row">
                        <div class="row_head"><?=GetMessage("USER_LAST_NAME")?></div>
                        <div class="needItem">*</div>
                        <div class="holder row_val">
                            <input type="text" name="LAST_NAME" value="<?=$arResult['SHOW_FIELDS']['LAST_NAME']?>" placeholder="<?=GetMessage("USER_LAST_NAME")?>" />
                        </div>
                    </div>

                    <div class="row">
                        <div class="row_head"><?=GetMessage("USER_NAME")?></div>
                        <div class="needItem">*</div>
                        <div class="holder row_val">
                            <input type="text" name="NAME" value="<?=$arResult['SHOW_FIELDS']['NAME']?>" placeholder="<?=GetMessage("USER_NAME")?>" />
                        </div>
                    </div>

                    <div class="field row">
                        <div class="row_head"><?=GetMessage("USER_SECOND_NAME")?></div>
                        <div class="holder row_val">
                            <input type="text" name="SECOND_NAME" maxlength="50" value="<?=$arResult['SHOW_FIELDS']['SECOND_NAME']?>" placeholder="<?=GetMessage("USER_SECOND_NAME")?>"/>
                        </div>
                    </div>

                    <div class="row">
                        <div class="row_head"><?=GetMessage("AUTH_EMAIL")?></div>
                        <div class="holder row_val">
                            <input type="text" data-checkval="y" data-checktype="email" data-stabval="<?=(checkEmailFromPhone($arResult['SHOW_FIELDS']['EMAIL']) ? '' : $arResult['SHOW_FIELDS']['EMAIL']);?>" name="EMAIL" value="<?=(checkEmailFromPhone($arResult['SHOW_FIELDS']['EMAIL']) ? '' : $arResult['SHOW_FIELDS']['EMAIL']);?>" placeholder="<?=GetMessage("AUTH_EMAIL")?>">
                        </div>
                    </div>

                    <?if(isset($arParams['BY_AGENT']) && $arParams['BY_AGENT'] == 'Y'){?>
                        <div class="row phone_no_sms">
                            <div class="row_head"><?=$arResult['SHOW_PROPS_TYPE']['PHONE']['NAME']?></div>
                            <div class="needItem">*</div>
                            <div class="holder row_val">
                                <input type="text" class="phone_msk" name="PROP__PHONE" data-checktype="phone" value="<?=$arResult['SHOW_PROPS']['PHONE']?>" placeholder="<?=$arResult['SHOW_PROPS_TYPE']['PHONE']['NAME']?>" />
                                <div class="clear"></div>
                            </div>
                        </div>
                    <?}else{?>
                        <div class="row phone_sms<?=($arResult['SHOW_FIELDS']['PHONE_NEVER_APPROVED'] ? ' changed' : '');?>">
                            <div class="row_head"><?=$arResult['SHOW_PROPS_TYPE']['PHONE']['NAME']?></div>
                            <div class="needItem">*</div>
                            <div class="holder row_val">
                                <?
                                $phone_val = str_replace(' ', '', $arResult['SHOW_PROPS']['PHONE']);
                                ?>
                                <input type="text" class="phone_msk" name="PROP__PHONE" data-checktype="phone" data-val="<?=($arResult['SHOW_FIELDS']['PHONE_NEVER_APPROVED'] ? 't' : $phone_val);?>" value="<?=$phone_val?>" placeholder="<?=$arResult['SHOW_PROPS_TYPE']['PHONE']['NAME']?>" />
                                <div class="success_ico"></div>
                                <div class="clear"></div>
                            </div>
                        </div>
                    <?}?>

                    <div class="row">
                        <div class="holder row_sub_head"><?=$arResult['SHOW_PROPS_TYPE']['REGION']['NAME']?>:</div>
                        <div class="needItem">*</div>
                        <div class="holder row_val">
                            <select data-search="y" name="PROP__REGION">
                                <option value=""></option>
                                <?
                                if (isset($arResult['SHOW_PROPS_LIST_DATA'][$arResult['SHOW_PROPS_TYPE']['REGION']['LINK_IBLOCK_ID']])) {
                                    foreach($arResult['SHOW_PROPS_LIST_DATA'][$arResult['SHOW_PROPS_TYPE']['REGION']['LINK_IBLOCK_ID']] as $cur_id => $cur_name) {
                                    ?>
                                        <option value="<?=$cur_id;?>" <? if ($cur_id == $arResult['SHOW_PROPS']['REGION']) { ?>selected="selected"<? } ?>><?=$cur_name?></option>
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

                    <input type="hidden" name="PROP__UL_TYPE" value="<?=$arResult['SHOW_PROPS']['UL_TYPE']?>">

                    <div class="row">
                        <div class="row_head"><?=$arResult['SHOW_PROPS_TYPE']['INN']['NAME']?></div>
                        <div class="needItem">*</div>
                        <div class="holder row_val">
                            <?if(isset($arParams['BY_AGENT'])
                                && trim($arResult['SHOW_PROPS']['INN']) != ''
                            ){?>
                                <input type="text" disabled="disabled" name="PROP__INN" value="<?=$arResult['SHOW_PROPS']['INN']?>" placeholder="<?=$arResult['SHOW_PROPS_TYPE']['INN']['NAME']?>" />
                            <?}else{?>
                                <input type="text" name="PROP__INN" value="<?=$arResult['SHOW_PROPS']['INN']?>" placeholder="<?=$arResult['SHOW_PROPS_TYPE']['INN']['NAME']?>" />
                                <input class="empty_but" data-val="Запросить данные по ИНН" type="button" onclick="uploadInn(this);" value="Запросить данные по ИНН" title="Данные об организации заполняются автоматически по ИНН" />
                            <?}?>
                        </div>
                    </div>

                    <div class="row" <? if ($arResult['SHOW_PROPS']['UL_TYPE'] == $arResult['UL_TYPES_LIST']['ip']['ID']) { ?>style="display: none;"<? } ?>>
                        <div class="row_head"><?=$arResult['SHOW_PROPS_TYPE']['FULL_COMPANY_NAME']['NAME']?></div>
                        <div class="holder row_val">
                            <input type="hidden" name="PROP__FULL_COMPANY_NAME" value="<?=str_replace('"', '″', $arResult['SHOW_PROPS']['FULL_COMPANY_NAME'])?>" />
                            <textarea title="Заполняется автоматически после валидации ИНН" name="" class="disabled" readonly="readonly" placeholder="<?=$arResult['SHOW_PROPS_TYPE']['FULL_COMPANY_NAME']['NAME']?>"><?
                                echo ($arResult['SHOW_PROPS']['FULL_COMPANY_NAME'] != '' ? $arResult['SHOW_PROPS_TYPE']['FULL_COMPANY_NAME']['NAME'] . ': ' . $arResult['SHOW_PROPS']['FULL_COMPANY_NAME'] : '');
                                ?></textarea>
                        </div>
                    </div>

                    <div class="row" <? if ($arResult['SHOW_PROPS']['UL_TYPE'] != $arResult['UL_TYPES_LIST']['ip']['ID']) { ?>style="display: none;"<? } ?>>
                        <div class="row_head"><?=$arResult['SHOW_PROPS_TYPE']['IP_FIO']['NAME']?></div>
                        <div class="holder row_val">
                            <input type="hidden" name="PROP__IP_FIO" value="<?=$arResult['SHOW_PROPS']['IP_FIO']?>" />
                            <textarea title="Заполняется автоматически после валидации ИНН" name="" class="disabled" readonly="readonly" placeholder="<?=$arResult['SHOW_PROPS_TYPE']['IP_FIO']['NAME']?>"><?
                                echo ($arResult['SHOW_PROPS']['IP_FIO'] != '' ? $arResult['SHOW_PROPS_TYPE']['IP_FIO']['NAME'] . ': ' . $arResult['SHOW_PROPS']['IP_FIO'] : '');
                                ?></textarea>
                        </div>
                    </div>

                    <input type="hidden" name="PROP__REG_DATE" value="<?=$arResult['SHOW_PROPS']['REG_DATE']?>">

                    <div class="row" <? if ($arResult['SHOW_PROPS']['UL_TYPE'] == $arResult['UL_TYPES_LIST']['ip']['ID']) { ?>style="display: none;"<? } ?>>
                        <div class="row_head"><?=$arResult['SHOW_PROPS_TYPE']['YUR_ADRESS']['NAME']?></div>
                        <div class="holder row_val">
                            <input type="hidden" name="PROP__YUR_ADRESS" value="<?=$arResult['SHOW_PROPS']['YUR_ADRESS']?>" />
                            <textarea title="Заполняется автоматически после валидации ИНН" name="" class="disabled" readonly="readonly" placeholder="<?=$arResult['SHOW_PROPS_TYPE']['YUR_ADRESS']['NAME']?>"><?
                                echo ($arResult['SHOW_PROPS']['YUR_ADRESS'] != '' ? $arResult['SHOW_PROPS_TYPE']['YUR_ADRESS']['NAME'] . ': ' . $arResult['SHOW_PROPS']['YUR_ADRESS'] : '');
                                ?></textarea>
                        </div>
                    </div>

                    <div class="row">
                        <div class="row_head"><?=$arResult['SHOW_PROPS_TYPE']['POST_ADRESS']['NAME']?></div>
                        <div class="holder row_val">
                            <input type="text" name="PROP__POST_ADRESS" value="<?=$arResult['SHOW_PROPS']['POST_ADRESS']?>" placeholder="<?=$arResult['SHOW_PROPS_TYPE']['POST_ADRESS']['NAME']?>" />
                        </div>
                    </div>

                    <div class="row" <? if ($arResult['SHOW_PROPS']['UL_TYPE'] == $arResult['UL_TYPES_LIST']['ip']['ID']) { ?>style="display: none;"<? } ?>>
                        <div class="radio_group">
                            <div class="radio_area">
                                <input type="checkbox" <?if(isset($_POST['is_yur_adres_checkbox']) && $_POST['is_yur_adres_checkbox'] == 'y'){?>checked="checked"<?}?> data-text="соответствует юридическому адресу" name="is_yur_adres_checkbox" id="is_yur_adres_f" class="is_yur_adres" value="y"/>
                            </div>
                        </div>
                    </div>

                    <div class="row" <? if ($arResult['SHOW_PROPS']['UL_TYPE'] == $arResult['UL_TYPES_LIST']['ip']['ID']) { ?>style="display: none;"<? } ?>>
                        <div class="row_head"><?=$arResult['SHOW_PROPS_TYPE']['KPP']['NAME']?></div>
                        <div class="holder row_val">
                            <input type="hidden" name="PROP__KPP" value="<?=$arResult['SHOW_PROPS']['KPP']?>" />
                            <input type="text" title="Заполняется автоматически после валидации ИНН" name="" class="disabled" readonly="readonly" value="<?=($arResult['SHOW_PROPS']['KPP'] ? $arResult['SHOW_PROPS_TYPE']['KPP']['NAME'] . ': ' . $arResult['SHOW_PROPS']['KPP'] : '');?>" placeholder="<?=$arResult['SHOW_PROPS_TYPE']['KPP']['NAME']?>" />
                        </div>
                    </div>

                    <div class="row">
                        <div class="row_head"><?=$arResult['SHOW_PROPS_TYPE']['OGRN']['NAME']?></div>
                        <div class="holder row_val">
                            <input type="hidden" name="PROP__OGRN" value="<?=$arResult['SHOW_PROPS']['OGRN']?>" />
                            <input type="text" title="Заполняется автоматически после валидации ИНН" name="" class="disabled" readonly="readonly" value="<?=($arResult['SHOW_PROPS']['KPP'] ? $arResult['SHOW_PROPS_TYPE']['OGRN']['NAME'] . ': ' . $arResult['SHOW_PROPS']['OGRN'] : '');?>" placeholder="<?=$arResult['SHOW_PROPS_TYPE']['OGRN']['NAME']?>" />
                        </div>
                    </div>

                    <div class="row">
                        <div class="row_head"><?=$arResult['SHOW_PROPS_TYPE']['OKPO']['NAME']?></div>
                        <div class="holder row_val">
                            <input type="hidden" name="PROP__OKPO" value="<?=$arResult['SHOW_PROPS']['OKPO']?>" />
                            <input type="text" title="Заполняется автоматически после валидации ИНН" name="" class="disabled" readonly="readonly" value="<?=($arResult['SHOW_PROPS']['KPP'] ? $arResult['SHOW_PROPS_TYPE']['OKPO']['NAME'] . ': ' . $arResult['SHOW_PROPS']['OKPO'] : '');?>" placeholder="<?=$arResult['SHOW_PROPS_TYPE']['OKPO']['NAME']?>" />
                        </div>
                    </div>

                    <div class="row" <? if ($arResult['SHOW_PROPS']['UL_TYPE'] == $arResult['UL_TYPES_LIST']['ip']['ID']) { ?>style="display: none;"<? } ?>>
                        <div class="row_head"><?=$arResult['SHOW_PROPS_TYPE']['FIO_DIR']['NAME']?></div>
                        <div class="holder row_val">
                            <input type="text" name="PROP__FIO_DIR" value="<?=$arResult['SHOW_PROPS']['FIO_DIR']?>" placeholder="<?=$arResult['SHOW_PROPS_TYPE']['FIO_DIR']['NAME']?>" />
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
                                            <input type="radio" class="ch_signer" name="signer[ul]" data-text="<?=$signer['NAME']?>" data-code="ul_<?=$signer['CODE']?>" id="s<?=$signer['ID']?>" value="<?=$signer['ID']?>" <? if ($k == 0) { ?>checked="checked"<? } ?>>
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
                                            <input type="radio" class="ch_signer" name="signer[ip]" data-text="<?=$signer['NAME']?>" data-code="ip_<?=$signer['CODE']?>" id="s<?=$signer['ID']?>" value="<?=$signer['ID']?>" <? if ($k == 0) { ?>checked="checked"<? } ?>>
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
                            <?=$arResult['SHOW_PROPS_TYPE']['OSNOVANIE_PRAVA_PODPISI_FILE']['NAME']?>:
                        </div>
                        <div class="holder row_val">
                            <input type="file" name="PROP__OSNOVANIE_PRAVA_PODPISI_FILE"/>
                        </div>
                    </div>


                    <div class="field row nds">
                        <div class="row_head"><?=$arResult['SHOW_PROPS_TYPE']['NDS']['NAME']?></div>
                        <div class="holder row_val">
                            <?/*<input type="text" name="" value="<?=$arResult['SHOW_PROPS_LIST_DATA'][$arResult['SHOW_PROPS_TYPE']['NDS']['LINK_IBLOCK_ID']][$arResult['SHOW_PROPS']['NDS']]?>" disabled="disabled">*/?>
                            <select data-search="n" name="TYPE_NDS" <?=($arResult['CHANGE_NDS']['LOCK']) ? 'disabled="disabled"' : ''?>>
                                <option></option>
                                <?foreach ($arResult['SHOW_PROPS_LIST_DATA'][$arResult['SHOW_PROPS_TYPE']['NDS']['LINK_IBLOCK_ID']] as $sKey => $sType):?>
                                    <?$sSelected = ($sKey == $arResult['SHOW_PROPS']['NDS']) ? 'selected="selected"' : '';?>
                                    <option <?=$sSelected?> value="<?=$sKey?>"><?=$sType?></option>
                                <?endforeach;?>
                            </select>
                            <?if(!empty($arResult['CHANGE_NDS']['MSG'])):?>
                                <div class="row_error_msg">Нет возможности изменить тип НДС: "<?=$arResult['CHANGE_NDS']['MSG']?>"</div>
                            <?endif;?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="holder row_sub_head">Банковские реквизиты компании:</div>
                    </div>

                    <div class="row">
                        <div class="row_head"><?=$arResult['SHOW_PROPS_TYPE']['BIK']['NAME']?></div>
                        <div class="holder row_val">
                            <input type="text" name="PROP__BIK" value="<?=$arResult['SHOW_PROPS']['BIK']?>" placeholder="<?=$arResult['SHOW_PROPS_TYPE']['BIK']['NAME']?>" />
                            <input class="empty_but" data-val="Запросить данные по БИК" type="button" onclick="uploadBic(this);" value="Запросить данные по БИК" title="Данные о банковской организации заполняются автоматически по БИК" />
                        </div>
                    </div>

                    <div class="row">
                        <div class="row_head"><?=$arResult['SHOW_PROPS_TYPE']['BANK']['NAME']?></div>
                        <div class="holder row_val">
                            <input type="text" name="PROP__BANK" value="<?=$arResult['SHOW_PROPS']['BANK']?>" placeholder="<?=$arResult['SHOW_PROPS_TYPE']['BANK']['NAME']?>" />
                        </div>
                    </div>

                    <div class="row">
                        <div class="row_head"><?=$arResult['SHOW_PROPS_TYPE']['RASCH_SCHET']['NAME']?></div>
                        <div class="holder row_val">
                            <input type="text" name="PROP__RASCH_SCHET" value="<?=$arResult['SHOW_PROPS']['RASCH_SCHET']?>" placeholder="<?=$arResult['SHOW_PROPS_TYPE']['RASCH_SCHET']['NAME']?>" />
                        </div>
                    </div>

                    <div class="row">
                        <div class="row_head"><?=$arResult['SHOW_PROPS_TYPE']['KOR_SCHET']['NAME']?></div>
                        <div class="holder row_val">
                            <input type="text" name="PROP__KOR_SCHET" value="<?=$arResult['SHOW_PROPS']['KOR_SCHET']?>" placeholder="<?=$arResult['SHOW_PROPS_TYPE']['KOR_SCHET']['NAME']?>" />
                        </div>
                    </div>

                    <?if(isset($arParams['BY_AGENT']) && $arParams['BY_AGENT'] == 'Y'){?>
                        <div class="row policy_row">
                            <div class="field field-option radio_group">
                                <div class="radio_area">
                                    <input type="checkbox" data-text="<?=GetMessage("AUTH_REGISTER_CONFIM_BY_AGENT");?>" name="AUTH_REG_CONFIM_BY_AGENT" value="Y" />
                                </div>
                            </div>
                        </div>
                    <?}else{?>
                        <div class="row">
                            <div class="holder row_sub_head">Для сохранений данных введите ваш текущий пароль:</div>
                        </div>

                        <div class="row">
                            <div class="needItem">*</div>
                            <div class="holder row_val">
                                <input type="password" name="PASSWORD" value="" placeholder="Текущий пароль" />
                            </div>
                        </div>
                    <?}?>
                    <?if(isset($arParams['DEMO']) && $arParams['DEMO'] == 'Y'){?>
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
                </div>
            </div>

            <div class="content-form profile-form row">
                <input name="save" value="Сохранить настройки профиля" class="input-submit submit-btn" type="submit">
            </div>
        </form>
    </div>
<?
}
?>