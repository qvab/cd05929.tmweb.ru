/**
 * @author: Vitaly Melnik <vitali@rarus.ru>
 */

$(function () {

    $('.wrap-report .wrap-btn .reset').click(function () {
        $('.wrap-report select').prop('selectedIndex',0).trigger('change');
        $('.wrap-report input[type="text"], .wrap-report input[type="hidden"]').val('');
        $('#filter-report').submit();
    });
});