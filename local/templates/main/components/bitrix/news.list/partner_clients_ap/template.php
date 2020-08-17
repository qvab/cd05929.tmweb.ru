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
if (sizeof($arResult["ITEMS"]) > 0) {
?>
    <div class="connected_users_list">
        <div class="list_page_rows">
        <?
        foreach($arResult["ITEMS"] as $arItem) {
            if(!$arItem['BL_EXISTS']){
                $but = 'unlink_but_plus';
                $b_color = ' bl';
                $title = GetMessage("ADD_TO_BL");
            }else{
                $but = 'unlink_but';
                $b_color = ' blacklist';
                $title = GetMessage("DELETE_FROM_BL");
            }
        ?>
            <div class="line_area<?=$b_color?>">
                <div class="line_inner">
                    <div class="inner_text">
                        <span class="email_val">
                            <?
                            $sTempName = '';
                            if(!empty($arItem['CLIENT_NAME'])){
                                $sTempName = $arItem['CLIENT_NAME'];
                            }
                            if(
                                $sTempName
                                && !empty($arItem['CLIENT_LOGIN'])
                            ){
                                $sTempName = $sTempName . ', ' . $arItem['CLIENT_LOGIN'];
                            }

                            if(empty($sTempName)){
                                $sTempName = $arItem['ELEMENT_ID'];
                            }
                            ?><?=$sTempName;?><br/>
                        </span>
                    </div>
                    <div title="<?=$title?>" data-blid="<?=$arItem['BL_EXISTS']?>" data-client-id="<?=$arItem['CLIENT_ID']?>" data-farmer-id="<?=$arResult['FARMER_ID']?>" class="<?=$but?>"></div>
                    <div class="clear"></div>
                </div>
            </div>
        <?
        }
        ?>
        </div>
    </div>
        <?if($arParams["DISPLAY_BOTTOM_PAGER"]):?>
            <br /><?=$arResult["NAV_STRING"]?>
        <?endif;?>
<?
}
$templateData = $arResult;
?>