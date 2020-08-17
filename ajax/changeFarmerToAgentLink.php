<?php
header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header ("Cache-Control: no-cache, must-revalidate");
header ("Pragma: no-cache");
header ("content-type: application/x-javascript; charset=UTF-8");
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

$result = 0;

global $USER;

//check if agent settings update is need
if($USER->IsAuthorized() && isset($_POST['update_agent_settings']) && $_POST['update_agent_settings'] == 'y')
{
    $error = '';

    if(isset($_POST['farmer_id'])
        && (filter_var($_POST['farmer_id'], FILTER_VALIDATE_INT)) !== false
        && isset($_POST['agent_id'])
        && filter_var($_POST['agent_id'], FILTER_VALIDATE_INT) !== false
    )
    {
        //check if user if user is partner
        $user_groups = CUser::GetUserGroup($USER->GetID());
        if (in_array(10, $user_groups))
        {
            CModule::IncludeModule('iblock');
            $el_obj = new CIBlockElement;
            $agentObj = new agent();

            $farmer_to_agent_link_ib = rrsIblock::getIBlockId('farmer_agent_link');

            if($_POST['agent_id'] > 0){
                //if need to set new link with agent
                $result = $agentObj->setLinkWithPartner($_POST['farmer_id'], $_POST['agent_id'], $USER->GetID(), (isset($_POST['control_id']) ? $_POST['control_id'] : ''));

            }
            elseif($_POST['agent_id'] == -1){
                //if need to drop farmer link with agent
                $result = $agentObj->dropLinkWithFarmer($_POST['farmer_id'], $USER->GetID());
            }
        }
    }
}

echo $result;