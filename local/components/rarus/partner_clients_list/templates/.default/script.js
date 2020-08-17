var stop_slide_anim = 0;
var agent_contract_interval_id = 0, agent_contract_interval_err_id = 0, agent_contract_interval_time = 0, agent_contract_interval_err_time = 0;

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

    //сохранение данных агентского договора
    $('.contract_prop .submit-btn').on('click', function(){
        var uid = 0, checked = false, cdate = '', cfile = '', is_err = false;

        var wArea = $(this).parents('.contract_prop');
        var wObj = wArea.find('input[name="agent_contract"]');
        if(wObj.length > 0){
            checked = wObj.prop('checked');
        }
        // wObj = wArea.find('input[name="agent_contract_date"]');
        // if(wObj.length > 0
        //     && wObj.val() != ''
        // ){
        //     cdate = wObj.val();
        // }
        wObj = wArea.parents('.line_area')
        if(wObj.length > 0
            && typeof wObj.attr('data-uid') != 'undefined'
        ){
            uid = wObj.attr('data-uid');
        }
        wObj = document.querySelector('.line_area[data-uid="' + uid + '"] input[name="agent_contract_file"]');
        if(wObj
            && typeof wObj.files != 'undefined'
            && typeof wObj.files[0] != 'undefined'
        ){
            cfile = wObj.files[0];
        }

        //проверяем прикреплен ли файл, если он не был прикреплен ранее (и если ен идет удаление агентского договора)
        /*if(cfile === ''
            && wArea.find('.get_file').length === 0
            && cdate != ''
            && checked
        ){
            if(wArea.find('.error_message.by_file').length === 0) {
                wArea.prepend('<div class="error_message by_file spec">Прикрепите файл договора</div>');
            }
            is_err = true;
        }*/

        if(!is_err) {
            var formd = new FormData();
            formd.append('uid', uid);
            //formd.append('agent_contract_date', cdate);
            formd.append('agent_contract', (checked ? 1 : 0));
            formd.append('agent_contract_file', cfile);
            $.ajax({
                url: "/ajax/changePartnerContractData.php",
                type: "POST",
                cache: false,
                contentType: false,
                processData: false,
                data: formd,
                success: function (mes) {
                    if (mes == 0) {
                        //произошла ошибка
                        wArea.find('.error_message').addClass('active');

                        clearInterval(agent_contract_interval_err_id);
                        agent_contract_interval_err_time = $.now();
                        agent_contract_interval_err_id = setInterval(function () {
                            if(agent_contract_interval_err_time < $.now() - 3000) {
                                clearInterval(agent_contract_interval_err_id);
                                $('.contract_prop .error_message.active').removeClass('active');
                            }
                        }, 1000);
                    } else if (mes != '') {
                        //данные успешно обновлены
                        wArea.find('.result_message').addClass('active');

                        clearInterval(agent_contract_interval_id);
                        agent_contract_interval_time = $.now();
                        agent_contract_interval_id = setInterval(function () {
                            if(agent_contract_interval_time < $.now() - 3000) {
                                clearInterval(agent_contract_interval_id);
                                $('.contract_prop .result_message.active').removeClass('active');
                            }
                        }, 3000);

                        //если прошло добавление нового файла обновляем данные на скачивание
                        if (mes != 1
                            && mes != 2
                        ) {
                            var temp_data = mes.split('|', 3);
                            var wObj = wArea.find('.get_file a');
                            if (wObj.length == 0) {
                                wArea.find('.input_before_file:first').before('<div class="get_file">Посмотреть текущий договор <a target="_blank" href="">по ссылке</a><div class="date_line">Дата последнего изменения: <span class="date_val"></span></div></div>');
                                wObj = wArea.find('.get_file a');
                            }
                            wObj.attr({/*download: temp_data[1],*/ href: temp_data[0]});

                            //обновляем дату
                            if(typeof temp_data[2] != 'undefined') {
                                wObj = wArea.find('.get_file .date_val').text(temp_data[2]);
                            }

                            if(typeof temp_data[3] != 'undefined'
                                && temp_data[3] == 1
                            ) {
                                wArea.parents('.line_area').find('.agent_contract_sign').addClass('active');
                            }else{
                                wArea.parents('.line_area').find('.agent_contract_sign.active').removeClass('active');
                            }
                        } else if (mes == 2) {
                            //если произошло удаление старого файла
                            var wObj = wArea.find('input[name="agent_contract"]');
                            if (wObj.prop('checked') === true) {
                                wObj.trigger('click');
                            }

                            //wObj = wArea.find('.get_file').remove();
                            //wArea.find('.input_before_file .val').text('Прикрепить файл');
                            //wArea.find('input[name="agent_contract_date"]').val('');
                            //wArea.find('input[name="agent_contract_file"]').replaceWith('<input type="file" name="agent_contract_file" class="file_btn needFile">');
                            wArea.parents('.line_area').find('.agent_contract_sign').removeClass('active');
                        }else{
                            wArea.parents('.line_area').find('.agent_contract_sign').addClass('active');
                        }
                    }
                }
            });
        }
    });

    //обработка выбора файла агентского договора (убираем сообщение об ошибке, если есть)
    /*$('.contract_prop').on('change', 'input[name="agent_contract_file"]', function(){
        var checkObj = $(this).parents('.contract_prop').find('.error_message.by_file');
        if(checkObj.length > 0){
            checkObj.remove();
        }
    });*/

    //обработка изменения данных
    $('.contract_prop').on('change', 'input[name="agent_contract_file"], input[name="agent_contract"]', function(){
        var uid = 0, checked = false,  cfile = '';
        var wArea = $(this).parents('.contract_prop');

        var wObj = wArea.find('input[name="agent_contract"]');
        if(wObj.length > 0){
            checked = wObj.prop('checked');
        }
        wObj = wArea.parents('.line_area')
        if(wObj.length > 0
            && typeof wObj.attr('data-uid') != 'undefined'
        ){
            uid = wObj.attr('data-uid');
        }

        var formd = new FormData();
        formd.append('uid', uid);
        formd.append('agent_contract', (checked ? 1 : 0));

        if($(this).attr('name') == 'agent_contract_file') {
            wObj = document.querySelector('.line_area[data-uid="' + uid + '"] input[name="agent_contract_file"]');
            if (wObj
                && typeof wObj.files != 'undefined'
                && typeof wObj.files[0] != 'undefined'
            ) {
                cfile = wObj.files[0];
                formd.append('agent_contract_file', cfile);
            }
        }

        $.ajax({
            url: "/ajax/changePartnerContractData.php",
            type: "POST",
            cache: false,
            contentType: false,
            processData: false,
            data: formd,
            success: function (mes) {
                if (mes == 0) {
                    //произошла ошибка
                    wArea.find('.error_message').addClass('active');

                    clearInterval(agent_contract_interval_err_id);
                    agent_contract_interval_err_time = $.now();
                    agent_contract_interval_err_id = setInterval(function () {
                        if(agent_contract_interval_err_time < $.now() - 3000) {
                            clearInterval(agent_contract_interval_err_id);
                            $('.contract_prop .error_message.active').removeClass('active');
                        }
                    }, 1000);
                } else if (mes != '') {
                    //данные успешно обновлены
                    wArea.find('.result_message').addClass('active');

                    clearInterval(agent_contract_interval_id);
                    agent_contract_interval_time = $.now();
                    agent_contract_interval_id = setInterval(function () {
                        if(agent_contract_interval_time < $.now() - 3000) {
                            clearInterval(agent_contract_interval_id);
                            $('.contract_prop .result_message.active').removeClass('active');
                        }
                    }, 3000);

                    //если прошло добавление нового файла обновляем данные на скачивание
                    if (mes != 1
                        && mes != 2
                    ) {
                        var temp_data = mes.split('|', 4);
                        var wObj = wArea.find('.get_file a');
                        if (wObj.length == 0) {
                            wArea.find('.input_before_file:first').before('<div class="get_file">Посмотреть текущий договор <a target="_blank" href="">по ссылке</a><div class="date_line">Дата последнего изменения: <span class="date_val"></span></div></div>');
                            wObj = wArea.find('.get_file a');
                        }
                        wObj.attr({/*download: temp_data[1],*/ href: temp_data[0]});

                        //обновляем дату
                        if(typeof temp_data[2] != 'undefined') {
                            wObj = wArea.find('.get_file .date_val').text(temp_data[2]);
                        }

                        if(typeof temp_data[3] != 'undefined'
                            && temp_data[3] == 1
                        ) {
                            wArea.parents('.line_area').find('.agent_contract_sign').addClass('active');
                        }else{
                            wArea.parents('.line_area').find('.agent_contract_sign.active').removeClass('active');
                        }
                    } else if (mes == 2) {
                        //если произошло удаление старого файла
                        var wObj = wArea.find('input[name="agent_contract"]');
                        if (wObj.prop('checked') === true) {
                            wObj.trigger('click');
                        }

                        //wObj = wArea.find('.get_file').remove();
                        //wArea.find('.input_before_file .val').text('Прикрепить файл');
                        //wArea.find('input[name="agent_contract_date"]').val('');
                        //wArea.find('input[name="agent_contract_file"]').replaceWith('<input type="file" name="agent_contract_file" class="file_btn needFile">');
                        wArea.parents('.line_area').find('.agent_contract_sign').removeClass('active');
                    }else{
                        wArea.parents('.line_area').find('.agent_contract_sign').addClass('active');
                    }
                }
            }
        });
    });
});

/* отвязаться от пользователя */
function unlinkPartner(uid){
    $.post('/ajax/changePartnerLinkToUser.php', {
        uid: uid,
        type: 'c',
        mode: 'unlink'
    }, function (mes) {
        if(mes == 1){
            $('.list_page_rows .line_area[data-uid="' + uid + '"]').removeClass('is_linked').addClass('not_linked');
        }
    });
}

/* отвязаться от пользователя */
function linkPartner(uid){
    $.post('/ajax/changePartnerLinkToUser.php', {
        uid: uid,
        type: 'c',
        mode: 'link'
    }, function (mes) {
        if(mes == 1){
            $('.list_page_rows .line_area[data-uid="' + uid + '"]').removeClass('not_linked').addClass('is_linked');
        }
    });
}

/* отвязаться от пользователя */
function inviteByPartner(uid){
    $.post('/ajax/getUserInviteByPartner.php', {
        uid: uid,
        type: 'c'
    }, function (mes) {
        var wObjArea = $('.list_page_rows .line_area[data-uid="' + uid + '"]');
        if(wObjArea.length == 1){
            var wObj = wObjArea.find('.invite_href');
            if(wObj.length == 0) {
                wObjArea.find('.additional_submits a[data-val="make_invite"]').after('<div class="invite_href">' + mes + '</div>');
            }else{
                wObj.text(mes);
            }
        }
    });
}