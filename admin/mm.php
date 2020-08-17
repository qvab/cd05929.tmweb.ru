<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");?>
<?$APPLICATION->SetTitle("Текущие паритетные цены");?>
<br><br><br><br>
<?
if ($_POST['save']) {
    $arrCentersToRegions = array();
    foreach ($_POST['data'] as $center_id => $data) {
        $arrCentersToRegions[$center_id] = 1;
    }
    if(count($arrCentersToRegions) > 0){
        $arrCentersToRegions = model::getRegionByCenter(array_keys($arrCentersToRegions));
    }

    foreach ($_POST['data'] as $center_id => $data) {
        foreach ($data as $culture_id => $val) {
            $arPrices = model::MathCalculation($center_id, $culture_id);
            $id = model::saveParityPrice($center_id, $culture_id, $arPrices, $arrCentersToRegions);
            if ($id > 0) {
                log::addParityPriceLog($center_id, $culture_id, 'пересчет мат. модели', 'mm', $arPrices);
            }
        }
    }

    LocalRedirect('/admin/mm.php');
}

CModule::IncludeModule('iblock');
$res = CIBlockElement::GetList(
    array('PROPERTY_REGION.NAME' => 'ASC', 'PROPERTY_CULTURE.NAME' => 'ASC'),
    array(
        'IBLOCK_ID' => rrsIblock::getIBlockId('reg_center_leader'),
        'ACTIVE' => 'Y'
    ),
    false,
    false,
    array(
        'ID',
        'NAME',
        'PROPERTY_REGION',
        'PROPERTY_REGION.NAME',
        'PROPERTY_CENTER',
        'PROPERTY_CENTER.NAME',
        'PROPERTY_CULTURE',
        'PROPERTY_CULTURE.NAME',
    )
);
while ($ob = $res->Fetch()) {
    $centerList[$ob['PROPERTY_REGION_VALUE']][] = $ob;
}

if ($_POST['calc']) {
    if (sizeof($_POST['centers']) > 0 && sizeof($_POST['cultures']) > 0) {
        unset($_POST['data']);
        if (in_array('all', $_POST['centers'])) {
            if (in_array('all', $_POST['cultures'])) {
                foreach ($centerList as $center) {
                    foreach ($cultureList as $culture) {
                        $_POST['data'][$center['ID']][$culture['ID']] = 'Y';
                    }
                }
            }
            else {
                foreach ($centerList as $center) {
                    foreach ($_POST['cultures'] as $culture) {
                        $_POST['data'][$center['ID']][$culture] = 'Y';
                    }
                }
            }
        }
        else {
            if (in_array('all', $_POST['cultures'])) {
                foreach ($_POST['centers'] as $center) {
                    foreach ($cultureList as $culture) {
                        $_POST['data'][$center][$culture['ID']] = 'Y';
                    }
                }
            }
            else {
                foreach ($_POST['centers'] as $center) {
                    foreach ($_POST['cultures'] as $culture) {
                        $_POST['data'][$center][$culture] = 'Y';
                    }
                }
            }
        }
    }

    $calcPrices =array();
    foreach ($_POST['data'] as $center_id => $data) {
        foreach ($data as $culture_id => $val) {
            $calcPrices[$center_id][$culture_id] = model::MathCalculation($center_id, $culture_id);
        }
    }
}

$priceList = model::getParityPriceList();
?>
<form action="" method="post">
    <div class="row">
        <?
        if ($_POST['calc']) {
        ?>
            <input type="submit" name="save" class="submit-btn" value="Сохранить">
        <?
        }
        else {
        ?>
            <input type="submit" name="calc" class="submit-btn" value="Пересчитать">
        <?
        }
        ?>
        <div class="radio_group all-region">
            <div class="cinput radio_area">
                <input type="checkbox" id="all_region">
            </div>
            <div class="row_head">
                Все регионы
            </div>
        </div>
    </div>
    <?/*<div>
        <div>Выбрать региональные центры</div>
        <select name="centers[]" multiple="multiple">
            <option value="all" <? if (in_array('all', $_POST['centers'])) { ?>selected="selected"<? } ?>>все</option>
            <?
            foreach ($centerList as $center) {
            ?>
                <option value="<?=$center['ID']?>" <? if (in_array($center['ID'], $_POST['centers'])) { ?>selected="selected"<? } ?>><?=$center['NAME']?></option>
            <?
            }
            ?>
        </select>
    </div>
    <div>
        <div>Выбрать культуры</div>
        <select name="cultures[]" multiple="multiple">
            <option value="all" <? if (in_array('all', $_POST['cultures'])) { ?>selected="selected"<? } ?>>все</option>
            <?
            foreach ($cultureList as $culture) {
            ?>
                <option value="<?=$culture['ID']?>" <? if (in_array($culture['ID'], $_POST['cultures'])) { ?>selected="selected"<? } ?>><?=$culture['NAME']?></option>
            <?
            }
            ?>
        </select>
    </div>*/?>
    <?
    $postData = array_keys($_POST['data']);
    $openRegions = array();
    foreach ($centerList as $region) {
        foreach ($region as $center) {
            if (in_array($center['PROPERTY_CENTER_VALUE'], $postData)) {
                $openRegions[$center['PROPERTY_REGION_VALUE']] = true;
                break;
            }
        }
    }

    foreach ($centerList as $key => $region) {
        $iRegionId = current($region)['ID'];
        $class = '';
        $content = '+';
        if (in_array($key, array_keys($openRegions))) {
            $class = 'open';
            $content = '&ndash;';
        }
        ?>
        <div class="row rc_block <?=$class?>">

            <div class="row_head">
                <span><?=$content?></span>
                <?=current($region)['PROPERTY_REGION_NAME']?>
            </div>

            <div class="radio_group rc_list">
                <div class="crow ctitle clear">
                    <div class="cinput radio_area region" region-id="<?=$iRegionId?>">
                        <input region-id="<?=$iRegionId?>" type="checkbox" class="region-culture">
                    </div>
                    <div class="cname">
                        Культура / Рег. центр
                    </div>
                    <div class="cprice">
                        Цена пассивной закупки
                    </div>
                    <div class="cprice">
                        Цена стандартной закупки
                    </div>
                    <div class="cprice">
                        Цена активной закупки
                    </div>
                </div>
                <?
                foreach ($region as $center) {
                ?>
                    <div class="crow radio_group clear cultures" region-id="<?=$iRegionId?>">

                        <div class="cinput radio_area">
                            <? if (!$_POST['calc'] || ($_POST['calc'] && $calcPrices[$center['PROPERTY_CENTER_VALUE']][$center['PROPERTY_CULTURE_VALUE']]['PRICE'] > 0)):?>
                                <?$sChecked = ($calcPrices[$center['PROPERTY_CENTER_VALUE']][$center['PROPERTY_CULTURE_VALUE']]['PRICE'] > 0) ? 'checked="checked"' : null?>
                                <input region-id="<?=$iRegionId?>" type="checkbox" name="data[<?=$center['PROPERTY_CENTER_VALUE']?>][<?=$center['PROPERTY_CULTURE_VALUE']?>]" <?=$sChecked?> value="Y">
                            <?endif;?>
                        </div>
                        <div class="cname">
                            <?=$center['PROPERTY_CULTURE_NAME']?> / <?=$center['PROPERTY_CENTER_NAME']?>
                        </div>
                        <div class="cprice">
                            <?
                            if (isset($priceList[$center['PROPERTY_CENTER_VALUE']][$center['PROPERTY_CULTURE_VALUE']]['PRICE_PASSIVE'])) {
                            ?>
                                <div class="price_y"><?=$priceList[$center['PROPERTY_CENTER_VALUE']][$center['PROPERTY_CULTURE_VALUE']]['PRICE_PASSIVE']?> р/тн</div>
                            <?
                            }
                            else {
                            ?>
                                <div class="price_n">-</div>
                            <?
                            }

                            if ($_POST['calc'] && $_POST['data'][$center['PROPERTY_CENTER_VALUE']][$center['PROPERTY_CULTURE_VALUE']] == 'Y') {
                                if ($calcPrices[$center['PROPERTY_CENTER_VALUE']][$center['PROPERTY_CULTURE_VALUE']]['PRICE_PASSIVE'] > 0) {
                                ?>
                                    <div class="price_yn"><?=round($calcPrices[$center['PROPERTY_CENTER_VALUE']][$center['PROPERTY_CULTURE_VALUE']]['PRICE_PASSIVE'], 2)?> р/тн</div>
                                <?
                                }
                                else {
                                ?>
                                    <div class="price_n">-</div>
                                <?
                                }
                            }
                            ?>
                        </div>
                        <div class="cprice">
                            <?
                            if (isset($priceList[$center['PROPERTY_CENTER_VALUE']][$center['PROPERTY_CULTURE_VALUE']]['PRICE_STANDART'])) {
                            ?>
                                <div class="price_y"><?=$priceList[$center['PROPERTY_CENTER_VALUE']][$center['PROPERTY_CULTURE_VALUE']]['PRICE_STANDART']?> р/тн</div>
                            <?
                            }
                            else {
                            ?>
                                <div class="price_n">-</div>
                            <?
                            }

                            if ($_POST['calc'] && $_POST['data'][$center['PROPERTY_CENTER_VALUE']][$center['PROPERTY_CULTURE_VALUE']] == 'Y') {
                                if ($calcPrices[$center['PROPERTY_CENTER_VALUE']][$center['PROPERTY_CULTURE_VALUE']]['PRICE_STANDART'] > 0) {
                                ?>
                                    <div class="price_yn"><?=round($calcPrices[$center['PROPERTY_CENTER_VALUE']][$center['PROPERTY_CULTURE_VALUE']]['PRICE_STANDART'], 2)?> р/тн</div>
                                <?
                                }
                                else {
                                ?>
                                    <div class="price_n">-</div>
                                <?
                                }
                            }
                            ?>
                        </div>
                        <div class="cprice">
                            <?
                            if (isset($priceList[$center['PROPERTY_CENTER_VALUE']][$center['PROPERTY_CULTURE_VALUE']]['PRICE_ACTIVE'])) {
                            ?>
                                <div class="price_y"><?=$priceList[$center['PROPERTY_CENTER_VALUE']][$center['PROPERTY_CULTURE_VALUE']]['PRICE_ACTIVE']?> р/тн</div>
                            <?
                            }
                            else {
                            ?>
                                <div class="price_n">-</div>
                            <?
                            }

                            if ($_POST['calc'] && $_POST['data'][$center['PROPERTY_CENTER_VALUE']][$center['PROPERTY_CULTURE_VALUE']] == 'Y') {
                                if ($calcPrices[$center['PROPERTY_CENTER_VALUE']][$center['PROPERTY_CULTURE_VALUE']]['PRICE_ACTIVE'] > 0) {
                                ?>
                                    <div class="price_yn"><?=round($calcPrices[$center['PROPERTY_CENTER_VALUE']][$center['PROPERTY_CULTURE_VALUE']]['PRICE_ACTIVE'], 2)?> р/тн</div>
                                <?
                                }
                                else {
                                ?>
                                    <div class="price_n">-</div>
                                <?
                                }
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
        <?
        if ($_POST['calc']) {
        ?>
            <input type="submit" name="save" class="submit-btn" value="Сохранить">
        <?
        }
        else {
        ?>
            <input type="submit" name="calc" class="submit-btn" value="Пересчитать">
        <?
        }
        ?>
    </div>
</form>

<style type="text/css">
    .crow {
        margin: 8px 0;
        padding: 8px;
        font-size: 16px;
        line-height: 20px;
        border-radius: 4px;
    }
    .crow:hover {
        background: #c9dbea;
    }
    .crow.ctitle {
        background: #e6e6e6;
    }
    .cinput {
        float: left;
        width: 2%;
    }
    .cname {
        float: left;
        width: 50%;
        padding-left: 10px;
        box-sizing: border-box;
    }
    .cprice {
        float: left;
        width: 16%;
        text-align: right;
    }
    .price_y {
        color: #008000;
    }
    .price_n {
        color: #d71212;
    }
    .price_yn {
        color: #008000;
        font-weight: bold;
    }
    .all-region {
        padding-top: 15px;
    }
    .all-region .row_head {
       margin-left: 25px;
    }

    .rc_block .row_head {
        cursor: pointer;
    }
    .rc_block .row_head:hover {
        text-decoration: underline;
    }
    .rc_block .rc_list {
        display: none;
        margin: 24px 24px 0 24px;
    }
    .rc_block .row_head span {
        display: inline-block;
        padding: 2px 6px;
        //cursor: pointer;
        color: #FFFFFF;
        margin-right: 12px;
        width: 12px;
        text-align: center;
        background: #47a1f0;
    }
    .rc_block.open .rc_list {
        display: block;
    }
    .rc_block.open .row_head span {

    }
</style>

<script>
$(function () {
    //скрыть/открыть список культур/рег. центров
    $('.rc_block .row_head').click(function () {
        var obj = $(this).parents('.rc_block');
        obj.toggleClass('open');

        if (obj.hasClass('open')) {
            $(this).find('span').html('&ndash;');
        }
        else {
            $(this).find('span').html('+');
        }
    });

    /*$('.rc_block .row_head span').click(function () {
        var obj = $(this).parents('.rc_block');
        obj.toggleClass('open');

        if ((obj).hasClass('open')) {
            $(this).html('&ndash;');
        }
        else {
            $(this).html('+');
        }
    });*/

    // Все регионы
    $('#all_region').click(function () {

        var bChecked = $(this).prop('checked');

        if(bChecked) {
            $('.crow.cultures .custom_input, .row .region .custom_input').addClass('checked');
        } else {
            $('.crow.cultures .custom_input, .row .region .custom_input').removeClass('checked');
        }

        $('.crow.cultures input[type="checkbox"], .row .region input[type="checkbox"]').prop('checked', bChecked).trigger('change');
    });


    // Все культуры в регионе
    $('.row input.region-culture').click(function () {

        var regionId = parseInt($(this).attr('region-id'));
        var bChecked = $(this).prop('checked');

        if(bChecked) {
            $('.crow.cultures[region-id="'+regionId+'"] .custom_input').addClass('checked');
            $('.row .region[region-id="'+regionId+'"] .custom_input').addClass('checked');
        } else {
            $('.crow.cultures[region-id="'+regionId+'"] .custom_input').removeClass('checked');
            $('.row .region[region-id="'+regionId+'"] .custom_input').removeClass('checked');
        }
        $('.crow.cultures[region-id="'+regionId+'"] input[type="checkbox"]').prop('checked', bChecked).trigger('change');
    });


    // Изменение культуры
    $('.crow.cultures input[type="checkbox"]').click(function () {

        var regionId = parseInt($(this).attr('region-id'));

        var bNotChecked = false;

        if($('.crow.cultures[region-id="'+regionId+'"] input[type="checkbox"]').not(':checked').length > 0) {
            bNotChecked = true;
        }

        if(bNotChecked) {
            $('.row .all-region .custom_input').removeClass('checked');
            $('#all_region').prop('checked', false).trigger("change");
            $('.row .region[region-id="'+regionId+'"] .custom_input').removeClass('checked');
        } else {
            $('.row .region[region-id="'+regionId+'"] .custom_input').addClass('checked');
        }

        $('.region[region-id="'+regionId+'"] input[type="checkbox"]').prop('checked', !bNotChecked).trigger("change");
    });
});    
</script>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>