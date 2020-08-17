<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");?>
<?$APPLICATION->SetTitle('Генерация договора КП');?>
<div class="main_page_area">
    <h1 class="page_header">Генерация договора КП</h1>
    <?
    $iId = htmlspecialcharsbx($_GET['id']);
    if (!$iId) {
        ShowError('Ошибка! Не указан идентификатор профиля');
    }
    else {
        if (intval($iId) > 0) {
            $res = CIBlockElement::GetList(
                array('ID' => 'DESC'),
                array('IBLOCK_ID' => rrsIblock::getIBlockId('client_profile'), 'ACTIVE' => 'Y', 'ID' => $iId),
                false,
                false,
                array('ID', 'PROPERTY_CLIENT', 'PROPERTY_dkp2dap', 'PROPERTY_dkp2fca', 'PROPERTY_ds', 'PROPERTY_act_deal')
            );
            if ($ob = $res->Fetch()) {
                if (in_array($_GET['d'], array('dkp2dap', 'dkp2fca', 'ds', 'act_deal'))) {
                    $file_html = $_SERVER["DOCUMENT_ROOT"]."/upload/tmp/".$iId."_".$_GET['d'].".html";
                    $file_pdf = $_SERVER["DOCUMENT_ROOT"]."/upload/tmp/".$iId."_".$_GET['d'].".pdf";

                    unlink($file_html);
                    unlink($file_pdf);

                    $templateText = $ob['PROPERTY_'.strtoupper($_GET['d']).'_VALUE']['TEXT'];
                    $text = '<html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"></head><body style="width: 1024px; font-size: 10px;">';
                    $text .= "<style>
                        ul{line-height: 18px; margin: 0; padding: 0;}
                        p{line-height: 18px; text-indent: 24px; margin: 0 !important; padding: 0 !important;}
                        table tr td{text-align: left;}
                        .pay1 tr td{border: 1px solid #333333; color: #333333;}
                        .pay2{font-size: 9px;}
                        .pay2 .title td{text-align: center;}
                    </style>";
                    $text .= $templateText;
                    $text .= '</body></html>';

                    $f = fopen($file_html, 'w+');
                    fwrite($f, $text);
                    fclose($f);

                    $pdf = new pdf();
                    $pdf->HtmlToPDF($file_html, 'F', $_SERVER["DOCUMENT_ROOT"]."/upload/tmp/");

                    LocalRedirect("/upload/tmp/".$iId."_".$_GET['d'].".pdf");
                }
                else {
                    $k = 0;
                    if (trim($ob['PROPERTY_DKP2DAP_VALUE']['TEXT']) != '') {
                        $k++;
                        ?>
                        <a href="/admin/genpdf/?id=<?=$iId?>&d=dkp2dap" class="create_dkp" target="_blank">Сформировать договор КП-2 (CPT поставка)</a>
                    <?
                    }

                    if (trim($ob['PROPERTY_DKP2FCA_VALUE']['TEXT']) != '') {
                        $k++;
                        ?>
                        <a href="/admin/genpdf/?id=<?=$iId?>&d=dkp2fca" class="create_dkp" target="_blank">Сформировать договор КП-2 (FCA поставка)</a>
                    <?
                    }

                    if (trim($ob['PROPERTY_DS_VALUE']['TEXT']) != '') {
                        $k++;
                        ?>
                        <a href="/admin/genpdf/?id=<?=$iId?>&d=ds" class="create_dkp" target="_blank">Сформировать доп. соглашение к ДКП</a>
                    <?
                    }

                    if (trim($ob['PROPERTY_ACT_DEAL_VALUE']['TEXT']) != '') {
                        $k++;
                        ?>
                        <a href="/admin/genpdf/?id=<?=$iId?>&d=act_deal" class="create_dkp" target="_blank">Сформировать акт сдачи-приемки услуг</a>
                    <?
                    }

                    if ($k == 0) {
                        ShowError('Ошибка! У пользователя не найдено ни одного шаблона договора');
                    }
                ?>

                <?
                }
            }
            else {
                ShowError('Ошибка! Неверно указан идентификатор профиля');
            }
        }
        else {
            ShowError('Ошибка! Неверно указан идентификатор профиля');
        }
    }
    ?>
</div>
<style type="text/css">
    a.create_dkp {
        display: block;
        background: #47a1f0;
        font-size: 14px;
        padding: 24px;
        width: 320px;
        text-align: center;
        color: #FFFFFF;
        margin-bottom: 24px;
        text-decoration: none;
    }
    a.create_dkp:hover {
        background: #309cfa;
    }
</style>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>