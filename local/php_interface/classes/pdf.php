<?php
/**
 * Created by PhpStorm.
 * User: bragev
 * Date: 21.05.2018
 * Time: 17:54
 */

//require_once 'lib/tc-lib-pdf/Pdf/Tcpdf.php'; //новая библиотека
require_once 'lib/TCPDF/tcpdf.php';



/**
 * Class pdf
 * Создание и работа с PDF документами
 * на основе библиотеки TCPDF
 */
class pdf {

    public function __construct(){
        //для новой библиотеки, делаем autoload классов
        /*spl_autoload_register(function ($class_name) {
            $class_name = str_replace('Com/Tecnick',$_SERVER['DOCUMENT_ROOT'].'/local/php_interface/classes/lib/tc-lib-pdf',str_replace("\\","/",$class_name));
            include $class_name.=".php";
        });*/
    }


    /**
     * Конвертирования HTML файла в PDF
     * @param $source_html_path - адрес HTML файла
     * @param string $output - режим сохранения файла: I - отдача файла на экран, F - сохранение в файл
     * @param string $out_dir - папка в которую сохранится файл pdf в случае режима F
     */
    public function HtmlToPDF($source_html_path,$output = 'I',$out_dir = ''){

        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // set font
        $pdf->SetFont('dejavusans', '', 10);

        // add a page
        $pdf->AddPage();

        if(file_exists($source_html_path)) {
            $file_info = pathinfo($source_html_path);
            $html = file_get_contents($source_html_path);
            // output the HTML content
            $pdf->writeHTML($html, true, false, true, false, '');

            switch ($output){
                case 'I':
                    header("Content-type:application/pdf");
                    $pdf->Output($file_info['filename'].'.pdf', 'I');
                    return true;
                    break;
                case 'F':
                    if(file_exists($out_dir)){
                        $pdf->Output($out_dir.'/'.$file_info['filename'].'.pdf', 'F');
                    }
                    return true;
                    break;
            }
        }
        return false;
    }

}