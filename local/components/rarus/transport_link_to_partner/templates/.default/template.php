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
    if (count($arResult['CURRENT_PARTNER_DATA']) > 0) {
    ?>
        <div class="current_partner_link_area">
            <div class="title">Вы привязаны к организатор<?=(count($arResult['CURRENT_PARTNER_DATA']) > 1 ? 'ам' : 'у')?>:</div>
            <div class="list_page_rows requests">
                <?
                foreach($arResult['CURRENT_PARTNER_DATA'] as $cur_data){
                ?>
                    <div class="line_area">
                        <div class="line_inner item">
                            <div class="partner_val"><?=implode(' ', array($cur_data['LAST_NAME'], $cur_data['NAME'], $cur_data['SECOND_NAME'], '[' . $cur_data['LOGIN'] . ']'));?></div>
                            <div data-id="<?=$cur_data['ID'];?>" title="Отвязаться от организатора" class="unlink_but<?if(isset($arResult['UNCOMPLETE_DEALS'][$cur_data['ID']])){?> disabled<?}?>"></div>
                            <div class="clear"></div>
                        </div>
                        <div class="line_additional">
                            <div class="prop_area">
                                <a href="/profile/?uid=<?=$cur_data['ID'];?>">Перейти в профиль пользователя</a>
                                <?/*<div class="sub_row"></div>
                                <?
                                if(isset($cur_data['DOCS']['PARTNER_LINK_DOC']['SRC']) && trim($cur_data['DOCS']['PARTNER_LINK_DOC']['SRC']) != '') {
                                ?>
                                    <div class="row_sub_head">Файл договора:</div>
                                    <div class="sub_row">
                                        <a href="<?=$cur_data['DOCS']['PARTNER_LINK_DOC']['SRC'];?>" download="<?=$cur_data['DOCS']['PARTNER_LINK_DOC']['NAME'];?>">Скачать</a>
                                    </div>
                                    <div class="sub_row">
                                        <input type="text" readonly="readonly" name="doc_num" placeholder="Номер договора" value="<?=$cur_data['DOCS']['PARTNER_LINK_DOC_NUM'];?>" />
                                    </div>
                                    <div class="sub_row">
                                        <input type="text" readonly="readonly" name="doc_date" placeholder="Дата договора" value="<?=$cur_data['DOCS']['PARTNER_LINK_DOC_DATE'];?>" />
                                    </div>
                                <?
                                }
                                else {
                                ?>
                                    <div class="row_sub_head">Договора с организатором нет</div>
                                <?
                                }*/
                                ?>
                            </div>
                        </div>
                    </div>
                <?
                }
                ?>
            </div>
        </div>
    <?
    }
    ?>
    <div class="partner_list_area">
        <?
        if (!is_numeric($arParams['REGION_ID'])) {
            echo '<div class="row mess_txt">Выберите регион для отображения доступных организаторов.</div>';
        }
        elseif (count($arResult['PARTNERS_LIST']) > 0) {
        ?>
            <div class="title">Список других организаторов системы в регионе:</div>
            <div class="list_page_rows requests partner_other_list">
                <?
                foreach ($arResult['PARTNERS_LIST'] as $cur_val) {
                ?>
                    <div class="line_area">
                        <div class="line_inner item">
                            <div class="name"><?=implode(' ', array($cur_val['LAST_NAME'], $cur_val['NAME'], $cur_val['SECOND_NAME'], '[' . $cur_val['LOGIN'] . ']'));?></div>
                                <div data-id="<?=$cur_val['ID'];?>" class="link_but"></div>
                            <div class="clear"></div>
                        </div>
                    </div>
                <?
                }
                ?>
            </div>
        <?
        }
        else {
        ?>
            <div class="row mess_txt">Нет организаторов для отображения в выбранном регионе.</div>
        <?
        }
        ?>
    </div>
<?
}
?>