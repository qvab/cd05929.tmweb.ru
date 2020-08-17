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

//проверка соответствия выбранного фильтра и кук
$agentObj = new agent();
$checkFilter = $agentObj->filterAgentOfferCheck();
if($checkFilter['NEED_UPD']
    && !isset($_POST['send_counter_offer_ajax'])
){
    //делаем переадресацию, если нужно
    LocalRedirect($checkFilter['URL_UPD']);
    exit;
}

?>
<?
if (!isset($_REQUEST['status']) || (!in_array($_REQUEST['status'], array('yes', 'no', 'all')))) {
    $_REQUEST['status'] = 'yes';
}
?>
<div class="tab_form">
    <?
    if (in_array('yes', $arParams['STATUS_LIST'])) {
        if ($_REQUEST['status'] == 'yes') { ?>
            <div class="item active"><span>Активные (<?=$arResult['Q']['yes']?>)</span></div>
        <? } else { ?>
            <div class="item"><a href="<?=$arParams['LIST_URL']?>">Активные (<?=$arResult['Q']['yes']?>)</a></div>
        <? }
    }

    if (in_array('no', $arParams['STATUS_LIST'])) {
        if ($_REQUEST['status'] == 'no') { ?>
            <div class="item active"><span>Неактивные (<?=$arResult['Q']['no']?>)</span></div>
        <? } else { ?>
            <div class="item"><a href="<?=$arParams['LIST_URL']?>?status=no">Неактивные (<?=$arResult['Q']['no']?>)</a></div>
        <? }
    }

    if (in_array('all', $arParams['STATUS_LIST'])) {
        if ($_REQUEST['status'] == 'all') { ?>
            <div class="item active last"><span>Все (<?=$arResult['Q']['all']?>)</span></div>
        <? } else { ?>
            <div class="item last"><a href="<?=$arParams['LIST_URL']?>?status=all">Все (<?=$arResult['Q']['all']?>)</a></div>
        <? }
    }
    ?>
    <div class="clear"></div>

        <?
        if(count($arResult['FARMERS_LIST']) > 0){
            $filter_count = 0;
            $selected_farmers = array();
            $check_arr = array();
            if(isset($_GET['farmer_id']) && is_array($_GET['farmer_id']) && count($_GET['farmer_id']) > 0)
                $check_arr = array_flip($_GET['farmer_id']);
            ?>
            <form action="" id="offers_filter" method="get" class="select_item" name="offers_filter">

                <?if(!empty($arResult['REGIONS_LIST'])):
                    $filter_count++;
                    ?>
                    <div class="row fblock_cl">
                            <!--Регион-->
                            <select <?if(count($arResult['REGIONS_LIST']) > 4){?>data-search="y"<?}?> name="region_id" placeholder="Выберите регион">
                                <option value="0">Все регионы</option>
                                <?
                                foreach($arResult['REGIONS_LIST'] as $cur_id => $cur_data){
                                    $cur_active = false;
                                    if(isset($check_arr[$cur_id])) {
                                        $cur_active = true;
                                        $selected_regions[] = $cur_id;
                                    }
                                    ?><option value="<?=$cur_id;?>" <? if ($cur_id == $_GET['region_id']) { ?>selected="selected"<? } ?> ><?=$cur_data['NAME'];?></option><?
                                }?>
                            </select>
                    </div>
                <?endif;?>



                <div class="fblock_fm row">
                        <select <?if(count($arResult['FARMERS_LIST']) > 3){?>data-search="y"<?}?> name="farmer_id[]" placeholder="Выберите поставщика">
                            <option value="0">Все поставщики</option>
                            <?
                            $filter_count++;
                            foreach($arResult['FARMERS_LIST'] as $cur_id => $cur_data){
                                $cur_active = false;
                                if(isset($check_arr[$cur_id]))
                                {
                                    $cur_active = true;
                                    $selected_farmers[] = $cur_id;
                                }
                                if($cur_data['NICK'] != ''){?>
                                    <option value="<?=$cur_id;?>" <?if($cur_active){?>selected="selected"<?}?> ><?=$cur_data['NICK'];?></option>
                                <?}elseif($cur_data['NAME'] == ''){?>
                                    <option value="<?=$cur_id;?>" <?if($cur_active){?>selected="selected"<?}?> ><?=(!checkEmailFromPhone($cur_data['EMAIL']) ? $cur_data['EMAIL'] : $cur_id);?></option>
                                <?}else{?>
                                    <option value="<?=$cur_id;?>" <?if($cur_active){?>selected="selected"<?}?> ><?=$cur_data['NAME'];?><?if(!checkEmailFromPhone($cur_data['EMAIL'])){?> (<?=$cur_data['EMAIL'];?>)<?}?></option>
                                <?}
                            }?>
                        </select>
                </div>

                <?
                if (is_array($arResult['CULTURE_LIST']) && sizeof($arResult['CULTURE_LIST']) > 0) {
                    $filter = true;

                    if($filter_count>=2){
                        ?><div class="clear"></div><?
                    }

                    ?>
                    <div class="row fblock_cl">
                            <select <? if (sizeof($arResult['CULTURE_LIST']) > 4) { ?>data-search="y"<? } ?> name="culture" placeholder="Выберите культуру">
                                <option value="0">Все культуры</option>
                                <?
                                foreach ($arResult['CULTURE_LIST'] as $arItem) {
                                    ?>
                                    <option value="<?=$arItem['ID']?>" <? if ($arItem['ID'] == $_GET['culture']) { ?>selected="selected"<? } ?> ><?=$arItem['NAME']?></option>
                                <?
                                }
                                ?>
                            </select>
                    </div>
                <?
                }
                ?>

                <?if (is_array($arResult['NDS_LIST']) && sizeof($arResult['NDS_LIST']) > 0) {?>
                <div class="fblock_fm row">
                    <select name="type_nds" placeholder="Налог на добавленную стоимость">
                        <option value="0">Все типы НДС</option>
                        <?foreach ($arResult['NDS_LIST'] as $arItem){
                            ?><option <?=($_GET['type_nds'] == $arItem['VALUE']) ? 'selected="selected"' : '' ?> value="<?=$arItem['VALUE']?>"><?=$arItem['NAME']?></option><?
                        }
                        ?>
                    </select>
                </div>
                <?}?>

                <div class="clear"></div>

                <div class="row fbtn_submit">
                    <input class="submit-btn left" value="Применить" type="submit" />
                </div>

                <div class="row fbtn_cancel">
                    <a href="/partner/offer/<?=(isset($_GET['status']) ? '?status=' . $_GET['status'] : '')?>" class="cancel_filter">Сбросить</a>
                </div>

                <div class="clear"></div>
            </form>
        <?}?>

</div>

<?
$filterName = $arParams['FILTER_NAME'];
$GLOBALS[$filterName] = array();
if(isset($_REQUEST['region_id']) && is_numeric($_REQUEST['region_id']) && $_REQUEST['region_id'] > 0) {
    if(isset($arResult['REGIONS_WH'][$_REQUEST['region_id']])){
        $GLOBALS[$filterName]['PROPERTY_WAREHOUSE'] = $arResult['REGIONS_WH'][$_REQUEST['region_id']];
    }
}

$GLOBALS[$filterName]['PROPERTY_FARMER'] = (count($selected_farmers) > 0 ? $selected_farmers : array_keys($arResult['FARMERS_LIST']));
if(count($GLOBALS[$filterName]['PROPERTY_FARMER']) == 0){
    $GLOBALS[$filterName]['PROPERTY_FARMER'] = 0;
}

if(isset($_REQUEST['culture']) && is_numeric($_REQUEST['culture']) && $_REQUEST['culture'] > 0) {
    $GLOBALS[$filterName]['PROPERTY_CULTURE'] = $_REQUEST['culture'];
}

if(isset($_REQUEST['type_nds']) && is_numeric($_REQUEST['type_nds']) && $_REQUEST['type_nds'] > 0) {
    if($_REQUEST['type_nds'] == 1){
        $GLOBALS[$filterName]['PROPERTY_USER_NDS'] = rrsIblock::getPropListKey('farmer_offer', 'USER_NDS', 'yes');;
    }elseif($_REQUEST['type_nds'] == 2){
        $GLOBALS[$filterName]['PROPERTY_USER_NDS'] = rrsIblock::getPropListKey('farmer_offer', 'USER_NDS', 'no');
    }
}

if (in_array($_REQUEST['status'], array('yes', 'no'))) {
    $GLOBALS[$filterName]['PROPERTY_ACTIVE'] = rrsIblock::getPropListKey('farmer_offer', 'ACTIVE', $_REQUEST['status']);
}
?>