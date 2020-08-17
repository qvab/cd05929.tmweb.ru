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
if (is_array($arResult['AUTOPARK']) && sizeof($arResult['AUTOPARK']) > 0) {
    $view_count = 0;
    if (sizeof($arResult["ITEMS"]) > 0) {
    ?>
        <div class="list_page_rows">
            <?
            foreach ($arResult["ITEMS"] as $arItem) {
                //если партнер в запросе не привязан к ТК, то данный запрос не выводим
                if (!$arResult['LINKED_PARTNERS'][$arItem['PROPERTIES']['PARTNER']['VALUE']]['ID']) {
                    continue;
                }
                $commission = 0.01 * $arResult['COMMISSION'] * $arItem['PROPERTIES']['TARIF']['VALUE'];
                $tarif = $arItem['PROPERTIES']['TARIF']['VALUE'] - $commission;
                //$tarif = $arItem['PROPERTIES']['TARIF']['VALUE'];
                $cost = $tarif * $arItem['PROPERTIES']['VOLUME']['VALUE'];

                $sLinkToGoogleMap = 'https://www.google.ru/maps/dir/'.
                    $arResult['FARMER_WAREHOUSES_LIST'][$arItem['PROPERTIES']['FARMER_WAREHOUSE']['VALUE']]['MAP'].'/'.
                    $arResult['CLIENT_WAREHOUSES_LIST'][$arItem['PROPERTIES']['CLIENT_WAREHOUSE']['VALUE']]['MAP'].'/';
                ?>
                <div class="line_area<? if ($_GET['id'] == $arItem['ID']) { ?> active<? } ?>">
                    <div class="line_inner">
                        <div class="name"><?=$arResult['CULTURE_LIST'][$arItem['PROPERTIES']['CULTURE']['VALUE']]['NAME']?></div>
                        <div class="tons"><span class="val decs_separators"><?=$arItem['PROPERTIES']['VOLUME']['VALUE']?></span> т.</div>
                        <div class="price"><span class="val decs_separators"><?=number_format($tarif)?></span> руб/т</div>
                        <div class="date"><?=date("d.m.Y H:i", strtotime($arItem['PROPERTIES']['DATE_STAGE']['VALUE']));?></div>
                        <div class="arw_list arw_icon_close"></div>
                        <div class="clear l"></div>
                    </div>

                    <form action="" method="post" class="line_additional" <? if ($_GET['id'] == $arItem['ID']) { ?> style="display: block;"<? } ?>>
                        <input type="hidden" name="deal" value="<?=$arItem['ID']?>">

                        <div class="prop_area double_val">
                            <div class="prop_name">Дата создания запроса:</div>
                            <div class="prop_val"><?=date("d.m.Y H:i", strtotime($arItem['PROPERTIES']['DATE_STAGE']['VALUE']));?></div>
                            <div class="clear"></div>
                        </div>

                        <div class="prop_area adress_val">
                            <div class="adress">Организатор сделки:</div>
                            <div class="val_adress">
                                <a href="/profile/?uid=<?=$arItem['PROPERTIES']['PARTNER']['VALUE']?>">
                                    <?=$arResult['PARTNERS'][$arItem['PROPERTIES']['PARTNER']['VALUE']]['PROPERTY_FULL_COMPANY_NAME_VALUE']?>
                                </a>
                            </div>
                            <div class="clear"></div>
                        </div>

                        <div class="prop_area adress_val">
                            <div class="adress">Показать маршрут на карте:</div>
                            <div class="val_adress">
                                <div><a href="<?=$sLinkToGoogleMap?>" target="_blank">Смотреть маршрут</a></div>
                            </div>
                            <div class="clear"></div>
                        </div>

                        <div class="prop_area adress_val">
                            <div class="adress">Адрес отгрузки:</div>
                            <div class="val_adress">
                                <?=$arResult['FARMER_WAREHOUSES_LIST'][$arItem['PROPERTIES']['FARMER_WAREHOUSE']['VALUE']]['ADDRESS']?>
                            </div>
                            <div class="clear"></div>
                        </div>

                        <?
                        $ap_id = $arResult['MIN_ROUTE'][$arItem['PROPERTIES']['FARMER_WAREHOUSE']['VALUE']]['AP_ID'];
                        $route = $arResult['MIN_ROUTE'][$arItem['PROPERTIES']['FARMER_WAREHOUSE']['VALUE']]['ROUTE'];
                        if ($ap_id > 0 && $route > 0) {
                        ?>
                            <div class="prop_area adress_val">
                                <div class="adress">Ближайшая база автопарка:</div>
                                <div class="val_adress"><?=$arResult['AUTOPARK'][$ap_id]['NAME']?></div>
                                <div class="val_adress">Адрес: <?=$arResult['AUTOPARK'][$ap_id]['ADDRESS']?></div>
                                <div class="val_adress">Расстояние до адреса отгрузки: <?=$route?> км</div>
                                <div class="clear"></div>
                            </div>
                        <?
                        }
                        ?>

                        <div class="prop_area adress_val">
                            <div class="adress">Адрес доставки:</div>
                            <div class="val_adress">
                                <?=$arResult['CLIENT_WAREHOUSES_LIST'][$arItem['PROPERTIES']['CLIENT_WAREHOUSE']['VALUE']]['ADDRESS']?>
                            </div>
                            <div class="clear"></div>
                        </div>

                        <div class="prop_area adress_val">
                            <div class="adress">Расстояние от адреса отгрузки до адреса доставки:</div>
                            <div class="val_adress"><?=$arItem['PROPERTIES']['ROUTE']['VALUE']?> км</div>
                            <div class="clear"></div>
                        </div>

                        <?
                        $route = $arResult['MIN_ROUTE'][$arItem['PROPERTIES']['CLIENT_WAREHOUSE']['VALUE']]['ROUTE'];
                        if ($route > 0) {
                        ?>
                            <div class="prop_area adress_val">
                                <div class="adress">Расстояние от базы автопарка до адреса доставки:</div>
                                <div class="val_adress"><?=$route?> км</div>
                                <div class="clear"></div>
                            </div>
                        <?
                        }

                        if (sizeof($arResult['CLIENT_WAREHOUSES_LIST'][$arItem['PROPERTIES']['CLIENT_WAREHOUSE']['VALUE']]['TRANSPORT']) > 0) {
                        ?>
                            <div class="prop_area adress_val">
                                <div class="adress">Тип транспорта:</div>
                                <?
                                foreach ($arResult['CLIENT_WAREHOUSES_LIST'][$arItem['PROPERTIES']['CLIENT_WAREHOUSE']['VALUE']]['TRANSPORT'] as $val) {
                                ?>
                                    <div class="val_adress"><?=$arResult['TRANSPORT_LIST'][$val]['NAME']?></div>
                                <?
                                }
                                ?>
                                <div class="clear"></div>
                            </div>
                        <?
                        }
                        ?>

                        <div class="prop_area total">
                            <?
                            $total_disabled = false;
                            $partner_id = $arItem['PROPERTIES']['PARTNER']['VALUE'];
                            if (!$arResult['LINKED_PARTNERS'][$partner_id]['ID']) {
                                echo '<div class="no_deal_rights">Для заключения сделки необходимо <a href="/transport/link_to_partner/">привязаться к организатору</a></div>';
                                $total_disabled = true;
                            }

                            /*if (intval($arResult['LINKED_PARTNERS'][$partner_id]['ID']) > 0) {
                                if (!$arResult['LINKED_PARTNERS'][$partner_id]['PROPERTY_PARTNER_LINK_DOC_VALUE']) {
                                    echo '<div class="no_deal_rights">Для заключения сделки необходимо наличие договора с <a href="/transport/link_to_partner/">организатором</a></div>';
                                    $total_disabled = true;
                                }
                            }
                            else {
                                echo '<div class="no_deal_rights">Для заключения сделки необходимо <a href="/transport/link_to_partner/">привязаться к организатору</a></div>';
                                $total_disabled = true;
                            }*/
                            ?>
                            <div class="name">Итого стоимость: </div>
                            <div class="val">
                                <span class="decs_separators"><?=$cost?></span> руб
                                <span class="val_type"></span>
                            </div>
                            <input type="submit" class="submit-btn <?if($total_disabled){?> inactive hard_disabled<?}?>" name="accept" value="Принять" />
                        </div>
                    </form>
                </div>
            <?
                $view_count++;
            }
            if($view_count==0){
                ?>
                <div class="no-item">
                    Ни одного запроса не найдено
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
            Ни одного запроса не найдено
        </div>
    <?
    }

}
else {
?>
    <div class="list_page_rows requests no-item">
        У вас нет ни одной базы автопарка
    </div>
<?
}
$templateData = $arResult;
?>