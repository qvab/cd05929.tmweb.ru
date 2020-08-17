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

$href_val = '/client/warehouses/';
if($arParams['USER_TYPE'] == 'farmer'){
    $href_val = '/farmer/warehouses/';
}
?>
<script>
    regionArray = <?=json_encode($arResult['REGION_NAMES']);?>;
</script>
<a class="go_back cross" href="<?=$href_val?>"></a>
<form class="new_warehouse" action="<?=$href_val;?>action.php" method="post">
    <?/*<input type="hidden" id="MAP_0" name="P[MAP][0]" data-oldval="<?=$form_data['MAP'][0]?>" value="<?=$form_data['MAP'][0]?>">
        <input type="hidden" id="MAP_1" name="P[MAP][1]" data-oldval="<?=$form_data['MAP'][1]?>" value="<?=$form_data['MAP'][1]?>">*/?>
    <input type="hidden" id="MAP_0" name="P[MAP][0]" data-oldval="" value="">
    <input type="hidden" id="MAP_1" name="P[MAP][1]" data-oldval="" value="">
    <input type="hidden" id="PRegion" class="w_400" name="P[REGION]"  value="">

    <div class="form_block row">
        <div class="row_val">
            <input placeholder="Название склада" type="text" id="P_NAME" name="NAME" class="w_300" value="" />
        </div>
    </div>

    <div class="form_block row">
        <div class="row_val address">
            <input placeholder="Адрес" id="Address" class="w_400" autocomplete="off" type="text" name="P[ADDRESS]"/>
        </div>
    </div>

    <div class="form_block row double_region inactive">
        <div class="row_label">Выбрать регион:</div>
        <div class="row_val">
            <select <?if(count($arResult['REGION_NAMES']) > 4){?>data-search="y"<?}?> >
                <option value="0">Не выбрано</option>
                <?foreach($arResult['REGION_NAMES'] as $sRegionName => $iRegionId){?>
                    <option value="<?=$iRegionId;?>"><?=$sRegionName;?></option>
                <?}?>
            </select>
        </div>
    </div>

    <div class="form_block row map">
        <div class="row_val">
            <div id="myMap" style="width: 100%; height: 600px;"></div>
        </div>
    </div>

    <?php
    if($arParams['USER_TYPE'] != 'farmer'){
        ?>
        <div class="form_block row transport">
            <div class="step-title row_head">Тип транспорта, возможный для выгрузки</div>
            <a href="javascript:void(0);" class="ch_all_tr">Выбрать все</a> <a href="javascript:void(0);" class="no_tr">Снять все</a>
            <div class="radio_group">
                <?
                foreach ($arResult['TRANSPORT_LIST'] as $item) {
                    ?>
                    <div class="radio_area">
                        <input type="checkbox" data-text="<?=$item['NAME']?>" name="transport[<?=$item['ID']?>]" id="transport[<?=$item['ID']?>]" value="Y">
                    </div>
                    <?
                }
                ?>
            </div>
        </div>
        <?
    }
    ?>


    <div class="row">
        <input name="iblock_submit" class="submit-btn left" value="Добавить склад" type="submit" />
        <div class="clear"></div>
    </div>
</form>

<script>
    var map, gGeocoderObj, gMarker = null, gAutocomplete = null, gAutocompleteListener = null; //данные для google maps
    var reqDelay = 700, reqDelaysStartTime = 0; //данные для отложенного запроса в google maps
    var adresInput = document.getElementById('Address');
    var first_run = true;

    ID = <?=$arResult['GRAPH_DATA']['ID']?>;
    map0 = <?=$arResult['GRAPH_DATA']['MAP'][0]?>;
    map1 = <?=$arResult['GRAPH_DATA']['MAP'][1]?>;
</script>

<script src="https://maps.googleapis.com/maps/api/js?key=<?=$GLOBALS['googleMapKey'];?>&callback=initWHMap&libraries=places&language=ru" async defer></script>