
$(document).ready(function() {
    $('.blacklist .unlink_but').on('click',function(){
        $.ajax({
            type: "POST",
            url: "/ajax/deleteFarmerFromBL.php",
            data: "uid="+$(this).attr('data-uid'),
            dataType: 'JSON',
            success: function(data){
                document.location.reload();
            }
        });
    });
});