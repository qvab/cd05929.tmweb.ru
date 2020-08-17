<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();?>
<?
$arResult['UL_TYPES_LIST'] = rrsIblock::getPropListKey('client_profile', 'UL_TYPE');
if (isset($_POST['update']) && $_POST['update'] == 'y') {
    if(!empty($_POST['NAME'])) {
        $arResult['SHOW_FIELDS']['NAME'] = $_POST['NAME'];
    }
    if(!empty($_POST['LAST_NAME'])) {
        $arResult['SHOW_FIELDS']['LAST_NAME'] = $_POST['LAST_NAME'];
    }
    if(!empty($_POST['EMAIL'])) {
        $arResult['SHOW_FIELDS']['EMAIL'] = $_POST['EMAIL'];
    }
    if(!empty($_POST['PROP__PHONE'])) {
        $arResult['SHOW_PROPS']['PHONE'] = $_POST['PROP__PHONE'];
    }
    if(!empty($_POST['PROP__REGION'])) {
        $arResult['SHOW_PROPS']['REGION'] = $_POST['PROP__REGION'];
    }
    if(!empty($_POST['PROP__UL_TYPE'])) {
        $arResult['SHOW_PROPS']['UL_TYPE'] = $_POST['PROP__UL_TYPE'];
    }
    if(!empty($_POST['PROP__INN'])) {
        $arResult['SHOW_PROPS']['INN'] = $_POST['PROP__INN'];
    }

    if(!empty($_POST['PROP__FULL_COMPANY_NAME'])) {
        $arResult['SHOW_PROPS']['FULL_COMPANY_NAME'] = $_POST['PROP__FULL_COMPANY_NAME'];
    }
    if(!empty($_POST['PROP__IP_FIO'])) {
        $arResult['SHOW_PROPS']['IP_FIO'] = $_POST['PROP__IP_FIO'];
    }
    if(!empty($_POST['PROP__REG_DATE'])) {
        $arResult['SHOW_PROPS']['REG_DATE'] = $_POST['PROP__REG_DATE'];
    }
    if(!empty($_POST['PROP__YUR_ADRESS'])) {
        $arResult['SHOW_PROPS']['YUR_ADRESS'] = $_POST['PROP__YUR_ADRESS'];
    }
    if(!empty($_POST['PROP__POST_ADRESS'])) {
        $arResult['SHOW_PROPS']['POST_ADRESS'] = $_POST['PROP__POST_ADRESS'];
    }
    if(!empty($_POST['PROP__KPP'])) {
        $arResult['SHOW_PROPS']['KPP'] = $_POST['PROP__KPP'];
    }
    if(!empty($_POST['PROP__OGRN'])) {
        $arResult['SHOW_PROPS']['OGRN'] = $_POST['PROP__OGRN'];
    }
    if(!empty($_POST['PROP__OKPO'])) {
        $arResult['SHOW_PROPS']['OKPO'] = $_POST['PROP__OKPO'];
    }
    if(!empty($_POST['PROP__FIO_DIR'])) {
        $arResult['SHOW_PROPS']['FIO_DIR'] = $_POST['PROP__FIO_DIR'];
    }

    if(!empty($_POST['PROP__BIK'])) {
        $arResult['SHOW_PROPS']['BIK'] = $_POST['PROP__BIK'];
    }
    if(!empty($_POST['PROP__BANK'])) {
        $arResult['SHOW_PROPS']['BANK'] = $_POST['PROP__BANK'];
    }
    if(!empty($_POST['PROP__RASCH_SCHET'])) {
        $arResult['SHOW_PROPS']['RASCH_SCHET'] = $_POST['PROP__RASCH_SCHET'];
    }
    if(!empty($_POST['PROP__KOR_SCHET'])) {
        $arResult['SHOW_PROPS']['KOR_SCHET'] = $_POST['PROP__KOR_SCHET'];
    }
}

if (!$arResult['SHOW_PROPS']['UL_TYPE'])
    $arResult['SHOW_PROPS']['UL_TYPE'] = 'ul';
?>