<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
/** @var CBitrixComponent $this */
/** @var array $arParams */
/** @var array $arResult */
/** @var string $componentPath */
/** @var string $componentName */
/** @var string $componentTemplate */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

/** @global CIntranetToolbar $INTRANET_TOOLBAR */
global $INTRANET_TOOLBAR;

use Bitrix\Main\Context,
	Bitrix\Main\Type\DateTime,
	Bitrix\Main\Loader,
	Bitrix\Iblock;

$arResult['ERROR'] = '';
$arResult['ERROR_MESSAGE'] = '';

if(!isset($arParams['USER_ID']) || !is_numeric($arParams['USER_ID']))
{
    $arResult['ERROR'] = 'Y';
    $arResult['ERROR_MESSAGE'] = 'Не указан ID пользователя.';
}

if(!isset($arParams['USER_TYPE']) ||
    (
        $arParams['USER_TYPE'] != 'client'
        && $arParams['USER_TYPE'] != 'farmer'
    )
)
{
    $arResult['ERROR'] = 'Y';
    $arResult['ERROR_MESSAGE'] = 'Не указан корректный тип пользователя.';
}

if(isset($_REQUEST['WCODE'])){
    $regionArray = array();
    $arResult['GRAPH_DATA']['REGION'] = 0;
    $arResult['GRAPH_DATA']['ID'] = 0;
    $arResult['GRAPH_DATA']['MAP'] = array('55.753215','37.622504');
    $arResult['REGION_NAMES'] = array();

    if ($_REQUEST['WCODE'] == 'add') {
        $IB_REG_ID = rrsIblock::getIBlockId('regions');
        if(!empty($IB_REG_ID)){
            //создание нового склада
            $arSelect = Array("ID", "NAME");
            $arFilter = Array("IBLOCK_ID"=>$IB_REG_ID, "ACTIVE"=>"Y");
            $res = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
            $toJSArray = array();
            while ($ob = $res->GetNextElement()) {
                $arFields = $ob->GetFields();
                $regionArray[$arFields['ID']] = $arFields['NAME'];
                $arResult['REGION_NAMES'][$arFields['NAME']] = $arFields['ID'];
            }
            $jsonArray = json_encode($toJSArray);
            ?>
            <script>
                regionArray = <?=$jsonArray?>;
            </script>
            <?
        }
    }

    /**
     * Получаем регион текущего пользователя
     */
    $userRegionId = 0;

    $arSelect = Array("ID", "NAME", "PROPERTY_REGION");
    $arFilter = Array(
        "IBLOCK_ID" => rrsIblock::getIBlockId('client_profile'),
        "ACTIVE" => "Y",
        "PROPERTY_USER" => $USER->GetID()
    );
    $res = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
    $toJSArray = array();

    while ($ob = $res->GetNextElement()) {
        $arFields = $ob->GetFields();
        if (!empty($arFields['PROPERTY_REGION_VALUE'])){
            $userRegionId = $arFields['PROPERTY_REGION_VALUE'];
            $arResult['GRAPH_DATA']['REGION'] = $userRegionId;
        }
    }
    if (!empty($userRegionId)) {
        if ((sizeof($regionArray))&&(is_array($regionArray))) {
            if (array_key_exists($userRegionId,$regionArray)) {
                $userRegionName = $regionArray[$userRegionId];
            }
        }
        //get user's region coordinates for start point
        if ($_REQUEST['WCODE'] == 'add') {
            $res = CIBlockElement::GetList(array('ID' => 'ASC'), array('IBLOCK_CODE' => 'regions', 'ID' => $userRegionId), false, array('nTopCount' => 1), array('PROPERTY_COORDINATES'));
            if ($data = $res->Fetch()) {
                if (trim($data['PROPERTY_COORDINATES_VALUE']) != '') {
                    $arResult['GRAPH_DATA']['MAP'] = explode(',', $data['PROPERTY_COORDINATES_VALUE']);
                }
            }
            $arResult['GRAPH_DATA']['REGION'] = $userRegionId;
        }
    }

    if($arParams['USER_TYPE'] != 'farmer') {
        $arResult['TRANSPORT_LIST'] = rrsIblock::getElementList(rrsIblock::getIBlockId('transport_type'));
    }

    $this->includeComponentTemplate();
}

