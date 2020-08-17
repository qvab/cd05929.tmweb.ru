<?

//получение ссылки для формирования встречного предложения (предполагается. что текущий пользователь - агент/организатор)
if(isset($_POST['uid'])
    && is_numeric($_POST['uid'])
){
    header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
    header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
    header ("Cache-Control: no-cache, must-revalidate");
    header ("Pragma: no-cache");
    header("content-type: application/x-javascript; charset=UTF-8");

    require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

    global $USER;
    if($USER->IsAuthorized()){
        if(isset($_POST['wh_id'])
            && is_numeric($_POST['wh_id'])
            && isset($_POST['culture_id'])
            && is_numeric($_POST['culture_id'])
        ){
            $arParams = [
                'warehouse_id' => $_POST['wh_id'],
                'culture_id'   => $_POST['culture_id'],
                'checked'      => $_POST['checked'],
                'page'         => $_POST['page'],

            ];
            //ссылка для агента покупателя
            $target_url = '/client/exclusive_offers/?' . http_build_query($arParams);
            $href_val = generateStraightHref($USER->GetID(), $_POST['uid'], 'c', '', '', '', $target_url);
            if($href_val != ''){
                echo $href_val;
                exit;
            }
        }
    }
}

echo 0;
exit;