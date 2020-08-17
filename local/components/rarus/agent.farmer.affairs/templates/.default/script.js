/**
 * @author: Vitaly Melnik <vitali@rarus.ru>
 */




/**
 *
 */
$(function () {

    /**
     * Сбрасываем поля фильтра
     */
    $('#filter_affairs .wrap-btn .reset').click(function () {

        $('#filter_affairs select').prop('selectedIndex',0).trigger('change');
        $('#filter_affairs input[type="text"]').val('').trigger('change');

        $('.wrap-affairs .wrap-filter .submit-btn.reset').addClass('hidden');

        $('#filter_affairs').submit();
    });

    // Вывод кнопки сброса
    $('#filter_affairs input, #filter_affairs select').change(function () {
        $('.wrap-affairs .wrap-filter .submit-btn.reset').removeClass('hidden');
    });
});
