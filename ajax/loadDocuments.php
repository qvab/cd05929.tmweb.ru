<?
header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header ("Cache-Control: no-cache, must-revalidate");
header ("Pragma: no-cache");
header("content-type: application/x-javascript; charset=UTF-8");
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
?>
<?
$arFilter = array(
    'IBLOCK_ID' => rrsIblock::getIBlockId('user_docs'),
    'ACYIVE' => 'Y',
    'SECTION_CODE' => $_POST['user_type'],
    'PROPERTY_TYPE' => rrsIblock::getPropListKey('user_docs', 'TYPE', $_POST['type'])
);

if ($_POST['type'] == 'ul' && in_array($_POST['user_type'], array('client', 'farmer'))) {
    $arFilter['PROPERTY_NDS'] = $_POST['nds'];
}

$arDocs = array();
$res = CIBlockElement::GetList(
    array('SORT' => 'ASC'),
    $arFilter,
    false,
    false,
    array('ID', 'NAME', 'CODE', 'PROPERTY_NAME')
);
while ($ob = $res->Fetch()) {
    $arDocs[] = $ob;
}

if (is_array($arDocs) && sizeof($arDocs) > 0) {
?>
    <div class="docs_line">
        <div class="docs_line">
            <div class="row">
                <div class="holder row_sub_head">Документы:</div>
                <?
                foreach ($arDocs as $doc) {
                ?>
                    <div class="doc_line row">
                        <div class="doc_title"><?=$doc['PROPERTY_NAME_VALUE']?></div>
                        <div class="doc_val">
                            <div class="input_before_file">
                                <span class="val">Прикрепить файл</span>
                                <div class="ico"></div>
                            </div>
                            <input name="PROP__<?=$doc['CODE']?>" type="file" />
                        </div>
                    </div>
                <?
                }
                ?>
            </div>
        </div>
    </div>
<?
}
?>