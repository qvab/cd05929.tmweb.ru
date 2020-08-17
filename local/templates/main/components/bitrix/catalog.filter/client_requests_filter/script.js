$(document).ready(function(){
    $('#client_requests_filter .wrap-btn .reset').click(function () {
        $('#client_requests_filter select').prop('selectedIndex',0).trigger('change');
        $('#client_requests_filter').submit();
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
        $('#client_requests_filter .submit-btn[type="submit"]').on('click', function(){
            workCookieName = 'client_request_' + tabFilterSuf + '_wh';
            wFilterObj = $('#client_requests_filter select[name="warehouse_id"]');
            if(wFilterObj.length == 1){
                setCookie(workCookieName, wFilterObj.val(), 3);
            }
            workCookieName = 'client_request_' + tabFilterSuf + '_culture';
            wFilterObj = $('#client_requests_filter select[name="culture_id"]');
            if(wFilterObj.length == 1){
                setCookie(workCookieName, wFilterObj.val(), 3);
            }
        });
        //сбрасываем данные при сбросе фильтра
        $('#client_requests_filter .submit-btn.reset').on('click', function(){
            workCookieName = 'client_request_' + tabFilterSuf + '_wh';
            setCookie(workCookieName, '0', 3);
            workCookieName = 'client_request_' + tabFilterSuf + '_culture';
            setCookie(workCookieName, '0', 3);
        });
    }
});