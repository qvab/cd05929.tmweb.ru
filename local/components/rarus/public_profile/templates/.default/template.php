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
else  {
    if ($arResult['MESS_STR'] != '') {
        echo '<div class="err_text row">' . $arResult['MESS_STR'] . '</div>';
    }
    ?>

    <div class="user_profile_area <?if($arParams['WITH_TABS']){?>no_margin<?}?>">
        <?if(!empty($arResult['IS_PARTNER_CONTRACT'])){?>
            <div class="row">
                <div class="row_head green_text">Агенсткий договор</div>
                <?
                    if(!empty($arResult['PARTNER_CONTRACT_FILE']['f_src'])){
                        ?><div class="get_file">
                        Посмотреть текущий договор <a target="_blank" href="<?=$arResult['PARTNER_CONTRACT_FILE']['f_src'];?>">по ссылке</a>
                        </div><?
                    }
                ?>
            </div>
        <?}?>

        <?if($arResult['u_last_name'] != ''){?>
            <div class="row">
                <div class="row_head">Фамилия</div>
                <div class="row_val">
                    <input readonly="readonly" value="<?=$arResult['u_last_name'];?>" type="text" />
                </div>
            </div>
        <?}?>

        <?if($arResult['u_name'] != ''){?>
            <div class="row">
                <div class="row_head">Имя</div>
                <div class="row_val">
                    <input readonly="readonly" value="<?=$arResult['u_name'];?>" type="text" />
                </div>
            </div>
        <?}?>

        <?if($arResult['u_second_name'] != ''){?>
            <div class="row">
                <div class="row_head">Отчество</div>
                <div class="row_val">
                    <input readonly="readonly" value="<?=$arResult['u_second_name'];?>" type="text" />
                </div>
            </div>
        <?}?>

        <?if(!checkEmailFromPhone($arResult['u_email'])) { ?>
            <div class="row">
                <div class="row_head">Email</div>
                <div class="row_val">
                    <input readonly="readonly" value="<?=$arResult['u_email'];?>" type="text"/>
                </div>
            </div>
            <?
        }

        if (count($arResult['LINKED_PARTNERS_DATA']) > 0) {
            //linked partners data
        ?>
            <div class="row">
                <div class="row_head">
                    <?if(count($arResult['LINKED_PARTNERS_DATA']) > 1){?>
                        Привязки к организаторам
                    <?}else{?>
                        Привязка к организатору
                    <?}?>
                </div>
                <div class="row_val">
                    <?
                    foreach($arResult['LINKED_PARTNERS_DATA'] as $cur_id => $cur_data){
                        if($cur_data){
                        ?>
                        <div class="public_partner_block">
                            <textarea readonly="readonly"><?=$cur_data;?></textarea>
                        </div>
                        <?}
                    }?>
                </div>
            </div>
        <?
        }
        ?>
        <?
        foreach ($arResult['EDIT_PROPS_LIST'] as $cur_code => $cur_flag) {
            if(!isset($arResult['EDIT_PROPS_DATA'][$cur_code])
                || isset($arResult['EDIT_PROPS_DATA'][$cur_code]['LINK_USER']) && !isset($arResult['USERS_DATA'][$arResult['EDIT_PROPS_DATA'][$cur_code]['LINK_USER']]) //check if user data exist
                || isset($arResult['EDIT_PROPS_DATA'][$cur_code]['LINK_IBLOCK_ID']) && !isset($arResult['LINKED_IB_DATA'][$arResult['EDIT_PROPS_DATA'][$cur_code]['LINK_IBLOCK_ID']]) // check if linked iblock data exist
                || isset($arResult['EDIT_PROPS_DATA'][$cur_code]['PROPERTY_TYPE']) && $arResult['EDIT_PROPS_DATA'][$cur_code]['PROPERTY_TYPE'] == 'F' && is_numeric($arResult['EDIT_PROPS_DATA'][$cur_code]['VALUE']) && !isset($arResult['FILES_DATA'][$arResult['EDIT_PROPS_DATA'][$cur_code]['VALUE']]) //check if file data exist
                || trim($arResult['EDIT_PROPS_DATA'][$cur_code]['VALUE']) == '' //check if property value not empty
            )
                continue;

            if ($cur_code == 'FULL_COMPANY_NAME' && trim($arResult['EDIT_PROPS_DATA'][$cur_code]['NAME']) != '')              {
                $APPLICATION->SetTitle($arResult['EDIT_PROPS_DATA'][$cur_code]['VALUE']);
            }
            elseif ($cur_code == 'IP_FIO' && trim($arResult['EDIT_PROPS_DATA'][$cur_code]['NAME']) != '') {
                $APPLICATION->SetTitle('ИП ' . $arResult['EDIT_PROPS_DATA'][$cur_code]['VALUE']);
            }
            ?>
            <div class="row">
                <div class="row_head"><?=$arResult['EDIT_PROPS_DATA'][$cur_code]['NAME'];?></div>
                <div class="row_val">
                    <?if(isset($arResult['EDIT_PROPS_DATA'][$cur_code]['LINK_USER'])){
                        $temp_name = trim($arResult['USERS_DATA'][$arResult['EDIT_PROPS_DATA'][$cur_code]['LINK_USER']]['NAME'] . ' '
                            . $arResult['USERS_DATA'][$arResult['EDIT_PROPS_DATA'][$cur_code]['LINK_USER']]['LAST_NAME'] . ' ('
                            . $arResult['USERS_DATA'][$arResult['EDIT_PROPS_DATA'][$cur_code]['LINK_USER']]['EMAIL'] . ') '
                        );?>
                        <textarea readonly="readonly"><?=addslashes($temp_name);?></textarea>
                    <?}elseif(isset($arResult['EDIT_PROPS_DATA'][$cur_code]['LINK_IBLOCK_ID'])){?>
                        <input readonly="readonly" value="<?=addslashes($arResult['LINKED_IB_DATA'][$arResult['EDIT_PROPS_DATA'][$cur_code]['LINK_IBLOCK_ID']][$arResult['EDIT_PROPS_DATA'][$cur_code]['VALUE']]);?>" type="text" />
                    <?}elseif(isset($arResult['EDIT_PROPS_DATA'][$cur_code]['PROPERTY_TYPE']) && $arResult['EDIT_PROPS_DATA'][$cur_code]['PROPERTY_TYPE'] == 'F'){?>
                        <a download="<?=addslashes($arResult['FILES_DATA'][$arResult['EDIT_PROPS_DATA'][$cur_code]['VALUE']]['NAME']);?>" href="<?=$arResult['FILES_DATA'][$arResult['EDIT_PROPS_DATA'][$cur_code]['VALUE']]['SRC'];?>">скачать файл</a>
                    <?}elseif($cur_code == 'FULL_COMPANY_NAME' || $cur_code == 'YUR_ADRESS'){?>
                        <textarea readonly="readonly"><?=$arResult['EDIT_PROPS_DATA'][$cur_code]['VALUE'];?></textarea>
                    <?}else{?>
                        <input readonly="readonly" value="<?=addslashes($arResult['EDIT_PROPS_DATA'][$cur_code]['VALUE']);?>" type="text" />
                    <?}?>
                </div>
            </div>
        <?}?>
    </div>
<?
}
?>