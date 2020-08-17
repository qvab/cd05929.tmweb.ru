var arCorrectFileFormat = ['png', 'jpeg', 'jpg', 'bmp', 'doc', 'docx', 'xls', 'xlsx', 'pdf'];

$(document).ready(function(){

    $('form[name="profile_form"]').on('submit', function(e){
        //check all fields are not empty
        var found_err = false, temp_val = '', err_val = '', check_code = '', err_scroll_top = '';

        $(this).find('input[type="text"], input[type="password"], textarea, select').each(function(ind, cObj){
            temp_val = $(cObj).val().toString().replace(/ /g, '');

            check_code = $(cObj).attr('name');
            if (check_code == 'USER_EMAIL') {
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

            if(err_val != '')
            {
                found_err = true;
                if(err_scroll_top == 0)
                {
                    err_scroll_top = $(cObj).offset().top - 100;
                }
                if($(cObj).parents('.row').find('.row_err').length == 1)
                {
                    $(cObj).parents('.row').addClass('error').find('.row_err').text(err_val);
                }
                else
                {
                    $(cObj).after('<div class="row_err">' + err_val + '</div>');
                    $(cObj).parents('.row').addClass('error');
                }
            }
            err_val = '';
        });

        $(this).find('input[type="file"]').each(function(ind, cObj){
            err_val = '';
            checkObj = $(cObj);
            var val = checkObj.val();
            if (val != '') {
                var arVal = val.split('.');
                var ext = arVal[arVal.length - 1];
                ext = ext.toLowerCase();
                if (!in_array(ext, arCorrectFileFormat)) {
                    err_val = 'Неверный формат файла';
                }

                if(err_val != '') {
                    found_err = true;
                    if(err_scroll_top == 0)
                    {
                        err_scroll_top = checkObj.offset().top - 100;
                    }
                    if(checkObj.parents('.row').find('.row_err').length == 1)
                    {
                        checkObj.parents('.row').addClass('error').find('.row_err').text(err_val);
                    }
                    else
                    {
                        checkObj.after('<div class="row_err">' + err_val + '</div>');
                        checkObj.parents('.row').addClass('error');
                    }
                }
            }
        });

        if(found_err)
        {
            e.preventDefault();
            if(err_scroll_top != 0)
            {
                window.scrollTo(0, err_scroll_top);
            }
        }
    });

    $('form[name="profile_form"] input[type="password"], form[name="profile_form"] input[type="text"], form[name="profile_form"] input[type="file"], form[name="profile_form"] select, form[name="profile_form"] textarea').on('change', function(e){
        var error_val = '';
        checkObj = $(this);
        var name = checkObj.attr('name');
        var val = checkObj.val();
        var len = val.toString().length;

        if (name == 'PROP__BIK') {
            if (len != 9) {
                var error_val = 'Поле должно содержать 9 цифр';
            }
        }
        else if (name == 'PROP__RASCH_SCHET' || name == 'PROP__KOR_SCHET') {
            if (len != 20) {
                var error_val = 'Поле должно содержать 20 цифр';
            }
        }

        if (error_val != '') {
            if (checkObj.parents('.row').find('.row_err').length == 1) {
                checkObj.parents('.row').addClass('error').find('.row_err').text(error_val);
            }
            else {
                checkObj.after('<div class="row_err">' + error_val + '</div>');
                checkObj.parents('.row').addClass('error');
            }
        }
        else {
            var err_obj = $(this).parents('.row.error');
            if (err_obj.length == 1) {
                err_obj.removeClass('error');
            }
        }
    });
});