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
<?
$user_email = $USER->GetEmail();
if(checkEmailFromPhone($user_email)){
    $user_email = '';
}
if(intval($arResult['LIMITS']) > 0) {
    ?>
    <div class="opening_limit_available"><div class="result_message"></div>Доступно принятий:<div class="limit_val"><?=$arResult['LIMITS'];?></div><a href="javascript: void(0);" onclick="showCounterRequestFeedbackForm('<?=$user_email;?>', <?=rrsIblock::getConst('min_counter_req_limit');?>, <?=rrsIblock::getConst('counter_req_price');?>);">(подать заявку)</a></div>
    <?
}else{
    ?>
    <div class="opening_limit_ended"><div class="result_message"></div>Лимит принятий исчерпан, <a href="javascript: void(0);" onclick="showCounterRequestFeedbackForm('<?=$user_email;?>', <?=rrsIblock::getConst('min_counter_req_limit');?>, <?=rrsIblock::getConst('counter_req_price');?>);">подайте заявку</a> на пополнение</div>
    <?
}