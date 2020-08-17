$(document).ready(function(){

    //отправка формы
    $('form.tk_request_form').on('submit', function(e){
        var found_err = false;
        var err_val = '';
        var err_scroll_top = 0;
        var temp_val = '';

        var check_code = '';
        var checkObj = {};

        //check all required fields
        $(this).find('.row .needItem').each(function(ind, cObj){
            checkObj = $(cObj).siblings('.row_val').find('input[type="text"], textarea, select');
            if (checkObj.length == 1 && checkObj.parents('.sign_block.no_active').length == 0) {
                temp_val = checkObj.val().toString().replace(/ /g, '');
                check_code = checkObj.attr('name');
                if (check_code == 'email') {
                    if (temp_val == '') {
                        err_val = 'Пожалуйста заполните это обязательное поле';
                    }
                    else if (!checkEmailRfc(temp_val)) {
                        err_val = 'Укажите корректный email';
                    }
                }
                else {
                    if (temp_val == '') {
                        err_val = 'Пожалуйста заполните это обязательное поле';
                    }
                }

                if (err_val != '') {
                    found_err = true;
                    if (err_scroll_top == 0) {
                        err_scroll_top = checkObj.offset().top - 100;
                    }
                    if (checkObj.parents('.row').find('.row_err').length == 1) {
                        checkObj.parents('.row').addClass('error').find('.row_err').text(err_val);
                    }
                    else {
                        checkObj.after('<div class="row_err">' + err_val + '</div>');
                        checkObj.parents('.row').addClass('error');
                    }
                }
                err_val = '';
            }
            else if (checkObj.length > 1 && checkObj.parents('.sign_block.no_active').length == 0) {

                checkObj.each(function(i, cObj2){
                    temp_val = cObj2.value.toString().replace(/ /g, '');
                    if (temp_val == '') {
                        err_val = 'Пожалуйста, заполните все поля';
                        return;
                    }
                });

                if (err_val != '') {
                    var cObj2 = checkObj.first();
                    found_err = true;
                    if (err_scroll_top == 0) {
                        err_scroll_top = cObj2.offset().top - 100;
                    }
                    if (cObj2.parents('.row').find('.row_err').length == 1) {
                        cObj2.parents('.row').addClass('error').find('.row_err').text(err_val);
                    }
                    else {
                        cObj2.after('<div class="row_err">' + err_val + '</div>');
                        cObj2.parents('.row').addClass('error');
                    }
                }
            }
        });

        err_val = '';

        //check politics confirm flag
        checkObj = $(this).find('input[type="checkbox"][name="AUTH_REG_CONFIM"]');
        if (found_err == 0 && checkObj.length == 1 && !checkObj.prop('checked')) {
            e.preventDefault();
            found_err = true;
            if (err_scroll_top == 0) {
                err_scroll_top = checkObj.offset().top - 100;
            }
            alert('Не отмечена галочка согласия хранения персональных данных.');
        }

        if (found_err == true || $(this).hasClass('inactive')) {
            e.preventDefault();
            window.scrollTo(0, err_scroll_top);
            return false;
        }
        else if (!$(this).hasClass('g_passed')) {
            grecaptcha.execute();
            e.preventDefault();
        }
    });

});