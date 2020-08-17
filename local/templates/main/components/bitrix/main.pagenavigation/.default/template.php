<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/** @var array $arParams */
/** @var array $arResult */
/** @var CBitrixComponentTemplate $this */

/** @var PageNavigationComponent $component */
$component = $this->getComponent();

$this->setFrameMode(true);

$colorSchemes = array(
	"green" => "bx-green",
	"yellow" => "bx-yellow",
	"red" => "bx-red",
	"blue" => "bx-blue",
);
if(isset($colorSchemes[$arParams["TEMPLATE_THEME"]]))
{
	$colorScheme = $colorSchemes[$arParams["TEMPLATE_THEME"]];
}
else
{
	$colorScheme = "";
}
?>

<div class="pagination">
	<?if ($arResult["CURRENT_PAGE"] > 1):?>
        <a class="link_page" href="<?=htmlspecialcharsbx(str_replace('page-', '', $arResult["URL"]));?>">1</a>
        <?if (
            ($arResult["CURRENT_PAGE"] > 5
                ||
                $arResult["CURRENT_PAGE"] > 3
                && $arResult["PAGE_COUNT"] > 5
            )
            && $arResult["CURRENT_PAGE"] <= $arResult["PAGE_COUNT"]

        ):
            $dot_number = ceil($arResult["CURRENT_PAGE"] / 2);
            ?>
            <a href="<?=htmlspecialcharsbx(str_replace('page-', '', $component->replaceUrlTemplate($dot_number)));?>" class="link_page"><?=($dot_number != 2 ? '...' : $dot_number);?></a>
        <?endif;?>
	<?else:?>
        <a class="link_page active_page" href="<?=htmlspecialcharsbx(str_replace('page-', '', $arResult["URL"]));?>">1</a>
	<?endif?>

	<?
	$page = $arResult["START_PAGE"] + 1;
	while($page <= $arResult["END_PAGE"]-1):
	?>
		<?if ($page == $arResult["CURRENT_PAGE"]):?>
            <a class="link_page active_page" href="<?=htmlspecialcharsbx(str_replace('page-', '', $component->replaceUrlTemplate($page)));?>"><?=$page?></a>
		<?else:?>
            <a class="link_page" href="<?=htmlspecialcharsbx(str_replace('page-', '', $component->replaceUrlTemplate($page)));?>"><?=$page?></a>
		<?endif?>
		<?$page++?>
	<?endwhile?>

	<?if($arResult["CURRENT_PAGE"] < $arResult["PAGE_COUNT"]):?>
		<?if($arResult["PAGE_COUNT"] > 1):?>
            <?if (
                ($arResult["PAGE_COUNT"] - $arResult["CURRENT_PAGE"] > 2)
                    && $arResult["PAGE_COUNT"] > 5
                ):
                $dot_number = $arResult["CURRENT_PAGE"] + ceil(($arResult["PAGE_COUNT"] - $arResult["CURRENT_PAGE"]) / 2);
                ?>
                <a href="<?=htmlspecialcharsbx(str_replace('page-', '', $component->replaceUrlTemplate($dot_number)));?>" class="link_page"><?=(($arResult["PAGE_COUNT"] - $dot_number) != 1 ? '...' : $dot_number);?></a>
            <?endif;?>

            <a class="link_page" href="<?=htmlspecialcharsbx(str_replace('page-', '', $component->replaceUrlTemplate($arResult["PAGE_COUNT"])));?>"><?=$arResult["PAGE_COUNT"]?></a>
		<?endif?>
	<?else:?>
		<?if($arResult["PAGE_COUNT"] > 1):?>
            <a class="link_page active_page" href="<?=htmlspecialcharsbx(str_replace('page-', '', $component->replaceUrlTemplate($arResult["PAGE_COUNT"])));?>"><?=$arResult["PAGE_COUNT"]?></a>
		<?endif?>
	<?endif?>
</div>
