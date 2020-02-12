<?php
/**
 * @var \App\View\AppView $this
 * @var mixed $reportSpreadsheet
 */
$objWriter = \PHPExcel_IOFactory::createWriter($reportSpreadsheet, 'Excel2007');
$objWriter->save('php://output');
