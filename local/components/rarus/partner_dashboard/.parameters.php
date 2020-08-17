<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
$arComponentParameters = array(
    "GROUPS" => array(),
    "PARAMETERS" => array(
        "UID" => array(
            "PARENT" => "BASE",
            "NAME" => "ID пользователя",
            "TYPE" => "INTEGER",
            "MULTIPLE" => "N",
            "DEFAULT" => "0"
        ),
        "AGENT_ID" => array(
            "PARENT" => "BASE",
            "NAME" => "ID агента",
            "TYPE" => "INTEGER",
            "MULTIPLE" => "N",
            "DEFAULT" => "0"
        ),
    ),
);