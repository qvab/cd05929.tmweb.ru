function validate(event) {
    event.preventDefault();

    var found_err = false;
    var err_val = '';
    var err_scroll_top = 0;
    var temp_val = '';
    var form = $('form.response-form');
    //check warehouse name
    var checkObj = form.find('input[type="text"][name="USER_NAME"]');
    if(checkObj.length == 1) {
       temp_val = checkObj.val();
       if(temp_val == '') {
           err_val = 'Пожалуйста заполните это обязательное поле';
       }
       if(err_val != '') {
           found_err = true;
           err_scroll_top = checkObj.offset().top - 100;
           if(checkObj.parents('.row').find('.row_err').length == 1) {
               checkObj.parents('.row').addClass('error').find('.row_err').text(err_val);
           }
           else {
               checkObj.parents('.row').find('.row_val').append('<div class="row_err"></div>');
               checkObj.parents('.row').addClass('error').find('.row_err').text(err_val);
           }
           err_val = '';
       }
    }
    //check email
    checkObj = form.find('input[type="text"][name="USER_EMAIL"]');
    if(checkObj.length == 1) {
       temp_val = checkObj.val();
       if(temp_val == '') {
           err_val = 'Пожалуйста заполните это обязательное поле';
       }
       else if(!checkEmailRfc(temp_val)) {
           err_val = 'Укажите корректный email';
       }
       if(err_val != '') {
           found_err = true;
           if(err_scroll_top == 0) {
               err_scroll_top = checkObj.offset().top - 100;
           }
           if(checkObj.parents('.row').find('.row_err').length == 1) {
               checkObj.parents('.row').addClass('error').find('.row_err').text(err_val);
           }
           else {
               checkObj.parents('.row').find('input[name="USER_EMAIL"]').after('<div class="row_err"></div>');
               checkObj.parents('.row').addClass('error').find('.row_err').text(err_val);
           }
       }
       err_val = '';
    }
    checkObj = form.find('textarea[name="RESP_QUESTION"]');
    if(checkObj.length == 1 && err_val == '') {
       temp_val = checkObj.val();
       if(temp_val == '') {
           err_val = 'Пожалуйста заполните это обязательное поле';
       }
       if(err_val != '') {
           found_err = true;
           if(err_scroll_top == 0) {
               err_scroll_top = checkObj.offset().top - 100;
           }
           if(checkObj.parents('.row').find('.row_err').length == 1) {
               checkObj.parents('.row').addClass('error').find('.row_err').text(err_val);
           }
           else {
               checkObj.parents('.row').find('textarea[name="RESP_QUESTION"]').after('<div class="row_err"></div>');
               checkObj.parents('.row').addClass('error').find('.row_err').text(err_val);
           }
       }
    }
    err_val = '';
    if (found_err == true || form.hasClass('inactive')) {
       event.preventDefault();
       window.scrollTo(0, err_scroll_top);
       return true;
    }else if(!form.hasClass('g_passed')){
       grecaptcha.execute();
    }
}

function onload() {
    var element = document.getElementById('resp_submit');
    element.onclick = validate;
}


$(document).ready(function(){

    onload();

   // var that = $(this);
   /*grecaptcha.render('responseRecaptcha', {
        sitekey: '6LeDzmAUAAAAABZV4UNfOq9SzwqDqtWJXvtDPb5G',
        callback: function(token) {
            alert('1111');
            $('form.response-form').submit(token)
        }
    });
*/
    $('form.response-form input[type="text"]').on('change', function(e){
        //remove error message after value change
        var err_obj = $(this).parents('.row.error');
        if(err_obj.length == 1)
        {
            err_obj.removeClass('error');
        }
    });
    $('form.response-form textarea').on('change', function(e){
        //remove error message after value change
        var err_obj = $(this).parents('.row.error');
        if(err_obj.length == 1)
        {
            err_obj.removeClass('error');
        }
    });


});


