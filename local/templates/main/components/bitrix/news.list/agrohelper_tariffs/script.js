$(document).ready(function(){
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
        var tariffId = $(this).closest('.tariff-item').attr("tariffId");
        $('.wrap-freight-tariff-update .current-tariffs .tariff-item[tariffId = ' + tariffId + ']').remove();
        $('.wrap-freight-tariff-update .current-tariffs').prepend('<input type="hidden" name="REMOVE_TARIFF[]" value="'+tariffId+'" />');

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
    //$('.wrap-freight-tariff-update .current-tariffs').on('change', 'input[type=text]', function () {

        /*// Скрываем/Отображаем кнопку сохранения одного тарифа
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
        }*/

        // Валидация всей формы
        /*if($('.wrap-freight-tariff-update #all_tariff').valid()) {
            $('.wrap-freight-tariff-update .tariff-block-btn .all-save').show();
        } else {
            $('.wrap-freight-tariff-update .tariff-block-btn .all-save').hide();
        }*/
    //});

    /**
     * Изменение нового тарифа
     */
    $('.wrap-freight-tariff-update .new-tariffs').on('change', 'input[type=text]', function () {

        /*// Скрываем/Отображаем кнопку сохранения одного тарифа
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
        }*/

        // Валидация всей формы
        if($('.wrap-freight-tariff-update #all_tariff').valid()) {
            $('.wrap-freight-tariff-update .tariff-block-btn .all-save').show();
        } else {
            $('.wrap-freight-tariff-update .tariff-block-btn .all-save').hide();
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
});

