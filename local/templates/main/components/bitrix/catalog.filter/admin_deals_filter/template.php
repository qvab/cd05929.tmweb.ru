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

$arRemoveParams = [
    'status',
    'client_warehouse_id',
    'farmer_warehouse_id',
    'culture_id',
    'client_id',
    'farmer_id',
    'distance_id',
    'PAGEN_1'
];
?>
<div class="tab_form">
    <?
    if ($arResult['SHOW_FORM']) {
    ?>
        <form method="GET" id="deals_filter">
            <div class="row">
                <?
                if (!empty($arResult['CULTURE_LIST'])) {
                ?>
                    <?$sDataSearch = (count($arResult['CULTURE_LIST']) > 3) ? 'data-search="y"' : null;?>
                    <div class="wrap-select">
                        <select <?=$sDataSearch?> name="culture" placeholder="Выберите культуру">
                            <option value="0">Все культуры</option>
                            <?
                            foreach ($arResult['CULTURE_LIST'] as $arCulture) {
                            ?>
                                <?$sSelected = ($_GET['culture'] == $arCulture['ID']) ? 'selected="selected"' : null?>
                                <option <?=$sSelected?> value="<?=$arCulture['ID']?>"><?=$arCulture['NAME']?></option>
                            <?
                            }
                            ?>
                        </select>
                    </div>
                <?
                }

                if (!empty($arResult['CLIENT_LIST'])) {
                ?>
                    <?$sDataSearch = (count($arResult['CLIENT_LIST']) > 3) ? 'data-search="y"' : null;?>
                    <div class="wrap-select">
                        <select <?=$sDataSearch?> name="client" placeholder="Выберите покупателя">
                            <option value="0">Все покупатели</option>
                            <?
                            foreach ($arResult['CLIENT_LIST'] as $iClientId => $arClient) {
                            ?>
                                <?$sSelected = ($_GET['client'] == $iClientId) ? 'selected="selected"' : null?>
                                <option <?=$sSelected?> value="<?=$iClientId?>"><?=$arClient['COMPANY']?></option>
                            <?
                            }
                            ?>
                        </select>
                    </div>
                <?
                }

                if (!empty($arResult['FARMER_LIST'])) {
                ?>
                    <?$sDataSearch = (count($arResult['FARMER_LIST']) > 3) ? 'data-search="y"' : null;?>
                    <div class="wrap-select">
                        <select <?=$sDataSearch?> name="farmer" placeholder="Выберите поставщика">
                            <option value="0">Все поставщики</option>
                            <?
                            foreach ($arResult['FARMER_LIST'] as $iFarmerId => $arFarmer) {
                            ?>
                                <?$sSelected = ($_GET['farmer'] == $iFarmerId) ? 'selected="selected"' : null?>
                                <option <?=$sSelected?> value="<?=$iFarmerId?>"><?=$arFarmer['COMPANY']?></option>
                            <?
                            }
                            ?>
                        </select>
                    </div>
                <?
                }

                if (!empty($arResult['PARTNER_LIST'])) {
                ?>
                    <?$sDataSearch = (count($arResult['PARTNER_LIST']) > 3) ? 'data-search="y"' : null;?>
                    <div class="wrap-select">
                        <select <?=$sDataSearch?> name="partner" placeholder="Выберите организатора">
                            <option value="0">Все организаторы</option>
                            <?
                            foreach ($arResult['PARTNER_LIST'] as $iPartnerId => $arPartner) {
                            ?>
                                <?$sSelected = ($_GET['partner'] == $iPartnerId) ? 'selected="selected"' : null?>
                                <option <?=$sSelected?> value="<?=$iPartnerId?>"><?=$arPartner['COMPANY']?></option>
                            <?
                            }
                            ?>
                        </select>
                    </div>
                <?
                }

                if (!empty($arResult['TRANSPORT_LIST'])) {
                ?>
                    <?$sDataSearch = (count($arResult['TRANSPORT_LIST']) > 3) ? 'data-search="y"' : null;?>
                    <div class="wrap-select">
                        <select <?=$sDataSearch?> name="transport" placeholder="Выберите перевозчика">
                            <option value="0">Все перевозчики</option>
                            <?
                            foreach ($arResult['TRANSPORT_LIST'] as $iTransportId => $arTransport) {
                            ?>
                                <?$sSelected = ($_GET['transport'] == $iTransportId) ? 'selected="selected"' : null?>
                                <option <?=$sSelected?> value="<?=$iTransportId?>"><?=$arTransport['COMPANY']?></option>
                            <?
                            }
                            ?>
                        </select>
                    </div>
                <?
                }

                if (!empty($arResult['STAGE_LIST'])) {
                ?>
                    <?$sDataSearch = (count($arResult['STAGE_LIST']) > 3) ? 'data-search="y"' : null;?>
                    <div class="wrap-select">
                        <select <?=$sDataSearch?> name="status" placeholder="Выберите статус">
                            <option value="0">Любой статус</option>
                            <?
                            foreach ($arResult['STAGE_LIST'] as $arStatus) {
                            ?>
                                <?$sSelected = ($_GET['status'] == $arStatus['ID']) ? 'selected="selected"' : null?>
                                <option <?=$sSelected?> value="<?=$arStatus['ID']?>"><?=$arStatus['VALUE']?></option>
                            <?
                            }
                            ?>
                        </select>
                    </div>
                <?
                }
                ?>
                <div class="clear"></div>
            </div>

            <div class="row">
                <div class="wrap-btn">
                    <input class="submit-btn" value="Применить" type="submit">
                </div>
                <div class="wrap-btn">
                    <input class="submit-btn reset" value="Сбросить" type="button">
                </div>
                <div class="clear"></div>
            </div>
        </form>
    <?
    }
    ?>
</div>

<?
$filterName = $arParams['FILTER_NAME'];
$GLOBALS[$filterName] = array(
    'IBLOCK_ID' => rrsIblock::getIBlockId('deals_deals'),
    'ACTIVE' => 'Y',
    'PROPERTY_STATUS' => rrsIblock::getPropListKey('deals_deals', 'STATUS', 'open'),
);

//культура
if (intval($_GET['culture']) > 0) {
    $GLOBALS[$filterName]['PROPERTY_CULTURE'] = $_GET['culture'];
}

//покупатель
if (intval($_GET['client']) > 0) {
    $GLOBALS[$filterName]['PROPERTY_CLIENT'] = $_GET['client'];
}

//поставщик
if (intval($_GET['farmer']) > 0) {
    $GLOBALS[$filterName]['PROPERTY_FARMER'] = $_GET['farmer'];
}

//организатор
if (intval($_GET['partner']) > 0) {
    $GLOBALS[$filterName]['PROPERTY_PARTNER'] = $_GET['partner'];
}

//перевозчик
if (intval($_GET['transport']) > 0) {
    $GLOBALS[$filterName]['PROPERTY_TRANSPORT'] = $_GET['transport'];
}

//статус
if (intval($_GET['status']) > 0) {
    $GLOBALS[$filterName]['PROPERTY_STAGE'] = $_GET['status'];
}
?>