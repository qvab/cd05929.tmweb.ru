<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();?>
<?/*
//set filter value
if (isset($_REQUEST['region_id']) && is_numeric($_REQUEST['region_id']) && $_REQUEST['region_id'] > 0)
{?>
    <script type="text/javascript">
        $(document).ready(function(){
            $('form.region_filter select[name="region_id"]').val(<?=$_REQUEST['region_id'];?>).trigger('change');
        });
    </script>
<?}*/