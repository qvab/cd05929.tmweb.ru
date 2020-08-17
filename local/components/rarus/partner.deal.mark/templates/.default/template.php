<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<div class="page_sub_title">
    <span class="bold"><?=$arResult['DEAL']['PROPERTY_CULTURE_NAME']?></span>
    <span class="num">/ Сделка #<?=$arResult['DEAL']['ID']?> от <?=date('d.m.Y', strtotime($arResult['DEAL']['ACTIVE_FROM']))?></span>
</div>

<!--Участники сделки-->
<div class="participants">
    <?if(!empty($arResult['CLIENT']['COMPANY'])):?>
        <div class="client">
            <div class="item">Покупатель:</div>
            <a href="/profile/?uid=<?=$arResult['DEAL']['PROPERTY_CLIENT_VALUE']?>" target="_blank"><?=$arResult['CLIENT']['COMPANY']?></a>
        </div>
    <?endif;?>

    <?if(!empty($arResult['FARMER']['COMPANY'])):?>
        <div class="farmer">
            <div class="item">Поставщик:</div>
            <a href="/profile/?uid=<?=$arResult['DEAL']['PROPERTY_FARMER_VALUE']?>" target="_blank"><?=$arResult['FARMER']['COMPANY']?></a>
        </div>
    <?endif;?>
</div>

<div class="list_page_rows deals mark deal_detail">
    <form action="" method="post">
        <div class="line_area current with_info">
            <div class="line_inner">
                <div class="step_num">1</div>
                <div class="name">
                    Были ли проблемы при приемке между Поставщиком <?=$arResult['FARMER']['COMPANY']?>
                    и Покупателем <?=$arResult['CLIENT']['COMPANY']?> при исполнении сделки #<?=$arResult['DEAL']['ID']?>, а именно:
                </div>
                <div class="clip_item"></div>
                <div class="clear l"></div>
            </div>
            <div class="line_additional" style="display: block;">
                <div class="line_additional_inner">
                    <div class="prop_area i0">
                        <?
                        if ($arResult['DELIVERY'] == 'CPT') {
                        ?>
                            простой транспорта Поставщика более 1 дня на приемке продукции на складе Покупателя при поставке CPT
                        <?
                        }
                        else {
                        ?>
                            позднее (более 1 дня просрочки) прибытие транспорта Покупателя на склад Поставщика при поставке FCA
                        <?
                        }
                        ?>
                        <div class="row">
                            <div class="row_val">
                                <div class="radio_group">
                                    <div class="radio_area">
                                        <input type="radio" name="rec" data-text="Да" id="rec1" value="1">
                                    </div>
                                    <div class="radio_area">
                                        <input type="radio" name="rec" data-text="Нет" id="rec0" value="0" checked="checked">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="line_area current with_info">
            <div class="line_inner">
                <div class="step_num">2</div>
                <div class="name">
                    Были ли неурегулированные проблемы по качеству продукции между Поставщиком <?=$arResult['FARMER']['COMPANY']?>
                    и Покупателем <?=$arResult['CLIENT']['COMPANY']?> при исполнении сделки #<?=$arResult['DEAL']['ID']?>:
                </div>
                <div class="clip_item"></div>
                <div class="clear l"></div>
            </div>
            <div class="line_additional" style="display: block;">
                <div class="line_additional_inner">
                    <div class="prop_area i0">
                        <div class="row">
                            <div class="row_val">
                                <div class="radio_group">
                                    <div class="radio_area">
                                        <input type="radio" name="lab" data-text="Да" id="lab1" value="1">
                                    </div>
                                    <div class="radio_area">
                                        <input type="radio" name="lab" data-text="Нет" id="lab0" value="0" checked="checked">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="line_area current with_info">
            <div class="line_inner">
                <div class="step_num">3</div>
                <div class="name">
                    Были ли неурегулированные проблемы по оплате договора купли-продажи между Поставщиком <?=$arResult['FARMER']['COMPANY']?>
                    и Покупателем <?=$arResult['CLIENT']['COMPANY']?> при исполнении сделки #<?=$arResult['DEAL']['ID']?>:
                </div>
                <div class="clip_item"></div>
                <div class="clear l"></div>
            </div>
            <div class="line_additional" style="display: block;">
                <div class="line_additional_inner">
                    <div class="prop_area i0">
                        <div class="row">
                            <div class="row_val">
                                <div class="radio_group">
                                    <div class="radio_area">
                                        <input type="radio" name="pay" data-text="Да" id="pay1" value="1">
                                    </div>
                                    <div class="radio_area">
                                        <input type="radio" name="pay" data-text="Нет" id="pay0" value="0" checked="checked">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <input type="submit" class="submit-btn" value="Отправить" name="save">
    </form>
</div>