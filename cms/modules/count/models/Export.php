<?php
/** PHPExcel */
require_once LIBRARY_PATH . 'PHPExcel/PHPExcel.php';	

/** PHPExcel_RichText */
require_once LIBRARY_PATH . 'PHPExcel/PHPExcel/RichText.php';

function GetObjPHPExcel($name, $title, $data){
	// Create new PHPExcel object
	$objPHPExcel = new PHPExcel();

	function ch($string){
		return iconv("GB2312", "UTF-8", $string);
	}

	// Set properties
	$objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
								 ->setLastModifiedBy("Maarten Balliauw")
								 ->setTitle("Office 2007 XLSX Test Document")
								 ->setSubject("Office 2007 XLSX Test Document")
								 ->setDescription("Test document for Office 2007 XLSX.")
								 ->setKeywords("office 2007 openxml php")
								 ->setCategory("Test result file");


	// Create a first sheet, representing sales data
	$objPHPExcel->setActiveSheetIndex(0);
	$objPHPExcel->getActiveSheet()->setCellValue('B1', ch($name));
	$objPHPExcel->getActiveSheet()->setCellValue('F1', 'Export time:'.date('Y-m-d H:i:s'));
	$objPHPExcel->getActiveSheet()->getStyle('F1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
	$objPHPExcel->getActiveSheet()->getStyle('F1')->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_XLSX15);		

	$objPHPExcel->getActiveSheet()->setCellValue('A3', $title[0]);
	$objPHPExcel->getActiveSheet()->setCellValue('B3', $title[1]);
	$objPHPExcel->getActiveSheet()->setCellValue('C3', $title[2]);
	$objPHPExcel->getActiveSheet()->setCellValue('D3', $title[3]);
	$objPHPExcel->getActiveSheet()->setCellValue('E3', $title[4]);
	$objPHPExcel->getActiveSheet()->setCellValue('F3', $title[5]);
	$i= 4;
	foreach($data as $var){
		$objPHPExcel->getActiveSheet()->setCellValue('A'.$i, $var[0]);
		$objPHPExcel->getActiveSheet()->setCellValue('B'.$i, $var[1]);
		$objPHPExcel->getActiveSheet()->setCellValue('C'.$i, $var[2]);
		$objPHPExcel->getActiveSheet()->setCellValue('D'.$i, $var[3]);
		$objPHPExcel->getActiveSheet()->setCellValue('E'.$i, $var[4]);
		$objPHPExcel->getActiveSheet()->setCellValue('F'.$i, $var[5]);
		$objPHPExcel->getActiveSheet()->getStyle('A'.$i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
		$objPHPExcel->getActiveSheet()->getStyle('B'.$i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
		$objPHPExcel->getActiveSheet()->getStyle('C'.$i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
		$objPHPExcel->getActiveSheet()->getStyle('D'.$i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
		$objPHPExcel->getActiveSheet()->getCell('B'.$i)->getHyperlink()->setUrl($var[6]);
		$objPHPExcel->getActiveSheet()->getCell('B'.$i)->getHyperlink()->setTooltip('Navigate to website');
		$objPHPExcel->getActiveSheet()->getStyle('B'.$i)->getFont()->setUnderline(PHPExcel_Style_Font::UNDERLINE_SINGLE);
		$objPHPExcel->getActiveSheet()->getStyle('B'.$i)->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_BLUE);
		$i++;
	}
	$j= $i-1;
	$objPHPExcel->getActiveSheet()->setCellValue('E'.$i, "=SUM(E4:E{$j})");
	$objPHPExcel->getActiveSheet()->setCellValue('F'.$i, "=SUM(F4:F{$j})");
	$objPHPExcel->getActiveSheet()->mergeCells("A{$i}:D{$i}");
	$objPHPExcel->getActiveSheet()->setCellValue("A{$i}", 'The total article number is :'.count($data));

	/*
	// Add rich-text string
	$objRichText = new PHPExcel_RichText( $objPHPExcel->getActiveSheet()->getCell('A'.($i+1)) );
	$objRichText->createText("Note:");

	$objPayable = $objRichText->createTextRun("if these statistics have problems, please contact us!");
	$objPayable->getFont()->setBold(true);
	$objPayable->getFont()->setItalic(true);
	$objPayable->getFont()->setColor( new PHPExcel_Style_Color( PHPExcel_Style_Color::COLOR_DARKGREEN ) );	

	// Merge cells
	$objPHPExcel->getActiveSheet()->mergeCells("A".($i+1).":F".($i+5));
	$objPHPExcel->getActiveSheet()->getStyle("A".($i+1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);	
	*/

	// Set column widths
	//$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(60);
	$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(24);
	$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(12);
	$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(12);

	// Set fonts
	$objPHPExcel->getActiveSheet()->getStyle('B1')->getFont()->setName('Candara');
	$objPHPExcel->getActiveSheet()->getStyle('B1')->getFont()->setSize(20);
	$objPHPExcel->getActiveSheet()->getStyle('B1')->getFont()->setBold(true);
	$objPHPExcel->getActiveSheet()->getStyle('B1')->getFont()->setUnderline(PHPExcel_Style_Font::UNDERLINE_SINGLE);
	$objPHPExcel->getActiveSheet()->getStyle('B1')->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_WHITE);

	$objPHPExcel->getActiveSheet()->getStyle('D1')->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_WHITE);
	$objPHPExcel->getActiveSheet()->getStyle('E1')->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_WHITE);


	// Set thin black border outline around column
	$styleThinBlackBorderOutline = array(
		'borders' => array(
			'outline' => array(
				'style' => PHPExcel_Style_Border::BORDER_THIN,
				'color' => array('argb' => 'FF000000'),
			),
		),
	);
	$objPHPExcel->getActiveSheet()->getStyle('A4:F'.$i)->applyFromArray($styleThinBlackBorderOutline);

	// Set fills
	$objPHPExcel->getActiveSheet()->getStyle('A1:F1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
	$objPHPExcel->getActiveSheet()->getStyle('A1:F1')->getFill()->getStartColor()->setARGB('FF808080');

	// Set style for header row using alternative method
	$objPHPExcel->getActiveSheet()->getStyle('A3:F3')->applyFromArray(
			array(
				'font'    => array(
					'bold'      => true
				),
				'alignment' => array(
					'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
				),
				'borders' => array(
					'top'     => array(
						'style' => PHPExcel_Style_Border::BORDER_THIN
					)
				),
				'fill' => array(
					'type'       => PHPExcel_Style_Fill::FILL_GRADIENT_LINEAR,
					'rotation'   => 90,
					'startcolor' => array(
						'argb' => 'FFA0A0A0'
					),
					'endcolor'   => array(
						'argb' => 'FFFFFFFF'
					)
				)
			)
	);
			
	$objPHPExcel->getActiveSheet()->getStyle('A3')->applyFromArray(
			array(
				'alignment' => array(
					'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
				),
				'borders' => array(
					'left'     => array(
						'style' => PHPExcel_Style_Border::BORDER_THIN
					)
				)
			)
	);

	$objPHPExcel->getActiveSheet()->getStyle('B3')->applyFromArray(
			array(
				'alignment' => array(
					'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
				)
			)
	);

	$objPHPExcel->getActiveSheet()->getStyle('E3')->applyFromArray(
			array(
				'borders' => array(
					'right'     => array(
						'style' => PHPExcel_Style_Border::BORDER_THIN
					)
				)
			)
	);

	
	// Add a drawing to the worksheet
	$objDrawing = new PHPExcel_Worksheet_Drawing();
	$objDrawing->setName('Logo');
	$objDrawing->setDescription('Logo');
	$objDrawing->setPath(WEB_PATH.'images/officelogo.jpg');
	$objDrawing->setHeight(36);
	$objDrawing->setWorksheet($objPHPExcel->getActiveSheet());
	/*
	// Add a drawing to the worksheet
	$objDrawing = new PHPExcel_Worksheet_Drawing();
	$objDrawing->setName('TitanCMS logo');
	$objDrawing->setDescription('TitanCMS logo');
	$objDrawing->setPath(WEB_PATH.'theme/default/image/logo.jpg');
	$objDrawing->setHeight(36);
	$objDrawing->setCoordinates('D'.($i+2));
	$objDrawing->setOffsetX(10);
	$objDrawing->setWorksheet($objPHPExcel->getActiveSheet());
	$objPHPExcel->getActiveSheet()->getCell('D'.($i+2))->getHyperlink()->setUrl('http://cms.titan24.net.cn');
	*/

	// Set header and footer. When no different headers for odd/even are used, odd header is assumed.
	$objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader('&L&BInvoice&RPrinted on &D');
	$objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L&B' . $objPHPExcel->getProperties()->getTitle() . '&RPage &P of &N');

	// Set page orientation and size
	$objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_PORTRAIT);
	$objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);

	// Rename sheet
	$objPHPExcel->getActiveSheet()->setTitle('Sheet1');
	return $objPHPExcel;
}
