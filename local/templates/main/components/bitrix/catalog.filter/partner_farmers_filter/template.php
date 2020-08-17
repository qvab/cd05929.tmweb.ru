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
$arRemoveParams = ['status', 'warehouse_id', 'culture_id', 'q', 'best_price', 'PAGEN_1', 'id', 'request_id'];
$GLOBALS['el_count'] = 100;

//проверка соответствия выбранного фильтра и кук
$checkFilter = partner::filterPartnerFarmersCheck();
if($checkFilter['NEED_UPD']){
    //делаем переадресацию, если нужно
    LocalRedirect($checkFilter['URL_UPD']);
    exit;
}
?>
<?if(!empty($arResult['ITEMS'])):?>
<div class="tab_form">
    <form method="GET" class="farmer_select">

        <div class="row">
            <?$sDataSearch = (count($arResult['ITEMS']) > 4) ? 'data-search="y"' : null;?>
            <div class="wrap-select">

                <select <?=$sDataSearch?> name="farmer_id">
                    <option value="0">Все поставщики</option>
                    <?foreach($arResult['ITEMS'] as $cur_id => $cur_name):
                        $sSelected = ($_GET['farmer_id'] == $cur_id ? 'selected="selected"' : null);
                        ?>
                        <option <?=$sSelected?> value="<?=$cur_id?>"><?=$cur_name?></option>
                    <?endforeach;?>
                </select>
            </div>
        </div>
        <div class="row">
            <div class="wrap-select">
                <select name="is_linked">
                    <?
                    $active_link_type = 0;
                    if(isset($_GET['is_linked'])
                        && ($_GET['is_linked'] == 1
                            ||
                            $_GET['is_linked'] == 2
                        )
                    ){
                        $active_link_type = $_GET['is_linked'];
                    }
                    ?>
                    <option value="0" <?=($active_link_type == 0 ? 'selected="selected"' : '');?>>Привязанные / не привязанные</option>
                    <option value="1" <?=($active_link_type == 1 ? 'selected="selected"' : '');?>>Привязанные поставщики</option>
                    <option value="2" <?=($active_link_type == 2 ? 'selected="selected"' : '');?>>Не привязанные поставщики</option>
                </select>
            </div>
        </div>

        <div class="clear"></div>

        <div class="row second_line">
            <?
            $sDataSearch = (count($arResult['REGION_LIST']) > 4) ? 'data-search="y"' : null;?>
            <div class="wrap-select">
                <select <?=$sDataSearch?> name="region_id">
                    <option value="0">Все регионы</option>
                    <?foreach($arResult['REGION_LIST'] as $cur_id => $cur_name):
                        $sSelected = ($_GET['region_id'] == $cur_id ? 'selected="selected"' : null);
                        ?>
                        <option <?=$sSelected?> value="<?=$cur_id?>"><?=$cur_name?></option>
                    <?endforeach;?>
                </select>
            </div>
        </div>

        <div class="row second_line">
            <?$sDataSearch = (count($arResult['CULTURE_LIST']) > 4) ? 'data-search="y"' : null;?>
            <div class="wrap-select">
                <select <?=$sDataSearch?> name="culture_id">
                    <option value="0">Все культуры</option>
                    <?foreach($arResult['CULTURE_LIST'] as $cur_id => $cur_name):
                        $sSelected = ($_GET['culture_id'] == $cur_id ? 'selected="selected"' : null);
                        ?>
                        <option <?=$sSelected?> value="<?=$cur_id?>"><?=$cur_name?></option>
                    <?endforeach;?>
                </select>
            </div>
        </div>

        <div class="clear"></div>

        <div class="row">
            <div class="wrap-btn">
                <input class="submit-btn" value="Применить" type="submit">
            </div>
        </div>
        <div class="row">
            <div class="wrap-btn">
                <input class="submit-btn reset" value="Сбросить" type="button">
            </div>
        </div>
            <div class="clear"></div>

    </form>
</div>
<?endif;?>

<?
$filterName = $arParams['FILTER_NAME'];
if ($_GET['id']) {
    $newsCount = $GLOBALS['el_count'];
    $curPage = $_GET['PAGEN_1'];
    if (!$curPage)
        $curPage = 1;
    $arFilter = array_merge($GLOBALS[$filterName], array('IBLOCK_ID' => $arParams['IBLOCK_ID'], 'ACTIVE' => 'Y'));
    $res = CIBlockElement::GetList(
        Array("PROPERTY_ACTIVE" => "ASC", "ACTIVE_TO" => "DESC"),
        $arFilter,
        false,
        array("nPageSize"=>0, "nElementID"=>$_GET['id']),
        array(
            'ID'
        )
    );
    if ($ob = $res->GetNext()) {
        $page = floor(($ob['RANK'] - 1)/$newsCount) + 1;
        if ($page != $curPage) {
            if ($page == 1) {
                LocalRedirect($APPLICATION->GetCurPageParam("", array('warehouse_id', 'culture_id', 'q', 'best_price', 'PAGEN_1')));
            }
            else {
                LocalRedirect($APPLICATION->GetCurPageParam("PAGEN_1=".$page, array('warehouse_id', 'culture_id', 'q', 'best_price')));
            }
        }
    }
    elseif (!$_GET['status']) {
        LocalRedirect($APPLICATION->GetCurPageParam("status=no", array('warehouse_id', 'culture_id', 'q', 'best_price', 'PAGEN_1')));
    }
}
?>