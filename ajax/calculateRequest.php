<?
header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header ("Cache-Control: no-cache, must-revalidate");
header ("Pragma: no-cache");
header ("content-type: application/x-javascript; charset=UTF-8");
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

//$iFcaId = getFCAItemID();
?>
<div class="request-block-intro">
    <div class="step-title row_head">5. Выберите склад и установите цену (<span class="fca_val">CPT</span>) закупки:</div>
    <?
    //выбранная культура
    $culture_id = $_POST["csort"];

    //покупатель
    $userId = $USER->GetID();

    //добавлние запроса агентом покупателя
    if(isset($_POST['client_id'])){
        $err_val = false;

        //проверка является ли текущий пользователь агентом
        $arrGroups = $USER->GetUserGroupArray();

        if(in_array(10, $arrGroups)){
            $agentObj = new agent();

            CModule::IncludeModule('iblock');

            //проверка привязки агента
            if(is_numeric($_POST['client_id'])
                && $_POST['client_id'] != 0
                && $agentObj->checkLinkWithClient($_POST['client_id'], $userId)
            ){
                //используем id покупателя
                $userId = $_POST['client_id'];
            }else{
                $err_val = true;
            }
        }else{
            $err_val = true;
        }

        if($err_val){
            exit;
        }
    }

    $arPrices = client::basePriceCalculation($userId, $culture_id, (isset($_POST['param']) ? $_POST['param'] : array()));

    if (is_array($arPrices['WAREHOUSES']) && sizeof($arPrices['WAREHOUSES']) > 0) {
        $sNDS = client::getNds($userId);
        $nNdsValue = rrsIblock::getConst('nds');
    ?>
        <?/*<div style="margin-bottom: 24px;">Базисная цена, (<?=($sNDS == 'Y') ? 'с' : 'без'?> НДС)</div>*/?>
        <div class="radio_group">
            <?
            $checked = false;
            if(sizeof($arPrices['WAREHOUSES'])==1){
                $checked = true;
            }
            foreach ($arPrices['WAREHOUSES'] as $warehouse) {

                //получаем значения цен, округленные до 50 (<=25 округляется до 0, <=75 округляется до 50, >75 округляется до 100)
                $iTempMod = $arPrices['PRICES'][$warehouse['CENTER_ID']]['BASE_PRICE_PASSIVE'] % 100;
                $iMinPrice = floor($arPrices['PRICES'][$warehouse['CENTER_ID']]['BASE_PRICE_PASSIVE'] / 100) * 100 + ($iTempMod > 75 ? 100 : ($iTempMod > 25 ? 50 : 0));
                $iTempMod = $arPrices['PRICES'][$warehouse['CENTER_ID']]['BASE_PRICE_ACTIVE'] % 100;
                $iMaxPrice = floor($arPrices['PRICES'][$warehouse['CENTER_ID']]['BASE_PRICE_ACTIVE'] / 100) * 100 + ($iTempMod > 75 ? 100 : ($iTempMod > 25 ? 50 : 0));
                $iTempMod = $arPrices['PRICES'][$warehouse['CENTER_ID']]['BASE_PRICE_STANDART'] % 100;
                $iCurrentPrice = floor($arPrices['PRICES'][$warehouse['CENTER_ID']]['BASE_PRICE_STANDART'] / 100) * 100 + ($iTempMod > 75 ? 100 : ($iTempMod > 25 ? 50 : 0));

                //значения цен с противоположной СНО покупателю, округленные до 50
                $nPassiveSno = 0;
                $nActiveSno = 0;
                $nStandartSno = 0;
                $nCurrentPriceValue = $iCurrentPrice;
                $iMinPriceSno = 0;
                $iMaxPriceSno = 0;
                $iCurrentPriceSno = 0;
                if($sNDS == 'Y'){
                    //вычитаем НДС из цены
                    $nPassiveSno = $iMinPrice / (1 + 0.01 * $nNdsValue);
                    $nActiveSno = $iMaxPrice / (1 + 0.01 * $nNdsValue);
                    $nStandartSno = $nCurrentPriceValue / (1 + 0.01 * $nNdsValue);
                }else{
                    //добавляем НДС в цену
                    $nPassiveSno = $iMinPrice + ($iMinPrice * 0.01 * $nNdsValue);
                    $nActiveSno = $iMaxPrice + ($iMaxPrice * 0.01 * $nNdsValue);
                    $nStandartSno = $nCurrentPriceValue + ($nCurrentPriceValue * 0.01 * $nNdsValue);
                }
                $iTempMod = $nPassiveSno % 100;
                $iMinPriceSno = floor($nPassiveSno / 100) * 100 + ($iTempMod > 75 ? 100 : ($iTempMod > 25 ? 50 : 0));
                $iTempMod = $nActiveSno % 100;
                $iMaxPriceSno = floor($nActiveSno / 100) * 100 + ($iTempMod > 75 ? 100 : ($iTempMod > 25 ? 50 : 0));
                $iTempMod = $nStandartSno % 100;
                $iCurrentPriceSno = floor($nStandartSno / 100) * 100 + ($iTempMod > 75 ? 100 : ($iTempMod > 25 ? 50 : 0));
                ?>
                <div class="wh_price">
                    <div class="sub_row txt">
                        <div class="min_price">
                            <?=number_format($iMinPrice, 0, ',', ' ')?>
                            <span>min</span>
                        </div>
                        <div class="quality-param-intro txt">
                            <span class="minus minus_bg" data-step="50" onclick="rrsClickMinPrice(this);" data-min="<?=$iMinPrice?>"></span>
                            <input type="text" name="wh_prc" value="<?=number_format($iCurrentPrice, 0, ',', ' ')?>">
                            <span class="plus plus_bg" data-step="50" onclick="rrsClickMaxPrice(this);" data-max="<?=$iMaxPrice?>"></span>
                        </div>
                        <div class="max_price">
                            <?=number_format($iMaxPrice, 0, ',', ' ')?>
                            <span>max</span>
                        </div>
                        <div class="other_sno_area">
                            <div class="label_cur"><?=($sNDS == 'Y' ? 'С' : 'Без');?> НДС</div>
                            <div class="label"><?=($sNDS == 'Y' ? 'Без' : 'С');?> НДС</div>
                            <div class="minimum_price"><?=number_format($iMinPriceSno, 0, ',', ' ');?></div>
                            <div class="current_price"><?=number_format($iCurrentPriceSno, 0, ',', ' ');?></div>
                            <div class="maximum_price"><?=number_format($iMaxPriceSno, 0, ',', ' ');?></div>
                        </div>
                    </div>
                    <div class="cost-item radio_area">
                        <input type="radio" data-whid="<?=$warehouse['ID']?>" id="warehouse[<?=$warehouse['ID']?>]" name="warehouse" <? if ($checked) { ?>checked="checked"<? } ?> value="<?=$warehouse['ID']?>|<?=$iCurrentPrice?>" onclick="rrsCheckSubmit();">
                        <label>
                            <span class="name"><?=$warehouse['NAME']?></span>
                            <span class="address"><?=$warehouse['ADDRESS']?></span>
                        </label>
                    </div>
                </div>
                <div class="clear"></div>
            <?
            }
            ?>
        </div>
    <?
    }
    else {
        echo '<div class="form_line_error">Ошибка! Не найдено ни одного склада.</div>';
    }
    ?>
</div>