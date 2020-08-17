<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
CModule::IncludeModule('iblock');
$res = CIBlockElement::GetList(
    array('ID' => 'DESC'),
    array('IBLOCK_ID' => rrsIblock::getIBlockId('user_notice'), 'ACTIVE' => 'Y', 'PROPERTY_USER' => $USER->GetID(), 'PROPERTY_READ' => 'N'),
    false,
    false,
    array('ID', 'NAME')
);
$notRead = $res->SelectedRowsCount();
if ($notRead > 0) {
    $class = '';
    if ($notRead > 99)
        $class = 'more';
?>
    <script type="application/javascript">
        $(document).ready(function(){
           $('.mess_avail').find('.ico').html('<span class="<?=$class?>"><?=$notRead?></span>');
        });
    </script>
<?
}
else {
?>
    <script type="application/javascript">
        $(document).ready(function(){
            $('.mess_avail').removeClass('mess_avail').addClass('mess');
        });
    </script>
<?
}
?>