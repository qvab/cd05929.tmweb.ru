$(document).ready(function(){

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