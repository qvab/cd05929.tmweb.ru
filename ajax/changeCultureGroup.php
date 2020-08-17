<?
header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header ("Cache-Control: no-cache, must-revalidate");
header ("Pragma: no-cache");
header("content-type: application/x-javascript; charset=UTF-8");
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
?>
<div class="request-block-intro">
    <div class="step-title row_head">2. Выберите товар</div>
    <?
    $groupId = intval($_POST['cgroup']);
    if ($groupId > 0) {
        $cultures = culture::getListByGroupId($groupId);
        if (is_array($cultures) && sizeof($cultures) > 0) {
        ?>
            <div class="row_val">
                <div class="radio_group">
                <?
                foreach ($cultures as $item) {
                ?>
                    <div class="radio_area">
                        <input type="radio" name="csort" data-text="<?=$item['NAME']?>" id="csort<?=$item['ID']?>" value="<?=$item['ID']?>">
                    </div>
                <?
                }
                ?>
                </div>
            </div>
        <?
        }
        else {
            ShowError("Ошибка! Ни одного сорта не найдено");
        }
    }
    ?>
</div>