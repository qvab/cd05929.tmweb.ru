<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
    "NAME" => GetMessage("CLIENT_COMPONENTS_NAME"),
    "DESCRIPTION" => GetMessage("CLIENT_COMPONENTS_DESC"),
    "SORT" => 20,
    "CACHE_PATH" => "Y",
    "PATH" => array(
        "ID" => "client",
        "NAME" => GetMessage("CLIENT_COMPONENTS"),
    ),
);
?>