<?php

/**
 * Класс для работы с сущностью агента (не статичный!)
 */
class agent {

    /**
     * Отвязать поставщика от агента (агент проверяется по привязке к партнеру)
     * @param $farmer_id
     * @param $partner_id
     * @return int
     */
    function dropLinkWithFarmer($farmer_id, $partner_id){
        $result = 0;

        $el_obj = new CIBlockElement;

        //get all agents linked to farmer
        $check_agents_ids = array();
        $res = $el_obj->GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID'         => rrsIblock::getIBlockId('farmer_agent_link'),
                'PROPERTY_USER_ID'  => $farmer_id
            ),
            false,
            false,
            array('ID', 'PROPERTY_AGENT_ID')
        );
        while($data = $res->Fetch()){
            $check_agents_ids[$data['PROPERTY_AGENT_ID_VALUE']] = $data['ID'];
        }

        $noticeList     = notice::getNoticeList();
        $farmerProfile  = farmer::getProfile($farmer_id);

        $url = '/agent/users/linked_users/';

        //check agent connection to current partner
        if(count($check_agents_ids) > 0){
            $res = $el_obj->GetList(
                array('ID' => 'ASC'),
                array(
                    'IBLOCK_ID'             => rrsIblock::getIBlockId('agent_partner_link'),
                    'PROPERTY_PARTNER_ID'   => $partner_id,
                    'PROPERTY_USER_ID'      => array_keys($check_agents_ids)
                ),
                false,
                false,
                array('PROPERTY_USER_ID')
            );
            while($data = $res->Fetch()){
                if(isset($check_agents_ids[$data['PROPERTY_USER_ID_VALUE']])){
                    //delete farmer links to agents, that link with selected farmer and was added by current partner
                    $el_obj->Delete($check_agents_ids[$data['PROPERTY_USER_ID_VALUE']]);

                    //send notices to agent
                    //отправка сообщений агенту поставщика
                    /*$agentProfile = self::getProfile($data['PROPERTY_USER_ID_VALUE']);

                    if (in_array($noticeList['e_l']['ID'], $agentProfile['PROPERTY_NOTICE_VALUE'])) {
                        $arEventFields = array(
                            'LINKED_URL'    => $GLOBALS['host'].$url,
                            'FARMER_ID'     => $farmer_id,
                            'EMAIL'         => $agentProfile['USER']['EMAIL'],
                            'PROFILE_LINK'  => $GLOBALS['host'].'/profile/?uid='.$farmer_id,
                            'COMPANY_NAME'  => $farmerProfile['PROPERTY_FULL_COMPANY_NAME_VALUE']
                        );
                        CEvent::Send('AGENT_UNLINK_FARMER', 's1', $arEventFields);
                    }
                    if (in_array($noticeList['c_l']['ID'], $agentProfile['PROPERTY_NOTICE_VALUE'])) {
                        notice::addNotice($agentProfile['USER']['ID'], 'l', 'Открепление поставщика', $url, '#' . $farmer_id);
                    }
                    if (in_array($noticeList['s_l']['ID'], $agentProfile['PROPERTY_NOTICE_VALUE']) && $agentProfile['PROPERTY_PHONE_VALUE']) {
                        $phone = str_replace(array('+', ' ', '(', ')', '-'), "", $agentProfile['PROPERTY_PHONE_VALUE']);
                        notice::sendNoticeSMS($phone, 'Открепление поставщика: '.$GLOBALS['host'].$url);
                    }*/
                }
            }
            $result = 2;
        }

        return $result;
    }

    /**
     * Отвязать покупателя от агента (агент проверяется по привязке к партнеру)
     * @param $client_id
     * @param $partner_id
     * @return int
     */
    function dropLinkWithClient($client_id, $partner_id){
        $result = 0;

        $el_obj = new CIBlockElement;

        //get all agents linked to farmer
        $check_agents_ids = array();
        $res = $el_obj->GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID'         => rrsIblock::getIBlockId('client_agent_link'),
                'PROPERTY_USER_ID'  => $client_id
            ),
            false,
            false,
            array('ID', 'PROPERTY_AGENT_ID')
        );
        while($data = $res->Fetch()){
            $check_agents_ids[$data['PROPERTY_AGENT_ID_VALUE']] = $data['ID'];
        }

        $noticeList     = notice::getNoticeList();
        $clientProfile  = farmer::getProfile($client_id);

        $url = '/agent/users/linked_users/';

        //check agent connection to current partner
        if(count($check_agents_ids) > 0){
            $res = $el_obj->GetList(
                array('ID' => 'ASC'),
                array(
                    'IBLOCK_ID'             => rrsIblock::getIBlockId('client_agent_partner_link'),
                    'PROPERTY_PARTNER_ID'   => $partner_id,
                    'PROPERTY_USER_ID'      => array_keys($check_agents_ids)
                ),
                false,
                false,
                array('PROPERTY_USER_ID')
            );
            while($data = $res->Fetch()){
                if(isset($check_agents_ids[$data['PROPERTY_USER_ID_VALUE']])){
                    //delete farmer links to agents, that link with selected farmer and was added by current partner
                    $el_obj->Delete($check_agents_ids[$data['PROPERTY_USER_ID_VALUE']]);

                    //send notices to agent
                    /*$agentProfile = self::getProfile($data['PROPERTY_USER_ID_VALUE']);

                    if (in_array($noticeList['e_l']['ID'], $agentProfile['PROPERTY_NOTICE_VALUE'])) {
                        $arEventFields = array(
                            'LINKED_URL'    => $GLOBALS['host'].$url,
                            'FARMER_ID'     => $client_id,
                            'EMAIL'         => $agentProfile['USER']['EMAIL'],
                            'PROFILE_LINK'  => $GLOBALS['host'].'/profile/?uid='.$client_id,
                            'COMPANY_NAME'  => $clientProfile['PROPERTY_FULL_COMPANY_NAME_VALUE']
                        );
                        CEvent::Send('AGENT_UNLINK_CLIENT', 's1', $arEventFields);
                    }
                    if (in_array($noticeList['c_l']['ID'], $agentProfile['PROPERTY_NOTICE_VALUE'])) {
                        notice::addNotice($agentProfile['USER']['ID'], 'l', 'Открепление колхозника', $url, '#' . $client_id);
                    }
                    if (in_array($noticeList['s_l']['ID'], $agentProfile['PROPERTY_NOTICE_VALUE']) && $agentProfile['PROPERTY_PHONE_VALUE']) {
                        $phone = str_replace(array('+', ' ', '(', ')', '-'), "", $agentProfile['PROPERTY_PHONE_VALUE']);
                        notice::sendNoticeSMS($phone, 'Открепление колхозника: '.$GLOBALS['host'].$url);
                    }*/
                }
            }
            $result = 2;
        }

        return $result;
    }

    /**
     * Привязать агента к поставщику (проверяется также привязка агента АП к партнеру)
     * @param $farmer_id
     * @param $agent_id
     * @param $partner_id
     * @return int
     */
    function setLinkWithPartner($farmer_id, $agent_id, $partner_id, $control_id){
        $result = 0;

        $el_obj = new CIBlockElement;

        $farmer_to_agent_link_ib = rrsIblock::getIBlockId('farmer_agent_link');

        //check if partner linked to agent
        $res = $el_obj->GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID'             => rrsIblock::getIBlockId('agent_partner_link'),
                'PROPERTY_PARTNER_ID'   => $partner_id,
                'PROPERTY_USER_ID'      => $agent_id
            ),
            false,
            array('nTopCount' => 1),
            array('ID')
        );

        if($res->SelectedRowsCount() > 0){
            //check if agent not already linked to farmer
            $res = $el_obj->GetList(
                array('ID' => 'ASC'),
                array(
                    'IBLOCK_ID'             => $farmer_to_agent_link_ib,
                    'PROPERTY_USER_ID'      => $farmer_id
                ),
                false,
                false,
                array('ID', 'PROPERTY_AGENT_ID')
            );

            $linked_aready = 0;
            while($data = $res->Fetch()){
                if($data['PROPERTY_AGENT_ID_VALUE'] != $agent_id)
                {
                    //update link between farmer & agent (delete old links)
                    $el_obj->Delete($data['ID']);
                }
                else
                {
                    $linked_aready = $data['ID'];
                }
            }

            if($linked_aready == 0)
            {
                //add new link
                $arFields = array(
                    'IBLOCK_ID' => $farmer_to_agent_link_ib,
                    'NAME' => "Привязка [{$farmer_id}] к организатору [{$agent_id}]",
                    'ACTIVE' => 'Y',
                    'PROPERTY_VALUES' => array(
                        'USER_ID'           => $farmer_id,
                        'AGENT_ID'          => $agent_id,
                        'AGENT_LINK_DATE'   => ConvertTimeStamp(false, 'FULL', 's1'),
                        'AGENT_RIGHTS'      => $control_id
                    )
                );

                $el_obj->Add($arFields);

                $result = 1;

                //send notices to partner
                //уведомление агента поставщика
                /*$url = '/partner/users/linked_users/';

                $noticeList = notice::getNoticeList();
                $farmerProfile = farmer::getProfile($farmer_id);
                $agentProfile = self::getProfile($agent_id);

                if (in_array($noticeList['e_l']['ID'], $agentProfile['PROPERTY_NOTICE_VALUE'])) {
                    $arEventFields = array(
                        'LINKED_URL'    => $GLOBALS['host'].$url,
                        'FARMER_ID'     => $farmer_id,
                        'EMAIL'         => $agentProfile['USER']['EMAIL'],
                        'PROFILE_LINK'  => $GLOBALS['host'].'/profile/?uid='.$farmer_id,
                        'COMPANY_NAME'  => $farmerProfile['PROPERTY_FULL_COMPANY_NAME_VALUE']
                    );
                    CEvent::Send('AGENT_LINK_FARMER', 's1', $arEventFields);
                }
                if (in_array($noticeList['c_l']['ID'], $agentProfile['PROPERTY_NOTICE_VALUE'])) {
                    notice::addNotice($agentProfile['USER']['ID'], 'l', 'Прикрепление поставщика', $url, '#' . $farmer_id);
                }
                if (in_array($noticeList['s_l']['ID'], $agentProfile['PROPERTY_NOTICE_VALUE']) && $agentProfile['PROPERTY_PHONE_VALUE']) {
                    $phone = str_replace(array('+', ' ', '(', ')', '-'), "", $agentProfile['PROPERTY_PHONE_VALUE']);
                    notice::sendNoticeSMS($phone, 'Прикрепление поставщика: '.$GLOBALS['host'].$url);
                }*/
            }
            else
            {//обновление данных привязки (например типа управления)
                $el_obj->SetPropertyValuesEx($linked_aready, $farmer_to_agent_link_ib, array('AGENT_RIGHTS' => $control_id));
                $result = 1;
            }
        }

        return $result;
    }

    /**
     * Привязать агента к покупателю (проверяется также привязка агента АП к покупателю)
     * @param $client_id
     * @param $agent_id
     * @param $partner_id
     * @return int
     */
    function setClientLinkWithPartner($client_id, $agent_id, $partner_id, $control_id){
        $result = 0;

        $el_obj = new CIBlockElement;

        $client_to_agent_link_ib = rrsIblock::getIBlockId('client_agent_link');

        //check if partner linked to agent
        $res = $el_obj->GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID'             => rrsIblock::getIBlockId('client_agent_partner_link'),
                'PROPERTY_PARTNER_ID'   => $partner_id,
                'PROPERTY_USER_ID'      => $agent_id
            ),
            false,
            array('nTopCount' => 1),
            array('ID')
        );

        if($res->SelectedRowsCount() > 0){
            //check if agent not already linked to client
            $res = $el_obj->GetList(
                array('ID' => 'ASC'),
                array(
                    'IBLOCK_ID'             => $client_to_agent_link_ib,
                    'PROPERTY_USER_ID'      => $client_id
                ),
                false,
                false,
                array('ID', 'PROPERTY_AGENT_ID')
            );

            $linked_aready = 0;
            while($data = $res->Fetch()){
                if($data['PROPERTY_AGENT_ID_VALUE'] != $agent_id)
                {
                    //update link between client & agent (delete old links)
                    $el_obj->Delete($data['ID']);
                }
                else
                {
                    $linked_aready = $data['ID'];
                }
            }

            if($linked_aready == 0)
            {
                //add new link
                $arFields = array(
                    'IBLOCK_ID' => $client_to_agent_link_ib,
                    'NAME' => "Привязка покупателя [{$client_id}] к агенту [{$agent_id}]",
                    'ACTIVE' => 'Y',
                    'PROPERTY_VALUES' => array(
                        'USER_ID'           => $client_id,
                        'AGENT_ID'          => $agent_id,
                        'AGENT_LINK_DATE'   => ConvertTimeStamp(false, 'FULL', 's1'),
                        'AGENT_RIGHTS'      => $control_id
                    )
                );

                $el_obj->Add($arFields);

                $result = 1;

                //send notices to agent

                /*$url = '/client_agent/users/linked_users/';

                $noticeList = notice::getNoticeList();
                $clientProfile = client::getProfile($client_id);
                $agentProfile = self::getProfile($agent_id);

                if (in_array($noticeList['e_l']['ID'], $agentProfile['PROPERTY_NOTICE_VALUE'])) {
                    $arEventFields = array(
                        'LINKED_URL'    => $GLOBALS['host'].$url,
                        'FARMER_ID'     => $client_id,
                        'EMAIL'         => $agentProfile['USER']['EMAIL'],
                        'PROFILE_LINK'  => $GLOBALS['host'].'/profile/?uid='.$client_id,
                        'COMPANY_NAME'  => $clientProfile['PROPERTY_FULL_COMPANY_NAME_VALUE']
                    );
                    CEvent::Send('AGENT_LINK_CLIENT', 's1', $arEventFields);
                }
                if (in_array($noticeList['c_l']['ID'], $agentProfile['PROPERTY_NOTICE_VALUE'])) {
                    notice::addNotice($agentProfile['USER']['ID'], 'l', 'Прикрепление покупателя', $url, '#' . $client_id);
                }
                if (in_array($noticeList['s_l']['ID'], $agentProfile['PROPERTY_NOTICE_VALUE']) && $agentProfile['PROPERTY_PHONE_VALUE']) {
                    $phone = str_replace(array('+', ' ', '(', ')', '-'), "", $agentProfile['PROPERTY_PHONE_VALUE']);
                    notice::sendNoticeSMS($phone, 'Прикрепление покупателя: '.$GLOBALS['host'].$url);
                }*/
            }
            else
            {//обновление данных привязки (например типа управления)
                $el_obj->SetPropertyValuesEx($linked_aready, $client_to_agent_link_ib, array('AGENT_RIGHTS' => $control_id));
                $result = 1;
            }
        }

        return $result;
    }

    /**
     * Получить список агентов АП для партнера (страница вывода списка агентов)
     * @param $partner_id
     * @return array
     */
    function getAgentsOfPartner($partner_id){
        $result = array();

        $el_obj = new CIBlockElement;
        $u_obj  = new CUser;

        $selected_ids   = array();
        $docs_data      = array();

        //get all active links
        $res = $el_obj->GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID'             => rrsIblock::getIBlockId('agent_partner_link'),
                'ACTIVE'                => 'Y',
                'PROPERTY_PARTNER_ID'   => $partner_id
            ),
            false,
            false,
            array('PROPERTY_USER_ID', 'PROPERTY_PARTNER_LINK_DOC', 'PROPERTY_PARTNER_LINK_DOC_NUM', 'PROPERTY_PARTNER_LINK_DOC_DATE')
        );
        while($data = $res->Fetch()){

            $selected_ids[$data['PROPERTY_USER_ID_VALUE']] = true;

            if(isset($data['PROPERTY_PARTNER_LINK_DOC_VALUE'])
                && is_numeric($data['PROPERTY_PARTNER_LINK_DOC_VALUE'])
            ){
                $docs_data[$data['PROPERTY_USER_ID_VALUE']] = array(
                    'LINK_DOC'      => $data['PROPERTY_PARTNER_LINK_DOC_VALUE'],
                    'LINK_DOC_NUM'  => $data['PROPERTY_PARTNER_LINK_DOC_NUM_VALUE'],
                    'LINK_DOC_DATE' => $data['PROPERTY_PARTNER_LINK_DOC_DATE_VALUE']
                );
            }
        }

        if(empty($selected_ids)) {
            return $result;
        }

        // Активные агенты
        $rs = $el_obj->GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID'     => getIBlockID('agent', 'agent_profile'),
                'ACTIVE'        => 'Y',
                'PROPERTY_USER' => array_keys($selected_ids)
            ),
            false,
            false,
            array(
                'PROPERTY_USER', 'PROPERTY_REWARD_PERCENT', 'PROPERTY_PERCENT_TRANSPORTATION',
            )
        );

        while ($arRow = $rs->Fetch()) {

            $result[$arRow['PROPERTY_USER_VALUE']] = array(
                'ID'                        => $arRow['PROPERTY_USER_VALUE'],
                'NAME'                      => '',
                'EMAIL'                     => '',
                'ACTIVE'                    => 'Y',
                'LINK_DOC'                  => 'n',
                'REWARD_PERCENT'            => $arRow['PROPERTY_REWARD_PERCENT_VALUE'],
                'PERCENT_TRANSPORTATION'    => $arRow['PROPERTY_PERCENT_TRANSPORTATION_VALUE'],
            );
        }


        //get all inactive link
        $res = $el_obj->GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID'             => rrsIblock::getIBlockId('agent_profile'),
                'ACTIVE'                => 'Y',
                'PROPERTY_PARTNER_ID'   => $partner_id,
                '!PROPERTY_USER'        => array_keys($selected_ids)
            ),
            false,
            false,
            array(
                'PROPERTY_USER', 'PROPERTY_REWARD_PERCENT',
            )
        );
        while($data = $res->Fetch()){
            $result[$data['PROPERTY_USER_VALUE']] = array(
                'ID'                => $data['PROPERTY_USER_VALUE'],
                'NAME'              => '',
                'EMAIL'             => '',
                'ACTIVE'            => 'N',
                'REWARD_PERCENT'    => $arRow['PROPERTY_REWARD_PERCENT_VALUE'],
            );
            $selected_ids[$data['PROPERTY_USER_VALUE']] = true;
        }

        //get users data
        if(count($selected_ids) > 0){
            $res = $u_obj->GetList(
                ($by = 'ID'),
                ($order = 'ASC'),
                array('ID' => implode(' | ', array_keys($selected_ids))),
                array('FIELDS' => array('ID', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'EMAIL'))
            );
            while($data = $res->Fetch()){
                if(isset($result[$data['ID']])){
                    $result[$data['ID']]['EMAIL'] = $data['EMAIL'];
                    $result[$data['ID']]['NAME'] = trim($data['LAST_NAME'].' '.$data['NAME'].' '.$data['SECOND_NAME']);

                    //добавление данных договора
                    if(isset($docs_data[$data['ID']]['LINK_DOC'])){
                        $result[$data['ID']]['LINK_DOC']        = $docs_data[$data['ID']]['LINK_DOC'];
                        $result[$data['ID']]['LINK_DOC_NUM']    = $docs_data[$data['ID']]['LINK_DOC_NUM'];
                        $result[$data['ID']]['LINK_DOC_DATE']   = $docs_data[$data['ID']]['LINK_DOC_DATE'];
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Получить список агентов покупателей для партнера (страница вывода списка агентов)
     * @param $partner_id
     * @return array
     */
    function getClientAgentsOfPartner($partner_id){
        $result = array();

        $el_obj = new CIBlockElement;
        $u_obj  = new CUser;

        $selected_ids = array();
        $docs_data      = array();

        //get all active links
        $res = $el_obj->GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID'             => rrsIblock::getIBlockId('client_agent_partner_link'),
                'ACTIVE'                => 'Y',
                'PROPERTY_PARTNER_ID'   => $partner_id
            ),
            false,
            false,
            array('PROPERTY_USER_ID', 'PROPERTY_PARTNER_LINK_DOC', 'PROPERTY_PARTNER_LINK_DOC_NUM', 'PROPERTY_PARTNER_LINK_DOC_DATE')
        );
        while($data = $res->Fetch()){

            $selected_ids[$data['PROPERTY_USER_ID_VALUE']] = true;

            if(isset($data['PROPERTY_PARTNER_LINK_DOC_VALUE'])
                && is_numeric($data['PROPERTY_PARTNER_LINK_DOC_VALUE'])
            ){
                $docs_data[$data['PROPERTY_USER_ID_VALUE']] = array(
                    'LINK_DOC'      => $data['PROPERTY_PARTNER_LINK_DOC_VALUE'],
                    'LINK_DOC_NUM'  => $data['PROPERTY_PARTNER_LINK_DOC_NUM_VALUE'],
                    'LINK_DOC_DATE' => $data['PROPERTY_PARTNER_LINK_DOC_DATE_VALUE']
                );
            }
        }

        if(empty($selected_ids)) {
            return $result;
        }

        // Активные агенты покупателя
        $rs = $el_obj->GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID'     => rrsIblock::getIBlockId('client_agent_profile'),
                'ACTIVE'        => 'Y',
                'PROPERTY_USER' => array_keys($selected_ids)
            ),
            false,
            false,
            array(
                'PROPERTY_USER', 'PROPERTY_REWARD_PERCENT',
            )
        );

        while ($arRow = $rs->Fetch()) {

            $result[$arRow['PROPERTY_USER_VALUE']] = array(
                'ID'                => $arRow['PROPERTY_USER_VALUE'],
                'NAME'              => '',
                'EMAIL'             => '',
                'ACTIVE'            => 'Y',
                'LINK_DOC'          => 'n',
                'REWARD_PERCENT'    => $arRow['PROPERTY_REWARD_PERCENT_VALUE'],
            );
        }

        //get all inactive link
        $res = $el_obj->GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID'             => rrsIblock::getIBlockId('client_agent_profile'),
                'ACTIVE'                => 'Y',
                'PROPERTY_PARTNER_ID'   => $partner_id,
                '!PROPERTY_USER'        => array_keys($selected_ids)
            ),
            false,
            false,
            array(
                'PROPERTY_USER', 'PROPERTY_REWARD_PERCENT',
            )
        );
        while($data = $res->Fetch()){
            $result[$data['PROPERTY_USER_VALUE']] = array(
                'ID'        => $data['PROPERTY_USER_VALUE'],
                'NAME'      => '',
                'EMAIL'     => '',
                'ACTIVE'    => 'N'
            );
            $selected_ids[$data['PROPERTY_USER_VALUE']] = true;
        }

        //get users data
        if(count($selected_ids) > 0){
            $res = $u_obj->GetList(
                ($by = 'ID'),
                ($order = 'ASC'),
                array('ID' => implode(' | ', array_keys($selected_ids))),
                array('FIELDS' => array('ID', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'EMAIL'))
            );
            while($data = $res->Fetch()){
                if(isset($result[$data['ID']])){
                    $result[$data['ID']]['EMAIL'] = $data['EMAIL'];
                    $result[$data['ID']]['NAME'] = trim($data['LAST_NAME'].' '.$data['NAME'].' '.$data['SECOND_NAME']);

                    //добавление данных договора
                    if(isset($docs_data[$data['ID']]['LINK_DOC'])){
                        $result[$data['ID']]['LINK_DOC']        = $docs_data[$data['ID']]['LINK_DOC'];
                        $result[$data['ID']]['LINK_DOC_NUM']    = $docs_data[$data['ID']]['LINK_DOC_NUM'];
                        $result[$data['ID']]['LINK_DOC_DATE']   = $docs_data[$data['ID']]['LINK_DOC_DATE'];
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Удаление агента поставщика (удаление всех связей и пользователя из БД)
     * @param $agent_id
     * @param $partner_id
     * @return bool
     */
    function deleteAgent($agent_id, $partner_id)
    {
        $result = false;

        if(!is_numeric($agent_id) || !is_numeric($partner_id)){
            $result = false;
        }
        else{
            $el_obj = new CIBlockElement;

            $delete_partner_link_id = 0;
            $delete_partner_profile_id = 0;

            //check if agent linked to selected partner
            $res = $el_obj->GetList(
                array('ID' => 'ASC'),
                array(
                    'IBLOCK_ID'             => rrsIblock::getIBlockId('agent_partner_link'),
                    'PROPERTY_USER_ID'      => $agent_id,
                    'PROPERTY_PARTNER_ID'   => $partner_id
                ),
                false,
                array('nTopCount' => 1),
                array('ID')
            );
            if($data = $res->Fetch()){
                $delete_partner_link_id = $data['ID'];
            }

            //check if agent linked to selected partner by profile
            $res = $el_obj->GetList(
                array('ID' => 'ASC'),
                array(
                    'IBLOCK_ID'             => rrsIblock::getIBlockId('agent_profile'),
                    'PROPERTY_USER'         => $agent_id
                ),
                false,
                array('nTopCount' => 1),
                array('ID')
            );
            if($data = $res->Fetch())
            {
                $delete_partner_profile_id = $data['ID'];
            }

            if($delete_partner_profile_id + $delete_partner_link_id > 0){
                //partner is linked to agent -> delete agent data

                //unlink farmers
                $res = $el_obj->GetList(
                    array('ID' => 'ASC'),
                    array(
                        'IBLOCK_ID' => rrsIblock::getIBlockId('farmer_agent_link'),
                        'PROPERTY_AGENT_ID' => $agent_id
                    ),
                    false,
                    array('nTopCount' => 1),
                    array('ID')
                );
                while($data = $res->Fetch())
                {
                    $el_obj->Delete($data['ID']);
                }

                if($delete_partner_link_id > 0)
                {
                    $el_obj->Delete($delete_partner_link_id);
                }

                if($delete_partner_profile_id > 0)
                {
                    $el_obj->Delete($delete_partner_profile_id);
                }

                $user_obj = new CUser();
                $user_obj->Delete($agent_id);

                $result = true;
            }
        }

        return $result;
    }

    /**
     * Удаление агента покупателя (удаление всех связей и пользователя из БД)
     * @param $agent_id
     * @param $partner_id
     * @return bool
     */
    function deleteClientAgent($agent_id, $partner_id)
    {
        $result = false;

        if(!is_numeric($agent_id) || !is_numeric($partner_id)){
            $result = false;
        }
        else{
            $el_obj = new CIBlockElement;

            $delete_partner_link_id = 0;
            $delete_partner_profile_id = 0;

            //check if agent linked to selected partner
            $res = $el_obj->GetList(
                array('ID' => 'ASC'),
                array(
                    'IBLOCK_ID'             => rrsIblock::getIBlockId('client_agent_partner_link'),
                    'PROPERTY_USER_ID'      => $agent_id,
                    'PROPERTY_PARTNER_ID'   => $partner_id
                ),
                false,
                array('nTopCount' => 1),
                array('ID')
            );
            if($data = $res->Fetch()){
                $delete_partner_link_id = $data['ID'];
            }

            //check if agent linked to selected partner by profile
            $res = $el_obj->GetList(
                array('ID' => 'ASC'),
                array(
                    'IBLOCK_ID'             => rrsIblock::getIBlockId('client_agent_profile'),
                    'PROPERTY_USER'         => $agent_id
                ),
                false,
                array('nTopCount' => 1),
                array('ID')
            );
            if($data = $res->Fetch())
            {
                $delete_partner_profile_id = $data['ID'];
            }

            if($delete_partner_profile_id + $delete_partner_link_id > 0){
                //partner is linked to agent -> delete agent data

                //unlink clients
                $res = $el_obj->GetList(
                    array('ID' => 'ASC'),
                    array(
                        'IBLOCK_ID' => rrsIblock::getIBlockId('client_agent_link'),
                        'PROPERTY_AGENT_ID' => $agent_id
                    ),
                    false,
                    array('nTopCount' => 1),
                    array('ID')
                );
                while($data = $res->Fetch())
                {
                    $el_obj->Delete($data['ID']);
                }

                if($delete_partner_link_id > 0)
                {
                    $el_obj->Delete($delete_partner_link_id);
                }

                if($delete_partner_profile_id > 0)
                {
                    $el_obj->Delete($delete_partner_profile_id);
                }

                $user_obj = new CUser();
                $user_obj->Delete($agent_id);

                $result = true;
            }
        }

        return $result;
    }


    /**
     * Получение регионов которые участвуют в запросах агента
     * @param $agent_id - ID агента поставщика
     */
    static function getAgentRegionsByLeads($agent_id){
        $arAgentRegions = array();
        $farmers = self::getFarmersForSelect($agent_id);
        $arFilter = array(
            'UF_FARMER_ID' => array_keys($farmers)
        );
        $arLeads = lead::getLeadList($arFilter);
        $farmers_whs = array();
        foreach ($arLeads as $item){
            if(!empty($item['UF_FARMER_WH_ID'])){
                $farmers_whs[$item['UF_FARMER_WH_ID']] = 1;
            }
        }
        //получаем все регионы
        $arRegions = array();
        $res = CIBlockElement::GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('regions'),
                'ACTIVE' => 'Y',
            ),
            false,
            false,
            array('ID', 'NAME')
        );
        while ($ob = $res->Fetch()) {
            $arRegions[$ob['ID']] = $ob;
        }
        //получаем регионы из складов
        $res = CIBlockElement::GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('farmer_warehouse'),
                'ACTIVE' => 'Y',
                'ID' => array_keys($farmers_whs),
            ),
            false,
            false,
            array('ID','PROPERTY_REGION')
        );
        while ($ob = $res->Fetch()) {
            if(array_key_exists($ob['PROPERTY_REGION_VALUE'],$arRegions)){
                $arAgentRegions[$ob['PROPERTY_REGION_VALUE']] = $arRegions[$ob['PROPERTY_REGION_VALUE']];
            }

        }
        return $arAgentRegions;
    }


    /**
     * Возврашает массив ID поставщиков для выбранного агента
     * @param int $agent_id
     * @return array
     */
    function getFarmers($agent_id, $checked_farmers_ids = array())
    {
        $result = array();

        if(is_numeric($agent_id))
        {
            $filerArr = array(
                'IBLOCK_ID'         => rrsIblock::getIBlockId('farmer_agent_link'),
                'ACTIVE'            => 'Y',
                'PROPERTY_AGENT_ID' => $agent_id
            );

            if(is_array($checked_farmers_ids) && count($checked_farmers_ids) > 0)
            {
                $filerArr['PROPERTY_USER_ID'] = $checked_farmers_ids;
            }

            $el_obj = new CIBlockElement();
            $res = $el_obj->GetList(
                array('ID' => 'ASC'),
                $filerArr,
                false,
                false,
                array('PROPERTY_USER_ID')
            );
            while($data = $res->Fetch())
            {
                $result[] = $data['PROPERTY_USER_ID_VALUE'];
            }
        }

        return $result;
    }

    /**
     * Возврашает массив ID покупателей для выбранного агента
     * @param int $agent_id
     * @return array
     */
    function getClients($agent_id, $checked_clients_ids = array())
    {
        $result = array();

        if(is_numeric($agent_id))
        {
            $filerArr = array(
                'IBLOCK_ID'         => rrsIblock::getIBlockId('client_agent_link'),
                'ACTIVE'            => 'Y',
                'PROPERTY_AGENT_ID' => $agent_id
            );

            if(is_array($checked_clients_ids) && count($checked_clients_ids) > 0)
            {
                $filerArr['PROPERTY_USER_ID'] = $checked_clients_ids;
            }

            $el_obj = new CIBlockElement();
            $res = $el_obj->GetList(
                array('ID' => 'ASC'),
                $filerArr,
                false,
                false,
                array('PROPERTY_USER_ID')
            );
            while($data = $res->Fetch())
            {
                $result[] = $data['PROPERTY_USER_ID_VALUE'];
            }
        }

        return $result;
    }

    /**
     * Возврашает массив ID агентов и их настроек контроля для поставщиков
     * @param array $agents_by_farmers - массив, где ключ - id поставщика, значение - id агента
     * @return array
     */
    function getFarmersControlsByFarmers($agents_by_farmers, $checked_farmers_ids = array())
    {
        $result = array();

        if(is_array($agents_by_farmers) && count($agents_by_farmers) > 0)
        {
            $filerArr = array(
                'IBLOCK_ID'         => rrsIblock::getIBlockId('farmer_agent_link'),
                'ACTIVE'            => 'Y',
                'PROPERTY_AGENT_ID' => $agents_by_farmers
            );
            if(is_array($checked_farmers_ids) && count($checked_farmers_ids) > 0)
            {
                $filerArr['PROPERTY_USER_ID'] = $checked_farmers_ids;
            }

            $el_obj = new CIBlockElement();
            $res = $el_obj->GetList(
                array('ID' => 'ASC'),
                $filerArr,
                false,
                false,
                array('PROPERTY_USER_ID')
            );
            while($data = $res->Fetch())
            {
                $result[] = $data['PROPERTY_USER_ID_VALUE'];
            }
        }

        return $result;
    }

    /**
     * Возврашает массив ID поставщиков для выбранного агента (с данными поставщика)
     * @param int $agent_id - id агента
     * @param array $checked_farmers_ids - массив ID поставщиков
     * @param boolean $get_demo - признак того возвращать ли св-во UF_DEMO пользователя
     * @param boolean $get_first_authorize - признак того возвращать ли св-во UF_FIRST_LOGIN пользователя
     * @return array
     */
    function getFarmersForSelect($agent_id, $checked_farmers_ids = array(), $get_demo = false, $get_first_authorize = false)
    {
        $result = array();

        if(is_numeric($agent_id)){
            $user_ids = array();
            $filerArr = array(
                'IBLOCK_ID'         => rrsIblock::getIBlockId('farmer_agent_link'),
                'ACTIVE'            => 'Y',
                'PROPERTY_AGENT_ID' => $agent_id
            );

            if(is_array($checked_farmers_ids) && count($checked_farmers_ids) > 0){
                $filerArr['PROPERTY_USER_ID'] = $checked_farmers_ids;
            }

            $el_obj = new CIBlockElement();
            $res = $el_obj->GetList(
                array('ID' => 'DESC'),
                $filerArr,
                false,
                false,
                array('PROPERTY_USER_ID', 'PROPERTY_FARMER_NICKNAME')
            );
            while($data = $res->Fetch()){
                $user_ids[$data['PROPERTY_USER_ID_VALUE']] = trim($data['PROPERTY_FARMER_NICKNAME_VALUE']);
            }

            if(count($user_ids) > 0){
                $u_obj = new CUser;
                $arSelect = array(
                    'FIELDS' => array('ID', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'EMAIL')
                );
                if($get_demo){
                    $arSelect['SELECT'] = array('UF_DEMO');
                }
                if($get_first_authorize){
                    $arSelect['SELECT'] = array('UF_FIRST_LOGIN');
                }
                $res = $u_obj->GetList(
                    ($by = 'email'),
                    ($order = 'asc'),
                    array(
                        'ID'        => implode(' | ', array_keys($user_ids)),
                        'ACTIVE'    => 'Y'
                    ),
                    $arSelect
                );
                while($data = $res->Fetch()){
                    $result[$data['ID']] = array(
                        'NAME'  => trim($data['LAST_NAME'].' '.$data['NAME'].' '.$data['SECOND_NAME']),
                        'EMAIL' => $data['EMAIL'],
                        'NICK'  => ''
                    );
                    if(isset($user_ids[$data['ID']]) && $user_ids[$data['ID']] != ''){
                        $result[$data['ID']]['NICK'] = $user_ids[$data['ID']];
                    }
                    if($get_demo){
                        $result[$data['ID']]['UF_DEMO'] = $data['UF_DEMO'];
                    }
                    if($get_first_authorize){
                        $result[$data['ID']]['UF_FIRST_LOGIN'] = $data['UF_FIRST_LOGIN'];
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Проверка сохранения фильтра на странице пар агентов
     * @return array массив, где у ключа 'NEED_UPD' значение либо true либо false,
     * у ключа 'URL_UPD' значение адреса переадресации
     */
    public static function filterAgentPairCheck(){
        $result = array(
            'NEED_UPD'  => false,
            'URL_UPD'   => ''
        );
        if(isset($_GET['id'])
            && $_GET['id'] > 0){
            return $result;
        }

        $page_need_update = false;
        $new_url_params = array();
        $base_url = '/partner/pair/';

        if(((isset($_GET['farmer_warehouse_id']))&&(!empty($_GET['farmer_warehouse_id'])))
            ||((isset($_GET['client_warehouse_id']))&&(!empty($_GET['client_warehouse_id'])))
            ||((isset($_GET['culture_id']))&&(!empty($_GET['culture_id'])))
            ||((isset($_GET['region_id']))&&(!empty($_GET['region_id'])))
            ||((isset($_GET['farmer_id']))&&(!empty($_GET['farmer_id'])))
            ||((isset($_GET['client_id']))&&(!empty($_GET['client_id'])))
            || (
                isset($_POST['send_ajax'])
                && $_POST['send_ajax'] == 'y'
            )
        ){
            return $result;
        }

        $cookie_value = '';
        //проверка куки поставщика
        $cookie_name = 'deals_filter_farmer_id';
        if(isset($_COOKIE[trim($cookie_name)])){
            $cookie_value = $_COOKIE[$cookie_name];
            if((!isset($_GET['farmer_id']) || $_GET['farmer_id'] == '' || $_GET['farmer_id'] == '0')
                && $cookie_value != 0 && $cookie_value != ''
            ){
                $result['NEED_UPD'] = true;
                $new_url_params[] = 'farmer_id=' . $cookie_value;
            }
        }
        //проверка куки покупателя
        $cookie_name = 'deals_filter_client_id';
        if(isset($_COOKIE[trim($cookie_name)])){
            $cookie_value = $_COOKIE[$cookie_name];
            if((!isset($_GET['client_id']) || $_GET['client_id'] == '' || $_GET['client_id'] == '0')
                && $cookie_value != 0 && $cookie_value != ''
            ){
                $result['NEED_UPD'] = true;
                $new_url_params[] = 'client_id=' . $cookie_value;
            }
        }

        //проверка куки склада поставщика
        $cookie_name = 'deals_filter_farmer_warehouse_id';
        if(isset($_COOKIE[$cookie_name])){
            $cookie_value = $_COOKIE[$cookie_name];
            if((!isset($_GET['farmer_warehouse_id']) || $_GET['farmer_warehouse_id'] == '' || $_GET['farmer_warehouse_id'] == '0')
                && $cookie_value != 0 && $cookie_value != ''
            ){
                $result['NEED_UPD'] = true;
                $new_url_params[] = 'farmer_warehouse_id=' . $cookie_value;
            }
        }

        //проверка куки склада покупателя
        $cookie_name = 'deals_filter_client_warehouse_id';
        if(isset($_COOKIE[$cookie_name])){
            $cookie_value = $_COOKIE[$cookie_name];
            if((!isset($_GET['client_warehouse_id']) || $_GET['client_warehouse_id'] == '' || $_GET['client_warehouse_id'] == '0')
                && $cookie_value != 0 && $cookie_value != ''
            ){
                $result['NEED_UPD'] = true;
                $new_url_params[] = 'client_warehouse_id=' . $cookie_value;
            }
        }


        //проверка куки культуры
        $cookie_name = 'deals_filter_culture_id';
        if(isset($_COOKIE[trim($cookie_name)])){
            $cookie_value = $_COOKIE[$cookie_name];
            if((!isset($_GET['culture_id']) || $_GET['culture_id'] == '' || $_GET['culture_id'] == '0')
                && $cookie_value != 0 && $cookie_value != ''
            ){
                $result['NEED_UPD'] = true;
                $new_url_params[] = 'culture_id=' . $cookie_value;
            }
        }
        //проверка куки региона
        $cookie_name = 'deals_filter_region_id';
        if(isset($_COOKIE[trim($cookie_name)])){
            $cookie_value = $_COOKIE[$cookie_name];
            if((!isset($_GET['region_id']) || $_GET['region_id'] == '' || $_GET['region_id'] == '0')
                && $cookie_value != 0 && $cookie_value != ''
            ){
                $result['NEED_UPD'] = true;
                $new_url_params[] = 'region_id=' . $cookie_value;
            }
        }


        if(($result['NEED_UPD'])&&(!empty($base_url))){
            $result['URL_UPD'] = $base_url . (count($new_url_params) > 0 ? '?' . implode('&', $new_url_params) : '');
        }
        return $result;
    }


    /**
     * Получение регионов, которые встречаются в парах агента (всех его покупателей|поставщиков)
     * @param $agent_id - ID агента
     * @param string $agent_type - тип агента:
     *      CLIENT - агент клиента
     *      FARMER - агент поставщика
     * @return array
     */
    function getAgentRegionsByPAIR($agent_id,$agent_type = 'CLIENT'){
        $result = array();
        $arRegions = array();
        $users_whs = array();
        $agentObj = new agent();
        $arUsRegions = array();
        $allCount = 0;
        if($agent_type == 'FARMER'){
            $userIds = $this->getFarmers($agent_id);
        }else{
            $userIds = $this->getClients($agent_id);
        }
        ;
        $el_obj = new CIBlockElement();
        $res = $el_obj->GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('deals_deals'),
                'PROPERTY_'.$agent_type => $userIds,
            ),
            false,
            false,
            array(
                'ID', 'PROPERTY_'.$agent_type.'_WAREHOUSE'
            )
        );
        while($data = $res->Fetch()){
            $users_whs[$data['PROPERTY_'.$agent_type.'_WAREHOUSE_VALUE']][] = $data['PROPERTY_'.$agent_type.'_WAREHOUSE_VALUE'];
            $allCount++;
        }

        foreach ($users_whs as $k=>$wh){
            $users_whs[$k] = count($wh);
        }

        //получаем все регионы
        $arRegions = array();
        $res = CIBlockElement::GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('regions'),
                'ACTIVE' => 'Y',
            ),
            false,
            false,
            array('ID', 'NAME')
        );
        while ($ob = $res->Fetch()) {
            $arRegions[$ob['ID']] = $ob;
        }

        $ib_code = 'client_warehouse';
        if($agent_type == 'FARMER'){
            $ib_code = 'farmer_warehouse';
        }


        //получаем регионы из складов
        $res = CIBlockElement::GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId($ib_code),
                'ACTIVE' => 'Y',
                'ID' => array_keys($users_whs),
            ),
            false,
            false,
            array('ID','PROPERTY_REGION')
        );
        while ($ob = $res->Fetch()) {
            if(array_key_exists($ob['PROPERTY_REGION_VALUE'],$arRegions)){
                $cp_count = 0;
                if(isset($users_whs[$ob['ID']])){
                    $cp_count = $users_whs[$ob['ID']];
                }
                if(isset($arUsRegions[$ob['PROPERTY_REGION_VALUE']])){
                    $arUsRegions[$ob['PROPERTY_REGION_VALUE']]['COUNT'] = $arUsRegions[$ob['PROPERTY_REGION_VALUE']]['COUNT']+$cp_count;
                }else{
                    $arUsRegions[$ob['PROPERTY_REGION_VALUE']] = $arRegions[$ob['PROPERTY_REGION_VALUE']];
                    $arUsRegions[$ob['PROPERTY_REGION_VALUE']]['COUNT'] = $cp_count;
                }
            }
            //отдельно кладём принадлежность склада к региону
            $result['REGION_TO_WH'][$ob['PROPERTY_REGION_VALUE']][] = $ob['ID'];
        }

        $result['REGIONS'] = $arUsRegions;
        $result['ALL_COUNT_CP'] = $allCount;

        return $result;

    }

    /**
     * Получение регионов, которые встречаются в парах агента (всех его покупателей|поставщиков)
     * $users_whs - массив складов
     * @param string $agent_type - тип агента:
     *      CLIENT - агент клиента
     *      FARMER - агент поставщика
     * @return array
     */
    function getAgentRegionsByWH($users_whs = array(), $agent_type = 'CLIENT'){
        $result = array();
        $arRegions = array();
        $agentObj = new agent();
        $arUsRegions = array();
        $allCount = 0;

        if((sizeof($users_whs))&&(is_array($users_whs))){
            foreach ($users_whs as $k=>$wh){
                $users_whs[$k] = count($wh);
            }

            //получаем все регионы
            $arRegions = array();
            $res = CIBlockElement::GetList(
                array('ID' => 'ASC'),
                array(
                    'IBLOCK_ID' => rrsIblock::getIBlockId('regions'),
                    'ACTIVE' => 'Y',
                ),
                false,
                false,
                array('ID', 'NAME')
            );
            while ($ob = $res->Fetch()) {
                $arRegions[$ob['ID']] = $ob;
            }

            $ib_code = 'client_warehouse';
            if($agent_type == 'FARMER'){
                $ib_code = 'farmer_warehouse';
            }


            //получаем регионы из складов
            $res = CIBlockElement::GetList(
                array('ID' => 'ASC'),
                array(
                    'IBLOCK_ID' => rrsIblock::getIBlockId($ib_code),
                    'ACTIVE' => 'Y',
                    'ID' => array_keys($users_whs),
                ),
                false,
                false,
                array('ID','PROPERTY_REGION')
            );
            while ($ob = $res->Fetch()) {
                if(array_key_exists($ob['PROPERTY_REGION_VALUE'],$arRegions)){
                    $cp_count = 0;
                    if(isset($users_whs[$ob['ID']])){
                        $cp_count = $users_whs[$ob['ID']];
                    }
                    if(isset($arUsRegions[$ob['PROPERTY_REGION_VALUE']])){
                        $arUsRegions[$ob['PROPERTY_REGION_VALUE']]['COUNT'] = $arUsRegions[$ob['PROPERTY_REGION_VALUE']]['COUNT']+$cp_count;
                    }else{
                        $arUsRegions[$ob['PROPERTY_REGION_VALUE']] = $arRegions[$ob['PROPERTY_REGION_VALUE']];
                        $arUsRegions[$ob['PROPERTY_REGION_VALUE']]['COUNT'] = $cp_count;
                    }
                }
                //отдельно кладём принадлежность склада к региону
                $result['REGION_TO_WH'][$ob['PROPERTY_REGION_VALUE']][] = $ob['ID'];
            }
        }

        $result['REGIONS'] = $arUsRegions;
        $result['ALL_COUNT_CP'] = $allCount;

        return $result;
    }


    /**
     * Возврашает массив ID покупателей для выбранного агента (с данными покупателя)
     * @param int $agent_id - ID агента покупателя
     * @param int $checked_clients_ids - массив ID покупателей, для сужения выборки
     * @param boolean $get_first - признак выборки доп поля UF_FIRST_LOGIN (старая логика - UF_DEMO)
     * @param boolean $get_agent_rights - признак выборки доп поля с правами агента на управление
     * @return array
     */
    function getClientsForSelect($agent_id, $checked_clients_ids = array(), $get_first = false, $get_agent_rights = false, $get_partner_rights = false)
    {
        $result = array();

        if(is_numeric($agent_id)){
            $user_ids = array();
            $filterArr = array(
                'IBLOCK_ID'         => rrsIblock::getIBlockId('client_agent_link'),
                'ACTIVE'            => 'Y',
                'PROPERTY_AGENT_ID' => $agent_id
            );

            if(is_array($checked_clients_ids) && count($checked_clients_ids) > 0){
                $filterArr['PROPERTY_USER_ID'] = $checked_clients_ids;
            }elseif(is_numeric($checked_clients_ids) && $checked_clients_ids > 0){
                $filterArr['PROPERTY_USER_ID'] = array($checked_clients_ids);
            }

            $arSelect = array('PROPERTY_USER_ID', 'PROPERTY_CLIENT_NICKNAME', 'ID');
            if($get_agent_rights){
                $arSelect[] = 'PROPERTY_AGENT_RIGHTS';
            }
            $el_obj = new CIBlockElement();
            $res = $el_obj->GetList(
                array('ID' => 'DESC'),
                $filterArr,
                false,
                false,
                $arSelect
            );
            while($data = $res->Fetch()){
                $user_ids[$data['PROPERTY_USER_ID_VALUE']] = array(
                    'NICK' => trim($data['PROPERTY_CLIENT_NICKNAME_VALUE'])
                );
                if($get_agent_rights){
                    $user_ids[$data['PROPERTY_USER_ID_VALUE']]['AGENT_RIGHTS'] = $data['PROPERTY_AGENT_RIGHTS_ENUM_ID'];
                }
            }

            if(count($user_ids) > 0){
                $u_obj = new CUser;
                $arSelect = array(
                    'FIELDS' => array('ID', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'EMAIL')
                );
                if($get_first){
                    $arSelect['SELECT'] = array('UF_DEMO');
                    $arSelect['SELECT'] = array('UF_FIRST_LOGIN');
                }
                $res = $u_obj->GetList(
                    ($by = 'email'),
                    ($order = 'asc'),
                    array(
                        'ID'        => implode(' | ', array_keys($user_ids)),
                        'ACTIVE'    => 'Y'
                    ),
                    $arSelect
                );
                while($data = $res->Fetch()){
                    $result[$data['ID']] = array(
                        'NAME'  => trim($data['LAST_NAME'].' '.$data['NAME'].' '.$data['SECOND_NAME']),
                        'EMAIL' => $data['EMAIL'],
                        'NICK'  => ''
                    );
                    if(isset($user_ids[$data['ID']]['NICK']) && $user_ids[$data['ID']]['NICK'] != ''){
                        $result[$data['ID']]['NICK'] = $user_ids[$data['ID']]['NICK'];
                    }
                    if($get_first){
                        $result[$data['ID']]['UF_DEMO'] = $data['UF_DEMO'];
                        $result[$data['ID']]['UF_FIRST_LOGIN'] = $data['UF_FIRST_LOGIN'];
                    }
                    if($get_agent_rights){
                        $result[$data['ID']]['AGENT_RIGHTS'] = $user_ids[$data['ID']]['AGENT_RIGHTS'];
                    }
                }

                //получение наличия агентских договоров
                $filterArr = array(
                    'IBLOCK_ID'         => rrsIblock::getIBlockId('client_profile'),
                    'PROPERTY_USER'     => array_keys($user_ids)
                );
                $arSelect = array(
                    'PROPERTY_USER', 'PROPERTY_PARTNER_CONTRACT_DATA', 'PROPERTY_PARTNER_CONTRACT_SET', 'PROPERTY_PARTNER_CONTRACT_FILE', 'PROPERTY_PARTNER_CONTRACT_LAST_CHANGE_DATE'
                );
                $res = $el_obj->GetList(
                    array('ID' => 'DESC'),
                    $filterArr,
                    false,
                    false,
                    $arSelect
                );
                while($data = $res->Fetch()){
                    //проверяем дату контракта, если есть
//                    if(trim($data['PROPERTY_PARTNER_CONTRACT_DATA_VALUE']) != ''){
                        $result[$data['PROPERTY_USER_VALUE']]['CONTRACT_DATE'] = $data['PROPERTY_PARTNER_CONTRACT_DATA_VALUE'];

                        if(intval($data['PROPERTY_PARTNER_CONTRACT_SET_VALUE']) > 0) {
                            $result[$data['PROPERTY_USER_VALUE']]['CONTRACT'] = 1;
                        }

                        if(intval($data['PROPERTY_PARTNER_CONTRACT_FILE_VALUE']) > 0) {
                            $result[$data['PROPERTY_USER_VALUE']]['CONTRACT_FILE'] = $data['PROPERTY_PARTNER_CONTRACT_FILE_VALUE'];
                            $result[$data['PROPERTY_USER_VALUE']]['CONTRACT_LAST_DATE'] = $data['PROPERTY_PARTNER_CONTRACT_LAST_CHANGE_DATE_VALUE'];
                        }
//                    }
                }

                //получение подтверждений для покупателей
                if($get_partner_rights){
                    $res = $el_obj->GetList(
                        array('ID' => 'ASC'),
                        array(
                            'IBLOCK_ID' => rrsIblock::getIBlockId('client_partner_link'),
                            'ACTIVE' => 'Y',
                            'PROPERTY_USER_ID' => array_keys($user_ids)
                        ),
                        false,
                        false,
                        array('PROPERTY_VERIFIED', 'PROPERTY_USER_ID', 'PROPERTY_PARTNER_LINK_DOC')
                    );
                    while($data = $res->Fetch()){
                        if(isset($result[$data['PROPERTY_USER_ID_VALUE']])){
                            if(isset($data['PROPERTY_VERIFIED_ENUM_ID'])
                                && $data['PROPERTY_VERIFIED_ENUM_ID'] == rrsIblock::getPropListKey('client_partner_link', 'VERIFIED', 'yes')
                            ){
                                $result[$data['PROPERTY_USER_ID_VALUE']]['VERIFIED'] = 'Y';
                            }

                            if(isset($data['PROPERTY_PARTNER_LINK_DOC_VALUE'])
                                && $data['PROPERTY_PARTNER_LINK_DOC_VALUE'] != ''
                            ){
                                $result[$data['PROPERTY_USER_ID_VALUE']]['LINK_DOC'] = 'Y';
                            }
                        }
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Получение полной информации профиля Агента поставщика
     * @param  int $user_id идентификатор пользователя
     * @return [] массив с полями профиля
     */
    function getProfile($user_id) {
        CModule::IncludeModule('iblock');

        $res = CIBlockElement::GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('agent_profile'),
                'ACTIVE' => 'Y',
                'PROPERTY_USER' => $user_id
            ),
            false,
            false,
            array(
                'ID',
                'NAME',
                'PROPERTY_PHONE',
                'PROPERTY_NOTICE'
            )
        );
        if ($ob = $res->Fetch()) {
            $result = $ob;
        }

        $result['USER'] = rrsIblock::getUserInfo($user_id);

        return $result;
    }

    /**
     * Проверка заполненности необходимых полей и свойств покупателей (проверка необходимых для регистрации данных)
     * @param  mixed $user_id идентификатор/массив идентификаторов пользователя
     * @return mixed флаг/массив флагов заполненности необходимых полей
     */
    function getClientsRegistrationRights($user_id) {
        $result = array();

        //ставим флаг true по умолчанию
        if(is_array($user_id)){
            foreach($user_id as $cur_id){
                $result[$cur_id] = true;
            }
        }else{
            $result[$user_id] = true;
        }

        //Проверка полей пользователя
        $u_obj = new CUser;
        $res = $u_obj->GetList(
            ($by = 'id'), ($order = 'asc'),
            array('ID' => implode(' | ', array_keys($result))),
            array('FIELDS' => array('ID', 'NAME', 'LAST_NAME'))
        );
        while($data = $res->Fetch()){
            if(trim($data['NAME']) == ''
                || trim($data['LAST_NAME']) == ''
            ){
                $result[$data['ID']] = false;
            }
        }

        //Проверка свойств профиля
        CModule::IncludeModule('iblock');
        $ib_id = rrsIblock::getIBlockId('client_profile');
        $el_obj = new CIBlockElement;
        $res = $el_obj->GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => $ib_id,
                'PROPERTY_USER' => $user_id,
            ),
            false,
            false,
            array('PROPERTY_USER', 'PROPERTY_PHONE', 'PROPERTY_REGION', 'PROPERTY_INN')
        );
        while($data = $res->Fetch()){
            if(trim($data['PROPERTY_PHONE_VALUE']) == ''
                || trim($data['PROPERTY_REGION_VALUE']) == ''
                || trim($data['PROPERTY_INN_VALUE']) == ''
            ){
                $result[$data['PROPERTY_USER_VALUE']] = false;
            }
        }

        if(!is_array($user_id)){
            $result = $result[$user_id];
        }

        return $result;
    }

    /**
     * Проверка заполненности необходимых полей и свойств поставщиков (проверка необходимых для регистрации данных)
     * @param mixed $user_id идентификатор/массив идентификаторов пользователя
     * @return mixed флаг/массив флагов заполненности необходимых полей
     */
    function getFarmersRegistrationRights($user_id) {
        $result = array();

        //ставим флаг true по умолчанию
        if(is_array($user_id)){
            foreach($user_id as $cur_id){
                $result[$cur_id] = true;
            }
        }else{
            $result[$user_id] = true;
        }

        //Проверка полей пользователя
        $u_obj = new CUser;
        $res = $u_obj->GetList(
            ($by = 'id'), ($order = 'asc'),
            array('ID' => implode(' | ', array_keys($result))),
            array('FIELDS' => array('ID', 'NAME', 'LAST_NAME'))
        );
        while($data = $res->Fetch()){
            if(trim($data['NAME']) == ''
                || trim($data['LAST_NAME']) == ''
            ){
                $result[$data['ID']] = false;
            }
        }

        //Проверка свойств профиля
        CModule::IncludeModule('iblock');
        $ib_id = rrsIblock::getIBlockId('farmer_profile');
        $el_obj = new CIBlockElement;
        $res = $el_obj->GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => $ib_id,
                'PROPERTY_USER' => $user_id,
            ),
            false,
            false,
            array('PROPERTY_USER', 'PROPERTY_PHONE', 'PROPERTY_REGION', 'PROPERTY_INN')
        );
        while($data = $res->Fetch()){
            if(trim($data['PROPERTY_PHONE_VALUE']) == ''
                || trim($data['PROPERTY_REGION_VALUE']) == ''
                || trim($data['PROPERTY_INN_VALUE']) == ''
            ){
                $result[$data['PROPERTY_USER_VALUE']] = false;
            }
        }

        if(!is_array($user_id)){
            $result = $result[$user_id];
        }

        return $result;
    }

    /**
     * Получение полной информации профиля Агента покупателя
     * @param  int $user_id идентификатор пользователя
     * @return [] массив с полями профиля
     */
    function getClientAgentProfile($user_id) {
        CModule::IncludeModule('iblock');

        $res = CIBlockElement::GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('client_agent_profile'),
                'ACTIVE' => 'Y',
                'PROPERTY_USER' => $user_id
            ),
            false,
            false,
            array(
                'ID',
                'NAME',
                'PROPERTY_PHONE',
                'PROPERTY_NOTICE',
                'PROPERTY_REWARD_PERCENT',
            )
        );
        if ($ob = $res->Fetch()) {
            $result = $ob;
        }

        $result['USER'] = rrsIblock::getUserInfo($user_id);

        return $result;
    }

    /**
     * Получение агентов привязанных к указанным поставщикам
     * также получение настроек уведомлений агентов
     *
     * @param mixed $farmers_id идентификатор поставщика или массив индентификаторов
     * @return [] массив, где ключи ID фермера, а значения - id связанного агента
     */
    function getAgentsByFarmers($farmers_ids) {
        CModule::IncludeModule('iblock');

        $result = array();

        $work_farmer_arr = array();
        if(is_array($farmers_ids))
            $work_farmer_arr = $farmers_ids;
        elseif(is_numeric($farmers_ids))
            $work_farmer_arr[] = $farmers_ids;
        else
            $work_farmer_arr[] = 0;

        $link_farmer_to_agent_arr = array();

        $res = CIBlockElement::GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('farmer_agent_link'),
                'ACTIVE' => 'Y',
                'PROPERTY_USER_ID' => $work_farmer_arr
            ),
            false,
            false,
            array(
                'PROPERTY_USER_ID',
                'PROPERTY_AGENT_ID'
            )
        );
        while($ob = $res->Fetch()) {
            $link_farmer_to_agent_arr[$ob['PROPERTY_AGENT_ID_VALUE']] = $ob['PROPERTY_USER_ID_VALUE'];
        }

        //получить настройки уведомлений агентов и данные профилей
        if(count($link_farmer_to_agent_arr) > 0){
            $res = CIBlockElement::GetList(
                array('ID' => 'ASC'),
                array(
                    'IBLOCK_ID' => rrsIblock::getIBlockId('agent_profile'),
                    'ACTIVE' => 'Y',
                    'PROPERTY_USER_ID' => array_keys($link_farmer_to_agent_arr)
                ),
                false,
                false,
                array(
                    'PROPERTY_USER',
                    'PROPERTY_NOTICE',
                    'PROPERTY_PHONE',
                    'PROPERTY_REWARD_PERCENT',
                    'PROPERTY_PERCENT_TRANSPORTATION',
                )
            );
            while($ob = $res->Fetch()){
                if(isset($link_farmer_to_agent_arr[$ob['PROPERTY_USER_VALUE']])){

                    $result[$link_farmer_to_agent_arr[$ob['PROPERTY_USER_VALUE']]] = array(
                        'ID'                        => $ob['PROPERTY_USER_VALUE'],
                        'PROPERTY_NOTICE_VALUE'     => $ob['PROPERTY_NOTICE_VALUE'],
                        'PROPERTY_PHONE_VALUE'      => $ob['PROPERTY_PHONE_VALUE'],
                        'EMAIL'                     => '',
                        'REWARD_PERCENT'            => $ob['PROPERTY_REWARD_PERCENT_VALUE'],
                        'PERCENT_TRANSPORTATION'    => $ob['PROPERTY_PERCENT_TRANSPORTATION_VALUE'],
                    );
                }
            }

            $res = CUser::GetList(
                ($by = 'id'),
                ($order = 'asc'),
                array('ID' => implode(' | ', array_keys($link_farmer_to_agent_arr))),
                array(
                    'FIELDS' => array( 'ID', 'EMAIL' )
                )
            );
            while($ob = $res->Fetch())
            {
                if(isset($link_farmer_to_agent_arr[$ob['ID']])
                    && isset($result[$link_farmer_to_agent_arr[$ob['ID']]])
                ){
                    $result[$link_farmer_to_agent_arr[$ob['ID']]]['EMAIL'] = $ob['EMAIL'];
                }
            }
        }

        return $result;
    }

    /**
     * Добавление поставщика агентом
     *
     * @param int $agent_id идентификатор агента
     * @param int $new_login логин поставщика
     * @param int $new_email email поставщика
     * @param int $nds_value поставщик работает с НДС/без НДС (ID значения свойства)
     * @param int $nick прозвище, данное поставщику агентом для отображения в списках агента
     * @param int $phone телефон (при добавлении с помощью телефона)
     * @param array $additional_data дополнительные данные (включая полученные от контр-фокуса)
     * @return int ID добавленного поставщика
     */
    function addFarmerByAgent($agent_id, $new_login, $new_email, $nds_value, $nick = '', $phone = '', $additional_data = array())
    {
        $result = 0;

        $el_obj = new CIBlockElement;
        $u_obj  = new CUser;
        $gr_obj = new CGroup;

        //получение групп пользователей
        $groups_arr = array();
        $res = $gr_obj->GetList(
            ($by    = 'id'),
            ($order = 'desc'),
            array(
                'STRING_ID' => 'farmer'
            )
        );
        if($ob = $res->Fetch())
        {
            $groups_arr[] = $ob['ID'];
        }

        $new_pass = md5('agroagent_' . time());

        //добавление пользователя
        $UID = $u_obj->Add(array(
            'LOGIN'             => $new_email,
            'EMAIL'             => $new_email,
            'PASSWORD'          => $new_pass,
            'CONFIRM_PASSWORD'  => $new_pass,
            'GROUP_ID'          => $groups_arr,
            'ACTIVE'            => 'Y',
            'UF_DEMO'           => 'Y',
            'UF_FIRST_PHONE'    => 1,
            'UF_AGENT_ADDED'    => 1,
            'UF_FIRST_LOGIN'    => 1,
            'NAME' => (isset($additional_data['FIELDS']['NAME']) ? $additional_data['FIELDS']['NAME'] : ''),
            'LAST_NAME' => (isset($additional_data['FIELDS']['LAST_NAME']) ? $additional_data['FIELDS']['LAST_NAME'] : ''),
            'SECOND_NAME' => (isset($additional_data['FIELDS']['SECOND_NAME']) ? $additional_data['FIELDS']['SECOND_NAME'] : ''),
        ));

        if(intval($UID) > 0)
        {
            //Отправляем инфо админу
            $arSendFields = [];
            $user_data = 'Пользователь роли "поставщик" был зарегистрирован организатором ' . date('Y.m.d H:i') . '<br/><br/>'; //данные для рассылки
            $res = CUser::GetList(
                ($by = 'id'), ($order = 'asc'),
                array(
                    'GROUPS_ID' => 1,
                    'ACTIVE' => 'Y'
                ),
                array('SELECT' => array('EMAIL', 'NAME'))
            );
            $arSendFields['USER_DATA'] = $user_data;
            if(!checkEmailFromPhone($new_email)){
                $arSendFields['USER_DATA'] .= 'Email: ' . $new_email . '<br/>';
            }
            if(!empty($phone)){
                $arSendFields['USER_DATA'] .= 'Телефон: ' . $phone . '<br/>';
            }
            $arSendFields['USER_DATA'] .= '<a href="' . $GLOBALS['host'] . '/bitrix/admin/user_edit.php?lang=ru&ID=' . $UID . '">Перейти к пользователю в административном разделе</a><br/>';
            while($arAdmin = $res->Fetch()){
                $arSendFields['RECIPIENT_DATA'] = $arAdmin['NAME'];
                $arSendFields['EMAIL_LIST'] = $arAdmin['EMAIL'];
                CEvent::Send('NEW_USER_ADD', 's1', $arSendFields);
            }
            ///////////////////////////////
            //добавление профиля поставщика
            $arFields = array(
                'IBLOCK_ID'         => rrsIblock::getIBlockId('farmer_profile'),
                'NAME'              => "Свойства пользователя {$new_login} с ID [{$UID}]",
                'ACTIVE'            => 'Y',
                'PROPERTY_VALUES'   => array(
                    'USER'          => $UID,
                    'PARTNER_ID'    => $agent_id,
                    'NDS'           => $nds_value,
                    'PHONE'         => $phone
                )
            );

            $ul_type_list = rrsIblock::getPropListKey('farmer_profile', 'UL_TYPE');
            if(isset($additional_data['PROPS'])) {
                foreach ($additional_data['PROPS'] as $cur_code => $cur_val) {
                    if($cur_code == 'UL_TYPE'){
                        if(isset($ul_type_list[$cur_val]['ID'])) {
                            $arFields['PROPERTY_VALUES'][$cur_code] = $ul_type_list[$cur_val]['ID'];
                        }
                    }else {
                        $arFields['PROPERTY_VALUES'][$cur_code] = $cur_val;
                    }
                }
            }
            $farmerPartnerLink = $el_obj->Add($arFields);

            //добавление связи с агентом
            $arFields = array(
                'IBLOCK_ID'         => rrsIblock::getIBlockId('farmer_agent_link'),
                'NAME'              => "Привязка [{$UID}] к организатору [{$agent_id}]",
                'ACTIVE'            => 'Y',
                'PROPERTY_VALUES'   => array(
                    'USER_ID'           => $UID,
                    'AGENT_ID'          => $agent_id,
                    'AGENT_LINK_DATE'   => ConvertTimeStamp(false, 'FULL'),
                    'AGENT_RIGHTS'      => rrsIblock::getPropListKey('farmer_agent_link', 'AGENT_RIGHTS', 'control_w_deals'),
                    'FARMER_NICKNAME'   => $nick
                )
            );
            $farmerAgentLink = $el_obj->Add($arFields);

            if(intval($farmerPartnerLink) * intval($farmerAgentLink) > 0)
            {
                $result = $UID;

                //удаляем переменную, если проверка инн уже не нужна
                if(isset($arFields['PROPERTY_VALUES']['INN'])
                    && isset($_SESSION['success_inn_' . $arFields['PROPERTY_VALUES']['INN']])
                ){
                    unset($_SESSION['success_inn_' . $arFields['PROPERTY_VALUES']['INN']]);
                }
            }
        }

        return $result;
    }

    /**
     * Добавление покупателя агентом
     *
     * @param int $agent_id идентификатор агента покупателя
     * @param int $new_login логин поставщика
     * @param int $new_email email поставщика
     * @param int $nds_value поставщик работает с НДС/без НДС (ID значения свойства)
     * @param int $nick прозвище, данное поставщику агентом для отображения в списках агента
     * @param int $phone телефон (при добавлении с помощью телефона)
     * @param array $additional_data дополнительные данные (включая полученные от контр-фокуса)
     * @return int ID добавленного поставщика
     */
    function addClientByAgent($agent_id, $new_login, $new_email, $nds_value, $nick = '', $phone = '', $additional_data = array())
    {
        $result = 0;

        $el_obj = new CIBlockElement;
        $u_obj  = new CUser;
        $gr_obj = new CGroup;

        //получение групп пользователей
        $groups_arr = array();
        $res = $gr_obj->GetList(
            ($by    = 'id'),
            ($order = 'desc'),
            array(
                'STRING_ID' => 'client'
            )
        );
        if($ob = $res->Fetch())
        {
            $groups_arr[] = $ob['ID'];
        }

        $new_pass = md5('agroclagent_' . time());
        //добавление пользователя
        $UID = $u_obj->Add(array(
            'LOGIN'             => $new_email,
            'EMAIL'             => $new_email,
            'PASSWORD'          => $new_pass,
            'CONFIRM_PASSWORD'  => $new_pass,
            'GROUP_ID'          => $groups_arr,
            'ACTIVE'            => 'Y',
            'UF_DEMO'           => 'Y',
            'UF_FIRST_PHONE'    => 1,
            'UF_AGENT_ADDED'    => 1,
            'UF_FIRST_LOGIN'    => 1,
            'NAME' => (isset($additional_data['FIELDS']['NAME']) ? $additional_data['FIELDS']['NAME'] : ''),
            'LAST_NAME' => (isset($additional_data['FIELDS']['LAST_NAME']) ? $additional_data['FIELDS']['LAST_NAME'] : ''),
            'SECOND_NAME' => (isset($additional_data['FIELDS']['SECOND_NAME']) ? $additional_data['FIELDS']['SECOND_NAME'] : ''),
        ));

        if(intval($UID) > 0)
        {
            //Отправляем инфо админу
            $arSendFields = [];
            $user_data = 'Пользователь роли "покупатель" был зарегистрирован организатором ' . date('Y.m.d H:i') . '<br/><br/>'; //данные для рассылки
            $res = CUser::GetList(
                ($by = 'id'), ($order = 'asc'),
                array(
                    'GROUPS_ID' => 1,
                    'ACTIVE' => 'Y'
                ),
                array('SELECT' => array('EMAIL', 'NAME'))
            );
            $arSendFields['USER_DATA'] = $user_data;
            if(!checkEmailFromPhone($new_email)){
                $arSendFields['USER_DATA'] .= 'Email: ' . $new_email;
            }
            if(!empty($phone)){
                $arSendFields['USER_DATA'] .= 'Телефон: ' . $phone . '<br/>';
            }
            $arSendFields['USER_DATA'] .= '<a href="' . $GLOBALS['host'] . '/bitrix/admin/user_edit.php?lang=ru&ID=' . $UID . '">Перейти к пользователю в административном разделе</a><br/>';
            while($arAdmin = $res->Fetch()){
                $arSendFields['RECIPIENT_DATA'] = $arAdmin['NAME'];
                $arSendFields['EMAIL_LIST'] = $arAdmin['EMAIL'];
                CEvent::Send('NEW_USER_ADD', 's1', $arSendFields);
            }
            ///////////////////////////////
            //добавление профиля покупателя
            $arFields = array(
                'IBLOCK_ID'         => rrsIblock::getIBlockId('client_profile'),
                'NAME'              => "Свойства пользователя {$new_login} с ID [{$UID}]",
                'ACTIVE'            => 'Y',
                'PROPERTY_VALUES'   => array(
                    'USER'          => $UID,
                    'NDS'           => $nds_value,
                    'PHONE'         => $phone
                )
            );

            $ul_type_list = rrsIblock::getPropListKey('client_profile', 'UL_TYPE');
            if(isset($additional_data['PROPS'])) {
                foreach ($additional_data['PROPS'] as $cur_code => $cur_val) {
                    if($cur_code == 'UL_TYPE'){
                        if(isset($ul_type_list[$cur_val]['ID'])) {
                            $arFields['PROPERTY_VALUES'][$cur_code] = $ul_type_list[$cur_val]['ID'];
                        }
                    }else {
                        $arFields['PROPERTY_VALUES'][$cur_code] = $cur_val;
                    }
                }
            }
            $clientProfileLink = $el_obj->Add($arFields);

            //добавление связи с агентом
            $arFields = array(
                'IBLOCK_ID'         => rrsIblock::getIBlockId('client_agent_link'),
                'NAME'              => "Привязка покупателя [{$UID}] к организатору [{$agent_id}]",
                'ACTIVE'            => 'Y',
                'PROPERTY_VALUES'   => array(
                    'USER_ID'           => $UID,
                    'AGENT_ID'          => $agent_id,
                    'AGENT_LINK_DATE'   => ConvertTimeStamp(false, 'FULL'),
                    'AGENT_RIGHTS'      => rrsIblock::getPropListKey('client_agent_link', 'AGENT_RIGHTS', 'control_w_deals'),
                    'CLIENT_NICKNAME'   => $nick
                )
            );
            $clientAgentLink = $el_obj->Add($arFields);

            if($clientProfileLink > 0 && $clientAgentLink > 0)
            {
                $result = $UID;

                //удаляем переменную, если проверка инн уже не нужна
                if(isset($arFields['PROPERTY_VALUES']['INN'])
                    && isset($_SESSION['success_inn_' . $arFields['PROPERTY_VALUES']['INN']])
                ){
                    unset($_SESSION['success_inn_' . $arFields['PROPERTY_VALUES']['INN']]);
                }
            }
        }

        return $result;
    }

    /*
     * Формирование ссылки для активации аккаунта поставщика
     *
     * @param int $farmer_id идентификатор поставщика
     * @param int $agent_id идентификатор агента
     * @return string ссылка для активации аккаунта
     * */
    function getFarmerInviteHref($farmer_id, $agent_id)
    {
        $result = '';
        //проверка привязан ли пользователь к агенту
        if(self::checkFarmerByAgent($farmer_id, $agent_id))
        {
            $login = '';
            //формирование приглашения
            global $DB;
            $user_obj = new CUser;
            $res = $user_obj->GetList(
                ($by = 'id'),
                ($order = 'desc'),
                array('ID' => $farmer_id),
                array('FIELDS' => array(
                    'LOGIN'
                ))
            );
            if($data = $res->Fetch())
            {
                $login = $data['LOGIN'];
            }

            $ID = intval($farmer_id);
            $salt = randString(8);
            $checkword = md5(CMain::GetServerUniqID().uniqid());
            $strSql = "UPDATE b_user SET ".
                "    CHECKWORD = '".$salt.md5($salt.$checkword)."', ".
                "    CHECKWORD_TIME = ".$DB->CurrentTimeFunction().", ".
                "    LID = '".$DB->ForSql(SITE_ID, 2)."', ".
                "   TIMESTAMP_X = TIMESTAMP_X ".
                "WHERE ID = '".$ID."'".
                "    AND (EXTERNAL_AUTH_ID IS NULL OR EXTERNAL_AUTH_ID='') LIMIT 1";

            $DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

            $result = $GLOBALS['host'] . '/?change_password=yes&lang=ru&USER_CHECKWORD=' . $checkword . '&USER_LOGIN=' . $login . '&invite_by_agent=y';
        }

        return $result;
    }

    /*
     * Формирование ссылки для активации аккаунта покупателя
     *
     * @param int $client_id идентификатор покупателя
     * @param int $agent_id идентификатор агента
     * @return string ссылка для активации аккаунта
     * */
    function getClientInviteHref($client_id, $agent_id)
    {
        $result = '';
        //проверка привязан ли пользователь к агенту
        if(self::checkClientByAgent($client_id, $agent_id))
        {
            $login = '';
            //формирование приглашения
            global $DB;
            $user_obj = new CUser;
            $res = $user_obj->GetList(
                ($by = 'id'),
                ($order = 'desc'),
                array('ID' => $client_id),
                array('FIELDS' => array(
                    'LOGIN'
                ))
            );
            if($data = $res->Fetch())
            {
                $login = $data['LOGIN'];
            }

            $ID = intval($client_id);
            $salt = randString(8);
            $checkword = md5(CMain::GetServerUniqID().uniqid());
            $strSql = "UPDATE b_user SET ".
                "    CHECKWORD = '".$salt.md5($salt.$checkword)."', ".
                "    CHECKWORD_TIME = ".$DB->CurrentTimeFunction().", ".
                "    LID = '".$DB->ForSql(SITE_ID, 2)."', ".
                "   TIMESTAMP_X = TIMESTAMP_X ".
                "WHERE ID = '".$ID."'".
                "    AND (EXTERNAL_AUTH_ID IS NULL OR EXTERNAL_AUTH_ID='') LIMIT 1";

            $DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

            $result = $GLOBALS['host'] . '/?change_password=yes&lang=ru&USER_CHECKWORD=' . $checkword . '&USER_LOGIN=' . $login . '&invite_by_agent=y';
        }

        return $result;
    }

    /*
     * Проверка привязки поставщика к агенту
     *
     * @param int $farmer_id идентификатор поставщика
     * @param int $agent_id идентификатор агента
     * @return boolean флаг наличия привязки
     * */
    function checkFarmerByAgent($farmer_id, $agent_id)
    {
        $result = false;
        $el_obj = new CIBlockElement;
        $res = $el_obj->GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID'         => rrsIblock::getIBlockId('farmer_agent_link'),
                'PROPERTY_USER_ID'  => $farmer_id,
                'PROPERTY_AGENT_ID' => $agent_id,
                'ACTIVE'            => 'Y'
            ),
            false,
            array('nTopCount' => 1),
            array(
                'ID'
            )
        );
        if($res->SelectedRowsCount() > 0){
            $result = true;
        }

        return $result;
    }

    /*
     * Проверка привязки покупателя к агенту
     *
     * @param int $client_id идентификатор покупателя
     * @param int $agent_id идентификатор агента
     * @return boolean флаг наличия привязки
     * */
    function checkClientByAgent($client_id, $agent_id)
    {
        $result = false;
        $el_obj = new CIBlockElement;
        $res = $el_obj->GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID'         => rrsIblock::getIBlockId('client_agent_link'),
                'PROPERTY_USER_ID'  => $client_id,
                'PROPERTY_AGENT_ID' => $agent_id,
                'ACTIVE'            => 'Y'
            ),
            false,
            array('nTopCount' => 1),
            array(
                'ID'
            )
        );
        if($res->SelectedRowsCount() > 0){
            $result = true;
        }

        return $result;
    }

    /*
     * Получение организатора агента поставщика
     *
     * @param int $agent_id идентификатор агента поставщика
     * @return int id организатора
     * */
    function getPartnerByAgent($agent_id)
    {
        $result = 0;
        $el_obj = new CIBlockElement;
        $res = $el_obj->GetList(
            array('ID' => "ASC"),
            array(
                'IBLOCK_ID'         => rrsIblock::getIBlockId('agent_partner_link'),
                'ACTIVE'            => 'Y',
                'PROPERTY_USER_ID'  => $agent_id
            ),
            false,
            false,
            array('PROPERTY_PARTNER_ID')
        );
        if($data = $res->Fetch())
        {
            $result = $data['PROPERTY_PARTNER_ID_VALUE'];
        }

        return $result;
    }

    /*
     * Получение организатора агента клиента
     *
     * @param int $agent_id идентификатор агента клиента
     * @return int id организатора
     * */
    function getPartnerByClientAgent($agent_id)
    {
        $result = 0;
        $el_obj = new CIBlockElement;
        $res = $el_obj->GetList(
            array('ID' => "ASC"),
            array(
                'IBLOCK_ID'         => rrsIblock::getIBlockId('client_agent_partner_link'),
                'ACTIVE'            => 'Y',
                'PROPERTY_USER_ID'  => $agent_id
            ),
            false,
            false,
            array('PROPERTY_PARTNER_ID')
        );
        if($data = $res->Fetch())
        {
            $result = $data['PROPERTY_PARTNER_ID_VALUE'];
        }

        return $result;
    }

    /**
     * Получение списка всех складов поставщиков связаных с агентов
     * @param  int $user_id идентификатор агента
     * @return [] массив со списком элементов
     */
    function getWarehouseList($user_id) {
        CModule::IncludeModule('iblock');

        $el_obj = new CIBlockElement;
        $farmers_arr = self::getFarmers($user_id);

        $res = $el_obj->GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('farmer_warehouse'),
                'ACTIVE' => 'Y',
                'PROPERTY_FARMER' => $farmers_arr,
                'PROPERTY_ACTIVE' => rrsIblock::getPropListKey('farmer_warehouse', 'ACTIVE', 'yes')
            ),
            false,
            false,
            array(
                'ID',
                'NAME',
                'SORT',
                'PROPERTY_ADDRESS',
                'PROPERTY_FARMER',
                'PROPERTY_MAP'
            )
        );
        while ($ob = $res->Fetch()) {
            $result[$ob['ID']] = array(
                'ID'        => $ob['ID'],
                'NAME'      => $ob['NAME'],
                'ADDRESS'   => $ob['PROPERTY_ADDRESS_VALUE'],
                'MAP'       => $ob['PROPERTY_MAP_VALUE'],
                'FARMER'    => $ob['PROPERTY_FARMER_VALUE']
            );
        }

        return $result;
    }

    /**
     * Получение списка прав агентов по спику поставщиков
     * @param mixed $farmers_arr идентификатор или массив идентификаторов поставщиков
     * @param mixed $agents_arr идентификатор или массив идентификаторов агентов
     * @return [] массив со списком прав агента по каждому привязанному поставщику
     */
    function getAgentsRightsToFarmers($farmers_arr, $agents_arr) {
        $result = array();

        if(!is_array($farmers_arr) && is_numeric($farmers_arr)){
            $farmers_arr = array($farmers_arr);
        }

        if(!is_array($agents_arr) && is_numeric($agents_arr)){
            $agents_arr = array($agents_arr);
        }

        CModule::IncludeModule('iblock');

        $ib_id = rrsIblock::getIBlockId('farmer_agent_link');
        $el_obj = new CIBlockElement;
        $filter_arr = array();
        foreach($farmers_arr as $cur_farmer){
            foreach($agents_arr as $cur_agent){
                $filter_arr[] = array('PROPERTY_USER_ID' => $cur_farmer, 'PROPERTY_AGENT_ID' => $cur_agent);
            }
        }
        if(count($filter_arr) > 0){
            $filter_arr['LOGIC'] = 'OR';
            $filter_arr = array(
                'IBLOCK_ID' => $ib_id,
                $filter_arr
            );

            $res = $el_obj->GetList(
                array('ID' => 'ASC'),
                $filter_arr,
                false,
                false,
                array('ID', 'NAME', 'PROPERTY_USER_ID', 'PROPERTY_AGENT_ID', 'PROPERTY_AGENT_RIGHTS')
            );
            while($data = $res->Fetch()){
                $result[$data['PROPERTY_USER_ID_VALUE']][$data['PROPERTY_AGENT_ID_VALUE']] = array(
                    'AGENT_RIGHTS'  => $data['PROPERTY_AGENT_RIGHTS_ENUM_ID']
                );
            }
        }

        return $result;
    }

    /**
     * Получение списка прав агентов по спику покупателей
     *
     * @param mixed $clients_arr идентификатор или массив идентификаторов покупателей
     * @param mixed $agents_arr идентификатор или массив идентификаторов агентов
     * @return [] массив со списком прав агента по каждому привязанному покупателю
     */
    function getAgentsRightsToClients($clients_arr, $agents_arr) {
        $result = array();

        if(!is_array($clients_arr) && is_numeric($clients_arr)){
            $clients_arr = array($clients_arr);
        }

        if(!is_array($agents_arr) && is_numeric($agents_arr)){
            $agents_arr = array($agents_arr);
        }

        CModule::IncludeModule('iblock');

        $ib_id = rrsIblock::getIBlockId('client_agent_link');
        $el_obj = new CIBlockElement;
        $filter_arr = array();
        foreach($clients_arr as $cur_farmer){
            foreach($agents_arr as $cur_agent){
                $filter_arr[] = array('PROPERTY_USER_ID' => $cur_farmer, 'PROPERTY_AGENT_ID' => $cur_agent);
            }
        }
        if(count($filter_arr) > 0){
            $filter_arr['LOGIC'] = 'OR';
            $filter_arr = array(
                'IBLOCK_ID' => $ib_id,
                $filter_arr
            );

            $res = $el_obj->GetList(
                array('ID' => 'ASC'),
                $filter_arr,
                false,
                false,
                array('ID', 'NAME', 'PROPERTY_USER_ID', 'PROPERTY_AGENT_ID', 'PROPERTY_AGENT_RIGHTS')
            );
            while($data = $res->Fetch()){
                $result[$data['PROPERTY_USER_ID_VALUE']][$data['PROPERTY_AGENT_ID_VALUE']] = array(
                    'AGENT_RIGHTS'  => $data['PROPERTY_AGENT_RIGHTS_ENUM_ID']
                );
            }
        }

        return $result;
    }

    /**
     * Получение прав на участие в сделках для поставщиков (например наличие загруженных документов)
     * @param mixed $farmers_ids идентификатор или массив идентификаторов поставщиков
     * @return [] массив со списком элементов
     */
    function checkFarmersDealsRights($farmers_ids) {
        $result = array();

        if(!is_array($farmers_ids) && is_numeric($farmers_ids)){
            $farmers_ids = array($farmers_ids);
        }

        foreach($farmers_ids as $cur_farmer){
            $result[$cur_farmer] = farmer::checkDealsRights($cur_farmer);
        }

        return $result;
    }

    /**
     * Получение наличия прав на работу агента с поставщиком (например разрешение от организатора на участие агента в сделке от лица АП)
     * @param mixed $farmer_id идентификатор или массив поставщика
     * @param int $agent_id идентификатор агент
     * @param string $deal_type код работы проверяемой на разрешение
     * @return mixed разрешено ли агенту совершать действия от лица поставщика (например участие в сделке)
     */
    function checkFarmerByAgentRights($farmer_id, $agent_id, $deal_type) {
        $result = array();

        $check_farmer_id = array();
        if(is_array($farmer_id)){
            $check_farmer_id = $farmer_id;
        }else{
            $check_farmer_id[] = $farmer_id;
            $result = false;
        }

        $farmers_rights = self::getAgentsRightsToFarmers($farmer_id, $agent_id);
        $rights_list = rrsIblock::getPropListId('farmer_agent_link', 'AGENT_RIGHTS');

        switch($deal_type){
            case 'deals':
                foreach($check_farmer_id as $cur_farmer_id){
                    $temp_right = true;
                    if(!isset($farmers_rights[$cur_farmer_id][$agent_id]['AGENT_RIGHTS'])
                        || !isset($rights_list[$farmers_rights[$cur_farmer_id][$agent_id]['AGENT_RIGHTS']])
                        || $rights_list[$farmers_rights[$cur_farmer_id][$agent_id]['AGENT_RIGHTS']]['XML_ID'] != 'control_w_deals'
                    ){
                        $temp_right = false;
                    }

                    if(is_array($farmer_id)){
                        $result[$cur_farmer_id] = $temp_right;
                    }else{
                        $result = $temp_right;
                    }
                }
                break;
        }

        return $result;
    }

    /**
     * Получение ID АП по ID сделки
     * @param int $deal_id - ID сделки
     *
     * @return int ID поставщика
     */
    function getFarmerByDeal($deal_id) {
        $result = 0;

        CModule::IncludeModule('iblock');
        $el_obj = new CIBlockElement;
        $res = $el_obj->GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('deals_deals'),
                'ID'        => $deal_id,
                'ACTIVE'    => 'Y'
            ),
            false,
            array('nTopCount' => 1),
            array('PROPERTY_FARMER')
        );
        if($data = $res->Fetch()){
            $result = $data['PROPERTY_FARMER_VALUE'];
        }

        return $result;
    }

    /**
     * Получение ID покупателя по ID сделки
     * @param int $deal_id - ID сделки
     *
     * @return int ID покупателя
     */
    function getClientByDeal($deal_id) {
        $result = 0;

        CModule::IncludeModule('iblock');
        $el_obj = new CIBlockElement;
        $res = $el_obj->GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('deals_deals'),
                'ID'        => $deal_id,
                'ACTIVE'    => 'Y'
            ),
            false,
            array('nTopCount' => 1),
            array('PROPERTY_CLIENT')
        );
        if($data = $res->Fetch()){
            $result = $data['PROPERTY_CLIENT_VALUE'];
        }

        return $result;
    }

    /**
     * Получение id поставщика по id товара
     * @param int $offer_id идентификатор поставщика
     * @return int id поставщика
     */
    function getFarmerByOffer($offer_id) {
        $result = 0;

        $el_obj = new CIBlockElement();
        $res = $el_obj->GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('farmer_offer'),
                'ID'        => $offer_id,
                'ACTIVE'    => 'Y'
            ),
            false,
            array('nTopCount' => 1),
            array('PROPERTY_FARMER')
        );
        if($data = $res->Fetch()){
            $result = $data['PROPERTY_FARMER_VALUE'];
        }

        return $result;
    }

    /**
     * Признак привязки агента к клиенту
     * @param int $client_id идентификатор покупателя
     * @param int $agent_id идентификатор агента
     * @return boolean признак привязки
     */
    function checkLinkWithClient($client_id, $agent_id) {
        $result = false;

        $el_obj = new CIBlockElement();
        $res = $el_obj->GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID'         => rrsIblock::getIBlockId('client_agent_link'),
                'ACTIVE'            => 'Y',
                'PROPERTY_USER_ID'  => $client_id,
                'PROPERTY_AGENT_ID' => $agent_id,
            ),
            false,
            array('nTopCount' => 1),
            array('ID')
        );
        if($res->SelectedRowsCount() > 0){
            $result = true;
        }

        return $result;
    }

    /**
     * Признак привязки агента к клиенту и также проверка прав клиента на добавление запросов и участие в сделках,
     * а также разрешение агенту на добавление запросов и участие в сделках
     * @param int $client_id идентификатор покупателя
     * @param int $agent_id идентификатор агента
     * @return boolean признак привязки и наличия прав у агента
     */
    function checkAgentWithClientRights($client_id, $agent_id) {
        $result = false;

        //проверка привязки агента и прав агента на работу от лица клиента
        $el_obj = new CIBlockElement();
        $res = $el_obj->GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('client_agent_link'),
                'ACTIVE'    => 'Y',
                'PROPERTY_USER_ID' => $client_id,
                'PROPERTY_AGENT_ID' => $agent_id,
                'PROPERTY_AGENT_RIGHTS' => rrsIblock::getPropListKey('client_agent_link', 'AGENT_RIGHTS', 'control_w_deals')
            ),
            false,
            array('nTopCount' => 1),
            array('ID')
        );
        if($res->SelectedRowsCount() > 0){
            //если есть привязка и права агента, то проверяем права клиента на работу в системе
            if(client::getAddRights($client_id) == 'Y'){
                $result = true;
            }
        }

        return $result;
    }

    /*
     * Возвращает id агента поставщика по id поставщика
     *
     * @param int $farmer_id идентификатор поставщика
     * @return int идентификатор агента поставщика
     * */
    function getAgentByFarmer($farmer_id){
        $result = 0;

        if(!is_numeric($farmer_id) || $farmer_id == 0){
            return $result;
        }

        CModule::IncludeModule('iblock');
        $res = CIBlockElement::GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('farmer_agent_link'),
                'ACTIVE' => 'Y',
                'PROPERTY_USER_ID' => $farmer_id
            ),
            false,
            array('nTopCount' => 1),
            array('PROPERTY_AGENT_ID')
        );
        if($data = $res->Fetch()){
            if(isset($data['PROPERTY_AGENT_ID_VALUE'])
                && is_numeric($data['PROPERTY_AGENT_ID_VALUE'])
            ){
                $result = $data['PROPERTY_AGENT_ID_VALUE'];
            }
        }

        return $result;
    }

    /*
     * Возвращает id агента покупателя по id покупателя
     *
     * @param int $client_id идентификатор покупателя
     * @return int идентификатор агента покупателя
     * */
    function getClientAgentByClient($client_id){
        $result = 0;

        if(!is_numeric($client_id) || $client_id == 0){
            return $result;
        }

        CModule::IncludeModule('iblock');
        $res = CIBlockElement::GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('client_agent_link'),
                'ACTIVE' => 'Y',
                'PROPERTY_USER_ID' => $client_id
            ),
            false,
            array('nTopCount' => 1),
            array('PROPERTY_AGENT_ID')
        );
        if($data = $res->Fetch()){
            if(isset($data['PROPERTY_AGENT_ID_VALUE'])
                && is_numeric($data['PROPERTY_AGENT_ID_VALUE'])
            ){
                $result = $data['PROPERTY_AGENT_ID_VALUE'];
            }
        }

        return $result;
    }

    /*
     * Проверка установленного у агента АП разрешения организатора на участие в сделках от лица АП
     * если передан массив идентификаторовпокупателей, то возвращается true, если хотя бы для одного
     * из АП у агента есть права на совершение сделок и создание запросов
     * если не задан $farmer_id, то берутся все покупатели привязанные к данному агенту
     *
     * @param mixed $farmer_id идентификатор покупателя либо массив идентификаторов
     * @param int $agent_id идентификатор агента покупателя
     * @return boolean флаг наличия прав
     * */
    function agentHasRightsToDealsProperty($farmer_id = '', $agent_id){
        $result = false;

        if(!is_numeric($agent_id) || $agent_id == 0){
            return $result;
        }

        CModule::IncludeModule('iblock');

        $check_farmers = array();
        if(is_array($farmer_id) && count($farmer_id) > 0){
            $check_farmers = $farmer_id;
        }elseif(is_numeric($farmer_id) && $farmer_id> 0){
            $check_farmers = array($farmer_id);
        }elseif($farmer_id == ''){
            $check_farmers = self::getClients($agent_id);
            if(count($check_farmers) == 0){
                return $result;
            }
        }else{
            return $result;
        }

        $res = CIBlockElement::GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID'             => rrsIblock::getIBlockId('farmer_agent_link'),
                'ACTIVE'                => 'Y',
                'PROPERTY_USER_ID'      => $check_farmers,
                'PROPERTY_AGENT_ID'     => $agent_id,
                'PROPERTY_AGENT_RIGHTS' => rrsIblock::getPropListKey('farmer_agent_link', 'AGENT_RIGHTS', 'control_w_deals')
            ),
            false,
            array('nTopCount' => 1),
            array('ID')
        );
        if($res->SelectedRowsCount() > 0){
            $result = true;
        }

        return $result;
    }

    /*
     * Проверка установленного у агента покупателя разрешения организатора на участие в сделках от лица покупателя
     * если передан массив идентификаторовпокупателей, то возвращается true, если хотя бы для одного
     * из покупателей у агента есть права на совершение сделок
     * если не задан $client_id, то берутся все покупатели привязанные к данному агенту
     *
     * @param mixed $client_id идентификатор покупателя либо массив идентификаторов
     * @param int $agent_id идентификатор агента покупателя
     * @param boolean $check_partner_doc дополнительная проверка на наличие договора привязки организатора и покупателя (необязательный)
     * @return boolean флаг наличия прав
     * */
    function clientAgentHasRightsToDealsProperty($client_id = '', $agent_id, $check_partner_doc = false){
        $result = false;

        if(!is_numeric($agent_id) || $agent_id == 0){
            return $result;
        }

        CModule::IncludeModule('iblock');

        $check_clients = array();
        if(is_array($client_id) && count($client_id) > 0){
            $check_clients = $client_id;
        }elseif(is_numeric($client_id) && $client_id> 0){
            $check_clients = array($client_id);
        }elseif($client_id == ''){
            $check_clients = self::getClients($agent_id);
            if(count($check_clients) == 0){
                return $result;
            }
        }else{
            return $result;
        }

        $res = CIBlockElement::GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID'             => rrsIblock::getIBlockId('client_agent_link'),
                'ACTIVE'                => 'Y',
                'PROPERTY_USER_ID'      => $check_clients,
                'PROPERTY_AGENT_ID'     => $agent_id,
                'PROPERTY_AGENT_RIGHTS' => rrsIblock::getPropListKey('client_agent_link', 'AGENT_RIGHTS', 'control_w_deals')
            ),
            false,
            array('nTopCount' => 1),
            array('ID')
        );
        if($res->SelectedRowsCount() > 0){
            $result = true;
        }

        //дополнительная проверка на наличие договора привязки организатора и покупателя
        if($result && $check_partner_doc){
            $res = CIBlockElement::GetList(
                array('ID' => 'ASC'),
                array(
                    'IBLOCK_ID'             => rrsIblock::getIBlockId('client_partner_link'),
                    'ACTIVE'                => 'Y',
                    'PROPERTY_USER_ID'      => $check_clients,
                    '!PROPERTY_PARTNER_LINK_DOC' => false
                ),
                false,
                array('nTopCount' => 1),
                array('ID')
            );
            if($res->SelectedRowsCount() == 0){
                $result = false;
            }
        }

        return $result;
    }

    /*
     * Получение профиля агента АП по ID АП
     *
     * @param int $client_id идентификатор покупателя
     * @return array данные профиля покупателя
     * */
    function getProfileByFarmerID($farmer_id){
        $result = array();

        if(!is_numeric($farmer_id) || $farmer_id == 0){
            return $result;
        }

        $agent_id = self::getAgentByFarmer($farmer_id);
        if($agent_id > 0){
            $result = self::getProfile($agent_id);
            if(isset($result['USER']['ID'])){
                $result['DEALS_RIGHTS'] = self::agentHasRightsToDealsProperty($farmer_id, $agent_id);
            }
        }

        return $result;
    }

        /*
     * Получение профилей организаторов АП по ID АП
     *
     * @param int $farmer_id идентификатор поставщика
     * @return array данные профиля покупателя
     * */
        function getProfileListByFarmerID($farmer_id,$check_right = true){
            $result = array();

            if(!is_numeric($farmer_id) || $farmer_id == 0){
                return $result;
            }
            $partnersList = farmer::getLinkedPartnerList($farmer_id);
            foreach ($partnersList as $partner_id){
                if($partner_id > 0){
                    $result[$partner_id] = self::getProfile($partner_id);
                    if($check_right === true){
                        if(isset($result[$partner_id]['USER']['ID'])){
                            $result[$partner_id]['DEALS_RIGHTS'] = self::agentHasRightsToDealsProperty($farmer_id, $partner_id);
                        }
                    }
                }
            }
            return $result;
        }



    /*
     * Получение профиля агента покупателя по ID покупателя
     *
     * @param int $client_id идентификатор покупателя
     * @return array данные профиля покупателя
     * */
    function getProfileByClientID($client_id){
        $result = array();

        if(!is_numeric($client_id) || $client_id == 0){
            return $result;
        }

        $agent_id = self::getClientAgentByClient($client_id);
        if($agent_id > 0){
            $result = self::getClientAgentProfile($agent_id);
            if(isset($result['USER']['ID'])){
                $result['DEALS_RIGHTS'] = self::clientAgentHasRightsToDealsProperty($client_id, $agent_id);
            }
        }

        return $result;
    }

  /*
 * Получение профилей организаторов покупателя по ID покупателя
 *
 * @param int $client_id идентификатор покупателя
 * @return array данные профиля покупателя
 * */
    function getProfileListByClientID($client_id,$check_right = true){
        $result = array();

        if(!is_numeric($client_id) || $client_id == 0){
            return $result;
        }
        $partnersList = client::getLinkedPartnerList($client_id);
        foreach ($partnersList as $partner_id){
            if($partner_id > 0){
                $result[$partner_id] = self::getClientAgentProfile($partner_id);
                if($check_right === true){
                    if(isset($result[$partner_id]['USER']['ID'])){
                        $result[$partner_id]['DEALS_RIGHTS'] = self::clientAgentHasRightsToDealsProperty($client_id, $partner_id);
                    }
                }
            }
        }
        return $result;
    }

    /**
     * Проверка пользователя на принадлежность к группе "Агенты поставщиков"
     * @param $iUserId
     * @return bool
     */
    public static function checkIsAgent($iUserId) {

        $obUser     = new CUser;
        $obGroup    = new CGroup;

        // Группа "Агенты поставщиков"
        $arGroupAgents = $obGroup->GetList(
            $by = "c_sort",
            $order = "asc",
            ['STRING_ID' => 'partner']
        )->Fetch();

        // Группы пользователя
        $arGroupUser = $obUser->GetUserGroup($iUserId);

        return in_array($arGroupAgents['ID'], $arGroupUser);
    }

    /**
     * Получение id покупателя по id склада
     * @param int $warehouse_id - ID склада
     * @return int - ID покупателя
     */
    function getClientByWarehouse($warehouse_id){
        $result = 0;

        $res = CIBlockElement::GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('client_warehouse'),
                'ACTIVE' => 'Y',
                'ID' => $warehouse_id
            ),
            false,
            array('nTopCount' => 1),
            array('PROPERTY_CLIENT')
        );
        if($data = $res->Fetch()){
            if(isset($data['PROPERTY_CLIENT_VALUE'])
                && is_numeric($data['PROPERTY_CLIENT_VALUE'])
            ){
                $result = $data['PROPERTY_CLIENT_VALUE'];
            }
        }

        return $result;
    }

    /*
     * Получение прав агента покупателя на участие в сделках от имени покупателя
     *
     * @param int $agent_id - ID агента покупателя
     * @param array $client_data - данные покупателя, либо данные массива покупателей
     * @param bool $is_list - флаг того передаются данные для одного пользователя либо, для списка пользователей
     *
     * @return mixed - если переданы данные одного покупателя то возвращается строка (Y/N),
     * иначе массив значений, где ключ - ID покупателя, занчение строка наличия прав (Y/N)
     * */
    function getClientDealsRightsForAgent($agent_id, $client_data, $is_list = false){
        $result = array();

        if(is_numeric($agent_id)
            && $agent_id > 0
        ){
            $agent_right_val = rrsIblock::getPropListKey('client_agent_link', 'AGENT_RIGHTS', 'control_w_deals');
            if($is_list){
                //проверка переданных данных для списка
                foreach($client_data as $cur_uid => $cur_data){
                    if(isset($cur_data['LINK_DOC'])
                        && $cur_data['LINK_DOC'] == 'Y'
                        && isset($cur_data['VERIFIED'])
                        && $cur_data['VERIFIED'] == 'Y'
                        && (!isset($cur_data['UF_DEMO'])
                            || $cur_data['UF_DEMO'] != '1'
                        )
                        && isset($cur_data['AGENT_RIGHTS'])
                        && $cur_data['AGENT_RIGHTS'] == $agent_right_val
                    ){
                        $result[$cur_uid] = 'Y';
                    }
                }
            }else{
                $result = 'N';

                //проверка переданных данных для одного пользователя
                foreach($client_data as $cur_uid => $cur_data){
                    if(isset($cur_data['LINK_DOC'])
                        && $cur_data['LINK_DOC'] == 'Y'
                        && isset($cur_data['VERIFIED'])
                        && $cur_data['VERIFIED'] == 'Y'
                        && (!isset($cur_data['UF_DEMO'])
                            || $cur_data['UF_DEMO'] != '1'
                        )
                        && isset($cur_data['AGENT_RIGHTS'])
                        && $cur_data['AGENT_RIGHTS'] == $agent_right_val
                    ){
                        $result = 'Y';
                        break;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Проверка сохранения фильтра на странице запросов агента поставщика
     *
     * @return array массив, где у ключа 'NEED_UPD' значение либо true либо false,
     * у ключа 'URL_UPD' значение адреса переадресации
     */
    public static function filterAgentRequestCheck(){
        $result = array(
            'NEED_UPD'  => false,
            'URL_UPD'   => ''
        );
        if(isset($_GET['r'])
            && $_GET['r'] > 0){
            return $result;
        }

        if(((isset($_GET['region_id']))&&(!empty($_GET['region_id'])))||
            ((isset($_GET['farmer_id'][0]))&&(!empty($_GET['farmer_id'][0])))||
            ((isset($_GET['culture']))&&(!empty($_GET['culture'])))||
            ((isset($_GET['type_nds']))&&(!empty($_GET['type_nds'])))
        ){
            return $result;
        }

        $page_need_update = false;
        $new_url_params = array();

        $region_id_cookie = '';
        $farmer_cookie = '';
        $culture_cookie = '';
        $type_nds_cookie = '';
        //проверка куки регионы
        $cookie_name = 'agent_request_region_id';
        if(isset($_COOKIE[$cookie_name])){
            $region_id_cookie = $_COOKIE[$cookie_name];
            if((!isset($_GET['region_id']) || $_GET['region_id'] == '' || $_GET['region_id'] == '0')
                && $region_id_cookie != 0 && $region_id_cookie != ''
            ){
                $result['NEED_UPD'] = true;
                $new_url_params[] = 'region_id=' . $region_id_cookie;
            }
        }
        //проверка куки фермера
        $cookie_name = 'agent_request_farmer_id';
        if(isset($_COOKIE[trim($cookie_name)])){
            $farmer_cookie = $_COOKIE[$cookie_name];
            if((!isset($_GET['farmer_id'][0]) || $_GET['farmer_id'][0] == '' || $_GET['farmer_id'][0] == '0')
                && $farmer_cookie != 0 && $farmer_cookie != ''
            ){
                $result['NEED_UPD'] = true;
                $new_url_params[] = 'farmer_id[]=' . $farmer_cookie;
            }
        }
        //проверка куки тип ндс
        $cookie_name = 'agent_request_culture';
        if(isset($_COOKIE[trim($cookie_name)])){
            $culture_cookie = $_COOKIE[$cookie_name];
            if((!isset($_GET['culture']) || $_GET['culture'] == '' || $_GET['culture'] == '0')
                && $culture_cookie != 0 && $culture_cookie != ''
            ){
                $result['NEED_UPD'] = true;
                $new_url_params[] = 'culture=' . $culture_cookie;
            }
        }


        //проверка куки тип ндс
        $cookie_name = 'agent_request_type_nds';
        if(isset($_COOKIE[trim($cookie_name)])){
            $type_nds_cookie = $_COOKIE[$cookie_name];
            if((!isset($_GET['type_nds']) || $_GET['type_nds'] == '' || $_GET['type_nds'] == '0')
                && $type_nds_cookie != 0 && $type_nds_cookie != ''
            ){
                $result['NEED_UPD'] = true;
                $new_url_params[] = 'type_nds=' . $type_nds_cookie;
            }
        }


        if($result['NEED_UPD']){
            $result['URL_UPD'] = '/partner/farmer_request/' . (count($new_url_params) > 0 ? '?' . implode('&', $new_url_params) : '');
        }
        return $result;
    }


    /**
     * Проверка сохранения фильтра на странице складов агента поставщика
     *
     * @return array массив, где у ключа 'NEED_UPD' значение либо true либо false,
     * у ключа 'URL_UPD' значение адреса переадресации
     */
    public static function filterAgentWhCheck(){
        $result = array(
            'NEED_UPD'  => false,
            'URL_UPD'   => ''
        );

        if((isset($_GET['farmer_id'][0]))&&(!empty($_GET['farmer_id'][0]))){
            return $result;
        }

        $tabFilterSuf = '';
        $page_need_update = false;
        $new_url_params = array();

        switch($_REQUEST['status']){
            case 'yes':
                $tabFilterSuf = 'yes';
                break;
            case 'no':
                $tabFilterSuf = 'no';
                $new_url_params[] = 'status=no';
                break;
            default:
                $tabFilterSuf = 'all';
                $new_url_params[] = 'status=all';
        }
        $warehouse_cookie = '';
        $culture_cookie = '';
        //проверка куки с id поставщика
        $cookie_name = 'agent_wh_' . $tabFilterSuf . '_farmer_id';
        if(isset($_COOKIE[trim($cookie_name)])){
            $farmer_cookie = $_COOKIE[$cookie_name];
            if((!isset($_GET['farmer_id'][0]) || $_GET['farmer_id'][0] == '' || $_GET['farmer_id'][0] == '0')
                && $farmer_cookie != 0 && $farmer_cookie != ''
            ){
                $result['NEED_UPD'] = true;
                $new_url_params[] = 'farmer_id[]=' . $farmer_cookie;
            }
        }

        if($result['NEED_UPD']){
            $result['URL_UPD'] = '/agent/warehouses/' . (count($new_url_params) > 0 ? '?' . implode('&', $new_url_params) : '');
        }
        return $result;
    }


    /**
     * Проверка сохранения фильтра на странице складов агента покупателя
     *
     * @return array массив, где у ключа 'NEED_UPD' значение либо true либо false,
     * у ключа 'URL_UPD' значение адреса переадресации
     */
    public static function filterClientAgentWhCheck(){
        $result = array(
            'NEED_UPD'  => false,
            'URL_UPD'   => ''
        );

        if((isset($_GET['client_id'][0]))&&(!empty($_GET['client_id'][0]))){
            return $result;
        }

        $tabFilterSuf = '';
        $page_need_update = false;
        $new_url_params = array();

        switch($_REQUEST['status']){
            case 'yes':
                $tabFilterSuf = 'yes';
                break;
            case 'no':
                $tabFilterSuf = 'no';
                $new_url_params[] = 'status=no';
                break;
            default:
                $tabFilterSuf = 'all';
                $new_url_params[] = 'status=all';
        }
        $farmer_cookie = '';
        //проверка куки с id поставщика
        $cookie_name = 'client_agent_wh_' . $tabFilterSuf . '_client_id';
        if(isset($_COOKIE[trim($cookie_name)])){
            $farmer_cookie = $_COOKIE[$cookie_name];
            if((!isset($_GET['client_id'][0]) || $_GET['client_id'][0] == '' || $_GET['client_id'][0] == '0')
                && $farmer_cookie != 0 && $farmer_cookie != ''
            ){
                $result['NEED_UPD'] = true;
                $new_url_params[] = 'client_id[]=' . $farmer_cookie;
            }
        }

        if($result['NEED_UPD']){
            $result['URL_UPD'] = '/client_agent/warehouses/' . (count($new_url_params) > 0 ? '?' . implode('&', $new_url_params) : '');
        }
        return $result;
    }



    /**
     * Проверка сохранения фильтра на странице товаров агента поставщика
     *
     * @return array массив, где у ключа 'NEED_UPD' значение либо true либо false,
     * у ключа 'URL_UPD' значение адреса переадресации
     */
    public static function filterAgentOfferCheck(){
        $result = array(
            'NEED_UPD'  => false,
            'URL_UPD'   => ''
        );
        if(isset($_GET['id'])
            && $_GET['id'] > 0){
            return $result;
        }

        if(((isset($_GET['farmer_id'][0]))&&(!empty($_GET['farmer_id'][0])))||
            ((isset($_GET['culture']))&&(!empty($_GET['culture'])))||
            ((isset($_GET['region_id']))&&(!empty($_GET['region_id'])))||
            ((isset($_GET['type_nds']))&&(!empty($_GET['type_nds'])))){
            return $result;
        }

        $tabFilterSuf = '';
        $page_need_update = false;
        $new_url_params = array();

        switch($_REQUEST['status']){
            case 'yes':
                $tabFilterSuf = 'yes';
                break;
            case 'no':
                $tabFilterSuf = 'no';
                $new_url_params[] = 'status=no';
                break;
            default:
                $tabFilterSuf = 'all';
                $new_url_params[] = 'status=all';
        }
        $farmer_cookie = '';
        $culture_cookie = '';
        //проверка куки с id поставщика
        $cookie_name = 'farmer_offer_' . $tabFilterSuf . '_farmer_id';
        if(isset($_COOKIE[trim($cookie_name)])){
            $farmer_cookie = $_COOKIE[$cookie_name];
            if((!isset($_GET['farmer_id'][0]) || $_GET['farmer_id'][0] == '' || $_GET['farmer_id'][0] == '0')
                && $farmer_cookie != 0 && $farmer_cookie != ''
            ){
                $result['NEED_UPD'] = true;
                $new_url_params[] = 'farmer_id[]=' . $farmer_cookie;
            }
        }

        //проверка куки культуры
        $cookie_name = 'farmer_offer_' . $tabFilterSuf . '_culture_id';
        if(isset($_COOKIE[$cookie_name])){
            $culture_cookie = $_COOKIE[$cookie_name];
            if((!isset($_GET['culture']) || $_GET['culture'] == '' || $_GET['culture'] == '0')
                && $culture_cookie != 0 && $culture_cookie != ''
            ){
                $result['NEED_UPD'] = true;
                $new_url_params[] = 'culture=' . $culture_cookie;
            }
        }
        //проверка куки региона
        $cookie_name = 'farmer_offer_' . $tabFilterSuf . '_region_id';
        if(isset($_COOKIE[$cookie_name])){
            $culture_cookie = $_COOKIE[$cookie_name];
            if((!isset($_GET['region_id']) || $_GET['region_id'] == '' || $_GET['region_id'] == '0')
                && $culture_cookie != 0 && $culture_cookie != ''
            ){
                $result['NEED_UPD'] = true;
                $new_url_params[] = 'region_id=' . $culture_cookie;
            }
        }
        //проверка куки ндс
        $cookie_name = 'farmer_offer_' . $tabFilterSuf . '_type_nds';
        if(isset($_COOKIE[$cookie_name])){
            $culture_cookie = $_COOKIE[$cookie_name];
            if((!isset($_GET['type_nds']) || $_GET['type_nds'] == '' || $_GET['type_nds'] == '0')
                && $culture_cookie != 0 && $culture_cookie != ''
            ){
                $result['NEED_UPD'] = true;
                $new_url_params[] = 'type_nds=' . $culture_cookie;
            }
        }
        if($result['NEED_UPD']){
            $result['URL_UPD'] = '/partner/offer/' . (count($new_url_params) > 0 ? '?' . implode('&', $new_url_params) : '');
        }
        return $result;
    }

    /**
     * Проверка сохранения фильтра на странице запросов покупателей агента
     *
     * @return array массив, где у ключа 'NEED_UPD' значение либо true либо false,
     * у ключа 'URL_UPD' значение адреса переадресации
     */
    public static function filterClientRequestCheck() {
        $result = array(
            'NEED_UPD'  => false,
            'URL_UPD'   => ''
        );

        if(isset($_GET['request_id'])
            && $_GET['request_id'] > 0
            || !empty($_GET['client_id'][0])
            ||!empty($_GET['culture'])
        ){
            return $result;
        }

        $tabFilterSuf = '';
        $page_need_update = false;
        $new_url_params = array();

        switch($_REQUEST['status']){
            case 'yes':
                $tabFilterSuf = 'yes';
                break;
            case 'no':
                $tabFilterSuf = 'no';
                $new_url_params[] = 'status=no';
                break;
            default:
                $tabFilterSuf = 'all';
                $new_url_params[] = 'status=all';
        }

        $user_cookie = '';
        $culture_cookie = '';
        //проверка куки с id покупателя
        $cookie_name = 'client_ag_request_' . $tabFilterSuf . '_user';
        if(isset($_COOKIE[$cookie_name])){
            $user_cookie = $_COOKIE[$cookie_name];
            echo '<br/>';
            if((!isset($_GET['client_id'][0])
                    || $_GET['client_id'][0] == ''
                )
                && $user_cookie != 0
                || isset($_GET['client_id'][0])
                && $_GET['client_id'][0] != ''
                && $_GET['client_id'][0] != $user_cookie
            ){
                $result['NEED_UPD'] = true;
                $new_url_params[] = 'client_id[]=' . $user_cookie;
            }
        }

        //проверка куки культуры
        $cookie_name = 'client_ag_request_' . $tabFilterSuf . '_culture';
        if(isset($_COOKIE[$cookie_name])){
            $culture_cookie = $_COOKIE[$cookie_name];
            echo '<br/>';
            if((!isset($_GET['culture'])
                    || $_GET['culture'] == ''
                )
                && $culture_cookie != 0
                || isset($_GET['culture'])
                && $_GET['culture'] != ''
                && $_GET['culture'] != $culture_cookie
            ){
                $result['NEED_UPD'] = true;
                $new_url_params[] = 'culture=' . $culture_cookie;
            }
        }

        if($result['NEED_UPD']){
            $result['URL_UPD'] = '/partner/client_request/' . (count($new_url_params) > 0 ? '?' . implode('&', $new_url_params) : '');
        }

        return $result;
    }

    /**
     * возвращает данные организатора, привязанного к пользователю (для рассылки при регистрации из приглашения)
     * @param $userId - id агента АП
     * @param $is_invited - признак того был ли приглашен пользователь или регистрируется самостоятельно
     * @return array - массив данных с ключами [EMAIL] - почта, [NAME] - ФИО или логин
     */
    public static function getPartnerEmailDataF($userId, $is_invited = false) {
        $result = array();

        if($is_invited){
            $res = CIBlockElement::GetList(
                array('ID' => 'ASC'),
                array(
                    'IBLOCK_ID' => rrsIblock::getIBlockId('agent_partner_link'),
                    'PROPERTY_USER_ID' => $userId
                ),
                false,
                array('nTopCount' => 1),
                array('PROPERTY_PARTNER_ID')
            );
        }else{
            $res = CIBlockElement::GetList(
                array('ID' => 'ASC'),
                array(
                    'IBLOCK_ID' => rrsIblock::getIBlockId('agent_profile'),
                    'PROPERTY_USER' => $userId
                ),
                false,
                array('nTopCount' => 1),
                array('PROPERTY_PARTNER_ID')
            );
        }
        if($data = $res->Fetch()){
            if(isset($data['PROPERTY_PARTNER_ID_VALUE'])
                && is_numeric($data['PROPERTY_PARTNER_ID_VALUE'])
            ) {
                $res = CUser::GetList(
                    ($by = 'id'), ($order = 'asc'),
                    array('ID' => $data['PROPERTY_PARTNER_ID_VALUE']),
                    array('FIELDS' => array('NAME', 'LAST_NAME', 'SECOND_NAME', 'EMAIL', 'LOGIN'))
                );
                if($data = $res->Fetch()){
                    $result['EMAIL'] = $data['EMAIL'];

                    $temp_name = trim($data['LAST_NAME'].' '.$data['NAME'].' '.$data['SECOND_NAME']);
                    if($temp_name == ''){
                        $temp_name = $data['LOGIN'];
                    }

                    $result['NAME'] = $temp_name;
                }
            }
        }

        return $result;
    }

    /**
     * возвращает данные организатора, привязанного к пользователю (для рассылки при регистрации из приглашения)
     * @param int $userId - id агента покупателя
     * @param boolean $is_invited - признак того был ли приглашен пользователь или регистрируется самостоятельно
     * @return array - массив данных с ключами [EMAIL] - почта, [NAME] - ФИО или логин
     */
    public static function getPartnerEmailDataCL($userId, $is_invited = false) {
        $result = array();

        if($is_invited){
            $res = CIBlockElement::GetList(
                array('ID' => 'ASC'),
                array(
                    'IBLOCK_ID' => rrsIblock::getIBlockId('client_agent_partner_link'),
                    'PROPERTY_USER_ID' => $userId
                ),
                false,
                array('nTopCount' => 1),
                array('PROPERTY_PARTNER_ID')
            );
        }else {
            $res = CIBlockElement::GetList(
                array('ID' => 'ASC'),
                array(
                    'IBLOCK_ID' => rrsIblock::getIBlockId('client_agent_profile'),
                    'PROPERTY_USER' => $userId
                ),
                false,
                array('nTopCount' => 1),
                array('PROPERTY_PARTNER_ID')
            );
        }
        if($data = $res->Fetch()){
            if(isset($data['PROPERTY_PARTNER_ID_VALUE'])
                && is_numeric($data['PROPERTY_PARTNER_ID_VALUE'])
            ) {
                $res = CUser::GetList(
                    ($by = 'id'), ($order = 'asc'),
                    array('ID' => $data['PROPERTY_PARTNER_ID_VALUE']),
                    array('FIELDS' => array('NAME', 'LAST_NAME', 'SECOND_NAME', 'EMAIL', 'LOGIN'))
                );
                if($data = $res->Fetch()){
                    $result['EMAIL'] = $data['EMAIL'];

                    $temp_name = trim($data['LAST_NAME'].' '.$data['NAME'].' '.$data['SECOND_NAME']);
                    if($temp_name == ''){
                        $temp_name = $data['LOGIN'];
                    }

                    $result['NAME'] = $temp_name;
                }
            }
        }

        return $result;
    }

    /**
     * Проверка лимита на доступные запросы списка покупателей
     * @param mixed $user_ids - ID покупателей
     * @param boolean $return_overlimit - флаг необходимости возвращения превышения над лимитом
     * @return array - массив, где CNT - общее количество разрешенных покупателям запросов, REMAINS - общее оставшееся разрешенное количество, USERS - данные по пользователям, OVERLIM - счетчик пользователей с превышенным лимитом сущностей
     */
    public static function checkAvailableRequestLimit($user_ids, $return_overlimit = false){
        $result = array('CNT' => 0, 'REMAINS' => 0, 'USERS' => array());
        if($return_overlimit){
            $result['OVERLIM'] = 0;
        }

        if(!is_numeric($user_ids)
            &&
            (!is_array($user_ids)
            || count($user_ids) == 0
            )
        ){
            return $result;
        }

        CModule::IncludeModule('iblock');
        $el_obj = new CIBlockElement;

        //получение константы ограничения
        $current_const = intval(rrsIblock::getConst('min_request_limit'));
        if($current_const > 0){
            $result['CNT'] = $current_const * count($user_ids);
        }else{
            $current_const = 0;
        }

        //индивидуальное дополнительное к общему ограничению
        $res = $el_obj->GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('client_profile'),
                'PROPERTY_USER' => $user_ids
            ),
            false,
            false,
            array('PROPERTY_USER', 'PROPERTY_REQUEST_LIMIT')
        );
        while($data = $res->Fetch()){
            if(is_numeric($data['PROPERTY_USER_VALUE'])){
                $result['USERS'][$data['PROPERTY_USER_VALUE']] = array('CNT' => $current_const, 'REMAINS' => $current_const);
                $temp_val = intval($data['PROPERTY_REQUEST_LIMIT_VALUE']);
                if($temp_val > 0){
                    $result['CNT'] += $temp_val;
                    $result['USERS'][$data['PROPERTY_USER_VALUE']]['CNT'] += $temp_val;
                }
            }
        }

        //проверка наличия текущих активных запросов у пользователя
        $res = $el_obj->GetList(
            array('PROPERTY_CLIENT' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('client_request'),
                'ACTIVE' => 'Y',
                'PROPERTY_CLIENT' => $user_ids,
                'PROPERTY_ACTIVE' => rrsIblock::getPropListKey('client_request', 'ACTIVE', 'yes'),
            ),
            array('PROPERTY_CLIENT'),
            false,
            array('PROPERTY_CLIENT')
        );
        while($data = $res->Fetch()){
            if(isset($data['PROPERTY_CLIENT_VALUE'])
                && is_numeric($data['PROPERTY_CLIENT_VALUE'])
                && isset($result['USERS'][$data['PROPERTY_CLIENT_VALUE']]['REMAINS'])
                && isset($data['CNT'])
            ){
                $result['USERS'][$data['PROPERTY_CLIENT_VALUE']]['REMAINS'] = $result['USERS'][$data['PROPERTY_CLIENT_VALUE']]['CNT'] - $data['CNT'];
                if($result['USERS'][$data['PROPERTY_CLIENT_VALUE']]['REMAINS'] < 0){
                    if($return_overlimit){
                        $result['USERS'][$data['PROPERTY_CLIENT_VALUE']]['OVERLIM'] = (-1) * $result['USERS'][$data['PROPERTY_CLIENT_VALUE']]['REMAINS'];
                        $result['OVERLIM']++;
                    }

                    $result['USERS'][$data['PROPERTY_CLIENT_VALUE']]['REMAINS'] = 0;
                }
            }
        }

        if($result['CNT'] < 0)
            $result['CNT'] = 0;

        foreach($result['USERS'] as $cur_uid => $cur_data){
            $result['REMAINS'] += $cur_data['REMAINS'];
        }

        return $result;
    }

    /**
     * Проверка лимита на доступные товары списка поставщиков
     * @param mixed $user_ids - ID поставщиков
     * @param boolean $return_overlimit - флаг необходимости возвращения превышения над лимитом
     * @return array - массив, где CNT - общее количество разрешенных поставщикам товаров, REMAINS - общее оставшееся разрешенное количество, USERS - данные по пользователям
     */
    public static function checkAvailableOfferLimit($user_ids, $return_overlimit = false){
        $result = array('CNT' => 0, 'REMAINS' => 0, 'USERS' => array());
        if($return_overlimit){
            $result['OVERLIM'] = 0;
        }

        if(!is_numeric($user_ids)
            &&
            (!is_array($user_ids)
            || count($user_ids) == 0
            )
        ){
            return $result;
        }

        CModule::IncludeModule('iblock');
        $el_obj = new CIBlockElement;

        //получение константы ограничения
        $current_const = intval(rrsIblock::getConst('min_offer_limit', true));
        if($current_const > 0){
            $result['CNT'] = $current_const * count($user_ids);
        }else{
            $current_const = 0;
        }

        //индивидуальное дополнительное к общему ограничению
        $res = $el_obj->GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('farmer_profile'),
                'PROPERTY_USER' => $user_ids
            ),
            false,
            false,
            array('PROPERTY_USER', 'PROPERTY_OFFER_LIMIT')
        );
        while($data = $res->Fetch()){
            if(is_numeric($data['PROPERTY_USER_VALUE'])){
                $result['USERS'][$data['PROPERTY_USER_VALUE']] = array('CNT' => $current_const, 'REMAINS' => $current_const);
                $temp_val = intval($data['PROPERTY_OFFER_LIMIT_VALUE']);
                if($temp_val > 0){
                    $result['CNT'] += $temp_val;
                    $result['USERS'][$data['PROPERTY_USER_VALUE']]['CNT'] += $temp_val;
                }
            }
        }

        //проверка наличия текущих активных запросов у пользователя
        $res = $el_obj->GetList(
            array('PROPERTY_FARMER' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('farmer_offer'),
                'ACTIVE' => 'Y',
                'PROPERTY_FARMER' => $user_ids,
                'PROPERTY_ACTIVE' => rrsIblock::getPropListKey('farmer_offer', 'ACTIVE', 'yes'),
            ),
            array('PROPERTY_FARMER'),
            false,
            array('PROPERTY_FARMER')
        );
        while($data = $res->Fetch()){
            if(isset($data['PROPERTY_FARMER_VALUE'])
                && is_numeric($data['PROPERTY_FARMER_VALUE'])
                && isset($result['USERS'][$data['PROPERTY_FARMER_VALUE']]['REMAINS'])
                && isset($data['CNT'])
            ){
                $result['USERS'][$data['PROPERTY_FARMER_VALUE']]['REMAINS'] = $result['USERS'][$data['PROPERTY_FARMER_VALUE']]['CNT'] - $data['CNT'];
                if($result['USERS'][$data['PROPERTY_FARMER_VALUE']]['REMAINS'] < 0){
                    if($return_overlimit){
                        $result['USERS'][$data['PROPERTY_FARMER_VALUE']]['OVERLIM'] = (-1) * $result['USERS'][$data['PROPERTY_FARMER_VALUE']]['REMAINS'];
                        $result['OVERLIM']++;
                    }

                    $result['USERS'][$data['PROPERTY_FARMER_VALUE']]['REMAINS'] = 0;
                }
            }
        }

        if($result['CNT'] < 0)
            $result['CNT'] = 0;

        foreach($result['USERS'] as $cur_uid => $cur_data){
            $result['REMAINS'] += $cur_data['REMAINS'];
        }

        return $result;
    }
}