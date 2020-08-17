$(document).ready(function(){
    //check errors
    $('form.add_form input[type=button].submit-btn').on('click', function(e){
        $('form.add_form').trigger('submit');
    });
    $('form.add_form').on('submit', function(e){
        var err = '';
        var error_flag = false;
        var rowObj;

        var formObj = $(this);
        var mode = 'login';
        if(formObj.find('input[type="hidden"][name="by_phone"]').length > 0){
            mode = 'phone';
        }

        //check nds
        var checkField = formObj.find('select[name="nds_value"]');
        if(checkField.val() == 0)
        {
            err = 'Не выбран тип налогообложения';
            error_flag = true;
            rowObj = checkField.parents('.row');
            rowObj.addClass('error');
            if(rowObj.find('.row_err').length == 0)
            {
                rowObj.find('.row_val').append('<div class="row_err">' + err +  '</div>');
            }
            else
            {
                rowObj.find('.row_err').text(err);
            }
            err = '';
        }

        //check login
        if(mode == 'login'){
            //проверяем логин
            checkField = formObj.find('input[name="login"]');
            rowObj = checkField.parents('.row');
            if(checkField.val().toString().replace(/ /g, '') == '')
            {
                err = 'Укажите login';
                error_flag = true;
            }
        }else{
            //проверяем телефон
            checkField = formObj.find('input[name="phone"]');
            rowObj = checkField.parents('.row');
            if(!checkIsPhone(checkField.val()))
            {
                err = 'Укажите корректный телефон';
                error_flag = true;
            }
        }

        if(err != '')
        {
            rowObj = checkField.parents('.row');
            rowObj.addClass('error');
            if(rowObj.find('.row_err').length == 0)
            {
                rowObj.find('.row_val').append('<div class="row_err">' + err +  '</div>');
            }
            else
            {
                rowObj.find('.row_err').text(err);
            }
            err = '';
        }

        if(error_flag != false)
        {
            $('form.add_form .submit-btn').addClass('inactive');
            e.preventDefault();
        }
    });

    //remove error message after change
    $('.row_val input[type="text"], .row_val select[name="nds_value"]').on('change', function(){
        var err_obj = $(this).parents('.row.error');
        if(err_obj.length == 1)
        {
            err_obj.removeClass('error');
        }

        //remove inactive class
        if($('form.add_form .row.error').length == 0)
        {
            $('form.add_form .submit-btn').removeClass('inactive');
        }
    });

    //переключение между телефоном и email
    $('.tab_form.reg_form_control_tabs.agent_add_user .item.form_control_tab').on('click', function(){
        if(!$(this).hasClass('active')){
            var formObj = $(this).parents('form');
            var phoneTab = formObj.find('input[name="phone"]');
            var phoneHidden = formObj.find('input[type="hidden"][name="by_phone"]');
            var loginTab = formObj.find('input[name="email"]');
            if($(this).attr('data-val') == 'phone'){
                phoneTab.removeClass('inactive');
                loginTab.addClass('inactive');
                if(phoneHidden.length == 0){
                    formObj.prepend('<input type="hidden" name="by_phone" value="y" />');
                }
            }else{
                phoneTab.addClass('inactive');
                loginTab.removeClass('inactive');
                if(phoneHidden.length > 0){
                    phoneHidden.remove();
                }
            }

            $(this).siblings('.active').removeClass('active');
            $(this).addClass('active');
        }
    });
});