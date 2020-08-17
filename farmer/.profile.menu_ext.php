<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$aMenuLinksExt = [];

$obAgent    = new agent;
$obUser     = new CUser;
$iUserId    = $obUser->GetID();

$arFarmerAgents = $obAgent->getAgentsByFarmers($iUserId);

if(!empty($arFarmerAgents[$iUserId])) {
    $aMenuLinksExt[] = [
        "Об агенте",
        "/farmer/agent_info/",
        Array(),
        "",
        "",
    ];
}

$aMenuLinks = array_merge($aMenuLinks, $aMenuLinksExt);