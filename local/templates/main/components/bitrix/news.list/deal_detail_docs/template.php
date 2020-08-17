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

            <?if(!empty($arResult['PARTNER']['ID'])):?>
                <span class="partner-name">
                    (организатор покупателя <a href="/profile/?uid=<?=$arResult['DEAL']['PROPERTY_PARTNER_VALUE']?>" target="_blank"><?=$arResult['PARTNER']['PROPERTY_FULL_COMPANY_NAME_VALUE']?></a>)
                </span>
            <?endif;?>
        </div>
    <?endif;?>

    <?if(!empty($arResult['FARMER']['COMPANY'])):?>
        <div class="farmer">
            <div class="item">Поставщик:</div>
            <a href="/profile/?uid=<?=$arResult['DEAL']['PROPERTY_FARMER_VALUE']?>" target="_blank"><?=$arResult['FARMER']['COMPANY']?></a>
        </div>
    <?endif;?>
</div>

<?/*$APPLICATION->IncludeComponent(
    "bitrix:menu",
    "deal",
    array(
        "ROOT_MENU_TYPE" => "deal",
        "MENU_CACHE_TYPE" => "N",
        "MENU_CACHE_TIME" => "36000000",
        "MENU_CACHE_USE_GROUPS" => "Y",
        "MENU_CACHE_GET_VARS" => array(),
        "MAX_LEVEL" => "1",
        "CHILD_MENU_TYPE" => "deal",
        "USE_EXT" => "Y",
        "DELAY" => "N",
        "ALLOW_MULTI_SELECT" => "N",
        "PAGE" => $_REQUEST['page']
    ),
    false
);*/?>

<a class="go_back cross" href="<?=$arParams['LIST_URL']?>"></a>
<?
$k = 1;
if (sizeof($arResult['DOCS']) > 0) {
?>
    <div class="list_page_rows deals deal_detail">
        <?
        if ($arResult['DOCS']['dkp_client']['FILE']['SRC']
            && in_array($arParams['USER_TYPE'], array('c', 'f', 'p', 'ag', 'agc'))
        ) {
        ?>
            <div class="line_area deal_done">
                <div class="line_inner">
                    <div class="indicator"></div>
                    <div class="step_num"><?=$k++?></div>
                    <div class="name">
                        <?=$arResult['DOCS']['dkp_client']['NAME']?>
                        <a href="<?=$arResult['DOCS']['dkp_client']['FILE']['SRC']?>" download="">скачать</a>
                    </div>
                    <div class="clip_item"></div>
                    <div class="clear l"></div>
                </div>
            </div>
        <?
        }
        elseif ($arResult['DOCS']['dkp_partner']['FILE']['SRC']
            && in_array($arParams['USER_TYPE'], array('c', 'f', 'p', 'ag', 'agc'))
        ) {
        ?>
            <div class="line_area deal_done">
                <div class="line_inner">
                    <div class="indicator"></div>
                    <div class="step_num"><?=$k++?></div>
                    <div class="name">
                        <?=$arResult['DOCS']['dkp_partner']['NAME']?>
                        <a href="<?=$arResult['DOCS']['dkp_partner']['FILE']['SRC']?>" download="">скачать</a>
                    </div>
                    <div class="clip_item"></div>
                    <div class="clear l"></div>
                </div>
            </div>
        <?
        }
        elseif ($arResult['DOCS']['dkp']['FILE']['SRC']
            && in_array($arParams['USER_TYPE'], array('f', 'p', 'ag'))
        ) {
        ?>
            <div class="line_area deal_done">
                <div class="line_inner">
                    <div class="indicator"></div>
                    <div class="step_num"><?=$k++?></div>
                    <div class="name">
                        <?=$arResult['DOCS']['dkp']['NAME']?>
                        <a href="<?=$arResult['DOCS']['dkp']['FILE']['SRC']?>" download="">скачать</a>
                    </div>
                    <div class="clip_item"></div>
                    <div class="clear l"></div>
                </div>
            </div>
        <?
        }

        if ($arResult['DOCS']['ds_client']['FILE']['SRC']
            && in_array($arParams['USER_TYPE'], array('c', 'p', 'agc'))
        ) {
            ?>
            <div class="line_area deal_done">
                <div class="line_inner">
                    <div class="indicator"></div>
                    <div class="step_num"><?=$k++?></div>
                    <div class="name">
                        <?=$arResult['DOCS']['ds_client']['NAME']?>
                        <a href="<?=$arResult['DOCS']['ds_client']['FILE']['SRC']?>" download="">скачать</a>
                    </div>
                    <div class="clip_item"></div>
                    <div class="clear l"></div>
                </div>
            </div>
        <?
        }
        elseif ($arResult['DOCS']['ds_partner']['FILE']['SRC']
            && in_array($arParams['USER_TYPE'], array('c', 'p', 'agc'))
        ) {
            ?>
            <div class="line_area deal_done">
                <div class="line_inner">
                    <div class="indicator"></div>
                    <div class="step_num"><?=$k++?></div>
                    <div class="name">
                        <?=$arResult['DOCS']['ds_partner']['NAME']?>
                        <a href="<?=$arResult['DOCS']['ds_partner']['FILE']['SRC']?>" download="">скачать</a>
                    </div>
                    <div class="clip_item"></div>
                    <div class="clear l"></div>
                </div>
            </div>
        <?
        }
        elseif ($arResult['DOCS']['ds']['FILE']['SRC']
            && in_array($arParams['USER_TYPE'], array('p'))
        ) {
            ?>
            <div class="line_area deal_done">
                <div class="line_inner">
                    <div class="indicator"></div>
                    <div class="step_num"><?=$k++?></div>
                    <div class="name">
                        <?=$arResult['DOCS']['ds']['NAME']?>
                        <a href="<?=$arResult['DOCS']['ds']['FILE']['SRC']?>" download="">скачать</a>
                    </div>
                    <div class="clip_item"></div>
                    <div class="clear l"></div>
                </div>
            </div>
        <?
        }

        if ($arResult['DOCS']['dtr_transport']['FILE']['SRC']
            && in_array($arParams['USER_TYPE'], array('f', 'p', 't'))
        ) {
        ?>
            <div class="line_area deal_done">
                <div class="line_inner">
                    <div class="indicator"></div>
                    <div class="step_num"><?=$k++?></div>
                    <div class="name">
                        <?=$arResult['DOCS']['dtr_transport']['NAME']?>
                        <a href="<?=$arResult['DOCS']['dtr_transport']['FILE']['SRC']?>" download="">скачать</a>
                    </div>
                    <div class="clip_item"></div>
                    <div class="clear l"></div>
                </div>
            </div>
        <?
        }
        elseif ($arResult['DOCS']['dtr_partner']['FILE']['SRC']
            && in_array($arParams['USER_TYPE'], array('f', 'p', 't', 'ag'))
        ) {
        ?>
            <div class="line_area deal_done">
                <div class="line_inner">
                    <div class="indicator"></div>
                    <div class="step_num"><?=$k++?></div>
                    <div class="name">
                        <?=$arResult['DOCS']['dtr_partner']['NAME']?>
                        <a href="<?=$arResult['DOCS']['dtr_partner']['FILE']['SRC']?>" download="">скачать</a>
                    </div>
                    <div class="clip_item"></div>
                    <div class="clear l"></div>
                </div>
            </div>
        <?
        }
        elseif ($arResult['DOCS']['dtr']['FILE']['SRC']
            && in_array($arParams['USER_TYPE'], array('f', 'p', 'ag'))
        ) {
        ?>
            <div class="line_area deal_done">
                <div class="line_inner">
                    <div class="indicator"></div>
                    <div class="step_num"><?=$k++?></div>
                    <div class="name">
                        <?=$arResult['DOCS']['dtr']['NAME']?>
                        <a href="<?=$arResult['DOCS']['dtr']['FILE']['SRC']?>" download="">скачать</a>
                    </div>
                    <div class="clip_item"></div>
                    <div class="clear l"></div>
                </div>
            </div>
        <?
        }

        if ($arResult['DOCS']['ds_transport_transport']['FILE']['SRC']
            && in_array($arParams['USER_TYPE'], array('p', 't'))
        ) {
        ?>
            <div class="line_area deal_done">
                <div class="line_inner">
                    <div class="indicator"></div>
                    <div class="step_num"><?=$k++?></div>
                    <div class="name">
                        <?=$arResult['DOCS']['ds_transport_transport']['NAME']?>
                        <a href="<?=$arResult['DOCS']['ds_transport_transport']['FILE']['SRC']?>" download="">скачать</a>
                    </div>
                    <div class="clip_item"></div>
                    <div class="clear l"></div>
                </div>
            </div>
        <?
        }
        elseif ($arResult['DOCS']['ds_transport_partner']['FILE']['SRC']
            && in_array($arParams['USER_TYPE'], array('p', 't'))
        ) {
        ?>
            <div class="line_area deal_done">
                <div class="line_inner">
                    <div class="indicator"></div>
                    <div class="step_num"><?=$k++?></div>
                    <div class="name">
                        <?=$arResult['DOCS']['ds_transport_partner']['NAME']?>
                        <a href="<?=$arResult['DOCS']['ds_transport_partner']['FILE']['SRC']?>" download="">скачать</a>
                    </div>
                    <div class="clip_item"></div>
                    <div class="clear l"></div>
                </div>
            </div>
        <?
        }
        elseif ($arResult['DOCS']['ds_transport']['FILE']['SRC']
            && in_array($arParams['USER_TYPE'], array('p', 't'))
        ) {
        ?>
            <div class="line_area deal_done">
                <div class="line_inner">
                    <div class="indicator"></div>
                    <div class="step_num"><?=$k++?></div>
                    <div class="name">
                        <?=$arResult['DOCS']['ds_transport']['NAME']?>
                        <a href="<?=$arResult['DOCS']['ds_transport']['FILE']['SRC']?>" download="">скачать</a>
                    </div>
                    <div class="clip_item"></div>
                    <div class="clear l"></div>
                </div>
            </div>
        <?
        }

        if ($arResult['DOCS']['payment']['FILE']['SRC']
            && ($arParams['USER_TYPE'] == 'p'
                || (in_array($arParams['USER_TYPE'], array('c', 'f', 'p', 'ag', 'agc'))
                    && in_array('payment_send', array_keys($arParams['LOGS']))
                )
            )
        ) {
        ?>
            <div class="line_area deal_done">
                <div class="line_inner">
                    <div class="indicator"></div>
                    <div class="step_num"><?=$k++?></div>
                    <div class="name">
                        <?=$arResult['DOCS']['payment']['NAME']?>
                        <a href="<?=$arResult['DOCS']['payment']['FILE']['SRC']?>" download="">скачать</a>
                    </div>
                    <div class="clip_item"></div>
                    <div class="clear l"></div>
                </div>
            </div>
        <?
        }
        elseif ($arResult['DOCS']['prepayment']['FILE']['SRC']
            && ($arParams['USER_TYPE'] == 'p'
                || (in_array($arParams['USER_TYPE'], array('c', 'f', 'p', 'ag', 'agc'))
                    && in_array('prepayment_send', array_keys($arParams['LOGS']))
                )
            )
        ) {
        ?>
            <div class="line_area deal_done">
                <div class="line_inner">
                    <div class="indicator"></div>
                    <div class="step_num"><?=$k++?></div>
                    <div class="name">
                        <?=$arResult['DOCS']['prepayment']['NAME']?>
                        <a href="<?=$arResult['DOCS']['prepayment']['FILE']['SRC']?>" download="">скачать</a>
                    </div>
                    <div class="clip_item"></div>
                    <div class="clear l"></div>
                </div>
            </div>
        <?
        }

        if ($arResult['DOCS']['commission']['FILE']['SRC']
            && ($arParams['USER_TYPE'] == 'p'
                || (in_array($arParams['USER_TYPE'], array('c', 'p', 'agc'))
                    && in_array('payment_send', array_keys($arParams['LOGS']))
                )
            )
        ) {
        ?>
            <div class="line_area deal_done">
                <div class="line_inner">
                    <div class="indicator"></div>
                    <div class="step_num"><?=$k++?></div>
                    <div class="name">
                        <?=$arResult['DOCS']['commission']['NAME']?>
                        <a href="<?=$arResult['DOCS']['commission']['FILE']['SRC']?>" download="">скачать</a>
                    </div>
                    <div class="clip_item"></div>
                    <div class="clear l"></div>
                </div>
            </div>
        <?
        }

        if (sizeof($arResult['DOCS']['reestr']) > 0
            && in_array($arParams['USER_TYPE'], array('c', 'p', 'agc'))
        ) {
        ?>
            <div class="line_area deal_done">
                <div class="line_inner">
                    <div class="indicator"></div>
                    <div class="step_num"><?=$k++?></div>
                    <div class="name">
                        <?
                        foreach ($arResult['DOCS']['reestr'] as $file) {
                        ?>
                            Документ от <?=$file['DATE_CREATE']?>
                            <a href="<?=$file['FILE']['SRC']?>" download="">скачать</a><br>
                        <?
                        }
                        ?>
                    </div>
                    <div class="clip_item"></div>
                    <div class="clear l"></div>
                </div>
            </div>
        <?
        }

        if ($arResult['DOCS']['payment_transport']['FILE']['SRC']
            && ($arParams['USER_TYPE'] == 't'
                || (in_array($arParams['USER_TYPE'], array('f', 'p', 't', 'ag'))
                    && in_array('payment_transport_send', array_keys($arParams['LOGS']))
                )
            )
        ) {
        ?>
            <div class="line_area deal_done">
                <div class="line_inner">
                    <div class="indicator"></div>
                    <div class="step_num"><?=$k++?></div>
                    <div class="name">
                        <?=$arResult['DOCS']['payment_transport']['NAME']?>
                        <a href="<?=$arResult['DOCS']['payment_transport']['FILE']['SRC']?>" download="">скачать</a>
                    </div>
                    <div class="clip_item"></div>
                    <div class="clear l"></div>
                </div>
            </div>
        <?
        }

        if ($arResult['DOCS']['commission_transport']['FILE']['SRC']
            && ($arParams['USER_TYPE'] == 'p'
                || (in_array($arParams['USER_TYPE'], array('p', 't'))
                    && in_array('payment_transport_send', array_keys($arParams['LOGS']))
                )
            )
        ) {
        ?>
            <div class="line_area deal_done">
                <div class="line_inner">
                    <div class="indicator"></div>
                    <div class="step_num"><?=$k++?></div>
                    <div class="name">
                        <?=$arResult['DOCS']['commission_transport']['NAME']?>
                        <a href="<?=$arResult['DOCS']['commission_transport']['FILE']['SRC']?>" download="">скачать</a>
                    </div>
                    <div class="clip_item"></div>
                    <div class="clear l"></div>
                </div>
            </div>
        <?
        }

        if ($arResult['DOCS']['act_deal']['FILE']['SRC']
            && ($arParams['USER_TYPE'] == 'p'
                || (in_array($arParams['USER_TYPE'], array('c', 'f', 'p', 'ag', 'agc'))
                    && in_array('payment_send', array_keys($arParams['LOGS']))
                )
            )
        ) {
        ?>
            <div class="line_area deal_done">
                <div class="line_inner">
                    <div class="indicator"></div>
                    <div class="step_num"><?=$k++?></div>
                    <div class="name">
                        <?=$arResult['DOCS']['act_deal']['NAME']?>
                        <a href="<?=$arResult['DOCS']['act_deal']['FILE']['SRC']?>" download="">скачать</a>
                    </div>
                    <div class="clip_item"></div>
                    <div class="clear l"></div>
                </div>
            </div>
        <?
        }

        if ($arResult['DOCS']['act_transport']['FILE']['SRC']
            && ($arParams['USER_TYPE'] == 'p'
                || (in_array($arParams['USER_TYPE'], array('f', 'p', 't', 'ag'))
                    && in_array('payment_transport_send', array_keys($arParams['LOGS']))
                )
            )
        ) {
        ?>
            <div class="line_area deal_done">
                <div class="line_inner">
                    <div class="indicator"></div>
                    <div class="step_num"><?=$k++?></div>
                    <div class="name">
                        <?=$arResult['DOCS']['act_transport']['NAME']?>
                        <a href="<?=$arResult['DOCS']['act_transport']['FILE']['SRC']?>" download="">скачать</a>
                    </div>
                    <div class="clip_item"></div>
                    <div class="clear l"></div>
                </div>
            </div>
        <?
        }
        ?>
    </div>
<?
}

if ($k == 1) {
?>
    <div class="list_page_rows requests no-item">
        Ни одного документа не найдено
    </div>
<?
}

$templateData = $arResult;
?>