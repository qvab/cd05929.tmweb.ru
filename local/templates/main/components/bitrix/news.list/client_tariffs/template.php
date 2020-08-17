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
<div class="content">
    <div class="prop_area adress_val">
        <div class="client_tariffs">
            <form action="" method="post">
                <input type="hidden" name="action" value="<?=$arResult['ACTION']?>">

                <div class="stuck-head ">
                    <?foreach ($arResult['GROUP_CULTURES'] as $group):?>
                        <div class="group_title"><?=$group['NAME']?></div>
                    <?endforeach;?>
                </div>
                <?
                $k = 1;
                foreach ($arResult['GROUP_CULTURES'] as $group) {
                ?>
                    <div class="group_item i<?=$k?>">
                        <div class="group_title"><?=$group['NAME']?></div>
                        <?
                        foreach ($arResult['TARIFFS'][$group['ID']] as $key => $arItem) {
                        ?>
                            <div class="wh_price">
                                <div class="tariff_name"><?=$arItem['NAME']?></div>
                                <div class="sub_row txt">
                                    <div class="min_price">
                                        <?=number_format($arItem['MIN'], 0, ',', ' ')?>
                                        <span>min</span>
                                    </div>
                                    <div class="quality-param-intro txt">
                                        <span class="minus minus_bg" data-step="10" onclick="rrsClickMinPrice(this);" data-min="<?=$arItem['MIN']?>"></span>
                                        <input type="text" name="" onchange="changeAfter(this);" onkeypress="changeVal();" value="<?=number_format($arItem['VALUE'], 0, ',', ' ')?>" >
                                        <span class="plus plus_bg" data-step="10" onclick="rrsClickMaxPrice(this);" data-max="<?=$arItem['MAX']?>"></span>
                                    </div>
                                    <div class="max_price">
                                        <?=number_format($arItem['MAX'], 0, ',', ' ')?>
                                        <span>max</span>
                                    </div>
                                </div>
                                <input type="hidden" class="tarif_val" name="tariffs[<?=$group['ID']?>][<?=$key?>][VALUE]" value="<?=$arItem['VALUE']?>">
                                <?
                                if (intval($arItem['ID']) > 0){
                                ?>
                                    <input type="hidden" name="tariffs[<?=$group['ID']?>][<?=$key?>][ID]" value="<?=$arItem['ID']?>">
                                <?
                                }
                                ?>
                            </div>
                        <?
                        }
                        ?>
                    </div>
                    <?
                    $k++;
                }
                ?>
                <div class="row">
                    <input type="submit" name="iblock_submit" class="submit-btn" value="Сохранить тарифы">
                </div>
            </form>
        </div>
        <div class="clear"></div>
    </div>
</div>