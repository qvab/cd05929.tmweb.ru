<?
//проверяет нет ли просроченных встречных предложений (удаляет те, что существуют более 48 часов)

if(empty($_SERVER['SHELL']))
die();

set_time_limit(12000);
//боевой
$_SERVER["DOCUMENT_ROOT"] = "/home/bitrix/www";
//песочница
//$_SERVER["DOCUMENT_ROOT"] = "/www/unorganized_projects/agrohelper/sandboxes/dmitrd/www";

define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS",true);
flush();
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
ob_end_flush();

CModule::IncludeModule('iblock');
CModule::IncludeModule('highloadblock');

global $DB;
$arFilter = array(
    '<UF_DATE' => $DB->FormatDate(date("d.m.Y H:i:s", time() - 168 * 3600), "DD.MM.YYYY HH:MI:SS", FORMAT_DATETIME)
); //7 суток

$counter_req_list = log::_getEntitiesList(log::getIdByName('COUNTEROFFERS'), $arFilter);
//получение почт поставщиков
$user_mails = array();
$agentObj = new agent();
$farmers = array();
$farmers_agents = array();
$agents = array();
$offers = array();
$offers_wh = array();

if(count($counter_req_list) > 0){
    foreach($counter_req_list as $cur_con_request){
        $offers[$cur_con_request['UF_OFFER_ID']] = 1;
        $farmers[$cur_con_request['UF_FARMER_ID']] = '';
        $agentIds = farmer::getLinkedPartnerList($cur_con_request['UF_FARMER_ID']);
        foreach ($agentIds as $id){
            $agents[$id] = '';
            $farmers_agents[$cur_con_request['UF_FARMER_ID']][$id] = 1;
        }
    }
    if(count($farmers) > 0){
        $res = CUser::GetList(($by = 'id'), ($order = 'asc'), array('ID' => implode(' | ', array_keys($farmers))), array('FIELDS' => array('ID', 'EMAIL')));
        while($data = $res->Fetch()){
            $farmers[$data['ID']] = $data['EMAIL'];
        }
    }
    if(count($agents) > 0){
        $res = CUser::GetList(($by = 'id'), ($order = 'asc'), array('ID' => implode(' | ', array_keys($agents))), array('FIELDS' => array('ID', 'EMAIL')));
        while($data = $res->Fetch()){
            $agents[$data['ID']] = $data['EMAIL'];
        }
    }
    $res = CIBlockElement::GetList(
        array('ID' => 'DESC'),
        array(
            'IBLOCK_ID' => rrsIblock::getIBlockId('farmer_offer'),
            'ACTIVE' => 'Y',
            'PROPERTY_ACTIVE' => rrsIblock::getPropListKey('farmer_offer', 'ACTIVE', 'yes'),
            'ID' => array_keys($offers)
        ),
        false,
        false,
        array(
            'ID',
            'PROPERTY_WAREHOUSE.NAME'
        )
    );
    while ($ob = $res->Fetch()) {
        $offers_wh[$ob['ID']] = $ob['PROPERTY_WAREHOUSE_NAME'];
    }

}
$unanswered_offers = array();

//удаление старых встречных предложений и отправка уведомлений
foreach($counter_req_list as $cur_con_request){
    $arRequest = client::getRequestById($cur_con_request['UF_REQUEST_ID']);
    $wh_name = '';
    if(isset($offers_wh[$cur_con_request['UF_OFFER_ID']])){
        $wh_name = $offers_wh[$cur_con_request['UF_OFFER_ID']];
    }
    //если исходный объем равен оставшемуся, то предложение не отвеченное
    if($cur_con_request['UF_VOLUME_OFFER'] == $cur_con_request['UF_VOLUME_REMAINS']){
        $unanswered_offers[$cur_con_request['UF_OFFER_ID']]['VOLUME'] = $cur_con_request['UF_VOLUME_OFFER'];
        $unanswered_offers[$cur_con_request['UF_OFFER_ID']]['PRICE'] = $cur_con_request['UF_FARMER_PRICE'];
        $unanswered_offers[$cur_con_request['UF_OFFER_ID']]['CULTURE'] = $arRequest['CULTURE_NAME'];
        $unanswered_offers[$cur_con_request['UF_OFFER_ID']]['WH_NAME'] = $wh_name;
        $unanswered_offers[$cur_con_request['UF_OFFER_ID']]['UF_FARMER_ID'] = $cur_con_request['UF_FARMER_ID'];
        $unanswered_offers[$cur_con_request['UF_OFFER_ID']]['UF_CLIENT_ID'][$cur_con_request['UF_CLIENT_ID']] = 1;
        $unanswered_offers[$cur_con_request['UF_OFFER_ID']]['FARMER_NDS'] = ($cur_con_request['UF_NDS_FARMER'] == 'yes' ? ' (с НДС)' : ' (без НДС)');
    }
    log::_deleteEntity(log::getIdByName('COUNTEROFFERS'), $cur_con_request['ID']);

    //уведомление АП об окончании действия встречного предложения
    /*if(isset($user_mails[$cur_con_request['UF_FARMER_ID']])) {
        $arRequest = client::getRequestById($cur_con_request['UF_REQUEST_ID']);
        $req_data = '<br/>Культура: ' . $arRequest['CULTURE_NAME'] . '<br/>Цена за тонну: ' .
            $cur_con_request['UF_FARMER_PRICE'] . '<br/>Объём: ' . $cur_con_request['UF_VOLUME'];
        $arEventFields = array(
            'COUNTER_REQUEST_ID' => $cur_con_request['ID'],
            'EMAIL' => $user_mails[$cur_con_request['UF_FARMER_ID']],
            'REQUEST_DATA' => $req_data
        );
        CEvent::Send('FARMER_COUNTER_REQUEST_END', 's1', $arEventFields);
    }*/
}
if((sizeof($unanswered_offers))&&(is_array($unanswered_offers))){

    //получение данных поставщиков (имен, названий, телефонов)
    $arrFarmersIds = array();
    $arrFarmersData = array();
    $arrFarmersNames = array();
    foreach ($unanswered_offers as $offer_id => $values){
        $arrFarmersIds[$values['UF_FARMER_ID']] = true;
    }
    if(count($arrFarmersIds) > 0) {
        $arrFarmersData = farmer::getUserListData(array_keys($arrFarmersIds));
    }
    unset($arrFarmersIds);
    //дополнительно получение названий для организаторов
    if(count($arrFarmersData) > 0){
        $arrFarmersNames = getUserName(array_keys($arrFarmersData));
    }

    foreach ($unanswered_offers as $offer_id=>$values){

        $f_href = $GLOBALS['host'] . '/farmer/offer/?offer_id='.$offer_id;
        $href_val = generateStraightHref(0, $values['UF_FARMER_ID'], 'f', '', '', '', $f_href, '/send_offer_page/');
        $f_link = '<a href="'.$href_val.'">'.$href_val.'</a>';

        $client_count = 'не принял ни один из '.count($values['UF_CLIENT_ID']).' покупателей';
        if(count($values['UF_CLIENT_ID']) == 1){
            $client_count = 'не принял ни один покупатель';
        }

        if(isset($farmers[$values['UF_FARMER_ID']])){
            $arEventFields = array(
                'OFFER_LINK' => $f_link,
                'EMAIL' => $farmers[$values['UF_FARMER_ID']],
                'CULTURE' => $values['CULTURE'],
                'PRICE' => $values['PRICE'],
                'VOLUME' => $values['VOLUME'],
                'WH' => $values['WH_NAME'],
                'CLIENT_COUNT' => $client_count,
                'FARMER_NDS' => $values['FARMER_NDS'],
            );
            CEvent::Send('FARMER_COUNTER_REQUEST_END_ALL', 's1', $arEventFields);
            /**
             * отправляем уведомление
             * Предложение по товару "Культура" на складе "Склад" не было принято
             */
            $message = 'Предложение по товару "'.$values['CULTURE'].'" на складе "'.$values['WH_NAME'].'" не было принято';
            notice::addNotice($values['UF_FARMER_ID'], 'd', $message, $f_href, '#' . $offer_id);
        }
        //отправка сообщений агенту поставщика
        if(isset($farmers_agents[$values['UF_FARMER_ID']])&&is_array($farmers_agents[$values['UF_FARMER_ID']])){
            foreach ($farmers_agents[$values['UF_FARMER_ID']] as $agent_id=>$i){
                if(isset($agents[$agent_id])){
                    //шлем письмо агенту
                    $a_href = $GLOBALS['host'] . '/partner_offer_page/?offer_id='.$offer_id;
                    $href_val = generateStraightHref($agent_id, $values['UF_FARMER_ID'], 'f', '', '', '', $a_href, '/partner_offer_page/');
                    $a_link = '<a href="'.$href_val.'">'.$href_val.'</a>';
                    $sUserName = (!empty($arrFarmersData[$values['UF_FARMER_ID']]['FIO']) ? $arrFarmersData[$values['UF_FARMER_ID']]['FIO'] : '');
                    $sInfo = (!empty($arrFarmersNames[$values['UF_FARMER_ID']]) ? '<br/>ФИО: ' . $arrFarmersNames[$values['UF_FARMER_ID']] . '<br/>' : '<br/>');
                    if(!empty($arrFarmersData[$values['UF_FARMER_ID']]['PHONE'])){
                        $sInfo .= 'Телефон:' . $arrFarmersData[$values['UF_FARMER_ID']]['PHONE'] . '<br/>';
                    }

                    $arEventFields = array(
                        'OFFER_LINK' => $a_link,
                        'EMAIL' => $agents[$agent_id],
                        'CULTURE' => $values['CULTURE'],
                        'PRICE' => $values['PRICE'],
                        'VOLUME' => $values['VOLUME'],
                        'WH' => $values['WH_NAME'],
                        'CLIENT_COUNT' => $client_count,
                        'USER_NAME' => $sUserName,
                        'USER_INFO' => $sInfo,
                        'FARMER_NDS' => $values['FARMER_NDS'],
                    );
                    CEvent::Send('PARTNER_COUNTER_REQUEST_END_ALL', 's1', $arEventFields);
                    /**
                     * отправляем уведомление
                     * Предложение по товару "Культура" на складе "Склад" не было принято
                     */
                    $message = 'Предложение по товару "'.$values['CULTURE'].'" на складе "'.$values['WH_NAME'].'" не было принято';
                    notice::addNotice($agent_id, 'd', $message, $GLOBALS['host'] . '/partner/offer/?offer_id=' . $offer_id, '#' . $offer_id);
                }
            }
        }
    }
}

?>