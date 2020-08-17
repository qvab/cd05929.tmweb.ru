<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$aMenuLinksExt = array();

$aMenuLinksExt[] = array(
    "Основное",
    "/farmer/deals/".$_REQUEST['ELEMENT_ID']."/",
    Array(),
    "",
    "",
);
$aMenuLinksExt[] = array(
    "Информация",
    "/farmer/deals/".$_REQUEST['ELEMENT_ID']."/?page=info",
    Array(),
    "",
    "",
);
$aMenuLinksExt[] = array(
    "Документы",
    "/farmer/deals/".$_REQUEST['ELEMENT_ID']."/?page=docs",
    Array(),
    "",
    "",
);

if ($_REQUEST['ELEMENT_ID']) {
    $res = CIBlockElement::GetList(
        array('ID' => 'DESC'),
        array(
            'IBLOCK_ID' => rrsIblock::getIBlockId('client_marks'),
            'ACTIVE' => 'Y',
            'PROPERTY_DEAL' => $_REQUEST['ELEMENT_ID']
        ),
        false,
        false,
        array('ID', 'PROPERTY_CHECK_FARMER')
    );
    if ($ob = $res->Fetch()) {
        if (!$ob['PROPERTY_CHECK_FARMER_ENUM_ID']) {
            $aMenuLinksExt[] = array(
                "Оценить покупателя",
                "/farmer/deals/".$_REQUEST['ELEMENT_ID']."/?page=mark",
                Array(),
                "",
                "",
            );
        }
    }
}

$aMenuLinks = array_merge($aMenuLinks, $aMenuLinksExt);
?>