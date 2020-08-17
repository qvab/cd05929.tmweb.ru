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
if (!isset($_REQUEST['status']) || (!in_array($_REQUEST['status'], array('open', 'close', 'cancel', 'all')))) {
    $_REQUEST['status'] = 'open';
}
?>
<div class="tab_form">
    <?
    if (in_array('open', $arParams['STATUS_LIST'])) {
        if ($_REQUEST['status'] == 'open') { ?>
            <div class="item active"><span>Открытые (<?=$arResult['Q']['open']?>)</span></div>
        <? } else { ?>
            <div class="item"><a href="<?=$arParams['LIST_URL']?>">Открытые (<?=$arResult['Q']['open']?>)</a></div>
        <? }
    }

    if (in_array('close', $arParams['STATUS_LIST'])) {
        if ($_REQUEST['status'] == 'close') { ?>
            <div class="item active"><span>Закрытые (<?=$arResult['Q']['close']?>)</span></div>
        <? } else { ?>
            <div class="item"><a href="<?=$arParams['LIST_URL']?>?status=close">Закрытые (<?=$arResult['Q']['close']?>)</a></div>
        <? }
    }

    if (in_array('cancel', $arParams['STATUS_LIST'])) {
        if ($_REQUEST['status'] == 'cancel') { ?>
            <div class="item active"><span>Отмененные (<?=$arResult['Q']['cancel']?>)</span></div>
        <? } else { ?>
            <div class="item"><a href="<?=$arParams['LIST_URL']?>?status=cancel">Отмененные (<?=$arResult['Q']['cancel']?>)</a></div>
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
    if(count($arResult['CLIENTS_LIST']) > 0){
        $selected_clients = array();
        $check_arr = array();
        if(isset($_GET['client_id']) && is_array($_GET['client_id']) && count($_GET['client_id']) > 0)
            $check_arr = array_flip($_GET['client_id']);

        ?>
        <form action="" method="get" class="select_item" name="deals_filter">
            <div class="fblock_fm row">
                <select <?if(count($arResult['CLIENTS_LIST']) > 3){?>data-search="y"<?}?> name="client_id[]" placeholder="Выберите покупателя">
                    <option value="0">Все покупатели</option>
                    <?
                    foreach($arResult['CLIENTS_LIST'] as $cur_id => $cur_data){
                        $cur_active = false;
                        if(isset($check_arr[$cur_id]))
                        {
                            $cur_active = true;
                            $selected_clients[] = $cur_id;
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
                <a href="/client_agent/deals/<?=(isset($_GET['status']) ? '?status=' . $_GET['status'] : '')?>" class="cancel_filter">Сбросить</a>
            </div>

            <div class="clear"></div>
        </form>
    <?}?>
</div>

<?
$filterName = $arParams['FILTER_NAME'];
$GLOBALS[$filterName]['PROPERTY_'.$arParams['USER_TYPE']] = (isset($_GET['client_id']) ? $_GET['client_id'] : array_keys($arResult['CLIENTS_LIST']));
if (in_array($_REQUEST['status'], array('open', 'close', 'cancel'))) {
    $GLOBALS[$filterName]['PROPERTY_STATUS'] = rrsIblock::getPropListKey('deals_deals', 'STATUS', $_REQUEST['status']);
}
?>