<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();?>
<?
if ($_REQUEST['warehouse'] > 0) {
    $get = '';
    CModule::IncludeModule('iblock');
    if (isset($_REQUEST['deactivate'])) {
        $prop = array(
            'ACTIVE' => rrsIblock::getPropListKey('farmer_warehouse', 'ACTIVE', 'no')
        );
    }
    elseif (isset($_REQUEST['activate'])) {
        $prop = array(
            'ACTIVE' => rrsIblock::getPropListKey('farmer_warehouse', 'ACTIVE', 'yes')
        );
    }
    CIBlockElement::SetPropertyValuesEx(
        $_REQUEST['warehouse'],
        rrsIblock::getIBlockId('farmer_warehouse'),
        $prop
    );
    $el = new CIBlockElement;
    $res = $el->Update($_REQUEST['warehouse'], array('TIMESTAMP_X' => date('d.m.Y H:i:s')));

    //деактивация всех товаров на складе
    if ($_REQUEST['deactivate']) {
        $IB_WH_ID = rrsIblock::getIBlockId('farmer_warehouse');
        if($IB_WH_ID){
            //проверяем активность склада, если деактивация прошла успешно, то деактивируем товары
            $res = $el->GetList(array('ID' => 'DESC'), array(
                'IBLOCK_TYPE' => 'farmer',
                'IBLOCK_ID' => $IB_WH_ID,
                'ID'=>$_REQUEST['warehouse'],
                'PROPERTY_ACTIVE' => rrsIblock::getPropListKey('farmer_warehouse', 'ACTIVE', 'no')
            ),
                false,
                false,
                array('ID','PROPERTY_ACTIVE_VALUE'));
            if ($res->SelectedRowsCount() > 0) {
                //если склад неактивный то деактивируем товары
                $d_offer_count = farmer::setWHOfferDeactivation($_REQUEST['warehouse']);
                if($d_offer_count>0){
                    $get = 'q='.$d_offer_count;
                }
            }
        }
    }

    LocalRedirect($APPLICATION->GetCurPageParam($get, ['q',]));
}
?>
<script type="application/javascript">
    var stop_slide_anim = 0;

    $(document).ready(function() {
        $('.list_page_rows .line_area .line_inner, .list_page_rows .line_area .line_additional .hide_but').on('click', function(){
            if(stop_slide_anim == 0)
            {
                stop_slide_anim = 1;
                var wObj = $(this).parents('.line_area');
                wObj.toggleClass('active');
                if(!wObj.hasClass('active'))
                {
                    wObj.find('form.line_additional').slideUp(300, function(){
                        stop_slide_anim = 0;
                    });
                    wObj.find('.arw_list').removeClass('arw_icon_open').addClass('arw_icon_close');
                }
                else
                {
                    wObj.find('form.line_additional').slideDown(300, function(){
                        stop_slide_anim = 0;
                    });
                    wObj.find('.arw_list').removeClass('arw_icon_close').addClass('arw_icon_open');
                }
            }
        });
        <?/*if (isset($_GET['q'])) {?>
                var q = <?=$_GET['q']?>;
                if (q > 0) {
                    var mess = 'Товары по данному складу были деактивированы'
                }
                startBackMessage(mess);
        <?}*/?>

    });
    <?/*if (isset($_GET['q'])) {?>
    function startBackMessage(mess) {
        if (stop_backshad_animation == 0) {
            stop_backshad_animation = 1;
            if ($('#back_shad').length > 0) {
                $('#back_shad:first').fadeIn(300);
            }
            else {
                $('body').append('<div id="back_shad"></div>');
                $('#back_shad').fadeIn(300);
            }

            if ($('#load_mes').length > 0) {
                $('#load_img:first').fadeIn(300, function(){ stop_backshad_animation = 0;});
            }
            else {
                $('body').append('<div id="load_mes"><div>' + mess + '</div><input value="ОК" type="button" onclick="stopBackMessage();"></div>');
                $('#load_mes').fadeIn(300, function(){ stop_backshad_animation = 0;});
            }
        }

        return true;
    }

    function stopBackMessage() {
        if (stop_backshad_animation == 0) {
            stop_backshad_animation = 1;

            if ($('#back_shad').length > 0) {
                $('#back_shad:first').fadeOut(300);
            }

            if ($('#load_mes').length > 0) {
                $('#load_mes:first').fadeOut(300, function(){
                    stop_backshad_animation = 0;
                    $('#load_mes').remove();
                });
            }
        }
        return true;
    }
    <?}*/?>

    function closeSubmPopup(){
        $('#popup_phone_num').remove();
        $('#back_shad').hide();
    }

    function sendSubmPopup(whID)
    {
        var wInp = $('input[type="hidden"][name="warehouse"][value="' + whID + '"]');
        if(wInp.length == 1) {
            var wForm = wInp.parents('form.line_additional');
            if(wForm.length == 1){
                wForm.attr('approved', 'y');
                wForm.find('input[type="submit"][name="deactivate"]').trigger('click');
            }
        }
    }

    function initWHMap(){
        //старт отображения карты
        var map1, map2, mapObj, wObj;
        $('.list_page_rows.warehouses form.line_additional').each(function(ind, cObj){
            map1 = 0;
            map2 = 0;
            if(typeof $(cObj).attr('data-lat') != 'undefined'){
                map1 = parseFloat($(cObj).attr('data-lat'));
            }
            if(typeof $(cObj).attr('data-lng') != 'undefined'){
                map2 = parseFloat($(cObj).attr('data-lng'));
            }

            if(map1 != 0 && map2 != 0){
                wObj = $(cObj).find('input[type="hidden"][name="warehouse"]');
                if(wObj.length == 1){
                    mapObj = $(cObj).find('#myMap' + wObj.val());
                    if(mapObj.length == 1){
                        gMap = new google.maps.Map(document.getElementById('myMap' + wObj.val()), {
                            center: {lat: map1, lng: map2},
                            zoom: 12,
                            streetViewControl: false,
                            rotateControl: false,
                            fullscreenControl: false
                        });
                        gMarker = new google.maps.Marker({
                            position: {lat: map1, lng: map2},
                            map: gMap,
                            title: "",
                            icon: '/images/blu-blank.png'
                        });
                    }
                }

            }
        });
    }
</script>