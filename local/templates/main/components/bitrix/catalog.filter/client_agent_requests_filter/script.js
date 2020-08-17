$(document).ready(function(){
    //$('.select_item select').on('change', function(){
    $('form[name="request_filter"]').on('submit', function(e){
        var link_href = document.location.href.toString();
        var new_href = link_href.replace(/\?.*/g, '');
        var additional_href = '';
        var client_select_obj = $(this).find('select[name="client_id[]"]');
        var culture_select_obj = $(this).find('select[name="culture"]');

        if(link_href.length != link_href.replace('status=all', '').length){
            additional_href = 'status=all';
        }
        else if(link_href.length != link_href.replace('status=no', '').length){
            additional_href = 'status=no';
        }

        if(client_select_obj.val() > 0)
        {
            additional_href += (additional_href == '' ? '' : '&') + 'client_id[]=' + client_select_obj.val();
        }

        if(culture_select_obj.val() > 0)
        {
            additional_href += (additional_href == '' ? '' : '&') + 'culture=' + culture_select_obj.val();
        }

        if(additional_href != ''){
            new_href += '?' + additional_href;
        }

        //сохраняем значение данных фильтра
        saveCookiesAg();

        document.location.href = new_href;

        e.preventDefault();
    });

    var workCookieName = '';
    var tabFilterSuf = '';
    //проверка выбранной вкладки
    var tabFilterObj = $('#page_body .tab_form:first .item.active');
    var tabVal = tabFilterObj.index();
    if (tabVal == 0){
        tabFilterSuf = 'yes';
    }else if(tabVal == 1){
        tabFilterSuf = 'no';
    }else{
        tabFilterSuf = 'all';
    }
    //сбрасываем данные при сбросе фильтра
    $('form[name="request_filter"] .cancel_filter').on('click', function(){
        workCookieName = 'client_ag_request_' + tabFilterSuf + '_user';
        setCookie(workCookieName, '0', 3);
        workCookieName = 'client_ag_request_' + tabFilterSuf + '_culture';
        setCookie(workCookieName, '0', 3);
    });
});

//сохраняем значение данных фильтра (например при применении фильтра)
function saveCookiesAg() {
    var tabFilterSuf = '';
    var wFilterObj = '';
    var tabFilterObj = $('#page_body .tab_form:first .item.active');
    var workCookieName = '';

    if(tabFilterObj.length === 1){
        //проверка выбранной вкладки
        var tabVal = tabFilterObj.index();
        if (tabVal == 0){
            tabFilterSuf = 'yes';
        }else if(tabVal == 1){
            tabFilterSuf = 'no';
        }else{
            tabFilterSuf = 'all';
        }

        workCookieName = 'client_ag_request_' + tabFilterSuf + '_user';
        wFilterObj = $('form[name="request_filter"] select[name="client_id[]"]');
        if(wFilterObj.length === 1){
            setCookie(workCookieName, wFilterObj.val(), 0);
        }
        workCookieName = 'client_ag_request_' + tabFilterSuf + '_culture';
        wFilterObj = $('form[name="request_filter"] select[name="culture"]');
        if(wFilterObj.length === 1){
            setCookie(workCookieName, wFilterObj.val(), 0);
        }
    }
}