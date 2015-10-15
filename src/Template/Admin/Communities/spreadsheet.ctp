<?php
	if (isset($_GET['debug'])) {
	    $this->layout = 'ajax';
		echo '<pre>';
        print_r($objPHPExcel);
	}

	$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	$objWriter->save('php://output');

	if (isset($_GET['debug'])) {
		echo '</pre>';
	}