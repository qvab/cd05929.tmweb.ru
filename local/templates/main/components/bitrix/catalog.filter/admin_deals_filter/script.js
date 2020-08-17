$(function () {

    $('#deals_filter .wrap-btn .reset').click(function () {
        $('#deals_filter select').prop('selectedIndex',0).trigger('change');
        $('#deals_filter').submit();
    });
});