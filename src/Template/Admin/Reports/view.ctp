<?php
$objWriter = \PHPExcel_IOFactory::createWriter($reportSpreadsheet, 'Excel2007');
$objWriter->save('php://output');
