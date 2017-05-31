<?php
namespace App\Reports\FullReports;

use App\Reports\FullReports\Sheets\NotesSheet;
use App\Reports\FullReports\Sheets\OfficialsSurveySheet;
use App\Reports\FullReports\Sheets\OrganizationsSurveySheet;
use App\Reports\FullReports\Sheets\RecentActivitySheet;
use App\Reports\Reports;
use App\Reports\Spreadsheet;

class AdminReport
{
    /**
     * Returns a PHPExcel object for the admin report
     *
     * @return \PHPExcel
     */
    public function getSpreadsheet()
    {
        $Report = new Reports();
        $data = $Report->getReport();

        $workbook = new Spreadsheet();
        $workbook
            ->setTitle('CRI Admin Report - ' . date('F j, Y'))
            ->setMetadataTitle()
            ->setAuthor('Center for Business and Economic Research, Ball State University')

            // Remove default starting sheet
            ->removeSheet();

        $sheet = new OfficialsSurveySheet();
        $workbook = $sheet->addSheetToWorkbook($workbook, $data);

        $sheet = new OrganizationsSurveySheet();
        $workbook = $sheet->addSheetToWorkbook($workbook, $data);

        $sheet = new NotesSheet();
        $workbook = $sheet->addSheetToWorkbook($workbook, $data);

        $sheet = new RecentActivitySheet();
        $workbook = $sheet->addSheetToWorkbook($workbook, $data);

        $workbook->selectFirstSheet();
        $phpExcelObj = $workbook->get();

        return $phpExcelObj;
    }
}
