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
$assetObj = \Bitrix\Main\Page\Asset::getInstance();
$assetObj->addJs($this->GetFolder() . '/js/script.js');

if (count($arResult["ITEMS"]) > 0) {
?>
    <form action="" method="get" class="region_filter">
        <div class="fields">
            <div class="field row">
                <div class="row_head">Регион</div>
                <div class="row_val">
                    <select data-search="y" name="region_id">
                        <?foreach ($arResult["ITEMS"] as $arItem) {?>
                            <option value="<?=$arItem['ID']?>" <? if ($arItem['ID'] == $arParams['REGION_ID']) { ?>selected="selected"<? } ?>><?=$arItem['NAME']?></option>
                        <?}?>
                    </select>

                    <input type="submit" class="submit-btn" value="Применить" />
                </div>
            </div>
        </div>
    </form>
<?
}