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
                    Своевременность приемки продукции
                </div>
                <div class="clip_item"></div>
                <div class="clear l"></div>
            </div>
            <div class="line_additional" style="display: block;">
                <div class="line_additional_inner">
                    <div class="prop_area i0">
                        <input type="hidden" name="rec" value=""/>
                        <div class="rating"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="line_area current with_info">
            <div class="line_inner">
                <div class="step_num">2</div>
                <div class="name">
                    Оценка качества продукции
                </div>
                <div class="clip_item"></div>
                <div class="clear l"></div>
            </div>
            <div class="line_additional" style="display: block;">
                <div class="line_additional_inner">
                    <div class="prop_area i0">
                        <input type="hidden" name="lab" value=""/>
                        <div class="rating"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="line_area current with_info">
            <div class="line_inner">
                <div class="step_num">3</div>
                <div class="name">
                    Своевременность оплаты
                </div>
                <div class="clip_item"></div>
                <div class="clear l"></div>
            </div>
            <div class="line_additional" style="display: block;">
                <div class="line_additional_inner">
                    <div class="prop_area i0">
                        <input type="hidden" name="pay" value=""/>
                        <div class="rating"></div>
                    </div>
                </div>
            </div>
        </div>
        <input type="submit" class="submit-btn" value="Отправить" name="save">
    </form>
</div>