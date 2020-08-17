<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
    "NAME" => GetMessage("LEADS_COMPONENTS_NAME"),
    "DESCRIPTION" => GetMessage("LEADS_COMPONENTS_DESC"),
	"SORT" => 30,
	"CACHE_PATH" => "Y",
	"PATH" => array(
		"ID" => "leads",
        "NAME" => GetMessage("LEADS_COMPONENTS"),
	),
);

?>