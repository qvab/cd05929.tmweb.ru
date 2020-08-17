<?php
class CrmIntegration
{

  public function addUser(&$arFields)
  {
    /*
    $response = self::__curl($arFields, "https://agro.mosinfotech18112019.ru/add_user");
    $f = fopen($_SERVER["DOCUMENT_ROOT"]."/______________test.json", "w");
    $response = fwrite($f, json_encode(["response" => $response, "arFiledls" => $arFields], JSON_UNESCAPED_UNICODE));
    fclose($f);*/
    return $arFields;
  }

  private function __curl($data, $link, $bPost = true)
  {
    $ch = curl_init();
    if (!empty($ch)) {
      curl_reset($ch);
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, $link);
    if (!empty($bPost)) {
      curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    } else {
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    }
    //curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    return curl_exec($ch);
  }
}