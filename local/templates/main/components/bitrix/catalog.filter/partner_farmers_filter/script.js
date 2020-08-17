$(document).ready(function(){

    //проверка/установка значений фильтра
    var wFilterObj = '';
    var tabFilterObj = $('form.farmer_select');
    if(tabFilterObj.length == 1){
        //сохраняем значение данных при применении фильтра
        tabFilterObj.find('.submit-btn[type="submit"]').on('click', function(){
            wFilterObj = tabFilterObj.find('select[name="farmer_id"]');
            if(wFilterObj.length == 1){
                setCookie('partner_farmer_list_farmer', wFilterObj.val(), 0);
            }
            wFilterObj = tabFilterObj.find('select[name="is_linked"]');
            if(wFilterObj.length == 1){
                setCookie('partner_farmer_list_link_type', wFilterObj.val(), 0);
            }
            wFilterObj = tabFilterObj.find('select[name="region_id"]');
            if(wFilterObj.length == 1){
                setCookie('partner_farmer_list_region', wFilterObj.val(), 0);
            }
            wFilterObj = tabFilterObj.find('select[name="culture_id"]');
            if(wFilterObj.length == 1){
                setCookie('partner_farmer_list_culture', wFilterObj.val(), 0);
            }
        });
        //сбрасываем данные при сбросе фильтра
        $('form.farmer_select .submit-btn.reset').on('click', function(){
            setCookie('partner_farmer_list_farmer', '0', 0);
            setCookie('partner_farmer_list_link_type', '0', 0);
            setCookie('partner_farmer_list_region', '0', 0);
            setCookie('partner_farmer_list_culture', '0', 0);

            tabFilterObj.find('select').val(0).trigger('change');
            document.location.href = '/partner/users/linked_users/';
        });
    }
});