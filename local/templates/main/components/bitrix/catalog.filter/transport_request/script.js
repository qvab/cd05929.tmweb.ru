$(function () {

    transportRequestFilterInit();

    $('#transport_request .wrap-btn .reset').click(function () {
        $('#transport_request select').prop('selectedIndex',0).trigger('change');
        $('#transport_request').submit();
    });
});


function transportRequestFilterInit() {
    //проверка/установка значений фильтра
    var wFilterObj = '';
    var tabFilterSuf = '';
    var tabFilterObj = $('form[name="transport_request"]');
    var workCookieName = '';
    if(tabFilterObj.length == 1){
        //сохраняем значение данных при применении фильтра
        $('form[name="transport_request"] .submit-btn[type="submit"]').on('click', function(){
            workCookieName = 'transport_request_culture_id';
            wFilterObj = $('form[name="transport_request"] select[name="culture_id"]');
            if(wFilterObj.length == 1){
                setCookie(workCookieName, wFilterObj.val(), 3);
            }
            workCookieName = 'transport_request_distance_id';
            wFilterObj = $('form[name="transport_request"] select[name="distance_id"]');
            if(wFilterObj.length == 1){
                setCookie(workCookieName, wFilterObj.val(), 3);
            }
        });
        //сбрасываем данные при сбросе фильтра
        $('form[name="transport_request"] .cancel_filter').on('click', function(){
            workCookieName = 'transport_request_culture_id';
            setCookie(workCookieName, '0', 3);
            workCookieName = 'transport_request_distance_id';
            setCookie(workCookieName, '0', 3);
        });
    }
}