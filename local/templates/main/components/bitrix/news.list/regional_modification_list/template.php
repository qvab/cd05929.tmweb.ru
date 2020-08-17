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
if (sizeof($arResult["ITEMS"]) > 0) {
?>
    <div>
        <div class="crow radio_group clear">
            <div class="cname">
                Дата загрузки<br/>
            </div>
            <div class="status">
                Обработан<br/>
            </div>
            <div class="do_work">

            </div>
        </div>
    </div>
        <?
        foreach($arResult["ITEMS"] as $arItem) {
            $file_path = CFile::GetPath($arItem['PROPERTIES']['INPUT_FILE']['VALUE']);
            $full_file_path = '';
            if(!empty($file_path)){
                $full_file_path = $_SERVER['DOCUMENT_ROOT'].$file_path;
            }
            if(!((!file_exists($full_file_path))
                &&(rrsIblock::getPropListKey('regional_modification', 'SUCCESS', 'Y')!=$arItem['PROPERTIES']['SUCCESS']['VALUE_ENUM_ID']))){
                ?>
                <div>
                    <div class="crow radio_group clear">
                        <div class="cname">
                            <?=$arItem['DATE_CREATE']?><br/>
                        </div>
                        <div class="status">
                            <?=$arItem['PROPERTIES']['SUCCESS']['VALUE']?><br/>
                        </div>
                        <div class="do_work">
                            <?
                            if ($arItem['PROPERTIES']['SUCCESS']['VALUE_XML_ID'] == 'N') {
                            ?>
                                <a href="/admin/read_regional_mod.php?key=read-regional-modifacation&RM_ID=<?=$arItem['ID']?>">Обработать</a>
                            <?
                            }
                            ?>
                        </div>
                    </div>
                </div>
                <?
            }
        }
        ?>
        <?if($arParams["DISPLAY_BOTTOM_PAGER"]):?>
            <br /><?=$arResult["NAV_STRING"]?>
        <?endif;?>
<?
}
else {
    ShowError('Файлов для загрузки не найдено');
}
?>