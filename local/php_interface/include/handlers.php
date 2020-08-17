<?
AddEventHandler("main", "OnBeforeUserUpdate", Array("Handlers", "OnBeforeUserUpdateHandler"));
AddEventHandler("main", "OnBeforeUserDelete", Array("Handlers", "OnBeforeUserDeleteHandler"));
AddEventHandler("main", "OnUserDelete", Array("Handlers", "OnUserDeleteHandler"));
AddEventHandler("main", "OnBeforeUserLogin", Array("Handlers", "OnBeforeUserLoginHandler"));
AddEventHandler("main", "OnBeforeUserChangePassword", Array("Handlers", "OnBeforeUserChangePasswordHandler"));
AddEventHandler("main", "OnAfterUserLogin", Array("Handlers", "OnAfterUserLoginHandler"));
AddEventHandler("main", "OnAfterUserUpdate", Array("Handlers", "OnAfterUserUpdateHandler"));
AddEventHandler("main", "OnAfterUserLogout", Array("Handlers", "OnAfterUserLogoutHandler"));

//AddEventHandler("main", "OnAfterUserAdd", Array("Handlers", "createCompany"));

AddEventHandler("iblock", "OnBeforeIBlockElementDelete", Array("Handlers", "OnBeforeIBlockElementDeleteHandler"));

AddEventHandler("iblock", "OnStartIBlockElementAdd", Array("Handlers", "OnStartIBlockElementAddHandler"));
AddEventHandler("iblock", "OnBeforeIBlockElementAdd", Array("Handlers", "OnBeforeIBlockElementAddHandler"));
AddEventHandler("iblock", "OnAfterIBlockElementAdd", Array("Handlers", "OnAfterIBlockElementAddHandler"));
AddEventHandler("iblock", "OnAfterIBlockElementUpdate", Array("Handlers", "OnAfterIBlockElementUpdateHandler"));


AddEventHandler("iblock", "OnBeforeIBlockElementUpdate", Array("Handlers", "OnBeforeIBlockElementUpdateHandler"));


AddEventHandler("iblock", "OnBeforeIBlockElementAdd", Array("Handlers", "setBindRegionalManagerToOrganizer"));
AddEventHandler("iblock", "OnBeforeIBlockElementUpdate", Array("Handlers", "setBindRegionalManagerToOrganizer"));
AddEventHandler("main", "OnAfterUserAdd", Array("CrmIntegration", "addUser"));


class Handlers
{

  function OnAfterIBlockElementAddHandler(&$fields)
  {

    $iblock = new \CIBlockElement(false);
    //self::writeToLog($fields);
    if ($fields['IBLOCK_ID'] === 13) {
      $arFilter = array("IBLOCK_ID" => '13', "ID" => (int)$fields['ID']);
      $arSelect = array('*');
      $result = $iblock->GetList(array(), $arFilter, false, false, $arSelect);
      while ($rows = $result->Fetch()) {
        $names[] = $rows['NAME'];
        $results[] = $rows['ID'];
      }
      //self::writeToLog($results);
      $values = [];
      foreach ($results as $key => $value) {
        $values[] = $iblock->GetPropertyValues(13, ['ID' => $value], true, [])->Fetch();
      }
      //self::writeToLog($values);
      $type = '';
      $title = '';
      if (CModule::IncludeModule('main')) {
        $user = \CUser::GetByID($values[0]['95'])->Fetch();
      }
      switch ($values[0]['388']) {
        case '114':
          $type = 'Юр.лицо';
          $title = $values[0]['127'];
          break;
        case '115':
          $type = 'ИП';
          $title = $values[0]['389'];
          break;
      }
      $companyFields = [
        'TITLE' => $type.' '.$title,

      ];
      $company = self::getEntity('crm.company.add', ['fields' => $companyFields])['result'];


      $nds = '';
      switch ($values[0]['120']) {
        case '372':
          $nds = 'с НДС';
          break;
        case '373':
          $nds = 'без НДС';
          break;
      }

      $requisiteFields = [
        'ENTITY_TYPE_ID' => 4,
        'ENTITY_ID' => $company,
        "PRESET_ID" => 1,
        "NAME" => "Requisite",
        'RQ_INN' => $values[0]['132'],
        'RQ_KPP' => $values[0]['133'],
        'RQ_OGRN' => $values[0]['134'],
        'RQ_OKPO' => $values[0]['320'],
        'RQ_COMPANY_FULL_NAME' => $values[0]['127'],
        'RQ_NAME' => $values[0]['392'],
        'RQ_DIRECTOR' => $values[0]['135'],
        'UF_CRM_1595518163' => $nds

      ];
      $requisite = self::getEntity('crm.requisite.add', ['fields' => $requisiteFields])['result'];
      $bankFields = [
        "ENTITY_ID" => $requisite,
        "COUNTRY_ID" => 1,
        "NAME" => "Реквизит банка",
        "XML_ID" => "1e4641fd-2dd9-31e6-b2f2-105056c00008",
        "ACTIVE" => "Y",
        'RQ_BANK_NAME' => $values[0]['138'],
        'RQ_BIK' => $values[0]['139'],
        'RQ_ACC_NUM' => $values[0]['140'],
        'RQ_COR_ACC_NUM' => $values[0]['141'],
      ];
      $requisite1 = self::getEntity('crm.requisite.bankdetail.add', ['fields' => $bankFields]);
      $fieldsBillingAddress = [
        'TYPE_ID' => 6,
        'ENTITY_TYPE_ID' => 8,
        'ENTITY_ID' => $requisite,
        'ADDRESS_1' => $values[0]['128'],
        'ANCHOR_TYPE_ID' => $requisite
      ];
      $billingAddress = self::getEntity('crm.address.add', ['fields' => $fieldsBillingAddress]);
      $contactFields = [
        'LAST_NAME' => $user['LAST_NAME'],
        'NAME' => $user['NAME'],
        'EMAIL' => [['VALUE' => $user['EMAIL'], 'VALUE_TYPE' => 'WORK']],
        'PHONE' => [['VALUE' => $values[0]['130'], 'VALUE_TYPE' => 'WORK']],
        'COMPANY_IDS' => [$company]
      ];
      self::getEntity('crm.contact.add', ['fields' => $contactFields])['result'];
    } elseif ($fields['IBLOCK_ID'] === 19) {
      $arFilter = array("IBLOCK_ID" => '19', "PROPERTY_167_VALUE" => $fields['ID']);
      $arSelect = array('*');
      $result = $iblock->GetList(array(), $arFilter, false, false, $arSelect);
      while ($rows = $result->Fetch()) {
        $names[] = $rows['NAME'];
        $results[] = $rows['ID'];
      }
      $values = [];
      foreach ($results as $key => $value) {
        $values[] = $iblock->GetPropertyValues(19, ['ID' => $value], true, [])->Fetch();
      }
      if (CModule::IncludeModule('main')) {
        $user = \CUser::GetByID($values[0]['95'])->Fetch();
      }
      $type = '';
      $title = '';

      switch ($values[0]['391']) {
        case '117':
          $type = 'ИП';
          $title = $values[0]['392'];
          break;
        case '116':
          $type = '';
          $title = $values[0]['171'];
          break;
      }
      $companyFields = [
        'TITLE' => $type.' '.$title,

      ];
      $company = self::getEntity('crm.company.add', ['fields' => $companyFields])['result'];
      $nds = '';
      switch ($values[0]['168']) {
        case '372':
          $nds = 'с НДС';
          break;
        case '373':
          $nds = 'без НДС';
          break;
      }
      $requisiteFields = [
        'ENTITY_TYPE_ID' => 4,
        'ENTITY_ID' => $company,
        "PRESET_ID" => 1,
        "NAME" => "Requisite",
        'RQ_INN' => $values[0]['170'],
        'RQ_KPP' => $values[0]['174'],
        'RQ_OGRN' => $values[0]['322'],
        'RQ_OKPO' => $values[0]['175'],
        'RQ_COMPANY_FULL_NAME' => $values[0]['171'],
        'RQ_NAME' => $values[0]['392'],
        'RQ_DIRECTOR' => $values[0]['176'],
        'UF_CRM_1595518163' => $nds

      ];

      $requisite = self::getEntity('crm.requisite.add', ['fields' => $requisiteFields])['result'];
      $bankFields = [
        "ENTITY_ID" => $requisite,
        "COUNTRY_ID" => 1,
        "NAME" => "Реквизит банка",
        "XML_ID" => "1e4641fd-2dd9-31e6-b2f2-105056c00008",
        "ACTIVE" => "Y",
        'RQ_BANK_NAME' => $values[0]['182'],
        'RQ_BIK' => $values[0]['183'],
        'RQ_ACC_NUM' => $values[0]['184'],
        'RQ_COR_ACC_NUM' => $values[0]['185'],
      ];
      $requisite1 = self::getEntity('crm.requisite.bankdetail.add', ['fields' => $bankFields]);

      $fieldsBillingAddress = [
        'TYPE_ID' => 6,
        'ENTITY_TYPE_ID' => 8,
        'ENTITY_ID' => $requisite,
        'ADDRESS_1' => $values[0]['172'],
        'ANCHOR_TYPE_ID' => $requisite
      ];
      $billingAddress = self::getEntity('crm.address.add', ['fields' => $fieldsBillingAddress]);


      $contactFields = [
        'LAST_NAME' => $user['LAST_NAME'],
        'NAME' => $user['NAME'],
        'EMAIL' => [['VALUE' => $user['EMAIL'], 'VALUE_TYPE' => 'WORK']],
        'PHONE' => [['VALUE' => $values[0]['179'], 'VALUE_TYPE' => 'WORK']],
        'COMPANY_IDS' => [$company]
      ];
      self::getEntity('crm.contact.add', ['fields' => $contactFields])['result'];
    } elseif ($fields['IBLOCK_ID'] === 17) {
      /**
       * Создание новой сделки в CRM
       */
      $arJSON = [];

      $info1 = $iblock->GetList(array(), array("IBLOCK_ID" => '17', "ID" => (int)$fields['ID']), false, false, ['*']);
      while ($rows1 = $info1->Fetch()) {
        $names2[] = $rows1['NAME'];
        $results2[] = $rows1['ID'];
      }
      $values2 = [];
      foreach ($results2 as $key2 => $value2) {
        $values2[] = $iblock->GetPropertyValues(17, ['ID' => $value2], true, [])->Fetch();
      }
      //self::writeToLog($values2);
      $arFilter = array("IBLOCK_ID" => '15', "ID" => (int)$values2[0]['80']);
      $link = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['SERVER_NAME'].'/client/request/new/?id='.$values2[0]['80'];
      $arSelect = array('*');
      $result = $iblock->GetList(array(), $arFilter, false, false, $arSelect);
      while ($rows = $result->Fetch()) {
        $names[] = $rows['NAME'];
        $results[] = $rows['ID'];
      }

      $values = [];
      foreach ($results as $key => $value) {
        $values[] = $iblock->GetPropertyValues(15, ['ID' => $value], true, [])->Fetch();
      }
      //self::writeToLog($values);
      $info = $iblock->GetList(array(), array("IBLOCK_ID" => '13', "PROPERTY_95_VALUE" => (int)$values[0]['60']), false, false, $arSelect);
      while ($rows = $info->Fetch()) {
        $names1[] = $rows['NAME'];
        $results1[] = $rows['ID'];
      }
      $values1 = [];
      foreach ($results1 as $key1 => $value1) {
        $values1[] = $iblock->GetPropertyValues(13, ['ID' => $value1], true, [])->Fetch();
      }

      //self::writeToLog($values2);

      $culture = $iblock->GetList(array(), array("IBLOCK_ID" => '10', "ID" => (int)$values2[0]['378']), false, false, $arSelect);
      while ($rows = $culture->Fetch()) {
        $names3[] = $rows['NAME'];
        $results3[] = $rows['ID'];
      }
      $values3 = [];
      foreach ($results3 as $key1 => $value1) {
        $values3[] = $iblock->GetPropertyValues(13, ['ID' => $value1], true, [])->Fetch();
      }

      $delivery = 0;
      switch ($values[0]['144']) {
        case '386':
          $delivery = 37;
          break;
        case '385':
          $delivery = 38;
          break;
      }
      $nds = 0;
      switch ($values[0]['376']) {
        case '110':
          $nds = 39;
          break;
        case '111':
          $nds = 40;
          break;
      }

      if (!empty($values1[0]['127'])) {
        $sCompanyTitle = $values1[0]['127'];
      } else {
        switch ($values1[0]['388']) {
          case '114':
            $type = 'Юр.лицо';
            break;
          case '115':
            $type = 'ИП';
            break;
        }
        $sCompanyTitle = $type." ".$values1[0]['389'];
      }
      $company = self::getEntity('crm.company.list', ['filter' => ['TITLE' => $sCompanyTitle]]);
      $count = count($company['result']);
      $id = (int)$company['result'][$count - 1]['ID'];
      $arJSON["company"] = $company;
      $arJSON["companyTitle"] = $sCompanyTitle;
      $arJSON["~fields"] = $fields;
      $arJSON["~values"] = $values;
      $arJSON["~values1"] = $values1;
      $arJSON["~values3"] = $values3;

      $rsWarehouse = CIBlockElement::GetList(
        [],
        ["ID" => $fields["PROPERTY_VALUES"]["WAREHOUSE"]],
        false,
        false,
        ["ID", "IBLOCK_ID", "NAME", "PROPERTY_ADDRESS", "UF_*", "PROPERTY_ADDRESS_VALUE", "PROPERTY_*"]
      );
      $arWarehouse = $rsWarehouse->Fetch();


      $arWarehouse["NAME"];

      $fields = [
        'TITLE' => $names3[0],
        'CATEGORY_ID' => '0',
        'STAGE_ID' => "NEW",
        'COMPANY_ID' => $id,
        'OPPORTUNITY' => $values2[0]['82'],
        'UF_CRM_1595501674113' => $link, // link on request
        'UF_CRM_1596184837367' => $nds,
        'UF_CRM_1596184779847' => $delivery,
        'UF_CRM_1597482854995' => $arWarehouse["NAME"],
        'UF_CRM_1597482891048' => $arWarehouse["PROPERTY_ADDRESS_VALUE"],
        'UF_CRM_1597483112538' => $fields["PROPERTY_VALUES"]["PRICE"],
        'UF_CRM_1597483135996' => $values[0][70]
      ];
      $arJSON["~fieldsCRM"] = $fields;
      $arJSON["~Warehouse"] = $arWarehouse;

      $f = fopen($_SERVER["DOCUMENT_ROOT"]."/___addRequest.json", "w");
      fwrite($f, json_encode($arJSON, JSON_UNESCAPED_UNICODE));
      fclose($f);
      $deal = self::getEntity('crm.deal.add', ['fields' => $fields])['result'];
      $productFields[0] = [
        'PRODUCT_NAME' => $names3[0],
        'QUANTITY' => 0,
        'PRICE' => 0,
      ];
//            self::writeToLog($deal);
//            self::writeToLog($productFields);
      $products = self::getEntity('crm.deal.productrows.set', ['id' => $deal, 'rows' => $productFields]);
      //self::writeToLog($Nfrproducts);
    }


    if ($fields['IBLOCK_ID'] == rrsIblock::getIBlockId('blacklist_ap')) {
      //при добавлении фермера в черный список клиента, удаляем пары
      if ((isset($fields['PROPERTY_VALUES']['USER_ID'])) && (isset($fields['PROPERTY_VALUES']['FARMER_ID']))) {
        $filter = array(
          'UF_CLIENT_ID' => $fields['PROPERTY_VALUES']['USER_ID'],
          'UF_FARMER_ID' => $fields['PROPERTY_VALUES']['FARMER_ID']
        );
        $arLeads = lead::getLeadList($filter);
        if (is_array($arLeads) && sizeof($arLeads) > 0) {
          lead::deleteLeads($arLeads);
        }
      }
    }
    if ($fields['IBLOCK_ID'] == rrsIblock::getIBlockId('blacklist_partner')) {
      $farmers_ids = array();
      //проверяем пары, если добавили элемент ИБ черного списка
      if ((sizeof($fields['PROPERTY_VALUES'])) && (is_array($fields['PROPERTY_VALUES']))) {
        $options = array();
        //получаем ID партнера и клиента из параметров
        foreach ($fields['PROPERTY_VALUES'] as $opt_id => $values) {
          $value = 0;
          $res = CIBlockProperty::GetByID($opt_id, $fields['IBLOCK_ID']);
          if (isset($values[key($values)])) {
            $value = $values[key($values)]['VALUE'];
          }
          if ($ar_res = $res->GetNext()) {
            if ((!empty($value)) && (isset($ar_res['CODE']))) {
              $options[$ar_res['CODE']] = $value;
            }
          }
        }
        if ((isset($options['PARTNER_ID'])) && (isset($options['USER_ID']))) {
          $farmers_ids = BlackList::getPartnerAgentsFarmers($options['PARTNER_ID']);
          if ((sizeof($farmers_ids)) && (is_array($farmers_ids))) {
            foreach ($farmers_ids as $id => $v) {
              $filter = array(
                'UF_CLIENT_ID' => $options['USER_ID'],
                'UF_FARMER_ID' => $id
              );
              $arLeads = lead::getLeadList($filter);
              if (is_array($arLeads) && sizeof($arLeads) > 0) {
                lead::deleteLeads($arLeads);
              }
            }
          }
        }
      }
    } //добавление записи в счетчик принятий (действия после добавления записи)
    elseif ($fields['IBLOCK_ID'] == rrsIblock::getIBlockId('counter_request_limits_changes')) {
      //работа с баллами пользователей
      //получение id нужных свойств
      $user_prop_id = rrsIblock::getIBlockPropertyID('counter_request_limits_changes', 'USERS_IDS');
      $action_prop_id = rrsIblock::getIBlockPropertyID('counter_request_limits_changes', 'ACTION');
      $number_prop_id = rrsIblock::getIBlockPropertyID('counter_request_limits_changes', 'NUMBER');

      //получение значений свойств
      if (
        $user_prop_id > 0
        && $action_prop_id > 0
        && $number_prop_id > 0
      ) {
        $action_prop_value = 0;
        $number_prop_value = '';

        //получаем значение типа действия с принятий
        if (isset($fields['PROPERTY_VALUES'][$action_prop_id]['0']['VALUE'])) {
          $action_prop_value = $fields['PROPERTY_VALUES'][$action_prop_id]['0']['VALUE'];
        } elseif (isset($fields['PROPERTY_VALUES'][$action_prop_id]['n0']['VALUE'])) {
          $action_prop_value = $fields['PROPERTY_VALUES'][$action_prop_id]['n0']['VALUE'];
        }

        //получаем значение принятий для изменений
        if (isset($fields['PROPERTY_VALUES'][$number_prop_id]['0']['VALUE'])) {
          $number_prop_value = $fields['PROPERTY_VALUES'][$number_prop_id]['0']['VALUE'];
        } elseif (isset($fields['PROPERTY_VALUES'][$number_prop_id]['n0']['VALUE'])) {
          $number_prop_value = $fields['PROPERTY_VALUES'][$number_prop_id]['n0']['VALUE'];
        }

        if ($action_prop_value > 0
          && $number_prop_value != ''
        ) {
          $users_ids = array();
          //собираем переданных пользователей
          if (isset($fields['PROPERTY_VALUES'][$user_prop_id])
            && is_array($fields['PROPERTY_VALUES'][$user_prop_id])
          ) {
            foreach ($fields['PROPERTY_VALUES'][$user_prop_id] as $cur_data) {
              if (isset($cur_data['VALUE'])) {
                $users_ids[] = $cur_data['VALUE'];
              }
            }
          }

          //получаем код действия
          $action_code = '';
          switch ($action_prop_value) {
            case rrsIblock::getPropListKey('counter_request_limits_changes', 'ACTION', 'change'):
              $action_code = 'change';
              break;

            case rrsIblock::getPropListKey('counter_request_limits_changes', 'ACTION', 'set'):
              $action_code = 'set';
              break;
          }

          //вносим изменения в принятия пользователей
          client::counterReqLimitQuantityChange($action_code, intval($number_prop_value), $users_ids, $fields['ID']);
        }
      }
    } //добавление записи в ограничение количества товаров (действия после добавления записи)
    elseif ($fields['IBLOCK_ID'] == rrsIblock::getIBlockId('farmer_offer_limits_changes')) {
      //работа с баллами пользователей
      //получение id нужных свойств
      $user_prop_id = rrsIblock::getIBlockPropertyID('farmer_offer_limits_changes', 'USERS_IDS');
      $action_prop_id = rrsIblock::getIBlockPropertyID('farmer_offer_limits_changes', 'ACTION');
      $number_prop_id = rrsIblock::getIBlockPropertyID('farmer_offer_limits_changes', 'NUMBER');

      //получение значений свойств
      if (
        $user_prop_id > 0
        && $action_prop_id > 0
        && $number_prop_id > 0
      ) {
        $action_prop_value = 0;
        $number_prop_value = '';

        //получаем значение типа действия с принятий
        if (isset($fields['PROPERTY_VALUES'][$action_prop_id]['0']['VALUE'])) {
          $action_prop_value = $fields['PROPERTY_VALUES'][$action_prop_id]['0']['VALUE'];
        } elseif (isset($fields['PROPERTY_VALUES'][$action_prop_id]['n0']['VALUE'])) {
          $action_prop_value = $fields['PROPERTY_VALUES'][$action_prop_id]['n0']['VALUE'];
        }

        //получаем значение принятий для изменений
        if (isset($fields['PROPERTY_VALUES'][$number_prop_id]['0']['VALUE'])) {
          $number_prop_value = $fields['PROPERTY_VALUES'][$number_prop_id]['0']['VALUE'];
        } elseif (isset($fields['PROPERTY_VALUES'][$number_prop_id]['n0']['VALUE'])) {
          $number_prop_value = $fields['PROPERTY_VALUES'][$number_prop_id]['n0']['VALUE'];
        }

        if ($action_prop_value > 0
          && $number_prop_value != ''
        ) {
          $users_ids = array();
          //собираем переданных пользователей
          if (isset($fields['PROPERTY_VALUES'][$user_prop_id])
            && is_array($fields['PROPERTY_VALUES'][$user_prop_id])
          ) {
            foreach ($fields['PROPERTY_VALUES'][$user_prop_id] as $cur_data) {
              if (isset($cur_data['VALUE'])) {
                $users_ids[] = $cur_data['VALUE'];
              }
            }
          }

          //получаем код действия
          $action_code = '';
          switch ($action_prop_value) {
            case rrsIblock::getPropListKey('farmer_offer_limits_changes', 'ACTION', 'change'):
              $action_code = 'change';
              break;

            case rrsIblock::getPropListKey('farmer_offer_limits_changes', 'ACTION', 'set'):
              $action_code = 'set';
              break;
          }

          //получаем текст из созданной записи (для почтового сообщения)
          $text_prop_id = rrsIblock::getIBlockPropertyID('farmer_offer_limits_changes', 'COMMENT_USER');
          $text_prop_value = '';
          if (isset($fields['PROPERTY_VALUES'][$text_prop_id]['0']['VALUE']['TEXT'])) {
            $text_prop_value = $fields['PROPERTY_VALUES'][$text_prop_id]['0']['VALUE']['TEXT'];
          } elseif (isset($fields['PROPERTY_VALUES'][$text_prop_id]['n0']['VALUE']['TEXT'])) {
            $text_prop_value = $fields['PROPERTY_VALUES'][$text_prop_id]['n0']['VALUE']['TEXT'];
          }

          //вносим изменения в ограничение товаров в профиле поставщика
          farmer::offerLimitQuantityChange($action_code, intval($number_prop_value), $users_ids, $fields['ID'], $text_prop_value);
        }
      }
    } //добавление записи в ограничение количества запросов (действия после добавления записи)
    elseif ($fields['IBLOCK_ID'] == rrsIblock::getIBlockId('client_request_limits_changes')) {
      //работа с баллами пользователей
      //получение id нужных свойств
      $user_prop_id = rrsIblock::getIBlockPropertyID('client_request_limits_changes', 'USERS_IDS');
      $action_prop_id = rrsIblock::getIBlockPropertyID('client_request_limits_changes', 'ACTION');
      $number_prop_id = rrsIblock::getIBlockPropertyID('client_request_limits_changes', 'NUMBER');

      //получение значений свойств
      if (
        $user_prop_id > 0
        && $action_prop_id > 0
        && $number_prop_id > 0
      ) {
        $action_prop_value = 0;
        $number_prop_value = '';

        //получаем значение типа действия с принятий
        if (isset($fields['PROPERTY_VALUES'][$action_prop_id]['0']['VALUE'])) {
          $action_prop_value = $fields['PROPERTY_VALUES'][$action_prop_id]['0']['VALUE'];
        } elseif (isset($fields['PROPERTY_VALUES'][$action_prop_id]['n0']['VALUE'])) {
          $action_prop_value = $fields['PROPERTY_VALUES'][$action_prop_id]['n0']['VALUE'];
        }

        //получаем значение принятий для изменений
        if (isset($fields['PROPERTY_VALUES'][$number_prop_id]['0']['VALUE'])) {
          $number_prop_value = $fields['PROPERTY_VALUES'][$number_prop_id]['0']['VALUE'];
        } elseif (isset($fields['PROPERTY_VALUES'][$number_prop_id]['n0']['VALUE'])) {
          $number_prop_value = $fields['PROPERTY_VALUES'][$number_prop_id]['n0']['VALUE'];
        }

        if ($action_prop_value > 0
          && $number_prop_value != ''
        ) {
          $users_ids = array();
          //собираем переданных пользователей
          if (isset($fields['PROPERTY_VALUES'][$user_prop_id])
            && is_array($fields['PROPERTY_VALUES'][$user_prop_id])
          ) {
            foreach ($fields['PROPERTY_VALUES'][$user_prop_id] as $cur_data) {
              if (isset($cur_data['VALUE'])) {
                $users_ids[] = $cur_data['VALUE'];
              }
            }
          }

          //получаем код действия
          $action_code = '';
          switch ($action_prop_value) {
            case rrsIblock::getPropListKey('client_request_limits_changes', 'ACTION', 'change'):
              $action_code = 'change';
              break;

            case rrsIblock::getPropListKey('client_request_limits_changes', 'ACTION', 'set'):
              $action_code = 'set';
              break;
          }

          //получаем текст из созданной записи (для почтового сообщения)
          $text_prop_id = rrsIblock::getIBlockPropertyID('client_request_limits_changes', 'COMMENT_USER');
          $text_prop_value = '';
          if (isset($fields['PROPERTY_VALUES'][$text_prop_id]['0']['VALUE']['TEXT'])) {
            $text_prop_value = $fields['PROPERTY_VALUES'][$text_prop_id]['0']['VALUE']['TEXT'];
          } elseif (isset($fields['PROPERTY_VALUES'][$text_prop_id]['n0']['VALUE']['TEXT'])) {
            $text_prop_value = $fields['PROPERTY_VALUES'][$text_prop_id]['n0']['VALUE']['TEXT'];
          }

          //вносим изменения в ограничение запросов в профиле покупателя
          client::requestLimitQuantityChange($action_code, intval($number_prop_value), $users_ids, $fields['ID'], $text_prop_value);
        }
      }
    }
  }

  function OnAfterIBlockElementUpdateHandler(&$fields)
  {
    //проверяем пары, если обновили элемент ИБ черного списка
    if ($fields['IBLOCK_ID'] == rrsIblock::getIBlockId('blacklist_partner')) {
      if ((sizeof($fields['PROPERTY_VALUES'])) && (is_array($fields['PROPERTY_VALUES']))) {
        $options = array();
        //получаем ID партнера и клиента из параметров
        foreach ($fields['PROPERTY_VALUES'] as $opt_id => $values) {
          $value = 0;
          $res = CIBlockProperty::GetByID($opt_id, $fields['IBLOCK_ID']);
          if (isset($values[key($values)])) {
            $value = $values[key($values)]['VALUE'];
          }
          if ($ar_res = $res->GetNext()) {
            if ((!empty($value)) && (isset($ar_res['CODE']))) {
              $options[$ar_res['CODE']] = $value;
            }
          }
        }
        if ((isset($options['PARTNER_ID'])) && (isset($options['USER_ID']))) {
          $farmers_ids = BlackList::getPartnerAgentsFarmers($options['PARTNER_ID']);
          if ((sizeof($farmers_ids)) && (is_array($farmers_ids))) {
            foreach ($farmers_ids as $id => $v) {
              $filter = array(
                'UF_CLIENT_ID' => $options['USER_ID'],
                'UF_FARMER_ID' => $id
              );
              $arLeads = lead::getLeadList($filter);
              if (is_array($arLeads) && sizeof($arLeads) > 0) {
                lead::deleteLeads($arLeads);
              }
            }
          }
        }
      }
    } //если константа - сбрасываем её кеш
    elseif ($fields['IBLOCK_ID'] == rrsIblock::getIBlockId('data')) {
      $obCache = new CPHPCache;
      $obCache->Clean('getConst_'.$fields['CODE'], "/", $basedir = "cache");
    }
  }

  /**
   * Событие до добаления нового элемента (до проверки полей на корректность)
   * @param array $arFields - массив значений
   * @return bool
   */
  function OnStartIBlockElementAddHandler(&$arFields)
  {
    //добавление записи в счетчик принятий
    if ($arFields['IBLOCK_ID'] == rrsIblock::getIBlockId('counter_request_limits_changes')) {
      //создание названия записи, если пусто
      global $USER;
      $arGroups = array_flip($USER->GetUserGroupArray());

      if (isset($arGroups[1])) {
        $arFields['NAME'] = 'Новая запись от администратора с id '.$USER->GetID();
      } elseif (isset($arGroups[9])) {
        $arFields['NAME'] = 'Новая запись от покупателя с id '.$USER->GetID();
      }

      //получаем комментарий для пользователя, если он есть
      $u_comment_prop_id = rrsIblock::getIBlockPropertyID('counter_request_limits_changes', 'COMMENT_USER');
      $action_prop_id = rrsIblock::getIBlockPropertyID('counter_request_limits_changes', 'ACTION');
      $number_prop_id = rrsIblock::getIBlockPropertyID('counter_request_limits_changes', 'NUMBER');

      $u_comment_prop_value = '';
      $action_prop_value = 0;
      $number_prop_value = 0;

      //проверяем - если комментария для пользователя нет, то ставим стандартные описания
      if ($u_comment_prop_id > 0) {
        if (isset($arFields['PROPERTY_VALUES'][$u_comment_prop_id]['0']['VALUE']['TEXT'])) {
          $u_comment_prop_value = trim($arFields['PROPERTY_VALUES'][$u_comment_prop_id]['0']['VALUE']['TEXT']);
        } elseif (isset($arFields['PROPERTY_VALUES'][$u_comment_prop_id]['n0']['VALUE']['TEXT'])) {
          $u_comment_prop_value = trim($arFields['PROPERTY_VALUES'][$u_comment_prop_id]['n0']['VALUE']['TEXT']);
        }

        if ($u_comment_prop_value == '') {
          //получаем тип действия
          if (isset($arFields['PROPERTY_VALUES'][$action_prop_id]['0']['VALUE'])) {
            $action_prop_value = $arFields['PROPERTY_VALUES'][$action_prop_id]['0']['VALUE'];
          } elseif (isset($arFields['PROPERTY_VALUES'][$action_prop_id]['n0']['VALUE'])) {
            $action_prop_value = $arFields['PROPERTY_VALUES'][$action_prop_id]['n0']['VALUE'];
          }

          //получаем значение принятий
          if (isset($arFields['PROPERTY_VALUES'][$number_prop_id]['0']['VALUE'])) {
            $number_prop_value = $arFields['PROPERTY_VALUES'][$number_prop_id]['0']['VALUE'];
          } elseif (isset($arFields['PROPERTY_VALUES'][$number_prop_id]['n0']['VALUE'])) {
            $number_prop_value = $arFields['PROPERTY_VALUES'][$number_prop_id]['n0']['VALUE'];
          }

          switch ($action_prop_value) {
            case rrsIblock::getPropListKey('counter_request_limits_changes', 'ACTION', 'change'):
              //изменение значения (+/- к текущему значению)
              $arFields['PROPERTY_VALUES'][$u_comment_prop_id]['n0']['VALUE'] = array(
                'TYPE' => 'html',
                'TEXT' => client::counterRequestOpenerDefaultText('change', $number_prop_value)
              );
              break;

            case rrsIblock::getPropListKey('counter_request_limits_changes', 'ACTION', 'set'):
              $arFields['PROPERTY_VALUES'][$u_comment_prop_id]['n0']['VALUE'] = array(
                'TYPE' => 'html',
                'TEXT' => client::counterRequestOpenerDefaultText('set', $number_prop_value)
              );
              break;
          }
        }
      }
    } //добавление записи в ограничение количества товаров
    elseif ($arFields['IBLOCK_ID'] == rrsIblock::getIBlockId('farmer_offer_limits_changes')) {
      //создание названия записи, если пусто
      global $USER;
      $arGroups = array_flip($USER->GetUserGroupArray());

      if (isset($arGroups[1])) {
        $arFields['NAME'] = 'Новая запись от администратора с id '.$USER->GetID();
      } elseif (isset($arGroups[9])) {
        $arFields['NAME'] = 'Новая запись от покупателя с id '.$USER->GetID();
      }

      //получаем комментарий для пользователя, если он есть
      $u_comment_prop_id = rrsIblock::getIBlockPropertyID('farmer_offer_limits_changes', 'COMMENT_USER');
      $action_prop_id = rrsIblock::getIBlockPropertyID('farmer_offer_limits_changes', 'ACTION');
      $number_prop_id = rrsIblock::getIBlockPropertyID('farmer_offer_limits_changes', 'NUMBER');

      $u_comment_prop_value = '';
      $action_prop_value = 0;
      $number_prop_value = 0;

      //проверяем - если комментария для пользователя нет, то ставим стандартные описания
      if ($u_comment_prop_id > 0) {
        if (isset($arFields['PROPERTY_VALUES'][$u_comment_prop_id]['0']['VALUE']['TEXT'])) {
          $u_comment_prop_value = trim($arFields['PROPERTY_VALUES'][$u_comment_prop_id]['0']['VALUE']['TEXT']);
        } elseif (isset($arFields['PROPERTY_VALUES'][$u_comment_prop_id]['n0']['VALUE']['TEXT'])) {
          $u_comment_prop_value = trim($arFields['PROPERTY_VALUES'][$u_comment_prop_id]['n0']['VALUE']['TEXT']);
        }

        if ($u_comment_prop_value == '') {
          //получаем тип действия
          if (isset($arFields['PROPERTY_VALUES'][$action_prop_id]['0']['VALUE'])) {
            $action_prop_value = $arFields['PROPERTY_VALUES'][$action_prop_id]['0']['VALUE'];
          } elseif (isset($arFields['PROPERTY_VALUES'][$action_prop_id]['n0']['VALUE'])) {
            $action_prop_value = $arFields['PROPERTY_VALUES'][$action_prop_id]['n0']['VALUE'];
          }

          //получаем значение принятий
          if (isset($arFields['PROPERTY_VALUES'][$number_prop_id]['0']['VALUE'])) {
            $number_prop_value = $arFields['PROPERTY_VALUES'][$number_prop_id]['0']['VALUE'];
          } elseif (isset($arFields['PROPERTY_VALUES'][$number_prop_id]['n0']['VALUE'])) {
            $number_prop_value = $arFields['PROPERTY_VALUES'][$number_prop_id]['n0']['VALUE'];
          }

          switch ($action_prop_value) {
            case rrsIblock::getPropListKey('farmer_offer_limits_changes', 'ACTION', 'change'):
              //изменение значения (+/- к текущему значению)
              $arFields['PROPERTY_VALUES'][$u_comment_prop_id]['n0']['VALUE'] = array(
                'TYPE' => 'html',
                'TEXT' => farmer::offerLimitDefaultText('change', $number_prop_value)
              );
              break;

            case rrsIblock::getPropListKey('farmer_offer_limits_changes', 'ACTION', 'set'):
              $arFields['PROPERTY_VALUES'][$u_comment_prop_id]['n0']['VALUE'] = array(
                'TYPE' => 'html',
                'TEXT' => farmer::offerLimitDefaultText('set', $number_prop_value)
              );
              break;
          }
        }
      }
    } //добавление записи в ограничение количества запросов
    elseif ($arFields['IBLOCK_ID'] == rrsIblock::getIBlockId('client_request_limits_changes')) {
      //создание названия записи, если пусто
      global $USER;
      $arGroups = array_flip($USER->GetUserGroupArray());

      if (isset($arGroups[1])) {
        $arFields['NAME'] = 'Новая запись от администратора с id '.$USER->GetID();
      } elseif (isset($arGroups[9])) {
        $arFields['NAME'] = 'Новая запись от покупателя с id '.$USER->GetID();
      }

      //получаем комментарий для пользователя, если он есть
      $u_comment_prop_id = rrsIblock::getIBlockPropertyID('client_request_limits_changes', 'COMMENT_USER');
      $action_prop_id = rrsIblock::getIBlockPropertyID('client_request_limits_changes', 'ACTION');
      $number_prop_id = rrsIblock::getIBlockPropertyID('client_request_limits_changes', 'NUMBER');

      $u_comment_prop_value = '';
      $action_prop_value = 0;
      $number_prop_value = 0;

      //проверяем - если комментария для пользователя нет, то ставим стандартные описания
      if ($u_comment_prop_id > 0) {
        if (isset($arFields['PROPERTY_VALUES'][$u_comment_prop_id]['0']['VALUE']['TEXT'])) {
          $u_comment_prop_value = trim($arFields['PROPERTY_VALUES'][$u_comment_prop_id]['0']['VALUE']['TEXT']);
        } elseif (isset($arFields['PROPERTY_VALUES'][$u_comment_prop_id]['n0']['VALUE']['TEXT'])) {
          $u_comment_prop_value = trim($arFields['PROPERTY_VALUES'][$u_comment_prop_id]['n0']['VALUE']['TEXT']);
        }

        if ($u_comment_prop_value == '') {
          //получаем тип действия
          if (isset($arFields['PROPERTY_VALUES'][$action_prop_id]['0']['VALUE'])) {
            $action_prop_value = $arFields['PROPERTY_VALUES'][$action_prop_id]['0']['VALUE'];
          } elseif (isset($arFields['PROPERTY_VALUES'][$action_prop_id]['n0']['VALUE'])) {
            $action_prop_value = $arFields['PROPERTY_VALUES'][$action_prop_id]['n0']['VALUE'];
          }

          //получаем значение принятий
          if (isset($arFields['PROPERTY_VALUES'][$number_prop_id]['0']['VALUE'])) {
            $number_prop_value = $arFields['PROPERTY_VALUES'][$number_prop_id]['0']['VALUE'];
          } elseif (isset($arFields['PROPERTY_VALUES'][$number_prop_id]['n0']['VALUE'])) {
            $number_prop_value = $arFields['PROPERTY_VALUES'][$number_prop_id]['n0']['VALUE'];
          }

          switch ($action_prop_value) {
            case rrsIblock::getPropListKey('client_request_limits_changes', 'ACTION', 'change'):
              //изменение значения (+/- к текущему значению)
              $arFields['PROPERTY_VALUES'][$u_comment_prop_id]['n0']['VALUE'] = array(
                'TYPE' => 'html',
                'TEXT' => client::requestLimitDefaultText('change', $number_prop_value)
              );
              break;

            case rrsIblock::getPropListKey('client_request_limits_changes', 'ACTION', 'set'):
              $arFields['PROPERTY_VALUES'][$u_comment_prop_id]['n0']['VALUE'] = array(
                'TYPE' => 'html',
                'TEXT' => client::requestLimitDefaultText('set', $number_prop_value)
              );
              break;
          }
        }
      }
    } //обработка добавления в ИБ "Связанные регионы"
    elseif ($arFields['IBLOCK_ID'] == rrsIblock::getIBlockId('linked_regions')) {
      //Генерация названия, если не задано
      if (trim($arFields['NAME']) == '') {
        $region_prop_id = rrsIblock::getIBlockPropertyID('linked_regions', 'REGION');

        //получаем названия
        $get_regions = 0;
        if (isset($arFields['PROPERTY_VALUES'][$region_prop_id]['0']['VALUE'])) {
          $get_regions = $arFields['PROPERTY_VALUES'][$region_prop_id]['0']['VALUE'];
        } elseif (isset($arFields['PROPERTY_VALUES'][$region_prop_id]['n0']['VALUE'])) {
          $get_regions = $arFields['PROPERTY_VALUES'][$region_prop_id]['n0']['VALUE'];
        }
        if ($get_regions != 0) {
          CModule::IncludeModule('iblock');
          $res = CIBlockElement::GetList(
            array('ID' => 'ASC'),
            array('IBLOCK_ID' => rrsIblock::getIBlockId('regions'), 'ID' => $get_regions),
            false,
            array('nTopCount' => 1),
            array('NAME')
          );
          if ($data = $res->Fetch()) {
            $arFields['NAME'] = "Привязки для региона \"{$data['NAME']}\"";
          }

          if (trim($arFields['NAME']) == '') {
            $arFields['NAME'] = "Привязки для региона с ID {$get_regions}";
          }
        }
      }
    }
  }

  /**
   * Событие до добаления нового элемента
   * @param $fields
   * @return bool
   */
  function OnBeforeIBlockElementAddHandler(&$fields)
  {
    if ($fields['IBLOCK_ID'] == rrsIblock::getIBlockId('blacklist_partner')) {
      if ((sizeof($fields['PROPERTY_VALUES'])) && (is_array($fields['PROPERTY_VALUES']))) {
        $options = array();
        //получаем ID партнера и клиента из параметров
        foreach ($fields['PROPERTY_VALUES'] as $opt_id => $values) {
          $value = 0;
          $res = CIBlockProperty::GetByID($opt_id, $fields['IBLOCK_ID']);
          if (isset($values[key($values)])) {
            $value = $values[key($values)]['VALUE'];
          }
          if ($ar_res = $res->GetNext()) {
            if ((!empty($value)) && (isset($ar_res['CODE']))) {
              $options[$ar_res['CODE']] = $value;
            }
          }
        }
        if ((isset($options['PARTNER_ID'])) && (isset($options['USER_ID']))) {
          if (trim($fields['NAME']) == 'Элемент черного списка') {
            $fields['NAME'] = 'Организатор ['.$options['PARTNER_ID'].'] в черном списке покупателя ['.$options['USER_ID'].']';
          }
          //проверяем принадлежность покупателя партнеру
          //если клиент принадлежит организатору, то выводим сообщение об ошибке
          $partner_id = client::getLinkedPartnerVerified($options['USER_ID']);
          if ($partner_id == $options['PARTNER_ID']) {
            global $APPLICATION;
            $APPLICATION->throwException("Нельзя добавить в черный список организатора, с которым связан покупатель!");
            return false;
          }
        }
      }
    } //проверка того, что передано корректное значение принятия
    elseif ($fields['IBLOCK_ID'] == rrsIblock::getIBlockId('counter_request_limits_changes')) {
      $number_prop_id = rrsIblock::getIBlockPropertyID('counter_request_limits_changes', 'NUMBER');
      $number_prop_value = '';
      if (isset($fields['PROPERTY_VALUES'][$number_prop_id]['0']['VALUE'])) {
        $number_prop_value = $fields['PROPERTY_VALUES'][$number_prop_id]['0']['VALUE'];
      } elseif (isset($fields['PROPERTY_VALUES'][$number_prop_id]['n0']['VALUE'])) {
        $number_prop_value = $fields['PROPERTY_VALUES'][$number_prop_id]['n0']['VALUE'];
      }

      if (filter_var($number_prop_value, FILTER_VALIDATE_INT) === false) {
        global $APPLICATION;
        $APPLICATION->throwException("Указано некорректная величина принятий (требуется целое число)");
        return false;
      }
    } //проверка того, что передано корректное значение ограничения товаров
    elseif ($fields['IBLOCK_ID'] == rrsIblock::getIBlockId('farmer_offer_limits_changes')) {
      $number_prop_id = rrsIblock::getIBlockPropertyID('farmer_offer_limits_changes', 'NUMBER');
      $number_prop_value = '';
      if (isset($fields['PROPERTY_VALUES'][$number_prop_id]['0']['VALUE'])) {
        $number_prop_value = $fields['PROPERTY_VALUES'][$number_prop_id]['0']['VALUE'];
      } elseif (isset($fields['PROPERTY_VALUES'][$number_prop_id]['n0']['VALUE'])) {
        $number_prop_value = $fields['PROPERTY_VALUES'][$number_prop_id]['n0']['VALUE'];
      }

      if (filter_var($number_prop_value, FILTER_VALIDATE_INT) === false) {
        global $APPLICATION;
        $APPLICATION->throwException("Указано некорректная величина ограничения (требуется целое число)");
        return false;
      }
    } //проверка того, что передано корректное значение ограничения запросов
    elseif ($fields['IBLOCK_ID'] == rrsIblock::getIBlockId('client_request_limits_changes')) {
      $number_prop_id = rrsIblock::getIBlockPropertyID('client_request_limits_changes', 'NUMBER');
      $number_prop_value = '';
      if (isset($fields['PROPERTY_VALUES'][$number_prop_id]['0']['VALUE'])) {
        $number_prop_value = $fields['PROPERTY_VALUES'][$number_prop_id]['0']['VALUE'];
      } elseif (isset($fields['PROPERTY_VALUES'][$number_prop_id]['n0']['VALUE'])) {
        $number_prop_value = $fields['PROPERTY_VALUES'][$number_prop_id]['n0']['VALUE'];
      }

      if (filter_var($number_prop_value, FILTER_VALIDATE_INT) === false) {
        global $APPLICATION;
        $APPLICATION->throwException("Указано некорректная величина ограничения (требуется целое число)");
        return false;
      }
    } elseif ($fields['IBLOCK_ID'] == rrsIblock::getIBlockId('farmer_profile')
      || $fields['IBLOCK_ID'] == rrsIblock::getIBlockId('client_profile')
      || $fields['IBLOCK_ID'] == rrsIblock::getIBlockId('partner_profile')
    ) {
      //проверяем - если меняется телефон в профиле АП или покупателя, то приводим его к стандартному виду
      $prop_phone_code = '';
      $prop_phone_id = 0;
      //получаем ID свойства инфоблока для телефона
      $res = CIBlockProperty::GetList(
        array('ID' => 'ASC'),
        array('IBLOCK_ID' => $fields['IBLOCK_ID'], 'CODE' => 'PHONE')
      );
      if ($data = $res->Fetch()) {
        $prop_phone_id = $data['ID'];
      }

      if ($prop_phone_id > 0
        && isset($fields['PROPERTY_VALUES'][$prop_phone_id]['n0']['VALUE'])
        && $fields['PROPERTY_VALUES'][$prop_phone_id]['n0']['VALUE'] != ''
      ) {
        $phone_digits = str_replace(array('+', '(', ')', '-', ' '), '', $fields['PROPERTY_VALUES'][$prop_phone_id]['n0']['VALUE']);
        if (strlen($phone_digits) == 10) {
          $phone_digits = '7'.$phone_digits;
        }
        if (strlen($phone_digits) == 11) {
          //приводим телефон к стандартной форме "+7 (123) 456-78-90"
          $fields['PROPERTY_VALUES'][$prop_phone_id]['n0']['VALUE'] = makeCorrectPhone($phone_digits);
        }
      }
    }
  }


  function OnBeforeIBlockElementUpdateHandler(&$fields)
  {
    if ($fields['IBLOCK_ID'] == rrsIblock::getIBlockId('blacklist_partner')) {
      if ((sizeof($fields['PROPERTY_VALUES'])) && (is_array($fields['PROPERTY_VALUES']))) {
        $options = array();
        //получаем ID партнера и клиента из параметров
        foreach ($fields['PROPERTY_VALUES'] as $opt_id => $values) {
          $value = 0;
          $res = CIBlockProperty::GetByID($opt_id, $fields['IBLOCK_ID']);
          if (isset($values[key($values)])) {
            $value = $values[key($values)]['VALUE'];
          }
          if ($ar_res = $res->GetNext()) {
            if ((!empty($value)) && (isset($ar_res['CODE']))) {
              $options[$ar_res['CODE']] = $value;
            }
          }
        }
        if ((isset($options['PARTNER_ID'])) && (isset($options['USER_ID']))) {
          if (trim($fields['NAME']) == 'Элемент черного списка') {
            $fields['NAME'] = 'Организатор ['.$options['PARTNER_ID'].'] в черном списке покупателя ['.$options['USER_ID'].']';
          }
          //проверяем принадлежность покупателя партнеру
          //если клиент принадлежит организатору, то выводим сообщение об ошибке
          $partner_id = client::getLinkedPartnerVerified($options['USER_ID']);
          if ($partner_id == $options['PARTNER_ID']) {
            global $APPLICATION;
            $APPLICATION->throwException("Нельзя добавить в черный список организатора, с которым связан покупатель!");
            return false;
          }
        }
      }
    } elseif ($fields['IBLOCK_ID'] == rrsIblock::getIBlockId('farmer_profile')
      || $fields['IBLOCK_ID'] == rrsIblock::getIBlockId('client_profile')
//            || $fields['IBLOCK_ID'] == rrsIblock::getIBlockId('agent_profile')
//            || $fields['IBLOCK_ID'] == rrsIblock::getIBlockId('client_agent_profile')
      || $fields['IBLOCK_ID'] == rrsIblock::getIBlockId('partner_profile')
    ) {
      //проверяем - если меняется телефон в профиле АП или покупателя, то приводим его к стандартному виду
      $prop_phone_code = '';
      $prop_phone_id = '';
      //получаем ID свойства инфоблока для телефона
      $res = CIBlockProperty::GetList(
        array('ID' => 'ASC'),
        array('IBLOCK_ID' => $fields['IBLOCK_ID'], 'CODE' => 'PHONE')
      );
      if ($data = $res->Fetch()) {
        $prop_phone_id = $data['ID'];
      }

      //приводим код свойства к виду "<$fields[ID]>:<$property_id>" (как свойство хранится в $fields['PROPERTY_VALUES'])
      if ($prop_phone_id > 0) {
        $prop_phone_code = $fields['ID'].':'.$prop_phone_id;
      }

      if ($prop_phone_code != ''
        && isset($fields['PROPERTY_VALUES'][$prop_phone_id][$prop_phone_code]['VALUE'])
        && $fields['PROPERTY_VALUES'][$prop_phone_id][$prop_phone_code]['VALUE'] != ''
      ) {
        $phone_digits = str_replace(array('+', '(', ')', '-', ' '), '', $fields['PROPERTY_VALUES'][$prop_phone_id][$prop_phone_code]['VALUE']);
        if (strlen($phone_digits) == 10) {
          $phone_digits = '7'.$phone_digits;
        }
        if (strlen($phone_digits) == 11) {
          //приводим телефон к стандартной форме "+7 (123) 456-78-90"
          $fields['PROPERTY_VALUES'][$prop_phone_id][$prop_phone_code]['VALUE'] = makeCorrectPhone($phone_digits);
        }
      }

      //запрещаем менять значение счетчика принятия напрямую в профиле покупателя в админке
      if ($fields['IBLOCK_ID'] == rrsIblock::getIBlockId('client_profile')
        && isset($_GET['IBLOCK_ID'])
        && $_GET['IBLOCK_ID'] == $fields['IBLOCK_ID'] //если находимся в админке на странице элемента профился или списка профилей
      ) {
        $cont_req_limit_prop_id = rrsIblock::getIBlockPropertyID('client_profile', 'COUNTER_REQUEST_LIMIT');

        //новое значение
        $new_val = '';
        if (isset($fields['PROPERTY_VALUES'][$cont_req_limit_prop_id][$fields['ID'].':'.$cont_req_limit_prop_id]['VALUE'])) {
          $new_val = $fields['PROPERTY_VALUES'][$cont_req_limit_prop_id][$fields['ID'].':'.$cont_req_limit_prop_id]['VALUE'];
        } elseif (isset($fields['PROPERTY_VALUES'][$cont_req_limit_prop_id][$fields['ID']]['VALUE'])) {
          $new_val = $fields['PROPERTY_VALUES'][$cont_req_limit_prop_id][$fields['ID']]['VALUE'];
        }

        //текущее значение
        $cur_val = 0;
        $res = CIBlockElement::GetList(false, array('IBLOCK_ID' => $fields['IBLOCK_ID'], 'ID' => $fields['ID']), false, false, array('PROPERTY_COUNTER_REQUEST_LIMIT'));
        if ($data = $res->Fetch()) {
          $cur_val = intval($data['PROPERTY_COUNTER_REQUEST_LIMIT_VALUE']);
        }

        //попытка изменить значение напрямую из профиля в админке
        if ($cur_val != $new_val
          && $new_val != ''
        ) {
          global $APPLICATION;
          $APPLICATION->throwException("Нельзя изменять количество принятий напрямую из профиля.");
          return false;
        }
      }
    } //запрещаем менять данные в инфоблоке счётчика принятий
    elseif ($fields['IBLOCK_ID'] == rrsIblock::getIBlockId('counter_request_limits_changes')) {
      global $APPLICATION;
      $APPLICATION->throwException("Нельзя изменять данные в записях счетчика принятий.");
      return false;
    } //запрещаем менять данные в инфоблоке "Ограничение количества товаров"
    elseif ($fields['IBLOCK_ID'] == rrsIblock::getIBlockId('farmer_offer_limits_changes')) {
      global $APPLICATION;
      $APPLICATION->throwException("Нельзя изменять данные в записях ограничений товаров.");
      return false;
    } //запрещаем менять данные в инфоблоке "Ограничение количества запроов"
    elseif ($fields['IBLOCK_ID'] == rrsIblock::getIBlockId('client_request_limits_changes')) {
      global $APPLICATION;
      $APPLICATION->throwException("Нельзя изменять данные в записях ограничений запросов.");
      return false;
    }
  }


  function OnBeforeUserLoginHandler(&$fields)
  {
    //проверяем - не с помощью телефона ли авторизуется пользователь
    //проверяем типовой вид почты
    if (strlen($fields['LOGIN']) != strlen(preg_replace('/^p[0-9]{11}\@agrohelper\.ru$/', '', $fields['LOGIN']))) {
      //пользователь авторизуется с помощью телефона
      //проверяем наличие пользователя с таким login
      $res = CUser::GetList(
        ($by = 'id'), ($order = 'asc'),
        array('LOGIN' => $fields['LOGIN'], 'ACTIVE' => 'Y'),
        array('FIELDS' => array('ID'))
      );
      if ($res->SelectedRowsCount() == 0) {
        //логин пользователя не вида "p71234567890@agrohelper.ru" (например старый или регистрировался по email)
        //проверяем пользователя по телефону
        $prepared_phone = makeCorrectPhone($fields['LOGIN'], true);
        if ($prepared_phone != '') {
          $uid = 0;
          //проверяем наличие такого пользователя по телефону среди профилей ап
          CModule::IncludeModule('iblock');
          $res = CIBlockElement::GetList(
            array('ID' => 'ASC'),
            array('IBLOCK_ID' => rrsIblock::getIBlockId('farmer_profile'), 'ACTIVE' => 'Y', 'PROPERTY_PHONE' => $prepared_phone),
            false,
            array('nTopCount' => 1),
            array('PROPERTY_USER')
          );
          if ($data = $res->Fetch()) {
            if (is_numeric($data['PROPERTY_USER_VALUE'])) {
              $uid = $data['PROPERTY_USER_VALUE'];
            }
          }
          //проверяем среди профилей ап
          if ($uid == 0) {
            $res = CIBlockElement::GetList(
              array('ID' => 'ASC'),
              array('IBLOCK_ID' => rrsIblock::getIBlockId('client_profile'), 'ACTIVE' => 'Y', 'PROPERTY_PHONE' => $prepared_phone),
              false,
              array('nTopCount' => 1),
              array('PROPERTY_USER')
            );
            if ($data = $res->Fetch()) {
              if (is_numeric($data['PROPERTY_USER_VALUE'])) {
                $uid = $data['PROPERTY_USER_VALUE'];
              }
            }
          }
          //проверяем среди агентов поставщиков
//                    if($uid == 0) {
//                        $res = CIBlockElement::GetList(
//                            array('ID' => 'ASC'),
//                            array('IBLOCK_ID' => rrsIblock::getIBlockId('agent_profile'), 'ACTIVE' => 'Y', 'PROPERTY_PHONE' => $prepared_phone),
//                            false,
//                            array('nTopCount' => 1),
//                            array('PROPERTY_USER')
//                        );
//                        if ($data = $res->Fetch()) {
//                            if (is_numeric($data['PROPERTY_USER_VALUE'])) {
//                                $uid = $data['PROPERTY_USER_VALUE'];
//                            }
//                        }
//                    }
          //проверяем среди агентов покупателей
//                    if($uid == 0) {
//                        $res = CIBlockElement::GetList(
//                            array('ID' => 'ASC'),
//                            array('IBLOCK_ID' => rrsIblock::getIBlockId('client_agent_profile'), 'ACTIVE' => 'Y', 'PROPERTY_PHONE' => $prepared_phone),
//                            false,
//                            array('nTopCount' => 1),
//                            array('PROPERTY_USER')
//                        );
//                        if ($data = $res->Fetch()) {
//                            if (is_numeric($data['PROPERTY_USER_VALUE'])) {
//                                $uid = $data['PROPERTY_USER_VALUE'];
//                            }
//                        }
//                    }
          //проверяем среди организаторов
          if ($uid == 0) {
            $res = CIBlockElement::GetList(
              array('ID' => 'ASC'),
              array('IBLOCK_ID' => rrsIblock::getIBlockId('partner_profile'), 'ACTIVE' => 'Y', 'PROPERTY_PHONE' => $prepared_phone),
              false,
              array('nTopCount' => 1),
              array('PROPERTY_USER')
            );
            if ($data = $res->Fetch()) {
              if (is_numeric($data['PROPERTY_USER_VALUE'])) {
                $uid = $data['PROPERTY_USER_VALUE'];
              }
            }
          }

          //если пользователь найден, берем логин
          if ($uid > 0) {
            $res = CUser::GetList(
              ($by = 'id'), ($order = 'asc'),
              array('ID' => $uid, 'ACTIVE' => 'Y'),
              array('FIELDS' => array('LOGIN'))
            );
            if ($data = $res->Fetch()) {
              $fields['LOGIN'] = $data['LOGIN'];
            }
          }
        }
      }
    }
  }

  function OnAfterUserAuthorizeHandler($arUser)
  {
    //после авторизации пользователя проверяем - была ли это первая авторизация
    if (isset($arUser['user_fields']['ID'])
      && is_numeric($arUser['user_fields']['ID'])
    ) {
      $u_obj = new Cuser;
      $res = $u_obj->GetList(
        ($by = 'id'), ($order = 'asc'),
        array(
          'ID' => $arUser['user_fields']['ID'],
          'UF_FIRST_LOGIN' => 1
        ),
        array('FIELDS' => array('ID'))
      );
      if ($res->SelectedRowsCount() > 0) {
        $u_obj->Update($arUser['user_fields']['ID'], array('UF_FIRST_LOGIN' => 0));
      }
    }
  }


  function OnAfterUserLoginHandler(&$fields)
  {
    if (intval($fields['USER_ID']) > 0) {
      //для установления хеш данных для авторизации из приложения используем $fields['LOGIN'] т.к. он совпадает с $fields['EMAIL']
      $user = new CUser;
      $prop = Array(
        "UF_SHA1" => sha1($fields['PASSWORD']),
        "UF_API_KEY" => Agrohelper::hashApiKey($fields['LOGIN'], sha1($fields['PASSWORD']))
      );
      $user->Update($fields['USER_ID'], $prop);
    }
  }

  function OnBeforeUserChangePasswordHandler(&$fields)
  {
    //проверяем если пользователь перешел по ссылке добавления агентом/организатором
    if (isset($_GET['change_password'])
      && $_GET['change_password'] == 'yes'
      && isset($_GET['invite_by_agent'])
      && $_GET['invite_by_agent'] == 'y'
      && isset($_POST['USER_EMAIL'])
      && check_email(trim($_POST['USER_EMAIL']))
    ) {
      $_POST['USER_EMAIL'] = trim($_POST['USER_EMAIL']);
      $u_obj = new CUser;
      $res = $u_obj->GetList(
        ($by = 'id'), ($order = 'asc'),
        array(
          'EMAIL' => $_POST['USER_EMAIL'],
          'UF_FIRST_LOGIN' => 0
        ),
        array('FIELDS' => array('ID'))
      );
      if ($res->SelectedRowsCount() > 0) {
        //дубликат почты
        global $APPLICATION;
        $APPLICATION->throwException('Указанный email уже зарегистрирован в системе');
        return false;
      }
    }
  }

  function OnBeforeUserUpdateHandler(&$fields)
  {
    //проверяем если пользователь перешел по ссылке добавления агентом/организатором
    if (isset($_GET['change_password'])
      && $_GET['change_password'] == 'yes'
      && isset($_GET['invite_by_agent'])
      && $_GET['invite_by_agent'] == 'y'
    ) {
      //устанавливаем email, если задан
      if (isset($_POST['USER_EMAIL'])
        && check_email(trim($_POST['USER_EMAIL']))
      ) {
        //проверка на дубликат почты производится в обработчике события OnBeforeUserChangePassword (т.е. в функции OnBeforeUserChangePasswordHandler)
        $fields['EMAIL'] = trim($_POST['USER_EMAIL']);
        $fields['LOGIN'] = $fields['EMAIL'];
      }

      //устанавливаем также телефон, если задан
      if (isset($_POST['USER_PHONE'])) {
        $_POST['USER_PHONE'] = makeCorrectPhone($_POST['USER_PHONE']);
        if ($_POST['USER_PHONE'] != '') {
          $u_obj = new CUser;
          $groups_arr = $u_obj->GetUserGroup($fields['ID']);
          $groups_arr = array_flip($groups_arr);
          $ib_id = 0;
          if (isset($groups_arr['9'])) {
            $ib_id = rrsIblock::getIBlockId('client_profile');
          } elseif (isset($groups_arr['11'])) {
            $ib_id = rrsIblock::getIBlockId('farmer_profile');
          }

          if ($ib_id > 0) {
            CModule::IncludeModule('iblock');
            $el_obj = new CIBlockElement;
            $el_obj->SetPropertyValuesEx($fields['ID'], $ib_id, array('PHONE' => trim($_POST['USER_PHONE'])));
            $fields['UF_FIRST_PHONE'] = 0;
          }
        }
      }

      if (isset($_POST['USER_EMAIL'])
        && check_email(trim($_POST['USER_EMAIL']))
      ) {
        $fields['LOGIN'] = $_POST['USER_EMAIL'];
      }
    }

    if (intval($fields['ID']) > 0 && isset($fields['PASSWORD'])) {
      //обновление хэша пароля для авторизации по телефону
      $prop_uf_api_key_mobile = userGenPhoneApiKey($fields['ID'], $fields['PASSWORD']);

      $arUser = CUser::GetByID($fields['ID'])->Fetch();
      if ($arUser['ID'] > 0) {
        $user = new CUser;
        $prop = Array(
          "UF_SHA1" => sha1($fields['PASSWORD']),
          "UF_API_KEY" => Agrohelper::hashApiKey($arUser['EMAIL'], sha1($fields['PASSWORD']))
        );
        if ($prop_uf_api_key_mobile != '') {
          $prop['UF_API_KEY_M'] = $prop_uf_api_key_mobile;
        }
        $user->Update($fields['ID'], $prop);
      }
    }

    //отправляем уведомление если поставщик стал активным
    $u_obj = new CUser;
    $arGroups = array_flip($u_obj->GetUserGroup($fields['ID']));
    if (isset($arGroups['11'])) {
      $rsUser = $u_obj->GetByID($fields['ID'])->Fetch();
      if (($fields['ACTIVE'] == 'Y') && ($rsUser['ACTIVE'] == 'N')) {
        $user_email = $fields['EMAIL'];
        //шлем уведомление организаторам
        $user_data = 'Пользователь роли поставщик зарегистрирован '.date('Y.m.d H:i').'<br/><br/>';
        $email_list = array();
        $res = $u_obj->GetList(
          ($by = 'id'), ($order = 'asc'),
          array('GROUPS_ID' => array(10)),
          array('FIELDS' => array('EMAIL', 'NAME', 'LAST_NAME', 'LOGIN'))
        );
        while ($data = $res->Fetch()) {
          $temp_name = trim($data['NAME'].' '.$data['LAST_NAME']);
          if ($temp_name == '') {
            $temp_name = $data['LOGIN'];
          }
          $email_list[] = array('EMAIL' => $data['EMAIL'], 'NAME' => $temp_name);
        }
        foreach ($email_list as $cur_data) {
          $arSendFields['RECIPIENT_DATA'] = $cur_data['NAME'];
          $arSendFields['EMAIL_LIST'] = $cur_data['EMAIL'];
          $arSendFields['USER_DATA'] = $user_data.'Email: '.$user_email;
          CEvent::Send("NEW_USER_ADD", "s1", $arSendFields);
        }

        //шлем пользователю sms
        $profile = farmer::getProfile($fields['ID'], false);
        if (sizeof($profile) > 0) {
          if (isset($profile['PROPERTY_PHONE_VALUE'])) {
            if (!empty($profile['PROPERTY_PHONE_VALUE'])) {
              $phone = str_replace(array('+', ' ', '(', ')', '-'), "", $profile['PROPERTY_PHONE_VALUE']);
              notice::sendNoticeSMS($phone, 'Ваш аккаунт в системе АГРОХЕЛПЕР подтвержден. Чтобы начать работу, перейдите по ссылке и авторизуйтесь: https://agrohelper.ru/#action=auth');
            }
          }
        }
      }
    }
  }

  function OnAfterUserUpdateHandler(&$arFields)
  {
    if (is_array($arFields['GROUP_ID']) && sizeof($arFields['GROUP_ID']) > 0) {
      $groupIds = array();
      foreach ($arFields['GROUP_ID'] as $val) {
        $groupIds[] = $val['GROUP_ID'];
      }
    }

    /*if (in_array(10, $groupIds) && $arFields['ACTIVE'] == 'N' && $arFields['UF_CONFIRM_REG'] == 1) {
        $hash = hashPass();

        $user_obj = new CUser;
        $fields = array(
            "UF_CONFIRM_REG" => 0,
            "UF_HASH" => $hash,
            "UF_HASH_INVITE" => '',
        );
        $user_obj->Update($arFields['ID'], $fields);

        $arEventFields = array(
            "EMAIL" => trim($arFields['EMAIL']),
            "HREF" => $GLOBALS['host'] . '/?reg=yes&hash=' . $hash . '#action=register',
        );

        $res_val = CEvent::Send("REG_HASH_PASSWORD_PARTNER_2", "s1", $arEventFields);
    }*/

    global $USER;
    if (!$USER->IsAuthorized() && intval($arFields['ID']) > 0 && isset($arFields['PASSWORD'])) {
      if (isset($_GET['invite_by_agent']) && $_GET['invite_by_agent'] == 'y'
        || isset($_GET['change_password']) && $_GET['change_password'] == 'yes'
      ) {
        $USER->Authorize($arFields['ID']);

        //переадресуем на нужную страницу, если задана
        if (isset($_GET['backurl'])) {
          $_GET['backurl'] = trim(urldecode($_GET['backurl']));
          if ($_GET['backurl'] != '') {
            LocalRedirect($_GET['backurl']);
            exit;
          }
        }
      }
    }
  }

  function OnAfterUserLogoutHandler($arParams)
  {
    //снимаем запоминание фильтра встречных предложений
    setcookie('count_req_filter_culture', '', time() - 1, '/');
    setcookie('count_req_filter_warehouse', '', time() - 1, '/');
    setcookie('count_req_filter_client', '', time() - 1, '/');
    setcookie('count_req_filter_region', '', time() - 1, '/');

    //флаг открытия графика в ВП
    setcookie('client_counter_graph', '', time() - 1, '/');

    //снимаем запоминание фильтра ЧС
    if (isset($_COOKIE['blacklist_filter_region_id'])) {
      setcookie('blacklist_filter_region_id', '', time() - 1, '/');
      setcookie('blacklist_filter_culture_id', '', time() - 1, '/');
      setcookie('blacklist_filter_reasond_id', '', time() - 1, '/');
    }

    //снимаем фильтр в спсике товаров поставщика
    setcookie('farmer_offer_yes_culture_id', '', time() - 1, '/');
    setcookie('farmer_offer_no_culture_id', '', time() - 1, '/');
    setcookie('farmer_offer_all_culture_id', '', time() - 1, '/');
    setcookie('farmer_offer_yes_warehouse_id', '', time() - 1, '/');
    setcookie('farmer_offer_no_warehouse_id', '', time() - 1, '/');
    setcookie('farmer_offer_all_warehouse_id', '', time() - 1, '/');
    setcookie('farmer_offer_yes_farmer_id', '', time() - 1, '/');
    setcookie('farmer_offer_no_farmer_id', '', time() - 1, '/');
    setcookie('farmer_offer_all_farmer_id', '', time() - 1, '/');
    setcookie('farmer_offer_yes_region_id', '', time() - 1, '/');
    setcookie('farmer_offer_no_region_id', '', time() - 1, '/');
    setcookie('farmer_offer_all_region_id', '', time() - 1, '/');

    //пары агентов
    if (isset($_COOKIE['deals_filter_culture_id'])) {
      setcookie('deals_filter_client_warehouse_id', '', time() - 1, '/');
      setcookie('deals_filter_farmer_warehouse_id', '', time() - 1, '/');
      setcookie('deals_filter_culture_id', '', time() - 1, '/');
      setcookie('deals_filter_client_id', '', time() - 1, '/');
      setcookie('deals_filter_farmer_id', '', time() - 1, '/');
      setcookie('deals_filter_region_id', '', time() - 1, '/');
    }

    //фильтры в складах партнера
    setcookie('partner_client_wh_yes_client_id', '', time() - 1, '/');
    setcookie('partner_client_wh_no_client_id', '', time() - 1, '/');
    setcookie('partner_client_wh_all_client_id', '', time() - 1, '/');
    setcookie('partner_farmer_wh_yes_client_id', '', time() - 1, '/');
    setcookie('partner_farmer_wh_no_client_id', '', time() - 1, '/');
    setcookie('partner_farmer_wh_all_client_id', '', time() - 1, '/');

    //фильтры в списках партнеров
    setcookie('partner_client_list_client', '', time() - 1, '/');
    setcookie('partner_client_list_link_type', '', time() - 1, '/');
    setcookie('partner_client_list_region', '', time() - 1, '/');
    setcookie('partner_client_list_culture', '', time() - 1, '/');
    setcookie('partner_farmer_list_farmer', '', time() - 1, '/');
    setcookie('partner_farmer_list_link_type', '', time() - 1, '/');
    setcookie('partner_farmer_list_region', '', time() - 1, '/');
    setcookie('partner_farmer_list_culture', '', time() - 1, '/');
  }

  function OnBeforeUserDeleteHandler($user_id)
  {
    //запретим удаление пользователя
    /*global $APPLICATION;
    $u_obj  = new CUser;
    $rsUser = $u_obj::GetByID($user_id);
    $arUser = $rsUser->Fetch();
    if(($arUser['UF_NOT_RESP']=='Y')&&($arUser['ACTIVE']=='N')){
        return true;
    }
    $APPLICATION->throwException("Пользователь не может быть удален!");
    return false;*/
  }

  /* вызывается в момент удаления пользователя */
  function OnUserDeleteHandler($user_id)
  {
    //удаляем связанные с покупателем сущности
    deleteUserData($user_id);
    global $USER;
    deleteUserLog($user_id, $USER->GetID());
  }

  function OnBeforeIBlockElementDeleteHandler($ID)
  {
    /*$res = CIBlockElement::GetByID($ID);
    if ($arRes = $res->GetNext()) {
        if ($arRes['IBLOCK_ID'] == rrsIblock::getIBlockId('cultures')) {
            //удаляется культура
            global $APPLICATION;
            $APPLICATION->throwException("Культура не может быть удалена!");
            return false;
        }
        elseif ($arRes['IBLOCK_ID'] == rrsIblock::getIBlockId('regions_centers')) {
            //удаляется РЦ
            global $APPLICATION;
            $APPLICATION->throwException("Региональный центр не может быть удалён!");
            return false;
        }
        elseif ($arRes['IBLOCK_ID'] == rrsIblock::getIBlockId('deals_deals')) {
            //удаляется сделка
            global $APPLICATION;
            $APPLICATION->throwException("Сделка не может быть удалена!");
            return false;
        }
        elseif ($arRes['IBLOCK_ID'] == rrsIblock::getIBlockId('client_profile')) {
            //удаляется профиль покупателя
            global $APPLICATION;
            $APPLICATION->throwException("Профиль покупателя не может быть удалён!");
            return false;
        }
        elseif ($arRes['IBLOCK_ID'] == rrsIblock::getIBlockId('client_warehouse')) {
            //удаляется склад покупателя
            //проверяем, есть ли сделки с этим складом
            $rsDeals = CIBlockElement::GetList(
                array('ID' => 'ASC'),
                array('IBLOCK_ID' => rrsIblock::getIBlockId('deals_deals'), 'PROPERTY_CLIENT_WAREHOUSE' => $arRes['ID']),
                false,
                false,
                array('ID')
            );
            if (intval($rsDeals->SelectedRowsCount()) > 0) {
                global $APPLICATION;
                $APPLICATION->throwException("Данный склад используется в сделках и не может быть удалён!");
                return false;
            }

            //проверяем, есть ли запросы с этим складом
            $rsCost = CIBlockElement::GetList(
                array('ID' => 'ASC'),
                array('IBLOCK_ID' => rrsIblock::getIBlockId('client_request_cost'), 'PROPERTY_WAREHOUSE' => $arRes['ID']),
                false,
                false,
                array('ID')
            );
            if (intval($rsCost->SelectedRowsCount()) > 0) {
                global $APPLICATION;
                $APPLICATION->throwException("Данный склад используется в запросах покупателя и не может быть удалён!");
                return false;
            }

            //удаляем привязки склада к РЦ
            $rsReg = CIBlockElement::GetList(
                array('ID' => 'ASC'),
                array('IBLOCK_ID' => rrsIblock::getIBlockId('client_warehouse_rc'), 'PROPERTY_WAREHOUSE' => $arRes['ID']),
                false,
                false,
                array('ID')
            );
            while ($arElement = $rsReg->GetNext()) {
                CIBlockElement::Delete($arElement['ID']);
            }

            //удаляем записи с данным складом из кеша расстояний
            $entityDataClass = log::getEntityDataClass(10);
            $el = new $entityDataClass;
            $rsData = $el->getList(array(
                'select' => array('ID'),
                'filter' => array(
                    'UF_CLIENT_WH_ID' => $arRes['ID']
                ),
                'order' => array('ID'=>'ASC')
            ));
            while ($arElement = $rsData->fetch()) {
                $el->delete($arElement['ID']);
            }
        }
        elseif ($arRes['IBLOCK_ID'] == rrsIblock::getIBlockId('client_request')) {
            //удаляется запрос
            //проверяем, есть ли сделки с этим запросом
            $rsDeals = CIBlockElement::GetList(
                array('ID' => 'ASC'),
                array('IBLOCK_ID' => rrsIblock::getIBlockId('deals_deals'), 'PROPERTY_REQUEST' => $arRes['ID']),
                false,
                false,
                array('ID')
            );
            if (intval($rsDeals->SelectedRowsCount()) > 0) {
                global $APPLICATION;
                $APPLICATION->throwException("Данный запрос участвует в сделках и не может быть удалён!");
                return false;
            }

            //удаляем характеристики запроса
            $rsChar = CIBlockElement::GetList(
                array('ID' => 'ASC'),
                array('IBLOCK_ID' => rrsIblock::getIBlockId('client_request_chars'), 'PROPERTY_REQUEST' => $arRes['ID']),
                false,
                false,
                array('ID')
            );
            while ($arElement = $rsChar->GetNext()) {
                CIBlockElement::Delete($arElement['ID']);
            }

            //удаляем стоимости запроса
            $rsCost = CIBlockElement::GetList(
                array('ID' => 'ASC'),
                array('IBLOCK_ID' => rrsIblock::getIBlockId('client_request_cost'), 'PROPERTY_REQUEST' => $arRes['ID']),
                false,
                false,
                array('ID')
            );
            while ($arElement = $rsCost->GetNext()) {
                CIBlockElement::Delete($arElement['ID']);
            }

            //удаляем пары, в которых участвует данный запрос
            $entityDataClass = log::getEntityDataClass(8);
            $el = new $entityDataClass;
            $rsData = $el->getList(array(
                'select' => array('ID'),
                'filter' => array(
                    'UF_REQUEST_ID' => $arRes['ID']
                ),
                'order' => array('ID'=>'ASC')
            ));
            while ($arElement = $rsData->fetch()) {
                $el->delete($arElement['ID']);
            }
        }
        elseif ($arRes['IBLOCK_ID'] == rrsIblock::getIBlockId('farmer_profile')) {
            //удаляется профиль АП
            global $APPLICATION;
            $APPLICATION->throwException("Профиль поставщика не может быть удалён!");
            return false;
        }
        elseif ($arRes['IBLOCK_ID'] == rrsIblock::getIBlockId('farmer_warehouse')) {
            //удаляется склад покупателя
            //проверяем, есть ли сделки с этим складом
            $rsDeals = CIBlockElement::GetList(
                array('ID' => 'ASC'),
                array('IBLOCK_ID' => rrsIblock::getIBlockId('deals_deals'), 'PROPERTY_FARMER_WAREHOUSE' => $arRes['ID']),
                false,
                false,
                array('ID')
            );
            if (intval($rsDeals->SelectedRowsCount()) > 0) {
                global $APPLICATION;
                $APPLICATION->throwException("Данный склад используется в сделках и не может быть удалён!");
                return false;
            }

            //проверяем, есть ли товары с этим складом
            $rsCost = CIBlockElement::GetList(
                array('ID' => 'ASC'),
                array('IBLOCK_ID' => rrsIblock::getIBlockId('farmer_offer'), 'PROPERTY_WAREHOUSE' => $arRes['ID']),
                false,
                false,
                array('ID')
            );
            if (intval($rsCost->SelectedRowsCount()) > 0) {
                global $APPLICATION;
                $APPLICATION->throwException("Данный склад используется в товарах поставщика и не может быть удалён!");
                return false;
            }

            //удаляем записи с данным складом из кеша расстояний
            $entityDataClass = log::getEntityDataClass(10);
            $el = new $entityDataClass;
            $rsData = $el->getList(array(
                'select' => array('ID'),
                'filter' => array(
                    'UF_FARMER_WH_ID' => $arRes['ID']
                ),
                'order' => array('ID'=>'ASC')
            ));
            while ($arElement = $rsData->fetch()) {
                $el->delete($arElement['ID']);
            }
        }
        elseif ($arRes['IBLOCK_ID'] == rrsIblock::getIBlockId('farmer_offer')) {
            //удаляется товар
            //проверяем, есть ли сделки с этим товаром
            $rsDeals = CIBlockElement::GetList(
                array('ID' => 'ASC'),
                array('IBLOCK_ID' => rrsIblock::getIBlockId('deals_deals'), 'PROPERTY_OFFER' => $arRes['ID']),
                false,
                false,
                array('ID')
            );
            if (intval($rsDeals->SelectedRowsCount()) > 0) {
                global $APPLICATION;
                $APPLICATION->throwException("Данный товар учавствует в сделках и не может быть удален!");
                return false;
            }

            //удаляем характеристики товара
            $rsChar = CIBlockElement::GetList(
                array('ID' => 'ASC'),
                array('IBLOCK_ID' => rrsIblock::getIBlockId('farmer_offer_chars'), 'PROPERTY_OFFER' => $arRes['ID']),
                false,
                false,
                array('ID')
            );
            while ($arElement = $rsChar->GetNext()) {
                CIBlockElement::Delete($arElement['ID']);
            }

            //удаляем пары, в которых участвует данный товар
            $entityDataClass = log::getEntityDataClass(8);
            $el = new $entityDataClass;
            $rsData = $el->getList(array(
                'select' => array('ID'),
                'filter' => array(
                    'UF_OFFER_ID' => $arRes['ID']
                ),
                'order' => array('ID'=>'ASC')
            ));
            while ($arElement = $rsData->fetch()) {
                $el->delete($arElement['ID']);
            }
        }
        elseif ($arRes['IBLOCK_ID'] == rrsIblock::getIBlockId('partner_profile')) {
            //удаляется профиль организатора
            global $APPLICATION;
            $APPLICATION->throwException("Профиль организатора не может быть удалён!");
            return false;
        }
        elseif ($arRes['IBLOCK_ID'] == rrsIblock::getIBlockId('transport_profile')) {
            //удаляется профиль ТК
            global $APPLICATION;
            $APPLICATION->throwException("Профиль перевозчика не может быть удалён!");
            return false;
        }
        elseif ($arRes['IBLOCK_ID'] == rrsIblock::getIBlockId('counter_request_limits_changes')) {
            //удаляется профиль ТК
            global $APPLICATION;
            $APPLICATION->throwException("Запись в счетчике принятий не может быть удалена.");
            return false;
        }
        elseif ($arRes['IBLOCK_ID'] == rrsIblock::getIBlockId('farmer_offer_limits_changes')) {
            //удаляется профиль ТК
            global $APPLICATION;
            $APPLICATION->throwException("Запись в ограничении товаров не может быть удалена.");
            return false;
        }
        elseif ($arRes['IBLOCK_ID'] == rrsIblock::getIBlockId('client_request_limits_changes')) {
            //удаляется профиль ТК
            global $APPLICATION;
            $APPLICATION->throwException("Запись в ограничении запросов не может быть удалена.");
            return false;
        }
    }*/
  }


  /**
   * Обработка элементов сущности "Региональные менеджеры"->"Привязка к организаторам"
   * @param $arFields
   * @return bool
   */
  public function setBindRegionalManagerToOrganizer(&$arFields)
  {

    if ($arFields['IBLOCK_ID'] == getIBlockID('REGIONAL_MANAGERS', 'BIND_REGIONAL_TO_ORGANIZERS')) {

      // 1) Формируем наименование элемента
      // 2) Проверяем связь на уникальность

      try {

        // Получаем св-во: "Региональный менеджер"
        $arPropRegionalManager = CIBlockProperty::GetList(
          [],
          [
            'IBLOCK_ID' => $arFields['IBLOCK_ID'],
            'CODE' => 'REGIONAL_MANAGER',
          ]
        )->Fetch();

        if (empty($arPropRegionalManager['ID'])) {
          throw new Exception('Не удалось получить св-во "Региональный менеджер"');
        }

        $sKeyProp = 'n0';
        if (!empty($arFields['ID'])) {
          $sKeyProp = $arFields['ID'].':'.$arPropRegionalManager['ID'];
        }

        $iRegionalManagerId = intval($arFields['PROPERTY_VALUES'][$arPropRegionalManager['ID']][$sKeyProp]['VALUE']);
        if (empty($iRegionalManagerId)) {
          throw new Exception('Не задан "Региональный менеджер"');
        }


        // Получаем св-во: "Организатор"
        $arPropOrganizer = CIBlockProperty::GetList(
          [],
          [
            'IBLOCK_ID' => $arFields['IBLOCK_ID'],
            'CODE' => 'ORGANIZER',
          ]
        )->Fetch();

        if (empty($arPropOrganizer['ID'])) {
          throw new Exception('Не удалось получить св-во "Организатор"');
        }

        $sKeyProp = 'n0';
        if (!empty($arFields['ID'])) {
          $sKeyProp = $arFields['ID'].':'.$arPropOrganizer['ID'];
        }

        $iOrganizerId = intval($arFields['PROPERTY_VALUES'][$arPropOrganizer['ID']][$sKeyProp]['VALUE']);
        if (empty($iOrganizerId)) {
          throw new Exception('Не задан "Организатор"');
        }

        $arFields['NAME'] = 'Привязка регионального менеджера['.$iRegionalManagerId.'] к организатору['.$iOrganizerId.']';

        // Проверяем связку на уникальность
        $arFilter = [
          'IBLOCK_ID' => $arFields['IBLOCK_ID'],
          'ACTIVE' => 'Y',
          'PROPERTY_REGIONAL_MANAGER' => $iRegionalManagerId,
          'PROPERTY_ORGANIZER' => $iOrganizerId,
        ];

        if (!empty($arFields['ID'])) {
          $arFilter['!ID'] = $arFields['ID'];
        }

        $arEl = CIBlockElement::GetList([], $arFilter, false, false, ['ID',])->Fetch();

        if (!empty($arEl)) {
          throw new Exception('Элемент "'.$arFields['NAME'].'" уже существует');
        }

        // Проверяем организатора на наличие привязки к нему регионального менеджера (Может быть только одна привязка!)
        $arFilter = [
          'IBLOCK_ID' => $arFields['IBLOCK_ID'],
          'ACTIVE' => 'Y',
          'PROPERTY_ORGANIZER' => $iOrganizerId,
        ];

        if (!empty($arFields['ID'])) {
          $arFilter['!ID'] = $arFields['ID'];
        }

        $arEl = CIBlockElement::GetList([], $arFilter, false, false, ['ID', 'NAME'])->Fetch();

        if (!empty($arEl)) {
          throw new Exception('К организатору "['.$iOrganizerId.']" уже есть привязка у элемента "'.$arEl['NAME'].'" ID['.$arEl['ID'].']');
        }

      } catch (Exception $e) {
        $GLOBALS['APPLICATION']->ThrowException($e->getMessage());
        return false;
      }
    }
  }

  public static function executeHTTPRequest($queryUrl, array $params = array())
  {
    $result = array();
    $queryData = http_build_query($params);
    $curl = curl_init();
    curl_setopt_array($curl, array(
      CURLOPT_SSL_VERIFYPEER => 0,
      CURLOPT_POST => 1,
      CURLOPT_HEADER => 0,
      CURLOPT_RETURNTRANSFER => 1,
      CURLOPT_URL => $queryUrl,
      CURLOPT_POSTFIELDS => $queryData,
    ));

    $curlResult = curl_exec($curl);
    curl_close($curl);

    if ($curlResult != '') $result = json_decode($curlResult, true);

    return $result;
  }

  public static function getEntity($method, $array = array())
  {
    $url = "https://crm.agrohelper.ru/rest/1/8zaozztcmwxi9zon/".$method.'.json?';
    return self::executeHTTPRequest($url, $array);

  }

  public static function writeToLog($data)
  {
    $log = "\n------------------------\n";
    $log .= print_r($data, 1);
    $log .= "\n------------------------\n";
    //print_r($_SERVER['DOCUMENT_ROOT']);
    file_put_contents($_SERVER['DOCUMENT_ROOT'].'/local/php_interface/test.log', $log, FILE_APPEND);
    return true;
  }

  public function createCompany(&$arFields)
  {

    if (CModule::IncludeModule('main')) {
      $user = \CUser::GetByID($arFields['ID'])->Fetch();
      if (CModule::IncludeModule('iblock')) {
        $iblock = new \CIBlockElement(false);
        if (array_search(9, $arFields['GROUP_ID'])) {
          $arFilter = array("IBLOCK_ID" => '13', "PROPERTY_95_VALUE" => (int)$arFields['ID']);
          $arSelect = array('*');
          $result = $iblock->GetList(array(), $arFilter, false, false, $arSelect);
          while ($rows = $result->Fetch()) {
            $names[] = $rows['NAME'];
            $results[] = $rows['ID'];
          }
          $values = [];
          foreach ($results as $key => $value) {
            $values[] = $iblock->GetPropertyValues(13, ['ID' => $value], true, [])->Fetch();
          }
          $type = '';
          $title = '';

          switch ($values[0]['388']) {
            case '114':
              $type = 'ИП';
              $title = $values[0]['389'];
              break;
            case '115':
              $type = 'Юр.лицо';
              $title = $values[0]['127'];
              break;
          }
          $companyFields = [
            'TITLE' => $type.' '.$title,

          ];
          $company = self::getEntity('crm.company.add', ['fields' => $companyFields]);
          self::writeToLog($company);
          $requisiteFields = [
            'ENTITY_TYPE_ID' => 4,
            'ENTITY_ID' => $company,
            "PRESET_ID" => 1,
            "NAME" => "Requisite",
            'RQ_INN' => $values[0]['132'],
            'RQ_KPP' => $values[0]['133'],
            'RQ_OGRN' => $values[0]['134'],
            'RQ_OKPO' => $values[0]['320'],
            'RQ_COMPANY_FULL_NAME' => $values[0]['127'],
            'RQ_NAME' => $values[0]['392'],

          ];
          $requisite = self::getEntity('crm.requisite.add', ['fields' => $requisiteFields])['result'];
          $bankFields = [
            "ENTITY_ID" => $requisite,
            "COUNTRY_ID" => 1,
            "NAME" => "Реквизит банка",
            "XML_ID" => "1e4641fd-2dd9-31e6-b2f2-105056c00008",
            "ACTIVE" => "Y",
            'RQ_BANK_NAME' => $values[0]['138'],
            'RQ_BIK' => $values[0]['139'],
            'RQ_ACC_NUM' => $values[0]['140'],
            'RQ_COR_ACC_NUM' => $values[0]['141'],
          ];
          $requisite1 = self::getEntity('crm.requisite.bankdetail.add', ['fields' => $bankFields]);
          $contactFields = [
            'LAST_NAME' => $user['LAST_NAME'],
            'NAME' => $user['NAME'],
            'EMAIL' => [['VALUE' => $user['EMAIL'], 'VALUE_TYPE' => 'WORK']],
            'PHONE' => [['VALUE' => $values[0]['130'], 'VALUE_TYPE' => 'WORK']],
            'COMPANY_ID' => [$company]
          ];
          self::getEntity('crm.contact.add', ['fields' => $contactFields])['result'];
        } else {
          $arFilter = array("IBLOCK_ID" => '19', "PROPERTY_167_VALUE" => $arFields['ID']);
          $arSelect = array('*');
          $result = $iblock->GetList(array(), $arFilter, false, false, $arSelect);
          while ($rows = $result->Fetch()) {
            $names[] = $rows['NAME'];
            $results[] = $rows['ID'];
          }
          $values = [];
          foreach ($results as $key => $value) {
            $values[] = $iblock->GetPropertyValues(19, ['ID' => $value], true, [])->Fetch();
          }
          $type = '';
          $title = '';

          switch ($values[0]['391']) {
            case '117':
              $type = 'ИП';
              $title = $values[0]['392'];
              break;
            case '116':
              $type = 'Юр.лицо';
              $title = $values[0]['171'];
              break;
          }
          $companyFields = [
            'TITLE' => $type.' '.$title,

          ];
          $company = self::getEntity('crm.company.add', ['fields' => $companyFields]);
          self::writeToLog($company);
          $requisiteFields = [
            'ENTITY_TYPE_ID' => 4,
            'ENTITY_ID' => $company,
            "PRESET_ID" => 1,
            "NAME" => "Requisite",
            'RQ_INN' => $values[0]['170'],
            'RQ_KPP' => $values[0]['174'],
            'RQ_OGRN' => $values[0]['322'],
            'RQ_OKPO' => $values[0]['175'],
            'RQ_COMPANY_FULL_NAME' => $values[0]['171'],
            'RQ_NAME' => $values[0]['392'],

          ];

          $requisite = self::getEntity('crm.requisite.add', ['fields' => $requisiteFields])['result'];
          $bankFields = [
            "ENTITY_ID" => $requisite,
            "COUNTRY_ID" => 1,
            "NAME" => "Реквизит банка",
            "XML_ID" => "1e4641fd-2dd9-31e6-b2f2-105056c00008",
            "ACTIVE" => "Y",
            'RQ_BANK_NAME' => $values[0]['182'],
            'RQ_BIK' => $values[0]['183'],
            'RQ_ACC_NUM' => $values[0]['184'],
            'RQ_COR_ACC_NUM' => $values[0]['185'],
          ];
          $requisite1 = self::getEntity('crm.requisite.bankdetail.add', ['fields' => $bankFields]);
          $contactFields = [
            'LAST_NAME' => $user['LAST_NAME'],
            'NAME' => $user['NAME'],
            'EMAIL' => [['VALUE' => $user['EMAIL'], 'VALUE_TYPE' => 'WORK']],
            'PHONE' => [['VALUE' => $values[0]['179'], 'VALUE_TYPE' => 'WORK']],
            'COMPANY_IDS' => [$company]
          ];
          self::getEntity('crm.contact.add', ['fields' => $contactFields])['result'];
        }


        //self::writeToLog($values);
      }

    }
  }
}