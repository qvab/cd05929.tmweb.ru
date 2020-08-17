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
$this->setFrameMode(true);?>

<?if (sizeof($arResult["ITEMS"]) > 0):?>
    <div class="list_page_rows requests">
        <?foreach ($arResult["ITEMS"] as $arItem):?>
            <div class="line_area" id="item_<?=$arItem['ID'];?>" data-offer="<?=$arItem['PROPERTIES']['OFFER']['VALUE'];?>" data-request="<?=$arItem['PROPERTIES']['REQUEST']['VALUE'];?>">
                <div class="line_inner">
                    <div class="name"><?=$arItem['OPPONENT_NAME']?></div>
                    <div class="id_date"># <?=$arItem['DATE_CREATE']?></div>
                    <div class="clear"></div>
                </div>
                <div  class="line_additional">
                    <div class="prop_area adress_val">
                        <div class="val_adress">
                            Пара: <a target="_blank" href="<?=$arItem['DEAL_LINK']?>">#<?=$arItem['DEAL_ID']?> от <?=reset(explode(' ', $arItem['DEAL_NAME']));?></a>
                        </div>
                        <div class="val_adress">
                            Культура: <?=$arItem['CULTURE_NAME']?>
                        </div>
                        <div class="val_adress">
                            Регион: <?=$arItem['DEAL_REGION_NAME']?>
                        </div>
                    </div>
                    <?php
                    if(isset($arParams['SHOW_DEL_BUTTON']) && $arParams['SHOW_DEL_BUTTON'] == 1
                        || isset($arItem['NO_LIMIT_CHECK'])
                    ){?>
                        <div class="prop_area bl_del">
                            <div class="val"></div>
                            <input type="button" data-id="<?=$arItem['ID']?>" name="del_bl" value="Активировать" class="submit-btn del_bl<?if(isset($arItem['NO_LIMIT_CHECK'])){?> agent_offer<?}?>">
                            <div style="clear: both"></div>
                        </div>
                    <?}?>
                </div>
            </div>
        <?endforeach;?>

        <?if($arParams["DISPLAY_BOTTOM_PAGER"]):?>
            <br /><?=$arResult["NAV_STRING"]?>
        <?endif;?>
    </div>
<?else:?>
    <div class="list_page_rows requests no-item">
        Ни одной записи не найдено
    </div>
<?endif;?>
