<?php
/**
 * Created by PhpStorm.
 * User: bragev
 * Date: 22.05.2018
 * Time: 10:04
 */
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
//require_once $_SERVER['DOCUMENT_ROOT'].'/local/php_interface/classes/pdf.php';

if(isset($_GET['PDF'])){

    if(isset($_GET['path'])){
        $pdf = new pdf();
        echo $pdf->HtmlToPDF($_SERVER['DOCUMENT_ROOT'].$_GET['path'],'I');
    }
    die();
}

$document_dir = $_SERVER['DOCUMENT_ROOT'].'/upload/tmp/';


$filelist = glob($document_dir."*.html");

if((sizeof($filelist))&&(is_array($filelist))){
    ?>
    <h1>Список HTML документов для конвертирования в PDF</h1>
    <ul class="doc_list"><?
    for($i=0,$c=sizeof($filelist);$i<$c;$i++){
        $file_info = pathinfo($filelist[$i]);
        echo '<li><a target="_blank" href="/upload/tmp/'.$file_info['basename'].'">'.$file_info['basename'].'</a> => <a target="_blank" class="pdf" href="/test_pdf.php?PDF&path=/upload/tmp/'.$file_info['basename'].'">'.$file_info['filename'].'.pdf</a></li>';
    }
    ?></ul><?
}

?>


<?