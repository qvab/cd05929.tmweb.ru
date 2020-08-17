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
        if(isset($_POST['offer_id'])
            && is_numeric($_POST['offer_id'])
        ){
            $result_html = '';
            //ссылка для агента поставщика
            $target_url = '/farmer/offer/?offer_id=' . $_POST['offer_id'];
            if(isset($_POST['vol'])
                && is_numeric($_POST['vol'])
                && $_POST['vol'] > 0
            ){
                $target_url .= '&vol=' . $_POST['vol'];
            }
            $href_val = generateStraightHref($USER->GetID(), $_POST['uid'], 'f', '', '', '', $target_url, '/send_offer_page/');
            if($href_val != ''){
                //получаем даныне спроса для товаров
                $arrOffersSpros = farmer::getOfferSprosLast(array($_POST['offer_id']));

                //выбираем спрос товара из общего списка
                $arrCurOfferSpros = (array_key_exists($_POST['offer_id'], $arrOffersSpros) ? $arrOffersSpros[$_POST['offer_id']] : array());
                if(
                    !empty($arrCurOfferSpros['BY'])
                    && $arrCurOfferSpros['BY'] != $arrCurOfferSpros['Y']
                ){
                    $tmpVal = round($arrCurOfferSpros['Y'] - $arrCurOfferSpros['BY']);
                    if($tmpVal != 0) {
                        $arrCurOfferSpros['CH'] = $tmpVal;
                    }
                }

                $result_html = farmer::partnerCreateCounterRequestText($_POST['offer_id'], $href_val, !empty($_POST['no_best']), false, false, true, $arrCurOfferSpros);
                echo $result_html;
                exit;
            }
        }elseif(isset($_POST['wh_id'])
            && is_numeric($_POST['wh_id'])
            && isset($_POST['culture_id'])
            && is_numeric($_POST['culture_id'])
            && isset($_POST['o'])
            && is_numeric($_POST['o'])
            && isset($_POST['r'])
            && is_numeric($_POST['r'])
        ){
            //данные попапа организатора для встречного предложения
            $additional_arr = array();
            if(isset($_POST['offer_csmprice'])){
                $additional_arr['offer_csmprice'] = trim($_POST['offer_csmprice']);
            }
            if(isset($_POST['offer_nds'])){
                $additional_arr['offer_nds'] = trim($_POST['offer_nds']);
            }
            if(isset($_POST['offer_csm_addittext'])){
                $additional_arr['offer_csm_addittext'] = trim($_POST['offer_csm_addittext']);
            }
            if(isset($_POST['offer_tarif'])){
                $additional_arr['offer_tarif'] = trim($_POST['offer_tarif']);
            }
            if(isset($_POST['offer_tarif_distance'])){
                $additional_arr['offer_tarif_distance'] = trim($_POST['offer_tarif_distance']);
            }
            if(isset($_POST['offer_dump'])){
                $additional_arr['offer_dump'] = trim($_POST['offer_dump']);
            }
            if(isset($_POST['offer_base_price'])){
                $additional_arr['offer_base_price'] = trim($_POST['offer_base_price']);
            }
            if(isset($_POST['offer_delivery_type'])){
                $additional_arr['offer_delivery_type'] = trim($_POST['offer_delivery_type']);
            }
            if(isset($_POST['offer_agentfullprice'])){
                $additional_arr['offer_agentfullprice'] = trim($_POST['offer_agentfullprice']);
            }
            if(isset($_POST['offer_agentprice'])){
                $additional_arr['offer_agentprice'] = trim($_POST['offer_agentprice']);
            }

            //ссылка для агента покупателя
            $target_url = '/client/exclusive_offers/?warehouse_id=' . $_POST['wh_id']
                . '&culture_id=' . $_POST['culture_id']
                . '&r=' . $_POST['r']
                . '&o=' . $_POST['o'];
            if(!empty($_POST['counter_id'])){
                $target_url .= '&cid=' . $_POST['counter_id'];
            }
            $href_val = generateStraightHref($USER->GetID(), $_POST['uid'], 'c', '', '', '', $target_url, '/pair_page/');
            if($href_val != ''){
                $text = client::getClientTextToCOfferLink($_POST['uid'], $_POST['r'],$_POST['o'],$_POST['culture_id'],$_POST['wh_id'],$href_val, $additional_arr);
                echo $text;
                exit;
            }
        }
    }
}

echo 0;
exit;