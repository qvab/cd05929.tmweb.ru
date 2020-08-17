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

if (sizeof($arResult["ITEMS"]) > 0) {
?>
    <div class="additional_href_data">
        <a href="/partner/client_warehouses/add/">+ Добавить склад</a>
    </div>

    <div class="list_page_rows warehouses">
        <?
        foreach ($arResult["ITEMS"] as $arItem) {
            $map = explode(',', $arItem['PROPERTIES']['MAP']['VALUE']);
        ?>
            <div class="line_area <?=$arItem["PROPERTIES"]['ACTIVE']['VALUE_XML_ID']?>">
                <div class="line_inner">
                    <div class="name"><?=$arItem['NAME']?>
                        <?if(isset($arResult['CLIENTS_DATA'][$arItem['PROPERTIES']['CLIENT']['VALUE']])){
                            $cur_client = $arResult['CLIENTS_DATA'][$arItem['PROPERTIES']['CLIENT']['VALUE']];
                            if($cur_client['NICK'] != ''){?>
                                <div class="client_name"><?=$cur_client['NICK'];?></div>
                            <?}elseif($cur_client['NAME'] == ''){?>
                                <div class="client_name"><?=(!checkEmailFromPhone($cur_client['EMAIL']) ? $cur_client['EMAIL'] : $arItem['PROPERTIES']['CLIENT']['VALUE']);?></div>
                            <?}else{?>
                                <div class="client_name"><?=$cur_client['NAME'];?><?if(!checkEmailFromPhone($cur_client['EMAIL'])){?> (<?=$cur_client['EMAIL'];?>)<?}?></div>
                            <?}
                            }
                        ?>
                    </div>
                    <div class="region">
                        <?=$arResult['REGION_LIST'][$arItem["PROPERTIES"]['REGION']['VALUE']]['NAME']?>
                    </div>
                    <div class="address"><?=(isset($arItem["PROPERTIES"]['ADDRESS']['VALUE']) ? $arItem["PROPERTIES"]['ADDRESS']['VALUE'] : '');?></div>
                    <div class="arw_list arw_icon_close"></div>
                    <div class="clear"></div>
                </div>
                <form action="" method="post" class="line_additional" data-lat="<?=$map[0]?>" data-lng="<?=$map[1]?>">
                    <input type="hidden" name="warehouse" value="<?=$arItem['ID']?>">

                    <div class="form_block row map">
                        <div class="row_val">
                            <div id="myMap<?=$arItem['ID']?>" style="width:70%; height: 250px; margin: 0 auto;"></div>
                        </div>
                    </div>

                    <div class="prop_area additional_submits">
                        <?if ($arItem['PROPERTIES']['ACTIVE']['VALUE_XML_ID'] == 'yes') {
                            ?>
                                <input type="submit" class="reject_but" name="deactivate" value="Деактивировать склад" />
                            <?
                            } else {
                            ?>
                                <input type="submit" class="reject_but" name="activate" value="Активировать склад" />
                            <?
                            }
                        ?>
                        <div class="hide_but">Свернуть</div>
                    </div>
                </form>
            </div>
        <?
        }
        ?>
    </div>
<?
}
elseif(!isset($GLOBALS['arrFilter']['PROPERTY_CLIENT']) || $GLOBALS['arrFilter']['PROPERTY_CLIENT'] == 0) {
?>
    <div class="list_page_rows requests no-item">
        К вам не привязан ни один покупатель
    </div>
<?
}
else {
?>
    <div class="list_page_rows requests no-item">
        Ни одного склада не найдено
    </div>
<?
}
if(count($arResult['CLIENTS_DATA']) > 0){
    ?><a href="/partner/client_warehouses/add/" class="add_blue_button warehouses">Добавить склад<div class="but_addit"><div class="ico">+</div></div></a><?
}
?>
<script src="https://maps.googleapis.com/maps/api/js?key=<?=$GLOBALS['googleMapKey'];?>&callback=initWHMap&libraries=places&language=ru" async defer></script>