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
    'client_warehouse_id',
    'farmer_warehouse_id',
    'culture_id',
    'client_id',
    'farmer_id',
    'distance_id',
    'PAGEN_1'
];
if(!($arParams['PROFILE'] == 'Y')){
    //проверка соответствия выбранного фильтра и кук
    $farmerObj = new farmer();
    $checkFilter = $farmerObj->filterFarmerPairCheck();
    if($checkFilter['NEED_UPD']){
        //делаем переадресацию, если нужно
        LocalRedirect($checkFilter['URL_UPD']);
        exit;
    }
}

?>
<div class="tab_form">
    <?/*
    if (in_array('new', $arParams['STATUS_LIST'])) {
        if ($_REQUEST['status'] == 'new') { ?>
            <div class="item active"><span>Новые (<?=$arResult['Q']['new']?>)</span></div>
        <? } else { ?>
            <div class="item"><a href="<?=$APPLICATION->GetCurPageParam('', $arRemoveParams)?>">Новые (<?=$arResult['Q']['new']?>)</a></div>
        <? }
    }
*/
    ?>
    <div class="clear"></div>


    <?if($arResult['SHOW_FORM']):?>

        <form method="GET" id="deals_filter" name="deals_filter">
            <?if(isset($_GET['uid'])&&is_numeric($_GET['uid'])){
                ?><input type="hidden" name="uid" value="<?=$_GET['uid']?>"><?
            }
            ?>
            <input type="hidden" name="status" value="<?=$_REQUEST['status']?>">

            <div class="row">

                <?if(!empty($arResult['CLIENT_WAREHOUSE_LIST'])):?>
                    <!--Склады покупателя-->
                    <?$sDataSearch = (count($arResult['CLIENT_WAREHOUSE_LIST']) > 3) ? 'data-search="y"' : null;?>
                    <div class="wrap-select">

                        <select <?=$sDataSearch?> name="client_warehouse_id" placeholder="Выберите склад покупателя">
                            <option value="0">Все склады</option>
                            <?foreach ($arResult['CLIENT_WAREHOUSE_LIST'] as $arWarehouse):?>
                                <?$sSelected = ($_GET['client_warehouse_id'] == $arWarehouse['ID']) ? 'selected="selected"' : null?>
                                <option <?=$sSelected?> value="<?=$arWarehouse['ID']?>"><?=$arWarehouse['NAME']?></option>
                            <?endforeach;?>
                        </select>
                    </div>
                <?endif;?>


                <?if(!empty($arResult['FARMER_WAREHOUSE_LIST'])):?>
                    <!--Склады поставщика-->
                    <?$sDataSearch = (count($arResult['FARMER_WAREHOUSE_LIST']) > 3) ? 'data-search="y"' : null;?>
                    <div class="wrap-select">

                        <select <?=$sDataSearch?> name="farmer_warehouse_id" placeholder="Выберите склад поставщика">
                            <option value="0">Все склады</option>
                            <?foreach ($arResult['FARMER_WAREHOUSE_LIST'] as $arWarehouse):?>
                                <?$sSelected = ($_GET['farmer_warehouse_id'] == $arWarehouse['ID']) ? 'selected="selected"' : null?>
                                <option <?=$sSelected?> value="<?=$arWarehouse['ID']?>"><?=$arWarehouse['NAME']?></option>
                            <?endforeach;?>
                        </select>
                    </div>
                <?endif;?>

                <?if(!empty($arResult['CULTURE_LIST'])):?>
                    <!--Культура-->
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


                <?if(!empty($arResult['CLIENT_LIST'])):?>
                    <!--Покупатели-->
                    <?$sDataSearch = (count($arResult['CLIENT_LIST']) > 3) ? 'data-search="y"' : null;?>
                    <div class="wrap-select">
                        <select <?=$sDataSearch?> name="client_id" placeholder="Выберите покупателя">
                            <option value="0">Все покупатели</option>
                            <?foreach ($arResult['CLIENT_LIST'] as $iClientId => $arClient):?>
                                <?$sSelected = ($_GET['client_id'] == $iClientId) ? 'selected="selected"' : null?>
                                <option <?=$sSelected?> value="<?=$iClientId?>"><?=$arClient['COMPANY']?></option>
                            <?endforeach;?>
                        </select>
                    </div>
                <?endif;?>


                <?if(!empty($arResult['FARMER_LIST'])):?>
                    <!--Поставщики-->
                    <?$sDataSearch = (count($arResult['FARMER_LIST']) > 3) ? 'data-search="y"' : null;?>
                    <div class="wrap-select">
                        <select <?=$sDataSearch?> name="farmer_id" placeholder="Выберите поставщика">
                            <option value="0">Все поставщики</option>
                            <?foreach ($arResult['FARMER_LIST'] as $iFarmerId => $arFarmer):?>
                                <?$sSelected = ($_GET['farmer_id'] == $iFarmerId) ? 'selected="selected"' : null?>
                                <option <?=$sSelected?> value="<?=$iFarmerId?>"><?=$arFarmer['COMPANY']?></option>
                            <?endforeach;?>
                        </select>
                    </div>
                <?endif;?>


                <?if(!empty($arResult['DISTANCE_LIST'])):?>
                    <!--Расстояния-->
                    <?$sDataSearch = (count($arResult['DISTANCE_LIST']) > 3) ? 'data-search="y"' : null;?>
                    <div class="wrap-select">
                        <select <?=$sDataSearch?> name="distance_id" placeholder="Выберите расстояние">
                            <option value="0">Все расстояния</option>
                            <?foreach ($arResult['DISTANCE_LIST'] as $iDistanceId => $arDistance):?>
                                <?$sSelected = ($_GET['distance_id'] == $iDistanceId) ? 'selected="selected"' : null?>
                                <option <?=$sSelected?> value="<?=$iDistanceId?>"><?=$arDistance['NAME']?></option>
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