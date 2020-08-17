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
?>
<div class="agent-info">

    <?if(empty($arResult['ERROR_MSG'])):?>

        <div class="user_profile_area no_margin">

            <?if(!empty($arResult['AGENT_PROFILE']['USER']['NAME'])):?>
                <div class="row">
                    <div class="row_head">Имя</div>
                    <div class="row_val">
                        <input readonly="readonly" value="<?=$arResult['AGENT_PROFILE']['USER']['NAME']?>" type="text">
                    </div>
                </div>
            <?endif;?>

            <?if(!empty($arResult['AGENT_PROFILE']['USER']['LAST_NAME'])):?>
                <div class="row">
                    <div class="row_head">Фамилия</div>
                    <div class="row_val">
                        <input readonly="readonly" value="<?=$arResult['AGENT_PROFILE']['USER']['LAST_NAME']?>" type="text">
                    </div>
                </div>
            <?endif;?>

            <?if(!empty($arResult['AGENT_PROFILE']['USER']['EMAIL'])):?>
                <div class="row">
                    <div class="row_head">Email</div>
                    <div class="row_val">
                        <input readonly="readonly" value="<?=$arResult['AGENT_PROFILE']['USER']['EMAIL']?>" type="text">
                    </div>
                </div>
            <?endif;?>

            <?if(!empty($arResult['AGENT_PROFILE']['PROPERTY_PHONE_VALUE'])):?>
                <div class="row">
                    <div class="row_head">Телефон</div>
                    <div class="row_val">
                        <input readonly="readonly" value="<?=$arResult['AGENT_PROFILE']['PROPERTY_PHONE_VALUE']?>" type="text">
                    </div>
                </div>
            <?endif;?>

        </div>

    <?else:?>
        <div class="error-msg">
            <?ShowError('Ошибка! ' . $arResult['ERROR_MSG'])?>
        </div>
    <?endif;?>
</div>