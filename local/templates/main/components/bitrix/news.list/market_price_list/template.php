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
if (sizeof($arResult['PRICE_LIST']) > 0) {
?>
    <form action="" method="post">
        <div class="list_page_rows market">
            <?
            foreach($arResult['PRICE_LIST'] as $arCulture) {
            ?>
                <div class="line_area yes active">
                    <div class="title">
                        <?=$arCulture['NAME']?>
                    </div>
                    <div class="type_list_block">
                        <?
                        foreach ($arCulture['TYPE'] as $key => $arType) {
                            if ($key != 0) {
                            ?>
                                <div class="split"></div>
                            <?
                            }
                            ?>
                            <div class="type_item">
                                <div class="type_title">
                                    <?=$arType['NAME']?>:
                                </div>
                                <div class="model_list">
                                <?
                                foreach ($arType['MODEL'] as $model) {
                                ?>
                                    <div class="model_item">
                                        <div class="model_title">
                                            <?=$model['NAME']?>
                                        </div>
                                        <div class="model_input">
                                            <input type="text" name="price[<?=$arCulture['ID']?>][<?=$arType['ID']?>][<?=$model['ID']?>]" value="<?=$model['PRICE']?>">
                                            <span>$</span>
                                        </div>
                                    </div>
                                <?
                                }
                                ?>
                                </div>
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
                <input type="submit" name="save" class="submit-btn" value="Сохранить">
            </div>
        </div>
    </form>
    <?
    /*
?>
    <div class="list_page_rows requests notifications">
        <?
        foreach($arResult["ITEMS"] as $arItem) {
        ?>
            <div class="line_area<? if ($arItem['PROPERTIES']['READ']['VALUE'] == 'Y') {?> cancel<? } ?>" id="<?=$this->GetEditAreaId($arItem['ID']);?>">
                <a href="<?=$arItem['PROPERTIES']['LINK_HREF']['VALUE']?>" class="line_inner">
                    <div class="name"><?=$arItem['NAME']?></div>
                    <div class="tons"><?=$arItem['PROPERTIES']['LINK_NAME']['VALUE']?></div>
                    <div class="price" style="width: 150px;"><?=$arItem['DATE_CREATE']?></div>
                    <div class="arw_icon"></div>
                    <div class="clear"></div>
                </a>
            </div>
        <?
        }
        ?>

        <?if($arParams["DISPLAY_BOTTOM_PAGER"]):?>
            <br /><?=$arResult["NAV_STRING"]?>
        <?endif;?>
    </div>
<?
}
else {
?>
    <div class="list_page_rows requests">
        На данный момент оповещений нет.
    </div>
<?*/
}

?>