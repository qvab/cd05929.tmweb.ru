$(function () {

    agentPairFilterInit();

    $('#deals_filter .wrap-btn .reset').click(function () {
        $('#deals_filter select').prop('selectedIndex',0).trigger('change');
        $('#deals_filter').submit();
    });
});


function agentPairFilterInit() {
    //проверка/установка значений фильтра
    var wFilterObj = '';
    var tabFilterObj = $('form[name="deals_filter"]');
    var workCookieName = '';
    if(tabFilterObj.length == 1){

        //сохраняем значение данных при применении фильтра
        $('form[name="deals_filter"] .submit-btn[type="submit"]').on('click', function(){

            workCookieName = 'deals_filter_client_warehouse_id';
            wFilterObj = $('form[name="deals_filter"] select[name="client_warehouse_id"]');
            if(wFilterObj.length == 1){
                setCookie(workCookieName, wFilterObj.val(), 0);
            }

            workCookieName = 'deals_filter_farmer_warehouse_id';
            wFilterObj = $('form[name="deals_filter"] select[name="farmer_warehouse_id"]');
            if(wFilterObj.length == 1){
                setCookie(workCookieName, wFilterObj.val(), 0);
            }

            workCookieName = 'deals_filter_culture_id';
            wFilterObj = $('form[name="deals_filter"] select[name="culture_id"]');
            if(wFilterObj.length == 1){
                setCookie(workCookieName, wFilterObj.val(), 0);
            }

            workCookieName = 'deals_filter_client_id';
            wFilterObj = $('form[name="deals_filter"] select[name="client_id"]');
            if(wFilterObj.length == 1){
                setCookie(workCookieName, wFilterObj.val(), 0);
            }

            workCookieName = 'deals_filter_farmer_id';
            wFilterObj = $('form[name="deals_filter"] select[name="farmer_id"]');
            if(wFilterObj.length == 1){
                setCookie(workCookieName, wFilterObj.val(), 0);
            }

            workCookieName = 'deals_filter_region_id';
            wFilterObj = $('form[name="deals_filter"] select[name="region_id"]');
            if(wFilterObj.length == 1){
                setCookie(workCookieName, wFilterObj.val(), 0);
            }

        });
        //сбрасываем данные при сбросе фильтра
        $('form[name="deals_filter"] .reset').on('click', function(){
            workCookieName = 'deals_filter_client_warehouse_id'; setCookie(workCookieName, '0', 0);
            workCookieName = 'deals_filter_farmer_warehouse_id'; setCookie(workCookieName, '0', 0);
            workCookieName = 'deals_filter_culture_id'; setCookie(workCookieName, '0', 0);
            workCookieName = 'deals_filter_client_id'; setCookie(workCookieName, '0', 0);
            workCookieName = 'deals_filter_farmer_id'; setCookie(workCookieName, '0', 0);
            workCookieName = 'deals_filter_region_id'; setCookie(workCookieName, '0', 0);
        });
    }
}