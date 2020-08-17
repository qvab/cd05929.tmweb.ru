<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
$arComponentParameters = array(
    "GROUPS" => array(),
    "PARAMETERS" => array(
        "MODE" => array(
            "PARENT" => "BASE",
            "NAME" => "Режим работы (admin, partner, reg_manager)",
            "TYPE" => "STRING",
            "MULTIPLE" => "N",
            "DEFAULT" => ""
        ),
        "UID" => array(
            "PARENT" => "BASE",
            "NAME" => "ID пользователя",
            "TYPE" => "INTEGER",
            "MULTIPLE" => "N",
            "DEFAULT" => "0"
        ),
        "USER_TYPE" => array(
            "PARENT" => "BASE",
            "NAME" => "Тип пользователя (client, farmer, transport)",
            "TYPE" => "STRING",
            "MULTIPLE" => "N",
            "DEFAULT" => ""
        ),
        "SHOW_TYPE" => array(
            "PARENT" => "BASE",
            "NAME" => "Тип режима (пусто - демо и не демо пользователи, demo - демо-режим, not_demo - не демо-режим, no_data - без товаров или без запросов)",
            "TYPE" => "STRING",
            "MULTIPLE" => "N",
            "DEFAULT" => ""
        ),
        "AGENT_ID" => array(
            "PARENT" => "BASE",
            "NAME" => "ID агента",
            "TYPE" => "INTEGER",
            "MULTIPLE" => "N",
            "DEFAULT" => "0"
        ),
        "PARTNER_ID" => array(
            "PARENT" => "BASE",
            "NAME" => "ID организатора",
            "TYPE" => "INTEGER",
            "MULTIPLE" => "N",
            "DEFAULT" => "0"
        ),
    ),
);