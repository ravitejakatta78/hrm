<?php 
require_once '../Classes/PHPExcel.php';

$phpExcel = new PHPExcel();

// We'll be outputting an excel file
//header('Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

// It will be called file.xls
//header('Content-Disposition: attachment; filename="file.xlsx"');

//header('Cache-Control: max-age=0');

//$objWriter = PHPExcel_IOFactory::createWriter($phpExcel,'Excel2007');

// Write file to the browser
//$objWriter->save('php://output');
?>