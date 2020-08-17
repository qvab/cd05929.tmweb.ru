<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
$this->setFrameMode(true);
?>

<div class="prop_area adress_val dashboard_data">

    <h1 class="page_header">Активность пользователей</h1>
    <a class="go_back cross" href="<?=$arResult['BACKURL'];?>"></a>

    <div class="folder_header"><?=$arResult['title'];?></div>

    <?if(count($arResult['DATA']['USERS_DATA']) > 0){?>
        <div class="list_page_rows requests dashboard">
            <div class="line_area legend">
                <div class="line_inner">
                    <div class="name">Название компании</div>
                    <div class="id_date">Email</div>
                    <div class="tons">Агент</div>
                    <div class="price">Организатор</div>
                    <div class="clear"></div>
                </div>
            </div>
            <?foreach($arResult['DATA']['USERS_DATA'] as $cur_uid => $cur_data){?>
                <div class="line_area">
                    <div class="line_inner">
                        <div class="name"><a href="/profile/?uid=<?=$cur_uid?>"><?=$cur_data['NAME'];?></a></div>
                        <div class="id_date"><?=$cur_data['EMAIL'];?></div>
                        <div class="tons"><?=$arResult['DATA']['AGENT_DATA'][$cur_data['AGENT']];?></div>
                        <div class="price"><a href="/profile/?uid=<?=$cur_data['PARTNER']?>"><?=$arResult['DATA']['PARTNER_DATA'][$cur_data['PARTNER']];?></a></div>
                        <div class="clear"></div>
                    </div>
                </div>
            <?}?>
        </div>
    <?}else{?>
        <br/>
        <br/>
        <div>Записи не найдены</div>
    <?}?>
</div>