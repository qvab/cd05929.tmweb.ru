$(document).ready(function(){

    //проверка/установка значений фильтра
    var wFilterObj = '';
    var tabFilterObj = $('form.client_select');
    if(tabFilterObj.length == 1){
        //сохраняем значение данных при применении фильтра
        tabFilterObj.find('.submit-btn[type="submit"]').on('click', function(){
            wFilterObj = tabFilterObj.find('select[name="client_id"]');
            if(wFilterObj.length == 1){
                setCookie('partner_client_list_client', wFilterObj.val(), 0);
            }
            wFilterObj = tabFilterObj.find('select[name="is_linked"]');
            if(wFilterObj.length == 1){
                setCookie('partner_client_list_link_type', wFilterObj.val(), 0);
            }
            wFilterObj = tabFilterObj.find('select[name="region_id"]');
            if(wFilterObj.length == 1){
                setCookie('partner_client_list_region', wFilterObj.val(), 0);
            }
            wFilterObj = tabFilterObj.find('select[name="culture_id"]');
            if(wFilterObj.length == 1){
                setCookie('partner_client_list_culture', wFilterObj.val(), 0);
            }
        });
        //сбрасываем данные при сбросе фильтра
        $('form.client_select .submit-btn.reset').on('click', function(){
            setCookie('partner_client_list_client', '0', 0);
            setCookie('partner_client_list_link_type', '0', 0);
            setCookie('partner_client_list_region', '0', 0);
            setCookie('partner_client_list_culture', '0', 0);

            tabFilterObj.find('select').val(0).trigger('change');
            document.location.href = '/partner/users/linked_clients/';
        });
    }
});