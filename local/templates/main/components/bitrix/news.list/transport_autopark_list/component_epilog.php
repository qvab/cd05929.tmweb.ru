<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();?>
<?
if ($_REQUEST['autopark'] > 0) {
    CModule::IncludeModule('iblock');
    if ($_REQUEST['deactivate']) {
        $prop = array(
            'ACTIVE' => rrsIblock::getPropListKey('transport_autopark', 'ACTIVE', 'no')
        );
    }
    elseif ($_REQUEST['activate']) {
        $prop = array(
            'ACTIVE' => rrsIblock::getPropListKey('transport_autopark', 'ACTIVE', 'yes')
        );
    }

    CIBlockElement::SetPropertyValuesEx(
        $_REQUEST['autopark'],
        rrsIblock::getIBlockId('transport_autopark'),
        $prop
    );
    $el = new CIBlockElement;
    $res = $el->Update($_REQUEST['autopark'], array('TIMESTAMP_X' => date('d.m.Y H:i:s')));

    LocalRedirect($APPLICATION->GetCurPageParam(null, ['autopark',]));
}
?>
<script type="application/javascript">
    var stop_slide_anim = 0;

    $(document).ready(function() {
        $('.list_page_rows .line_area .line_inner, .list_page_rows .line_area .line_additional .hide_but').on('click', function(){
            if(stop_slide_anim == 0)
            {
                stop_slide_anim = 1;
                var wObj = $(this).parents('.line_area');
                wObj.toggleClass('active');
                if(!wObj.hasClass('active'))
                {
                    wObj.find('form.line_additional').slideUp(300, function(){
                        stop_slide_anim = 0;
                    });
                    wObj.find('.arw_list').removeClass('arw_icon_open').addClass('arw_icon_close');
                }
                else
                {
                    wObj.find('form.line_additional').slideDown(300, function(){
                        stop_slide_anim = 0;
                    });
                    wObj.find('.arw_list').removeClass('arw_icon_close').addClass('arw_icon_open');
                }
            }
        });
    });
</script>