<?php
declare(strict_types=1);

namespace App\Reports\FullReports;

use App\Reports\FullReports\Sheets\NotesSheet;
use App\Reports\FullReports\Sheets\OfficialsSurveySheet;
use App\Reports\FullReports\Sheets\OrganizationsSurveySheet;
use App\Reports\FullReports\Sheets\RecentActivitySheet;
use App\Reports\Reports;
use App\Reports\Spreadsheet;

class OcraReport
{
    /**
     * Returns a PHPExcel object for the OCRA full report
     *
     * @return \PHPExcel
     */
    public function getSpreadsheet()
    {
        $Report = new Reports();
        $data = $Report->getReport();

        $workbook = new Spreadsheet();
        $workbook
            ->setTitle('CRI Report for OCRA - ' . date('F j, Y'))
            ->setMetadataTitle()
            ->setAuthor('Center for Business and Economic Research, Ball State University')

            // Remove default starting sheet
            ->removeSheet();

        $mode = 'ocra';
        $sheet = new OfficialsSurveySheet($mode);
        $workbook = $sheet->addSheetToWorkbook($workbook, $data);

        $sheet = new OrganizationsSurveySheet($mode);
        $workbook = $sheet->addSheetToWorkbook($workbook, $data);

        $sheet = new NotesSheet($mode);
        $workbook = $sheet->addSheetToWorkbook($workbook, $data);

        $sheet = new RecentActivitySheet($mode);
        $workbook = $sheet->addSheetToWorkbook($workbook, $data);

        $workbook->selectFirstSheet();
        $phpExcelObj = $workbook->get();

        return $phpExcelObj;
    }
}
