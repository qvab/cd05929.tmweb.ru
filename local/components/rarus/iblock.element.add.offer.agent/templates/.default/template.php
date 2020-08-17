<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
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
$this->setFrameMode(false);
?>
<?
$assetObj = \Bitrix\Main\Page\Asset::getInstance();
$assetObj->addJs($this->GetFolder() . '/js/script.js');

if (!empty($arResult["ERRORS"])) {
?>
    <?ShowError(implode("<br />", $arResult["ERRORS"]))?>
<?
}

if (strlen($arResult["MESSAGE"]) > 0) {
?>
    <?ShowNote($arResult["MESSAGE"])?>
<?
}
?>
<a class="go_back cross" href="<?=$arResult['BACK_URL']?>"></a>
<form name="iblock_add" class="offer_add" action="<?=POST_FORM_ACTION_URI?>" method="post" enctype="multipart/form-data">
    <?=bitrix_sessid_post()?>
    <input type="hidden" name="save" value="Y">
    <input type="hidden" name="back_url" value="<?=$arResult['BACK_URL']?>">
    <div class="farmer_filter request-block1 row">
        <div class="row_val label">Выберите поставщика:</div>
        <div class="row_val">
            <select <?if(count($arResult['FARMERS_DATA']) > 4){?>data-search="y"<?}?> name="farmer_filter">
                <option value="0">Все</option>
                <?foreach($arResult['FARMERS_DATA'] as $cur_id => $cur_data){
                    $limit_val = 0;
                    if(isset($arResult['LIMITS_DATA']['USERS'][$cur_id]['REMAINS'])
                        && $arResult['LIMITS_DATA']['USERS'][$cur_id]['REMAINS'] > 0
                    ){
                        $limit_val = $arResult['LIMITS_DATA']['USERS'][$cur_id]['REMAINS'];
                    }

                    if($cur_data['NICK'] != ''){?>
                        <option <?if($arResult["SELECTED_FARMER"] != '' && $arResult['SELECTED_FARMER'] == $cur_id){?>selected="selected"<?}?> data-limit="<?=$limit_val;?>" value="<?=$cur_id;?>"><?=$cur_data['NICK'];?></option>
                    <?}elseif($cur_data['NAME'] != ''){?>
                        <option <?if($arResult["SELECTED_FARMER"] != '' && $arResult['SELECTED_FARMER'] == $cur_id){?>selected="selected"<?}?> data-limit="<?=$limit_val;?>" value="<?=$cur_id;?>"><?=$cur_data['NAME'];?><?if(!checkEmailFromPhone($cur_data['EMAIL'])){?> (<?=$cur_data['EMAIL'];?>)<?}?></option>
                    <?}else{?>
                        <option <?if($arResult["SELECTED_FARMER"] != '' && $arResult['SELECTED_FARMER'] == $cur_id){?>selected="selected"<?}?> data-limit="<?=$limit_val;?>" value="<?=$cur_id;?>"><?=(!checkEmailFromPhone($cur_data['EMAIL']) ? $cur_data['EMAIL'] : $cur_id);?></option>
                    <?}?>
                <?}?>
            </select>
        </div>
    </div>


    <div class="request-block1 row agent_sec">
        <div class="step-title row_head">1. Выберите тип товара</div>
        <div class="row_val">
            <div class="radio_group">
                <?
                foreach ($arResult['CULTURE_GROUP_LIST'] as $item) {
                ?>
                    <div class="radio_area">
                        <input type="radio" name="cgroup" data-text="<?=$item['NAME']?>" id="cgroup<?=$item['ID']?>" value="<?=$item['ID']?>" <? if ($item["ID"] == $arResult["VALUES"]["CGROUP"]) { ?>checked="checked"<? } ?>>
                    </div>
                <?
                }
                ?>
            </div>
        </div>
    </div>
    <div class="request-block2 row">
        <?
        if ($arResult["SAVE"] == "Y" && sizeof($arResult['CULTURE_LIST']) > 0) {
        ?>
            <div class="request-block-intro">
                <div class="step-title row_head">2. Выберите сорт</div>
                <div class="row_val">
                    <div class="radio_group">
                        <?
                        foreach ($arResult['CULTURE_LIST'] as $item) {
                        ?>
                            <div class="radio_area">
                                <input type="radio" name="csort" data-text="<?=$item['NAME']?>" id="csort<?=$item['ID']?>" value="<?=$item['ID']?>" <? if ($item["ID"] == $arResult["VALUES"]["CSORT"]) { ?>checked="checked"<? } ?>>
                            </div>
                        <?
                        }
                        ?>
                    </div>
                </div>
            </div>
        <?
        }
        ?>
    </div>
    <div class="request-block3 row">
        <?
        if ($arResult["SAVE"] == "Y" && sizeof($arResult['CULTURE_LIST']) > 0) {
        ?>
            <div class="request-block-intro">
                <div class="step-title row_head">3. Заполните данные о продукте</div>
                <div class="row_val">
                    <?
                    foreach ($arResult['PARAMS_LIST'] as $item) {
                        if ($item["LBASE_ID"] > 0) {
                        ?>
                            <div class="sub_row">
                                <div class="quality-param-title"><?=$item["QUALITY_NAME"]?>:</div>
                                <div class="quality-param-intro">
                                    <select name="param[<?=$item['QUALITY_ID']?>][LBASE]">
                                        <?
                                        foreach ($item["LIST"] as $l) {
                                        ?>
                                            <option value="<?=$l['ID']?>" <? if ($l["ID"] == $arResult["VALUES"]["PARAM"][$item['QUALITY_ID']]["LBASE"]) { ?>selected="selected"<? } ?>><?=$l["NAME"]?></option>
                                        <?
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                        <?
                        }
                        else {
                        ?>
                            <div class="sub_row txt">
                                <div class="quality-param-intro txt">
                                    <div class="name"><?=$item["QUALITY_NAME"]?></div>
                                    <div class="prop_cntrl_area">
                                        <span class="minus minus_bg" data-step="<?=$item['STEP']?>" onclick="rrsClickMin(this);"></span>
                                        <input type="text" name="param[<?=$item['QUALITY_ID']?>][BASE]" value="<?=$arResult["VALUES"]["PARAM"][$item['QUALITY_ID']]["BASE"]?>">
                                        <span class="plus plus_bg" data-step="<?=$item['STEP']?>" onclick="rrsClickMax(this);"></span>
                                    </div>
                                </div>
                            </div>
                        <?
                        }
                    }
                    ?>
                </div>
            </div>
        <?
        }
        ?>
    </div>

    <div class="request-blocks" <? if (!$arResult["ELEMENT"]["ID"]) { ?>style="display: none;"<? } ?>>

        <div class="request-block4 row">
            <div class="request-block-intro">

                <div class="warehouses_fake_list" data-selected="<?=$arResult["SELECTED_WAREHOUSE"];?>" style="display: none;">
                    <?
                    foreach ($arResult["WAREHOUSE_LIST"] as $item) {
                        ?>
                        <div class="element" data-uid="<?=$item['FARMER']?>" data-name="<?=str_replace(array('\'', '"'), '', $item['NAME'])?>" data-eid="<?=$item['ID'];?>"></div>
                    <?
                    }
                    ?>
                </div>

                <div class="step-title row_head for_warehouse_list" data-u1="4. Адрес отгрузки" data-u3="4. Укажите адрес отгрузки"></div>
                <div class="row_val for_warehouse_list_val" data-value="N">
                    Нет складов для отгрузки (выберите поставщика)
                </div>

                <?
                /*if (count($arResult["WAREHOUSE_LIST"]) > 1) {
                ?>
                    <div class="step-title row_head">4. Укажите адрес отгрузки</div>
                    <div class="row_val" data-value="Y">
                        <select <?if(count($arResult["WAREHOUSE_LIST"]) > 4){?>data-search="y"<?}?> name="warehouse">
                            <option value="">Выберите склад отгрузки</option>
                            <?
                            foreach ($arResult["WAREHOUSE_LIST"] as $item) {
                            ?>
                                <option data-uid="<?=$item['FARMER'];?>" value="<?=$item['ID']?>" <? if ($item["ID"] == $arResult["VALUES"]["WAREHOUSE"]) { ?>selected=""<? } ?>><?=$item["NAME"]?> (<?=$item["ADDRESS"]?>)</option>
                            <?
                            }
                            ?>
                        </select>
                        <label class="select_label">Можно выбрать только один склад</label>
                    </div>
                <?
                }
                elseif (count($arResult["WAREHOUSE_LIST"]) == 1) {
                ?>
                    <div class="step-title row_head">4. Адрес отгрузки</div>
                    <div class="row_val" data-value="Y">
                        <?
                        foreach ($arResult["WAREHOUSE_LIST"] as $item) {
                        ?>
                            <input type="hidden" name="warehouse" value="<?=$item["ID"]?>" />
                            <input type="text" class="disabled" readonly="readonly" value="<?=$item["NAME"]?>" />
                        <?
                        }
                        ?>
                    </div>
                <?
                }
                else {
                ?>
                    <div class="step-title row_head">4. Адрес отгрузки</div>
                    <div class="row_val" data-value="N">
                        нет складов для отгрузки
                    </div>
                <?
                }*/
                ?>
            </div>
        </div>

        <div class="request-block6 row">
            <div class="request-block-intro">
                <div class="step-title row_head">5. Подтвердите ваше согласие с условиями работы площадки</div>
                <div class="agree-item row_val">
                    <div class="radio_group">
                        <div class="radio_area">
                            <input data-text="Принимаю пользовательское соглашение" type="checkbox" name="agreement" id="agreement" value="Y" checked="checked" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="form_line_error limit">У выбранного Вами поставщика исчерпан лимит создания товаров</div>

    <input type="button" name="iblock_submit" class="submit-btn <? if ($arResult['SAVE'] != "Y") { ?>inactive<? } ?>" value="<?=GetMessage("IBLOCK_FORM_SUBMIT")?>" />
</form>