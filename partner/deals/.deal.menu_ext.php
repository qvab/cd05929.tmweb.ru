<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$aMenuLinksExt = array();

$aMenuLinksExt[] = array(
    "Основное",
    "/partner/deals/".$_REQUEST['ELEMENT_ID']."/",
    Array(),
    "",
    "",
);
$aMenuLinksExt[] = array(
    "Информация",
    "/partner/deals/".$_REQUEST['ELEMENT_ID']."/?page=info",
    Array(),
    "",
    "",
);
$aMenuLinksExt[] = array(
    "Документы",
    "/partner/deals/".$_REQUEST['ELEMENT_ID']."/?page=docs",
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
        array('ID', 'PROPERTY_CHECK_PARTNER')
    );
    if ($ob = $res->Fetch()) {
        if (!$ob['PROPERTY_CHECK_PARTNER_ENUM_ID']) {
            $aMenuLinksExt[] = array(
                "Оценить покупателя",
                "/partner/deals/".$_REQUEST['ELEMENT_ID']."/?page=mark",
                Array(),
                "",
                "",
            );
        }
    }
}

$aMenuLinks = array_merge($aMenuLinks, $aMenuLinksExt);
?>