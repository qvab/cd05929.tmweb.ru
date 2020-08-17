<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
$arComponentParameters = array(
    "GROUPS" => array(),
    "PARAMETERS" => array(
        "PARTNER_ID" => array(
            "PARENT" => "BASE",
            "NAME" => "ID организатора",
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