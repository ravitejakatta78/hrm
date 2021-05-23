<?php 
require_once 'Classes/PHPExcel.php';


/*$objPHPExcel = new PHPExcel();
$objPHPExcel->getActiveSheet()->setCellValue('A2','hello world');
$objPHPExcel->getActiveSheet()->setCellValue('A3','Teja');
$objPHPExcel->getActiveSheet()->setCellValue('A4','Ravi');
$objPHPExcel->getActiveSheet()->setTitle('Cheese1');
$objPHPExcel->getActiveSheet()
        ->getStyle('A1')
        ->getFill()
        ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
        ->getStartColor()
        ->setRGB('FF0000');
		
		--------------------
		
		$objPHPExcel = new PHPExcel();
$activeSheet = $objPHPExcel->getActiveSheet();
$objPHPExcel->getActiveSheet()->setTitle('Senior Managers Site Tour');
$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(77);

$styleArray = array(
    'borders' => array(
        'allborders' => array(
            'style' => PHPExcel_Style_Border::BORDER_THIN
            )
        )
    );

$objPHPExcel->getActiveSheet()->getStyle('A5:D7')->applyFromArray($styleArray);
unset($styleArray); 
$objPHPExcel->getActiveSheet()->mergeCells('C6:D6');
$objPHPExcel->getActiveSheet()->mergeCells('C7:D7');
----------------------
	

$objPHPExcel = new PHPExcel();
$activeSheet = $objPHPExcel->getActiveSheet();
$objPHPExcel->getActiveSheet()->setTitle('Purchase Details');
$objPHPExcel->getActiveSheet()->setCellValue('A4','S.No');
$objPHPExcel->getActiveSheet()->setCellValue('B4','Date');
$objPHPExcel->getActiveSheet()->setCellValue('C4','Purchase Number');
$objPHPExcel->getActiveSheet()->setCellValue('D4','Ingredient Name');
$objPHPExcel->getActiveSheet()->setCellValue('E4','Ingredient Quantity');
$objPHPExcel->getActiveSheet()->setCellValue('F4','Ingredient Price');
$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(-1);
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(-1);
$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(-1);
$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(-1);
$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(-1);
$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(-1);


$styleArray = array(
    'borders' => array(
        'allborders' => array(
            'style' => PHPExcel_Style_Border::BORDER_THIN
            )
        )
    );
$objPHPExcel->getActiveSheet()->setCellValue('A5','hello world');
$objPHPExcel->getActiveSheet()->setCellValue('B5','Teja');
$objPHPExcel->getActiveSheet()->setCellValue('C5','Ravi');
$objPHPExcel->getActiveSheet()->getStyle('A4:F4')->applyFromArray($styleArray);
unset($styleArray); 
$objPHPExcel->getActiveSheet()->mergeCells('A1:F1');
//$objPHPExcel->getActiveSheet()->mergeCells('C7:D7');
*/
$phpExcel = new PHPExcel();

$styleArray = array(
	'font' => array(
		'bold' => true,
	)
	
);

$styleBordersArray = array(
   'borders' => array(
        'allborders' => array(
            'style' => PHPExcel_Style_Border::BORDER_THIN
            )
        )
    );


//Get the active sheet and assign to a variable
$foo = $phpExcel->getActiveSheet();

//add column headers, set the title and make the text bold
$foo->setCellValue("A1", "Foo1")
	->setCellValue("B1", "Foo2")
	->setCellValue("C1", "Foo3")
	->setCellValue("D1", "Foo3")
	->setTitle("Foo")
	->getStyle("A1:D1")->applyFromArray($styleArray);
$foo->getStyle("A1:D20")->applyFromArray($styleBordersArray);
   //Create a new sheet
$bar = $phpExcel->createSheet();
$bar->setCellValue("A1", "Bar1")
	->setCellValue("B1", "Bar2")
	->setCellValue("C1", "Bar3")
	->setCellValue("D1", "Bar3")
	->setTitle("Bar")
	->getStyle("A1:D1")->applyFromArray($styleArray);

//When in loops you always need to use a counter to ensure data goes into the next row.
for ($rowCounter = 2; $rowCounter < 20; $rowCounter++) {

	$foo->setCellValue("A$rowCounter", "Row" . ($rowCounter - 2))
		->setCellValue("B$rowCounter", $rowCounter * 2)
		->setCellValue("C$rowCounter", $rowCounter / 2)
		->setCellValue("D$rowCounter", "=B$rowCounter+C$rowCounter");

	$bar->setCellValue("A$rowCounter", "Row" . ($rowCounter - 2))
		->setCellValue("B$rowCounter", ($rowCounter % 2) ? "Type 1" : "Type2")
		->setCellValue("C$rowCounter", str_repeat("foo ", rand(5, 10)))
		->setCellValue("D$rowCounter", str_repeat("% ", rand(20, 50)));
}

//Merge the first two columns of the next row and sum columns C & D.
$foo->mergeCells("A$rowCounter:B$rowCounter");
$foo->setCellValue("A$rowCounter", "Total")
	->setCellValue("C$rowCounter", "=SUM(C2:C" . ($rowCounter -1) . ")")
	->setCellValue("D$rowCounter", "=SUM(D2:D" . ($rowCounter -1) . ")");

//Set the text alignment to right for the total cell.
$foo->getStyle("A$rowCounter")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

//Set the column widths
$foo->getColumnDimension("A")->setWidth(20);
$foo->getColumnDimension("B")->setWidth(20);
$foo->getColumnDimension("C")->setWidth(20);
$foo->getColumnDimension("D")->setWidth(20);

$bar->getColumnDimension("A")->setAutoSize(true);
$bar->getColumnDimension("B")->setAutoSize(true);
$bar->getColumnDimension("C")->setAutoSize(true);
$bar->getColumnDimension("D")->setWidth(40);

//Wrap long fields
$bar->getStyle("D1:D20")->getAlignment()->setWrapText(true);

//Set the active sheet to the first sheet before outputting. This is only needed if you want to ensure the file is opened on the first sheet.
$phpExcel->setActiveSheetIndex(0);

// We'll be outputting an excel file
header('Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

// It will be called file.xls
header('Content-Disposition: attachment; filename="file.xlsx"');

header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($phpExcel,'Excel2007');

// Write file to the browser
$objWriter->save('php://output');
?>