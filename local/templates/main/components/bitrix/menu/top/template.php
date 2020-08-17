<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
if (!empty($arResult)) {


    // Така как ыбло сверстано поределнным образом, стили не будем нарушать, но нам необоходимо скрыть некоторые пункты меню  в верхум обильной версии
    // Следовательно вносим в расщет коррективы задача №12861
    $tot = 0;
	$arClient = $arFarmer = [];
	$bClientActive = $bFarmerActive = false;

    foreach ($arResult as $cur_pos => $arItem) {
        if(!isset($arItem['PARAMS']['NOT_SHOW_TOP_MOB'])){
            $tot++;
        }
		$arResult[$cur_pos]['PARAMS']['HIDE_TABLE'] = false;
		switch($arItem['PARAMS']['TYPE']){
			case 'C':
				$arClient[$cur_pos] = $arItem;
				$arResult[$cur_pos]['PARAMS']['HIDE_TABLE'] = true;
				if($arItem['SELECTED']) {
					$bClientActive = true;
				}
				break;
			case 'F':
				$arFarmer[$cur_pos] = $arItem;
				$arResult[$cur_pos]['PARAMS']['HIDE_TABLE'] = true;
				if($arItem['SELECTED']) {
					$bFarmerActive = true;
				}
				break;
		}
    }
    $tot = $tot > 7 ? 7 : $tot;

?>
    <div class="main_menu tot<?=$tot;?>">
        <?
        foreach ($arResult as $cur_pos => $arItem) {

            if($cur_pos == 7){?>
                <div class="popup_menu_area">
            <?}
        ?>
            <div data-pos="<?=$cur_pos;?>"
					class="item_area<?if($cur_pos == 0){?> first<?}?><? if ($arItem['SELECTED']) { ?> active<? } ?> <?=$arItem['PARAMS']['class']?> <?if($arItem['PARAMS']['HIDE_TABLE']):?> show-m <?endif;?>"
                <?if(isset($arItem['PARAMS']['NOT_SHOW_TOP_MOB']) && $arItem['PARAMS']['NOT_SHOW_TOP_MOB'] == 'Y'):?>
                    hide-top
                <?endif;?>
            >
                <a href="<?=$arItem["LINK"]?>"><div class="ico"></div><?=$arItem["TEXT"]?></a>
                <img class="arw" src="<?=SITE_TEMPLATE_PATH?>/images/item_arw.png" />
            </div>
        <?
        }
        if(count($arResult) > 7){?>
            </div><!-- /popup_menu_area -->
        <?}


		if(count($arClient) > 0){
			?>
			<div class="submenu">
				<div class="item_area  <? if ($bClientActive) { ?> active<? } ?>" hide-top>
					<a  href="#" class="sub-link"><div class="ico"></div>Покупатели</a>
				</div>
				<div class="submenu-content">
					<?
					foreach ($arClient as $cur_pos => $arItem) { ?>
						<div data-pos="<?=$cur_pos;?>"
							 class="item_area<?if($cur_pos == 0){?> first<?}?><? if ($arItem['SELECTED']) { ?> active<? } ?> <?=$arItem['PARAMS']['class']?> "

						>
							<a href="<?=$arItem["LINK"]?>"><div class="ico"></div><?=$arItem["TEXT"]?></a>
							<img class="arw" src="<?=SITE_TEMPLATE_PATH?>/images/item_arw.png" />
						</div>
					<?}
					?>
				</div>
			</div>
		<?
		}


		if(count($arFarmer) > 0){
			?>
			<div class="submenu">
				<div class="item_area  <? if ($bFarmerActive) { ?> active<? } ?>" hide-top>
					<a href="#" class="sub-link"><div class="ico"></div>Поставщики</a>

				</div>
				<div class="submenu-content">
					<?
					foreach ($arFarmer as $cur_pos => $arItem) { ?>
						<div data-pos="<?=$cur_pos;?>"
							 class="item_area<?if($cur_pos == 0){?> first<?}?><? if ($arItem['SELECTED']) { ?> active<? } ?> <?=$arItem['PARAMS']['class']?>"

						>
							<a href="<?=$arItem["LINK"]?>"><div class="ico"></div><?=$arItem["TEXT"]?></a>
							<img class="arw" src="<?=SITE_TEMPLATE_PATH?>/images/item_arw.png" />
						</div>
					<?}
					?>
				</div>
			</div>
		<?
		}
?>
        <div class="item_area mob_menu_item last">
            <a href="javascript: void(0);" onclick="showHideMenu();"><div class="ico"></div></a>
        </div>
    </div>
<?
}
?>