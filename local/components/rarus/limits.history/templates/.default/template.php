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
//вывод сообщений об остатке лимита
$email_val = (checkEmailFromPhone($arResult['EMAIL']) ? '' : $arResult['EMAIL']);
if(isset($arParams['CLIENT_ID'])){
    $req_limit = client::checkAvailableRequestLimit($arParams['CLIENT_ID']);
    if($req_limit['REMAINS'] > 0){
        ?><div class="add_limit_line available"><div class="result_message"></div>Текущий лимит запросов: <span class="val"><?=$req_limit['CNT']?></span> (доступно запросов: <span class="remains"><?=$req_limit['REMAINS']?></span>).<br/>Вы можете <a href="javascript: void(0);" onclick="showRequestLimitsFeedbackForm('<?=$email_val;?>', '<?=rrsIblock::getConst('min_request_limit_form');?>', '<?=rrsIblock::getConst('request_limit_price');?>');">подать заявку</a> на пополнение</div><?
    }else{
        ?><div class="add_limit_line ended"><div class="result_message"></div>Исчерпан лимит создания запросов (лимит запросов: <span class="val"><?=$req_limit['CNT']?></span>), <a href="javascript: void(0);" onclick="showRequestLimitsFeedbackForm('<?=$email_val;?>', '<?=rrsIblock::getConst('min_request_limit_form');?>', '<?=rrsIblock::getConst('request_limit_price');?>');">подайте заявку</a> на пополнение</div><?
    }
}else{
    $off_limit = farmer::checkAvailableOfferLimit($arParams['FARMER_ID']);
    if($off_limit['REMAINS'] > 0){
        ?><div class="add_limit_line available"><div class="result_message"></div>Текущий лимит товаров: <span class="val"><?=$off_limit['CNT']?></span> (доступно товаров: <span class="remains"><?=$off_limit['REMAINS']?></span>).<br/>Вы можете <a href="javascript: void(0);" onclick="showOfferLimitsFeedbackForm('<?=$email_val;?>', '<?=rrsIblock::getConst('min_offer_limit_form');?>', '<?=rrsIblock::getConst('offer_limit_price');?>', '<?=rrsIblock::getConst('min_month_offer_limit');?>');">подать заявку</a> на пополнение</div><?
    }else{
        ?><div class="add_limit_line ended"><div class="result_message"></div>Исчерпан лимит создания товаров (лимит товаров: <span class="val"><?=$off_limit['CNT']?></span>), <a href="javascript: void(0);" onclick="showOfferLimitsFeedbackForm('<?=$email_val;?>', '<?=rrsIblock::getConst('min_offer_limit_form');?>', '<?=rrsIblock::getConst('offer_limit_price');?>', '<?=rrsIblock::getConst('min_month_offer_limit');?>');">подайте заявку</a> на пополнение</div><?
    }
}

if(count($arResult['ITEMS']) > 0) {
        ?>
        <div class="list_page_rows_area">
            <div class="list_page_rows counter_req_limitis">
                <div class="head_line">
                    <div class="line_inner">
                        <div class="nds">Дата</div>
                        <div class="farmer_delivery_type">Было</div>
                        <div class="tons">Стало</div>
                        <div class="price">Комментарий к операции</div>
                        <div class="clear l"></div>
                    </div>
                </div>
                <?
                    foreach ($arResult['ITEMS'] as $arItem) {
                        $comm_value = (isset($arResult['COMMENTS'][$arItem['ELEMENT_ID']]['U_COMMENT']) ? $arResult['COMMENTS'][$arItem['ELEMENT_ID']]['U_COMMENT'] : '');
                        //если нет комментария, то устанавливаем сообщение по умолчанию
                        if ($comm_value == '') {
                            switch($arItem['ENTITY_TYPE']) {
                                case 'client_counter_request':
                                    $comm_value = client::counterRequestOpenerDefaultText($arItem['ACTION'], $arItem['NUMBER']);
                                    break;

                                case 'client_request_limit':
                                    $comm_value = client::requestLimitDefaultText($arItem['ACTION'], $arItem['NUMBER']);
                                    break;

                                case 'farmer_offer_limit':
                                    $comm_value = farmer::offerLimitDefaultText($arItem['ACTION'], $arItem['NUMBER']);
                                    break;
                            }
                        }
                        ?>
                        <div class="line_area">
                            <div class="line_inner">
                                <div class="nds"><?= $arItem['DATE']; ?></div>
                                <div class="farmer_delivery_type"><?= $arItem['BEFORE']; ?></div>
                                <div class="tons"><?= $arItem['AFTER']; ?></div>
                                <div class="price"><?= $comm_value; ?></div>
                                <div class="clear"></div>
                            </div>
                        </div>
                        <?
                    }
                ?>
            </div>
        </div>
        <?
        //пагинация
        $APPLICATION->IncludeComponent("bitrix:main.pagenavigation", ".default",
            array(
                "NAV_OBJECT" => $arResult['NAV_OBJ'],
                "SEF_MODE" => "N"
            ),
            false
        );
}else{
    ?>Ни одной записи не найдено<?
}