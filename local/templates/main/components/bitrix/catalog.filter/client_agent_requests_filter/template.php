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
$checkFilter = $agentObj->filterClientRequestCheck();
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
    if(count($arResult['CLIENT_LIST']) > 0){
        $selected_clients = array();
        $check_arr = array();
        if(isset($_GET['client_id']) && is_array($_GET['client_id']) && count($_GET['client_id']) > 0)
            $check_arr = array_flip($_GET['client_id']);

        ?>
        <form action="" method="get" class="select_item" name="request_filter">
            <div class="fblock_fm row">
                <select <?if(count($arResult['CLIENT_LIST']) > 3){?>data-search="y"<?}?> name="client_id[]" placeholder="Выберите покупателя">
                    <option value="0">Все покупатели</option>
                    <?
                    foreach($arResult['CLIENT_LIST'] as $cur_id => $cur_data){
                        $cur_active = false;
                        if(isset($check_arr[$cur_id]))
                        {
                            $cur_active = true;
                            $selected_clients[] = $cur_id;
                        }
                        if($cur_data['NICK'] != ''){?>
                            <option value="<?=$cur_id;?>" <?if($cur_active){?>selected="selected"<?}?> ><?=$cur_data['NICK'];?></option>
                        <?}elseif($cur_data['NAME'] == ''){?>
                            <option value="<?=$cur_id;?>" <?if($cur_active){?>selected="selected"<?}?> ><?=(!checkEmailFromPhone($cur_data['EMAIL']) ? $cur_data['EMAIL'] : $cur_data['ID']);?></option>
                        <?}else{?>
                            <option value="<?=$cur_id;?>" <?if($cur_active){?>selected="selected"<?}?> ><?=$cur_data['NAME'];?><?if(!checkEmailFromPhone($cur_data['EMAIL'])){?> (<?=$cur_data['EMAIL'];?>)<?}?></option>
                        <?}
                    }?>
                </select>
            </div>

            <?
            if (is_array($arResult['CULTURE_LIST']) && sizeof($arResult['CULTURE_LIST']) > 0) {
                $filter = true;
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

            <div class="clear"></div>

            <div class="row fbtn_submit">
                <input class="submit-btn left" value="Применить" type="submit" />
            </div>

            <div class="row fbtn_cancel">
                <a href="/partner/client_request/<?=(isset($_GET['status']) ? '?status=' . $_GET['status'] : '')?>" class="cancel_filter">Сбросить</a>
            </div>

            <div class="clear"></div>
        </form>
    <?}?>
</div>

<?
$filterName = $arParams['FILTER_NAME'];
$GLOBALS[$filterName] = array();

$GLOBALS[$filterName]['PROPERTY_CLIENT'] = (count($selected_clients) > 0 ? $selected_clients : array_keys($arResult['CLIENT_LIST']));
if(count($GLOBALS[$filterName]['PROPERTY_CLIENT']) == 0){
    $GLOBALS[$filterName]['PROPERTY_CLIENT'] = 0;
}
if (in_array($_REQUEST['status'], array('yes', 'no'))) {
    $GLOBALS[$filterName]['PROPERTY_ACTIVE'] = rrsIblock::getPropListKey('client_request', 'ACTIVE', $_REQUEST['status']);
}

$_GET['culture'] = intval($_GET['culture']);
if(!empty($_GET['culture'])) {
    $GLOBALS[$filterName]['PROPERTY_CULTURE'] = $_GET['culture'];
}