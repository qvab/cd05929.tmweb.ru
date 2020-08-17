<?php
global $USER;
if(($arParams['PROFILE'] == 'Y')&&(isset($_GET['uid']))){
    $user_id = $_GET['uid'];
}else{
    $user_id = $USER->GetID();
}
//раскрываем вкладку пары
if(isset($_GET['offer_id'])
    && is_numeric($_GET['offer_id'])
    && isset($_GET['request_id'])
    && is_numeric($_GET['request_id'])
){
    //проверяем относится ли пара к АП
    CModule::IncludeModule('iblock');
    $res = CIBlockElement::GetList(
        array('ID' => 'ASC'),
        array(
            'IBLOCK_ID' => rrsIblock::getIBlockId('deals_deals'),
            'ACTIVE' => 'Y',
            'PROPERTY_CLIENT' => $user_id,
            'PROPERTY_REQUEST' => $_GET['request_id'],
            'PROPERTY_OFFER' => $_GET['offer_id']
        ),
        false,
        array('nTopCount' => 1),
        array('ID')
    );
    if($res->SelectedRowsCount() > 0){
        ?>
            <script type="text/javascript">
                $(document).ready(function(){
                    var active_offer = '<?=$_GET['offer_id'];?>', active_request = '<?=$_GET['request_id'];?>';
                    var wObj = $('.list_page_rows .line_area[data-offer="' + active_offer + '"][data-request="' + active_request + '"]');
                    if(wObj.length == 1){
                        var offset = wObj.offset();
                        wObj.addClass('active').find('.line_additional').css('display', 'block');
                        setTimeout(function(){
                            $(window).scrollTop(offset.top - 50);
                        }, 100);
                    }
                });
            </script>
        <?
    }
}

//проверка прав на принятие встречного предложения
$arResult['USER_RIGHTS'] = client::checkRights('counter_request', $user_id);

//изменение опций пары
if(
    isset($_POST['send_ajax'])
    && $_POST['send_ajax'] == 'y'
    && isset($_POST['accept'])
    && $_POST['accept'] == 'y'
    && isset($_POST['pair']) && is_numeric($_POST['pair'])
    && isset($_POST['request']) && is_numeric($_POST['request'])
    && isset($_POST['offer']) && is_numeric($_POST['offer'])
    && isset($arResult['USER_RIGHTS']['REQUEST_RIGHT'])
    && (
        //кроме ограничений здесь же проверяется и что тип пользователя - покупатель
        $arResult['USER_RIGHTS']['REQUEST_RIGHT'] == 'Y'
        || $arResult['USER_RIGHTS']['REQUEST_RIGHT'] == 'LIM'
    )
){
    //в любом случае обрабатываем ajax запрос
    $GLOBALS['APPLICATION']->RestartBuffer();

    $arUpdatePropertyValues = [];

    $arSList = [];

    //Доп опции
    foreach ($arResult['SHOW_SERVICES'] as $sName => $bFlag) {
        //если опцию можно поставить/убрать, то обрабатываем эту ситуацию
        if(isset($arResult['ALOWED_TO_CHANGE_OPTIONS'][$sName])){
            $arUpdatePropertyValues[$sName] = (isset($_REQUEST[$sName]) && $_REQUEST[$sName] == 'Y' ? 'Y' : 'N');
        }
        //иначе опцию можно только поставить
        elseif(isset($_REQUEST[$sName]) && $_REQUEST[$sName] === 'Y' ) {
            $arUpdatePropertyValues[$sName] = 'Y';
        }
    }

    if(count($arUpdatePropertyValues) > 0){
        CIBlockElement::SetPropertyValuesEx($_POST['pair'], rrsIblock::getIBlockId('deals_deals'), $arUpdatePropertyValues);
        //Обновляем цену агенстких услуг после обновления набора опций
        $arDealData = deal::getById($_POST['pair']);
        if(!empty($arDealData['PRICE_CSM'])) {
            $iNewAgentPrice = partner::countCounterOfferPartnerPrice($arDealData['PRICE_CSM'], $arDealData['VOLUME'], ($arDealData['IS_ADD_CERT'] == 'Y'), ($arDealData['IS_AGENT_SUPPORT'] == 'Y'), rrsIblock::getConst('counter_option_contract'), rrsIblock::getConst('counter_option_lab'), rrsIblock::getConst('counter_option_support'));
            if ($iNewAgentPrice) {
                CIBlockElement::SetPropertyValuesEx($_POST['pair'], rrsIblock::getIBlockId('deals_deals'), array('PARTNER_PRICE' => $iNewAgentPrice));
            }
        }

        $IB_ID_DEAL = rrsIblock::getIBlockId('deals_deals');

        global $CACHE_MANAGER;
        $CACHE_MANAGER->ClearByTag("iblock_id_".$IB_ID_DEAL);

        $sList = 'Услуги Агрохелпера:<ul>';
        $arFieldsEv = [
            'IS_ADD_CERT' => 'Отбор проб и лабораторная диагностика',
            'IS_BILL_OF_HEALTH' => 'Карантинное свидетельство',
            'IS_VET_CERT' => 'Ветеринарные свидетельства',
            'IS_QUALITY_CERT' => 'Сертификаты качества',
            'IS_TRANSFER' => 'Транспортировка',
            'IS_SECURE_DEAL' => 'Безопасная сделка',
            'IS_AGENT_SUPPORT' => 'Сопровождение сделки',
        ];
        $offerId = 0;
        $farmerId = 0;
        //получаем данные по опциям, которое сохранились в базе
        $res = CIBlockElement::GetList(
            array('ID' => 'DESC'),
            array('IBLOCK_ID' => $IB_ID_DEAL, 'ACTIVE' => 'Y', 'ID' => $_POST['pair']),
            false,
            false,
            array('ID', 'NAME', 'PROPERTY_IS_ADD_CERT', 'PROPERTY_IS_BILL_OF_HEALTH', 'PROPERTY_IS_VET_CERT', 'PROPERTY_IS_QUALITY_CERT',
                'PROPERTY_IS_TRANSFER', 'PROPERTY_IS_SECURE_DEAL', 'PROPERTY_IS_AGENT_SUPPORT', 'PROPERTY_FARMER', 'PROPERTY_OFFER')
        );
        if($ob = $res->Fetch()) {
            $offerId = $ob['PROPERTY_OFFER_VALUE'];
            $farmerId = $ob['PROPERTY_FARMER_VALUE'];
            foreach ($arFieldsEv as $sName => $sTranslate) {
                if(isset($ob['PROPERTY_'.$sName.'_VALUE']) && trim($ob['PROPERTY_'.$sName.'_VALUE']) == 'Y' ) {
                    $arSList[] = "<li>" . $sTranslate . " =  да</li>";
                }else{
                    $arSList[] = "<li>" . $sTranslate . " =  нет</li>";
                }
            }
        }
        if(count($arSList) > 0) {
            $sList .= implode('', $arSList);
            $sList .= '</ul>';
        }
        else {
            $sList = '';
        }

        $sList_admin = $sList;
        if(!empty($_POST['pair'])){
            $sList.='<br><a target="_blank" href="https://agrohelper.ru/partner/pair/?id='.$_POST['pair'].'">Пара #'.$_POST['pair'].'</a>';
            $sList_admin.='<br><a target="_blank" href="https://agrohelper.ru/bitrix/admin/iblock_element_edit.php?IBLOCK_ID=38&type=deals&ID='.$_POST['pair'].'">Пара #'.$_POST['pair'].'</a>';
        }


        $sUserName = $USER->GetFullName();
        if(empty(trim($sUserName))) {
            $sUserName = $USER->GetLogin();
        }

        $culture_name = '';
        if(!empty($offerId)){
            $offerData = farmer::getOfferById($offerId,false,false);
            $culture_name = $offerData['CULTURE_NAME'];
            unset($offerData);
        }

        $arSendedEmails = array(); //защита от повторной отправки почты
        $agentObj = new agent();
        //партнеры покупателя
        $clientPartners = $agentObj->getProfileListByClientID($user_id);
        if((sizeof($clientPartners))&&(is_array($clientPartners))){
            foreach ($clientPartners as $partner_id=>$partner){
                if(
                    !empty($partner['USER']['EMAIL'])
                    && !isset($arSendedEmails[$partner['USER']['EMAIL']])
                ){
                    $arSendedEmails[$partner['USER']['EMAIL']] = true;
                    //отправляем письмо агенту покупателя
                    $arEventFields = array(
                        'FIO'   => $sUserName,
                        'URL'   => $GLOBALS['host'].'/profile/?uid=' . $user_id,
                        'LIST'  => $sList,
                        'EMAIL' => $partner['USER']['EMAIL'],
                    );
                    CEvent::Send('ADD_NEW_PAIR_DOP', 's1', $arEventFields);
                    $partner_link = 'https://agrohelper.ru/partner/pair/?id='.$_POST['pair'];
                    $message = 'Изменены опции пары по товару "'.$culture_name.'"';
                    notice::addNotice($partner_id, 'd', $message, $partner_link, '#' . $_POST['pair']);
                }
            }
        }
        //партнеры поставщика
        $farmerPartners = $agentObj->getProfileListByFarmerID($farmerId);
        if((sizeof($farmerPartners))&&(is_array($farmerPartners))){
            foreach ($farmerPartners as $partner_id=>$partner){
                if(
                    !empty($partner['USER']['EMAIL'])
                    && !isset($arSendedEmails[$partner['USER']['EMAIL']])
                ){
                    $arSendedEmails[$partner['USER']['EMAIL']] = true;
                    //отправляем письмо агенту поставщика
                    $arEventFields = array(
                        'FIO'   => $sUserName,
                        'URL'   => $GLOBALS['host'].'/profile/?uid=' . $farmerId,
                        'LIST'  => $sList,
                        'EMAIL' => $partner['USER']['EMAIL'],
                    );
                    CEvent::Send('ADD_NEW_PAIR_DOP', 's1', $arEventFields);
                    $partner_link = 'https://agrohelper.ru/partner/pair/?id='.$_POST['pair'];
                    $message = 'Изменены опции пары по товару "'.$culture_name.'"';
                    notice::addNotice($partner_id, 'd', $message, $partner_link, '#' . $_POST['pair']);
                }
            }
        }
        /**
         * отправляем админам
         */
        $arFilter = array('GROUPS_ID' => 1, 'ACTIVE' => 'Y');
        $res = CUser::GetList(($by="id"), ($order="asc"), $arFilter, array('FIELDS' => array('ID', 'EMAIL', 'ACTIVE', 'NAME', 'LAST_NAME', 'LOGIN')));
        while ($arUser = $res->Fetch()) {
            if(!isset($arSendedEmails[$arUser['EMAIL']])) {
                $arSendedEmails[$arUser['EMAIL']] = true;
                $arEventFields = array(
                    'FIO' => $sUserName,
                    'URL' => $GLOBALS['host'] . '/profile/?uid=' . $user_id,
                    'LIST' => $sList_admin,
                    'EMAIL' => $arUser['EMAIL'],
                );
                CEvent::Send('ADD_NEW_PAIR_DOP', 's1', $arEventFields);
            }
        }
        
    }
    //LocalRedirect('/client/pair/?offer_id=' . $_POST['offer'] . '&request_id=' . $_POST['request']);
    echo 1;
    exit;
}


//получение данных о чёрных списках и оставшемся времени с момента последнего принятия (отображение кнопки добавления в ЧС)
if(isset($arResult['ELEMENTS'])
    && count($arResult['ELEMENTS']) > 0
){
    //получение данных о чёрных списках
    CModule::IncludeModule('iblock');
    global $USER;
    $el_obj = new CIBlockElement;
    $opp_arr = array();

    if($arParams['USER_TYPE'] == 'CLIENT'){
        $opp_arr = client::getUserBlackList($user_id);
    }else{
        $opp_arr = farmer::getUserBlackList($user_id);
    }
    $opp_arr = array_flip($opp_arr);

    //отмечаем те пары, которые относятся к ЧС
    if(count($opp_arr) > 0) {
        ?>
        <script type="text/javascript">
            $(document).ready(function() {
                var opp_list = [<?=implode(',', array_keys($opp_arr));?>];
                var opp_type = '<?=(isset($arParams['USER_TYPE']) && $arParams['USER_TYPE'] == 'CLIENT' ? 'f' : 'c');?>';
                var list_page_rows_obj = $('.list_page_rows:first');
                var black_obj;
                var additional_filter = '[data-' + opp_type + 'id="';
                for(var i = 0; i < opp_list.length; i++){
                    black_obj = list_page_rows_obj.find('.line_area' + additional_filter + opp_list[i] + '"]');
                    if(black_obj.length > 0){
                        black_obj.each(function(cInd, cObj){
                            $(cObj).addClass('black_list');
                        });
                    }
                }
            });
        </script>
        <?
    }
    //отбор тех пользователей, которые подходят для добавления в ЧС (т.е. которые были добавлены не более чем 24 часа назад и которые еще не в ЧС)
    $check_time = time() - (24 * 3600); //текущая дата минус 24 часа
    $arResult['C_DATES'] = array();
    $res = $el_obj->GetList(
        array('ID' => 'DESC'),
        array(
            'IBLOCK_ID' => rrsIblock::getIBlockId('deals_deals'),
            'PROPERTY_USER' => $user_id,
            '>DATE_CREATE' => ConvertTimeStamp($check_time, 'FULL', 's1')
        ),
        false,
        false,
        array('ID', 'DATE_CREATE', 'PROPERTY_CLIENT', 'PROPERTY_FARMER')
    );
    while($data = $res->Fetch()){
        $arResult['C_DATES'][$data['ID']] = array(
            'CLIENT' => $data['PROPERTY_CLIENT_VALUE'],
            'FARMER' => $data['PROPERTY_FARMER_VALUE'],
            'DATE' => MakeTimeStamp($data['DATE_CREATE'])
        );
    }

    $potential_black_arr = array();
    if(isset($arParams['USER_TYPE']) && $arParams['USER_TYPE'] == 'CLIENT') {
        foreach($arResult['C_DATES'] as $cur_deal => $cur_data) {
            if(!isset($opp_arr[$cur_data['FARMER']])
                && $check_time < $cur_data['DATE']
                && !isset($potential_black_arr[$cur_data['FARMER']])
            ){
                //устанавливаем в значение - количетсво оставшегося времени (т.к. сортировка идет по ID, то это значение отражает последнюю по времени дату)
                $potential_black_arr[$cur_data['FARMER']] = secondToHoursRod($cur_data['DATE'] - $check_time);
            }
        }
    }else{
        foreach($arResult['C_DATES'] as $cur_deal => $cur_data) {
            if(!isset($opp_arr[$cur_data['CLIENT']])
                && $check_time < $cur_data['DATE']
                && !isset($potential_black_arr[$cur_data['CLIENT']])
            ){
                //устанавливаем в значение - количетсво оставшегося времени (т.к. сортировка идет по ID, то это значение отражает последнюю по времени дату)
                $potential_black_arr[$cur_data['CLIENT']] = secondToHoursRod($cur_data['DATE'] - $check_time);
            }
        }
    }
    if(count($potential_black_arr) > 0){
        ?>
        <script type="text/javascript">
            $(document).ready(function() {
                var pot_blacklist = [<?=implode(',', array_keys($potential_black_arr));?>];
                var pot_date_values = ["<?=implode('","', $potential_black_arr);?>"];
                var opp_type = '<?=(isset($arParams['USER_TYPE']) && $arParams['USER_TYPE'] == 'CLIENT' ? 'f' : 'c');?>';
                var list_page_rows_obj = $('.list_page_rows:first');
                var pot_obj;
                for(var i = 0; i < pot_blacklist.length; i++){
                    pot_obj = list_page_rows_obj.find('.line_area[data-' + opp_type + 'id="' + pot_blacklist[i] + '"]');
                    if(pot_obj.length > 0){
                        pot_obj.each(function(cInd, cObj){
                            $(cObj).addClass('pot_black_list_area').find('.prop_area.pot_black_list_row .val_adress').prepend('<div class="related_time">Возможна отправка в чёрный список в течение: <b>' + pot_date_values[i] + '</b></div>');
                        });
                    }
                }
            });
        </script>
        <?
        //отдельно выводим данные для анкетирования
        $res = $el_obj->GetList(
            array('SORT' => 'ASC', 'ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('black_list_questionary'),
                'ACTIVE' => 'Y',
                'PROPERTY_USER_TYPE' => rrsIblock::getPropListKey('black_list_questionary', 'USER_TYPE', (isset($arParams['USER_TYPE']) && $arParams['USER_TYPE'] == 'CLIENT' ? 'c' : 'f'))
            ),
            false,
            false,
            array('ID', 'NAME')
        );
        if($res->SelectedRowsCount() > 0) {
            ?>
            <div class="anket_answers_data" style="display: none;">
            <?
            while ($data = $res->Fetch()) {
                ?>
                <div class="item" data-id="<?=$data['ID']?>"><?=$data['NAME']?></div>
                <?
            }
            ?>
            </div>
            <?
        }
    }
}