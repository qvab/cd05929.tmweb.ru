<?//Формирование хранения лучших цен по дням (из запросов покупателей) для товаров АП и дальнейших построений графиков

if(empty($_SERVER['SHELL']))
	die();

set_time_limit(12000);
//боевой
$_SERVER["DOCUMENT_ROOT"] = "/home/bitrix/www";
//песочница
//$_SERVER["DOCUMENT_ROOT"] = "/www/unorganized_projects/agrohelper/sandboxes/dmitrd/www";

define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS",true);
flush();
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
ob_end_flush();

$arOffersData = array();
$arBestPriceData = array();
$cDateTime = date('d.m.Y H:i:s');
$cDate = reset(explode(' ', $cDateTime));

$hlIblockId = 12;

//получаем данные товаров
$arResult = lead::getLeadList(array());
foreach($arResult as $cur_data){

    if($cur_data['UF_CSM_PRICE'] == ''){
        $cur_data['UF_CSM_PRICE'] = 0;
    }

    if(!isset($arOffersData[$cur_data['UF_OFFER_ID']])){
        $arOffersData[$cur_data['UF_OFFER_ID']] = array(
            'FARMER_ID' => $cur_data['UF_FARMER_ID'],
            'CULTURE_ID' => $cur_data['UF_CULTURE_ID'],
            'BASE_PRICE' => $cur_data['UF_BASE_CONTR_PRICE'],
            'CSM_PRICE' => $cur_data['UF_CSM_PRICE'],
            'UF_REQUEST_ID' => $cur_data['UF_REQUEST_ID'],
        );
    }else{
        if($arOffersData[$cur_data['UF_OFFER_ID']]['CSM_PRICE'] < $cur_data['UF_CSM_PRICE']){
            $arOffersData[$cur_data['UF_OFFER_ID']]['CSM_PRICE'] = $cur_data['UF_CSM_PRICE'];
            $arOffersData[$cur_data['UF_OFFER_ID']]['BASE_PRICE'] = $cur_data['UF_BASE_CONTR_PRICE'];
            $arOffersData[$cur_data['UF_OFFER_ID']]['UF_REQUEST_ID'] = $cur_data['UF_REQUEST_ID'];
        }
    }
}

//получаем данные лучших цен
$entityDataClass = log::getEntityDataClass($hlIblockId);
$el = new $entityDataClass;
$result = array();
$change_flag = false;
$rsData = $el->getList(array(
    'select' => array('*'),
    'filter' => array('UF_DATE' => $cDate)
));
while($cur_data = $rsData->fetch()) {
    if($cur_data['UF_BEST_CSM_PRICE'] == ''){
        $cur_data['UF_BEST_CSM_PRICE'] = 0;
    }

    $arBestPriceData[$cur_data['UF_OFFER_ID']] = array(
        'ID'            => $cur_data['ID'],
        'BASE_PRICE'    => $cur_data['UF_BEST_BASE_PRICE'],
        'CSM_PRICE'     => $cur_data['UF_BEST_CSM_PRICE']
    );
}

//проверяем нужно ли обновлять данные или создавать новые записи
foreach($arOffersData as $cur_offer_id => $cur_data){
    if(!isset($arBestPriceData[$cur_offer_id])){
        //нет записи в таблице лучших цен -> создаём
        log::_createEntity($hlIblockId, array(
            'UF_DATE_UPDATE'        => $cDateTime,
            'UF_DATE'               => $cDate,
            'UF_OFFER_ID'           => $cur_offer_id,
            'UF_REQUEST_ID'         => $cur_data['UF_REQUEST_ID'],
            'UF_FARMER_ID'          => $cur_data['FARMER_ID'],
            'UF_CULTURE_ID'         => $cur_data['CULTURE_ID'],
            'UF_BEST_BASE_PRICE'    => $cur_data['BASE_PRICE'],
            'UF_BEST_CSM_PRICE'     => $cur_data['CSM_PRICE'],
        ));
        $change_flag = true;
    }else{
        //есть запись товара -> возможно нужно обновление записи
        $updateArr = array();
        if($cur_data['CSM_PRICE'] == ''){
            $cur_data['CSM_PRICE'] = 0;
        }

        if($cur_data['CSM_PRICE'] > $arBestPriceData[$cur_offer_id]['CSM_PRICE']){
            //обновим цену с места
            $updateArr['UF_BEST_CSM_PRICE'] = $cur_data['CSM_PRICE'];
            $updateArr['UF_BEST_BASE_PRICE'] = $cur_data['BASE_PRICE'];
        }
        if(count($updateArr) > 0){
            $updateArr['UF_DATE_UPDATE'] = $cDateTime;
            log::_updateEntity($hlIblockId, $arBestPriceData[$cur_offer_id]['ID'], $updateArr);
            $change_flag = true;
        }
    }
}

//если появились новые данные -> сбрасываем кеш в инфоблоке
if($change_flag){
    global $CACHE_MANAGER;
    $CACHE_MANAGER->ClearByTag("iblock_id_".rrsIblock::getIBlockId('farmer_offer'));
}