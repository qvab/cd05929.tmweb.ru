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
        if(isset($arParams['BY_AGENT']) && $arParams['BY_AGENT'] == 'Y'){
            echo '<div class="success">Данные пользователя успешно сохранены</div>';
        }else{
            echo '<div class="success">Ваши данные успешно сохранены</div>';
        }
    }
    ?>
    <form method="post" name="profile_form" action="" enctype="multipart/form-data">
        <input type="hidden" name="update" value="y" />
        <input type="hidden" name="user_type" value="<?=$arResult['USER_UL_TYPE']?>" />
        <div class="content-form profile-form">
            <div class="fields">
                <?
                if ($arParams['TYPE'] == 1) {
                ?>
                    <div class="row">
                        <div class="holder row_sub_head">Данные пользователя:</div>
                    </div>

                    <div class="field row">
                        <div class="row_head">Имя</div>
                        <div class="row_val">
                            <input type="text" name="NAME" maxlength="50" value="<?=$arResult['SHOW_FIELDS']['NAME']?>">
                        </div>
                    </div>

                    <div class="field row">
                        <div class="row_head">Фамилия</div>
                        <div class="row_val">
                            <input type="text" name="LAST_NAME" maxlength="50" value="<?=$arResult['SHOW_FIELDS']['LAST_NAME']?>">
                        </div>
                    </div>

                    <div class="field row">
                        <div class="row_head">Отчество</div>
                        <div class="row_val">
                            <input type="text" name="SECOND_NAME" maxlength="50" value="<?=$arResult['SHOW_FIELDS']['SECOND_NAME']?>">
                        </div>
                    </div>

                    <?if(!checkEmailFromPhone($arResult['SHOW_FIELDS']['EMAIL'])){?>
                    <div class="field row">
                        <div class="row_head">E-mail</div>
                        <div class="row_val">
                            <input type="text" name="" maxlength="50" value="<?=$arResult['SHOW_FIELDS']['EMAIL']?>" disabled="disabled">
                        </div>
                    </div>
                    <?}?>

                    <div class="row phone_sms<?=($arResult['SHOW_FIELDS']['PHONE_NEVER_APPROVED'] ? ' changed' : '');?>">
                        <div class="row_head">Телефон</div>
                        <div class="holder row_val">
                            <?
                            $phone_val = str_replace(' ', '', $arResult['SHOW_PROPS']['PHONE']);
                            ?>
                            <input type="text" name="PROP__PHONE" data-phone="PROP__PHONE" data-val="<?=($arResult['SHOW_FIELDS']['PHONE_NEVER_APPROVED'] ? 't' : $phone_val);?>" class="phone_msk" value="<?=$phone_val;?>" placeholder="Телефон" />
                            <div class="success_ico"></div>
                            <div class="clear"></div>
                        </div>
                    </div>

                    <div class="field row">
                        <div class="row_head"><?=$arResult['SHOW_PROPS_TYPE']['REGION']['NAME']?></div>
                        <div class="row_val">
                            <select <? if (count($arResult['SHOW_PROPS_LIST_DATA'][$arResult['SHOW_PROPS_TYPE']['REGION']['LINK_IBLOCK_ID']]) > 3){?>data-search="y"<?}?> name="PROP__REGION">
                                <?
                                if (count($arResult['SHOW_PROPS_LIST_DATA'][$arResult['SHOW_PROPS_TYPE']['REGION']['LINK_IBLOCK_ID']]) < 4){
                                ?>
                                    <option></option>
                                <?
                                }

                                foreach ($arResult['SHOW_PROPS_LIST_DATA'][$arResult['SHOW_PROPS_TYPE']['REGION']['LINK_IBLOCK_ID']] as $cur_id => $cur_list_name) {
                                ?>
                                    <option value="<?=$cur_id?>" <?if($cur_id == $arResult['SHOW_PROPS']['REGION']){?>selected="selected"<?}?> ><?=$cur_list_name;?></option>
                                <?
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="holder row_sub_head">Данные компании:</div>
                    </div>

                    <div class="field row">
                        <div class="row_head"><?=$arResult['SHOW_PROPS_TYPE']['INN']['NAME']?></div>
                        <div class="row_val">
                            <input type="text" name="PROP__INN" value="<?=$arResult['SHOW_PROPS']['INN']?>" disabled="disabled" >
                        </div>
                    </div>

                    <?
                    if ($arResult['USER_UL_TYPE'] == 'ul') {
                    ?>
                        <div class="field row">
                            <div class="row_head"><?=$arResult['SHOW_PROPS_TYPE']['FULL_COMPANY_NAME']['NAME']?></div>
                            <div class="row_val">
                                <textarea name="PROP__FULL_COMPANY_NAME"><?=$arResult['SHOW_PROPS']['FULL_COMPANY_NAME']?></textarea>
                            </div>
                        </div>

                        <div class="field row">
                            <div class="row_head"><?=$arResult['SHOW_PROPS_TYPE']['YUR_ADRESS']['NAME']?></div>
                            <div class="row_val">
                                <textarea name="PROP__YUR_ADRESS"><?=$arResult['SHOW_PROPS']['YUR_ADRESS']?></textarea>
                            </div>
                        </div>
                    <?
                    }
                    elseif ($arResult['USER_UL_TYPE'] == 'ip') {
                    ?>
                        <div class="field row">
                            <div class="row_head"><?=$arResult['SHOW_PROPS_TYPE']['IP_FIO']['NAME']?></div>
                            <div class="row_val">
                                <input type="text" name="PROP__IP_FIO" value="<?=$arResult['SHOW_PROPS']['IP_FIO']?>">
                            </div>
                        </div>
                    <?
                    }
                    ?>

                    <div class="field row">
                        <div class="row_head"><?=$arResult['SHOW_PROPS_TYPE']['POST_ADRESS']['NAME']?></div>
                        <div class="row_val">
                            <textarea name="PROP__POST_ADRESS"><?=$arResult['SHOW_PROPS']['POST_ADRESS']?></textarea>
                        </div>
                    </div>

                    <?
                    if ($arResult['USER_UL_TYPE'] == 'ul') {
                    ?>
                        <div class="field row">
                            <div class="row_head"><?=$arResult['SHOW_PROPS_TYPE']['KPP']['NAME']?></div>
                            <div class="row_val">
                                <input type="text" name="PROP__KPP" value="<?=$arResult['SHOW_PROPS']['KPP']?>">
                            </div>
                        </div>
                    <?
                    }
                    ?>

                    <div class="field row">
                        <div class="row_head"><?=$arResult['SHOW_PROPS_TYPE']['OGRN']['NAME']?></div>
                        <div class="row_val">
                            <input type="text" name="PROP__OGRN" value="<?=$arResult['SHOW_PROPS']['OGRN']?>">
                        </div>
                    </div>

                    <div class="field row">
                        <div class="row_head"><?=$arResult['SHOW_PROPS_TYPE']['OKPO']['NAME']?></div>
                        <div class="row_val">
                            <input type="text" name="PROP__OKPO" value="<?=$arResult['SHOW_PROPS']['OKPO']?>">
                        </div>
                    </div>

                    <?
                    if ($arResult['USER_UL_TYPE'] == 'ul') {
                    ?>
                        <div class="field row">
                            <div class="row_head"><?=$arResult['SHOW_PROPS_TYPE']['FIO_DIR']['NAME']?></div>
                            <div class="row_val">
                                <input type="text" name="PROP__FIO_DIR" value="<?=$arResult['SHOW_PROPS']['FIO_DIR']?>">
                            </div>
                        </div>
                    <?
                    }
                    ?>

                    <div class="field row nds">
                        <div class="row_head"><?=$arResult['SHOW_PROPS_TYPE']['NDS']['NAME']?></div>
                        <div class="row_val">
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

                    <?
                    if ($arResult['USER_UL_TYPE'] == 'ul') {
                    ?>
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
                                        foreach ($arResult['SIGNERS']['ul'] as $signer) {
                                            $checked = false;
                                            if ($signer['ID'] == $arResult['SHOW_PROPS']['SIGNER']) {
                                                $checked = true;
                                                $signerCode = $signer['CODE'];
                                            }
                                            ?>
                                            <div class="radio_area">
                                                <input type="radio" class="ch_signer" name="signer[ul]" data-text="<?=$signer['NAME']?>" data-code="ul_<?=$signer['CODE']?>" id="s<?=$signer['ID']?>" value="<?=$signer['ID']?>" <? if ($checked) { ?>checked="checked"<? } ?>>
                                            </div>
                                        <?
                                        }
                                        ?>
                                    </div>
                                </div>

                                <div class="sign_block signer_ul_dir<? if ($signerCode != 'dir') { ?> no_active<? } ?>" <? if ($signerCode == 'dir') { ?>style="display: block;"<? } ?>>
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
                                                        $selected = false;
                                                        if ($item['ID'] == $arResult['SHOW_PROPS']['FOUND']) {
                                                            $selected = true;
                                                        }
                                                        ?>
                                                        <option value="<?=$item['ID']?>" <? if ($selected) { ?>selected="selected"<? } ?>><?=$item['NAME']?></option>
                                                    <?
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="sign_block signer_ul_gendir<? if ($signerCode != 'gendir') { ?> no_active<? } ?>" <? if ($signerCode == 'gendir') { ?>style="display: block;"<? } ?>>
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
                                                        $selected = false;
                                                        if ($item['ID'] == $arResult['SHOW_PROPS']['FOUND']) {
                                                            $selected = true;
                                                        }
                                                        ?>
                                                        <option value="<?=$item['ID']?>" <? if ($selected) { ?>selected="selected"<? } ?>><?=$item['NAME']?></option>
                                                    <?
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="sign_block signer_ul_sign<? if ($signerCode != 'sign') { ?> no_active<? } ?>" <? if ($signerCode == 'sign') { ?>style="display: block;"<? } ?>>
                                    <div class="row">
                                        <div class="holder row_val">
                                            <input type="text" name="fio[ul][sign]" value="<?=$arResult['SHOW_PROPS']['FIO_SIGN']?>" placeholder="ФИО">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="holder row_val">
                                            <input type="text" name="post[ul][sign]" value="<?=$arResult['SHOW_PROPS']['POST']?>" placeholder="Должность">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <input type="hidden" name="found[ul][sign]" value="<?=$arResult['FOUND']['doverennost']['ID']?>">
                                        <div class="holder row_sub_head">Действует на основании <?=$arResult['FOUND']['doverennost']['PROPERTY_CHEGO_VALUE']?></div>
                                    </div>
                                    <div class="row">
                                        <div class="holder row_val">
                                            №<input type="text" class="signer_found_num" name="num[ul][sign]" value="<?=$arResult['SHOW_PROPS']['FOUND_NUM']?>" placeholder="" />
                                            от<input type="text" class="signer_found_date" autocomplete="off" name="date[ul][sign]" value="<?=$arResult['SHOW_PROPS']['FOUND_DATE']?>" onclick="var tmp_val = this.value; this.value = ''; BX.calendar({value: tmp_val, node: this, field: this, bTime: false, callback_after: goTriggerChange(this)}); changeCalendar();" placeholder="" />
                                        </div>
                                    </div>
                                </div>
                            <?
                            }
                            ?>
                        </div>
                    <?
                    }

                    if ($arResult['USER_UL_TYPE'] == 'ip') {
                    ?>
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
                                        foreach ($arResult['SIGNERS']['ip'] as $signer) {
                                            $checked = false;
                                            if ($signer['ID'] == $arResult['SHOW_PROPS']['SIGNER']) {
                                                $checked = true;
                                                $signerCode = $signer['CODE'];
                                            }
                                            ?>
                                            <div class="radio_area">
                                                <input type="radio" class="ch_signer" name="signer[ip]" data-text="<?=$signer['NAME']?>" data-code="ip_<?=$signer['CODE']?>" id="s<?=$signer['ID']?>" value="<?=$signer['ID']?>" <? if ($checked) { ?>checked="checked"<? } ?>>
                                            </div>
                                        <?
                                        }
                                        ?>
                                    </div>
                                </div>

                                <div class="sign_block signer_ip_ip<? if ($signerCode != 'ip') { ?> no_active<? } ?>" <? if ($signerCode == 'ip') { ?>style="display: block;"<? } ?>>
                                    <input type="hidden" name="post[ip][ip]" value="">
                                    <input type="hidden" name="num[ip][ip]" value="">
                                    <input type="hidden" name="date[ip][ip]" value="">
                                    <div class="row">
                                        <input type="hidden" name="found[ip][ip]" value="<?=$arResult['FOUND']['svidetelstvo']['ID']?>">
                                        <div class="holder row_sub_head">Действует на основании <?=$arResult['FOUND']['svidetelstvo']['PROPERTY_CHEGO_VALUE']?></div>
                                    </div>
                                </div>

                                <div class="sign_block signer_ip_sign<? if ($signerCode != 'sign') { ?> no_active<? } ?>" <? if ($signerCode == 'sign') { ?>style="display: block;"<? } ?>>
                                    <div class="row">
                                        <div class="holder row_val">
                                            <input type="text" name="fio[ip][sign]" value="<?=$arResult['SHOW_PROPS']['FIO_SIGN']?>" placeholder="ФИО">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="holder row_val">
                                            <input type="text" name="post[ip][sign]" value="<?=$arResult['SHOW_PROPS']['POST']?>" placeholder="Должность">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <input type="hidden" name="found[ip][sign]" value="<?=$arResult['FOUND']['doverennost']['ID']?>">
                                        <div class="holder row_sub_head">Действует на основании <?=$arResult['FOUND']['doverennost']['PROPERTY_CHEGO_VALUE']?></div>
                                    </div>
                                    <div class="row">
                                        <div class="holder row_val">
                                            №<input type="text" class="signer_found_num" name="num[ip][sign]" value="<?=$arResult['SHOW_PROPS']['FOUND_NUM']?>" placeholder="" />
                                            от<input type="text" class="signer_found_date" autocomplete="off" name="date[ip][sign]" value="<?=$arResult['SHOW_PROPS']['FOUND_DATE']?>" onclick="var tmp_val = this.value; this.value = ''; BX.calendar({value: tmp_val, node: this, field: this, bTime: false, callback_after: goTriggerChange(this)}); changeCalendar();" placeholder="" />
                                        </div>
                                    </div>
                                </div>
                            <?
                            }
                            ?>
                        </div>
                    <?
                    }
                    ?>

                    <?
                    $cur_file = array();
                    if(is_numeric($arResult['SHOW_PROPS']['OSNOVANIE_PRAVA_PODPISI_FILE'])) {
                        $res = CFile::GetByID($arResult['SHOW_PROPS']['OSNOVANIE_PRAVA_PODPISI_FILE']);
                        if ($cur_file = $res->Fetch()) {
                            $temp_path = CFile::GetPath($arResult['SHOW_PROPS']['OSNOVANIE_PRAVA_PODPISI_FILE']);
                            if ($temp_path) {
                                $cur_file['f_src'] = $temp_path;
                            }
                        }
                    }

                    $name = $arResult['SHOW_PROPS_TYPE']['OSNOVANIE_PRAVA_PODPISI_FILE']['NAME'];
                    $class = '';
                    ?>
                    <div class="field row">
                        <div class="row_head"><?=$name?></div>
                        <div class="row_val">
                            <?
                            if(isset($cur_file['f_src'])) {
                                if (file_exists($_SERVER['DOCUMENT_ROOT'] . $cur_file['f_src'])) {
                                    $class = '';
                                    ?>
                                    <div class="get_file">
                                        Скачать текущий файл можно <a download="<?=$cur_file['ORIGINAL_NAME'];?>" href="<?=$cur_file['f_src'];?>">по ссылке</a>
                                    </div>
                                <?
                                }
                                else {
                                ?>
                                    <div class="get_file">Ошибка загруженного файла (требуется заменить файл, загрузив новый)</div>
                                <?
                                }
                            }
                            ?>
                            <input type="file" <?if(isset($cur_file['f_src'])){?>data-text="Заменить файл"<?}?> name="PROP__OSNOVANIE_PRAVA_PODPISI_FILE" class="<?=$class?>" />
                        </div>
                    </div>

                    <div class="row">
                        <div class="holder row_sub_head">Банковские реквизиты компании:</div>
                    </div>

                    <div class="field row">
                        <div class="row_head"><?=$arResult['SHOW_PROPS_TYPE']['BANK']['NAME']?></div>
                        <div class="row_val">
                            <input type="text" name="PROP__BANK" value="<?=$arResult['SHOW_PROPS']['BANK']?>">
                        </div>
                    </div>

                    <div class="field row">
                        <div class="row_head"><?=$arResult['SHOW_PROPS_TYPE']['BIK']['NAME']?></div>
                        <div class="row_val">
                            <input type="text" name="PROP__BIK" value="<?=$arResult['SHOW_PROPS']['BIK']?>">
                        </div>
                    </div>

                    <div class="field row">
                        <div class="row_head"><?=$arResult['SHOW_PROPS_TYPE']['RASCH_SCHET']['NAME']?></div>
                        <div class="row_val">
                            <input type="text" name="PROP__RASCH_SCHET" value="<?=$arResult['SHOW_PROPS']['RASCH_SCHET']?>">
                        </div>
                    </div>

                    <div class="field row">
                        <div class="row_head"><?=$arResult['SHOW_PROPS_TYPE']['KOR_SCHET']['NAME']?></div>
                        <div class="row_val">
                            <input type="text" name="PROP__KOR_SCHET" value="<?=$arResult['SHOW_PROPS']['KOR_SCHET']?>">
                        </div>
                    </div>
                <?
                }
                elseif ($arParams['TYPE'] == 2) {
                    foreach ($arResult['SHOW_PROPS'] as $cur_code => $cur_val) {
                        if ($arResult['SHOW_PROPS_TYPE'][$cur_code]['PROPERTY_TYPE'] == 'F') {
                            $cur_file = array();
                            if (is_numeric($cur_val)) {
                                $res = CFile::GetByID($cur_val);
                                if ($cur_file = $res->Fetch()) {
                                    $temp_path = CFile::GetPath($cur_val);
                                    if ($temp_path) {
                                        $cur_file['f_src'] = $temp_path;
                                    }
                                }
                            }

                            if ($arResult['DOCS_LIST']['PROPERTY_' . $cur_code]['PROPERTY_NAME_VALUE'] != '')
                                $name = $arResult['DOCS_LIST']['PROPERTY_' . $cur_code]['PROPERTY_NAME_VALUE'];
                            else
                                $name = $arResult['SHOW_PROPS_TYPE'][$cur_code]['NAME'];
                            ?>
                            <div class="field row">
                                <div class="row_head"><?=$name?></div>
                                <div class="row_val">
                                    <?
                                    if(isset($cur_file['f_src'])) {
                                        if (file_exists($_SERVER['DOCUMENT_ROOT'] . $cur_file['f_src'])) {
                                        ?>
                                            <div class="get_file">
                                                Скачать текущий файл можно <a download="<?=$cur_file['ORIGINAL_NAME'];?>" href="<?=$cur_file['f_src'];?>">по ссылке</a>
                                            </div>
                                        <?
                                        }
                                        else {
                                        ?>
                                            <div class="get_file">Ошибка загруженного файла (требуется заменить файл, загрузив новый)</div>
                                        <?
                                        }
                                    }
                                    ?>
                                    <input type="file" <?if(isset($cur_file['f_src'])){?>data-text="Заменить файл"<?}?> name="PROP__<?=$cur_code?>" />
                                </div>
                            </div>
                        <?
                        }
                    }
                }
                ?>
            </div>
        </div>

        <div class="content-form profile-form row">
            <input name="save" value="Сохранить настройки профиля" class="input-submit submit-btn" type="submit">
        </div>
    </form>
<?
}
?>