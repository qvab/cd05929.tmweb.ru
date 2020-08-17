<?
header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header ("Cache-Control: no-cache, must-revalidate");
header ("Pragma: no-cache");
header ("content-type: application/x-javascript; charset=UTF-8");
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
$result = array('success'=>0);
CModule::IncludeModule('iblock');
    if((isset($_POST['link_id']))&&(isset($_POST['val']))&&(isset($_POST['user']))){
        if((!empty($_POST['link_id']))&&(!empty($_POST['val']))&&(!empty($_POST['user']))){
            $ib_code = '';
            switch ($_POST['user']){
                case 'client':
                    $ib_code = 'client_partner_link';
                    break;
                case 'transport':
                    $ib_code = 'transport_partner_link';
                    break;
                case 'farmer':
                    $ib_code = 'farmer_profile';
                    break;
            }
            if(!empty($ib_code)){
                $prop = array(
                    'VERIFIED' => rrsIblock::getPropListKey($ib_code, 'VERIFIED', $_POST['val'])
                );
                if ($ib_code == 'farmer_profile') {
                    $prop['PARTNER_ID_TIMESTAMP'] = 0;
                }
                CIBlockElement::SetPropertyValuesEx($_POST['link_id'], false, $prop);
                //отправляем оповещения

                $res = CIBlockElement::GetList(
                    array('ID' => 'ASC'),
                    array(
                        'IBLOCK_ID' => rrsIblock::getIBlockId($ib_code),
                        'ACTIVE' => 'Y',
                        'ID' => $_POST['link_id']
                    ),
                    false,
                    false,
                    array(
                        'ID',
                        'NAME',
                        'PROPERTY_PARTNER_ID',
                        'PROPERTY_USER' . ($_POST['user'] != 'farmer' ? '_ID' : ''),
                    )
                );
                if($data = $res->Fetch()) {
                    $email_event_code = '';
                    $url = '';
                    switch ($_POST['user']){
                        case 'client':
                            $profile = client::getProfile($data['PROPERTY_USER_ID_VALUE'],true);
                            $email_event_code = 'CLIENT_PARTNER_LINK_VERIFIED';
                            $url = '/client/link_to_partner/';
                            break;
                        case 'transport':
                            $profile = transport::getProfile($data['PROPERTY_USER_ID_VALUE'],true);
                            $email_event_code = 'TRANSPORT_PARTNER_LINK_VERIFIED';
                            $url = '/transport/link_to_partner/';
                            break;
                        case 'farmer':
                            $profile = farmer::getProfile($data['PROPERTY_USER_VALUE'],true);
                            $email_event_code = 'FARMER_PARTNER_LINK_VERIFIED';
                            $url = '/farmer/link_to_partner/';
                            break;
                    }
                    $partner = partner::getProfile($data['PROPERTY_PARTNER_ID_VALUE']);
                    if(!empty($email_event_code)){
                        $arEventFields = array(
                            'EMAIL' => $profile['USER']['EMAIL'],
                            'COMPANY_NAME' => $partner['PROPERTY_FULL_COMPANY_NAME_VALUE'],
                            'LINKED_URL' => $GLOBALS['host'].$url,
                            'PARTNER_ID' => $data['PROPERTY_PARTNER_ID_VALUE'],
                        );
                        CEvent::Send($email_event_code, 's1', $arEventFields);
                        notice::addNotice($profile['USER']['ID'], 'l', 'Организатор подтвердил прикрепление', $url, '#' . $data['PROPERTY_PARTNER_ID_VALUE']);

                        //отправка сообщений агенту покупателя
                        /*if($_POST['user'] == 'client'){
                            $agentObj = new agent();
                            $agentData = $agentObj->getProfileByClientID($profile['USER']['ID']);

                            if(isset($agentData['DEALS_RIGHTS']) && $agentData['DEALS_RIGHTS']){
                                $url                            = '/client_agent/';
                                $arEventFields['EMAIL']         = $agentData['EMAIL'];
                                $arEventFields['LINKED_URL']    = $GLOBALS['host'].$url;
                                CEvent::Send('CLIENT_PARTNER_LINK_VERIFIED_FOR_AGENT', 's1', $arEventFields);
                                notice::addNotice($agentData['USER']['ID'], 'l', 'Организатор подтвердил прикрепление', $url, '#' . $data['PROPERTY_PARTNER_ID_VALUE']);
                            }
                        }elseif($_POST['user'] == 'farmer'){
                            //уведомление для агента АП
                            $agentObj = new agent();
                            $agentData = $agentObj->getProfileByFarmerID($profile['USER']['ID']);

                            if(isset($agentData['DEALS_RIGHTS']) && $agentData['DEALS_RIGHTS']){
                                $url                            = '/agent/';
                                $arEventFields['EMAIL']         = $agentData['EMAIL'];
                                $arEventFields['LINKED_URL']    = $GLOBALS['host'].$url;
                                CEvent::Send('FARMER_PARTNER_LINK_VERIFIED_FOR_AGENT', 's1', $arEventFields);
                                notice::addNotice($agentData['USER']['ID'], 'l', 'Организатор подтвердил прикрепление', $url, '#' . $data['PROPERTY_PARTNER_ID_VALUE']);
                            }
                        }*/
                    }
                }
                if (in_array($noticeList['c_l']['ID'], $partnerProfile['PROPERTY_NOTICE_VALUE'])) {
                    $transportProfile = transport::getProfile($arParams['U_ID']);
                    $profile = client::getProfile($user_id);
                }

                $result = array('success'=>1);
            }

        }
    }
    echo json_encode($result);
    die();