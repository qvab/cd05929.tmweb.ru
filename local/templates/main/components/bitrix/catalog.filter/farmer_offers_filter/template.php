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
$arRemoveParams = [
    'status',
    'warehouse_id',
    'culture_id',
];

//проверка соответствия выбранного фильтра и кук
$farmerObj = new farmer();
$checkFilter = $farmerObj->filterFarmerOffersCheck();
if($checkFilter['NEED_UPD']
    && !isset($_POST['send_counter_offer_ajax'])
){
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
            <div class="item"><a href="<?=$APPLICATION->GetCurPageParam('', $arRemoveParams)?>">Активные (<?=$arResult['Q']['yes']?>)</a></div>
        <? }
    }

    if (in_array('no', $arParams['STATUS_LIST'])) {
        if ($_REQUEST['status'] == 'no') { ?>
            <div class="item active"><span>Неактивные (<?=$arResult['Q']['no']?>)</span></div>
        <? } else { ?>
            <div class="item"><a href="<?=$APPLICATION->GetCurPageParam('status=no', $arRemoveParams)?>">Неактивные (<?=$arResult['Q']['no']?>)</a></div>
        <? }
    }

    if (in_array('all', $arParams['STATUS_LIST'])) {
        if ($_REQUEST['status'] == 'all') { ?>
            <div class="item active last"><span>Все (<?=$arResult['Q']['all']?>)</span></div>
        <? } else { ?>
            <div class="item last"><a href="<?=$APPLICATION->GetCurPageParam('status=all', $arRemoveParams)?>">Все (<?=$arResult['Q']['all']?>)</a></div>
        <? }
    }
    ?>
    <div class="clear"></div>


    <?if($arResult['SHOW_FORM']):?>
        <form method="GET" id="farmer_offers_filter" name="farmer_offers_filter">

            <input type="hidden" name="status" value="<?=$_REQUEST['status']?>">

            <div class="row">

                <?if(!empty($arResult['WAREHOUSE_LIST'])):?>

                    <?$sDataSearch = (count($arResult['WAREHOUSE_LIST']) > 3) ? 'data-search="y"' : null;?>
                    <div class="wrap-select">

                        <select <?=$sDataSearch?> name="warehouse_id" placeholder="Выберите склад">
                            <option value="0">Все склады</option>
                            <?foreach ($arResult['WAREHOUSE_LIST'] as $arWarehouse):?>
                                <?$sSelected = ($_GET['warehouse_id'] == $arWarehouse['ID']) ? 'selected="selected"' : null?>
                                <option <?=$sSelected?> value="<?=$arWarehouse['ID']?>"><?=$arWarehouse['NAME']?></option>
                            <?endforeach;?>
                        </select>
                    </div>
                <?endif;?>

                <?if(!empty($arResult['CULTURE_LIST'])):?>

                    <?$sDataSearch = (count($arResult['CULTURE_LIST']) > 3) ? 'data-search="y"' : null;?>
                    <div class="wrap-select">
                        <select <?=$sDataSearch?> name="culture_id" placeholder="Выберите культуру">
                            <option value="0">Все культуры</option>
                            <?foreach ($arResult['CULTURE_LIST'] as $arCulture):?>
                                <?$sSelected = ($_GET['culture_id'] == $arCulture['ID']) ? 'selected="selected"' : null?>
                                <option <?=$sSelected?> value="<?=$arCulture['ID']?>"><?=$arCulture['NAME']?></option>
                            <?endforeach;?>
                        </select>
                    </div>
                <?endif;?>
                <div class="clear"></div>
            </div>

            <div class="row">
                <div class="wrap-btn">
                    <input class="submit-btn" value="Применить" type="submit">
                </div>
                <div class="wrap-btn">
                    <input class="submit-btn reset" value="Сбросить" type="button">
                </div>
                <div class="clear"></div>
            </div>

        </form>
    <?endif;?>
</div>