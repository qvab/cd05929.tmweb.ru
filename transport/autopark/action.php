<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");?>
<?
if ((isset($_POST['NAME'])) && (sizeof($_POST['P'])) && (is_array($_POST['P']))) {
    CModule::IncludeModule("iblock");
    $el = new CIBlockElement;
    $PROP = array();

    $PROP['ADDRESS'] = $_POST['P']['ADDRESS'];
    $PROP['REGION'] = $_POST['P']['REGION'];
    $PROP['MAP'] = implode(',', $_POST['P']['MAP']);
    $PROP['TRANSPORT'] = $USER->GetID();
    $PROP['ACTIVE'] = rrsIblock::getPropListKey('transport_autopark', 'ACTIVE', 'yes');

    $fieldArray = Array(
        'IBLOCK_ID' => rrsIblock::getIBlockId('transport_autopark'),
        'NAME' => $_POST['NAME'],
        'ACTIVE' => 'Y',
        'PROPERTY_VALUES'=> $PROP,
    );

    $el->Add($fieldArray);
}
LocalRedirect('/transport/autopark/');
?>