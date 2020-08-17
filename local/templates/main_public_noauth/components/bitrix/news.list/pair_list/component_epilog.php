<?php

//получаем ссылку для логотипа (ссылка с авторизацией)
if(
    isset($arResult['ELEMENTS'])
    && is_array($arResult['ELEMENTS'])
    && count($arResult['ELEMENTS']) > 0
){
    //получение/генерирование ссылки на главную страницу с авторизацией пользователя
    $sLogoHref = client::getStraightHrefMain($arParams['CLIENT_ID']);
    if($sLogoHref != ''){
        //установка ссылки на логотип
        ?>
            <script type="text/javascript">
                $(document).ready(function(){
                    $('#logo').replaceWith('<a id="logo" href="<?=$sLogoHref;?>" class="color white"></a>');
                });
            </script>
        <?
    }
}

//проверка прав на принятие встречного предложения (для работы с доп опциями)
$arResult['USER_RIGHTS'] = client::checkRights('counter_request', $arParams['CLIENT_ID']);

if(
    /*isset($_POST['send_ajax'])
    && $_POST['send_ajax'] == 'y'
    &&*/ isset($_POST['accept'])
    && $_POST['accept'] == 'y'
    && isset($_POST['pair']) && is_numeric($_POST['pair'])
    && isset($_POST['request']) && is_numeric($_POST['request'])
    && isset($_POST['offer']) && is_numeric($_POST['offer'])
    && isset($arResult['USER_RIGHTS']['REQUEST_RIGHT'])
    && (
        $arResult['USER_RIGHTS']['REQUEST_RIGHT'] == 'Y'
        || $arResult['USER_RIGHTS']['REQUEST_RIGHT'] == 'LIM'
    )
){
    //в любом случае обрабатываем ajax запрос
    $GLOBALS['APPLICATION']->RestartBuffer();

    $arUpdatePropertyValues = [];
    $arFields = ['IS_ADD_CERT', 'IS_BILL_OF_HEALTH', 'IS_VET_CERT', 'IS_QUALITY_CERT', 'IS_TRANSFER', 'IS_SECURE_DEAL', 'IS_AGENT_SUPPORT'];

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

        //отображение подзаголовка
        $_SESSION['showSubTitle'] = true;

        //отправка данных на почту
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
                'PROPERTY_IS_TRANSFER', 'PROPERTY_IS_SECURE_DEAL', 'PROPERTY_IS_AGENT_SUPPORT', 'PROPERTY_FARMER', 'PROPERTY_OFFER', 'PROPERTY_COFFER_TYPE')
        );
        if($ob = $res->Fetch()) {
            $offerId = $ob['PROPERTY_OFFER_VALUE'];
            $farmerId = $ob['PROPERTY_FARMER_VALUE'];
            if($ob['PROPERTY_COFFER_TYPE_VALUE'] == 'p') {
                $arSList[] = "<li>Заключение договора =  да</li>";
            }else{
                $arSList[] = "<li>Заключение договора =  нет</li>";
            }
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

        //получение данных пользователя
        $sUserName = getUserName($arParams['CLIENT_ID']);

        $culture_name = '';
        if(!empty($offerId)){
            $offerData = farmer::getOfferById($offerId,false,false);
            $culture_name = $offerData['CULTURE_NAME'];
            unset($offerData);
        }

        $agentObj = new agent();
        //партнеры покупателя
        $clientPartners = $agentObj->getProfileListByClientID($arParams['CLIENT_ID']);
        if((sizeof($clientPartners))&&(is_array($clientPartners))){
            foreach ($clientPartners as $partner_id=>$partner){
                if(!empty($partner['USER']['EMAIL'])){
                    //отправляем письмо агенту покупателя
                    $arEventFields = array(
                        'FIO'   => $sUserName,
                        'URL'   => $GLOBALS['host'].'/profile/?uid=' . $arParams['CLIENT_ID'],
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
                if(!empty($partner['USER']['EMAIL'])){
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
            $arEventFields = array(
                'FIO'   => $sUserName,
                'URL'   => $GLOBALS['host'].'/profile/?uid=' . $arParams['CLIENT_ID'],
                'LIST'  => $sList_admin,
                'EMAIL' => $arUser['EMAIL'],
            );
            CEvent::Send('ADD_NEW_PAIR_DOP', 's1', $arEventFields);
        }
    }

        LocalRedirect('/pair_page/?spec_href=' . $_GET['spec_href']);
        exit;
//    echo 1;
//    exit;
}