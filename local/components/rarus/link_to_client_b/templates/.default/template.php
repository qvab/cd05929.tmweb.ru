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

if(isset($arResult['CURRENT_PARTNER_DATA']))
{
    ?>
        <div class="current_partner_link_area">
            <div class="title">Вы привязаны к организатору:</div>
            <div class="list_page_rows requests">
                <div class="line_area">
                    <div class="line_inner item">
                        <div class="partner_val"><?=implode(' ', array($arResult['CURRENT_PARTNER_DATA']['NAME'], $arResult['CURRENT_PARTNER_DATA']['LAST_NAME'], '[' . $arResult['CURRENT_PARTNER_DATA']['LOGIN'] . ']'));?></div>
                        <div title="Отвязаться от организатора" class="unlink_but"></div>
                        <div class="clear"></div>
                    </div>
                </div>
            </div>
        </div>
<?
}

if(count($arResult['PARTNERS_LIST']) > 0)
{
    echo '<div class="partner_list_area">
            <div class="title">Вы можете привязаться к организатору:</div>
            <div class="list_page_rows requests partner_other_list">';
    foreach($arResult['PARTNERS_LIST'] as $cur_val)
    {
        ?>
            <div class="line_area">
                <div title="Чтобы прикрепиться к организатору вам нужно открепиться от вашего текущего организатора" class="line_inner item">
                    <div class="name"><?=implode(' ', array($cur_val['NAME'], $cur_val['LAST_NAME'], '[' . $cur_val['LOGIN'] . ']'));?></div>
                    <?if(!isset($arResult['CURRENT_PARTNER_DATA'])){?>
                        <div data-id="<?=$cur_val['ID'];?>" class="link_but"></div>
                    <?}?>
                    <div class="clear"></div>
                </div>
            </div>
    <?
    }
    echo '</div>
    </div>';
}