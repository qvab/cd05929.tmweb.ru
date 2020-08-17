<?php
/**
 * Created by PhpStorm.
 * User: bragev
 * Date: 03.04.2018
 * Time: 10:40
 */
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

if(!CModule::IncludeModule("iblock"))
    return;
global $USER;

$IB_ID = rrsIblock::getIBlockId('client_warehouse');
if(!empty($IB_ID)) {

    if ((isset($_POST['ID'])) && (!empty($_POST['ID'])) && (isset($_POST['NAME'])) && (sizeof($_POST['P'])) && (is_array($_POST['P']))) {
        $el = new CIBlockElement;
        $PROP = array();
        if (isset($_POST['P']['ADDRESS'])) {
            $PROP['ADDRESS'] = $_POST['P']['ADDRESS'];
        }
        if (isset($_POST['P']['REGION'])) {
            $PROP['REGION'] = $_POST['P']['REGION'];
        }
        if(isset($_POST['P']['MAP'])){
            if(sizeof($_POST['P']['MAP'])>1){
                $PROP['MAP'] = implode(',',$_POST['P']['MAP']);
            }
        }
        $PROP['CLIENT'] = $USER->GetID();
        //check activation/deactivation edit
        if (isset($_POST['activate']) && $_POST['activate'] == 'y') {
            $PROP['ACTIVE'] = rrsIblock::getPropListKey('client_warehouse', 'ACTIVE', 'yes');
        }
        elseif (isset($_POST['deactivate']) && $_POST['deactivate'] == 'y') {
            $PROP['ACTIVE'] = rrsIblock::getPropListKey('client_warehouse', 'ACTIVE', 'no');
        }

        /*//get closest region center (check all regions)
        if(isset($_POST['coord_change_flag']) && $_POST['coord_change_flag'] == 'y' && isset($PROP['MAP']) && trim($PROP['MAP']) != '')
        {//recount nearest region center
            $PROP['CENTER'] = rrsIblock::getNearestRegCenterID($PROP['MAP']);
        }*/

        $fieldArray = Array(
            "IBLOCK_SECTION" => false,
            "NAME" => $_POST['NAME'],
            "ACTIVE" => "Y"
        );

        $res = $el->Update($_POST['ID'], $fieldArray);
        //echo "[" . $res . "]";
        if ($res) {
            CIBlockElement::SetPropertyValuesEx($_POST['ID'], $IB_ID, $PROP);
        }
    }
    elseif((isset($_POST['NAME'])) && (sizeof($_POST['P'])) && (is_array($_POST['P']))){
        $el = new CIBlockElement;
        echo "Добавление нового";
        $PROP = array();
        if (isset($_POST['P']['ADDRESS'])) {
            $PROP['ADDRESS'] = $_POST['P']['ADDRESS'];
        }
        if (isset($_POST['P']['REGION'])) {
            $PROP['REGION'] = $_POST['P']['REGION'];
        }
        if(isset($_POST['P']['MAP'])){
            if(sizeof($_POST['P']['MAP'])>1){
                $PROP['MAP'] = implode(',',$_POST['P']['MAP']);
            }
        }
        $PROP['CLIENT'] = $USER->GetID();
        $PROP['ACTIVE'] = rrsIblock::getPropListKey('client_warehouse', 'ACTIVE', 'yes');

        $n = 0;
        foreach ($_POST['transport'] as $key => $val) {
            $PROP['TRANSPORT']["n".$n] = array("VALUE" => $key);
            $n++;
        }

        /*//get closest region center (check all regions)
        if (isset($PROP['MAP']) && trim($PROP['MAP']) != '') {
            $PROP['CENTER'] = rrsIblock::getNearestRegCenterID($PROP['MAP']);
        }*/

        $fieldArray = Array(
            "MODIFIED_BY"    => $USER->GetID(),    // элемент изменен текущим пользователем
            "IBLOCK_SECTION" => false,
            "NAME" => $_POST['NAME'],
            "IBLOCK_ID"      => $IB_ID,
            "PROPERTY_VALUES"=> $PROP,
            "ACTIVE" => "Y"
        );

        $el->Add($fieldArray);
    }
}
LocalRedirect('/client/warehouses/');






