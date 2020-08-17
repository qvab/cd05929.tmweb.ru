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
$farmerObj = new farmer();
$checkFilter = $farmerObj->filterFarmerBLCheck();
if($checkFilter['NEED_UPD']){
    //делаем переадресацию, если нужно
    LocalRedirect($checkFilter['URL_UPD']);
    exit;
}

?>
<div class="tab_form">
    <div class="clear"></div>
        <form method="GET" id="deals_filter" name="blacklist_filter">
            <div class="row">
                <?if(!empty($arResult['REGIONS_LIST'])):?>
                    <!--Регион-->
                    <div class="wrap-select">
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
                <?if(
                        (!empty($arResult['REGIONS_LIST'])
                            || !empty($arResult['CULTURE_LIST'])
                        )
                        && !empty($arResult['REASON_LIST'])
                ):?>
                    <!--Культура-->
                    <?$sDataSearch = (count($arResult['REASON_LIST']) > 5 ? 'data-search="y"' : null);?>
                    <div class="wrap-select">
                        <select <?=$sDataSearch?> name="reasond_id">
                            <option value="0">Все причины</option>
                            <?foreach ($arResult['REASON_LIST'] as $reasonId => $reasonName):
                                $name = mb_strtoupper(mb_substr($reasonName, 0, 1)) . mb_substr($reasonName, 1, mb_strlen($reasonName) - 1);
                                ?>
                                <?$sSelected = ($_GET['reasond_id'] == $reasonId) ? 'selected="selected"' : null?>
                                <option <?=$sSelected?> value="<?=$reasonId?>"><?=$name?></option>
                            <?endforeach;?>
                        </select>
                    </div>
                <?endif;?>
                <div class="clear"></div>
            </div>
            <?if(!empty($arResult['REGIONS_LIST'])
                || !empty($arResult['CULTURE_LIST'])
            ){?>
            <div class="row">
                <div class="wrap-btn">
                    <input class="submit-btn" value="Применить" type="submit">
                </div>
                <div class="wrap-btn">
                    <input class="submit-btn reset" value="Сбросить" type="button">
                </div>
                <div class="clear"></div>
            </div>
            <?}?>

        </form>
</div>