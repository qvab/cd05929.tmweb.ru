$(document).ready(function(){
    //обработка авторизационных данных перед отправкой (например если аторизация происходит по номеру телефона)
    $('form[name="form_auth"]').on('submit', function(e){

        var loginObj = $(this).find('input[name="USER_LOGIN_S"]');
        var oldLoginObj = $(this).find('input[name="USER_LOGIN"]');
        if(loginObj.length == 1){
            var login_val = loginObj.val();
            var phone_found = false;
            //проверяем не введён ли телефон
            if(!checkEmail(login_val)){
                login_val = login_val.replace(/[\+\-\(\) ]/g, '');
                if(
                    (login_val.length == 10
                        || login_val.length == 11
                    )
                    && login_val.replace(/[0-9]+/g,'').length == 0
                ){
                    //найден телефон
                    if(login_val.length == 10)
                    {
                        login_val = '7' + login_val.toString();
                    }else{
                        login_val = '7' + login_val.toString().substr(1, 10);
                    }

                    login_val = 'p' + login_val.toString() + '@agrohelper.ru';

                    phone_found = true;
                    if(oldLoginObj.length == 1) {
                        oldLoginObj.val(login_val);
                    }else{
                        loginObj.after('<input type="hidden" name="USER_LOGIN" value="' + login_val + '" />');
                    }
                }
            }

            if(!phone_found){
                if(oldLoginObj.length == 1) {
                    oldLoginObj.val(loginObj.val().replace('"', '\''));
                }else{
                    loginObj.after('<input type="hidden" name="USER_LOGIN" value="' + loginObj.val().replace('"', '\'') + '" />');
                }
            }
        }
    });
});