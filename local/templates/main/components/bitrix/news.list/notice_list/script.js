
$(document).ready(function(){

 $('.list_page_rows.notifications .line_area .line_inner .accept .submit-btn').on('click',function (e) {
     e.preventDefault();
     e.stopPropagation();
     var pair_id = $(this).attr('data-pair-id');
     var email = $(this).attr('data-email');
     var uid = $(this).attr('data-uid');
     $.post('/ajax/getFarmerPairText.php', {
         pair_id: pair_id,
     }, function (mes) {
         if (mes != 0) {
             url_val = mes;
             var haveEmail = 0;
             if((typeof email != "undefined")&&(email.length>0)){
                 haveEmail = 1;
             }
             showTextLinkFeedbackForm('Текст для поставщика',haveEmail,url_val,0,uid,'notice_farmer');
         }
     });
 })

});