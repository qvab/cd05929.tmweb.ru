$(document).ready(function(){
    //$('.select_item select').on('change', function(){
    agentOffersFilterInit();

    $('form[name="offers_filter"]').on('submit', function(e){
        var link_href = document.location.href.toString();
        var new_href = link_href.replace(/\?.*/g, '');
        var additional_href = '';

        var region_select_obj = $(this).find('select[name="region_id"]');
        var farmer_select_obj = $(this).find('select[name="farmer_id[]"]');
        var culture_select_obj = $(this).find('select[name="culture"]');
        var nds_select_obj = $(this).find('select[name="type_nds"]');

        if(link_href.length != link_href.replace('status=all', '').length){
            additional_href = 'status=all';
        }
        else if(link_href.length != link_href.replace('status=no', '').length){
            additional_href = 'status=no';
        }

        if(region_select_obj.val() > 0)
        {
            additional_href += (additional_href == '' ? '' : '&') + 'region_id=' + region_select_obj.val();
        }

        if(farmer_select_obj.val() > 0)
        {
            additional_href += (additional_href == '' ? '' : '&') + 'farmer_id[]=' + farmer_select_obj.val();
        }

        if(culture_select_obj.val() > 0)
        {
            additional_href += (additional_href == '' ? '' : '&') + 'culture=' + culture_select_obj.val();
        }

        if(nds_select_obj.val() > 0)
        {
            additional_href += (additional_href == '' ? '' : '&') + 'type_nds=' + nds_select_obj.val();
        }

        if(additional_href != ''){
            new_href += '?' + additional_href;
        }

        document.location.href = new_href;

        e.preventDefault();
    });
});


function agentOffersFilterInit() {
    //проверка/установка значений фильтра
    var wFilterObj = '';
    var tabFilterSuf = '';
    var tabFilterObj = $('#page_body .tab_form:first .item.active');
    var workCookieName = '';
    if(tabFilterObj.length == 1){
        //проверка выбранной вкладки
        var tabVal = tabFilterObj.index();
        if (tabVal == 0){
            tabFilterSuf = 'yes';
        }else if(tabVal == 1){
            tabFilterSuf = 'no';
        }else{
            tabFilterSuf = 'all';
        }

        //сохраняем значение данных при применении фильтра
        $('form[name="offers_filter"] .submit-btn[type="submit"]').on('click', function(){
            workCookieName = 'farmer_offer_' + tabFilterSuf + '_farmer_id';
            wFilterObj = $('form[name="offers_filter"] select[name="farmer_id[]"]');
            if(wFilterObj.length == 1){
                setCookie(workCookieName, wFilterObj.val(), 3);
            }
            workCookieName = 'farmer_offer_' + tabFilterSuf + '_culture_id';
            wFilterObj = $('form[name="offers_filter"] select[name="culture"]');
            if(wFilterObj.length == 1){
                setCookie(workCookieName, wFilterObj.val(), 3);
            }
            workCookieName = 'farmer_offer_' + tabFilterSuf + '_region_id';
            wFilterObj = $('form[name="offers_filter"] select[name="region_id"]');
            if(wFilterObj.length == 1){
                setCookie(workCookieName, wFilterObj.val(), 3);
            }
            workCookieName = 'farmer_offer_' + tabFilterSuf + '_type_nds';
            wFilterObj = $('form[name="offers_filter"] select[name="type_nds"]');
            if(wFilterObj.length == 1){
                setCookie(workCookieName, wFilterObj.val(), 3);
            }

        });
        //сбрасываем данные при сбросе фильтра
        $('form[name="offers_filter"] .cancel_filter').on('click', function(){
            workCookieName = 'farmer_offer_' + tabFilterSuf + '_farmer_id';
            setCookie(workCookieName, '0', 3);
            workCookieName = 'farmer_offer_' + tabFilterSuf + '_culture_id';
            setCookie(workCookieName, '0', 3);
            workCookieName = 'farmer_offer_' + tabFilterSuf + '_region_id';
            setCookie(workCookieName, '0', 3);
            workCookieName = 'farmer_offer_' + tabFilterSuf + '_type_nds';
            setCookie(workCookieName, '0', 3);
        });
    }
}