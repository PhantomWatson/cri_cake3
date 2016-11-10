<?php
if (isset($_GET['debug'])) {
    $this->layout = 'default';
} else {
    $objWriter = \PHPExcel_IOFactory::createWriter($ocraReportSpreadsheet, 'Excel2007');
    $objWriter->save('php://output');
}
