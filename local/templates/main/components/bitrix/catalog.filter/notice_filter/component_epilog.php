<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();?>
<?
//если перешли на новые с других страниц
if(!isset($_GET['read'])){
    $el = new CIBlockElement;
    $res = $el->GetList(array('ID' => 'DESC'), array(
        'IBLOCK_TYPE' => 'services',
        'IBLOCK_ID' => 51,
        'PROPERTY_USER'=>$USER->GetID(),
        'PROPERTY_READ' => 'N'
    ),
        false,
        false,
        array('ID'));
    if($res->SelectedRowsCount()==0){
        //если нет новых, то открываем вкладку с прочитанными
        if(!empty($arResult['FORM_ACTION'])){
            $url_arr = explode('?',$arResult['FORM_ACTION']);
            if(sizeof($url_arr)){
                LocalRedirect($url_arr[0] . '?read=1');
            }
        }
    }
}
?>