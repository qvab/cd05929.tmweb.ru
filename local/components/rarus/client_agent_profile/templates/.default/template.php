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

if ($arResult['ERROR'] == 'Y') {
    echo '<div class="err_text row">' . $arResult['ERROR_MESSAGE'] . '</div>';
}
else {
    if ($arResult['ERROR_TEXT'] != '') {
        echo '<div class="err_text row">' . $arResult['ERROR_TEXT'] . '</div>';
    }

    if ($_GET['success'] == 'ok') {
        echo '<div class="success">Ваши данные успешно сохранены</div>';
    }
    ?>
    <form method="post" name="profile_form" action="" enctype="multipart/form-data">
        <input type="hidden" name="update" value="y" />
        <div class="content-form profile-form">
            <div class="fields">

                <?if(!empty($arResult['PARTNER_NAME'])):?>
                    <div class="row">
                        <div class="holder row_sub_head">Ваш организатор: <a href="/profile/?uid=<?=$arResult['PARTNER_ID']?>"><?=$arResult['PARTNER_NAME']?></a></div>
                    </div>
                <?endif;?>

                <div class="row">
                    <div class="holder row_sub_head">Данные пользователя:</div>
                </div>

                <div class="field row">
                    <div class="row_head">Имя</div>
                    <div class="row_val">
                        <input type="text" name="NAME" maxlength="50" value="<?=$arResult['SHOW_FIELDS']['NAME']?>">
                    </div>
                </div>

                <div class="field row">
                    <div class="row_head">Фамилия</div>
                    <div class="row_val">
                        <input type="text" name="LAST_NAME" maxlength="50" value="<?=$arResult['SHOW_FIELDS']['LAST_NAME']?>">
                    </div>
                </div>

                <div class="field row">
                    <div class="row_head">Отчество</div>
                    <div class="row_val">
                        <input type="text" name="SECOND_NAME" maxlength="50" value="<?=$arResult['SHOW_FIELDS']['SECOND_NAME']?>">
                    </div>
                </div>

                <div class="field row">
                    <div class="row_head">E-mail</div>
                    <div class="row_val">
                        <input type="text" name="" maxlength="50" value="<?=$arResult['SHOW_FIELDS']['EMAIL']?>" disabled="disabled">
                    </div>
                </div>

                <div class="field row">
                    <div class="row_head"><?=$arResult['SHOW_PROPS_TYPE']['PHONE']['NAME']?></div>
                    <div class="row_val">
                        <input type="text" name="PROP__PHONE" class="phone_msk" value="<?=$arResult['SHOW_PROPS']['PHONE']?>">
                    </div>
                </div>
            </div>
        </div>

        <div class="content-form profile-form row">
            <input name="save" value="Сохранить настройки профиля" class="input-submit submit-btn" type="submit">
        </div>
    </form>
<?
}
?>