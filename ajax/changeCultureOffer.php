<?
header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header ("Cache-Control: no-cache, must-revalidate");
header ("Pragma: no-cache");
header("content-type: application/x-javascript; charset=UTF-8");
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
?>
<div class="request-block-intro">
    <div class="step-title row_head">3. Заполните данные о продукте</div>
    <?
    $cultureId = intval($_POST['csort']);
    if ($cultureId > 0) {
        $params = culture::getParamsListByCultureId($cultureId);

        if (is_array($params) && sizeof($params) > 0) {
        ?>
            <div class="row_val">
                <?
                foreach ($params as $item)
                {
                    if ($item["LBASE_ID"] > 0) {
                    ?>
                        <div class="sub_row">
                            <div class="quality-param-title"><?=$item["QUALITY_NAME"]?>:</div>
                            <div class="quality-param-intro">
                                <select name="param[<?=$item['QUALITY_ID']?>][LBASE]">
                                    <?
                                    foreach ($item["LIST"] as $l) {
                                    ?>
                                        <option value="<?=$l['ID']?>" <? if ($l["ID"] == $item["LBASE_ID"]) { ?>selected=""<? } ?>><?=$l["NAME"]?></option>
                                    <?
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                    <?
                    }
                    else {
                    ?>
                        <div class="sub_row txt">
                            <div class="quality-param-intro txt">
                                <div class="name"><?=$item["QUALITY_NAME"]?></div>
                                <div class="prop_cntrl_area">
                                    <span class="minus minus_bg" data-step="<?=$item['STEP']?>" onclick="rrsClickMin(this);"></span>
                                    <input type="text" name="param[<?=$item['QUALITY_ID']?>][BASE]" value="<?=$item['BASE']?>">
                                    <span class="plus plus_bg" data-step="<?=$item['STEP']?>" onclick="rrsClickMax(this);"></span>
                                </div>
                            </div>
                        </div>
                    <?
                    }
                }
                ?>
            </div>
        <?
        }
        else {
            ShowError("Ошибка! Ни одной характеристики для данного сорта не найдено");
        }
    }
    ?>
</div>