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

$IB_ID = rrsIblock::getIBlockId('farmer_warehouse');
if(!empty($IB_ID)) {

    if ((isset($_POST['ID'])) && (!empty($_POST['ID'])) && (isset($_POST['NAME'])) && (sizeof($_POST['P'])) && (is_array($_POST['P']))) {
        //редактирование склада
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
        $PROP['FARMER'] = $USER->GetID();
        //check activation/deactivation edit
        if (isset($_POST['activate']) && $_POST['activate'] == 'y') {
            $PROP['ACTIVE'] = rrsIblock::getPropListKey('farmer_warehouse', 'ACTIVE', 'yes');
        }
        elseif (isset($_POST['deactivate']) && $_POST['deactivate'] == 'y') {
            $PROP['ACTIVE'] = rrsIblock::getPropListKey('farmer_warehouse', 'ACTIVE', 'no');
        }

        $fieldArray = Array(
            "IBLOCK_SECTION" => false,
            "NAME" => $_POST['NAME'],
            "ACTIVE" => "Y"
        );

        $res = $el->Update($_POST['ID'], $fieldArray);
        if ($res) {
            CIBlockElement::SetPropertyValuesEx($_POST['ID'], $IB_ID, $PROP);
        }
    }elseif((isset($_POST['NAME'])) && (sizeof($_POST['P'])) && (is_array($_POST['P']))){
        //Добавление нового склада
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
        $PROP['FARMER'] = $USER->GetID();
        $PROP['ACTIVE'] = rrsIblock::getPropListKey('farmer_warehouse', 'ACTIVE', 'yes');

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
LocalRedirect('/farmer/warehouses/');






