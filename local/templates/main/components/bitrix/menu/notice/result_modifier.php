<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if((sizeof($arResult))&&(is_array($arResult))){
    $el = new CIBlockElement;
    for($i=0,$c=sizeof($arResult);$i<$c;$i++){
        if(isset($arResult[$i]['PARAMS']['READ'])){
            $res = $el->GetList(array('ID' => 'DESC'), array(
                'IBLOCK_TYPE' => 'services',
                'IBLOCK_ID' => rrsIblock::getIBlockId('user_notice'),
                'PROPERTY_USER'=>$USER->GetID(),
                'PROPERTY_READ' => $arResult[$i]['PARAMS']['READ']
            ),
                false,
                false,
                array('ID'));
            $arResult[$i]['COUNT'] = $res->SelectedRowsCount();
        }
    }
}
?>