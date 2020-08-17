<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();?>
<?
if ($_REQUEST['save']) {
    CModule::IncludeModule('iblock');
    $el = new CIBlockElement;

    $prop = array();
    if (sizeof($_REQUEST['notice']) > 0) {
        $n = 0;
        foreach ($_REQUEST['notice'] as $item) {
            $prop['NOTICE']["n".$n] = array("VALUE" => $item);
            $n++;
        }
    }
    else {
        $prop['NOTICE'][] = array("VALUE" => '');
    }

    CIBlockElement::SetPropertyValuesEx(
        $_REQUEST['id'],
        $arParams['IBLOCK_ID'],
        $prop
    );

    $res = $el->Update($_REQUEST['id'], array('TIMESTAMP_X' => date('d.m.Y H:i:s')));

    LocalRedirect($arParams['SELF_URL'] . '?up=ok');
}
?>

<script type="application/javascript">
    $(document).ready(function() {
        $('.ch_all_tr').on('click', function(e){
            $(this).parents('form').find('input[type="checkbox"]:not(:checked)').trigger('click');
            e.preventDefault();
        });

        $('.no_tr').on('click', function(e){
            $(this).parents('form').find('input[type="checkbox"]:checked').trigger('click');
            e.preventDefault();
        });
    });
</script>