<?
header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header ("Cache-Control: no-cache, must-revalidate");
header ("Pragma: no-cache");
header("content-type: application/x-javascript; charset=UTF-8");
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
?>
<div class="request-block-intro row">
    <div class="step-title row_head">3. Указана стандартная сетка прямых сбросов, скорректируйте по необходимости</div>
    <?
    $cultureId = intval($_POST['csort']);
    if ($cultureId > 0) {
        $params = culture::getParamsListByCultureId($cultureId);

        if (is_array($params) && sizeof($params) > 0) {
        ?>
            <div class="row_val">
                <?
                foreach ($params as $item) {
                    if ($item["LBASE_ID"] > 0) {
                    ?>
                        <div class="sub_row">
                            <div class="quality-param-title"><?=$item["QUALITY_NAME"]?>:</div>
                            <div class="quality-param-intro">
                                <select name="param[<?=$item['QUALITY_ID']?>][LBASE]">
                                    <?
                                    foreach ($item["LIST"] as $l) {
                                    ?>
                                        <option value="<?=$l['ID']?>" <? if ($l["ID"] == $item["LBASE_ID"]) { ?>selected="selected"<? } ?>><?=$l["NAME"]?></option>
                                    <?
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                    <?
                    }
                    else {

                        $bIsReset = filter_var($item['RESET_BELOW_BASIS'], FILTER_VALIDATE_FLOAT) !== false || filter_var($item['RESET_MORE_BASIS'], FILTER_VALIDATE_FLOAT) !== false;
                    ?>
                        <div class="sub_row txt">
                            <div class="quality-param-intro txt">
                                <div class="name"><?=$item["QUALITY_NAME"]?></div>
                                <div class="prop_cntrl_area">
                                    <span class="minus minus_bg" data-step="<?=$item['STEP']?>" onclick="rrsClickMin(this);"></span>
                                    <input type="text" name="param[<?=$item['QUALITY_ID']?>][BASE]" value="<?=$item['BASE']?>">
                                    <span class="plus plus_bg" data-step="<?=$item['STEP']?>" onclick="rrsClickMax(this);"></span>

                                    <?if ($item["TYPE_ID"] == 41) {
                                    ?>
                                        <?if ($bIsReset) { ?>
                                            <div class="add-dump collapse">- Свернуть</div>
                                        <? } else { ?>
                                            <div class="add-dump">+ Добавить ограничения и сбросы</div>
                                        <? } ?>
                                    <?
                                    }
                                    else {
                                    ?>
                                        <div class="add-dump">+ Добавить ограничения</div>
                                    <?
                                    }
                                    ?>
                                </div>
                            </div>

                            <div class="quality-dump-intro<? if ($bIsReset) { ?> active<? } ?>" <? if ($item["TYPE_ID"] == 41) { ?>data-dump="Y"<? } ?>>
                                <div class="quality-param-intro txt fst">
                                    <span class="minus minus_bg" data-step="<?=$item['STEP']?>" onclick="rrsClickMin(this);"></span>
                                    <input type="text" name="param[<?=$item['QUALITY_ID']?>][MIN]" value="<?=$item['MIN']?>">
                                    <span class="plus plus_bg" data-step="<?=$item['STEP']?>" onclick="rrsClickMax(this);"></span>
                                    <div class="min">min</div>
                                </div>
                                <div class="quality-param-intro txt sec">
                                    <span class="minus minus_bg" data-step="<?=$item['STEP']?>" onclick="rrsClickMin(this);"></span>
                                    <input type="text" name="param[<?=$item['QUALITY_ID']?>][MAX]" value="<?=$item['MAX']?>">
                                    <span class="plus plus_bg" data-step="<?=$item['STEP']?>" onclick="rrsClickMax(this);"></span>
                                    <div class="max">max</div>
                                    <?
                                    if ($item["TYPE_ID"] == 41) {
                                    ?>
                                        <div class="add-dump-table<?if($bIsReset){?> inactive<?}?>">+ Назначить сброс/прибавку</div>
                                        <?/*<div class="add_straight_or">или</div>
                                        <div class="add-dump-table straight">прямой сброс</div>*/?>
                                    <?
                                    }
                                    ?>
                                </div>

                                <?
                                if ($item["TYPE_ID"] == 41) {
                                ?>
                                    <div class="quality-dump-table-intro<? if ($bIsReset) { ?> active<? } ?>" data-step="<?=$item['STEP']?>" data-param="<?=$item['QUALITY_ID']?>">
                                        <div class="add-dump-item" onclick="rrsAddDumpItem(this);">+ Добавить еще</div>
                                        <?
                                        if (filter_var($item['RESET_BELOW_BASIS'], FILTER_VALIDATE_FLOAT) !== false) {
                                        ?>

                                            <div class="quality-dump-table-item">
                                                <div class="quality-param-intro d_fst">
                                                    <span class="minus minus_bg" data-step="<?=$item['STEP']?>" onclick="rrsClickMin(this);"></span>
                                                    <input type="text" name="param[<?=$item['QUALITY_ID']?>][DUMP][MIN][]" value="<?=$item['MIN']?>">
                                                    <span class="plus plus_bg" data-step="<?=$item['STEP']?>" onclick="rrsClickMax(this);"></span>
                                                </div>
                                                <div class="quality-param-intro d_sec">
                                                    <span class="minus minus_bg" data-step="<?=$item['STEP']?>" onclick="rrsClickMin(this);"></span>
                                                    <input type="text" name="param[<?=$item['QUALITY_ID']?>][DUMP][MAX][]" value="<?=$item['BASE']?>">
                                                    <span class="plus plus_bg" data-step="<?=$item['STEP']?>" onclick="rrsClickMax(this);"></span>
                                                </div>
                                                <div class="quality-param-intro percent_block">
                                                    <span class="minus minus_bg" data-step=0.5 onclick="rrsClickMin(this);"></span>
                                                    <input type="text" name="param[<?=$item['QUALITY_ID']?>][DUMP][DISCOUNT][]" value="<?=$item['RESET_BELOW_BASIS']?>">
                                                    <span class="plus plus_bg" data-step=0.5 onclick="rrsClickMax(this);"></span>
                                                    <div class="prc_pic">%</div>
                                                    <?
                                                    if ($item['RESET_BELOW_BASIS'] > 0)
                                                        $text = 'прибавка';
                                                    elseif ($item['RESET_BELOW_BASIS'] < 0)
                                                        $text = 'сброс';
                                                    else
                                                        $text = '';
                                                    ?>
                                                    <div class="min"><?=$text?></div>
                                                </div>
                                                <div class="delete-dump-item" onclick="rrsDeleteDumpItem(this);">удалить</div>
                                                <div class="clear"></div>
                                            </div>
                                        <?
                                        }
                                        ?><?
                                        if (filter_var($item['RESET_MORE_BASIS'], FILTER_VALIDATE_FLOAT) !== false) {
                                        ?>

                                            <div class="quality-dump-table-item">
                                                <div class="quality-param-intro d_fst">
                                                    <span class="minus minus_bg" data-step="<?=$item['STEP']?>" onclick="rrsClickMin(this);"></span>
                                                    <input type="text" name="param[<?=$item['QUALITY_ID']?>][DUMP][MIN][]" value="<?=$item['BASE']?>">
                                                    <span class="plus plus_bg" data-step="<?=$item['STEP']?>" onclick="rrsClickMax(this);"></span>
                                                </div>
                                                <div class="quality-param-intro d_sec">
                                                    <span class="minus minus_bg" data-step="<?=$item['STEP']?>" onclick="rrsClickMin(this);"></span>
                                                    <input type="text" name="param[<?=$item['QUALITY_ID']?>][DUMP][MAX][]" value="<?=$item['MAX']?>">
                                                    <span class="plus plus_bg" data-step="<?=$item['STEP']?>" onclick="rrsClickMax(this);"></span>
                                                </div>
                                                <div class="quality-param-intro percent_block">
                                                    <span class="minus minus_bg" data-step=0.5 onclick="rrsClickMin(this);"></span>
                                                    <input type="text" name="param[<?=$item['QUALITY_ID']?>][DUMP][DISCOUNT][]" value="<?=$item['RESET_MORE_BASIS']?>">
                                                    <span class="plus plus_bg" data-step=0.5 onclick="rrsClickMax(this);"></span>
                                                    <div class="prc_pic">%</div>
                                                    <?
                                                    if ($item['RESET_MORE_BASIS'] > 0)
                                                        $text = 'прибавка';
                                                    elseif ($item['RESET_MORE_BASIS'] < 0)
                                                        $text = 'сброс';
                                                    else
                                                        $text = '';
                                                    ?>
                                                    <div class="min"><?=$text?></div>
                                                </div>
                                                <div class="delete-dump-item" onclick="rrsDeleteDumpItem(this);">удалить</div>
                                                <div class="clear"></div>
                                            </div>
                                        <?
                                        }
                                        ?>


                                    </div>
                                <?
                                }
                                ?>
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