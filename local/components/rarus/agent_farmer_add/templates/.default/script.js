$(document).ready(function(){
    //check errors
    $('form.add_form input[type=button].submit-btn').on('click', function(e){
        $('form.add_form').trigger('submit');
    });
    $('form.add_form').on('submit', function(e){
        var err = '';
        var error_flag = false;
        var rowObj;

        var formObj = $(this);

        //check last name
        var checkField = formObj.find('input[name="last_name"]');
        if(checkField.val().toString().replace(/ /g, '') == '')
        {
            err = 'Укажите фамилию';
            error_flag = true;
            rowObj = checkField.parents('.row');
            rowObj.addClass('error');
            if(rowObj.find('.row_err').length == 0)
            {
                rowObj.find('.row_val').append('<div class="row_err">' + err +  '</div>');
            }
            else
            {
                rowObj.find('.row_err').text(err);
            }
            err = '';
        }

        //check name
        checkField = formObj.find('input[name="name"]');
        if(checkField.val().toString().replace(/ /g, '') == '')
        {
            err = 'Укажите имя';
            error_flag = true;
            rowObj = checkField.parents('.row');
            rowObj.addClass('error');
            if(rowObj.find('.row_err').length == 0)
            {
                rowObj.find('.row_val').append('<div class="row_err">' + err +  '</div>');
            }
            else
            {
                rowObj.find('.row_err').text(err);
            }
            err = '';
        }

        //check nds
        checkField = formObj.find('select[name="nds_value"]');
        if(checkField.val() == 0)
        {
            err = 'Не выбран тип налогообложения';
            error_flag = true;
            rowObj = checkField.parents('.row');
            rowObj.addClass('error');
            if(rowObj.find('.row_err').length == 0)
            {
                rowObj.find('.row_val').append('<div class="row_err">' + err +  '</div>');
            }
            else
            {
                rowObj.find('.row_err').text(err);
            }
            err = '';
        }

        //check email
        checkField = formObj.find('input[name="email"]');
        rowObj = checkField.parents('.row');
        if (checkField.val().replace(/ /g, '') != ''
            && !checkEmailRfc(checkField.val())
        ) {
            err = 'Укажите корректный email';
            error_flag = true;
        }

        if(err != '')
        {
            rowObj = checkField.parents('.row');
            rowObj.addClass('error');
            if(rowObj.find('.row_err').length == 0)
            {
                rowObj.find('.row_val').append('<div class="row_err">' + err +  '</div>');
            }
            else
            {
                rowObj.find('.row_err').text(err);
            }
            err = '';
        }

        //проверяем телефон
        checkField = formObj.find('input[name="phone"]');
        rowObj = checkField.parents('.row');
        if(!checkIsPhone(checkField.val()))
        {
            err = 'Укажите корректный телефон';
            error_flag = true;
        }

        if(err != '')
        {
            rowObj = checkField.parents('.row');
            rowObj.addClass('error');
            if(rowObj.find('.row_err').length == 0)
            {
                rowObj.find('.row_val').append('<div class="row_err">' + err +  '</div>');
            }
            else
            {
                rowObj.find('.row_err').text(err);
            }
            err = '';
        }

        //проверяем регион
        checkField = formObj.find('select[name="region"]');
        rowObj = checkField.parents('.row');
        if(checkField.val() == '')
        {
            err = 'Укажите регион';
            error_flag = true;
        }
        if(err != '')
        {
            rowObj = checkField.parents('.row');
            rowObj.addClass('error');
            if(rowObj.find('.row_err').length == 0)
            {
                rowObj.find('.row_val').append('<div class="row_err">' + err +  '</div>');
            }
            else
            {
                rowObj.find('.row_err').text(err);
            }
            err = '';
        }

        //проверяем инн
        checkField = formObj.find('input[name="PROP__INN"]');
        var check_val = checkField.val().toString().replace(/[ ]/g, '');
        if(check_val.length == check_val.replace(/[^0-9]/g, '')
            ||
            (check_val.length != 10 && check_val.length != 12)
        ){
            err = 'ИНН должен состоять из 10 или 12 цифр';
            error_flag = true;
        }
        //проверяем успех проверки ИНН
        if(
            err === ''
            && !checkField.parents('.inn_row').hasClass('check_success')
        ){
            err = 'Необходима проверка ИНН';
            error_flag = true;
        }
        if(err != '')
        {
            rowObj = checkField.parents('.row');
            rowObj.addClass('error');
            if(rowObj.find('.row_err').length == 0)
            {
                rowObj.find('.row_val').append('<div class="row_err">' + err +  '</div>');
            }
            else
            {
                rowObj.find('.row_err').text(err);
            }
            err = '';
        }

        //check confirmation
        checkField = $('input[name="AUTH_REG_CONFIM_BY_AGENT"]');
        if(!error_flag && checkField.length == 1 && checkField.prop('checked') === false)
        {
            alert('Вы не отметили подтверждение, что предоставление персональных данных на третьих лиц производится с их согласия');
            e.preventDefault();
        }

        if(error_flag != false)
        {
            $('form.add_form .submit-btn').addClass('inactive');
            e.preventDefault();
        }
    });

    //remove error message after change
    $('.row_val input[type="text"], .row_val select[name="nds_value"], .row_val select[name="region"]').on('change', function(){
        var err_obj = $(this).parents('.row.error');
        if(err_obj.length == 1)
        {
            err_obj.removeClass('error');
        }

        //remove inactive class
        if($('form.add_form .row.error').length == 0)
        {
            $('form.add_form .submit-btn').removeClass('inactive');
        }
    });
});

//отправка ИНН в CF
function uploadInnAdd(argObj){
    //проверка ИНН на корректность
    var wObj = $(argObj).siblings('input[name="PROP__INN"]');
    //убираем ошибку, если была
    var err_obj = wObj.siblings('.row_err:first');
    var err_val = '';
    if(err_obj.length != 0){
        err_obj.parents('.row').removeClass('error');
        err_obj.remove();
    }

    //работаем со значением
    if(wObj.length == 1){
        var temp_val = wObj.val().toString().replace(' ', '');
        if(temp_val.length != 10
            && temp_val.length != 12
        ){
            //ошибка в данных ИНН
            err_val = 'ИНН должен состоять из 10 или 12 цифр';

            err_obj = wObj.siblings('.row_err:first');
            if(err_obj.length == 0){
                wObj.after('<div class="row_err"></div>');
                err_obj = wObj.siblings('.row_err');
            }

            err_obj.html(err_val).parents('.row').addClass('error');
        }
        else{
            //корректное значение ИНН -> проверяем на сервере
            $.post('/ajax/uploadContur.php',{
                inn_val: temp_val,
                u_type: $(argObj).parents('.auth-form').find('input[type="hidden"][name="TYPE"]').val()
            }, function(mes){
                if (mes == 1 || mes == 'null') {
                    err_val = 'Проверьте корректность введенного значения ИНН.';
                }else if (mes == 2) {
                    err_val = 'Данный ИНН уже зарегистрирован в системе.';
                }else{
                    var res_val = $.parseJSON(mes);
                    var show_name = '';
                    if (typeof res_val.IP != 'undefined'
                        || typeof res_val.UL != 'undefined'
                    ){
                        //проверены данные для ИП или иного юр. лица -> ставим флаг успеха
                        wObj.addClass('check_success').attr('readonly', 'readonly').parents('.row').addClass('check_success');

                        //также добавляем остальные связанные с ИНН свойства
                        if (typeof res_val.IP != 'undefined') {
                            //для ИП
                            wObj.after('<input type="hidden" name="PROP__REG_DATE" value="' + res_val.IP.registrationDate.toString().replace(/(\d\d\d\d)\-(\d\d)\-(\d\d)/g, '$3.$2.$1') +'" />');
                            wObj.after('<input type="hidden" name="PROP__IP_FIO" value="' + res_val.IP.fio + '" />');
                            wObj.after('<input type="hidden" name="PROP__OGRN" value="' + res_val.ogrn + '" />');
                            wObj.after('<input type="hidden" name="PROP__OKPO" value="' + res_val.IP.okpo + '" />');
                            wObj.after('<input type="hidden" name="PROP__UL_TYPE" value="ip" />');
                            show_name = res_val.IP.fio;
                        }else{
                            //для других типов юр. лиц
                            wObj.after('<input type="hidden" name="PROP__FULL_COMPANY_NAME" value="' + res_val.UL.legalName.full.toString().replace(/\"/g, '&quot;') + '" />');
                            show_name = res_val.UL.legalName.full.toString().replace(/\"/g, '&quot;');

                            //собираем значение адреса
                            var adres = '';
                            //регион
                            if (typeof res_val.UL.legalAddress.parsedAddressRF.regionName != 'undefined'
                                && typeof res_val.UL.legalAddress.parsedAddressRF.regionName.topoValue != 'undefined'
                            ) {
                                adres += res_val.UL.legalAddress.parsedAddressRF.regionName.topoValue;
                            }
                            if (typeof res_val.UL.legalAddress.parsedAddressRF.regionName != 'undefined'
                                && typeof res_val.UL.legalAddress.parsedAddressRF.regionName.topoShortName != 'undefined'
                            ) {
                                adres += ' '+res_val.UL.legalAddress.parsedAddressRF.regionName.topoShortName+'.';
                            }
                            //район
                            if (typeof res_val.UL.legalAddress.parsedAddressRF.district != 'undefined'
                                && typeof res_val.UL.legalAddress.parsedAddressRF.district.topoShortName != 'undefined'
                            ) {
                                adres += (adres != '' ? ', ' : '') + res_val.UL.legalAddress.parsedAddressRF.district.topoShortName;
                            }
                            if (typeof res_val.UL.legalAddress.parsedAddressRF.district != 'undefined'
                                && typeof res_val.UL.legalAddress.parsedAddressRF.district.topoValue != 'undefined'
                            ) {
                                adres += res_val.UL.legalAddress.parsedAddressRF.district.topoValue;
                            }
                            //город
                            if (typeof res_val.UL.legalAddress.parsedAddressRF.city != 'undefined'
                                && typeof res_val.UL.legalAddress.parsedAddressRF.city.topoShortName != 'undefined'
                            ) {
                                adres += (adres != '' ? ', ' : '') + res_val.UL.legalAddress.parsedAddressRF.city.topoShortName + '. ';
                            }
                            if (typeof res_val.UL.legalAddress.parsedAddressRF.city != 'undefined'
                                && typeof res_val.UL.legalAddress.parsedAddressRF.city.topoValue != 'undefined'
                            ) {
                                adres += res_val.UL.legalAddress.parsedAddressRF.city.topoValue;
                            }
                            //поселение
                            if (typeof res_val.UL.legalAddress.parsedAddressRF.settlement != 'undefined'
                                && typeof res_val.UL.legalAddress.parsedAddressRF.settlement.topoShortName != 'undefined'
                            ) {
                                adres += (adres != '' ? ', ' : '') + res_val.UL.legalAddress.parsedAddressRF.settlement.topoShortName + '. ';
                            }
                            if (typeof res_val.UL.legalAddress.parsedAddressRF.settlement != 'undefined'
                                && typeof res_val.UL.legalAddress.parsedAddressRF.settlement.topoValue != 'undefined'
                            ) {
                                adres += res_val.UL.legalAddress.parsedAddressRF.settlement.topoValue;
                            }
                            //улица
                            if (typeof res_val.UL.legalAddress.parsedAddressRF.street != 'undefined'
                                && typeof res_val.UL.legalAddress.parsedAddressRF.street.topoShortName != 'undefined'
                            ) {
                                adres += (adres != '' ? ', ' : '') + res_val.UL.legalAddress.parsedAddressRF.street.topoShortName + '. ';
                            }
                            if (typeof res_val.UL.legalAddress.parsedAddressRF.street != 'undefined'
                                && typeof res_val.UL.legalAddress.parsedAddressRF.street.topoValue != 'undefined'
                            ) {
                                adres += res_val.UL.legalAddress.parsedAddressRF.street.topoValue;
                            }
                            //дом
                            if (typeof res_val.UL.legalAddress.parsedAddressRF.house != 'undefined'
                                && typeof res_val.UL.legalAddress.parsedAddressRF.house.topoShortName != 'undefined'
                            ) {
                                adres += (adres != '' ? ', ' : '') + res_val.UL.legalAddress.parsedAddressRF.house.topoShortName + ' ';
                            }
                            if (typeof res_val.UL.legalAddress.parsedAddressRF.house != 'undefined'
                                && typeof res_val.UL.legalAddress.parsedAddressRF.house.topoValue != 'undefined'
                            ) {
                                adres += res_val.UL.legalAddress.parsedAddressRF.house.topoValue;
                            }
                            //корпус, этаж
                            if (typeof res_val.UL.legalAddress.parsedAddressRF.bulk != 'undefined'
                                && typeof res_val.UL.legalAddress.parsedAddressRF.bulk.topoValue != 'undefined'
                            ) {
                                adres += (adres != '' ? ', ' : '') + res_val.UL.legalAddress.parsedAddressRF.bulk.topoValue;
                            }
                            //помещение, офис
                            if (typeof res_val.UL.legalAddress.parsedAddressRF.flat != 'undefined'
                                && typeof res_val.UL.legalAddress.parsedAddressRF.flat.topoValue != 'undefined'
                            ) {
                                adres += (adres != '' ? ', ' : '');
                                if (typeof res_val.UL.legalAddress.parsedAddressRF.flat.topoShortName != 'undefined') {
                                    adres += res_val.UL.legalAddress.parsedAddressRF.flat.topoShortName + ' ';
                                }
                                adres += res_val.UL.legalAddress.parsedAddressRF.flat.topoValue;
                            }
                            wObj.after('<input type="hidden" name="PROP__YUR_ADRESS" value="' + adres + '" />');
                            wObj.after('<input type="hidden" name="PROP__OGRN" value="' + res_val.ogrn + '" />');
                            wObj.after('<input type="hidden" name="PROP__OKPO" value="' + res_val.UL.okpo + '" />');
                            wObj.after('<input type="hidden" name="PROP__KPP" value="' + res_val.UL.kpp + '" />');
                            wObj.after('<input type="hidden" name="PROP__REG_DATE" value="' + res_val.UL.registrationDate.toString().replace(/(\d\d\d\d)\-(\d\d)\-(\d\d)/g, '$3.$2.$1') + '" />');

                            if(typeof res_val.UL.heads != 'undefined'
                                && typeof res_val.UL.heads[0] != 'undefined'
                                && typeof res_val.UL.heads[0].fio != 'undefined'
                            ) {
                                wObj.after('<input type="hidden" name="PROP__FIO_DIR" value="' + res_val.UL.heads[0].fio + '" />');
                            }else if(typeof res_val.UL.history.heads != 'undefined'
                                && typeof res_val.UL.history.heads[0] != 'undefined'
                                && typeof res_val.UL.history.heads[0].fio != 'undefined')
                            {
                                wObj.after('<input type="hidden" name="PROP__FIO_DIR" value="' + res_val.UL.history.heads[0].fio + '" />');
                            }

                            wObj.after('<input type="hidden" name="PROP__UL_TYPE" value="ul" />');
                        }

                        //выводим название организации
                        if((show_name != '') && ($('#full-inn-name').length <= 0)){
                            wObj.parents('.inn_row').after('<div class="line row" id="full-inn-name"><div class="row_head">Полное название организации</div><div class="row_val one_line"><textarea title="Заполняется автоматически после валидации ИНН" name="" readonly="readonly">' + show_name + '</textarea></div></div>');
                        }else if((show_name != '') && ($('#full-inn-name').length > 0)) {
                            $('#full-inn-name').html('<div class="row_head">Полное название организации</div><div class="row_val one_line"><textarea title="Заполняется автоматически после валидации ИНН" name="" readonly="readonly">' + show_name + '</textarea></div>');
                        }
                    }else{
                        //ошибка при проверке данных
                        err_val = 'Проверьте корректность введенного значения ИНН.';
                    }
                }

                //если есть ошибка - выводим её
                if(err_val != ''){
                    err_obj = wObj.siblings('.row_err:first');
                    if(err_obj.length == 0){
                        wObj.after('<div class="row_err"></div>');
                        err_obj = wObj.siblings('.row_err');
                    }

                    err_obj.html(err_val).parents('.row').addClass('error');
                }
            });
        }
    }
}

function showAgentPolicy()
{
    $('.content-form.policy_page.public_form').toggleClass('active');
    $('body').toggleClass('disable_scroll');
}