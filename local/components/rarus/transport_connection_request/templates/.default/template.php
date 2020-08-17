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

<div class="content-form transport_request_page public_form" data-actionobj="transport_connection">
    <div class="close" onclick="closePublicForm(this);"></div>

    <div class="page_sub_title">Заявка на подключение в качестве перевозчика</div>

    <form class="tk_request_form" action="" method="post" enctype="multipart/form-data">

        <input type="hidden" name="PUBLIC_FORM" value="TK_REQUEST">

        <?if (!empty($arResult['ERROR_MESSAGE'])) {?>
            <div class="row">
                <div class="err_text row"><?=$arResult['ERROR_MESSAGE'];?></div>
            </div>
        <?}

        if (isset($arResult['success']) && $arResult['success'] == 'ok') {?>
            <div class="success">Заявка успешно отправлена</div>
        <?}?>

        <div class="row">
            <div class="needItem">*</div>
            <div class="holder row_val">
                <input type="text" name="fio" value="" placeholder="ФИО" />
            </div>
        </div>

        <?if(count($arResult['REGIONS']) > 0){?>
            <div class="row">
                <div class="holder row_sub_head">Регион:</div>
                <div class="needItem">*</div>
                <div class="holder row_val">
                    <select data-search="y" name="region">
                        <option value=""></option>
                        <?foreach($arResult['REGIONS'] as $cur_id => $cur_region){?>
                            <option value="<?=$cur_id?>"><?=$cur_region;?></option>
                        <?}?>
                    </select>
                </div>
            </div>
        <?}?>

        <div class="row">
            <div class="needItem">*</div>
            <div class="holder row_val">
                <input type="text" class="phone_msk" name="phone" data-checktype="phone" value="" placeholder="Телефон">
            </div>
        </div>

        <div class="row">
            <div class="needItem">*</div>
            <div class="holder row_val">
                <input type="text" name="email" data-checkval="y" data-checktype="email" value="" placeholder="Email">
            </div>
        </div>

        <div class="row">
            <div class="holder row_val">
                <textarea name="comment" maxlength="1000" placeholder="Комментарий"></textarea>
            </div>
        </div>

        <div class="row policy_row">
            <div class="field field-option radio_group">
                <div class="radio_area">
                    <input type="checkbox" data-text="<span class='checkbox_href_text' onclick='triggerCustomClick(this, false);'>Я принимаю</span> <a href='javascript: void(0);' data-href='/#action=policy' onclick='triggerCustomClick(this, true);' class='checkbox_href'>Политику обработки персональных данных</a><span class='checkbox_href_text' onclick='triggerCustomClick(this, false);'>, а также даю</span> <a href='javascript: void(0);' data-href='/#action=agreement' onclick='triggerCustomClick(this, true);' class='checkbox_href'>согласие на обработку своих персональных</a><span class='checkbox_href_text' onclick='triggerCustomClick(this, false);'> в системе &quot;АГРОХЕЛПЕР&quot;</span>" name="AUTH_REG_CONFIM" value="Y" />
                </div>
            </div>
        </div>

        <div id="recaptcha_tk_request" class="g-recaptcha" data-callback="onRecSubmit" data-size="invisible"></div>

        <div class="row">
            <span class="rrs_btn_auth">
                <input class="submit-btn" type="submit" name="Login" value="Отправить заявку" data-role="none">
            </span>
        </div>

    </form>

</div>