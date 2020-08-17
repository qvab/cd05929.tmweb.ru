$(document).ready(function(){

    clientAgentWhFilterInit();

    //$('.select_item select').on('change', function(){
    $('form[name="warehouse_filter"]').on('submit', function(e){
        var link_href = document.location.href.toString();
        var new_href = link_href.replace(/\?.*/g, '');
        var additional_href = '';
        var client_select_obj = $(this).find('select[name="select_client"]');

        if(link_href.length != link_href.replace('status=all', '').length){
            additional_href = 'status=all';
        }
        else if(link_href.length != link_href.replace('status=no', '').length){
            additional_href = 'status=no';
        }

        if(client_select_obj.val() != 0)
        {
            additional_href += (additional_href == '' ? '' : '&') + 'client_id[]=' + client_select_obj.val();
        }

        if(additional_href != ''){
            new_href += '?' + additional_href;
        }

        document.location.href = new_href;

        e.preventDefault();
    });
});

function clientAgentWhFilterInit() {
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
        $('form[name="warehouse_filter"] .submit-btn[type="submit"]').on('click', function(){
            workCookieName = 'client_agent_wh_' + tabFilterSuf + '_client_id';
            wFilterObj = $('form[name="warehouse_filter"] select[name="select_client"]');
            if(wFilterObj.length == 1){
                setCookie(workCookieName, wFilterObj.val(), 3);
            }

        });
        //сбрасываем данные при сбросе фильтра
        $('form[name="warehouse_filter"] .cancel_filter').on('click', function(){
            workCookieName = 'client_agent_wh_' + tabFilterSuf + '_client_id';
            setCookie(workCookieName, '0', 3);
        });
    }
}