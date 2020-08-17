
$(document).ready(function() {
    $('.blacklist .unlink_but').on('click',function(){
        $.ajax({
            type: "POST",
            url: "/ajax/deleteFarmerFromBL.php",
            data: "uid="+$(this).attr('data-blid'),
            dataType: 'JSON',
            success: function(data){
                document.location.reload();
            }
        });
    });
    $('.bl .unlink_but_plus').on('click',function(){
        $.ajax({
            type: "POST",
            url: "/ajax/addClientToBL.php",
            data: "clid="+$(this).attr('data-client-id')+'&fid='+$(this).attr('data-farmer-id'),
            dataType: 'JSON',
            success: function(data){
                document.location.reload();
            }
        });
    });
});