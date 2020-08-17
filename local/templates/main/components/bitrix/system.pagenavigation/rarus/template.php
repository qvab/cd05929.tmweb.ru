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

//use Bitrix\Main\Web\Uri;

if(!$arResult["NavShowAlways"]) {
    if ($arResult["NavRecordCount"] == 0 || ($arResult["NavPageCount"] == 1 && $arResult["NavShowAll"] == false))
        return;
}
?>
<div class="pagination">
    <?
    $strNavQueryString = ($arResult["NavQueryString"] != "" ? $arResult["NavQueryString"]."&amp;" : "");
    $strNavQueryStringFull = ($arResult["NavQueryString"] != "" ? "?".$arResult["NavQueryString"] : "");

    //$strNavQueryString = str_replace('&amp;', '&', $strNavQueryString);
    //$strNavQueryStringFull = str_replace('&amp;', '&', $strNavQueryStringFull);

    // to show always first and last pages
    $arResult["nStartPage"] = 1;
    $arResult["nEndPage"] = $arResult["NavPageCount"];

    $bFirst = true;
    $bPoints = false;
    if($arResult["NavPageCount"] > 1)
    do {
        if ($arResult["nStartPage"] <= 2 || $arResult["nEndPage"]-$arResult["nStartPage"] <= 1 || abs($arResult['nStartPage']-$arResult["NavPageNomer"])<=2) {
            if ($arResult["nStartPage"] == $arResult["NavPageNomer"]) {
                if ($arResult["NavPageNomer"] == 1) {
                    //$uri = new Uri($arResult["sUrlPath"].$strNavQueryStringFull);
                    //$uri->deleteParams(array("id"));
                    //$pageUrl = $uri->getUri();
                    ?>
                    <a href='<?=$arResult["sUrlPath"]?><?=$strNavQueryStringFull?>' class="link_page active_page">
                        <?=$arResult["nStartPage"]?>
                    </a>
                <?
                }
                else {
                    //$uri = new Uri($arResult["sUrlPath"].'?'.$strNavQueryString.'PAGEN_'.$arResult["NavNum"].'='.$arResult["NavPageNomer"]);
                    //$uri->deleteParams(array("id"));
                    //$pageUrl = $uri->getUri();
                    ?>
                    <a href='<?=$arResult["sUrlPath"]?>?<?=$strNavQueryString?>PAGEN_<?=$arResult["NavNum"]?>=<?=$arResult["NavPageNomer"]?>' class="link_page active_page">
                        <?=$arResult["nStartPage"]?>
                    </a>
                <?
                }
            }
            elseif ($arResult["nStartPage"] == 1 && $arResult["bSavePage"] == false) {
                //$uri = new Uri($arResult["sUrlPath"].$strNavQueryStringFull);
                //$uri->deleteParams(array("id"));
                //$pageUrl = $uri->getUri();
            ?>
                <a class="link_page" href="<?=$arResult["sUrlPath"]?><?=$strNavQueryStringFull?>">
                    <?=$arResult["nStartPage"]?>
                </a>
            <?
            }
            else {
                //$uri = new Uri($arResult["sUrlPath"].'?'.$strNavQueryString.'PAGEN_'.$arResult["NavNum"].'='.$arResult["nStartPage"]);
                //$uri->deleteParams(array("id"));
                //$pageUrl = $uri->getUri();
                if($arResult["nStartPage"] > 1) {
                    ?>
                    <a class="link_page"
                       href="<?=$arResult["sUrlPath"]?>?<?=$strNavQueryString ?>PAGEN_<?=$arResult["NavNum"]?>=<?=$arResult["nStartPage"] ?>">
                        <?=$arResult["nStartPage"] ?>
                    </a>
                    <?
                }else{
                    ?>
                    <a class="link_page" href="<?=$arResult["sUrlPath"]?><?=$strNavQueryStringFull?>">
                        <?=$arResult["nStartPage"] ?>
                    </a>
                    <?
                }
            }
            $bFirst = false;
            $bPoints = true;
        }
        else {
            if ($bPoints) {
                if ($arResult['nStartPage'] < $arResult['NavPageNomer']) {
                    $s = $arResult['nStartPage'] - 1;
                    $f = $arResult['NavPageNomer'] - 2;
                    $c = round(0.5 * ($s + $f), 0);
                }
                if ($arResult['nStartPage'] > $arResult['NavPageNomer']) {
                    $s = $arResult['nEndPage'] - 1;
                    $f = $arResult['NavPageNomer'] + 2;
                    $c = round(0.5 * ($s + $f), 0);
                }
                //$uri = new Uri($arResult["sUrlPath"].'?'.$strNavQueryString.'PAGEN_'.$arResult["NavNum"].'='.$c);
                //$uri->deleteParams(array("id"));
                //$pageUrl = $uri->getUri();
            ?>
                <a href='<?=$arResult["sUrlPath"]?>?<?=$strNavQueryString?>PAGEN_<?=$arResult["NavNum"]?>=<?=$c?>' class="link_page">...</a>
            <?
                $bPoints = false;
            }
        }
        $arResult["nStartPage"]++;
    } while($arResult["nStartPage"] <= $arResult["nEndPage"]);
    ?>
</div>