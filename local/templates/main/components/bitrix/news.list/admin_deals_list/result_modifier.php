<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
$obElement  = new CIBlockElement;

$dealIds = $clientIds = $farmerIds = $partnerIds = $transportIds = array();

foreach ($arResult['ITEMS'] as $arItem) {
    if (!empty($arItem['PROPERTIES']['CLIENT']['VALUE'])) {
        $clientIds[$arItem['PROPERTIES']['CLIENT']['VALUE']] = true;
    }

    if (!empty($arItem['PROPERTIES']['FARMER']['VALUE'])) {
        $farmerIds[$arItem['PROPERTIES']['FARMER']['VALUE']] = true;
    }

    if (!empty($arItem['PROPERTIES']['PARTNER']['VALUE'])) {
        $partnerIds[$arItem['PROPERTIES']['PARTNER']['VALUE']] = true;
    }

    if (!empty($arItem['PROPERTIES']['TRANSPORT']['VALUE'])) {
        $transportIds[$arItem['PROPERTIES']['TRANSPORT']['VALUE']] = true;
    }

    $dealIds[$arItem['ID']] = true;
}

//Список покупателей
$arResult['CLIENT_LIST'] = array();
if (sizeof($clientIds) > 0) {
    $res = $obElement->GetList(
        array('NAME' => 'ASC'),
        array(
            'IBLOCK_ID' => rrsIblock::getIBlockId('client_profile'),
            'ACTIVE' => 'Y',
            'PROPERTY_USER' => array_keys($clientIds),
        ),
        false,
        false,
        array(
            'ID',
            'PROPERTY_FULL_COMPANY_NAME',
            'PROPERTY_IP_FIO',
            'PROPERTY_USER'
        )
    );

    while ($ob = $res->Fetch()) {
        if (trim($ob['PROPERTY_FULL_COMPANY_NAME_VALUE']) != '') {
            $ob['COMPANY'] = $ob['PROPERTY_FULL_COMPANY_NAME_VALUE'];
        }
        elseif (trim($ob['PROPERTY_IP_FIO_VALUE']) != '') {
            $ob['COMPANY'] = 'ИП ' . $ob['PROPERTY_IP_FIO_VALUE'];
        }
        $arResult['CLIENT_LIST'][$ob['PROPERTY_USER_VALUE']] = $ob;
    }
}

//Список АП
$arResult['FARMER_LIST'] = array();
if (sizeof($farmerIds) > 0) {
    $res = $obElement->GetList(
        array('NAME' => 'ASC'),
        array(
            'IBLOCK_ID' => rrsIblock::getIBlockId('farmer_profile'),
            'ACTIVE' => 'Y',
            'PROPERTY_USER' => array_keys($farmerIds),
        ),
        false,
        false,
        array(
            'ID',
            'PROPERTY_FULL_COMPANY_NAME',
            'PROPERTY_IP_FIO',
            'PROPERTY_USER'
        )
    );

    while ($ob = $res->Fetch()) {
        if (trim($ob['PROPERTY_FULL_COMPANY_NAME_VALUE']) != '') {
            $ob['COMPANY'] = $ob['PROPERTY_FULL_COMPANY_NAME_VALUE'];
        }
        elseif (trim($ob['PROPERTY_IP_FIO_VALUE']) != '') {
            $ob['COMPANY'] = 'ИП ' . $ob['PROPERTY_IP_FIO_VALUE'];
        }
        $arResult['FARMER_LIST'][$ob['PROPERTY_USER_VALUE']] = $ob;
    }
}

//Список организаторов
$arResult['PARTNER_LIST'] = array();
if (sizeof($partnerIds) > 0) {
    $res = $obElement->GetList(
        array('NAME' => 'ASC'),
        array(
            'IBLOCK_ID' => rrsIblock::getIBlockId('partner_profile'),
            'ACTIVE' => 'Y',
            'PROPERTY_USER' => array_keys($partnerIds),
        ),
        false,
        false,
        array(
            'ID',
            'PROPERTY_FULL_COMPANY_NAME',
            'PROPERTY_IP_FIO',
            'PROPERTY_USER'
        )
    );

    while ($ob = $res->Fetch()) {
        if (trim($ob['PROPERTY_FULL_COMPANY_NAME_VALUE']) != '') {
            $ob['COMPANY'] = $ob['PROPERTY_FULL_COMPANY_NAME_VALUE'];
        }
        elseif (trim($ob['PROPERTY_IP_FIO_VALUE']) != '') {
            $ob['COMPANY'] = 'ИП ' . $ob['PROPERTY_IP_FIO_VALUE'];
        }
        $arResult['PARTNER_LIST'][$ob['PROPERTY_USER_VALUE']] = $ob;
    }
}

//Список ТК
$arResult['TRANSPORT_LIST'] = array();
if (sizeof($transportIds) > 0) {
    $res = $obElement->GetList(
        array('NAME' => 'ASC'),
        array(
            'IBLOCK_ID' => rrsIblock::getIBlockId('transport_profile'),
            'ACTIVE' => 'Y',
            'PROPERTY_USER' => array_keys($transportIds),
        ),
        false,
        false,
        array(
            'ID',
            'PROPERTY_FULL_COMPANY_NAME',
            'PROPERTY_IP_FIO',
            'PROPERTY_USER'
        )
    );

    while ($ob = $res->Fetch()) {
        if (trim($ob['PROPERTY_FULL_COMPANY_NAME_VALUE']) != '') {
            $ob['COMPANY'] = $ob['PROPERTY_FULL_COMPANY_NAME_VALUE'];
        }
        elseif (trim($ob['PROPERTY_IP_FIO_VALUE']) != '') {
            $ob['COMPANY'] = 'ИП ' . $ob['PROPERTY_IP_FIO_VALUE'];
        }
        $arResult['TRANSPORT_LIST'][$ob['PROPERTY_USER_VALUE']] = $ob;
    }
}

$logs = log::getDealStatusLogList(array_keys($dealIds));

/*$arActions = array(
    10 => array(
        'code' => 'vi',
        'status' => array(
            'code' => 'execution',
            'name' => 'Исполнение заказа'
        ),
        'name' => 'Вернуться к редактированию ведомости исполнения',
        'logs' => array(
            'code' => array('complete')
        ),
        'set_status' => 'execution',
        'delete_logs' => array(
            'payment_transport_send',
            'payment_send',
            'complete'
        ),
        'delete_docs' => array(
            'act_transport',
            'commission_transport',
            'payment_transport',
            'act_deal',
            'commission',
            'payment'
        ),
        'deleve_vi' => false
    ),
    20 => array(
        'code' => 'return_reestr',
        'status' => array(
            'code' => 'execution',
            'name' => 'Исполнение заказа'
        ),
        'name' => 'Вернуться к сохранению реестров',
        'logs' => array(
            'code' => array('reestr')
        ),
        'set_status' => 'execution',
        'delete_logs' => array(
            'payment_transport_send',
            'payment_send',
            'complete',
            'reestr'
        ),
        'delete_docs' => array(
            'act_transport',
            'commission_transport',
            'payment_transport',
            'act_deal',
            'commission',
            'payment'
        ),
        'deleve_vi' => false
    ),
    30 => array(
        'code' => 'new_reestr',
        'status' => array(
            'code' => 'execution',
            'name' => 'Исполнение заказа'
        ),
        'name' => 'Перейти к загрузке реестров заново, удалив все имеющиеся',
        'logs' => array(
            'code' => array('reestr')
        ),
        'set_status' => 'execution',
        'delete_logs' => array(
            'payment_transport_send',
            'payment_send',
            'complete',
            'reestr'
        ),
        'delete_docs' => array(
            'act_transport',
            'commission_transport',
            'payment_transport',
            'act_deal',
            'commission',
            'payment',
            'reestr'
        ),
        'deleve_vi' => true
    ),
    40 => array(
        'code' => 'dtr_transport',
        'status' => array(
            'code' => 'dkp',
            'name' => 'Подписание ДКП'
        ),
        'name' => 'Сохранение перевозчиком ДС к ДТР и ДТР заново',
        'logs' => array(
            'logic' => 'and',
            'code' => array('ds_transport_transport', 'dtr_transport')
        ),
        'delivery' => true,
        'set_status' => 'dkp',
        'delete_logs' => array(
            'payment_transport_send',
            'payment_send',
            'complete',
            'reestr',
            'еxecution',
            'prepayment_send',
            'prepayment_ready',
            'dtr_transport',
            'ds_transport_transport'
        ),
        'delete_docs' => array(
            'act_transport',
            'commission_transport',
            'payment_transport',
            'act_deal',
            'commission',
            'payment',
            'reestr',
            'prepayment',
            'dtr_transport',
            'ds_transport_transport'
        ),
        'deleve_vi' => true
    ),
    50 => array(
        'code' => 'dkp_client',
        'status' => array(
            'code' => 'dkp',
            'name' => 'Подписание ДКП'
        ),
        'name' => 'Сохранение покупателем ДС к ДКП и ДКП заново',
        'logs' => array(
            'logic' => 'and',
            'code' => array('ds_client', 'dkp_client')
        ),
        'set_status' => 'dkp',
        'delete_logs' => array(
            'payment_transport_send',
            'payment_send',
            'complete',
            'reestr',
            'еxecution',
            'prepayment_send',
            'prepayment_ready',
            'dkp_client',
            'ds_client'
        ),
        'delete_docs' => array(
            'act_transport',
            'commission_transport',
            'payment_transport',
            'act_deal',
            'commission',
            'payment',
            'reestr',
            'prepayment',
            'dkp_client',
            'ds_client'
        ),
        'deleve_vi' => true
    ),
    60 => array(
        'code' => 'dtr_partner',
        'status' => array(
            'code' => 'dkp',
            'name' => 'Подписание ДКП'
        ),
        'name' => 'Сохранение организатором ДС к ДТР и ДТР заново',
        'logs' => array(
            'logic' => 'and',
            'code' => array('ds_transport_partner', 'dtr_partner')
        ),
        'delivery' => true,
        'set_status' => 'dkp',
        'delete_logs' => array(
            'payment_transport_send',
            'payment_send',
            'complete',
            'reestr',
            'еxecution',
            'prepayment_send',
            'prepayment_ready',
            'dtr_transport',
            'ds_transport_transport',
            'dtr_partner',
            'ds_transport_partner'
        ),
        'delete_docs' => array(
            'act_transport',
            'commission_transport',
            'payment_transport',
            'act_deal',
            'commission',
            'payment',
            'reestr',
            'prepayment',
            'dtr_transport',
            'ds_transport_transport',
            'dtr_partner',
            'ds_transport_partner'
        ),
        'deleve_vi' => true
    ),
    70 => array(
        'code' => 'dkp_partner',
        'status' => array(
            'code' => 'dkp',
            'name' => 'Подписание ДКП'
        ),
        'name' => 'Сохранение организатором ДС к ДКП и ДКП заново',
        'logs' => array(
            'logic' => 'and',
            'code' => array('ds_partner', 'dkp_partner')
        ),
        'set_status' => 'dkp',
        'delete_logs' => array(
            'payment_transport_send',
            'payment_send',
            'complete',
            'reestr',
            'еxecution',
            'prepayment_send',
            'prepayment_ready',
            'dkp_client',
            'ds_client',
            'dkp_partner',
            'ds_partner'
        ),
        'delete_docs' => array(
            'act_transport',
            'commission_transport',
            'payment_transport',
            'act_deal',
            'commission',
            'payment',
            'reestr',
            'prepayment',
            'dkp_client',
            'ds_client',
            'dkp_partner',
            'ds_partner'
        ),
        'deleve_vi' => true
    ),
    80 => array(
        'code' => 'form_dtr',
        'status' => array(
            'code' => 'dkp',
            'name' => 'Подписание ДКП'
        ),
        'name' => 'Вернуться к формированию ДС к ДТР и ДТР',
        'logs' => array(
            'logic' => 'or',
            'code' => array('ds_transport_ready', 'dtr_ready')
        ),
        'delivery' => true,
        'set_status' => 'dkp',
        'delete_logs' => array(
            'payment_transport_send',
            'payment_send',
            'complete',
            'reestr',
            'еxecution',
            'prepayment_send',
            'prepayment_ready',
            'dtr_transport',
            'ds_transport_transport',
            'dtr_partner',
            'ds_transport_partner',
            'dtr_ready',
            'ds_transport_ready'
        ),
        'delete_docs' => array(
            'act_transport',
            'commission_transport',
            'payment_transport',
            'act_deal',
            'commission',
            'payment',
            'reestr',
            'prepayment',
            'dtr_transport',
            'ds_transport_transport',
            'dtr_partner',
            'ds_transport_partner',
            'ds_transport',
            'dtr'
        ),
        'deleve_vi' => true
    ),
    90 => array(
        'code' => 'form_dkp',
        'status' => array(
            'code' => 'dkp',
            'name' => 'Подписание ДКП'
        ),
        'name' => 'Вернуться к формированию ДС к ДКП и ДКП',
        'logs' => array(
            'logic' => 'or',
            'code' => array('ds_ready', 'dkp_ready')
        ),
        'delivery' => true,
        'set_status' => 'dkp',
        'delete_logs' => array(
            'payment_transport_send',
            'payment_send',
            'complete',
            'reestr',
            'еxecution',
            'prepayment_send',
            'prepayment_ready',
            'dkp_client',
            'ds_client',
            'dkp_partner',
            'ds_partner',
            'dkp_ready',
            'ds_ready'
        ),
        'delete_docs' => array(
            'act_transport',
            'commission_transport',
            'payment_transport',
            'act_deal',
            'commission',
            'payment',
            'reestr',
            'prepayment',
            'dkp_client',
            'ds_client',
            'dkp_partner',
            'ds_partner',
            'ds',
            'dkp'
        ),
        'deleve_vi' => true
    ),
    100 => array(
        'code' => 'form_docs_tr',
        'status' => array(
            'code' => 'dkp',
            'name' => 'Подписание ДКП'
        ),
        'name' => 'Вернуться к формированию документов по сделке',
        'logs' => array(
            'logic' => 'or',
            'code' => array('ds_ready', 'dkp_ready', 'ds_transport_ready', 'dtr_ready')
        ),
        'delivery' => true,
        'set_status' => 'dkp',
        'delete_logs' => array(
            'payment_transport_send',
            'payment_send',
            'complete',
            'reestr',
            'еxecution',
            'prepayment_send',
            'prepayment_ready',
            'dtr_transport',
            'ds_transport_transport',
            'dkp_client',
            'ds_client',
            'dtr_partner',
            'ds_transport_partner',
            'dkp_partner',
            'ds_partner',
            'dtr_ready',
            'ds_transport_ready',
            'dkp_ready',
            'ds_ready'
        ),
        'delete_docs' => array(
            'act_transport',
            'commission_transport',
            'payment_transport',
            'act_deal',
            'commission',
            'payment',
            'reestr',
            'prepayment',
            'dtr_transport',
            'ds_transport_transport',
            'dkp_client',
            'ds_client',
            'dtr_partner',
            'ds_transport_partner',
            'dkp_partner',
            'ds_partner',
            'ds_transport',
            'dtr',
            'ds',
            'dkp'
        ),
        'deleve_vi' => true
    ),
    110 => array(
        'code' => 'form_docs',
        'status' => array(
            'code' => 'dkp',
            'name' => 'Подписание ДКП'
        ),
        'name' => 'Вернуться к формированию документов по сделке',
        'logs' => array(
            'logic' => 'or',
            'code' => array('ds_ready', 'dkp_ready')
        ),
        'delivery' => false,
        'set_status' => 'dkp',
        'delete_logs' => array(
            'payment_transport_send',
            'payment_send',
            'complete',
            'reestr',
            'еxecution',
            'prepayment_send',
            'prepayment_ready',
            'dkp_client',
            'ds_client',
            'dkp_partner',
            'ds_partner',
            'dkp_ready',
            'ds_ready'
        ),
        'delete_docs' => array(
            'act_transport',
            'commission_transport',
            'payment_transport',
            'act_deal',
            'commission',
            'payment',
            'reestr',
            'prepayment',
            'dkp_client',
            'ds_client',
            'dkp_partner',
            'ds_partner',
            'ds',
            'dkp'
        ),
        'deleve_vi' => true
    ),
    120 => array(
        'code' => 'tariff',
        'status' => array(
            'code' => 'new',
            'name' => 'Новая сделка'
        ),
        'name' => 'Вернуться к назначению тарифа',
        'logs' => array(
            'code' => array('order_transport')
        ),
        'delivery' => true,
        'set_status' => 'search',
        'delete_logs' => array(
            'payment_transport_send',
            'payment_send',
            'complete',
            'reestr',
            'еxecution',
            'prepayment_send',
            'prepayment_ready',
            'dtr_transport',
            'ds_transport_transport',
            'dkp_client',
            'ds_client',
            'dtr_partner',
            'ds_transport_partner',
            'dkp_partner',
            'ds_partner',
            'dtr_ready',
            'ds_transport_ready',
            'dkp_ready',
            'ds_ready',
            'transport',
            'order_transport'
        ),
        'delete_docs' => array(
            'act_transport',
            'commission_transport',
            'payment_transport',
            'act_deal',
            'commission',
            'payment',
            'reestr',
            'prepayment',
            'dtr_transport',
            'ds_transport_transport	',
            'dkp_client',
            'ds_client',
            'dtr_partner',
            'ds_transport_partner',
            'dkp_partner',
            'ds_partner',
            'ds_transport',
            'dtr',
            'ds',
            'dkp'
        ),
        'deleve_vi' => true
    ),
    130 => array(
        'code' => 'order',
        'status' => array(
            'code' => 'new',
            'name' => 'Новая сделка'
        ),
        'name' => 'Вернуться к отправлению согласия с условиями сделки',
        'logs' => array(
            'code' => array('order_deal')
        ),
        'set_status' => 'new',
        'delete_logs' => array(
            'payment_transport_send',
            'payment_send',
            'complete',
            'reestr',
            'еxecution',
            'prepayment_send',
            'prepayment_ready',
            'dtr_transport',
            'ds_transport_transport',
            'dkp_client',
            'ds_client',
            'dtr_partner',
            'ds_transport_partner',
            'dkp_partner',
            'ds_partner',
            'dtr_ready',
            'ds_transport_ready',
            'dkp_ready',
            'ds_ready',
            'transport',
            'order_transport',
            'order_deal'
        ),
        'delete_docs' => array(
            'act_transport',
            'commission_transport',
            'payment_transport',
            'act_deal',
            'commission',
            'payment',
            'reestr',
            'prepayment',
            'dtr_transport',
            'ds_transport_transport	',
            'dkp_client',
            'ds_client',
            'dtr_partner',
            'ds_transport_partner',
            'dkp_partner',
            'ds_partner',
            'ds_transport',
            'dtr',
            'ds',
            'dkp'
        ),
        'deleve_vi' => true
    ),
);*/

$res = $obElement->GetList(
    array('SORT' => 'ASC'),
    array(
        'IBLOCK_ID' => rrsIblock::getIBlockId('deals_manage'),
        'ACTIVE' => 'Y'
    ),
    false,
    false,
    array(
        'ID',
        'NAME',
        'SORT',
        'CODE',
        'PROPERTY_STATUS',
        'PROPERTY_LOGIC',
        'PROPERTY_LOGS',
        'PROPERTY_DELIVERY',
        'PROPERTY_SET_STATUS',
        'PROPERTY_DELETE_LOGS',
        'PROPERTY_DELETE_DOCS',
        'PROPERTY_DELETE_VI',
    )
);

while ($ob = $res->Fetch()) {
    $arActions[$ob['ID']] = array(
        'code' => $ob['CODE'],
        'status' => array(
            'code' => rrsIblock::getPropListId('deals_manage', 'STATUS', $ob['PROPERTY_STATUS_ENUM_ID']),
            'name' => $ob['PROPERTY_STATUS_VALUE']
        ),
        'name' => $ob['NAME'],
        'logs' => array(
            'logic' => $ob['PROPERTY_LOGIC_VALUE'],
            'code' => $ob['PROPERTY_LOGS_VALUE']
        ),
        'delivery' => $ob['PROPERTY_DELIVERY_VALUE'],
        'set_status' => $ob['PROPERTY_SET_STATUS_VALUE'],
        'delete_logs' => $ob['PROPERTY_DELETE_LOGS_VALUE'],
        'delete_docs' => $ob['PROPERTY_DELETE_DOCS_VALUE'],
        'deleve_vi' => $ob['PROPERTY_DELETE_VI_VALUE']
    );
    if (!$ob['PROPERTY_LOGIC_VALUE'])
        unset($arActions[$ob['ID']]['logs']['logic']);

    if (!$ob['PROPERTY_DELIVERY_VALUE'])
        unset($arActions[$ob['ID']]['delivery']);
}

$arResult['ACTIONS'] = $arActions;

foreach ($arResult['ITEMS'] as $arDeal) {
    $actions = array();
    foreach ($arActions as $key => $item) {
        if (isset($item['delivery']) && $item['delivery'] == 1 && $arDeal['PROPERTIES']['DELIVERY']['VALUE_XML_ID'] != 'c') {
            continue;
        }
        if (isset($item['delivery']) && $item['delivery'] == -1 && $arDeal['PROPERTIES']['DELIVERY']['VALUE_XML_ID'] == 'c') {
            continue;
        }

        if (sizeof($item['logs']['code']) > 1) {
            if ($item['logs']['logic'] == 'or') {
                if (sizeof(array_intersect($item['logs']['code'], array_keys($logs[$arDeal['ID']]))) > 0) {
                    $actions[$key] = array(
                        'name' => $item['name'],
                        'status' => $item['status'],
                    );
                }
            }
            else {
                if (sizeof(array_intersect($item['logs']['code'], array_keys($logs[$arDeal['ID']]))) == sizeof($item['logs']['code'])) {
                    $actions[$key] = array(
                        'name' => $item['name'],
                        'status' => $item['status'],
                    );
                }
            }
        }
        else {
            if (in_array($item['logs']['code'][0], array_keys($logs[$arDeal['ID']]))) {
                $actions[$key] = array(
                    'name' => $item['name'],
                    'status' => $item['status'],
                );
            }
        }
    }

    $arResult['DEAL_ACTIONS'][$arDeal['ID']] = array();
    if (sizeof($actions) > 0) {
        foreach ($actions as $key => $item) {
            $arResult['DEAL_ACTIONS'][$arDeal['ID']][$item['status']['code']]['name'] = $item['status']['name'];
            $arResult['DEAL_ACTIONS'][$arDeal['ID']][$item['status']['code']]['items'][$key] = $item['name'];
        }
    }
}

$obj = $this->__component;
$obj->arResult['ACTIONS'] = $arActions;
$obj->SetResultCacheKeys(array('ACTIONS'));
?>