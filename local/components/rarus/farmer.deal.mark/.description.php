<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("DEALS_COMPONENTS_NAME"),
	"DESCRIPTION" => GetMessage("DEALS_COMPONENTS_DESC"),
	"SORT" => 20,
	"CACHE_PATH" => "Y",
	"PATH" => array(
		"ID" => "deals",
        "NAME" => GetMessage("DEALS_COMPONENTS"),
	),
);

?>