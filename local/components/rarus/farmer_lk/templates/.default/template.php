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

if($arResult['ERROR'] == 'Y')
{
    echo '<div class="err_text row">' . $arResult['ERROR_MESSAGE'] . '</div>';
}
else
{
    if($arResult['MESS_STR'] != '')
    {
        echo '<div class="err_text row">' . $arResult['MESS_STR'] . '</div>';
    }
    ?>
    <div class="profile_menu menu_list">
        <div class="item"><a href="<?=$arParams['PROFILE_EDIT_LINK'];?><?=(isset($_GET['backurl']) && trim($_GET['backurl']) != '' ? '?backurl=' . $_GET['backurl'] : '');?>">Редактирование профиля</a></div>
        <div class="item"><a href="/farmer/link_to_partner/">Привязка к организатору</a></div>
    </div>
<?}