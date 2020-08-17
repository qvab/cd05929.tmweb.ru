$(function () {

    clientPairFilterInit();

    $('#deals_filter .wrap-btn .reset').click(function () {
        document.location.href = '/client/blacklist/';
    });
});


function clientPairFilterInit() {
    //проверка/установка значений фильтра
    var wFilterObj = '';
    var tabFilterSuf = '';
    var tabFilterObj = $('form[name="blacklist_filter"]');
    var workCookieName = '';
    if(tabFilterObj.length == 1){

        //сохраняем значение данных при применении фильтра
        $('form[name="blacklist_filter"] .submit-btn[type="submit"]').on('click', function(){

            workCookieName = 'blacklist_filter_region_id';
            wFilterObj = $('form[name="blacklist_filter"] select[name="region_id"]');
            if(wFilterObj.length == 1){
                setCookie(workCookieName, wFilterObj.val(), 0);
            }

            workCookieName = 'blacklist_filter_culture_id';
            wFilterObj = $('form[name="blacklist_filter"] select[name="culture_id"]');
            if(wFilterObj.length == 1){
                setCookie(workCookieName, wFilterObj.val(), 0);
            }

            workCookieName = 'blacklist_filter_reasond_id';
            wFilterObj = $('form[name="blacklist_filter"] select[name="reasond_id"]');
            if(wFilterObj.length == 1){
                setCookie(workCookieName, wFilterObj.val(), 0);
            }

        });
        //сбрасываем данные при сбросе фильтра
        $('form[name="blacklist_filter"] .submit-btn.reset').on('click', function(){
            workCookieName = 'blacklist_filter_region_id'; setCookie(workCookieName, '0', 0);
            workCookieName = 'blacklist_filter_culture_id'; setCookie(workCookieName, '0', 0);
            workCookieName = 'blacklist_filter_reasond_id'; setCookie(workCookieName, '0', 0);
        });
    }
}