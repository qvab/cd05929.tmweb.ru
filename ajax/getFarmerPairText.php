<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
global $USER;

//проверяем обязательные данные
if(isset($_POST['pair_id'])&&(($_POST['pair_id']))){
    CModule::IncludeModule('iblock');
    $el_obj = new CIBlockElement;
    $res = $el_obj->GetList(
        array('ID' => 'DESC'),
        array(
            'IBLOCK_ID' => rrsIblock::getIBlockId('deals_deals'),
            'ID' => $_POST['pair_id']
        ),
        false,
        false,
        array(
            'ID',
            'PROPERTY_CULTURE',
            'PROPERTY_CULTURE.NAME',
            'PROPERTY_CLIENT',
            'PROPERTY_FARMER',
            'PROPERTY_ACC_PRICE_CSM',
            'PROPERTY_B_NDS'
        )
    );
    if($data = $res->Fetch()){
        $text = '';
        $clientProfile = array();
        if($data['PROPERTY_CLIENT_VALUE']){
            $clientProfile = client::getProfile($data['PROPERTY_CLIENT_VALUE'],true);
        }
        $partnerProfile = partner::getProfile($USER->GetID(),true);
        $partnerPhone = '';
        $partnerName = '';
        if((sizeof($partnerProfile))&&(is_array($partnerProfile))){
            $partnerName = trim($partnerProfile['USER']['LAST_NAME'].' '.$partnerProfile['USER']['NAME'].' '.$partnerProfile['USER']['SECOND_NAME']);
            if(isset($partnerProfile['PROPERTY_PHONE_VALUE'])){
                $partnerPhone = $partnerProfile['PROPERTY_PHONE_VALUE'];
            }
        }
        $nds = '';
        if($data['PROPERTY_B_NDS_VALUE'] == 'N'){
            $nds = ' без НДС';
        }elseif($data['PROPERTY_B_NDS_VALUE'] == 'Y'){
            $nds = ' с НДС';
        }
        if((sizeof($clientProfile))&&(is_array($clientProfile))){
            $text = 'Найден покупатель '.$clientProfile['COMPANY'].' на ваше предложение по товару '.$data['PROPERTY_CULTURE_NAME'].' с ценой «с места»'.$nds.': '.number_format($data['PROPERTY_ACC_PRICE_CSM_VALUE'], 0, ',', ' ').' руб/т'."<br><br>";
            if(!empty($clientProfile['PROPERTY_PHONE_VALUE'])){
                $text.='Телефон покупателя: '.$clientProfile['PROPERTY_PHONE_VALUE']."<br><br>";
            }
            $text.='ФИО покупателя: '.trim($clientProfile['USER']['LAST_NAME'].' '.$clientProfile['USER']['NAME'].' '.$clientProfile['USER']['SECOND_NAME'])."<br><br>";
            $text.='Ваш организатор: ';
            if(!empty($partnerPhone)){
                $text.=$partnerPhone.', ';
            }
            $text.=$partnerName;
        }
        echo $text;
        exit;
    }
}
echo 0;
exit;