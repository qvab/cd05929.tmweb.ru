<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
/** @var array $arCurrentValues */

if(!CModule::IncludeModule("iblock"))
	return;

$arComponentParameters = array(
	"PARAMETERS" => array(
		"FARMER_ID" => array(
			"NAME" => GetMessage("T_FARMER_ID"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
		),
	),
);
