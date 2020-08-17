<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
/** @var array $arCurrentValues */

if(!CModule::IncludeModule("iblock"))
	return;

$arComponentParameters = array(
	"PARAMETERS" => array(
		"USER_ID" => array(
			"NAME" => "ID пользователя",
			"TYPE" => "STRING",
			"DEFAULT" => "",
		),
        "USER_TYPE" => array(
            "NAME" => "Тип пользователя (client или farmer)",
            "TYPE" => "STRING",
            "DEFAULT" => "client",
        ),
	),
);
