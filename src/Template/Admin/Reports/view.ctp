<?php
if ($this->request->getQuery('debug')) {
    $this->layout = 'default';
} else {
    $objWriter = \PHPExcel_IOFactory::createWriter($reportSpreadsheet, 'Excel2007');
    $objWriter->save('php://output');
}
