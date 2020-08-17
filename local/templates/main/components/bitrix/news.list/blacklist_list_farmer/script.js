var stop_slide_anim = 0;

//диактивация записи в черном списке
function deactivateItem(item_id) {
    startBackLoad();
    $.ajax({
        type: "POST",
        url: "/ajax/farmer_blacklist_del.php",
        data: {
            bl_id: item_id
        },
        dataType: 'JSON',
        success: function(msg){
            var message_area = $('.list_page_rows').siblings('.message_area:first');
            if(message_area.length == 0){
                $('.list_page_rows').before('<div class="message_area"><div class="message"></div></div>');
                message_area = $('.list_page_rows').siblings('.message_area:first');
            }
            var message_obj = message_area.find('.message');

            console.log(msg);
            if(msg.result == 1){
                $('#item_'+item_id).remove();
                message_area.addClass('success');
                message_obj.removeClass('err');
                message_obj.text('Пользователь удален из чёрного списка');
            }else{
                message_area.removeClass('success');
                message_obj.addClass('err');
                message_obj.text('При удалении из черного списка возникла ошибка. Попробуйте позднее или свяжитесь с администрацией.');
            }
            setTimeout('stopBackLoad()', 300);
            setTimeout('closeSubmPopup()', 300);

        }
    });
}

//закрытие окна подтверждения
function closeSubmPopup(){
    $('#popup_phone_num').remove();
    $('#back_shad').hide();
}

$(document).ready(function() {
    $('.list_page_rows .line_area .line_inner, .list_page_rows .line_area .line_additional .hide_but').on('click', function(){
        if(stop_slide_anim == 0)
        {
            stop_slide_anim = 1;
            var wObj = $(this).parents('.line_area');
            wObj.toggleClass('active');
            if(!wObj.hasClass('active'))
            {
                wObj.find('.line_additional').slideUp(300, function(){
                    stop_slide_anim = 0;
                });
                wObj.find('.arw_list').removeClass('arw_icon_open').addClass('arw_icon_close');
            }
            else
            {
                wObj.find('.line_additional').slideDown(300, function(){
                    stop_slide_anim = 0;
                });
                wObj.find('.arw_list').removeClass('arw_icon_close').addClass('arw_icon_open');
            }
        }
    });

    $('.del_bl').on('click',function () {
        if($(this).attr('data-id').length>0){
            var item_id = $(this).attr('data-id');
            //проверка подтверждения деактивации
            $('#page_body').append('<div id="popup_phone_num" class="no_round_pop y_n_popup">' +
                '<div class="popup_logo"></div><div class="popup_close" onclick="closeSubmPopup();"></div>' +
                '<div class="popup_header">Удаление покупателя из черного списка</div>' +
                '<div class="text">После удаления покупателя из черного списка, Вы снова сможете отправлять ему встречные предложения.</div>' +
                '<div class="send_button popup_repeat_send" onclick="closeSubmPopup();">Отмена</div>' +
                '<div class="submit_sms_button" onclick="deactivateItem(' + item_id + ');">Подтвердить</div>' +
                '<div class="clear"></div>' +
                '</div>');
            $('#back_shad').show();
            e.preventDefault();
            return false;
        }
    });

});