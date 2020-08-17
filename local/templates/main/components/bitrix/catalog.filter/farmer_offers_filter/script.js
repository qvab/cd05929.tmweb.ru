$(function () {

    $('#farmer_offers_filter .wrap-btn .reset').click(function () {
        $('#farmer_offers_filter select').prop('selectedIndex',0).trigger('change');
        $('#farmer_offers_filter').submit();
    });

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
        $('form[name="farmer_offers_filter"] .submit-btn[type="submit"]').on('click', function(){
            workCookieName = 'farmer_offer_' + tabFilterSuf + '_culture_id';
            wFilterObj = $('form[name="farmer_offers_filter"] select[name="culture_id"]');
            if(wFilterObj.length == 1){
                setCookie(workCookieName, wFilterObj.val(), 0);
            }
            workCookieName = 'farmer_offer_' + tabFilterSuf + '_warehouse_id';
            wFilterObj = $('form[name="farmer_offers_filter"] select[name="warehouse_id"]');
            if(wFilterObj.length == 1){
                setCookie(workCookieName, wFilterObj.val(), 0);
            }
        });
        //сбрасываем данные при сбросе фильтра
        $('form[name="farmer_offers_filter"] .submit-btn.reset').on('click', function(){
            workCookieName = 'farmer_offer_' + tabFilterSuf + '_culture_id';
            setCookie(workCookieName, '0', 0);
            workCookieName = 'farmer_offer_' + tabFilterSuf + '_warehouse_id';
            setCookie(workCookieName, '0', 0);
        });
    }


});