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
$checkFilter = $agentObj->filterAgentRequestCheck();
if($checkFilter['NEED_UPD']){
    //делаем переадресацию, если нужно
    LocalRedirect($checkFilter['URL_UPD']);
    exit;
}

?>
<div class="tab_form filter_area" data-region="<?=(isset($_GET['region_id']) ? $_GET['region_id'] : 0);?>" data-farmer="<?=(isset($_GET['farmer_id'][0]) ? $_GET['farmer_id'][0] : 0);?>" data-culture="<?=(isset($_GET['culture']) ? $_GET['culture'] : 0);?>" data-nds="<?=(isset($_GET['type_nds']) ? $_GET['type_nds'] : 0);?>">
    <?

    $filter = false;

        if(count($arResult['FARMERS_LIST']) > 0){
            $selected_farmers = array();
            $selected_regions = array();
            $check_arr = array();
            if(isset($_GET['farmer_id']) && is_array($_GET['farmer_id']) && count($_GET['farmer_id']) > 0)
                $check_arr = array_flip($_GET['farmer_id']);

            ?>
            <form action="" method="get" class="select_item" name="request_filter">
                <div class="fblock_fm row">
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

                <div class="fblock_fm row">
                    <select <?if(count($arResult['FARMERS_LIST']) > 4){?>data-search="y"<?}?> name="farmer_id[]" placeholder="Выберите поставщика">
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
                <?
                if (is_array($arResult['CULTURE_LIST']) && sizeof($arResult['CULTURE_LIST']) > 0) {
                    $filter = true;
                    ?>
                    <div class="row fblock_fm">
                        <select <? if (sizeof($arResult['CULTURE_LIST']) > 4) { ?>data-search="y"<? } ?> name="culture" placeholder="Выберите культуру">
                            <option value="0">Все культуры</option>
                            <?
                            foreach ($arResult['CULTURE_LIST'] as $cur_id => $arItem) {
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

                <div class="fblock_fm row">
                    <select name="type_nds" placeholder="Налог на добавленную стоимость">
                        <option value="0">Все типы НДС</option>
                        <option <?=($_GET['type_nds'] == 1) ? 'selected="selected"' : '' ?> value="1">С НДС</option>
                        <option <?=($_GET['type_nds'] == 2) ? 'selected="selected"' : '' ?> value="2">Без НДС</option>
                    </select>
                </div>
                <div class="clear"></div>
                <?
                if ($filter) {
                    ?>
                    <div class="row fbtn_submit">
                        <input class="submit-btn left" value="Применить" type="submit">
                    </div>

                    <div class="row fbtn_cancel">
                        <a href="<?=$arParams['LIST_URL']?>" class="cancel_filter">Сбросить</a>
                    </div>

                    <div class="clear"></div>
                <?
                }
                ?>
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

$_GET['type_nds'] = intval($_GET['type_nds']);
$GLOBALS[$filterName]['TYPE_NDS'] = null;
if(!empty($_GET['type_nds'])) {
    switch ($_GET['type_nds']) {
        case 1:
            $GLOBALS[$filterName]['TYPE_NDS'] = 'yes';
            break;
        case 2:
            $GLOBALS[$filterName]['TYPE_NDS'] = 'no';
            break;
    }
}