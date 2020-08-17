


$(document).ready(function(){
    var get_params = getParams();
    if (typeof get_params['cr_form'] != "undefined"){
        if(get_params['cr_form'] == '1'){
            //открываем форму добавления лимитов
            if($('.opening_limit_available').length>0){
                $('.opening_limit_available').find('a').trigger('click');
            }else if($('.opening_limit_ended').length>0){
                $('.opening_limit_ended').find('a').trigger('click');
            }
        }
    }
});