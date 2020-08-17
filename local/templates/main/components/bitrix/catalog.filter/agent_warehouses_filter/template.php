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
?>
<?
if (!isset($_REQUEST['status']) || (!in_array($_REQUEST['status'], array('yes', 'no', 'all')))) {
    $_REQUEST['status'] = 'yes';
}

//проверка соответствия выбранного фильтра и кук
$agentObj = new agent();
$checkFilter = $agentObj->filterAgentWhCheck();
if($checkFilter['NEED_UPD']){
    //делаем переадресацию, если нужно
    LocalRedirect($checkFilter['URL_UPD']);
    exit;
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
            $selected_farmers = array();
            $check_arr = array();
            if(isset($_GET['farmer_id']) && is_array($_GET['farmer_id']) && count($_GET['farmer_id']) > 0)
                $check_arr = array_flip($_GET['farmer_id']);

            ?>
            <form action="" method="get" name="warehouse_filter" class="select_item">
                <div class="fblock_fm row">
                    <select <?if(count($arResult['FARMERS_LIST']) > 4){?>data-search="y"<?}?> name="select_farmer" placeholder="Выберите поставщика">
                        <option value="0">Все поставщики</option>
                        <?
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
                                <option value="<?=$cur_id;?>" <?if($cur_active){?>selected="selected"<?}?> ><?=$cur_data['EMAIL'];?></option>
                            <?}else{?>
                                <option value="<?=$cur_id;?>" <?if($cur_active){?>selected="selected"<?}?> ><?=$cur_data['NAME'];?> (<?=$cur_data['EMAIL'];?>)</option>
                            <?}
                        }?>
                    </select>
                </div>

                <div class="clear"></div>

                <div class="row fbtn_submit">
                    <input class="submit-btn left" value="Применить" type="submit" />
                </div>

                <div class="row fbtn_cancel">
                    <a href="/agent/warehouses/<?=(isset($_GET['status']) ? '?status=' . $_GET['status'] : '')?>" class="cancel_filter">Сбросить</a>
                </div>

                <div class="clear"></div>
            </form>
        <?}?>

</div>

<?
$filterName = $arParams['FILTER_NAME'];
$GLOBALS[$filterName] = array();

$GLOBALS[$filterName]['PROPERTY_FARMER'] = (count($selected_farmers) > 0 ? $selected_farmers : array_keys($arResult['FARMERS_LIST']));
if(count($GLOBALS[$filterName]['PROPERTY_FARMER']) == 0){
    $GLOBALS[$filterName]['PROPERTY_FARMER'] = 0;
}

if (in_array($_REQUEST['status'], array('yes', 'no'))) {
    $GLOBALS[$filterName]['PROPERTY_ACTIVE'] = rrsIblock::getPropListKey('farmer_warehouse', 'ACTIVE', $_REQUEST['status']);
}
?>