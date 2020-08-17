/**
 * @author: Vitaly Melnik <vitali@rarus.ru>
 */

$(function () {

    /**
     * Добавляет строчку нового тарифа
     */
    $('.wrap-freight-tariff-update .submit-btn.add-row').click(function () {
        $('.wrap-freight-tariff-update .new-tariffs').append(getRowTariff(iNewRowTariff++));
    });


    /**
     * Удаляет строчку нового тарифа
     */
    $('.wrap-freight-tariff-update .new-tariffs').on('click', '.btn-delete-tariff', function () {
        var rowId = $(this).attr('row');
        $('.wrap-freight-tariff-update .new-tariffs .tariff-item[row = ' + rowId + ']').remove();

        // Валидация всей формы
        if($('.wrap-freight-tariff-update #all_tariff').valid()) {
            $('.wrap-freight-tariff-update .tariff-block-btn .all-save').show();
        } else {
            $('.wrap-freight-tariff-update .tariff-block-btn .all-save').hide();
        }
    });


    /**
     * Удаляет строчку текущего тарифа
     */
    $('.wrap-freight-tariff-update .current-tariffs').on('click', '.btn-delete-tariff', function () {
        var intervalFrom = $(this).closest('.tariff-item').attr("from");
        var intervalTo = $(this).closest('.tariff-item').attr("to");
        $('.wrap-freight-tariff-update .current-tariffs .tariff-item[from = ' + intervalFrom + '][to = ' + intervalTo + ']').remove();
        $('.wrap-freight-tariff-update .current-tariffs').prepend('<input type="hidden" name="REMOVE_TARIFF[]" value="'+intervalFrom+'_'+intervalTo+'" />');

        // Валидация всей формы
        if($('.wrap-freight-tariff-update #all_tariff').valid()) {
            $('.wrap-freight-tariff-update .tariff-block-btn .all-save').show();
        } else {
            $('.wrap-freight-tariff-update .tariff-block-btn .all-save').hide();
        }
    });


    /**
     * Изменение текущего тарифа
     */
    $('.wrap-freight-tariff-update .current-tariffs').on('change', 'input[type=text]', function () {

        // Скрываем/Отображаем кнопку сохранения одного тарифа
        var bIsSowBtnItem = true;
        $(this).closest('.tariff-item').find('input[type=text]').each(function (index, value) {
            if(!$(value).val()) {
                bIsSowBtnItem = false;
            }
        });

        if(bIsSowBtnItem) {
            $(this).closest('.tariff-item').find('.save-tariff').show();
        } else {
            $(this).closest('.tariff-item').find('.save-tariff').hide();
        }

        // Валидация всей формы
        if($('.wrap-freight-tariff-update #all_tariff').valid()) {
            $('.wrap-freight-tariff-update .tariff-block-btn .all-save').show();
        } else {
            $('.wrap-freight-tariff-update .tariff-block-btn .all-save').hide();
        }
    });


    /**
     * Изменение нового тарифа
     */
     $('.wrap-freight-tariff-update .new-tariffs').on('change', 'input[type=text]', function () {

         // Скрываем/Отображаем кнопку сохранения одного тарифа
         var bIsSowBtnItem = true;
         $(this).closest('.tariff-item').find('input[type=text]').each(function (index, value) {
             if(!$(value).val()) {
                 bIsSowBtnItem = false;
             }
         });

         if(bIsSowBtnItem) {
             $(this).closest('.tariff-item').find('.add-tariff').show();
         } else {
             $(this).closest('.tariff-item').find('.add-tariff').hide();
         }

         // Валидация всей формы
         if($('.wrap-freight-tariff-update #all_tariff').valid()) {
             $('.wrap-freight-tariff-update .tariff-block-btn .all-save').show();
         } else {
             $('.wrap-freight-tariff-update .tariff-block-btn .all-save').hide();
         }
     });


    /**
     * Обработка обновления одного тарифа
     */
    $('.wrap-freight-tariff-update .current-tariffs').on('click', '.submit-btn.save-tariff', function () {

        // Защита от дублей клика
        var btn = $(this);
        if(btn.data('save_tariff')) {
            return;
        }
        btn.data('save_tariff', true);

        try {

            var obTariffBlock = $(this).closest('.tariff-item');

            // Текущие значения
            var intervalFrom = obTariffBlock.attr("from");
            var intervalTo = obTariffBlock.attr("to");

            if(!intervalFrom) {
                throw new Error('Не удалось определить текущее значение "Расстояние от"');
            }

            if(!intervalTo) {
                throw new Error('Не удалось определить текущее значение "Расстояние до"');
            }

            // Новые значения
            var sKmFrom =  obTariffBlock.find('input.km-from').val();
            var sKmTo   =  obTariffBlock.find('input.km-to').val();
            var sDays   =  obTariffBlock.find('input.days').val();
            var sTariff =  obTariffBlock.find('input.tariff').val();

            if(!sKmFrom) {
                throw new Error('Не удалось определить новое значение "Расстояние от"');
            }

            if(!sKmTo) {
                throw new Error('Не удалось определить новое значение "Расстояние до"');
            }

            if(!sDays) {
                throw new Error('Не удалось определить новое значение "Кол-во дней в рейсе"');
            }

            if(!sTariff) {
                throw new Error('Не удалось определить новое значение "Тарифная ставка"');
            }

            $.ajax({
                url: '',
                data: {
                    AJAX: 'Y',
                    UPDATE_TARIFF : 'Y',
                    KEY_FROM : intervalFrom,
                    KEY_TO : intervalTo,
                    FROM : sKmFrom,
                    TO : sKmTo,
                    DAYS : sDays,
                    TARIFF : sTariff,
                    sessid : BX.bitrix_sessid()
                },
                type: 'GET',
                dataType: "json",
                cache: false,
                success: function(json){

                    if(json) {
                        if(json.result) {

                            obTariffBlock.remove();
                            $('.wrap-freight-tariff-update .current-tariffs').append(json.htmlRowTariff);
                            sortTariff();
                            blink('.wrap-freight-tariff-update .current-tariffs .tariff-item[from="'+json.from+'"][to="'+json.to+'"]');

                        } else {
                            alert('Ошибка обновления тарифа: ' + json.errorMessage);
                        }
                        btn.data('save_tariff', false);
                        stopBackLoad();
                    }
                },
                error: function(){
                    btn.data('save_tariff', false);
                    alert('Ошибка запроса, при повторных ошибках сообщите администратору.');
                },
                beforeSend: function () {
                    startBackLoad();
                }
            });

        } catch (e) {
            btn.data('save_tariff', false);
            alert('Ошибка обновления тарифа: ' + e.message );
        }
    });


    /**
     * Обработка добавления нового тарифа
     */
    $('.wrap-freight-tariff-update .new-tariffs').on('click', '.submit-btn.add-tariff', function () {


        // Защита от дублей клика
        var btn = $(this);
        if(btn.data('add_tariff')) {
            return;
        }
        btn.data('add_tariff', true);

        try {

            var obTariffBlock = $(this).closest('.tariff-item');

            // Новые значения
            var sKmFrom =  obTariffBlock.find('input.km-from').val();
            var sKmTo   =  obTariffBlock.find('input.km-to').val();
            var sDays   =  obTariffBlock.find('input.days').val();
            var sTariff =  obTariffBlock.find('input.tariff').val();


            if(!sKmFrom) {
                throw new Error('Не удалось определить значение поля "Расстояние от"');
            }

            if(!sKmTo) {
                throw new Error('Не удалось определить значение поля "Расстояние до"');
            }

            if(!sDays) {
                throw new Error('Не удалось определить значение поля "Кол-во дней в рейсе"');
            }

            if(!sTariff) {
                throw new Error('Не удалось определить значение поля "Тарифная ставка"');
            }

            $.ajax({
                url: '',
                data: {
                    AJAX: 'Y',
                    ADD_TARIFF : 'Y',
                    FROM : sKmFrom,
                    TO : sKmTo,
                    DAYS : sDays,
                    TARIFF : sTariff,
                    sessid : BX.bitrix_sessid()
                },
                type: 'GET',
                dataType: "json",
                cache: false,
                success: function(json){

                    if(json) {
                        if(json.result) {
                            obTariffBlock.remove();
                            $('.wrap-freight-tariff-update .current-tariffs').append(json.htmlRowTariff);
                            sortTariff();
                            blink('.wrap-freight-tariff-update .current-tariffs .tariff-item[from="'+json.from+'"][to="'+json.to+'"]');
                        } else {
                            alert('Ошибка обновления тарифа: ' + json.errorMessage);
                        }

                        btn.data('add_tariff', false);
                        stopBackLoad();
                    }
                },
                error: function(){
                    btn.data('add_tariff', false);
                    alert('Ошибка запроса, при повторных ошибках сообщите администратору.');
                    stopBackLoad();
                },
                beforeSend: function () {
                    startBackLoad();
                }
            });

        } catch (e) {
            btn.data('add_tariff', false);
            alert('Ошибка добавления тарифа: ' + e.message );
        }
    });


    /**
     * Вводим только числа
     */
    $('.wrap-freight-tariff-update .tariff-list').on('keyup', '.tariff-item input[type=text]', function () {

        var value = $(this).val();

        if(value.length > 0) {
            value = value.replace(/\s/g, '');
            value = value.replace(/[^\d\.]/g, '');
        }
        $(this).val(value);
    });


    /**
     * Отправка формы
     */
    $('.wrap-freight-tariff-update .submit-btn.all-save').click(function () {

        // Защита от дублей отправки
        var btn = $(this);
        if(btn.data('all_save')) {
            return;
        }
        btn.data('all_save', true);

        var form = $('.wrap-freight-tariff-update #all_tariff');
        if(form.valid()) {
            form.submit();
            startBackLoad();
        } else {
            $(this).hide();
            btn.data('all_save', false);
        }
    });
});


/**
 * Моргун элемента
 * @param selector
 */
function blink(selector){
    $(selector).fadeOut('slow', function(){
        $(this).fadeIn('slow', function(){});
    });
}


/**
 * Сортирует список тарифов
 */
function sortTariff() {

    var items = $('.wrap-freight-tariff-update .tariff-list .current-tariffs .tariff-item');
    var arItems = $.makeArray(items);

    arItems .sort(function(a, b) {
        return $(a).data("sort") - $(b).data("sort")
    });

    $(arItems).appendTo('.wrap-freight-tariff-update .tariff-list .current-tariffs');
}