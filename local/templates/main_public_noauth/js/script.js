$(document).ready(function(){
    if(isMobileDevice()){
        $('body').addClass('mobile_device');
    }

    //start selects, radio & checkbox customization
    makeCustomForms();
    //fix select width when mobile orientation change
    window.addEventListener("orientationchange", function() {
        setTimeout(function(){
            var checkObj = $('.row .row_val input[type="text"]:first');
            var check_val = (checkObj.length == 1 ? checkObj.width() + 30 : 0)
            $('span.agro_select2_container').each(function(ind, cObj){
                if(check_val > 0)
                {
                    $(cObj).css('width', check_val + 'px');
                }
                else
                {
                    $(cObj).css('width', (parseInt($(this).parent().width()) - 20) + 'px');
                }
            });
        }, 50);
    }, false);

    //customize decs with space separators
    $('.decs_separators').each(function(ind, cObj){
        $(cObj).text(number_format($(cObj).text(), 0, '.', ' '));
    });

    $('.option-name').on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();

        var $this = $(this);
        $this.closest('.slide-description').toggleClass('active');
    });

    $(document).on('click', '.js-copy', function () {
        if(!$(this).hasClass('html_val')) {
            if (copyToClipboard($(this).parent().find('.copy-text').text())) {
                // alert('Текст скопирован в буфер обмена');
            }
        }//копирование html
        else{
            var argObj = $(this);
            var sHtmlVal = argObj.siblings('.copy-text').html().replace(/\<div[^>]*cancel_counter_offer_area.*?\<\/div\>/gi, '');

            //очистка html
            $.post('/ajax/normalizeHtmlText.php',{ html: sHtmlVal },function(mes){
                copyLinkHtml(mes);

                //показываем текст об успешном копировании
                argObj.siblings('.copy-text').before('<div class="copy-textallowed">Сообщение скопировано в буфер обмена</div>');
                var copyObj = argObj.siblings('.copy-textallowed:last');
                setTimeout(function(){
                    copyObj.remove();
                }, 3000);
            });
        }
    });
});

/**
 * Попытка копирования в буфер обмена текущей ссылки для поставщика
 * @param argObj        - ссылка на dom элемент
 * @param volumeVal     - объем
 * @param mode          - режим работы (farmer|client)
 */
function copyLinkHtml(argObj){
    // var wObj = argObj.siblings('.copy-text');
    // var text = wObj.html();
    var text = argObj;

    //убираем теги и лишние данные из копирования (чтобы корректно отображалось в мессенджерах)
    //меняем символ &nbsp; на пробелы
    text = text.replace(new RegExp("&nbsp;", 'g'), " ");
    //меняем переносы
    text = text.replace(/\<br\/?\>/gi,"\n");
    //убираем оставшиеся теги открывающие
    text = text.replace(/\<(a|b|u|i)[^>]*\>/gi,"");
    //убираем оставшиеся теги закрывающие
    text = text.replace(/\<\/(a|b|u|i) *\>/gi,"");

    //if(copyToClipboard(text)){
    CopyClipboardText(text);
    //}
}

//функция копирует текст аргумента в буфер обмена (требует разрешения браузера, т.е. реального действия пользователя например клика по элементу)
//можено использовать в синхронном аякс запросе (async: false)
function CopyClipboardText(arg) {
    var copyFrom = document.createElement("textarea");
    document.body.appendChild(copyFrom);
    copyFrom.textContent = arg;
    copyFrom.select();
    document.execCommand("copy");
    copyFrom.remove();
}

/*
* Определяет с мобильного ли устройства пришел пользователь (проверяется на уровне CSS (через media hover и pointer) и на уровне javascript (наличие события ontouchstart))
* проверка идет через объект с id mobile_check, добавленный в конец body в footer.php шаблона сайта
* @return Boolean - возвращает true, если устройство определено как мобильное
* */
function isMobileDevice() {
    return (typeof device_type != 'undefined' && device_type === 'm');
}

//пересчитывает и устанавливает стоимость агентской услуги
//objArg - объект line_area в списке товаров
function recountCounterOfferPartnerPrice(objArg){
    if(
        typeof counter_option_contract !== 'undefined'
        && typeof counter_option_lab !== 'undefined'
        && typeof counter_option_support !== 'undefined'
    ){
        var iPrice = 0, iCsmPrice = 0, iVolume = 0, bLabChecked = false, bSupportChecked = false, objPriceInput, objPricePerTonInput, iTemp = 0;

        //если в списке товаров
        var objWork, objTemp;
        var objWorkArea = objArg.find('.options_form');
        if(objWorkArea.length > 0) {
            objWork = objArg.find('.counter_href_area .partner_price_val');
            if (objWork.length > 0) {
                objPriceInput = objWork;
                iTemp = parseInt(objArg.find('.counter_href_area .tons_val').text().replace(' ', ''));
                if (!isNaN(iTemp)) {
                    iVolume = iTemp;
                }

                iTemp = parseInt(objArg.find('.counter_href_area .csm_price').text().replace(' ', ''));
                if (!isNaN(iTemp)) {
                    iCsmPrice = iTemp;
                }

                objTemp = objWorkArea.find('.val_adress.slide-description:not(.inactive) input[name="IS_ADD_CERT"]');
                if (
                    objTemp.length === 1
                    && objTemp.prop('checked') === true
                ) {
                    bLabChecked = true;
                }

                objTemp = objWorkArea.find('.val_adress.slide-description:not(.inactive) input[name="IS_AGENT_SUPPORT"]');
                if (
                    objTemp.length === 1
                    && objTemp.prop('checked') === true
                ) {
                    bSupportChecked = true;
                }

                objPricePerTonInput = objArg.find('.counter_href_area .partner_price_per_ton_val');
            }
        }

        //установление данных
        if(objPriceInput) {
            //расчет стоимости агентского договора
            iPrice += parseInt(Math.round((counter_option_contract / 10000.0) * iCsmPrice * iVolume));

            //добавление стоимости услуг лаборатории
            if (bLabChecked) {
                iPrice += counter_option_lab;
            }

            //добавление стоимости сопровождения сделки
            if (bSupportChecked) {
                iPrice += counter_option_support * iVolume;
            }

            //устанавливаем рассчитанное значение
            objPriceInput.text(number_format(iPrice, 0, '.', ' '));
            if(
                objPricePerTonInput
                && iVolume > 0
            ){
                objPricePerTonInput.text(number_format(Math.round(iPrice / iVolume), 0, '.', ' '));
            }
        }
    }
}

//Функция "перетаскивает" пункты в агентском предложении после обновления данных (задержка в 50мс нужна для того, чтобы не мешать с физическим переключением чекбоксов)
//objArg - контейнер form.options_form
function moveOptionsAfterUpdate(objArg){
    var objTemp, i = 0;
    setTimeout(function(){
        //находим убранные опции среди "нажатых"
        objArg.find('.checked_options .slide-description:not(.inactive) input[type="checkbox"]').each(function(cInd, cObj){
            //если опция убрана, то отображаем данные в другом списке
            if($(cObj).prop('checked') !== true){
                // //работаем в колонке div.checked_options
                $(cObj).prop({disabled: true, checked: true}).parents('.val_adress').addClass('inactive').find('.custom_input').addClass('checked');
                // //работаем в колонке div.no_checked_options
                objArg.find('.no_checked_options input[type="checkbox"][name="' + $(cObj).attr('name') + '"]').prop({checked: false, disabled: false}).parents('.val_adress').removeClass('inactive').find('.custom_input').removeClass('checked');
            }
        });

        //находим поставленные опции среди "не нажатых"
        objArg.find('.no_checked_options .slide-description:not(.inactive) input[type="checkbox"]').each(function(cInd, cObj){
            //если опция поставлена, то отображаем данные в другом списке
            if($(cObj).prop('checked') === true){
                //работаем в колонке div.no_checked_options
                $(cObj).prop({disabled: true, checked: false}).parents('.val_adress').addClass('inactive').find('.custom_input').removeClass('checked');
                //работаем в колонке div.checked_options
                objArg.find('.checked_options input[type="checkbox"][name="' + $(cObj).attr('name') + '"]').prop({checked: true, disabled: false}).parents('.val_adress').removeClass('inactive').find('.custom_input').addClass('checked');
            }
        });

        //скрываем пункт "Возможные к заказу", если нужно
        var objUnselectTitle = objArg.find('.no_checked_options .message-add');
        if(objUnselectTitle.length === 1) {
            if (objArg.find('.no_checked_options .slide-description:not(.inactive)').length > 0) {
                objUnselectTitle.removeClass('inactive');
            } else {
                objUnselectTitle.addClass('inactive');
            }
        }

        //убираем выделение кнопки
        objArg.find('.accept').addClass('empty');
    }, 50);
}