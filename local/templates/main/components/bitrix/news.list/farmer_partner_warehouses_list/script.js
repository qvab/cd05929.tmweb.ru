var stop_slide_anim = 0;

$(document).ready(function() {
    $('.ch_all_tr').on('click', function(e){
        $(this).parents('form').find('input[type="checkbox"]:not(:checked)').trigger('click');
        e.preventDefault();
    });

    $('.no_tr').on('click', function(e){
        $(this).parents('form').find('input[type="checkbox"]:checked').trigger('click');
        e.preventDefault();
    });

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

    /*$('form.line_additional').on('submit', function(e){
        var found_err = false;
        var err_val = '';

        checkObj = $(this).find('input[type="checkbox"]:checked');
        if (checkObj.length == 0) {
            err_val = 'Пожалуйста, укажите хотя бы один тип транспортного средства';
            if (err_val != '') {
                found_err = true;
                if ($(this).find('.transport .row_err').length == 1) {
                    $(this).find('.transport .row_err').text(err_val);
                    $(this).find('.transport').addClass('error');
                }
                else {
                    $(this).find('.transport').find('.step-title.row_head').after('<div class="row_err"></div>');
                    $(this).find('.transport').find('.row_err').text(err_val);
                    $(this).find('.transport').addClass('error');
                }
            }
            err_val = '';

            if (found_err == true) {
                e.preventDefault();
                return false;
            }
        }
    });*/

    $('form.line_additional').on('submit', function(e){
        var found_err = false;
        var err_val = '';
        var wForm = $(this);
        var whId = wForm.find('input[type="hidden"][name="warehouse"]').val();

        //проверка подтверждения деактивации
        if(wForm.find('input[type="submit"][name="deactivate"]').length == 1 && typeof wForm.attr('approved') == 'undefined'){
            $('#page_body').append('<div id="popup_phone_num" class="no_round_pop y_n_popup">' +
                '<div class="popup_logo"></div><div class="popup_close" onclick="closeSubmPopup();"></div>' +
                '<div class="popup_header">Деактивация склада</div>' +
                '<div class="text">При деактивации склада будут деактивированы все товары, относящиеся к нему.</div>' +
                '<div class="send_button popup_repeat_send" onclick="closeSubmPopup();">Отмена</div>' +
                '<div class="submit_sms_button" onclick="sendSubmPopup(' + whId + ');">Подтвердить</div>' +
                '<div class="clear"></div>' +
                '</div>');
            $('#back_shad').show();

            e.preventDefault();
            return false;
        }
    });
});

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

        if(map1 != 0
            && map2 != 0
            && !isNaN(map1)
            && !isNaN(map2)
        ){
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
