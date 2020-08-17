<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();?>
<?

//получаем ссылку для логотипа (ссылка с авторизацией, только для страницы поставщика)
if(
    !empty($arResult['FARMER_ID'])
    && empty($arParams['PARTNER_ID'])
){
    //получение/генерирование ссылки на главную страницу с авторизацией пользователя
    $sLogoHref = farmer::getStraightHrefMain($arResult['FARMER_ID']);
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

//отправка встречных предложений из списка (только для активных)
if(isset($_POST['send_counter_offer_ajax'])
    && $_POST['send_counter_offer_ajax'] == 'y'
    && isset($_POST['offer_id'])
    && isset($_POST['volume'])
    && isset($_POST['price'])
){
    //в любом случае обрабатываем ajax запрос
    $GLOBALS['APPLICATION']->RestartBuffer();

    $counter_option_contract = rrsIblock::getConst('counter_option_contract');
    $counter_option_lab = rrsIblock::getConst('counter_option_lab');
    $counter_option_support = rrsIblock::getConst('counter_option_support');

    if(is_numeric($_POST['offer_id'])
        && is_numeric($_POST['volume'])
        && is_numeric($_POST['price'])
    ){
        //проверяем не было ли ранее отправлено встречных предложений по данному предложению
        $counter_request_data = farmer::getCounterRequestsData(array($_POST['offer_id']));
        if(!isset($counter_request_data[$_POST['offer_id']]['UF_VOLUME_REMAINS'])
            || $counter_request_data[$_POST['offer_id']]['UF_VOLUME_REMAINS'] == 0
        ){
            //если есть ВП с нулевым объемом, то сначала удаляем их
            if(isset($counter_request_data[$_POST['offer_id']]['UF_VOLUME_REMAINS'])
                && $counter_request_data[$_POST['offer_id']]['UF_VOLUME_REMAINS'] == 0
            ){
                farmer::removeCountersByOfferID($_POST['offer_id']);
            }

            //собираем все подходящие запросы для отправки ВП
            $arLeads = lead::getLeadList(array('UF_OFFER_ID' => $_POST['offer_id']), ['UF_CSM_PRICE' => 'DESC']);
            $offerRequestApply = lead::createLeadList($arLeads);
            $my_c = 0;
            $sendData = array();
//            global $USER;
//            $user_rights = farmer::checkRights('counter_request', $USER->GetID());
//            if(isset($user_rights['REQUEST_RIGHT'])
//                && $user_rights['REQUEST_RIGHT'] == 'Y'
//            ) {
            $delivery_type = 'exw';

            if(isset($_POST['can_deliver'])
                && $_POST['can_deliver'] == 1
            ){
                $delivery_type = 'cpt';
            }elseif(isset($_POST['lab_trust'])
                && $_POST['lab_trust'] == 1
            ){
                $delivery_type = 'fca';
            }

            $coffer_type = 'c';
            $_POST['coffer_type'] = 'p';//всегда отправляем агентское предложение
            $arOfferData = farmer::getOfferById($_POST['offer_id']);
            $partner_quality_approved = $addit_partner_id = $addit_partner_price = $addit_is_add_cert = $addit_is_bill_of_health = $addit_is_vet_cert = $addit_is_quality_cert = $addit_is_transfer = $addit_is_secure_deal = $addit_is_agent_support = 0;
            $partner_quality_approved_d = '';
            if(isset($_POST['coffer_type'])
                && $_POST['coffer_type'] == 'p'
            ){
                $coffer_type = 'p';
                $addit_partner_price = partner::countCounterOfferPartnerPrice($_POST['price'], $_POST['volume'], ($arOfferData['Q_APPROVED'] == 1), (!empty($arParams['PARTNER_ID'])), $counter_option_contract, $counter_option_lab, $counter_option_support);
                //$addit_partner_price = (!empty($_POST['addit_partner_price']) ? str_replace(' ', '', $_POST['addit_partner_price']) : 0);
                $addit_is_add_cert = (!empty($_POST['addit_is_add_cert']) || $arOfferData['Q_APPROVED'] == 1 ? 1 : 0);
                $addit_is_bill_of_health = (!empty($_POST['addit_is_bill_of_health']) ? 1 : 0);
                $addit_is_vet_cert = (!empty($_POST['addit_is_vet_cert']) ? 1 : 0);
                $addit_is_quality_cert = (!empty($_POST['addit_is_quality_cert']) ? 1 : 0);
                $addit_is_transfer = (!empty($_POST['addit_is_transfer']) ? 1 : 0);
                $addit_is_secure_deal = (!empty($_POST['addit_is_secure_deal']) ? 1 : 0);
                $addit_is_agent_support = (!empty($_POST['addit_is_agent_support']) || !empty($arParams['PARTNER_ID']) ? 1 : 0);

                if(!empty($arParams['SENDED_BY_PARTNER'])){
                    $addit_partner_id = $arParams['SENDED_BY_PARTNER'];
                }else {
                    $arTemp = farmer::getLinkedPartnerList($arOfferData['FARMER_ID'], true);
                    $addit_partner_id = reset($arTemp);
                }
            }

            if($arOfferData['Q_APPROVED'] == 1){
                $partner_quality_approved = 1;

                if(!empty($arOfferData['Q_APPROVED_DATA'])){
                    $partner_quality_approved_d = $arOfferData['Q_APPROVED_DATA'];
                }
            }

            foreach ($offerRequestApply as $cur_data) {
                $sendData = array(
                    'offer_id' => $_POST['offer_id'],
                    'selected_requests' => $cur_data['REQUEST']['ID'],
                    'price' => $_POST['price'],
                    'volume' => $_POST['volume'],
                    'type' => 'c', //"counter"
                    'farmer_id' => $arOfferData['FARMER_ID'],
                    'delivery' => $delivery_type,
                    'coffer_type' => $coffer_type,
                    'addit_partner_price' => $addit_partner_price,
                    'addit_is_add_cert' => $addit_is_add_cert,
                    'addit_is_bill_of_health' => $addit_is_bill_of_health,
                    'addit_is_vet_cert' => $addit_is_vet_cert,
                    'addit_is_quality_cert' => $addit_is_quality_cert,
                    'addit_is_transfer' => $addit_is_transfer,
                    'addit_is_secure_deal' => $addit_is_secure_deal,
                    'addit_is_agent_support' => $addit_is_agent_support,
                    'addit_partner_id' => $addit_partner_id,
                    'partner_quality_approved' => $partner_quality_approved,
                    'partner_quality_approved_d' => $partner_quality_approved_d,
                    'real_partner_id' => (!empty($arParams['PARTNER_ID']) ? $arParams['PARTNER_ID'] : 0),
                );
                farmer::addCounterRequest($sendData);

                $my_c++;
            }

            if($my_c > 0)
            {
                echo 1;
            }else{
                //ошибка не добавлено ни одно ВП
                ob_start();
                echo "==========================\n", date('r'), "\n";
                echo "ошибка не добавлено ни одно ВП\n";
                echo $arOfferData['FARMER_ID'], "\n";
                p($_POST);
                echo "\n\n";
                file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/err_log.txt', ob_get_clean(), FILE_APPEND);
                mail("somefor@yandex.ru", "Ошибка на агрохелпере", "ошибка не добавлено ни одно ВП (send_offer_page)");
            }
//            }else{
//                //ошибка не хватает прав на добавление ВП
//                ob_start();
//                echo "==========================\n", date('r'), "\n";
//                echo "ошибка не хватает прав на добавление ВП\n";
//                global $USER;
//                echo $USER->GetID(), "\n";
//                p($_POST);
//                var_dump($user_rights);
//                echo "\n\n";
//                file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/err_log.txt', ob_get_clean(), FILE_APPEND);
//                mail("somefor@yandex.ru", "Ошибка на агрохелпере", "ошибка не хватает прав на добавление ВП");
//            }
        }else{
            //ошибка ранее были отправлены ВП по данному предложению
            ob_start();
            echo "==========================\n", date('r'), "\n";
            echo "ошибка ранее были отправлены ВП по данному предложению\n";
            echo $arOfferData['FARMER_ID'], "\n";
            p($_POST);
            p($counter_request_data);
            echo "\n\n";
            file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/err_log.txt', ob_get_clean(), FILE_APPEND);
            mail("somefor@yandex.ru", "Ошибка на агрохелпере", "ошибка ранее были отправлены ВП по данному предложению (send_offer_page)");
        }
    }else{
        //ошибка в передаваемых данных offer_id, volume или price
        ob_start();
        echo "==========================\n", date('r'), "\n";
        echo "ошибка в передаваемых данных offer_id, volume или price\n";
        echo $arOfferData['FARMER_ID'], "\n";
        p($_POST);
        echo "\n\n";
        file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/err_log.txt', ob_get_clean(), FILE_APPEND);
        mail("somefor@yandex.ru", "Ошибка на агрохелпере", "ошибка в передаваемых данных offer_id, volume или price (send_offer_page)");
    }

    exit;
}

//дополняем данные константами
?>
    <script type="text/javascript">
        <?
        //дополняем данные коэффициентом партнерских услуг
        $nCoef = rrsIblock::getConst('partner_pair_price');
        if($nCoef){?>
        var partner_price_coef = parseInt('<?=$nCoef;?>');
        <?
        }
        //дополняем данные константами для рассчета стоимости агенстких услуг
        $nCoef = rrsIblock::getConst('counter_option_contract');
        if($nCoef){?>
        var counter_option_contract = parseInt('<?=$nCoef;?>');
        <?
        }
        $nCoef = rrsIblock::getConst('counter_option_lab');
        if($nCoef){?>
        var counter_option_lab = parseInt('<?=$nCoef;?>');
        <?
        }
        $nCoef = rrsIblock::getConst('counter_option_support');
        if($nCoef){?>
        var counter_option_support = parseInt('<?=$nCoef;?>');
        <?
        }
        ?>
    </script>
<?
