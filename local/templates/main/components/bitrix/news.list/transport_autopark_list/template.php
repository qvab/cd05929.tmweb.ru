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
<?/*<script src="http://api-maps.yandex.ru/2.1/?lang=ru_RU" type="text/javascript"></script>*/?>
<script src="https://api-maps.yandex.ru/2.0-stable/?load=package.full&lang=ru-RU" type="text/javascript"></script>
<?
if (sizeof($arResult["ITEMS"]) > 0) {

    if(count($arResult["ITEMS"]) > 10):?>
        <div class="additional_href_data">
            <a href="/transport/autopark/add/">+ Добавить автопарк</a>
        </div>
    <?endif;?>

    <div class="list_page_rows warehouses">
        <?
        foreach ($arResult["ITEMS"] as $arItem) {
            $map = explode(',', $arItem['PROPERTIES']['MAP']['VALUE']);
        ?>
            <div class="line_area <?=$arItem["PROPERTIES"]['ACTIVE']['VALUE_XML_ID']?>">
                <div class="line_inner">
                    <div class="name"><?=$arItem['NAME']?></div>
                    <div class="region">
                        <?=$arResult['REGION_LIST'][$arItem["PROPERTIES"]['REGION']['VALUE']]['NAME']?>
                    </div>
                    <div class="address"><?=(isset($arItem["PROPERTIES"]['ADDRESS']['VALUE']) ? $arItem["PROPERTIES"]['ADDRESS']['VALUE'] : '');?></div>
                    <div class="arw_list arw_icon_close"></div>
                    <div class="clear"></div>
                </div>
                <form action="" method="post" class="line_additional">
                    <input type="hidden" name="autopark" value="<?=$arItem['ID']?>">

                    <div class="form_block row map">
                        <div class="row_val">
                            <div id="myMap<?=$arItem['ID']?>" style="width:70%; height: 250px; margin: 0 auto;"></div>
                        </div>
                    </div>

                    <script>
                        map<?=$arItem['ID']?>0 = <?=$map[0]?>;
                        map<?=$arItem['ID']?>1 = <?=$map[1]?>;
                    </script>

                    <script type="text/javascript">
                        ymaps.ready(init);

                        function init() {
                            var mapOptions = {
                                center: [map<?=$arItem['ID']?>0, map<?=$arItem['ID']?>1],
                                zoom: 12,
                                controls: ['geolocationControl', 'zoomControl', 'fullscreenControl']
                            };
                            var zoomOptions = {
                                maxZoom: 16,
                                checkZoomRange: true
                            };

                            myMap<?=$arItem['ID']?> = new ymaps.Map('myMap<?=$arItem['ID']?>', mapOptions, zoomOptions);
                            myMap<?=$arItem['ID']?>.controls.add('zoomControl');
                            var coords = [map<?=$arItem['ID']?>0, map<?=$arItem['ID']?>1];

                            myPlacemark = new ymaps.Placemark(coords);
                            myMap<?=$arItem['ID']?>.geoObjects.add(myPlacemark);
                        }
                    </script>

                    <div class="prop_area additional_submits">
                        <?
                        if ($arItem['PROPERTIES']['ACTIVE']['VALUE_XML_ID'] == 'yes') {
                        ?>
                            <input type="submit" class="reject_but" name="deactivate" value="Деактивировать базу автопарка" />
                        <?
                        }
                        else {
                        ?>
                            <input type="submit" class="reject_but" name="activate" value="Активировать базу автопарка" />
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
else {
?>
    <div class="list_page_rows requests no-item">
        Ни одной базы автопарка не найдено
    </div>
<?
}
?>
<a href="/transport/autopark/add/" class="add_blue_button">Добавить базу автопарка<div class="but_addit"><div class="ico">+</div></div></a>
