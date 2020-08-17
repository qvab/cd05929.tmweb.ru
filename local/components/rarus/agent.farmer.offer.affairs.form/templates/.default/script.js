/**
 * @author: Vitaly Melnik <vitali@rarus.ru>
 */

/**
 * Обработка ошибок
 * @param _this
 * @param textError
 */
function showError(_this, textError) {
    console.error(textError);
    _this.children('.error-msg').text(textError).removeClass('hidden');
}



/**
 *
 */
$(function () {

    // Вывод формы добавления нового дела
    $('.wrap-affairs .btn-show-form-add-new').click(function () {
        var parent = $(this).closest('.wrap-affairs');
        parent.children('.form-add-new-affair').toggle( "slow", function () {
            parent.children('.btn-show-form-add-new').toggle();
        });
    });

    // Добавляем новое дело к предложению
    $('.wrap-affairs .add-new-affair').click(function () {

        // Защита от повторного нажатия
        var btn = $(this);
        if(btn.data('add_affair')) {
            return;
        }
        btn.data('add_affair', true);

        // Обертка
        var parent = $(this).closest('.wrap-affairs');

        // Валидация полей
        var bValid = true;

        // Дата
        var rowDate = parent.find('.fields .date-affair');
        var dateAffair = rowDate.find('input').val();
        if(!dateAffair) {
            rowDate.addClass('error');
            bValid = false;
        } else {
            rowDate.removeClass('error');
        }

        // Объем
        var rowVolume = parent.find('.fields .farmer-volume');
        var farmerVolume = rowVolume.find('input').val();
        if(!farmerVolume) {
            rowVolume.addClass('error');
            bValid = false;
        } else {
            rowVolume.removeClass('error');
        }

        // Цена
        var rowPrice = parent.find('.fields .expected-price');
        var expectedPrice = rowPrice.find('input').val();
        if(!expectedPrice) {
            rowPrice.addClass('error');
            bValid = false;
        } else {
            rowPrice.removeClass('error');
        }


        // Комментарий
        var comment = parent.find('.fields .comment textarea').val();

        // ИД предложения
        var offerId = parent.find('[name=FARMER_OFFER]').val();
        if(!offerId) {
            bValid = false;
            showError(parent, 'Не удалось получить ИД предложения')
        }

        var farmerID = parent.data('uid');


        if(bValid) {

            $('.wrap-affairs .error-msg').text('');

            // Отправляем AJAX запрос на добавление записи
            $.ajax({
                url: '',
                data: {
                    AJAX: 'Y',
                    ADD_AFFAIR: 'Y',
                    OFFER_ID: offerId,
                    DATE_AFFAIR: dateAffair,
                    FARMER_VOLUME: farmerVolume,
                    EXPECTED_PRICE: expectedPrice,
                    COMMENT: comment,
                    FARMER_ID: farmerID,
                    sessid : BX.bitrix_sessid()
                },
                type: 'GET',
                dataType: "json",
                cache: false,
                success: function(json){
                    if(json) {

                        if(json.result) {

                            // Выводим HTML
                            parent.html('<div class="add-form color-green">Дело по товару добавлено</div>');

                            if(json.href.length > 0){
                                location.href = json.href;
                            }



                        } else {

                            // Обработка ошибок
                            for (var key in json.errorsMessage) {

                                if(key == 'all') {
                                    showError(parent, json.errorsMessage[key]);
                                } else {

                                    if(json.errorsMessage[key]) {
                                        var row = parent.find('.'+key);
                                        row.find('.row_val .row_err').text(json.errorsMessage[key]);
                                        row.addClass('error');
                                    }
                                }
                            }
                        }

                        setTimeout('stopBackLoad()', 300);
                        btn.data('add_affair', false);
                    }
                },
                error: function(){
                    btn.data('add_affair', false);
                    setTimeout('stopBackLoad()', 300);
                    alert('Ошибка запроса, при повторных ошибках сообщите администратору.');
                },
                beforeSend: function () {
                    startBackLoad();
                }
            });

        } else {
            btn.data('add_affair', false);
        }
    });

});
