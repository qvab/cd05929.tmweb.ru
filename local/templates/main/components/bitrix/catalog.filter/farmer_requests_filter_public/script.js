$(document).ready(function(){
    $('.submit-btn[type="submit"]').on('click', function(e) {
        var selectObj;
        $(this).parents('form').find('select').each(function(ind, sObj){
            selectObj = $(sObj);
            if (selectObj.val() == 0) {
                selectObj.attr('disabled', 'disabled');
            }
            else {
                selectObj.removeAttr('disabled');
            }
        });
    });
});