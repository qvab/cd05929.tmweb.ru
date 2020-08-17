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

CJSCore::Init(array('translit'));

if($arResult['ERROR'] == 'Y')
{
    echo '<div class="err_text row">' . $arResult['ERROR_MESSAGE'] . '</div>';
}
else
{
    if (is_array($arResult['success_text']) && sizeof($arResult['success_text']) > 0){
        foreach ($arResult['success_text'] as $cur_type => $val) {
        ?>
            <div class="success_text" style="padding: 10px 0; color: <?if($cur_type=='error'){?>#c10000<?}else{?>#00c100<?}?>"><?=$val?></div>
        <?
        }
    }
    ?>
        <form class="add_form" action="" method="POST">
            <input type="hidden" name="add_client" value="y" />
            <div class="form_area">
                <?if(isset($arResult['error_text']) && $arResult['error_text'] != ''){?>
                    <div class="err_text row">
                        <?=$arResult['error_text'];?>
                    </div>
                <?}?>
                <div class="line row">
                    <div class="row_head">Название (будет отображаться только вам):</div>
                    <div class="row_val one_line">
                        <input placeholder="Название" type="text" name="nick" value="<?=(!isset($arResult['success_text'][0]) && isset($_POST['nick']) ? $_POST['nick'] : '');?>" />
                    </div>
                </div>

                <div class="line row">
                    <div class="row_val one_line">
                        <div class="needItem">*</div>
                        <input placeholder="Фамилия" type="text" name="last_name" value="<?=(!isset($arResult['success_text'][0]) && isset($_POST['last_name']) ? $_POST['last_name'] : '');?>" />
                    </div>
                </div>

                <div class="line row">
                    <div class="row_val one_line">
                        <div class="needItem">*</div>
                        <input placeholder="Имя" type="text" name="name" value="<?=(!isset($arResult['success_text'][0]) && isset($_POST['name']) ? $_POST['name'] : '');?>" />
                    </div>
                </div>

                <div class="line row">
                    <div class="row_val one_line">
                        <input placeholder="Отчество" type="text" name="second_name" value="<?=(!isset($arResult['success_text'][0]) && isset($_POST['second_name']) ? $_POST['second_name'] : '');?>" />
                    </div>
                </div>

                <div class="line row">
                    <div class="row_val one_line">
                        <input class="" placeholder="Email" data-checkval="y" data-checktype="email" type="text" name="email" value="<?=(!isset($arResult['success_text'][0]) && isset($_POST['email']) ? trim($_POST['email']) . '" data-stabval="' . trim($_POST['email']) : '');?>" />
                    </div>
                </div>

                <div class="line row">
                    <div class="row_val one_line">
                        <div class="needItem">*</div>
                        <input class="phone_msk" placeholder="Телефон" type="text" name="phone" value="<?=(!isset($arResult['success_text'][0]) && isset($_POST['phone']) ? $_POST['phone'] : '');?>" />
                    </div>
                </div>

                <?if(isset($arResult['REGIONS'])
                    && is_array($arResult['REGIONS'])
                    && count($arResult['REGIONS']) > 0
                ){?>
                    <div class="line row">
                        <div class="row_val one_line">
                            <div class="needItem">*</div>
                            <select name="region" data-search="y">
                                <option value="">Регион</option>
                                <?foreach($arResult['REGIONS'] as $cur_id => $cur_name) {
                                    ?>
                                    <option value="<?=$cur_id;?>" <?=(isset($_POST['region']) && $_POST['region'] == $cur_id ? 'selected="selected"' : '')?>><?=$cur_name;?></option>
                                    <?
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                <?}?>

                <div class="line row inn_row<?=(isset($_POST['PROP__INN']) && isset($_SESSION['success_inn_' . $_POST['PROP__INN']]) ? ' check_success' : '');?>">
                    <div class="holder row_val">
                        <div class="needItem">*</div>
                        <input type="text" placeholder="ИНН" class="<?if(isset($_POST['PROP__INN']) && isset($_SESSION['success_inn_' . $_POST['PROP__INN']]) ? ' check_success' : ''){?> check_success" readonly="readonly<?}?>" name="PROP__INN" data-checktype="pos_int_empty" data-checkval="y" value="<?=(!isset($arResult['success_text'][0]) && isset($_POST['PROP__INN']) ? $_POST['PROP__INN'] : '');?>" />
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
                        <input class="empty_but" data-val="Запросить данные по ИНН" type="button" onclick="uploadInnAdd(this);" value="Проверка ИНН" title="Данные об организации заполняются автоматически по ИНН">
                        <div class="clear"></div>
                    </div>
                </div>

                <div class="line row">
                    <div class="row_head"><span class="required" style="color: #c10000">*</span> С НДС/без НДС</div>
                    <div class="row_val one_line">
                        <select name="nds_value">
                            <option value="0">Не выбрано</option>
                            <?foreach($arResult['NDS_LIST'] as $cur_data){?>
                                <option <?if(!isset($arResult['success_text'][0]) && isset($_POST['nds_value']) && $_POST['nds_value'] == $cur_data['ID']){?> selected="selected" <?}?> value="<?=$cur_data['ID'];?>"><?=$cur_data['NAME'];?></option>
                            <?}?>
                        </select>
                    </div>
                </div>

                <div class="row policy_row">
                    <div class="field field-option radio_group">
                        <div class="radio_area">
                            <input type="checkbox" data-text="<a href='javascript: void(0);' onclick='showAgentPolicy();' class='checkbox_href'>Настоящим подтверждаю, что в случае регистрации мною на сайте документов на третьих лиц, предоставляю персональные данные с их согласия</a>" name="AUTH_REG_CONFIM_BY_AGENT" value="Y" />
                        </div>
                    </div>
                </div>

                <div class="line row">
                    <input class="submit-btn inactive" type="button" value="Добавить" />
                </div>
            </div>
        </form>
    <?
}