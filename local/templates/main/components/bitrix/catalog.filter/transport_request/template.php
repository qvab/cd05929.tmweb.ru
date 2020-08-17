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
$transportObj = new transport();
$checkFilter = $transportObj->filterTransportRequestCheck();
if($checkFilter['NEED_UPD']){
    //делаем переадресацию, если нужно
    LocalRedirect($checkFilter['URL_UPD']);
    exit;
}


?>
<h1 class="page_header"><?$APPLICATION->ShowTitle()?></h1>
<div class="tab_form">

    <?if(!empty($arResult['ERROR_MSG'])):?>
        <div class="error-msg">
            <?showError($arResult['ERROR_MSG']);?>
        </div>
    <?endif;?>

    <?if($arResult['SHOW_FORM']):?>

        <form method="GET" id="transport_request" name="transport_request">

            <?if(!empty($arResult['CULTURE_LIST'])):?>
                <!--Культура-->
                <?$sDataSearch = (count($arResult['CULTURE_LIST']) > 3) ? 'data-search="y"' : null;?>
                <div class="row wrap-select">
                    <select <?=$sDataSearch?> name="culture_id" placeholder="Выберите культуру">
                        <option value="0">Все культуры</option>
                        <?foreach ($arResult['CULTURE_LIST'] as $arCulture):?>
                            <?$sSelected = ($_GET['culture_id'] == $arCulture['ID']) ? 'selected="selected"' : null?>
                            <option <?=$sSelected?> value="<?=$arCulture['ID']?>"><?=$arCulture['NAME']?></option>
                        <?endforeach;?>
                    </select>
                </div>
            <?endif;?>

            <?if(!empty($arResult['DISTANCE_LIST'])):?>
                <!--Расстояния-->
                <?$sDataSearch = (count($arResult['DISTANCE_LIST']) > 3) ? 'data-search="y"' : null;?>
                <div class="row wrap-select">
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

            <div class="row wrap-btn">
                <input class="submit-btn" value="Применить" type="submit">
            </div>
            <div class="row wrap-btn">
                <input class="submit-btn reset" value="Сбросить" type="button">
            </div>
            <div class="clear"></div>

        </form>
    <?endif;?>
</div>