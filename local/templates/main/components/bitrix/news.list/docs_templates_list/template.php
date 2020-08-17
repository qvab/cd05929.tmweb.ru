<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
if (sizeof($arResult["ITEMS"]) > 0) {
    foreach($arResult["ITEMS"] as $arItem) {
        $arExt = explode('.', $arItem['DISPLAY_PROPERTIES']['FILE']['FILE_VALUE']['FILE_NAME']);
        $ext = $arExt[count($arExt) - 1];
        if (in_array($ext, array('doc', 'docx', 'rtf'))) {
            $type = 'doc';
        }
        elseif (in_array($ext, array('xls', 'xlsx', 'csv'))) {
            $type = 'xls';
        }
        else {
            $type = '';
        }
        ?>
        <div>
            <div class="crow radio_group clear">
                <div class="cname">
                    <?=$arItem['NAME']?><br/>
                    <span class="comment_sp">
                        (размер файла: <?=CFile::FormatSize($arItem['DISPLAY_PROPERTIES']['FILE']['FILE_VALUE']['FILE_SIZE'])?>)
                    </span>
                </div>
                <div class="download_block">
                    <a href="<?=$arItem['DISPLAY_PROPERTIES']['FILE']['FILE_VALUE']['SRC']?>" download="<?=$arItem['DISPLAY_PROPERTIES']['FILE']['FILE_VALUE']['ORIGINAL_NAME']?>">
                        <div class="download_<?=$type?>"></div>
                    </a>
                </div>
            </div>
        </div>
    <?
    }
    ?>
    <?if($arParams["DISPLAY_BOTTOM_PAGER"]):?>
        <?=$arResult["NAV_STRING"]?>
    <?endif;?>
<?
}
else {
    ShowError('Документы не найдены');
}
?>
