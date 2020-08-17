<?php


class BlackList {

    /**
     * Проверка есть ли фермер в черном списке клиента
     *
     * @param $client_id - ID клиента
     * @param $farmer_id - ID фермера
     * @return int
     */
    static function ClientFarmerBLExists($client_id,$farmer_id){
        CModule::IncludeModule('iblock');
        $elObj = new CIBlockElement;
        $res = $elObj->GetList(array('ID' => 'DESC'), array(
            'IBLOCK_ID' => rrsIblock::getIBlockId('blacklist_ap'),
            'PROPERTY_USER_ID' => $client_id,
            'PROPERTY_FARMER_ID' => $farmer_id),
            false, false, array('ID')
        );
        if ($res->SelectedRowsCount() > 0) {
            $item = $res->Fetch();
            $ID = $item['ID'];
            return $ID;
        }
        return 0;
    }

    /**
     * Проверка, есть ли партнер в черном списке клиента
     * @param $client_id - ID клиента
     * @param $partner_id - ID партнера
     * @return int
     */
    static function clientPartnerBLExists($client_id,$partner_id){
        CModule::IncludeModule('iblock');
        $elObj = new CIBlockElement;
        $res = $elObj->GetList(array('ID' => 'DESC'), array(
            'IBLOCK_ID' => rrsIblock::getIBlockId('blacklist_partner'),
            'PROPERTY_USER_ID' => $client_id,
            'PROPERTY_PARTNER_ID' => $partner_id),
            false, false, array('ID')
        );
        if ($res->SelectedRowsCount() > 0) {
            $item = $res->Fetch();
            $ID = $item['ID'];
            return $ID;
        }
        return 0;
    }

    /**
     * Получение элементов черного списка с ключем по клиенту
     * @return array
     */
    static function getBL(){
        CModule::IncludeModule('iblock');
        $result = array();
        $elObj = new CIBlockElement;
        $res = $elObj->GetList(array('ID' => 'DESC'), array(
            'IBLOCK_ID' => rrsIblock::getIBlockId('blacklist_ap')),
            false, false, array('ID','PROPERTY_USER_ID','PROPERTY_FARMER_ID')
        );
        if ($res->SelectedRowsCount() > 0) {
            while($ar_fields = $res->Fetch()) {
                $result[$ar_fields['PROPERTY_USER_ID_VALUE']][] = $ar_fields['PROPERTY_FARMER_ID_VALUE'];
            }
        }
        return $result;
    }

    /**
     * Фермеры которые в черном списке покупателя
     * @param $client_id - ID покупателя
     * @return array - массив по ключу из фермеров
     */
    static function getClientFarmersBL($client_id){
        CModule::IncludeModule('iblock');
        $result = array();
        $elObj = new CIBlockElement;
        $res = $elObj->GetList(array('ID' => 'DESC'), array(
            'IBLOCK_ID' => rrsIblock::getIBlockId('blacklist_ap'),
            'PROPERTY_USER_ID' => $client_id),
            false, false, array('ID','PROPERTY_FARMER_ID')
        );
        if ($res->SelectedRowsCount() > 0) {
            while($ar_fields = $res->Fetch()) {
                //делаем массив по ключу, для более быстрого поиска
                $result[$ar_fields['PROPERTY_FARMER_ID_VALUE']] = true;
            }
        }
        return $result;
    }

    /**
     * Получаем фермеров привязанных к партнерам которые добавлены в черным список выбранного клиента
     * @param $client_id - ID покупателя
     * @return array
     */
    static function getClientPartnerFarmersBL($client_id){
        CModule::IncludeModule('iblock');
        $elObj = new CIBlockElement;
        $farmers = array();
        $all_farmers = array();
        $res = $elObj->GetList(array('ID' => 'DESC'), array(
            'IBLOCK_ID' => rrsIblock::getIBlockId('blacklist_partner'),
            'PROPERTY_USER_ID' => $client_id),
            false, false, array('PROPERTY_PARTNER_ID')
        );
        if ($res->SelectedRowsCount() > 0) {
            while($ar_fields = $res->Fetch()) {
                $all_farmers = self::getPartnerFarmers($ar_fields['PROPERTY_PARTNER_ID_VALUE']);
            }
        }
        return $all_farmers;
    }


    /**
     * Получаем список покупателей которые добавили фермера в черным список
     * @param $farmer_id - ID фермера
     * @return array
     */
    static function getClientsByFarmerBL($farmer_id){
        CModule::IncludeModule('iblock');
        $result = array();
        $elObj = new CIBlockElement;
        $res = $elObj->GetList(array('ID' => 'DESC'), array(
            'IBLOCK_ID' => rrsIblock::getIBlockId('blacklist_ap'),
            'PROPERTY_FARMER_ID' => $farmer_id),
            false, false, array('ID','PROPERTY_USER_ID')
        );
        if ($res->SelectedRowsCount() > 0) {
            while($ar_fields = $res->Fetch()) {
                //делаем массив по ключу, для более быстрого поиска
                $result[$ar_fields['PROPERTY_USER_ID_VALUE']] = true;
            }
        }
        return $result;
    }

    /**
     * Получение клиентов которые добавили партнера выбранного фермера в черный список
     * @param $farmer_id
     * @return array
     */
    static function getClientsFromPartnersByFarmersBL($farmer_id){
        $result = array();
        //получаем ID партнера по ID фермера
        $partner_id = farmer::getPartnerIdByFarmer($farmer_id);
        //Получение клиентов которые добавили выбранного организатора в Черный список
        $result = self::getClientByPartnersBL($partner_id);
        /*$agent_id = agent::getAgentByFarmer($farmer_id);
        if(!empty($agent_id)){
            $partner_id = agent::getPartnerByAgent($agent_id);
            $result = self::getClientByPartnersBL($partner_id);
        }else{
            $partner_id = farmer::getPartnerIdByFarmer($farmer_id);
            $result = self::getClientByPartnersBL($partner_id);
        }*/
        return $result;

    }

    /**
     *Добавление фермера в черный список клиента
     *
     * @param $client_id - ID клиента
     * @param $farmer_id - ID фермера
     */
    static function addFarmerToClientBL($client_id,$farmer_id){
        CModule::IncludeModule('iblock');
        global $USER;
        $el = new CIBlockElement;
        $PROP = array();
        $PROP['USER_ID'] = $client_id;
        $PROP['FARMER_ID'] = $farmer_id;
        $PROP['PARTNER_ID'] = $USER->GetID();
        $arAdd = Array(
            "IBLOCK_ID"      => rrsIblock::getIBlockId('blacklist_ap'),
            "PROPERTY_VALUES"=> $PROP,
            "NAME"           => "АП [".$farmer_id."] в черном списке покупателя [".$client_id."]",
            "ACTIVE"         => "Y",            // активен
        );
        if($BL_ELEMENT_ID = $el->Add($arAdd)){
            return $BL_ELEMENT_ID;
        }
        return 0;
    }

    /**
     * Получение поставщиков привязанных к партнерам через агентов
     * @param $partner_id - ID партнера
     */
    public static function getPartnerAgentsFarmers($partner_id){
        $farmers_array = array();
        $farmers = agent::getFarmers($partner_id);
        if((sizeof($farmers)>0)&&(is_array($farmers))){
            for($i=0,$c=sizeof($farmers);$i<$c;$i++){
                $farmers_array[$farmers[$i]] = true;
            }
        }
        return $farmers_array;
    }

    /**
     * Получение поставщиков привязанных напрямую к профилю партнера
     * @param $partner_id
     */
    public static function getPartnerFarmers($partner_id){
        $farmers_array = array();
        CModule::IncludeModule('iblock');
        $elObj = new CIBlockElement;
        $res = $elObj->GetList(array('ID' => 'DESC'), array(
            'IBLOCK_ID' => rrsIblock::getIBlockId('farmer_profile'),
            'PROPERTY_PARTNER_ID' => $partner_id),
            false, false, array('PROPERTY_USER')
        );
        if ($res->SelectedRowsCount() > 0) {
            while($ar_fields = $res->Fetch()) {
                $farmers_array[$ar_fields['PROPERTY_USER_VALUE']] = true;
            }
        }
        return $farmers_array;
    }

    /**
     * Получение клиентов которые добавили выбранного организатора в Черный список
     * @param $partner_id - ID партнера
     * @return array
     */
    public static function getClientByPartnersBL($partner_id){
        CModule::IncludeModule('iblock');
        $elObj = new CIBlockElement;
        $clients = array();
        $res = $elObj->GetList(array('ID' => 'DESC'), array(
            'IBLOCK_ID' => rrsIblock::getIBlockId('blacklist_partner'),
            'PROPERTY_PARTNER_ID' => $partner_id),
            false, false, array('PROPERTY_USER_ID')
        );
        if ($res->SelectedRowsCount() > 0) {
            while($ar_fields = $res->Fetch()) {
                $clients[$ar_fields['PROPERTY_USER_ID_VALUE']] = true;
            }
        }
        return $clients;
    }

}