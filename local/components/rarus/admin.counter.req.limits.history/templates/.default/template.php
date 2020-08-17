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
    <a target="_blank" href="/bitrix/admin/iblock_element_edit.php?IBLOCK_ID=<?=rrsIblock::getIBlockId('counter_request_limits_changes');?>&type=open_counter&ID=0&lang=ru&find_section_section=0&IBLOCK_SECTION_ID=0&from=iblock_list_admin">Перейти к созданию операции со счётчиком принятий</a>
<?
if(count($arResult['ITEMS']) > 0) {
    ?>
    <table class="history_table">
        <tr class="legend">
            <th class="date">Дата</th>
            <th class="userdata">Покупатель</th>
            <th class="was">Было</th>
            <th class="become">Стало</th>
            <th class="u_comment">Комментарий пользователю</th>
            <th class="a_comment">Комментарий администраторам</th>
        </tr>
    <?
    foreach($arResult['ITEMS'] as $item_id => $arItem){
        $comm_value = (isset($arResult['COMMENTS'][$arItem['ELEMENT_ID']]['U_COMMENT']) ? $arResult['COMMENTS'][$arItem['ELEMENT_ID']]['U_COMMENT'] : '');
        //если нет комментария, то устанавливаем сообщение по умолчанию
        if($comm_value == ''){
            $comm_value = client::counterRequestOpenerDefaultText($arItem['ACTION'], $arItem['NUMBER']);
        }
        ?>
        <tr data-id="<?=$item_id;?>">
            <td><?=$arItem['DATE'];?></td>
            <td><a target="_blank" href="/bitrix/admin/user_edit.php?lang=ru&ID=<?=$arItem['UID'];?>"><?=(isset($arResult['USERS'][$arItem['UID']]) ? $arResult['USERS'][$arItem['UID']] : $arItem['UID']);?></a></td>
            <td><?=$arItem['BEFORE'];?></td>
            <td><?=$arItem['AFTER'];?></td>
            <td><?=$comm_value;?></td>
            <td><?=(isset($arResult['COMMENTS'][$arItem['ELEMENT_ID']]['A_COMMENT']) ? $arResult['COMMENTS'][$arItem['ELEMENT_ID']]['A_COMMENT'] : '-');?></td>
        </tr>
        <?
    }
    ?></table><?

    //пагинация
    $APPLICATION->IncludeComponent("bitrix:main.pagenavigation", ".default",
        array(
            "NAV_OBJECT" => $arResult['NAV_OBJ'],
            "SEF_MODE" => "N"
        ),
        false
    );
}else{
    ?><br/>Ни одной записи не найдено<?
}