<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Title");

echo '<br><br><br><br><br><br>';
var_dump(rrsIblock::getRoute('44.6014637,33.5205434', '45.4773295,34.3303085'));
?>


<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
