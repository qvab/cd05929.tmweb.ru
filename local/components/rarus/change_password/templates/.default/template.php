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
    if($_GET['success'] == 'ok')
    {
        echo '<div class="success">Ваши данные успешно сохранены</div>';
    }
    ?>
        <form method="post" name="profile_form" action="<?=$APPLICATION->GetCurDir();?>" enctype="multipart/form-data">
            <input type="hidden" name="change" value="y" />

            <div class="fields">
                <div class="field row">
                    <div class="row_head">Новый пароль</div>
                    <div class="row_val">
                        <input type="password" name="new_passw" maxlength="50" value="<?=(isset($_POST['new_passw']) ? $_POST['new_passw'] : '');?>" />
                    </div>
                </div>
            </div>

            <div class="fields">
                <div class="field row">
                    <div class="row_head">Подтверждение нового пароля</div>
                    <div class="row_val">
                        <input type="password" name="new_passw_conf" maxlength="50" value="<?=(isset($_POST['new_passw_conf']) ? $_POST['new_passw_conf'] : '');?>" />
                    </div>
                </div>
            </div>

            <div class="content-form profile-form row">
                <input name="save" value="Сохранить настройки профиля" class="input-submit submit-btn" type="submit" />
            </div>

        </form>
    <?
}