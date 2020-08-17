<?
header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header ("Cache-Control: no-cache, must-revalidate");
header ("Pragma: no-cache");
header("content-type: application/x-javascript; charset=UTF-8");
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

CModule::IncludeModule('iblock');
global $USER;
$result = array('success'=>0);
if((isset($_POST['clid']))&&(isset($_POST['fid']))){
    if ($USER->IsAuthorized()){
        $arGroups = CUser::GetUserGroup($USER->GetID());
        if (in_array(10, $arGroups )) {
            //проверяем если текущий пользователь партнер
            //добавляем фермера в черный список покупателя
            $bl_element_id = BlackList::addFarmerToClientBL($_POST['clid'],$_POST['fid']);
            $result = array('success'=>$bl_element_id);
        }
    }
}
echo json_encode($result);
die();



?>