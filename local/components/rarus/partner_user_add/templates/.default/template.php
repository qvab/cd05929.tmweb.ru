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
            <input type="hidden" name="by_phone" value="y" />
            <input type="hidden" name="add_farmer" value="y" />
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
                    <div class="row_head">
                        <div class="tab_form reg_form_control_tabs agent_add_user">
                            <div class="item form_control_tab active" data-val="phone">
                                <span>Телефон</span>
                                <div class="ico"></div>
                            </div>
                            <div class="item form_control_tab" data-val="login">
                                <span>email</span>
                                <div class="ico"></div>
                            </div>
                            <div class="clear"></div>
                        </div>
                    </div>
                    <div class="row_val one_line">
                        <input class="phone_msk" placeholder="Телефон" type="text" name="phone" value="<?=(!isset($arResult['success_text'][0]) && isset($_POST['phone']) ? $_POST['phone'] : '');?>" />
                        <input class="inactive" placeholder="Email" data-checkval="y" data-checktype="email" type="text" name="email" value="<?=(!isset($arResult['success_text'][0]) && isset($_POST['email']) ? $_POST['email'] : '');?>" />
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
                <div class="line row">
                    <input class="submit-btn inactive" type="button" value="Добавить" />
                </div>
            </div>
        </form>
    <?
}