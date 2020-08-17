<?php


include $_SERVER["DOCUMENT_ROOT"]."/local/php_interface/functions/Utils/CrmIntegration.php";

$crm = new CrmIntegration();
$arData = [
  "NAME" => "Михаил",
  "LAST_NAME" => "Мишанин",
  "SECOND_NAME" => "Алексеевич",
  "EMAIL" => "miha@yan.ru",


];
$res = $crm->addUser($arData);
var_dump($res);