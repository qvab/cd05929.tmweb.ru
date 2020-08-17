<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

if (!$USER->IsAuthorized()
    || !isset($_GET['uid'])
    || !is_numeric($_GET['uid'])
) {
    LocalRedirect('/');
    exit;
}

$group = getUserType($_GET['uid']);
?>
<h1><?$APPLICATION->ShowTitle();?></h1>

<?

$show_menu = $APPLICATION->IncludeComponent(
    'rarus:public_profile_menu',
    '',
    Array(
        'U_ID'  => $_GET['uid'],
        'TYPE'  => $group['TYPE'],
        'TAB'   => ''
    )
);?>
<?$APPLICATION->IncludeComponent(
    'rarus:public_profile',
    '',
    Array(
        'U_ID' => $_GET['uid'],
        'U_GROUP_ID' => $group['ID'],
        'EDIT_PROPS_LIST' => array(
            'PHONE',
            'REGION',
            'INN',
            'FULL_COMPANY_NAME',
            'IP_FIO',
            'REG_DATE',
            'YUR_ADRESS',
            'POST_ADRESS',
            'KPP',
            'OGRN',
            'OKPO',
            'FIO_DIR',
            'NDS',
            'SIGNER',
            'POST',
            'FIO_SIGN',
            'FOUND',
            'FOUND_NUM',
            'FOUND_DATE',
            'OSNOVANIE_PRAVA_PODPISI_FILE',
            'BANK',
            'BIK',
            'RASCH_SCHET',
            'KOR_SCHET',
            'DOU_DOC',
        ),
        'DOU_DEFAULT_DOC_IB_ID'=>rrsIblock::getIBlockId('services_docs_templates'),    //ID инфоблока к котором ищем документ который будет выводится в случае того, если у покупателя не загружен шаблон ДОУ
        'DOU_DEFAULT_DOC_ELEMENT_CODE'=>'CL_SHDVOUP',   //код элемента документа по умолчания для поля шаблон ДОУ
        'WITH_TABS' => $show_menu
    )
);?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>