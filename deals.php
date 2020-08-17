<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Макет сделок");
?>

<script src="/local/templates/main/js/pure_swipe_lib.js" type="text/javascript"></script>

<div class="page_sub_title"><span class="bold">Ячмень Яровой</span><span class="num">/ Сделка #1376 от 18.09.2019</span></div>

<div class="tab_form deals">
	<div class="tab_form_inner" id="tab_form_inner">
		<div class="item active">
			<span>Этапы</span>
		</div>
		<div class="item">
			<a href="#">Информация</a>
		</div>
		<div class="item">
			<a href="#">История операций</a>
		</div>
		<div class="item last">
			<a href="#">Документы</a>
		</div>
		<div class="clear"></div>
	</div>
</div>

<a class="go_back cross" href="javascript: void(0);"></a>

<div class="list_page_rows deals">

	<div class="line_area deal_done with_info">
		<div class="line_inner">
			<div class="indicator"></div>
			<div class="step_num">1</div>
			<div class="name">Поиск поставщика</div>
			<div class="clip_item"></div>
			<div class="clear l"></div>
			<div class="clip"></div>
		</div>

		<div class="line_additional">
			<div class="line_additional_inner">
				<div class="prop_area i0"><span class="sub_step_num">1.1</span> Отправить бланк уведомлений</div>
				<div class="prop_area i1"><span class="sub_step_num">1.2</span> Подписать ЭЦП или что-то еще <a href="#">Contract.doc</a></div>
				<div class="prop_area i2 last"><span class="sub_step_num">1.3</span> <input type="button" class="submit-btn" value="Принять запрос "/></div>	
			</div>
			<!--<a href="#" class="note_area"><span>Инструкция<br/>по заполнению полей</span><div class="ico"></div></a>-->
		</div>
	</div>

	<div class="line_area deal_done with_info">
		<div class="line_inner">
			<div class="indicator"></div>
			<div class="step_num">2</div>
			<div class="name">Поиск поставщика</div>
			<div class="clip_item"></div>
			<div class="clear l"></div>
		</div>

		<div class="line_additional">
			<div class="line_additional_inner">
				<div class="prop_area i0"><span class="sub_step_num">2.1</span> Отправить бланк уведомлений</div>
				<div class="prop_area i1"><span class="sub_step_num">2.2</span> Подписать ЭЦП или что-то еще <a href="#">Contract.doc</a></div>
				<div class="prop_area i2 last"><span class="sub_step_num">2.3</span> <input type="button" class="submit-btn" value="Принять запрос "/></div>
			</div>
			<!--<a href="#" class="note_area"><span>Инструкция<br/>по заполнению полей</span><div class="ico"></div></a>-->
		</div>
	</div>

	<div class="line_area deal_done with_info">
		<div class="line_inner">
			<div class="indicator"></div>
			<div class="step_num">3</div>
			<div class="name">Поиск поставщика</div>
			<div class="clip_item"></div>
			<div class="clear l"></div>
		</div>

		<div class="line_additional">
			<div class="line_additional_inner">
				<div class="prop_area i0"><span class="sub_step_num">3.1</span> Отправить бланк уведомлений</div>
				<div class="prop_area i1"><span class="sub_step_num">3.2</span> Подписать ЭЦП или что-то еще <a href="#">Contract.doc</a></div>
				<div class="prop_area i2 last"><span class="sub_step_num">3.3</span> <input type="button" class="submit-btn" value="Принять запрос "/></div>	
			</div>
			<!--<a href="#" class="note_area"><span>Инструкция<br/>по заполнению полей</span><div class="ico"></div></a>-->
		</div>
	</div>

	<div class="line_area current with_info">
		<div class="line_inner">
			<div class="indicator"></div>
			<div class="step_num">4</div>
			<div class="name">Поиск поставщика</div>
			<div class="clip_item"></div>
			<div class="clear l"></div>
			<div class="clip"></div>
		</div>

		<div class="line_additional">
			<div class="line_additional_inner">
				<div class="prop_area i0"><span class="sub_step_num">4.1</span> Отправить бланк уведомлений</div>
				<div class="prop_area i1"><span class="sub_step_num">4.2</span> Подписать ЭЦП или что-то еще <a href="#">Contract.doc</a></div>
				<div class="prop_area i2 last"><span class="sub_step_num">4.3</span> <input type="button" class="submit-btn" value="Принять запрос "/></div>
			</div>
			<!--<a href="#" class="note_area"><span>Инструкция<br/>по заполнению полей</span><div class="ico"></div></a>-->
		</div>
	</div>

	<div class="line_area with_info">
		<div class="line_inner">
			<div class="indicator"></div>
			<div class="step_num">5</div>
			<div class="name">Поиск поставщика</div>
			<div class="clip_item"></div>
			<div class="clear l"></div>
		</div>
	</div>

	<div class="line_area with_info">
		<div class="line_inner">
			<div class="indicator"></div>
			<div class="step_num">6</div>
			<div class="name">Поиск поставщика</div>
			<div class="clip_item"></div>
			<div class="clear l"></div>
		</div>
	</div>

	<div class="line_area">
		<div class="line_inner">
			<div class="indicator"></div>
			<div class="step_num">7</div>
			<div class="name">Поиск поставщика</div>
			<div class="clip_item"></div>
			<div class="clear l"></div>
		</div>
	</div>
	
</div>

<script type="text/javascript">
	var stop_slide_anim = 0;
	$(document).ready(function() {
		$('.list_page_rows .line_area.deal_done .line_inner, .list_page_rows .line_area.current .line_inner').on('click', function(){
			if(stop_slide_anim == 0)
			{
				stop_slide_anim = 1;
				var wObj = $(this).parents('.line_area');
				wObj.toggleClass('active');
				if(!wObj.hasClass('active'))
				{
					wObj.find('.line_additional').slideUp(300, function(){
						stop_slide_anim = 0;
					});
				}
				else
				{
					wObj.find('.line_additional').slideDown(300, function(){
						stop_slide_anim = 0;
					});
				}
			}
		});
	});
</script>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>